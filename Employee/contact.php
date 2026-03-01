<?php
require_once __DIR__ . '/../Comman Point/security_bootstrap.php';
if (empty($_SESSION['driver_id'])) {
    header('Location: login.php');
    exit;
}
$driver_name = $_SESSION['driver_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact — Employee Portal</title>
    <link rel="stylesheet" href="employee.css">
</head>
<body>
    <!-- Navigation -->
    <header class="emp-header">
        <div class="brand">
            <img src="../Images/optimized/logo.webp" alt="Logo">
            <h1>Employee Portal</h1>
        </div>
        <nav class="emp-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="request_leave.php">Request Leave</a>
            <a href="contact.php" class="active">Contact</a>
            <a href="logout.php" class="logout-link">Logout</a>
        </nav>
    </header>

    <main class="emp-main">
        <!-- Company Info -->
        <div class="content-card">
            <h2>🏢 Company Contact</h2>
            <div class="contact-grid">
                <div class="contact-item">
                    <div class="icon">📞</div>
                    <div>
                        <h3>Office Phone</h3>
                        <p>+91 9405228627</p>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="icon">📧</div>
                    <div>
                        <h3>Email</h3>
                        <p>athawalevarun3@gmail.com</p>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="icon">📍</div>
                    <div>
                        <h3>Office Address</h3>
                        <p>123 Transport Street, Miraj, Maharashtra, India</p>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="icon">🕐</div>
                    <div>
                        <h3>Office Hours</h3>
                        <p>Mon – Sat: 9:00 AM – 6:00 PM<br>Sunday: Closed</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Emergency Contact -->
        <div class="content-card" style="border-left: 4px solid #dc3545;">
            <h2>🚨 Emergency Contact</h2>
            <div class="contact-grid">
                <div class="contact-item" style="border-left-color: #dc3545; background: #fff5f5;">
                    <div class="icon">🆘</div>
                    <div>
                        <h3>Road Emergency</h3>
                        <p>Call: +91 9405228627<br><small>Available 24/7 for breakdowns, accidents</small></p>
                    </div>
                </div>
                <div class="contact-item" style="border-left-color: #dc3545; background: #fff5f5;">
                    <div class="icon">🚔</div>
                    <div>
                        <h3>Police (India)</h3>
                        <p>Dial: 100<br><small>For accidents or theft</small></p>
                    </div>
                </div>
                <div class="contact-item" style="border-left-color: #dc3545; background: #fff5f5;">
                    <div class="icon">🚑</div>
                    <div>
                        <h3>Ambulance</h3>
                        <p>Dial: 108<br><small>Medical emergencies</small></p>
                    </div>
                </div>
                <div class="contact-item" style="border-left-color: #dc3545; background: #fff5f5;">
                    <div class="icon">🔥</div>
                    <div>
                        <h3>Fire Brigade</h3>
                        <p>Dial: 101<br><small>Fire emergencies</small></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Important Reminders -->
        <div class="content-card">
            <h2>📋 Important Reminders</h2>
            <ul style="list-style: none; padding: 0; font-size: 0.9rem; color: #555; line-height: 2;">
                <li>✅ Always carry your <strong>driving license</strong> and <strong>RC book</strong></li>
                <li>✅ Keep the <strong>vehicle insurance</strong> papers in the truck at all times</li>
                <li>✅ Report any <strong>accidents or breakdowns</strong> immediately to the office</li>
                <li>✅ Follow all <strong>traffic rules</strong> and speed limits</li>
                <li>✅ Take <strong>mandatory rest breaks</strong> every 4 hours of driving</li>
                <li>✅ <strong>Do not drive</strong> under the influence of alcohol or drugs</li>
                <li>✅ Submit <strong>leave requests</strong> at least 2 days in advance</li>
            </ul>
        </div>
    </main>
</body>
</html>
