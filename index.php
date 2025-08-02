<?php
require_once 'config.php';

// Fetch top needed blood groups using the VIEW
$top_needed_blood = [];

// SQL query to get the top 3 blood groups with the highest units needed
$sql_needed = "SELECT blood_group, SUM(units_needed) AS total_units FROM v_urgent_blood_needs GROUP BY blood_group ORDER BY total_units DESC LIMIT 3";


$result_needed = $conn->query($sql_needed);
if ($result_needed && $result_needed->num_rows > 0) {
    while ($row = $result_needed->fetch_assoc()) {
        $top_needed_blood[] = $row;
    }
}

// Fetch recent donors (recent 5 donors)
$recent_donors = [];

// SQL query to get the last 5 donors who have donated blood
$sql_recent_donors = "SELECT name, blood_group, last_donation_date FROM donors WHERE last_donation_date IS NOT NULL ORDER BY last_donation_date DESC LIMIT 5";

$result_recent_donors = $conn->query($sql_recent_donors);
if ($result_recent_donors && $result_recent_donors->num_rows > 0) {
    while ($row = $result_recent_donors->fetch_assoc()) {
        $recent_donors[] = $row;
    }
}

// Fetch recent donation history (e.g., last 5 donations)
$recent_donations = [];
$sql_recent_donations = "SELECT dh.donation_date, d.name AS donor_name, dh.blood_group, dh.units_donated
                        FROM donation_history dh
                        JOIN donors d ON dh.donor_id = d.id
                        ORDER BY dh.donation_date DESC LIMIT 5";
$result_recent_donations = $conn->query($sql_recent_donations);
if ($result_recent_donations && $result_recent_donations->num_rows > 0) {
    while ($row = $result_recent_donations->fetch_assoc()) {
        $recent_donations[] = $row;
    }
}

$conn->close();
?>












<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rokto-Link</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="img/icon.png">
</head>
<body class="font-inter">

    <!-- Header -->
    <header class="header">
        <div class="container header-content">
            <h1 class="site-title">Rokto-Link</h1>
            <nav>
                <ul class="nav-list">
                    <li><a href="#home" >Home</a></li>
                    <li><a href="#features" >Features</a></li>
                    <li><a href="#contact" >Contact</a></li>
                    <li><a href="#need-blood" >Need Blood</a></li>
                    <li><a href="#recent-donors" >Recent Donors</a></li>
                    <li><a href="#donation-history" >Donation History</a></li>
                    <li><a href="donors.php" class="dashboard-button">All Donors</a></li>
                    <li><a href="recipients.php" class="dashboard-button">All Recipients</a></li>
                    <li><a href="search.php" class="dashboard-button">Search</a></li>
                    <li><a href="dashboard.php" class="dashboard-button">Dashboard</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="container hero-content">
            <h2 class="hero-title animate-fade-in-down">Donate Blood, Save Lives.</h2>
            <p class="hero-subtitle animate-fade-in-up">Every drop counts. Join our community to make a difference.</p>
            <a href="#contact" class="btn btn-primary animate-fade-in-up">Become a Donor</a>
        </div>
    </section>

    <!-- Information Section -->
    <section id="info" class="info-section">
        <div class="container text-center">
            <h3 class="section-title">Why Donate Blood?</h3>
            <div class="grid-3-cols">
                <div class="info-card">
                    <img src="img/life_icon.png" alt="Bandage Icon" height='100px' alt="Heart Icon" class="info-icon">
                    <h4 class="info-card-title">Life-Saving Impact</h4>
                    <p class="info-card-text">Your single donation can save up to three lives. It's a simple act with profound impact.</p>
                </div>
                <div class="info-card">
                    <img src="img/bandage_icon.png" alt="Bandage Icon" height='100px' class="info-icon">
                    <h4 class="info-card-title">For Emergencies</h4>
                    <p class="info-card-text">Blood is constantly needed for accident victims, surgeries, and chronic illnesses.</p>
                </div>
                <div class="info-card">
                    <img src="img/hospital_icon.png" alt="Hospital Icon" height='100px' class="info-icon">
                    <h4 class="info-card-title">Community Health</h4>
                    <p class="info-card-text">Maintaining a healthy blood supply is crucial for the well-being of our entire community.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features-section">
        <div class="container text-center">
            <h3 class="section-title">Our Features</h3>
            <div class="grid-4-cols">
                <div class="feature-card">
                    <img src="img/find_doner_icon.png" height="100px" alt="Search Icon" class="feature-icon">
                    <h4 class="feature-card-title">Find Donors</h4>
                    <p class="feature-card-text">Quickly locate available donors by blood group and location.</p>
                </div>
                <div class="feature-card">
                    <img src="img/register_icon.png" height="100px" alt="Register Icon" class="feature-icon">
                    <h4 class="feature-card-title">Register as Donor</h4>
                    <p class="feature-card-text">Easy registration process to become a part of our donor network.</p>
                </div>
                <div class="feature-card">
                    <img src="img/request_icon.png" height="100px" alt="Blood Drop Icon" class="feature-icon">
                    <h4 class="feature-card-title">Request Blood</h4>
                    <p class="feature-card-text">Submit urgent or regular blood requests for patients in need.</p>
                </div>
                <div class="feature-card">
                    <img src="img/dashboard_icon.png" height="100px" alt="Dashboard Icon" class="feature-icon">
                    <h4 class="feature-card-title">Dashboard Access</h4>
                    <p class="feature-card-text">Manage all data, view statistics, and perform CRUD operations.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Top Needed Blood Section -->
    <section id="need-blood" class="top-needed-section">
        <div class="container text-center">
            <h3 class="section-title">Top Needed Blood Groups (Urgent Requests)</h3>








            <?php if (!empty($top_needed_blood)): ?>
                <div class="grid-3-cols max-width-content">
                    <?php foreach ($top_needed_blood as $blood): ?>
                        <div class="blood-needed-card">
                            <p class="blood-group-large"><?php echo htmlspecialchars($blood['blood_group']); ?></p>
                            <p class="blood-units-text">Units Needed: <span class="font-semibold"><?php echo htmlspecialchars($blood['total_units']); ?></span></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-data-message">No urgent blood requests at the moment. Please check back later.</p>
            <?php endif; ?>










        </div>
    </section>

    <!-- Recent Donors Section -->
    <section id="recent-donors" class="recent-donors-section">
        <div class="container text-center">
            <h3 class="section-title">Recent Donors</h3>
















            <?php if (!empty($recent_donors)): ?>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Donor Name</th>
                                <th>Blood Group</th>
                                <th>Last Donation</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_donors as $donor): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($donor['name']); ?></td>
                                    <td class="font-semibold"><?php echo htmlspecialchars($donor['blood_group']); ?></td>
                                    <td><?php echo htmlspecialchars($donor['last_donation_date']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="no-data-message">No recent donor information available.</p>
            <?php endif; ?>



















        </div>
    </section>

    <!-- Recent Donation History Section -->
    <section id="donation-history" class="donation-history-section">
        <div class="container text-center">
            <h3 class="section-title">Recent Donation History</h3>














            <?php if (!empty($recent_donations)): ?>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Donation Date</th>
                                <th>Donor Name</th>
                                <th>Blood Group</th>
                                <th>Units Donated</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_donations as $donation): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($donation['donation_date']); ?></td>
                                    <td><?php echo htmlspecialchars($donation['donor_name']); ?></td>
                                    <td class="font-semibold"><?php echo htmlspecialchars($donation['blood_group']); ?></td>
                                    <td><?php echo htmlspecialchars($donation['units_donated']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="no-data-message">No recent donation history available.</p>
            <?php endif; ?>















            
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact-section">
        <div class="container text-center">
            <h3 class="section-title text-white">Get in Touch</h3>
            <p class="contact-intro-text">Have questions or want to register? Fill out the form below or contact us directly.</p>

            <div class="contact-form-container">
                <form action="process_form.php" method="POST" class="form-layout">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-submit">Send Message</button>
                </form>
                <div class="contact-info">
                    <p>Or call us at: <span class="font-semibold">123-456-7890</span></p>
                    <p>Email: <span class="font-semibold">info@rokto-link.org</span></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> Rokto-Link. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>
