<?php
session_start();
require_once __DIR__ . '/../Public/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $user_id = trim($_POST['user_id'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($user_id === '' || $password === '') {
        header('Location: index.php?error=' . urlencode('Please enter User Id and Password.'));
        exit;
    }

    $stmt = $conn->prepare("SELECT user_id, password FROM admin_users WHERE user_id = ? AND password = ?");
    $stmt->bind_param("ss", $user_id, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['admin_user_id'] = $user_id;
        $stmt->close();
        header('Location: admin_dashboard.php');
        exit;
    }
    $stmt->close();
}

header('Location: index.php?error=' . urlencode('Invalid User Id or Password.'));
exit;
