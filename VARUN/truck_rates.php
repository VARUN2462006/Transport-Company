<?php
require_once __DIR__ . '/../Comman Point/security_bootstrap.php';
if (empty($_SESSION['admin_user_id'])) {
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/../Comman Point/csrf_helper.php';
require_once __DIR__ . '/../connect.php';

$trucks = [];
$trucks_result = $conn->query("SELECT * FROM truck_rates ORDER BY id");
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
    <link rel="stylesheet" href="admin_truck_rates.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
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

    <?php if ($msg === 'price_updated' || $msg === 'updated'): ?>
        <p class="flash flash-success">Truck model updated successfully.</p>
    <?php elseif ($msg === 'added'): ?>
        <p class="flash flash-success">New truck model added successfully.</p>
    <?php elseif ($msg === 'invalid_price'): ?>
        <p class="flash flash-error">Invalid request.</p>
    <?php endif; ?>

    <main class="rates-main">
        <section class="rates-section">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h2>Truck Models & Rates Catalog</h2>
                <a href="edit_truck_rate.php?id=0" class="btn" style="background:var(--success-color); color:white; text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
                    <i class="fas fa-plus"></i> Add New Model
                </a>
            </div>
            <p class="formula-desc">Manage the types of trucks you offer, their features, capacity, and pricing.</p>
            <div class="table-wrap">
                <table class="rates-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Truck Model</th>
                            <th>Capacity</th>
                            <th>₹/km</th>
                            <th>₹/ton</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($trucks as $t): ?>
                        <tr>
                            <td>
                                <?php if(!empty($t['image_path'])): ?>
                                    <img src="../Images/optimized/trucks/<?= htmlspecialchars($t['image_path']) ?>" style="width:60px; border-radius:4px; object-fit:cover;">
                                <?php else: ?>
                                    <span style="color:#999;font-size:0.8rem;">No Image</span>
                                <?php endif; ?>
                            </td>
                            <td class="truck-name">
                                <strong><?= htmlspecialchars($t['name'] ?? ucfirst($t['truck_key'])) ?></strong><br>
                                <small style="color:#7f8c8d;"><?= htmlspecialchars($t['truck_key']) ?></small>
                            </td>
                            <td><?= (float)($t['capacity_ton'] ?? 1.0) ?> Ton</td>
                            <td>₹<?= number_format((float)$t['price_per_km'], 2) ?></td>
                            <td>₹<?= number_format((float)$t['price_per_ton'], 2) ?></td>
                            <td>
                                <a href="edit_truck_rate.php?id=<?= $t['id'] ?>" class="btn btn-update" style="text-decoration:none; display:inline-block;">Edit Details</a>
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
