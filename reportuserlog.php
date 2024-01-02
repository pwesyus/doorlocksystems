<?php
    require 'fpdf/fpdf.php';

    // Create a new PDF instance
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 10); // Change font size for the title

    // Add a title to the PDF (centered)
    $pdf->Cell(0, 10, 'User Logs', 0, 1, 'C');

    // Set header background color
    $pdf->SetFillColor(0, 100, 0);
    $pdf->SetTextColor(255, 255, 255);

    // Add headers to the PDF table (centered)
    $pdf->Cell(30, 10, 'RFID Number', 1, 0, 'C', true);  // Header cell with background color
    $pdf->Cell(40, 10, 'Name', 1, 0, 'C', true);        // Header cell with background color
    $pdf->Cell(45, 10, 'Time in', 1, 0, 'C', true);     // Header cell with background color
    $pdf->Cell(45, 10, 'Time out', 1, 0, 'C', true);    // Header cell with background color
    $pdf->Cell(30, 10, 'Status', 1, 1, 'C', true);      // Header cell with background color, move to the next line

    // Reset text color
    $pdf->SetTextColor(0, 0, 0);

    // Prepare SQL query with filter conditions
    $pdfSql = "SELECT RFIDNumber, name, Timein, Timeout, status FROM userlogs WHERE 1=1";

    if (!empty($filterDate)) {
        $pdfSql .= " AND DATE(Timein) = '$filterDate'";
    } elseif (!empty($filterMonth)) {
        $month = date('m', strtotime($filterMonth));
        $pdfSql .= " AND MONTH(Timein) = '$month'";
    } elseif (!empty($startDate) && !empty($endDate)) {
        $pdfSql .= " AND Timein BETWEEN '$startDate' AND '$endDate'";
    }

    // Execute the query
    $pdfResult = $conn->query($pdfSql);
// Iterate through the result and add rows to the PDF table
while ($pdfRow = $pdfResult->fetch_assoc()) {
    $pdf->Cell(30, 10, $pdfRow["RFIDNumber"], 1, 0, 'C');
    $pdf->Cell(40, 10, $pdfRow["name"], 1, 0, 'C');
    $pdf->Cell(45, 10, $pdfRow["Timein"], 1, 0, 'C');
    $pdf->Cell(45, 10, $pdfRow["Timeout"], 1, 0, 'C');

    // Set text color and background color based on status
    $status = strtoupper($pdfRow["status"]); // Convert to uppercase
    switch ($status) {
        case 'ABSENT':
            $textColor = array(255, 0, 0); // Red text
            $backgroundColor = array(255, 255, 255); // White background
            break;
        case 'MASTERKEY':
            $textColor = array(0, 0, 255); // Blue text
            $backgroundColor = array(255, 255, 255); // White background
            break;
        case 'LEAVE':
            $textColor = array(255, 255, 0); // Yellow text
            $backgroundColor = array(255, 255, 255); // White background
            break;
        case 'LATE':
            $textColor = array(255, 165, 0); // Orange text
            $backgroundColor = array(255, 255, 255); // White background
            break;
        case 'ON-TIME':
            $textColor = array(0, 128, 0); // Green text
            $backgroundColor = array(255, 255, 255); // White background
            break;
        default:
            $textColor = array(0, 0, 0); // Default: Black text
            $backgroundColor = array(255, 255, 255); // White background
            break;
    }
    $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
    $pdf->SetFillColor($backgroundColor[0], $backgroundColor[1], $backgroundColor[2]);
    $pdf->Cell(30, 10, $status, 1, 1, 'C', true); // move to the next line

    // Reset text color and background color to default for the next row
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFillColor(255, 255, 255);
}

// Output the PDF to the browser
$pdfFileName = 'userlog.pdf';
$pdf->Output('I', $pdfFileName);

// Terminate script to prevent further output
exit();
?>