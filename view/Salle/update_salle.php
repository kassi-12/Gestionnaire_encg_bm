<?php
include 'db_connect.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : '';

$sql = "SELECT * FROM salles WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Aucune salle trouvée pour cet ID.";
    exit;
}

$room = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $numero_salle = isset($_POST['room-number']) ? $conn->real_escape_string($_POST['room-number']) : '';
    $capacite = isset($_POST['capacity']) ? $conn->real_escape_string($_POST['capacity']) : '';
    $capacite_exam = isset($_POST['capacity-exam']) ? $conn->real_escape_string($_POST['capacity-exam']) : NULL;
    $fonctionnalites = isset($_POST['features']) ? implode(',', $_POST['features']) : '';
    $room_types = isset($_POST['room-type']) ? implode(',', $_POST['room-type']) : '';

    $sql = "UPDATE salles SET name=?, capacity=?, capacity_exam=?, features=?, room_type=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sisssi', $numero_salle, $capacite, $capacite_exam, $fonctionnalites, $room_types, $id);

    if ($stmt->execute()) {
        echo "Salle mise à jour avec succès";
    } else {
        echo "Erreur : " . $stmt->error;
    }
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mettre à jour la salle</title>
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
    <div class="container">
        <div class="add-classes">
            <h3>Mettre à jour la salle</h3>
            <form action="update_salle.php?id=<?php echo $id; ?>" method="post">
                <label for="room-number">Numéro de la salle * :</label>
                <input type="text" id="room-number" name="room-number" value="<?php echo htmlspecialchars($room['name']); ?>" required>

                <label for="capacity">Capacité * :</label>
                <input type="number" id="capacity" name="capacity" value="<?php echo htmlspecialchars($room['capacity']); ?>" required>

                <label for="capacity-exam">Capacité pour Exam / Controle :</label>
                <input type="number" id="capacity-exam" name="capacity-exam" value="<?php echo htmlspecialchars($room['capacity_exam']); ?>">

                <fieldset>
                    <legend>Type de séance * :</legend>
                    <div class="checkbox-group">
                        <?php
                        $room_types = explode(',', $room['room_type']);
                        $types = ['cours', 'TD', 'TP', 'controle', 'rattrapage-cours', 'soutenance', 'evenement', 'exam'];
                        foreach ($types as $type) {
                            $checked = in_array($type, $room_types) ? 'checked' : '';
                            echo "<div class='checkbox-item'>
                                    <input type='checkbox' id='$type' name='room-type[]' value='$type' $checked>
                                    <label for='$type'>" . ucfirst($type) . "</label>
                                  </div>";
                        }
                        ?>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Fonctionnalités * :</legend>
                    <?php
                    $features = explode(',', $room['features']);
                    ?>
                    <label><input type="checkbox" name="features[]" value="Accès à l'internet" <?php echo in_array('Accès à l\'internet', $features) ? 'checked' : ''; ?>> Accès à l'internet</label>
                    <label><input type="checkbox" name="features[]" value="Équipements du réseau" <?php echo in_array('Équipements du réseau', $features) ? 'checked' : ''; ?>> Équipements du réseau</label>
                    <label><input type="checkbox" name="features[]" value="Vidéo projecteur" <?php echo in_array('Vidéo projecteur', $features) ? 'checked' : ''; ?>> Vidéo projecteur</label>
                    <label><input type="checkbox" name="features[]" value="Climatiseur" <?php echo in_array('Climatiseur', $features) ? 'checked' : ''; ?>> Climatiseur</label>
                </fieldset>

                <button type="submit">Mettre à jour</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
