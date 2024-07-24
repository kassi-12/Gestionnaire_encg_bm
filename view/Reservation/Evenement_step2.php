<?php
include '../db/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_name = isset($_POST['EventName']) ? $_POST['EventName'] : '';
    $number_of_people = isset($_POST['NumberOfPeople']) ? $_POST['NumberOfPeople'] : 0;
    $event_date = isset($_POST['EventDate']) ? $_POST['EventDate'] : '';
    $organizer = isset($_POST['Organizer']) ? $_POST['Organizer'] : '';

    // Utiliser IntlDateFormatter pour obtenir le jour de la semaine en français
    $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE);
    $formatter->setPattern('EEEE');
    $day_of_week = $formatter->format(strtotime($event_date)); 

    $capacity_column = 'capacity';
    $sql_rooms = "SELECT id, name, $capacity_column as capacity FROM salles 
                  WHERE $capacity_column >= $number_of_people";
    $result_rooms = $conn->query($sql_rooms);

    if (!$result_rooms) {
        die("Erreur lors de la récupération des salles : " . $conn->error);
    }

    $sql_reservations = "SELECT salle_id, start_time, end_time FROM reservation 
                         WHERE jour_par_semaine = '$day_of_week' 
                         AND salle_id IN (SELECT id FROM salles WHERE $capacity_column >= $number_of_people)";
    $result_reservations = $conn->query($sql_reservations);

    if (!$result_reservations) {
        die("Erreur lors de la récupération des réservations : " . $conn->error);
    }

    $reservations = [];
    while ($row = $result_reservations->fetch_assoc()) {
        $reservations[$row['salle_id']][] = ['start' => $row['start_time'], 'end' => $row['end_time']];
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
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deuxième Étape de Réservation</title>
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
            <h3>Deuxième Étape de Réservation</h3>
            <p class="required-fields">* Tous les champs sont obligatoires</p>
            
            <table class="table-spacing">
                <tr>
                    <th>Nom de la Salle</th>
                    <th>Heure disponible</th>
                </tr>
                <?php
                if (isset($result_rooms) && $result_rooms->num_rows > 0) {
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
            <form action="Evenement_step3.php" method="POST">
                <label for="selected_room">Sélectionnez la salle * :</label>
                <select name="selected_room" id="selected_room" class="select-spacing" required>
                    <?php
                    if (isset($result_rooms) && $result_rooms->num_rows > 0) {
                        $result_rooms->data_seek(0); 
                        while ($room = $result_rooms->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($room['id']) . '">' . htmlspecialchars($room['name']) . '</option>';
                        }
                    }
                    ?>
                </select>
                <br><br>
                <label for="selected_time">Sélectionnez l'heure * :</label>
                <select name="selected_time" id="selected_time" class="select-spacing" required>
                </select>
                <br><br>
                <input type="hidden" name="EventName" value="<?php echo htmlspecialchars($event_name); ?>">
                <input type="hidden" name="NumberOfPeople" value="<?php echo htmlspecialchars($number_of_people); ?>">
                <input type="hidden" name="EventDate" value="<?php echo htmlspecialchars($event_date); ?>">
                <input type="hidden" name="Organizer" value="<?php echo htmlspecialchars($organizer); ?>">

                <button type="submit">Suivant</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
