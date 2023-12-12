<?php
require('fpdf/fpdf.php');

// Function to generate PDF for the selected users
function generateUsersPDF($userSchedules)
{
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 30);

    // Increase the top margin to add space
    $pdf->SetTopMargin(20);

    // Add a title to the PDF
    $pdf->Cell(0, 10, 'List of Users', 0, 1, 'C');

    // Add a line break to create some space
    $pdf->Ln(5);

    // Set table header
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(0, 100, 0);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(25, 10, 'ID', 1, 0, 'C', true);
    $pdf->Cell(45, 10, 'Name', 1, 0, 'C', true);
    $pdf->Cell(30, 10, 'Access Level', 1, 0, 'C', true);
    $pdf->Cell(50, 10, 'Email', 1, 0, 'C', true);
    $pdf->Cell(40, 10, 'Mobile', 1, 1, 'C', true);

    // Set table data
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', '', 10);

    foreach ($userSchedules as $user) {
        $pdf->Cell(25, 10, $user['id'], 1, 0, 'C');
        $pdf->Cell(45, 10, $user['name'], 1, 0, 'C');
        $pdf->Cell(30, 10, $user['accesslevel'], 1, 0, 'C');
        $pdf->Cell(50, 10, $user['email'], 1, 0, 'C');
        $pdf->Cell(40, 10, $user['mobile'], 1, 1, 'C');
    }

    // Include the filename without timestamp
    $pdfFileName = 'list_of_user.pdf';

    // Output the PDF without forcing download
    $pdf->Output('I', $pdfFileName);

    return $pdfFileName;
}
?>