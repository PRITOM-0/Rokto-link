<?php
/**
 * php/update_profile.php
 *
 * This script handles the server-side logic for updating a user's profile.
 * It allows both 'donor' and 'recipient' users to update their specific details.
 * It receives POST data from a profile update form (which would typically be on dashboard.php
 * or a dedicated update_profile.php page), validates it, and updates the
 * corresponding record in the 'donors' or 'recipients' table, and potentially 'users' table.
 *
 * It uses prepared statements to prevent SQL injection.
 */

// Start the session
session_start();

// Check if the user is logged in. If not, redirect to login page.
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit();
}

// Include the database connection file
require_once 'db_connect.php';

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$message = ""; // To store success or error messages

// --- Fetch Current User Data for Pre-filling Form (if not a POST request) ---
$current_user_data = [];
if ($_SERVER["REQUEST_METHOD"] == "GET" || (isset($_POST['update_profile']) && !empty($errors))) {
    if ($user_type === 'donor') {
        $sql_fetch = "SELECT u.username, u.email, d.full_name, d.date_of_birth, d.gender, d.blood_group, d.contact_number, d.address, d.city, d.state
                      FROM users u JOIN donors d ON u.user_id = d.user_id WHERE u.user_id = ?";
    } elseif ($user_type === 'recipient') {
        $sql_fetch = "SELECT u.username, u.email, r.full_name, r.date_of_birth, r.gender, r.blood_group, r.contact_number, r.address, r.city, r.state, r.medical_condition
                      FROM users u JOIN recipients r ON u.user_id = r.user_id WHERE u.user_id = ?";
    } else {
        // Admins might not have donor/recipient specific profiles to update here
        $sql_fetch = "SELECT username, email FROM users WHERE user_id = ?";
    }

    if ($sql_fetch && $stmt_fetch = $conn->prepare($sql_fetch)) {
        $stmt_fetch->bind_param("i", $user_id);
        $stmt_fetch->execute();
        $result_fetch = $stmt_fetch->get_result();
        if ($result_fetch->num_rows > 0) {
            $current_user_data = $result_fetch->fetch_assoc();
        }
        $stmt_fetch->close();
    }
}


// --- Handle Profile Update Logic ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    // Collect and sanitize input data
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
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

    // Check for duplicate username/email (excluding current user's own)
    if (empty($errors)) {
        $stmt_check = $conn->prepare("SELECT user_id FROM users WHERE (username = ? OR email = ?) AND user_id != ?");
        $stmt_check->bind_param("ssi", $username, $email, $user_id);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $errors[] = "Username or Email already exists for another user. Please choose a different one.";
        }
        $stmt_check->close();
    }


    // If there are validation errors, set message and re-populate form with submitted data
    if (!empty($errors)) {
        $message = "<p class='error-message'>" . implode("<br>", $errors) . "</p>";
        // Re-populate $current_user_data with submitted (invalid) data for display
        $current_user_data = $_POST;
        // Ensure user_id, user_type are kept from session
        $current_user_data['user_id'] = $user_id;
        $current_user_data['user_type'] = $user_type;

    } else {
        // --- Proceed with Update if no errors ---
        mysqli_begin_transaction($conn);

        try {
            // 1. Update 'users' table
            $sql_users = "UPDATE users SET username = ?, email = ? WHERE user_id = ?";
            $stmt_users = $conn->prepare($sql_users);
            $stmt_users->bind_param("ssi", $username, $email, $user_id);
            $stmt_users->execute();
            $stmt_users->close();

            // 2. Update 'donors' or 'recipients' table based on user_type
            if ($user_type === 'donor') {
                $sql_profile = "UPDATE donors SET full_name = ?, date_of_birth = ?, gender = ?, blood_group = ?, contact_number = ?, address = ?, city = ?, state = ? WHERE user_id = ?";
                $stmt_profile = $conn->prepare($sql_profile);
                $stmt_profile->bind_param("ssssssssi", $full_name, $date_of_birth, $gender, $blood_group, $contact_number, $address, $city, $state, $user_id);
                $stmt_profile->execute();
                $stmt_profile->close();
            } elseif ($user_type === 'recipient') {
                $sql_profile = "UPDATE recipients SET full_name = ?, date_of_birth = ?, gender = ?, blood_group = ?, contact_number = ?, address = ?, city = ?, state = ?, medical_condition = ? WHERE user_id = ?";
                $stmt_profile = $conn->prepare($sql_profile);
                $stmt_profile->bind_param("sssssssssi", $full_name, $date_of_birth, $gender, $blood_group, $contact_number, $address, $city, $state, $medical_condition, $user_id);
                $stmt_profile->execute();
                $stmt_profile->close();
            }

            // Commit the transaction
            mysqli_commit($conn);

            // Update session variables if username or email changed
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION['full_name'] = $full_name; // Update full_name in session

            $message = "<p class='success-message'>Profile updated successfully!</p>";

            // Re-fetch data to ensure form shows latest from DB after successful update
            if ($user_type === 'donor') {
                $sql_fetch = "SELECT u.username, u.email, d.full_name, d.date_of_birth, d.gender, d.blood_group, d.contact_number, d.address, d.city, d.state
                              FROM users u JOIN donors d ON u.user_id = d.user_id WHERE u.user_id = ?";
            } elseif ($user_type === 'recipient') {
                $sql_fetch = "SELECT u.username, u.email, r.full_name, r.date_of_birth, r.gender, r.blood_group, r.contact_number, r.address, r.city, r.state, r.medical_condition
                              FROM users u JOIN recipients r ON u.user_id = r.user_id WHERE u.user_id = ?";
            } else {
                $sql_fetch = "SELECT username, email FROM users WHERE user_id = ?";
            }

            if ($sql_fetch && $stmt_fetch = $conn->prepare($sql_fetch)) {
                $stmt_fetch->bind_param("i", $user_id);
                $stmt_fetch->execute();
                $result_fetch = $stmt_fetch->get_result();
                if ($result_fetch->num_rows > 0) {
                    $current_user_data = $result_fetch->fetch_assoc();
                }
                $stmt_fetch->close();
            }

        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($conn);
            error_log("Profile Update Error: " . $e->getMessage());
            $message = "<p class='error-message'>An unexpected error occurred during profile update. Please try again.</p>";
        }
    }
}

// Close database connection
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BloodLink - Update Profile</title>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* General Body and Layout */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f4f4f4;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Navigation Bar (reusing styles from style.css) */
        .navbar {
            background-color: #c0392b; /* Darker Red */
            padding: 15px 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .navbar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 90%;
            max-width: 1200px;
        }

        .navbar-brand {
            color: #fff;
            font-size: 28px;
            font-weight: bold;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .navbar-brand:hover {
            background-color: #a93226;
        }

        .navbar-links a {
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
            margin-left: 10px;
            border-radius: 5px;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .navbar-links a:hover {
            background-color: #e74c3c;
            transform: translateY(-2px);
        }

        .navbar-links .btn-dashboard {
            background-color: #fff;
            color: #c0392b;
        }

        .navbar-links .btn-dashboard:hover {
            background-color: #eee;
        }

        .navbar-links .btn-logout {
            background-color: #e74c3c; /* Red */
            color: #fff;
        }

        .navbar-links .btn-logout:hover {
            background-color: #c0392b;
        }

        /* Main Content Area */
        .main-container {
            flex: 1; /* Allows content area to grow and fill space */
            width: 90%;
            max-width: 700px; /* Adjust max-width for forms */
            margin: 40px auto; /* Center content and add vertical margin */
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            box-sizing: border-box;
        }

        .main-container h2 {
            font-size: 2.8em;
            color: #333;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
            text-align: center;
        }

        /* Form Styling (reusing styles from style.css) */
        .profile-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .form-group {
            margin-bottom: 0;
        }

        .form-row {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .form-row .form-group {
            flex: 1;
            min-width: 250px;
        }

        .form-group label {
            display: block;
            font-size: 1em;
            color: #555;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="date"],
        .form-group input[type="tel"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #e74c3c;
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.2);
        }

        .error-message-inline { /* For client-side JS validation */
            color: #e74c3c;
            font-size: 0.9em;
            margin-top: 5px;
            display: none; /* Hidden by default, shown by JS */
        }

        .submit-button {
            width: 100%;
            padding: 15px;
            background-color: #e74c3c;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 1.2em;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .submit-button:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        /* Messages from PHP (reusing styles from style.css) */
        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }

        /* Footer (reusing styles from style.css) */
        .footer {
            background-color: #333;
            color: #fff;
            padding: 20px 0;
            text-align: center;
            width: 100%;
            margin-top: auto;
        }

        .footer p {
            margin-bottom: 10px;
            font-size: 0.8em;
            color: #ccc;
        }

        .footer-links a {
            color: #ccc;
            text-decoration: none;
            margin: 0 10px;
            transition: color 0.3s ease;
            font-size: 0.8em;
        }

        .footer-links a:hover {
            color: #fff;
            text-decoration: underline;
        }

        /* Responsive Design (reusing general styles from style.css) */
        @media (max-width: 768px) {
            .navbar-content {
                flex-direction: column;
                text-align: center;
            }

            .navbar-brand {
                margin-bottom: 15px;
            }

            .navbar-links a {
                margin: 0 5px 10px 5px;
                display: block;
            }

            .main-container {
                margin: 20px auto;
                width: 95%;
                padding: 20px;
            }

            .main-container h2 {
                font-size: 2.2em;
            }

            .form-row {
                flex-direction: column;
                gap: 0;
            }

            .form-row .form-group {
                min-width: unset;
            }

            .submit-button {
                font-size: 1.1em;
                padding: 12px;
            }
        }

        @media (max-width: 480px) {
            .main-container h2 {
                font-size: 1.8em;
            }
            .submit-button {
                font-size: 1em;
                padding: 10px 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="navbar-content">
            <a href="index.html" class="navbar-brand">BloodLink</a>
            <div class="navbar-links">
                <a href="dashboard.php" class="btn-dashboard">Dashboard</a>
                <a href="php/logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <div class="main-container">
        <h2>Update Your Profile</h2>

        <?php echo $message; // Display success or error messages ?>

        <!-- Profile Update Form -->
        <form action="update_profile.php" method="POST" id="profileUpdateForm" class="profile-form">
            <!-- User Account Details -->
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" placeholder="Your username" value="<?php echo htmlspecialchars($current_user_data['username'] ?? ''); ?>" required>
                <p id="usernameError" class="error-message-inline">Username is required and must be unique.</p>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" placeholder="Your email address" value="<?php echo htmlspecialchars($current_user_data['email'] ?? ''); ?>" required>
                <p id="emailError" class="error-message-inline">Please enter a valid email address.</p>
            </div>

            <!-- Personal Details (Common for Donor/Recipient) -->
            <div class="form-group">
                <label for="full_name">Full Name:</label>
                <input type="text" id="full_name" name="full_name" placeholder="Your full name" value="<?php echo htmlspecialchars($current_user_data['full_name'] ?? ''); ?>" required>
                <p id="fullNameError" class="error-message-inline">Full name is required.</p>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="date_of_birth">Date of Birth:</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($current_user_data['date_of_birth'] ?? ''); ?>" required>
                    <p id="dobError" class="error-message-inline">Date of birth is required.</p>
                </div>
                <div class="form-group">
                    <label for="gender">Gender:</label>
                    <select id="gender" name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="Male" <?php echo (isset($current_user_data['gender']) && $current_user_data['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo (isset($current_user_data['gender']) && $current_user_data['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo (isset($current_user_data['gender']) && $current_user_data['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                    <p id="genderError" class="error-message-inline">Please select your gender.</p>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="blood_group">Blood Group:</label>
                    <select id="blood_group" name="blood_group" required>
                        <option value="">Select Blood Group</option>
                        <option value="A+" <?php echo (isset($current_user_data['blood_group']) && $current_user_data['blood_group'] == 'A+') ? 'selected' : ''; ?>>A+</option>
                        <option value="A-" <?php echo (isset($current_user_data['blood_group']) && $current_user_data['blood_group'] == 'A-') ? 'selected' : ''; ?>>A-</option>
                        <option value="B+" <?php echo (isset($current_user_data['blood_group']) && $current_user_data['blood_group'] == 'B+') ? 'selected' : ''; ?>>B+</option>
                        <option value="B-" <?php echo (isset($current_user_data['blood_group']) && $current_user_data['blood_group'] == 'B-') ? 'selected' : ''; ?>>B-</option>
                        <option value="AB+" <?php echo (isset($current_user_data['blood_group']) && $current_user_data['blood_group'] == 'AB+') ? 'selected' : ''; ?>>AB+</option>
                        <option value="AB-" <?php echo (isset($current_user_data['blood_group']) && $current_user_data['blood_group'] == 'AB-') ? 'selected' : ''; ?>>AB-</option>
                        <option value="O+" <?php echo (isset($current_user_data['blood_group']) && $current_user_data['blood_group'] == 'O+') ? 'selected' : ''; ?>>O+</option>
                        <option value="O-" <?php echo (isset($current_user_data['blood_group']) && $current_user_data['blood_group'] == 'O-') ? 'selected' : ''; ?>>O-</option>
                    </select>
                    <p id="bloodGroupError" class="error-message-inline">Blood group is required.</p>
                </div>
                <div class="form-group">
                    <label for="contact_number">Contact Number:</label>
                    <input type="tel" id="contact_number" name="contact_number" placeholder="e.g., +1234567890" value="<?php echo htmlspecialchars($current_user_data['contact_number'] ?? ''); ?>" required>
                    <p id="contactNumberError" class="error-message-inline">Please enter a valid contact number.</p>
                </div>
            </div>

            <div class="form-group">
                <label for="address">Address:</label>
                <textarea id="address" name="address" rows="3" placeholder="Your full address" required><?php echo htmlspecialchars($current_user_data['address'] ?? ''); ?></textarea>
                <p id="addressError" class="error-message-inline">Address is required.</p>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="city">City:</label>
                    <input type="text" id="city" name="city" placeholder="Your city" value="<?php echo htmlspecialchars($current_user_data['city'] ?? ''); ?>" required>
                    <p id="cityError" class="error-message-inline">City is required.</p>
                </div>
                <div class="form-group">
                    <label for="state">State/Province:</label>
                    <input type="text" id="state" name="state" placeholder="Your state/province" value="<?php echo htmlspecialchars($current_user_data['state'] ?? ''); ?>" required>
                    <p id="stateError" class="error-message-inline">State/Province is required.</p>
                </div>
            </div>

            <!-- Recipient Specific Field (conditionally visible) -->
            <div id="medicalConditionGroup" class="form-group" style="display: <?php echo ($user_type === 'recipient') ? 'block' : 'none'; ?>;">
                <label for="medical_condition">Medical Condition (for Recipients):</label>
                <textarea id="medical_condition" name="medical_condition" rows="3" placeholder="Describe any relevant medical conditions"><?php echo htmlspecialchars($current_user_data['medical_condition'] ?? ''); ?></textarea>
                <p id="medicalConditionError" class="error-message-inline">Medical condition is required for recipients.</p>
            </div>

            <button type="submit" name="update_profile" class="submit-button">Update Profile</button>
        </form>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 BloodLink. All rights reserved.</p>
            <div class="footer-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Contact Us</a>
            </div>
        </div>
    </footer>

    <!-- Client-side validation script -->
    <script src="js/validation.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const profileUpdateForm = document.getElementById('profileUpdateForm');
            const userType = "<?php echo $user_type; ?>"; // Get user type from PHP
            const medicalConditionGroup = document.getElementById('medicalConditionGroup');
            const medicalConditionInput = document.getElementById('medical_condition');

            // Function to toggle medical condition field visibility
            function toggleMedicalCondition() {
                if (userType === 'recipient') {
                    medicalConditionGroup.style.display = 'block';
                    medicalConditionInput.required = true; // Make required for recipients
                } else {
                    medicalConditionGroup.style.display = 'none';
                    medicalConditionInput.required = false; // Not required for others
                    // medicalConditionInput.value = ''; // Don't clear value on update page
                }
            }

            // Initial call to set visibility based on user type
            toggleMedicalCondition();

            if (profileUpdateForm) {
                // Attach validation using the generic function from validation.js
                attachFormValidation(profileUpdateForm, function() {
                    let isValid = true;

                    // Helper function to show/hide error messages (from validation.js)
                    // validateField function is now part of validation.js, so we call it directly
                    // or recreate similar logic here if validation.js is not directly linked for this form.
                    // For simplicity, we'll re-implement the error toggling here, assuming validation.js provides the core functions.

                    // Re-using helper functions from validation.js
                    const isNotEmpty = window.isNotEmpty; // Assuming validation.js functions are global or imported
                    const isValidEmail = window.isValidEmail;
                    const isValidDate = window.isValidDate;

                    function toggleError(elementId, condition) {
                        const errorElement = document.getElementById(elementId);
                        if (errorElement) {
                            errorElement.style.display = condition ? 'block' : 'none';
                        }
                        if (condition) isValid = false;
                    }

                    // Validate Username
                    toggleError('usernameError', !isNotEmpty(document.getElementById('username').value));

                    // Validate Email
                    toggleError('emailError', !isValidEmail(document.getElementById('email').value));

                    // Validate Full Name
                    toggleError('fullNameError', !isNotEmpty(document.getElementById('full_name').value));

                    // Validate Date of Birth
                    toggleError('dobError', !isValidDate(document.getElementById('date_of_birth').value));

                    // Validate Gender
                    toggleError('genderError', !isNotEmpty(document.getElementById('gender').value));

                    // Validate Blood Group
                    toggleError('bloodGroupError', !isNotEmpty(document.getElementById('blood_group').value));

                    // Validate Contact Number
                    toggleError('contactNumberError', !isNotEmpty(document.getElementById('contact_number').value));

                    // Validate Address
                    toggleError('addressError', !isNotEmpty(document.getElementById('address').value));

                    // Validate City
                    toggleError('cityError', !isNotEmpty(document.getElementById('city').value));

                    // Validate State
                    toggleError('stateError', !isNotEmpty(document.getElementById('state').value));

                    // Validate Medical Condition if Recipient
                    if (userType === 'recipient') {
                        toggleError('medicalConditionError', medicalConditionInput.required && !isNotEmpty(medicalConditionInput.value));
                    }

                    return isValid;
                });
            }
        });
    </script>
</body>
</html>
