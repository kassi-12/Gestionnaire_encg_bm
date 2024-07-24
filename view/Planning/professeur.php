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
                
                <form action="generate_schedule_prof.php" method="POST">
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
