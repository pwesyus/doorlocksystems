<?php
session_start(); // Start the session

include 'database.php';
require('fpdf/fpdf.php');
include 'sidenav.php';

 if (isset($_POST['printPdf'])) {


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

    // Instead of WriteHTML, use MultiCell to add the HTML content
    $pdf->MultiCell(0, 10, $tableContent);

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

    // Optionally, you can delete the saved PDF file if it's not needed anymore
    unlink($pdfFileName);

} else {
    echo "Invalid request.";
}




if (isset($_GET['addLoA'])) {
    $_SESSION['addLoA'] = true;

    // Get the current day
    $currentDay = date('l');

    // Database connection
    $pdo = new PDO('mysql:host=localhost;dbname=nodemcu_rfid_iot_projects', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Step 1: Retrieve schedules for room CL8 on the current day
    $sqlSchedules = "SELECT id, name, scheduledtimein, scheduledtimeout FROM schedule WHERE room = 'CL8' AND day = ? AND scheduledtimein < NOW()";
    $qSchedules = $pdo->prepare($sqlSchedules);
    $qSchedules->execute([$currentDay]);
    $schedules = $qSchedules->fetchAll(PDO::FETCH_ASSOC);

    // Step 2: Retrieve user logs for today
    $sqlUserLogs = "SELECT RFIDNumber, name, timein, timeout FROM userlogs WHERE DATE(timein) = CURDATE()";
    $qUserLogs = $pdo->query($sqlUserLogs);
    $userLogs = $qUserLogs->fetchAll(PDO::FETCH_ASSOC);

    // Step 3: Retrieve leave records from labsence table
    $sqlLeaveRecords = "SELECT Name, LAbsenceFrom, LAbsenceTo FROM labsence";
    $qLeaveRecords = $pdo->query($sqlLeaveRecords);
    $leaveRecords = $qLeaveRecords->fetchAll(PDO::FETCH_ASSOC);

    // Step 4: Find and insert leave and absent records
    foreach ($schedules as $schedule) {
        $scheduleID = $schedule['id'];
        $name = $schedule['name'];

        // Check if there is any entry for the current schedule
        $entryExists = false;
        foreach ($userLogs as $userLog) {
            if ($userLog['name'] == $name) {
                $entryExists = true;
                break;
            }
        }

        // Check if there is a leave entry for the current schedule
        $leaveExists = false;
        foreach ($leaveRecords as $leaveRecord) {
            if ($leaveRecord['Name'] == $name) {
                $leaveFrom = strtotime($leaveRecord['LAbsenceFrom']);
                $leaveTo = strtotime($leaveRecord['LAbsenceTo']);
                $currentDate = strtotime(date('Y-m-d'));

                if ($currentDate >= $leaveFrom && $currentDate <= $leaveTo) {
                    $leaveExists = true;
                    break;
                }
            }
        }

        // If there is no entry, insert a leave record
        if (!$entryExists && $leaveExists) {
            $currentDate = date('Y-m-d'); // Get the current date
            $sqlInsertLeave = "INSERT INTO userlogs (RFIDNumber, name, timein, timeout, status) VALUES (?, ?, ?, ?, 'LEAVE')";
            $qInsertLeave = $pdo->prepare($sqlInsertLeave);

            // Use the prepared statement to bind parameters and execute the query
            $qInsertLeave->execute([$scheduleID, $name, "{$currentDate} 00:00:00", "{$currentDate} 00:00:00"]);

            // Check for success or failure
            if ($qInsertLeave->rowCount() > 0) {
                echo '<script>alert("Successfully added the leave");</script>';
            } else {
                echo '<script>alert("Failed to add leave");</script>';
            }

            echo '<script>setTimeout(function() { window.location = "userlog.php"; }, 100);</script>';
        }
        // If there is no entry, insert an absent record
        elseif (!$entryExists) {
            $currentDate = date('Y-m-d'); // Get the current date
            $sqlInsertAbsent = "INSERT INTO userlogs (RFIDNumber, name, timein, timeout, status) VALUES (?, ?, ?, ?, 'ABSENT')";
            $qInsertAbsent = $pdo->prepare($sqlInsertAbsent);

            // Use the prepared statement to bind parameters and execute the query
            $qInsertAbsent->execute([$scheduleID, $name, "{$currentDate} 00:00:00", "{$currentDate} 00:00:00"]);

            // Check for success or failure
            if ($qInsertAbsent->rowCount() > 0) {
                echo '<script>alert("Successfully added the absent");</script>';
            } else {
                echo '<script>alert("Failed to add absent");</script>';
            }

            echo '<script>setTimeout(function() { window.location = "userlog.php"; }, 100);</script>';
        } else {
            echo '<script>alert("Absent and Leave of Absence already added");</script>';
            echo '<script>setTimeout(function() { window.location = "userlog.php"; }, 100);</script>';
        }
    }
}


// Query to retrieve the RFID entry and exit logs for today
$sql = "SELECT RFIDNumber, name, TIME(Timein) as Timein, TIME(Timeout) as Timeout, status FROM userlogs WHERE DATE(Timein) = CURDATE()";
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

// Query to retrieve the RFID entry and exit logs
$sql1 = "SELECT RFIDNumber, name, Timein, Timeout FROM userlogs";
$result1 = $conn->query($sql1);

$filterDate = "";
$filterMonth = "";
$startDate = "";
$endDate = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $filterDate = $_POST["filterDate"];
  $filterMonth = $_POST["filterMonth"];
  $startDate = $_POST["startDate"];
  $endDate = $_POST["endDate"];

  $sql1 = "SELECT * FROM `userlogs` WHERE 1=1";

  if (!empty($filterDate)) {
    $sql1 .= " AND DATE(Timein) = '$filterDate'";
  }

  if (!empty($filterMonth)) {
    $month = date('m', strtotime($filterMonth));
    $sql1 .= " AND MONTH(Timein) = '$month'";
  }

  if (!empty($startDate) && !empty($endDate)) {
    $sql1 .= " AND Timein BETWEEN '$startDate' AND '$endDate'";
  }

  $result = $conn->query($sql1);
  if (!$result) {
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
    <link rel="stylesheet" href="cssuserlogs.css">
</head>

<body>
    <div id="container">
        <form method="get" action="">
            <button type="submit" class="btn btn-success btn-leave" name="addLoA">Add LoA and Absent</button>
        </form>
        <p><?php echo date("F j, Y"); ?></p>
        <h1>Total Logs for Today: <?php echo $totalLogsToday; ?></h1>
        <div class="container">
    <form action="" method="post" class="row mb-3">
      <div class="col">
        <label for="filterDate" style="margin-left: -10px;">Filter by Date:</label>
        <input type="date" name="filterDate" id="filterDate" style="width: 170px;margin-left: -10px; "class="form-control" value="<?php echo $filterDate; ?>">
      </div>

      <form method="post">
      <div class="col">
        <label for="filterMonth" style="margin-left: -50px;">Filter by Month:</label>
        <select name="filterMonth" style="width: 170px; margin-left: -50px"id="filterMonth" class="form-control">
          <option value="">All</option>
                <option value="January">January</option>
                <option value="February">February</option>
                <option value="March">March</option>
                <option value="April">April</option>
                <option value="May">May</option>
                <option value="June">June</option>
                <option value="July">July</option>
                <option value="August">August</option>
                <option value="September">September</option>
                <option value="October">October</option>
                <option value="November">November</option>
                <option value="December">December</option>
          
        </select>

      </div>

      <div class="col">
        <label for="startDate" style="margin-left: -90px;">Start Date:</label>
        <input type="date" style="width: 170px; margin-left: -90px"name="startDate" id="startDate" class="form-control" value="<?php echo $startDate; ?>">
      </div>

      <div class="col">
        <label for="endDate"style="margin-left: -130px;">End Date:</label>
        <input type="date" style="width: 170px;margin-left: -130px "name="endDate" id="endDate" class="form-control" value="<?php echo $endDate; ?>">
      </div>
<div class="col">
        <button type="submit" class="btn btn-danger" style="margin-left: -180px; margin-top: 32px; margin-right: 0px;">Filter</button>
        <a href="?clear=true" class="btn btn-secondary" style="margin-top: 30px;">Clear</a>
        <button type="submit" name="printPdf" style="margin-right: 10px; margin-top: 30px;" class="btn btn-dark">Print</button>
    </div>
  
      </form>


  <table id="userLogTable">
            <tr>
                <th>RFID Number</th>
                <th>Name</th>
                <th>Time in</th>
                <th>Time out</th>
                <th>Status</th>
            </tr>
            <?php
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $statusClass = '';

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


                    echo '<tr>';
                    echo '<td>' . $row["RFIDNumber"] . '</td>';
                    echo '<td>' . $row["name"] . '</td>';
                    echo '<td class="log-time">' . $row["Timein"] . '</td>';
                    echo '<td class="log-time">' . $row["Timeout"] . '</td>';
                    echo '<td class="' . $statusClass . '"><span>' . $row["status"] . '</span></td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="5">Error retrieving logs or no logs found for today</td></tr>';
            }
            ?>
        </table>
    </div>
  </div>

  <?php
        $sql = "SELECT * FROM `userlogs`";

        // Apply filters`
        if (!empty($filterDate)) {
          $sql .= " WHERE Timein = '$filterDate'";
        } elseif (!empty($filterMonth)) {
          $sql .= " WHERE MONTH(Timein) = '$filterMonth'";
        } elseif (!empty($startDate) && !empty($endDate)) {
          $sql .= " WHERE Timein BETWEEN '$startDate' AND '$endDate'";
        }

        $result = mysqli_query($conn, $sql);
        ?>
            </div>





</body>

</html>
