<?php
include 'sidenav.php';
include 'database.php';

// Set the number of rows per page
$rowsPerPage = 5;

// Determine the current page
if (isset($_GET['page']) && is_numeric($_GET['page'])) {
    $currentPage = $_GET['page'];
} else {
    $currentPage = 1;
}

// Calculate the offset for the SQL query
$offset = ($currentPage - 1) * $rowsPerPage;

// Fetch user data with pagination
$sql = "SELECT * FROM labsence ORDER BY name ASC LIMIT $offset, $rowsPerPage";
$result = $conn->query($sql);

// Count total number of rows
$totalRowsQuery = $conn->query("SELECT COUNT(*) AS count FROM labsence");
$totalRowsAssoc = mysqli_fetch_assoc($totalRowsQuery);
$totalRows = $totalRowsAssoc['count'];

// Calculate the total number of pages
$totalPages = ceil($totalRows / $rowsPerPage);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>List of Leave of Absence</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <script src="js/bootstrap.min.js"></script>
    <style>
        .container {
            width: 79%;
            margin-left: 250px;
            padding: 20px;
        }

        .container h2 {
            display: inline-block;
            margin: 0; /* Remove default margin to avoid extra space */
        }

        .btn-custom {
            display: inline-block;
            float: right;
            font-size: 16px;
            background-color: darkgreen;
            color: #fff;
            padding: 10px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .fa.fa-plus {
            margin-right: 10px;
        }

        .btn.btn-info {
            margin-right: 5px;
            margin-left: 4px;
        }

        .btn.btn-danger {
            margin-right: 0px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: darkgreen;
            color: white;
            text-align: center;
        }

        .pagination {
            margin-top: 10px;
            display: flex;
            justify-content: center;
        }

        .pagination a {
            color: black;
            float: left;
            padding: 8px 16px;
            text-decoration: none;
            border: 1px solid #ddd;
            margin: 0 4px;
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
        <h2>List of Leave of Absence</h2>
        <a class="btn btn-success btn-custom" href="fileleaveinsert.php"> <i class="fa fa-plus"></i> Add Leave of Absence</a>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Leave From</th>
                    <th>Leave To</th>
                    <th>Reason</th>
                    <th>Days of Leave</th>
                    <th>Total Days</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM labsence ORDER BY name ASC LIMIT $offset, $rowsPerPage";
                foreach ($conn->query($sql) as $row) {
                    echo '<tr>';
                    echo '<td>' . $row['Name'] . '</td>';
                    echo '<td>' . $row['LAbsenceFrom'] . '</td>';
                    echo '<td>' . $row['LAbsenceTo'] . '</td>';
                    echo '<td>' . $row['Reason'] . '</td>';
                    echo '<td>' . $row['DaysLAbsence'] . '</td>';
                    echo '<td>' . $row['TotalDaysLAbsence'] . '</td>';
                    echo '<td><a class="btn btn-danger" href="fileleavedelete.php?LAbsenceID=' . $row['LAbsenceID'] . '" onclick="return confirm(\'Are you sure you want to delete this schedule?\')">Delete</a>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php
            for ($i = 1; $i <= $totalPages; $i++) {
                echo "<a href='?page=$i' " . (($i == $currentPage) ? "class='active'" : "") . ">$i</a>";
            }
            ?>
               
        </div>
    </div>
</body>

</html>
