<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_strict_mode', 1);
    @ini_set('session.cookie_samesite', 'Lax');
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    ini_set('session.sid_length', 48);
    ini_set('session.sid_bits_per_character', 6);
    ini_set('session.gc_maxlifetime', 1800);
    ini_set('session.cookie_lifetime', 0);
    session_start();
    if (isset($_SESSION['_last_activity']) && (time() - $_SESSION['_last_activity']) > 1800) {
        session_unset();
        session_destroy();
        session_start();
    }
    $_SESSION['_last_activity'] = time();
}

header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()");

$csp_nonce = base64_encode(random_bytes(16));
$GLOBALS['csp_nonce'] = $csp_nonce;

$csp = [
    "default-src 'self'",
    "base-uri 'self'",
    "form-action 'self'",
    "object-src 'none'",
    "frame-ancestors 'none'",
    "img-src 'self' data:",
    "font-src 'self' https://fonts.gstatic.com data:",
    "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com",
    "script-src 'self' 'nonce-" . $csp_nonce . "' https://cdnjs.cloudflare.com",
    "connect-src 'self'"
];
header('Content-Security-Policy: ' . implode('; ', $csp));
?>
