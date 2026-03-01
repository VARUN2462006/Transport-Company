<?php
require_once __DIR__ . '/../Comman Point/security_bootstrap.php';
if (empty($_SESSION['admin_user_id'])) {
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/../connect.php';

// Fetch Booking Stats
$stats = [
    'total' => 0,
    'pending' => 0,
    'accepted' => 0,
    'rejected' => 0,
    'revenue' => 0
];

$result = $conn->query("SELECT status, total_cost FROM bookings");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $stats['total']++;
        if ($row['status'] === 'pending') $stats['pending']++;
        elseif ($row['status'] === 'accepted') {
            $stats['accepted']++;
            $stats['revenue'] += $row['total_cost'];
        }
        elseif ($row['status'] === 'rejected') $stats['rejected']++;
    }
}

// Fetch all bookings
$bookings = [];
$listResult = $conn->query("SELECT * FROM bookings ORDER BY id DESC");
if ($listResult) {
    while ($row = $listResult->fetch_assoc()) {
        $bookings[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings Report</title>
    <link rel="stylesheet" href="admin_bookings.css?v=2.0">
</head>
<body>
    <header class="admin-header">
        <h1>Bookings Report</h1>
        <nav class="admin-nav">
            <a href="report.php">← Back to Reports</a>
            <button onclick="window.print()" class="logout-btn" style="border:none; cursor:pointer; font-family:inherit; font-size:inherit;">🖨 Print</button>
        </nav>
    </header>

    <main class="bookings-main">
        <!-- Stats Row -->
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px,1fr)); gap:1rem; margin-bottom:1.5rem;">
            <div class="bookings-section" style="text-align:center; padding:1.25rem;">
                <div style="font-size:0.8rem; text-transform:uppercase; color:#7f8c8d; font-weight:600; letter-spacing:0.5px;">Total Revenue</div>
                <div style="font-size:1.8rem; font-weight:800; color:#1a1a1a; margin-top:0.25rem;">₹<?= number_format($stats['revenue'], 2) ?></div>
            </div>
            <div class="bookings-section" style="text-align:center; padding:1.25rem;">
                <div style="font-size:0.8rem; text-transform:uppercase; color:#7f8c8d; font-weight:600; letter-spacing:0.5px;">Total Bookings</div>
                <div style="font-size:1.8rem; font-weight:800; color:#1a1a1a; margin-top:0.25rem;"><?= number_format($stats['total']) ?></div>
            </div>
            <div class="bookings-section" style="text-align:center; padding:1.25rem;">
                <div style="font-size:0.8rem; text-transform:uppercase; color:#7f8c8d; font-weight:600; letter-spacing:0.5px;">Pending</div>
                <div style="font-size:1.8rem; font-weight:800; color:#f39c12; margin-top:0.25rem;"><?= number_format($stats['pending']) ?></div>
            </div>
            <div class="bookings-section" style="text-align:center; padding:1.25rem;">
                <div style="font-size:0.8rem; text-transform:uppercase; color:#7f8c8d; font-weight:600; letter-spacing:0.5px;">Accepted</div>
                <div style="font-size:1.8rem; font-weight:800; color:#27ae60; margin-top:0.25rem;"><?= number_format($stats['accepted']) ?></div>
            </div>
        </div>

        <section class="bookings-section">
            <h2>All Bookings</h2>
            <div class="table-wrap">
                <table class="bookings-table">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Customer</th>
                            <th>Truck</th>
                            <th>Order Placed</th>
                            <th>Booking Date</th>
                            <th>Address</th>
                            <th>KM</th>
                            <th>Ton</th>
                            <th>₹/km</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($bookings)): ?>
                            <?php foreach ($bookings as $b): ?>
                            <tr>
                                <td><?= (int)$b['id'] ?></td>
                                <td><?= htmlspecialchars($b['customer_name']) ?></td>
                                <td><?= htmlspecialchars($b['truck_name']) ?></td>
                                <td><?= date('D, d M Y', strtotime($b['created_at'])) ?></td>
                                <td><?= date('D, d M Y', strtotime($b['booking_date'])) ?></td>
                                <td><?= htmlspecialchars($b['address']) ?></td>
                                <td><?= (int)$b['distance_km'] ?></td>
                                <td><?= $b['weight_ton'] !== null ? number_format((float)$b['weight_ton'], 2) : '—' ?></td>
                                <td>₹<?= number_format((float)$b['price_per_km'], 2) ?></td>
                                <td>₹<?= number_format((float)$b['total_cost'], 2) ?></td>
                                <td><span class="status status-<?= htmlspecialchars($b['status']) ?>"><?= ucfirst(htmlspecialchars($b['status'])) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="11" style="text-align:center; color:#7f8c8d;">No bookings records found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
