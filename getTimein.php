<?php
include 'database.php'
// Receive the JSON data sent by the Arduino
$jsonData = file_get_contents('php://input');

// Check if any data was received
if ($jsonData) {
    // Decode the JSON data into a PHP associative array
    $data = json_decode($jsonData, true);

    // Check if the JSON data was successfully decoded
    if ($data !== null) {
        // Extract the values from the JSON data
        $RFIDNumber = $data['RFIDNumber'];
        $userName = $data['UserName']; // Extract the userName field
        // Modify the SQL query to use the database's current timestamp
        $sql = "INSERT INTO userlogs (Timein, RFIDNumber, name, Timeout) VALUES (NOW(), '$RFIDNumber', '$userName', NULL)";

        if ($conn->query($sql) === TRUE) {
            echo "Data inserted successfully";

            // Get current day
            $currentDay = date('l');

            $assignedRoom = "CL8";
            // Retrieve the last log
            $query = "SELECT RFIDNumber, name, Timein, Timeout
                      FROM userlogs
                      WHERE Timein IS NOT NULL AND Timeout IS NULL
                      ORDER BY Timein DESC
                      LIMIT 1";
            $result = $conn->query($query);

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $RFIDNumber = $row['RFIDNumber'];
                $userName = $row['name'];
                $timeIn = $row['Timein'];
                $timeOut = $row['Timeout'];

                // Retrieve access level
                $query3 = "SELECT id, accesslevel FROM table_the_iot_projects WHERE id = '$RFIDNumber'";
                $result3 = $conn->query($query3);

                if ($result3 && $result3->num_rows > 0) {
                    $row3 = $result3->fetch_assoc();
                    $accesslevel = $row3['accesslevel'];

                    // Check if access level is masterkey
                    if ($accesslevel === 'MASTERKEY') {
                        $status = "MASTERKEY";
                    } else {
                        // Retrieve schedule based on RFIDNumber
                        $query2 = "SELECT * FROM schedule WHERE id = '$RFIDNumber' AND room = '$assignedRoom' AND day = '$currentDay'";
                            $result2 = $conn->query($query2);
                        if ($result2 && $result2->num_rows > 0) {
                            // Initialize default values
                            $scheduledTimeIn = null;
                            $scheduledTimeOut = null;
                            $status = "ABSENT";
                             while ($row2 = $result2->fetch_assoc()) {
                                $scheduledTimeIn = $row2['scheduledtimein'];
                                $scheduledTimeOut = $row2['scheduledtimeout'];

                                // Calculate time difference
                                $timeDifference = strtotime($timeIn) - strtotime($scheduledTimeIn);

                                // Update status based on conditions
                                if ($timeDifference >= 0 && $timeDifference <= 600) {
                                    // On-time (within 5 to 10 minutes)
                                    $status = "ON-TIME";
                                    // No need to check further, as the user is already on time for one schedule
                                    break;
                                } elseif ($timeDifference > 600) {
                                    // Late (greater than 10 minutes)
                                    $status = "LATE";
                                    // No need to check further, as the user is already late for one schedule
                                    break;
                                }
                            }
                            
                        }
                    }

                    // Insert status into userlogs
                    $updateQuery = "UPDATE userlogs
                                    SET status = '$status'
                                    WHERE RFIDNumber = '$RFIDNumber' AND Timein = '$timeIn'";
                    if ($conn->query($updateQuery) === TRUE) {
                        echo "Status updated successfully!";
                    } else {
                        echo "Error updating status: " . $conn->error;
                    }
                } else {
                    echo "Error retrieving access level: " . $conn->error;
                }
            } else {
                echo "No logs found.";
            }
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }

        // Close the database connection
        $conn->close();
    } else {
        echo "Failed to decode JSON data";
    }
} else {
    echo "No JSON data received";
}
?>
