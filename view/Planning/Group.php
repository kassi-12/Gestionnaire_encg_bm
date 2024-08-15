<?php
include '../db/db_connect.php';

// Fetch groups
$sql_groups = "SELECT DISTINCT year FROM grp";
$result_groups = $conn->query($sql_groups);

// Handle AJAX requests
if (isset($_GET['year'])) {
    $year = $conn->real_escape_string($_GET['year']);
    $sql_group_names = "SELECT DISTINCT name FROM grp WHERE year = '$year'";
    $result_group_names = $conn->query($sql_group_names);

    $group_names = array();
    while ($row = $result_group_names->fetch_assoc()) {
        $group_names[] = $row;
    }
    echo json_encode($group_names);
    $conn->close();
    exit;
}

if (isset($_GET['group_name'])) {
    $group_name = $conn->real_escape_string($_GET['group_name']);
    $sql_group_info = "SELECT year, filiere, extra_info FROM grp WHERE name = '$group_name'";
    $result_group_info = $conn->query($sql_group_info);

    $group_info = array();
    while ($row = $result_group_info->fetch_assoc()) {
        $group_info[] = $row;
    }
    echo json_encode($group_info);
    $conn->close();
    exit;
}

// Fetch types of seance
$sql_types_seance = "SELECT DISTINCT type_seance FROM reservation";
$result_types_seance = $conn->query($sql_types_seance);

$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Normal</title>
    <link rel="stylesheet" href="../../assets/styles.css">
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css'>

    <script>
        function updateGroupName() {
            var annee = document.getElementById('Niveau').value;
            var groupSelect = document.getElementById('GroupNameDropdown');
            groupSelect.innerHTML = '<option value="">-</option>';

            if (annee) {
                fetch('?year=' + annee)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(item => {
                            var option = document.createElement('option');
                            option.value = item.name;
                            option.textContent = item.name;
                            groupSelect.appendChild(option);
                        });
                        updateSemesters(annee);
                    });
            }
        }

        function updateFiliereOrSection() {
            var groupName = document.getElementById('GroupNameDropdown').value;
            var filiereSelect = document.getElementById('Filiere');
            filiereSelect.innerHTML = '<option value="">-</option>';

            if (groupName) {
                fetch('?group_name=' + groupName)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(item => {
                            var option = document.createElement('option');
                            var year = document.getElementById('Niveau').value;
                            if (year === "1er" || year === "2ème" || year === "3ème") {
                                option.value = item.extra_info;
                                option.textContent = item.extra_info;
                            } else if (year === "4ème" || year === "5ème") {
                                option.value = item.filiere;
                                option.textContent = item.filiere;
                            }
                            filiereSelect.appendChild(option);
                        });
                    });
            }
        }

        function updateSemesters(year) {
            var semesterSelect = document.getElementById('Semester');
            semesterSelect.innerHTML = '<option value="">-</option>';

            var semesters = [];
            if (year === "1er") {
                semesters = ["1", "2"];
            } else if (year === "2ème") {
                semesters = ["3", "4"];
            } else if (year === "3ème") {
                semesters = ["5", "6"];
            } else if (year === "4ème") {
                semesters = ["7", "8"];
            } else if (year === "5ème") {
                semesters = ["9", "10"];
            }

            semesters.forEach(semester => {
                var option = document.createElement('option');
                option.value = semester;
                option.textContent = 'Semestre ' + semester;
                semesterSelect.appendChild(option);
            });
        }
    </script>
</head>
<body>
<div class="sidebar">
    <div class="logo">
        <img src="../../image/ENCG-BM_logo_header.png" width="200" alt="Logo">
    </div>
    <ul class="nav-links">
        <li><a href="../dashboard/dashboard.php"><i class="fas fa-home"></i> Tableau de bord</a></li>
        <li><a href="../group/groups.php"><i class="fas fa-users"></i> Groupes</a></li>
        <li><a href="../professeur/professeur.php"><i class="fas fa-chalkboard-teacher"></i> Professeurs</a></li>
        <li><a href="../matier/matier.php"><i class="fas fa-book"></i> Matière</a></li> <!-- Changed icon to fa-book -->
        <li class="dropdown">
            <a href="../salle/salles.php"><i class="fas fa-building"></i> Salles</a> <!-- Changed icon to fa-building -->
            <ul class="dropdown-content">
                <li><a href="../salle/Aj_salle.php">Ajouter une salle</a></li>
                <li><a href="../salle/Maj_salle.php">Mettre à jour les salles</a></li>
            </ul>
        </li>
        <li class="dropdown">
            <a href="../reservation/Reserve.php"><i class="fas fa-calendar-check"></i> Réservation</a> <!-- Changed icon to fa-calendar-check -->
            <ul class="dropdown-content">
                <li><a href="../reservation/Evenement.php">Événement</a></li>
                <li><a href="../reservation/normal.php">Cours/Exam</a></li>
            </ul>
        </li>
        <li><a href="../rapport/rapports.php"><i class="fas fa-file-alt"></i> Rapport</a></li> <!-- Changed icon to fa-file-alt -->
        <li><a href="../planning/planning.php"><i class="fas fa-calendar"></i> Planning</a></li> <!-- Changed icon to fa-calendar -->
        <li><a href="#"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
    </ul>
</div>
    <div class="main-content">
        <h2>Planning</h2>
        <div class="container">
            <div class="add-classes">
                <h3>Formulaire de Planning</h3>
                <p class="required-fields">* Tous les champs sont obligatoires</p>
                
                <form action="generate_schedule.php" method="POST">
                    

                    <label for="Niveau">Niveau :</label>
                    <select id="Niveau" name="Niveau" onchange="updateGroupName()">
                        <option value="">-</option>
                        <?php
                        while ($row = $result_groups->fetch_assoc()) {
                            echo '<option value="' . $row['year'] . '">' . $row['year'] . " année" . '</option>';
                        }
                        ?>
                    </select><br><br>

                    <label for="GroupNameDropdown">Nom du Groupe :</label>
                    <select id="GroupNameDropdown" name="GroupNameDropdown" onchange="updateFiliereOrSection()">
                        <option value="">-</option>
                    </select><br><br>

                    <label for="Filiere">Filière/Section :</label>
                    <select id="Filiere" name="Filiere">
                        <option value="">-</option>
                    </select><br><br>

                    <label for="Semester">Semestre :</label>
                    <select id="Semester" name="Semester">
                        <option value="">-</option>
                    </select><br><br>
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
                                <input type="checkbox" id="soutenance" name="room-type[]" value="soutenance">
                                <label for="soutenance">Soutenance</label>
                            </div>
                            
                            
                        </div>
                    </fieldset>

                    

                    <button type="submit">Suivant</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
