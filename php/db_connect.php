<?php
/**
 * php/db_connect.php
 *
 * This file establishes a connection to the MySQL database.
 * It uses basic PHP MySQLi for database interaction.
 *
 * IMPORTANT: Replace the placeholder values with your actual database credentials.
 * For XAMPP, typically:
 * DB_SERVER: 'localhost'
 * DB_USERNAME: 'root'
 * DB_PASSWORD: '' (empty string for default XAMPP root user)
 * DB_NAME: 'blood_donation' (as defined in your SQL script)
 */

// Database credentials
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', ''); // Default for XAMPP, change if you set one
define('DB_NAME', 'blood_donation'); // The database name from your SQL script

/* Attempt to connect to MySQL database */
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

// Set charset to UTF-8 for proper character handling
mysqli_set_charset($conn, "utf8");

// You can optionally add a simple function here to close the connection
// when it's no longer needed, though PHP automatically closes connections
// at the end of script execution.
/*
function close_db_connection($conn) {
    if ($conn) {
        mysqli_close($conn);
    }
}
*/
?>
