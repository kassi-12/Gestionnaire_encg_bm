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
    echo "Filiere/Section: " . implode(', ', array_map('htmlspecialchars', $filieres)) . "<br>";
    echo "reservation_id : " . implode(', ', array_map('htmlspecialchars', $reservation_id)) . "<br>";
    // Use IntlDateFormatter to get the day of the week in French
    $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE);
    $formatter->setPattern('EEEE');
    $day_of_week = $formatter->format(strtotime($date));

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
            WHERE name = '$groupName_safe' 
            AND year = '$niveau' 
            AND (extra_info = '$filiere_safe' OR filiere = '$filiere_safe')
        ";
        $result_group_id = $conn->query($sql_group_id);
        if ($result_group_id) {
            $group_row = $result_group_id->fetch_assoc();
            $group_id = $group_row['id'];
            $group_size = $group_row['nombre'];
            $total_capacity += $group_size;
            $groups_info[] = ['id' => $group_id, 'size' => $group_size];
        }
    }

    $sql_rooms = "SELECT id, name, $capacity_column as capacity_exam FROM salles 
                  WHERE FIND_IN_SET('$room_type', room_type)";
    $result_rooms = $conn->query($sql_rooms);

    if (!$result_rooms) {
        die("Erreur lors de la récupération des salles : " . $conn->error);
    }

    $sql_reservations = "SELECT salle_id, start_time, end_time, group_id FROM reservation 
                         WHERE jour_par_semaine = '$day_of_week' 
                         AND semester_id = '$semester'";
    $result_reservations = $conn->query($sql_reservations);

    if (!$result_reservations) {
        die("Erreur lors de la récupération des réservations : " . $conn->error);
    }

    while ($row = $result_reservations->fetch_assoc()) {
        $reservations[$row['salle_id']][] = [
            'start' => $row['start_time'], 
            'end' => $row['end_time'],
            'group_id' => $row['group_id']
        ];
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
        if (isset($reservations[$room_id])) {
            foreach ($reservations[$room_id] as $reservation) {
                if (
                    strtotime($start_time) >= strtotime($reservation['start']) && 
                    strtotime($start_time) < strtotime($reservation['end']) && 
                    $reservation['group_id'] != $selected_group_id
                ) {
                    $is_reserved = true;
                    break;
                }
            }
        }
        $option_style = $is_reserved ? 'class="reserved"' : '';
        $html .= '<option value="' . $slot . '" ' . $option_style . '>' . $slot . '</option>';
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

            let availableTimes = new Set();
            timeSlotSelects.forEach(select => {
                if (selectedRooms.includes(select.getAttribute('data-room-id'))) {
                    select.querySelectorAll('option').forEach(option => {
                        if (!option.classList.contains('reserved')) {
                            availableTimes.add(option.value);
                        }
                    });
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

                    <button type="submit">Suivant</button>
                </form>

            </div>
        </div>
    </div>
</div>
</body>
</html>
