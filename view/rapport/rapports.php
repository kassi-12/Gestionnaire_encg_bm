<?php
include '../db/db_connect.php';

// Fetch professors for the filter
$professor_sql = "SELECT id, CONCAT(first_name, ' ', last_name) AS full_name FROM professeur";
$professor_result = $conn->query($professor_sql);

// Filter by professor if selected
$professor_filter = isset($_POST['professor_id']) ? $_POST['professor_id'] : '';

// SQL query to fetch all records from the rapport table
$sql = "SELECT r.id, r.reservation_id, r.motif, r.rapport_date, r.statut, s.name AS salle_name, 
               res.start_time, res.end_time, p.first_name, p.last_name
        FROM rapport r
        JOIN reservation res ON r.reservation_id = res.id
        JOIN salles s ON res.salle_id = s.id
        JOIN professeur p ON res.professeur_id = p.id";

// Add filter condition if a professor is selected
if ($professor_filter) {
    $sql .= " WHERE p.id = ?";
}

// Prepare and execute the query
$stmt = $conn->prepare($sql);
if ($professor_filter) {
    $stmt->bind_param("i", $professor_filter);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord</title>
    <link rel="stylesheet" href="../../assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
            <form method="POST" action="">
                <label for="professor_id">Sélectionnez un professeur :</label>
                <select id="professor_id" name="professor_id">
                    <option value="">Tous les professeurs</option>
                    <?php
                    while ($professor = $professor_result->fetch_assoc()) {
                        $selected = ($professor_filter == $professor['id']) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($professor['id']) . "' $selected>" . htmlspecialchars($professor['full_name']) . "</option>";
                    }
                    ?>
                </select>
                <button type="submit"><i class="fas fa-filter"></i> Filtrer</button>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>Salle</th>
                        <th>Heure de début</th>
                        <th>Heure de fin</th>
                        <th>Professeur</th>
                        <th>Date de séance</th>
                        <th>Motif</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['salle_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['start_time']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['end_time']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['rapport_date']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['motif']) . "</td>";
                        
                        echo "<td>" . htmlspecialchars($row['statut']) . "</td>";
                        echo "<td>
                                    <form method='POST' action='../rattrapage/Rattrapage.php' style='display:inline;'>
                                        <input type='hidden' name='reservation_id' value='" . htmlspecialchars($row["reservation_id"]) . "'>
                                        <button type='submit' name='rapport' class='rapport-btn'><i class='fas fa-calendar-day'></i></button>
                                    </form>
                                  </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </div>
</body>
</html>

<?php
// Close the database connection
$stmt->close();
$conn->close();
?>
