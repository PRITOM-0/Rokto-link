<?php
session_start(); // Start the session

// Check if the user is logged in AND is an 'admin'. If not, redirect accordingly.
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    if (isset($_SESSION['user_id'])) {
        header("Location: dashboard.php"); // Redirect to dashboard if logged in but not admin
    } else {
        header("Location: login.html"); // Redirect to login if not logged in
    }
    exit();
}

// Include database connection
require_once 'php/db_connect.php'; // This file will contain your database connection logic

$message = ""; // To store success or error messages for donation operations

// --- Donation Management Logic (Simulated) ---

// Handle Add Donation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_donation'])) {
    $donor_id = $_POST['donor_id'] ?? '';
    $event_id = $_POST['event_id'] ?? null; // Can be null if not part of an event
    $donation_date = $_POST['donation_date'] ?? '';
    $blood_group = $_POST['blood_group'] ?? '';
    $quantity_ml = $_POST['quantity_ml'] ?? '';
    $status = $_POST['status'] ?? '';
    $notes = $_POST['notes'] ?? '';

    // Basic server-side validation
    if (empty($donor_id) || empty($donation_date) || empty($blood_group) || empty($quantity_ml) || empty($status)) {
        $message = "<p class='error-message'>Please fill in all required fields for the donation record.</p>";
    } else {
        // --- SIMULATED DATABASE INSERT ---
        // In a real scenario, you'd use prepared statements to insert into 'donations' table.
        // Example: INSERT INTO donations (donor_id, event_id, donation_date, blood_group, quantity_ml, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?);
        $simulated_success = true; // Assume success
        if ($simulated_success) {
            $message = "<p class='success-message'>Donation from Donor ID <strong>" . htmlspecialchars($donor_id) . "</strong> recorded successfully!</p>";
            // Clear form fields after successful submission (optional)
            $_POST = array();
        } else {
            $message = "<p class='error-message'>Failed to add donation record. Please try again.</p>";
        }
        // --- END SIMULATED DATABASE INSERT ---
    }
}

// Handle Delete Donation (Simulated)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_donation'])) {
    $donation_id = $_POST['donation_id_to_delete'] ?? '';

    if (empty($donation_id)) {
        $message = "<p class='error-message'>No donation ID provided for deletion.</p>";
    } else {
        // --- SIMULATED DATABASE DELETE ---
        // In a real scenario, you'd execute a DELETE query from the 'donations' table.
        // Example: DELETE FROM donations WHERE donation_id=?;
        $simulated_success = true; // Assume success
        if ($simulated_success) {
            $message = "<p class='success-message'>Donation ID <strong>" . htmlspecialchars($donation_id) . "</strong> deleted successfully!</p>";
        } else {
            $message = "<p class='error-message'>Failed to delete donation. Please try again.</p>";
        }
        // --- END SIMULATED DATABASE DELETE ---
    }
}


// Fetch Existing Donors for Dropdown (Simulated)
$simulated_donors_for_dropdown = [
    ['donor_id' => 2, 'full_name' => 'John Donor'],
    ['donor_id' => 4, 'full_name' => 'Peter D'],
    ['donor_id' => 5, 'full_name' => 'Alice B'],
];

$donor_options = "<option value=''>Select Donor</option>";
foreach ($simulated_donors_for_dropdown as $donor) {
    $donor_options .= "<option value='" . htmlspecialchars($donor['donor_id']) . "'>" . htmlspecialchars($donor['full_name']) . " (ID: " . htmlspecialchars($donor['donor_id']) . ")</option>";
}

// Fetch Existing Events for Dropdown (Simulated)
$simulated_events_for_dropdown = [
    ['event_id' => 1, 'event_name' => 'City Blood Drive 2025'],
    ['event_id' => 2, 'event_name' => 'University Health Day'],
    ['event_id' => 3, 'event_name' => 'Hospital Emergency Drive'],
];

$event_options = "<option value=''>No Specific Event</option>";
foreach ($simulated_events_for_dropdown as $event) {
    $event_options .= "<option value='" . htmlspecialchars($event['event_id']) . "'>" . htmlspecialchars($event['event_name']) . " (ID: " . htmlspecialchars($event['event_id']) . ")</option>";
}


// Fetch Existing Donations (Simulated)
// In a real application, this would join 'donations' with 'donors' and 'events'.
$donations = [
    ['donation_id' => 1001, 'donor_id' => 2, 'donor_name' => 'John Donor', 'event_id' => 1, 'event_name' => 'City Blood Drive 2025', 'donation_date' => '2025-08-15', 'blood_group' => 'A+', 'quantity_ml' => 450, 'status' => 'completed', 'notes' => 'Regular donor.'],
    ['donation_id' => 1002, 'donor_id' => 4, 'donor_name' => 'Peter D', 'event_id' => null, 'event_name' => 'N/A', 'donation_date' => '2025-07-20', 'blood_group' => 'O-', 'quantity_ml' => 400, 'status' => 'completed', 'notes' => 'Walk-in donation.'],
    ['donation_id' => 1003, 'donor_id' => 5, 'donor_name' => 'Alice B', 'event_id' => 2, 'event_name' => 'University Health Day', 'donation_date' => '2025-09-20', 'blood_group' => 'B+', 'quantity_ml' => 450, 'status' => 'pending', 'notes' => 'First time donor.'],
];

$donations_table_rows = "";
if (!empty($donations)) {
    foreach ($donations as $donation) {
        $event_display_name = $donation['event_name'] ?? 'N/A';
        $donations_table_rows .= "
            <tr>
                <td>" . htmlspecialchars($donation['donation_id']) . "</td>
                <td>" . htmlspecialchars($donation['donor_name']) . " (ID: " . htmlspecialchars($donation['donor_id']) . ")</td>
                <td>" . htmlspecialchars($event_display_name) . "</td>
                <td>" . htmlspecialchars($donation['donation_date']) . "</td>
                <td>" . htmlspecialchars($donation['blood_group']) . "</td>
                <td>" . htmlspecialchars($donation['quantity_ml']) . " ml</td>
                <td>" . htmlspecialchars(ucfirst($donation['status'])) . "</td>
                <td>
                    <a href='#' class='action-button edit-button'>Edit</a>
                    <form action='donations.php' method='POST' style='display:inline-block; margin-left: 5px;'>
                        <input type='hidden' name='donation_id_to_delete' value='" . htmlspecialchars($donation['donation_id']) . "'>
                        <button type='submit' name='delete_donation' class='action-button delete-button' onclick='return confirm(\"Are you sure you want to delete this donation record?\");'>Delete</button>
                    </form>
                </td>
            </tr>
        ";
    }
} else {
    $donations_table_rows = "<tr><td colspan='8' class='no-records'>No donation records found.</td></tr>";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BloodLink - Track Donations</title>
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
            max-width: 1000px; /* Wider for forms and tables */
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

        /* Form Styling */
        .donation-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 40px;
            border: 1px solid #e0e0e0;
            padding: 25px;
            border-radius: 8px;
            background-color: #fcfcfc;
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
        .form-group input[type="date"],
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

        /* Donations Table Styling */
        .donations-table-section h3 {
            font-size: 2em;
            color: #c0392b;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .table-container {
            overflow-x: auto; /* Allows horizontal scrolling on small screens */
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.05);
        }

        .donations-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px; /* Ensure table doesn't get too narrow */
        }

        .donations-table th,
        .donations-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .donations-table th {
            background-color: #f2f2f2;
            color: #333;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.9em;
        }

        .donations-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .donations-table tbody tr:hover {
            background-color: #fdeaea; /* Light red on hover */
        }

        .no-records {
            text-align: center;
            color: #555;
            padding: 20px;
            font-style: italic;
        }

        .action-button {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            font-weight: 600;
            transition: background-color 0.3s ease;
            text-decoration: none; /* For anchor tags */
            display: inline-block;
        }

        .edit-button {
            background-color: #3498db; /* Blue */
            color: #fff;
        }

        .edit-button:hover {
            background-color: #2980b9;
        }

        .delete-button {
            background-color: #e74c3c; /* Red */
            color: #fff;
        }

        .delete-button:hover {
            background-color: #c0392b;
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
                gap: 0;
            }

            .form-row .form-group {
                min-width: unset;
            }

            .submit-button {
                font-size: 1.1em;
                padding: 12px;
            }

            .donations-table th,
            .donations-table td {
                padding: 10px;
                font-size: 0.85em;
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
            .action-button {
                padding: 6px 10px;
                font-size: 0.8em;
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
        <h2>Track and Manage Donations</h2>

        <?php echo $message; // Display success or error messages ?>

        <!-- Add New Donation Form -->
        <h3>Add New Donation Record</h3>
        <form action="donations.php" method="POST" class="donation-form">
            <div class="form-group">
                <label for="donor_id">Select Donor:</label>
                <select id="donor_id" name="donor_id" required>
                    <?php echo $donor_options; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="event_id">Select Event (Optional):</label>
                <select id="event_id" name="event_id">
                    <?php echo $event_options; ?>
                </select>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="donation_date">Donation Date:</label>
                    <input type="date" id="donation_date" name="donation_date" value="<?php echo htmlspecialchars($_POST['donation_date'] ?? date('Y-m-d')); ?>" required>
                </div>
                <div class="form-group">
                    <label for="blood_group">Blood Group:</label>
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
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="quantity_ml">Quantity (ml):</label>
                    <input type="text" id="quantity_ml" name="quantity_ml" placeholder="e.g., 450" value="<?php echo htmlspecialchars($_POST['quantity_ml'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="status">Status:</label>
                    <select id="status" name="status" required>
                        <option value="">Select Status</option>
                        <option value="completed" <?php echo (isset($_POST['status']) && $_POST['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                        <option value="pending" <?php echo (isset($_POST['status']) && $_POST['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="cancelled" <?php echo (isset($_POST['status']) && $_POST['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="notes">Notes (Optional):</label>
                <textarea id="notes" name="notes" rows="3" placeholder="Any additional notes about this donation"><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
            </div>
            <button type="submit" name="add_donation" class="submit-button">Add Donation Record</button>
        </form>

        <!-- Existing Donations List -->
        <div class="donations-table-section">
            <h3>All Donation Records</h3>
            <div class="table-container">
                <table class="donations-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Donor</th>
                            <th>Event</th>
                            <th>Date</th>
                            <th>Blood Group</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php echo $donations_table_rows; ?>
                    </tbody>
                </table>
            </div>
        </div>
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
        // Basic client-side validation for the Add Donation form
        document.addEventListener('DOMContentLoaded', function() {
            const addDonationForm = document.querySelector('.donation-form');

            if (addDonationForm) {
                addDonationForm.addEventListener('submit', function(event) {
                    let isValid = true;

                    // Helper function for validation
                    function validateField(inputElement, validationFn) {
                        if (validationFn(inputElement.value.trim())) {
                            // In a real app, you'd show an error next to the field.
                        } else {
                            isValid = false;
                        }
                    }

                    // Validation functions
                    const isNotEmpty = value => value !== '';
                    const isNumeric = value => !isNaN(parseInt(value)) && isFinite(value);

                    validateField(document.getElementById('donor_id'), isNotEmpty);
                    validateField(document.getElementById('donation_date'), isNotEmpty);
                    validateField(document.getElementById('blood_group'), isNotEmpty);
                    validateField(document.getElementById('quantity_ml'), isNumeric);
                    validateField(document.getElementById('status'), isNotEmpty);

                    if (!isValid) {
                        event.preventDefault(); // Prevent form submission if validation fails
                        alert('Please fill in all required fields correctly (Donor, Date, Blood Group, Quantity, Status).'); // Simple alert
                    }
                });
            }
        });
    </script>
</body>
</html>
