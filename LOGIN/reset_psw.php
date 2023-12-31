<?php session_start() ;
include('connect/connection.php');
?>
<link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<!------ Include the above in your HEAD tag ---------->

<!doctype html>
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <title>Login Form</title>
    <style>
           html {
      font-family: Arial;
      display: inline-block;
      margin: 0px auto;
      text-align: center;
    }

    
.fa.fa-users {
    margin-right: 10px;
}

.fa.fa-clock-o {
    margin-right: 10px;
}

.fa.fa-calendar-plus-o {
    margin-right: 10px;
}

.fa.fa-sign-out {
    margin-right: 10px;
}


ul.topnav {
    list-style-type: none;
    margin: auto;
    font-size: 18px;
    height: 50px;
    padding: 0;
    overflow: hidden;
    background-color: #4CAF50; 
    width: 100%;
}

ul.topnav li {
    float: left;
}

ul.topnav li.title {
    margin-left: 30px;
}

ul.topnav li a {
    margin: auto;
    display: block;
    color: white;
    text-align: center;
    padding: 14px 16px;
    text-decoration: none;
}

ul.topnav li a:hover:not(.active) {
    background-color: #3e8e41;
}

ul.topnav li a.active {
    background-color: #333;
}

ul.topnav li.right {
    margin-right: 15px;
    float: right;
}

     </style>
</head>
<body>
<ul class="topnav">
        <li class="title"><a  href="../user data.php"><i class="fa fa-users" style="color: #ffffff;"></i>List of Users</a></li>
        <li><a class="active" href="../log.php"><i class="fa fa-clock-o" style="color: #ffffff;"></i>Timein/Timeout of ComLab</a></li>
        <li><a href="../scheduletable.php"><i class="fa fa-calendar-plus-o" style="color: #fafcff;"></i>Schedule</a></li>
        <li><a href="login/reset_psw.php"><i class="fa fa-cogs" style="color: #fafcff;"></i>Settings</a></li>
        <li class="right"><a href="login/index.php"><i class="fa fa-sign-out" style="color: #fafcff;"></i>Logout</a></li>

    </ul>
<nav class="navbar navbar-expand-lg navbar-light navbar-laravel">

    <div class="container">
        <a class="navbar-brand" href="#">Password Reset Form</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>
</nav>

<main class="login-form">
    <div class="cotainer">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Reset Your Password</div>
                    <div class="card-body">
                        <form action="#" method="POST" name="reset_password">

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
                                    <i class="bi bi-eye-slash" id="togglePassword">Show password</i>
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
</body>
</html>

<?php
if (isset($_POST["reset"])) {
    include('connect/connection.php');
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
            window.location.replace('otp_verification.php');
        </script>
        <?php
    }
}
?>

<script>
    const toggle = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    const confirmpassword = document.getElementById('confirmpassword');

    toggle.addEventListener('click', function () {
        if (password.type === "password") {
            password.type = 'text';
        } else {
            password.type = 'password';
        }
        this.classList.toggle('bi-eye');
    });
</script>