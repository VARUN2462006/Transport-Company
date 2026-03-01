<?php
require_once __DIR__ . '/../Comman Point/security_bootstrap.php';
if (empty($_SESSION['admin_user_id'])) {
    header('Location: ../index.php');
    exit;
}
require_once __DIR__ . '/../Comman Point/csrf_helper.php';
require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // CSRF check on all POST actions
    require_csrf_token();
    
    // Add New Service Model (user-facing catalog)
    if ($_POST['action'] === 'add_model') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $features = trim($_POST['features'] ?? '');
        // Capacity: accept qty + unit, store as tons
        $cap_qty = (float)($_POST['capacity_qty'] ?? $_POST['capacity_ton'] ?? 1);
        $cap_unit = $_POST['capacity_unit'] ?? 'ton';
        if ($cap_unit === 'kg') {
            $capacity_ton = $cap_qty / 1000.0;
        } elseif ($cap_unit === 'lb') {
            $capacity_ton = $cap_qty / 2204.6226218488;
        } else {
            $capacity_ton = $cap_qty;
        }
        $price_per_km = (int)($_POST['price_per_km'] ?? 0);
        $price_per_ton = (float)($_POST['price_per_ton'] ?? 0);
        $image_path = '';

        if ($name === '' || $capacity_ton <= 0 || $price_per_km < 0 || $price_per_ton < 0) {
            $_SESSION['error_msg'] = "Please provide valid model name, capacity, and prices.";
            header("Location: admin_trucks.php");
            exit;
        }

        // Generate truck_key slug from name
        $truck_key = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
        if ($truck_key === '') $truck_key = 'truck-' . time();

        // Handle image upload
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
                    if (!is_dir($upload_dir)) { @mkdir($upload_dir, 0777, true); }
                    if (move_uploaded_file($tmp, $upload_dir . $new_filename)) {
                        $image_path = $new_filename;
                    }
                }
            }
        }

        // Ensure unique truck_key
        try {
            $check = $conn->prepare("SELECT id FROM truck_rates WHERE truck_key = ?");
            if ($check) {
                $check->bind_param("s", $truck_key);
                $check->execute();
                $res = $check->get_result();
                if ($res && $res->num_rows > 0) {
                    $truck_key .= '-' . substr(md5((string)time()), 0, 6);
                }
                $check->close();
            }
        } catch (Exception $e) {}

        // Insert into catalog
        try {
            $insert = $conn->prepare("INSERT INTO truck_rates (truck_key, name, description, image_path, features, capacity_ton, price_per_km, price_per_ton) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if ($insert) {
                $insert->bind_param("sssssddd", $truck_key, $name, $description, $image_path, $features, $capacity_ton, $price_per_km, $price_per_ton);
                if ($insert->execute()) {
                    $_SESSION['msg'] = "Service model \"$name\" added to catalog.";
                } else {
                    $_SESSION['error_msg'] = "Failed to add model to catalog.";
                }
                $insert->close();
            } else {
                $_SESSION['error_msg'] = "Database error: Unable to prepare insert for catalog.";
            }
        } catch (Exception $e) {
            $_SESSION['error_msg'] = "Database error: " . $e->getMessage();
        }
        header("Location: admin_trucks.php");
        exit;
    }
    
    // Add New Truck
    if ($_POST['action'] === 'add_truck') {
        $truck_number = trim($_POST['truck_number'] ?? '');
        $truck_model = trim($_POST['truck_model'] ?? '');
        $truck_type = trim($_POST['truck_type'] ?? '');
        $driver_name = NULL;
        $driver_phone = NULL;
        $status = 'available'; // Default status
        
        if($truck_number && $truck_model && $truck_type) {
            try {
                $stmt = $conn->prepare("INSERT INTO trucks (truck_number, truck_model, truck_type, driver_name, driver_phone, status) VALUES (?, ?, ?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("ssssss", $truck_number, $truck_model, $truck_type, $driver_name, $driver_phone, $status);
                    if ($stmt->execute()) {
                        $_SESSION['msg'] = "Truck $truck_number added successfully!";
                    } else {
                        $_SESSION['error_msg'] = "Failed to add truck. Ensure plate number is unique.";
                    }
                    $stmt->close();
                } else {
                    $_SESSION['error_msg'] = "Database error: Unable to prepare statement (ensure trucks database table is created).";
                }
            } catch (Exception $e) {
                $_SESSION['error_msg'] = "Database error: " . $e->getMessage();
            }
        } else {
            $_SESSION['error_msg'] = "Truck Number, Model, and Type are required fields.";
        }
        header("Location: admin_trucks.php");
        exit;
    }
    
    // Update Truck Status
    if ($_POST['action'] === 'update_status') {
        $truck_id = (int)($_POST['truck_id'] ?? 0);
        $new_status = $_POST['new_status'] ?? '';
        
        $valid_statuses = ['available', 'in_transit', 'maintenance'];
        if (in_array($new_status, $valid_statuses)) {
            try {
                $stmt = $conn->prepare("UPDATE trucks SET status = ? WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param("si", $new_status, $truck_id);
                    if($stmt->execute()) {
                        $_SESSION['msg'] = "Truck status updated to $new_status.";
                    } else {
                        $_SESSION['error_msg'] = "Failed to update status.";
                    }
                    $stmt->close();
                }
            } catch (Exception $e) {}
        }
        header("Location: admin_trucks.php");
        exit;
    }

    // Delete Truck
    if ($_POST['action'] === 'delete_truck') {
        $truck_id = (int)($_POST['truck_id'] ?? 0);
        try {
            $stmt = $conn->prepare("DELETE FROM trucks WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $truck_id);
                if($stmt->execute()) {
                    $_SESSION['msg'] = "Truck removed from inventory permanently.";
                } else {
                    $_SESSION['error_msg'] = "Failed to remove truck.";
                }
                $stmt->close();
            }
        } catch (Exception $e) {}
        header("Location: admin_trucks.php");
        exit;
    }
}

header("Location: admin_trucks.php");
exit;
?>
