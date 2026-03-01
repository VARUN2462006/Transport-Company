<?php
require_once __DIR__ . '/Comman Point/security_bootstrap.php';
ob_start();
$is_logged_in = isset($_SESSION['email']);
$user_initial = '';
if ($is_logged_in) {
    require_once __DIR__ . '/connect.php';
    $email = $_SESSION['email'];
    $stmt = $conn->prepare("SELECT name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $name = trim((string)$row['name']);
        $user_initial = strtoupper(substr($name, 0, 1));
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
    <title>MAHARAJA TRANSPORT COMPANY</title>
    <meta name="description" content="Maharaja Transport Company — Trusted pan-India road transport for FTL & PTL cargo. Safe, timely, cost-effective truck transport services.">
    
    <!-- DNS Prefetching -->
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Preload Critical Assets -->
    <link rel="preload" href="Homepage.css?v=3.1" as="style">
    <link rel="preload" href="Images/optimized/logo.webp" as="image">
    <link rel="preload" href="Images/optimized/header.webp" as="image">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Homepage.css?v=3.1">
    
    <script src="Homepage.js" defer></script>
</head>
<body>

<!-- ============ NAVBAR ============ -->
<div class="menu">
    <div class="left-brand">
        <?php if ($is_logged_in): ?>
            <div style="width:60px;height:60px;border-radius:50%;background:#ff7300;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;letter-spacing:0.5px;">
                <?= htmlspecialchars($user_initial ?: 'U', ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php else: ?>
            <img src="Images/optimized/logo.webp" alt="Maharaja Transport Logo" class="brand-icon" width="60" height="60">
        <?php endif; ?>
        <h1 class="logo-text">MAHARAJA TRANSPORT</h1>
    </div>

    <button class="hamburger" id="hamburgerBtn" aria-label="Toggle navigation">
        <span></span><span></span><span></span>
    </button>

    <nav class="center-nav" id="mainNav">
        <a href="index.php" class="nav-link">Home</a>
        <a href="#about" class="nav-link">About</a>
        <a href="#features" class="nav-link">Why Us</a>
        <a href="#contact" class="nav-link">Contact</a>
        <a href="services.php" class="nav-link">Services</a> 
        <?php if (isset($_SESSION['email'])): ?>
            <a href="show_booking.php" class="nav-link">My Bookings</a> 
        <?php endif; ?>
    </nav>

    <div class="right-actions" id="rightActions">
        <?php if ($is_logged_in): ?>
            <a href="logout.php" class="action-btn logout-btn">Logout</a>
        <?php else: ?>
            <a href="login.php" class="action-btn login-btn">Login</a>
        <?php endif; ?>
    </div>
</div>

<!-- ============ HERO SECTION ============ -->
<section class="hero">
    <img src="Images/optimized/header.webp" alt="Maharaja Transport Fleet" class="hero-bg" width="1920" height="800">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h2 class="hero-title">Move Your Cargo<br><span>Across India</span></h2>
        <p class="hero-subtitle">Full & Part Truckload services connecting businesses from Mumbai to Delhi, Chennai to Kolkata — safe, timely, and cost-effective.</p>
        <div class="hero-actions">
            <a href="services.php" class="hero-btn hero-btn-primary">
                <i class="fas fa-truck-fast"></i> Book a Truck
            </a>
            <a href="#about" class="hero-btn hero-btn-secondary">
                Learn More <i class="fas fa-arrow-down"></i>
            </a>
        </div>
    </div>
</section>

<!-- ============ FEATURES SECTION ============ -->
<section class="features-section" id="features">
    <div class="section-header">
        <span class="section-tag">Why Choose Us</span>
        <h2 class="section-title">Built for Reliability</h2>
        <p class="section-desc">We don't just transport cargo — we deliver peace of mind.</p>
    </div>
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon"><i class="fas fa-shield-halved"></i></div>
            <h3>Safe & Secure</h3>
            <p>Every consignment is insured and handled with care by trained professionals.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon"><i class="fas fa-clock"></i></div>
            <h3>On-Time Delivery</h3>
            <p>We guarantee timely delivery with real-time tracking and route optimization.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon"><i class="fas fa-indian-rupee-sign"></i></div>
            <h3>Best Rates</h3>
            <p>Transparent, competitive pricing with no hidden charges. Pay per KM + per Ton.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon"><i class="fas fa-map-location-dot"></i></div>
            <h3>Pan-India Network</h3>
            <p>Covering 100+ cities including Mumbai, Delhi, Chennai, Kolkata, and Bengaluru.</p>
        </div>
    </div>
</section>

<!-- ============ ABOUT SECTION ============ -->
<section id="about" class="about-section">
    <div class="about-container">
        <div class="about-badge">About Us</div>
        <h2 class="about-title">India's Growing Transport Company</h2>
        <div class="about-text">
            <p>
                <strong>Maharaja Transport Company</strong> is a trusted road‑transport partner that specializes in full‑truckload (FTL) and part‑truckload (PTL) cargo movement across India. Operating exclusively through a modern fleet of trucks, we connect businesses, manufacturers, and traders with reliable, on‑time deliveries from one corner of the country to another.
            </p>
            <p>
                Our mission is to provide <strong>safe, timely, and cost‑effective truck transport services</strong> that keep your supply chain moving smoothly. We focus on transparency, real‑time tracking, and responsive customer support so you always know where your goods are and when they will reach their destination.
            </p>
            <p>
                We offer <strong>pan‑India truck transport</strong>, including Full Truck Load (FTL) for large volumes and Part Truck Load (PTL) for smaller shipments, serving major industrial and commercial hubs. Our well‑maintained trucks and trained drivers ensure that every consignment reaches its destination safely and on schedule.
            </p>
        </div>
        <a href="services.php" class="about-cta">
            <i class="fas fa-arrow-right"></i> Explore Our Fleet
        </a>
    </div>
</section>

<!-- ============ CONTACT SECTION ============ -->
<section class="contact-section" id="contact">
    <div class="section-header">
        <span class="section-tag">Get In Touch</span>
        <h2 class="section-title">Contact Us</h2>
    </div>
    <div class="contact-grid">
        <div class="contact-card">
            <div class="contact-icon"><i class="fas fa-phone"></i></div>
            <strong>Phone</strong>
            <p>+91 9405228607</p>
        </div>
        <div class="contact-card">
            <div class="contact-icon"><i class="fas fa-envelope"></i></div>
            <strong>Email</strong>
            <p>athawalevarun3@gmail.com</p>
        </div>
        <div class="contact-card">
            <div class="contact-icon"><i class="fas fa-location-dot"></i></div>
            <strong>Address</strong>
            <p>123 Transport Street, Miraj, Maharashtra</p>
        </div>
    </div>
</section>

<!-- ============ FOOTER ============ -->
<footer class="site-footer">
    <div class="footer-content">
        <div class="footer-brand">
            <img src="Images/optimized/logo.webp" alt="Logo" width="40" height="40">
            <span>Maharaja Transport Company</span>
        </div>
        <p class="footer-copy">&copy; <?= date('Y') ?> Maharaja Transport. All rights reserved.</p>
    </div>
</footer>

</body>
</html>
