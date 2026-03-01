<?php
require_once __DIR__ . '/../Comman Point/security_bootstrap.php';
if (empty($_SESSION['admin_user_id'])) {
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/../Comman Point/csrf_helper.php';
require_once __DIR__ . '/../connect.php';

// CSRF check on all POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_token();
}

$action = $_POST['action'] ?? '';
$booking_id = (int) ($_POST['booking_id'] ?? 0);
$source = $_POST['source'] ?? 'dashboard';
// Whitelist source to prevent redirect manipulation
$allowed_sources = ['dashboard', 'bookings'];
if (!in_array($source, $allowed_sources, true)) {
    $source = 'dashboard';
}
$reject_reason = trim($_POST['reject_reason'] ?? '');
$driver_id = (int) ($_POST['driver_id'] ?? 0);

if (!in_array($action, ['accept', 'reject', 'dispatch', 'deliver', 'assign_driver']) || $booking_id <= 0) {
    header('Location: admin_dashboard.php?msg=invalid');
    exit;
}

if ($action === 'accept') {
    $stmt = $conn->prepare("UPDATE bookings SET status = 'accepted', reject_reason = NULL WHERE id = ?");
    $stmt->bind_param("i", $booking_id);

} elseif ($action === 'reject') {
    $stmt = $conn->prepare("UPDATE bookings SET status = 'rejected', reject_reason = ? WHERE id = ?");
    $stmt->bind_param("si", $reject_reason, $booking_id);

} elseif ($action === 'dispatch') {
    // Dispatch with driver assignment
    if ($driver_id > 0) {
        // Assign driver and set status to on_the_way
        $stmt = $conn->prepare("UPDATE bookings SET status = 'on_the_way', driver_id = ? WHERE id = ?");
        $stmt->bind_param("ii", $driver_id, $booking_id);
        $stmt->execute();
        $stmt->close();

        // Update driver status to 'On Trip'
        $stmt2 = $conn->prepare("UPDATE drivers SET status = 'On Trip' WHERE id = ?");
        $stmt2->bind_param("i", $driver_id);
        $stmt2->execute();
        $stmt2->close();

        $redirect_to = ($source === 'bookings') ? 'bookings.php' : 'admin_dashboard.php';
        header('Location: ' . $redirect_to . '?msg=dispatch&id=' . $booking_id);
        exit;
    } else {
        // No driver selected — dispatch without driver
        $stmt = $conn->prepare("UPDATE bookings SET status = 'on_the_way' WHERE id = ?");
        $stmt->bind_param("i", $booking_id);
    }

} elseif ($action === 'deliver') {
    // Mark delivered and free the driver
    // First get the driver_id from this booking
    $get_drv = $conn->prepare("SELECT driver_id FROM bookings WHERE id = ?");
    $get_drv->bind_param("i", $booking_id);
    $get_drv->execute();
    $get_drv->bind_result($assigned_driver_id);
    $get_drv->fetch();
    $get_drv->close();

    // Update booking
    $stmt = $conn->prepare("UPDATE bookings SET status = 'delivered' WHERE id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $stmt->close();

    // Free the driver
    if ($assigned_driver_id > 0) {
        $free_drv = $conn->prepare("UPDATE drivers SET status = 'Available' WHERE id = ?");
        $free_drv->bind_param("i", $assigned_driver_id);
        $free_drv->execute();
        $free_drv->close();
    }

    $redirect_to = ($source === 'bookings') ? 'bookings.php' : 'admin_dashboard.php';
    header('Location: ' . $redirect_to . '?msg=deliver&id=' . $booking_id);
    exit;

} elseif ($action === 'assign_driver') {
    if ($driver_id > 0) {
        $stmt = $conn->prepare("UPDATE bookings SET driver_id = ? WHERE id = ?");
        $stmt->bind_param("ii", $driver_id, $booking_id);
        $stmt->execute();
        $stmt->close();

        $stmt2 = $conn->prepare("UPDATE drivers SET status = 'On Trip' WHERE id = ?");
        $stmt2->bind_param("i", $driver_id);
        $stmt2->execute();
        $stmt2->close();

        $redirect_to = ($source === 'bookings') ? 'bookings.php' : 'admin_dashboard.php';
        header('Location: ' . $redirect_to . '?msg=assign&id=' . $booking_id);
        exit;
    }
}

$stmt->execute();
$stmt->close();

$redirect_to = ($source === 'bookings') ? 'bookings.php' : 'admin_dashboard.php';
header('Location: ' . $redirect_to . '?msg=' . $action . '&id=' . $booking_id);
exit;
