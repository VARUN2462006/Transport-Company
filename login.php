<?php
require_once __DIR__ . '/Comman Point/security_bootstrap.php';
include 'connect.php';
require_once __DIR__ . '/Comman Point/csrf_helper.php';

$login_error = false;
$rate_limited = false;

// Rate limiting: max 5 attempts per 60 seconds
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = [];
}

if (isset($_POST['SignIn'])) {
    // CSRF check
    require_csrf_token();

    // Rate limiting check
    $now = time();
    $_SESSION['login_attempts'] = array_filter($_SESSION['login_attempts'], function($t) use ($now) {
        return ($now - $t) < 60;
    });
    
    if (count($_SESSION['login_attempts']) >= 5) {
        $rate_limited = true;
    } else {
        $email = trim($_POST['email']);
        $password = $_POST['password'];
    
        // Only fetch by email first, do not pass plaintext password to SQL
        $stmt = $conn->prepare("SELECT `password` FROM `users` WHERE `email`=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res->num_rows > 0) {
            $row = $res->fetch_assoc();
            
            // Verify the provided plaintext password against the stored BCrypt hash
            if (password_verify($password, $row['password'])) {
                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);
                $_SESSION['email'] = $email;
                $_SESSION['login_attempts'] = []; // Reset on success
            
                // Attempt to update last_login timestamp, silently ignore if column doesn't exist
                try {
                    $update_stmt = $conn->prepare("UPDATE `users` SET `last_login` = NOW() WHERE `email`=?");
                    $update_stmt->bind_param("s", $email);
                    $update_stmt->execute();
                } catch (mysqli_sql_exception $e) {
                    // Ignore error if column doesn't exist yet
                }
            
                if (isset($_SESSION['redirect_url'])) {
                    $url = $_SESSION['redirect_url'];
                    unset($_SESSION['redirect_url']);
                    // Validate redirect URL is a safe relative path (prevent open redirect)
                    if (is_string($url) && strlen($url) > 0 && $url[0] === '/' && (strlen($url) < 2 || $url[1] !== '/')) {
                        header("Location: $url");
                    } else {
                        header("Location: services.php");
                    }
                } else {
                    header("Location: services.php");
                }
                exit();
            } else {
                // Password did not match the hash
                $login_error = true;
                $_SESSION['login_attempts'][] = $now;
            }
        } else {
            // Email not found
            $login_error = true;
            $_SESSION['login_attempts'][] = $now;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>

    <!-- DNS Prefetching -->
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Preload Critical Assets -->
    <link rel="preload" href="login.css?v=4.0" as="style">
    <link rel="preload" href="Images/optimized/logo.webp" as="image">

    <link rel="stylesheet" href="login.css?v=4.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
</head>
<body>

<div class="container">
    <div style="text-align: center; margin-bottom: 20px;">
        <img src="Images/optimized/logo.webp" alt="Company Logo" width="100" height="100">
    </div>
    <h1 class="form-title">Login Page</h1>
    
    <div class="project-warning">
        <p><strong>⚠️ NOTICE:</strong> This is a college project. Do not enter real personal or financial information.</p>
    </div>

    <?php if ($rate_limited): ?>
        <div style="background:#f8d7da;color:#721c24;padding:10px 15px;border-radius:5px;margin-bottom:15px;text-align:center;font-size:0.9rem;">
            Too many failed attempts. Please wait 60 seconds before trying again.
        </div>
    <?php elseif ($login_error): ?>
        <div style="background:#f8d7da;color:#721c24;padding:10px 15px;border-radius:5px;margin-bottom:15px;text-align:center;font-size:0.9rem;">
            Invalid Email or Password.
        </div>
    <?php endif; ?>

    <form method="post" action="login.php">
        <?= csrf_input_field() ?>

        <!-- Email -->
        <div class="input-group">
            <i class="fas fa-envelope"></i>
            <input type="email" id="email" name="email" required placeholder=" ">
            <label for="email">Email</label>
        </div>

        <!-- Password -->
        <div class="input-group">
            <i class="fas fa-lock"></i>
            <input type="password" id="password" name="password" required placeholder=" ">
            <label for="password">Password</label>
        </div>

        <input type="submit" class="btn" value="Sign In" name="SignIn">

        <p class="forgot-link">
            <a href="forgot_password.php" >Forgot Password?</a>
        </p>

        <p class="register-link">
            Don't have an account?
            <a href="registor.php">Register here</a>
        </p>
    </form>
</div>

</body>
</html>
