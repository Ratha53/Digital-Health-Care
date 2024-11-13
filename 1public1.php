<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Center</title>
    <link rel="stylesheet" type="text/css" href="styling.css">
</head>
<body>
    <div class="slideshow">
        <?php
            $servername = "localhost";
            $username = "root";
            $password = "";
            $database = "projecthospital";

            // Connect to the projecthospital database
            $connect = mysqli_connect($servername, $username, $password, $database);

            if (!$connect) {
                die("Connection Failed!" . mysqli_connect_error());
            }

            // Fetch unique CHC names from the centralized table `centralizeddatatable`
            $chcQuery = "SELECT DISTINCT CHCName FROM centralizeddatatable"; // Use DISTINCT to get unique CHC names
            $chcResult = mysqli_query($connect, $chcQuery);

            if (!$chcResult) {
                die("Query Failed!" . mysqli_error($connect));
            }
        ?>
        <div class="container">
            <h1><i>Welcome to CHC Services...</i></h1>
            <div class="search-box">
                <form class="form-container" onsubmit="return validateForm()"  method="GET" action="2public2.php">
                    <label for="date">Date:</label>
                    <input type="date" id="date" name="date" required><br>

                    <label for="chcName">CHC Name:</label>
                    <select name="chcName" id="chcName" required>
                        <option value="">Select CHC</option>
                        <?php
                            // Populate the dropdown with unique CHC names
                            while ($row = mysqli_fetch_assoc($chcResult)) {
                                echo "<option value='" . $row['CHCName'] . "'>" . $row['CHCName'] . "</option>"; // Output unique CHC names
                            }
                        ?>
                    </select>

                    <div class="radio-group">
                        <label>Timing:</label>
                        <input type="radio" name="timing" value="9AM-1PM">9AM-1PM
                        <input type="radio" name="timing" value="2PM-4PM">2PM-4PM
                    </div>

                    <!-- Centered submit button -->
                    <button type="submit">Search</button>

                    <!-- Error message container -->
                    <div id="error-message" style="color: red; display: none;"></div>
                </form>
            </div>

            <div class="button-container">
                <div>
                    <a href="http://localhost/PROJECTS/DOCTOR/4LocalAdmin1.php">
                        <input type="button" value="Doctor's Login">
                    </a>
                    <div class="auth-warning">(<sup>*</sup>Authorized Only)</div>
                </div>
                <div>
                    <a href="http://localhost/PROJECTS/DOCTOR/5LocalAdmin2.php">
                        <input type="button" value="Admin Login">
                    </a>
                    <div class="auth-warning">(<sup>*</sup>Authorized Only)</div>
                </div>
            </div>
        </div>

        <script>
        // Form validation to prevent blank submission and show error message
        function validateForm() {
            const chcName = document.getElementById('chcName').value;
            const date = document.getElementById('date').value;
            const timing = document.querySelector('input[name="timing"]:checked');
            const errorContainer = document.getElementById('error-message');

            let errorMessage = '';

            if (!date) {
                errorMessage += 'Please select a date.<br>';
            }
            if (!chcName) {
                errorMessage += 'Please select a CHC.<br>';
            }
            if (!timing) {
                errorMessage += 'Please select a timing.<br>';
            }

            if (errorMessage) {
                errorContainer.innerHTML = errorMessage;
                errorContainer.style.display = 'block';
                return false;
            }

            errorContainer.style.display = 'none';
            return true;
        }

        // Set date input to limit calendar selection within 6 days
        function setDateLimit() {
            const dateInput = document.getElementById('date');
            const today = new Date();
            const maxDate = new Date(today);

            // Add 6 days to the current date
            maxDate.setDate(today.getDate() + 6);

            // Format dates to YYYY-MM-DD
            const todayString = today.toISOString().split('T')[0];
            const maxDateString = maxDate.toISOString().split('T')[0];

            // Set the min and max attributes of the date input
            dateInput.min = todayString;
            dateInput.max = maxDateString;
        }

        // Initialize date limit on page load
        document.addEventListener('DOMContentLoaded', setDateLimit);

        function createBackgroundCycler(images, element, interval) {
            let currentIndex = 0; // Keeps track of the current image index

            // Closure function to change the background image
            return function () {
                element.style.backgroundImage = `url(${images[currentIndex]})`;
                currentIndex = (currentIndex + 1) % images.length; // Cycles through the array
            };
        }

        // Select the element to apply the background slideshow
        const slideshow = document.querySelector('.slideshow');

        // Array of background images
        const images = [
            '/PROJECTS/DOCTOR/images/5.jpg',
            '/PROJECTS/DOCTOR/images/2.jpg',
            '/PROJECTS/DOCTOR/images/3.jpg',
            '/PROJECTS/DOCTOR/images/4.jpg',
            '/PROJECTS/DOCTOR/images/1.jpg',
            '/PROJECTS/DOCTOR/images/6.jpg'
        ];

        // Create the background cycler closure
        const cycleBackground = createBackgroundCycler(images, slideshow, 5000);

        // Change the background every 5 seconds using setInterval
        setInterval(cycleBackground, 2500);

        // Initial background setup
        cycleBackground();

        </script>
    </div>
</body>
</html>