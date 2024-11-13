<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Dr. Account</title>
    <link rel="stylesheet" type="text/css" href="styling.css">
</head>
<body>

<?php
// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $chcName = $_POST['chcName'];
    $drName = $_POST['drName'];
    $specialization = $_POST['specialization'];
    $adminName = $_POST['createdBy']; // Capturing the Created By field from the form

    // Availability array
    $availability = [
        'MON(9am-1pm)' => isset($_POST['MON(9am-1pm)']) ? 1 : 0,
        'MON(2pm-4pm)' => isset($_POST['MON(2pm-4pm)']) ? 1 : 0,
        'TUS(9am-1pm)' => isset($_POST['TUS(9am-1pm)']) ? 1 : 0,
        'TUS(2pm-4pm)' => isset($_POST['TUS(2pm-4pm)']) ? 1 : 0,
        'WED(9am-1pm)' => isset($_POST['WED(9am-1pm)']) ? 1 : 0,
        'WED(2pm-4pm)' => isset($_POST['WED(2pm-4pm)']) ? 1 : 0,
        'THU(9am-1pm)' => isset($_POST['THU(9am-1pm)']) ? 1 : 0,
        'THU(2pm-4pm)' => isset($_POST['THU(2pm-4pm)']) ? 1 : 0,
        'FRI(9am-1pm)' => isset($_POST['FRI(9am-1pm)']) ? 1 : 0,
        'FRI(2pm-4pm)' => isset($_POST['FRI(2pm-4pm)']) ? 1 : 0,
        'SAT(9am-1pm)' => isset($_POST['SAT(9am-1pm)']) ? 1 : 0,
        'SAT(2pm-4pm)' => isset($_POST['SAT(2pm-4pm)']) ? 1 : 0,
        'SUN(9am-1pm)' => isset($_POST['SUN(9am-1pm)']) ? 1 : 0,
        'SUN(2pm-4pm)' => isset($_POST['SUN(2pm-4pm)']) ? 1 : 0,
    ];

    // Check if the CHC name is selected
    if (empty($chcName)) {
        echo "<script>alert('Please select a CHC');</script>";
    } elseif (!array_filter($availability)) { // Check if at least one checkbox is checked
        echo "<script>alert('Please enter at least 1 availability');</script>";
    } else {
        // Database connection
        $servername = "localhost";
        $username = "root";
        $password = "";
        $database = "projecthospitaldr."; // Remove trailing dot

        // Connect to the database
        $connect = mysqli_connect($servername, $username, $password, $database);

        // Check connection
        if (!$connect) {
            die("Connection Failed: " . mysqli_connect_error());
        }

        $sql = "INSERT INTO `$chcName` (`Dr.Name`, `Specialization`, `Created By`, `MON(9am-1pm)`, `MON(2pm-4pm)`, `TUE(9am-1pm)`, `TUE(2pm-4pm)`, `WED(9am-1pm)`, `WED(2pm-4pm)`, `THU(9am-1pm)`, `THU(2pm-4pm)`, `FRI(9am-1pm)`, `FRI(2pm-4pm)`, `SAT(9am-1pm)`, `SAT(2pm-4pm)`, `SUN(9am-1pm)`, `SUN(2pm-4pm)`) 
        VALUES ('$drName', '$specialization', '$adminName', '{$availability['MON(9am-1pm)']}', '{$availability['MON(2pm-4pm)']}', '{$availability['TUS(9am-1pm)']}', '{$availability['TUS(2pm-4pm)']}', '{$availability['WED(9am-1pm)']}', '{$availability['WED(2pm-4pm)']}', '{$availability['THU(9am-1pm)']}', '{$availability['THU(2pm-4pm)']}', '{$availability['FRI(9am-1pm)']}', '{$availability['FRI(2pm-4pm)']}', '{$availability['SAT(9am-1pm)']}', '{$availability['SAT(2pm-4pm)']}', '{$availability['SUN(9am-1pm)']}', '{$availability['SUN(2pm-4pm)']}')";

        // Execute the query
        if (!mysqli_query($connect, $sql)) {
            echo "Error: " . $sql . "<br>" . mysqli_error($connect);
        } else {
            echo "<script>alert('Data inserted successfully');</script>";
        }

        // Close the connection
        mysqli_close($connect);
    }
}
?>

<div>
<?php
// Retrieve the CHC Name and CHCAdminName from the URL
$chcName = isset($_GET['chcName']) ? $_GET['chcName'] : 'Unknown CHC';
$adminName = isset($_GET['adminName']) ? $_GET['adminName'] : 'Unknown Admin';  // Get the admin name
?>
    <form class="form-container" action="/PROJECTS/DOCTOR/10LocalAdmin5.php" method="post">
        <label for="chcName">CHC-Name:</label>
        <input type="text" name="chcName" id="chcName" value="<?php echo htmlspecialchars($chcName); ?>" readonly required> <!-- Auto-filled CHC Name -->

        <label for="drName">Dr. Name:</label>
        <input type="text" name="drName" id="drName" required>

        <label for="specialization">Specialization:</label>
        <input type="text" name="specialization" id="specialization" required>

        <h3 style="text-align:center">Availability</h3>
        <table>
            <thead>
                <tr>
                    <th>S/N</th>
                    <th>Days</th>
                    <th>9AM-1PM</th>
                    <th>2PM-4PM</th>
                </tr>
            </thead>
            <tbody>
                <!-- Availability checkboxes -->
                <tr>
                    <td>1</td>
                    <td>Sunday</td>
                    <td><input type="checkbox" name="SUN(9am-1pm)" value="1"></td>
                    <td><input type="checkbox" name="SUN(2pm-4pm)" value="1"></td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Monday</td>
                    <td><input type="checkbox" name="MON(9am-1pm)" value="1"></td>
                    <td><input type="checkbox" name="MON(2pm-4pm)" value="1"></td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>Tuesday</td>
                    <td><input type="checkbox" name="TUE(9am-1pm)" value="1"></td>
                    <td><input type="checkbox" name="TUE(2pm-4pm)" value="1"></td>
                </tr>
                <tr>
                    <td>4</td>
                    <td>Wednesday</td>
                    <td><input type="checkbox" name="WED(9am-1pm)" value="1"></td>
                    <td><input type="checkbox" name="WED(2pm-4pm)" value="1"></td>
                </tr>
                <tr>
                    <td>5</td>
                    <td>Thursday</td>
                    <td><input type="checkbox" name="THU(9am-1pm)" value="1"></td>
                    <td><input type="checkbox" name="THU(2pm-4pm)" value="1"></td>
                </tr>
                <tr>
                    <td>6</td>
                    <td>Friday</td>
                    <td> <input type="checkbox" name="FRI(9am-1pm)" value="1"></td>
                    <td> <input type="checkbox" name="FRI(2pm-4pm)" value="1">
                    </td>
                </tr>
                <tr>
                    <td>7</td>
                    <td>Saturday</td>
                    <td><input type="checkbox" name="SAT(9am-1pm)" value="1"></td>
                    <td><input type="checkbox" name="SAT(2pm-4pm)" value="1">
                    </td>
                </tr>
                    <!-- Repeat for other days -->
                </tbody>
            </table>
            <label for="cratedBy">Created By:</label>
            <input type="text" name="createdBy" id="createdBy" value="<?php echo htmlspecialchars($adminName); ?>" readonly required>
            <button type="submit">Submit</button>
        </form>
    </div>
   
</body>
</html>