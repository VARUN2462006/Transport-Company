<?php
require_once __DIR__ . '/../Comman Point/security_bootstrap.php';
if (empty($_SESSION['admin_user_id'])) {
    header('Location: ../index.php');
    exit;
}
require_once __DIR__ . '/../connect.php';

// Fetch Driver Stats
$stats = [
    'total_drivers' => 0,
    'active_drivers' => 0,
    'total_salary_monthly' => 0.00,
    'drivers_on_leave' => 0
];

$res = $conn->query("SELECT COUNT(*) as count, SUM(salary) as total_salary FROM drivers WHERE status != 'Fired'");
if ($res && $row = $res->fetch_assoc()) {
    $stats['total_drivers'] = $row['count'] ? $row['count'] : 0;
    $stats['total_salary_monthly'] = $row['total_salary'] ? $row['total_salary'] : 0.00;
}

$res = $conn->query("SELECT status, COUNT(*) as count FROM drivers WHERE status != 'Fired' GROUP BY status");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        if ($row['status'] == 'Available' || $row['status'] == 'On Trip') {
            $stats['active_drivers'] += $row['count'];
        } else if ($row['status'] == 'On Leave') {
            $stats['drivers_on_leave'] = $row['count'];
        }
    }
}

// Get all non-fired drivers
$drivers = [];
$drivers_result = $conn->query("SELECT * FROM drivers WHERE status != 'Fired' ORDER BY name ASC");
if ($drivers_result) {
    while ($row = $drivers_result->fetch_assoc()) {
        $drivers[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drivers Report</title>
    <link rel="stylesheet" href="admin_bookings.css?v=2.0">
</head>
<body>
    <header class="admin-header">
        <h1>Driver Staff Report</h1>
        <nav class="admin-nav">
            <a href="report.php">← Back to Reports</a>
            <button onclick="window.print()" class="logout-btn" style="border:none; cursor:pointer; font-family:inherit; font-size:inherit;">🖨 Print</button>
        </nav>
    </header>

    <main class="bookings-main">
        <!-- Stats Row -->
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px,1fr)); gap:1rem; margin-bottom:1.5rem;">
            <div class="bookings-section" style="text-align:center; padding:1.25rem;">
                <div style="font-size:0.8rem; text-transform:uppercase; color:#7f8c8d; font-weight:600; letter-spacing:0.5px;">Active Roster</div>
                <div style="font-size:1.8rem; font-weight:800; color:#1a1a1a; margin-top:0.25rem;"><?= number_format($stats['total_drivers']) ?></div>
            </div>
            <div class="bookings-section" style="text-align:center; padding:1.25rem;">
                <div style="font-size:0.8rem; text-transform:uppercase; color:#7f8c8d; font-weight:600; letter-spacing:0.5px;">On Duty</div>
                <div style="font-size:1.8rem; font-weight:800; color:#27ae60; margin-top:0.25rem;"><?= number_format($stats['active_drivers']) ?></div>
            </div>
            <div class="bookings-section" style="text-align:center; padding:1.25rem;">
                <div style="font-size:0.8rem; text-transform:uppercase; color:#7f8c8d; font-weight:600; letter-spacing:0.5px;">On Leave</div>
                <div style="font-size:1.8rem; font-weight:800; color:#f39c12; margin-top:0.25rem;"><?= number_format($stats['drivers_on_leave']) ?></div>
            </div>
            <div class="bookings-section" style="text-align:center; padding:1.25rem;">
                <div style="font-size:0.8rem; text-transform:uppercase; color:#7f8c8d; font-weight:600; letter-spacing:0.5px;">Monthly Payroll</div>
                <div style="font-size:1.8rem; font-weight:800; color:#1a1a1a; margin-top:0.25rem;">₹<?= number_format($stats['total_salary_monthly'], 2) ?></div>
            </div>
        </div>

        <section class="bookings-section">
            <h2>Driver Roster</h2>
            <div class="table-wrap">
                <table class="bookings-table">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Name</th>
                            <th>License</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Monthly Salary</th>
                            <th>Employed Since</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($drivers)): ?>
                            <?php foreach ($drivers as $d): ?>
                            <tr>
                                <td><?= (int)$d['id'] ?></td>
                                <td><?= htmlspecialchars($d['name']) ?></td>
                                <td><?= htmlspecialchars($d['license_number']) ?></td>
                                <td><?= htmlspecialchars($d['phone']) ?></td>
                                <td>
                                    <?php
                                    $sClass = 'status-pending';
                                    if ($d['status'] === 'Available') $sClass = 'status-accepted';
                                    elseif ($d['status'] === 'On Trip') $sClass = 'status-on_the_way';
                                    elseif ($d['status'] === 'On Leave') $sClass = 'status-pending';
                                    ?>
                                    <span class="status <?= $sClass ?>"><?= htmlspecialchars($d['status']) ?></span>
                                </td>
                                <td>₹<?= isset($d['salary']) ? number_format($d['salary'], 2) : '0.00' ?></td>
                                <td><?= date('d M Y', strtotime($d['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" style="text-align:center; color:#7f8c8d;">No active drivers found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
