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
        header("Location: register.php?error=Email is required");
        exit();
    } else if(empty($pass)){
        header("Location: register.php?error=Password is required");
        exit();
    } else {
        // Check if email already exists
        $check_sql = "SELECT * FROM users WHERE email='$email'";
        $result = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($result) > 0) {
            header("Location: register.php?error=The email is already taken");
            exit();
        } else {
            // Hash the password for security
            $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
            $sql2 = "INSERT INTO users(email, password) VALUES('$email', '$hashed_password')";
            $result2 = mysqli_query($conn, $sql2);
            
            if ($result2) {
                header("Location: index.php?error=Account created successfully");
                exit();
            } else {
                header("Location: register.php?error=Unknown error occurred");
                exit();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CSS Glassmorphism | Register</title>
  <link rel="stylesheet" href="./style.css">
  <style>
    .error { color: #ff4d4d; padding: 10px; text-align: center; background: rgba(0,0,0,0.2); margin-bottom: 15px;}
  </style>
</head>
<body>

<div class="wrapper">
  <div class="login-box">
    <form action="register.php" method="post">
      <h2>Register</h2>

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

      <button type="submit">Register</button>

      <div class="register-link">
        <p>Already have an account? <a href="index.php">Login</a></p>
      </div>
    </form>
  </div>
</div>

<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

</body>
</html>