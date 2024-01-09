<?php
// Assuming you have a database connection established
include('database.php');
$assignedroom = "CL8"; 

// Set the time zone to Asia/Manila
date_default_timezone_set('Asia/Manila');

$currentTime = strtotime("now"); // Get the current time as a Unix timestamp
$currentDay = date("w", $currentTime);
$currentTimeString = date("H:i:s", $currentTime);

$firstQuery = "SELECT id, name, subject, room, scheduledtimein, scheduledtimeout FROM schedule WHERE room = '$assignedroom' AND dayno = $currentDay AND scheduledtimein <= '$currentTimeString' AND scheduledtimeout >= '$currentTimeString'";

$resultFirstQuery = mysqli_query($conn, $firstQuery);

if ($resultFirstQuery) {
    // Check if there are any rows in the result set
    if (mysqli_num_rows($resultFirstQuery) > 0) {
        // Fetch the schedule details
        $scheduleDetails = mysqli_fetch_assoc($resultFirstQuery);

        // Extract schedule details
        $scheduleId = $scheduleDetails['id'];
        $scheduleName = $scheduleDetails['name'];
        $scheduleSubject = $scheduleDetails['subject'];
        $scheduleRoom = $scheduleDetails['room'];
        $scheduleTimeIn = $scheduleDetails['scheduledtimein'];
        $scheduleTimeOut = $scheduleDetails['scheduledtimeout'];

        // Echo schedule details
        echo "Schedule Details:<br>";
        echo "ID: $scheduleId<br>";
        echo "Name: $scheduleName<br>";
        echo "Subject: $scheduleSubject<br>";
        echo "Room: $scheduleRoom<br>";
        echo "Time In: $scheduleTimeIn<br>";
        echo "Time Out: $scheduleTimeOut<br>";
        echo "---------------------------------------<br>";

        // Second Query to get user logs based on schedule details and timeframe
        $secondQuery = "SELECT RFIDNumber, name, subject, room, Timein, Timeout, status, statustimeout FROM userlogs WHERE room = '$scheduleRoom' AND Timein >= '$scheduleTimeIn'";

        $resultSecondQuery = mysqli_query($conn, $secondQuery);

        if ($resultSecondQuery) {
            // Fetch and echo user logs
            while ($userLog = mysqli_fetch_assoc($resultSecondQuery)) {
                echo "RFIDNumber: " . $userLog['RFIDNumber'] . "<br>";
                echo "Name: " . $userLog['name'] . "<br>";
                echo "Subject: " . $userLog['subject'] . "<br>";
                echo "Room: " . $userLog['room'] . "<br>";
                echo "Timein: " . $userLog['Timein'] . "<br>";
                echo "Timeout: " . $userLog['Timeout'] . "<br>";
                echo "Status: " . $userLog['status'] . "<br>";
                echo "Statustimeout: " . $userLog['statustimeout'] . "<br>";
                echo "---------------------------------------<br>";
            }
        } else {
            echo "Error in second query: " . mysqli_error($conn);
        }
    } else {
        echo "No schedule found for the specified conditions.";
    }
} else {
    echo "Error in first query: " . mysqli_error($conn);
}

// Close the database connection if needed
mysqli_close($conn);
?>
