<?php
session_start();
include 'database.php'; // Include your database connection file

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    processForm($conn);
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

    // Insert leave application into the database
    $sql = "INSERT INTO labsence (Name, LAbsenceFrom, LAbsenceTo, Reason, DaysLAbsence) VALUES ('$name', '$dateFrom', '$dateTo', '$reason', '$totalDays')";
    if (mysqli_query($conn, $sql)) {
        // Update TotalDaysLAbsence in the database
        updateTotalDaysLAbsence($conn, $name, $totalDays);

        echo '<script>alert("Leave of Absence Saved Successfully");</script>';
        echo '<script>setTimeout(function() { window.location = "fileleaveinsert.php"; }, 100);</script>';
    } else {
        echo '<script>alert("Error saving leave application!");</script>';
        echo '<script>setTimeout(function() { window.location = "fileleaveinsert.php"; }, 100);</script>';
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

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .container {
            width: 50%;
            margin: 0 auto;
        }

        form {
            border: 2px solid #ccc;
            padding: 20px;
            background-color: #f2f2f2;
        }

        select,
        input[type="date"],
        textarea {
            width: 100%;
            padding: 12px 20px;
            margin: 8px 0;
            display: inline-block;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 14px 20px;
            margin: 8px 0;
            border: none;
            cursor: pointer;
        }

        .button {
            background-color: #dc3545;
            color: white;
            padding: 12.9px 20px;
            margin: 8px 0;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }


    </style>
</head>

<body>
    <div class="container">
        <h2><center>File Leave of Absence</center></h2>
 
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <label for="name">Name:</label>
            <select name="name" required>
                <option value="" disabled selected>Select the name of instructor</option>
                <?php
                
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
                // mysqli_close($conn);
                ?>
            </select>

            <label for="date_from">Date From:</label>
            <input type="date" name="date_from" required>

            <label for="date_to">Date To:</label>
            <input type="date" name="date_to" required>

            <label for="reason">Reason:</label>
            <textarea name="reason" rows="4" required></textarea>

            <input type="submit" value="Submit">
            <a href="fileleavetable.php" class="button">Back</a>
        </form>
    </div>
</body>

</html>
