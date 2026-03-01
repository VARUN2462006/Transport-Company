<?php
/**
 * CSRF Token Helper
 * Generates, stores, and verifies CSRF tokens to protect forms against
 * Cross-Site Request Forgery attacks.
 */

/**
 * Generate a CSRF token and store it in the session.
 * @return string The generated token.
 */
function generate_csrf_token() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_time']) || (time() - $_SESSION['csrf_token_time']) > 3600) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

/**
 * Return a hidden input field containing the CSRF token.
 * @return string HTML hidden input element.
 */
function csrf_input_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Verify the CSRF token from POST data against the session token.
 * @return bool True if valid, false otherwise.
 */
function verify_csrf_token() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        return false;
    }
    // Check token age (1 hour max)
    if (empty($_SESSION['csrf_token_time']) || (time() - $_SESSION['csrf_token_time']) > 3600) {
        return false;
    }
    return true;
}

/**
 * Verify CSRF token and die with error if invalid.
 */
function require_csrf_token() {
    if (!verify_csrf_token()) {
        http_response_code(403);
        die('<div style="font-family:sans-serif;text-align:center;margin-top:50px;">
            <h1 style="color:#c00;">403 Forbidden</h1>
            <p>Invalid or expired security token. Please go back and try again.</p>
            <a href="javascript:history.back()" style="color:#2c3e50;">Go Back</a>
        </div>');
    }
}
?>
