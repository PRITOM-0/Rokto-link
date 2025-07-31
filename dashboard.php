<?php
require_once 'config.php'; // Include database connection

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';
$error = '';

// --- Helper Functions for Database Operations ---

/**
 * Fetches all records from a given table.
 * @param mysqli $conn The MySQLi database connection object.
 * @param string $tableName The name of the table.
 * @param string $orderByColumn The column to order by.
 * @return array An array of associative arrays, each representing a row.
 */
function getAllData($conn, $tableName, $orderByColumn = 'id') {
    try {
        // Basic ordering, adjust as needed per table
        $orderClause = '';
        if ($tableName === 'donors') $orderByColumn = 'created_at';
        if ($tableName === 'recipients') $orderByColumn = 'created_at';
        if ($tableName === 'blood_requests') $orderByColumn = 'request_date';
        if ($tableName === 'donation_history') $orderByColumn = 'donation_date';
        if ($tableName === 'blood_inventory') $orderByColumn = 'blood_group';
        if ($tableName === 'pending_requests') $orderByColumn = 'submitted_at';

        $result = $conn->query("SELECT * FROM $tableName ORDER BY $orderByColumn DESC");
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    } catch (Exception $e) {
        // Error handling for fetching data
        global $error;
        $error .= "Error fetching data from $tableName: " . $e->getMessage() . "<br>";
        return [];
    }
}

/**
 * Inserts a new record into a table.
 * @param mysqli $conn The MySQLi database connection object.
 * @param string $tableName The name of the table.
 * @param array $data An associative array of column_name => value.
 * @return bool True on success, false on failure.
 */
function insertData($conn, $tableName, $data) {
    global $message, $error;

    // Filter out empty values (e.g., from empty text inputs)
    $filtered_data = array_filter($data, function($value) {
        return $value !== '';
    });

    if (empty($filtered_data)) {
        $error .= "No data provided for insertion into $tableName.<br>";
        return false;
    }

    $columns = implode(', ', array_keys($filtered_data));
    $placeholders = implode(', ', array_fill(0, count($filtered_data), '?'));
    $sql = "INSERT INTO $tableName ($columns) VALUES ($placeholders)";

    try {
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }

        $types = '';
        $values = [];
        foreach ($filtered_data as $key => $value) {
            // Handle boolean for is_available (checkbox)
            if ($key === 'is_available') {
                $value = ($value === 'on' || $value === true) ? 1 : 0;
            }

            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
            $values[] = $value;
        }

        $stmt->bind_param($types, ...$values);
        if ($stmt->execute()) {
            $message .= "Record added successfully to $tableName.<br>";
            return true;
        } else {
            $error .= "Error adding record to $tableName: " . $stmt->error . "<br>";
            return false;
        }
    } catch (Exception $e) {
        $error .= "Error inserting into $tableName: " . $e->getMessage() . "<br>";
        return false;
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
    }
}

/**
 * Updates an existing record in a table.
 * @param mysqli $conn The MySQLi database connection object.
 * @param string $tableName The name of the table.
 * @param int $id The ID of the record to update.
 * @param array $data An associative array of column_name => value.
 * @param string $idColumn The name of the ID column (default 'id').
 * @return bool True on success, false on failure.
 */
function updateData($conn, $tableName, $id, $data, $idColumn = 'id') {
    global $message, $error;

    // Filter out empty values (e.g., from empty text inputs)
    $filtered_data = array_filter($data, function($value) {
        return $value !== '';
    });

    if (empty($filtered_data)) {
        $error .= "No data provided for update in $tableName (ID: $id).<br>";
        return false;
    }

    $setClauses = [];
    foreach ($filtered_data as $key => $value) {
        $setClauses[] = "$key = ?";
    }
    $setClause = implode(', ', $setClauses);
    $sql = "UPDATE $tableName SET $setClause WHERE $idColumn = ?";

    try {
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }

        $types = '';
        $values = [];
        foreach ($filtered_data as $key => $value) {
            // Handle boolean for is_available (checkbox)
            if ($key === 'is_available') {
                $value = ($value === 'on' || $value === true) ? 1 : 0;
            }

            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
            $values[] = $value;
        }
        $types .= 's'; // For the ID parameter (assuming ID can be string for blood_group)
        $values[] = $id;

        $stmt->bind_param($types, ...$values);
        if ($stmt->execute()) {
            $message .= "Record updated successfully in $tableName (ID: $id).<br>";
            return true;
        } else {
            $error .= "Error updating $tableName (ID: $id): " . $stmt->error . "<br>";
            return false;
        }
    } catch (Exception $e) {
        $error .= "Error updating $tableName (ID: $id): " . $e->getMessage() . "<br>";
        return false;
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
    }
}

/**
 * Deletes a record from a table.
 * @param mysqli $conn The MySQLi database connection object.
 * @param string $tableName The name of the table.
 * @param int $id The ID of the record to delete.
 * @param string $idColumn The name of the ID column (default 'id').
 * @return bool True on success, false on failure.
 */
function deleteData($conn, $tableName, $id, $idColumn = 'id') {
    global $message, $error;
    $sql = "DELETE FROM $tableName WHERE $idColumn = ?";
    try {
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }
        // Bind based on the ID column type
        $bind_type = ($idColumn === 'id') ? 'i' : 's';
        $stmt->bind_param($bind_type, $id);
        if ($stmt->execute()) {
            $message .= "Record deleted successfully from $tableName (ID: $id).<br>";
            return true;
        } else {
            $error .= "Error deleting from $tableName (ID: $id): " . $stmt->error . "<br>";
            return false;
        }
    } catch (Exception $e) {
        $error .= "Error deleting from $tableName (ID: $id): " . $e->getMessage() . "<br>";
        return false;
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
    }
}

/**
 * Function to fetch foreign key options for dropdowns
 */
function getForeignKeyOptions($conn, $fkTable, $displayColumn) {
    global $error;
    try {
        $result = $conn->query("SELECT id, $displayColumn FROM $fkTable ORDER BY $displayColumn");
        if ($result) {
            $options = [];
            while ($row = $result->fetch_assoc()) {
                $options[$row['id']] = $row[$displayColumn];
            }
            return $options;
        }
        return [];
    } catch (Exception $e) {
        $error .= "Error fetching foreign key options from $fkTable: " . $e->getMessage() . "<br>";
        return [];
    }
}


// --- Form Handling Logic (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $tableName = $_POST['table_name'] ?? '';
    $id = $_POST['id'] ?? null; // ID for update/delete operations
    $data = $_POST;

    // Remove internal form fields from data array before passing to insert/update
    unset($data['action']);
    unset($data['table_name']);
    unset($data['id']);

    if ($action === 'add') {
        insertData($conn, $tableName, $data);
    } elseif ($action === 'update') {
        // Special handling for blood_inventory update, as its ID is blood_group
        if ($tableName === 'blood_inventory') {
            $blood_group_id = $_POST['id']; // ID comes from the hidden 'id' field, which is blood_group for inventory
            unset($data['blood_group']); // blood_group is the ID, not a field to update
            updateData($conn, $tableName, $blood_group_id, $data, 'blood_group');
        } else {
            updateData($conn, $tableName, $id, $data);
        }
    } elseif ($action === 'delete') {
        // Special handling for blood_inventory delete, as its ID is blood_group
        if ($tableName === 'blood_inventory') {
            $blood_group_id = $_POST['id'];
            deleteData($conn, $tableName, $blood_group_id, 'blood_group');
        } else {
            deleteData($conn, $tableName, $id);
        }
    }

    // Redirect to prevent form resubmission on refresh
    header("Location: dashboard.php");
    exit();
}

// --- Dynamic State for Displaying Add Forms or Editing Rows ---
$display_add_form = $_GET['add_form'] ?? null; // 'donor', 'recipient', 'donation', 'blood_request'
$current_edit_table = $_GET['edit_table'] ?? null;
$current_edit_id = $_GET['edit_id'] ?? null;
$edit_data = [];

// If an edit request is active, fetch the data for the specific row
if ($current_edit_table && $current_edit_id) {
    $select_sql = "";
    $id_column = 'id';
    switch ($current_edit_table) {
        case 'donors':
        case 'recipients':
        case 'blood_requests':
        case 'donation_history':
            $select_sql = "SELECT * FROM $current_edit_table WHERE id = ?";
            break;
        case 'blood_inventory':
            $select_sql = "SELECT * FROM blood_inventory WHERE blood_group = ?";
            $id_column = 'blood_group'; // Special ID for inventory
            break;
    }

    if (!empty($select_sql)) {
        $stmt = $conn->prepare($select_sql);
        if ($stmt) {
            // Bind based on the ID column type
            $bind_type = ($id_column === 'id') ? 'i' : 's';
            $stmt->bind_param($bind_type, $current_edit_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $edit_data = $result->fetch_assoc();
            } else {
                $error .= "Record not found for editing in $current_edit_table (ID: $current_edit_id).<br>";
                // Reset edit state if record not found
                $current_edit_table = null;
                $current_edit_id = null;
            }
            $stmt->close();
        } else {
            $error .= "Error preparing edit fetch statement for $current_edit_table: " . $conn->error . "<br>";
            $current_edit_table = null;
            $current_edit_id = null;
        }
    }
}

// --- Data Fetching for Display ---
$donors = getAllData($conn, 'donors');
$recipients = getAllData($conn, 'recipients');
$blood_requests = getAllData($conn, 'blood_requests');
$donation_history = getAllData($conn, 'donation_history');
$blood_inventory = getAllData($conn, 'blood_inventory');
$pending_requests = getAllData($conn, 'pending_requests'); // Assuming you want to keep this

// Fetch foreign key options for dropdowns
$all_donors_options = getForeignKeyOptions($conn, 'donors', 'name');
$all_recipients_options = getForeignKeyOptions($conn, 'recipients', 'name');

// Define table configurations (columns, ENUM options, foreign key lookups)
$tableConfigs = [
    'donors' => [
        'display_name' => 'Donors',
        'columns' => ['id', 'name', 'email', 'phone', 'blood_group', 'last_donation_date', 'address', 'city', 'state', 'zip_code', 'is_available', 'created_at'],
        'editable_columns' => ['name', 'email', 'phone', 'blood_group', 'last_donation_date', 'address', 'city', 'state', 'zip_code', 'is_available'],
        'enum_options' => [
            'blood_group' => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']
        ],
        'input_types' => [
            'last_donation_date' => 'date',
            'is_available' => 'checkbox',
            'address' => 'textarea'
        ],
        'read_only_columns' => ['id', 'created_at'] // Columns that are displayed but not editable
    ],
    'recipients' => [
        'display_name' => 'Recipients',
        'columns' => ['id', 'name', 'email', 'phone', 'blood_group', 'reason', 'hospital_name', 'city', 'state', 'created_at'],
        'editable_columns' => ['name', 'email', 'phone', 'blood_group', 'reason', 'hospital_name', 'city', 'state'],
        'enum_options' => [
            'blood_group' => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']
        ],
        'input_types' => [
            'reason' => 'textarea'
        ],
        'read_only_columns' => ['id', 'created_at']
    ],
    'blood_requests' => [
        'display_name' => 'Blood Requests',
        'columns' => ['id', 'recipient_id', 'blood_group', 'units_needed', 'request_date', 'status'],
        'editable_columns' => ['recipient_id', 'blood_group', 'units_needed', 'status'],
        'enum_options' => [
            'blood_group' => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'],
            'status' => ['pending', 'fulfilled', 'urgent', 'cancelled']
        ],
        'foreign_keys' => [
            'recipient_id' => ['table' => 'recipients', 'display_column' => 'name', 'options' => $all_recipients_options]
        ],
        'input_types' => [
            'request_date' => 'text' // This will be auto-generated by DB, so display only
        ],
        'read_only_columns' => ['id', 'request_date']
    ],
    'blood_inventory' => [
        'display_name' => 'Blood Inventory',
        'columns' => ['blood_group', 'available_units', 'last_updated'], // ID is blood_group
        'editable_columns' => ['available_units'], // blood_group is the primary key, usually not edited
        'enum_options' => [
            'blood_group' => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']
        ],
        'input_types' => [],
        'read_only_columns' => ['blood_group', 'last_updated'] // blood_group is key, last_updated is auto
    ],
    'donation_history' => [
        'display_name' => 'Donation History',
        'columns' => ['id', 'donor_id', 'donation_date', 'blood_group', 'units_donated', 'notes', 'created_at'],
        'editable_columns' => ['donor_id', 'donation_date', 'blood_group', 'units_donated', 'notes'],
        'enum_options' => [
            'blood_group' => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']
        ],
        'foreign_keys' => [
            'donor_id' => ['table' => 'donors', 'display_column' => 'name', 'options' => $all_donors_options]
        ],
        'input_types' => [
            'donation_date' => 'date',
            'notes' => 'textarea'
        ],
        'read_only_columns' => ['id', 'created_at']
    ]
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Donation Management Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container header-content">
            <h1 class="site-title">Dashboard</h1>
            <nav>
                <ul class="nav-list">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="search.php">Search Data</a></li>
                    <li><a href="logout.php" class="dashboard-button">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container main-content">
        <?php if (!empty($message)): ?>
            <div class="message success-message">
                <span><?php echo $message; ?></span>
            </div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="message error-message">
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <h2 class="welcome-title">Admin Dashboard</h2>

        <!-- Action Buttons for Adding New Records -->
        <section class="action-buttons-section data-section">
            <h3 class="section-heading">Quick Actions</h3>
            <div class="button-group">
                <a href="dashboard.php?add_form=donor" class="btn btn-green">Add New Donor</a>
                <a href="dashboard.php?add_form=recipient" class="btn btn-green">Add New Recipient</a>
                <a href="dashboard.php?add_form=donation" class="btn btn-green">Add New Donation</a>
                <a href="dashboard.php?add_form=blood_request" class="btn btn-green">Add New Blood Request</a>
                <?php if ($display_add_form): ?>
                    <a href="dashboard.php" class="btn btn-secondary">Hide Add Forms</a>
                <?php endif; ?>
            </div>
        </section>

        <!-- Conditional Add Forms -->
        <?php if ($display_add_form === 'donor'): ?>
            <section class="add-form-section data-section" id="addDonorForm">
                <h3 class="section-heading">Add New Donor</h3>
                <form action="" method="POST" class="form-layout">
                    <input type="hidden" name="table_name" value="donors">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group"><label for="donor_name">Name:</label><input type="text" id="donor_name" name="name" required></div>
                    <div class="form-group"><label for="donor_email">Email:</label><input type="email" id="donor_email" name="email"></div>
                    <div class="form-group"><label for="donor_phone">Phone:</label><input type="text" id="donor_phone" name="phone"></div>
                    <div class="form-group">
                        <label for="donor_blood_group">Blood Group:</label>
                        <select id="donor_blood_group" name="blood_group" required>
                            <option value="">Select Blood Group</option>
                            <?php foreach ($tableConfigs['donors']['enum_options']['blood_group'] as $option): ?>
                                <option value="<?php echo htmlspecialchars($option); ?>"><?php echo htmlspecialchars($option); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label for="donor_last_donation_date">Last Donation Date:</label><input type="date" id="donor_last_donation_date" name="last_donation_date"></div>
                    <div class="form-group"><label for="donor_address">Address:</label><textarea id="donor_address" name="address" rows="2"></textarea></div>
                    <div class="form-group"><label for="donor_city">City:</label><input type="text" id="donor_city" name="city"></div>
                    <div class="form-group"><label for="donor_state">State:</label><input type="text" id="donor_state" name="state"></div>
                    <div class="form-group"><label for="donor_zip_code">Zip Code:</label><input type="text" id="donor_zip_code" name="zip_code"></div>
                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="donor_is_available" name="is_available" checked>
                        <label for="donor_is_available">Is Available for Donation?</label>
                    </div>
                    <div class="form-group full-width">
                        <button type="submit" class="btn btn-primary">Add Donor</button>
                    </div>
                </form>
            </section>
        <?php endif; ?>

        <?php if ($display_add_form === 'recipient'): ?>
            <section class="add-form-section data-section" id="addRecipientForm">
                <h3 class="section-heading">Add New Recipient</h3>
                <form action="" method="POST" class="form-layout">
                    <input type="hidden" name="table_name" value="recipients">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group"><label for="recipient_name">Name:</label><input type="text" id="recipient_name" name="name" required></div>
                    <div class="form-group"><label for="recipient_email">Email:</label><input type="email" id="recipient_email" name="email"></div>
                    <div class="form-group"><label for="recipient_phone">Phone:</label><input type="text" id="recipient_phone" name="phone"></div>
                    <div class="form-group">
                        <label for="recipient_blood_group">Blood Group:</label>
                        <select id="recipient_blood_group" name="blood_group" required>
                            <option value="">Select Blood Group</option>
                            <?php foreach ($tableConfigs['recipients']['enum_options']['blood_group'] as $option): ?>
                                <option value="<?php echo htmlspecialchars($option); ?>"><?php echo htmlspecialchars($option); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label for="recipient_reason">Reason for Blood:</label><textarea id="recipient_reason" name="reason" rows="2"></textarea></div>
                    <div class="form-group"><label for="recipient_hospital_name">Hospital Name:</label><input type="text" id="recipient_hospital_name" name="hospital_name"></div>
                    <div class="form-group"><label for="recipient_city">City:</label><input type="text" id="recipient_city" name="city"></div>
                    <div class="form-group"><label for="recipient_state">State:</label><input type="text" id="recipient_state" name="state"></div>
                    <div class="form-group full-width">
                        <button type="submit" class="btn btn-primary">Add Recipient</button>
                    </div>
                </form>
            </section>
        <?php endif; ?>

        <?php if ($display_add_form === 'donation'): ?>
            <section class="add-form-section data-section" id="addDonationForm">
                <h3 class="section-heading">Add New Donation Record</h3>
                <form action="" method="POST" class="form-layout">
                    <input type="hidden" name="table_name" value="donation_history">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label for="donation_donor_id">Donor:</label>
                        <select id="donation_donor_id" name="donor_id" required>
                            <option value="">Select Donor</option>
                            <?php foreach ($all_donors_options as $id => $name): ?>
                                <option value="<?php echo htmlspecialchars($id); ?>"><?php echo htmlspecialchars($name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label for="donation_date">Donation Date:</label><input type="date" id="donation_date" name="donation_date" required></div>
                    <div class="form-group">
                        <label for="donation_blood_group">Blood Group:</label>
                        <select id="donation_blood_group" name="blood_group" required>
                            <option value="">Select Blood Group</option>
                            <?php foreach ($tableConfigs['donation_history']['enum_options']['blood_group'] as $option): ?>
                                <option value="<?php echo htmlspecialchars($option); ?>"><?php echo htmlspecialchars($option); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label for="donation_units_donated">Units Donated:</label><input type="number" id="donation_units_donated" name="units_donated" required min="1" value="1"></div>
                    <div class="form-group"><label for="donation_notes">Notes:</label><textarea id="donation_notes" name="notes" rows="2"></textarea></div>
                    <div class="form-group full-width">
                        <button type="submit" class="btn btn-primary">Add Donation</button>
                    </div>
                </form>
            </section>
        <?php endif; ?>

        <?php if ($display_add_form === 'blood_request'): ?>
            <section class="add-form-section data-section" id="addBloodRequestForm">
                <h3 class="section-heading">Add New Blood Request</h3>
                <form action="" method="POST" class="form-layout">
                    <input type="hidden" name="table_name" value="blood_requests">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label for="blood_request_recipient_id">Recipient:</label>
                        <select id="blood_request_recipient_id" name="recipient_id" required>
                            <option value="">Select Recipient</option>
                            <?php foreach ($all_recipients_options as $id => $name): ?>
                                <option value="<?php echo htmlspecialchars($id); ?>"><?php echo htmlspecialchars($name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="blood_request_blood_group">Blood Group:</label>
                        <select id="blood_request_blood_group" name="blood_group" required>
                            <option value="">Select Blood Group</option>
                            <?php foreach ($tableConfigs['blood_requests']['enum_options']['blood_group'] as $option): ?>
                                <option value="<?php echo htmlspecialchars($option); ?>"><?php echo htmlspecialchars($option); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label for="blood_request_units_needed">Units Needed:</label><input type="number" id="blood_request_units_needed" name="units_needed" required min="1"></div>
                    <div class="form-group">
                        <label for="blood_request_status">Status:</label>
                        <select id="blood_request_status" name="status" required>
                            <?php foreach ($tableConfigs['blood_requests']['enum_options']['status'] as $option): ?>
                                <option value="<?php echo htmlspecialchars($option); ?>"><?php echo htmlspecialchars(ucfirst($option)); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group full-width">
                        <button type="submit" class="btn btn-primary">Add Blood Request</button>
                    </div>
                </form>
            </section>
        <?php endif; ?>



        <?php
        // --- Function to Display CRUD Tables with Inline Editing ---
        function displayCrudTable($conn, $tableName, $data, $config, $current_edit_table, $current_edit_id, $edit_data) {
            echo "<section class='data-section'>";
            echo "<h3 class='section-heading'>";
            echo htmlspecialchars($config['display_name']);
            echo "</h3>";
            echo "<div class='table-container'>";
            echo "<table class='data-table'>";
            echo "<thead><tr>";
            foreach ($config['columns'] as $col) {
                echo "<th>" . htmlspecialchars(ucwords(str_replace('_', ' ', $col))) . "</th>";
            }
            echo "<th>Actions</th>";
            echo "</tr></thead>";
            echo "<tbody>";

            foreach ($data as $row) {
                $is_editing_this_row = ($current_edit_table === $tableName && (
                    ($tableName === 'blood_inventory' && $row['blood_group'] == $current_edit_id) ||
                    ($tableName !== 'blood_inventory' && $row['id'] == $current_edit_id)
                ));

                echo "<tr" . ($is_editing_this_row ? " class='editing-row'" : "") . ">";
                echo "<form action='' method='POST'>";
                echo "<input type='hidden' name='table_name' value='" . htmlspecialchars($tableName) . "'>";
                echo "<input type='hidden' name='action' value='update'>";

                // Determine the ID for the current row for update operations
                $row_id_value = ($tableName === 'blood_inventory') ? $row['blood_group'] : $row['id'];
                echo "<input type='hidden' name='id' value='" . htmlspecialchars($row_id_value) . "'>";

                foreach ($config['columns'] as $col) {
                    echo "<td data-label='" . htmlspecialchars(ucwords(str_replace('_', ' ', $col))) . "'>";
                    $display_value = htmlspecialchars($row[$col] ?? '');

                    if ($is_editing_this_row && in_array($col, $config['editable_columns'])) {
                        $input_type = $config['input_types'][$col] ?? 'text';
                        $input_value = htmlspecialchars($edit_data[$col] ?? '');

                        if (isset($config['enum_options'][$col])) {
                            echo "<select name='" . htmlspecialchars($col) . "'>";
                            foreach ($config['enum_options'][$col] as $option) {
                                $selected = ($input_value === $option) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($option) . "' $selected>" . htmlspecialchars($option) . "</option>";
                            }
                            echo "</select>";
                        } elseif (isset($config['foreign_keys'][$col])) {
                            $fk_options = $config['foreign_keys'][$col]['options'];
                            echo "<select name='" . htmlspecialchars($col) . "'>";
                            foreach ($fk_options as $fk_id => $fk_display) {
                                $selected = ($input_value == $fk_id) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($fk_id) . "' $selected>" . htmlspecialchars($fk_display) . "</option>";
                            }
                            echo "</select>";
                        } elseif ($input_type === 'textarea') {
                            echo "<textarea name='" . htmlspecialchars($col) . "' rows='2'>" . htmlspecialchars($input_value) . "</textarea>";
                        } elseif ($input_type === 'checkbox') {
                            $checked = ($input_value == 1) ? 'checked' : '';
                            echo "<input type='checkbox' name='" . htmlspecialchars($col) . "' " . $checked . ">";
                        } else {
                            echo "<input type='" . htmlspecialchars($input_type) . "' name='" . htmlspecialchars($col) . "' value='" . htmlspecialchars($input_value) . "'>";
                        }
                    } else {
                        // Display read-only value
                        if (isset($config['foreign_keys'][$col])) {
                            echo htmlspecialchars($config['foreign_keys'][$col]['options'][$row[$col]] ?? 'N/A');
                        } elseif ($col === 'is_available') {
                            echo $row[$col] ? 'Yes' : 'No';
                        } else {
                            echo $display_value;
                        }
                    }
                    echo "</td>";
                }

                echo "<td class='actions-cell'>";
                if ($is_editing_this_row) {
                    echo "<button type='submit' class='btn btn-primary btn-small'>Update</button>";
                    echo "<a href='dashboard.php' class='btn btn-secondary btn-small' style='margin-left: 5px;'>Cancel</a>";
                } else {
                    // Link to trigger inline edit for this row
                    $edit_link_id = ($tableName === 'blood_inventory') ? $row['blood_group'] : $row['id'];
                    echo "<a href='dashboard.php?edit_table=" . htmlspecialchars($tableName) . "&edit_id=" . htmlspecialchars($edit_link_id) . "' class='btn btn-yellow btn-small'>✏️ Edit</a>";

                    // Delete form
                    echo "<form action='' method='POST' class='inline-form' onsubmit='return confirm(\"Are you sure you want to delete this record?\");'>";
                    echo "<input type='hidden' name='table_name' value='" . htmlspecialchars($tableName) . "'>";
                    echo "<input type='hidden' name='action' value='delete'>";
                    echo "<input type='hidden' name='id' value='" . htmlspecialchars($row_id_value) . "'>";
                    echo "<button type='submit' class='btn btn-red btn-small'>🗑️ Delete</button>";
                    echo "</form>";
                }
                echo "</td>";
                echo "</form>"; // Close the form for the row
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
            echo "</div>";
            echo "</section>";
        }

        // --- Call displayCrudTable for each main table ---
        displayCrudTable($conn, 'blood_inventory', $blood_inventory, $tableConfigs['blood_inventory'], $current_edit_table, $current_edit_id, $edit_data);
        displayCrudTable($conn, 'donors', $donors, $tableConfigs['donors'], $current_edit_table, $current_edit_id, $edit_data);
        displayCrudTable($conn, 'recipients', $recipients, $tableConfigs['recipients'], $current_edit_table, $current_edit_id, $edit_data);
        displayCrudTable($conn, 'blood_requests', $blood_requests, $tableConfigs['blood_requests'], $current_edit_table, $current_edit_id, $edit_data);
        displayCrudTable($conn, 'donation_history', $donation_history, $tableConfigs['donation_history'], $current_edit_table, $current_edit_id, $edit_data);
        ?>

    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> BloodLink. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // JavaScript for scrolling to forms/tables if an action was triggered
        document.addEventListener('DOMContentLoaded', function() {
            const displayAddForm = "<?php echo $display_add_form; ?>";
            const currentEditTable = "<?php echo $current_edit_table; ?>";

            if (displayAddForm) {
                const formId = 'add' + displayAddForm.charAt(0).toUpperCase() + displayAddForm.slice(1) + 'Form';
                const formElement = document.getElementById(formId);
                if (formElement) {
                    formElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            } else if (currentEditTable) {
                // Find the section corresponding to the edited table and scroll to it
                // This uses a more robust selector for the section heading
                const sectionHeadingText = currentEditTable.replace('_', ' ').split(' ').map(s => s.charAt(0).toUpperCase() + s.slice(1)).join(' ');
                // Find the h3 element that contains the exact text
                const h3Elements = document.querySelectorAll('section.data-section h3.section-heading');
                let targetSection = null;
                h3Elements.forEach(h3 => {
                    if (h3.textContent.includes(sectionHeadingText)) {
                        targetSection = h3.closest('section.data-section');
                    }
                });

                if (targetSection) {
                    targetSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        });
    </script>

</body>
</html>
