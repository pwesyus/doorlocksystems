<?php
include 'database.php';
        // Check if the form is submitted
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
           
            // Retrieve form data
            $heName = $_POST['hename'];
            $heDateFrom = $_POST['date_from'];
            $heDateTo = $_POST['date_to'];

            // Insert data into the database
            $sql = "INSERT INTO holidayevent (heName, heDateFrom, heDateTo) VALUES ('$heName', '$heDateFrom', '$heDateTo')";
            
            if (mysqli_query($conn, $sql)) {
                echo '<script>alert("Holiday Saved Successfully!");</script>';
                echo '<script>setTimeout(function() { window.location = "holidayevent.php"; }, 100);</script>';
            } else {
                echo "Error: " . $sql . "<br>" . mysqli_error($conn);
            }

            // Close the database connection
            mysqli_close($conn);
        }
        ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .container {
            width: 40%;
            margin: 0 auto;
        }

        form {
            border: 2px solid #ccc;
            padding: 20px;
            background-color: #f2f2f2;
        }

        select,
        input[type="date"],
        textarea,
        input[type="text"] {
            width: 100%;
            padding: 12px 20px;
            margin: 8px 0;
            display: inline-block;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 14px 20px;
            margin: 8px 0;
            border: none;
            cursor: pointer;
        }

        .button {
            background-color: #dc3545;
            color: white;
            padding: 12.9px 20px;
            margin: 8px 0;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
    </style>

    <script>
        // Function to show/hide text input and update date inputs based on dropdown value
        function toggleHolidayInput() {
            var holidayDropdown = document.getElementById('hename');
            var customHolidayInput = document.getElementById('customHolidayInput');
            var dateFromInput = document.getElementById('date_from');
            var dateToInput = document.getElementById('date_to');

            customHolidayInput.style.display = (holidayDropdown.value === 'other') ? 'block' : 'none';

            
            switch (holidayDropdown.value) {
                
                case 'holyweek':
                    dateFromInput.value = '2024-03-28'; 
                    dateToInput.value = '2024-03-31'; 
                    break;
                case 'kagitingan':
                    dateFromInput.value = '2024-04-09'; 
                    dateToInput.value = '2024-04-09'; 
                    break;
                case 'laborday':
                    dateFromInput.value = '2024-05-01'; 
                    dateToInput.value = '2024-05-01'; 
                    break;
                case 'kalayaan':
                    dateFromInput.value = '2024-06-12'; 
                    dateToInput.value = '2024-06-12'; 
                    break;
                case 'hero':
                    dateFromInput.value = '2024-08-26'; 
                    dateToInput.value = '2024-08-26'; 
                    break;
                case 'bonifacioday':
                    dateFromInput.value = '2024-11-30'; 
                    dateToInput.value = '2024-11-30';
                    break;
                case 'christmas':
                    dateFromInput.value = '2024-12-21'; 
                    dateToInput.value = '2025-01-04'; 
                    break;
                case 'rizalday':
                    dateFromInput.value = '2024-12-30'; 
                    dateToInput.value = '2024-12-30'; 
                    break;    
                default:
                    dateFromInput.value = '';
                    dateToInput.value = '';
                    break;
            }
        }
    </script>
</head>

<body>
    <div class="container">
        <h2><center>Holidays / Events</center></h2>

        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <label for="hename">Holiday / Event Name</label>
            <select name="hename" id="hename" onchange="toggleHolidayInput()" required>
                <option value="" disabled selected>Select the Holiday / Event</option>
                <option value="holyweek">Holy Week</option>
                <option value="kagitingan">Araw ng Kagitingan</option>
                <option value="eidul-fitar">Eidul-Fitar</option>
                <option value="laborday">Labor Day</option>
                <option value="kalayaan">Independence Day</option>
                <option value="hero">National Heroes Day</option>
                <option value="bonifactioday">Bonifactio Day</option>
                <option value="christmas">Christmas / New Year Vacation</option>
                <option value="rizalday">Rizal Day</option>
                <option value="other">Other</option>
            </select>

            <!-- Text input for custom holiday name -->
            <input type="text" name="custom_hename" id="customHolidayInput" style="display: none;">

            <label for="date_from">Date From:</label>
            <input type="date" name="date_from" id="date_from" required>

            <label for="date_to">Date To:</label>
            <input type="date" name="date_to" id="date_to" required>

            <input type="submit" value="Submit">
            <a href="holidayevent.php" class="button">Back</a>
        </form>
    </div>
</body>

</html>
