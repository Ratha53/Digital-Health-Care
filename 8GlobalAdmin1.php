<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Global Admin</title>
    <link rel="stylesheet" type="text/css" href="styling.css">  
    <link rel="stylesheet" type="text/css" href="fingerPrint.css">
</head>
<body>
    <div>
        <form class="form-container" action="/PROJECTS/DOCTOR/8GlobalAdmin1.php" method="post">
            <label for="district">District:</label>
            <select name="district" id="district">
                <option value="">Select District</option>
                <option value="Bhadrak">Bhadrak</option>
                <option value="Baleshwar">Baleshwar</option>
                <option value="Cuttack">Cuttack</option>
                <option value="Sambalpur">Sambalpur</option>
                <option value="Brahmapur">Brahmapur</option>
            </select>

            <label for="chcName">CHC-Name:</label>
            <select name="chcName" id="chcName">
                <option value="">Select CHC</option>
            </select>

            <label for="LocalAdmin">CHC-Admin Name:</label>
            <input type="text" name="chcAdmin" id="LocalAdmin" required>

            <label>Thumb Impression:</label>
            <div class="scan" onclick="simulateFingerprintScan()">
                <div class="fingerprint-box">
                    <div class="fingerprint"></div>
                    <div class="scan-bar"></div>
                </div>
            </div>

            <button type="submit">Submit</button>
        </form>
        <a href="http://localhost/PROJECTS/DOCTOR/9GlobalAdmin2.php">
            <input type="button" value="Local-Admin-List">
        </a>
    </div>

    <?php
// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $district = $_POST['district'];
    $chcName = $_POST['chcName'];
    $chcAdmin = $_POST['chcAdmin'];

    // Validation to check if district and CHC are selected
    if (empty($district)) {
        echo "<script>alert('Please select a District');</script>";
    } elseif (empty($chcName)) {
        echo "<script>alert('Please select a CHC');</script>";
    } else {
        // Database connection
        $servername = "localhost";
        $username = "root";
        $password = "";
        $database = "projecthospital";

        // Connect to the database
        $connect = mysqli_connect($servername, $username, $password, $database);

        // Check connection
        if (!$connect) 
            die("Connection Failed: " . mysqli_connect_error());

        // SQL query to insert the form data into the database
        $sql = "INSERT INTO centralizeddatatable (District, CHCName, CHCAdminName, Date) 
                VALUES ('$district', '$chcName', '$chcAdmin', current_timestamp())";

        // Execute the query
        $result = mysqli_query($connect, $sql);

        // Check if the insertion was successful
        if (!$result) 
            echo "Error: " . $sql . "<br>" . mysqli_error($connect);
        else
            echo "<script>alert('Data inserted successfully');</script>";

        // Close the connection
        mysqli_close($connect);
    }
}
?>


    <script>
        // Define the CHC options for each district
        const chcOptions = {
            Bhadrak: ["Basudevpur CHC", "Bhandaripokhari CHC", "Chandabali CHC", "Dhamanagar CHC", "Aradi CHC", "Bant CHC"],
            Baleshwar: ["Khaira CHC", "Baaliapala CHC", "Jaleshwar CHC", "Nilagiri CHC"],
            Cuttack: ["Niali CHC", "Taangi CHC", "Salepur CHC", "Baanki CHC"],
            Sambalpur: ["Burla CHC", "Rengali CHC", "Kuchinda CHC"],
            Brahmapur: ["Digapahandi CHC", "Chhatrapur CHC", "Hinjili CHC"]
        };

        // Event listener for the District dropdown
        document.getElementById('district').addEventListener('change', function () {
            const district = this.value;
            const chcNameSelect = document.getElementById('chcName');

            // Clear current options
            chcNameSelect.innerHTML = '<option value="">Select CHC</option>';

            // If a valid district is selected, populate the corresponding CHC options
            if (district && chcOptions[district]) {
                chcOptions[district].forEach(function(chc) {
                    const option = document.createElement('option');
                    option.value = chc;
                    option.textContent = chc;
                    chcNameSelect.appendChild(option);
                });
            }
        });
    </script>
</body>
</html>