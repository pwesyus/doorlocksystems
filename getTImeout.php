<?php

include 'database.php';
$jsonData = file_get_contents('php://input');

// Check if any data was received
if ($jsonData) {
    // Decode the JSON data into a PHP associative array
    $data = json_decode($jsonData, true);

    // Check if the JSON data was successfully decoded
    if ($data !== null) {
        // Extract the values from the JSON data
        $RFIDNumber = $data['RFIDNumber'];


        // Update RFID exit time in the database
        $sql = "UPDATE userlogs SET Timeout=CURRENT_TIMESTAMP WHERE RFIDNumber='$RFIDNumber' AND Timeout IS NULL";

        if ($conn->query($sql) === TRUE) {
            echo "Log updated successfully";

            // Query to retrieve the email from the table_the_iot_projects
            $query2 = "SELECT email FROM table_the_iot_projects WHERE id = '$RFIDNumber'";
            $result2 = $conn->query($query2);

            if ($result2) {
                $row2 = $result2->fetch_assoc();
                $email = $row2['email'];

                if (!empty($email)) {
                    // Query to select the updated timeout row with name, timein, and timeout
                    $query = "SELECT name, Timein, Timeout, status
                      FROM userlogs
                      WHERE RFIDNumber = '$RFIDNumber' AND Timeout IS NOT NULL
                      ORDER BY Timeout DESC
                      LIMIT 1";
                    $result = $conn->query($query);

                    if ($result && $result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        $name = $row['name'];
                        $timeIn = $row['Timein'];
                        $timeOut = $row['Timeout'];
                        $status = $row['status'];

                        // Send an email to the user who updated the timeout
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
                        $mail->Body = "Hi Ms./Mr. $name, <br><br>       You entered Computer Laboratory 8 with the <br>       Time In: $timeIn and <br>       Time Out: $timeOut <br>       Status: $status <br> <br><br> Door Lock System ";

                        if (!$mail->send()) {
                            echo "Email could not be sent for user: $name\n";
                            echo 'Mailer Error: ' . $mail->ErrorInfo;
                        } else {
                            echo "Email sent successfully for user: $name\n";
                        }
                    } else {
                        echo "Failed to retrieve user information from the database.";
                    }
                } else {
                    echo "Invalid or missing recipient email address for user: $name\n";
                }
            } else {
                echo "Failed to retrieve email from the table_the_iot_projects table for user: $name\n";
            }
        } else {
            echo "Error updating log: " . $conn->error;
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
