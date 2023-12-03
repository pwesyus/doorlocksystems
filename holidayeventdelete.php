<?php
require 'database.php';

if (!empty($_GET['heID'])) {
    $heID = $_GET['heID'];

    try {

        $sql = "DELETE FROM holidayevent WHERE heID = ?";
        $q = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($q, 'i', $heID);
        mysqli_stmt_execute($q);

        mysqli_close($conn);
        header("Location: holidayevent.php");
    } catch (Exception $e) {
        echo "Error deleting data: " . $e->getMessage();
    }
} else {
    echo "No heID provided for deletion.";
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
    <title>Delete Holiday / Event</title>
</head>

<body>
    <div class="container">
        <div class="span5 offset3">
            <form class="form-horizontal" action="" method="post"> <!-- Removed action attribute to submit to the same file -->
                <input type="hidden" name="heID" value="<?php echo $heID; ?>" />
                <p class="alert alert-error"> Are you sure you want to delete the holiday/event?</p>
                <div class="form-actions">
                    <button type="submit" class="btn btn-danger" style="margin-left: -35px;">Yes</button>
                    <a class="btn" href="holidayevent.php">No</a>
                </div>
            </form>
        </div>
    </div> <!-- /container -->
</body>

</html>
