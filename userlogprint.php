<?php
require('fpdf/fpdf.php');


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['tableContent'])) {
    // Retrieve HTML content from the POST data
    $tableContent = $_POST['tableContent'];

    // Initialize PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 12);

    // Add content from the table to the PDF
    $pdf->writeHTML($tableContent);

    // Output PDF content
    $pdf->Output('userlogs.pdf', 'D');
} else {
    // Handle the case when tableContent parameter is not provided
    echo "Error: Missing table content parameter.";
}
?>
