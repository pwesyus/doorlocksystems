<?php
session_start(); // Start the session

include 'database.php';
include 'sidenav.php';

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

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <script src="js/bootstrap.min.js"></script>
    <style>
        
        .container {
            margin-left: 270px;
        }

        table {
            width: 77%;
            border-collapse: collapse;
            margin-left: 270px;
            margin-bottom: 30px;
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
            margin-left: 270px;
            margin-right: 400px;
            margin-bottom: 30px;
            color: black;
            display: inline-block;
        }

        h1 {
            font-size: 18px;
            font-weight: bold;
            display: inline-flex;
        }

        .btn-leave {
            float: right;
            font-size: 16px;
            background-color: darkgreen;
            color: #fff;
            padding: 10px;
            margin-right: 20px;
            margin-bottom: 0;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div id="container">
        <form method="get" action="">
            <button type="submit" class="btn btn-success btn-leave" name="addLoA">Add LoA and Absent</button>
        </form>
        <p><?php echo date("F j, Y"); ?></p>
        <h1>Total Logs for Today: <?php echo $totalLogsToday; ?></h1>
        <table>
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
</body>

</html>
