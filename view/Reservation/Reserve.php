<?php
include '../db/db_connect.php';
require('../../fpdf/fpdf.php');

// Initialize the date filter
$date_filter = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$day_of_week = '';
$salle_filter = isset($_GET['salle']) ? $_GET['salle'] : '';

// Fetch the current day of the week in French if no date is selected
if (isset($_POST["reservation_date"])) {
    $date_filter = $_POST["reservation_date"];
    $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE);
    $formatter->setPattern('EEEE');
    $day_of_week = $formatter->format(strtotime($date_filter));
} else {
    $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE);
    $formatter->setPattern('EEEE');
    $day_of_week = $formatter->format(time());
}

// Handle the deletion of a reservation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete"])) {
    $reservation_id = $_POST["reservation_id"];
    $delete_sql = "DELETE FROM reservation WHERE id = $reservation_id";
    if ($conn->query($delete_sql) === TRUE) {
        echo "Réservation supprimée avec succès";
    } else {
        echo "Erreur lors de la suppression de la réservation: " . $conn->error;
    }
}

// Handle the filter for salle
if (isset($_POST["salle_name"]) && !empty($_POST["salle_name"])) {
    $salle_filter = $_POST["salle_name"];
}

if (!empty($salle_filter)) {
    $sql = "SELECT r.id, s.name AS salle_name, r.start_time, r.end_time, p.first_name, p.last_name, r.jour_par_semaine
            FROM reservation r
            JOIN salles s ON r.salle_id = s.id
            JOIN professeur p ON r.professeur_id = p.id
            WHERE r.jour_par_semaine = '$day_of_week' AND s.name = '$salle_filter'";
} else {
    $sql = "SELECT r.id, s.name AS salle_name, r.start_time, r.end_time, p.first_name, p.last_name, r.jour_par_semaine
            FROM reservation r
            JOIN salles s ON r.salle_id = s.id
            JOIN professeur p ON r.professeur_id = p.id
            WHERE r.jour_par_semaine = '$day_of_week'";
}

$result = $conn->query($sql);

// Handle the PDF generation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["enregistrer"])) {
    class PDF extends FPDF {
        public $header_title;

        function __construct($header_title) {
            parent::__construct();
            $this->header_title = $header_title;
        }

        // Page header
        function Header() {
            $this->SetFont('Arial', 'B', 12);
            $this->Cell(0, 10, $this->header_title, 0, 1, 'C');
            $this->Ln(10);
        }

        // Page footer
        function Footer() {
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 8);
            $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
        }

        // Load data
        function LoadData($result) {
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            return $data;
        }

        // Simple table
        function BasicTable($header, $data) {
            // Calculate total table width
            $tableWidth = 0;
            foreach ($header as $col) {
                $tableWidth += 40; // Width of each column
            }

            // Set the X position for the first cell to center the table
            $this->SetX(($this->GetPageWidth() - $tableWidth) / 2);

            // Header
            foreach ($header as $col) {
                $this->Cell(40, 7, $col, 1, 0, 'C');
            }
            $this->Ln();

            // Data
            foreach ($data as $row) {
                $this->SetX(($this->GetPageWidth() - $tableWidth) / 2); // Reset X position for each row
                $this->Cell(40, 6, $row['salle_name'], 1, 0, 'C');
                $this->Cell(40, 6, $row['start_time'], 1, 0, 'C');
                $this->Cell(40, 6, $row['end_time'], 1, 0, 'C');
                $this->Cell(40, 6, $row['first_name'] . ' ' . $row['last_name'], 1, 0, 'C');
                $this->Cell(40, 6, $row['jour_par_semaine'], 1, 0, 'C');
                $this->Ln();
            }
        }
    }

    // Clean output buffer to avoid errors
    ob_clean();

    $header_title = "Salle " . $salle_filter;
    $pdf = new PDF($header_title);
    $header = ['Salle', 'Heure de debut', 'Heure de fin', 'Professeur', 'Jour de la semaine'];
    $data = $pdf->LoadData($result);
    $pdf->SetFont('Arial', '', 12);
    $pdf->AddPage();
    $pdf->BasicTable($header, $data);
    $pdf->Output('D', 'reservations.pdf');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord</title>
    <link rel="stylesheet" href="../../assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <img src="ENCG-BM_logo_header.png" width="200" alt="Logo">
        </div>
        <ul class="nav-links">
            <li><a href="#"><i class="fas fa-home"></i> Tableau de bord</a></li>
            <li><a href="#"><i class="fas fa-users"></i> Groupes</a></li>
            <li><a href="#"><i class="fas fa-chalkboard-teacher"></i> Professeurs</a></li>
            <li class="dropdown">
                <a href="Reserve.php"><i class="icon-attendance"></i> Réserve</a>
                <ul class="dropdown-content">
                    <li><a href="Evenement.php">Événement</a></li>
                    <li><a href="Rattrapage.php">Rattrapage</a></li>
                    <li><a href="normal.php">Cours/Exam</a></li>
                </ul>
            </li>
            <li><a href="salle.html"><i class="fas fa-calendar-alt"></i> Réserver</a></li>
            <li><a href="#"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
        </ul>
    </div>
    <div class="main-content">
        <section class="attendance">
            <form method="POST" action="">
                <label for="reservation_date">Sélectionnez la date :</label>
                <input type="date" id="reservation_date" name="reservation_date" value="<?php echo $date_filter; ?>" required>
                <label for="salle_name">Sélectionnez la salle :</label>
                <select id="salle_name" name="salle_name">
                    <?php
                    $salles_sql = "SELECT name FROM salles";
                    $salles_result = $conn->query($salles_sql);
                    while ($salle = $salles_result->fetch_assoc()) {
                        // Check if the current salle is selected
                        $selected = ($salle_filter == $salle['name']) ? 'selected' : '';
                        echo "<option value='" . $salle['name'] . "' $selected>" . $salle['name'] . "</option>";
                    }
                    ?>
                </select>
                <button type="submit"><i class="fas fa-filter"></i></button>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>Salle</th>
                        <th>Heure de début</th>
                        <th>Heure de fin</th>
                        <th>Professeur</th>
                        <th>Jour de la semaine</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row["salle_name"] . "</td>";
                            echo "<td>" . $row["start_time"] . "</td>";
                            echo "<td>" . $row["end_time"] . "</td>";
                            echo "<td>" . $row["first_name"] . " " . $row["last_name"] . "</td>";
                            echo "<td>" . $row["jour_par_semaine"] . "</td>";
                            echo "<td>
                                    <form method='POST' action=''>
                                        <input type='hidden' name='reservation_id' value='" . $row["id"] . "'>
                                        <button type='submit' name='delete' class='delete-btn'><i class='fas fa-trash'></i></button>
                                    </form>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>Aucune réservation trouvée pour ce jour.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
            <form method="POST" action="">
                <input type="hidden" name="reservation_date" value="<?php echo $date_filter; ?>">
                <input type="hidden" name="salle_name" value="<?php echo $salle_filter; ?>">
                <button type="submit" name="enregistrer"><i class="fas fa-save"></i> Enregistrer</button>
            </form>
        </section>
    </div>
</body>
</html>
