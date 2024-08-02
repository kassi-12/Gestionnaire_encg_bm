<?php

require '../../dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Initialize DOMPDF
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('isFontSubsettingEnabled', true);
$options->set('defaultFont', 'DejaVu Sans'); // Default font

$dompdf = new Dompdf($options);

// Load the HTML content
ob_start();
include 'generate_schedule_all.php'; // Path to the HTML content you want to convert
$html = ob_get_clean();
$dompdf->loadHtml($html);

// Set paper size and orientation
$dompdf->setPaper('A4', 'landscape');

// Render the PDF
$dompdf->render();

// Output the PDF for download
$dompdf->stream('schedule.pdf', array('Attachment' => true)); // Set 'Attachment' to false to view in browser
