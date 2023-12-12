<?php
require('fpdf/fpdf.php');

function generatePDF($teacherName, $teacherId, $schedules)
{
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 14);

    $pdf->Cell(0, 10, $teacherName, 0, 1, 'C');
    $pdf->Cell(0, 0, 'RFID Number: ' . $teacherId, 0, 1, 'C');

    // Add a line break to create some space
    $pdf->Ln(10);

    $pdf->SetFont('Arial', 'B', 10);

    $pdf->SetFillColor(0, 100, 0);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(55, 10, 'Scheduled Time In', 1, 0, 'C', true);
    $pdf->Cell(55, 10, 'Scheduled Time Out', 1, 0, 'C', true);
    $pdf->Cell(40, 10, 'Day', 1, 0, 'C', true);
    $pdf->Cell(40, 10, 'Room', 1, 1, 'C', true);

    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', '', 10);

    foreach ($schedules as $schedule) {
        $pdf->Cell(55, 10, $schedule['scheduledtimein'], 1, 0, 'C');
        $pdf->Cell(55, 10, $schedule['scheduledtimeout'], 1, 0, 'C');
        $pdf->Cell(40, 10, $schedule['day'], 1, 0, 'C');
        $pdf->Cell(40, 10, $schedule['room'], 1, 1, 'C');
    }

    // Include the teacher's name in the filename without timestamp
    $pdfFileName = $teacherName . '_schedule.pdf';
    $pdf->Output($pdfFileName, 'F');

    return $pdfFileName;
}
?>
