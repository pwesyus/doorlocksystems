<?php
require 'database.php';

if (!empty($_GET['LAbsenceID'])) {
    $LAbsenceID = $_GET['LAbsenceID'];

    try {

        $sql = "DELETE FROM labsence WHERE LAbsenceID = ?";
        $q = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($q, 'i', $LAbsenceID);
        mysqli_stmt_execute($q);

        mysqli_close($conn);
        header("Location: fileleavetable.php");
    } catch (Exception $e) {
        echo "Error deleting data: " . $e->getMessage();
    }
} else {
    echo "No LAbsenceID provided for deletion.";
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
    <title>Delete Leave of Absence</title>
</head>

<body>
    <div class="container">
        <div class="span5 offset3">
            <form class="form-horizontal" action="" method="post"> <!-- Removed action attribute to submit to the same file -->
                <input type="hidden" name="LAbsenceID" value="<?php echo $LAbsenceID; ?>" />
                <p class="alert alert-error"> Are you sure you want to delete the leave of absence?</p>
                <div class="form-actions">
                    <button type="submit" class="btn btn-danger" style="margin-left: -35px;">Yes</button>
                    <a class="btn" href="fileleavetable.php">No</a>
                </div>
            </form>
        </div>
    </div> <!-- /container -->
</body>

</html>
