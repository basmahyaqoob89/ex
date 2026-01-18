<?php
require_once 'db.php';
header('Content-Type: application/json');

// 1. حساب إجمالي الحجوزات
$totalRes = $conn->query("SELECT COUNT(*) as count FROM appointments");
$total = $totalRes->fetch_assoc()['count'];

// 2. حساب حجوزات اليوم
$todayRes = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE DATE(appointment_date) = CURDATE()");
$today = $todayRes->fetch_assoc()['count'];

// 3. حساب إجمالي المستخدمين
$usersRes = $conn->query("SELECT COUNT(*) as count FROM users");
$users = $usersRes->fetch_assoc()['count'];

echo json_encode([
    'success' => true,
    'total' => $total,
    'today' => $today,
    'users' => $users
]);
?>