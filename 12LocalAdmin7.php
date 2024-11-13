<?php
// Database configuration
$host = 'localhost';
$dbname = 'projecthospitalpatientchecked';
$username = 'root';
$password = '';

// Retrieve CHCName and District from URL parameters
$chcName = isset($_GET['CHCName']) ? htmlspecialchars($_GET['CHCName']) : 'Unknown CHC';
$district = isset($_GET['District']) ? htmlspecialchars($_GET['District']) : 'Unknown District';

// Handle AJAX request to fetch data based on the selected month
if (isset($_GET['month']) && isset($_GET['chcName'])) {
    $month = $_GET['month'];
    $chcTableName = strtolower($_GET['chcName']); // Convert to lowercase

    try {
        // Connect to the database
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // SQL query to fetch data for the specified month with grouped age ranges
        $stmt = $pdo->prepare("
            SELECT 
                CASE 
                    WHEN Age BETWEEN 0 AND 5 THEN 'Child (0-5 yrs)'
                    WHEN Age BETWEEN 6 AND 15 THEN 'Teenage (6-15 yrs)'
                    WHEN Age BETWEEN 16 AND 30 THEN 'Young (16-30 yrs)'
                    WHEN Age BETWEEN 31 AND 50 THEN 'Midage (31-50 yrs)'
                    ELSE 'Oldage (50+ yrs)' 
                END AS ageGroup,
                SUM(CASE WHEN Gender = 'Male' THEN 1 ELSE 0 END) AS maleCount,
                SUM(CASE WHEN Gender = 'Female' THEN 1 ELSE 0 END) AS femaleCount,
                COUNT(*) AS totalCount
            FROM `$chcTableName`
            WHERE DATE_FORMAT(Date, '%Y-%m') = :month
            GROUP BY ageGroup
            ORDER BY ageGroup
        ");
        $stmt->execute(['month' => $month]);

        // Fetch data and initialize total counts
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Check if no data was returned
        if (!$data) {
            echo json_encode(['error' => 'No data found for the selected month.']);
            exit;
        }

        $totalMale = 0;
        $totalFemale = 0;
        $totalPatients = 0;
        $groupedData = [];

        // Aggregate male, female, and total counts for individual groups
        foreach ($data as $row) {
            $totalMale += $row['maleCount'];
            $totalFemale += $row['femaleCount'];
            $totalPatients += $row['totalCount'];
            $groupedData[$row['ageGroup']] = $row;
        }

        // Ensure all age groups are included even if no data exists for them
        $allAgeGroups = [
            'Child (0-5 yrs)', 
            'Teenage (6-15 yrs)', 
            'Young (16-30 yrs)', 
            'Midage (31-50 yrs)', 
            'Oldage (50+ yrs)'
        ];

        foreach ($allAgeGroups as $ageGroup) {
            if (!isset($groupedData[$ageGroup])) {
                $groupedData[$ageGroup] = [
                    'ageGroup' => $ageGroup,
                    'maleCount' => 0,
                    'femaleCount' => 0,
                    'totalCount' => 0
                ];
            }
        }

        // Append total summary to data
        $groupedData['Total'] = [
            'ageGroup' => 'Total',
            'maleCount' => $totalMale,
            'femaleCount' => $totalFemale,
            'totalCount' => $totalPatients
        ];

        // Output data as JSON
        echo json_encode($groupedData);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report</title>
    <link rel="stylesheet" type="text/css" href="styling.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
</head>
<body>
    <div class="container">
        <div>
            <img src="/PROJECTS/DOCTOR/images/7.png">
        <div>

        <h1><?php echo $chcName; ?>, <?php echo $district; ?></h1>
        <h3 id="reportMonthYear"></h3>
        <input type="month" id="monthInput" onchange="fetchReport()">
        
        <table id="reportTable">
            <thead>
                <tr>
                    <th>S/N</th>
                    <th>Age</th>
                    <th>Male (M)</th>
                    <th>Female (F)</th>
                </tr>
            </thead>
            <tbody id="reportTableBody">
                <!-- Table rows will be populated by JavaScript -->
            </tbody>
        </table>

        <button onclick="downloadReportAsPDF()">Download as PDF</button>
        <button onclick="downloadReportAsImage()">Download as Image</button>
    </div>
       
    <script>
        function fetchReport() {
            const monthInput = document.getElementById('monthInput').value;
            if (!monthInput) return;

            const [year, month] = monthInput.split("-");
            const formattedMonthYear = new Date(year, month - 1).toLocaleString('default', { month: 'long', year: 'numeric' });

            // Update header to show the format "November, 2024" dynamically based on the input
            document.querySelector("#reportMonthYear").textContent = formattedMonthYear;

            // Fetch data from PHP script
            const chcName = "<?php echo strtolower($chcName); ?>";
            fetch(`12LocalAdmin7.php?month=${monthInput}&chcName=${chcName}`)
                .then(response => response.json())
                .then(data => {
                    const reportTableBody = document.getElementById('reportTableBody');
                    reportTableBody.innerHTML = ''; // Clear previous rows

                    const ageGroups = [
                        'Child (0-5 yrs)', 
                        'Teenage (6-15 yrs)', 
                        'Young (16-30 yrs)', 
                        'Midage (31-50 yrs)', 
                        'Oldage (50+ yrs)'
                    ];

                    // Total patients count from the data
                    const totalPatients = data['Total'] ? data['Total'].totalCount : 0;

                    // Populate table rows for each age group
                    ageGroups.forEach((ageGroup, index) => {
                        const groupData = data[ageGroup] || { maleCount: 0, femaleCount: 0, totalCount: 0 };
                        const malePercent = totalPatients ? ((groupData.maleCount / totalPatients) * 100).toFixed(2) : '0.00';
                        const femalePercent = totalPatients ? ((groupData.femaleCount / totalPatients) * 100).toFixed(2) : '0.00';
                        const totalPercent = totalPatients ? ((groupData.totalCount / totalPatients) * 100).toFixed(2) : '0.00';

                        const row = `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${ageGroup} = ${groupData.totalCount} (${totalPercent}%)</td>
                                <td>${groupData.maleCount} (${malePercent}%)</td>
                                <td>${groupData.femaleCount} (${femalePercent}%)</td>
                            </tr>
                        `;
                        reportTableBody.insertAdjacentHTML('beforeend', row);
                    });

                    // Add Total row if it exists
                    if (data['Total']) {
                        const totalRow = `
                            <tr>
                                <td>${ageGroups.length + 1}</td>
                                <td>Total-Patients = ${totalPatients}</td>
                                <td>${data['Total'].maleCount} (${((data['Total'].maleCount / totalPatients) * 100).toFixed(2)}%)</td>
                                <td>${data['Total'].femaleCount} (${((data['Total'].femaleCount / totalPatients) * 100).toFixed(2)}%)</td>
                            </tr>
                        `;
                        reportTableBody.insertAdjacentHTML('beforeend', totalRow);
                    }
                })
                .catch(error => console.error('Error fetching data:', error));
        }

        function downloadReportAsPDF() {
            const { jsPDF } = window.jspdf;
            const container = document.querySelector(".container");
            const buttons = document.querySelectorAll("button");
            const monthInput = document.getElementById("monthInput");

            // Hide buttons and month input temporarily
            buttons.forEach(button => button.style.display = 'none');
            monthInput.style.display = 'none';

            // Capture the report container and generate PDF
            html2canvas(container).then(canvas => {
                const pdf = new jsPDF('p', 'mm', 'a4');
                const imgData = canvas.toDataURL('image/png');
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = (canvas.height * pdfWidth) / canvas.width;

                // Add header with specific format
                pdf.setFontSize(16);

                pdf.addImage(imgData, 'PNG', 0, 30, pdfWidth, pdfHeight); // Adjust starting position for the table
                pdf.save('report.pdf');

                // Show buttons and month input again
                buttons.forEach(button => button.style.display = 'inline');
                monthInput.style.display = 'inline';
            });
        }

        function downloadReportAsImage() {
            const container = document.querySelector(".container");
            const buttons = document.querySelectorAll("button");
            const monthInput = document.getElementById("monthInput");

            // Hide buttons and month input temporarily
            buttons.forEach(button => button.style.display = 'none');
            monthInput.style.display = 'none';

            html2canvas(container).then(canvas => {
                const imgData = canvas.toDataURL('image/png');
                const link = document.createElement('a');
                link.href = imgData;
                link.download = 'report.png';
                link.click();

                // Show buttons and month input again
                buttons.forEach(button => button.style.display = 'inline');
                monthInput.style.display = 'inline';
            });
        }
    </script>
</body>
</html>