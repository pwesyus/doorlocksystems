<?php

function generatePDF($teacherName, $teacherId, $schedules){
    require('fpdf/fpdf.php');
    $pdf = new FPDF();
    $pdf->AddPage();

     $pdf->SetFont('Arial', 'B', 12);
    // Add logos on both sides
    $pdf->Image('images/cvsu.png', 30, 18, 25);
    $pdf->Image('images/bagongpilipinas.png', 155, 18, 25);

    // Add header information in the middle
    $pdf->SetY(15); // Adjust Y coordinate to position the header
    $pdf->Cell(0, 5, 'Republic of the Philippines', 0, 1, 'C');
    $pdf->Cell(0, 5, 'CAVITE STATE UNIVERSITY', 0, 1, 'C');
    $pdf->Cell(0, 5, 'Imus Campus', 0, 1, 'C');
    $pdf->Cell(0, 5, 'Cavite Civic Center Palico IV, Imus, Cavite', 0, 1, 'C');
    $pdf->Cell(0, 5, '(046) 471-6607 / (046) 436-6584', 0, 1, 'C');
    $pdf->Cell(0, 5, 'www.cvsu.edu.ph', 0, 1, 'C');

    $pdf->SetFont('Arial', 'BU', 20);
    $pdf->Cell(0, 25, 'Class Schedule', 0, 1, 'C');
    $pdf->SetFont('Arial', 'BU', 15);

    $pdf->Cell(0, 15, '1ST SEMESTER A.Y. 2023 - 2024', 0, 1, 'C');

    $pdf->SetFont('Arial', 'B', 30);

    $pdf->Cell(0, 15, $teacherName, 0, 1, 'C');

    // Add a line break to create some space
    $pdf->Ln(10);

    $pdf->SetFont('Arial', 'B', 10);

    $pdf->SetFillColor(0, 100, 0);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(40, 10, 'Scheduled Time In', 1, 0, 'C', true);
    $pdf->Cell(40, 10, 'Scheduled Time Out', 1, 0, 'C', true);
    $pdf->Cell(30, 10, 'Subject', 1, 0, 'C', true);
    $pdf->Cell(25, 10, 'Section', 1, 0, 'C', true);
    $pdf->Cell(30, 10, 'Day', 1, 0, 'C', true);
    $pdf->Cell(25, 10, 'Room', 1, 1, 'C', true);

    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', '', 10);

    foreach ($schedules as $schedule) {
        $pdf->Cell(40, 10, $schedule['scheduledtimein'], 1, 0, 'C');
        $pdf->Cell(40, 10, $schedule['scheduledtimeout'], 1, 0, 'C');
        $pdf->Cell(30, 10, $schedule['subject'], 1, 0, 'C');
        $pdf->Cell(25, 10, $schedule['section'], 1, 0, 'C');
        $pdf->Cell(30, 10, $schedule['day'], 1, 0, 'C');
        $pdf->Cell(25, 10, $schedule['room'], 1, 1, 'C');
    }

    // Include the teacher's name in the filename without timestamp
    $pdfFileName = $teacherName . '_schedule.pdf';
    $pdf->Output($pdfFileName, 'I');

    return $pdfFileName;
}
?>
