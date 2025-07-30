<?php
session_start(); // Start the session to access session variables

// Check if the user is logged in. If not, redirect to login page.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Get user data from session
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$user_type = $_SESSION['user_type']; // 'donor', 'recipient', or 'admin'
$full_name = $_SESSION['full_name'] ?? 'User'; // Use full_name if available, else default

// Include database connection
require_once 'php/db_connect.php';

// Fetch additional user-specific data if needed (e.g., last donation date for donor, requests for recipient)
// This is a placeholder; actual data fetching would go here.
$dashboard_content = "";
$sidebar_links = "";

if ($user_type === 'donor') {
    $dashboard_content = "
        <h3 class='dashboard-section-title'>Your Donor Dashboard</h3>
        <p>Welcome, " . htmlspecialchars($full_name) . "! Thank you for being a vital part of our community.</p>
        <div class='dashboard-info-card'>
            <h4>Last Donation</h4>
            <p><strong>Date:</strong> Not recorded yet.</p>
            <p><strong>Eligibility:</strong> You are currently eligible to donate.</p>
            <p><em>(This information would be dynamically loaded from your donor profile)</em></p>
            <a href='#' class='dashboard-action-button'>View Your Donations</a>
        </div>
        <div class='dashboard-info-card'>
            <h4>Upcoming Events</h4>
            <p>Check out nearby blood donation drives and events.</p>
            <a href='search_donor.php' class='dashboard-action-button'>Find Events</a>
        </div>
    ";
    $sidebar_links = "
        <a href='#' class='sidebar-link active'>Dashboard</a>
        <a href='update_profile.php' class='sidebar-link'>Update Profile</a>
        <a href='#' class='sidebar-link'>My Donations</a>
        <a href='search_donor.php' class='sidebar-link'>Find Events</a>
    ";
} elseif ($user_type === 'recipient') {
    $dashboard_content = "
        <h3 class='dashboard-section-title'>Your Recipient Dashboard</h3>
        <p>Welcome, " . htmlspecialchars($full_name) . "! We're here to help you find the blood you need.</p>
        <div class='dashboard-info-card'>
            <h4>Your Blood Requests</h4>
            <p><strong>Status:</strong> No active requests.</p>
            <p><em>(This information would be dynamically loaded from your blood requests)</em></p>
            <a href='request_blood.php' class='dashboard-action-button'>Make a New Request</a>
        </div>
        <div class='dashboard-info-card'>
            <h4>Search for Donors</h4>
            <p>Quickly find available donors matching your blood group.</p>
            <a href='search_donor.php' class='dashboard-action-button'>Search Donors</a>
        </div>
    ";
    $sidebar_links = "
        <a href='#' class='sidebar-link active'>Dashboard</a>
        <a href='update_profile.php' class='sidebar-link'>Update Profile</a>
        <a href='request_blood.php' class='sidebar-link'>Request Blood</a>
        <a href='search_donor.php' class='sidebar-link'>Search Donors</a>
    ";
} elseif ($user_type === 'admin') {
    $dashboard_content = "
        <h3 class='dashboard-section-title'>Admin Dashboard</h3>
        <p>Welcome, Administrator " . htmlspecialchars($full_name) . "! Manage the system effectively.</p>
        <div class='dashboard-grid'>
            <div class='dashboard-admin-card'>
                <h4>Manage Users</h4>
                <p>View and manage all user accounts (donors, recipients, admins).</p>
                <a href='manage_users.php' class='dashboard-action-button'>Go to Users</a>
            </div>
            <div class='dashboard-admin-card'>
                <h4>Manage Events</h4>
                <p>Create, update, or delete blood donation events.</p>
                <a href='manage_events.php' class='dashboard-action-button'>Go to Events</a>
            </div>
            <div class='dashboard-admin-card'>
                <h4>Track Donations</h4>
                <p>View and record all blood donation activities.</p>
                <a href='donations.php' class='dashboard-action-button'>Go to Donations</a>
            </div>
            <div class='dashboard-admin-card'>
                <h4>Medical Records</h4>
                <p>Manage donor medical eligibility records.</p>
                <a href='medical_records.php' class='dashboard-action-button'>Go to Medical Records</a>
            </div>
        </div>
    ";
    $sidebar_links = "
        <a href='#' class='sidebar-link active'>Dashboard</a>
        <a href='manage_users.php' class='sidebar-link'>Manage Users</a>
        <a href='manage_events.php' class='sidebar-link'>Manage Events</a>
        <a href='donations.php' class='sidebar-link'>Track Donations</a>
        <a href='medical_records.php' class='sidebar-link'>Medical Records</a>
    ";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BloodLink - Dashboard</title>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
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

        .navbar-links .btn-logout {
            background-color: #e74c3c; /* Red */
            color: #fff;
        }

        .navbar-links .btn-logout:hover {
            background-color: #c0392b;
        }

        /* Main Content Area */
        .main-content-area {
            display: flex;
            flex: 1; /* Allows content area to grow and fill space */
            width: 90%;
            max-width: 1200px;
            margin: 20px auto; /* Center content and add vertical margin */
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden; /* Ensures rounded corners apply correctly */
        }

        /* Sidebar Navigation */
        .sidebar {
            width: 250px;
            background-color: #34495e; /* Dark blue-grey */
            padding: 30px 0;
            color: #fff;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            flex-shrink: 0; /* Prevent sidebar from shrinking */
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 1.8em;
            color: #ecf0f1; /* Light grey */
            padding: 0 20px;
        }

        .sidebar-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-links a {
            display: block;
            padding: 15px 20px;
            color: #ecf0f1;
            text-decoration: none;
            font-size: 1.1em;
            transition: background-color 0.3s ease, color 0.3s ease;
            border-left: 5px solid transparent; /* For active indicator */
        }

        .sidebar-links a:hover {
            background-color: #4a627a; /* Lighter blue-grey on hover */
            border-left-color: #e74c3c; /* Red highlight on hover */
        }

        .sidebar-links a.active {
            background-color: #e74c3c; /* Red for active link */
            border-left-color: #fff; /* White highlight for active */
            font-weight: bold;
        }

        /* Dashboard Content Area */
        .dashboard-content {
            flex-grow: 1; /* Allows content to take remaining space */
            padding: 30px;
        }

        .dashboard-content h2 {
            font-size: 2.8em;
            color: #333;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
        }

        .dashboard-content p {
            font-size: 1.1em;
            color: #555;
            margin-bottom: 15px;
        }

        .dashboard-section-title {
            font-size: 2em;
            color: #c0392b;
            margin-top: 30px;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        /* Info Cards (for Donor/Recipient) */
        .dashboard-info-card {
            background-color: #fdeaea; /* Light red background */
            border: 1px solid #f0b2b2;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        .dashboard-info-card h4 {
            font-size: 1.5em;
            color: #c0392b;
            margin-bottom: 10px;
        }

        .dashboard-info-card p {
            font-size: 1em;
            color: #666;
            margin-bottom: 10px;
        }

        .dashboard-action-button {
            display: inline-block;
            background-color: #e74c3c;
            color: #fff;
            padding: 10px 18px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            font-size: 0.9em;
            transition: background-color 0.3s ease;
        }

        .dashboard-action-button:hover {
            background-color: #c0392b;
        }

        /* Admin Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .dashboard-admin-card {
            background-color: #eef7f9; /* Light blue-grey */
            border: 1px solid #d0e0e3;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .dashboard-admin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15);
        }

        .dashboard-admin-card h4 {
            font-size: 1.8em;
            color: #34495e;
            margin-bottom: 15px;
        }

        .dashboard-admin-card p {
            font-size: 1em;
            color: #666;
            margin-bottom: 20px;
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
        @media (max-width: 992px) {
            .main-content-area {
                flex-direction: column;
                margin: 10px auto;
                width: 95%;
            }

            .sidebar {
                width: 100%;
                padding: 20px 0;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            }

            .sidebar h2 {
                margin-bottom: 20px;
            }

            .sidebar-links {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 10px;
            }

            .sidebar-links a {
                border-left: none;
                border-bottom: 3px solid transparent;
                padding: 10px 15px;
                font-size: 1em;
            }

            .sidebar-links a:hover {
                border-left-color: transparent;
                border-bottom-color: #e74c3c;
            }

            .sidebar-links a.active {
                border-left-color: transparent;
                border-bottom-color: #fff;
            }

            .dashboard-content {
                padding: 20px;
            }

            .dashboard-content h2 {
                font-size: 2.2em;
            }

            .dashboard-section-title {
                font-size: 1.8em;
            }

            .dashboard-admin-card h4 {
                font-size: 1.6em;
            }
        }

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

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-content h2 {
                font-size: 2em;
            }
        }

        @media (max-width: 480px) {
            .dashboard-content h2 {
                font-size: 1.8em;
            }
            .dashboard-section-title {
                font-size: 1.5em;
            }
            .dashboard-info-card h4, .dashboard-admin-card h4 {
                font-size: 1.3em;
            }
            .dashboard-action-button {
                padding: 8px 15px;
                font-size: 0.8em;
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
                <span>Welcome, <?php echo htmlspecialchars($username); ?>!</span>
                <a href="php/logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <div class="main-content-area">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <h2>Navigation</h2>
            <div class="sidebar-links">
                <?php echo $sidebar_links; ?>
            </div>
        </aside>

        <!-- Dashboard Content -->
        <main class="dashboard-content">
            <h2>Dashboard</h2>
            <?php echo $dashboard_content; ?>
        </main>
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
</body>
</html>
