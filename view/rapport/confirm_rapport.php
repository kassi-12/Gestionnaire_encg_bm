<?php
include '../db/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve data from the form submission
    $reservation_id = isset($_POST['reservation_id']) ? $_POST['reservation_id'] : '';
    $motif = isset($_POST['motif']) ? $_POST['motif'] : '';
    $reservation_date = isset($_POST['reservation_date']) ? $_POST['reservation_date'] : '';

    // Check if reservation_id is valid and motif is not empty
    if (empty($reservation_id) || empty($motif)) {
        die("Réservation ID ou motif manquant.");
    }

    // Prepare statement to insert data into rapport table
    $stmt = $conn->prepare("INSERT INTO rapport (reservation_id, motif, rapport_date, statut) VALUES (?, ?, ?, ?)");
    $statut = "En attente"; // Set the status variable
    $stmt->bind_param("isss", $reservation_id, $motif, $reservation_date, $statut);

    if ($stmt->execute()) {
        echo "Réservation confirmée avec succès. Rapport ID: " . $stmt->insert_id;
        header('Location: rapports.php');
    } else {
        echo "Erreur lors de la confirmation de la réservation: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    // Handle the case when the form is accessed directly (GET request)
    header('Location: ../Reservation/reserve.php'); // Redirect to the form page
    exit();
}
?>
