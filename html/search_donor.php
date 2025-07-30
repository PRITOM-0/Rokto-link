<?php
session_start(); // Start the session

// Check if the user is logged in. If not, redirect to login page.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Include database connection
require_once 'php/db_connect.php'; // This file will contain your database connection logic

$search_results_html = ""; // Initialize variable to store search results HTML

// Check if search form was submitted
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['search'])) {
    $blood_group = $_GET['blood_group'] ?? '';
    $city = $_GET['city'] ?? '';

    // Basic validation (more robust validation would be in php/search_donor.php)
    if (empty($blood_group) && empty($city)) {
        $search_results_html = "<p class='no-results'>Please enter at least a blood group or city to search.</p>";
    } else {
        // Prepare the SQL query
        // IMPORTANT: This is a simplified example. In a real application,
        // you would use prepared statements to prevent SQL injection.
        // The actual database interaction logic should be in php/search_donor.php.

        $sql = "SELECT d.full_name, d.blood_group, d.contact_number, d.city, d.state, d.last_donation_date
                FROM donors d
                WHERE d.is_eligible = TRUE"; // Only show eligible donors

        $params = [];
        $types = "";

        if (!empty($blood_group) && $blood_group !== 'Any') {
            $sql .= " AND d.blood_group = ?";
            $params[] = $blood_group;
            $types .= "s";
        }
        if (!empty($city)) {
            $sql .= " AND d.city LIKE ?";
            $params[] = "%" . $city . "%";
            $types .= "s";
        }

        // Execute the query (placeholder for actual execution)
        // In a proper setup, this would call a function from php/search_donor.php
        // For demonstration, we'll simulate results.
        
        // --- SIMULATED DATABASE FETCH ---
        // In a real scenario, you'd execute the prepared statement here
        // and fetch results from your database.
        $simulated_donors = [
            ['full_name' => 'John Doe', 'blood_group' => 'A+', 'contact_number' => '111-222-3333', 'city' => 'Springfield', 'state' => 'IL', 'last_donation_date' => '2025-01-15'],
            ['full_name' => 'Jane Smith', 'blood_group' => 'O-', 'contact_number' => '444-555-6666', 'city' => 'Springfield', 'IL', 'last_donation_date' => '2024-11-20'],
            ['full_name' => 'Peter Jones', 'blood_group' => 'A+', 'contact_number' => '777-888-9999', 'city' => 'Shelbyville', 'IL', 'last_donation_date' => '2025-02-01'],
        ];

        $found_donors = [];
        foreach ($simulated_donors as $donor) {
            $match_blood = empty($blood_group) || $blood_group === 'Any' || $donor['blood_group'] === $blood_group;
            $match_city = empty($city) || stripos($donor['city'], $city) !== false;

            if ($match_blood && $match_city) {
                $found_donors[] = $donor;
            }
        }
        // --- END SIMULATED DATABASE FETCH ---

        if (!empty($found_donors)) {
            $search_results_html .= "<div class='results-table-container'><table class='donor-results-table'><thead><tr><th>Name</th><th>Blood Group</th><th>Contact</th><th>City</th><th>State</th><th>Last Donation</th></tr></thead><tbody>";
            foreach ($found_donors as $donor) {
                $last_donation = $donor['last_donation_date'] ? htmlspecialchars($donor['last_donation_date']) : 'N/A';
                $search_results_html .= "<tr>";
                $search_results_html .= "<td>" . htmlspecialchars($donor['full_name']) . "</td>";
                $search_results_html .= "<td>" . htmlspecialchars($donor['blood_group']) . "</td>";
                $search_results_html .= "<td>" . htmlspecialchars($donor['contact_number']) . "</td>";
                $search_results_html .= "<td>" . htmlspecialchars($donor['city']) . "</td>";
                $search_results_html .= "<td>" . htmlspecialchars($donor['state']) . "</td>";
                $search_results_html .= "<td>" . $last_donation . "</td>";
                $search_results_html .= "</tr>";
            }
            $search_results_html .= "</tbody></table></div>";
        } else {
            $search_results_html = "<p class='no-results'>No eligible donors found matching your criteria.</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BloodLink - Search Donors</title>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* General Body and Layout */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f4f4f4;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Navigation Bar */
        .navbar {
            background-color: #c0392b; /* Darker Red */
            padding: 15px 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .navbar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 90%;
            max-width: 1200px;
        }

        .navbar-brand {
            color: #fff;
            font-size: 28px;
            font-weight: bold;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .navbar-brand:hover {
            background-color: #a93226;
        }

        .navbar-links a {
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
            margin-left: 10px;
            border-radius: 5px;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .navbar-links a:hover {
            background-color: #e74c3c;
            transform: translateY(-2px);
        }

        .navbar-links .btn-dashboard {
            background-color: #fff;
            color: #c0392b;
        }

        .navbar-links .btn-dashboard:hover {
            background-color: #eee;
        }

        .navbar-links .btn-logout {
            background-color: #e74c3c; /* Red */
            color: #fff;
        }

        .navbar-links .btn-logout:hover {
            background-color: #c0392b;
        }

        /* Main Content Area */
        .main-container {
            flex: 1; /* Allows content area to grow and fill space */
            width: 90%;
            max-width: 900px;
            margin: 40px auto; /* Center content and add vertical margin */
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            box-sizing: border-box;
        }

        .main-container h2 {
            font-size: 2.8em;
            color: #333;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
            text-align: center;
        }

        /* Search Form Styling */
        .search-form {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
            justify-content: center;
            align-items: flex-end; /* Align items to the bottom */
        }

        .form-group {
            flex: 1; /* Allows items to grow */
            min-width: 200px; /* Minimum width for form groups */
        }

        .form-group label {
            display: block;
            font-size: 1em;
            color: #555;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-group select,
        .form-group input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }

        .form-group select:focus,
        .form-group input[type="text"]:focus {
            outline: none;
            border-color: #e74c3c;
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.2);
        }

        .search-button {
            padding: 12px 25px;
            background-color: #e74c3c;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .search-button:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        /* Search Results Styling */
        .results-section {
            margin-top: 30px;
        }

        .results-section h3 {
            font-size: 2em;
            color: #c0392b;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .results-table-container {
            overflow-x: auto; /* Allows horizontal scrolling on small screens */
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.05);
        }

        .donor-results-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px; /* Ensure table doesn't get too narrow */
        }

        .donor-results-table th,
        .donor-results-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .donor-results-table th {
            background-color: #f2f2f2;
            color: #333;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.9em;
        }

        .donor-results-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .donor-results-table tbody tr:hover {
            background-color: #fdeaea; /* Light red on hover */
        }

        .no-results {
            text-align: center;
            color: #555;
            font-size: 1.1em;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            border: 1px solid #ddd;
            margin-top: 20px;
        }

        /* Footer (similar to other pages) */
        .footer {
            background-color: #333;
            color: #fff;
            padding: 20px 0;
            text-align: center;
            width: 100%;
            margin-top: auto; /* Push footer to the bottom */
        }

        .footer p {
            margin-bottom: 10px;
            font-size: 0.8em;
            color: #ccc;
        }

        .footer-links a {
            color: #ccc;
            text-decoration: none;
            margin: 0 10px;
            transition: color 0.3s ease;
            font-size: 0.8em;
        }

        .footer-links a:hover {
            color: #fff;
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .navbar-content {
                flex-direction: column;
                text-align: center;
            }

            .navbar-brand {
                margin-bottom: 15px;
            }

            .navbar-links a {
                margin: 0 5px 10px 5px;
                display: block;
            }

            .main-container {
                margin: 20px auto;
                width: 95%;
                padding: 20px;
            }

            .main-container h2 {
                font-size: 2.2em;
            }

            .search-form {
                flex-direction: column;
                align-items: stretch;
            }

            .form-group {
                min-width: unset; /* Remove min-width on small screens */
            }

            .search-button {
                width: 100%;
            }

            .donor-results-table th,
            .donor-results-table td {
                padding: 10px;
                font-size: 0.9em;
            }
        }

        @media (max-width: 480px) {
            .main-container h2 {
                font-size: 1.8em;
            }
            .search-button {
                font-size: 1em;
                padding: 10px 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="navbar-content">
            <a href="index.html" class="navbar-brand">BloodLink</a>
            <div class="navbar-links">
                <a href="dashboard.php" class="btn-dashboard">Dashboard</a>
                <a href="php/logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <div class="main-container">
        <h2>Search for Donors</h2>

        <!-- Search Form -->
        <form action="search_donor.php" method="GET" class="search-form">
            <div class="form-group">
                <label for="blood_group">Blood Group:</label>
                <select id="blood_group" name="blood_group">
                    <option value="Any">Any</option>
                    <option value="A+" <?php echo (isset($_GET['blood_group']) && $_GET['blood_group'] == 'A+') ? 'selected' : ''; ?>>A+</option>
                    <option value="A-" <?php echo (isset($_GET['blood_group']) && $_GET['blood_group'] == 'A-') ? 'selected' : ''; ?>>A-</option>
                    <option value="B+" <?php echo (isset($_GET['blood_group']) && $_GET['blood_group'] == 'B+') ? 'selected' : ''; ?>>B+</option>
                    <option value="B-" <?php echo (isset($_GET['blood_group']) && $_GET['blood_group'] == 'B-') ? 'selected' : ''; ?>>B-</option>
                    <option value="AB+" <?php echo (isset($_GET['blood_group']) && $_GET['blood_group'] == 'AB+') ? 'selected' : ''; ?>>AB+</option>
                    <option value="AB-" <?php echo (isset($_GET['blood_group']) && $_GET['blood_group'] == 'AB-') ? 'selected' : ''; ?>>AB-</option>
                    <option value="O+" <?php echo (isset($_GET['blood_group']) && $_GET['blood_group'] == 'O+') ? 'selected' : ''; ?>>O+</option>
                    <option value="O-" <?php echo (isset($_GET['blood_group']) && $_GET['blood_group'] == 'O-') ? 'selected' : ''; ?>>O-</option>
                </select>
            </div>
            <div class="form-group">
                <label for="city">City:</label>
                <input type="text" id="city" name="city" placeholder="e.g., Springfield" value="<?php echo htmlspecialchars($_GET['city'] ?? ''); ?>">
            </div>
            <button type="submit" name="search" class="search-button">Search Donors</button>
        </form>

        <!-- Search Results Display Area -->
        <div class="results-section">
            <h3>Search Results</h3>
            <?php echo $search_results_html; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 BloodLink. All rights reserved.</p>
            <div class="footer-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Contact Us</a>
            </div>
        </div>
    </footer>

    <!-- Client-side JS for dynamic filtering (optional, can be added later) -->
    <script src="js/search.js"></script>
</body>
</html>
