<?php
session_start();
require 'database.php';

$Write = "<?php $" . "UIDresult=''; " . "echo $" . "UIDresult;" . " ?>";
file_put_contents('UIDContainer.php', $Write);

if (!empty($_POST)) {
    // Keep track of post values
    $name = $_POST['name'];
    $id = $_POST['id'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $accesslevel = $_POST['accesslevel'];

    // Check if the ID is empty
    if (empty($id)) {
        echo '<script>alert("RFID Number is empty!");</script>';
        echo '<script>setTimeout(function() { window.location = "registermasterkey.php"; }, 100);</script>';
        exit;
    }

    // Check if the ID is already registered
    $checkSql = "SELECT COUNT(*) FROM table_the_iot_projects WHERE id = ?";
    $checkQuery = $conn->prepare($checkSql);
    $checkQuery->bind_param("s", $id);
    $checkQuery->execute();
    $checkQuery->bind_result($count);
    $checkQuery->fetch();

    if ($count > 0) {
        echo '<script>alert("ID is already registered!");</script>';
        echo '<script>setTimeout(function() { window.location = "registermasterkey.php"; }, 100);</script>';
        exit;
    } else {
        // Insert data if the ID is not registered
        $insertSql = "INSERT INTO table_the_iot_projects (name, id, accesslevel, email, mobile) VALUES (?, ?, ?, ?, ?)";
        $insertQuery = $conn->prepare($insertSql);
        $insertQuery->bind_param("sssss", $name, $id, $accesslevel, $email, $mobile);
        $insertQuery->execute();
        header("Location: listofuser.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="utf-8">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <script src="js/bootstrap.min.js"></script>
    <script src="jquery.min.js"></script>
    <script>
        $(document).ready(function () {
            $("#getUID").load("UIDContainer.php");
            setInterval(function () {
                $("#getUID").load("UIDContainer.php");
            }, 500);
        });
    </script>

    <style>
        html {
            font-family: Arial;
            display: inline-block;
            margin: 41px auto;
        }

        textarea {
            resize: none;
        }

        ul.topnav {
            list-style-type: none;
            margin: auto;
            padding: 0;
            overflow: hidden;
            background-color: #4CAF50;
            width: 95%;
        }

        ul.topnav li {float: left;}

        ul.topnav li a {
            display: block;
            color: white;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
        }

        ul.topnav li a:hover:not(.active) {background-color: #3e8e41;}

        ul.topnav li a.active {background-color: #333;}

        ul.topnav li.right {float: right;}

        @media screen and (max-width: 600px) {
            ul.topnav li.right, 
            ul.topnav li {float: none;}
        }
    </style>

    <title>RFID Registration</title>
</head>

<body>
    <div class="container">
        <br>
        <div class="center" style="margin: 0 auto; width:495px; border-style: solid; border-color: #f2f2f2;">
            <div class="row">
                <h3 align="center" style="margin-left: 35px; margin-top: 20px; margin-bottom: 5px;">Register RFID Card</h3>
            </div>
            <br>
            <form class="form-horizontal" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <div class="control-group">
                    <label class="control-label">RFID No.</label>
                    <div class="controls">
                        <textarea name="id" id="getUID" placeholder="Please Scan RFID Card" rows="1" cols="1" required readonly></textarea>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Name</label>
                    <div class="controls">
                        <input id="div_refresh" name="name" type="text" placeholder="" required>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Email Address</label>
                    <div class="controls">
                        <input name="email" type="text" placeholder="" required>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Mobile Number</label>
                    <div class="controls">
                        <input name="mobile" type="text" placeholder="" required>
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label">Access Level</label>
                    <div class="controls">
                        <input name="accesslevel" type="text" placeholder="" value="Masterkey" readonly required>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-success">Save</button>
                    <a href="listofuser.php" class="btn btn-danger">Back</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
