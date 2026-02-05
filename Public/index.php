<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>

    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
</head>
<body>

<div class="container">
    <h1 class="form-title">Login Page</h1>

    <form method="post" action="index.php">

        <!-- Email -->
        <div class="input-group">
            <i class="fas fa-envelope"></i>
            <input type="email" id="email" name="email" required>
            <label for="email">Email</label>
        </div>

        <!-- Password -->
        <div class="input-group">
            <i class="fas fa-lock"></i>
            <input type="password" id="password" name="password" required>
            <label for="password">Password</label>
        </div>

        <input type="submit" class="btn" value="Sign In" name="SignIn">

        <p class="register-link">
            Don't have an account?
            <a href="registor.php">Register here</a>
        </p>
    </form>
</div>


<!-- php code -->
<?php
include 'connect.php';
if(isset($_POST['SignIn'])){
    $email=$_POST['email'];
    $password=$_POST['password'];

    $selectquery="SELECT * FROM `users` WHERE `email`='$email' AND `password`='$password'";
    $res=mysqli_query($conn,$selectquery);
    if(mysqli_num_rows($res) > 0){
        header("Location: Homepage.php");
        exit();
    }else{
        ?>
        <script>
            alert("Invalid Email or Password");
        </script>
        <?php
    }
}
?>
</body>
</html>
