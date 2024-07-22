<?php
include '../db/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer les données du formulaire
    $event_name = isset($_POST['EventName']) ? $_POST['EventName'] : '';
    $number_of_people = isset($_POST['NumberOfPeople']) ? $_POST['NumberOfPeople'] : 0;
    $event_date = isset($_POST['EventDate']) ? $_POST['EventDate'] : '';
    $organizer = isset($_POST['Organizer']) ? $_POST['Organizer'] : '';
    $selected_room = isset($_POST['selected_room']) ? $_POST['selected_room'] : '';
    $selected_time = isset($_POST['selected_time']) ? $_POST['selected_time'] : '';

    // Séparer l'heure de début et l'heure de fin
    list($start_time, $end_time) = explode('-', $selected_time);

    // Préparer l'instruction SQL d'insertion
    $sql_insert = "INSERT INTO evenement (event_name, salle_id, event_date, start_time, end_time, organizer) 
                   VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql_insert);
    if ($stmt === false) {
        die("Erreur de préparation de la requête : " . $conn->error);
    }

    // Associer les paramètres
    $stmt->bind_param('sissss', $event_name, $selected_room, $event_date, $start_time, $end_time, $organizer);

    // Exécuter l'instruction SQL
    if ($stmt->execute()) {
        echo "Événement confirmé avec succès!";
        header('Location: reserve.php?date=' . urlencode($event_date) . '&salle=' . urlencode($selected_room));
        exit();
    } else {
        die("Erreur lors de la confirmation de l'événement : " . $stmt->error);
    }

    // Fermer la déclaration et la connexion
    $stmt->close();
    $conn->close();
} else {
    header('Location: step1.php');
    exit();
}
?>
