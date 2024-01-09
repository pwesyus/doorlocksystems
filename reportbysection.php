<?php

function generateSectionPDF($section, $schedules){

        require('fpdf/fpdf.php');
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 22);

    $pdf->Cell(0, 10, $section, 0, 1, 'C');

    // Add a line break to create some space
    $pdf->Ln(10);

    $pdf->SetFont('Arial', 'B', 10);

    $pdf->SetFillColor(0, 100, 0);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(40, 10, 'Scheduled Time In', 1, 0, 'C', true);
    $pdf->Cell(40, 10, 'Scheduled Time Out', 1, 0, 'C', true);
    $pdf->Cell(25, 10, 'Subject', 1, 0, 'C', true);
    $pdf->Cell(25, 10, 'Day', 1, 0, 'C', true);
    $pdf->Cell(25, 10, 'Room', 1, 0, 'C', true);
    $pdf->Cell(35, 10, 'Teacher', 1, 1, 'C', true);

    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', '', 10);

    foreach ($schedules as $schedule) {  // Change variable name here
        $pdf->Cell(40, 10, $schedule['scheduledtimein'], 1, 0, 'C');  // Use $schedule instead of $section
        $pdf->Cell(40, 10, $schedule['scheduledtimeout'], 1, 0, 'C');
        $pdf->Cell(25, 10, $schedule['subject'], 1, 0, 'C');
        $pdf->Cell(25, 10, $schedule['day'], 1, 0, 'C');
        $pdf->Cell(25, 10, $schedule['room'], 1, 0, 'C');
        $pdf->Cell(35, 10, $schedule['name'], 1, 1, 'C');
    }

    // Include the teacher's name in the filename without timestamp
    $pdfFileName = $section . '_schedule.pdf';
    $pdf->Output($pdfFileName, 'I');

    return $pdfFileName;
}
?>
