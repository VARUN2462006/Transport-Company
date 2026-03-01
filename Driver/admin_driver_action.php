<?php
require_once __DIR__ . '/../Comman Point/security_bootstrap.php';
if (empty($_SESSION['admin_user_id'])) {
    header('Location: ../VARUN/index.php');
    exit;
}
require_once __DIR__ . '/../Comman Point/csrf_helper.php';
require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // CSRF check on all POST actions
    require_csrf_token();
    
    // Add New Driver
    if ($_POST['action'] === 'add_driver') {
        $name = trim($_POST['name']);
        $license = trim($_POST['license']);
        $phone = trim($_POST['phone']);
        $salary = isset($_POST['salary']) ? (float)$_POST['salary'] : 0.00;
        
        if($name && $license && $phone) {
            $stmt = $conn->prepare("INSERT INTO drivers (name, license_number, phone, salary) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssd", $name, $license, $phone, $salary);
            
            if ($stmt->execute()) {
                $_SESSION['msg'] = "Driver added successfully!";
            } else {
                $_SESSION['error_msg'] = "Failed to add driver. Ensure license number is unique.";
            }
            $stmt->close();
        } else {
            $_SESSION['error_msg'] = "All fields are required.";
        }
        header("Location: admin_drivers.php");
        exit;
    }
    
    // Update Driver Status (Manual Override)
    if ($_POST['action'] === 'update_status') {
        $driver_id = (int)$_POST['driver_id'];
        $new_status = $_POST['new_status'];
        
        $valid_statuses = ['Available', 'On Trip', 'On Leave'];
        if (in_array($new_status, $valid_statuses)) {
            $stmt = $conn->prepare("UPDATE drivers SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $new_status, $driver_id);
            if($stmt->execute()) {
                $_SESSION['msg'] = "Driver status updated to $new_status.";
            } else {
                 $_SESSION['error_msg'] = "Failed to update status.";
            }
            $stmt->close();
        }
        header("Location: admin_drivers.php");
        exit;
    }

    // Fire Driver
    if ($_POST['action'] === 'fire_driver') {
        $driver_id = (int)$_POST['driver_id'];
        $stmt = $conn->prepare("UPDATE drivers SET status = 'Fired' WHERE id = ?");
        $stmt->bind_param("i", $driver_id);
        if($stmt->execute()) {
            $_SESSION['msg'] = "Driver has been marked as Fired.";
        } else {
             $_SESSION['error_msg'] = "Failed to fire driver.";
        }
        $stmt->close();
        header("Location: admin_drivers.php");
        exit;
    }

    // Simulate Leave Request
    if ($_POST['action'] === 'simulate_leave') {
        $driver_id = (int)$_POST['driver_id'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $reason = trim($_POST['reason']);
        
        $stmt = $conn->prepare("INSERT INTO driver_leaves (driver_id, start_date, end_date, reason) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $driver_id, $start_date, $end_date, $reason);
        if($stmt->execute()){
            $_SESSION['msg'] = "Leave request submitted successfully (Simulation).";
        } else {
            $_SESSION['error_msg'] = "Failed to submit leave request.";
        }
        $stmt->close();
        header("Location: admin_drivers.php");
        exit;
    }

    // Handle Leave Request (Approve/Reject)
    if ($_POST['action'] === 'handle_leave') {
        $leave_id = (int)$_POST['leave_id'];
        $driver_id = (int)$_POST['driver_id'];
        $status = $_POST['status']; // 'Approved' or 'Rejected'
        
        if (in_array($status, ['Approved', 'Rejected'])) {
            // Update leave request status
            $stmt = $conn->prepare("UPDATE driver_leaves SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $leave_id);
            if ($stmt->execute()) {
                $_SESSION['msg'] = "Leave request $status.";
                
                // If reasonably approved, update driver status automatically to 'On Leave'
                if ($status == 'Approved') {
                    $update_driver = $conn->prepare("UPDATE drivers SET status = 'On Leave' WHERE id = ?");
                    $update_driver->bind_param("i", $driver_id);
                    $update_driver->execute();
                }
            } else {
                $_SESSION['error_msg'] = "Failed to process leave request.";
            }
            $stmt->close();
        }
        header("Location: admin_drivers.php");
        exit;
    }

}

header("Location: admin_drivers.php");
exit;
?>
