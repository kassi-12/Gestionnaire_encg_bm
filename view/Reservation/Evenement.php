<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation d'Événement</title>
    <link rel="stylesheet" href="../../assets/styles.css">
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
            <li class="dropdown">
                <a href="Reserve.php"><i class="icon-attendance"></i> Réserve</a>
                <ul class="dropdown-content">
                    <li><a href="Evenement.php">Événement</a></li>
                    <li><a href="Rattrapage.php">Rattrapage</a></li>
                    <li><a href="normal.php">Cours/Exam</a></li>
                </ul>
            </li>
            <li><a href="#"><i class="icon-logout"></i> Déconnexion</a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="container">
            <div class="add-classes">
                <h3>Formulaire de Réservation d'Événement</h3>
                <p class="required-fields">* Tous les champs sont obligatoires</p>
                
                <form action="Evenement_step2.php" method="POST">
                    <label for="EventName">Nom de l'Événement :</label>
                    <input type="text" id="EventName" name="EventName" required><br><br>

                    <label for="NumberOfPeople">Nombre de personnes :</label>
                    <input type="number" id="NumberOfPeople" name="NumberOfPeople" min="0" required><br><br>

                    <label for="EventDate">Date de l'Événement :</label>
                    <input type="date" id="EventDate" name="EventDate" required><br><br>

                    <label for="Organizer">Organisateur :</label>
                    <input type="text" id="Organizer" name="Organizer"><br><br>

                    <button type="submit">Suivant</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
