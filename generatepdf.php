<?php
require('fpdf/fpdf.php');
include('database.php');

// Fetch data from the database
$sql = "SELECT RFIDNumber, name, Timein, Timeout, status FROM userlogs";
$result = $conn->query($sql);

// Check if the query was successful
if (!$result) {
    die("Query failed: " . $conn->error);
}

// Initialize PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12);


// Set background color for header cells
$pdf->SetFillColor(0, 100, 0); // Dark green color

// Set text color for header cells
$pdf->SetTextColor(255, 255, 255); // White color

// Add header cells to the PDF
$pdf->Cell(38, 10, 'RFID Number', 1, 0, 'C', true);
$pdf->Cell(38, 10, 'Name', 1, 0, 'C', true);
$pdf->Cell(38, 10, 'Time In', 1, 0, 'C', true);
$pdf->Cell(38, 10, 'Time Out', 1, 0, 'C', true);
$pdf->Cell(38, 10, 'Status', 1, 1, 'C', true); // Move to the next line

// Reset background and text color for data cells
$pdf->SetFillColor(255, 255, 255); // White background color
$pdf->SetTextColor(0, 0, 0); // Black text color

// Adjust font size for data cells
$pdf->SetFont('Arial', '', 10);

// Add data to the PDF
while ($row = $result->fetch_assoc()) {
    $pdf->Cell(38, 10, $row['RFIDNumber'], 1, 0, 'C');
    $pdf->Cell(38, 10, $row['name'], 1, 0, 'C');
    $pdf->Cell(38, 10, $row['Timein'], 1, 0, 'C');
    $pdf->Cell(38, 10, $row['Timeout'], 1, 0, 'C');
    $pdf->Cell(38, 10, $row['status'], 1, 1, 'C'); // Move to the next line
}

// Dynamically generate and store the PDF filename
$pdfFileName = 'userlogs_' . date('Ymd_His') . '.pdf';

// Save the PDF to a file (optional)
$pdf->Output($pdfFileName, 'F');

// Set appropriate headers for PDF content
header('Content-Type: application/pdf');
header('Content-Transfer-Encoding: Binary');
header('Content-Disposition: inline; filename="' . $pdfFileName . '"');

// Output PDF content
readfile($pdfFileName);

// Close the database connection
$conn->close();
?>
