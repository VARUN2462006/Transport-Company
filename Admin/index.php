<?php
session_start();
if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin.css?v1.0">
    <title>Admin login</title>
</head>
<body>
    <?php
    if (isset($_GET['error'])) {
        echo '<p class="login-error" style="text-align:center;color:#c00;margin-bottom:1rem;">' . htmlspecialchars($_GET['error']) . '</p>';
    }
    ?>
<form class="admin-login" action="admin_login_handler.php" method="post">
    <div class="login">
    <div class="logo">
        <img class="logo-img" src="..\Images\logo.png" alt="logo">
        <br>
    </div>
    <div class="username">
        <label for="userId">User Id</label>
        <input class="user-input" type="text" name="user_id" id="userId" required>
    </div>
    <div class="password">
        <label for="password">Password</label>
        <input class="user-input" type="password" name="password" id="password" required>
    </div>
    <div class="submit">
        <input type="submit" name="submit" id="submit" value="Login">
    </div>
    </div>
</form>
</body>
</html>
