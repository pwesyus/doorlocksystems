<?php
function generateTeacherAttendanceReport($pdo, $teacherName, $selectedMonth, $selectedYear, $startDate, $endDate, $attendanceData)
{
    // Create and return the PDF file
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

    $pdf->SetFont('Arial', 'BU', 15);
    $pdf->Cell(0, 20, '1ST SEMESTER A.Y. 2023 - 2024', 0, 1, 'C');
    $pdf->SetFont('Arial', 'BU', 20);
    $pdf->Cell(0, 20, 'Class Schedule', 0, 1, 'C');

    $pdf->SetFont('Arial', 'B', 25);

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
