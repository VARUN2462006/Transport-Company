<?php
require_once __DIR__ . '/../Comman Point/security_bootstrap.php';
if (empty($_SESSION['admin_user_id'])) {
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/../Comman Point/csrf_helper.php';
require_once __DIR__ . '/../connect.php';

$id = (int)($_GET['id'] ?? 0);
$is_new = ($id === 0);

$truck = [];
if (!$is_new) {
    $stmt = $conn->prepare("SELECT * FROM truck_rates WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $truck = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$truck) {
        header('Location: truck_rates.php');
        exit;
    }
} else {
    // Default values for new truck model
    $truck = [
        'truck_key' => '',
        'name' => '',
        'description' => '',
        'features' => '',
        'capacity_ton' => 1.0,
        'price_per_km' => 0,
        'price_per_ton' => 0,
        'image_path' => ''
    ];
}

// Handle Insert/Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_token();
    
    $name = trim($_POST['name']);
    
    // For new trucks, we MUST generate a truck_key from the name BEFORE doing anything else
    $truck_key = $is_new ? trim(strtolower(str_replace(' ', '-', $name))) : $truck['truck_key'];
    // Fallback if name was empty somehow
    if(empty($truck_key)) $truck_key = 'truck-' . time();

    $desc = trim($_POST['description']);
    $features = trim($_POST['features']);
    // Capacity: accept qty + unit and store in tons
    $cap_qty = (float)($_POST['capacity_qty'] ?? $_POST['capacity_ton'] ?? 0);
    $cap_unit = $_POST['capacity_unit'] ?? 'ton';
    if ($cap_unit === 'kg') {
        $cap = $cap_qty / 1000.0;
    } elseif ($cap_unit === 'lb') {
        $cap = $cap_qty / 2204.6226218488;
    } else {
        $cap = $cap_qty;
    }
    $p_km = (int)$_POST['price_per_km'];
    $p_ton = (float)$_POST['price_per_ton'];
    
    $image_path = $truck['image_path'];
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['image']['tmp_name'];
        $size = (int)($_FILES['image']['size'] ?? 0);
        if (is_uploaded_file($tmp) && $size > 0 && $size <= 1048576) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $tmp);
            finfo_close($finfo);
            $allowed_mimes = ['image/jpeg','image/png','image/webp','image/avif'];
            if (in_array($mime, $allowed_mimes, true)) {
                $ext_map = [
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/webp' => 'webp',
                    'image/avif' => 'avif'
                ];
                $ext = $ext_map[$mime] ?? strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $new_filename = $truck_key . '_' . time() . '.' . $ext;
                $upload_dir = __DIR__ . '/../Images/optimized/trucks/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                if (move_uploaded_file($tmp, $upload_dir . $new_filename)) {
                    $image_path = $new_filename;
                }
            }
        }
    }

    if ($is_new) {
        $insert = $conn->prepare("INSERT INTO truck_rates (truck_key, name, description, image_path, features, capacity_ton, price_per_km, price_per_ton) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $insert->bind_param("sssssddd", $truck_key, $name, $desc, $image_path, $features, $cap, $p_km, $p_ton);
        if ($insert->execute()) {
            header("Location: truck_rates.php?msg=added");
            exit;
        } else {
             $error = "Failed to add new record.";
        }
    } else {
        $update = $conn->prepare("UPDATE truck_rates SET name=?, description=?, image_path=?, features=?, capacity_ton=?, price_per_km=?, price_per_ton=? WHERE id=?");
        $update->bind_param("ssssdddi", $name, $desc, $image_path, $features, $cap, $p_km, $p_ton, $id);
        if ($update->execute()) {
            header("Location: truck_rates.php?msg=updated");
            exit;
        } else {
            $error = "Failed to update record.";
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin_truck_rates.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Edit Truck Model – Admin</title>
    <style>
        .edit-form { max-width: 600px; margin: 2rem auto; background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .edit-form label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #2c3e50; }
        .edit-form input[type="text"], .edit-form input[type="number"], .edit-form textarea { width: 100%; padding: 0.8rem; margin-bottom: 1rem; border: 1px solid #ced4da; border-radius: 4px; }
        .edit-form img { max-width: 200px; border-radius: 8px; margin-bottom: 1rem; }
        .edit-form button { background: #27ae60; color: white; border: none; padding: 0.8rem 1.5rem; cursor: pointer; border-radius: 4px; font-size: 1rem; }
        .edit-form button:hover { background: #2ecc71; }
    </style>
</head>
<body>
    <header class="admin-header">
        <h1>Admin Panel</h1>
        <nav class="admin-nav">
            <a href="truck_rates.php"><i class="fas fa-arrow-left"></i> Back to Models</a>
        </nav>
    </header>

    <main class="rates-main">
        <section class="rates-section">
            <div class="edit-form">
                <h2><?= $is_new ? '<i class="fas fa-plus"></i> Add New Truck Model' : 'Edit Truck Model: ' . htmlspecialchars($truck['truck_key']) ?></h2>
                <?php if(isset($error)) echo "<p class='flash flash-error'>$error</p>"; ?>
                
                <form method="post" enctype="multipart/form-data">
                    <?= csrf_input_field() ?>
                    
                    <label>Public Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($truck['name'] ?? ucfirst($truck['truck_key'])) ?>" required>

                    <label>Description</label>
                    <textarea name="description" rows="3"><?= htmlspecialchars($truck['description'] ?? '') ?></textarea>

                    <label>Features (Comma separated)</label>
                    <input type="text" name="features" value="<?= htmlspecialchars($truck['features'] ?? '') ?>" placeholder="e.g. 1.5 Ton, Short Haul, AC">

                    <label>Capacity</label>
                    <div style="display:flex; gap:8px; align-items:center;">
                        <input type="number" step="0.01" name="capacity_qty" value="<?= (float)($truck['capacity_ton'] ?? 1.0) ?>" required style="flex:1;">
                        <select name="capacity_unit" style="padding:0.6rem;">
                            <option value="ton" selected>ton</option>
                            <option value="kg">kg</option>
                            <option value="lb">lb</option>
                        </select>
                    </div>
                    <small style="color:#7f8c8d; display:block; margin:6px 0 10px;">Saved internally as tons.</small>

                    <label>Price per KM (₹)</label>
                    <input type="number" step="1" name="price_per_km" value="<?= (int)$truck['price_per_km'] ?>" required>

                    <label>Price per Ton (₹)</label>
                    <input type="number" step="0.01" name="price_per_ton" value="<?= (float)$truck['price_per_ton'] ?>" required>

                    <label>Current Image</label>
                    <?php if(!empty($truck['image_path'])): ?>
                        <img src="../Images/optimized/trucks/<?= htmlspecialchars($truck['image_path']) ?>" alt="Truck Image">
                    <?php endif; ?>
                    <input type="file" name="image" accept="image/*">
                    <small style="display:block; margin-bottom:1rem; color:#7f8c8d;">Leave file input empty to keep current image.</small>

                    <button type="submit"><i class="fas fa-save"></i> Save Changes</button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
