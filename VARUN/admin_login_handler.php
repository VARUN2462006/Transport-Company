<?php
require_once __DIR__ . '/../Comman Point/security_bootstrap.php';
require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../Comman Point/csrf_helper.php';

$rate_limited = false;

// Rate limiting: max 5 attempts per 60 seconds
if (!isset($_SESSION['admin_login_attempts'])) {
    $_SESSION['admin_login_attempts'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    // CSRF check
    require_csrf_token();

    $now = time();
    $_SESSION['admin_login_attempts'] = array_filter($_SESSION['admin_login_attempts'], function($t) use ($now) {
        return ($now - $t) < 60;
    });

    if (count($_SESSION['admin_login_attempts']) >= 5) {
        header('Location: index.php?error=' . urlencode('Too many failed attempts. Please wait 60 seconds.'));
        exit;
    }

    $user_id = trim($_POST['user_id'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($user_id === '' || $password === '') {
        header('Location: index.php?error=' . urlencode('Please enter User Id and Password.'));
        exit;
    }

    // Authenticate against admin_users table (bcrypt hashed passwords only)
    $found = false;
    $stmt = $conn->prepare("SELECT user_id, password FROM admin_users WHERE user_id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $found = true;
        }
    }
    $stmt->close();

    if ($found) {
        session_regenerate_id(true);
        $_SESSION['admin_user_id'] = $user_id;
        $_SESSION['admin_login_attempts'] = [];
        header('Location: admin_dashboard.php');
        exit;
    }

    // Record failed attempt
    $_SESSION['admin_login_attempts'][] = $now;
}

header('Location: index.php?error=' . urlencode('Invalid User Id or Password.'));
exit;
