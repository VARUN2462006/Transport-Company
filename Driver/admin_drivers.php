<?php
require_once __DIR__ . '/../Comman Point/security_bootstrap.php';
if (empty($_SESSION['admin_user_id'])) {
    header('Location: ../VARUN/index.php');
    exit;
}
require_once __DIR__ . '/../Comman Point/csrf_helper.php';
require_once __DIR__ . '/../connect.php';

// Fetch all drivers
$drivers_result = $conn->query("SELECT * FROM drivers ORDER BY id DESC");

// Fetch pending leave requests
$leaves_result = $conn->query("SELECT dl.*, d.name AS driver_name FROM driver_leaves dl JOIN drivers d ON dl.driver_id = d.id WHERE dl.status = 'Pending' ORDER BY dl.created_at DESC");

// Fetch approved/rejected history
$leaves_history = $conn->query("SELECT dl.*, d.name AS driver_name FROM driver_leaves dl JOIN drivers d ON dl.driver_id = d.id WHERE dl.status != 'Pending' ORDER BY dl.created_at DESC LIMIT 10");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Drivers - Admin Panel</title>
    <link rel="stylesheet" href="admin_drivers.css">
</head>
<body>
    <header class="dashboard-header">
        <h1>Driver Management</h1>
        <div class="nav-links">
            <a href="../VARUN/admin_dashboard.php" class="nav-btn">Back to Dashboard</a>
            <a href="../VARUN/index.php?logout=1" class="logout-btn">Logout</a>
        </div>
    </header>

    <div class="container">
        <!-- Sidebar / Forms -->
        <div class="sidebar">
            <?php
            if(isset($_SESSION['msg'])) {
                echo "<div class='alert success'>" . htmlspecialchars($_SESSION['msg']) . "</div>";
                unset($_SESSION['msg']);
            }
            if(isset($_SESSION['error_msg'])) {
                echo "<div class='alert error'>" . htmlspecialchars($_SESSION['error_msg']) . "</div>";
                unset($_SESSION['error_msg']);
            }
            ?>

            <!-- Add new Driver form -->
            <div class="form-card" style="margin-bottom: 2rem;">
                <h2>Add New Driver</h2>
                <form action="admin_driver_action.php" method="POST">
                    <?= csrf_input_field() ?>
                    <input type="hidden" name="action" value="add_driver">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" required placeholder="e.g. Rahul Sharma">
                    </div>
                    <div class="form-group">
                        <label>License Number</label>
                        <input type="text" name="license" required placeholder="e.g. MH-12-2023-1234567">
                    </div>
                    <div class="form-group">
                        <label>Salary (₹)</label>
                        <input type="number" step="0.01" name="salary" required placeholder="e.g. 25000">
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" required placeholder="10-digit number">
                    </div>
                    <button type="submit" class="submit-btn">Add Driver</button>
                </form>
            </div>

            <!-- Simulate Leave Request Form (for demonstration purposes) -->
            <div class="form-card">
                <h2>Simulate Employee Portal</h2>
                <p style="font-size: 0.85rem; color: #7f8c8d; margin-bottom: 1rem;">(Simulate a driver requesting leave)</p>
                <form action="admin_driver_action.php" method="POST">
                    <?= csrf_input_field() ?>
                    <input type="hidden" name="action" value="simulate_leave">
                    <div class="form-group">
                        <label>Select Driver</label>
                        <select name="driver_id" required>
                            <option value="">-- Choose Driver --</option>
                            <?php
                            $d_res = $conn->query("SELECT id, name FROM drivers");
                            while($d = $d_res->fetch_assoc()) {
                                echo "<option value='".$d['id']."'>".htmlspecialchars($d['name'])."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" name="start_date" required>
                    </div>
                    <div class="form-group">
                        <label>End Date</label>
                        <input type="date" name="end_date" required>
                    </div>
                    <div class="form-group">
                        <label>Reason</label>
                        <input type="text" name="reason" placeholder="e.g. Family Function" required>
                    </div>
                    <button type="submit" class="submit-btn" style="background-color:#f39c12;">Submit Leave Request</button>
                </form>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="main-content">
            
            <!-- Pending Leave Requests Table -->
            <div class="table-card" style="margin-bottom: 2rem;">
                <h2>Pending Leave Requests</h2>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Driver</th>
                                <th>Dates</th>
                                <th>Reason</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($leaves_result && $leaves_result->num_rows > 0): ?>
                                <?php while($leave = $leaves_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($leave['driver_name']) ?></strong></td>
                                        <td><?= htmlspecialchars($leave['start_date']) ?> to <?= htmlspecialchars($leave['end_date']) ?></td>
                                        <td><?= htmlspecialchars($leave['reason']) ?></td>
                                        <td>
                                            <form action="admin_driver_action.php" method="POST" class="action-form">
                                                <?= csrf_input_field() ?>
                                                <input type="hidden" name="action" value="handle_leave">
                                                <input type="hidden" name="leave_id" value="<?= $leave['id'] ?>">
                                                <input type="hidden" name="driver_id" value="<?= $leave['driver_id'] ?>">
                                                <button type="submit" name="status" value="Approved" class="btn-small accept">Approve</button>
                                                <button type="submit" name="status" value="Rejected" class="btn-small reject">Reject</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align:center;">No pending requests.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Drivers Roster Table -->
            <div class="table-card">
                <h2>Company Drivers Roster</h2>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>License & Phone</th>
                                <th>Salary</th>
                                <th>Current Status</th>
                                <th>Change Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($drivers_result && $drivers_result->num_rows > 0): ?>
                                <?php while($row = $drivers_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['id']) ?></td>
                                        <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                                        <td>
                                            <?= htmlspecialchars($row['license_number']) ?><br>
                                            <small><?= htmlspecialchars($row['phone']) ?></small>
                                        </td>
                                        <td>₹ <?= isset($row['salary']) ? number_format($row['salary'], 2) : '0.00' ?></td>
                                        <td>
                                            <?php
                                            $badge_class = 'Available';
                                            if($row['status'] == 'On Trip') $badge_class = 'Trip';
                                            if($row['status'] == 'On Leave') $badge_class = 'Leave';
                                            if($row['status'] == 'Fired') $badge_class = 'Fired';
                                            ?>
                                            <span class="status-badge <?= $badge_class ?>"><?= htmlspecialchars($row['status']) ?></span>
                                        </td>
                                        <td>
                                            <form action="admin_driver_action.php" method="POST" class="action-form">
                                                <?= csrf_input_field() ?>
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="driver_id" value="<?= $row['id'] ?>">
                                                <select name="new_status" <?= $row['status'] == 'Fired' ? 'disabled' : '' ?>>
                                                    <option value="Available" <?= $row['status'] == 'Available' ? 'selected' : '' ?>>Available</option>
                                                    <option value="On Trip" <?= $row['status'] == 'On Trip' ? 'selected' : '' ?>>On Trip</option>
                                                    <option value="On Leave" <?= $row['status'] == 'On Leave' ? 'selected' : '' ?>>On Leave</option>
                                                </select>
                                                <button type="submit" class="btn-small" <?= $row['status'] == 'Fired' ? 'disabled' : '' ?>>Update</button>
                                            </form>
                                        </td>
                                        <td>
                                            <form action="admin_driver_action.php" method="POST" class="action-form" onsubmit="return confirm('Are you sure you want to fire this driver?');">
                                                <?= csrf_input_field() ?>
                                                <input type="hidden" name="action" value="fire_driver">
                                                <input type="hidden" name="driver_id" value="<?= $row['id'] ?>">
                                                <button type="submit" class="btn-small reject" <?= $row['status'] == 'Fired' ? 'disabled' : '' ?>>Fire</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" style="text-align:center;">No drivers found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </div>
</body>
</html>
