<?php
require 'database.php';

if (!empty($_POST)) {
    // Keep track of post values
    $name = $_POST['name'];
    $id = $_POST['id'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $accesslevel = $_POST['accessevel'];
    

    // Check if the ID is already registered
    $checkSql = "SELECT COUNT(*) FROM table_the_iot_projects WHERE id = ?";
    $checkQuery = $conn->prepare($checkSql);
    $checkQuery->bind_param("s", $id);
    $checkQuery->execute();
    $checkQuery->bind_result($count);
    $checkQuery->fetch();

    if ($count > 0) {
        echo '<script>alert("ID is already registered!");</script>';
        echo '<script>setTimeout(function() { window.location = "registerspecific.php"; }, 100);</script>';
        exit;
    } else {
        // Insert data if the ID is not registered
        $insertSql = "INSERT INTO table_the_iot_projects (name, id, accesslevel, email, mobile) VALUES (?, ?, ?, ?, ?)";
        $insertQuery = $conn->prepare($insertSql);
        $insertQuery->bind_param("sssss", $name, $id, $accessLevel, $email, $mobile);
        $insertQuery->execute();
        header("Location: listofuser.php");
        exit;
    }
}

?>
