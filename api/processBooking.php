<?php
require_once 'db.php'; 
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// التأكد من تسجيل الدخول
if (!isset($_SESSION['loggedIn'])) {
    echo json_encode(['success' => false, 'message' => 'not_logged_in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user']['id'];
    
    $serviceID = $_POST['serviceID'] ?? '';
    $appointmentDate = $_POST['date'] ?? '';
    $slotID = $_POST['slotID'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    $sql = "INSERT INTO appointments (userID, serviceID, appointment_date, slotID, notes, status) VALUES (?, ?, ?, ?, ?, 'confirmed')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisss", $userId, $serviceID, $appointmentDate, $slotID, $notes);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }
}
?>