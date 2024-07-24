<?php
$host = 'localhost'; 
$dbname = 'ecoleencg'; 
$username = 'root'; 
$password = ''; 

// Create a new connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to archive past rattrapages
function archivePastRattrapages($conn) {
    // Check connection state
    if (!$conn->ping()) {
        echo "Connection is closed.<br>";
        return;
    }

    // Get current date and time
    $current_datetime = date('Y-m-d H:i:s');

    // Insert past records into archive table
    $insert_sql = "INSERT INTO `rattrapage_archive` (id, reservation_id, rattrapage_date, start_time, end_time, salle_id)
                   SELECT id, reservation_id, rattrapage_date, start_time, end_time, salle_id
                   FROM `rattrapage`
                   WHERE CONCAT(rattrapage_date, ' ', end_time) <= ?";

    if ($stmt = $conn->prepare($insert_sql)) {
        $stmt->bind_param('s', $current_datetime);
        if ($stmt->execute()) {
            // Update rapport table status
            $update_sql = "UPDATE rapport 
                           SET statut = 'Terminé' 
                           WHERE reservation_id IN (SELECT reservation_id FROM rattrapage_archive)";
            if ($conn->query($update_sql) === FALSE) {
                echo "Error updating rapport table: " . $conn->error;
            }

            // Delete past records from original table
            $delete_sql = "DELETE FROM `rattrapage` WHERE CONCAT(rattrapage_date, ' ', end_time) <= ?";
            if ($delete_stmt = $conn->prepare($delete_sql)) {
                $delete_stmt->bind_param('s', $current_datetime);
                if ($delete_stmt->execute() === FALSE) {
                    echo "Error deleting records from rattrapage table: " . $conn->error;
                }
                $delete_stmt->close();
            } else {
                echo "Error preparing delete statement: " . $conn->error;
            }
        } else {
            echo "Error inserting into archive table: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing insert statement: " . $conn->error;
    }
}
?>
