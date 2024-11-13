<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Local-Admin Login</title>
    <link rel="stylesheet" type="text/css" href="fingerPrint.css">
    <link rel="stylesheet" type="text/css" href="styling.css">
</head>
<body>

<div class="container">
    <h1>Admin Attendance</h1>
    <form action="/PROJECTS/DOCTOR/5LocalAdmin2.php" method="POST">
        <label for="chcName">CHC-Name:</label>
        <select name="chcName" id="chcName" required>
            <option value="">Select CHC</option>
            <?php
            // Database connection details
            $servername = "localhost";
            $username = "root";
            $password = "";
            $database = "projecthospital";

            // Connect to the database
            $connect = mysqli_connect($servername, $username, $password, $database);

            // Check connection
            if (!$connect) {
                die("Connection Failed: " . mysqli_connect_error());
            }

            // Query to get distinct CHC Names from the table
            $chcQuery = "SELECT DISTINCT CHCName FROM centralizeddatatable";
            $chcResult = mysqli_query($connect, $chcQuery);

            // Populate the CHCName dropdown
            if (mysqli_num_rows($chcResult) > 0) {
                while ($row = mysqli_fetch_assoc($chcResult)) {
                    echo '<option value="' . htmlspecialchars($row['CHCName']) . '">' . htmlspecialchars($row['CHCName']) . '</option>';
                }
            }
            ?>
        </select>

        <label for="adminName">CHC-Admin Name:</label>
        <select name="adminName" id="adminName" required>
            <option value="">Select Admin</option>
        </select>

        <label>Thumb-Impression:</label>
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

<?php
// Fetch all CHC and Admin data for the JavaScript part
$adminQuery = "SELECT CHCName, CHCAdminName FROM centralizeddatatable";
$adminResult = mysqli_query($connect, $adminQuery);
$chcAdmins = [];

if (mysqli_num_rows($adminResult) > 0) {
    while ($row = mysqli_fetch_assoc($adminResult)) {
        $chcAdmins[$row['CHCName']][] = $row['CHCAdminName'];
    }
}

// Encode the data to use it in JavaScript
echo '<script>';
echo 'const chcAdmins = ' . json_encode($chcAdmins) . ';';
echo '</script>';

// Close the connection
mysqli_close($connect);
?>

<script>
    // JavaScript to handle dynamic update of admin dropdown
    document.getElementById('chcName').addEventListener('change', function() {
        const selectedCHC = this.value;
        const adminSelect = document.getElementById('adminName');

        // Clear the existing options in the admin dropdown
        adminSelect.innerHTML = '<option value="">Select Admin</option>';

        // Get the admins for the selected CHC
        if (selectedCHC && chcAdmins[selectedCHC]) {
            chcAdmins[selectedCHC].forEach(admin => {
                const option = document.createElement('option');
                option.value = admin;
                option.text = admin;
                adminSelect.appendChild(option);
            });
        }
    });

    function simulateFingerprintScan() {
        const fingerprintID = Math.floor(Math.random() * 10000);
        document.getElementById('status').innerText = `Fingerprint scanned: ${fingerprintID}`;
    }
</script>

<?php
// Handle form submission after POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data and trim spaces
    $chcName = trim($_POST['chcName']);
    $adminName = trim($_POST['adminName']);

    // Reconnect to the database for form submission validation
    $connect = mysqli_connect($servername, $username, $password, $database);

    if (!$connect) {
        die("Connection Failed: " . mysqli_connect_error());
    }

    // Prepare the query to validate the CHC and Admin Name
    $sql = "SELECT * FROM centralizeddatatable 
            WHERE LOWER(REPLACE(CHCName, ' ', '')) = LOWER(REPLACE('$chcName', ' ', '')) 
            AND LOWER(REPLACE(CHCAdminName, ' ', '')) = LOWER(REPLACE('$adminName', ' ', ''))";

    $result = mysqli_query($connect, $sql);

    if (mysqli_num_rows($result) > 0) {
        // If match found, redirect to the admin dashboard, including the selected adminName
        header("Location: /PROJECTS/DOCTOR/6LocalAdmin3.php?chcName=" . urlencode($chcName) . "&adminName=" . urlencode($adminName));
        exit();
    } else {
        // Display an error if no match found
        echo "<script>alert('Invalid CHC Name or Admin Name. Please try again.');</script>";
        echo "<script>window.location.href = 'admin_login.php';</script>";
    }

    // Close the connection
    mysqli_close($connect);
}
?>
</body>
</html>