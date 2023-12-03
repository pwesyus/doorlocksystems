<?php
    require 'database.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$id = 0;

if (!empty($_GET['id'])) {
    $id = $_REQUEST['id'];
}

if (!empty($_POST)) {
    // Keep track of post values
    $id = $_POST['id'];

    // Delete data
    $sql = "DELETE FROM table_the_iot_projects WHERE id = ?";
    $q = $pdo->prepare($sql);
    $q->execute([$id]);

    header("Location: listofuser.php");
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <link   href="css/bootstrap.min.css" rel="stylesheet">
    <script src="js/bootstrap.min.js"></script>
    <style>
        html{
            margin: 70px;
        }

    </style>
	<title>Delete User</title>
</head>
 
<body>


    <div class="container">
     
		<div class="span5 offset3">
		

			<form class="form-horizontal" action="" method="post">
				<input type="hidden" name="id" value="<?php echo $id;?>"/>
				<p class="alert alert-error"> Are you sure you want to delete the user?</p>
				<div class="form-actions">
					<button type="submit" class="btn btn-danger" style="margin-left: -35px;">Yes</button>
					<a class="btn" href="listofuser.php">No</a>
				</div>
			</form>
		</div>
                 
    </div> <!-- /container -->
  </body>
</html>