<?php
require_once __DIR__ . '/../Comman Point/security_bootstrap.php';
if (empty($_SESSION['admin_user_id'])) {
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/../Comman Point/csrf_helper.php';
require_once '../connect.php';

// Load company fleet catalog from truck_rates (shown on website)
$fleet_rows = [];
try {
    $fleet_q = $conn->query("SELECT id, truck_key, name, description, capacity_ton, price_per_km, price_per_ton FROM truck_rates ORDER BY id ASC");
    if ($fleet_q) {
        while ($row = $fleet_q->fetch_assoc()) {
            $fleet_rows[] = $row;
        }
    }
} catch (mysqli_sql_exception $e) {}

// Fetch registered trucks from DB (if table exists)
$trucks_result = null;
try {
    $trucks_result = $conn->query("SELECT * FROM trucks ORDER BY id DESC");
} catch (mysqli_sql_exception $e) {
    // trucks table doesn't exist — that's okay
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Trucks - Admin Panel</title>
    <link rel="stylesheet" href="admin_trucks.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="dashboard-header">
        <h1>Truck Fleet Management</h1>
        <div class="nav-links">
            <a href="admin_dashboard.php" class="nav-btn"><i class="fas fa-arrow-left"></i> Back</a>
            <a href="index.php?logout=1" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </header>

    <div class="container">
        <!-- Sidebar / Forms -->
        <div class="sidebar">
            <?php
            if(isset($_SESSION['msg'])) {
                echo "<div class='alert success'><i class='fas fa-check-circle'></i> " . htmlspecialchars($_SESSION['msg']) . "</div>";
                unset($_SESSION['msg']);
            }
            if(isset($_SESSION['error_msg'])) {
                echo "<div class='alert error'><i class='fas fa-exclamation-circle'></i> " . htmlspecialchars($_SESSION['error_msg']) . "</div>";
                unset($_SESSION['error_msg']);
            }
            ?>

            <!-- Add Truck (Catalog) -->
            <div class="form-card" style="margin-bottom: 1.5rem;">
                <h2><i class="fas fa-truck" style="color:#ff7300; margin-right:8px;"></i>Add Truck</h2>
                <p style="font-size:0.85rem;color:#7f8c8d;margin-top:-6px;margin-bottom:10px;">This appears on the Services page. Include image & pricing.</p>
                <form action="admin_truck_action.php" method="POST" enctype="multipart/form-data">
                    <?= csrf_input_field() ?>
                    <input type="hidden" name="action" value="add_model">
                    <div class="form-group">
                        <label>Public Model Name</label>
                        <input type="text" name="name" required placeholder="e.g. Tata Ace Gold">
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" name="description" placeholder="Short description (optional)">
                    </div>
                    <div class="form-group">
                        <label>Features (comma separated)</label>
                        <input type="text" name="features" placeholder="e.g. 1 Ton, City delivery">
                    </div>
                    <div class="form-group">
                        <label>Capacity</label>
                        <div style="display:flex; gap:8px; align-items:center;">
                            <input type="number" step="0.01" min="0.1" name="capacity_qty" required placeholder="e.g. 1.0" style="flex:1;">
                            <select name="capacity_unit" style="padding: 10px;">
                                <option value="ton" selected>ton</option>
                                <option value="kg">kg</option>
                                <option value="lb">lb</option>
                            </select>
                        </div>
                        <small style="display:block; color:#7f8c8d; margin-top:6px;">Saved internally as tons.</small>
                    </div>
                    <div class="form-group">
                        <label>Price per KM (₹)</label>
                        <input type="number" step="1" min="0" name="price_per_km" required placeholder="e.g. 25">
                    </div>
                    <div class="form-group">
                        <label>Price per Ton (₹)</label>
                        <input type="number" step="0.01" min="0" name="price_per_ton" required placeholder="e.g. 0">
                    </div>
                    <div class="form-group">
                        <label>Display Image</label>
                        <input type="file" name="image" accept="image/*">
                        <small style="display:block;color:#7f8c8d;margin-top:4px;">Accepted: jpg, jpeg, png, webp, avif</small>
                    </div>
                    <button type="submit" class="submit-btn" style="background:#ff7300;"><i class="fas fa-plus" style="margin-right:5px;"></i> Add Truck</button>
                </form>
            </div>

            <!-- Removed internal inventory add form by request -->
        </div>

        <!-- Main Content Area -->
        <div class="main-content">

            <!-- ===== COMPANY FLEET (Website Catalog from truck_rates) ===== -->
            <div class="table-card" style="margin-bottom: 2rem;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <h2><i class="fas fa-truck-moving" style="color:#ff7300; margin-right:8px;"></i>Company Fleet (Service Trucks)</h2>
                    <a href="edit_truck_rate.php?id=0" class="nav-btn" style="text-decoration:none;"><i class="fas fa-plus-circle"></i> Add New Model</a>
                </div>
                <p style="font-size: 0.85rem; color: #7f8c8d; margin: 0.5rem 0 1rem;">These are the trucks listed on the website for customer bookings. Edit or add new models in “Manage Truck Rates”.</p>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Truck Name</th>
                                <th>Description</th>
                                <th>Capacity</th>
                                <th>₹/km</th>
                                <th>₹/ton</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($fleet_rows)): ?>
                            <?php foreach ($fleet_rows as $i => $t): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><strong><?= htmlspecialchars($t['name']) ?></strong><br><small style="color:#7f8c8d;"><?= htmlspecialchars($t['truck_key']) ?></small></td>
                                <td><?= htmlspecialchars($t['description']) ?></td>
                                <td><?= number_format((float)$t['capacity_ton'], 1) ?> Tons</td>
                                <td><?= '₹' . number_format((float)$t['price_per_km'], 2) ?></td>
                                <td><?= '₹' . number_format((float)$t['price_per_ton'], 2) ?></td>
                                <td><span class="status-badge status-available">Active</span></td>
                                <td><a href="edit_truck_rate.php?id=<?= (int)$t['id'] ?>" class="btn-small"><i class="fas fa-edit"></i></a></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align:center; padding:1.25rem; color:#7f8c8d;">
                                    No models are defined yet. Use “Add Truck” to create your first truck.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- ===== REGISTERED TRUCKS (From Database) ===== -->
            <div class="table-card">
                <h2><i class="fas fa-truck" style="color:#2c3e50; margin-right:8px;"></i>Registered Trucks Inventory</h2>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Truck No.</th>
                                <th>Details</th>
                                <th>Driver Info</th>
                                <th>Status</th>
                                <th>Update</th>
                                <th>Remove</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($trucks_result && $trucks_result->num_rows > 0): ?>
                                <?php while($row = $trucks_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><div class="plate-number"><?= htmlspecialchars($row['truck_number']) ?></div></td>
                                        <td>
                                            <strong><?= htmlspecialchars($row['truck_model']) ?></strong><br>
                                            <span style="color:#7f8c8d; font-size:0.85rem;"><?= htmlspecialchars($row['truck_type']) ?></span>
                                        </td>
                                        <td>
                                            <?php if($row['driver_name']): ?>
                                                <?= htmlspecialchars($row['driver_name']) ?><br>
                                                <small><i class="fas fa-phone-alt" style="color:#95a5a6; font-size:0.75rem; margin-right:3px;"></i><?= htmlspecialchars($row['driver_phone']) ?></small>
                                            <?php else: ?>
                                                <span style="color:#bdc3c7; font-style:italic;">Unassigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $badge_class = 'available';
                                            if($row['status'] == 'in_transit') $badge_class = 'transit';
                                            if($row['status'] == 'maintenance') $badge_class = 'maintenance';
                                            ?>
                                            <span class="status-badge status-<?= $badge_class ?>">
                                                <?php 
                                                  if($row['status'] == 'in_transit') echo 'In Transit';
                                                  else if($row['status'] == 'maintenance') echo 'Maintenance';
                                                  else echo 'Available';
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form action="admin_truck_action.php" method="POST" class="action-form">
                                                <?= csrf_input_field() ?>
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="truck_id" value="<?= $row['id'] ?>">
                                                <select name="new_status" style="margin-bottom: 5px;">
                                                    <option value="available" <?= $row['status'] == 'available' ? 'selected' : '' ?>>Available</option>
                                                    <option value="in_transit" <?= $row['status'] == 'in_transit' ? 'selected' : '' ?>>In Transit</option>
                                                    <option value="maintenance" <?= $row['status'] == 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                                                </select>
                                                <button type="submit" class="btn-small"><i class="fas fa-sync-alt"></i></button>
                                            </form>
                                        </td>
                                        <td>
                                            <form action="admin_truck_action.php" method="POST" class="action-form" onsubmit="return confirm('WARNING: Are you sure you want to completely remove this truck from inventory?');">
                                                <?= csrf_input_field() ?>
                                                <input type="hidden" name="action" value="delete_truck">
                                                <input type="hidden" name="truck_id" value="<?= $row['id'] ?>">
                                                <button type="submit" class="btn-small delete-btn"><i class="fas fa-trash-alt"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" style="text-align:center; padding:2rem; color:#7f8c8d;">No additional trucks registered.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </div>
</body>
</html>

