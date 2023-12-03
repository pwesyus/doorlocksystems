<?php
require_once('database.php'); // Assuming your database connection file is named 'database.php'

// Get the current day
$currentDay = date('l');

// Step 1: Retrieve schedules for room CL8 on the current day
$pdo = new PDO('mysql:host=localhost;dbname=nodemcu_rfid_iot_projects', 'root', ''); // Update with your actual credentials
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sqlSchedules = "SELECT id, name, scheduledtimein, scheduledtimeout FROM schedule WHERE room = 'CL8' AND day = ? AND scheduledtimein < NOW()";
$qSchedules = $pdo->prepare($sqlSchedules);
$qSchedules->execute([$currentDay]);
$schedules = $qSchedules->fetchAll(PDO::FETCH_ASSOC);

// Step 2: Retrieve userlogs for today
$sqlUserLogs = "SELECT RFIDNumber, name, timein, timeout FROM userlogs WHERE DATE(timein) = CURDATE()";
$qUserLogs = $pdo->query($sqlUserLogs);
$userLogs = $qUserLogs->fetchAll(PDO::FETCH_ASSOC);

// Step 3: Find and insert absent records
foreach ($schedules as $schedule) {
    $scheduleID = $schedule['id'];
    $name = $schedule['name'];

    // Check if there is no userlog entry for the current schedule
    $userLogExists = false;
    foreach ($userLogs as $userLog) {
        if ($userLog['name'] == $name) {
            $userLogExists = true;
            break;
        }
    }

    // If no userlog entry exists, insert an absent record
    if (!$userLogExists) {
        $sqlInsertAbsent = "INSERT INTO userlogs (RFIDNumber, name, timein, timeout, status) VALUES (?, ?, '00:00:00', '00:00:00', 'absent')";
        $qInsertAbsent = $pdo->prepare($sqlInsertAbsent);
        $qInsertAbsent->execute([$scheduleID, $name]);

        echo "Absent record inserted for $name in schedule ID $scheduleID.\n";
    }
}

$pdo = null; // Close the database connection
?>
