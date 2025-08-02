<?php
require_once 'config.php';

$search_results = [];
$search_type = isset($_GET['search_type']) ? sanitize_input($_GET['search_type']) : '';
$search_query = isset($_GET['query']) ? sanitize_input($_GET['query']) : '';
$blood_group_filter = isset($_GET['blood_group']) ? sanitize_input($_GET['blood_group']) : '';
$status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';
$city_filter = isset($_GET['city']) ? sanitize_input($_GET['city']) : '';

if (!empty($search_type) && (!empty($search_query) || !empty($blood_group_filter) || !empty($status_filter) || !empty($city_filter))) {
    switch ($search_type) {





        case 'donors':
            $sql = "SELECT * FROM donors WHERE 1=1";
            $params = [];
            $types = "";

            if (!empty($search_query)) {
                $sql .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
                $params[] = "%" . $search_query . "%";
                $params[] = "%" . $search_query . "%";
                $params[] = "%" . $search_query . "%";
                $types .= "sss";
            }
            if (!empty($blood_group_filter)) {
                $sql .= " AND blood_group = ?";
                $params[] = $blood_group_filter;
                $types .= "s";
            }
            if (!empty($city_filter)) {
                $sql .= " AND city LIKE ?";
                $params[] = "%" . $city_filter . "%";
                $types .= "s";
            }
            $sql .= " ORDER BY name ASC";
            break;


















        case 'requests':
            $sql = "SELECT br.*, r.name AS recipient_name, r.phone AS recipient_phone, r.hospital_name
                    FROM blood_requests br
                    JOIN recipients r ON br.recipient_id = r.id
                    WHERE 1=1";
            $params = [];
            $types = "";

            if (!empty($search_query)) {
                $sql .= " AND (r.name LIKE ? OR r.hospital_name LIKE ?)";
                $params[] = "%" . $search_query . "%";
                $params[] = "%" . $search_query . "%";
                $types .= "ss";
            }
            if (!empty($blood_group_filter)) {
                $sql .= " AND br.blood_group = ?";
                $params[] = $blood_group_filter;
                $types .= "s";
            }
            if (!empty($status_filter)) {
                $sql .= " AND br.status = ?";
                $params[] = $status_filter;
                $types .= "s";
            }
            $sql .= " ORDER BY br.request_date DESC";
            break;

















        case 'donation_history':
            $sql = "SELECT dh.*, d.name AS donor_name, d.email AS donor_email, d.phone AS donor_phone
                    FROM donation_history dh
                    JOIN donors d ON dh.donor_id = d.id
                    WHERE 1=1";
            $params = [];
            $types = "";

            if (!empty($search_query)) {
                $sql .= " AND (d.name LIKE ? OR dh.notes LIKE ?)";
                $params[] = "%" . $search_query . "%";
                $params[] = "%" . $search_query . "%";
                $types .= "ss";
            }
            if (!empty($blood_group_filter)) {
                $sql .= " AND dh.blood_group = ?";
                $params[] = $blood_group_filter;
                $types .= "s";
            }
            // Can add date range filters here if needed
            $sql .= " ORDER BY dh.donation_date DESC";
            break;











        case 'inventory':
            $sql = "SELECT * FROM blood_inventory WHERE 1=1";
            $params = [];
            $types = "";

            if (!empty($blood_group_filter)) {
                $sql .= " AND blood_group = ?";
                $params[] = $blood_group_filter;
                $types .= "s";
            }
            $sql .= " ORDER BY blood_group ASC";
            break;







            

        default:
            $sql = ""; // No valid search type
            break;
    }

    if (!empty($sql)) {
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            $search_results = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        } else {
            // Handle prepare error
            $search_results = ['error' => 'Database query preparation failed.'];
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search - Rokto-Link</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="img/icon.png">
</head>
<body class="font-inter">

    <!-- Header -->
    <header class="header">
        <div class="container header-content">
            <h1 class="site-title">Search Blood Data</h1>
            <nav>
                <ul class="nav-list">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="dashboard.php" class="dashboard-button">Dashboard</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container main-content">
        <div class="content-section">
            <h2 class="section-title">Search Options</h2>
            <form action="" method="GET" class="form-layout search-form">
                <div class="form-group">
                    <label for="search_type">Search In:</label>
                    <select id="search_type" name="search_type" onchange="toggleFilters()">
                        <option value="">Select Table to Search</option>
                        <option value="donors" <?php echo ($search_type == 'donors') ? 'selected' : ''; ?>>Donors</option>
                        <option value="requests" <?php echo ($search_type == 'requests') ? 'selected' : ''; ?>>Blood Requests</option>
                        <option value="donation_history" <?php echo ($search_type == 'donation_history') ? 'selected' : ''; ?>>Donation History</option>
                        <option value="inventory" <?php echo ($search_type == 'inventory') ? 'selected' : ''; ?>>Blood Inventory</option>
                    </select>
                </div>

                <div id="commonFilters" class="filter-group">
                    <div class="form-group">
                        <label for="query">Keyword Search:</label>
                        <input type="text" id="query" name="query" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Enter name, email, hospital, notes...">
                    </div>
                    <div class="form-group">
                        <label for="blood_group">Blood Group:</label>
                        <select id="blood_group" name="blood_group">
                            <option value="">Any</option>
                            <option value="A+" <?php echo ($blood_group_filter == 'A+') ? 'selected' : ''; ?>>A+</option>
                            <option value="A-" <?php echo ($blood_group_filter == 'A-') ? 'selected' : ''; ?>>A-</option>
                            <option value="B+" <?php echo ($blood_group_filter == 'B+') ? 'selected' : ''; ?>>B+</option>
                            <option value="B-" <?php echo ($blood_group_filter == 'B-') ? 'selected' : ''; ?>>B-</option>
                            <option value="AB+" <?php echo ($blood_group_filter == 'AB+') ? 'selected' : ''; ?>>AB+</option>
                            <option value="AB-" <?php echo ($blood_group_filter == 'AB-') ? 'selected' : ''; ?>>AB-</option>
                            <option value="O+" <?php echo ($blood_group_filter == 'O+') ? 'selected' : ''; ?>>O+</option>
                            <option value="O-" <?php echo ($blood_group_filter == 'O-') ? 'selected' : ''; ?>>O-</option>
                        </select>
                    </div>
                </div>

                <div id="requestFilters" style="display: none;" class="filter-group">
                    <div class="form-group">
                        <label for="status">Request Status:</label>
                        <select id="status" name="status">
                            <option value="">Any</option>
                            <option value="pending" <?php echo ($status_filter == 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="fulfilled" <?php echo ($status_filter == 'fulfilled') ? 'selected' : ''; ?>>Fulfilled</option>
                            <option value="urgent" <?php echo ($status_filter == 'urgent') ? 'selected' : ''; ?>>Urgent</option>
                            <option value="cancelled" <?php echo ($status_filter == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                </div>

                <div id="donorFilters" style="display: none;" class="filter-group">
                    <div class="form-group">
                        <label for="city">City (Donors):</label>
                        <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($city_filter); ?>" placeholder="Enter city">
                    </div>
                </div>

                <button type="submit" class="btn btn-submit">Search</button>
            </form>
        </div>

        <?php if (!empty($search_results) && !isset($search_results['error'])): ?>
            <div class="content-section">
                <h2 class="section-title">Search Results (<?php echo ucfirst(str_replace('_', ' ', $search_type)); ?>)</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <?php
                                if (!empty($search_results)) {
                                    foreach (array_keys($search_results[0]) as $key) {
                                        echo "<th>" . htmlspecialchars(ucwords(str_replace('_', ' ', $key))) . "</th>";
                                    }
                                }
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($search_results as $row): ?>
                                <tr>
                                    <?php foreach ($row as $value): ?>
                                        <td><?php echo htmlspecialchars($value); ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php elseif (isset($search_results['error'])): ?>
            <div class="message error-message">
                <span><?php echo $search_results['error']; ?></span>
            </div>
        <?php elseif (!empty($search_type)): ?>
            <div class="message warning-message">
                <span>No results found for your search criteria.</span>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> BloodLink. All rights reserved.</p>
        </div>
    </footer>

    <script>
        function toggleFilters() {
            const searchType = document.getElementById('search_type').value;
            document.getElementById('requestFilters').style.display = 'none';
            document.getElementById('donorFilters').style.display = 'none';

            // Reset specific filters when switching search type
            document.getElementById('status').value = '';
            document.getElementById('city').value = '';
            document.getElementById('query').value = ''; // Clear keyword search too

            if (searchType === 'requests') {
                document.getElementById('requestFilters').style.display = 'block';
            } else if (searchType === 'donors') {
                document.getElementById('donorFilters').style.display = 'block';
            }
        }

        // Call on page load to set initial state
        document.addEventListener('DOMContentLoaded', toggleFilters);
    </script>

</body>
</html>
