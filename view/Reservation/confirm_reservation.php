<?php
include '../db/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer les données du formulaire
    $niveau = isset($_POST['Niveau']) ? $_POST['Niveau'] : '';
    $group_name = isset($_POST['GroupName']) ? $_POST['GroupName'] : '';
    $filiere = isset($_POST['Filiere']) ? $_POST['Filiere'] : '';
    $semester_id = isset($_POST['Semester']) ? $_POST['Semester'] : '';
    $room_type = isset($_POST['room-type']) ? $_POST['room-type'] : '';
    $subject_id = isset($_POST['Matier']) ? $_POST['Matier'] : '';
    $prof_id = isset($_POST['Prof']) ? $_POST['Prof'] : '';
    $date = isset($_POST['date_debut']) ? $_POST['date_debut'] : '';
    $selected_room = isset($_POST['selected_room']) ? $_POST['selected_room'] : '';
    $selected_time = isset($_POST['selected_time']) ? $_POST['selected_time'] : '';

    // Séparer l'heure de début et l'heure de fin
    list($start_time, $end_time) = explode('-', $selected_time);

    // Obtenir le jour de la semaine en français
    $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE);
    $formatter->setPattern('EEEE');
    $day_of_week = $formatter->format(strtotime($date));

    // Préparer l'instruction SQL d'insertion
    $sql_insert = "INSERT INTO reservation (group_id, salle_id, professeur_id, reservation_date, start_time, end_time, jour_par_semaine, semester_id, subject_id, type_seance) 
                   VALUES ((SELECT id FROM `grp` WHERE name = ? and (extra_info = '$filiere' OR filiere = '$filiere') LIMIT 1), ?, ?, ?, ?, ?, ?, ?, ?,?)";
    $stmt = $conn->prepare($sql_insert);
    if ($stmt === false) {
        die("Erreur de préparation de la requête : " . $conn->error);
    }

    // Associer les paramètres
    $stmt->bind_param('siissssiis', $group_name, $selected_room, $prof_id, $date, $start_time, $end_time, $day_of_week, $semester_id, $subject_id, $room_type);

    // Exécuter l'instruction SQL
    if ($stmt->execute()) {
        echo "Réservation confirmée avec succès!";
        header('Location: reserve.php?date=' . urlencode($date) . '&salle=' . urlencode($selected_room));
        exit();
    } else {
        die("Erreur lors de la confirmation de la réservation : " . $stmt->error);
    }
    

    // Fermer la déclaration et la connexion
    $stmt->close();
    $conn->close();
} else {
    header('Location: step1.php');
    exit();
}
?>
