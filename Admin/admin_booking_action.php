<?php
session_start();
if (empty($_SESSION['admin_user_id'])) {
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/../Public/connect.php';

$action = $_POST['action'] ?? '';
$booking_id = (int) ($_POST['booking_id'] ?? 0);

if (!in_array($action, ['accept', 'reject']) || $booking_id <= 0) {
    header('Location: admin_dashboard.php?msg=invalid');
    exit;
}

$status = $action === 'accept' ? 'accepted' : 'rejected';
$stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $booking_id);
$stmt->execute();
$stmt->close();

header('Location: admin_dashboard.php?msg=' . $action . '&id=' . $booking_id);
exit;
