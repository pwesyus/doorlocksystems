<?php
include 'database.php';

// Set the time zone to Asia/Manila
date_default_timezone_set('Asia/Manila');

$currentTime = strtotime("now"); // Get the current time as a Unix timestamp
$currentDay = date("w", $currentTime);
$currentTimeString = date("H:i:s", $currentTime);

$usersWithMasterKey = array();
$usersWithSchedules = array();
$assignedroom = "CL8";  // Replace with the actual room value

// First Query: Retrieve id and name of users with accesslevel = 'masterkey'
$firstQuery = "SELECT id, name FROM table_the_iot_projects WHERE accesslevel = 'masterkey'";
$firstResult = $conn->query($firstQuery);

while ($row = $firstResult->fetch_assoc()) {
    $usersWithMasterKey[] = $row;
}

// Query to fetch userlogs with Timeout IS NULL
$fourthQuery = "SELECT RFIDNumber AS id, name, section, Timein FROM userlogs WHERE Timeout IS NULL";
$fourthResult = $conn->query($fourthQuery);

while ($row = $fourthResult->fetch_assoc()) {
    $userlogsnull = $row;
}
if (!empty($userlogsnull)) {
        $userId = $userlogsnull['id'];
        $Timein = $userlogsnull['Timein'];
        $section = $userlogsnull['section'];

 $scheduleQuery = "SELECT id, name, subject, section, room, scheduledtimein, DATE_ADD(scheduledtimeout, INTERVAL 15 MINUTE) AS adjusted_scheduledtimeout FROM schedule WHERE id = '$userId' AND section = '$section' AND room = '$assignedroom' AND dayno = $currentDay AND scheduledtimein <= '$currentTimeString' AND DATE_ADD(scheduledtimeout, INTERVAL 15 MINUTE) >= '$currentTimeString' ORDER BY scheduledtimein DESC LIMIT 1";


        $scheduleResult = $conn->query($scheduleQuery);

        if ($scheduleResult->num_rows > 0) {
            $scheduleDetails = $scheduleResult->fetch_assoc();
            $usersWithSchedules[] = $scheduleDetails;
        }
    }
else{
// Second Query: Retrieve id and name of users from the schedule table
$secondQuery = "SELECT id, name, subject, section, room, scheduledtimein, scheduledtimeout FROM schedule WHERE room = '$assignedroom' AND dayno = $currentDay AND scheduledtimein <= '$currentTimeString' AND scheduledtimeout >= '$currentTimeString'";
$secondResult = $conn->query($secondQuery);

while ($row = $secondResult->fetch_assoc()) {
    $scheduleDetails = $row;

    // Third Query: Retrieve user logs based on schedule details
    $thirdQuery = "SELECT RFIDNumber, name, subject, section, room, Timein, Timeout, status, statustimeout FROM userlogs WHERE room = '$scheduleDetails[room]' AND section = '$scheduleDetails[section]' AND subject = '$scheduleDetails[subject]' AND Timein >= '$scheduleDetails[scheduledtimein]' AND Timeout IS NOT NULL";
    $resultThirdQuery = $conn->query($thirdQuery);

    // Process and use $resultThirdQuery as needed
    $logsToSendToArduino = array(); // Initialize an array to store logs to be sent to Arduino

    while ($userLog = $resultThirdQuery->fetch_assoc()) {
        // Do not include echo statements for userlogs in the response
        // Instead, store the log in the array
        $logsToSendToArduino[] = $userLog;
    }

    // If user logs are not fetched for the current schedule, include it in the list
    if (empty($logsToSendToArduino)) {
        $usersWithSchedules[] = $scheduleDetails;
    }
}
}

// Close the database connection
$conn->close();

// Combine the data from both queries into a single array for names and another for IDs
$combinedData = array(
    "masterkey_users" => $usersWithMasterKey,
    "users_with_schedules" => $usersWithSchedules,
);

// Send the data to Arduino (you need to implement this part)
// Serialize the data to JSON and send it to the Arduino
$dataToSendToArduinoJson = json_encode($combinedData);

// For testing, you can echo the JSON data
echo $dataToSendToArduinoJson;
?>
