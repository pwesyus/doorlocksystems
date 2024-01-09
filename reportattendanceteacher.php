<?php
function generateTeacherAttendanceReport($pdo, $teacherName, $selectedMonth, $selectedYear, $startDate, $endDate, $attendanceData)
{
    // Create and return the PDF file
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 14);

    $pdf->Cell(0, 10, $teacherName, 0, 1, 'C');
    $pdf->Ln(10);

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(0, 100, 0);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(25, 10, 'Subject', 1, 0, 'C', true);
    $pdf->Cell(20, 10, 'Section', 1, 0, 'C', true);
    $pdf->Cell(35, 10, 'Time In', 1, 0, 'C', true);
    $pdf->Cell(35, 10, 'Time Out', 1, 0, 'C', true);
    $pdf->Cell(20, 10, 'Room', 1, 0, 'C', true);
    $pdf->Cell(28, 10, 'Status Timein', 1, 0, 'C', true);
    $pdf->Cell(28, 10, 'Status Timeout', 1, 1, 'C', true);

    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', '', 10);

    foreach ($attendanceData as $attendance) {
        $pdf->Cell(25, 10, $attendance['subject'], 1, 0, 'C');
        $pdf->Cell(20, 10, $attendance['section'], 1, 0, 'C');
        $pdf->Cell(35, 10, $attendance['Timein'], 1, 0, 'C');
        $pdf->Cell(35, 10, $attendance['Timeout'], 1, 0, 'C');
        $pdf->Cell(20, 10, $attendance['room'], 1, 0, 'C');
        $pdf->Cell(28, 10, $attendance['status'], 1, 0, 'C');
        $pdf->Cell(28, 10, $attendance['statustimeout'], 1, 1, 'C');
    }

    $pdfFileName = $teacherName . '_attendance_report.pdf';
    $pdf->Output( $pdfFileName, 'I');

    return $pdfFileName;
}
?>
