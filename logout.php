<?php
require_once __DIR__ . '/Comman Point/security_bootstrap.php';
ob_start();
session_unset();
session_destroy();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
    // Explicitly try to clear root path cookie as well just in case
    setcookie(session_name(), '', time() - 42000, '/');
}

header("Location: index.php?msg=logged_out");
exit();
?>
