<?php
include 'guardsidenav.php';
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
$sql = "SELECT * FROM table_the_iot_projects WHERE accesslevel = 'masterkey' ORDER BY name ASC LIMIT $offset, $rowsPerPage";
$result = $conn->query($sql);

// Count total number of rows
$totalRows = $result->num_rows;

// Calculate the total number of pages
$totalPages = ceil($totalRows / $rowsPerPage);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>List of Users</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <script src="js/bootstrap.min.js"></script>
    <style>
        .container {
            width: 80%;
            margin-left: 245px;
            padding: 20px;
        }

        .container h2 {
            display: inline-block;
            margin: 0;
        }

        .btn-master,
        .btn-custom {
            display: inline-block;
            float: right;
            font-size: 16px;
            padding: 10px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .btn-master {
            background-color: blue;
            color: #fff;
        }

        .btn-custom {
            background-color: darkgreen;
            color: #fff;
        }

        .btn-master:hover {
            background-color: #0077b6;
        }

        .btn-custom:hover {
            background-color: #0a5c36;
        }

        .fa.fa-plus {
            margin-right: 10px;
        }

        .btn.btn-info {
            float: center;
            margin-left: 4px;
            width: 100px;
        }

        .btn.btn-danger {
            margin-right: -5px;
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
            text-align: left;
        }

        th {
            background-color: darkgreen;
            color: white;
            text-align: center;
        }
        .pagination {
        display: flex;
        justify-content: center;
        margin-top: 10px;

    }

    .pagination a {
        color: white;
        background-color: #0077b6;  
        padding: 8px 16px;
        text-decoration: none;
        border: 1px solid #ddd;
        margin: 0 4px;
        display: inline-block;
    }

    .pagination a.active {
        background-color: #0077b6 !important;
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
        <h2>User Directory</h2>
        <a class="btn btn-primary btn-master" href="guardregistermasterkey.php" style="margin-right: 5px"> <i class="fa fa-plus"></i> Add Masterkey</a>

        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>RFID No.</th>
                    <th>Name</th>
                    <th>Access Level</th>
                    <th>Email</th>
                    <th>Mobile Number</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($result as $row) {
                    echo '<tr>';
                    echo '<td>' . $row['id'] . '</td>';
                    echo '<td>' . $row['name'] . '</td>';
                    echo '<td>' . $row['accesslevel'] . '</td>';
                    echo '<td>' . $row['email'] . '</td>';
                    echo '<td>' . $row['mobile'] . '</td>';
                    echo '<td><a class="btn btn-info" style="text-decoration: none;" href="guardlistofuseredit.php?id=' . $row['id'] . '">Edit</a>';
                    echo ' ';
                    echo '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>

        <?php
       

        echo '<div class="pagination">';
        for ($i = 1; $i <= $totalPages; $i++) {
            echo '<a href="' . $_SERVER['PHP_SELF'] . '?page=' . $i . '" class="page-link">' . $i . '</a>';
        }
        echo '</div>';
        ?>
        </div>
    </div>
</body>

</html>
