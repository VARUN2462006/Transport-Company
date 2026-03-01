<?php
require_once __DIR__ . '/../Comman Point/security_bootstrap.php';
if (empty($_SESSION['admin_user_id'])) {
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/../connect.php';

// Fetch Prices Stats
$stats = [
    'avg_km' => 0,
    'avg_ton' => 0,
    'count' => 0
];

$rates = [];
$result = $conn->query("SELECT * FROM truck_rates ORDER BY id ASC");
if ($result) {
    $total_km = 0;
    $total_ton = 0;
    while ($row = $result->fetch_assoc()) {
        $stats['count']++;
        $total_km += $row['price_per_km'];
        $total_ton += $row['price_per_ton'];
        $rates[] = $row;
    }
    if ($stats['count'] > 0) {
        $stats['avg_km'] = $total_km / $stats['count'];
        $stats['avg_ton'] = $total_ton / $stats['count'];
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Truck Prices Report</title>
    <link rel="stylesheet" href="admin_bookings.css?v=2.0">
</head>
<body>
    <header class="admin-header">
        <h1>Truck Prices Report</h1>
        <nav class="admin-nav">
            <a href="report.php">← Back to Reports</a>
            <button onclick="window.print()" class="logout-btn" style="border:none; cursor:pointer; font-family:inherit; font-size:inherit;">🖨 Print</button>
        </nav>
    </header>

    <main class="bookings-main">
        <!-- Stats Row -->
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px,1fr)); gap:1rem; margin-bottom:1.5rem;">
            <div class="bookings-section" style="text-align:center; padding:1.25rem;">
                <div style="font-size:0.8rem; text-transform:uppercase; color:#7f8c8d; font-weight:600; letter-spacing:0.5px;">Total Rate Cards</div>
                <div style="font-size:1.8rem; font-weight:800; color:#1a1a1a; margin-top:0.25rem;"><?= $stats['count'] ?></div>
            </div>
            <div class="bookings-section" style="text-align:center; padding:1.25rem;">
                <div style="font-size:0.8rem; text-transform:uppercase; color:#7f8c8d; font-weight:600; letter-spacing:0.5px;">Avg Rate / KM</div>
                <div style="font-size:1.8rem; font-weight:800; color:#1a1a1a; margin-top:0.25rem;">₹<?= number_format($stats['avg_km'], 2) ?></div>
            </div>
            <div class="bookings-section" style="text-align:center; padding:1.25rem;">
                <div style="font-size:0.8rem; text-transform:uppercase; color:#7f8c8d; font-weight:600; letter-spacing:0.5px;">Avg Rate / Ton</div>
                <div style="font-size:1.8rem; font-weight:800; color:#1a1a1a; margin-top:0.25rem;">₹<?= number_format($stats['avg_ton'], 2) ?></div>
            </div>
        </div>

        <section class="bookings-section">
            <h2>Rate Card</h2>
            <div class="table-wrap">
                <table class="bookings-table">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Truck Type</th>
                            <th>Truck Key</th>
                            <th>Rate per KM</th>
                            <th>Rate per Ton</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($rates)): ?>
                            <?php foreach ($rates as $r): ?>
                            <tr>
                                <td><?= (int)$r['id'] ?></td>
                                <td><strong><?= htmlspecialchars(ucfirst(str_replace('-', ' ', $r['truck_key']))) ?></strong></td>
                                <td><?= htmlspecialchars($r['truck_key']) ?></td>
                                <td>₹<?= number_format($r['price_per_km'], 2) ?></td>
                                <td>₹<?= number_format($r['price_per_ton'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align:center; color:#7f8c8d;">No rate cards found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
