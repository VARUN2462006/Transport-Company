<?php
session_start();
include "db_conn.php";

if (isset($_POST['email']) && isset($_POST['password'])) {

    function validate($data){
       $data = trim($data);
       $data = stripslashes($data);
       $data = htmlspecialchars($data);
       return $data;
    }

    $email = validate($_POST['email']);
    $pass = validate($_POST['password']);

    if (empty($email)) {
        header("Location: index.php?error=Email is required");
        exit();
    } else if(empty($pass)){
        header("Location: index.php?error=Password is required");
        exit();
    } else {
        $sql = "SELECT * FROM users WHERE email='$email'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) === 1) {
            $row = mysqli_fetch_assoc($result);
            
            if (password_verify($pass, $row['password'])) {
                // --- LOGIN SUCCESS ---
                $_SESSION['email'] = $row['email'];
                $_SESSION['id'] = $row['id'];
                
                // REDIRECT TO main.html
                header("Location: main.html");
                exit();
                // ---------------------
            } else {
                header("Location: index.php?error=Incorrect User name or password");
                exit();
            }
        } else {
            header("Location: index.php?error=Incorrect User name or password");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CSS Glassmorphism | Login</title>
  <link rel="stylesheet" href="./style.css">
  <style>
    .error { color: #ff4d4d; padding: 10px; text-align: center; border-radius: 5px; background: rgba(0,0,0,0.2); margin-bottom: 15px;}
  </style>
</head>
<body>

<div class="wrapper">
  <div class="login-box">
    <form action="index.php" method="post">
      <h2>Login</h2>
      
      <?php if (isset($_GET['error'])) { ?>
          <p class="error"><?php echo $_GET['error']; ?></p>
      <?php } ?>

      <div class="input-box">
        <span class="icon"><ion-icon name="mail"></ion-icon></span>
        <input type="email" name="email" required>
        <label>Email</label>
      </div>

      <div class="input-box">
        <span class="icon"><ion-icon name="lock-closed"></ion-icon></span>
        <input type="password" name="password" required>
        <label>Password</label>
      </div>

      <div class="remember-forgot">
        <label><input type="checkbox"> Remember me</label>
        <a href="#">Forgot Password?</a>
      </div>

      <button type="submit">Login</button>

      <div class="register-link">
        <p>Don't have an account? <a href="register.php">Register</a></p>
      </div>
    </form>
  </div>
</div>

<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

</body>
</html>