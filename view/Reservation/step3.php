<?php
include '../db/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $niveau = $_POST['Niveau'] ?? '';
    $group_name = $_POST['GroupName'] ?? '';
    $filiere = $_POST['Filiere'] ?? '';
    $semester = $_POST['Semester'] ?? '';
    $room_type = $_POST['room-type'] ?? '';
    $subject_id = $_POST['Matier'] ?? '';
    $prof_id = $_POST['Prof'] ?? '';
    $date = $_POST['date_debut'] ?? '';

    // Use IntlDateFormatter to get the day of the week in French
    $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE);
    $formatter->setPattern('EEEE');
    $day_of_week = $formatter->format(strtotime($date)); 

    // Get the number of students in the group
    $sql_group = "SELECT nombre FROM grp WHERE name = ? AND year = ? AND (extra_info = ? OR filiere = ?)";
    $stmt_group = $conn->prepare($sql_group);
    $stmt_group->bind_param("ssss", $group_name, $niveau, $filiere, $filiere);
    $stmt_group->execute();
    $result_group = $stmt_group->get_result();

    if (!$result_group || $result_group->num_rows === 0) {
        die("Erreur lors de la récupération du groupe : " . $conn->error);
    }

    $group = $result_group->fetch_assoc();
    $group_size = $group['nombre'];

    if ($group_size == 0) {
        die("Le groupe spécifié n'a pas été trouvé ou la taille du groupe est invalide.");
    }

    $capacity_column = ($room_type == 'exam' || $room_type == 'controle') ? 'capacity_exam' : 'capacity';
    $sql_rooms = "SELECT id, name, $capacity_column as capacity FROM salles 
                  WHERE FIND_IN_SET(?, room_type) AND $capacity_column >= ?";
    $stmt_rooms = $conn->prepare($sql_rooms);
    $stmt_rooms->bind_param("si", $room_type, $group_size);
    $stmt_rooms->execute();
    $result_rooms = $stmt_rooms->get_result();

    if (!$result_rooms) {
        die("Erreur lors de la récupération des salles : " . $conn->error);
    }

    $is_odd_semester = in_array($semester, [1, 3, 5]);
    $semester_condition = $is_odd_semester ? 'IN (1, 3, 5)' : 'IN (2, 4, 6)';
    
    // Fetch existing reservations for the selected day and appropriate semesters
    $sql_reservations = "SELECT salle_id, start_time, end_time FROM reservation 
                         WHERE jour_par_semaine = ? 
                         AND semester_id $semester_condition 
                         AND salle_id IN (SELECT id FROM salles WHERE FIND_IN_SET(?, room_type) AND $capacity_column >= ?)";
    $stmt = $conn->prepare($sql_reservations);
    $stmt->bind_param("ssi", $day_of_week, $room_type, $group_size);
    $stmt->execute();
    $result_reservations = $stmt->get_result();
    
    // Fetch reservations from controle table
    $sql_controle = "SELECT sc.salle_id, c.start_time, c.end_time FROM controle c
                     JOIN salles_controle sc ON c.id = sc.controle_id
                     WHERE c.controle_date = ?";
    $stmt_controle = $conn->prepare($sql_controle);
    $stmt_controle->bind_param("s", $date);
    $stmt_controle->execute();
    $result_controle = $stmt_controle->get_result();
    
    // Fetch reservations from rattrapage table
    $sql_rattrapage = "SELECT salle_id, start_time, end_time FROM rattrapage 
                       WHERE rattrapage_date = ?";
    $stmt_rattrapage = $conn->prepare($sql_rattrapage);
    $stmt_rattrapage->bind_param("s", $date);
    $stmt_rattrapage->execute();
    $result_rattrapage = $stmt_rattrapage->get_result();
    
    // Fetch reservations from evenement table
    $sql_evenement = "SELECT salle_id, start_time, end_time FROM evenement 
                      WHERE event_date = ?";
    $stmt_evenement = $conn->prepare($sql_evenement);
    $stmt_evenement->bind_param("s", $date);
    $stmt_evenement->execute();
    $result_evenement = $stmt_evenement->get_result();
    
    // Fetch reservations in attente from rapport table
    $sql_rapport = "SELECT res.salle_id, res.start_time, res.end_time FROM rapport r
                    JOIN reservation res ON r.reservation_id = res.id
                    WHERE r.statut = 'En attente' AND r.rapport_date = ?";
    $stmt_rapport = $conn->prepare($sql_rapport);
    $stmt_rapport->bind_param("s", $date);
    $stmt_rapport->execute();
    $result_rapport = $stmt_rapport->get_result();
    
    // Store all reservations from the different queries in an associative array
    $reservations = [];
    while ($row = $result_reservations->fetch_assoc()) {
        $reservations[$row['salle_id']][] = ['start' => $row['start_time'], 'end' => $row['end_time']];
    }
    while ($row = $result_controle->fetch_assoc()) {
        $reservations[$row['salle_id']][] = ['start' => $row['start_time'], 'end' => $row['end_time']];
    }
    while ($row = $result_rattrapage->fetch_assoc()) {
        $reservations[$row['salle_id']][] = ['start' => $row['start_time'], 'end' => $row['end_time']];
    }
    while ($row = $result_evenement->fetch_assoc()) {
        $reservations[$row['salle_id']][] = ['start' => $row['start_time'], 'end' => $row['end_time']];
    }
    while ($row = $result_rapport->fetch_assoc()) {
        $reservations[$row['salle_id']][] = ['start' => $row['start_time'], 'end' => $row['end_time']];
    }
    
    $time_slots = [
        'Lundi-Vendredi' => [
            '09:00-10:30',
            '10:45-12:15',
            '14:00-15:30',
            '15:45-17:15'
        ],
        'Samedi' => [
            '09:00-10:30',
            '10:45-12:15'
        ]
    ];

    function generate_html_time_slots($time_slots, $reservations, $room_id) {
        $html = '<select class="time-slot-select" data-room-id="' . htmlspecialchars($room_id) . '">';
        foreach ($time_slots as $slot) {
            list($start_time, $end_time) = explode('-', $slot);
            $is_reserved = false;
            if (isset($reservations[$room_id])) {
                foreach ($reservations[$room_id] as $reservation) {
                    // Check if the slot overlaps with any reservation
                    if ((strtotime($start_time) < strtotime($reservation['end'])) && (strtotime($end_time) > strtotime($reservation['start']))) {
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
            const selectedRoom = document.getElementById('selected_room').value;
            const timeSlotSelects = document.querySelectorAll('.time-slot-select');

            let availableTimes = [];
            timeSlotSelects.forEach(select => {
                if (select.getAttribute('data-room-id') === selectedRoom) {
                    select.querySelectorAll('option').forEach(option => {
                        if (!option.classList.contains('reserved')) {
                            availableTimes.push(option.value);
                        }
                    });
                }
            });

            const selectedTimeSelect = document.getElementById('selected_time');
            selectedTimeSelect.innerHTML = '';
            availableTimes.forEach(time => {
                const option = document.createElement('option');
                option.value = time;
                option.text = time;
                selectedTimeSelect.appendChild(option);
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('selected_room').addEventListener('change', updateAvailableTimes);
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
        <li><a href='../rapport/rapports.php'><i class='fas fa-file-alt'></i> Rapport</a></li>
        <li><a href='../planning/planning.php'><i class='fas fa-calendar'></i> Planning</a></li>
        <li><a href='#'><i class='fas fa-sign-out-alt'></i> Déconnexion</a></li>
    </ul>
</div>
<div class="main-content">
    <div class="container">
        <div class="add-classes">
            <h3>Troisième Étape de Réservation</h3>
            <p class="required-fields">* Tous les champs sont obligatoires</p>
            
            <table class="table-spacing">
                <tr>
                    <th>Nom de la Salle</th>
                    <th>Heure disponible</th>
                </tr>
                <?php
                if ($result_rooms->num_rows > 0) {
                    $result_rooms->data_seek(0); 
                    while ($room = $result_rooms->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($room['name']) . '</td>';
                        echo '<td>';
                        
                        $day_key = $day_of_week == 'samedi' ? 'Samedi' : 'Lundi-Vendredi';
                        
                        echo generate_html_time_slots($time_slots[$day_key], $reservations, $room['id']);
                        
                        echo '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="2">Aucune salle disponible</td></tr>';
                }
                ?>
            </table>
            
            <br>
            <form action="step4.php" method="POST">
                <label for="selected_room">Sélectionnez la salle * :</label>
                <select name="selected_room" id="selected_room" class="select-spacing" required>
                    <?php
                    $result_rooms->data_seek(0); 
                    while ($room = $result_rooms->fetch_assoc()) {
                        echo '<option value="' . htmlspecialchars($room['id']) . '">' . htmlspecialchars($room['name']) . '</option>';
                    }
                    ?>
                </select>
                <br><br>
                <label for="selected_time">Sélectionnez l'heure * :</label>
                <select name="selected_time" id="selected_time" class="select-spacing" required>
                </select>
                <br><br>
                <input type="hidden" name="Niveau" value="<?php echo htmlspecialchars($niveau); ?>">
                <input type="hidden" name="GroupName" value="<?php echo htmlspecialchars($group_name); ?>">
                <input type="hidden" name="Filiere" value="<?php echo htmlspecialchars($filiere); ?>">
                <input type="hidden" name="Semester" value="<?php echo htmlspecialchars($semester); ?>">
                <input type="hidden" name="room-type" value="<?php echo htmlspecialchars($room_type); ?>">
                <input type="hidden" name="Matier" value="<?php echo htmlspecialchars($subject_id); ?>">
                <input type="hidden" name="Prof" value="<?php echo htmlspecialchars($prof_id); ?>">
                <input type="hidden" name="date_debut" value="<?php echo htmlspecialchars($date); ?>">

                <button type="submit">Suivant</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
