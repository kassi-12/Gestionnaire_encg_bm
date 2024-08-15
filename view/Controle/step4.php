<?php
include '../db/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve data from the form submission
    $selected_rooms = $_POST['selected_rooms'] ?? [];
    $selected_times = $_POST['selected_times'] ?? [];
    $total_capacity = $_POST['total_capacity'] ?? '';
    $niveau = $_POST['Niveau'] ?? '';
    $filieres = $_POST['Filiere'] ?? array();
    $groupName = $_POST['GroupName'] ?? '';
    $semester = $_POST['Semester'] ?? '';
    $room_type = $_POST['room-type'] ?? '';
    $date = $_POST['date_debut'] ?? '';
    $reservation_ids = $_POST['reservation_id'] ?? [];
    echo "Filiere : " . implode(', ', array_map('htmlspecialchars', $filieres)) . "<br>";
    // Validate the input data
    if (empty($selected_rooms) || empty($selected_times) || empty($total_capacity) || empty($reservation_ids)) {
        die("Tous les champs sont obligatoires.");
    }

    // Fetch additional details for each reservation ID
    foreach ($reservation_ids as $reservation_id) {
        $stmt = $conn->prepare("SELECT * FROM reservation WHERE id = ?");
        $stmt->bind_param("s", $reservation_id);
        $stmt->execute();
        $result_reservation = $stmt->get_result();

        if ($result_reservation->num_rows === 0) {
            die("Aucune réservation trouvée pour l'ID fourni.");
        }

        $reservation = $result_reservation->fetch_assoc();

        // Fetch additional details like group, professor, semester, subject, etc.
        $group_id = $reservation['group_id'];
        $professeur_id = $reservation['professeur_id'];
        $semester_id = $reservation['semester_id'];
        $subject_id = $reservation['subject_id'];

        // Fetch group details
        $stmt_group = $conn->prepare("SELECT name, year, filiere FROM grp WHERE id = ?");
        $stmt_group->bind_param("s", $group_id);
        $stmt_group->execute();
        $result_group = $stmt_group->get_result();
        $group = $result_group->fetch_assoc();
        $group_name = $group['name'];
        $niveau = $group['year'];
        $filiere = $group['filiere'];

        // Fetch professor details
        $stmt_prof = $conn->prepare("SELECT first_name, last_name FROM professeur WHERE id = ?");
        $stmt_prof->bind_param("s", $professeur_id);
        $stmt_prof->execute();
        $result_prof = $stmt_prof->get_result();
        $prof = $result_prof->fetch_assoc();
        $prof_name = $prof['first_name'] . ' ' . $prof['last_name'];

        // Fetch semester details
        $stmt_semester = $conn->prepare("SELECT name FROM semesters WHERE id = ?");
        $stmt_semester->bind_param("s", $semester_id);
        $stmt_semester->execute();
        $result_semester = $stmt_semester->get_result();
        $semester = $result_semester->fetch_assoc()['name'];

        // Fetch subject details
        $stmt_subject = $conn->prepare("SELECT subject_name FROM subjects WHERE subject_id = ?");
        $stmt_subject->bind_param("s", $subject_id);
        $stmt_subject->execute();
        $result_subject = $stmt_subject->get_result();
        $subject = $result_subject->fetch_assoc()['subject_name'];

        // Process or use the fetched details as needed
    }
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
    <title>Confirmation de Sélection</title>
    <link rel="stylesheet" href="../../assets/styles.css">
</head>
<body>
<div class='sidebar'>
    <!-- Sidebar content -->
</div>
<div class="main-content">
    <div class="container">
        <div class="add-classes">
            <h3>Confirmation de Sélection</h3>
            <p class="required-fields">Veuillez vérifier les détails de votre sélection ci-dessous :</p>
            
            <div class="confirmation-item">
                <span class="label">Niveau:</span>
                <span class="value"><input type="text" value="<?php echo htmlspecialchars($niveau); ?>" readonly></span>
            </div>
            <div class="confirmation-item">
                <span class="label">Nom du Groupe:</span>
                <span class="value"><input type="text" value="<?php echo htmlspecialchars($groupName); ?>" readonly></span>
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
                <span class="label">Date Sélectionnée:</span>
                <span class="value"><input type="text" value="<?php echo htmlspecialchars($date); ?>" readonly></span>
            </div>
            
            
            <!-- Display selected rooms -->
            <div class="confirmation-item">
                <span class="label">Salles Sélectionnées:</span>
                <span class="value">
                    <?php foreach ($selected_rooms as $room_id): ?>
                        <?php
                        // Fetch room details
                        $stmt_room = $conn->prepare("SELECT name FROM salles WHERE id = ?");
                        $stmt_room->bind_param("s", $room_id);
                        $stmt_room->execute();
                        $result_room = $stmt_room->get_result();
                        $room = $result_room->fetch_assoc();
                        ?>
                        <input type="text" value="<?php echo htmlspecialchars($room['name']); ?>" readonly><br>
                    <?php endforeach; ?>
                </span>
            </div>

            <!-- Display selected times -->
            <div class="confirmation-item">
                <span class="label">Heures Sélectionnées:</span>
                <span class="value">
                    <?php foreach ($selected_times as $time): ?>
                        <input type="text" value="<?php echo htmlspecialchars($time); ?>" readonly><br>
                    <?php endforeach; ?>
                </span>
            </div>

            <form action="confirm_reservation.php" method="POST">
                <input type="hidden" name="Niveau" value="<?php echo htmlspecialchars($niveau); ?>">
                <input type="hidden" name="GroupName" value="<?php echo htmlspecialchars($groupName); ?>">
                <input type="hidden" name="Semester" value="<?php echo htmlspecialchars($semester); ?>">
                <input type="hidden" name="room-type" value="<?php echo htmlspecialchars($room_type); ?>">
                <input type="hidden" name="date_debut" value="<?php echo htmlspecialchars($date); ?>">
                <input type="hidden" name="total_capacity" value="<?php echo htmlspecialchars($total_capacity); ?>">
                <input type="hidden" name="selected_rooms" value="<?php echo htmlspecialchars(implode(',', $selected_rooms)); ?>">
                <input type="hidden" name="selected_times" value="<?php echo htmlspecialchars(implode(',', $selected_times)); ?>">
                <input type="hidden" name="reservation_ids" value="<?php echo htmlspecialchars(implode(',', $reservation_ids)); ?>">
                <?php
                foreach ($filieres as $filiere) {
                    echo '<input type="hidden" name="Filiere[]" value="' . htmlspecialchars($filiere) . '">';
                }
                ?>
                <button type="submit">Confirmer</button>
            </form>

        </div>
    </div>
</div>

</body>
</html>
