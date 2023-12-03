<?php
session_start();
include 'database.php'; // Include your database connection file

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Process the form data and set the success message
    processForm($conn);
    // Redirect to prevent form resubmission on refresh
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Function to process the form data
function processForm($conn) {
    $name = $_POST['name'];
    $dateFrom = $_POST['date_from'];
    $dateTo = $_POST['date_to'];
    $reason = $_POST['reason'];

    // Validate the number of leave days
    $leaveDays = calculateLeaveDays($dateFrom, $dateTo);

    // Calculate the total days of absence
    $totalDays = calculateTotalDays($dateFrom, $dateTo);

    // Get the current TotalDaysLAbsence from the database
    $currentTotalDaysLAbsence = getCurrentTotalDaysLAbsence($conn, $name);

    // Check if the new leave application exceeds the allowed limit
    if (($currentTotalDaysLAbsence + $totalDays) > 13) {
        $_SESSION['alert'] = 'You are not allowed to take more than 13 days of leave in a year.';
    } else {
        // Insert leave application into the database
        $sql = "INSERT INTO labsence (Name, LAbsenceFrom, LAbsenceTo, Reason, DaysLAbsence) VALUES ('$name', '$dateFrom', '$dateTo', '$reason', '$totalDays')";
        mysqli_query($conn, $sql);

        // Update TotalDaysLAbsence in the database
        updateTotalDaysLAbsence($conn, $name, $currentTotalDaysLAbsence + $totalDays);

        // Update RemainingLAbsence in the database
        updateRemainingLAbsence($conn, $name, $currentTotalDaysLAbsence + $totalDays);

        // Insert leave information into userlogs
        $rfidNumber = getRFIDNumber($conn, $name); // Pass the database connection
        $status = 'leave';
        $sqlUserLogs = "INSERT INTO userlogs (Name, RFIDNumber, Status) VALUES ('$name', '$rfidNumber', '$status')";
        mysqli_query($conn, $sqlUserLogs);

        $_SESSION['alert'] = 'Leave application submitted successfully.';
    }
}

// Function to calculate leave days
function calculateLeaveDays($dateFrom, $dateTo) {
    $start = strtotime($dateFrom);
    $end = strtotime($dateTo);

    // Calculate the number of days between start and end dates
    $leaveDays = ceil(abs($end - $start) / 86400); // 86400 seconds in a day

    return $leaveDays;
}

// Function to calculate total days of absence
function calculateTotalDays($dateFrom, $dateTo) {
    $start = new DateTime($dateFrom);
    $end = new DateTime($dateTo);
    $interval = $start->diff($end);
    return $interval->days + 1; // Include both start and end dates
}

// Function to get RFID number based on the teacher's name
function getRFIDNumber($conn, $name) {
    $sql = "SELECT id FROM table_the_iot_projects WHERE name = '$name'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);

    return isset($row['id']) ? $row['id'] : null;
}

// Function to get the current TotalDaysLAbsence from the database
function getCurrentTotalDaysLAbsence($conn, $name) {
    $sql = "SELECT TotalDaysLAbsence FROM labsence WHERE Name = '$name'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);

    return isset($row['TotalDaysLAbsence']) ? $row['TotalDaysLAbsence'] : 0;
}

// Function to update TotalDaysLAbsence in the database
function updateTotalDaysLAbsence($conn, $name, $totalDays) {
    $sql = "UPDATE labsence SET TotalDaysLAbsence = '$totalDays' WHERE Name = '$name'";
    mysqli_query($conn, $sql);
}

// Function to update RemainingLAbsence in the database
function updateRemainingLAbsence($conn, $name, $totalDays) {
    $remainingLAbsence = 13 - $totalDays; // Ensure the remaining is not negative
    $sql = "UPDATE labsence SET RemainingLAbsence = '$remainingLAbsence' WHERE Name = '$name'";
    mysqli_query($conn, $sql);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Leave Application</title>
</head>
<body>

    <h2>Teacher Leave Application</h2>

    <!-- Display the alert if available -->
    <?php if (isset($_SESSION['alert'])): ?>
        <script>alert('<?php echo $_SESSION['alert']; ?>');</script>
        <?php unset($_SESSION['alert']); ?> <!-- Clear the session variable -->
    <?php endif; ?>

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <label for="name">Name:</label>
        <select name="name" required>
            <option value="" disabled selected>Select name of instructor</option>
            <?php
            // Assuming you have a connection to the database
            include 'database.php';

            // Fetch the names from your schedule table
            $sql = "SELECT DISTINCT name FROM schedule"; // Assuming 'schedule' is the name of your table
            $result = mysqli_query($conn, $sql);

            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $name = $row['name'];
                    echo "<option value='$name'>$name</option>";
                }
            } else {
                echo "<option value=''>Error fetching names</option>";
            }

            // Close the database connection
            mysqli_close($conn);
            ?>
        </select>

        <br>

        <label for="date_from">Date From:</label>
        <input type="date" name="date_from" required>

        <br>

        <label for="date_to">Date To:</label>
        <input type="date" name="date_to" required>

        <br>

        <label for="reason">Reason:</label>
        <textarea name="reason" rows="4" required></textarea>

        <br>

        <input type="submit" value="Submit">
    </form>

</body>
</html>
