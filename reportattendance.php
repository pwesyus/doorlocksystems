<?php

ob_start(); // Start output buffering

require 'fpdf/fpdf.php';
require 'reportattendanceteacher.php';

$pdo = new PDO('mysql:host=localhost;dbname=nodemcu_rfid_iot_projects', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function fetchTeachers($pdo)
{
    $sql = "SELECT DISTINCT name FROM schedule";
    $stmt = $pdo->query($sql);
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $options = '<option value="" disabled selected>Select Teacher</option>';
    foreach ($teachers as $teacher) {
        $options .= '<option value="' . $teacher['name'] . '">' . $teacher['name'] . '</option>';
    }

    return $options;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["teacherName"])) {
    $teacherName = $_POST["teacherName"];
    $selectedMonth = isset($_POST["selectedMonth"]) ? $_POST["selectedMonth"] : null;
    $selectedYear = isset($_POST["selectedYear"]) ? $_POST["selectedYear"] : null;
    $startDate = isset($_POST["startDate"]) ? $_POST["startDate"] : null;
    $endDate = isset($_POST["endDate"]) ? $_POST["endDate"] : null;

    if (empty($teacherName)) {
        echo "<script>alert('Please select a teacher.');</script>";
    } else {
        // Check if either year and month or start and end date are selected
        $isMonthYearSelected = !empty($selectedYear) && !empty($selectedMonth);
        $isStartDateSelected = !empty($startDate) && !empty($endDate);

        if (!$isMonthYearSelected && !$isStartDateSelected) {
            echo "<script>alert('Please select either a year and month or a start and end date.');</script>";
        } else {
            $whereClause = " WHERE name = :teacherName";

            if ($isMonthYearSelected) {
                $whereClause .= " AND YEAR(timein) = :selectedYear AND MONTH(timein) = :selectedMonth";
            } elseif ($isStartDateSelected) {
                $whereClause .= " AND timein BETWEEN :startDate AND :endDate";
            }

            $sql = "SELECT * FROM userlogs" . $whereClause;
            $stmt = $pdo->prepare($sql);

            $stmt->bindParam(':teacherName', $teacherName);

            if ($isMonthYearSelected) {
                $stmt->bindParam(':selectedYear', $selectedYear);
                $stmt->bindParam(':selectedMonth', $selectedMonth);
            } elseif ($isStartDateSelected) {
                $stmt->bindParam(':startDate', $startDate);
                $stmt->bindParam(':endDate', $endDate);
            }

            $stmt->execute();
            $attendanceData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if headers are already sent before generating PDF
if (!headers_sent()) {
    $pdfFileName = generateTeacherAttendanceReport($pdo, $teacherName, $selectedMonth, $selectedYear, $startDate, $endDate, $attendanceData);

    // Set headers based on the action query parameter
    if ($_GET['action'] === 'view') {
        // View inline
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $pdfFileName . '"');
    } else {
        // Download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $pdfFileName . '"');
    }

    // Output the PDF file
    readfile($pdfFileName);

    // End output buffering and flush the buffer
    ob_end_flush();

    exit;
} else {
    echo "Error: Headers already sent.";
}


        }
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
            margin-left: 450px;
            width: 80%;
            overflow-x: hidden;
        }

        #content {
            margin-top: 35px;
            padding: 20px;
        }

        label {
            font-size: 20px;
            font-weight: bold;
        }

        select,
        input,
        button {
            width: 300px;
            padding: 8px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        select[name='teacherName']{
            width: 660px;
        }

        label[for='selectedMonth']{
            margin-right: 210px;
            margin-bottom: -15px;
        }

        select[name='selectedMonth']{
            margin-right: 60px;
        }

        label[for='startDate']{
            margin-right: 245px;
        }

        input[name='startDate']{
            margin-right: 60px;
        }

        button {
            margin-top: 20px;
            margin-left: 170px;
            background-color: #006400;
            color: #ffffff;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div id="content">
        <p style="font-weight: BOLD; font-size: 30px; margin-top: -25px; margin-left:180px; margin-bottom:30px;">ATTENDANCE REPORT</p>
        <form action="reportattendance.php" method="post" onsubmit="return validateForm()">
            <div class="form-group">
                <label for="teacherName">SELECT TEACHER</label><br>
                <select name="teacherName" id="teacherName" required>
                    <?php echo fetchTeachers($pdo); ?>
                </select>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="selectedMonth">SELECT MONTH</label>
                    <label for="selectedYear">SELECT YEAR</label><br>
                    <select name="selectedMonth" id="selectedMonth" onsubmit="return validateForm()" onchange="clearDateFields()">
                        <option value="all" disabled selected>Select Month</option>
                        <option value="1">January</option>
                        <option value="2">February</option>
                        <option value="3">March</option>
                        <option value="4">April</option>
                        <option value="5">May</option>
                        <option value="6">June</option>
                        <option value="7">July</option>
                        <option value="8">August</option>
                        <option value="9">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                    <select name="selectedYear" id="selectedYear" onchange="clearDateFields()" onsubmit="return validateForm()">
                        <option value="" disabled selected>Select Year</option>
                        <option value="2024">2024</option>
                        <option value="2025">2025</option>
                        <option value="2026">2026</option>
                    </select>
                </div><br>
            </div>
            <div class="form-row">
                <div class="form-group full-width">
                    <label for="startDate">START DATE</label>
                    <label for="endDate">END DATE</label><br>
                    <input type="date" name="startDate" id="startDate" onchange="clearMonthYearFields()">
                    <input type="date" name="endDate" id="endDate" onchange="clearMonthYearFields()">
                </div>
            </div>
            <div>
                <button type="submit">Print Attendance Report</button>
            </div>
        </form>
                <a href="report.php" class="btn btn-dark btn-dark-green"style="margin-top: 8px; font-weight: BOLD; font-size: 22px; margin-left: 700px; background-color: #006400;">BACK</a>
    </div>

    <script>
        function validateForm() {
            var teacherName = document.getElementById("teacherName").value;
            var selectedMonth = document.getElementById("selectedMonth").value;
            var selectedYear = document.getElementById("selectedYear").value;
            var startDate = document.getElementById("startDate").value;
            var endDate = document.getElementById("endDate").value;

            // Check if either month and year or start and end date are selected
            var isMonthYearSelected = selectedMonth !== "" && selectedYear !== "";
            var isStartDateSelected = startDate !== "" && endDate !== "";

            if (teacherName === "" || (!isMonthYearSelected && !isStartDateSelected)) {
                alert("Please select either a teacher, month, and year, or a date range.");
                return false;
            }

            // If month and year are selected, start and end date become optional
            if (isMonthYearSelected) {
                startDate = "";
                endDate = "";
            }

            // If start and end date are selected, month and year become optional
            if (isStartDateSelected) {
                selectedMonth = "";
                selectedYear = "";
            }

            return true;
        }

        function clearDateFields() {
            document.getElementById("startDate").value = "";
            document.getElementById("endDate").value = "";
        }

        function clearMonthYearFields() {
            document.getElementById("selectedMonth").value = "all";
            document.getElementById("selectedYear").value = "";
        }
    </script>
</body>

</html>
