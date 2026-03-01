<?php
require_once __DIR__ . '/../Comman Point/security_bootstrap.php';
if (empty($_SESSION['admin_user_id'])) {
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/../connect.php';

// Fetch User Stats
$stats = [
    'total_users' => 0,
    'active_today' => 0
];

$res = $conn->query("SELECT COUNT(*) as count FROM users");
if ($res && $row = $res->fetch_assoc()) {
    $stats['total_users'] = $row['count'];
}

$res = $conn->query("SELECT COUNT(*) as count FROM users WHERE DATE(last_login) = CURDATE()");
if ($res && $row = $res->fetch_assoc()) {
    $stats['active_today'] = $row['count'];
}

// Fetch all users
$users = [];
$userResult = $conn->query("SELECT * FROM users ORDER BY id ASC");
if ($userResult) {
    while ($row = $userResult->fetch_assoc()) {
        $users[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Activity Report</title>
    <link rel="stylesheet" href="admin_bookings.css?v=2.0">
</head>
<body>
    <header class="admin-header">
        <h1>User Activity Report</h1>
        <nav class="admin-nav">
            <a href="report.php">← Back to Reports</a>
            <button onclick="window.print()" class="logout-btn" style="border:none; cursor:pointer; font-family:inherit; font-size:inherit;">🖨 Print</button>
        </nav>
    </header>

    <main class="bookings-main">
        <!-- Stats Row -->
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px,1fr)); gap:1rem; margin-bottom:1.5rem;">
            <div class="bookings-section" style="text-align:center; padding:1.25rem;">
                <div style="font-size:0.8rem; text-transform:uppercase; color:#7f8c8d; font-weight:600; letter-spacing:0.5px;">Total Users</div>
                <div style="font-size:1.8rem; font-weight:800; color:#1a1a1a; margin-top:0.25rem;"><?= number_format($stats['total_users']) ?></div>
            </div>
            <div class="bookings-section" style="text-align:center; padding:1.25rem;">
                <div style="font-size:0.8rem; text-transform:uppercase; color:#7f8c8d; font-weight:600; letter-spacing:0.5px;">Active Today</div>
                <div style="font-size:1.8rem; font-weight:800; color:#27ae60; margin-top:0.25rem;"><?= number_format($stats['active_today']) ?></div>
            </div>
        </div>

        <section class="bookings-section">
            <h2>All Users</h2>
            <div class="table-wrap">
                <table class="bookings-table">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Security</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?= (int)$u['id'] ?></td>
                                <td><?= htmlspecialchars($u['Name'] ?? $u['name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($u['Email'] ?? $u['email'] ?? '') ?></td>
                                <td><span class="status status-accepted">Hash Secured</span></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" style="text-align:center; color:#7f8c8d;">No user records found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
