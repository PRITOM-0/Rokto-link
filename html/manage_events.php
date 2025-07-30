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

$message = ""; // To store success or error messages for event operations

// --- Event Management Logic (Simulated) ---

// Handle Add Event
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_event'])) {
    $event_name = $_POST['event_name'] ?? '';
    $event_date = $_POST['event_date'] ?? '';
    $location = $_POST['location'] ?? '';
    $description = $_POST['description'] ?? '';
    $organizer_id = $_SESSION['user_id']; // The admin creating the event

    if (empty($event_name) || empty($event_date) || empty($location)) {
        $message = "<p class='error-message'>Please fill in all required fields for the event.</p>";
    } else {
        // --- SIMULATED DATABASE INSERT ---
        // In a real scenario, you'd execute an INSERT query into the 'events' table.
        // Example: INSERT INTO events (event_name, event_date, location, description, organizer_id, created_at) VALUES (?, ?, ?, ?, ?, NOW());
        $simulated_success = true; // Assume success
        if ($simulated_success) {
            $message = "<p class='success-message'>Event '<strong>" . htmlspecialchars($event_name) . "</strong>' added successfully!</p>";
            // Clear form fields after successful submission (optional)
            $_POST = array();
        } else {
            $message = "<p class='error-message'>Failed to add event. Please try again.</p>";
        }
        // --- END SIMULATED DATABASE INSERT ---
    }
}

// Handle Update Event (Placeholder - would typically involve fetching event details first)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_event'])) {
    $event_id = $_POST['event_id'] ?? '';
    $event_name = $_POST['event_name'] ?? '';
    $event_date = $_POST['event_date'] ?? '';
    $location = $_POST['location'] ?? '';
    $description = $_POST['description'] ?? '';

    if (empty($event_id) || empty($event_name) || empty($event_date) || empty($location)) {
        $message = "<p class='error-message'>Please fill in all required fields for updating the event.</p>";
    } else {
        // --- SIMULATED DATABASE UPDATE ---
        // In a real scenario, you'd execute an UPDATE query on the 'events' table.
        // Example: UPDATE events SET event_name=?, event_date=?, location=?, description=? WHERE event_id=?;
        $simulated_success = true; // Assume success
        if ($simulated_success) {
            $message = "<p class='success-message'>Event ID <strong>" . htmlspecialchars($event_id) . "</strong> updated successfully!</p>";
        } else {
            $message = "<p class='error-message'>Failed to update event. Please try again.</p>";
        }
        // --- END SIMULATED DATABASE UPDATE ---
    }
}

// Handle Delete Event
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_event'])) {
    $event_id = $_POST['event_id_to_delete'] ?? '';

    if (empty($event_id)) {
        $message = "<p class='error-message'>No event ID provided for deletion.</p>";
    } else {
        // --- SIMULATED DATABASE DELETE ---
        // In a real scenario, you'd execute a DELETE query from the 'events' table.
        // Example: DELETE FROM events WHERE event_id=?;
        $simulated_success = true; // Assume success
        if ($simulated_success) {
            $message = "<p class='success-message'>Event ID <strong>" . htmlspecialchars($event_id) . "</strong> deleted successfully!</p>";
        } else {
            $message = "<p class='error-message'>Failed to delete event. Please try again.</p>";
        }
        // --- END SIMULATED DATABASE DELETE ---
    }
}

// Fetch Existing Events (Simulated)
$events = [
    ['event_id' => 1, 'event_name' => 'City Blood Drive 2025', 'event_date' => '2025-08-15', 'location' => 'Community Hall, Downtown', 'description' => 'Annual blood drive open to all eligible donors.', 'organizer_id' => 1],
    ['event_id' => 2, 'event_name' => 'University Health Day', 'event_date' => '2025-09-20', 'location' => 'University Campus, Main Auditorium', 'description' => 'Special event for students and faculty to donate.', 'organizer_id' => 1],
    ['event_id' => 3, 'event_name' => 'Hospital Emergency Drive', 'event_date' => '2025-07-30', 'location' => 'St. Mary\'s Hospital, Blood Bank', 'description' => 'Urgent call for O- blood donors.', 'organizer_id' => 2],
];

$events_table_rows = "";
if (!empty($events)) {
    foreach ($events as $event) {
        $events_table_rows .= "
            <tr>
                <td>" . htmlspecialchars($event['event_id']) . "</td>
                <td>" . htmlspecialchars($event['event_name']) . "</td>
                <td>" . htmlspecialchars($event['event_date']) . "</td>
                <td>" . htmlspecialchars($event['location']) . "</td>
                <td>" . htmlspecialchars($event['description']) . "</td>
                <td>
                    <a href='#' class='action-button edit-button'>Edit</a>
                    <form action='manage_events.php' method='POST' style='display:inline-block; margin-left: 5px;'>
                        <input type='hidden' name='event_id_to_delete' value='" . htmlspecialchars($event['event_id']) . "'>
                        <button type='submit' name='delete_event' class='action-button delete-button' onclick='return confirm(\"Are you sure you want to delete this event?\");'>Delete</button>
                    </form>
                </td>
            </tr>
        ";
    }
} else {
    $events_table_rows = "<tr><td colspan='6' class='no-records'>No events found.</td></tr>";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BloodLink - Manage Events</title>
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
            max-width: 1000px; /* Wider for tables */
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
        .event-form {
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

        /* Events Table Styling */
        .events-table-section h3 {
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

        .events-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px; /* Ensure table doesn't get too narrow */
        }

        .events-table th,
        .events-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .events-table th {
            background-color: #f2f2f2;
            color: #333;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.9em;
        }

        .events-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .events-table tbody tr:hover {
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

            .events-table th,
            .events-table td {
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
        <h2>Manage Donation Events</h2>

        <?php echo $message; // Display success or error messages ?>

        <!-- Add New Event Form -->
        <h3>Add New Event</h3>
        <form action="manage_events.php" method="POST" class="event-form">
            <div class="form-group">
                <label for="event_name">Event Name:</label>
                <input type="text" id="event_name" name="event_name" placeholder="e.g., Annual City Blood Drive" value="<?php echo htmlspecialchars($_POST['event_name'] ?? ''); ?>" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="event_date">Event Date:</label>
                    <input type="date" id="event_date" name="event_date" value="<?php echo htmlspecialchars($_POST['event_date'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="location">Location:</label>
                    <input type="text" id="location" name="location" placeholder="e.g., Community Hall, Main Street" value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label for="description">Description (Optional):</label>
                <textarea id="description" name="description" rows="4" placeholder="Brief description of the event"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            </div>
            <button type="submit" name="add_event" class="submit-button">Add Event</button>
        </form>

        <!-- Existing Events List -->
        <div class="events-table-section">
            <h3>Existing Events</h3>
            <div class="table-container">
                <table class="events-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Event Name</th>
                            <th>Date</th>
                            <th>Location</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php echo $events_table_rows; ?>
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

    <!-- Client-side validation for forms (can be integrated from js/validation.js) -->
    <script src="js/validation.js"></script>
    <script>
        // Basic client-side validation for the Add Event form
        document.addEventListener('DOMContentLoaded', function() {
            const addEventForm = document.querySelector('.event-form'); // Assuming only one form for now

            if (addEventForm) {
                addEventForm.addEventListener('submit', function(event) {
                    let isValid = true;

                    // Helper function for validation
                    function validateField(inputElement, validationFn) {
                        if (validationFn(inputElement.value.trim())) {
                            // No error message display for simplicity in this file,
                            // but in a real app, you'd show an error next to the field.
                        } else {
                            isValid = false;
                        }
                    }

                    // Validation functions
                    const isNotEmpty = value => value !== '';

                    validateField(document.getElementById('event_name'), isNotEmpty);
                    validateField(document.getElementById('event_date'), isNotEmpty);
                    validateField(document.getElementById('location'), isNotEmpty);

                    if (!isValid) {
                        event.preventDefault(); // Prevent form submission if validation fails
                        alert('Please fill in all required fields for the event.'); // Simple alert for missing fields
                    }
                });
            }
        });
    </script>
</body>
</html>
