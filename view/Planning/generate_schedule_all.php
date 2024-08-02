<?php
include '../db/db_connect.php';

// Retrieve semester and room types from form submission
$semester = $_POST['Semester'] ?? '';
$roomTypes = $_POST['room-type'] ?? [];

// Escape the room types to prevent SQL injection
$roomTypesStr = implode("','", array_map([$conn, 'real_escape_string'], $roomTypes));

// Check if the "Tous les Semestres" option is selected
if ($semester === 'all') {
    // Query reservations for all semesters
    $sql_reservation = "SELECT jour_par_semaine, start_time, end_time, group_id, subject_id, professeur_id, salle_id, type_seance
                        FROM reservation WHERE type_seance IN ('$roomTypesStr')";
} else {
    // Query reservations for the selected semester
    $sql_reservation = "SELECT jour_par_semaine, start_time, end_time, group_id, subject_id, professeur_id, salle_id, type_seance
                        FROM reservation WHERE semester_id = '$semester' AND type_seance IN ('$roomTypesStr')";
}

$result_reservation = $conn->query($sql_reservation);

// Initialize arrays to store data
$schedule = [];
$group_ids = [];
$subject_ids = [];
$professor_ids = [];
$salle_ids = [];

// Retrieve reservation data and necessary IDs
while ($row = $result_reservation->fetch_assoc()) {
    $day = strtolower($row['jour_par_semaine']);
    $time_slot = $row['start_time'] . ' - ' . $row['end_time'];
    $salle_id = $row['salle_id'];
    $schedule[$day][$salle_id][$time_slot][] = [
        'group_id' => $row['group_id'],
        'subject_id' => $row['subject_id'],
        'professeur_id' => $row['professeur_id'],
        'type_seance' => $row['type_seance']
    ];

    // Accumulate IDs for subsequent queries
    $group_ids[] = $row['group_id'];
    $subject_ids[] = $row['subject_id'];
    $professor_ids[] = $row['professeur_id'];
    $salle_ids[] = $row['salle_id'];
}

// Eliminate duplicate IDs
$group_ids = array_unique($group_ids);
$subject_ids = array_unique($subject_ids);
$professor_ids = array_unique($professor_ids);
$salle_ids = array_unique($salle_ids);

// Prepare arrays to store detailed information
$groups = [];
$subjects = [];
$professors = [];
$salles = [];

// Retrieve group names
if (!empty($group_ids)) {
    $sql_group = "SELECT id, name, year, filiere, extra_info FROM grp WHERE id IN (" . implode(',', array_fill(0, count($group_ids), '?')) . ")";
    $stmt_group = $conn->prepare($sql_group);
    if (!$stmt_group) {
        die("Erreur de préparation de la requête : " . $conn->error);
    }
    $stmt_group->bind_param(str_repeat('i', count($group_ids)), ...$group_ids);
    $stmt_group->execute();
    $result_group = $stmt_group->get_result();
    while ($row = $result_group->fetch_assoc()) {
        $groups[$row['id']] = [
            'name' => $row['name'],
            'year' => $row['year'],
            'filiere' => $row['filiere'],
            'extra_info' => $row['extra_info']
        ];
    }
}

// Retrieve subject names
if (!empty($subject_ids)) {
    $sql_subject = "SELECT subject_id, subject_name FROM subjects WHERE subject_id IN (" . implode(',', array_fill(0, count($subject_ids), '?')) . ")";
    $stmt_subject = $conn->prepare($sql_subject);
    if (!$stmt_subject) {
        die("Erreur de préparation de la requête : " . $conn->error);
    }
    $stmt_subject->bind_param(str_repeat('i', count($subject_ids)), ...$subject_ids);
    $stmt_subject->execute();
    $result_subject = $stmt_subject->get_result();
    while ($row = $result_subject->fetch_assoc()) {
        $subjects[$row['subject_id']] = $row['subject_name'];
    }
}

// Retrieve professor names
if (!empty($professor_ids)) {
    $sql_professor = "SELECT id, first_name, last_name FROM professeur WHERE id IN (" . implode(',', array_fill(0, count($professor_ids), '?')) . ")";
    $stmt_professor = $conn->prepare($sql_professor);
    if (!$stmt_professor) {
        die("Erreur de préparation de la requête : " . $conn->error);
    }
    $stmt_professor->bind_param(str_repeat('i', count($professor_ids)), ...$professor_ids);
    $stmt_professor->execute();
    $result_professor = $stmt_professor->get_result();
    while ($row = $result_professor->fetch_assoc()) {
        $professors[$row['id']] = $row['first_name'] . ' ' . $row['last_name'];
    }
}

// Retrieve room names and capacities
if (!empty($salle_ids)) {
    $sql_salle = "SELECT id, name, capacity FROM salles WHERE id IN (" . implode(',', array_fill(0, count($salle_ids), '?')) . ")";
    $stmt_salle = $conn->prepare($sql_salle);
    if (!$stmt_salle) {
        die("Erreur de préparation de la requête : " . $conn->error);
    }
    $stmt_salle->bind_param(str_repeat('i', count($salle_ids)), ...$salle_ids);
    $stmt_salle->execute();
    $result_salle = $stmt_salle->get_result();
    while ($row = $result_salle->fetch_assoc()) {
        $salles[$row['id']] = [
            'name' => $row['name'],
            'capacity' => $row['capacity']
        ];
    }
}

$conn->close();

// Generate HTML for the schedule
echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Planning</title>
    <link rel='stylesheet' href='../../assets/styles.css'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css'>
    <style>
    .main-content {
    margin-top: 20px;
}
table {
    .main-content {
    margin-top: 20px;
}
table {
    width: 100%;
    border-collapse: collapse;
    text-align: center;
    margin-bottom: 20px;
    font-size: 12px;
}
th, td {
    border: 1px solid #000;
    padding: 5px;
    font-size: 12px;
}
th {
    background-color: #f0f0f0;
    font-weight: bold;
  
    justify-content: center; /* Center horizontally */
    align-items: center; /* Center vertically */
   
}
th br {
    display: block;
    margin-bottom: 5px;
    text-align: center; /* Center text horizontally */
    vertical-align: middle; /* Center text vertically */
}
.day {
    font-weight: bold;
    background-color: #f4f4f4;
}
.course {
    margin-bottom: 10px;
    padding: 5px;
    border-radius: 4px;
    color: #fff;
    text-align: center;
}
.year-1 { background-color: green; }
.year-2 { background-color: yellow; color: #000; }
.year-3 { background-color: blue; }
.year-4 { background-color: red; }
.year-5 { background-color: pink; }
    </style>
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
<div class='main-content'>
    <h2>Planning pour le semestre</h2>
    <button onclick='saveAsPDF()'>Save as PDF</button>
    <div id='schedule-content'>
        <table class='attendance'>
            <thead>
                <tr>
                    <th class='day'>Jour</th>
                    <th>Créneau</th>";

                    foreach ($salles as $salle_info) {
                        echo "<th>{$salle_info['name']}<br><br><hr><br>{$salle_info['capacity']}</th>";
                    }

echo "</tr>
    </thead>
    <tbody>";

$days = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
$time_slots = [
    '09:00:00 - 10:30:00' => '09H:00-10H:30',
    '10:45:00 - 12:15:00' => '10H:45-12H:15',
    '14:00:00 - 15:30:00' => '14H:00-15H:30',
    '15:45:00 - 17:15:00' => '15H:45-17H:15'
];

foreach ($days as $day) {
    $first_time_slot = true;
    foreach ($time_slots as $slot_key => $slot_label) {
        if ($day === 'samedi' && in_array($slot_key, ['14:00:00 - 15:30:00', '15:45:00 - 17:15:00'])) {
            continue;
        }
        echo "<tr>";
        if ($first_time_slot) {
            echo "<td class='day' rowspan='4'>$day</td>";
            $first_time_slot = false;
        }
        echo "<td class='day'>$slot_label</td>";

        foreach ($salles as $salle_id => $salle_info) {
            echo "<td>";
            if (isset($schedule[$day][$salle_id][$slot_key])) {
                foreach ($schedule[$day][$salle_id][$slot_key] as $course) {
                    $group_info = isset($groups[$course['group_id']]) ? $groups[$course['group_id']] : ['name' => 'Inconnu', 'filiere' => 'Inconnu', 'extra_info' => 'Inconnu'];
                    $subject_name = isset($subjects[$course['subject_id']]) ? $subjects[$course['subject_id']] : 'Inconnu';
                    $professor_name = isset($professors[$course['professeur_id']]) ? $professors[$course['professeur_id']] : 'Inconnu';
                    $type_seance = isset($course['type_seance']) ? $course['type_seance'] : 'Inconnu';

                    // Determine CSS class based on the year
                    $year_class = '';
                    switch ($group_info['year']) {
                        case '1er':
                            $year_class = 'year-1';
                            break;
                        case '2ème':
                            $year_class = 'year-2';
                            break;
                        case '3ème':
                            $year_class = 'year-3';
                            break;
                        case '4ème':
                            $year_class = 'year-4';
                            break;
                        case '5ème':
                            $year_class = 'year-5';
                            break;
                    }

                    // Display information based on the year
                    if (in_array($group_info['year'], ['1er', '2ème', '3ème'])) {
                        $group_display = $group_info['name'] . ($group_info['extra_info'] ? " ({$group_info['extra_info']})" : "");
                    } elseif (in_array($group_info['year'], ['4ème', '5ème'])) {
                        $group_display = $group_info['name'] . ($group_info['filiere'] ? " ({$group_info['filiere']})" : "");
                    } else {
                        $group_display = $group_info['name'];
                    }

                    echo "<div class='course $year_class'>
                            <span>$group_display</span><br>
                            <hr>
                            <span>$subject_name - $type_seance</span><br>
                            <hr>
                            <span>$professor_name</span><br>
                            <span></span>
                          </div>";
                }
            }
            echo "</td>";
        }

        echo "</tr>";
    }
}

echo "</tbody>
    </table>
</div>
</div>
<script src='https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.2/jspdf.min.js'></script>
<script>
function saveAsPDF() {
    html2canvas(document.getElementById('schedule-content'), {
        onrendered: function(canvas) {
            var imgData = canvas.toDataURL('image/jpeg');
            var doc = new jsPDF('landscape');
            doc.addImage(imgData, 'jpeg', 20, 20, 230, 160);
            doc.save('schedule.pdf');
        }
    });
}
</script>
</body>
</html>";
