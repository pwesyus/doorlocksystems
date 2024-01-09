<?php
require 'database.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$id = 0;
$recordSaved = false;

if (!empty($_GET['id'])) {
    $id = $_REQUEST['id'];
}

if (!empty($_POST)) {
    // Keep track of post values
    $id = $_POST['id'];

    $sql1 = "SELECT * FROM table_the_iot_projects WHERE id = ?";
    $stmt = $pdo->prepare($sql1);
    $stmt->execute([$id]);

    // Fetch a single row, not loop through all rows
    $sql1arr = $stmt->fetch(PDO::FETCH_ASSOC);

    $sql2 = "INSERT INTO archivelistofuser (id, name, accesslevel, email, mobile) VALUES (:id, :name, :accesslevel, :email, :mobile)";
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->bindParam(':id', $sql1arr['id']);
    $stmt2->bindParam(':name', $sql1arr['name']);
    $stmt2->bindParam(':accesslevel', $sql1arr['accesslevel']);
    $stmt2->bindParam(':email', $sql1arr['email']);
    $stmt2->bindParam(':mobile', $sql1arr['mobile']);
    
    if ($stmt2->execute()) {
        $recordSaved = true;
    }

    // Delete data
    $sql = "DELETE FROM table_the_iot_projects WHERE id = ?";
    $q = $pdo->prepare($sql);
    $q->execute([$id]);
    echo "<script>alert('Schedule removed successfully.'); 
    window.location.href='scheduletable.php';</script>";
        exit;

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
        html {
            margin: 70px;
        }
    </style>
    <title>Delete User</title>
</head>

<body>
    <div class="container">
        <div class="span5 offset3">
            <form class="form-horizontal" action="" method="post">
                <input type="hidden" name="id" value="<?php echo $id; ?>" />
                <?php if ($recordSaved): ?>
                    <div class="alert alert-success">Record saved to archive!</div>
                    <script>
                        setTimeout(function () {
                            document.querySelector('.alert').style.display = 'none';
                        }, 3000); // Hide the alert after 3 seconds
                    </script>
                <?php endif; ?>
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
