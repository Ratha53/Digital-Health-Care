<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Dr's</title>
    <link rel="stylesheet" type="text/css" href="styling.css">
</head>
<body>
    <?php
    // Get parameters from the URL
    $chcName = isset($_GET['chcName']) ? htmlspecialchars($_GET['chcName']) : 'N/A';
    $date = isset($_GET['date']) ? htmlspecialchars($_GET['date']) : 'N/A';
    $timing = isset($_GET['timing']) ? htmlspecialchars($_GET['timing']) : 'N/A';

    // Convert chcName to lowercase for the table name
    $normalizedChcName = strtolower($chcName);

    // Convert the date into day format and get first 3 letters in uppercase
    $dayName = strtoupper(date('D', strtotime($date))); // Get the first 3 letters of the day

    // Convert the date into 8-Oct-2024 format for UI display
    $formattedDate = date('j-M-Y', strtotime($date)); // 'j' removes leading zero from day

    // Combine day and timing into the required format
    $slotTiming = $dayName . "($timing)";

    // Display the heading with the original chcName
    echo "<h1><i>Welcome to $chcName</i></h1>";
    echo "<div class='info'>($formattedDate) || [$timing]</div>"; // Echo the formatted date in 8-Oct-2024 format

    // Database connection for doctor information
    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "projecthospitaldr."; // Ensure this database exists
    $connect = mysqli_connect($servername, $username, $password, $database);

    if (!$connect) {
        die("Connection Failed: " . mysqli_connect_error());
    }

    // Database connection for patient information
    $servername1 = "localhost";
    $username1 = "root";
    $password1 = "";
    $database1 = "projecthospitalpatient"; // Ensure this database exists
    $connect1 = new mysqli($servername1, $username1, $password1, $database1);
    
    if ($connect1->connect_error) {
        die("Connection to database1 failed: " . $connect1->connect_error);
    }

    // Query to fetch doctor information based on the table name and slot timing
    $doctorQuery = "SELECT * FROM `$normalizedChcName` WHERE `$slotTiming` = '1'"; // Assuming '1' indicates availability
    $doctorResult = mysqli_query($connect, $doctorQuery);
    ?>

    <table>
        <thead>
            <tr>
                <th style="width: 10%;">S/N</th>
                <th style="width: 30%;">Dr. Name</th>
                <th style="width: 30%;">Specialization</th>
                <th style="width: 15%;">No. Of Waiting</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Check if there are results and populate the table
            if ($doctorResult && mysqli_num_rows($doctorResult) > 0) {
                $serialNumber = 1; // For numbering rows
                while ($row = mysqli_fetch_assoc($doctorResult)) {
                    $doctorName = htmlspecialchars($row['Dr.Name']);
                    $specialization = htmlspecialchars($row['Specialization']);
                    
                    // Query to count the number of waiting patients for this doctor from the specific table in projecthospitalpatient
                    $waitingCountQuery = "SELECT COUNT(*) AS waitingCount FROM `$normalizedChcName`
                                          WHERE `Dr.Name` = '$doctorName' 
                                          AND `Slot` = '$timing'
                                          AND `Date` = '$date'";

                    $waitingCountResult = mysqli_query($connect1, $waitingCountQuery);
                    $waitingCount = 0;

                    if ($waitingCountResult) {
                        $waitingCountRow = mysqli_fetch_assoc($waitingCountResult);
                        $waitingCount = $waitingCountRow['waitingCount'];
                    }

                    echo "<tr>";
                    echo "<td>" . $serialNumber . "</td>";
                    echo "<td><a href='3public3.php?chcName=" . urlencode($chcName) . 
                         "&drName=" . urlencode($doctorName) . 
                         "&date=" . urlencode($date) . 
                         "&timing=" . urlencode($timing) . "'>" . $doctorName . "</a></td>"; // Hyperlink added
                    echo "<td>" . $specialization . "</td>";
                    echo "<td>" . htmlspecialchars($waitingCount) . "</td>"; // Display waiting count from patient database
                    echo "</tr>";
                    $serialNumber++;
                }
            } else {
                // If no doctors available, display empty fields
                echo "<tr><td colspan='5'>No doctors available.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>