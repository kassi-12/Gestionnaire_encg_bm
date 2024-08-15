<?php
include '../db/db_connect.php';

// Retrieve form data
$niveau = $_POST['Niveau'] ?? '';
$semester = $_POST['Semester'] ?? '';



// Check if the connection to the database is successful
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Fetch reservations based on the provided form data
$sql_reservation = "SELECT jour_par_semaine, start_time, end_time, group_id, subject_id, professeur_id, salle_id, type_seance
                    FROM reservation 
                    WHERE professeur_id = ? AND semester_id = ?";
$stmt_reservation = $conn->prepare($sql_reservation);
if (!$stmt_reservation) {
    die("Erreur de préparation de la requête : " . $conn->error);
}
$stmt_reservation->bind_param('ii', $niveau, $semester);
$stmt_reservation->execute();
$result_reservation = $stmt_reservation->get_result();

// Debug: Check if the query returned results
if (!$result_reservation) {
    die("Erreur lors de la récupération des données de réservation : " . $conn->error);
}


// Check if we have any results
if ($result_reservation->num_rows == 0) {
    die("Aucune réservation trouvée pour les critères donnés.");
}

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
    $schedule[$day][$time_slot][] = [
        'group_id' => $row['group_id'],
        'subject_id' => $row['subject_id'],
        'professeur_id' => $row['professeur_id'],
        'salle_id' => $row['salle_id'],
        'type_seance' => $row['type_seance']
    ];

    // Accumulate IDs for subsequent queries
    $group_ids[] = $row['group_id'];
    $subject_ids[] = $row['subject_id'];
    $professor_ids[] = $row['professeur_id'];
    $salle_ids[] = $row['salle_id'];
}

// Retrieve reservation data and necessary IDs
while ($row = $result_reservation->fetch_assoc()) {
    $day = strtolower($row['jour_par_semaine']);
    $time_slot = $row['start_time'] . ' - ' . $row['end_time'];
    $salle_id = $row['salle_id'];
    $reservation_id = $row['id']; // Ensure reservation_id is set correctly
    $type_seance = $row['type_seance'];

    // Check for status in `rapport`
    $sql_rapport = "SELECT statut FROM rapport WHERE reservation_id = $reservation_id AND rapport_date BETWEEN '$date_from' AND '$date_to'";
    $result_rapport = $conn->query($sql_rapport);
    if ($result_rapport->num_rows > 0) {
        $type_seance = 'Annulé';
    }

    // Check for status in `rattrapage`
    $sql_rattrapage = "SELECT rattrapage_date, start_time, end_time, salle_id 
                       FROM rattrapage 
                       WHERE reservation_id = $reservation_id 
                       AND DATE(rattrapage_date) BETWEEN DATE('$date_from') AND DATE('$date_to')";
    $result_rattrapage = $conn->query($sql_rattrapage);

    if ($result_rattrapage->num_rows > 0) {
        $row_rattrapage = $result_rattrapage->fetch_assoc();
        $type_seance = 'Rattrapage';  // Set type_seance to 'Rattrapage'
        $time_slot = $row_rattrapage['start_time'] . ' - ' . $row_rattrapage['end_time'];
        $salle_id = $row_rattrapage['salle_id'];  // Update salle_id based on rattrapage if needed

        // Update the day based on the rattrapage_date
        $day_of_week_english = strtolower(date('l', strtotime($row_rattrapage['rattrapage_date'])));
        $day = $english_to_french_days[$day_of_week_english];
    }

    // Store the schedule
    $schedule[$day][$salle_id][$time_slot][] = [
        'group_id' => $row['group_id'],
        'subject_id' => $row['subject_id'],
        'professeur_id' => $row['professeur_id'],
        'type_seance' => $type_seance  // This will now be 'Rattrapage' if applicable
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

// Retrieve room names
if (!empty($salle_ids)) {
    $sql_salle = "SELECT id, name FROM salles WHERE id IN (" . implode(',', array_fill(0, count($salle_ids), '?')) . ")";
    $stmt_salle = $conn->prepare($sql_salle);
    if (!$stmt_salle) {
        die("Erreur de préparation de la requête : " . $conn->error);
    }
    $stmt_salle->bind_param(str_repeat('i', count($salle_ids)), ...$salle_ids);
    $stmt_salle->execute();
    $result_salle = $stmt_salle->get_result();
    while ($row = $result_salle->fetch_assoc()) {
        $salles[$row['id']] = $row['name'];
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
        <img src='ENCG-BM_logo_header.png' width='200' alt='Logo'>
    </div>
    <ul class='nav-links'>
        <li><a href='#'><i class='icon-home'></i> Tableau de bord</a></li>
        <li><a href='#'><i class='icon-students'></i> Groupes</a></li>
        <li><a href='#'><i class='icon-teachers'></i> Professeurs</a></li>
        <li class='dropdown'>
            <a href='#'><i class='icon-attendance'></i> Salles</a>
            <ul class='dropdown-content'>
                <li><a href='Aj_salle.php'>Ajouter une salle</a></li>
                <li><a href='Maj_salle.php'>Mettre à jour les salles</a></li>
            </ul>
        </li>
        <li><a href='salle.html'><i class='icon-attendance'></i> Réservations</a></li>
        <li><a href='#'><i class='icon-logout'></i> Déconnexion</a></li>
    </ul>
</div>
<div class='main-content'>
    <h2>Planning pour le semestre</h2>
    <button onclick='saveAsPDF()'>Save as PDF</button>
    <div id='schedule-content'>
        <table class='attendance'>
            <thead>
                <tr>
                    <th class='time-slot'></th>
                    <th>9H:00 - 10H:30</th>
                    <th>10H:45 - 12H:15</th>
                    <th>14H:00 - 15H:30</th>
                    <th>15H:45 - 17H:15</th>
                </tr>
            </thead>
            <tbody>";

$days = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
foreach ($days as $day) {
    echo "<tr><th>$day</th>";
    $time_slots = ['09:00:00 - 10:30:00', '10:45:00 - 12:15:00', '14:00:00 - 15:30:00', '15:45:00 - 17:15:00'];
    foreach ($time_slots as $time_slot) {
        echo "<td>";
        if (isset($schedule[$day][$time_slot])) {
            foreach ($schedule[$day][$time_slot] as $course) {
                $group_info = isset($groups[$course['group_id']]) ? $groups[$course['group_id']] : ['name' => 'Inconnu', 'filiere' => 'Inconnu', 'extra_info' => 'Inconnu'];
                $subject_name = isset($subjects[$course['subject_id']]) ? $subjects[$course['subject_id']] : 'Inconnu';
                $professor_name = isset($professors[$course['professeur_id']]) ? $professors[$course['professeur_id']] : 'Inconnu';
                $salle_name = isset($salles[$course['salle_id']]) ? $salles[$course['salle_id']] : 'Inconnu';
                $type_seance = isset($course['type_seance']) ? $course['type_seance'] : 'Inconnu';
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
                        <span>$group_display</span><br><hr>
                        <span>$subject_name ". " - " . " $type_seance </span><br><hr>
                        <span>$professor_name</span><br><hr>
                        <span>$salle_name</span><br><hr>
                        
                      </div>";
            }
        }
        echo "</td>";
    }
    echo "</tr>";
}

echo "</tbody>
    </table>
</div>
</div>
<script src='https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js'></script>
<script>
function saveAsPDF() {
    const element = document.getElementById('schedule-content');
    const opt = {
        margin:[150, 0.5, 0.5, 0.5],
        filename: 'schedule.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 3, scrollX: 0, scrollY: 0 },  // Increased scale for better quality
        jsPDF: { unit: 'px', format: [1980, 1020], orientation: 'landscape' },  // Increased dimensions
        pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
    };

    const img = new Image();
    img.src = '../../image/ENCG-BM_logo_header.png';
    img.onload = function () {
        html2pdf().from(element).set(opt).toPdf().get('pdf').then(function (pdf) {
            pdf.addImage(img, 'PNG', 30, 30, 350, 100);

            const pageWidth = pdf.internal.pageSize.getWidth();
            const lines = [
                'Université Sultan Moulay Slimane',
                'École Nationale de Commerce et de Gestion',
                'Beni Mellal'
            ];

            pdf.setFontSize(18);
            pdf.setFont('helvetica', 'bold');

            // Calculate the initial y position for the first line of text
            let yPosition = 50;
            lines.forEach((line) => {
                pdf.text(line, pageWidth / 2, yPosition, { align: 'center' });
                yPosition += 25; // Adjust the spacing between lines
            });

            pdf.save(opt.filename);
        });
    };
}
</script>
</body>
</html>";
?>
