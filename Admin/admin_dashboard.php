<?php
session_start();
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
    <link rel="stylesheet" href="admin_dashboard.css">
    <title>Admin Dashboard</title>
</head>
<body>
    <header class="admin-header">
        <h1>Admin Panel</h1>
        <nav class="admin-nav">
            <a href="admin_dashboard.php" class="active">Dashboard</a>
            <a href="bookings.php">Bookings</a>
            <a href="truck_rates.php">Truck Rates</a>
            <a href="index.php?logout=1" class="logout-btn">Logout</a>
        </nav>
    </header>

    <main class="dashboard-main">
        <h2 class="welcome-title">Welcome to Admin Dashboard</h2>
        <p class="welcome-desc">Choose a section to manage.</p>
        <div class="dashboard-cards">
            <a href="bookings.php" class="card card-bookings">
                <span class="card-icon">📋</span>
                <h3>Bookings</h3>
                <p>View, accept and reject customer orders.</p>
            </a>
            <a href="truck_rates.php" class="card card-rates">
                <span class="card-icon">💰</span>
                <h3>Truck Rates</h3>
                <p>Update ₹/km and ₹/ton for each truck.</p>
            </a>
        </div>
    </main>
</body>
</html>
