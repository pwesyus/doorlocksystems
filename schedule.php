<?php
include 'database.php';

// Set the time zone to Asia/Manila
date_default_timezone_set('Asia/Manila');

$currentTime = strtotime("now"); // Get the current time as a Unix timestamp
$currentDay = date("w", $currentTime);
$currentTimeString = date("H:i:s", $currentTime);

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// First Query: Retrieve id and name of users with accesslevel = 'masterkey'
$firstQuery = "SELECT id, name FROM table_the_iot_projects WHERE accesslevel = 'masterkey'";
$firstResult = $conn->query($firstQuery);

$usersWithMasterKey = array();
while ($row = $firstResult->fetch_assoc()) {
    $usersWithMasterKey[] = $row;
}

// Second Query: Retrieve id and name of users from the schedule table
$assignedroom = "CL8";  // Replace with the actual room value

$secondQuery = "SELECT id, name FROM schedule WHERE room = '$assignedroom' AND dayno = $currentDay AND scheduledtimein <= '$currentTimeString' AND scheduledtimeout >= '$currentTimeString'";

$secondResult = $conn->query($secondQuery);

$usersWithSchedules = array();
while ($row = $secondResult->fetch_assoc()) {
    $usersWithSchedules[] = $row;
}

// Close the database connection
$conn->close();

// Combine the data from both queries into a single array for names and another for IDs
$combinedData = array(
    "masterkey_users" => $usersWithMasterKey,  // Include master key users
    "users_with_schedules" => $usersWithSchedules,
);

// Send the data to Arduino (you need to implement this part)
// Serialize the data to JSON and send it to the Arduino
$dataToSendToArduinoJson = json_encode($combinedData);

// For testing, you can echo the JSON data
echo $dataToSendToArduinoJson;
?>
