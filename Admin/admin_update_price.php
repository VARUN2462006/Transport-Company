<?php
session_start();
if (empty($_SESSION['admin_user_id'])) {
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/../Public/connect.php';

$truck_id = (int) ($_POST['truck_id'] ?? 0);
$price = (int) ($_POST['price_per_km'] ?? 0);

if ($truck_id <= 0 || $price < 0) {
    header('Location: admin_dashboard.php?msg=invalid_price');
    exit;
}

$stmt = $conn->prepare("UPDATE truck_rates SET price_per_km = ? WHERE id = ?");
$stmt->bind_param("ii", $price, $truck_id);
$stmt->execute();
$stmt->close();

header('Location: admin_dashboard.php?msg=price_updated&id=' . $truck_id);
exit;
