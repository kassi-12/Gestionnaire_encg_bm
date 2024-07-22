<?php
include '../db/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve data from the form submission
    $event_name = isset($_POST['EventName']) ? $_POST['EventName'] : '';
    $organizer = isset($_POST['Organizer']) ? $_POST['Organizer'] : '';
    $date = isset($_POST['EventDate']) ? $_POST['EventDate'] : '';
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

    // Obtenir le jour de la semaine en français
    $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE);
    $formatter->setPattern('EEEE');
    $day_of_week = $formatter->format(strtotime($date));
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
    <title>Confirmation d'Événement</title>
    <link rel="stylesheet" href="../../assets/styles.css">
    <style>
        .button-group {
            display: flex;
            gap: 10px; 
        }

        .button-group button {
            padding: 10px 20px;
            background-color: #002F6C;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .button-group button:hover {
            background-color: #004080;
        }
    </style>
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
            <h3>Confirmation d'Événement</h3>
            <p class="required-fields">Veuillez vérifier les détails de votre événement ci-dessous :</p>

            <div class="confirmation-item">
                <span class="label">Nom de l'Événement:</span>
                <span class="value"><input type="text" value="<?php echo htmlspecialchars($event_name); ?>" readonly></span>
            </div>
            <div class="confirmation-item">
                <span class="label">Organisateur:</span>
                <span class="value"><input type="text" value="<?php echo htmlspecialchars($organizer); ?>" readonly></span>
            </div>
            <div class="confirmation-item">
                <span class="label">Date Sélectionnée:</span>
                <span class="value"><input type="text" value="<?php echo htmlspecialchars($day_of_week) . " | " . htmlspecialchars($date); ?>" readonly></span>
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
            <form action="confirm_reservation_evenement.php" method="POST">
                <input type="hidden" name="EventName" value="<?php echo htmlspecialchars($event_name); ?>">
                <input type="hidden" name="Organizer" value="<?php echo htmlspecialchars($organizer); ?>">
                <input type="hidden" name="EventDate" value="<?php echo htmlspecialchars($date); ?>">
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
