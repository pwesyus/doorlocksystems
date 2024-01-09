<?php
session_start();
include 'sidenav.php';
include 'database.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Define the number of rows per page
$rowsPerPage = 5;

// Calculate the current page number
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start = ($page - 1) * $rowsPerPage;

// Fetch schedules from the database
$sql = "SELECT * FROM schedule ORDER BY name ASC LIMIT $start, $rowsPerPage";
$result = $conn->query($sql);

// Count total number of rows
$totalRowsQuery = "SELECT COUNT(*) AS count FROM schedule";
$totalRowsResult = $conn->query($totalRowsQuery);
$totalRows = mysqli_fetch_assoc($totalRowsResult)['count'];

// Calculate the total number of pages
$totalPages = ceil($totalRows / $rowsPerPage);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sql = "SELECT * FROM schedule ORDER BY name ASC LIMIT $start, $rowsPerPage";
$result = $conn->query($sql);
        

if ($result === false) {
    echo "Error: " . $conn->error;
    exit(1); }
    $totalRowsQuery = "SELECT COUNT(*) AS count FROM schedule";
        $totalRowsResult = $conn->query($totalRowsQuery);
        $totalRows = mysqli_fetch_assoc($totalRowsResult)['count'];

        $totalPages = ceil($totalRows / $rowsPerPage);

    $fileName = $_FILES['import_file']['name'];
    $file_ext = pathinfo($fileName, PATHINFO_EXTENSION);

    $allowed_ext = ['xls', 'csv', 'xlsx'];

    if (in_array($file_ext, $allowed_ext)) {
        $inputFileNamePath = $_FILES['import_file']['tmp_name'];
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileNamePath);
        $data = $spreadsheet->getActiveSheet()->toArray();

        $count = 0;
        foreach ($data as $row) {
            if ($count > 0) {
                $name = $row['0'];
                $scheduledtimein = $row['1'];
                $scheduledtimeout = $row['2'];
                $subject = $row['3'];
                $section = $row['4'];
                $room = $row['5'];
                $day = $row['6'];

                // Check for overlap
                $overlapQuery = "SELECT * FROM schedule 
                                WHERE ('$scheduledtimein' < scheduledtimeout AND '$scheduledtimeout' > scheduledtimein)
                                AND room = '$room'
                                AND day = '$day'";

                $overlapResult = mysqli_query($conn, $overlapQuery);

                if (mysqli_num_rows($overlapResult) > 0) {
                    echo "<script>alert('Overlap detected for $name. Schedule not imported.');</script>";
                    echo "<script>window.location.href='scheduletable.php';</script>";
                    exit(0);
                }

                // If no overlap, insert the schedule
                $studentQuery = "INSERT INTO schedule (name, scheduledtimein, scheduledtimeout, section, room, subject,day) 
                                VALUES ('$name','$scheduledtimein','$scheduledtimeout', '$section','$room','$subject','$day')";
                $result = mysqli_query($conn, $studentQuery);

                $msg = true;
            } else {
                $count = 1;
            }
        }

        if (isset($msg)) {
             echo "<script>alert('Schedule Successfully Inserted');</script>";
            echo "<script>window.location.href='scheduletable.php';</script>";
            exit(0);
        } else {
            echo "<script>alert('Not Imported');</script>";
            echo "<script>window.location.href='scheduletable.php';</script>";
        }
    } else {
        echo "<script>alert('Invalid File');</script>";
        echo "<script>window.location.href='scheduletable.php';</script>";
        exit(0);
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>List of Schedules</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <script src="js/bootstrap.min.js"></script>
    <style>
        .container {
            width: 79%;
            margin-left: 250px;
            padding: 20px;
            margin-top: 10px;
        }

        .container h2 {
            display: inline-block;
            margin: 0;
        }

        .btn-info {
            margin-right: 10px;
        }

        .action-column {
            width: 180px; /* Adjust the width as needed */
        }

        table {
            margin-left: 15px;
            width: 100%;
            border-collapse: collapse;
            margin-top: -20px;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: darkgreen;
            color: white;
            text-align: center;
        }
       .pagination {
    margin-top: -5px;
    text-align: center;
    margin-left: 430px;
}

.pagination a {
    color: black;
    padding: 8px 16px;
    text-decoration: none;
    border: 1px solid #ddd;
    margin: 0 4px;
    display: inline-block;
}

.pagination a.active {
    background-color: #0077b6;
    color: white;
    border: 1px solid #0077b6;
}

.pagination a:hover:not(.active) {
    background-color: #ddd;
}


    </style>
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h2 class="mb-5">List of Schedules</h2>
            </div>
            <div class="col-md-6">
                <?php
                if (isset($_SESSION['message'])) {
                    echo "<h4>" . $_SESSION['message'] . "</h4>";
                    unset($_SESSION['message']);
                }
                ?>
                <div class="card-body">
                    <form action=<?php echo $_SERVER['PHP_SELF']; ?> method="POST" enctype="multipart/form-data" class="form-inline">
                        <input type="file" name="import_file" class="form-control"style="margin-top: -20px;" />
                        <button type="submit" class="btn btn-primary ml-2" style="margin-top: -20px;">Import</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="row">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th class="text-center">Name</th>
                        <th class="text-center">Time in</th>
                        <th class="text-center">Time out</th>
                        <th class="text-center">Subject</th>
                        <th class="text-center">Section</th>
                        <th class="text-center">Room</th>
                        <th class="text-center">Day</th>
                        <th class="text-center action-column">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<tr>';
                        echo '<td>' . $row['name'] . '</td>';
                        echo '<td>' . $row['scheduledtimein'] . '</td>';
                        echo '<td>' . $row['scheduledtimeout'] . '</td>';
                        echo '<td>' . $row['subject'] . '</td>';
                        echo '<td>' . $row['section'] . '</td>';
                        echo '<td>' . $row['room'] . '</td>';
                        echo '<td>' . $row['day'] . '</td>';

                        echo '<td class="text-center">';
                        echo '<a class="btn btn-info" href="scheduleedit.php?schedID=' . $row['schedID'] . '">Edit</a>';
                        echo '<a class="btn btn-danger" href="scheduledelete.php?schedID=' . $row['schedID'] . '" onclick="return confirm(\'Are you sure you want to delete this schedule?\')">Delete</a>';
                        echo '</td>';

                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>

            <div class="pagination">
                <?php
                for ($i = 1; $i <= $totalPages; $i++) {
                    echo "<a href='?page=$i' " . (($i == $page) ? "class='active'" : "") . ">$i</a>";
                }
                ?>
            </div>
        </div>
    </div>
</body>


</html>
