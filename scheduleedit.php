<?php
require_once('database.php'); // Assuming your database connection file is named 'database.php'

$schedID = null;

if (!empty($_GET['schedID'])) {
    $schedID = $_REQUEST['schedID'];
}

if (!empty($_POST)) {
    // Fetch the form data
    $id = $_POST['id'];
    $name = $_POST['name'];
    $subject = $_POST['subject'];
    $section = $_POST['section'];
    $scheduledtimein = $_POST['scheduledtimein'];
    $scheduledtimeout = $_POST['scheduledtimeout'];
    $day = $_POST['day'];
    $room = $_POST['room'];

    // Validate form data (add your validation logic here if needed)

    // Check for overlapping schedules
    if (checkForOverlap($schedID, $name, $scheduledtimein, $scheduledtimeout, $day, $room)) {
        // Display alert in the browser
        echo '<script>alert("Error: Overlapping schedule detected.");</script>';
        // Redirect back to the edit page
        echo '<script>window.location.href = "scheduleedit.php?schedID=' . $schedID . '";</script>';
        exit();
    }

    // Update the schedule in the database
    $pdo = new PDO('mysql:host=localhost;dbname=nodemcu_rfid_iot_projects', 'root', ''); // Update with your actual credentials
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $dayno = ($day == "Monday") ? 1 :
             (($day == "Tuesday") ? 2 :
             (($day == "Wednesday") ? 3 :
             (($day == "Thursday") ? 4 :
             (($day == "Friday") ? 5 :
             (($day == "Saturday") ? 6 :
             (($day == "Sunday") ? 0 : 0))))));

    $sql = "UPDATE schedule SET name=?, subject=?, section=?, scheduledtimein=?, scheduledtimeout=?, day=?, room=?, dayno=? WHERE schedID=?";
    $q = $pdo->prepare($sql);
    $q->execute(array($name, $subject, $section, $scheduledtimein, $scheduledtimeout, $day, $room, $dayno, $schedID));

    // Display success message and redirect to the scheduletable.php page after updating
    echo '<script>alert("Schedule has been updated successfully.");</script>';
    echo '<script>window.location.href = "scheduletable.php";</script>';
    exit();
} else {
    // Fetch the existing schedule data
    $pdo = new PDO('mysql:host=localhost;dbname=nodemcu_rfid_iot_projects', 'root', ''); // Update with your actual credentials
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "SELECT * FROM schedule WHERE schedID = ?";
    $q = $pdo->prepare($sql);
    $q->execute(array($schedID));
    $data = $q->fetch(PDO::FETCH_ASSOC);
}

function checkForOverlap($schedID, $name, $scheduledtimein, $scheduledtimeout, $day, $room)
{
    // Connect to the database
    $pdo = new PDO('mysql:host=localhost;dbname=nodemcu_rfid_iot_projects', 'root', ''); // Update with your actual credentials
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query to check for overlapping schedules
    $sql = "SELECT schedID FROM schedule 
        WHERE day = ? AND room = ?
        AND ((scheduledtimein <= ? AND scheduledtimeout >= ?) OR (scheduledtimein <= ? AND scheduledtimeout >= ?))
        AND schedID != ?";

    $q = $pdo->prepare($sql);
    $q->execute(array($day, $room, $scheduledtimein, $scheduledtimein, $scheduledtimeout, $scheduledtimeout, $schedID));

    // Check if there are overlapping schedules
    $overlap = $q->rowCount() > 0;

    return $overlap;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="utf-8">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <script src="js/bootstrap.min.js"></script>
    <script>
        function showUpdateNotification() {
            // Your notification logic here
        }
    </script>
    <style>
        html {
            margin: 60px;
        }
    </style>
    <title>Edit Schedule</title>
</head>

<body>
    <div class="container">
        <div class="center" style="margin: 0 auto; width:495px; border-style: solid; border-color: #f2f2f2;">
            <div class="row">
                <h3 align="center">Edit Schedule</h3>
            </div>

            <form class="form-horizontal" action="" method="post">
                <div class="control-group">
                    <label class="control-label">ID</label>
                    <div class="controls">
                        <input name="id" type="text" placeholder="" value="<?php echo $data['id'];?>" readonly>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">Name</label>
                    <div class="controls">
                        <select id="name" name="name" required>
                            <?php
                            $pdo = new PDO('mysql:host=localhost;dbname=nodemcu_rfid_iot_projects', 'root', ''); // Update with your actual credentials
                            $sqlFetchNames = "SELECT DISTINCT name FROM table_the_iot_projects WHERE accesslevel = 'specific'";
                            $result = $pdo->query($sqlFetchNames);
                            if ($result->rowCount() > 0) {
                                while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                                    $selected = ($row['name'] === $data['name']) ? 'selected' : '';
                                    echo '<option value="' . $row["name"] . '" ' . $selected . '>' . $row["name"] . '</option>';
                                }
                            }
                            $pdo = null;
                            ?>

                        </select>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Subject</label>
                    <div class="controls">
                        <input name="subject" type="text" placeholder="" value="<?php echo $data['subject'];?>" required>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Section</label>
                    <div class="controls">
                        <input name="section" type="text" placeholder="" value="<?php echo $data['section'];?>" required>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">Time in</label>
                    <div class="controls">
                        <input name="scheduledtimein" type="time" placeholder="" value="<?php echo $data['scheduledtimein'];?>" required>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">Time out</label>
                    <div class="controls">
                        <input name="scheduledtimeout" type="time" placeholder="" value="<?php echo $data['scheduledtimeout'];?>" required>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">Day</label>
                    <div class="controls">
                        <select name="day" id="daySel">
                            <option value="Monday" <?php echo ($data['day'] === 'Monday') ? 'selected' : ''; ?>>Monday</option>
                            <option value="Tuesday" <?php echo ($data['day'] === 'Tuesday') ? 'selected' : ''; ?>>Tuesday</option>
                            <option value="Wednesday" <?php echo ($data['day'] === 'Wednesday') ? 'selected' : ''; ?>>Wednesday</option>
                            <option value="Thursday" <?php echo ($data['day'] === 'Thursday') ? 'selected' : ''; ?>>Thursday</option>
                            <option value="Friday" <?php echo ($data['day'] === 'Friday') ? 'selected' : ''; ?>>Friday</option>
                            <option value="Saturday" <?php echo ($data['day'] === 'Saturday') ? 'selected' : ''; ?>>Saturday</option>
                            <option value="Sunday" <?php echo ($data['day'] === 'Sunday') ? 'selected' : ''; ?>>Sunday</option>
                        </select>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">Room</label>
                    <div class="controls">
                        <select name="room" id="roomsel">
                            <option value="CL1" <?php echo ($data['room'] === 'CL1') ? 'selected' : ''; ?>>CL1</option>
                            <option value="CL2" <?php echo ($data['room'] === 'CL2') ? 'selected' : ''; ?>>CL2</option>
                            <option value="CL3" <?php echo ($data['room'] === 'CL3') ? 'selected' : ''; ?>>CL3</option>
                            <option value="CL4" <?php echo ($data['room'] === 'CL4') ? 'selected' : ''; ?>>CL4</option>
                            <option value="CL5" <?php echo ($data['room'] === 'CL5') ? 'selected' : ''; ?>>CL5</option>
                            <option value="CL6" <?php echo ($data['room'] === 'CL6') ? 'selected' : ''; ?>>CL6</option>
                            <option value="CL7" <?php echo ($data['room'] === 'CL7') ? 'selected' : ''; ?>>CL7</option>
                            <option value="CL8" <?php echo ($data['room'] === 'CL8') ? 'selected' : ''; ?>>CL8</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-success">Update</button>
                    <a class="btn btn-danger" href="scheduletable.php">Back</a>
                </div>
            </form>
        </div>
    </div> <!-- /container -->
</body>
</html>
