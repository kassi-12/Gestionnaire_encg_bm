<?php
include '../db/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve data from the form submission
    $reservation_id = $_POST['reservation_id'] ?? '';
    $date = $_POST['date_debut'] ?? '';
    $selected_room = $_POST['selected_room'] ?? '';
    $selected_time = $_POST['selected_time'] ?? '';

    // Get reservation details
    $stmt = $conn->prepare("SELECT * FROM reservation WHERE id = ?");
    $stmt->bind_param("s", $reservation_id);
    $stmt->execute();
    $result_reservation = $stmt->get_result();
    
    if ($result_reservation->num_rows === 0) {
        die("Aucune réservation trouvée pour l'ID fourni.");
    }
    
    $reservation = $result_reservation->fetch_assoc();
    
    $group_id = $reservation['group_id'];
    $salle_id = $reservation['salle_id'];
    $professeur_id = $reservation['professeur_id'];
    $semester_id = $reservation['semester_id'];
    $subject_id = $reservation['subject_id'];
    $room_type = $reservation['type_seance'];

    // Fetch group details
    $sql_group = "SELECT name, year, filiere FROM grp WHERE id = ?";
    $stmt_group = $conn->prepare($sql_group);
    $stmt_group->bind_param("s", $group_id);
    $stmt_group->execute();
    $result_group = $stmt_group->get_result();
    if (!$result_group) {
        die("Erreur lors de la récupération des détails du groupe : " . $conn->error);
    }
    $group = $result_group->fetch_assoc();
    $group_name = $group['name'];
    $niveau = $group['year'];
    $filiere = $group['filiere'];

    // Fetch room details
    $sql_room = "SELECT name FROM salles WHERE id = ?";
    $stmt_room = $conn->prepare($sql_room);
    $stmt_room->bind_param("s", $selected_room);
    $stmt_room->execute();
    $result_room = $stmt_room->get_result();
    if (!$result_room) {
        die("Erreur lors de la récupération des détails de la salle : " . $conn->error);
    }
    $room = $result_room->fetch_assoc();
    $room_name = $room['name'];

    // Fetch professor details
    $sql_prof = "SELECT first_name, last_name FROM professeur WHERE id = ?";
    $stmt_prof = $conn->prepare($sql_prof);
    $stmt_prof->bind_param("s", $professeur_id);
    $stmt_prof->execute();
    $result_prof = $stmt_prof->get_result();
    if (!$result_prof) {
        die("Erreur lors de la récupération des détails du professeur : " . $conn->error);
    }
    $prof = $result_prof->fetch_assoc();
    $prof_name = $prof['first_name'] . ' ' . $prof['last_name'];

    // Fetch semester details
    $sql_semester = "SELECT name FROM semesters WHERE id = ?";
    $stmt_semester = $conn->prepare($sql_semester);
    $stmt_semester->bind_param("s", $semester_id);
    $stmt_semester->execute();
    $result_semester = $stmt_semester->get_result();
    if (!$result_semester) {
        die("Erreur lors de la récupération des détails du semestre : " . $conn->error);
    }
    $semester = $result_semester->fetch_assoc()['name'];

    // Fetch subject details
    $sql_subject = "SELECT subject_name FROM subjects WHERE subject_id = ?";
    $stmt_subject = $conn->prepare($sql_subject);
    $stmt_subject->bind_param("s", $subject_id);
    $stmt_subject->execute();
    $result_subject = $stmt_subject->get_result();
    if (!$result_subject) {
        die("Erreur lors de la récupération des détails de la matière : " . $conn->error);
    }
    $subject = $result_subject->fetch_assoc()['subject_name'];

} else {
    // Handle the case when the form is accessed directly (GET request)
    header('Location: step1.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de Réservation</title>
    <link rel="stylesheet" href="../../assets/styles.css">
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
                <span class="value"><input type="text" value="<?php echo htmlspecialchars($niveau); ?>" readonly></span>
            </div>
            <div class="confirmation-item">
                <span class="label">Nom du Groupe:</span>
                <span class="value"><input type="text" value="<?php echo htmlspecialchars($group_name); ?>" readonly></span>
            </div>
            <div class="confirmation-item">
                <span class="label">Filère:</span>
                <span class="value"><input type="text" value="<?php echo htmlspecialchars($filiere); ?>" readonly></span>
            </div>
            <div class="confirmation-item">
                <span class="label">Semestre:</span>
                <span class="value"><input type="text" value="<?php echo htmlspecialchars($semester); ?>" readonly></span>
            </div>
            <div class="confirmation-item">
                <span class="label">Type de Salle:</span>
                <span class="value"><input type="text" value="<?php echo htmlspecialchars($room_type); ?>" readonly></span>
            </div>
            <div class="confirmation-item">
                <span class="label">Matière:</span>
                <span class="value"><input type="text" value="<?php echo htmlspecialchars($subject); ?>" readonly></span>
            </div>
            <div class="confirmation-item">
                <span class="label">Professeur:</span>
                <span class="value"><input type="text" value="<?php echo htmlspecialchars($prof_name); ?>" readonly></span>
            </div>
            <div class="confirmation-item">
                <span class="label">Type de Séance:</span>
                <span class="value"><input type="text" value="<?php echo htmlspecialchars($room_type); ?>" readonly></span>
            </div>
            <div class="confirmation-item">
                <span class="label">Date Sélectionnée:</span>
                <span class="value"><input type="text" value="<?php echo htmlspecialchars($date); ?>" readonly></span>
            </div>
            <div class="confirmation-item">
                <span class="label">Salle Sélectionnée:</span>
                <span class="value"><input type="text" value="<?php echo htmlspecialchars($room_name); ?>" readonly></span>
            </div>
            <div class="confirmation-item">
                <span class="label">Heure Sélectionnée:</span>
                <span class="value"><input type="text" value="<?php echo htmlspecialchars($selected_time); ?>" readonly></span>
            </div>
            
            <br>
            <form action="confirm_reservation.php" method="POST">
            <input type="hidden" name="reservation_id" value="<?php echo htmlspecialchars($reservation_id); ?>">
            <input type="hidden" name="salle_id" value="<?php echo htmlspecialchars($selected_room); ?>">
            <input type="hidden" name="selected_time" value="<?php echo htmlspecialchars($selected_time); ?>">
            <input type="hidden" name="date_debut" value="<?php echo htmlspecialchars($date); ?>">
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
