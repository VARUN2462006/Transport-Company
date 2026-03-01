<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <style>
        .alert { padding: 10px 15px; border-radius: 6px; margin-bottom: 15px; font-size: 0.9rem; text-align: center; }
        .alert-error { background: #f8d7da; color: #721c24; }
        .alert-success { background: #d4edda; color: #155724; }
    </style>
</head>
<body>

<div class="container">
    <h1 class="form-title">Reset Password</h1>

    <?php
    include 'connect.php';
    require_once __DIR__ . '/Comman Point/csrf_helper.php';

    $step = $_POST['step'] ?? 'email';
    $error = '';
    $success = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Verify CSRF on all POST steps
        require_csrf_token();
    }

    if ($step === 'verify') {
        // Step 2: User submitted email + name, verify them
        $email = trim($_POST['email'] ?? '');
        $name  = trim($_POST['name'] ?? '');

        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND name = ?");
        $stmt->bind_param("ss", $email, $name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $step = 'reset'; // Show password reset form
        } else {
            $error = 'Email and name do not match any account.';
            $step = 'email'; // Go back
        }
        $stmt->close();

    } elseif ($step === 'update') {
        // Step 3: User submitted new password
        $email    = trim($_POST['email'] ?? '');
        $name     = trim($_POST['name'] ?? '');
        $password = $_POST['new_password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
            $step = 'reset';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
            $step = 'reset';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ? AND name = ?");
            $stmt->bind_param("sss", $hashed_password, $email, $name);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $success = 'Password updated successfully! You can now login.';
                $step = 'done';
            } else {
                $error = 'Something went wrong. Please try again.';
                $step = 'email';
            }
            $stmt->close();
        }
    }
    ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if ($step === 'email'): ?>
    <!-- Step 1: Enter email and name -->
    <form method="post" action="forgot_password.php">
        <?= csrf_input_field() ?>
        <input type="hidden" name="step" value="verify">

        <div class="input-group">
            <i class="fas fa-envelope"></i>
            <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            <label for="email">Email</label>
        </div>

        <div class="input-group">
            <i class="fas fa-user"></i>
            <input type="text" id="name" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            <label for="name">Full Name</label>
        </div>

        <input type="submit" class="btn" value="Verify Identity">

        <p class="register-link">
            <a href="login.php">Back to Login</a>
        </p>
    </form>

    <?php elseif ($step === 'reset'): ?>
    <!-- Step 2: Set new password -->
    <form method="post" action="forgot_password.php">
        <?= csrf_input_field() ?>
        <input type="hidden" name="step" value="update">
        <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
        <input type="hidden" name="name" value="<?= htmlspecialchars($name) ?>">

        <div class="input-group">
            <i class="fas fa-lock"></i>
            <input type="password" id="new_password" name="new_password" required minlength="8">
            <label for="new_password">New Password (min 8 chars)</label>
        </div>

        <div class="input-group">
            <i class="fas fa-lock"></i>
            <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
            <label for="confirm_password">Confirm Password</label>
        </div>

        <input type="submit" class="btn" value="Reset Password">

        <p class="register-link">
            <a href="login.php">Back to Login</a>
        </p>
    </form>

    <?php elseif ($step === 'done'): ?>
    <!-- Step 3: Success -->
    <p class="register-link" style="margin-top: 20px;">
        <a href="login.php">Go to Login</a>
    </p>
    <?php endif; ?>
</div>

</body>
</html>
<?php if (isset($conn)) $conn->close(); ?>
