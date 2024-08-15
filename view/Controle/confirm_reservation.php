<?php
include '../db/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $selected_rooms = isset($_POST['selected_rooms']) ? $_POST['selected_rooms'] : '';
    $selected_times = isset($_POST['selected_times']) ? (array)$_POST['selected_times'] : [];
    $filiere = isset($_POST['Filiere']) ? (array)$_POST['Filiere'] : [];
    $niveau = $_POST['Niveau'] ?? '';
    $groupName = $_POST['GroupName'] ?? '';
    $semester = $_POST['Semester'] ?? '';
    $room_type = $_POST['room-type'] ?? '';
    $date = $_POST['date_debut'] ?? '';
    $reservation_ids = isset($_POST['reservation_ids']) ? (array)$_POST['reservation_ids'] : [];
    $selected_time = $selected_times[0]; // Assuming only one time slot is selected
    list($start_time, $end_time) = explode('-', $selected_time);

    // Parse the selected_rooms string into an array
    $selected_rooms_array = explode(',', $selected_rooms);

    // Debugging output
    echo "<pre>";
    echo "Selected rooms: " . print_r($selected_rooms_array, true) . "\n";
    echo "Selected times: " . print_r($selected_times, true) . "\n";
    echo "Filiere: " . print_r($filiere, true) . "\n";
    echo "Niveau: $niveau\n";
    echo "GroupName: $groupName\n";
    echo "Semester: $semester\n";
    echo "Room type: $room_type\n";
    echo "Date: $date\n";
    echo "Reservation IDs: " . print_r($reservation_ids, true) . "\n";
    echo "Selected time: $selected_time (Start: $start_time, End: $end_time)\n";

    // Get the number of students in the group
    $sql_group = "SELECT nombre FROM grp WHERE name = ? AND year = ? AND (extra_info = ? OR filiere = ?)";
    $stmt_group = $conn->prepare($sql_group);
    if ($stmt_group === false) {
        die("Error preparing query: " . $conn->error);
    }
    $stmt_group->bind_param('ssss', $groupName, $niveau, $filiere[0], $filiere[0]);
    $stmt_group->execute();
    $stmt_group->bind_result($nombre_etudiants);
    $stmt_group->fetch();
    $stmt_group->close();

    echo "Nombre of students: $nombre_etudiants\n";

    if (!$nombre_etudiants) {
        die("Error: Group not found.");
    }

    // Fetch all selected rooms from the database
    $sql_rooms = "SELECT id, capacity_exam FROM salles WHERE id IN (" . implode(',', array_map('intval', $selected_rooms_array)) . ")";
    $result_rooms = $conn->query($sql_rooms);

    if (!$result_rooms) {
        die("Error fetching rooms: " . $conn->error);
    }

    // Debugging output
    echo "Rooms fetched from the database:\n";
    $rooms = [];
    while ($row = $result_rooms->fetch_assoc()) {
        $rooms[] = $row;
    }
    echo print_r($rooms, true) . "\n";

    // Insert a new record based on room type
    if ($room_type == 'controle') {
        $sql_insert = "INSERT INTO controle (reservation_id, controle_date, start_time, end_time) VALUES (?, ?, ?, ?)";
    } else if ($room_type == 'exam') {
        $sql_insert = "INSERT INTO exam (reservation_id, exam_date, start_time, end_time) VALUES (?, ?, ?, ?)";
    } else {
        die("Error: Invalid room type.");
    }

    $stmt_insert = $conn->prepare($sql_insert);
    if ($stmt_insert === false) {
        die("Error preparing insert query: " . $conn->error);
    }
    $stmt_insert->bind_param('isss', $reservation_ids[0], $date, $start_time, $end_time);
    $stmt_insert->execute();
    $record_id = $stmt_insert->insert_id;
    $stmt_insert->close();

    echo "New record inserted with ID: $record_id\n";

    // Distribute the group among the selected rooms
    foreach ($rooms as $room) {
        $room_id = $room['id'];
        $capacity_exam = $room['capacity_exam'];

        if ($nombre_etudiants <= 0) {
            break; // Exit the loop if all students have been placed
        }

        $capacity_to_insert = ($nombre_etudiants > $capacity_exam) ? $capacity_exam : $nombre_etudiants;
        $nombre_etudiants -= $capacity_to_insert;

        // Debugging output
        echo "Processing room ID: $room_id, Capacity exam: $capacity_exam, Capacity to insert: $capacity_to_insert, Remaining students: $nombre_etudiants\n";

        // Insert into the corresponding salle_* table based on room type
        if ($room_type == 'controle') {
            $sql_insert_salles = "INSERT INTO salles_controle (capacity, salle_id, controle_id) VALUES (?, ?, ?)";
        } else if ($room_type == 'exam') {
            $sql_insert_salles = "INSERT INTO salles_exam (capacity, salle_id, exam_id) VALUES (?, ?, ?)";
        } else {
            die("Error: Invalid room type.");
        }

        $stmt_insert_salles = $conn->prepare($sql_insert_salles);
        if ($stmt_insert_salles === false) {
            die("Error preparing insert salles query: " . $conn->error);
        }
        $stmt_insert_salles->bind_param('iii', $capacity_to_insert, $room_id, $record_id);
        $stmt_insert_salles->execute();
        $stmt_insert_salles->close();
    }

    echo "Reservation successfully confirmed!";
    echo "</pre>";

    // Commenting out header redirection for debugging
    // header('Location: ../rapport/reserve.php?date=' . urlencode($date) . '&reservation_id=' . urlencode($reservation_ids[0]));
    // exit();
} else {
    header('Location: step1.php');
    exit();
}
?>
