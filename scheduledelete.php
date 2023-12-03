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

        $sql = "DELETE FROM schedule WHERE schedID = ?";
        $q = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($q, 'i', $schedID);
        mysqli_stmt_execute($q);

        mysqli_close($conn);
        header("Location: scheduletable.php");
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
            </form>
        </div>
    </div> <!-- /container -->
</body>

</html>
