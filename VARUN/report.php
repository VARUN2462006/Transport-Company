<?php
require_once __DIR__ . '/../Comman Point/security_bootstrap.php';
if (empty($_SESSION['admin_user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin Dashboard</title>
    <link rel="stylesheet" href="report.css">
</head>
<body>
    <header class="report-header">
        <h1>Reports Dashboard</h1>
        <div class="header-actions">
            <a href="admin_dashboard.php" class="back-btn">Back to Control Panel</a>
        </div>
    </header>

    <main class="report-container">
        <div class="action-cards">
            <!-- Bookings Report Card -->
            <a href="report_bookings.php" class="card">
                <div class="card-icon">📅</div>
                <h3>Bookings Report</h3>
                <p>View detailed booking statistics and history.</p>
            </a>

            <!-- Truck Inventory Report Card -->
            <a href="report_inventory.php" class="card">
                <div class="card-icon">🚛</div>
                <h3>Truck Inventory</h3>
                <p>View current status of truck fleet.</p>
            </a>

            <!-- Truck Prices Report Card -->
            <a href="report_prices.php" class="card">
                <div class="card-icon">💵</div>
                <h3>Truck Prices</h3>
                <p>View historical and current pricing data.</p>
            </a>

            <!-- Drivers Report Card -->
            <a href="report_drivers.php" class="card">
                <div class="card-icon">👨‍✈️</div>
                <h3>Drivers Report</h3>
                <p>View driver roster and salary expenditures.</p>
            </a>

            <!-- User Activity Report Card -->
            <a href="report_activity.php" class="card">
                <div class="card-icon">👥</div>
                <h3>User Activity</h3>
                <p>View user engagement and activity metrics.</p>
            </a>
        </div>
    </main>
</body>
</html>
