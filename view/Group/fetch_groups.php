<?php
include 'db_connect.php';
$sql = "SELECT name, nombre FROM grp";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["nombre"]) . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='2'>No data found</td></tr>";
}


$conn->close();
?>