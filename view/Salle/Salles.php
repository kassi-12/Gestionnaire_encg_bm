<?php
include '../db/db_connect.php';

// Fetch data from the 'salles' table
$sql = "SELECT id, name, capacity, features, capacity_exam, room_type FROM salles";
$salles = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salles List</title>
    <link rel="stylesheet" href="../../assets/styles.css">
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css'>
    <style>
        
        .checkbox-col input[disabled] {
            cursor: not-allowed;
            opacity: 0.6;
        }
    </style>
</head>
<body>
<div class='sidebar'>
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
        <h2>Salles List</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Capacity</th>
                    <th>Accès à l'internet</th>
                    <th>Équipements du réseau</th>
                    <th>Vidéo projecteur</th>
                    <th>Climatiseur</th>
                    <th>Capacity Exam</th>
                    <th>Room Type</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($salles->num_rows > 0): ?>
                    <?php while($row = $salles->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row["name"]); ?></td>
                            <td><?php echo htmlspecialchars($row["capacity"]); ?></td>
                            <td class="checkbox-col">
                                <input type="checkbox" <?php echo in_array('Accès à l\'internet', explode(',', $row["features"])) ? 'checked' : ''; ?> disabled>
                            </td>
                            <td class="checkbox-col">
                                <input type="checkbox" <?php echo in_array('Équipements du réseau', explode(',', $row["features"])) ? 'checked' : ''; ?> disabled>
                            </td>
                            <td class="checkbox-col">
                                <input type="checkbox" <?php echo in_array('Vidéo projecteur', explode(',', $row["features"])) ? 'checked' : ''; ?> disabled>
                            </td>
                            <td class="checkbox-col">
                                <input type="checkbox" <?php echo in_array('Climatiseur', explode(',', $row["features"])) ? 'checked' : ''; ?> disabled>
                            </td>
                            <td><?php echo htmlspecialchars($row["capacity_exam"]); ?></td>
                            <td><?php echo htmlspecialchars($row["room_type"]); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8">No data found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</div>
</body>
</html>
