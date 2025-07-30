<?php
/**
 * php/add_donation.php
 *
 * This script handles the server-side logic for adding new blood donation records.
 * It receives POST data from donations.php, validates it, and inserts
 * the new donation record into the 'donations' table.
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

// Check if the form was submitted using POST method and the 'add_donation' button was pressed
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_donation'])) {
    // Collect and sanitize input data
    $donor_id = trim($_POST['donor_id'] ?? '');
    $event_id = trim($_POST['event_id'] ?? null); // Can be null if no event selected
    $donation_date = $_POST['donation_date'] ?? '';
    $blood_group = $_POST['blood_group'] ?? '';
    $quantity_ml = trim($_POST['quantity_ml'] ?? '');
    $status = $_POST['status'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    // Convert empty event_id to NULL for the database
    if (empty($event_id)) {
        $event_id = null;
    }

    // --- Server-side Validation ---
    $errors = [];

    if (empty($donor_id) || !is_numeric($donor_id)) {
        $errors[] = "Invalid Donor selected.";
    }
    if (empty($donation_date)) {
        $errors[] = "Donation date is required.";
    } elseif (!strtotime($donation_date)) { // Basic date format validation
        $errors[] = "Invalid donation date format.";
    }
    if (empty($blood_group) || !in_array($blood_group, ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])) {
        $errors[] = "Blood group is required and must be valid.";
    }
    if (empty($quantity_ml)) {
        $errors[] = "Quantity (ml) is required.";
    } elseif (!is_numeric($quantity_ml) || $quantity_ml <= 0) {
        $errors[] = "Quantity (ml) must be a positive number.";
    }
    if (empty($status) || !in_array($status, ['completed', 'pending', 'cancelled'])) {
        $errors[] = "Status is required and must be valid.";
    }

    // If there are validation errors, redirect back to donations.php with errors
    if (!empty($errors)) {
        $error_string = implode("<br>", $errors);
        header("Location: ../donations.php?error=" . urlencode($error_string));
        exit();
    }

    // --- Proceed with Insertion if no errors ---
    // Prepare an insert statement
    $sql = "INSERT INTO donations (donor_id, event_id, donation_date, blood_group, quantity_ml, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        // Determine the type for event_id: 'i' for integer if not null, 's' for string if null (though 'i' is safer for NULL)
        // For nullable integers, you can bind them as integers, and PHP/MySQLi handles NULL correctly.
        $stmt->bind_param("iisdsss", $donor_id, $event_id, $donation_date, $blood_group, $quantity_ml, $status, $notes);

        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            // Donation added successfully, redirect back to donations.php with success message
            header("Location: ../donations.php?success=" . urlencode("Donation from Donor ID {$donor_id} recorded successfully!"));
            exit();
        } else {
            // Error executing the statement
            $error_message = "Error: Could not add donation record. " . $stmt->error;
            error_log("Add Donation Error: " . $error_message); // Log the error for debugging
            header("Location: ../donations.php?error=" . urlencode("Failed to add donation record. Please try again."));
            exit();
        }

        // Close statement
        $stmt->close();
    } else {
        // Error preparing the statement
        $error_message = "Database error: Could not prepare statement. " . $conn->error;
        error_log("Add Donation Prepare Error: " . $error_message); // Log the error for debugging
        header("Location: ../donations.php?error=" . urlencode("An unexpected database error occurred."));
        exit();
    }

} else {
    // If accessed directly without POST data from the form, redirect to donations.php
    header("Location: ../donations.php");
    exit();
}

// Close database connection
mysqli_close($conn);
?>
