<?php
include '../db/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $niveau = $_POST['Niveau'] ?? '';
    $groupName = $_POST['GroupName'] ?? '';
    $semester = $_POST['Semester'] ?? '';
    $filieres = $_POST['Filiere'] ?? array();
    $room_type = $_POST['room-type'] ?? '';
    $date = $_POST['date_debut'] ?? '';
    $reservation_id = $_POST['reservation'] ?? array();

    echo "Niveau: " . htmlspecialchars($niveau) . "<br>";
    echo "GroupName: " . htmlspecialchars($groupName) . "<br>";
    echo "Semester: " . htmlspecialchars($semester) . "<br>";
    echo "reservation_id : " . implode(', ', array_map('htmlspecialchars', $reservation_id)) . "<br>";
    echo "Filiere : " . implode(', ', array_map('htmlspecialchars', $filieres)) . "<br>";

    // Use IntlDateFormatter to get the day of the week in French
    $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE);
    $formatter->setPattern('EEEE');
    $day_of_week = $formatter->format(new DateTime($date));

    $capacity_column = ($room_type == 'exam' || $room_type == 'controle') ? 'capacity_exam' : 'capacity';

    // Prepare to fetch reservations for each filiere and group
    $reservations = [];
    $groups_info = [];
    $total_capacity = 0;

    foreach ($filieres as $filiere) {
        $filiere_safe = $conn->real_escape_string($filiere);
        $groupName_safe = $conn->real_escape_string($groupName);

        $sql_group_id = "
            SELECT id, nombre 
            FROM grp 
            WHERE name = ? 
            AND year = ? 
            AND (extra_info = ? OR filiere = ?)
        ";
        if ($stmt = $conn->prepare($sql_group_id)) {
            $stmt->bind_param('ssss', $groupName_safe, $niveau, $filiere_safe, $filiere_safe);
            $stmt->execute();
            $result_group_id = $stmt->get_result();
            if ($result_group_id) {
                $group_row = $result_group_id->fetch_assoc();
                $group_id = $group_row['id'];
                $group_size = $group_row['nombre'];
                $total_capacity += $group_size;
                $groups_info[] = ['id' => $group_id, 'size' => $group_size];
            }
            $stmt->close();
        }
    }

    $sql_rooms = "SELECT id, name, $capacity_column as capacity_exam FROM salles 
                  WHERE FIND_IN_SET(?, room_type)";
    if ($stmt_rooms = $conn->prepare($sql_rooms)) {
        $stmt_rooms->bind_param('s', $room_type);
        $stmt_rooms->execute();
        $result_rooms = $stmt_rooms->get_result();

        if (!$result_rooms) {
            die("Erreur lors de la récupération des salles : " . $conn->error);
        }

        // Fetch reservations from reservation table
        $odd_semesters = ['1', '3', '5'];
        $even_semesters = ['2', '4', '6'];

        $is_odd_semester = in_array($semester, $odd_semesters);
        $semester_group = $is_odd_semester ? $odd_semesters : $even_semesters;

        $placeholders = implode(',', array_fill(0, count($semester_group), '?'));

        $sql_reservations = "SELECT salle_id, start_time, end_time, group_id FROM reservation 
                            WHERE jour_par_semaine = ? 
                            AND semester_id IN ($placeholders)";

        if ($stmt_reservations = $conn->prepare($sql_reservations)) {
            $params = array_merge([$day_of_week], $semester_group);
            $types = str_repeat('s', count($params));
            $stmt_reservations->bind_param($types, ...$params);
            $stmt_reservations->execute();
            $result_reservations = $stmt_reservations->get_result();
            if (!$result_reservations) {
                die("Erreur lors de la récupération des réservations : " . $conn->error);
            }

            // Fetch all reservations
            $all_reservations = [];
            while ($row = $result_reservations->fetch_assoc()) {
                $all_reservations[$row['salle_id']][] = [
                    'start' => $row['start_time'],
                    'end' => $row['end_time'],
                    'group_id' => $row['group_id'],
                    'is_rapport' => false
                ];
            }

            // Filter reservations based on semester
            foreach ($all_reservations as $salle_id => $res_list) {
                foreach ($res_list as $res) {
                    if ($is_odd_semester) {
                        if (in_array($semester, $odd_semesters)) {
                            $reservations[$salle_id][] = $res;
                        }
                    } else {
                        if (in_array($semester, $even_semesters)) {
                            $reservations[$salle_id][] = $res;
                        }
                    }
                }
            }

            $stmt_reservations->close();
        }

        // Fetch additional control, rattrapage, and event reservations
        $sql_controle = "SELECT sc.salle_id, c.start_time, c.end_time FROM controle c
                         JOIN salles_controle sc ON c.id = sc.controle_id
                         WHERE c.controle_date = ?";
        $stmt_controle = $conn->prepare($sql_controle);
        $stmt_controle->bind_param("s", $date);
        $stmt_controle->execute();
        $result_controle = $stmt_controle->get_result();

        $sql_rattrapage = "SELECT salle_id, start_time, end_time FROM rattrapage 
                           WHERE rattrapage_date = ?";
        $stmt_rattrapage = $conn->prepare($sql_rattrapage);
        $stmt_rattrapage->bind_param("s", $date);
        $stmt_rattrapage->execute();
        $result_rattrapage = $stmt_rattrapage->get_result();

        $sql_evenement = "SELECT salle_id, start_time, end_time FROM evenement 
                          WHERE event_date = ?";
        $stmt_evenement = $conn->prepare($sql_evenement);
        $stmt_evenement->bind_param("s", $date);
        $stmt_evenement->execute();
        $result_evenement = $stmt_evenement->get_result();

        // Fetch reservations from the 'rapport' table with 'en attente' status
        $sql_rapport = "SELECT r.salle_id, r.start_time, r.end_time 
                        FROM rapport rp
                        JOIN reservation r ON rp.reservation_id = r.id
                        WHERE rp.rapport_date = ? 
                        AND rp.statut = 'en attente'";
        $stmt_rapport = $conn->prepare($sql_rapport);
        $stmt_rapport->bind_param("s", $date);
        $stmt_rapport->execute();
        $result_rapport = $stmt_rapport->get_result();

        // Integrate all reservation results
        while ($row = $result_reservations->fetch_assoc()) {
            $reservations[$row['salle_id']][] = [
                'start' => $row['start_time'], 
                'end' => $row['end_time'],
                'group_id' => $row['group_id'],
                'is_rapport' => false
            ];
        }
        while ($row = $result_controle->fetch_assoc()) {
            $reservations[$row['salle_id']][] = [
                'start' => $row['start_time'], 
                'end' => $row['end_time'],
                'group_id' => null,
                'is_rapport' => false
            ];
        }
        while ($row = $result_rattrapage->fetch_assoc()) {
            $reservations[$row['salle_id']][] = [
                'start' => $row['start_time'], 
                'end' => $row['end_time'],
                'group_id' => null,
                'is_rapport' => false
            ];
        }
        while ($row = $result_evenement->fetch_assoc()) {
            $reservations[$row['salle_id']][] = [
                'start' => $row['start_time'], 
                'end' => $row['end_time'],
                'group_id' => null,
                'is_rapport' => false
            ];
        }
        while ($row = $result_rapport->fetch_assoc()) {
            $reservations[$row['salle_id']][] = [
                'start' => $row['start_time'], 
                'end' => $row['end_time'],
                'group_id' => null,
                'is_rapport' => true
            ];
        }
        $stmt_rooms->close();
    }
}

$time_slots = [
    'Lundi-Jeudi' => [
        '09:00-10:30',
        '10:45-12:15',
        '14:00-15:30',
        '15:45-17:15'
    ],
    'Vendredi' => [
        '09:00-10:30',
        '10:45-12:15',
        '15:00-16:30',
        '16:45-18:15'
    ],
    'Samedi' => [
        '09:00-10:30',
        '10:45-12:15'
    ]
];
function generate_html_time_slots($time_slots, $reservations, $room_id, $selected_group_id) {
    $html = '<select class="time-slot-select" data-room-id="' . htmlspecialchars($room_id) . '">';
    
    foreach ($time_slots as $slot) {
        list($start_time, $end_time) = explode('-', $slot);
        $is_reserved = false;
        $is_pending_rapport = false;

        if (isset($reservations[$room_id])) {
            foreach ($reservations[$room_id] as $reservation) {
                // Check if this reservation is from the rapport table with a status of 'en attente'
                if (isset($reservation['is_rapport']) && $reservation['is_rapport']) {
                    if (
                        strtotime($start_time) >= strtotime($reservation['start']) && 
                        strtotime($start_time) < strtotime($reservation['end'])
                    ) {
                        $is_pending_rapport = true;
                        break;
                    }
                } else {
                    // Check for other reservations
                    $reservation_group_id = isset($reservation['group_id']) ? $reservation['group_id'] : null;

                    // Check if the time slot is reserved by another group
                    if (
                        strtotime($start_time) >= strtotime($reservation['start']) && 
                        strtotime($start_time) < strtotime($reservation['end']) && 
                        $reservation_group_id !== $selected_group_id
                    ) {
                        $is_reserved = true;
                    }
                }
            }
        }

        // Skip this slot if it's pending in the rapport table
        if ($is_pending_rapport) {
            continue;
        }

        // Determine the style and availability of the slot
        $option_style = $is_reserved ? 'class="reserved"' : '';
        $html .= '<option value="' . htmlspecialchars($slot) . '" ' . $option_style . '>' . htmlspecialchars($slot) . '</option>';
    }
    $html .= '</select>';
    return $html;
}

?>






<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Troisième Étape de Réservation</title>
    <link rel="stylesheet" href="../../assets/styles.css">
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css'>

    <style>
        .reserved {
            background-color: gray !important;
            pointer-events: none;
            color: white;
        }
    </style>
    <script>
        function updateAvailableTimes() {
            const selectedRooms = Array.from(document.getElementById('selected_rooms').selectedOptions).map(option => option.value);
            const timeSlotSelects = document.querySelectorAll('.time-slot-select');

            let availableTimes = null;
            timeSlotSelects.forEach(select => {
                if (selectedRooms.includes(select.getAttribute('data-room-id'))) {
                    const roomTimes = new Set();
                    select.querySelectorAll('option:not(.reserved)').forEach(option => {
                        roomTimes.add(option.value);
                    });
                    if (availableTimes === null) {
                        availableTimes = roomTimes;
                    } else {
                        availableTimes = new Set([...availableTimes].filter(time => roomTimes.has(time)));
                    }
                }
            });

            const selectedTimeSelect = document.getElementById('selected_times');
            selectedTimeSelect.innerHTML = '';
            availableTimes.forEach(time => {
                const option = document.createElement('option');
                option.value = time;
                option.text = time;
                selectedTimeSelect.appendChild(option);
            });

            updateTotalCapacity();
        }

        function updateTotalCapacity() {
            const selectedRooms = Array.from(document.getElementById('selected_rooms').selectedOptions).map(option => option.value);
            const groupCapacity = parseInt(document.getElementById('group_capacity').value, 10);
            let totalCapacity = groupCapacity;

            selectedRooms.forEach(room_id => {
                const roomCapacity = parseInt(document.querySelector(`option[value="${room_id}"]`).dataset.capacity, 10);
                if (!isNaN(roomCapacity)) {
                    totalCapacity -= roomCapacity;
                }
            });

            document.getElementById('total_capacity').value = totalCapacity;
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('selected_rooms').addEventListener('change', updateAvailableTimes);
            updateAvailableTimes();
        });
    </script>
</head>
<body>
<div class='sidebar'>
    <div class='logo'>
        <img src='../../image/ENCG-BM_logo_header.png' width='200' alt='Logo'>
    </div>
    <ul class='nav-links'>
        <li><a href='../dashboard/dashboard.php'><i class='fas fa-home'></i> Tableau de bord</a></li>
        <li><a href='../group/groups.php'><i class='fas fa-users'></i> Groupes</a></li>
        <li><a href='../professeur/professeur.php'><i class='fas fa-chalkboard-teacher'></i> Professeurs</a></li>
        <li><a href='../matier/matier.php'><i class='fas fa-book'></i> Matière</a></li>
        <li class='dropdown'>
            <a href='../salle/salles.php'><i class='fas fa-building'></i> Salles</a>
            <ul class='dropdown-content'>
                <li><a href='../salle/Aj_salle.php'>Ajouter une salle</a></li>
                <li><a href='../salle/Maj_salle.php'>Mettre à jour les salles</a></li>
            </ul>
        </li>
        <li class='dropdown'>
            <a href='../reservation/Reserve.php'><i class='fas fa-calendar-check'></i> Réservation</a>
            <ul class='dropdown-content'>
                <li><a href='../reservation/Evenement.php'>Événement</a></li>
                <li><a href='../reservation/normal.php'>Cours/Exam</a></li>
            </ul>
        </li>
        <li><a href='../logout/logout.php'><i class='fas fa-sign-out-alt'></i> Se déconnecter</a></li>
    </ul>
</div>
<div class="main-content">
    <div class="header">
        <h2>Réservation de Salles</h2>
    </div>
    <div class="content">
        <div class="container">
            <div class="add-classes">
                <h3>Troisième Étape de Réservation</h3>
                <p class="required-fields">* Tous les champs sont obligatoires</p>

                <table class="table-spacing">
                    <tr>
                        <th>Nom de la Salle</th>
                        <th>Capacité d'examen</th>
                        <th>Heure disponible</th>
                    </tr>
                    <?php
                    if ($result_rooms->num_rows > 0) {
                        $result_rooms->data_seek(0);
                        while ($room = $result_rooms->fetch_assoc()) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($room['name']) . '</td>';
                            echo '<td>' . htmlspecialchars($room['capacity_exam']) . '</td>';
                            echo '<td>';

                            $day_key = $day_of_week == 'vendredi' ? 'Vendredi' : ($day_of_week == 'samedi' ? 'Samedi' : 'Lundi-Jeudi');

                            echo generate_html_time_slots($time_slots[$day_key], $reservations, $room['id'], $group_id);

                            echo '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="3">Aucune salle disponible</td></tr>';
                    }
                    ?>
                </table>

                <br>
                <form action="step4.php" method="POST">
                    <label for="selected_rooms">Sélectionnez les salles * :</label>
                    <select name="selected_rooms[]" id="selected_rooms" class="select-spacing" multiple required>
                        <?php
                        $result_rooms->data_seek(0);
                        while ($room = $result_rooms->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($room['id']) . '" data-capacity="' . htmlspecialchars($room['capacity_exam']) . '">' . htmlspecialchars($room['name']) . '</option>';
                        }
                        ?>
                    </select>
                    <br><br>
                    <label for="selected_times">Sélectionnez les heures * :</label>
                    <select name="selected_times[]" id="selected_times" class="select-spacing" multiple required>
                        <!-- Options will be populated by JavaScript based on selected rooms -->
                    </select>
                    <br><br>

                    <label for="group_capacity">Capacité Totale :</label>
                    <input type="text" id="total_capacity" name="total_capacity" value="<?php echo htmlspecialchars($total_capacity); ?>" readonly>
                    <input type="hidden" id="group_capacity" value="<?php echo htmlspecialchars($total_capacity); ?>">
                    <br><br>
                    <input type="hidden" name="Niveau" value="<?php echo htmlspecialchars($niveau); ?>">
                    <input type="hidden" name="GroupName" value="<?php echo htmlspecialchars($groupName); ?>">
                    <input type="hidden" name="Semester" value="<?php echo htmlspecialchars($semester); ?>">
                    <input type="hidden" name="room-type" value="<?php echo htmlspecialchars($room_type); ?>">
                    <input type="hidden" name="date_debut" value="<?php echo htmlspecialchars($date); ?>">

                    <?php
                    // Include hidden fields for each selected reservation ID
                    foreach ($reservation_id as $id) {
                        echo '<input type="hidden" name="reservation_id[]" value="' . htmlspecialchars($id) . '">';
                    }
                    ?>
                    <?php
                    foreach ($filieres as $filiere) {
                        echo '<input type="hidden" name="Filiere[]" value="' . htmlspecialchars($filiere) . '">';
                    }
                    ?>
                    <button type="submit">Suivant</button>
                </form>

            </div>
        </div>
    </div>
</div>
</body>
</html>
