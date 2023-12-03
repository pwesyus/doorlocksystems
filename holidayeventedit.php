<?php
require 'database.php';

try {
    $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the data from the form
    $heID = $_POST['heID'];
    $heName = $_POST['heName'];
    $heDateFrom = $_POST['heDateFrom'];
    $heDateTo = $_POST['heDateTo'];

    // Update the data in the database
    $sql = "UPDATE labsence SET heName=?, heDateFrom=?, heDateTo=? WHERE heID=?";
    $q = $conn->prepare($sql);
    $q->execute([$heName, $heDateFrom, $heDateTo, $heID]);

    // Redirect to the list of users or display a success message
    header("Location: holidayevent.php");
    exit();
}

$data = null; // Initialize $data

if (!empty($_GET['heID'])) {
    $heID = $_GET['heID'];

    $sql = "SELECT * FROM holidayevent WHERE heID = ?";
    $q = $conn->prepare($sql);
    $q->execute([$heID]);

    // Check if data is found
    if ($q->rowCount() > 0) {
        $data = $q->fetch(PDO::FETCH_ASSOC);
    } else {
        // Handle case when no data is found (e.g., redirect to an error page)
        
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta charset="utf-8">
		<link href="css/bootstrap.min.css" rel="stylesheet">
		<script src="js/bootstrap.min.js"></script>
		<script>
        
        function showUpdateNotification() {
            
            alert("Holiday / Event has been updated successfully.");
        }
    	</script>
		<style>
			html{
				margin: 60px;

			}
		</style>
		
		<title>Holiday / Event Edit</title>
		
	</head>
	
	<body>
		
		<div class="container">
     
			<div class="center" style="margin: 0 auto; width:455px; border-style: solid; border-color: #f2f2f2;">
				<div class="row">
					<h3 align="center">Edit Holiday / Event</h3>
				</div>
		 
				<form class="form-horizontal" action="" method="post">
			
					
				<div class="control-group">
						<label class="control-label">Holiday / Event Name</label>
						<div class="controls">
							<input name="heName" type="text"  placeholder="" value="<?php echo $data['heName'];?>" required>
							
						</div>
					</div>
					
					
					<div class="control-group">
					    <label class="control-label">Date From</label>
					    <div class="controls">
					        <input name="heDateFrom" type="text"  placeholder="" value="<?php echo $data['heDateFrom'];?>" required>

					    </div>
					</div>

					<div class="control-group">
						<label class="control-label">Date To</label>
						<div class="controls">
							<input name="heDateTo" type="text"  placeholder="" value="<?php echo $data['heDateTo'];?>" required>

						</div>
					</div>

					<div class="form-actions">
						<button type="submit"onclick="showUpdateNotification()"	 class="btn btn-success">Update</button>
						<a class="btn btn-danger" href="holidayevent.php">Back</a>
					</div>
				</form>
			</div>               
		</div> <!-- /container -->	
		
		<script>
			
		</script>
	</body>
</html>