<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Normal</title>
    <link rel="stylesheet" href="../../assets/styles.css">
    <script>
        function redirectToPage() {
            var selectedOption = document.getElementById('GroupNameType').value;
            if (selectedOption === "Salle") {
                window.location.href = "salle.php";
            } else if (selectedOption === "Group") {
                window.location.href = "group.php";
            } else if (selectedOption === "Professeur") {
                window.location.href = "professeur.php";
            }
        }
    </script>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <img src="image\ENCG-BM_logo_header.png" width="200" alt="Logo">
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
        <h2>Planning</h2>
        <div class="container">
            <div class="add-classes">
                <h3>Formulaire de Planning</h3>
                <p class="required-fields">* Tous les champs sont obligatoires</p>
                
                <form action="step2.php" method="POST">
                <label for="TypeSeance">Planning Pour :</label>
                    <select id="GroupNameType" name="GroupNameType" onchange="redirectToPage()">
                        <option value="">-</option>
                        <option value="Salle">Salle</option>
                        <option value="Group">Group</option>
                        <option value="Professeur">Professeur</option>
                    </select><br><br>
                
                    
                </form>
            </div>
        </div>
    </div>
</body>
</html>
