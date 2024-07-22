<?php
include 'db_connect.php';

$edit_mode = false;
$professeur = ['first_name' => '', 'last_name' => '', 'email' => '', 'gsm' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_professeur'])) {
     
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];
        $gsm = $_POST['gsm']; // New field
        $sql = "INSERT INTO professeur (first_name, last_name, email, gsm) VALUES ('$first_name', '$last_name', '$email', '$gsm')";
        if ($conn->query($sql) === TRUE) {
            echo "New professeur added successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } elseif (isset($_POST['delete_professeur'])) {
       
        $id = $_POST['id'];
        $sql = "DELETE FROM professeur WHERE id=$id";
        if ($conn->query($sql) === TRUE) {
            echo "Professeur deleted successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } elseif (isset($_POST['update_professeur'])) {
     
        $id = $_POST['id'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];
        $gsm = $_POST['gsm']; 
        $update_sql = "UPDATE professeur SET first_name='$first_name', last_name='$last_name', email='$email', gsm='$gsm' WHERE id=$id";
        if ($conn->query($update_sql) === TRUE) {
            echo "Professeur updated successfully";
            header("Location: Professeurs.php"); 
            exit();
        } else {
            echo "Error: " . $update_sql . "<br>" . $conn->error;
        }
    }
}

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $sql = "SELECT first_name, last_name, email, gsm FROM professeur WHERE id=$id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $professeur = $result->fetch_assoc();
        $edit_mode = true;
    } else {
        echo "Professeur not found";
    }
}

$sql = "SELECT id, first_name, last_name, email, gsm FROM professeur";
$professeurs = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="sidebar">
        <div class="logo">
            <img src="ENCG-BM_logo_header.png" width="200" alt="Logo">
        </div>
        <ul class="nav-links">
            <li><a href="#"><i class="icon-home"></i> Dashboard</a></li>
            <li><a href="#"><i class="icon-students"></i> Groups</a></li>
            <li><a href="#"><i class="icon-teachers"></i> Professeur</a></li>
            <li class="dropdown">
                <a href="salle.html"><i class="icon-attendance"></i> Salles</a>
                <ul class="dropdown-content">
                    <li><a href="Aj_salle.php">Ajouter une salle</a></li>
                    <li><a href="Maj_salle.php">Mettre à jour les salles</a></li>
                </ul>
            </li>
            <li><a href="salle.html"><i class="icon-attendance"></i> Reserve</a></li>
            <li><a href="#"><i class="icon-logout"></i> Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
       
        <section class="attendance">
            <h2>Manage Professeur</h2>

            <form action="Professeur.php" method="post">
                <h3><?php echo $edit_mode ? 'Edit Professeur' : 'Add New Professeur'; ?></h3>
                <br>
                <hr>
                <br>
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($professeur['first_name']); ?>" required>
                
                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($professeur['last_name']); ?>" required>
                
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($professeur['email']); ?>" required>
                
                <label for="gsm">GSM:</label>
                <input type="text" id="gsm" name="gsm" value="<?php echo htmlspecialchars($professeur['gsm']); ?>">
                
                <?php if ($edit_mode): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($_GET['edit']); ?>">
                    <button type="submit" name="update_professeur">Update Professeur</button>
                <?php else: ?>
                    <button type="submit" name="add_professeur">Add Professeur</button>
                <?php endif; ?>
            </form>

          
            <table>
                <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Gsm</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($professeurs->num_rows > 0): ?>
                        <?php while($row = $professeurs->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row["first_name"]); ?></td>
                                <td><?php echo htmlspecialchars($row["last_name"]); ?></td>
                                <td><?php echo htmlspecialchars($row["email"]); ?></td>
                                <td><?php echo htmlspecialchars($row["gsm"]); ?></td>
                                <td>
                                   
                                    <form action="Professeur.php" method="get" style="display:inline;">
                                        <input type="hidden" name="edit" value="<?php echo htmlspecialchars($row["id"]); ?>">
                                        <button type="submit">Edit</button>
                                    </form>

                                   
                                    <form action="Professeur.php" method="post" style="display:inline;">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($row["id"]); ?>">
                                        <button type="submit" name="delete_professeur">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4">No data found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </div>
</body>
</html>
