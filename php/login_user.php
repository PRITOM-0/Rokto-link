<?php
/**
 * php/register_user.php
 *
 * This script handles the server-side logic for user registration.
 * It receives POST data from register.html, validates it, and inserts
 * new user records into the 'users' table, and then either the 'donors'
 * or 'recipients' table based on the user_type.
 *
 * It uses prepared statements to prevent SQL injection.
 */

// Include the database connection file
require_once 'db_connect.php';

// Check if the form was submitted using POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize input data
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? ''; // Password will be hashed
    $confirm_password = $_POST['confirm_password'] ?? '';
    $user_type = $_POST['user_type'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $blood_group = $_POST['blood_group'] ?? '';
    $contact_number = trim($_POST['contact_number'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $medical_condition = trim($_POST['medical_condition'] ?? ''); // Only for recipients

    // --- Server-side Validation ---
    $errors = [];

    if (empty($username)) {
        $errors[] = "Username is required.";
    } elseif (!preg_match("/^[a-zA-Z0-9_]+$/", $username)) {
        $errors[] = "Username can only contain letters, numbers, and underscores.";
    }

    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($user_type) || !in_array($user_type, ['donor', 'recipient'])) {
        $errors[] = "Invalid user type selected.";
    }

    if (empty($full_name)) {
        $errors[] = "Full name is required.";
    }

    if (empty($date_of_birth)) {
        $errors[] = "Date of birth is required.";
    }

    if (empty($gender) || !in_array($gender, ['Male', 'Female', 'Other'])) {
        $errors[] = "Gender is required.";
    }

    if (empty($blood_group) || !in_array($blood_group, ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])) {
        $errors[] = "Blood group is required.";
    }

    if (empty($contact_number)) {
        $errors[] = "Contact number is required.";
    }

    if (empty($address)) {
        $errors[] = "Address is required.";
    }

    if (empty($city)) {
        $errors[] = "City is required.";
    }

    if (empty($state)) {
        $errors[] = "State/Province is required.";
    }

    // Specific validation for recipient
    if ($user_type === 'recipient' && empty($medical_condition)) {
        $errors[] = "Medical condition is required for recipients.";
    }

    // Check if username or email already exists in the database
    if (empty($errors)) {
        $stmt_check = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $stmt_check->bind_param("ss", $username, $email);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $errors[] = "Username or Email already exists. Please choose a different one.";
        }
        $stmt_check->close();
    }

    // If there are validation errors, redirect back to register.html with errors
    if (!empty($errors)) {
        $error_string = implode("<br>", $errors);
        header("Location: ../register.html?error=" . urlencode($error_string));
        exit();
    }

    // --- Proceed with Registration if no errors ---

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Start a transaction for atomicity
    mysqli_begin_transaction($conn);

    try {
        // 1. Insert into 'users' table
        $sql_users = "INSERT INTO users (username, password, email, user_type) VALUES (?, ?, ?, ?)";
        $stmt_users = $conn->prepare($sql_users);
        $stmt_users->bind_param("ssss", $username, $hashed_password, $email, $user_type);
        $stmt_users->execute();

        // Get the last inserted user_id
        $user_id = $conn->insert_id;
        $stmt_users->close();

        // 2. Insert into 'donors' or 'recipients' table based on user_type
        if ($user_type === 'donor') {
            $sql_donors = "INSERT INTO donors (user_id, full_name, date_of_birth, gender, blood_group, contact_number, address, city, state) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_donors = $conn->prepare($sql_donors);
            $stmt_donors->bind_param("issssssss", $user_id, $full_name, $date_of_birth, $gender, $blood_group, $contact_number, $address, $city, $state);
            $stmt_donors->execute();
            $stmt_donors->close();
        } elseif ($user_type === 'recipient') {
            $sql_recipients = "INSERT INTO recipients (user_id, full_name, date_of_birth, gender, blood_group, contact_number, address, city, state, medical_condition) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_recipients = $conn->prepare($sql_recipients);
            $stmt_recipients->bind_param("isssssssss", $user_id, $full_name, $date_of_birth, $gender, $blood_group, $contact_number, $address, $city, $state, $medical_condition);
            $stmt_recipients->execute();
            $stmt_recipients->close();
        }

        // Commit the transaction
        mysqli_commit($conn);

        // Registration successful, redirect to login page with a success message
        header("Location: ../login.html?success=" . urlencode("Registration successful! Please log in."));
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        // Log the error for debugging (e.g., to a file)
        error_log("Registration error: " . $e->getMessage());
        // Redirect back to register.html with a generic error message
        header("Location: ../register.html?error=" . urlencode("An unexpected error occurred during registration. Please try again."));
        exit();
    } finally {
        // Close the database connection
        mysqli_close($conn);
    }

} else {
    // If accessed directly without POST data, redirect to register.html
    header("Location: ../register.html");
    exit();
}
?>
