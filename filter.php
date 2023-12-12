<?php
// Database connection settings
$servername = "localhost";  // Replace with your database server name
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$dbname = "nodemcu_rfid_iot_projects";   // Replace with your database name

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to retrieve the RFID entry and exit logs
$sql = "SELECT RFIDNumber, name, Timein, Timeout FROM userlogs";
$result = $conn->query($sql);

$filterDate = "";
$filterMonth = "";
$startDate = "";
$endDate = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $filterDate = $_POST["filterDate"];
  $filterMonth = $_POST["filterMonth"];
  $startDate = $_POST["startDate"];
  $endDate = $_POST["endDate"];

  $sql = "SELECT * FROM `userlogs` WHERE 1=1";

  if (!empty($filterDate)) {
    $sql .= " AND DATE(Timein) = '$filterDate'";
  }

  if (!empty($filterMonth)) {
    $month = date('m', strtotime($filterMonth));
    $sql .= " AND MONTH(Timein) = '$month'";
  }

  if (!empty($startDate) && !empty($endDate)) {
    $sql .= " AND Timein BETWEEN '$startDate' AND '$endDate'";
  }

  $result = $conn->query($sql);

  if (!$result) {
    die("Error: " . $conn->error);
}
}

// Check if the clear button is clicked
if (isset($_POST['clear'])) {
  // Reset the filter values
  $filterDate = "";
  $filterMonth = "";
  $startDate = "";
  $endDate = "";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

  
  <title>Timein/Timeout of ComLab</title>
  <style>
    /* Define styles for the table */
    table {
      width: 90%;
      border-collapse: collapse;
      margin-left: 50px;
      margin-top: 20px;
    }
    th, td {
      border: 1px solid #ccc;
      padding: 8px;
      text-align: center;
    }
    th {
      background-color: #4CAF50; /* Green color for title row */
      color: #fff; /* White text color for title row */
    }
    tr:nth-child(even) {
      background-color: #000; /* Black color for even rows in the body */
      color: #fff; /* White text color for even rows in the body */
    }
    html {
      font-family: Arial;
      display: inline-block;
      margin: 0px auto;
      text-align: center;
    }

    ul.topnav {
      list-style-type: none;
      margin: auto;
      padding: 0;
      overflow: hidden;
      background-color: #4CAF50;
      width: 100%;
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
    
    .table {
      margin: auto;
      width: 90%; 
    }
    
    thead {
      color: #FFFFFF;
    }
  </style>

    <script src="js/bootstrap.min.js"></script>

</head>
<body>
  <h2>ScanGuard: Door Management System</h2>
  <ul class="topnav">
      <li><a href="user data.php">List of Users</a></li>

      <li><a  class="active" href="log.php">Timein/Timeout of ComLab</a></li>
      <li><a  href="LOGIN/reset_psw.php">Settings</a></li>
      <li><a  href="login/index.php">Logout</a></li>
    </ul>
    <br>


    <div class="container">
    <form action="" method="post" class="row mb-3">
      <div class="col">
        <label for="filterDate">Filter by Date:</label>
        <input type="date" name="filterDate" id="filterDate" class="form-control" value="<?php echo $filterDate; ?>">
      </div>

      <form method="post">
      <div class="col">
        <label for="filterMonth">Filter by Month:</label>
        <select name="filterMonth" id="filterMonth" class="form-control">
          <option value="">All</option>
                <option value="January">January</option>
                <option value="February">February</option>
                <option value="March">March</option>
                <option value="April">April</option>
                <option value="May">May</option>
                <option value="June">June</option>
                <option value="July">July</option>
                <option value="August">August</option>
                <option value="September">September</option>
                <option value="October">October</option>
                <option value="November">November</option>
                <option value="December">December</option>
          
        </select>

      </div>

      <div class="col">
        <label for="startDate">Start Date:</label>
        <input type="date" name="startDate" id="startDate" class="form-control" value="<?php echo $startDate; ?>">
      </div>

      <div class="col">
        <label for="endDate">End Date:</label>
        <input type="date" name="endDate" id="endDate" class="form-control" value="<?php echo $endDate; ?>">
      </div>

      <div class="col">
        <div class="mt-4">
          <button type="submit" class="btn btn-danger">Filter</button>
          <a href="?clear=true" class="btn btn-secondary">Clear</a>
          <button onclick="window.print();" class="btn btn-dark">Print</button>

    <script>
        function printPage() {
            window.print();
        }
    </script>

          
        </div>
      </div>
      </form>


  <div id="logs-container">
    <!-- RFID logs will be displayed here -->
    <?php
    if ($result->num_rows > 0) {
        echo '<table>';
        echo '<tr>';
        echo '<th>RFID Number</th>';
        echo '<th>Name</th>';
        echo '<th>Time in</th>';
        echo '<th>Time out</th>';
        echo '</tr>';
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . $row["RFIDNumber"] . '</td>';
            echo '<td>' . $row["name"] . '</td>';
            echo '<td class="log-time">' . $row["Timein"] . '</td>';
            echo '<td class="log-time">' . $row["Timeout"] . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo "No logs found";
    }
    ?>
  </div>

  <?php
        $sql = "SELECT * FROM `userlogs`";

        // Apply filters`
        if (!empty($filterDate)) {
          $sql .= " WHERE Timein = '$filterDate'";
        } elseif (!empty($filterMonth)) {
          $sql .= " WHERE MONTH(Timein) = '$filterMonth'";
        } elseif (!empty($startDate) && !empty($endDate)) {
          $sql .= " WHERE Timein BETWEEN '$startDate' AND '$endDate'";
        }

        $result = mysqli_query($conn, $sql);
        ?>


  <script>
    // Function to update RFID logs
    function updateLogs() {
      // Send an AJAX request to fetch the logs
      $.ajax({
        url: 'log.php', // Replace with the correct URL to fetch the logs
        type: 'GET',
        success: function (data) {
          // Update the logs container with the fetched data
          $('#logs-container').html(data);
        },
        error: function () {
          console.error('Failed to fetch logs.');
        }
      });
    }

    // Call the updateLogs function initially and every 5 seconds (adjust as needed)
    updateLogs();
    setInterval(updateLogs, 5000); // Update every 5 seconds
    
    function PRINT() {
        console.log(ALLSQLCODE)
        location.href = `./Composer/speadsheet.php?sqlcode=${ALLSQLCODE}`
        /*
        $.ajax({
            url:`./Printing/printing.php`,
            type:"POST",
            data:'sqlcode='+ALLSQLCODE,
            beforeSend:function(){
                location.href = `./Printing/printing.php?sqlcode=${ALLSQLCODE}`
            },
            error: function() 
            {
                SweetError();
                reject("An error occurred.");
            },
            success:function(data){
              
                Swal.fire({
                    title: "",
                    text: "Downloaded",
                    icon: "success"
                });
            }
        }); 
        */
      }
  </script>
</body>
</html>