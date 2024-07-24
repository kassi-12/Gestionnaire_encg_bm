<?php
include '../db/db_connect.php';

$edit_mode = false;
$group = ['name' => '', 'nombre' => '', 'year' => '', 'filiere' => '', 'extra_info' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';
    $year = isset($_POST['year']) ? $_POST['year'] : '';
    $filiere = isset($_POST['filiere']) ? $_POST['filiere'] : '';
    $extra_info = isset($_POST['extra_info']) ? $_POST['extra_info'] : '';

    if (isset($_POST['add_group'])) {
        $sql = "INSERT INTO grp (name, nombre, year, filiere, extra_info) VALUES ('$name', '$nombre', '$year', '$filiere', '$extra_info')";
        if ($conn->query($sql) === TRUE) {
            echo "New group added successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } elseif (isset($_POST['delete_group'])) {
        $id = $_POST['id'];
        $sql = "DELETE FROM grp WHERE id=$id";
        if ($conn->query($sql) === TRUE) {
            echo "Group deleted successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } elseif (isset($_POST['update_group'])) {
        $id = $_POST['id'];
        $update_sql = "UPDATE grp SET name='$name', nombre='$nombre', year='$year', filiere='$filiere', extra_info='$extra_info' WHERE id=$id";
        if ($conn->query($update_sql) === TRUE) {
            echo "Group updated successfully";
            header("Location: Groups.php");
            exit();
        } else {
            echo "Error: " . $update_sql . "<br>" . $conn->error;
        }
    }
}

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $sql = "SELECT name, nombre, year, filiere, extra_info FROM grp WHERE id=$id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $group = $result->fetch_assoc();
        $edit_mode = true;
    } else {
        echo "Group not found";
    }
}

$sql = "SELECT id, name, nombre, year, filiere, extra_info FROM grp";
$groups = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../../assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script>
        function toggleSections() {
            const yearSelect = document.getElementById('year');
            const filiereSelect = document.getElementById('filiere');
            const filiereLabel = document.getElementById('filiere-label');
            const extraInfoSection = document.getElementById('extra-info-section');
            const extraInfoSelect = document.getElementById('extra_info');

            if (yearSelect.value === '4ème' || yearSelect.value === '5ème') {
                filiereSelect.style.display = 'block';
                filiereLabel.style.display = 'block';
                extraInfoSection.style.display = 'none';
                extraInfoSelect.value = '-';
            } else {
                filiereSelect.style.display = 'none';
                filiereLabel.style.display = 'none';
                extraInfoSection.style.display = 'block';
                filiereSelect.value = '-';
            }
        }

        function handleFiliereSectionToggle() {
            const filiereSelect = document.getElementById('filiere');
            const extraInfoSelect = document.getElementById('extra_info');

            if (filiereSelect.value !== '-') {
                extraInfoSelect.value = '-';
            } else if (extraInfoSelect.value !== '-') {
                filiereSelect.value = '-';
            }
        }

        document.addEventListener('DOMContentLoaded', (event) => {
            toggleSections();
            document.getElementById('filiere').addEventListener('change', handleFiliereSectionToggle);
            document.getElementById('extra_info').addEventListener('change', handleFiliereSectionToggle);
        });
    </script>
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
        <li><a href="../matier/matier.php"><i class="fas fa-book"></i> Matière</a></li> <!-- Changed icon to fa-book -->
        <li class="dropdown">
            <a href="../salle/salles.php"><i class="fas fa-building"></i> Salles</a> <!-- Changed icon to fa-building -->
            <ul class="dropdown-content">
                <li><a href="../salle/Aj_salle.php">Ajouter une salle</a></li>
                <li><a href="../salle/Maj_salle.php">Mettre à jour les salles</a></li>
            </ul>
        </li>
        <li class="dropdown">
            <a href="../reservation/Reserve.php"><i class="fas fa-calendar-check"></i> Réservation</a> <!-- Changed icon to fa-calendar-check -->
            <ul class="dropdown-content">
                <li><a href="../reservation/Evenement.php">Événement</a></li>
                <li><a href="../reservation/normal.php">Cours/Exam</a></li>
            </ul>
        </li>
        <li><a href="../rapport/rapports.php"><i class="fas fa-file-alt"></i> Rapport</a></li> <!-- Changed icon to fa-file-alt -->
        <li><a href="../planning/planning.php"><i class="fas fa-calendar"></i> Planning</a></li> <!-- Changed icon to fa-calendar -->
        <li><a href="#"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
    </ul>
</div>
<div class="main-content">
    <section class="attendance">
        <h2>Groups</h2>
        <form action="Groups.php" method="post">
            <h3><?php echo $edit_mode ? 'Edit Group' : 'Add New Group'; ?></h3>
            
            <label for="name">Nom de Group:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($group['name']); ?>" required>
            <label for="nombre">Nombre:</label>
            <input type="number" id="nombre" name="nombre" min="0" value="<?php echo htmlspecialchars($group['nombre']); ?>" required>
            <label for="year">Année:</label>
            <select id="year" name="year" onchange="toggleSections()" required>
                <option value="1er" <?php echo $group['year'] == '1èr' ? 'selected' : ''; ?>>1èr Année</option>
                <option value="2ème" <?php echo $group['year'] == '2ème' ? 'selected' : ''; ?>>2ème Année</option>
                <option value="3ème" <?php echo $group['year'] == '3ème' ? 'selected' : ''; ?>>3ème Année</option>
                <option value="4ème" <?php echo $group['year'] == '4ème' ? 'selected' : ''; ?>>4ème Année</option>
                <option value="5ème" <?php echo $group['year'] == '5ème' ? 'selected' : ''; ?>>5ème Année</option>
            </select>
            <label for="filiere" id="filiere-label" style="display: <?php echo ($group['year'] == '4ème' || $group['year'] == '5ème') ? 'block' : 'none'; ?>;">Filière:</label>
            <select id="filiere" name="filiere" style="display: <?php echo ($group['year'] == '4ème' || $group['year'] == '5ème') ? 'block' : 'none'; ?>;">
                <option value="-" <?php echo $group['filiere'] == '-' ? 'selected' : ''; ?>>-</option>
                <option value="Filiere1" <?php echo $group['filiere'] == 'Filiere1' ? 'selected' : ''; ?>>Filiere1</option>
                <option value="Filiere2" <?php echo $group['filiere'] == 'Filiere2' ? 'selected' : ''; ?>>Filiere2</option>
            </select>
            <div id="extra-info-section" style="display: <?php echo ($group['year'] == '1er' || $group['year'] == '2ème' || $group['year'] == '3ème') ? 'block' : 'none'; ?>;">
                <label for="extra_info">Section Info:</label>
                <select id="extra_info" name="extra_info">
                    <option value="-" <?php echo ($group['extra_info'] == '-') ? 'selected' : ''; ?>>-</option>
                    <option value="Section A" <?php echo ($group['extra_info'] == 'Section A') ? 'selected' : ''; ?>>Section A</option>
                    <option value="Section B" <?php echo ($group['extra_info'] == 'Section B') ? 'selected' : ''; ?>>Section B</option>
                </select>
            </div>
            <?php if ($edit_mode): ?>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($_GET['edit']); ?>">
                <button type="submit" name="update_group">Update Group</button>
            <?php else: ?>
                <button type="submit" name="add_group">Add Group</button>
            <?php endif; ?>
        </form>
        <table>
            <thead>
                <tr>
                    <th>Nom De Group</th>
                    <th>Nombre</th>
                    <th>Année</th>
                    <th>Filière</th>
                    <th>Section</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($groups->num_rows > 0): ?>
                    <?php while($row = $groups->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row["name"]); ?></td>
                            <td><?php echo htmlspecialchars($row["nombre"]); ?></td>
                            <td><?php echo htmlspecialchars($row["year"]); ?></td>
                            <td><?php echo htmlspecialchars($row["filiere"]); ?></td>
                            <td><?php echo htmlspecialchars($row["extra_info"]); ?></td>
                            <td>
                                <form action="Groups.php" method="get" style="display:inline;">
                                    <input type="hidden" name="edit" value="<?php echo htmlspecialchars($row["id"]); ?>">
                                    <button type="submit">Edit</button>
                                </form>
                                <form action="Groups.php" method="post" style="display:inline;">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($row["id"]); ?>">
                                    <button type="submit" name="delete_group">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6">No data found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</div>
</body>
</html>
