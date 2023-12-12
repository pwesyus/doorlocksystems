<?php

function generateRoomPDF($room, $schedules)
{
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 30);

    // Increase the top margin to add space
    $pdf->SetTopMargin(20);

    $pdf->Cell(0, 10, $room, 0, 1, 'C');

    // Add a line break to create some space
    $pdf->Ln(5);

    $pdf->SetFont('Arial', 'B', 10);

    $pdf->SetFillColor(0, 100, 0);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(45, 10, 'Teacher Name', 1, 0, 'C', true);
    $pdf->Cell(55, 10, 'Scheduled Time In', 1, 0, 'C', true);
    $pdf->Cell(55, 10, 'Scheduled Time Out', 1, 0, 'C', true);
    $pdf->Cell(40, 10, 'Day', 1, 1, 'C', true);

    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', '', 10);

    foreach ($schedules as $schedule) {
        $pdf->Cell(45, 10, $schedule['name'], 1, 0, 'C');
        $pdf->Cell(55, 10, $schedule['scheduledtimein'], 1, 0, 'C');
        $pdf->Cell(55, 10, $schedule['scheduledtimeout'], 1, 0, 'C');
        $pdf->Cell(40, 10, $schedule['day'], 1, 1, 'C');
    }

    // Include the room name in the filename without timestamp
    $pdfFileName = $room . '_schedule.pdf';

    // Output the PDF without forcing download
    $pdf->Output('I', $pdfFileName);

    return $pdfFileName;
}
?>
