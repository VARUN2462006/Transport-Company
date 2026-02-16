<?php
$conn = new mysqli("localhost", "root", "", "login");
$conn->connect_error && die("DB Error");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $km = (float) $_POST['km'];
    $price_per_km = (float) $_POST['price_per_km'];
    $price_per_ton = (float) ($_POST['price_per_ton'] ?? 0);
    $weight_ton = (float) ($_POST['weight_ton'] ?? 1);
    $use_ton_rate = false;
    $rc = $conn->query("SHOW COLUMNS FROM truck_rates LIKE 'price_per_ton'");
    if ($rc && $rc->num_rows > 0) {
        $use_ton_rate = true;
    }
    if ($use_ton_rate) {
        $total = ($price_per_km * $km) + ($price_per_ton * $weight_ton);
    } else {
        $total = $price_per_km * $km * $weight_ton;
    }

    $has_weight = false;
    $r = $conn->query("SHOW COLUMNS FROM bookings LIKE 'weight_ton'");
    if ($r && $r->num_rows > 0) {
        $has_weight = true;
    }

    session_start();
    $user_email = $_SESSION['email'] ?? null;

    if ($has_weight) {
        $stmt = $conn->prepare(
            "INSERT INTO bookings 
            (customer_name, user_email, truck_name, booking_date, address, distance_km, price_per_km, total_cost, weight_ton) 
            VALUES (?,?,?,?,?,?,?,?,?)"
        );
        $stmt->bind_param("sssssdddd", $_POST['customer_name'], $user_email, $_POST['truck_name'], $_POST['booking_date'], $_POST['address'], $km, $price_per_km, $total, $weight_ton);
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO bookings 
            (customer_name, user_email, truck_name, booking_date, address, distance_km, price_per_km, total_cost) 
            VALUES (?,?,?,?,?,?,?,?)"
        );
        $stmt->bind_param("sssssddd", $_POST['customer_name'], $user_email, $_POST['truck_name'], $_POST['booking_date'], $_POST['address'], $km, $price_per_km, $total);
    }

    $stmt->execute()
        ? header("Location: services.php")
        : die("Insert failed");
}
$conn->close();
