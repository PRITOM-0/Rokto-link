<?php
require_once 'config.php';

$recipients = [];
$search_name = isset($_GET['search_name']) ? sanitize_input($_GET['search_name']) : '';
$blood_group_filter = isset($_GET['blood_group']) ? sanitize_input($_GET['blood_group']) : '';
$hospital_filter = isset($_GET['hospital']) ? sanitize_input($_GET['hospital']) : '';

$sql = "SELECT * FROM recipients WHERE 1=1";
$params = [];
$types = "";

if (!empty($search_name)) {
    $sql .= " AND name LIKE ?";
    $params[] = "%" . $search_name . "%";
    $types .= "s";
}
if (!empty($blood_group_filter)) {
    $sql .= " AND blood_group = ?";
    $params[] = $blood_group_filter;
    $types .= "s";
}
if (!empty($hospital_filter)) {
    $sql .= " AND hospital_name LIKE ?";
    $params[] = "%" . $hospital_filter . "%";
    $types .= "s";
}

$sql .= " ORDER BY name ASC";

$stmt = $conn->prepare($sql);
if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $recipients = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    // Handle prepare error
    $recipients = ['error' => 'Database query preparation failed.'];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Recipients - Rokto-Link</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="img/icon.png">
</head>
<body class="font-inter">

    <!-- Header -->
    <header class="header">
        <div class="container header-content">
            <h1 class="site-title">All Recipients</h1>
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
            <h2 class="section-title">Filter Recipients</h2>
            <form action="" method="GET" class="form-layout filter-form">
                <div class="form-group">
                    <label for="search_name">Search by Name:</label>
                    <input type="text" id="search_name" name="search_name" value="<?php echo htmlspecialchars($search_name); ?>" placeholder="Enter recipient name">
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
                <div class="form-group">
                    <label for="hospital">Hospital Name:</label>
                    <input type="text" id="hospital" name="hospital" value="<?php echo htmlspecialchars($hospital_filter); ?>" placeholder="Enter hospital name">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </div>
            </form>
        </div>

        <?php if (!empty($recipients) && !isset($recipients['error'])): ?>
            <div class="content-section">
                <h2 class="section-title">All Recipients List</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Blood Group</th>
                                <th>Reason</th>
                                <th>Hospital</th>
                                <th>City</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recipients as $recipient): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($recipient['id']); ?></td>
                                    <td><?php echo htmlspecialchars($recipient['name']); ?></td>
                                    <td><?php echo htmlspecialchars($recipient['email']); ?></td>
                                    <td><?php echo htmlspecialchars($recipient['phone']); ?></td>
                                    <td class="font-semibold"><?php echo htmlspecialchars($recipient['blood_group']); ?></td>
                                    <td><?php echo htmlspecialchars($recipient['reason']); ?></td>
                                    <td><?php echo htmlspecialchars($recipient['hospital_name']); ?></td>
                                    <td><?php echo htmlspecialchars($recipient['city']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php elseif (isset($recipients['error'])): ?>
            <div class="message error-message">
                <span><?php echo $recipients['error']; ?></span>
            </div>
        <?php else: ?>
            <div class="message warning-message">
                <span>No recipients found.</span>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> BloodLink. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>
