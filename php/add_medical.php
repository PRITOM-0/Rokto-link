<?php
/**
 * php/add_medical.php
 *
 * This script handles the server-side logic for adding new donor medical records.
 * It receives POST data from medical_records.php, validates it, and inserts
 * the new medical record into the 'medical_records' table.
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

// Check if the form was submitted using POST method and the 'add_medical_record' button was pressed
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_medical_record'])) {
    // Collect and sanitize input data
    $donor_id = trim($_POST['donor_id'] ?? '');
    $record_date = $_POST['record_date'] ?? '';
    $hemoglobin_level = trim($_POST['hemoglobin_level'] ?? '');
    $blood_pressure = trim($_POST['blood_pressure'] ?? '');
    $diseases_history = trim($_POST['diseases_history'] ?? '');
    $medications = trim($_POST['medications'] ?? '');
    $eligibility_status = $_POST['eligibility_status'] ?? '';
    $notes = trim($_POST['notes'] ?? '');
    $recorded_by = $_SESSION['user_id']; // The ID of the currently logged-in admin

    // --- Server-side Validation ---
    $errors = [];

    if (empty($donor_id) || !is_numeric($donor_id)) {
        $errors[] = "Invalid Donor selected.";
    }
    if (empty($record_date)) {
        $errors[] = "Record date is required.";
    } elseif (!strtotime($record_date)) { // Basic date format validation
        $errors[] = "Invalid record date format.";
    }
    if (empty($hemoglobin_level)) {
        $errors[] = "Hemoglobin level is required.";
    } elseif (!is_numeric($hemoglobin_level) || $hemoglobin_level <= 0) {
        $errors[] = "Hemoglobin level must be a positive number.";
    }
    if (empty($blood_pressure)) {
        $errors[] = "Blood pressure is required.";
    } elseif (!preg_match("/^\d{2,3}\/\d{2,3}$/", $blood_pressure)) { // e.g., 120/80
        $errors[] = "Invalid blood pressure format (e.g., 120/80).";
    }
    if (empty($eligibility_status) || !in_array($eligibility_status, ['Eligible', 'Temporarily Deferred', 'Permanently Deferred'])) {
        $errors[] = "Eligibility status is required and must be valid.";
    }

    // If there are validation errors, redirect back to medical_records.php with errors
    if (!empty($errors)) {
        $error_string = implode("<br>", $errors);
        header("Location: ../medical_records.php?error=" . urlencode($error_string));
        exit();
    }

    // --- Proceed with Insertion if no errors ---
    // Prepare an insert statement
    $sql = "INSERT INTO medical_records (donor_id, record_date, hemoglobin_level, blood_pressure, diseases_history, medications, eligibility_status, notes, recorded_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters
        // 'isdsssssi' corresponds to integer, string, double, string, string, string, string, string, integer types
        $stmt->bind_param("isdsssssi", $donor_id, $record_date, $hemoglobin_level, $blood_pressure, $diseases_history, $medications, $eligibility_status, $notes, $recorded_by);

        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            // Medical record added successfully, redirect back to medical_records.php with success message
            header("Location: ../medical_records.php?success=" . urlencode("Medical record for Donor ID {$donor_id} added successfully!"));
            exit();
        } else {
            // Error executing the statement
            $error_message = "Error: Could not add medical record. " . $stmt->error;
            error_log("Add Medical Record Error: " . $error_message); // Log the error for debugging
            header("Location: ../medical_records.php?error=" . urlencode("Failed to add medical record. Please try again."));
            exit();
        }

        // Close statement
        $stmt->close();
    } else {
        // Error preparing the statement
        $error_message = "Database error: Could not prepare statement. " . $conn->error;
        error_log("Add Medical Record Prepare Error: " . $error_message); // Log the error for debugging
        header("Location: ../medical_records.php?error=" . urlencode("An unexpected database error occurred."));
        exit();
    }

} else {
    // If accessed directly without POST data from the form, redirect to medical_records.php
    header("Location: ../medical_records.php");
    exit();
}

// Close database connection
mysqli_close($conn);
?>
