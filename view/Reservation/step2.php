<?php
include '../db/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $niveau = isset($_POST['Niveau']) ? $_POST['Niveau'] : '';
    $group_name = isset($_POST['GroupName']) ? $_POST['GroupName'] : '';
    $filiere = isset($_POST['Filiere']) ? $_POST['Filiere'] : '';
    $semester = isset($_POST['Semester']) ? $_POST['Semester'] : '';

    $sql_subjects = "SELECT subject_id, subject_name FROM subjects where semester_id = '$semester'";
    $result_subjects = $conn->query($sql_subjects);
    
    if (!$result_subjects) {
        die("Erreur lors de la récupération des matières : " . $conn->error);
    }

    $sql_professors = "SELECT id, CONCAT(first_name, ' ', last_name) AS professor_name FROM professeur";
    $result_professors = $conn->query($sql_professors);
    
    if (!$result_professors) {
        die("Erreur lors de la récupération des professeurs : " . $conn->error);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deuxième Étape de Réservation</title>
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
            <h3>Deuxième Étape de Réservation</h3>
            <p class="required-fields">* Tous les champs sont obligatoires</p>
            
            <form action="step3.php" method="POST">
                <label for="room-type">Type de séance * :</label>
                <select id="room-type" name="room-type" required>
                    <option value="">-</option>
                    <option value="cours">Cours</option>
                    <option value="TD">TD</option>
                    <option value="TP">TP</option>
                    <option value="controle">Contrôle</option>
                    <option value="soutenance">Soutenance</option>
                    <option value="exam">Examen</option>
                </select><br><br>

                <label for="Matier">Matière :</label>
                <select id="Matier" name="Matier">
                    <option value="">-</option>
                    <?php
                    if ($result_subjects->num_rows > 0) {
                        while ($row = $result_subjects->fetch_assoc()) {
                            echo '<option value="' . $row['subject_id'] . '">' . $row['subject_name'] . '</option>';
                        }
                    } else {
                        echo '<option value="">Aucune matière disponible</option>';
                    }
                    ?>
                </select><br><br>

                <label for="Prof">Professeur :</label>
                <select id="Prof" name="Prof">
                    <option value="">-</option>
                    <?php
                    if ($result_professors->num_rows > 0) {
                        while ($row = $result_professors->fetch_assoc()) {
                            echo '<option value="' . $row['id'] . '">' . $row['professor_name'] . '</option>';
                        }
                    } else {
                        echo '<option value="">Aucun professeur disponible</option>';
                    }
                    ?>
                </select><br><br>

                <label for="date_debut">Date * :</label>
                <input type="date" id="date_debut" name="date_debut" required><br><br>

                

                <button type="submit">Suivant</button>
                
           
                <input type="hidden" name="Niveau" value="<?php echo htmlspecialchars($niveau); ?>">
                <input type="hidden" name="GroupName" value="<?php echo htmlspecialchars($group_name); ?>">
                <input type="hidden" name="Filiere" value="<?php echo htmlspecialchars($filiere); ?>">
                <input type="hidden" name="Semester" value="<?php echo htmlspecialchars($semester); ?>">
            </form>
        </div>
    </div>
</div>
</body>
</html>
