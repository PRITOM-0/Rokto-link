<?php
/**
 * php/delete_event.php
 *
 * This script handles the server-side logic for deleting blood donation events.
 * It receives POST data (typically from a form on manage_events.php), validates it,
 * and deletes the event record from the 'events' table.
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

// Check if the form was submitted using POST method and the 'delete_event' button was pressed
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_event'])) {
    // Collect and sanitize input data
    $event_id = trim($_POST['event_id_to_delete'] ?? ''); // Hidden input for event ID to delete

    // --- Server-side Validation ---
    $errors = [];

    if (empty($event_id) || !is_numeric($event_id)) {
        $errors[] = "Invalid Event ID provided for deletion.";
    }

    // If there are validation errors, redirect back to manage_events.php with errors
    if (!empty($errors)) {
        $error_string = implode("<br>", $errors);
        header("Location: ../manage_events.php?error=" . urlencode($error_string));
        exit();
    }

    // --- Proceed with Deletion if no errors ---
    // Prepare a delete statement
    $sql = "DELETE FROM events WHERE event_id = ?";

    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters
        // 'i' corresponds to integer type for event_id
        $stmt->bind_param("i", $event_id);

        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            // Check if any rows were affected (i.e., if the deletion actually happened)
            if ($stmt->affected_rows > 0) {
                // Event deleted successfully, redirect back to manage_events.php with success message
                header("Location: ../manage_events.php?success=" . urlencode("Event ID {$event_id} deleted successfully!"));
                exit();
            } else {
                // No rows affected, might mean event_id didn't exist
                header("Location: ../manage_events.php?info=" . urlencode("Event ID {$event_id} not found or already deleted."));
                exit();
            }
        } else {
            // Error executing the statement
            $error_message = "Error: Could not delete event. " . $stmt->error;
            error_log("Delete Event Error: " . $error_message); // Log the error for debugging
            header("Location: ../manage_events.php?error=" . urlencode("Failed to delete event. Please try again."));
            exit();
        }

        // Close statement
        $stmt->close();
    } else {
        // Error preparing the statement
        $error_message = "Database error: Could not prepare statement. " . $conn->error;
        error_log("Delete Event Prepare Error: " . $error_message); // Log the error for debugging
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
