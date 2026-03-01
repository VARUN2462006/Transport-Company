<?php
require_once __DIR__ . '/../Comman Point/security_bootstrap.php';
if (empty($_SESSION['driver_id'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../Comman Point/csrf_helper.php';
require_once __DIR__ . '/../connect.php';

$driver_id = $_SESSION['driver_id'];
$driver_name = $_SESSION['driver_name'];
$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    require_csrf_token();
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');
    $reason = trim($_POST['reason'] ?? '');

    if (!$start_date || !$end_date || !$reason) {
        $error = 'Please fill in all fields.';
    } elseif ($start_date > $end_date) {
        $error = 'End date must be on or after the start date.';
    } elseif ($start_date < date('Y-m-d')) {
        $error = 'Start date cannot be in the past.';
    } else {
        $stmt = $conn->prepare("INSERT INTO driver_leaves (driver_id, start_date, end_date, reason, status) VALUES (?, ?, ?, ?, 'Pending')");
        $stmt->bind_param("isss", $driver_id, $start_date, $end_date, $reason);

        if ($stmt->execute()) {
            $success = 'Leave request submitted successfully! Your manager will review it.';
        } else {
            $error = 'Something went wrong. Please try again.';
        }
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Leave — Employee Portal</title>
    <link rel="stylesheet" href="employee.css">
</head>
<body>
    <!-- Navigation -->
    <header class="emp-header">
        <div class="brand">
            <img src="../Images/optimized/logo.webp" alt="Logo">
            <h1>Employee Portal</h1>
        </div>
        <nav class="emp-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="request_leave.php" class="active">Request Leave</a>
            <a href="contact.php">Contact</a>
            <a href="logout.php" class="logout-link">Logout</a>
        </nav>
    </header>

    <main class="emp-main">
        <div class="content-card" style="max-width: 550px; margin: 0 auto;">
            <h2>📝 Request Leave</h2>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="request_leave.php">
                <?= csrf_input_field() ?>
                <div class="form-group">
                    <label>Employee</label>
                    <input type="text" value="<?= htmlspecialchars($driver_name) ?>" disabled 
                           style="background:#f0f0f0; cursor:not-allowed;">
                </div>

                <div class="form-group">
                    <label>Start Date</label>
                    <input type="date" name="start_date" required min="<?= date('Y-m-d') ?>"
                           value="<?= htmlspecialchars($_POST['start_date'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>End Date</label>
                    <input type="date" name="end_date" required min="<?= date('Y-m-d') ?>"
                           value="<?= htmlspecialchars($_POST['end_date'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Reason for Leave</label>
                    <textarea name="reason" rows="3" placeholder="e.g. Family function, medical, personal..." required><?= htmlspecialchars($_POST['reason'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn-primary">Submit Leave Request</button>
            </form>

            <p style="text-align:center; margin-top:1.25rem; font-size:0.85rem; color:#7f8c8d;">
                <a href="dashboard.php" style="color:#ff7300; text-decoration:none; font-weight:500;">← Back to Dashboard</a>
            </p>
        </div>
    </main>
</body>
</html>
