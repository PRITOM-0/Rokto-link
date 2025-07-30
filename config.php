<?php
// config.php - Database connection and helper functions

// Database credentials
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Your database username
define('DB_PASSWORD', '');     // Your database password
define('DB_NAME', 'blood_donation_db');

// Attempt to connect to MySQL database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to sanitize input
function sanitize_input($data) {
    global $conn; // Use global connection object
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

?>
