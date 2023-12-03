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
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $accesslevel = $_POST['accesslevel'];

    // Update the data in the database
    $sql = "UPDATE table_the_iot_projects SET name=?, email=?, mobile=?, accesslevel=? WHERE id=?";
    $q = $conn->prepare($sql);
    $q->execute([$name, $email, $mobile, $accesslevel, $id]);

    // Redirect to the list of users or display a success message
    header("Location: listofuser.php");
    exit();
}

$id = null;
if (!empty($_GET['id'])) {
    $id = $_REQUEST['id'];
}

$sql = "SELECT * FROM table_the_iot_projects WHERE id = ?";
$q = $conn->prepare($sql);
$q->execute([$id]);
$data = $q->fetch(PDO::FETCH_ASSOC);
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
            
            alert("Data has been updated successfully.");
        }
    	</script>
		<style>
			html{
				margin: 60px;

			}
		</style>
		
		<title>User Edit</title>
		
	</head>
	
	<body>
		
		<div class="container">
     
			<div class="center" style="margin: 0 auto; width:495px; border-style: solid; border-color: #f2f2f2;">
				<div class="row">
					<h3 align="center">Edit User Data</h3>
				</div>
		 
				<form class="form-horizontal" action="" method="post">
					<div class="control-group">
						<label class="control-label">ID</label>
						<div class="controls">
							<input name="id" type="text"  placeholder="" value="<?php echo $data['id'];?>" readonly>
						</div>
					</div>
					
					<div class="control-group">
						<label class="control-label">Name</label>
						<div class="controls">
							<input name="name" type="text"  placeholder="" value="<?php echo $data['name'];?>" required>
						</div>
					</div>
					

					<div class="control-group">
						<label class="control-label">Email Address</label>
						<div class="controls">
							<input name="email" type="text" placeholder="" value="<?php echo $data['email'];?>" required>
						</div>
					</div>
					
					<div class="control-group">
						<label class="control-label">Mobile Number</label>
						<div class="controls">
							<input name="mobile" type="text"  placeholder="" value="<?php echo $data['mobile'];?>" required>
						</div>
					</div>

					
					<div class="control-group">
					    <label class="control-label">Access Level</label>
					    <div class="controls">
					        <select name="accesslevel" id="ALsel">
					            <option value="masterkey" <?php echo ($data['accesslevel'] === 'masterkey') ? 'selected' : ''; ?>>masterkey</option>
					            <option value="specific" <?php echo ($data['accesslevel'] === 'specific') ? 'selected' : ''; ?>>specific</option>
					        </select>
					    </div>
					</div>

					<div class="form-actions">
						<button type="submit"onclick="showUpdateNotification()"	 class="btn btn-success">Update</button>
						<a class="btn btn-danger" href="listofuser.php">Back</a>
					</div>
				</form>
			</div>               
		</div> <!-- /container -->	
		
		<script>
			
		</script>
	</body>
</html>