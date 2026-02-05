<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Page</title>

    <link rel="stylesheet" href="register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
</head>
<body>

<div class="container">
    <h1 class="form-title">Registration Page</h1>

    <form method="post" action="registor.php">

        <!-- Full Name -->
        <div class="input-group">
            <i class="fas fa-user"></i>
            <input type="text" id="name" name="name" required>
            <label for="name">Full Name</label>
        </div>

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

        <input type="submit" class="btn" value="Sign Up" name="SignUp">

        <p class="register-link">
            Already have an account?
            <a href="index.php">Login here</a>
        </p>
    </form>
</div>


<!-- php code -->
<?php
include 'connect.php';
if(isset($_POST['SignUp'])){
    $name=$_POST['name'];
    $email=$_POST['email'];
    $password=$_POST['password'];

    $insertquery="INSERT INTO `users`(`name`, `email`, `password`) VALUES ('$name','$email','$password')";
    $res=mysqli_query($conn,$insertquery);
    if($res){
        ?>
        <script>
            alert("Data Inserted Successfully");
        </script>
        <?php
    }else{
        ?>
        <script>
            alert("Data Not Inserted");
        </script>
        <?php
    }
}
?>
</body>
</html>
