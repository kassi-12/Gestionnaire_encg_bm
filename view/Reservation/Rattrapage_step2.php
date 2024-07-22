<?php
include '../db/db_connect.php';

// Fetch POST data
$niveau = $_POST['Niveau'] ?? '';
$group_name = $_POST['GroupName'] ?? '';
$filiere = $_POST['Filiere'] ?? '';
$semester = $_POST['Semester'] ?? '';
$selected_day = $_POST['day'] ?? '';
$selected_timeslot = $_POST['time_slot'] ?? '';

// Initialize timeslots array
$timeslots = [];
if (!empty($selected_day)) {
    $sql_timeslots = "
        SELECT id, start_time, end_time 
        FROM reservation 
        WHERE jour_par_semaine = '$selected_day' AND semester_id = '$semester'
    ";
    $result_timeslots = $conn->query($sql_timeslots);

    if ($result_timeslots->num_rows > 0) {
        while ($row = $result_timeslots->fetch_assoc()) {
            $timeslots[] = $row;
        }
    }

    // Return timeslots as HTML options for AJAX
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax']) && $_POST['ajax'] == 'timeslot') {
        foreach ($timeslots as $slot) {
            echo '<option value="' . $slot['start_time'] . ' - ' . $slot['end_time'] . '" data-id="' . $slot['id'] . '">' . $slot['start_time'] . ' - ' . $slot['end_time'] . '</option>';
        }
        exit;
    }
}

// Fetch subjects based on the semester
$sql_subjects = "SELECT subject_id, subject_name FROM subjects WHERE semester_id = '$semester'";
$result_subjects = $conn->query($sql_subjects);
if (!$result_subjects) {
    die("Erreur lors de la récupération des matières : " . $conn->error);
}

// Fetch professors
$sql_professors = "SELECT id, CONCAT(first_name, ' ', last_name) AS professor_name FROM professeur";
$result_professors = $conn->query($sql_professors);
if (!$result_professors) {
    die("Erreur lors de la récupération des professeurs : " . $conn->error);
}

// Fetch reservations
$sql_reservations = "
    SELECT r.id, r.reservation_date, r.start_time, r.end_time, r.jour_par_semaine, s.name AS salle_name, 
           p.first_name, p.last_name, sub.subject_name
    FROM reservation r
    JOIN salles s ON r.salle_id = s.id
    JOIN professeur p ON r.professeur_id = p.id
    JOIN subjects sub ON r.subject_id = sub.subject_id
    JOIN grp g ON r.group_id = g.id
    WHERE g.year = '$niveau' AND g.name = '$group_name' AND r.semester_id = '$semester'
";

if (!empty($selected_timeslot)) {
    list($start_time, $end_time) = explode(' - ', $selected_timeslot);
    $sql_reservations .= " AND r.start_time = '$start_time' AND r.end_time = '$end_time'";
}

$result_reservations = $conn->query($sql_reservations);
if (!$result_reservations) {
    die("Erreur lors de la récupération des réservations : " . $conn->error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax']) && $_POST['ajax'] == 'reservations') {
    if ($result_reservations->num_rows > 0) {
        while ($row = $result_reservations->fetch_assoc()) {
            echo '<option value="' . $row['id'] . '">
                    Date: ' . $row['reservation_date'] . ', 
                    Heure: ' . $row['start_time'] . ' - ' . $row['end_time'] . ', 
                    Salle: ' . $row['salle_name'] . ', 
                    Professeur: ' . $row['first_name'] . ' ' . $row['last_name'] . ', 
                    Matière: ' . $row['subject_name'] . '
                  </option>';
        }
    } else {
        echo '<option value="">Aucune réservation disponible</option>';
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deuxième Étape de Réservation</title>
    <link rel="stylesheet" href="../../assets/styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#day').change(function() {
                var selectedDay = $(this).val();
                var semester = $('input[name="Semester"]').val();
                
                $.ajax({
                    type: 'POST',
                    url: '',
                    data: { day: selectedDay, Semester: semester, ajax: 'timeslot' },
                    success: function(response) {
                        $('#time_slot').html(response);
                    }
                });
            });

            $('#time_slot').change(function() {
                var selectedDay = $('#day').val();
                var selectedTimeslot = $(this).val();
                var niveau = $('input[name="Niveau"]').val();
                var groupName = $('input[name="GroupName"]').val();
                var filiere = $('input[name="Filiere"]').val();
                var semester = $('input[name="Semester"]').val();
                
                $.ajax({
                    type: 'POST',
                    url: '',
                    data: {
                        day: selectedDay,
                        time_slot: selectedTimeslot,
                        Niveau: niveau,
                        GroupName: groupName,
                        Filiere: filiere,
                        Semester: semester,
                        ajax: 'reservations'
                    },
                    success: function(response) {
                        $('#reservation').html(response);
                    }
                });
            });
        });
    </script>
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
            <h3>Deuxième Étape de Réservation Rattrapage</h3>
            <p class="required-fields">* Tous les champs sont obligatoires</p>
            
            <form action="" method="POST">
                <label for="day">Jour * :</label>
                <select id="day" name="day" required>
                    <option value="">-</option>
                    <option value="Lundi" <?php if ($selected_day == 'Lundi') echo 'selected'; ?>>Lundi</option>
                    <option value="Mardi" <?php if ($selected_day == 'Mardi') echo 'selected'; ?>>Mardi</option>
                    <option value="Mercredi" <?php if ($selected_day == 'Mercredi') echo 'selected'; ?>>Mercredi</option>
                    <option value="Jeudi" <?php if ($selected_day == 'Jeudi') echo 'selected'; ?>>Jeudi</option>
                    <option value="Vendredi" <?php if ($selected_day == 'Vendredi') echo 'selected'; ?>>Vendredi</option>
                    <option value="Samedi" <?php if ($selected_day == 'Samedi') echo 'selected'; ?>>Samedi</option>
                </select><br><br>

                <label for="time_slot">Heure * :</label>
                <select id="time_slot" name="time_slot" required>
                    <option value="">-</option>
                    <?php foreach ($timeslots as $slot): ?>
                        <option value="<?= $slot['start_time'] . ' - ' . $slot['end_time'] ?>" data-id="<?= $slot['id'] ?>">
                            <?= $slot['start_time'] . ' - ' . $slot['end_time'] ?>
                        </option>
                    <?php endforeach; ?>
                </select><br><br>

                <label for="reservation">Les Réservations * :</label>
                <select id="reservation" name="reservation" required>
                    <option value="">-</option>
                    <?php if ($result_reservations->num_rows > 0): ?>
                        <?php while ($row = $result_reservations->fetch_assoc()): ?>
                            <option value="<?= $row['id'] ?>">
                                Date: <?= $row['reservation_date'] ?>, 
                                Heure: <?= $row['start_time'] . ' - ' . $row['end_time'] ?>, 
                                Salle: <?= $row['salle_name'] ?>, 
                                Professeur: <?= $row['first_name'] . ' ' . $row['last_name'] ?>, 
                                Matière: <?= $row['subject_name'] ?>
                            </option>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <option value="">Aucune réservation disponible</option>
                    <?php endif; ?>
                </select><br><br>

                <input type="hidden" name="Niveau" value="<?= $niveau ?>">
                <input type="hidden" name="GroupName" value="<?= $group_name ?>">
                <input type="hidden" name="Filiere" value="<?= $filiere ?>">
                <input type="hidden" name="Semester" value="<?= $semester ?>">

                <button type="submit">Réserver</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
