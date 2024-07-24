<?php
include '../db/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve data from the form submission
    $reservation_id = isset($_POST['reservation_id']) ? $_POST['reservation_id'] : '';
    $reservation_date = isset($_POST['reservation_date']) ? $_POST['reservation_date'] : '';
    
    // Prepare statements to prevent SQL Injection
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

    // Use prepared statements for each related query
    $stmt_group = $conn->prepare("SELECT * FROM grp WHERE id = ?");
    $stmt_group->bind_param("s", $group_id);
    $stmt_group->execute();
    $group = $stmt_group->get_result()->fetch_assoc();

    $stmt_salle = $conn->prepare("SELECT * FROM salles WHERE id = ?");
    $stmt_salle->bind_param("s", $salle_id);
    $stmt_salle->execute();
    $salle = $stmt_salle->get_result()->fetch_assoc();

    $stmt_professeur = $conn->prepare("SELECT * FROM professeur WHERE id = ?");
    $stmt_professeur->bind_param("s", $professeur_id);
    $stmt_professeur->execute();
    $professeur = $stmt_professeur->get_result()->fetch_assoc();

    $stmt_semester = $conn->prepare("SELECT * FROM semesters WHERE id = ?");
    $stmt_semester->bind_param("s", $semester_id);
    $stmt_semester->execute();
    $semester = $stmt_semester->get_result()->fetch_assoc();

    $stmt_subject = $conn->prepare("SELECT * FROM subjects WHERE subject_id = ?");
    $stmt_subject->bind_param("s", $subject_id);
    $stmt_subject->execute();
    $subject = $stmt_subject->get_result()->fetch_assoc();
} else {
    // Handle the case when the form is accessed directly (GET request)
    header('Location: ../Reservation/reserve.php'); // Redirect to the form page
    exit();
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
            <h3>Confirmation de Réservation</h3>
            <p class="required-fields">Veuillez vérifier les détails de votre réservation ci-dessous :</p>
            
            <div class="confirmation-item">
                <span class="label">Niveau:</span>
                <span class="value"><input type="text" value="<?php echo htmlspecialchars($group['year']); ?>" readonly></span>
            </div>
            <div class="confirmation-item">
                <span class="label">Nom du Groupe:</span>
                <span class="value"><input type="text" value="<?php echo htmlspecialchars($group['name']); ?>" readonly></span>
            </div>
            <div class="confirmation-item">
                <span class="label">Filière:</span>
                <span class="value"><input type="text" value="<?php echo htmlspecialchars($group['filiere']); ?>" readonly></span>
            </div>
            <div class="confirmation-item">
                <span class="label">Semester:</span>
                <span class="value"><input type="text" value="<?php echo htmlspecialchars($semester['name']); ?>" readonly></span>
            </div>
            <div class="confirmation-item">
                <span class="label">Matière:</span>
                <span class="value"><input type="text" value="<?php echo htmlspecialchars($subject['subject_name']); ?>" readonly></span>
            </div>
            <div class="confirmation-item">
                <span class="label">Professeur:</span>
                <span class="value"><input type="text" value="<?php echo htmlspecialchars($professeur['first_name'] . ' ' . $professeur['last_name']); ?>" readonly></span>
            </div>
            <div class="confirmation-item">
                <span class="label">Type de Séance:</span>
                <span class="value"><input type="text" value="<?php echo htmlspecialchars($reservation['type_seance']); ?>" readonly></span>
            </div>
            <div class="confirmation-item">
                <span class="label">Date Sélectionnée:</span>
                <span class="value"><input type="text" value="<?php echo htmlspecialchars($reservation['reservation_date']); ?>" readonly></span>
            </div>
            <div class="confirmation-item">
                <span class="label">Salle :</span>
                <span class="value"><input type="text" value="<?php echo htmlspecialchars($salle['name']); ?>" readonly></span>
            </div>
            <div class="confirmation-item">
                <span class="label">Heure :</span>
                <span class="value"><input type="text" value="<?php echo htmlspecialchars($reservation['start_time'] . ' - ' . $reservation['end_time']); ?>" readonly></span>
            </div>
            <form action="confirm_rapport.php" method="POST">
            <div class="confirmation-item">
                <span class="label">Motif:</span>
                <span class="value"><input type="text" name="motif" required></span>
            </div>
            <br>
            
                <input type="hidden" name="reservation_id" value="<?php echo htmlspecialchars($reservation_id); ?>">
                <input type="hidden" name="reservation_date" value="<?php echo htmlspecialchars($reservation_date); ?>">
            

                <div class="button-container">
                    <button type="button" onclick="location.href='evenement.php';">Annuler</button>
                    <button type="submit">Confirmer</button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
