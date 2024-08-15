<?php
include '../db/db_connect.php';

$edit_mode_filiere = false;
$edit_mode_section = false;
$filiere = ['name' => ''];
$section = ['name' => ''];

// Handle Filiere operations
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_filiere'])) {
        $name = $conn->real_escape_string($_POST['name']);
        
        $stmt = $conn->prepare("INSERT INTO filiers (name) VALUES (?)");
        if ($stmt) {
            $stmt->bind_param('s', $name);
            if ($stmt->execute()) {
                echo "New filière added successfully";
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    } elseif (isset($_POST['delete_filiere'])) {
        $id = (int)$_POST['id'];

        $stmt = $conn->prepare("DELETE FROM filiers WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param('i', $id);
            if ($stmt->execute()) {
                echo "Filière deleted successfully";
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    } elseif (isset($_POST['update_filiere'])) {
        $id = (int)$_POST['id'];
        $name = $conn->real_escape_string($_POST['name']);
        
        $stmt = $conn->prepare("UPDATE filiers SET name = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param('si', $name, $id);
            if ($stmt->execute()) {
                header("Location: Settings.php");
                exit();
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    }

    if (isset($_POST['add_section'])) {
        $name = $conn->real_escape_string($_POST['name']);
        
        $stmt = $conn->prepare("INSERT INTO sections (name) VALUES (?)");
        if ($stmt) {
            $stmt->bind_param('s', $name);
            if ($stmt->execute()) {
                echo "New section added successfully";
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    } elseif (isset($_POST['delete_section'])) {
        $id = (int)$_POST['id'];

        $stmt = $conn->prepare("DELETE FROM sections WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param('i', $id);
            if ($stmt->execute()) {
                echo "Section deleted successfully";
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    } elseif (isset($_POST['update_section'])) {
        $id = (int)$_POST['id'];
        $name = $conn->real_escape_string($_POST['name']);
        
        $stmt = $conn->prepare("UPDATE sections SET name = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param('si', $name, $id);
            if ($stmt->execute()) {
                header("Location: Settings.php");
                exit();
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    }

    // Import CSV File
    if (isset($_POST['import_csv']) && isset($_FILES['file'])) {
        $file = $_FILES['file']['tmp_name'];

        if (($handle = fopen($file, 'r')) !== FALSE) {
            // Skip header row if it exists
            $header = fgetcsv($handle);

            while (($data = fgetcsv($handle)) !== FALSE) {
                // Example: Assuming the CSV has columns 'id', 'name' for the 'filiers' table
                $id = $conn->real_escape_string($data[0]);
                $name = $conn->real_escape_string($data[1]);

                $stmt = $conn->prepare("INSERT INTO filiers (id, name) VALUES (?, ?)");
                if ($stmt) {
                    $stmt->bind_param('is', $id, $name);
                    if (!$stmt->execute()) {
                        echo "Error: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    echo "Error preparing statement: " . $conn->error;
                }
            }
            fclose($handle);
            echo "CSV data imported successfully.";
        } else {
            echo "Error opening file.";
        }
    }

    // Import SQL File
    if (isset($_POST['import_sql']) && isset($_FILES['file'])) {
        $file = $_FILES['file']['tmp_name'];

        if (file_exists($file)) {
            $sql = file_get_contents($file);

            // Split SQL commands by delimiter (usually `;`)
            $queries = explode(';', $sql);

            foreach ($queries as $query) {
                $query = trim($query);
                if (!empty($query)) {
                    if (!$conn->query($query)) {
                        echo "Error executing query: " . $conn->error;
                    }
                }
            }
            echo "SQL file imported successfully.";
        } else {
            echo "File does not exist.";
        }
    }

    // Export Database as Excel
    if (isset($_POST['export_excel'])) {
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=database_export.xls");

        ob_start();

        echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
        echo '<head>';
        echo '<style>
                .header {
                    background-color: #FFFF00;
                    font-weight: bold;
                    text-align: center;
                }
                .table {
                    border-collapse: collapse;
                    width: 100%;
                }
                .table td, .table th {
                    border: 1px solid #000;
                    padding: 5px;
                    text-align: left;
                }
              </style>';
        echo '</head>';
        echo '<body>';

        $tables = $conn->query("SHOW TABLES");
        while ($table = $tables->fetch_array()) {
            $tableName = $table[0];
            
            echo "<table class='table'>";
            echo "<tr><th colspan='100%' style='background-color:#4CAF50;color:white;'>" . htmlspecialchars($tableName) . "</th></tr>";

            $columns = $conn->query("SHOW COLUMNS FROM $tableName");
            echo '<tr>';
            while ($column = $columns->fetch_array()) {
                echo '<th class="header">' . htmlspecialchars($column['Field']) . '</th>';
            }
            echo '</tr>';
            
            $rows = $conn->query("SELECT * FROM $tableName");
            while ($row = $rows->fetch_assoc()) {
                echo '<tr>';
                foreach ($row as $data) {
                    echo '<td>' . htmlspecialchars($data) . '</td>';
                }
                echo '</tr>';
            }
            echo '</table><br>';
        }

        echo '</body>';
        echo '</html>';

        ob_end_flush();
        exit();
    }
    // Export Database as CSV
// Import CSV File
if (isset($_POST['import_csv']) && isset($_FILES['file'])) {
    $file = $_FILES['file']['tmp_name'];

    if (($handle = fopen($file, 'r')) !== FALSE) {
        // Get the first row as the table name (assuming first row contains the table name)
        $table = basename($_FILES['file']['name'], ".csv"); // Assuming the table name matches the file name
        $table = $conn->real_escape_string($table);

        // Get the second row as column headers
        $columns = fgetcsv($handle);

        if (!$columns) {
            echo "Error: CSV file is empty or improperly formatted.";
            exit();
        }

        // Prepare the query dynamically based on the columns
        $columnList = implode(", ", array_map(function ($col) use ($conn) {
            return $conn->real_escape_string($col);
        }, $columns));
        
        $placeholders = implode(", ", array_fill(0, count($columns), '?'));
        $query = "INSERT INTO $table ($columnList) VALUES ($placeholders)";
        
        $stmt = $conn->prepare($query);
        if ($stmt) {
            // Bind parameters dynamically
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $types = str_repeat('s', count($data)); // Assuming all columns are strings, adjust if needed
                $stmt->bind_param($types, ...$data);

                if (!$stmt->execute()) {
                    echo "Error: " . $stmt->error;
                }
            }
            fclose($handle);
            $stmt->close();
            echo "CSV data imported successfully into $table.";
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    } else {
        echo "Error opening file.";
    }
}

    // Export Database as SQL
    if (isset($_POST['export_sql'])) {
        header("Content-Type: application/sql");
        header("Content-Disposition: attachment; filename=database_export.sql");
    
        $tables = $conn->query("SHOW TABLES");
        while ($table = $tables->fetch_array()) {
            $tableName = $table[0];
    
            $rows = $conn->query("SELECT * FROM $tableName");
            while ($row = $rows->fetch_assoc()) {
                $fields = array_keys($row);
                $values = array_map(function($value) use ($conn) {
                    if (is_null($value)) {
                        return "NULL";
                    } else {
                        return "'" . $conn->real_escape_string($value) . "'";
                    }
                }, array_values($row));
                echo "INSERT INTO $tableName (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $values) . ");\n";
            }
            echo "\n\n";
        }
        exit();
    }
    
    
    // Reset Database
    if (isset($_POST['reset_database'])) {
        $tables = $conn->query("SHOW TABLES");
        while ($table = $tables->fetch_array()) {
            $tableName = $table[0];
            if (!$conn->query("TRUNCATE TABLE $tableName")) {
                echo "Error truncating table $tableName: " . $conn->error;
            }
        }
        echo "Database reset successfully.";
    }
}

// Load Filiers and Sections for display
$filiers = $conn->query("SELECT * FROM filiers");
$sections = $conn->query("SELECT * FROM sections");

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Filiers and Sections</title>
    <link rel="stylesheet" href="../../assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="sidebar">
        <div class="logo">
            <img src="../../image/ENCG-BM_logo_header.png" width="200" alt="Logo">
        </div>
        <ul class="nav-links">
            <li><a href="../dashboard/dashboard.php"><i class="fas fa-home"></i> Tableau de bord</a></li>
            <li><a href="../group/groups.php"><i class="fas fa-users"></i> Groupes</a></li>
            <li><a href="../professeur/professeur.php"><i class="fas fa-chalkboard-teacher"></i> Professeurs</a></li>
            <li><a href="../matier/matier.php"><i class="fas fa-book"></i> Matière</a></li>
            <li class="dropdown">
                <a href="../salle/salles.php"><i class="fas fa-building"></i> Salles</a>
                <ul class="dropdown-content">
                    <li><a href="../salle/Aj_salle.php">Ajouter une salle</a></li>
                    <li><a href="../salle/Maj_salle.php">Mettre à jour les salles</a></li>
                </ul>
            </li>
            <li class="dropdown">
                <a href="../reservation/Reserve.php"><i class="fas fa-calendar-check"></i> Réservation</a>
                <ul class="dropdown-content">
                    <li><a href="../reservation/Evenement.php">Événement</a></li>
                    <li><a href="../reservation/normal.php">Cours/Exam</a></li>
                </ul>
            </li>
            <li><a href="../rapport/rapports.php"><i class="fas fa-file-alt"></i> Rapport</a></li>
            <li><a href="../planning/planning.php"><i class="fas fa-calendar"></i> Planning</a></li>
            <li><a href="#"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
        </ul>
    </div>

    <div class="main-content">
        <section class="attendance">
            <h2>Manage Filiers</h2>

            <form action="Settings.php" method="post">
                <h3><?php echo $edit_mode_filiere ? 'Edit Filière' : 'Add New Filière'; ?></h3>

                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($filiere['name']); ?>" required>

                <?php if ($edit_mode_filiere): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($_GET['edit']); ?>">
                    <button type="submit" name="update_filiere">Update Filière</button>
                <?php else: ?>
                    <button type="submit" name="add_filiere">Add Filière</button>
                <?php endif; ?>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($filiers->num_rows > 0): ?>
                        <?php while ($row = $filiers->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row["name"]); ?></td>
                                <td>
                                    <form action="Settings.php" method="get" style="display:inline;">
                                        <input type="hidden" name="edit" value="<?php echo htmlspecialchars($row["id"]); ?>">
                                        <button type="submit">Edit</button>
                                    </form>

                                    <form action="Settings.php" method="post" style="display:inline;">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($row["id"]); ?>">
                                        <button type="submit" name="delete_filiere">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2">No data found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
                    <hr>
                    <br>
            <h2>Manage Sections</h2>

            <form action="Settings.php" method="post">
                <h3><?php echo $edit_mode_section ? 'Edit Section' : 'Add New Section'; ?></h3>

                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($section['name']); ?>" required>

                <?php if ($edit_mode_section): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($_GET['edit']); ?>">
                    <button type="submit" name="update_section">Update Section</button>
                <?php else: ?>
                    <button type="submit" name="add_section">Add Section</button>
                <?php endif; ?>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($sections->num_rows > 0): ?>
                        <?php while ($row = $sections->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row["name"]); ?></td>
                                <td>
                                    <form action="Settings.php" method="get" style="display:inline;">
                                        <input type="hidden" name="edit" value="<?php echo htmlspecialchars($row["id"]); ?>">
                                        <button type="submit">Edit</button>
                                    </form>

                                    <form action="Settings.php" method="post" style="display:inline;">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($row["id"]); ?>">
                                        <button type="submit" name="delete_section">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2">No data found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <hr>
            <br>
            <h2>Import Data</h2>
            
                    <br>
            <form action="Settings.php" method="post" enctype="multipart/form-data">
                <label for="sql_file">Import SQL File:</label>
                <input type="file" id="sql_file" name="file">
                <button type="submit" name="import_sql">Import SQL</button>
            
            </form>
            
            <br>
            <hr>
            <br>
            <h2>Export Database</h2>
            <style>
               
                
                .inline-buttons button {
                display: block;
                width: 100%;
                padding: 10px;
                margin: 5px 0;
                background-color: #002a53; /* Darker color for buttons */
                color: #ffffff; /* White text */
                border: none;
                border-radius: 5px;
                cursor: pointer;
                text-align: center;
            }

            .inline-buttons button:hover {
                background-color: #004080; /* Slightly lighter color on hover */
            }


            
            </style>
            <form action="Settings.php" method="post" class="inline-buttons">
                <button type="submit" name="export_excel">Export as Excel</button>
                <button type="submit" name="export_sql">Export as SQL</button>
                <button type="submit" name="export_csv">Export as CSV</button>
                <br>
            </form>
            <br>
            <hr>
            <br>
            <h2>Reset DataBase</h2>           
            <form method="post">
                <button type="submit" name="reset_database" onclick="return confirm('Are you sure you want to reset the database? This action cannot be undone.')" style="margin-top: 10px;">Reset Database</button>
            </form>
        </section>
    </div>
</body>

</html>

