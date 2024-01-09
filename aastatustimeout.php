<?php
include 'database.php';

// Function to calculate the time difference in minutes
function calculateTimeDifference($time1, $time2)
{
    $diff = strtotime($time1) - strtotime($time2);
    return round($diff / 60);
}

// Function to send an email
function sendEmail($email, $name, $timeIn, $timeOut, $status)
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
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'User Entry Notification';
    $mail->Body = "Hi Ms./Mr. $name, <br><br> You entered Computer Laboratory 8 with the <br> Time In: $timeIn and <br> Time Out: $timeOut <br> Status: $status <br> <br><br> Door Lock System ";

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

        // Query to retrieve the last log from userlogs where the timeout is null
        $logQuery = "SELECT name, Timein, status FROM userlogs WHERE RFIDNumber='$RFIDNumber' AND Timeout IS NULL ORDER BY Timein DESC LIMIT 1";
        $logResult = $conn->query($logQuery);

        // Set the time zone to Asia/Manila
        date_default_timezone_set('Asia/Manila');

        if ($logResult && $logResult->num_rows > 0) {
            $logRow = $logResult->fetch_assoc();
            $name = $logRow['name'];
            $timeIn = $logRow['Timein'];
            $status = $logRow['status'];

            // Check if the status is masterkey
            if ($status == 'masterkey') {
                $statusTimeout = 'masterkey';
            } else {
                // Retrieve current schedule
                $currentTime = strtotime("now");
                $currentDay = date("w", $currentTime);
                $currentTimeString = date("H:i:s", $currentTime);
                $assignedRoom = "CL8";  // Replace with the actual room value

                // Assuming $RFIDNumber is available from your previous code
                $scheduleQuery = "SELECT name, subject, room, scheduledtimein, scheduledtimeout FROM schedule WHERE id = '$RFIDNumber' AND room = '$assignedRoom' AND dayno = $currentDay AND scheduledtimein <= '$currentTimeString' AND scheduledtimeout >= '$currentTimeString' ORDER BY scheduledtimein DESC LIMIT 1";

                $scheduleResult = $conn->query($scheduleQuery);

                if ($scheduleResult && $scheduleResult->num_rows > 0) {
                    $scheduleRow = $scheduleResult->fetch_assoc();
                    $scheduledTimeIn = $scheduleRow['scheduledtimein'];
                    $scheduledTimeout = $scheduleRow['scheduledtimeout'];

                    // Calculate the time difference in minutes
                    $timeDifference = calculateTimeDifference($timeIn, $scheduledTimeIn);

                    // Check if the user is on time, early out, or overtime
                    if ($timeDifference <= 15) {
                        $statusTimeout = 'on-time';
                    } elseif ($timeDifference > 5) {
                        $statusTimeout = 'early-out';
                    } elseif ($timeDifference < -5) {
                        $statusTimeout = 'overtime';
                    }
                } else {
                    echo "Failed to retrieve current schedule for user: $name\n";
                    exit;
                }
            }

            // Update the userlogs table with the calculated status timeout
            $updateQuery = "UPDATE userlogs SET Timeout=CURRENT_TIMESTAMP, statustimeout='$statusTimeout' WHERE RFIDNumber='$RFIDNumber' AND Timeout IS NULL";

            if ($conn->query($updateQuery) === TRUE) {
                echo "Log updated successfully for user: $name\n";

                // Retrieve email from the table_the_iot_projects
                $emailQuery = "SELECT email FROM table_the_iot_projects WHERE id = '$RFIDNumber'";
                $emailResult = $conn->query($emailQuery);

                if ($emailResult && $emailResult->num_rows > 0) {
                    $emailRow = $emailResult->fetch_assoc();
                    $recipientEmail = $emailRow['email'];

                    if (!empty($recipientEmail)) {
                        // Send email to the user
                        sendEmail($recipientEmail, $name, $scheduledTimeIn, $scheduledTimeout, $statusTimeout);
                    } else {
                        echo "Invalid or missing recipient email address for user: $name\n";
                    }
                } else {
                    echo "Failed to retrieve email from the table_the_iot_projects table for user: $name\n";
                }
            } else {
                echo "Error updating log for user: $name - " . $conn->error;
            }
        } else {
            echo "No logs found with Timeout as NULL\n";
        }
    }
}

// Close the database connection
$conn->close();
?>
