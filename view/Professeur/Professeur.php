<?php
include '../db/db_connect.php';

$edit_mode = false;
$professeur = ['first_name' => '', 'last_name' => '', 'email' => '', 'gsm' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_professeur'])) {
        // Retrieve and sanitize form data
        $first_name = $conn->real_escape_string($_POST['first_name']);
        $last_name = $conn->real_escape_string($_POST['last_name']);
        $email = $conn->real_escape_string($_POST['email']);
        $gsm = $conn->real_escape_string($_POST['gsm']);
        
        // Prepare SQL statement
        $stmt = $conn->prepare("INSERT INTO professeur (first_name, last_name, email, gsm) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $first_name, $last_name, $email, $gsm);

        if ($stmt->execute()) {
            echo "New professeur added successfully";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } elseif (isset($_POST['delete_professeur'])) {
        $id = (int)$_POST['id'];

        $stmt = $conn->prepare("DELETE FROM professeur WHERE id = ?");
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            echo "Professeur deleted successfully";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } elseif (isset($_POST['update_professeur'])) {
        $id = (int)$_POST['id'];
        $first_name = $conn->real_escape_string($_POST['first_name']);
        $last_name = $conn->real_escape_string($_POST['last_name']);
        $email = $conn->real_escape_string($_POST['email']);
        $gsm = $conn->real_escape_string($_POST['gsm']);
        
        $stmt = $conn->prepare("UPDATE professeur SET first_name = ?, last_name = ?, email = ?, gsm = ? WHERE id = ?");
        $stmt->bind_param('ssssi', $first_name, $last_name, $email, $gsm, $id);

        if ($stmt->execute()) {
            header("Location: Professeur.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT first_name, last_name, email, gsm FROM professeur WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $professeur = $result->fetch_assoc();
        $edit_mode = true;
    } else {
        echo "Professeur not found";
    }
    $stmt->close();
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
    <title>Manage Professeurs</title>
    <link rel="stylesheet" href="../../assets/styles.css">
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css'>
</head>
<body>
<div class="sidebar">
    <div class='logo'>
        <img src='../../image/ENCG-BM_logo_header.png' width='200' alt='Logo'>
    </div>
    <ul class='nav-links'>
        <li><a href='../dashboard/dashboard.php'><i class='fas fa-home'></i> Tableau de bord</a></li>
        <li><a href='../group/groups.php'><i class='fas fa-users'></i> Groupes</a></li>
        <li><a href='../professeur/professeur.php'><i class='fas fa-chalkboard-teacher'></i> Professeurs</a></li>
        <li><a href='../matier/matier.php'><i class='fas fa-book'></i> Matière</a></li>
        <li class='dropdown'>
            <a href='../salle/salles.php'><i class='fas fa-building'></i> Salles</a>
            <ul class='dropdown-content'>
                <li><a href='../salle/Aj_salle.php'>Ajouter une salle</a></li>
                <li><a href='../salle/Maj_salle.php'>Mettre à jour les salles</a></li>
            </ul>
        </li>
        <li class='dropdown'>
            <a href='../reservation/Reserve.php'><i class='fas fa-calendar-check'></i> Réservation</a>
            <ul class='dropdown-content'>
                <li><a href='../reservation/Evenement.php'>Événement</a></li>
                <li><a href='../reservation/normal.php'>Cours/Exam</a></li>
            </ul>
        </li>
        <li><a href='../rapport/rapports.php'><i class='fas fa-file-alt'></i> Rapport</a></li>
        <li><a href='../planning/planning.php'><i class='fas fa-calendar'></i> Planning</a></li>
        <li><a href='#'><i class='fas fa-sign-out-alt'></i> Déconnexion</a></li>
    </ul>
</div>
    <div class="main-content">
        <section class="attendance">
            <h2>Professeur</h2>

            <form action="Professeur.php" method="post">
                <h3><?php echo $edit_mode ? 'Edit Professeur' : 'Add New Professeur'; ?></h3>
                
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
                        <th>GSM</th>
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
                        <tr><td colspan="5">No data found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </div>
</body>
</html>
