<?php
require_once __DIR__ . '/../Comman Point/security_bootstrap.php';
if (empty($_SESSION['admin_user_id'])) {
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/../Comman Point/csrf_helper.php';
require_once __DIR__ . '/../connect.php';

// CSRF check
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_token();
}

$truck_id = (int) ($_POST['truck_id'] ?? 0);
$price_km = (float) ($_POST['price_per_km'] ?? 0);
$price_ton = (float) ($_POST['price_per_ton'] ?? 0);

if ($truck_id <= 0 || $price_km < 0 || $price_ton < 0) {
    header('Location: admin_dashboard.php?msg=invalid_price');
    exit;
}

$source = $_POST['source'] ?? 'dashboard';
$allowed_sources = ['dashboard', 'truck_rates'];
if (!in_array($source, $allowed_sources, true)) {
    $source = 'dashboard';
}

$stmt = $conn->prepare("UPDATE truck_rates SET price_per_km = ?, price_per_ton = ? WHERE id = ?");
$stmt->bind_param("ddi", $price_km, $price_ton, $truck_id);
$stmt->execute();
$stmt->close();

$redirect_to = ($source === 'truck_rates') ? 'truck_rates.php' : 'admin_dashboard.php';
header('Location: ' . $redirect_to . '?msg=price_updated&id=' . $truck_id);
exit;

