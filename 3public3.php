<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment</title>
    <link rel="stylesheet" type="text/css" href="styling.css">
</head>
<body>
<?php
    // Retrieve the parameters from the URL
    $chcName = isset($_GET['chcName']) ? htmlspecialchars($_GET['chcName']) : 'Unknown CHC';
    $drName = isset($_GET['drName']) ? htmlspecialchars($_GET['drName']) : 'Unknown Doctor';
    $date = isset($_GET['date']) ? htmlspecialchars($_GET['date']) : 'Unknown Date';
    $timing = isset($_GET['timing']) ? htmlspecialchars($_GET['timing']) : 'Unknown Time';

    // Convert the date into the required format (11-Oct-2024)
    $formattedDate = date('j-M-Y', strtotime($date));

    // Display the information
    echo "<h1>$chcName</h1>";
    echo "<h3>$drName</h3>";
    echo "<p>($formattedDate) || [$timing]</p>";

    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "projecthospitalpatient";
    $connect = mysqli_connect($servername, $username, $password, $database);

    if (!$connect) {
        die("Connection Failed!" . mysqli_connect_error());
    }

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $patientName = htmlspecialchars($_POST['name']);
        $age = htmlspecialchars($_POST['age']);
        $gender = htmlspecialchars($_POST['gender']);
        $address = htmlspecialchars($_POST['address']);
        $email = htmlspecialchars($_POST['email']);
        $phone = htmlspecialchars($_POST['phone']);
        $aadhaar = htmlspecialchars($_POST['aadhaar']);
        $reason = htmlspecialchars($_POST['reason']);
        
        // Format the current date and time for Fillup Date/Timing
        $currentDateTime = date("Y-m-d H:i:s");

        // Get the table name from chcName (lowercase)
        $tableName = strtolower($chcName);

        // Insert data into the respective table
        $insertQuery = "INSERT INTO `$tableName` (`Patient Name`, `Age`,`Gender`, `Dr.Name`, `Reason`, `Mobile No.`, `E-mail`, `Addhar`, `Slot`, `Date`) 
                        VALUES ('$patientName', '$age', '$gender', '$drName', '$reason', '$phone', '$email', '$aadhaar', '$timing', '$date')";

        if (mysqli_query($connect, $insertQuery)) {
            // If the insert is successful, show an alert
            echo "<script>alert('Appointment successfully booked!');</script>";
        } else {
            echo "Error: " . mysqli_error($connect);
        }
    }
?>

    <form class="form-container" method="POST" onsubmit="return validateForm()">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" placeholder="Enter your name as per Aadhaar" pattern="[A-Za-z\s]+" title="Name should only contain letters and spaces." required>
        
        <label for="age">Age:</label>
        <input type="number" id="age" name="age" placeholder="Age" min="1" max="100" title="" required>

        <label for="gender"></label>
        M<input type="radio" name="gender" value="Male">
        F<input type="radio" name="gender" value="Female">

        <label for="address">Address:</label>
        <input type="text" id="address" name="address" placeholder="Enter your address as per Aadhaar" required>

        <label for="email">E-mail:</label>
        <input type="email" id="email" name="email" value="@gmail.com" placeholder="Enter your email" required>

        <label for="phone">Phone:</label>
        <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" pattern="\d{10}" title="Phone number must be exactly 10 digits" required>

        <label for="aadhaar">Aadhaar No.:</label>
        <input type="tel" id="aadhaar" name="aadhaar" placeholder="Enter Aadhaar No." pattern="\d{12}"  required>

        <label for="reason">Reason:</label>
        <input type="text" id="reason" name="reason" placeholder="Write about disease..." required>

        <button type="submit">Book Slot</button>
    </form>

    <script>
        function validateForm() {
            const aadhaarInput = document.getElementById("aadhaar").value;
            const phoneInput = document.getElementById("phone").value;

            if (aadhaarInput.length !== 12) {
                alert("Aadhaar number must be exactly 12 digits.");
                return false;
            }

            if (phoneInput.length !== 10) {
                alert("Phone number must be exactly 10 digits.");
                return false;
            }

            return true;
        }
    </script>
</body>
</html>