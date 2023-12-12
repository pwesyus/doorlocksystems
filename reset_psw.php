<?php
session_start();
include('database.php');
include 'sidenav.php';

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    // Redirect to the login page or perform other actions for non-logged-in users
    header('Location:LOGIN/index.php');
    exit();
}

// Get the logged-in email
$loggedInEmail = $_SESSION['email'];


if (isset($_POST["reset"])) {
    $psw = $_POST["password"];
    $confirm_psw = $_POST["confirmpassword"];

    if ($psw !== $confirm_psw) {
        ?>
        <script>
            alert("<?php echo "Passwords do not match. Please try again."; ?>");
        </script>
        <?php
        exit(); // Stop further execution if passwords don't match
    }

    $email = $_SESSION['email'];
    $otp = rand(100000, 999999);
    $_SESSION['otp'] = $otp;

    require "Mail/phpmailer/PHPMailerAutoload.php";
    $mail = new PHPMailer;

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 587;
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = 'tls';

    $mail->Username = 'developer.doorlock@gmail.com';
    $mail->Password = 'olftvsjikbqhjkod';

    $mail->setFrom('developer.doorlock@gmail.com', 'OTP Verification');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = "Your password reset OTP code";
    $mail->Body = "<p>Dear user, </p> <h3>Your password reset OTP code is $otp <br></h3>";

    if (!$mail->send()) {
        ?>
        <script>
            alert("<?php echo "Failed to send OTP. Please try again."; ?>");
        </script>
        <?php
    } else {
        ?>
        <script>
            alert("<?php echo "OTP sent to " . $email; ?>");
            window.location.replace('LOGIN/otp_verification.php');
        </script>
        <?php
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Fonts -->
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <link rel="stylesheet" href="style.css">

    <link rel="icon" href="Favicon.png">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <style>
        body {
            margin-left: 240px;
        }

        main.login-form {
            margin-top: 50px;
        }

        form {
            padding: 20px;
        }

        input[type="submit"] {
            background-color: green;
            color: #fff;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
        }
    </style>

</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light navbar-laravel">
    <div class="container">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>
</nav>

<main class="login-form">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Reset Your Password</div>
                    <div class="card-body">
                        <form action="#" method="POST" name="reset_password">


                            <!-- Display logged-in email -->
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label text-md-right"> Email</label>
                                <div class="col-md-6">
                                    <p class="form-control-plaintext"><?php echo $_SESSION['email']; ?></p>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="password" class="col-md-4 col-form-label text-md-right">New Password</label>
                                <div class="col-md-6">
                                    <input type="password" id="password" class="form-control" name="password" required autofocus>
                                    <br>
                                </div>
                                <label for="confirmpassword" class="col-md-4 col-form-label text-md-right">Confirm Password</label>
                                <div class="col-md-6">
                                    <input type="password" id="confirmpassword" class="form-control" name="confirmpassword" required autofocus>
                                    <br>
                                    <i class="bi bi-eye-slash" id="togglePassword" style="cursor: pointer; margin-right: 5px; color: black">Show password</i>

                                </div>
                            </div>


                            <div class="col-md-6 offset-md-4">
                                <input type="submit" value="Reset" name="reset">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirmpassword');

        togglePassword.addEventListener('click', function () {
            const type = password.type === 'password' ? 'text' : 'password';
            password.type = type;
            confirmPassword.type = type;
            this.classList.toggle('bi-eye');
        });
    });
</script>

</body>
</html>
