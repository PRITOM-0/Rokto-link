<?php
/**
 * php/update_event.php
 *
 * This script handles the server-side logic for updating existing blood donation events.
 * It receives POST data (typically from a form on manage_events.php that's pre-filled
 * with existing event data for editing), validates it, and updates the
 * event record in the 'events' table.
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

// Check if the form was submitted using POST method and the 'update_event' button was pressed
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_event'])) {
    // Collect and sanitize input data
    $event_id = trim($_POST['event_id'] ?? ''); // Hidden input for event ID
    $event_name = trim($_POST['event_name'] ?? '');
    $event_date = $_POST['event_date'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');
    // Organizer ID is not updated here, as it's set during creation

    // --- Server-side Validation ---
    $errors = [];

    if (empty($event_id) || !is_numeric($event_id)) {
        $errors[] = "Invalid Event ID provided.";
    }
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

    // --- Proceed with Update if no errors ---
    // Prepare an update statement
    $sql = "UPDATE events SET event_name = ?, event_date = ?, location = ?, description = ? WHERE event_id = ?";

    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters
        // 'ssssi' corresponds to string, string, string, string, integer types
        $stmt->bind_param("ssssi", $event_name, $event_date, $location, $description, $event_id);

        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            // Check if any rows were affected (i.e., if the update actually happened)
            if ($stmt->affected_rows > 0) {
                // Event updated successfully, redirect back to manage_events.php with success message
                header("Location: ../manage_events.php?success=" . urlencode("Event '{$event_name}' (ID: {$event_id}) updated successfully!"));
                exit();
            } else {
                // No rows affected, might mean event_id didn't exist or no changes were made
                header("Location: ../manage_events.php?info=" . urlencode("No changes made to Event ID {$event_id} or event not found."));
                exit();
            }
        } else {
            // Error executing the statement
            $error_message = "Error: Could not update event. " . $stmt->error;
            error_log("Update Event Error: " . $error_message); // Log the error for debugging
            header("Location: ../manage_events.php?error=" . urlencode("Failed to update event. Please try again."));
            exit();
        }

        // Close statement
        $stmt->close();
    } else {
        // Error preparing the statement
        $error_message = "Database error: Could not prepare statement. " . $conn->error;
        error_log("Update Event Prepare Error: " . $error_message); // Log the error for debugging
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
