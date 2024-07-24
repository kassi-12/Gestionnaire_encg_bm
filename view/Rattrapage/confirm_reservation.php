<?php
include '../db/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer les données du formulaire
    $reservation_id = isset($_POST['reservation_id']) ? $_POST['reservation_id'] : '';
    $date = isset($_POST['date_debut']) ? $_POST['date_debut'] : '';
    $selected_room = isset($_POST['salle_id']) ? $_POST['salle_id'] : '';
    $selected_time = isset($_POST['selected_time']) ? $_POST['selected_time'] : '';

    // Séparer l'heure de début et l'heure de fin
    list($start_time, $end_time) = explode('-', $selected_time);

    // Préparer l'instruction SQL d'insertion
    $sql_insert = "INSERT INTO rattrapage (reservation_id, rattrapage_date, start_time, end_time, salle_id) 
                   VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql_insert);
    if ($stmt === false) {
        die("Erreur de préparation de la requête : " . $conn->error);
    }

    // Associer les paramètres
    $stmt->bind_param('isssi', $reservation_id, $date, $start_time, $end_time, $selected_room);

    // Exécuter l'instruction SQL
    if ($stmt->execute()) {
        // Mettre à jour le rapport correspondant pour définir le statut sur "en cours"
        $sql_update = "UPDATE rapport SET statut = 'En cours' WHERE reservation_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        if ($stmt_update === false) {
            die("Erreur de préparation de la requête de mise à jour : " . $conn->error);
        }

        $stmt_update->bind_param('i', $reservation_id);

        if ($stmt_update->execute()) {
            echo "Réservation de rattrapage confirmée avec succès et statut mis à jour!";
            header('Location: ../reservation/reserve.php?date=' . urlencode($date) . '&salle=' . urlencode($selected_room));
            exit();
        } else {
            die("Erreur lors de la mise à jour du statut du rapport : " . $stmt_update->error);
        }
        
        $stmt_update->close();
    } else {
        die("Erreur lors de la confirmation de la réservation de rattrapage : " . $stmt->error);
    }
    
    // Fermer la déclaration et la connexion
    $stmt->close();
    $conn->close();
} else {
    header('Location: step1.php');
    exit();
}
?>
