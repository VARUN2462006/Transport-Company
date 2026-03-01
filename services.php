<?php
require_once __DIR__ . '/Comman Point/security_bootstrap.php';
require_once __DIR__ . '/Comman Point/csrf_helper.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Fleet — Maharaja Transport</title>
    <meta name="description" content="Explore Maharaja Transport's premium fleet — Intra, Yodha, Tata Prima, Ashok Leyland, and Bharat Benz trucks for all cargo needs.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="services.css?v=4.0">
</head>
<body>

<!-- ═══ NAVBAR ═══ -->
<nav class="navbar">
    <a href="index.php" class="brand">
        <img src="Images/optimized/logo.webp" alt="Logo" class="logo" width="50" height="50">
        <span class="brand-name">MAHARAJA TRANSPORT</span>
    </a>
    <div class="nav-actions">
        <a href="index.php" class="nav-link"><i class="fas fa-home"></i> Home</a>
        <?php if (isset($_SESSION['email'])): ?>
            <a href="show_booking.php" class="nav-link"><i class="fas fa-clipboard-list"></i> My Bookings</a>
            <a href="logout.php" class="nav-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        <?php else: ?>
            <a href="login.php" class="nav-btn"><i class="fas fa-sign-in-alt"></i> Login</a>
        <?php endif; ?>
    </div>
</nav>

<!-- ═══ PAGE HEADER ═══ -->
<section class="page-header">
    <span class="tag">Our Fleet</span>
    <h1>Premium Trucks for Every Need</h1>
    <p>From lightweight deliveries to heavy-haul logistics — choose from our carefully maintained fleet.</p>
</section>

<!-- ═══ TRUCK GRID ═══ -->
<div class="services-container">
    <div class="truck-grid">
        <?php
        require_once __DIR__ . '/connect.php';
        $res = $conn->query("SELECT * FROM truck_rates ORDER BY id ASC");
        while($truck = $res->fetch_assoc()):
            $features = array_map('trim', explode(',', $truck['features'] ?? ''));
        ?>
        <div class="truck-card">
            <div class="card-img">
                <img src="Images/optimized/trucks/<?= htmlspecialchars($truck['image_path']) ?>" alt="<?= htmlspecialchars($truck['name']) ?>" loading="lazy">
            </div>
            <div class="card-body">
                <h3 class="truck-name"><?= htmlspecialchars($truck['name']) ?></h3>
                <p class="truck-desc"><?= htmlspecialchars($truck['description']) ?></p>
                <div class="truck-specs">
                    <?php foreach($features as $f): if($f): ?>
                    <span class="spec-badge"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($f) ?></span>
                    <?php endif; endforeach; ?>
                </div>
                <a href="Book.php?truck_name=<?= urlencode($truck['name']) ?>&truck_img=<?= urlencode($truck['image_path']) ?>&truck_key=<?= urlencode($truck['truck_key']) ?>" class="book-btn">
                    <i class="fas fa-truck-fast"></i> Book Now
                </a>
            </div>
        </div>
        <?php endwhile; $conn->close(); ?>
    </div>
</div>

<!-- ═══ CTA SECTION ═══ -->
<?php if (!isset($_SESSION['email'])): ?>
<section class="cta-section">
    <div class="cta-card">
        <h2>Ready to Ship?</h2>
        <p>Create a free account to start booking our premium fleet and track your shipments in real-time.</p>
        <div class="cta-actions">
            <a href="login.php" class="cta-btn-primary"><i class="fas fa-sign-in-alt"></i> Login</a>
            <a href="registor.php" class="cta-btn-secondary"><i class="fas fa-user-plus"></i> Create Account</a>
        </div>
    </div>
</section>
<?php endif; ?>

</body>
</html>