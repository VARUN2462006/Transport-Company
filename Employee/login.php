<?php
require_once __DIR__ . '/../Comman Point/security_bootstrap.php';
require_once __DIR__ . '/../Comman Point/csrf_helper.php';

// If already logged in, go to dashboard
if (!empty($_SESSION['driver_id'])) {
    header('Location: dashboard.php');
    exit;
}

$login_error = '';
$rate_limited = false;

// Rate limiting: max 5 attempts per 60 seconds
if (!isset($_SESSION['emp_login_attempts'])) {
    $_SESSION['emp_login_attempts'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    require_csrf_token();

    $now = time();
    $_SESSION['emp_login_attempts'] = array_filter($_SESSION['emp_login_attempts'], function($t) use ($now) {
        return ($now - $t) < 60;
    });

    if (count($_SESSION['emp_login_attempts']) >= 5) {
        $rate_limited = true;
    } else {
        require_once __DIR__ . '/../connect.php';

        $phone = trim($_POST['phone'] ?? '');
        $license = trim($_POST['license'] ?? '');

        if ($phone && $license) {
            $stmt = $conn->prepare("SELECT id, name, status FROM drivers WHERE phone = ? AND license_number = ? AND status != 'Fired'");
            $stmt->bind_param("ss", $phone, $license);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res->num_rows > 0) {
                $driver = $res->fetch_assoc();
                session_regenerate_id(true);
                $_SESSION['driver_id'] = $driver['id'];
                $_SESSION['driver_name'] = $driver['name'];
                $_SESSION['driver_status'] = $driver['status'];
                $_SESSION['emp_login_attempts'] = [];
                header('Location: dashboard.php');
                exit;
            } else {
                $login_error = 'Invalid phone number or license number. Please try again.';
                $_SESSION['emp_login_attempts'][] = $now;
            }
            $stmt->close();
            $conn->close();
        } else {
            $login_error = 'Please fill in both fields.';
            $_SESSION['emp_login_attempts'][] = $now;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Login — Maharaja Transport</title>
    <link rel="stylesheet" href="employee.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="logo-area">
                <img src="../Images/optimized/logo.webp" alt="Logo">
                <h1>Employee Portal</h1>
                <p>Maharaja Transport Company</p>
            </div>

            <?php if ($rate_limited): ?>
                <div class="alert alert-error">Too many failed attempts. Please wait 60 seconds before trying again.</div>
            <?php elseif ($login_error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($login_error) ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <?= csrf_input_field() ?>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" placeholder="Enter your 10-digit phone number" required
                           value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>License Number</label>
                    <input type="text" name="license" placeholder="Enter your license number" required
                           value="<?= htmlspecialchars($_POST['license'] ?? '') ?>">
                </div>
                <button type="submit" class="btn-primary">Sign In</button>
            </form>

            <p style="text-align:center; margin-top:1.25rem; font-size:0.85rem; color:#7f8c8d;">
                <a href="../index.php" style="color:#ff7300; text-decoration:none; font-weight:500;">← Back to Home</a>
            </p>
        </div>
    </div>
</body>
</html>
