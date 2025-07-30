<?php
/**
 * php/add_event.php
 *
 * This script handles the server-side logic for adding new blood donation events.
 * It receives POST data from manage_events.php, validates it, and inserts
 * the new event record into the 'events' table.
 *
 * It uses prepared statements to prevent SQL injection.
 */

// Start the session
session_start();

// Check if the user is logged in AND is an 'admin'. If not, redirect accordingly.
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    if (isset($_SESSION['user_id'])) {
        header("Location: ../dashboard.php"); // Redirect to dashboard if logged in but not admin
    } else {
        header("Location: ../login.html"); // Redirect to login if not logged in
    }
    exit();
}

// Include the database connection file
require_once 'db_connect.php';

// Check if the form was submitted using POST method and the 'add_event' button was pressed
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_event'])) {
    // Collect and sanitize input data
    $event_name = trim($_POST['event_name'] ?? '');
    $event_date = $_POST['event_date'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $organizer_id = $_SESSION['user_id']; // The ID of the currently logged-in admin

    // --- Server-side Validation ---
    $errors = [];

    if (empty($event_name)) {
        $errors[] = "Event name is required.";
    }
    if (empty($event_date)) {
        $errors[] = "Event date is required.";
    } elseif (!strtotime($event_date)) { // Basic date format validation
        $errors[] = "Invalid event date format.";
    }
    if (empty($location)) {
        $errors[] = "Location is required.";
    }

    // If there are validation errors, redirect back to manage_events.php with errors
    if (!empty($errors)) {
        $error_string = implode("<br>", $errors);
        header("Location: ../manage_events.php?error=" . urlencode($error_string));
        exit();
    }

    // --- Proceed with Insertion if no errors ---
    // Prepare an insert statement
    $sql = "INSERT INTO events (event_name, event_date, location, description, organizer_id) VALUES (?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters
        // 'ssssi' corresponds to string, string, string, string, integer types
        $stmt->bind_param("ssssi", $event_name, $event_date, $location, $description, $organizer_id);

        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            // Event added successfully, redirect back to manage_events.php with success message
            header("Location: ../manage_events.php?success=" . urlencode("Event '{$event_name}' added successfully!"));
            exit();
        } else {
            // Error executing the statement
            $error_message = "Error: Could not add event. " . $stmt->error;
            error_log("Add Event Error: " . $error_message); // Log the error for debugging
            header("Location: ../manage_events.php?error=" . urlencode("Failed to add event. Please try again."));
            exit();
        }

        // Close statement
        $stmt->close();
    } else {
        // Error preparing the statement
        $error_message = "Database error: Could not prepare statement. " . $conn->error;
        error_log("Add Event Prepare Error: " . $error_message); // Log the error for debugging
        header("Location: ../manage_events.php?error=" . urlencode("An unexpected database error occurred."));
        exit();
    }

} else {
    // If accessed directly without POST data from the form, redirect to manage_events.php
    header("Location: ../manage_events.php");
    exit();
}

// Close database connection
mysqli_close($conn);
?>
