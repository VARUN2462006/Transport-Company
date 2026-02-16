<?php
$conn = new mysqli("localhost", "root", "", "login");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if admin_users table exists
$r = $conn->query("SHOW TABLES LIKE 'admin_users'");
if ($r->num_rows == 0) {
    $conn->query("CREATE TABLE admin_users (user_id VARCHAR(10) NOT NULL, password VARCHAR(10) NOT NULL)");
    echo "Table admin_users created\n";
}

$conn->query("DELETE FROM admin_users");
$conn->query("INSERT INTO admin_users (user_id, password) VALUES ('762086', '1008')");
echo "Admin credentials set in admin_users: User ID=762086, Password=1008\n";

$conn->close();
?>
