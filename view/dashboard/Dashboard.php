<?php
// Include the database connection and archiving function
include '../db/db_connect.php';

session_start();

// Call the archiving function once per session
if (!isset($_SESSION['last_archived']) || (time() - $_SESSION['last_archived']) > 86400) { // 86400 seconds = 24 hours
    archivePastRattrapages($conn);
    $_SESSION['last_archived'] = time();
}

// Fetch number of Salles
$result = $conn->query("SELECT COUNT(*) AS count FROM salles");
$row = $result->fetch_assoc();
$numberOfSalles = $row['count'];

// Fetch number of Groups
$result = $conn->query("SELECT COUNT(*) AS count FROM grp");
$row = $result->fetch_assoc();
$numberOfGroups = $row['count'];

// Fetch number of Professeurs
$result = $conn->query("SELECT COUNT(*) AS count FROM professeur");
$row = $result->fetch_assoc();
$numberOfProfesseurs = $row['count'];



// Fetch top 4 Salles data
$result = $conn->query("SELECT name, capacity, capacity_exam FROM salles LIMIT 3");
$sallesData = $result->fetch_all(MYSQLI_ASSOC);

// Fetch top 4 Professeur data
$result = $conn->query("SELECT first_name, last_name, email, gsm FROM professeur LIMIT 3");
$professeurData = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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
        <header>
            <div class="card">
                <h3><?php echo $numberOfSalles; ?></h3>
                <p>Salles</p>
            </div>
            <div class="card">
                <h3><?php echo $numberOfGroups; ?></h3>
                <p>Groups</p>
            </div>
            <div class="card">
                <h3><?php echo $numberOfProfesseurs; ?></h3>
                <p>Professeur</p>
            </div>
            
        </header>
        <section class="attendance">
            <h2>salles</h2>
            <table>
                <thead>
                    <tr>
                        <th>Salle Name</th>
                        <th>Capacity</th>
                        <th>Capacity Exam/Control</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sallesData as $salle): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($salle['name']); ?></td>
                        <td><?php echo htmlspecialchars($salle['capacity']); ?></td>
                        <td><?php echo htmlspecialchars($salle['capacity_exam']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button onclick="window.location.href='../salle/salles.php'">View All</button>

        </section>
        <section class="attendance">
            <h2>Professeurs</h2>
            <table>
                <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>GSM</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($professeurData as $professeur): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($professeur['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($professeur['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($professeur['email']); ?></td>
                        <td><?php echo htmlspecialchars($professeur['gsm']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button onclick="window.location.href='../professeur/professeur.php'">View All</button>
        </section>
    </div>
</body>
</html>
