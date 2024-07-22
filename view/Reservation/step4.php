<?php
include '../db/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve data from the form submission
    $niveau = isset($_POST['Niveau']) ? $_POST['Niveau'] : '';
    $group_name = isset($_POST['GroupName']) ? $_POST['GroupName'] : '';
    $filiere = isset($_POST['Filiere']) ? $_POST['Filiere'] : '';
    $semester = isset($_POST['Semester']) ? $_POST['Semester'] : '';
    $room_type = isset($_POST['room-type']) ? $_POST['room-type'] : '';
    $subject_id = isset($_POST['Matier']) ? $_POST['Matier'] : '';
    $prof_id = isset($_POST['Prof']) ? $_POST['Prof'] : '';
    $date = isset($_POST['date_debut']) ? $_POST['date_debut'] : '';
    $selected_room = isset($_POST['selected_room']) ? $_POST['selected_room'] : '';
    $selected_time = isset($_POST['selected_time']) ? $_POST['selected_time'] : '';

    // Fetch room details
    $sql_room = "SELECT name FROM salles WHERE id = '$selected_room'";
    $result_room = $conn->query($sql_room);
    if (!$result_room) {
        die("Erreur lors de la récupération des détails de la salle : " . $conn->error);
    }
    $room = $result_room->fetch_assoc();
    $room_name = $room['name'];

    // Fetch professor details
    $sql_prof = "SELECT first_name, last_name FROM professeur WHERE id = '$prof_id'";
    $result_prof = $conn->query($sql_prof);
    if (!$result_prof) {
        die("Erreur lors de la récupération des détails du professeur : " . $conn->error);
    }
    $prof = $result_prof->fetch_assoc();
    $prof_name = $prof['first_name'] . ' ' . $prof['last_name'];

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
                <span class="label">Semester:</span>
                <span class="value"><input type="text" value="<?php echo htmlspecialchars($semester); ?>" readonly></span>
            </div>
            <div class="confirmation-item">
                <span class="label">Type de Salle:</span>
                <span class="value"><input type="text" value="<?php echo htmlspecialchars($room_type); ?>" readonly></span>
            </div>
            <div class="confirmation-item">
                <span class="label">Matière:</span>
                <span class="value"><input type="text" value="<?php echo htmlspecialchars($subject_id); ?>" readonly></span>
            </div>
            <div class="confirmation-item">
                <span class="label">Professeur:</span>
                <span class="value"><input type="text" value="<?php echo htmlspecialchars($prof_name); ?>" readonly></span>
            </div>
            <div class="confirmation-item">
                <span class="label">Type de Seance:</span>
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
                <input type="hidden" name="Niveau" value="<?php echo htmlspecialchars($niveau); ?>">
                <input type="hidden" name="GroupName" value="<?php echo htmlspecialchars($group_name); ?>">
                <input type="hidden" name="Filiere" value="<?php echo htmlspecialchars($filiere); ?>">
                <input type="hidden" name="Semester" value="<?php echo htmlspecialchars($semester); ?>">
                <input type="hidden" name="room-type" value="<?php echo htmlspecialchars($room_type); ?>">
                <input type="hidden" name="Matier" value="<?php echo htmlspecialchars($subject_id); ?>">
                <input type="hidden" name="Prof" value="<?php echo htmlspecialchars($prof_id); ?>">
                <input type="hidden" name="date_debut" value="<?php echo htmlspecialchars($date); ?>">
                <input type="hidden" name="selected_room" value="<?php echo htmlspecialchars($selected_room); ?>">
                <input type="hidden" name="selected_time" value="<?php echo htmlspecialchars($selected_time); ?>">

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
