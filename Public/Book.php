<?php
$name = $_GET['truck_name'] ?? 'Select Truck';
$img = $_GET['truck_img'] ?? 'default.jpg';
$truck_key = $_GET['truck_key'] ?? '';

// Truck capacity in tons: intra 1.5, yodha 2, tata-prima 17.5, ashok-layland 19, bharatbenz 15
$truck_capacity_ton = [
    'intra' => 1.5,
    'yodha' => 2,
    'tata-prima' => 17.50,
    'ashok-layland' => 19,
    'bharatbenz' => 15,
];
$capacity = isset($truck_capacity_ton[$truck_key]) ? $truck_capacity_ton[$truck_key] : 1;

$price_km = '0';
$price_ton = '0';
$use_ton_rate = false;
if ($truck_key) {
    require_once __DIR__ . '/connect.php';
    $res_col = $conn->query("SHOW COLUMNS FROM truck_rates LIKE 'price_per_ton'");
    $has_ton = $res_col && $res_col->num_rows > 0;
    $stmt = $conn->prepare($has_ton ? "SELECT price_per_km, price_per_ton FROM truck_rates WHERE truck_key = ?" : "SELECT price_per_km FROM truck_rates WHERE truck_key = ?");
    $stmt->bind_param("s", $truck_key);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $row = $res->fetch_assoc()) {
        $price_km = (string)(float)$row['price_per_km'];
        if ($has_ton && isset($row['price_per_ton'])) {
            $price_ton = (string)(float)$row['price_per_ton'];
            $use_ton_rate = true;
        }
    }
    $stmt->close();
    $conn->close();
} else {
    $price_km = $_GET['price'] ?? '0';
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
<div class="price-per-km">
    Price per km: <strong>₹<?= htmlspecialchars($price_km) ?></strong>
    <?php if ($use_ton_rate): ?> &nbsp;|&nbsp; Price per ton: <strong>₹<?= htmlspecialchars($price_ton) ?></strong><?php endif; ?>
</div>
<div class="capacity-info">Truck capacity: <strong><?= htmlspecialchars((string)$capacity) ?> ton</strong></div>

<form action="process_booking.php" method="POST">
<input type="hidden" name="truck_name" value="<?= htmlspecialchars($name) ?>">
<input type="hidden" name="truck_key" value="<?= htmlspecialchars($truck_key) ?>">
<input type="hidden" id="price_km" name="price_per_km" value="<?= htmlspecialchars($price_km) ?>">
<input type="hidden" id="price_ton" name="price_per_ton" value="<?= htmlspecialchars($price_ton) ?>">

<label>Your Name</label>
<input type="text" name="customer_name" placeholder="Full name" required>

<label>Date</label>
<input type="date" name="booking_date" required>

<label>Address</label>
<textarea name="address" rows="3" placeholder="Full address" required></textarea>

<label>Distance (KM)</label>
<input type="number" id="km" name="km" placeholder="e.g. 50" min="1" oninput="total()" required>

<label>Cargo weight (tons)</label>
<input type="number" id="weight_ton" name="weight_ton" placeholder="e.g. <?= (int)$capacity === $capacity ? (int)$capacity : $capacity ?>" min="0.1" max="<?= htmlspecialchars((string)$capacity) ?>" step="0.1" oninput="total()" required>

<div>Estimated Cost: <span id="total_display">₹0</span></div>
<p class="formula-note"><?= $use_ton_rate ? 'Cost = (₹/km × distance) + (₹/ton × weight)' : 'Cost = ₹/km × distance × weight (tons)' ?></p>
<button type="submit">Confirm Booking</button>
</form>
</div>
</div>

<script>
function total(){
var priceKm = parseFloat(document.getElementById('price_km').value) || 0;
var priceTon = parseFloat(document.getElementById('price_ton').value) || 0;
var km = parseFloat(document.getElementById('km').value) || 0;
var weight = parseFloat(document.getElementById('weight_ton').value) || 0;
var t = <?= $use_ton_rate ? '(priceKm * km) + (priceTon * weight)' : 'priceKm * km * weight' ?>;
document.getElementById('total_display').innerText = "₹" + (Math.round(t * 100) / 100);
}
</script>

</body>
</html>
