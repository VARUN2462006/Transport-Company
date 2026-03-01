<?php
require_once __DIR__ . '/../Comman Point/security_bootstrap.php';
if (empty($_SESSION['admin_user_id'])) {
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/../Comman Point/csrf_helper.php';
require_once __DIR__ . '/../connect.php';

// Fetch bookings with driver info
$bookings = [];
$bookings_result = $conn->query("SELECT b.*, d.name AS driver_name, d.phone AS driver_phone, d.salary AS driver_salary 
    FROM bookings b 
    LEFT JOIN drivers d ON b.driver_id = d.id 
    ORDER BY b.created_at DESC");
if ($bookings_result) {
    while ($row = $bookings_result->fetch_assoc()) {
        $bookings[] = $row;
    }
}

// Fetch available drivers for assignment
$available_drivers = [];
$drv_result = $conn->query("SELECT id, name, phone, salary, status FROM drivers WHERE status = 'Available' ORDER BY name");
if ($drv_result) {
    while ($d = $drv_result->fetch_assoc()) {
        $available_drivers[] = $d;
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
    <style>
        .driver-assign-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 10px;
            margin-top: 8px;
        }
        .driver-assign-box select {
            width: 100%;
            padding: 6px 8px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 0.85rem;
            margin-bottom: 6px;
        }
        .driver-info-badge {
            background: #e8f5e9;
            border: 1px solid #c8e6c9;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 0.82rem;
            margin-top: 6px;
        }
        .driver-info-badge strong { color: #2e7d32; }
        .driver-info-badge .phone { color: #555; }
        .driver-info-badge .salary { color: #e65100; font-weight: 600; }
        .btn-assign {
            background: #7c3aed;
            color: #fff;
            border: none;
            padding: 6px 14px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.85rem;
            width: 100%;
        }
        .btn-assign:hover { background: #6d28d9; }
    </style>
</head>
<body>
    <header class="admin-header">
        <h1>Admin Panel</h1>
        <nav class="admin-nav">
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="bookings.php" class="active">Bookings</a>
            <a href="truck_rates.php">Truck Rates</a>
            <a href="../Driver/admin_drivers.php">Drivers</a>
            <a href="index.php?logout=1" class="logout-btn">Logout</a>
        </nav>
    </header>

    <?php if ($msg === 'accept'): ?>
        <p class="flash flash-success">Booking #<?= (int)($_GET['id'] ?? 0) ?> accepted.</p>
    <?php elseif ($msg === 'reject'): ?>
        <p class="flash flash-error">Booking #<?= (int)($_GET['id'] ?? 0) ?> rejected.</p>
    <?php elseif ($msg === 'dispatch'): ?>
        <p class="flash flash-success">Booking #<?= (int)($_GET['id'] ?? 0) ?> dispatched with driver assigned.</p>
    <?php elseif ($msg === 'deliver'): ?>
        <p class="flash flash-success">Booking #<?= (int)($_GET['id'] ?? 0) ?> marked as delivered.</p>
    <?php elseif ($msg === 'assign'): ?>
        <p class="flash flash-success">Driver assigned to Booking #<?= (int)($_GET['id'] ?? 0) ?>.</p>
    <?php elseif ($msg === 'invalid'): ?>
        <p class="flash flash-error">Invalid request.</p>
    <?php endif; ?>

    <main class="bookings-main">
        <section class="bookings-section">
            <h2>Customer Bookings</h2>

            <div class="table-wrap">
                <table class="bookings-table">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Customer</th>
                            <th>Truck</th>
                            <th>Booking Date</th>
                            <th>Address</th>
                            <th>KM</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Assigned Driver</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td><?= (int)$b['id'] ?></td>
                            <td><?= htmlspecialchars($b['customer_name']) ?></td>
                            <td><?= htmlspecialchars($b['truck_name']) ?></td>
                            <td><?= date('d M Y', strtotime($b['booking_date'])) ?></td>
                            <td><?= htmlspecialchars($b['address']) ?></td>
                            <td><?= (int)$b['distance_km'] ?></td>
                            <td>₹<?= number_format((float)$b['total_cost'], 2) ?></td>
                            <td><span class="status status-<?= htmlspecialchars($b['status']) ?>"><?= htmlspecialchars($b['status']) ?></span></td>
                            <td>
                                <?php if (!empty($b['driver_name'])): ?>
                                    <div class="driver-info-badge">
                                        <strong><?= htmlspecialchars($b['driver_name']) ?></strong><br>
                                        <span class="phone">📞 <?= htmlspecialchars($b['driver_phone']) ?></span><br>
                                        <span class="salary">₹<?= number_format((float)$b['driver_salary'], 0) ?>/mo</span>
                                    </div>
                                <?php else: ?>
                                    <span style="color:#999;">Not assigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($b['status'] === 'pending'): ?>
                                <div style="display:flex; gap:0.5rem; flex-direction:column;">
                                    <form class="action-form" method="post" action="admin_booking_action.php">
                                        <?= csrf_input_field() ?>
                                        <input type="hidden" name="booking_id" value="<?= (int)$b['id'] ?>">
                                        <input type="hidden" name="action" value="accept">
                                        <input type="hidden" name="source" value="bookings">
                                        <button type="submit" class="btn btn-accept" style="width:100%;">Accept</button>
                                    </form>
                                    <form class="action-form" method="post" action="admin_booking_action.php">
                                        <?= csrf_input_field() ?>
                                        <input type="hidden" name="booking_id" value="<?= (int)$b['id'] ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <input type="hidden" name="source" value="bookings">
                                        <input type="text" name="reject_reason" placeholder="Reason..." class="reason-input" style="width:100%; margin-bottom:5px;">
                                        <button type="submit" class="btn btn-reject" style="width:100%;">Reject</button>
                                    </form>
                                </div>

                                <?php elseif ($b['status'] === 'accepted'): ?>
                                <!-- Assign Driver + Dispatch -->
                                <form class="action-form" method="post" action="admin_booking_action.php">
                                    <?= csrf_input_field() ?>
                                    <input type="hidden" name="booking_id" value="<?= (int)$b['id'] ?>">
                                    <input type="hidden" name="action" value="dispatch">
                                    <input type="hidden" name="source" value="bookings">
                                    <div class="driver-assign-box">
                                        <label style="font-weight:600; font-size:0.8rem; color:#555;">Assign Driver:</label>
                                        <select name="driver_id" required>
                                            <option value="">-- Select Driver --</option>
                                            <?php foreach ($available_drivers as $d): ?>
                                                <option value="<?= $d['id'] ?>" <?= (!empty($b['driver_id']) && $b['driver_id'] == $d['id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($d['name']) ?> (<?= htmlspecialchars($d['phone']) ?>) — ₹<?= number_format($d['salary'], 0) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-accept" style="background-color: #3498db; width:100%;">🚚 Dispatch with Driver</button>
                                    </div>
                                </form>

                                <?php elseif ($b['status'] === 'on_the_way'): ?>
                                <form class="action-form" method="post" action="admin_booking_action.php">
                                    <?= csrf_input_field() ?>
                                    <input type="hidden" name="booking_id" value="<?= (int)$b['id'] ?>">
                                    <input type="hidden" name="action" value="deliver">
                                    <input type="hidden" name="source" value="bookings">
                                    <button type="submit" class="btn" style="background-color: #27ae60; color:white; width:100%;">✅ Mark Delivered</button>
                                </form>

                                <?php elseif ($b['status'] === 'delivered'): ?>
                                    <span style="color: #27ae60; font-weight: bold;">✔ Job Complete</span>

                                <?php elseif ($b['status'] === 'rejected'): ?>
                                    <span class="reject-reason-text"><?= htmlspecialchars($b['reject_reason'] ?? 'No reason given') ?></span>

                                <?php else: ?>
                                —
                                <?php endif; ?>
                            </td>
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
