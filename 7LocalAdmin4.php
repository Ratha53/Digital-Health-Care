<!DOCTYPE html>
<html>
<head>
    <title>Patient-Details</title>
    <link rel="stylesheet" type="text/css" href="styling.css">
</head>
<body>

<?php
// Database connection for main and backup databases
$servername = "localhost";
$username = "root";
$password = "";
$mainDatabase = "projecthospitalpatient";
$backupDatabase = "projecthospitalpatientchecked";

$connect = mysqli_connect($servername, $username, $password, $mainDatabase);
$backupConnect = mysqli_connect($servername, $username, $password, $backupDatabase);

if (!$connect || !$backupConnect) {
    die("Connection Failed: " . mysqli_connect_error());
}

// Handle POST request to insert into backup and delete from main database
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addhar']) && isset($_POST['chcName'])) {
    $aadhaar = htmlspecialchars($_POST['addhar']);
    $chcName = htmlspecialchars($_POST['chcName']);

    // Normalize CHC name for table name
    $normalizedChcName = strtolower($chcName);

    // Step 1: Fetch the patient row from the main database
    $selectQuery = "SELECT * FROM `$normalizedChcName` WHERE `Addhar` = '$aadhaar'";
    $result = mysqli_query($connect, $selectQuery);

    if ($result && mysqli_num_rows($result) > 0) {
        $rowData = mysqli_fetch_assoc($result);

        // Step 2: Insert data into the backup database
        $insertQuery = "INSERT INTO `$normalizedChcName` (`Patient Name`, `Age`, `Gender`, `Dr.Name`, `Reason`, `Mobile No.`, `E-mail`, `Addhar`, `Slot`, `Date`, `Fillup  Date/Timming`)
                VALUES ('" . htmlspecialchars($rowData['Patient Name']) . "', '" . htmlspecialchars($rowData['Age']) . "', '" . htmlspecialchars($rowData['Gender']) . "', 
                        '" . htmlspecialchars($rowData['Dr.Name']) . "', '" . htmlspecialchars($rowData['Reason']) . "', '" . htmlspecialchars($rowData['Mobile No.']) . "', 
                        '" . htmlspecialchars($rowData['E-mail']) . "', '" . htmlspecialchars($rowData['Addhar']) . "', '" . htmlspecialchars($rowData['Slot']) . "', 
                        '" . htmlspecialchars($rowData['Date']) . "', '" . (isset($rowData['Fillup  Date/Timming']) ? htmlspecialchars($rowData['Fillup  Date/Timming']) : '') . "')";

        if (mysqli_query($backupConnect, $insertQuery)) {
            // Step 3: Delete the patient from the main database after successful insertion
            $deleteQuery = "DELETE FROM `$normalizedChcName` WHERE `Addhar` = '$aadhaar'";
            if (mysqli_query($connect, $deleteQuery)) {
                echo 'success';
            } else {
                echo 'error: ' . mysqli_error($connect);
            }
        } else {
            echo 'error: ' . mysqli_error($backupConnect);
        }
    } else {
        echo 'error: Patient data not found in the main database.';
    }
    exit();
}

// Patient list display logic remains the same
$chcName = isset($_GET['chcName']) ? htmlspecialchars($_GET['chcName']) : 'Unknown CHC';
$doctorTiming = isset($_GET['doctorTiming']) ? htmlspecialchars($_GET['doctorTiming']) : 'Unknown Timing';
$date = isset($_GET['date']) ? htmlspecialchars($_GET['date']) : 'Unknown Date';
$specialization = isset($_GET['specialization']) ? htmlspecialchars($_GET['specialization']) : 'Unknown Specialization';

$timingParts = explode('_', $doctorTiming);
$timingSlot = $timingParts[0];
$drName = isset($timingParts[1]) ? htmlspecialchars($timingParts[1]) : 'Unknown Doctor';
$timeRange = ($timingSlot === 'morning') ? '9AM-1PM' : '2PM-4PM';
$normalizedChcName = strtolower($chcName);

echo "<h1>$chcName (Patient Details)</h1>";
echo "<h3>$drName ($specialization)</h3>";
echo "<p> ($date) || [$timeRange]</p>";

$dateTime = DateTime::createFromFormat('d-M-Y', $date);
if ($dateTime) {
    $date1 = $dateTime->format('Y-m-d');
} else {
    die("Invalid date format.");
}

$query = "SELECT `Patient Name`, `Reason`, `Addhar` 
          FROM `$normalizedChcName` 
          WHERE `Date` = '$date1' AND `Dr.Name` = '$drName' AND `Slot` = '$timeRange'";

$result = mysqli_query($connect, $query);
if (!$result) {
    die("Query Failed: " . mysqli_error($connect));
}

$patientData = [];
while ($row = mysqli_fetch_assoc($result)) {
    $patientData[] = $row;
}
mysqli_free_result($result);
?>

<table>
    <thead>
        <tr>
            <th>S/N</th>
            <th>Patient Name</th>
            <th>Reason</th>
            <th>Aadhaar</th>
            <th>Checked</th>
        </tr>
    </thead>
    <tbody id="table-body">
        <?php
            if (!empty($patientData)) {
                foreach ($patientData as $index => $row) {
                    $aadhaar = htmlspecialchars($row['Addhar']);
                    echo "<tr id='row-$aadhaar'>
                        <td class='serial-number'>" . ($index + 1) . "</td>
                        <td>" . htmlspecialchars($row['Patient Name']) . "</td>
                        <td>" . htmlspecialchars($row['Reason']) . "</td>
                        <td>" . $aadhaar . "</td>
                        <td><input type='checkbox' class='delete-checkbox' data-addhar='$aadhaar'></td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No patients found!</td></tr>";
            }
        ?>
    </tbody>
</table>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const checkboxes = document.querySelectorAll('.delete-checkbox');

        checkboxes.forEach(function(checkbox) {
            checkbox.addEventListener('click', function() {
                const aadhaar = this.getAttribute('data-addhar');
                const row = document.getElementById('row-' + aadhaar);

                if (confirm("Are you sure you want to archive and delete this patient?")) {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', '7LocalAdmin4.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            if (xhr.responseText === 'success') {
                                row.remove();
                                renumberRows();
                            }
                        }
                    };
                    xhr.send('addhar=' + aadhaar + '&chcName=' + '<?php echo $normalizedChcName; ?>');
                } else {
                    checkbox.checked = false;
                }
            });
        });
    });

    function renumberRows() {
        const rows = document.querySelectorAll('#table-body .serial-number');
        rows.forEach((row, index) => {
            row.textContent = index + 1;
        });
    }
</script>
</body>
</html>