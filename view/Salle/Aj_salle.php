<?php
include '../db/db_connect.php';

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
