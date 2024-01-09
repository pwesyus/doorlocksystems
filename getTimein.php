<?php
include('database.php');

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
        $userName = $data['UserName'];
        $subject = $data['subjects'];
        $section = $data['section'];
        $room = $data['rooms'];

        // Use prepared statement to prevent SQL injection
// Use prepared statement to prevent SQL injection
$stmt = $conn->prepare("INSERT INTO userlogs (Timein, RFIDNumber, name, subject, section, room, Timeout) VALUES (NOW(), ?, ?, ?, ?, ?, NULL)");
$stmt->bind_param("sssss", $RFIDNumber, $userName, $subject, $section, $room);


        if ($stmt->execute()) {
            echo "Data inserted successfully";

            // Get current day
            $currentDay = date('l');

            $assignedRoom = "CL8";
            // Retrieve the last log
            $query = "SELECT RFIDNumber, name, subject,section, room, Timein, Timeout
                      FROM userlogs
                      WHERE Timein IS NOT NULL AND Timeout IS NULL
                      ORDER BY Timein DESC
                      LIMIT 1";
            $result = $conn->query($query);

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $userName = $row['name']; // Fix the variable assignment
                $subject = $row['subject'];
                $room = $row['room'];
                $section = $row['section'];
                $timeIn = $row['Timein'];
                $timeOut = $row['Timeout'];

                // Retrieve access level
                $query3 = "SELECT id, accesslevel FROM table_the_iot_projects WHERE id = '$RFIDNumber'";
                $result3 = $conn->query($query3);

                if ($result3 && $result3->num_rows > 0) {
    $row3 = $result3->fetch_assoc();
    $accesslevel = $row3['accesslevel'];

    // Check if access level is masterkey
    if (strcasecmp(trim($accesslevel), 'masterkey') === 0) {
        $status = "Masterkey";
        
        // Insert status into userlogs for Masterkey
        $updateQuery = "UPDATE userlogs
                        SET status = '$status'
                        WHERE RFIDNumber = '$RFIDNumber' AND Timein = '$timeIn'";
        
        if ($conn->query($updateQuery) === TRUE) {
            echo "Status updated successfully!";
        } else {
            echo "Error updating status: " . $conn->error;
        }
    } else {
        // Retrieve schedule based on RFIDNumber
        $query2 = "SELECT *
                   FROM schedule
                   WHERE id = '$RFIDNumber' AND room = '$assignedRoom' AND section = '$section' AND day = '$currentDay'
                         AND NOW() BETWEEN scheduledtimein AND scheduledtimeout
                         AND NOT EXISTS (
                             SELECT 1
                             FROM userlogs
                             WHERE RFIDNumber = '$RFIDNumber'
                             AND Timein IS NOT NULL
                             AND Timeout IS NULL
                             AND status IS NULL
                         )
                   ORDER BY scheduledtimein ASC
                   LIMIT 1";
        $result2 = $conn->query($query2);

        if ($result2 && $result2->num_rows > 0) {
            // Initialize default values
            $row2 = $result2->fetch_assoc();
            $scheduledTimeIn = $row2['scheduledtimein'];
            $scheduledTimeOut = $row2['scheduledtimeout'];

            // Calculate time difference
            $timeDifference = strtotime($timeIn) - strtotime($scheduledTimeIn);

            // Update status based on conditions
            if ($timeDifference >= 0 && $timeDifference <= 600) {
                // On-time (within 5 to 10 minutes)
                $status = "On-time";
            } elseif ($timeDifference > 600) {
                // Late (greater than 10 minutes)
                $status = "Late";
            } else {
                // Default status if not On-time or Late
                $status = "Absent";
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
            // Default status if no upcoming schedule is found
            $status = "Absent";
            
            // Insert default status into userlogs
            $updateQuery = "UPDATE userlogs
                            SET status = '$status'
                            WHERE RFIDNumber = '$RFIDNumber' AND Timein = '$timeIn'";
            
            if ($conn->query($updateQuery) === TRUE) {
                echo "Default status (Absent) updated successfully!";
            } else {
                echo "Error updating default status: " . $conn->error;
            }
        }
    }
} else {
    echo "Error retrieving access level: " . $conn->error;
}
    
            } else {
                echo "No logs found.";
            }
        } else {
            echo "Error: " . $stmt->error;
        }

        // Close the prepared statement
        $stmt->close();
    } else {
        echo "Failed to decode JSON data";
    }
} else {
    echo "No JSON data received";
}

// Close the database connection
$conn->close();
?>
