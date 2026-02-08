<?php
session_start();
if (empty($_SESSION['admin_user_id'])) {
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/../Public/connect.php';

$truck_id = (int) ($_POST['truck_id'] ?? 0);
$price_km = (float) ($_POST['price_per_km'] ?? 0);
$price_ton = (float) ($_POST['price_per_ton'] ?? 0);

if ($truck_id <= 0 || $price_km < 0 || $price_ton < 0) {
    header('Location: admin_dashboard.php?msg=invalid_price');
    exit;
}

$source = $_POST['source'] ?? 'dashboard';

$has_price_per_ton = false;
$r = $conn->query("SHOW COLUMNS FROM truck_rates LIKE 'price_per_ton'");
if ($r && $r->num_rows > 0) {
    $has_price_per_ton = true;
}

if ($has_price_per_ton) {
    $stmt = $conn->prepare("UPDATE truck_rates SET price_per_km = ?, price_per_ton = ? WHERE id = ?");
    $stmt->bind_param("ddi", $price_km, $price_ton, $truck_id);
} else {
    $stmt = $conn->prepare("UPDATE truck_rates SET price_per_km = ? WHERE id = ?");
    $stmt->bind_param("di", $price_km, $truck_id);
}
$stmt->execute();
$stmt->close();

$redirect_to = ($source === 'truck_rates') ? 'truck_rates.php' : 'admin_dashboard.php';
header('Location: ' . $redirect_to . '?msg=price_updated&id=' . $truck_id);
exit;
