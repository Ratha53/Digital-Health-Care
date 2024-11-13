<?php
// Start PHP session
session_start();

// Database connection for projecthospital
$servername = "localhost";
$username = "root";
$password = "";
$database1 = "projecthospital";
$database2 = "projecthospitaldr.";

// Connection to the first database
$connect1 = mysqli_connect($servername, $username, $password, $database1);
if (!$connect1) {
    die("Connection Failed: " . mysqli_connect_error());
}

// Connection to the second database
$connect2 = mysqli_connect($servername, $username, $password, $database2);
if (!$connect2) {
    die("Connection Failed: " . mysqli_connect_error());
}

// Fetch unique CHC names from the centralizeddatatable
$sql = "SELECT DISTINCT CHCName FROM centralizeddatatable";
$result = mysqli_query($connect1, $sql);

if (!$result) {
    die("Query Failed: " . mysqli_error($connect1));
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $chcName = strtolower(trim($_POST['chcName'])); // Convert to lowercase
    $doctorName = trim($_POST['doctor_name']);
    $todayStatus = isset($_POST['today_status']) ? intval($_POST['today_status']) : 0; // Default to 0 if not set

    // Prepare the SQL update statement with current timestamp
    $updateSql = "UPDATE `$chcName` SET `Today Status` = ?, `Today Status` = NOW() WHERE `Dr.Name` = ?";
    
    // Prepare the statement
    $stmt = mysqli_prepare($connect2, $updateSql);
    if (!$stmt) {
        die("Statement Preparation Failed: " . mysqli_error($connect2));
    }

    // Bind parameters
    mysqli_stmt_bind_param($stmt, "is", $todayStatus, $doctorName);

    // Execute the statement
    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Today Status updated successfully.');</script>";
    } else {
        echo "<script>alert('Error updating status: " . mysqli_error($connect2) . "');</script>";
    }

    // Close statement and connection
    mysqli_stmt_close($stmt);
}

// Check if the request is to fetch doctors
if (isset($_GET['chcName'])) {
    $chcName = strtolower($_GET['chcName']); // Convert to lowercase
    $doctorTable = $chcName; // Use the lowercase CHC name as the table name

    // Query to get doctors from the specific table in the second database
    $doctorSql = "SELECT `Dr.Name` AS name, Specialization FROM `$doctorTable`";
    $doctorResult = mysqli_query($connect2, $doctorSql);

    if (!$doctorResult) {
        echo json_encode([]); // Return empty array on error
        exit;
    }

    $doctors = [];
    while ($row = mysqli_fetch_assoc($doctorResult)) {
        $doctors[] = $row; // Collect doctor data
    }

    echo json_encode($doctors); // Return as JSON
    exit; // Terminate the script after sending the response
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Attendance</title>
    <link rel="stylesheet" type="text/css" href="fingerPrint.css"> 
    <link rel="stylesheet" type="text/css" href="styling.css"> 
</head>
<body>
    <div class="container">
        <h1>Dr. Attendance</h1>

        <form id="attendanceForm" action="/PROJECTS/DOCTOR/4LocalAdmin1.php" method="POST">
            <label for="chcName">CHC-Name:</label>
            <select name="chcName" id="chcName" required onchange="fetchDoctors(this.value)">
                <option value="">Select CHC</option>
                <?php
                    // Populate the dropdown with unique CHC names from the database
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<option value='" . htmlspecialchars($row['CHCName']) . "'>" . htmlspecialchars($row['CHCName']) . "</option>";
                    }
                ?>
            </select>

            <label for="doctor-name">Dr. Name:</label>
            <select name="doctor_name" id="doctor-name" required>
                <option value="">Select Doctor</option>
            </select>

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

    <script>
        function simulateFingerprintScan() {
            document.getElementById('status').textContent = 'Fingerprint scanned successfully!';
        }

        let allDoctors = []; // Array to hold all doctor data

        function fetchDoctors(chcName) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', '?chcName=' + encodeURIComponent(chcName.toLowerCase()), true); // Send lowercase CHC name
            xhr.onload = function () {
                if (this.status === 200) {
                    allDoctors = JSON.parse(this.responseText); // Store the doctor data
                    populateDoctorSelect(allDoctors); // Populate the dropdown
                } else {
                    console.error("Failed to fetch doctors: " + this.statusText);
                }
            };
            xhr.onerror = function() {
                console.error("Request error.");
            };
            xhr.send();
        }

        function populateDoctorSelect(doctors) {
            const doctorSelect = document.getElementById('doctor-name');
            doctorSelect.innerHTML = '<option value="">Select Doctor</option>'; // Reset options

            // Check if doctors array is not empty
            if (doctors.length > 0) {
                doctors.forEach(doctor => {
                    const specialization = doctor.Specialization || "Not Specified"; // Default message if specialization is missing
                    doctorSelect.innerHTML += `<option value="${doctor.name}">${doctor.name} (${specialization})</option>`;
                });
            } else {
                doctorSelect.innerHTML += `<option value="">No doctors available</option>`;
            }
        }
    </script>
</body>
</html>