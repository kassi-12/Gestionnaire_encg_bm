<?php
include '../db/db_connect.php';

$edit_mode_user = false;
$user = ['username' => '', 'email' => '', 'password' => ''];

// Handle User operations
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_user'])) {
        $username = $conn->real_escape_string($_POST['username']);
        $email = $conn->real_escape_string($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param('sss', $username, $email, $password);
            if ($stmt->execute()) {
                echo "New user added successfully";
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    } elseif (isset($_POST['delete_user'])) {
        $id = (int)$_POST['id'];

        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param('i', $id);
            if ($stmt->execute()) {
                echo "User deleted successfully";
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    } elseif (isset($_POST['update_user'])) {
        $id = (int)$_POST['id'];
        $username = $conn->real_escape_string($_POST['username']);
        $email = $conn->real_escape_string($_POST['email']);
        $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
        
        $sql = "UPDATE users SET username = ?, email = ?" . ($password ? ", password = ?" : "") . " WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            if ($password) {
                $stmt->bind_param('sssi', $username, $email, $password, $id);
            } else {
                $stmt->bind_param('ssi', $username, $email, $id);
            }
            if ($stmt->execute()) {
                echo "User updated successfully";
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    }
}

// Fetch users for display
$users = $conn->query("SELECT * FROM users");
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
            <h2>Manage Users</h2>

            <form action="users.php" method="post">
                <h3><?php echo $edit_mode_user ? 'Edit User' : 'Add New User'; ?></h3>

                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" <?php echo !$edit_mode_user ? 'required' : ''; ?>>

                <?php if ($edit_mode_user): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($_GET['edit']); ?>">
                    <button type="submit" name="update_user">Update User</button>
                <?php else: ?>
                    <button type="submit" name="add_user">Add User</button>
                <?php endif; ?>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users->num_rows > 0): ?>
                        <?php while ($row = $users->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row["username"]); ?></td>
                                <td><?php echo htmlspecialchars($row["email"]); ?></td>
                                <td>
                                    <form action="users.php" method="get" style="display:inline;">
                                        <input type="hidden" name="edit" value="<?php echo htmlspecialchars($row["id"]); ?>">
                                        <button type="submit">Edit</button>
                                    </form>

                                    <form action="users.php" method="post" style="display:inline;">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($row["id"]); ?>">
                                        <button type="submit" name="delete_user">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">No users found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </div>
</body>

</html>

