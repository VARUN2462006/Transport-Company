<?php
session_start();
if (empty($_SESSION['admin_user_id'])) {
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/../Public/connect.php';

$has_price_per_ton = false;
$res3 = $conn->query("SHOW COLUMNS FROM truck_rates LIKE 'price_per_ton'");
if ($res3 && $res3->num_rows > 0) {
    $has_price_per_ton = true;
}

$trucks = [];
$truck_cols = "id, truck_key, price_per_km";
if ($has_price_per_ton) $truck_cols .= ", price_per_ton";
$trucks_result = $conn->query("SELECT $truck_cols FROM truck_rates ORDER BY id");
if ($trucks_result) {
    while ($row = $trucks_result->fetch_assoc()) {
        if (!isset($row['price_per_ton'])) $row['price_per_ton'] = 0;
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
    <link rel="stylesheet" href="admin_truck_rates.css">
    <title>Truck Rates – Admin</title>
</head>
<body>
    <header class="admin-header">
        <h1>Admin Panel</h1>
        <nav class="admin-nav">
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="bookings.php">Bookings</a>
            <a href="truck_rates.php" class="active">Truck Rates</a>
            <a href="index.php?logout=1" class="logout-btn">Logout</a>
        </nav>
    </header>

    <?php if ($msg === 'price_updated'): ?>
        <p class="flash flash-success">Truck rate updated.</p>
    <?php elseif ($msg === 'invalid_price'): ?>
        <p class="flash flash-error">Invalid request.</p>
    <?php endif; ?>

    <main class="rates-main">
        <section class="rates-section">
            <h2>Truck rates – overall cost by distance & weight</h2>
            <p class="formula-desc">Cost = (₹/km × distance) + (₹/ton × weight). Update both rates below.</p>
            <div class="table-wrap">
                <table class="rates-table">
                    <thead>
                        <tr>
                            <th>Truck</th>
                            <th>₹/km</th>
                            <?php if ($has_price_per_ton): ?><th>₹/ton</th><?php endif; ?>
                            <th>Update rates</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($trucks as $t): ?>
                        <tr>
                            <td class="truck-name"><?= htmlspecialchars(ucfirst(str_replace('-', ' ', $t['truck_key']))) ?></td>
                            <td>₹<?= number_format((float)$t['price_per_km'], 2) ?></td>
                            <?php if ($has_price_per_ton): ?><td>₹<?= number_format((float)$t['price_per_ton'], 2) ?></td><?php endif; ?>
                            <td>
                                <form class="rate-form" method="post" action="admin_update_price.php">
                                    <input type="hidden" name="truck_id" value="<?= (int)$t['id'] ?>">
                                    <label class="rate-label">₹/km</label>
                                    <input type="number" name="price_per_km" value="<?= (int)$t['price_per_km'] ?>" min="0" step="1" required>
                                    <?php if ($has_price_per_ton): ?>
                                    <label class="rate-label">₹/ton</label>
                                    <input type="number" name="price_per_ton" value="<?= (float)$t['price_per_ton'] ?>" min="0" step="0.01" required>
                                    <?php endif; ?>
                                    <button type="submit" class="btn btn-update">Update</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
