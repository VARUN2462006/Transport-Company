<?php
$sname = "localhost";
$unmae = "root";     // Default XAMPP/WAMP username
$password = "";      // Default XAMPP/WAMP password is empty
$db_name = "glass_login_db";

$conn = mysqli_connect($sname, $unmae, $password, $db_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>