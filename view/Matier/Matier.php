<?php
include 'db_connect.php';

$edit_mode = false;
$subject = ['subject_name' => '', 'semester_id' => '', 'year' => ''];


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_subject'])) {
        $subject_name = $_POST['subject_name'];
        $semester_id = $_POST['semester_id'];
        $year = $_POST['year'];
        $sql = "INSERT INTO subjects (subject_name, semester_id, year) VALUES ('$subject_name', '$semester_id', '$year')";
        if ($conn->query($sql) === TRUE) {
            echo "Nouvelle matière ajoutée avec succès";
        } else {
            echo "Erreur : " . $sql . "<br>" . $conn->error;
        }
    } elseif (isset($_POST['delete_subject'])) {
        $id = $_POST['id'];
        $sql = "DELETE FROM subjects WHERE subject_id=$id";
        if ($conn->query($sql) === TRUE) {
            echo "Matière supprimée avec succès";
        } else {
            echo "Erreur : " . $sql . "<br>" . $conn->error;
        }
    } elseif (isset($_POST['update_subject'])) {
        $id = $_POST['id'];
        $subject_name = $_POST['subject_name'];
        $semester_id = $_POST['semester_id'];
        $year = $_POST['year'];
        $update_sql = "UPDATE subjects SET subject_name='$subject_name', semester_id='$semester_id', year='$year' WHERE subject_id=$id";
        if ($conn->query($update_sql) === TRUE) {
            echo "Matière mise à jour avec succès";
            header("Location: Matier.php");
            exit();
        } else {
            echo "Erreur : " . $update_sql . "<br>" . $conn->error;
        }
    }
}


if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $sql = "SELECT subject_name, semester_id, year FROM subjects WHERE subject_id=$id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $subject = $result->fetch_assoc();
        $edit_mode = true;
    } else {
        echo "Matière non trouvée";
    }
}


$sql = "SELECT subject_id, subject_name, semester_id, year FROM subjects";
$subjects = $conn->query($sql);

$sql = "SELECT id, name FROM semesters";
$semesters = $conn->query($sql);


$sql = "SELECT DISTINCT year FROM grp";
$years = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gérer les Matières</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <img src="ENCG-BM_logo_header.png" width="200" alt="Logo">
        </div>
        <ul class="nav-links">
            <li><a href="#"><i class="icon-home"></i> Tableau de Bord</a></li>
            <li><a href="#"><i class="icon-students"></i> Groupes</a></li>
            <li><a href="#"><i class="icon-teachers"></i> Professeurs</a></li>
            <li class="dropdown">
                <a href="salle.html"><i class="icon-attendance"></i> Salles</a>
                <ul class="dropdown-content">
                    <li><a href="Aj_salle.php">Ajouter une salle</a></li>
                    <li><a href="Maj_salle.php">Mettre à jour les salles</a></li>
                </ul>
            </li>
            <li><a href="#"><i class="icon-subjects"></i> Matières</a></li>
            <li><a href="#"><i class="icon-logout"></i> Déconnexion</a></li>
        </ul>
    </div>
    <div class="main-content">
        <section class="attendance">
            <h2>Gérer les Matières</h2>

            <form action="Matier.php" method="post">
                <h3><?php echo $edit_mode ? 'Modifier la Matière' : 'Ajouter une Nouvelle Matière'; ?></h3>
                <br>
                <hr>
                <br>
                <label for="subject_name">Nom de la Matière :</label>
                <input type="text" id="subject_name" name="subject_name" value="<?php echo htmlspecialchars($subject['subject_name']); ?>" required>
                
                <label for="semester_id">Semestre :</label>
                <select id="semester_id" name="semester_id" required>
                    <?php if ($semesters->num_rows > 0): ?>
                        <?php while($row = $semesters->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($row['id']); ?>" <?php echo ($row['id'] == $subject['semester_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <option value="">Aucun semestre disponible</option>
                    <?php endif; ?>
                </select>
                
                <label for="year">Niveau :</label>
                <select id="year" name="year" required>
                    <?php if ($years->num_rows > 0): ?>
                        <?php while($row = $years->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($row['year']); ?>" <?php echo ($row['year'] == $subject['year']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['year']) . " année"; ?>
                            </option>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <option value="">Aucun niveau disponible</option>
                    <?php endif; ?>
                </select>
                
                <?php if ($edit_mode): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($_GET['edit']); ?>">
                    <button type="submit" name="update_subject">Mettre à Jour la Matière</button>
                <?php else: ?>
                    <button type="submit" name="add_subject">Ajouter la Matière</button>
                <?php endif; ?>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>Nom de la Matière</th>
                        <th>Semestre</th>
                        <th>Niveau</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($subjects->num_rows > 0): ?>
                        <?php while($row = $subjects->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row["subject_name"]); ?></td>
                                <td><?php echo htmlspecialchars($row["semester_id"]); ?></td>
                                <td><?php echo htmlspecialchars($row["year"]); ?></td>
                                <td>
                                    <form action="Matier.php" method="get" style="display:inline;">
                                        <input type="hidden" name="edit" value="<?php echo htmlspecialchars($row["subject_id"]); ?>">
                                        <button type="submit">Modifier</button>
                                    </form>
                                    <form action="Matier.php" method="post" style="display:inline;">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($row["subject_id"]); ?>">
                                        <button type="submit" name="delete_subject">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4">Aucune donnée trouvée</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </div>
</body>
</html>
