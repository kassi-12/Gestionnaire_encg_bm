<?php
include '../db/db_connect.php';

$sql_groups = "SELECT DISTINCT year FROM grp";
$result_groups = $conn->query($sql_groups);

if (isset($_GET['year']) && isset($_GET['group'])) {
    $year = $conn->real_escape_string($_GET['year']);
    $group = $conn->real_escape_string($_GET['group']);
    $sql_group_info = "SELECT filiere, extra_info FROM grp WHERE year = '$year' AND name = '$group'";
    $result_group_info = $conn->query($sql_group_info);

    $group_info = array();
    while ($row = $result_group_info->fetch_assoc()) {
        $group_info[] = $row;
    }
    echo json_encode($group_info);
    $conn->close();
    exit;
}

if (isset($_GET['yearForGroups'])) {
    $year = $conn->real_escape_string($_GET['yearForGroups']);
    $sql_group_names = "SELECT DISTINCT name FROM grp WHERE year = '$year'";
    $result_group_names = $conn->query($sql_group_names);

    $group_names = array();
    while ($row = $result_group_names->fetch_assoc()) {
        $group_names[] = $row['name'];
    }
    echo json_encode($group_names);
    $conn->close();
    exit;
}

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
        function updateGroupInfo() {
            var annee = document.getElementById('Niveau').value;
            var groupNameSelect = document.getElementById('GroupName');
            groupNameSelect.innerHTML = '<option value="">-</option>';
            var filiereLabel = document.getElementById('FiliereLabel');
            var filiereDiv = document.getElementById('Filiere');
            filiereDiv.innerHTML = '';

            if (annee) {
                fetch('?yearForGroups=' + annee)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(item => {
                            var option = document.createElement('option');
                            option.value = item;
                            option.textContent = item;
                            groupNameSelect.appendChild(option);
                        });
                    });
            } else {
                filiereLabel.style.display = 'none';
                filiereDiv.innerHTML = '';
            }
        }

        function updateFiliereOrSection() {
            var annee = document.getElementById('Niveau').value;
            var group = document.getElementById('GroupName').value;
            var filiereLabel = document.getElementById('FiliereLabel');
            var filiereDiv = document.getElementById('Filiere');
            filiereDiv.innerHTML = '';

            if (annee && group) {
                fetch('?year=' + annee + '&group=' + group)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(item => {
                            var checkbox = document.createElement('input');
                            checkbox.type = 'checkbox';
                            checkbox.name = 'Filiere[]';
                            var label = document.createElement('label');
                            if (annee === "1er" || annee === "2ème" || annee === "3ème") {
                                checkbox.value = item.extra_info;
                                label.textContent = item.extra_info;
                            } else if (annee === "4ème" || annee === "5ème") {
                                checkbox.value = item.filiere;
                                label.textContent = item.filiere;
                            }

                            var div = document.createElement('div');
                            div.appendChild(checkbox);
                            div.appendChild(label);
                            filiereDiv.appendChild(div);
                        });
                        filiereLabel.style.display = 'block';
                    });
            } else {
                filiereLabel.style.display = 'none';
                filiereDiv.innerHTML = '';
            }
        }

        function updateSemesters() {
            var annee = document.getElementById('Niveau').value;
            var semesterSelect = document.getElementById('Semester');
            semesterSelect.innerHTML = '<option value="">-</option>';

            var semesters = [];
            if (annee === "1er") {
                semesters = ["1", "2"];
            } else if (annee === "2ème") {
                semesters = ["3", "4"];
            } else if (annee === "3ème") {
                semesters = ["5", "6"];
            } else if (annee === "4ème") {
                semesters = ["7", "8"];
            } else if (annee === "5ème") {
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
                <h3>Formulaire de Réservation</h3>
                <p class="required-fields">* Tous les champs sont obligatoires</p>
                
                <form action="step2.php" method="POST">
                    <label for="Niveau">Niveau :</label>
                    <select id="Niveau" name="Niveau" onchange="updateGroupInfo(); updateSemesters();">
                        <option value="">-</option>
                        <?php
                        while ($row = $result_groups->fetch_assoc()) {
                            echo '<option value="' . $row['year'] . '">' . $row['year'] . " année". '</option>';
                        }
                        ?>
                    </select><br><br>
                    <label for="GroupName">Nom du Groupe :</label>
                    <select id="GroupName" name="GroupName" onchange="updateFiliereOrSection()">
                        <option value="">-</option>
                    </select><br><br>
                    <fieldset id="FiliereLabel" style="display: none;">
                        <legend>Filière/Section :</legend>
                        <div id="Filiere"></div><br><br>
                    </fieldset>
                    <label for="Semester">Semestre :</label>
                    <select id="Semester" name="Semester">
                        <option value="">-</option>
                    </select><br><br>

                    <button type="submit">Suivant</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
