<?php
$name = $_GET['truck_name'] ?? 'Select Truck';
$img = $_GET['truck_img'] ?? 'default.jpg';
$truck_key = $_GET['truck_key'] ?? '';

$price = '0';
if ($truck_key) {
    require_once __DIR__ . '/connect.php';
    $stmt = $conn->prepare("SELECT price_per_km FROM truck_rates WHERE truck_key = ?");
    $stmt->bind_param("s", $truck_key);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $row = $res->fetch_assoc()) {
        $price = (string)(int)$row['price_per_km'];
    }
    $stmt->close();
    $conn->close();
} else {
    $price = $_GET['price'] ?? '0';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="book.css?v=1.3">
<title>Booking <?= $name ?></title>
</head>
<body>

<nav class="navbar">
<a href="services.php">← Back</a>
<img src="../Images/logo.png" alt="Logo" class="logo">
<a href="index.php">Logout</a>
</nav><hr>

<div class="main-content">
<div class="booking-card">
<img src="../Images/Truck Images/<?= $img ?>" alt="Truck">
<h2>Booking for: <span style="color:#28a745"><?= htmlspecialchars($name) ?></span></h2>
<div class="price-per-km">Price per km: <strong>₹<?= htmlspecialchars($price) ?></strong></div>

<form action="process_booking.php" method="POST">
<input type="hidden" name="truck_name" value="<?= htmlspecialchars($name) ?>">
<input type="hidden" id="price" name="price_per_km" value="<?= htmlspecialchars($price) ?>">

<label>Your Name</label>
<input type="text" name="customer_name" placeholder="Full name" required>

<label>Date</label>
<input type="date" name="booking_date" required>

<label>Address</label>
<textarea name="address" rows="3" placeholder="Full address" required></textarea>

<label>Distance (KM)</label>
<input type="number" id="km" name="km" placeholder="e.g. 50" oninput="total()" required>

<div>Estimated Cost: <span id="total_display">₹0</span></div>
<button type="submit">Confirm Booking</button>
</form>
</div>
</div>

<script>
function total(){
let t = document.getElementById('price').value * document.getElementById('km').value;
document.getElementById('total_display').innerText = "₹" + (t || 0);
}
</script>

</body>
</html>
