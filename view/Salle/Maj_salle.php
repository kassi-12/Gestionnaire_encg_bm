<?php
include 'db_connect.php';

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
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="sidebar">
    <div class="logo">
        <img src="ENCG-BM_logo_header.png" width="200" alt="Logo">
    </div>
    <ul class="nav-links">
        <li><a href="#"><i class="icon-home"></i> Tableau de bord</a></li>
        <li><a href="#"><i class="icon-students"></i> Groupes</a></li>
        <li><a href="#"><i class="icon-teachers"></i> Professeur</a></li>
        <li class="dropdown">
            <a href="salle.html"><i class="icon-attendance"></i> Salles</a>
            <ul class="dropdown-content">
                <li><a href="Aj_salle.php">Ajouter une salle</a></li>
                <li><a href="Maj_salle.php">Mettre à jour les salles</a></li>
            </ul>
        </li>
        <li><a href="salle.html"><i class="icon-attendance"></i> Réserve</a></li>
        <li><a href="#"><i class="icon-logout"></i> Déconnexion</a></li>
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
