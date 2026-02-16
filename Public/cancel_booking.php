<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['booking_id'])) {
    include 'connect.php';
    $email = $_SESSION['email'];
    $booking_id = (int) $_POST['booking_id'];

    // Only allow cancelling own bookings
    $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ? AND user_email = ?");
    $stmt->bind_param("is", $booking_id, $email);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

header("Location: show_booking.php");
exit();
?>
