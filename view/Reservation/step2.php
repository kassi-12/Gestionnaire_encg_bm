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
