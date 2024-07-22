<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Normal</title>
    <link rel="stylesheet" href="../../assets/styles.css">
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
                
                <form action="generate_schedule_salle.php" method="POST">
                    
                    <label for="Niveau">Salle :</label>
                    <select id="Niveau" name="Niveau">
                        <option value="">-</option>
                        <?php
                        // Populate salle options
                        include '../db/db_connect.php';
                        $sql_salles = "SELECT id, name FROM salles";
                        $result_salles = $conn->query($sql_salles);
                        while ($row = $result_salles->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($row['name']) . "</option>";
                        }
                        $conn->close();
                        ?>
                    </select><br><br>

                    <label for="Semester">Semestre :</label>
                    <select id="Semester" name="Semester">
                        <option value="">-</option>
                        <?php
                        // Populate semester options
                        include '../db/db_connect.php';
                        $sql_semesters = "SELECT id, name FROM semesters";
                        $result_semesters = $conn->query($sql_semesters);
                        while ($row = $result_semesters->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($row['name']) . "</option>";
                        }
                        $conn->close();
                        ?>
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
                            
                            <div class="checkbox-item">
                                <input type="checkbox" id="exam" name="room-type[]" value="exam">
                                <label for="exam">Examen</label>
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
