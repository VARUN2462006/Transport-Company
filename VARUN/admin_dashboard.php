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
            background-color: #2c3e50;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-decoration: none;
            transition: background 0.3s;
        }
        .logout-btn:hover {
            background-color: #34495e;
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

        /* ============ RESPONSIVE ============ */
        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                text-align: center;
                padding: 0.75rem 1rem;
                gap: 0.5rem;
            }
            .dashboard-header h1 {
                font-size: 1.2rem;
            }
            .user-info {
                gap: 0.5rem;
            }
            .dashboard-main {
                padding: 0 1rem;
                margin: 1rem auto;
            }
            .welcome-section h2 {
                font-size: 1.5rem;
            }
            .action-cards {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            .card {
                padding: 1.25rem;
            }
            .card h3 {
                font-size: 1.2rem;
            }
            .card-icon {
                font-size: 2rem;
            }
        }

        @media (max-width: 480px) {
            .dashboard-header h1 {
                font-size: 1rem;
            }
            .logout-btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.85rem;
            }
            .welcome-section h2 {
                font-size: 1.3rem;
            }
            .card {
                padding: 1rem;
            }
        }
        
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="dashboard-header">
        <h1>Admin Control Panel</h1>
        <div class="user-info">
            <b><span>Welcome Admin</span></b>
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
            
            <!-- Truck Management Card -->
            <a href="admin_trucks.php" class="card" style="border-left: 4px solid #3498db;">
                <div class="card-icon"><i class="fas fa-truck-moving"></i></div>
                <h3>Manage Trucks</h3>
                <p>Register new trucks, update their transit state, and remove old fleet.</p>
            </a>
            
            <!-- Driver Management Card -->
            <a href="../Driver/admin_drivers.php" class="card" style="border-left: 4px solid #f39c12;">
                <div class="card-icon">👨‍✈️</div>
                <h3>Manage Drivers / Staff</h3>
                <p>Add new drivers, update their working status, and process leave requests.</p>
            </a>
            
            <!-- User Activity Card -->
            <a href="user_activity.php" class="card">
                <div class="card-icon">👥</div>
                <h3>User Activity</h3>
                <p>Monitor user logins and activity history.</p>
            </a>

            <!-- Report Card -->
            <a href="report.php" class="card">
                <div class="card-icon">📊</div>
                <h3>Report</h3>
            </a>
        </div>
    </main>
</body>
</html>
