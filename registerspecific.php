<?php
session_start();
require 'database.php';

$Write = "<?php $" . "UIDresult=''; " . "echo $" . "UIDresult;" . " ?>";
file_put_contents('UIDContainer.php', $Write);

if (!empty($_POST)) {
    // Keep track of post values
    $name = $_POST['name'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $accesslevel = $_POST['accesslevel'];

    // Check if the ID from the schedule is empty
    $getSchedIdSql = "SELECT schedid, id FROM schedule WHERE name = ?";
    $getSchedIdQuery = $conn->prepare($getSchedIdSql);
    $getSchedIdQuery->bind_param("s", $name);
    $getSchedIdQuery->execute();
    $getSchedIdQuery->bind_result($schedid, $idFromSchedule);
    $getSchedIdQuery->fetch();
    $getSchedIdQuery->close(); // Close the result set

    // If id from the schedule is empty, use the value from the textarea
    $id = !empty($idFromSchedule) ? $idFromSchedule : $_POST['getUID'];

    // Check if the ID is already registered
    $checkSql = "SELECT COUNT(*) FROM table_the_iot_projects WHERE id = ?";
    $checkQuery = $conn->prepare($checkSql);
    $checkQuery->bind_param("s", $id);
    $checkQuery->execute();
    $checkQuery->bind_result($count);
    $checkQuery->fetch();
    $checkQuery->close(); // Close the result set

    if ($count > 0) {
        echo '<script>alert("ID is already registered!");</script>';
        echo '<script>setTimeout(function() { window.location = "registerspecific.php"; }, 100);</script>';
        exit;
    } else {
        // Insert data if the ID is not registered
        $insertSql = "INSERT INTO table_the_iot_projects (name, id, accesslevel, email, mobile) VALUES (?, ?, ?, ?, ?)";
        $insertQuery = $conn->prepare($insertSql);
        $insertQuery->bind_param("sssss", $name, $id, $accesslevel, $email, $mobile);
        $insertQuery->execute();

        $sql = "UPDATE schedule SET id=? WHERE name=?";
    $q = $conn->prepare($sql);
    $q->execute([$id, $name]);
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
                        <textarea name="getUID" id="getUID" placeholder="Please Scan RFID Card" rows="1" cols="1"  readonly required></textarea>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Name</label>
                    <div class="controls">
                        <select name="name" required>
                         <option value="" disabled selected>Select name of instructor</option>
                            <?php
                            
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
                        <input name="accesslevel" type="text" placeholder="" value="Specific" readonly required>
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
