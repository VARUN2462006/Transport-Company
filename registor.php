<?php
require_once __DIR__ . '/Comman Point/security_bootstrap.php';
include 'connect.php';
require_once __DIR__ . '/Comman Point/csrf_helper.php';

$reg_error = '';
$reg_success = false;

if (isset($_POST['SignUp'])) {
    // CSRF check
    require_csrf_token();

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validation
    if (strlen($name) < 2) {
        $reg_error = 'Name must be at least 2 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $reg_error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $reg_error = 'Password must be at least 8 characters.';
    } else {
        // Check for duplicate email
        $check = $conn->prepare("SELECT `id` FROM `users` WHERE `email` = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $reg_error = 'An account with this email already exists.';
        } else {
            // Hash the password securely using BCrypt
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO `users`(`name`, `email`, `password`) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashed_password);
            $res = $stmt->execute();
            if ($res) {
                $reg_success = true;
            } else {
                $reg_error = 'Registration failed. Please try again.';
            }
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Page</title>

    <link rel="stylesheet" href="login.css?v=4.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
</head>
<body>

<div class="container">
    <div style="text-align: center; margin-bottom: 20px;">
        <img src="Images/optimized/logo.webp" alt="Company Logo" width="100" height="100">
    </div>
    <h1 class="form-title">Registration Page</h1>

    <div class="project-warning">
        <p><strong>⚠️ NOTICE:</strong> This is a college project. Do not enter real personal or financial information.</p>
    </div>

    <?php if ($reg_success): ?>
        <div style="background:#d4edda;color:#155724;padding:10px 15px;border-radius:5px;margin-bottom:15px;text-align:center;font-size:0.9rem;">
            Registration successful! <a href="login.php" style="color:#155724;font-weight:600;">Login here</a>
        </div>
    <?php elseif ($reg_error): ?>
        <div style="background:#f8d7da;color:#721c24;padding:10px 15px;border-radius:5px;margin-bottom:15px;text-align:center;font-size:0.9rem;">
            <?= htmlspecialchars($reg_error) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="registor.php">
        <?= csrf_input_field() ?>

        <!-- Full Name -->
        <div class="input-group">
            <i class="fas fa-user"></i>
            <input type="text" id="name" name="name" required minlength="2" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" placeholder=" ">
            <label for="name">Full Name</label>
        </div>

        <!-- Email -->
        <div class="input-group">
            <i class="fas fa-envelope"></i>
            <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder=" ">
            <label for="email">Email</label>
        </div>

        <!-- Password -->
        <div class="input-group">
            <i class="fas fa-lock"></i>
            <input type="password" id="password" name="password" required minlength="8" placeholder=" ">
            <label for="password">Password (min 8 chars)</label>
        </div>

        <input type="submit" class="btn" value="Sign Up" name="SignUp">

        <p class="register-link">
            Already have an account?
            <a href="login.php">Login here</a>
        </p>
    </form>
</div>

</body>
</html>
