<?php
include '../db/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $niveau = isset($_POST['Niveau']) ? $_POST['Niveau'] : '';
    $group_name = isset($_POST['GroupName']) ? $_POST['GroupName'] : '';
    $filiere = isset($_POST['Filiere']) ? $_POST['Filiere'] : '';
    $semester = isset($_POST['Semester']) ? $_POST['Semester'] : '';
    $room_type = isset($_POST['room-type']) ? $_POST['room-type'] : '';
    $subject_id = isset($_POST['Matier']) ? $_POST['Matier'] : '';
    $prof_id = isset($_POST['Prof']) ? $_POST['Prof'] : '';
    $date = isset($_POST['date_debut']) ? $_POST['date_debut'] : '';

    // // Debug output
    // echo "Niveau: $niveau<br>";
    // echo "GroupName: $group_name<br>";
    // echo "Filiere: $filiere<br>";
    // echo "Semester: $semester<br>";
    // echo "Room Type: $room_type<br>";
    // echo "Subject ID: $subject_id<br>";
    // echo "Professor ID: $prof_id<br>";
    // echo "Date: $date<br>";
    
    // Use IntlDateFormatter to get the day of the week in French
    $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE);
    $formatter->setPattern('EEEE');
    $day_of_week = $formatter->format(strtotime($date)); 

    // Get the number of students in the group
    $sql_group = "SELECT nombre FROM grp WHERE name = '$group_name' AND year = '$niveau' AND (extra_info = '$filiere' OR filiere = '$filiere')";
    $result_group = $conn->query($sql_group);
    if (!$result_group) {
        die("Erreur lors de la récupération du groupe : " . $conn->error);
    }
    $group = $result_group->fetch_assoc();
    $group_size = isset($group['nombre']) ? $group['nombre'] : 0;

    // // Debug output
    // echo "Group Size: $group_size<br>";

    if ($group_size == 0) {
        die("Le groupe spécifié n'a pas été trouvé ou la taille du groupe est invalide.");
    }

    $capacity_column = ($room_type == 'exam' || $room_type == 'controle') ? 'capacity_exam' : 'capacity';
    $sql_rooms = "SELECT id, name, $capacity_column as capacity FROM salles 
                  WHERE FIND_IN_SET('$room_type', room_type) AND $capacity_column >= $group_size";
    $result_rooms = $conn->query($sql_rooms);

    if (!$result_rooms) {
        die("Erreur lors de la récupération des salles : " . $conn->error);
    }
    
    // // Debug output
    // echo "Rooms Query: $sql_rooms<br>";
    // echo "Rooms Found: " . $result_rooms->num_rows . "<br>";

    $sql_reservations = "SELECT salle_id, start_time, end_time FROM reservation 
                         WHERE jour_par_semaine = '$day_of_week' 
                         AND semester_id = '$semester' 
                         AND salle_id IN (SELECT id FROM salles WHERE FIND_IN_SET('$room_type', room_type) AND $capacity_column >= $group_size)";
    $result_reservations = $conn->query($sql_reservations);

    if (!$result_reservations) {
        die("Erreur lors de la récupération des réservations : " . $conn->error);
    }

    $reservations = [];
    while ($row = $result_reservations->fetch_assoc()) {
        $reservations[$row['salle_id']][] = ['start' => $row['start_time'], 'end' => $row['end_time']];
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

function generate_html_time_slots($time_slots, $reservations, $room_id) {
    $html = '<select class="time-slot-select" data-room-id="' . htmlspecialchars($room_id) . '">';
    foreach ($time_slots as $slot) {
        list($start_time, $end_time) = explode('-', $slot);
        $is_reserved = false;
        if (isset($reservations[$room_id])) {
            foreach ($reservations[$room_id] as $reservation) {
                if (strtotime($start_time) >= strtotime($reservation['start']) && strtotime($start_time) < strtotime($reservation['end'])) {
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
<div class="sidebar">
    <div class="logo">
        <img src="ENCG-BM_logo_header.png" width="200" alt="Logo">
    </div>
    <ul class="nav-links">
        <li><a href="#"><i class="icon-home"></i> Tableau de bord</a></li>
        <li><a href="#"><i class="icon-students"></i> Groupes</a></li>
        <li><a href="#"><i class="icon-teachers"></i> Professeurs</a></li>
        <li class="dropdown">
            <a href="salle.html"><i class="icon-attendance"></i> Salles</a>
            <ul class="dropdown-content">
                <li><a href="Aj_salle.php">Ajouter une salle</a></li>
                <li><a href="Maj_salle.php">Mettre à jour les salles</a></li>
            </ul>
        </li>
        <li><a href="salle.html"><i class="icon-attendance"></i> Réserve</a></li>
        <li><a href="#"><i class="icon-logout"></i> Déconnexion</a></li>
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
                        
                        $day_key = $day_of_week == 'vendredi' ? 'Vendredi' : ($day_of_week == 'samedi' ? 'Samedi' : 'Lundi-Jeudi');
                        
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
