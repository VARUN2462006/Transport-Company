<?php
require_once __DIR__ . '/Comman Point/security_bootstrap.php';
ob_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}
require_once __DIR__ . '/Comman Point/csrf_helper.php';
include 'connect.php';
$email = $_SESSION['email'];

// Join with drivers to get driver info
$sql = "SELECT b.*, d.name AS driver_name, d.phone AS driver_phone 
        FROM bookings b 
        LEFT JOIN drivers d ON b.driver_id = d.id 
        WHERE b.user_email = ? 
        ORDER BY b.booking_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
        :root {
            --bg-deep: #0a0a1a;
            --bg-surface: rgba(255,255,255,0.04);
            --glass-border: rgba(255,255,255,0.08);
            --orange: #ff7300;
            --orange-light: #ff9a44;
            --orange-glow: rgba(255,115,0,0.25);
            --gold: #ffb366;
            --text-primary: #f0f0f5;
            --text-secondary: rgba(255,255,255,0.55);
            --text-muted: rgba(255,255,255,0.35);
            --radius: 20px;
            --radius-sm: 12px;
            --font: 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
            --ease: cubic-bezier(0.25,0.8,0.25,1);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: var(--font); background: var(--bg-deep); color: var(--text-primary); min-height: 100vh; overflow-x: hidden; }
        body::before { content:''; position:fixed; width:500px; height:500px; border-radius:50%; background:radial-gradient(circle,var(--orange),transparent 70%); top:-150px; right:-100px; filter:blur(120px); opacity:0.1; z-index:0; pointer-events:none; }

        /* Navbar */
        .sb-navbar { position:sticky; top:0; z-index:100; display:flex; align-items:center; justify-content:space-between; padding:14px 30px; background:rgba(10,10,26,0.85); backdrop-filter:blur(24px); -webkit-backdrop-filter:blur(24px); border-bottom:1px solid var(--glass-border); }
        .sb-navbar .brand { display:flex; align-items:center; gap:12px; text-decoration:none; }
        .sb-navbar .brand img { width:44px; height:44px; border-radius:12px; object-fit:cover; border:2px solid rgba(255,115,0,0.4); box-shadow:0 0 15px rgba(255,115,0,0.12); }
        .sb-navbar .brand span { font-weight:700; font-size:1.1rem; color:var(--text-primary); }
        .sb-navbar .nav-actions { display:flex; gap:10px; }
        .sb-navbar .nav-actions a { text-decoration:none; color:var(--text-secondary); font-weight:500; font-size:0.88rem; padding:9px 18px; border-radius:var(--radius-sm); background:rgba(255,255,255,0.06); border:1px solid var(--glass-border); transition:all 0.3s var(--ease); }
        .sb-navbar .nav-actions a:hover { background:rgba(255,115,0,0.12); border-color:rgba(255,115,0,0.3); color:var(--gold); }

        /* Container */
        .booking-container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 32px;
            background: var(--bg-surface);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius);
            backdrop-filter: blur(12px);
            position: relative;
            z-index: 1;
            animation: fadeIn 0.5s var(--ease);
        }
        @keyframes fadeIn { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }

        h2 { text-align:center; margin-bottom:28px; font-weight:700; font-size:1.5rem; color:var(--text-primary); }
        
        .booking-cards { display: grid; gap: 20px; }
        
        .booking-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 22px 24px;
            transition: all 0.35s var(--ease);
        }
        .booking-card:hover { background:rgba(255,255,255,0.06); border-color:rgba(255,115,0,0.15); box-shadow:0 8px 30px rgba(0,0,0,0.3); }
        
        .card-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:10px; }
        .card-header h3 { margin:0; font-size:1.1rem; color:var(--text-primary); font-weight:600; }
        
        .card-body { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:14px; }
        
        .card-field .label { font-size:0.72rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; font-weight:600; }
        .card-field .value { font-size:0.95rem; color:var(--text-primary); font-weight:500; margin-top:3px; }
        
        /* Driver box */
        .driver-box { background:rgba(16,185,129,0.08); border:1px solid rgba(16,185,129,0.2); border-radius:var(--radius-sm); padding:16px 18px; margin-top:16px; }
        .driver-box h4 { margin:0 0 10px; font-size:0.85rem; color:#6ee7b7; font-weight:600; }
        .driver-box .driver-detail { display:flex; align-items:center; gap:8px; margin-bottom:5px; }
        .driver-box .driver-detail span { font-size:0.9rem; color:var(--text-primary); }
        .driver-box .driver-detail .icon { font-size:1rem; }
        .driver-box .call-btn { display:inline-flex; align-items:center; gap:6px; margin-top:10px; background:linear-gradient(135deg,#10b981,#34d399); color:#fff; padding:8px 20px; border-radius:50px; text-decoration:none; font-size:0.82rem; font-weight:600; transition:all 0.3s var(--ease); box-shadow:0 4px 15px rgba(16,185,129,0.2); }
        .driver-box .call-btn:hover { transform:translateY(-2px); box-shadow:0 8px 25px rgba(16,185,129,0.3); }
        
        .no-driver { background:rgba(255,115,0,0.08); border:1px solid rgba(255,115,0,0.2); border-radius:var(--radius-sm); padding:12px 16px; margin-top:14px; color:var(--gold); font-size:0.85rem; }
        
        /* Status badges */
        .status-badge { padding:6px 16px; border-radius:50px; font-size:0.78rem; font-weight:600; display:inline-block; letter-spacing:0.3px; }
        .status-pending { background:rgba(255,183,77,0.12); color:#ffb74d; border:1px solid rgba(255,183,77,0.2); }
        .status-accepted { background:rgba(56,189,248,0.12); color:#38bdf8; border:1px solid rgba(56,189,248,0.2); }
        .status-on_the_way { background:rgba(99,102,241,0.12); color:#a5b4fc; border:1px solid rgba(99,102,241,0.2); }
        .status-delivered { background:rgba(16,185,129,0.12); color:#6ee7b7; border:1px solid rgba(16,185,129,0.2); }
        .status-rejected { background:rgba(239,68,68,0.12); color:#fca5a5; border:1px solid rgba(239,68,68,0.2); }
        .reject-reason { color:#fca5a5; font-size:0.82rem; margin-top:8px; font-style:italic; }
        
        /* Progress tracker */
        .progress-track { display:flex; align-items:center; justify-content:center; gap:0; margin-top:16px; flex-wrap:wrap; }
        .progress-step { display:flex; align-items:center; gap:6px; font-size:0.73rem; color:var(--text-muted); font-weight:500; }
        .progress-step.active { color:var(--orange); font-weight:700; }
        .progress-step.completed { color:#6ee7b7; }
        .progress-step .dot { width:10px; height:10px; border-radius:50%; background:rgba(255,255,255,0.15); }
        .progress-step.active .dot { background:var(--orange); box-shadow:0 0 8px var(--orange-glow); }
        .progress-step.completed .dot { background:#10b981; box-shadow:0 0 6px rgba(16,185,129,0.3); }
        .progress-line { width:30px; height:2px; background:rgba(255,255,255,0.1); margin:0 4px; }
        .progress-line.completed { background:#10b981; }
        
        .card-footer { display:flex; justify-content:space-between; align-items:center; margin-top:16px; flex-wrap:wrap; gap:10px; }
        .cancel-btn { background:rgba(239,68,68,0.15); color:#fca5a5; border:1px solid rgba(239,68,68,0.25); padding:9px 20px; border-radius:var(--radius-sm); cursor:pointer; font-family:var(--font); font-size:0.85rem; font-weight:600; transition:all 0.3s var(--ease); }
        .cancel-btn:hover { background:rgba(239,68,68,0.25); color:#fff; transform:translateY(-2px); }
        
        .empty-state { text-align:center; padding:60px 20px; color:var(--text-secondary); }
        .empty-state .icon { font-size:3rem; margin-bottom:14px; opacity:0.5; }
        .empty-state p { font-size:1.05rem; }
        .empty-state a { color:var(--gold); text-decoration:none; font-weight:600; transition:color 0.3s; }
        .empty-state a:hover { color:var(--orange); }

        @media (max-width: 768px) {
            .booking-container { margin:16px; padding:20px; }
            .card-body { grid-template-columns:1fr 1fr; }
            .card-header { flex-direction:column; align-items:flex-start; }
            .progress-track { gap:2px; }
            .sb-navbar { padding:12px 16px; }
            .sb-navbar .brand span { display:none; }
        }
        @media (max-width: 480px) {
            .card-body { grid-template-columns:1fr; }
        }
    </style>
</head>
<body>

<nav class="sb-navbar">
    <a href="index.php" class="brand">
        <img src="Images/optimized/logo.webp" alt="Logo" width="44" height="44">
        <span>MAHARAJA TRANSPORT</span>
    </a>
    <div class="nav-actions">
        <a href="index.php"><i class="fas fa-home"></i> Home</a>
        <a href="services.php"><i class="fas fa-truck"></i> Services</a>
    </div>
</nav>

<div class="booking-container">
    <h2>📦 My Bookings</h2>
    <?php if ($result->num_rows > 0): ?>
        <div class="booking-cards">
            <?php while($row = $result->fetch_assoc()): 
                $status = $row['status'] ?? 'pending';
                $statusClass = 'status-' . htmlspecialchars($status);
            ?>
            <div class="booking-card">
                <div class="card-header">
                    <h3>🚚 <?= htmlspecialchars($row['truck_name']) ?></h3>
                    <span class="status-badge <?= $statusClass ?>"><?= ucfirst(str_replace('_', ' ', htmlspecialchars($status))) ?></span>
                </div>
                
                <!-- Progress Tracker -->
                <div class="progress-track">
                    <?php
                    $steps = ['pending' => 'Booked', 'accepted' => 'Accepted', 'on_the_way' => 'On The Way', 'delivered' => 'Delivered'];
                    $statusOrder = array_keys($steps);
                    $currentIdx = array_search($status, $statusOrder);
                    if ($status === 'rejected') $currentIdx = -1;
                    $i = 0;
                    foreach ($steps as $key => $label):
                        $cls = '';
                        if ($i < $currentIdx) $cls = 'completed';
                        elseif ($i == $currentIdx) $cls = 'active';
                        if ($i > 0): ?>
                            <div class="progress-line <?= ($i <= $currentIdx) ? 'completed' : '' ?>"></div>
                        <?php endif; ?>
                        <div class="progress-step <?= $cls ?>">
                            <div class="dot"></div>
                            <?= $label ?>
                        </div>
                    <?php $i++; endforeach; ?>
                </div>

                <div class="card-body" style="margin-top: 16px;">
                    <div class="card-field">
                        <div class="label">Booking Date</div>
                        <div class="value"><?= date('d M Y', strtotime($row['booking_date'])) ?></div>
                    </div>
                    <div class="card-field">
                        <div class="label">Order Placed</div>
                        <div class="value"><?= date('d M Y', strtotime($row['created_at'])) ?></div>
                    </div>
                    <div class="card-field">
                        <div class="label">Destination</div>
                        <div class="value"><?= htmlspecialchars($row['address']) ?></div>
                    </div>
                    <div class="card-field">
                        <div class="label">Distance</div>
                        <div class="value"><?= htmlspecialchars($row['distance_km']) ?> km</div>
                    </div>
                    <div class="card-field">
                        <div class="label">Total Cost</div>
                        <div class="value" style="color: #ff7300; font-weight: 700;">₹<?= number_format((float)$row['total_cost'], 2) ?></div>
                    </div>
                </div>

                <!-- Driver Info -->
                <?php if (!empty($row['driver_name'])): ?>
                    <div class="driver-box">
                        <h4>🧑‍✈️ Your Driver</h4>
                        <div class="driver-detail">
                            <span class="icon">👤</span>
                            <span><strong><?= htmlspecialchars($row['driver_name']) ?></strong></span>
                        </div>
                        <div class="driver-detail">
                            <span class="icon">📞</span>
                            <span><?= htmlspecialchars($row['driver_phone']) ?></span>
                        </div>
                        <a href="tel:<?= htmlspecialchars($row['driver_phone']) ?>" class="call-btn">📞 Call Driver</a>
                    </div>
                <?php elseif ($status === 'accepted'): ?>
                    <div class="no-driver">⏳ Your booking is accepted! A driver will be assigned soon.</div>
                <?php elseif ($status === 'pending'): ?>
                    <div class="no-driver">⏳ Your booking is under review. We'll update you shortly.</div>
                <?php endif; ?>

                <?php if ($status === 'rejected' && !empty($row['reject_reason'])): ?>
                    <div class="reject-reason" style="margin-top: 12px;">❌ Reason: <?= htmlspecialchars($row['reject_reason']) ?></div>
                <?php endif; ?>

                <!-- Action -->
                <?php if ($status !== 'rejected' && $status !== 'delivered'): ?>
                <div class="card-footer">
                    <div></div>
                    <form action="cancel_booking.php" method="POST" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                        <?= csrf_input_field() ?>
                        <input type="hidden" name="booking_id" value="<?= $row['id'] ?>">
                        <button type="submit" class="cancel-btn">Cancel Booking</button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <div class="icon">📦</div>
            <p>You have no bookings yet.</p>
            <p><a href="services.php">Browse our services →</a></p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
