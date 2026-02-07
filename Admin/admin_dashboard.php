<?php
session_start();
if (empty($_SESSION['admin_user_id'])) {
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/../Public/connect.php';

// Check if bookings has status column (for accept/reject)
$has_status = false;
$res = $conn->query("SHOW COLUMNS FROM bookings LIKE 'status'");
if ($res && $res->num_rows > 0) {
    $has_status = true;
}

$bookings = [];
$bookings_result = $conn->query("SELECT id, customer_name, truck_name, booking_date, address, distance_km, price_per_km, total_cost, created_at" . ($has_status ? ", status" : "") . " FROM bookings ORDER BY created_at DESC");
if ($bookings_result) {
    while ($row = $bookings_result->fetch_assoc()) {
        if (!$has_status) {
            $row['status'] = 'pending';
        }
        $bookings[] = $row;
    }
}

$trucks = [];
$trucks_result = $conn->query("SELECT id, truck_key, price_per_km FROM truck_rates ORDER BY id");
if ($trucks_result) {
    while ($row = $trucks_result->fetch_assoc()) {
        $trucks[] = $row;
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
    <link rel="stylesheet" href="admin_dashboard.css">
    <title>Admin Dashboard</title>
</head>
<body>
    <header class="dashboard-header">
        <h1>Admin Dashboard</h1>
        <p>Welcome, User Id: <strong><?= htmlspecialchars($_SESSION['admin_user_id']) ?></strong></p>
        <a href="index.php?logout=1" class="logout-btn">Logout</a>
    </header>

    <?php if ($msg === 'accept'): ?>
        <p class="flash flash-success">Booking #<?= (int)$_GET['id'] ?> accepted.</p>
    <?php elseif ($msg === 'reject'): ?>
        <p class="flash flash-error">Booking #<?= (int)$_GET['id'] ?> rejected.</p>
    <?php elseif ($msg === 'price_updated'): ?>
        <p class="flash flash-success">Truck price updated.</p>
    <?php elseif ($msg === 'invalid' || $msg === 'invalid_price'): ?>
        <p class="flash flash-error">Invalid request.</p>
    <?php endif; ?>

    <main class="dashboard-main">
        <section class="section">
            <h2>Customer Bookings</h2>
            <?php if (!$has_status): ?>
                <p class="notice">Run <code>admin_migrations.sql</code> in phpMyAdmin to enable Accept/Reject.</p>
            <?php endif; ?>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Customer</th>
                            <th>Truck</th>
                            <th>Date</th>
                            <th>Address</th>
                            <th>KM</th>
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
                            <td>₹<?= number_format((float)$b['price_per_km'], 2) ?></td>
                            <td>₹<?= number_format((float)$b['total_cost'], 2) ?></td>
                            <?php if ($has_status): ?>
                            <td><span class="status status-<?= htmlspecialchars($b['status']) ?>"><?= htmlspecialchars($b['status']) ?></span></td>
                            <td>
                                <?php if ($b['status'] === 'pending'): ?>
                                <form class="inline-form" method="post" action="admin_booking_action.php">
                                    <input type="hidden" name="booking_id" value="<?= (int)$b['id'] ?>">
                                    <input type="hidden" name="action" value="accept">
                                    <button type="submit" class="btn btn-accept">Accept</button>
                                </form>
                                <form class="inline-form" method="post" action="admin_booking_action.php">
                                    <input type="hidden" name="booking_id" value="<?= (int)$b['id'] ?>">
                                    <input type="hidden" name="action" value="reject">
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

        <section class="section">
            <h2>Truck Rates (₹ per km)</h2>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Truck</th>
                            <th>Current rate (₹/km)</th>
                            <th>New rate</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($trucks as $t): ?>
                        <tr>
                            <td><?= htmlspecialchars(ucfirst(str_replace('-', ' ', $t['truck_key']))) ?></td>
                            <td>₹<?= (int)$t['price_per_km'] ?></td>
                            <td>
                                <form class="inline-form rate-form" method="post" action="admin_update_price.php">
                                    <input type="hidden" name="truck_id" value="<?= (int)$t['id'] ?>">
                                    <input type="number" name="price_per_km" value="<?= (int)$t['price_per_km'] ?>" min="0" step="1" required>
                                    <button type="submit" class="btn btn-update">Update</button>
                                </form>
                            </td>
                            <td>—</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
