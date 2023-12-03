<?php
session_start();
$Write="<?php $" . "UIDresult=''; " . "echo $" . "UIDresult;" . " ?>";
file_put_contents('UIDContainer.php', $Write);


if (isset($_SESSION['registration_message'])) {
    $message = $_SESSION['registration_message'];
} else {
    $_SESSION['registration_open'] = true;
    $message = 'registration.php is now open.';
    $_SESSION['registration_message'] = $message;

    $_SESSION['registration_open'] = false;
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
            <form class="form-horizontal" action="registrationinsert.php" method="post">
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
                        <select name="accesslevel">
                            <option value="masterkey">Masterkey</option>
                            <option value="specific">Specific</option>
                        </select>
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
