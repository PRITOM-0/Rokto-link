<?php
session_start(); // Start the session

// Check if the user is logged in AND is an 'admin'. If not, redirect accordingly.
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    if (isset($_SESSION['user_id'])) {
        header("Location: dashboard.php"); // Redirect to dashboard if logged in but not admin
    } else {
        header("Location: login.html"); // Redirect to login if not logged in
    }
    exit();
}

// Include database connection
require_once 'php/db_connect.php'; // This file will contain your database connection logic

$message = ""; // To store success or error messages for user operations

// --- User Management Logic (Simulated) ---

// Handle Update User (Placeholder - would typically involve fetching user details first)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $user_type = $_POST['user_type'] ?? '';

    if (empty($user_id) || empty($username) || empty($email) || empty($user_type)) {
        $message = "<p class='error-message'>Please fill in all required fields for updating the user.</p>";
    } else {
        // --- SIMULATED DATABASE UPDATE ---
        // In a real scenario, you'd execute an UPDATE query on the 'users' table.
        // Example: UPDATE users SET username=?, email=?, user_type=? WHERE user_id=?;
        $simulated_success = true; // Assume success
        if ($simulated_success) {
            $message = "<p class='success-message'>User ID <strong>" . htmlspecialchars($user_id) . "</strong> updated successfully!</p>";
        } else {
            $message = "<p class='error-message'>Failed to update user. Please try again.</p>";
        }
        // --- END SIMULATED DATABASE UPDATE ---
    }
}

// Handle Delete User
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id_to_delete'] ?? '';

    if (empty($user_id)) {
        $message = "<p class='error-message'>No user ID provided for deletion.</p>";
    } else {
        // --- SIMULATED DATABASE DELETE ---
        // In a real scenario, you'd execute a DELETE query from the 'users' and related tables (donors/recipients).
        // Example: DELETE FROM users WHERE user_id=?;
        $simulated_success = true; // Assume success
        if ($simulated_success) {
            $message = "<p class='success-message'>User ID <strong>" . htmlspecialchars($user_id) . "</strong> deleted successfully!</p>";
        } else {
            $message = "<p class='error-message'>Failed to delete user. Please try again.</p>";
        }
        // --- END SIMULATED DATABASE DELETE ---
    }
}

// Fetch Existing Users (Simulated)
$users = [
    ['user_id' => 1, 'username' => 'admin_user', 'email' => 'admin@example.com', 'user_type' => 'admin', 'created_at' => '2024-01-01 10:00:00'],
    ['user_id' => 2, 'username' => 'john_donor', 'email' => 'john@example.com', 'user_type' => 'donor', 'created_at' => '2024-01-05 11:30:00'],
    ['user_id' => 3, 'username' => 'jane_recipient', 'email' => 'jane@example.com', 'user_type' => 'recipient', 'created_at' => '2024-01-10 14:00:00'],
    ['user_id' => 4, 'username' => 'peter_d', 'email' => 'peter@example.com', 'user_type' => 'donor', 'created_at' => '2024-02-15 09:00:00'],
];

$users_table_rows = "";
if (!empty($users)) {
    foreach ($users as $user) {
        $users_table_rows .= "
            <tr>
                <td>" . htmlspecialchars($user['user_id']) . "</td>
                <td>" . htmlspecialchars($user['username']) . "</td>
                <td>" . htmlspecialchars($user['email']) . "</td>
                <td>" . htmlspecialchars(ucfirst($user['user_type'])) . "</td>
                <td>" . htmlspecialchars($user['created_at']) . "</td>
                <td>
                    <a href='#' class='action-button edit-button'>Edit</a>
                    <form action='manage_users.php' method='POST' style='display:inline-block; margin-left: 5px;'>
                        <input type='hidden' name='user_id_to_delete' value='" . htmlspecialchars($user['user_id']) . "'>
                        <button type='submit' name='delete_user' class='action-button delete-button' onclick='return confirm(\"Are you sure you want to delete this user?\");'>Delete</button>
                    </form>
                </td>
            </tr>
        ";
    }
} else {
    $users_table_rows = "<tr><td colspan='6' class='no-records'>No users found.</td></tr>";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BloodLink - Manage Users</title>
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
            max-width: 1000px; /* Wider for tables */
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

        /* Messages from PHP */
        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }

        /* Users Table Styling */
        .users-table-section h3 {
            font-size: 2em;
            color: #c0392b;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .table-container {
            overflow-x: auto; /* Allows horizontal scrolling on small screens */
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.05);
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px; /* Ensure table doesn't get too narrow */
        }

        .users-table th,
        .users-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .users-table th {
            background-color: #f2f2f2;
            color: #333;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.9em;
        }

        .users-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .users-table tbody tr:hover {
            background-color: #fdeaea; /* Light red on hover */
        }

        .no-records {
            text-align: center;
            color: #555;
            padding: 20px;
            font-style: italic;
        }

        .action-button {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            font-weight: 600;
            transition: background-color 0.3s ease;
            text-decoration: none; /* For anchor tags */
            display: inline-block;
        }

        .edit-button {
            background-color: #3498db; /* Blue */
            color: #fff;
        }

        .edit-button:hover {
            background-color: #2980b9;
        }

        .delete-button {
            background-color: #e74c3c; /* Red */
            color: #fff;
        }

        .delete-button:hover {
            background-color: #c0392b;
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

            .users-table th,
            .users-table td {
                padding: 10px;
                font-size: 0.85em;
            }
        }

        @media (max-width: 480px) {
            .main-container h2 {
                font-size: 1.8em;
            }
            .action-button {
                padding: 6px 10px;
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
                <a href="dashboard.php" class="btn-dashboard">Dashboard</a>
                <a href="php/logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <div class="main-container">
        <h2>Manage Users</h2>

        <?php echo $message; // Display success or error messages ?>

        <!-- Existing Users List -->
        <div class="users-table-section">
            <h3>All System Users</h3>
            <div class="table-container">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>User Type</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php echo $users_table_rows; ?>
                    </tbody>
                </table>
            </div>
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
</body>
</html>
