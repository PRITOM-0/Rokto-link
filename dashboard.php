<?php
require_once 'config.php';

// Removed the redirect_to_login_if_not_authenticated() call.
// The dashboard is now directly accessible without any login.

$message = '';
$error = '';

// Handle CRUD operations for Donors, Blood Requests, Recipients, Blood Inventory, and Donation History
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? sanitize_input($_POST['action']) : '';
    $table = isset($_POST['table']) ? sanitize_input($_POST['table']) : '';

    if ($table == 'donors') {
        if ($action == 'add') {
            $name = sanitize_input($_POST['name']);
            $email = sanitize_input($_POST['email']);
            $phone = sanitize_input($_POST['phone']);
            $blood_group = sanitize_input($_POST['blood_group']);
            $last_donation_date = !empty($_POST['last_donation_date']) ? sanitize_input($_POST['last_donation_date']) : NULL;
            $address = sanitize_input($_POST['address']);
            $city = sanitize_input($_POST['city']);
            $state = sanitize_input($_POST['state']);
            $zip_code = sanitize_input($_POST['zip_code']);
            $is_available = isset($_POST['is_available']) ? 1 : 0;

            $stmt = $conn->prepare("INSERT INTO donors (name, email, phone, blood_group, last_donation_date, address, city, state, zip_code, is_available) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssssi", $name, $email, $phone, $blood_group, $last_donation_date, $address, $city, $state, $zip_code, $is_available);
            if ($stmt->execute()) {
                $message = "Donor added successfully.";
            } else {
                $error = "Error adding donor: " . $stmt->error;
            }
            $stmt->close();
        } elseif ($action == 'update') {
            $id = (int)$_POST['id'];
            $name = sanitize_input($_POST['name']);
            $email = sanitize_input($_POST['email']);
            $phone = sanitize_input($_POST['phone']);
            $blood_group = sanitize_input($_POST['blood_group']);
            $last_donation_date = !empty($_POST['last_donation_date']) ? sanitize_input($_POST['last_donation_date']) : NULL;
            $address = sanitize_input($_POST['address']);
            $city = sanitize_input($_POST['city']);
            $state = sanitize_input($_POST['state']);
            $zip_code = sanitize_input($_POST['zip_code']);
            $is_available = isset($_POST['is_available']) ? 1 : 0;

            $stmt = $conn->prepare("UPDATE donors SET name=?, email=?, phone=?, blood_group=?, last_donation_date=?, address=?, city=?, state=?, zip_code=?, is_available=? WHERE id=?");
            $stmt->bind_param("sssssssssii", $name, $email, $phone, $blood_group, $last_donation_date, $address, $city, $state, $zip_code, $is_available, $id);
            if ($stmt->execute()) {
                $message = "Donor updated successfully.";
            } else {
                $error = "Error updating donor: " . $stmt->error;
            }
            $stmt->close();
        } elseif ($action == 'delete') {
            $id = (int)$_POST['id'];
            $stmt = $conn->prepare("DELETE FROM donors WHERE id=?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = "Donor deleted successfully.";
            } else {
                $error = "Error deleting donor: " . $stmt->error;
            }
            $stmt->close();
        }
    } elseif ($table == 'recipients') {
        if ($action == 'add') {
            $name = sanitize_input($_POST['name']);
            $email = sanitize_input($_POST['email']);
            $phone = sanitize_input($_POST['phone']);
            $blood_group = sanitize_input($_POST['blood_group']);
            $reason = sanitize_input($_POST['reason']);
            $hospital_name = sanitize_input($_POST['hospital_name']);
            $city = sanitize_input($_POST['city']);
            $state = sanitize_input($_POST['state']);

            $stmt = $conn->prepare("INSERT INTO recipients (name, email, phone, blood_group, reason, hospital_name, city, state) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $name, $email, $phone, $blood_group, $reason, $hospital_name, $city, $state);
            if ($stmt->execute()) {
                $message = "Recipient added successfully.";
            } else {
                $error = "Error adding recipient: " . $stmt->error;
            }
            $stmt->close();
        } elseif ($action == 'update') {
            $id = (int)$_POST['id'];
            $name = sanitize_input($_POST['name']);
            $email = sanitize_input($_POST['email']);
            $phone = sanitize_input($_POST['phone']);
            $blood_group = sanitize_input($_POST['blood_group']);
            $reason = sanitize_input($_POST['reason']);
            $hospital_name = sanitize_input($_POST['hospital_name']);
            $city = sanitize_input($_POST['city']);
            $state = sanitize_input($_POST['state']);

            $stmt = $conn->prepare("UPDATE recipients SET name=?, email=?, phone=?, blood_group=?, reason=?, hospital_name=?, city=?, state=? WHERE id=?");
            $stmt->bind_param("ssssssssi", $name, $email, $phone, $blood_group, $reason, $hospital_name, $city, $state, $id);
            if ($stmt->execute()) {
                $message = "Recipient updated successfully.";
            } else {
                $error = "Error updating recipient: " . $stmt->error;
            }
            $stmt->close();
        } elseif ($action == 'delete') {
            $id = (int)$_POST['id'];
            $stmt = $conn->prepare("DELETE FROM recipients WHERE id=?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = "Recipient deleted successfully.";
            } else {
                $error = "Error deleting recipient: " . $stmt->error;
            }
            $stmt->close();
        }
    } elseif ($table == 'blood_requests') {
        if ($action == 'add') {
            $recipient_id = (int)$_POST['recipient_id'];
            $blood_group = sanitize_input($_POST['blood_group']);
            $units_needed = (int)$_POST['units_needed'];
            $status = sanitize_input($_POST['status']);

            $stmt = $conn->prepare("INSERT INTO blood_requests (recipient_id, blood_group, units_needed, status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $recipient_id, $blood_group, $units_needed, $status);
            if ($stmt->execute()) {
                $message = "Blood request added successfully.";
            } else {
                $error = "Error adding blood request: " . $stmt->error;
            }
            $stmt->close();
        } elseif ($action == 'update') {
            $id = (int)$_POST['id'];
            $recipient_id = (int)$_POST['recipient_id'];
            $blood_group = sanitize_input($_POST['blood_group']);
            $units_needed = (int)$_POST['units_needed'];
            $status = sanitize_input($_POST['status']);

            $stmt = $conn->prepare("UPDATE blood_requests SET recipient_id=?, blood_group=?, units_needed=?, status=? WHERE id=?");
            $stmt->bind_param("iissi", $recipient_id, $blood_group, $units_needed, $status, $id);
            if ($stmt->execute()) {
                $message = "Blood request updated successfully.";
            } else {
                $error = "Error updating blood request: " . $stmt->error;
            }
            $stmt->close();
        } elseif ($action == 'delete') {
            $id = (int)$_POST['id'];
            $stmt = $conn->prepare("DELETE FROM blood_requests WHERE id=?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = "Blood request deleted successfully.";
            } else {
                $error = "Error deleting blood request: " . $stmt->error;
            }
            $stmt->close();
        }
    } elseif ($table == 'donation_history') {
        if ($action == 'add') {
            $donor_id = (int)$_POST['donor_id'];
            $donation_date = sanitize_input($_POST['donation_date']);
            $blood_group = sanitize_input($_POST['blood_group']);
            $units_donated = (int)$_POST['units_donated'];
            $notes = sanitize_input($_POST['notes']);

            $stmt = $conn->prepare("INSERT INTO donation_history (donor_id, donation_date, blood_group, units_donated, notes) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("isiss", $donor_id, $donation_date, $blood_group, $units_donated, $notes);
            if ($stmt->execute()) {
                $message = "Donation history added successfully. Inventory updated by trigger.";
            } else {
                $error = "Error adding donation history: " . $stmt->error;
            }
            $stmt->close();
        } elseif ($action == 'update') {
            $id = (int)$_POST['id'];
            $donor_id = (int)$_POST['donor_id'];
            $donation_date = sanitize_input($_POST['donation_date']);
            $blood_group = sanitize_input($_POST['blood_group']);
            $units_donated = (int)$_POST['units_donated'];
            $notes = sanitize_input($_POST['notes']);

            // Note: Updating donation history directly won't trigger inventory updates automatically
            // You'd need to manually adjust inventory or re-design the trigger logic for updates.
            // For simplicity, this update only changes the history record.
            $stmt = $conn->prepare("UPDATE donation_history SET donor_id=?, donation_date=?, blood_group=?, units_donated=?, notes=? WHERE id=?");
            $stmt->bind_param("isissi", $donor_id, $donation_date, $blood_group, $units_donated, $notes, $id);
            if ($stmt->execute()) {
                $message = "Donation history updated successfully.";
            } else {
                $error = "Error updating donation history: " . $stmt->error;
            }
            $stmt->close();
        } elseif ($action == 'delete') {
            $id = (int)$_POST['id'];
            // Deleting a donation record would ideally decrement inventory, but this requires more complex trigger logic
            // For this example, deletion only removes the record.
            $stmt = $conn->prepare("DELETE FROM donation_history WHERE id=?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = "Donation history deleted successfully.";
            } else {
                $error = "Error deleting donation history: " . $stmt->error;
            }
            $stmt->close();
        }
    } elseif ($table == 'blood_inventory') {
        if ($action == 'update_inventory') {
            $blood_group = sanitize_input($_POST['blood_group']);
            $available_units = (int)$_POST['available_units'];

            $stmt = $conn->prepare("UPDATE blood_inventory SET available_units=? WHERE blood_group=?");
            $stmt->bind_param("is", $available_units, $blood_group);
            if ($stmt->execute()) {
                $message = "Blood inventory updated successfully for " . $blood_group . ".";
            } else {
                $error = "Error updating blood inventory: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Fetch all data for display
$donors = $conn->query("SELECT * FROM donors ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$recipients = $conn->query("SELECT * FROM recipients ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$blood_requests = $conn->query("SELECT br.*, r.name AS recipient_name FROM blood_requests br JOIN recipients r ON br.recipient_id = r.id ORDER BY request_date DESC")->fetch_all(MYSQLI_ASSOC);
$donation_history = $conn->query("SELECT dh.*, d.name AS donor_name FROM donation_history dh JOIN donors d ON dh.donor_id = d.id ORDER BY donation_date DESC")->fetch_all(MYSQLI_ASSOC);
$blood_inventory = $conn->query("SELECT * FROM blood_inventory ORDER BY blood_group ASC")->fetch_all(MYSQLI_ASSOC);

// Fetch donors and recipients for dropdowns in forms
$all_donors = $conn->query("SELECT id, name, blood_group FROM donors ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
$all_recipients = $conn->query("SELECT id, name, blood_group FROM recipients ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Blood Donation Management</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body class="font-inter">

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

        <h2 class="welcome-title">Welcome, Admin!</h2> <!-- Hardcoded for simple login -->

        <!-- Blood Inventory Section -->
        <section class="data-section">
            <h3 class="section-heading">
                Blood Inventory
                <button onclick="openModal('inventoryModal')" class="btn btn-blue">Update Inventory</button>
            </h3>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Blood Group</th>
                            <th>Available Units</th>
                            <th>Last Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($blood_inventory as $item): ?>
                            <tr>
                                <td class="font-semibold"><?php echo htmlspecialchars($item['blood_group']); ?></td>
                                <td><?php echo htmlspecialchars($item['available_units']); ?></td>
                                <td><?php htmlspecialchars($item['last_updated']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Donors Section -->
        <section class="data-section">
            <h3 class="section-heading">
                Donors
                <button onclick="openAddModal('donorModal')" class="btn btn-green">Add New Donor</button>
            </h3>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Blood Group</th>
                            <th>Last Donation</th>
                            <th>Available</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donors as $donor): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($donor['id']); ?></td>
                                <td><?php echo htmlspecialchars($donor['name']); ?></td>
                                <td><?php echo htmlspecialchars($donor['email']); ?></td>
                                <td><?php echo htmlspecialchars($donor['phone']); ?></td>
                                <td class="font-semibold"><?php echo htmlspecialchars($donor['blood_group']); ?></td>
                                <td><?php echo htmlspecialchars($donor['last_donation_date']); ?></td>
                                <td><?php echo $donor['is_available'] ? 'Yes' : 'No'; ?></td>
                                <td class="actions-cell">
                                    <button onclick="openEditModal('donorModal', <?php echo htmlspecialchars(json_encode($donor)); ?>)" class="btn btn-yellow btn-small">Edit</button>
                                    <form action="" method="POST" onsubmit="return confirm('Are you sure you want to delete this donor?');" class="inline-form">
                                        <input type="hidden" name="table" value="donors">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $donor['id']; ?>">
                                        <button type="submit" class="btn btn-red btn-small">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Recipients Section -->
        <section class="data-section">
            <h3 class="section-heading">
                Recipients
                <button onclick="openAddModal('recipientModal')" class="btn btn-green">Add New Recipient</button>
            </h3>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Blood Group</th>
                            <th>Hospital</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recipients as $recipient): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($recipient['id']); ?></td>
                                <td><?php echo htmlspecialchars($recipient['name']); ?></td>
                                <td><?php echo htmlspecialchars($recipient['email']); ?></td>
                                <td><?php echo htmlspecialchars($recipient['phone']); ?></td>
                                <td class="font-semibold"><?php echo htmlspecialchars($recipient['blood_group']); ?></td>
                                <td><?php echo htmlspecialchars($recipient['hospital_name']); ?></td>
                                <td class="actions-cell">
                                    <button onclick="openEditModal('recipientModal', <?php echo htmlspecialchars(json_encode($recipient)); ?>)" class="btn btn-yellow btn-small">Edit</button>
                                    <form action="" method="POST" onsubmit="return confirm('Are you sure you want to delete this recipient?');" class="inline-form">
                                        <input type="hidden" name="table" value="recipients">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $recipient['id']; ?>">
                                        <button type="submit" class="btn btn-red btn-small">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Blood Requests Section -->
        <section class="data-section">
            <h3 class="section-heading">
                Blood Requests
                <button onclick="openAddModal('bloodRequestModal')" class="btn btn-green">Add New Request</button>
            </h3>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Recipient Name</th>
                            <th>Blood Group</th>
                            <th>Units Needed</th>
                            <th>Request Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($blood_requests as $request): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($request['id']); ?></td>
                                <td><?php echo htmlspecialchars($request['recipient_name']); ?></td>
                                <td class="font-semibold"><?php echo htmlspecialchars($request['blood_group']); ?></td>
                                <td><?php echo htmlspecialchars($request['units_needed']); ?></td>
                                <td><?php htmlspecialchars($request['request_date']); ?></td>
                                <td><?php echo htmlspecialchars($request['status']); ?></td>
                                <td class="actions-cell">
                                    <button onclick="openEditModal('bloodRequestModal', <?php echo htmlspecialchars(json_encode($request)); ?>)" class="btn btn-yellow btn-small">Edit</button>
                                    <form action="" method="POST" onsubmit="return confirm('Are you sure you want to delete this blood request?');" class="inline-form">
                                        <input type="hidden" name="table" value="blood_requests">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $request['id']; ?>">
                                        <button type="submit" class="btn btn-red btn-small">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Donation History Section -->
        <section class="data-section">
            <h3 class="section-heading">
                Donation History
                <button onclick="openAddModal('donationHistoryModal')" class="btn btn-green">Add New Donation</button>
            </h3>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Donor Name</th>
                            <th>Donation Date</th>
                            <th>Blood Group</th>
                            <th>Units Donated</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donation_history as $donation): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($donation['id']); ?></td>
                                <td><?php echo htmlspecialchars($donation['donor_name']); ?></td>
                                <td><?php echo htmlspecialchars($donation['donation_date']); ?></td>
                                <td class="font-semibold"><?php echo htmlspecialchars($donation['blood_group']); ?></td>
                                <td><?php echo htmlspecialchars($donation['units_donated']); ?></td>
                                <td><?php echo htmlspecialchars($donation['notes']); ?></td>
                                <td class="actions-cell">
                                    <button onclick="openEditModal('donationHistoryModal', <?php echo htmlspecialchars(json_encode($donation)); ?>)" class="btn btn-yellow btn-small">Edit</button>
                                    <form action="" method="POST" onsubmit="return confirm('Are you sure you want to delete this donation record?');" class="inline-form">
                                        <input type="hidden" name="table" value="donation_history">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $donation['id']; ?>">
                                        <button type="submit" class="btn btn-red btn-small">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </main>

    <!-- Modals for Add/Edit Operations -->

    <!-- Inventory Update Modal -->
    <div id="inventoryModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal('inventoryModal')">&times;</span>
            <h3 class="modal-title">Update Blood Inventory</h3>
            <form action="" method="POST" class="form-layout">
                <input type="hidden" name="table" value="blood_inventory">
                <input type="hidden" name="action" value="update_inventory">
                <div class="form-group">
                    <label for="inventory_blood_group">Blood Group</label>
                    <select id="inventory_blood_group" name="blood_group" required>
                        <option value="">Select Blood Group</option>
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="inventory_available_units">Available Units</label>
                    <input type="number" id="inventory_available_units" name="available_units" required min="0">
                </div>
                <button type="submit" class="btn btn-blue">Update Inventory</button>
            </form>
        </div>
    </div>

    <!-- Donor Modal -->
    <div id="donorModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal('donorModal')">&times;</span>
            <h3 class="modal-title" id="donorModalTitle">Add New Donor</h3>
            <form action="" method="POST" class="form-layout">
                <input type="hidden" name="table" value="donors">
                <input type="hidden" name="action" id="donorAction" value="add">
                <input type="hidden" name="id" id="donorId">
                <div class="form-group">
                    <label for="donorName">Name</label>
                    <input type="text" id="donorName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="donorEmail">Email</label>
                    <input type="email" id="donorEmail" name="email">
                </div>
                <div class="form-group">
                    <label for="donorPhone">Phone</label>
                    <input type="text" id="donorPhone" name="phone">
                </div>
                <div class="form-group">
                    <label for="donorBloodGroup">Blood Group</label>
                    <select id="donorBloodGroup" name="blood_group" required>
                        <option value="">Select Blood Group</option>
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="donorLastDonationDate">Last Donation Date</label>
                    <input type="date" id="donorLastDonationDate" name="last_donation_date">
                </div>
                <div class="form-group">
                    <label for="donorAddress">Address</label>
                    <textarea id="donorAddress" name="address" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label for="donorCity">City</label>
                    <input type="text" id="donorCity" name="city">
                </div>
                <div class="form-group">
                    <label for="donorState">State</label>
                    <input type="text" id="donorState" name="state">
                </div>
                <div class="form-group">
                    <label for="donorZipCode">Zip Code</label>
                    <input type="text" id="donorZipCode" name="zip_code">
                </div>
                <div class="form-group checkbox-group">
                    <input type="checkbox" id="donorIsAvailable" name="is_available">
                    <label for="donorIsAvailable">Is Available for Donation?</label>
                </div>
                <button type="submit" class="btn btn-primary" id="donorSubmitButton">Add Donor</button>
            </form>
        </div>
    </div>

    <!-- Recipient Modal -->
    <div id="recipientModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal('recipientModal')">&times;</span>
            <h3 class="modal-title" id="recipientModalTitle">Add New Recipient</h3>
            <form action="" method="POST" class="form-layout">
                <input type="hidden" name="table" value="recipients">
                <input type="hidden" name="action" id="recipientAction" value="add">
                <input type="hidden" name="id" id="recipientId">
                <div class="form-group">
                    <label for="recipientName">Name</label>
                    <input type="text" id="recipientName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="recipientEmail">Email</label>
                    <input type="email" id="recipientEmail" name="email">
                </div>
                <div class="form-group">
                    <label for="recipientPhone">Phone</label>
                    <input type="text" id="recipientPhone" name="phone">
                </div>
                <div class="form-group">
                    <label for="recipientBloodGroup">Blood Group</label>
                    <select id="recipientBloodGroup" name="blood_group" required>
                        <option value="">Select Blood Group</option>
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="recipientReason">Reason for Blood</label>
                    <textarea id="recipientReason" name="reason" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label for="recipientHospitalName">Hospital Name</label>
                    <input type="text" id="recipientHospitalName" name="hospital_name">
                </div>
                <div class="form-group">
                    <label for="recipientCity">City</label>
                    <input type="text" id="recipientCity" name="city">
                </div>
                <div class="form-group">
                    <label for="recipientState">State</label>
                    <input type="text" id="recipientState" name="state">
                </div>
                <button type="submit" class="btn btn-primary" id="recipientSubmitButton">Add Recipient</button>
            </form>
        </div>
    </div>

    <!-- Blood Request Modal -->
    <div id="bloodRequestModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal('bloodRequestModal')">&times;</span>
            <h3 class="modal-title" id="bloodRequestModalTitle">Add New Blood Request</h3>
            <form action="" method="POST" class="form-layout">
                <input type="hidden" name="table" value="blood_requests">
                <input type="hidden" name="action" id="bloodRequestAction" value="add">
                <input type="hidden" name="id" id="bloodRequestId">
                <div class="form-group">
                    <label for="bloodRequestRecipientId">Recipient</label>
                    <select id="bloodRequestRecipientId" name="recipient_id" required>
                        <option value="">Select Recipient</option>
                        <?php foreach ($all_recipients as $rec): ?>
                            <option value="<?php echo htmlspecialchars($rec['id']); ?>"><?php echo htmlspecialchars($rec['name'] . ' (' . $rec['blood_group'] . ')'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="bloodRequestBloodGroup">Blood Group</label>
                    <select id="bloodRequestBloodGroup" name="blood_group" required>
                        <option value="">Select Blood Group</option>
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="bloodRequestUnitsNeeded">Units Needed</label>
                    <input type="number" id="bloodRequestUnitsNeeded" name="units_needed" required min="1">
                </div>
                <div class="form-group">
                    <label for="bloodRequestStatus">Status</label>
                    <select id="bloodRequestStatus" name="status" required>
                        <option value="pending">Pending</option>
                        <option value="fulfilled">Fulfilled</option>
                        <option value="urgent">Urgent</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" id="bloodRequestSubmitButton">Add Request</button>
            </form>
        </div>
    </div>

    <!-- Donation History Modal -->
    <div id="donationHistoryModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal('donationHistoryModal')">&times;</span>
            <h3 class="modal-title" id="donationHistoryModalTitle">Add New Donation Record</h3>
            <form action="" method="POST" class="form-layout">
                <input type="hidden" name="table" value="donation_history">
                <input type="hidden" name="action" id="donationHistoryAction" value="add">
                <input type="hidden" name="id" id="donationHistoryId">
                <div class="form-group">
                    <label for="donationHistoryDonorId">Donor</label>
                    <select id="donationHistoryDonorId" name="donor_id" required>
                        <option value="">Select Donor</option>
                        <?php foreach ($all_donors as $don): ?>
                            <option value="<?php echo htmlspecialchars($don['id']); ?>"><?php echo htmlspecialchars($don['name'] . ' (' . $don['blood_group'] . ')'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="donationHistoryDate">Donation Date</label>
                    <input type="date" id="donationHistoryDate" name="donation_date" required>
                </div>
                <div class="form-group">
                    <label for="donationHistoryBloodGroup">Blood Group</label>
                    <select id="donationHistoryBloodGroup" name="blood_group" required>
                        <option value="">Select Blood Group</option>
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="donationHistoryUnitsDonated">Units Donated</label>
                    <input type="number" id="donationHistoryUnitsDonated" name="units_donated" required min="1" value="1">
                </div>
                <div class="form-group">
                    <label for="donationHistoryNotes">Notes</label>
                    <textarea id="donationHistoryNotes" name="notes" rows="2"></textarea>
                </div>
                <button type="submit" class="btn btn-primary" id="donationHistorySubmitButton">Add Donation</button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> BloodLink. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // JavaScript for Modal functionality
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function openAddModal(modalId) {
            const modal = document.getElementById(modalId);
            const form = modal.querySelector('form');
            form.reset(); // Clear previous data

            modal.querySelector(`#${modalId}Title`).textContent = `Add New ${modalId.replace('Modal', '').replace(/([A-Z])/g, ' $1').trim()}`;
            modal.querySelector(`#${modalId.replace('Modal', '')}Action`).value = 'add';
            modal.querySelector(`#${modalId.replace('Modal', '')}Id`).value = '';
            modal.querySelector(`#${modalId.replace('Modal', '')}SubmitButton`).textContent = `Add ${modalId.replace('Modal', '').replace(/([A-Z])/g, ' $1').trim()}`;

            // Specific resets for modals that might have different fields
            if (modalId === 'donorModal') {
                document.getElementById('donorLastDonationDate').value = ''; // Clear date field
                document.getElementById('donorIsAvailable').checked = true; // Default to available
            } else if (modalId === 'bloodRequestModal') {
                 // Reset dropdowns to default "Select" option
                document.getElementById('bloodRequestRecipientId').value = '';
                document.getElementById('bloodRequestBloodGroup').value = '';
                document.getElementById('bloodRequestStatus').value = 'pending';
            } else if (modalId === 'donationHistoryModal') {
                document.getElementById('donationHistoryDonorId').value = '';
                document.getElementById('donationHistoryBloodGroup').value = '';
                document.getElementById('donationHistoryUnitsDonated').value = 1;
            }

            openModal(modalId);
        }

        function openEditModal(modalId, data) {
            const modal = document.getElementById(modalId);
            const form = modal.querySelector('form');
            form.reset(); // Clear previous data

            modal.querySelector(`#${modalId}Title`).textContent = `Edit ${modalId.replace('Modal', '').replace(/([A-Z])/g, ' $1').trim()}`;
            modal.querySelector(`#${modalId.replace('Modal', '')}Action`).value = 'update';
            modal.querySelector(`#${modalId.replace('Modal', '')}Id`).value = data.id;
            modal.querySelector(`#${modalId.replace('Modal', '')}SubmitButton`).textContent = `Update ${modalId.replace('Modal', '').replace(/([A-Z])/g, ' $1').trim()}`;

            // Populate form fields based on data
            for (const key in data) {
                const input = form.querySelector(`[name="${key}"]`);
                if (input) {
                    if (input.type === 'checkbox') {
                        input.checked = data[key] == 1;
                    } else if (input.tagName === 'SELECT') {
                        input.value = data[key];
                    } else if (input.type === 'date') {
                        // Ensure date format is YYYY-MM-DD
                        input.value = data[key] ? new Date(data[key]).toISOString().split('T')[0] : '';
                    }
                    else {
                        input.value = data[key];
                    }
                }
            }

            openModal(modalId);
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>

</body>
</html>
