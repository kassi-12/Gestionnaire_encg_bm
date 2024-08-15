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
        <img src="../../image/ENCG-BM_logo_header.png" width="200" alt="Logo">
        <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css'>
    </div>
    <ul class="nav-links">
        <li><a href="../dashboard/dashboard.php"><i class="fas fa-home"></i> Tableau de bord</a></li>
        <li><a href="../group/groups.php"><i class="fas fa-users"></i> Groupes</a></li>
        <li><a href="../professeur/professeur.php"><i class="fas fa-chalkboard-teacher"></i> Professeurs</a></li>
        <li><a href="../matier/matier.php"><i class="fas fa-book"></i> Matière</a></li>
        <li class="dropdown">
            <a href="../salle/salles.php"><i class="fas fa-building"></i> Salles</a>
            <ul class="dropdown-content">
                <li><a href="../salle/Aj_salle.php">Ajouter une salle</a></li>
                <li><a href="../salle/Maj_salle.php">Mettre à jour les salles</a></li>
            </ul>
        </li>
        <li class="dropdown">
            <a href="../reservation/Reserve.php"><i class="fas fa-calendar-check"></i> Réservation</a>
            <ul class="dropdown-content">
                <li><a href="../reservation/Evenement.php">Événement</a></li>
                <li><a href="../reservation/normal.php">Cours/Exam</a></li>
            </ul>
        </li>
        <li><a href="../rapport/rapports.php"><i class="fas fa-file-alt"></i> Rapport</a></li>
        <li><a href="../planning/planning.php"><i class="fas fa-calendar"></i> Planning</a></li>
        <li><a href="#"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
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
                    include '../db/db_connect.php';
                    // Fetch salles and semesters
                    $sql_salles = "SELECT id, name FROM salles";
                    $sql_semesters = "SELECT id, name FROM semesters";
                    $result_salles = $conn->query($sql_salles);
                    $result_semesters = $conn->query($sql_semesters);
                    
                    // Populate salle options
                    while ($row = $result_salles->fetch_assoc()) {
                        echo "<option value='" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($row['name']) . "</option>";
                    }
                    ?>
                </select><br><br>

                <label for="Semester">Semestre :</label>
                <select id="Semester" name="Semester">
                    <option value="">-</option>
                    <?php
                    // Populate semester options
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
                        
                        
                    </div>
                </fieldset>

                <button type="submit">Suivant</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
