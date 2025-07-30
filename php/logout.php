<?php
/**
 * php/logout.php
 *
 * This script handles the user logout process.
 * It destroys the current session and redirects the user to the login page.
 */

// Start the session if it's not already started
session_start();

// Unset all of the session variables
$_SESSION = array();

// Destroy the session.
// This will also delete the session cookie.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session.
session_destroy();

// Redirect to the login page (or home page) after logout
header("Location: ../login.html");
exit();
?>
