<?php
require_once __DIR__ . '/../Comman Point/security_bootstrap.php';
if (empty($_SESSION['driver_id'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../connect.php';

$driver_id = $_SESSION['driver_id'];
$driver_name = $_SESSION['driver_name'];

// Fetch fresh driver info
$stmt = $conn->prepare("SELECT * FROM drivers WHERE id = ?");
$stmt->bind_param("i", $driver_id);
$stmt->execute();
$driver = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$driver) {
    // Driver was deleted
    session_destroy();
    header('Location: login.php');
    exit;
}

$_SESSION['driver_status'] = $driver['status'];

// Fetch leave requests for this driver
$leaves = [];
$stmt = $conn->prepare("SELECT * FROM driver_leaves WHERE driver_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->bind_param("i", $driver_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $leaves[] = $row;
}
$stmt->close();

// Count stats
$pending_leaves = 0;
$approved_leaves = 0;
foreach ($leaves as $l) {
    if ($l['status'] === 'Pending') $pending_leaves++;
    if ($l['status'] === 'Approved') $approved_leaves++;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Employee Portal</title>
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
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="request_leave.php">Request Leave</a>
            <a href="contact.php">Contact</a>
            <a href="logout.php" class="logout-link">Logout</a>
        </nav>
    </header>

    <main class="emp-main">
        <!-- Welcome Greeting -->
        <div class="content-card" style="border-left: 4px solid #ff7300; margin-bottom: 1.5rem;">
            <h2 style="border-bottom: none; margin-bottom: 0.25rem;">👋 Welcome, <?= htmlspecialchars($driver_name) ?>!</h2>
            <p style="color: #7f8c8d; font-size: 0.9rem;">
                Your current status: 
                <?php
                $badgeClass = 'badge-available';
                if ($driver['status'] === 'On Trip') $badgeClass = 'badge-on-trip';
                elseif ($driver['status'] === 'On Leave') $badgeClass = 'badge-on-leave';
                ?>
                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($driver['status']) ?></span>
            </p>
        </div>

        <!-- Stat Cards -->
        <div class="stat-row">
            <div class="stat-card">
                <div class="stat-label">My Status</div>
                <div class="stat-value <?= $driver['status'] === 'Available' ? 'green' : ($driver['status'] === 'On Trip' ? 'blue' : 'orange') ?>"><?= htmlspecialchars($driver['status']) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Monthly Salary</div>
                <div class="stat-value">₹<?= number_format($driver['salary'] ?? 0, 2) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Pending Leaves</div>
                <div class="stat-value orange"><?= $pending_leaves ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Approved Leaves</div>
                <div class="stat-value green"><?= $approved_leaves ?></div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="request_leave.php" class="action-card">
                <div class="action-icon">📝</div>
                <div>
                    <h3>Request Leave</h3>
                    <p>Submit a new leave request</p>
                </div>
            </a>
            <a href="contact.php" class="action-card">
                <div class="action-icon">📞</div>
                <div>
                    <h3>Contact Company</h3>
                    <p>Emergency & office numbers</p>
                </div>
            </a>
        </div>

        <!-- My Info Card -->
        <div class="content-card">
            <h2>My Information</h2>
            <div class="table-wrap">
                <table class="emp-table">
                    <tr>
                        <th style="width: 180px;">Full Name</th>
                        <td><?= htmlspecialchars($driver['name']) ?></td>
                    </tr>
                    <tr>
                        <th>License Number</th>
                        <td><?= htmlspecialchars($driver['license_number']) ?></td>
                    </tr>
                    <tr>
                        <th>Phone</th>
                        <td><?= htmlspecialchars($driver['phone']) ?></td>
                    </tr>
                    <tr>
                        <th>Salary</th>
                        <td>₹<?= number_format($driver['salary'] ?? 0, 2) ?> / month</td>
                    </tr>
                    <tr>
                        <th>Employed Since</th>
                        <td><?= date('d M Y', strtotime($driver['created_at'])) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Leave History -->
        <div class="content-card">
            <h2>My Leave Requests</h2>
            <div class="table-wrap">
                <table class="emp-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Requested On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($leaves)): ?>
                            <?php foreach ($leaves as $i => $l): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($l['start_date']) ?></td>
                                <td><?= htmlspecialchars($l['end_date']) ?></td>
                                <td><?= htmlspecialchars($l['reason']) ?></td>
                                <td>
                                    <?php
                                    $cls = 'badge-pending';
                                    if ($l['status'] === 'Approved') $cls = 'badge-approved';
                                    elseif ($l['status'] === 'Rejected') $cls = 'badge-rejected';
                                    ?>
                                    <span class="badge <?= $cls ?>"><?= htmlspecialchars($l['status']) ?></span>
                                </td>
                                <td><?= date('d M Y', strtotime($l['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align:center; color:#7f8c8d;">No leave requests yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
