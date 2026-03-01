<?php
require_once __DIR__ . '/Comman Point/security_bootstrap.php';
ob_start();
require_once __DIR__ . '/Comman Point/csrf_helper.php';
if (!isset($_SESSION['email'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit();
}
$name = $_GET['truck_name'] ?? 'Select Truck';
$img = $_GET['truck_img'] ?? 'default.jpg';
$truck_key = $_GET['truck_key'] ?? '';

$capacity = 1.0;
$price_km = '0';
$price_ton = '0';
if ($truck_key) {
    require_once __DIR__ . '/connect.php';
    $stmt = $conn->prepare("SELECT price_per_km, price_per_ton, capacity_ton FROM truck_rates WHERE truck_key = ?");
    $stmt->bind_param("s", $truck_key);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $price_km = (string)(float)$row['price_per_km'];
        $price_ton = (string)(float)$row['price_per_ton'];
        $capacity = (float)$row['capacity_ton'];
    }
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="Book.css?v=1.0">
<title>Booking <?= $name ?></title>
</head>
<body>

<nav class="navbar">
<a href="services.php">← Back</a>
<img src="Images/optimized/logo.webp" alt="Logo" class="logo" width="80" height="80">
<a href="logout.php">Logout</a>
</nav><hr>

<div class="main-content">
<div class="booking-card">
<img src="Images/optimized/trucks/<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>" alt="Truck" loading="lazy" width="500">
<h2>Booking for: <span style="color:#28a745"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></span></h2>
<div class="price-per-km">
    Price per km: <strong>₹<?= htmlspecialchars($price_km, ENT_QUOTES, 'UTF-8') ?></strong>
    &nbsp;|&nbsp; Price per ton: <strong>₹<?= htmlspecialchars($price_ton, ENT_QUOTES, 'UTF-8') ?></strong>
</div>
<div class="capacity-info">Truck capacity: <strong><?= htmlspecialchars((string)$capacity) ?> ton</strong></div>

<form action="process_booking.php" method="POST">
<?= csrf_input_field() ?>
<input type="hidden" name="truck_name" value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>">
<input type="hidden" name="truck_key" value="<?= htmlspecialchars($truck_key, ENT_QUOTES, 'UTF-8') ?>">
<input type="hidden" id="price_km" name="price_per_km" value="<?= htmlspecialchars($price_km, ENT_QUOTES, 'UTF-8') ?>">
<input type="hidden" id="price_ton" name="price_per_ton" value="<?= htmlspecialchars($price_ton, ENT_QUOTES, 'UTF-8') ?>">

<label>Your Name</label>
<input type="text" name="customer_name" placeholder="Full name" required>

<label>Date</label>
<input type="date" name="booking_date" min="<?= date('Y-m-d') ?>" max="<?= date('Y-12-31', strtotime('+2 years')) ?>" required>

<label>Address</label>
<textarea name="address" rows="3" placeholder="Full address" required></textarea>

<label>Distance (KM)</label>
<input type="number" id="km" name="km" placeholder="e.g. 50" min="1" oninput="total()" required>

<label>Cargo weight</label>
<div style="display:flex; gap:8px; align-items:center;">
    <input type="number" id="weight_qty" placeholder="e.g. 0.8 or 800" step="0.01" min="0.1" max="<?= htmlspecialchars((string)$capacity) ?>" oninput="total()" required style="flex:1;">
    <select id="weight_unit" onchange="onUnitChange()" style="padding: 10px;">
        <option value="ton" selected>ton</option>
        <option value="kg">kg</option>
        <option value="lb">lb</option>
    </select>
    <input type="hidden" id="weight_ton" name="weight_ton" value="">
    <input type="hidden" id="capacity_ton" value="<?= htmlspecialchars((string)$capacity) ?>">
</div>

<div>Estimated Cost: <span id="total_display">₹0</span></div>
<p class="formula-note">Cost = (₹/km × distance) + (₹/ton × weight)</p>
<button type="submit" class="btn">Confirm Booking</button>
</form>
</div>
</div>

<script nonce="<?= htmlspecialchars($GLOBALS['csp_nonce'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
function onUnitChange(){
  const unit = document.getElementById('weight_unit').value;
  const qty = document.getElementById('weight_qty');
  const capTon = parseFloat(document.getElementById('capacity_ton').value) || 1;
  if(unit === 'kg'){
    qty.step = '1';
    qty.min = '1';
    qty.max = String(Math.round(capTon * 1000));
    if (parseFloat(qty.value) < 1) qty.value = '';
  } else if (unit === 'lb') {
    qty.step = '1';
    qty.min = '1';
    qty.max = String(Math.round(capTon * 2204.62262));
    if (parseFloat(qty.value) < 1) qty.value = '';
  }else{
    qty.step = '0.01';
    qty.min = '0.1';
    qty.max = String(capTon);
  }
  total();
}
function setWeightTonHidden(){
  const unit = document.getElementById('weight_unit').value;
  const qty = parseFloat(document.getElementById('weight_qty').value) || 0;
  let weightTon;
  if (unit === 'kg') {
    weightTon = qty / 1000.0;
  } else if (unit === 'lb') {
    weightTon = qty / 2204.6226218488; // lb -> metric ton
  } else {
    weightTon = qty; // ton
  }
  document.getElementById('weight_ton').value = weightTon > 0 ? weightTon.toFixed(4) : '';
  return weightTon;
}
function total(){
  const priceKm = parseFloat(document.getElementById('price_km').value) || 0;
  const priceTon = parseFloat(document.getElementById('price_ton').value) || 0;
  const km = parseFloat(document.getElementById('km').value) || 0;
  const weightTon = setWeightTonHidden() || 0;
  const t = (priceKm * km) + (priceTon * weightTon);
  document.getElementById('total_display').innerText = "₹" + (Math.round(t * 100) / 100);
}
onUnitChange();
</script>

</body>
</html>
