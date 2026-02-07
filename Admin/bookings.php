<?php
session_start();
if (empty($_SESSION['admin_user_id'])) {
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/../Public/connect.php';

$has_status = false;
$res = $conn->query("SHOW COLUMNS FROM bookings LIKE 'status'");
if ($res && $res->num_rows > 0) {
    $has_status = true;
}
$has_weight = false;
$res2 = $conn->query("SHOW COLUMNS FROM bookings LIKE 'weight_ton'");
if ($res2 && $res2->num_rows > 0) {
    $has_weight = true;
}

$bookings = [];
$cols = "id, customer_name, truck_name, booking_date, address, distance_km, price_per_km, total_cost, created_at";
if ($has_status) $cols .= ", status";
if ($has_weight) $cols .= ", weight_ton";
$bookings_result = $conn->query("SELECT $cols FROM bookings ORDER BY created_at DESC");
if ($bookings_result) {
    while ($row = $bookings_result->fetch_assoc()) {
        if (!$has_status) $row['status'] = 'pending';
        if (!$has_weight) $row['weight_ton'] = null;
        $bookings[] = $row;
    }
}

$msg = $_GET['msg'] ?? '';
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin_bookings.css">
    <title>Bookings – Admin</title>
</head>
<body>
    <header class="admin-header">
        <h1>Admin Panel</h1>
        <nav class="admin-nav">
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="bookings.php" class="active">Bookings</a>
            <a href="truck_rates.php">Truck Rates</a>
            <a href="index.php?logout=1" class="logout-btn">Logout</a>
        </nav>
    </header>

    <?php if ($msg === 'accept'): ?>
        <p class="flash flash-success">Booking #<?= (int)($_GET['id'] ?? 0) ?> accepted.</p>
    <?php elseif ($msg === 'reject'): ?>
        <p class="flash flash-error">Booking #<?= (int)($_GET['id'] ?? 0) ?> rejected.</p>
    <?php elseif ($msg === 'invalid'): ?>
        <p class="flash flash-error">Invalid request.</p>
    <?php endif; ?>

    <main class="bookings-main">
        <section class="bookings-section">
            <h2>Customer Bookings</h2>
            <?php if (!$has_status): ?>
                <p class="notice">Run <code>admin_migrations.sql</code> in phpMyAdmin to enable Accept/Reject.</p>
            <?php endif; ?>
            <div class="table-wrap">
                <table class="bookings-table">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Customer</th>
                            <th>Truck</th>
                            <th>Date</th>
                            <th>Address</th>
                            <th>KM</th>
                            <?php if ($has_weight): ?><th>Ton</th><?php endif; ?>
                            <th>₹/km</th>
                            <th>Total</th>
                            <?php if ($has_status): ?><th>Status</th><th>Action</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td><?= (int)$b['id'] ?></td>
                            <td><?= htmlspecialchars($b['customer_name']) ?></td>
                            <td><?= htmlspecialchars($b['truck_name']) ?></td>
                            <td><?= htmlspecialchars($b['booking_date']) ?></td>
                            <td><?= htmlspecialchars($b['address']) ?></td>
                            <td><?= (int)$b['distance_km'] ?></td>
                            <?php if ($has_weight): ?><td><?= $b['weight_ton'] !== null ? number_format((float)$b['weight_ton'], 2) : '—' ?></td><?php endif; ?>
                            <td>₹<?= number_format((float)$b['price_per_km'], 2) ?></td>
                            <td>₹<?= number_format((float)$b['total_cost'], 2) ?></td>
                            <?php if ($has_status): ?>
                            <td><span class="status status-<?= htmlspecialchars($b['status']) ?>"><?= htmlspecialchars($b['status']) ?></span></td>
                            <td>
                                <?php if ($b['status'] === 'pending'): ?>
                                <form class="action-form" method="post" action="admin_booking_action.php">
                                    <input type="hidden" name="booking_id" value="<?= (int)$b['id'] ?>">
                                    <input type="hidden" name="action" value="accept">
                                    <input type="hidden" name="source" value="bookings">
                                    <button type="submit" class="btn btn-accept">Accept</button>
                                </form>
                                <form class="action-form" method="post" action="admin_booking_action.php">
                                    <input type="hidden" name="booking_id" value="<?= (int)$b['id'] ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <input type="hidden" name="source" value="bookings">
                                    <button type="submit" class="btn btn-reject">Reject</button>
                                </form>
                                <?php else: ?>
                                —
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if (empty($bookings)): ?>
                <p class="empty">No bookings yet.</p>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
