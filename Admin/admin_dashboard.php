<?php
session_start();
if (empty($_SESSION['admin_user_id'])) {
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/../Public/connect.php';

// Check if bookings has status column (for information only)
$has_status = false;
$res = $conn->query("SHOW COLUMNS FROM bookings LIKE 'status'");
if ($res && $res->num_rows > 0) {
    $has_status = true;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            color: #333;
            line-height: 1.6;
        }

        /* Header Styles */
        .dashboard-header {
            background: #ffffff;
            padding: 1rem 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .dashboard-header h1 {
            color: #2c3e50;
            font-size: 1.5rem;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .logout-btn {
            background-color: #e74c3c;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-decoration: none;
            transition: background 0.3s;
        }
        .logout-btn:hover {
            background-color: #c0392b;
        }

        /* Main Content */
        .dashboard-main {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        .welcome-section {
            margin-bottom: 2rem;
            text-align: center;
        }
        .welcome-section h2 {
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        .welcome-section p {
            color: #7f8c8d;
        }

        /* Action Cards */
        .action-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        .card {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }
        .card-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #3498db;
        }
        .card h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: #2c3e50;
        }
        .card p {
            color: #7f8c8d;
        }
        
    </style>
</head>
<body>
    <header class="dashboard-header">
        <h1>Admin Control Panel</h1>
        <div class="user-info">
            <span>Welcome, <strong><?= htmlspecialchars($_SESSION['admin_user_id']) ?></strong></span>
            <a href="index.php?logout=1" class="logout-btn">Logout</a>
        </div>
    </header>

    <main class="dashboard-main">
        <div class="welcome-section">
            <h2>Welcome Back!</h2>
            <p>Select an action below to manage the transport system.</p>
        </div>

        <div class="action-cards">
            <!-- Booking Management Card -->
            <a href="bookings.php" class="card">
                <div class="card-icon">📅</div>
                <h3>Manage Bookings</h3>
                <p>View customer bookings, accept or reject requests, and track status.</p>
            </a>

            <!-- Truck Rates Management Card -->
            <a href="truck_rates.php" class="card">
                <div class="card-icon">🚛</div>
                <h3>Manage Truck Rates</h3>
                <p>Update pricing per kilometer and per ton for all available trucks.</p>
            </a>
        </div>
    </main>
</body>
</html>
