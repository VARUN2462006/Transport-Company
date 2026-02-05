<?php
$conn = new mysqli("localhost", "root", "", "login");
$conn->connect_error && die("DB Error");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $total = $_POST['km'] * $_POST['price_per_km'];

    $stmt = $conn->prepare(
        "INSERT INTO bookings 
        (customer_name, truck_name, booking_date, address, distance_km, price_per_km, total_cost) 
        VALUES (?,?,?,?,?,?,?)"
    );

    $stmt->bind_param(
        "ssssddd",
        $_POST['customer_name'],
        $_POST['truck_name'],
        $_POST['booking_date'],
        $_POST['address'],
        $_POST['km'],
        $_POST['price_per_km'],
        $total
    );

    $stmt->execute()
        ? header("Location: services.php")
        : die("Insert failed");
}
$conn->close();
