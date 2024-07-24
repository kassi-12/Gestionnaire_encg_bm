<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation d'Événement</title>
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
