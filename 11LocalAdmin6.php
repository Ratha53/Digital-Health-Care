<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Delete</title>
    <link rel="stylesheet" type="text/css" href="fingerPrint.css">  
    <link rel="stylesheet" type="text/css" href="styling.css">
</head>
<body>
<?php
// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $chcName = $_POST['chcName'];
    $drName = $_POST['drName'];

    // Check if the CHC name is selected and if Dr. Name is provided
    if (empty($chcName)) {
        echo "<script>alert('Please select a CHC');</script>";
    } elseif (empty($drName)) { // Check if Dr.Name is empty
        echo "<script>alert('Please enter Dr. Name');</script>";
    } else {
        // Database connection
        $servername = "localhost";
        $username = "root";
        $password = "";
        $database = "projecthospitaldr.";

        // Connect to the database
        $connect = mysqli_connect($servername, $username, $password, $database);

        // Check connection
        if (!$connect) {
            die("Connection Failed: " . mysqli_connect_error());
        }

        // Prepare SQL query to delete data
        $sql = "DELETE FROM `$chcName` WHERE `Dr.Name` = '$drName'";

        // Execute the query
        if (!mysqli_query($connect, $sql)) {
            echo "Error: " . $sql . "<br>" . mysqli_error($connect);
        } else {
            echo "<script>alert('Doctor account deleted successfully');</script>";
        }

        // Close the connection
        mysqli_close($connect);
    }
}
?>

    <div class="container">
        <h1>Delete Dr. Account</h1>
        <?php
         // Retrieve the CHC Name from the URL
         $chcName = isset($_GET['chcName']) ? $_GET['chcName'] : '';
         ?>
        <form action="/PROJECTS/DOCTOR/11LocalAdmin6.php" method="POST">
    <label for="chcName">CHC-Name:</label>
    <input type="text" name="chcName" id="chcName" value="<?php echo htmlspecialchars($chcName); ?>" readonly required> <!-- Auto-filled CHC Name -->

    <label for="drName">Dr. Name:</label>
    <input type="text" id="drName" name="drName" placeholder="Dr. Name" required><br>

    <label>Thumb Impression:</label>
    <div class="scan" onclick="simulateFingerprintScan()">
        <div class="fingerprint-box">
            <div class="fingerprint"></div>
            <div class="scan-bar"></div>
        </div>
        <h3>Scanning...</h3>
    </div>
    <p id="status"></p>
   <button type="submit">Submit</button>
</form>
</div>
</body>
</html>