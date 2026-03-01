<?php
require_once __DIR__ . '/../Comman Point/security_bootstrap.php';
if (empty($_SESSION['admin_user_id'])) {
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/../connect.php';

// Load fleet directly from truck_rates so all newly added trucks are shown
$fleet = [];
$result = $conn->query("SELECT truck_key, name, description, image_path, capacity_ton, price_per_km, price_per_ton FROM truck_rates ORDER BY id ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $fleet[] = [
            'key' => $row['truck_key'],
            'name' => $row['name'] ?: ucfirst(str_replace('-', ' ', $row['truck_key'])),
            'description' => $row['description'] ?? '',
            'image' => $row['image_path'] ?? '',
            'capacity_ton' => (float)($row['capacity_ton'] ?? 0),
            'price_km' => (float)($row['price_per_km'] ?? 0),
            'price_ton' => (float)($row['price_per_ton'] ?? 0),
        ];
    }
}
$total_capacity = 0.0;
foreach ($fleet as $t) { $total_capacity += (float)$t['capacity_ton']; }
$rate_cards_count = 0;
foreach ($fleet as $t) {
    if (($t['price_km'] ?? 0) > 0 || ($t['price_ton'] ?? 0) > 0) {
        $rate_cards_count++;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Truck Inventory Report</title>
    <link rel="stylesheet" href="admin_bookings.css?v=2.0">
</head>
<body>
    <header class="admin-header">
        <h1>Truck Inventory Report</h1>
        <nav class="admin-nav">
            <a href="report.php">← Back to Reports</a>
            <button onclick="window.print()" class="logout-btn" style="border:none; cursor:pointer; font-family:inherit; font-size:inherit;">🖨 Print</button>
        </nav>
    </header>

    <main class="bookings-main">
        <!-- Stats Row -->
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px,1fr)); gap:1rem; margin-bottom:1.5rem;">
            <div class="bookings-section" style="text-align:center; padding:1.25rem;">
                <div style="font-size:0.8rem; text-transform:uppercase; color:#7f8c8d; font-weight:600; letter-spacing:0.5px;">Fleet Size</div>
                <div style="font-size:1.8rem; font-weight:800; color:#1a1a1a; margin-top:0.25rem;"><?= count($fleet) ?> Trucks</div>
            </div>
            <div class="bookings-section" style="text-align:center; padding:1.25rem;">
                <div style="font-size:0.8rem; text-transform:uppercase; color:#7f8c8d; font-weight:600; letter-spacing:0.5px;">Total Capacity</div>
                <div style="font-size:1.8rem; font-weight:800; color:#1a1a1a; margin-top:0.25rem;"><?= number_format($total_capacity, 1) ?> Tons</div>
            </div>
            <div class="bookings-section" style="text-align:center; padding:1.25rem;">
                <div style="font-size:0.8rem; text-transform:uppercase; color:#7f8c8d; font-weight:600; letter-spacing:0.5px;">Rate Cards</div>
                <div style="font-size:1.8rem; font-weight:800; color:#27ae60; margin-top:0.25rem;"><?= (int)$rate_cards_count ?> Active</div>
            </div>
        </div>

        <section class="bookings-section">
            <h2>Fleet Inventory</h2>
            <div class="table-wrap">
                <table class="bookings-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Truck</th>
                            <th>Description</th>
                            <th>Capacity</th>
                            <th>₹/km</th>
                            <th>₹/ton</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fleet as $i => $t): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><strong><?= htmlspecialchars($t['name']) ?></strong></td>
                            <td><?= htmlspecialchars($t['description']) ?></td>
                            <td><?= number_format($t['capacity_ton'], 1) ?> Tons</td>
                            <td><?= $t['price_km'] > 0 ? '₹' . number_format($t['price_km'], 2) : '—' ?></td>
                            <td><?= $t['price_ton'] > 0 ? '₹' . number_format($t['price_ton'], 2) : '—' ?></td>
                            <td><span class="status status-accepted">Active</span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
