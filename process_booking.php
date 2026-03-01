<?php
require_once __DIR__ . '/Comman Point/security_bootstrap.php';
require_once __DIR__ . '/Comman Point/csrf_helper.php';

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // CSRF check
    require_csrf_token();
    
    include 'connect.php';

    $km = (float) $_POST['km'];
    $weight_ton = (float) ($_POST['weight_ton'] ?? 1);
    $truck_key = trim($_POST['truck_key'] ?? '');
    $user_email = $_SESSION['email'];

    // Re-validate pricing and capacity from database (don't trust hidden form fields)
    $price_per_km = 0;
    $price_per_ton = 0;
    $capacity_ton = 100; // fallback
    if ($truck_key) {
        $rate_stmt = $conn->prepare("SELECT price_per_km, price_per_ton, capacity_ton FROM truck_rates WHERE truck_key = ?");
        $rate_stmt->bind_param("s", $truck_key);
        $rate_stmt->execute();
        $rate_res = $rate_stmt->get_result();
        if ($rate_row = $rate_res->fetch_assoc()) {
            $price_per_km = (float) $rate_row['price_per_km'];
            $price_per_ton = (float) $rate_row['price_per_ton'];
            $capacity_ton = (float) ($rate_row['capacity_ton'] ?? $capacity_ton);
        }
        $rate_stmt->close();
    }

    $total = ($price_per_km * $km) + ($price_per_ton * $weight_ton);

    // Validate inputs
    $customer_name = trim($_POST['customer_name'] ?? '');
    $truck_name = trim($_POST['truck_name'] ?? '');
    $booking_date = trim($_POST['booking_date'] ?? '');
    $address = trim($_POST['address'] ?? '');

    // Length and format validation
    if ($customer_name === '' || strlen($customer_name) > 100) {
        die("Invalid name. Must be between 1 and 100 characters.");
    }
    if ($truck_name === '' || strlen($truck_name) > 50) {
        die("Invalid truck selection.");
    }
    if ($address === '' || strlen($address) > 500) {
        die("Address must be between 1 and 500 characters.");
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $booking_date) || strtotime($booking_date) === false) {
        die("Invalid date format.");
    }
    if ($km <= 0 || $km > 99999) {
        die("Invalid distance.");
    }
    if ($weight_ton <= 0 || $weight_ton > $capacity_ton) {
        die("Invalid cargo weight. Max for selected truck is " . htmlspecialchars((string)$capacity_ton) . " ton.");
    }

    $stmt = $conn->prepare(
        "INSERT INTO bookings 
        (customer_name, user_email, truck_name, booking_date, address, distance_km, price_per_km, total_cost, weight_ton) 
        VALUES (?,?,?,?,?,?,?,?,?)"
    );
    $stmt->bind_param("sssssdddd", $customer_name, $user_email, $truck_name, $booking_date, $address, $km, $price_per_km, $total, $weight_ton);

    $stmt->execute()
        ? header("Location: services.php")
        : die("Insert failed");
    exit();
}

header("Location: services.php");
exit();
