<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $numero_salle = isset($_POST['room-number']) ? $conn->real_escape_string($_POST['room-number']) : '';
    $capacite = isset($_POST['capacity']) ? $conn->real_escape_string($_POST['capacity']) : '';
    $capacity_exam = isset($_POST['capacity-exam']) ? $_POST['capacity-exam'] : '';
    $fonctionnalites = isset($_POST['features']) ? implode(',', $_POST['features']) : '';
    $fonctionnalites = $conn->real_escape_string($fonctionnalites);
    $room_type = isset($_POST['room-type']) ? implode(',', $_POST['room-type']) : '';
    $room_type = $conn->real_escape_string($room_type);

    
    $sql = "INSERT INTO salles (name, capacity, features, capacity_exam, room_type) 
            VALUES ('$numero_salle', '$capacite', '$fonctionnalites', '$capacity_exam', '$room_type')";

    if ($conn->query($sql) === TRUE) {
        echo "Nouvelle salle ajoutée avec succès";
    } else {
        echo "Erreur : " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>




<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord</title>
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
                <h3>Veuillez remplir ce formulaire pour ajouter une nouvelle salle :</h3>
                <p class="required-fields">* Tous les champs sont obligatoires</p>
                <form action="Aj_salle.php" method="post">
                    <label for="room-number">Numéro de la salle * :</label>
                    <input type="text" id="room-number" name="room-number" required>

                    <label for="capacity">Capacité * :</label>
                    <input type="number" id="capacity" name="capacity" required>

                    <label for="capacity-exam">Capacité pour Exam / Controle :</label>
                    <input type="number" id="capacity-exam" name="capacity-exam">

                    <fieldset>
                        <legend>Fonctionnalités * :</legend>
                        <div class="checkbox-group">
                            <div class="checkbox-item">
                                <input type="checkbox" id="internet" name="features[]" value="Accès à l'internet">
                                <label for="internet">Accès à l'internet</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="network" name="features[]" value="Équipements du réseau">
                                <label for="network">Équipements du réseau</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="projector" name="features[]" value="Vidéo projecteur">
                                <label for="projector">Vidéo projecteur</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="ac" name="features[]" value="Climatiseur">
                                <label for="ac">Climatiseur</label>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Type de séance * :</legend>
                        <div class="checkbox-group">
                            <div class="checkbox-item">
                                <input type="checkbox" id="cours" name="room-type[]" value="cours">
                                <label for="cours">Cours</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="td" name="room-type[]" value="TD">
                                <label for="td">TD</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="tp" name="room-type[]" value="TP">
                                <label for="tp">TP</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="controle" name="room-type[]" value="controle">
                                <label for="controle">Contrôle</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="rattrapage" name="room-type[]" value="rattrapage-cours">
                                <label for="rattrapage">Rattrapage de cours</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="soutenance" name="room-type[]" value="soutenance">
                                <label for="soutenance">Soutenance</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="evenement" name="room-type[]" value="evenement">
                                <label for="evenement">Événement</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="exam" name="room-type[]" value="exam">
                                <label for="exam">Examen</label>
                            </div>
                        </div>
                    </fieldset>

                    <button type="submit">Valider & Ajouter</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
