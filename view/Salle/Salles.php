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
            <h2>Today Attendance</h2>
            <table>
                <thead>
                    <tr>
                        <th>Salle</th>
                        <th>Salle</th>
                        <th>Professeur</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Attendance data will go here -->
                </tbody>
            </table>
            <button>View All</button>
        </section>
        <section class="teachers">
            <!-- Teachers data will go here -->
        </section>
    </div>
</body>
</html>
