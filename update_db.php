<?php
include 'Public/connect.php';

// Check if column exists
$check = $conn->query("SHOW COLUMNS FROM bookings LIKE 'user_email'");
if($check->num_rows == 0) {
    $sql = "ALTER TABLE bookings ADD COLUMN user_email VARCHAR(100) NOT NULL AFTER customer_name";
    if ($conn->query($sql) === TRUE) {
        echo "Column user_email added successfully";
    } else {
        echo "Error adding column: " . $conn->error;
    }
} else {
    echo "Column user_email already exists";
}

$conn->close();
?>
