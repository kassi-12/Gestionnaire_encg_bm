<?php
include '../db/db_connect.php';

if (isset($_POST['delete']) && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $sql = "DELETE FROM salles WHERE id = $id";
    if ($conn->query($sql) === TRUE) {
        echo "Salle supprimée avec succès";
    } else {
        echo "Erreur lors de la suppression de la salle: " . $conn->error;
    }
}

$sql = "SELECT * FROM salles";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mettre à jour les salles</title>
    <link rel="stylesheet" href="../../assets/styles.css">
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css'>

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
    <div class="attendance">
        <h3>Mettre à jour les salles</h3>
        <table>
            <thead>
                <tr>
                    <th>Salle</th>
                    <th>Capacité</th>
                    <th>Capacité Exam/Controle</th>
                    
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $features = explode(',', $row['features']);
                        $internet_checked = in_array("Accès à l'internet", $features) ? 'checked' : '';
                        $network_checked = in_array('Équipements du réseau', $features) ? 'checked' : '';
                        $projector_checked = in_array('Vidéo projecteur', $features) ? 'checked' : '';
                        $ac_checked = in_array('Climatiseur', $features) ? 'checked' : '';
                        echo "<tr>
                                <td>{$row['name']}</td>
                                <td>{$row['capacity']}</td>
                                <td>{$row['capacity_exam']}</td>
                               
                                <td>
                                    <form action='update_salle.php' method='get' style='display:inline-block;'>
                                        <input type='hidden' name='id' value='{$row['id']}'>
                                        <button type='submit'>Mettre à jour</button>
                                    </form>
                                    <form action='' method='post' style='display:inline-block;'>
                                        <input type='hidden' name='id' value='{$row['id']}'>
                                        <button type='submit' name='delete' onclick='return confirm(\"Êtes-vous sûr de vouloir supprimer cette salle?\")'>Supprimer</button>
                                    </form>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>Aucune salle trouvée</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>

<?php
$conn->close();
?>
