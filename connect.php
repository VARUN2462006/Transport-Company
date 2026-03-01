<?php
require_once __DIR__ . '/Comman Point/security_bootstrap.php';

$env_host = getenv('DB_HOST');
$env_user = getenv('DB_USER');
$env_pass = getenv('DB_PASS');
$env_name = getenv('DB_NAME');

if ($env_host && $env_user && $env_name) {
    $host = $env_host;
    $user = $env_user;
    $password = $env_pass ?: '';
    $db = $env_name;
} else {
    $server_name = $_SERVER['SERVER_NAME'];
    if ($server_name == "localhost" || $server_name == "127.0.0.1") {
        $host = 'localhost';
        $user = 'root';
        $password = '';
        $db = 'login';
    } else {
        $live_cfg = require __DIR__ . '/Comman Point/db_config.php';
        $host     = $live_cfg['host'];
        $user     = $live_cfg['user'];
        $password = $live_cfg['password'];
        $db       = $live_cfg['db'];
    }
}

mysqli_report(MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR);

try {
    $conn = new mysqli($host, $user, $password, $db);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    http_response_code(503);
    die('<div style="font-family:sans-serif;text-align:center;margin-top:50px;"><h1 style="color:#c00;">Service Temporarily Unavailable</h1><p>Please wait 60 seconds and refresh the page.</p></div>');
}
?>
