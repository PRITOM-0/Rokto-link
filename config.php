<?php

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');                                         //database username
define('DB_PASSWORD', '');                                             //database password
define('DB_NAME', 'blood_donation_db');

// connect to MySQL database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);


// Function to sanitize input
function sanitize_input($data) {
    global $conn; 
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}
?>
