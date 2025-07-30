<?php
session_start(); // Start the session

// Check if the user is logged in. If not, redirect to login page.
// Also, check if the user is a 'recipient'. If not, redirect to dashboard or show error.
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'recipient') {
    // Redirect to dashboard if logged in but not a recipient, otherwise to login
    if (isset($_SESSION['user_id'])) {
        header("Location: dashboard.php");
    } else {
        header("Location: login.html");
    }
    exit();
}

// Include database connection
require_once 'php/db_connect.php'; // This file will contain your database connection logic

$message = ""; // To store success or error messages

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_request'])) {
    $recipient_id = $_SESSION['recipient_id'] ?? null; // Assuming recipient_id is stored in session after login/registration
    $blood_group = $_POST['blood_group'] ?? '';
    $quantity_units = $_POST['quantity_units'] ?? '';
    $urgency = $_POST['urgency'] ?? '';
    $hospital_name = $_POST['hospital_name'] ?? '';
    $hospital_address = $_POST['hospital_address'] ?? '';
    $contact_person = $_POST['contact_person'] ?? '';
    $contact_number = $_POST['contact_number'] ?? '';
    $reason = $_POST['reason'] ?? '';

    // Basic server-side validation (more robust validation in php/request_blood.php)
    if (empty($recipient_id) || empty($blood_group) || empty($quantity_units) || empty($urgency) || empty($hospital_name) || empty($contact_number)) {
        $message = "<p class='error-message'>Please fill in all required fields.</p>";
    } else {
        // In a real application, you would use prepared statements to insert data.
        // The actual database interaction logic should be in php/request_blood.php.

        // --- SIMULATED DATABASE INSERT ---
        // For demonstration, we'll just set a success message.
        // In a real scenario, you'd execute an INSERT query into a 'blood_requests' table.
        // Example: INSERT INTO blood_requests (recipient_id, blood_group, quantity_units, urgency, hospital_name, hospital_address, contact_person, contact_number, reason, request_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'pending');
        $simulated_success = true; // Assume success for now

        if ($simulated_success) {
            $message = "<p class='success-message'>Your blood request has been submitted successfully! We will notify you when a match is found.</p>";
            // Clear form fields after successful submission (optional)
            $_POST = array();
        } else {
            $message = "<p class='error-message'>Failed to submit blood request. Please try again.</p>";
        }
        // --- END SIMULATED DATABASE INSERT ---
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BloodLink - Request Blood</title>
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

        /* Navigation Bar */
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

        /* Form Styling */
        .blood-request-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 0; /* Override default margin if form-row is used */
        }

        .form-row {
            display: flex;
            gap: 20px;
            flex-wrap: wrap; /* Allow wrapping on smaller screens */
        }

        .form-row .form-group {
            flex: 1; /* Distribute space equally */
            min-width: 250px; /* Minimum width for form groups in a row */
        }

        .form-group label {
            display: block;
            font-size: 1em;
            color: #555;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-group input[type="text"],
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

        .error-message-inline {
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

        /* Messages from PHP */
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

        /* Footer (similar to other pages) */
        .footer {
            background-color: #333;
            color: #fff;
            padding: 20px 0;
            text-align: center;
            width: 100%;
            margin-top: auto; /* Push footer to the bottom */
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

        /* Responsive Design */
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
                gap: 0; /* Remove gap when stacked */
            }

            .form-row .form-group {
                min-width: unset; /* Remove min-width on small screens */
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
        <h2>Request Blood</h2>

        <?php echo $message; // Display success or error messages ?>

        <!-- Blood Request Form -->
        <form action="request_blood.php" method="POST" id="bloodRequestForm" class="blood-request-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="blood_group">Required Blood Group:</label>
                    <select id="blood_group" name="blood_group" required>
                        <option value="">Select Blood Group</option>
                        <option value="A+" <?php echo (isset($_POST['blood_group']) && $_POST['blood_group'] == 'A+') ? 'selected' : ''; ?>>A+</option>
                        <option value="A-" <?php echo (isset($_POST['blood_group']) && $_POST['blood_group'] == 'A-') ? 'selected' : ''; ?>>A-</option>
                        <option value="B+" <?php echo (isset($_POST['blood_group']) && $_POST['blood_group'] == 'B+') ? 'selected' : ''; ?>>B+</option>
                        <option value="B-" <?php echo (isset($_POST['blood_group']) && $_POST['blood_group'] == 'B-') ? 'selected' : ''; ?>>B-</option>
                        <option value="AB+" <?php echo (isset($_POST['blood_group']) && $_POST['blood_group'] == 'AB+') ? 'selected' : ''; ?>>AB+</option>
                        <option value="AB-" <?php echo (isset($_POST['blood_group']) && $_POST['blood_group'] == 'AB-') ? 'selected' : ''; ?>>AB-</option>
                        <option value="O+" <?php echo (isset($_POST['blood_group']) && $_POST['blood_group'] == 'O+') ? 'selected' : ''; ?>>O+</option>
                        <option value="O-" <?php echo (isset($_POST['blood_group']) && $_POST['blood_group'] == 'O-') ? 'selected' : ''; ?>>O-</option>
                    </select>
                    <p id="bloodGroupError" class="error-message-inline">Please select the required blood group.</p>
                </div>
                <div class="form-group">
                    <label for="quantity_units">Quantity Needed (Units):</label>
                    <input type="text" id="quantity_units" name="quantity_units" placeholder="e.g., 2 units" value="<?php echo htmlspecialchars($_POST['quantity_units'] ?? ''); ?>" required>
                    <p id="quantityError" class="error-message-inline">Please specify the quantity needed.</p>
                </div>
            </div>

            <div class="form-group">
                <label for="urgency">Urgency Level:</label>
                <select id="urgency" name="urgency" required>
                    <option value="">Select Urgency</option>
                    <option value="Critical" <?php echo (isset($_POST['urgency']) && $_POST['urgency'] == 'Critical') ? 'selected' : ''; ?>>Critical (Immediate)</option>
                    <option value="Urgent" <?php echo (isset($_POST['urgency']) && $_POST['urgency'] == 'Urgent') ? 'selected' : ''; ?>>Urgent (Within 24-48 hrs)</option>
                    <option value="Normal" <?php echo (isset($_POST['urgency']) && $_POST['urgency'] == 'Normal') ? 'selected' : ''; ?>>Normal (Within a week)</option>
                </select>
                <p id="urgencyError" class="error-message-inline">Please select urgency level.</p>
            </div>

            <div class="form-group">
                <label for="hospital_name">Hospital Name:</label>
                <input type="text" id="hospital_name" name="hospital_name" placeholder="e.g., City General Hospital" value="<?php echo htmlspecialchars($_POST['hospital_name'] ?? ''); ?>" required>
                <p id="hospitalNameError" class="error-message-inline">Hospital name is required.</p>
            </div>

            <div class="form-group">
                <label for="hospital_address">Hospital Address:</label>
                <textarea id="hospital_address" name="hospital_address" rows="3" placeholder="Full hospital address" required><?php echo htmlspecialchars($_POST['hospital_address'] ?? ''); ?></textarea>
                <p id="hospitalAddressError" class="error-message-inline">Hospital address is required.</p>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="contact_person">Contact Person:</label>
                    <input type="text" id="contact_person" name="contact_person" placeholder="e.g., Dr. Jane Doe" value="<?php echo htmlspecialchars($_POST['contact_person'] ?? ''); ?>" required>
                    <p id="contactPersonError" class="error-message-inline">Contact person is required.</p>
                </div>
                <div class="form-group">
                    <label for="contact_number">Contact Number:</label>
                    <input type="tel" id="contact_number" name="contact_number" placeholder="e.g., +1234567890" value="<?php echo htmlspecialchars($_POST['contact_number'] ?? ''); ?>" required>
                    <p id="contactNumberError" class="error-message-inline">Contact number is required.</p>
                </div>
            </div>

            <div class="form-group">
                <label for="reason">Reason for Request (Optional):</label>
                <textarea id="reason" name="reason" rows="4" placeholder="Briefly explain the reason for the blood request (e.g., surgery, emergency)"><?php echo htmlspecialchars($_POST['reason'] ?? ''); ?></textarea>
            </div>

            <button type="submit" name="submit_request" class="submit-button">Submit Request</button>
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
            const form = document.getElementById('bloodRequestForm');

            form.addEventListener('submit', function(event) {
                let isValid = true;

                // Helper function to show/hide error messages
                function validateField(inputElement, errorElement, validationFn) {
                    if (validationFn(inputElement.value.trim())) {
                        errorElement.style.display = 'none';
                    } else {
                        errorElement.style.display = 'block';
                        isValid = false;
                    }
                }

                // Validation functions
                const isNotEmpty = value => value !== '';
                const isNumeric = value => /^\d+$/.test(value); // For quantity, assuming whole units

                // Validate Blood Group
                validateField(document.getElementById('blood_group'), document.getElementById('bloodGroupError'), isNotEmpty);

                // Validate Quantity (simple non-empty and numeric check)
                const quantityInput = document.getElementById('quantity_units');
                validateField(quantityInput, document.getElementById('quantityError'), value => isNotEmpty(value) && isNumeric(value));

                // Validate Urgency
                validateField(document.getElementById('urgency'), document.getElementById('urgencyError'), isNotEmpty);

                // Validate Hospital Name
                validateField(document.getElementById('hospital_name'), document.getElementById('hospitalNameError'), isNotEmpty);

                // Validate Hospital Address
                validateField(document.getElementById('hospital_address'), document.getElementById('hospitalAddressError'), isNotEmpty);

                // Validate Contact Person
                validateField(document.getElementById('contact_person'), document.getElementById('contactPersonError'), isNotEmpty);

                // Validate Contact Number (simple non-empty check)
                validateField(document.getElementById('contact_number'), document.getElementById('contactNumberError'), isNotEmpty);

                if (!isValid) {
                    event.preventDefault(); // Prevent form submission if validation fails
                }
            });
        });
    </script>
</body>
</html>
