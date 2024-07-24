<?php
include '../db/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve data from the form submission
    $reservation_id = isset($_POST['reservation_id']) ? $_POST['reservation_id'] : '';
    
    // Prepare statement to fetch reservation details
    $stmt = $conn->prepare("SELECT * FROM reservation WHERE id = ?");
    $stmt->bind_param("s", $reservation_id);
    $stmt->execute();
    $result_reservation = $stmt->get_result();
    
    if ($result_reservation->num_rows === 0) {
        die("Aucune réservation trouvée pour l'ID fourni.");
    }
    
    $reservation = $result_reservation->fetch_assoc();
    
    // Fetch related details
    $group_id = $reservation['group_id'];
    $salle_id = $reservation['salle_id'];
    $professeur_id = $reservation['professeur_id'];
    $semester_id = $reservation['semester_id'];
    $subject_id = $reservation['subject_id'];

    // Function to fetch related data with dynamic column name for primary key
    function fetch_data($conn, $table, $primaryKey, $id) {
        $stmt = $conn->prepare("SELECT * FROM $table WHERE $primaryKey = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Fetch details using the function with the correct primary key column names
    $group = fetch_data($conn, 'grp', 'id', $group_id);
    $salle = fetch_data($conn, 'salles', 'id', $salle_id);
    $professeur = fetch_data($conn, 'professeur', 'id', $professeur_id);
    $semester = fetch_data($conn, 'semesters', 'id', $semester_id);
    $subject = fetch_data($conn, 'subjects', 'subject_id', $subject_id);
} 
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la Réservation</title>
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
            <h3>Details De Seance</h3>
            <p class="required-fields">Veuillez vérifier les détails de votre réservation ci-dessous :</p>
            
            <?php
            // Function to create a confirmation item
            function create_confirmation_item($label, $value) {
                echo '<div class="confirmation-item">';
                echo '<span class="label">' . htmlspecialchars($label) . ':</span>';
                echo '<span class="value"><input type="text" value="' . htmlspecialchars($value) . '" readonly></span>';
                echo '</div>';
            }

            // Display the confirmation items
            create_confirmation_item('Niveau', $group['year']);
            create_confirmation_item('Nom du Groupe', $group['name']);
            create_confirmation_item('Filière', $group['filiere']);
            create_confirmation_item('Semester', $semester['name']);
            create_confirmation_item('Matière', $subject['subject_name']);
            create_confirmation_item('Professeur', $professeur['first_name'] . ' ' . $professeur['last_name']);
            create_confirmation_item('Type de Séance', $reservation['type_seance']);
          
            ?>
            
            <form action="rattrapage_step2.php" method="POST">
                <div class="confirmation-item">
                <label for="date_debut">Date De Rattrapage :</label>
                <input type="date" id="date_debut" name="date_debut" required><br><br>
                </div>
                <br>
                <input type="hidden" name="reservation_id" value="<?php echo htmlspecialchars($reservation_id); ?>">
                <div class="button-container">
                    <button type="submit">Suivant</button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
