<?php
include 'database.php';

// Function to calculate the time difference in minutes
function calculateTimeDifference($time1, $time2)
{
    $diff = strtotime($time1) - strtotime($time2);
    return round($diff / 60);
}

// Function to send an email
function sendEmail($recipientEmail, $name, $TimeIn, $Timeout, $subject, $section, $room, $status, $statusTimeout)
{
    require 'Mail/phpmailer/PHPMailerAutoload.php';
    $mail = new PHPMailer;
    $mail->SMTPDebug = 0;
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 587;
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = 'tls';
    $mail->Username = 'admn.doorlock@gmail.com';
    $mail->Password = 'mdmimlylawoxmwtl';
    $mail->setFrom('admn.doorlock@gmail.com', 'Attendance');
    $mail->addAddress($recipientEmail);
    $mail->isHTML(true);
    $mail->Subject = 'User Entry Notification';
    $mail->Body = "Hi Ms./Mr. $name, <br><br> You entered $room, with the <br> Subject: $subject <br> Section: $section <br> Time In: $TimeIn <br> Time Out: $Timeout <br> Status Timein: $status <br> Status Timeout: $statusTimeout <br> <br><br> Door Lock System ";

    if (!$mail->send()) {
        echo "Email could not be sent for user: $name\n";
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    } else {
        echo "Email sent successfully for user: $name\n";
    }
}

// Retrieve JSON data
$jsonData = file_get_contents('php://input');

// Check if any data was received
if ($jsonData) {
    // Decode the JSON data into a PHP associative array
    $data = json_decode($jsonData, true);

    // Check if the JSON data was successfully decoded
    if ($data !== null) {
        // Extract the values from the JSON data
        $RFIDNumber = $data['RFIDNumber'];

        // Query to retrieve users with masterkey from table_the_iot_projects
        $masterkeyQuery = "SELECT id, name, accesslevel, email FROM table_the_iot_projects WHERE accesslevel='masterkey'";
        $masterkeyResult = $conn->query($masterkeyQuery);

        if ($masterkeyResult && $masterkeyResult->num_rows > 0) {
            while ($masterkeyRow = $masterkeyResult->fetch_assoc()) {
                $masterkeyID = $masterkeyRow['id'];
                $masterkeyName = $masterkeyRow['name'];
                $masterkeyAccessLevel = $masterkeyRow['accesslevel'];
                $masterkeyEmail = $masterkeyRow['email'];

            if ($RFIDNumber == $masterkeyID) {
                // For masterkey, directly update the userlogs table
                $statusTimeout = 'masterkey';
                $updateQuery = "UPDATE userlogs SET Timeout=CURRENT_TIMESTAMP, statustimeout='$statusTimeout' WHERE RFIDNumber='$RFIDNumber' AND Timeout IS NULL";

                if ($conn->query($updateQuery) === TRUE) {
                    echo "Masterkey log updated successfully for user with RFIDNumber: $RFIDNumber\n";

                    // Retrieve the updated row
                    $updatedRowQuery = "SELECT * FROM userlogs WHERE RFIDNumber='$RFIDNumber' AND statustimeout='$statusTimeout' ORDER BY Timein DESC LIMIT 1";
                    $updatedRowResult = $conn->query($updatedRowQuery);

                    if ($updatedRowResult && $updatedRowResult->num_rows > 0) {
                        $updatedRow = $updatedRowResult->fetch_assoc();

                        // Send email to the masterkey with the details of the updated row
                        sendEmail($masterkeyEmail, $masterkeyName, $updatedRow['Timein'], $updatedRow['Timeout'], $updatedRow['subject'], $updatedRow['section'], $updatedRow['room'], $updatedRow['status'], $statusTimeout);
                    } else {
                        echo "Error retrieving updated row for user with RFIDNumber: $RFIDNumber - " . $conn->error;
                    }
                } else {
                    echo "Error updating masterkey log for user with RFIDNumber: $RFIDNumber - " . $conn->error;
                }

                // Exit the loop since we found a match
                break;
            }

            }
        }

        // If the RFIDNumber is not a masterkey, proceed with regular user logic
        $logQuery = "SELECT name, Timein, section, subject, room, status FROM userlogs WHERE RFIDNumber='$RFIDNumber' AND Timeout IS NULL ORDER BY Timein DESC LIMIT 1";

        // Set the time zone to Asia/Manila
        date_default_timezone_set('Asia/Manila');

        $logResult = $conn->query($logQuery);

        if ($logResult && $logResult->num_rows > 0) {
            $logRow = $logResult->fetch_assoc();
            $name = $logRow['name'];
            $section = $logRow['section'];
            $subject = $logRow['subject'];
            $room = $logRow['room'];
            $timeIn = $logRow['Timein'];
            $status = $logRow['status'];

            if ($status !== 'masterkey') {
                // Retrieve current schedule
                $currentTime = strtotime("now");
                $currentDay = date("w", $currentTime);
                $currentTimeString = date("H:i:s", $currentTime);
                $assignedRoom = "CL8";  // Replace with the actual room value

                // Query to retrieve user schedule
                $scheduleQuery = "SELECT id, name, subject, section, room, scheduledtimein, DATE_ADD(scheduledtimeout, INTERVAL 15 MINUTE) AS adjusted_scheduledtimeout, scheduledtimeout FROM schedule WHERE id = '$RFIDNumber' AND section = '$section' AND room = '$assignedRoom' AND dayno = $currentDay AND scheduledtimein <= '$currentTimeString' AND DATE_ADD(scheduledtimeout, INTERVAL 15 MINUTE) >= '$currentTimeString' ORDER BY scheduledtimein DESC LIMIT 1";

                $scheduleResult = $conn->query($scheduleQuery);

                if ($scheduleResult && $scheduleResult->num_rows > 0) {
                    $scheduleRow = $scheduleResult->fetch_assoc();
                    $scheduledTimeIn = $scheduleRow['scheduledtimein'];
                    $scheduledTimeoutWith15Min = $scheduleRow['adjusted_scheduledtimeout'];
                    $scheduledTimeout = $scheduleRow['scheduledtimeout'];

                    $timeDifference = calculateTimeDifference($timeIn, $scheduledTimeIn);
                    $timeout = date('H:i:s'); // Format: HH:MM:SS

                    // Initialize $statusTimeout here
                    $statusTimeout = '';

                    if ($timeDifference > 15) {
                        $timeDifference2 = calculateTimeDifference($scheduledTimeout, $timeout);

                        if ($timeDifference2 <= 0 && $timeDifference2 >= -4) {
                            $statusTimeout = 'on-time';
                        } else if ($timeDifference2 > 0) {
                            $statusTimeout = 'early-out';
                        } else {
                            $statusTimeout = 'overtime';
                        }
                    } else {
                        // Check for statusTimeout based on the original logic
                        $timeDifference3 = calculateTimeDifference($scheduledTimeout, $timeout);
                        if ($timeDifference3 <= 15) {
                            $statusTimeout = 'on-time';
                        } else if ($timeDifference3 > 15 ) {
                            $statusTimeout = 'early-out';
                        } else {
                            $statusTimeout = 'overtime';
                        }
                        echo $timeDifference3;
                    }

                    // Update the userlogs table
                    $updateQuery = "UPDATE userlogs SET Timeout=CURRENT_TIMESTAMP, statustimeout='$statusTimeout' WHERE RFIDNumber='$RFIDNumber' AND Timeout IS NULL";

                    if ($conn->query($updateQuery) === TRUE) {
                        echo "Log updated successfully for user: $name\n";

                        // Retrieve the updated row details
                        $updatedRowQuery = "SELECT * FROM userlogs WHERE RFIDNumber='$RFIDNumber' AND Timeout=CURRENT_TIMESTAMP";
                        $updatedRowResult = $conn->query($updatedRowQuery);

                        if ($updatedRowResult && $updatedRowResult->num_rows > 0) {
                            $updatedRow = $updatedRowResult->fetch_assoc();

                            // Retrieve email from the table_the_iot_projects
                            $emailQuery = "SELECT email FROM table_the_iot_projects WHERE id = '$RFIDNumber'";
                            $emailResult = $conn->query($emailQuery);

                            if ($emailResult && $emailResult->num_rows > 0) {
                                $emailRow = $emailResult->fetch_assoc();
                                $recipientEmail = $emailRow['email'];

                                if (!empty($recipientEmail)) {
                                    // Pass the updated row details to the sendEmail function
                                    sendEmail($recipientEmail, $name, $updatedRow['Timein'], $updatedRow['Timeout'], $subject, $section, $room, $status, $statusTimeout);
                                } else {
                                    echo "Invalid or missing recipient email address for user: $name\n";
                                }
                            } else {
                                echo "Failed to retrieve email from the table_the_iot_projects table for user: $name\n";
                            }
                        } else {
                            echo "Failed to retrieve updated row details for user: $name\n";
                        }
                    } else {
                        echo "Error updating log for user: $name - " . $conn->error;
                    }
                } else {
                    echo "Failed to retrieve current schedule for user: $name\n";
                }
            }
        } else {
            echo "No logs found with Timeout as NULL\n";
        }
    }
}

// Close the database connection
$conn->close();
?>
