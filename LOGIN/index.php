<?php
session_start();
include('../database.php');

if (isset($_POST["login"])) {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = trim($_POST['password']);

    $sql = mysqli_query($conn, "SELECT * FROM login where email = '$email'");
    $count = mysqli_num_rows($sql);

    if ($count > 0) {
        $fetch = mysqli_fetch_assoc($sql);
        $hashpassword = $fetch["password"];

        if ($fetch["status"] == 0) {
            ?>
            <script>
                alert("Please verify email account before login.");
            </script>
            <?php
        } else if (password_verify($password, $hashpassword)) {
            // Set the user's email in the session
            $_SESSION['email'] = $email;

            ?>
            <script>
                document.location.href = "../listofuser.php";
                alert("Login successful");
            </script>
            <?php
        } else {
            ?>
            <script>
                alert("Email or password invalid, please try again.");
            </script>
            <?php
        }
    } else {
        ?>
        <script>
            alert("Email or password invalid, please try again.");
        </script>
        <?php
    }
}
?>


<link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css" />
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Fonts -->
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Raleway:300,400,600" rel="stylesheet" type="text/css">

    <link rel="stylesheet" href="style.css">

    <link rel="icon" href="Favicon.png">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">

    <title>Login Form</title>
    <style>
        body {
            background-color: #ffffff; /* White background */
        }

        nav.navbar {
            background-color: #28a745; /* Green color for navbar */
        }

        nav.navbar a.navbar-brand {
            color: #ffffff !important; /* White color for navbar brand */
        }

        .card {
            border: 0;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: #28a745; /* Green color for card header */
            color: white;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }

        .card-body {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
        }

        input[type="text"],
        input[type="password"] {
            border-radius: 5px;
        }

        input[type="submit"] {
            background-color: #28a745; /* Green color for submit button */
            color: white;
            border: 0;
            border-radius: 5px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #218838; /* Darker shade of green on hover */
        }

        a.btn-link {
            color: #28a745; /* Green color for forgot password link */
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light navbar-laravel">
    <div class="container">
        <a class="navbar-brand">Login Form</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>
</nav>

<main class="login-form">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Login</h4>
                    </div>
                    <div class="card-body">
                        <form action="#" method="POST" name="login">
                            <div class="form-group row">
                                <label for="email_address" class="col-md-4 col-form-label text-md-right">E-Mail Address</label>
                                <div class="col-md-6">
                                    <input type="text" id="email_address" class="form-control" name="email" required autofocus>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="password" class="col-md-4 col-form-label text-md-right">Password</label>
                                <div class="col-md-6">
                                    <input type="password" id="password" class="form-control" name="password" required>
                                    <br>
                                    <i class="bi bi-eye-slash" id="togglePassword"> Show password</i>
                                </div>
                            </div>

                            <div class="col-md-6 offset-md-4">
                                <input type="submit" value="Login" name="login">
                                <a href="recover_psw.php" class="btn btn-link">
                                    Forgot Your Password?
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>

<script>
    const toggle = document.getElementById('togglePassword');
    const password = document.getElementById('password');

    toggle.addEventListener('click', function(){
        if(password.type === "password"){
            password.type = 'text';
        }else{
            password.type = 'password';
        }
        this.classList.toggle('bi-eye');
    });
</script>

</body>
</html>
