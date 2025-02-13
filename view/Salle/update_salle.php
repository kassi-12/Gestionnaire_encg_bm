<?php
include '../db/db_connect.php';

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
