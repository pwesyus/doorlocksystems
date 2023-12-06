<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="user.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
   ">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <script src="js/bootstrap.min.js"></script>

</head>
<body>
    <div class="sidenav">
        <img src="images/logo.png" style="width:100px;height:70px; margin-left: 70px; margin-top: 5px; margin-bottom: 20px;">
        
        <a href="listofuser.php"><i class="fa fa-users"style="margin-right: 10px;"></i>User Directory</a>
        <button class="dropdown-btn"><i class="fa fa-calendar-o" style="margin-right: 10px;"></i>Schedule<i class="fa fa-caret-down"></i></button>
        <div class="dropdown-container">
            <a href="scheduletable.php"><i class="fa fa-calendar-plus-o"style="margin-right: 10px"></i>List of Schedule</a>
            <a href="fileleavetable.php"><i class="fa fa-file-o" style="margin-right: 10px"></i>File Leave</a>
            <a href="holidayevent.php"><i class="fa fa-calendar" style="margin-right: 10px"></i>Holiday / Event</a>
            
        </div>
        <a href="userlog.php"><i class="fa fa-clock-o" style="margin-right: 10px;"></i>Timein/Timeout of ComLab</a>
        <a href="report.php"><i class="fa fa-print" style="margin-right: 10px;"></i>Report</a>
        <a href="reset_psw.php"><i class="fa fa-cogs" style="margin-right: 10px;"></i>Change Password</a>
        <a class="right" href="login/index.php"><i class="fa fa-sign-out" style="margin-right: 10px;"></i>Logout</a>
    </div>
</body>
<script>
        var dropdown = document.getElementsByClassName("dropdown-btn");
        var i;

        for (i = 0; i < dropdown.length; i++) {
            dropdown[i].addEventListener("click", function () {
                this.classList.toggle("active");
                var dropdownContent = this.nextElementSibling;
                if (dropdownContent.style.display === "block") {
                    dropdownContent.style.display = "none";
                } else {
                    dropdownContent.style.display = "block";
                }
            });
        }
    </script>
</html>