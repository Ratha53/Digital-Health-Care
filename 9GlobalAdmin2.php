<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Local-Admin</title>
    <link rel="stylesheet" type="text/css" href="styling.css">
    <style>
        #controls {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        select, input[type="text"] {
            padding: 5px;
            margin: 5px;
        }
        /* Remove pagination controls styling */
    </style>
</head>
<body>
    <h1>Local-Admin-List</h1>
    <?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "projecthospital";
    $connect = mysqli_connect($servername, $username, $password, $database);

    if (!$connect) {
        die("Connection Failed!" . mysqli_connect_error());
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_index'])) {
        $index = intval($_POST['delete_index']);
        $sql = "DELETE FROM `centralizeddatatable` WHERE `centralizeddatatable`.`S/N` = $index";
        mysqli_query($connect, $sql);
        exit;
    }

    $sql = "SELECT * FROM `centralizeddatatable`";
    $result = mysqli_query($connect, $sql);
    $rows = [];
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    ?>
    <script>
        let tableData = <?php echo json_encode($rows); ?>;
        let filteredData = tableData;

        function populateTable() {
            const tableBody = document.getElementById('table-body');
            tableBody.innerHTML = '';

            filteredData.forEach((row, index) => {
                let newRow = document.createElement('tr');

                newRow.innerHTML = `
                    <td>${index + 1}</td> 
                    <td>${row.District}</td>
                    <td>${row.CHCName}</td>
                    <td>${row.CHCAdminName}</td>
                    <td></td>
                    <td>${row.Date}</td>
                    <td>
                        <button class="button" onclick="openStatPage('${row.CHCName}', '${row.District}')">Stat</button>
                    </td>
                    <td><button class="delete-btn" data-index="${row['S/N']}">Delete</button></td>
                `;

                tableBody.appendChild(newRow);
            });

            addDeleteListeners();
        }

        function openStatPage(chcName, district) {
            const url = `12LocalAdmin7.php?CHCName=${encodeURIComponent(chcName)}&District=${encodeURIComponent(district)}`;
            window.location.href = url;
        }

        function addDeleteListeners() {
            const deleteButtons = document.querySelectorAll('.delete-btn');
            deleteButtons.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const index = e.target.getAttribute('data-index');
                    if (confirm('Are you sure you want to delete this record?')) {
                        fetch('', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'delete_index=' + index
                        })
                        .then(response => {
                            tableData = tableData.filter(row => row['S/N'] != index);
                            filteredData = filteredData.filter(row => row['S/N'] != index);
                            populateTable();
                        })
                        .catch(error => console.error('Error:', error));
                    }
                });
            });
        }

        function handleSearch(event) {
            const searchValue = event.target.value.toLowerCase();
            filteredData = tableData.filter(row =>
                row.District.toLowerCase().includes(searchValue) ||
                row.CHCName.toLowerCase().includes(searchValue) ||
                row.CHCAdminName.toLowerCase().includes(searchValue) ||
                row.Date.toLowerCase().includes(searchValue)
            );
            populateTable();
        }

        document.addEventListener('DOMContentLoaded', () => {
            populateTable();
            document.getElementById('search-input').addEventListener('input', handleSearch);
        });
    </script>

    <div id="controls">
        <div>
            <label for="search-input">Search:</label>
            <input type="text" id="search-input" placeholder="Search...">
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>S/N</th>
                <th>District</th>
                <th>CHC-Name</th>
                <th>CHC Local-Admin</th>
                <th>Local-Admin Thumb-Impression</th>
                <th>Date||Time</th>
                <th>Statistics</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody id="table-body">
            <!-- Rows will be inserted here by JS -->
        </tbody>
    </table>
</body>
</html>