<?php
require 'database.php';

if (!empty($_GET['schedID'])) {
    $schedID = $_GET['schedID'];

    try {
        // Create DB Connection
        $conn = mysqli_connect("localhost", "root", "", "nodemcu_rfid_iot_projects");

        // Check connection
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }

        $sql1 = "SELECT * FROM schedule WHERE schedID = ?";
        $stmt = mysqli_prepare($conn, $sql1);
        mysqli_stmt_bind_param($stmt, 'i', $schedID);
        mysqli_stmt_execute($stmt);

        // Fetch a single row, not loop through all rows
        $sql1arr = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if (!$sql1arr) {
            echo "<script>alert('Schedule is already removed.'); window.location.href='scheduletable.php';</script>";
            exit;
        }

        $sql2 = "INSERT INTO archiveschedule (id, name, subject, section, scheduledtimein, scheduledtimeout, room, day, dayno) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt2 = mysqli_prepare($conn, $sql2);
        mysqli_stmt_bind_param($stmt2, 'isssssssi', $sql1arr['id'], $sql1arr['name'], $sql1arr['subject'], $sql1arr['section'], $sql1arr['scheduledtimein'], $sql1arr['scheduledtimeout'], $sql1arr['room'], $sql1arr['day'], $sql1arr['dayno']);
        
        if (mysqli_stmt_execute($stmt2)) {
            $recordSaved = true;
        }

        $sql = "DELETE FROM schedule WHERE schedID = ?";
        $stmtDelete = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmtDelete, 'i', $schedID);
        mysqli_stmt_execute($stmtDelete);

        mysqli_close($conn);
        echo "<script>alert('Schedule removed successfully.'); window.location.href='scheduletable.php';</script>";
        exit;
    } catch (Exception $e) {
        echo "Error deleting data: " . $e->getMessage();
    }
} else {
    echo "No schedID provided for deletion.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <script src="js/bootstrap.min.js"></script>
    <style>
        html {
            margin: 70px;
        }
    </style>
    <title>Delete Schedule</title>
</head>

<body>
    <div class="container">
        <div class="span5 offset3">
            <form class="form-horizontal" action="" method="post"> <!-- Removed action attribute to submit to the same file -->
                <input type="hidden" name="schedID" value="<?php echo $schedID; ?>" />
                <p class="alert alert-error"> Are you sure you want to delete the schedule?</p>
                <div class="form-actions">
                    <button type="submit" class="btn btn-danger" style="margin-left: -35px;">Yes</button>
                    <a class="btn" href="scheduletable.php">No</a>
                </div>
                <?php if (isset($recordSaved) && $recordSaved): ?>
                    <div class="alert alert-success">Record saved to archive!</div>
                <?php endif; ?>
            </form>
        </div>
    </div> <!-- /container -->
</body>

</html>
