<?php
session_start(); // Start the session

include 'database.php';


if (isset($_POST['showabsent'])) {
    $_SESSION['showabsent'] = true;

    // Get the current day
    $currentDay = date('l');

    // Database connection
    $pdo = new PDO('mysql:host=localhost;dbname=nodemcu_rfid_iot_projects', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Step 1: Retrieve schedules for room CL8 on the current day
    $sqlSchedules = "SELECT id, name, subject, room, section, scheduledtimein, scheduledtimeout, day FROM schedule WHERE scheduledtimein < NOW() AND scheduledtimeout < NOW() AND day = :currentDay";
    $qSchedules = $pdo->prepare($sqlSchedules);
    $qSchedules->bindParam(':currentDay', $currentDay);
    $qSchedules->execute();
    $schedules = $qSchedules->fetchAll(PDO::FETCH_ASSOC);

    // Check if there are no schedules found
    if (empty($schedules)) {
        echo '<script>alert("No schedules found for today.");</script>';
        echo '<script>setTimeout(function() { window.location = "userlog.php"; }, 100);</script>';
    } else {

        // Retrieve all user logs for today
        $sqlAllUserLogs = "SELECT RFIDNumber, name, subject, section, room FROM userlogs WHERE DATE(timein) = CURDATE()";
        $qAllUserLogs = $pdo->query($sqlAllUserLogs);
        $allUserLogs = $qAllUserLogs->fetchAll(PDO::FETCH_ASSOC);

    
        // Convert user logs into a lookup array based on the concatenated key
        $allUserLogsLookup = [];
        foreach ($allUserLogs as $userLog) {
            $key = $userLog['RFIDNumber'] . $userLog['name'] . $userLog['subject'] . $userLog['section'] . $userLog['room'];
            $allUserLogsLookup[$key] = true;
        }

        // Step 3: Process schedules and collect absent records to be inserted
        $absentRecords = [];

        foreach ($schedules as $schedule) {
            $scheduleID = $schedule['id'];
            $name = $schedule['name'];
            $subject = $schedule['subject'];
            $section = $schedule['section'];
            $room = $schedule['room'];

            // Check if a user log entry already exists for the current schedule
            $key = $scheduleID . $name . $subject . $section . $room;

            if (!isset($allUserLogsLookup[$key])) {
                // If the user log entry doesn't exist, add it to the absent records array
                $currentDate = date('Y-m-d'); // Get the current date

                $absentRecords[] = [
                    $scheduleID, $name, $subject, $section, $room, "{$currentDate} 00:00:00", "{$currentDate} 00:00:00"
                ];
            }
        }

        // Insert all absent records at once
        if (!empty($absentRecords)) {
            $sqlInsertAbsent = "INSERT INTO userlogs (RFIDNumber, name, subject, section, room, timein, timeout, status, statustimeout) VALUES (?, ?, ?, ?, ?, ?, ?, 'ABSENT', 'ABSENT')";
            $qInsertAbsent = $pdo->prepare($sqlInsertAbsent);

            foreach ($absentRecords as $absentRecord) {
                $qInsertAbsent->execute($absentRecord);
            }

            // Check for success or failure
            if ($qInsertAbsent->rowCount() > 0) {
                echo '<script>alert("Successfully added absent records.");</script>';
            } else {
                echo '<script>alert("Failed to add absent records.");</script>';
            }
        } else {
            echo '<script>alert("All the schedule has userlogs");</script>';
        }

        // Clear echoed messages using JavaScript after displaying the alert
        echo '<script>
            setTimeout(function() {
                document.body.innerHTML = "";
            }, 1000);
        </script>';

        // Redirect to userlog.php after processing schedules
        echo '<script>setTimeout(function() { window.location = "userlog.php"; }, 100);</script>';
    }

    // Close the database connection when done
    $pdo = null;
}





// Query to retrieve the RFID entry and exit logs for today
$sql = "SELECT RFIDNumber, name, subject, section, room, TIME(Timein) as Timein, TIME(Timeout) as Timeout, status, statustimeout FROM userlogs WHERE DATE(Timein) = CURDATE()";
$sql .= " ORDER BY Timein DESC";
$result = $conn->query($sql);

if (!$result) {
    // Handle the error. You can print the error for debugging purposes.
    echo "Error retrieving logs: " . $conn->error;
}

// Query to count the total number of logs for today
$countSql = "SELECT COUNT(*) as totalLogs FROM userlogs WHERE DATE(Timein) = CURDATE()";
$countResult = $conn->query($countSql);

if (!$countResult) {
    // Handle the error. You can print the error for debugging purposes.
    echo "Error counting logs: " . $conn->error;
}

$totalLogsToday = $countResult->fetch_assoc()['totalLogs'];

    $filterDate = "";
    $filterMonth = "";
    $startDate = "";
    $endDate = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $filterDate = isset($_POST["filterDate"]) ? $_POST["filterDate"] : "";
    $filterMonth = isset($_POST["filterMonth"]) ? $_POST["filterMonth"] : "";
    $startDate = isset($_POST["startDate"]) ? $_POST["startDate"] : "";
    $endDate = isset($_POST["endDate"]) ? $_POST["endDate"] : "";

    $sql1 = "SELECT * FROM `userlogs` WHERE 1=1";

    if (!empty($filterDate)) {
        $sql1 .= " AND DATE(Timein) = '$filterDate'";
    }

    else if (!empty($filterMonth)) {
        if ($filterMonth !== 'all') {
            $month = date('m', strtotime($filterMonth));
            $sql1 .= " AND MONTH(Timein) = '$month'";
        }
        // If 'All' is selected, do not add month-specific condition
    }

    else if (!empty($startDate) && !empty($endDate)) {
        $sql1 .= " AND Timein BETWEEN '$startDate' AND '$endDate'";
    }

    $sql1 .= " ORDER BY Timein DESC";
    $resultFiltered = $conn->query($sql1);
    if (!$resultFiltered) {
        die("Error: " . $conn->error);
    }
}


// Check if the clear button is clicked
if (isset($_POST['clear'])) {
    // Reset the filter values
    $filterDate = "";
    $filterMonth = "";
    $startDate = "";
    $endDate = "";
}

// Check if the printPdf button is clicked
if (isset($_POST['printPdf'])) {
    // Include the FPDF library
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
    $pdf->Cell(40, 10, 'Name', 1, 0, 'C', true);  
    $pdf->Cell(45, 10, 'Subject', 1, 0, 'C', true);
    $pdf->Cell(45, 10, 'Section', 1, 0, 'C', true);      // Header cell with background color
    $pdf->Cell(45, 10, 'Time in', 1, 0, 'C', true);     // Header cell with background color
    $pdf->Cell(45, 10, 'Time out', 1, 0, 'C', true);    // Header cell with background color
    $pdf->Cell(30, 10, 'Status', 1, 1, 'C', true);      // Header cell with background color, move to the next line

    // Reset text color
    $pdf->SetTextColor(0, 0, 0);

    // Prepare SQL query with filter conditions
    $pdfSql = "SELECT RFIDNumber, name, subject, section, Timein, Timeout, status FROM userlogs WHERE 1=1";
    $filenamePrefix = ''; // Initialize filename prefix

    // Check filter conditions and update SQL query and filename prefix accordingly
    if (!empty($filterDate)) {
        $pdfSql .= " AND DATE(Timein) = '$filterDate'";
        $filenamePrefix = $filterDate . '_';
    } elseif (!empty($filterMonth)) {
        if ($filterMonth !== 'all') {
            $month = date('m', strtotime($filterMonth));
            $pdfSql .= " AND MONTH(Timein) = '$month'";
            $filenamePrefix = $filterMonth . '_';
        }
        $filenamePrefix = $filterMonth . '_';
    } elseif (!empty($startDate) && !empty($endDate)) {
        $pdfSql .= " AND Timein BETWEEN '$startDate' AND '$endDate'";
        $filenamePrefix = $startDate . '_' . $endDate . '_';
    } else {
        // If no filters are applied, print today's user logs based on the current day
        $currentDay = date('Y-m-d');
        $pdfSql .= " AND DATE(Timein) = '$currentDay'";
        $filenamePrefix = $currentDay;
    }
    $pdfSql .= " ORDER BY Timein DESC";
    // Execute the query
    $pdfResult = $conn->query($pdfSql);

    // Iterate through the result and add rows to the PDF table
    while ($pdfRow = $pdfResult->fetch_assoc()) {
        $pdf->Cell(30, 10, $pdfRow["RFIDNumber"], 1, 0, 'C');
        $pdf->Cell(40, 10, $pdfRow["name"], 1, 0, 'C');
        $pdf->Cell(40, 10, $pdfRow["subject"], 1, 0, 'C');
        $pdf->Cell(40, 10, $pdfRow["section"], 1, 0, 'C');
        $pdf->Cell(40, 10, $pdfRow["room"], 1, 0, 'C');
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

        // Apply text color and background color to the current cell
        $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
        $pdf->SetFillColor($backgroundColor[0], $backgroundColor[1], $backgroundColor[2]);

        // Add status cell and move to the next line
        $pdf->Cell(30, 10, $status, 1, 1, 'C', true);

        // Reset text color and background color to default for the next row
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(255, 255, 255);
    }

    // Output the PDF to the browser
    $pdfFileName = $filenamePrefix . ' UserLogs.pdf';
    $pdf->Output('D', $pdfFileName);

    // Terminate script to prevent further output
    exit();
}
include 'sidenav.php';



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>


    <script src="js/bootstrap.min.js"></script>
  
</head>

<style>
    
        .container {
            margin-left: 280px;
        }

        table {
            width: 90%;
            margin-bottom: 50px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }

        th {
            font-weight: bold;
            background-color: darkgreen;
            color: #fff;
        }

        tr {
            color: #000;
        }
        .status-early-out span {
        text-transform: uppercase;
        color: #fff;
        font-weight: bold;
        padding: 5px;
        border-radius: 5px;
        background-color: orange; /* Set background color for early-out */
    }

    .status-overtime span {
        text-transform: uppercase;
        color: #fff;
        font-weight: bold;
        padding: 5px;
        border-radius: 5px;
        background-color: red; /* Set background color for overtime */
    }

        .status-absent span,
        .status-late span,
        .status-on-time span,
        .status-masterkey span,
        .status-leave span {
            text-transform: uppercase;
            color: #fff;
            font-weight: bold;
            padding: 5px;
            border-radius: 5px;
        }

        .status-absent span {
            background-color: red;
        }

        .status-late span {
            background-color: orange;
        }

        .status-on-time span {
            background-color: green;
        }

        .status-masterkey span {
            background-color: blue;
        }

        .status-leave span {
            background-color: yellow;
            color: black;
        }

        p {
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
            margin-left: 300px;
            margin-right: 400px;
            margin-bottom: 30px;
            color: black;
            display: inline-block;
        }

        h1 {
            font-size: 18px;
            font-weight: bold;
            margin-top: 40px;
            display: inline-flex;
        }

    </style>

<body>
    <div id="titlebar">
        
        <p><?php echo date("F j, Y"); ?></p>
        <h1>Total Logs for Today: <?php echo $totalLogsToday; ?></h1>
        <form action="" method="post">
        <button type="submit" name="showabsent" class="btn btn-danger" style="margin-left: 1100px; margin-top:-110px; font-weight: bold; width: 150px;">Show Absent</button>
    </form>

        <div class="container">
            <form action="" method="post" class="row mb-3">
                <div class="col">
                    <label for="filterDate" style="margin-left: 10px;">Filter by Date:</label>
                    <input type="date" name="filterDate" id="filterDate" style="width: 170px;margin-left: 10px; " class="form-control" onchange="clearDate()" value="<?php echo $filterDate; ?>">
                </div>

                <div class="col">
                    <label for="filterMonth" style="margin-left: -20px;">Filter by Month:</label>
                    <select name="filterMonth" style="width: 170px; margin-left: -20px" id="filterMonth" class="form-control" onchange="clearMonth()">
                    <option value="" disabled <?php echo ($filterMonth === '') ? 'selected' : ''; ?>>Select Month</option>
                    <option value="all" <?php echo ($filterMonth === 'all') ? 'selected' : ''; ?>>All</option>
                    <?php
                        $months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
                        foreach ($months as $month) {
                            echo '<option value="' . $month . '" ' . (($filterMonth === $month) ? 'selected' : '') . '>' . $month . '</option>';
                        }
                    ?>
                </select>

            </div>
            <div class="col">
                    <label for="startDate" style="margin-left: -60px;">Start Date:</label>
                    <input type="date" style="width: 170px; margin-left: -60px" name="startDate" id="startDate" class="form-control" onchange="clearStart()" value="<?php echo $startDate; ?>">
                </div>

                <div class="col">
                    <label for="endDate" style="margin-left: -100px;">End Date:</label>
                    <input type="date" style="width: 170px;margin-left: -100px " name="endDate" id="endDate" class="form-control" onchange="clearStart()" value="<?php echo $endDate; ?>">
                </div>
                <div class="col">
                    <button type="submit" class="btn btn-danger" style="margin-left: -120px; margin-top: 32px; ">Filter</button>
                    <a href="?clear=true" class="btn btn-secondary" style="margin-top: 30px;">Clear</a>
                    <button type="submit" name="printPdf" style=" margin-top: 30px;" class="btn btn-dark">Print</button>
                </div>
            </form>

            <table id="userLogTable">
                <tr>
                    <th>RFID Number</th>
                    <th>Name</th>
                    <th>Subject</th>
                    <th>Section</th>
                    <th>Room</th>
                    <th>Time in</th>
                    <th>Time out</th>
                    <th>Status Timein</th>
                    <th>Status Timeout</th>
                </tr>
                <?php
                // Use the filtered result if available, otherwise, use the original result
                $logsResult = isset($resultFiltered) ? $resultFiltered : $result;

                if ($logsResult->num_rows > 0) {
                    while ($row = $logsResult->fetch_assoc()) {
                        $statusClass = '';
                         $statusTimeoutClass = '';

                        $status = strtoupper($row["status"]);

                        switch ($status) {
                            case 'ABSENT':
                                $statusClass = 'status-absent';
                                break;
                            case 'LATE':
                                $statusClass = 'status-late';
                                break;
                            case 'ON-TIME':
                                $statusClass = 'status-on-time';
                                break;
                            case 'MASTERKEY':
                                $statusClass = 'status-masterkey';
                                break;
                            case 'LEAVE':
                                $statusClass = 'status-leave';
                                break;
                            default:
                                // Handle other status values if needed
                                break;
                        }
                        switch (strtoupper($row["statustimeout"])) {
                            case 'EARLY-OUT':
                                $statusTimeoutClass = 'status-early-out';
                                break;
                            case 'OVERTIME':
                                $statusTimeoutClass = 'status-overtime';
                                break;
                            case 'MASTERKEY':
                                $statusTimeoutClass = 'status-masterkey';
                                break;
                            case 'ON-TIME':
                                $statusTimeoutClass = 'status-on-time';
                                break;
                             case 'ABSENT':
                                $statusTimeoutClass = 'status-absent';
                                break;     
                            default:
                                    // Handle other status values if needed
                                    break;
                        }

                        echo '<tr>';
                        echo '<td>' . $row["RFIDNumber"] . '</td>';
                        echo '<td>' . $row["name"] . '</td>';
                        echo '<td class="log-time">' . $row["subject"] . '</td>';
                        echo '<td class="log-time">' . $row["section"] . '</td>';
                        echo '<td class="log-time">' . $row["room"] . '</td>';
                        echo '<td class="log-time">' . $row["Timein"] . '</td>';
                        echo '<td class="log-time">' . $row["Timeout"] . '</td>';
                        echo '<td class="' . $statusClass . '"><span>' . $row["status"] . '</span></td>';
                        echo '<td class="' . $statusTimeoutClass . '"><span>' . $row["statustimeout"] . '</span></td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="9">No logs found</td></tr>';
                }
                ?>
            </table>
        </div>
    </div>
</body>

</html>

<?php
// Close the database connection
$conn->close();
?>
<script>
    function clearMonth() {
        document.getElementById('filterDate').value = '';
        document.getElementById('startDate').value = '';
        document.getElementById('endDate').value = '';
    }
    function clearDate() {
        document.getElementById('filterMonth').value = '';
        document.getElementById('startDate').value = '';
        document.getElementById('endDate').value = '';
    }
     function clearStart() {
        document.getElementById('filterDate').value = '';
        document.getElementById('filterMonth').value = '';
    }
</script>


