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

$message = ""; // To store success or error messages for medical record operations

// --- Medical Record Management Logic (Simulated) ---

// Handle Add Medical Record
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_medical_record'])) {
    $donor_id = $_POST['donor_id'] ?? '';
    $record_date = $_POST['record_date'] ?? '';
    $hemoglobin_level = $_POST['hemoglobin_level'] ?? '';
    $blood_pressure = $_POST['blood_pressure'] ?? '';
    $diseases_history = $_POST['diseases_history'] ?? '';
    $medications = $_POST['medications'] ?? '';
    $eligibility_status = $_POST['eligibility_status'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $recorded_by = $_SESSION['user_id']; // The admin adding the record

    // Basic server-side validation
    if (empty($donor_id) || empty($record_date) || empty($hemoglobin_level) || empty($blood_pressure) || empty($eligibility_status)) {
        $message = "<p class='error-message'>Please fill in all required fields for the medical record.</p>";
    } else {
        // --- SIMULATED DATABASE INSERT ---
        // In a real scenario, you'd use prepared statements to insert into 'medical_records' table.
        // Example: INSERT INTO medical_records (donor_id, record_date, hemoglobin_level, blood_pressure, diseases_history, medications, eligibility_status, notes, recorded_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);
        $simulated_success = true; // Assume success
        if ($simulated_success) {
            $message = "<p class='success-message'>Medical record for Donor ID <strong>" . htmlspecialchars($donor_id) . "</strong> added successfully!</p>";
            // Clear form fields after successful submission (optional)
            $_POST = array();
        } else {
            $message = "<p class='error-message'>Failed to add medical record. Please try again.</p>";
        }
        // --- END SIMULATED DATABASE INSERT ---
    }
}

// Fetch Existing Donors for Dropdown (Simulated)
// In a real application, this would fetch from the 'donors' table.
$simulated_donors_for_dropdown = [
    ['donor_id' => 2, 'full_name' => 'John Donor'],
    ['donor_id' => 4, 'full_name' => 'Peter D'],
];

$donor_options = "<option value=''>Select Donor</option>";
foreach ($simulated_donors_for_dropdown as $donor) {
    $donor_options .= "<option value='" . htmlspecialchars($donor['donor_id']) . "'>" . htmlspecialchars($donor['full_name']) . " (ID: " . htmlspecialchars($donor['donor_id']) . ")</option>";
}


// Fetch Existing Medical Records (Simulated)
// In a real application, this would join 'medical_records' with 'donors' to get donor names.
$medical_records = [
    ['record_id' => 101, 'donor_id' => 2, 'donor_name' => 'John Donor', 'record_date' => '2025-01-20', 'hemoglobin_level' => 14.5, 'blood_pressure' => '120/80', 'diseases_history' => 'None', 'medications' => 'None', 'eligibility_status' => 'Eligible', 'notes' => 'Healthy donor.'],
    ['record_id' => 102, 'donor_id' => 4, 'donor_name' => 'Peter D', 'record_date' => '2025-02-25', 'hemoglobin_level' => 13.8, 'blood_pressure' => '130/85', 'diseases_history' => 'Seasonal allergies', 'medications' => 'Antihistamines', 'eligibility_status' => 'Eligible', 'notes' => 'Eligible after medication check.'],
];

$medical_records_table_rows = "";
if (!empty($medical_records)) {
    foreach ($medical_records as $record) {
        $medical_records_table_rows .= "
            <tr>
                <td>" . htmlspecialchars($record['record_id']) . "</td>
                <td>" . htmlspecialchars($record['donor_name']) . " (ID: " . htmlspecialchars($record['donor_id']) . ")</td>
                <td>" . htmlspecialchars($record['record_date']) . "</td>
                <td>" . htmlspecialchars($record['hemoglobin_level']) . "</td>
                <td>" . htmlspecialchars($record['blood_pressure']) . "</td>
                <td>" . htmlspecialchars($record['eligibility_status']) . "</td>
                <td>
                    <a href='#' class='action-button edit-button'>Edit</a>
                    <form action='medical_records.php' method='POST' style='display:inline-block; margin-left: 5px;'>
                        <input type='hidden' name='record_id_to_delete' value='" . htmlspecialchars($record['record_id']) . "'>
                        <button type='submit' name='delete_record' class='action-button delete-button' onclick='return confirm(\"Are you sure you want to delete this medical record?\");'>Delete</button>
                    </form>
                </td>
            </tr>
        ";
    }
} else {
    $medical_records_table_rows = "<tr><td colspan='7' class='no-records'>No medical records found.</td></tr>";
}

// Handle Delete Medical Record (Simulated)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_record'])) {
    $record_id = $_POST['record_id_to_delete'] ?? '';

    if (empty($record_id)) {
        $message = "<p class='error-message'>No record ID provided for deletion.</p>";
    } else {
        // --- SIMULATED DATABASE DELETE ---
        // In a real scenario, you'd execute a DELETE query from the 'medical_records' table.
        // Example: DELETE FROM medical_records WHERE record_id=?;
        $simulated_success = true; // Assume success
        if ($simulated_success) {
            $message = "<p class='success-message'>Medical record ID <strong>" . htmlspecialchars($record_id) . "</strong> deleted successfully!</p>";
        } else {
            $message = "<p class='error-message'>Failed to delete medical record. Please try again.</p>";
        }
        // --- END SIMULATED DATABASE DELETE ---
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BloodLink - Manage Medical Records</title>
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
        .medical-record-form {
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

        /* Medical Records Table Styling */
        .records-table-section h3 {
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

        .medical-records-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px; /* Ensure table doesn't get too narrow */
        }

        .medical-records-table th,
        .medical-records-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .medical-records-table th {
            background-color: #f2f2f2;
            color: #333;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.9em;
        }

        .medical-records-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .medical-records-table tbody tr:hover {
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

            .medical-records-table th,
            .medical-records-table td {
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
        <h2>Manage Donor Medical Records</h2>

        <?php echo $message; // Display success or error messages ?>

        <!-- Add New Medical Record Form -->
        <h3>Add New Medical Record</h3>
        <form action="medical_records.php" method="POST" class="medical-record-form">
            <div class="form-group">
                <label for="donor_id">Select Donor:</label>
                <select id="donor_id" name="donor_id" required>
                    <?php echo $donor_options; ?>
                </select>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="record_date">Record Date:</label>
                    <input type="date" id="record_date" name="record_date" value="<?php echo htmlspecialchars($_POST['record_date'] ?? date('Y-m-d')); ?>" required>
                </div>
                <div class="form-group">
                    <label for="hemoglobin_level">Hemoglobin Level (g/dL):</label>
                    <input type="text" id="hemoglobin_level" name="hemoglobin_level" placeholder="e.g., 14.2" value="<?php echo htmlspecialchars($_POST['hemoglobin_level'] ?? ''); ?>" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="blood_pressure">Blood Pressure (e.g., 120/80):</label>
                    <input type="text" id="blood_pressure" name="blood_pressure" placeholder="e.g., 120/80" value="<?php echo htmlspecialchars($_POST['blood_pressure'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="eligibility_status">Eligibility Status:</label>
                    <select id="eligibility_status" name="eligibility_status" required>
                        <option value="">Select Status</option>
                        <option value="Eligible" <?php echo (isset($_POST['eligibility_status']) && $_POST['eligibility_status'] == 'Eligible') ? 'selected' : ''; ?>>Eligible</option>
                        <option value="Temporarily Deferred" <?php echo (isset($_POST['eligibility_status']) && $_POST['eligibility_status'] == 'Temporarily Deferred') ? 'selected' : ''; ?>>Temporarily Deferred</option>
                        <option value="Permanently Deferred" <?php echo (isset($_POST['eligibility_status']) && $_POST['eligibility_status'] == 'Permanently Deferred') ? 'selected' : ''; ?>>Permanently Deferred</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="diseases_history">Diseases History (Optional):</label>
                <textarea id="diseases_history" name="diseases_history" rows="3" placeholder="Any relevant past or current diseases"><?php echo htmlspecialchars($_POST['diseases_history'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label for="medications">Current Medications (Optional):</label>
                <textarea id="medications" name="medications" rows="3" placeholder="List any medications currently being taken"><?php echo htmlspecialchars($_POST['medications'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label for="notes">Additional Notes (Optional):</label>
                <textarea id="notes" name="notes" rows="3" placeholder="Any additional notes about the donor's medical status"><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
            </div>
            <button type="submit" name="add_medical_record" class="submit-button">Add Medical Record</button>
        </form>

        <!-- Existing Medical Records List -->
        <div class="records-table-section">
            <h3>Existing Medical Records</h3>
            <div class="table-container">
                <table class="medical-records-table">
                    <thead>
                        <tr>
                            <th>Record ID</th>
                            <th>Donor</th>
                            <th>Date</th>
                            <th>Hemoglobin</th>
                            <th>Blood Pressure</th>
                            <th>Eligibility</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php echo $medical_records_table_rows; ?>
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
        // Basic client-side validation for the Add Medical Record form
        document.addEventListener('DOMContentLoaded', function() {
            const addMedicalRecordForm = document.querySelector('.medical-record-form');

            if (addMedicalRecordForm) {
                addMedicalRecordForm.addEventListener('submit', function(event) {
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
                    const isNumeric = value => !isNaN(parseFloat(value)) && isFinite(value);
                    const isValidBloodPressure = value => /^\d{2,3}\/\d{2,3}$/.test(value);

                    validateField(document.getElementById('donor_id'), isNotEmpty);
                    validateField(document.getElementById('record_date'), isNotEmpty);
                    validateField(document.getElementById('hemoglobin_level'), isNumeric);
                    validateField(document.getElementById('blood_pressure'), isValidBloodPressure);
                    validateField(document.getElementById('eligibility_status'), isNotEmpty);

                    if (!isValid) {
                        event.preventDefault(); // Prevent form submission if validation fails
                        alert('Please fill in all required fields correctly (Donor, Record Date, Hemoglobin, Blood Pressure, Eligibility Status).'); // Simple alert
                    }
                });
            }
        });
    </script>
</body>
</html>
