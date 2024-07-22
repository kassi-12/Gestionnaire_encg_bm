<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Normal</title>
    <link rel="stylesheet" href="../../assets/styles.css">
    <script>
        function updateGroupName() {
            var niveau = document.getElementById('Niveau').value;
            var groupSelect = document.getElementById('GroupNameDropdown');
            groupSelect.innerHTML = '<option value="">-</option>';

            if (niveau) {
                fetch('?year=' + niveau)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(item => {
                            var option = document.createElement('option');
                            option.value = item.name;
                            option.textContent = item.name;
                            groupSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error fetching group names:', error));
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
                            var niveau = document.getElementById('Niveau').value;
                            if (niveau === "1er" || niveau === "2ème" || niveau === "3ème") {
                                option.value = item.extra_info;
                                option.textContent = item.extra_info;
                            } else if (niveau === "4ème" || niveau === "5ème") {
                                option.value = item.filiere;
                                option.textContent = item.filiere;
                            }
                            filiereSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error fetching filiere or section:', error));
            }
        }
    </script>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <img src="image/ENCG-BM_logo_header.png" width="200" alt="Logo">
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
                
                <form action="generate_schedule_salle.php" method="POST">
                    <label for="Niveau">Professeur :</label>
                    <select id="Niveau" name="Niveau" onchange="updateGroupName()">
                        <option value="">-</option>
                        <?php
                        // Populate professeur options
                        include '../db/db_connect.php';
                        $sql_profs = "SELECT id, CONCAT(first_name, ' ', last_name) AS name FROM professeur";
                        $result_profs = $conn->query($sql_profs);
                        while ($row = $result_profs->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($row['name']) . "</option>";
                        }
                        ?>
                    </select><br><br>

                    <label for="Semester">Semestre :</label>
                    <select id="Semester" name="Semester">
                        <option value="">-</option>
                        <?php
                        // Populate semester options
                        $sql_semesters = "SELECT id, name FROM semesters";
                        $result_semesters = $conn->query($sql_semesters);
                        while ($row = $result_semesters->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($row['name']) . "</option>";
                        }
                        $conn->close();
                        ?>
                    </select><br><br>
                    
                    <button type="submit">Suivant</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
