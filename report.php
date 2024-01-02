<?php

require('reportbyteacher.php');
require('reportbyroom.php');
require('reportbylistofuser.php');

$pdo = new PDO('mysql:host=localhost;dbname=nodemcu_rfid_iot_projects', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function fetchTeachers($pdo)
{
    $sql = "SELECT DISTINCT name FROM schedule";
    $stmt = $pdo->query($sql);
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $options = '<option value="">Select Teacher</option>';
    foreach ($teachers as $teacher) {
        $options .= '<option value="' . $teacher['name'] . '">' . $teacher['name'] . '</option>';
    }

    return $options;
}

function fetchRooms($pdo)
{
    $sql = "SELECT DISTINCT room FROM schedule";
    $stmt = $pdo->query($sql);
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $options = '<option value="">Select Room</option>';
    foreach ($rooms as $room) {
        $options .= '<option value="' . $room['room'] . '">' . $room['room'] . '</option>';
    }

    return $options;
}

function fetchSchedules($pdo, $teacherName = null, $room = null, $day = null)
{
    if ($day === 'all') {
        $sql = "SELECT id, name, day, room, scheduledtimein, scheduledtimeout FROM schedule WHERE (name = ? OR room = ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$teacherName, $room]);
    } else {
        $sql = "SELECT id, name, day, room, scheduledtimein, scheduledtimeout FROM schedule WHERE (name = ? OR room = ?) AND day = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$teacherName, $room, $day]);
    }

    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $schedules;
}

function fetchUserSchedules($pdo, $selectedAccessLevel)
{
    $stmt = null;

    if ($selectedAccessLevel === 'all') {
        $sql = "SELECT id, name, accesslevel, email, mobile FROM table_the_iot_projects";
        $stmt = $pdo->query($sql);
    } elseif ($selectedAccessLevel === 'masterkey') {
        $sql = "SELECT id, name, accesslevel, email, mobile FROM table_the_iot_projects WHERE accesslevel = 'masterkey'";
        $stmt = $pdo->query($sql);
    } elseif ($selectedAccessLevel === 'specific') {
        $sql = "SELECT id, name, accesslevel, email, mobile FROM table_the_iot_projects WHERE accesslevel = 'specific'";
        $stmt = $pdo->query($sql);
    }

    if ($stmt) {
        $userData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $userData;
    } else {
        return [];
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["teacherName"])) {
    $teacherName = $_POST["teacherName"];
    $sql = "SELECT id FROM schedule WHERE name = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$teacherName]);
    $teacherId = $stmt->fetchColumn();
    $schedules = fetchSchedules($pdo, $teacherName);

    if (!empty($schedules)) {
        $pdfFileName = generatePDF($teacherName, $teacherId, $schedules);

        header('Content-Type: application/pdf');
        header('Content-Transfer-Encoding: Binary');
        header('Content-Disposition: inline; filename="' . $pdfFileName . '"');
        readfile($pdfFileName);
        exit;
    } else {
        echo "No schedules found for the selected teacher";
    }
} else if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["room"])) {
    $room = $_POST["room"];
    $day = $_POST["day"];
    $schedules = fetchSchedules($pdo, null, $room, $day);

    if (!empty($schedules)) {
        $pdfFileName = generateRoomPDF($room, $schedules);

        header('Content-Type: application/pdf');
        header('Content-Transfer-Encoding: Binary');
        header('Content-Disposition: inline; filename="' . $pdfFileName . '"');
        readfile($pdfFileName);
        exit;
    } else {
        echo "No schedules found for the selected room and day";
    }
} else if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["selectedAccessLevel"])) {
    $selectedAccessLevel = $_POST["selectedAccessLevel"];
    $userSchedules = fetchUserSchedules($pdo, $selectedAccessLevel);

    if (!empty($userSchedules)) {
        $pdfFileName = generateUsersPDF($userSchedules);

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $pdfFileName . '"');
        readfile($pdfFileName);
        exit;
    } else {
        echo "No schedules found for the selected access level";
    }
}

include('sidenav.php');

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Generate Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="user.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <script src="js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <style>
        body {
            margin-left: 370px;
            width: 80%;
            overflow-x: hidden;
        }

        #content {
            margin-top: 25px;
            padding: 20px;

        }

        .btn-dark-green {
            background-color: #006400;
            color: #ffffff;
        }

       

        button {
            display: inline-block;
      
            width: 40%;
        }
    </style>

</head>

<body>

    <div id="content">
        <form action="report.php" method="post" id="scheduleForm" target="_blank">
            <div class="form-row">
                <div class="col-md-6">
                    <h3>Generate Teacher's schedule</h3>
                    <select name="teacherName" id="teacherSelect" class="form-control">
                        <?php echo fetchTeachers($pdo); ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <input type="hidden" name="teacherName" id="teacherName" value=""><br><br>
                    <button type="submit" name="printScheduleBtn" class="btn btn-dark btn-dark-green"style="margin-top: -7px;">Print Teacher Schedule</button><br><br>
                </div>
            </div>
        </form>

        <form action="report.php" method="post" id="roomScheduleForm" target="_blank">
    <div class="form-row">
        <div class="col-md-4">
            <h3>Generate room schedule</h3>
            <select name="room" id="roomSelect" class="form-control"style="width: 265px;">
                <?php echo fetchRooms($pdo); ?>
            </select>
        </div>
        <div class="col-md-3">
            <select name="day" id="daySelect" class="form-control" style="margin-top: 40px; margin-left: -90px;">
                <option value="" selected disabled>Select Day</option>
                <option value="all">All Days</option>
                <option value="Monday">Monday</option>
                <option value="Tuesday">Tuesday</option>
                <!-- Add more days as needed -->
            </select>
        </div>
        <div class="col-md-6">
            <input type="hidden" name="room" id="room" value="">
            <input type="hidden" name="day" id="day" value="">
            <button type="submit" name="printRoomScheduleBtn" class="btn btn-dark btn-dark-green" style="margin-left: 538px; margin-top:-70px;">Print Room Schedule</button><br>
        </div>
    </div>
</form>


        <form action="report.php" method="post" id="userScheduleForm" target="_blank">
            <div class="form-row">
                <div class="col-md-6">
                    <h3>Generate list of users</h3>
                    <select name="selectedAccessLevel" id="accessLevelSelect" class="form-control">
                        <option value="" selected disabled>Select Users</option>
                        <option value="all">All Users</option>
                        <option value="masterkey">MasterKey Users</option>
                        <option value="specific">Specific Users</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <input type="hidden" name="selectedAccessLevel" id="selectedAccessLevel" value=""><br><br>
                    <button type="submit" name="printUserScheduleBtn" class="btn btn-dark btn-dark-green"style="margin-top: -7px;">Print List of User</button><br><br>
                </div>
            </div>
        </form>
    </div>

    <script>
        $(document).ready(function () {
            $('#scheduleForm').submit(function () {
                var selectedTeacher = $('#teacherSelect').val();
                if (selectedTeacher) {
                    $('#teacherName').val(selectedTeacher);
                } else {
                    alert('Please select a teacher before generating the PDF.');
                    return false;
                }
            });

             $('#roomScheduleForm').submit(function () {
                var selectedRoom = $('#roomSelect').val();
                var selectedDay = $('#daySelect').val();

                if (selectedRoom) {
                    $('#room').val(selectedRoom);

                    if (selectedDay && selectedDay !== 'all') {
                        $('#day').val(selectedDay);
                    } else {
                        // If "All" is selected or no day is selected, set day to 'all'
                        $('#day').val('all');
                    }
                } else {
                    alert('Please select a room before generating the PDF.');
                    return false;
                }
            });

            $('#userScheduleForm').submit(function () {
                var selectedAccessLevel = $('#accessLevelSelect').val();
                if (selectedAccessLevel) {
                    $('#selectedAccessLevel').val(selectedAccessLevel);
                } else {
                    alert('Please select an access level before generating the PDF.');
                    return false;
                }
            });
        });
    </script>
</body>

</html>
