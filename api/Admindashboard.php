<?php
require_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['adminLoggedIn'])) {
    echo json_encode(['success' => false]);
    exit();
}

$res1 = $conn->query("SELECT COUNT(*) as total FROM appointments WHERE appointment_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
$totalBookings = $res1->fetch_assoc()['total'];

$res2 = $conn->query("SELECT COUNT(*) as active FROM appointments WHERE appointment_date = CURDATE() AND status = 'confirmed'");
$activeToday = $res2->fetch_assoc()['active'];

$res3 = $conn->query("SELECT * FROM reviews ORDER BY created_at DESC LIMIT 3");
$reviews = [];
while($r = $res3->fetch_assoc()) { $reviews[] = $r; }

echo json_encode([
    'success' => true,
    'stats' => [
        'total' => $totalBookings,
        'active' => $activeToday,
    ],
    'reviews' => $reviews
]);
?>