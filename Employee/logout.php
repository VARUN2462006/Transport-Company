<?php
require_once __DIR__ . '/../Comman Point/security_bootstrap.php';

if (empty($_SESSION['driver_id'])) {
    header('Location: login.php');
    exit;
}

// Properly destroy session and clear cookie
session_unset();
session_destroy();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
    setcookie(session_name(), '', time() - 42000, '/');
}

header('Location: login.php');
exit;
?>
