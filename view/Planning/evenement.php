<?php
include '../db/db_connect.php';

// Initialize the salles array
$salles = [];

// Fetch the salle information from the database
$sql_salle = "SELECT id, name, capacity FROM salles";
$result_salle = $conn->query($sql_salle);

if ($result_salle && $result_salle->num_rows > 0) {
    while ($row = $result_salle->fetch_assoc()) {
        $salles[$row['id']] = [
            'name' => $row['name'],
            'capacity' => $row['capacity']
        ];
    }
} else {
    echo "Aucune salle trouvée.";
}

// Fetch event data
$sql_evenement = "SELECT id, event_name, salle_id, event_date, start_time, end_time, organizer FROM evenement";
$result_evenement = $conn->query($sql_evenement);

// Initialize the schedule array
$schedule = [];

// Process event data
while ($row = $result_evenement->fetch_assoc()) {
    $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE);
    $formatter->setPattern('EEEE');
    $day_of_week = strtolower($formatter->format(new DateTime($row['event_date'])));
    $time_slot = $row['start_time'] . ' - ' . $row['end_time'];
    $salle_id = $row['salle_id'];

    $schedule[$day_of_week][$salle_id][$time_slot][] = [
        'event_name' => $row['event_name'],
        'organizer' => $row['organizer']
    ];
}


$conn->close();

echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Planning Événements</title>
    <link rel='stylesheet' href='../../assets/styles.css'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css'>
    <style>
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
        justify-content: center;
        align-items: center;
    }
    th br {
        display: block;
        margin-bottom: 5px;
        text-align: center;
        vertical-align: middle;
    }
    .day {
        font-weight: bold;
        background-color: #f4f4f4;
    }
    .event {
        margin-bottom: 10px;
        padding: 5px;
        border-radius: 4px;
        color: #fff;
        text-align: center;
        background-color: #007bff;
    }
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
    <h2>Planning des Événements</h2>
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
                foreach ($schedule[$day][$salle_id][$slot_key] as $event) {
                    echo "<div class='event'>
                            <span>{$event['event_name']}</span><br>
                            <hr>
                            <span>Organisé par: {$event['organizer']}</span>
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
<script src='https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js'></script>
<script>
function saveAsPDF() {
    const element = document.getElementById('schedule-content');
    const opt = {
        margin:[150, 0.5, 0.5, 0.5],
        filename: 'schedule.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 3, scrollX: 0, scrollY: 0 },  // Increased scale for better quality
        jsPDF: { unit: 'px', format: [1980, 1200], orientation: 'landscape' },  // Increased dimensions
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
