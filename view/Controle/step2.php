<?php
include '../db/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $niveau = $_POST['Niveau'] ?? '';
    $groupName = $_POST['GroupName'] ?? '';
    $semester = $_POST['Semester'] ?? '';
    $filieres = $_POST['Filiere'] ?? array();

    // Process the data
    echo "Niveau: " . htmlspecialchars($niveau) . "<br>";
    echo "GroupName: " . htmlspecialchars($groupName) . "<br>";
    echo "Semester: " . htmlspecialchars($semester) . "<br>";
    echo "Filiere/Section: " . implode(', ', array_map('htmlspecialchars', $filieres)) . "<br>";

    // Prepare to fetch reservations for each filiere
    $reservations = [];
    $group_id = null;
    foreach ($filieres as $filiere) {
        $filiere_safe = $conn->real_escape_string($filiere);
        $sql_group_id = "
            SELECT id 
            FROM grp 
            WHERE name = '$groupName' 
            AND year = '$niveau' 
            AND (extra_info = '$filiere_safe' OR filiere = '$filiere_safe')
        ";
        $result_group_id = $conn->query($sql_group_id);
        if ($result_group_id) {
            $group_row = $result_group_id->fetch_assoc();
            $group_id = $group_row['id'];
        }

        $sql_reservations = "
            SELECT r.id, 
                   CONCAT(r.jour_par_semaine, ' - ', r.start_time, ' to ', r.end_time) AS reservation_info,
                   r.jour_par_semaine,
                   s.subject_name,
                   CONCAT(p.first_name, ' ', p.last_name) AS professor_name
            FROM reservation r
            JOIN subjects s ON r.subject_id = s.subject_id
            JOIN professeur p ON r.professeur_id = p.id
            WHERE r.group_id = $group_id
        ";
        $result = $conn->query($sql_reservations);
        if ($result) {
            $reservations[$filiere] = $result->fetch_all(MYSQLI_ASSOC);
        }
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
            
            <form action="step3.php" method="POST">
    <label for="room-type">Type de séance * :</label>
    <select id="room-type" name="room-type" required>
        <option value="">-</option>
        <option value="controle">Contrôle</option>
        <option value="exam">Examen</option>
    </select><br><br>

    <?php
    foreach ($filieres as $filiere) {
        echo '<label for="reservation_' . htmlspecialchars($filiere) . '">' . htmlspecialchars($filiere) . ' :</label>';
        echo '<select id="reservation_' . htmlspecialchars($filiere) . '" name="reservation[]" required>';
        echo '<option value="">-</option>';
        if (isset($reservations[$filiere])) {
            foreach ($reservations[$filiere] as $reservation) {
                echo '<option value="' . $reservation['id'] . '">' . htmlspecialchars($reservation['reservation_info']) . ' | ' . htmlspecialchars($reservation['subject_name']) . ' | ' . htmlspecialchars($reservation['professor_name']) . '</option>';
            }
        }
        echo '</select><br><br>';
    }
    ?>

    <label for="date_debut">Date * :</label>
    <input type="date" id="date_debut" name="date_debut" required><br><br>

    <button type="submit">Suivant</button>

    <input type="hidden" name="Niveau" value="<?php echo htmlspecialchars($niveau); ?>">
    <input type="hidden" name="GroupName" value="<?php echo htmlspecialchars($groupName); ?>">
    <input type="hidden" name="Semester" value="<?php echo htmlspecialchars($semester); ?>">
    <?php
    foreach ($filieres as $filiere) {
        echo '<input type="hidden" name="Filiere[]" value="' . htmlspecialchars($filiere) . '">';
    }
    ?>
</form>

        </div>
    </div>
</div>
</body>
</html>

