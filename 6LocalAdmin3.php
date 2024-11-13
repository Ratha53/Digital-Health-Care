<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" type="text/css" href="styling.css">
</head>
<body>
<?php
// Retrieve the CHC Name and Admin Name from the URL
$chcName = isset($_GET['chcName']) ? $_GET['chcName'] : 'Unknown CHC';
$adminName = isset($_GET['adminName']) ? $_GET['adminName'] : 'Unknown Admin';

// Get today's date in day-Mon-Year format
$dateToday = date("d-M-Y");

// Get today's day (MON, TUE, WED, THU, FRI, SAT, SUN)
$currentDay = strtoupper(date("D"));

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";

// Connect to projecthospital database for centralizeddatatable
$projecthospital = mysqli_connect($servername, $username, $password, "projecthospital");
if (!$projecthospital) {
    die("Connection to projecthospital failed: " . mysqli_connect_error());
}

// Connect to projecthospitaldr. database for CHC-specific data
$projecthospitaldr = mysqli_connect($servername, $username, $password, "projecthospitaldr.");
if (!$projecthospitaldr) {
    die("Connection to projecthospitaldr. failed: " . mysqli_connect_error());
}

// Fetch District from centralizeddatatable in projecthospital based on CHCName and AdminName
$sql = "SELECT District FROM centralizeddatatable WHERE CHCName = '$chcName' AND CHCAdminName = '$adminName' LIMIT 1";
$result = mysqli_query($projecthospital, $sql);
$row = mysqli_fetch_assoc($result);
$districtName = $row ? $row['District'] : 'Unknown District';

// Close projecthospital connection as it’s no longer needed
mysqli_close($projecthospital);

// Fetch doctors from the respective CHC table in projecthospitaldr.
$sql = "SELECT * FROM `" . strtolower($chcName) . "`";
$result = mysqli_query($projecthospitaldr, $sql);
$rows = [];

// Check the doctor's availability for today
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Get the Today Status timestamp
        $todayStatusTimestamp = $row['Today Status']; // Assuming it's a timestamp

        // Get current date in Y-m-d format
        $currentDate = date("Y-m-d");

        // Check if the doctor is scheduled for today
        $isScheduledToday = (
            $row[$currentDay . '(9am-1pm)'] == 1 || 
            $row[$currentDay . '(2pm-4pm)'] == 1
        );

        // Determine if the timestamp matches today's date
        $isTodayStatusPresent = (
            strtotime($todayStatusTimestamp) !== false && 
            date("Y-m-d", strtotime($todayStatusTimestamp)) == $currentDate
        );

        // Determine if the doctor is present or absent
        $status = ($isTodayStatusPresent && $isScheduledToday) ? 'Present' : 'Absent';

        // Add the row with status to the results
        $row['status'] = $status; // Add status to the row
        $rows[] = $row; // Add row to rows
    }
} else {
    echo "No doctors found for this CHC.";
}

// Close projecthospitaldr connection after any required operations
mysqli_close($projecthospitaldr);
?>
    <h1><?php echo htmlspecialchars($chcName); ?></h1> <!-- Display the CHC Name dynamically -->
    
    <h3><?php echo htmlspecialchars($dateToday); ?></h3> <!-- Display today's date -->
    
    <form id="doctorForm" action="7LocalAdmin4.php" method="GET"> <!-- Start form for data submission -->
        <table>
            <thead>
                <tr>
                    <th>S/N</th>
                    <th>Dr Name</th>
                    <th>Specialization</th>
                    <th>Today Status</th>
                    <th>9 AM - 1 PM</th>
                    <th>2 PM - 4 PM</th>
                </tr>
            </thead>
            <tbody>
            <?php
            if (!empty($rows)) {
                $sn = 1; // Initialize serial number
                foreach ($rows as $row) {
                    echo "<tr>";
                    echo "<td>" . $sn++ . "</td>"; // Increment serial number
                    echo "<td>" . htmlspecialchars($row['Dr.Name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Specialization']) . "</td>";
                    
                    // Display today's status
                    echo "<td>" . htmlspecialchars($row['status']) . "</td>"; // Use the status from the row
                    
                    // For the 9 AM - 1 PM slot
                    if ($row[$currentDay . '(9am-1pm)'] == 1) {
                        echo "<td><input type='radio' name='doctorTiming' value='morning_" . htmlspecialchars($row['Dr.Name']) . "' data-specialization='" . htmlspecialchars($row['Specialization']) . "' required></td>";
                    } else {
                        echo "<td></td>"; // No radio button if not present
                    }

                    // For the 2 PM - 4 PM slot
                    if ($row[$currentDay . '(2pm-4pm)'] == 1) {
                        echo "<td><input type='radio' name='doctorTiming' value='afternoon_" . htmlspecialchars($row['Dr.Name']) . "' data-specialization='" . htmlspecialchars($row['Specialization']) . "' required></td>";
                    } else {
                        echo "<td></td>"; // No radio button if not present
                    }
                    
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No doctors available today.</td></tr>";
            }
            ?>
            </tbody>
        </table>

        <input type="hidden" name="chcName" value="<?php echo htmlspecialchars($chcName); ?>">
        <input type="hidden" name="date" value="<?php echo htmlspecialchars($dateToday); ?>">
        <input type="hidden" name="adminName" value="<?php echo htmlspecialchars($adminName); ?>">
        <input type="hidden" name="specialization" id="specialization" value=""> <!-- Hidden field for specialization -->
    </form>
    
    <div class="button-container">
        <a href="10LocalAdmin5.php?chcName=<?php echo urlencode($chcName); ?>&adminName=<?php echo urlencode($adminName); ?>">
            <input type="button" class="create" value="Create Dr. Account">
        </a>

        <!-- Statistics button updated with dynamic chcName and districtName -->
        <a href="12LocalAdmin7.php?CHCName=<?php echo urlencode($chcName); ?>&District=<?php echo urlencode($districtName); ?>">
            <input type="button" class="" value="Statistics">
        </a>

        <a href="11LocalAdmin6.php?chcName=<?php echo urlencode($chcName); ?>">
            <input type="button" class="delete" value="Delete Dr. Account">
        </a>
    </div>

    <script>
        // JavaScript to handle radio button click
        const radios = document.querySelectorAll('input[name="doctorTiming"]');
        radios.forEach(radio => {
            radio.addEventListener('click', function() {
                const specialization = this.getAttribute('data-specialization');
                document.getElementById('specialization').value = specialization; // Set specialization in hidden input
                document.getElementById('doctorForm').submit(); // Submit the form immediately
            });
        });
    </script>
</body>
</html>