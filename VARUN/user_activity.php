<?php
require_once __DIR__ . '/../Comman Point/security_bootstrap.php';
if (empty($_SESSION['admin_user_id'])) {
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/../connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Activity – Admin</title>
    <!-- Basic styling inspired by dashboard -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; color: #333; line-height: 1.6; }
        .admin-header { background: #ffffff; padding: 1rem 2rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .admin-header h1 { color: #2c3e50; font-size: 1.5rem; }
        .admin-nav { display: flex; align-items: center; gap: 1rem; }
        .admin-nav a { text-decoration: none; color: #333; font-weight: 500; }
        .admin-nav .active { color: #3498db; }
        .logout-btn { background-color: #2c3e50; color: white !important; padding: 0.5rem 1rem; border-radius: 4px; transition: background 0.3s; }
        .logout-btn:hover { background-color: #34495e; }
        .main-content { max-width: 1200px; margin: 2rem auto; padding: 0 2rem; }
    </style>
</head>
<body>
    <header class="admin-header">
        <h1>Admin Panel</h1>
        <nav class="admin-nav">
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="bookings.php">Bookings</a>
            <a href="truck_rates.php">Truck Rates</a>
            <a href="user_activity.php" class="active">User Activity</a>
            <a href="index.php?logout=1" class="logout-btn">Logout</a>
        </nav>
    </header>

    <main class="main-content">
        <h2 style="color: #2c3e50; margin-bottom: 20px; border-bottom: 2px solid #3498db; padding-bottom: 10px; display: inline-block;">User Activity Monitor</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 30px;">
            
            <!-- Users Logged In Today -->
            <div class="activity-card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <h3 style="color: #27ae60; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-user-clock"></i> Active Today (<?php echo date('d M Y'); ?>)
                </h3>
                <?php
                $today_sql = "SELECT name, email, last_login FROM users WHERE DATE(last_login) = CURDATE() ORDER BY last_login DESC";
                $today_stmt = $conn->prepare($today_sql);
                $today_stmt->execute();
                $today_res = $today_stmt->get_result();
                
                if ($today_res->num_rows > 0) {
                    echo '<table style="width: 100%; border-collapse: collapse;">';
                    echo '<tr style="background: #f8f9fa; text-align: left;">
                            <th style="padding: 10px; border-bottom: 1px solid #eee;">Name</th>
                            <th style="padding: 10px; border-bottom: 1px solid #eee;">Email</th>
                            <th style="padding: 10px; border-bottom: 1px solid #eee;">Time</th>
                          </tr>';
                    while ($row = $today_res->fetch_assoc()) {
                        $time = date('h:i A', strtotime($row['last_login']));
                        echo "<tr>
                                <td style='padding: 10px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($row['name']) . "</td>
                                <td style='padding: 10px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($row['email']) . "</td>
                                <td style='padding: 10px; border-bottom: 1px solid #eee; color: #27ae60; font-weight: bold;'>$time</td>
                              </tr>";
                    }
                    echo '</table>';
                    $today_stmt->close();
                } else {
                    echo '<p style="color: #7f8c8d; font-style: italic;">No users have logged in today yet.</p>';
                    $today_stmt->close();
                }
                ?>
            </div>

            <!-- All Users & Last Login -->
            <div class="activity-card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <h3 style="color: #2980b9; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-users"></i> All Users
                </h3>
                <?php
                $all_sql = "SELECT u.id, u.name, u.email, COUNT(b.id) AS order_count FROM users u LEFT JOIN bookings b ON u.email = b.user_email GROUP BY u.id ORDER BY u.id DESC"; 
                $all_stmt = $conn->prepare($all_sql);
                $all_stmt->execute();
                $all_res = $all_stmt->get_result();
                
                if ($all_res->num_rows > 0) {
                    echo '<table style="width: 100%; border-collapse: collapse;">';
                    echo '<tr style="background: #f8f9fa; text-align: left;">
                            <th style="padding: 10px; border-bottom: 1px solid #eee;">Name</th>
                            <th style="padding: 10px; border-bottom: 1px solid #eee;">Email</th>
                            <th style="padding: 10px; border-bottom: 1px solid #eee;">Orders</th>
                          </tr>';
                    while ($row = $all_res->fetch_assoc()) {
                        echo "<tr>
                                <td style='padding: 10px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($row['name']) . "</td>
                                <td style='padding: 10px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($row['email']) . "</td>
                                <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: center; color: #2980b9; font-weight: bold;'>{$row['order_count']}</td>
                              </tr>";
                    }
                    echo '</table>';
                    $all_stmt->close();
                } else {
                    echo '<p style="color: #7f8c8d;">No users found.</p>';
                    $all_stmt->close();
                }
                ?>
            </div>
            
        </div>
    </main>
</body>
</html>
