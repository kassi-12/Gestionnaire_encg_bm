<?php
include '../db/db_connect.php';


$sql_groups = "SELECT DISTINCT year FROM grp";
$result_groups = $conn->query($sql_groups);

$sql_semesters = "SELECT * FROM semesters";
$result_semesters = $conn->query($sql_semesters);

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

$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session De Rattrapage</title>
    <link rel="stylesheet" href="../../assets/styles.css">
    <script>
        function updateGroupName() {
            var annee = document.getElementById('Niveau').value;
            var groupSelect = document.getElementById('GroupName');
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
            var groupName = document.getElementById('GroupName').value;
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
        <div class="container">
            <div class="add-classes">
                <h3>Formulaire de Réservation</h3>
                <p class="required-fields">* Tous les champs sont obligatoires</p>
                
                <form action="Rattrapage_step2.php" method="POST">
                    <label for="Niveau">Niveau :</label>
                    <select id="Niveau" name="Niveau" onchange="updateGroupName()">
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

                    <label for="Filiere">Filière/Section :</label>
                    <select id="Filiere" name="Filiere">
                        <option value="">-</option>
                    </select><br><br>

                    <label for="Semester">Semestre :</label>
                    <select id="Semester" name="Semester">
                        <option value="">-</option>
                        <?php
                        while ($row = $result_semesters->fetch_assoc()) {
                            echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                        }
                        ?>
                    </select><br><br>

                    <button type="submit">Suivant</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
