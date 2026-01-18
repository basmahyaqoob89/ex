<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

// التحقق من وجود المعرف والجلسة
if (isset($_POST['id']) && isset($_SESSION['user']['id'])) {
    $appointmentID = $_POST['id'];
    $userId = $_SESSION['user']['id'];
    
    // الحذف باستخدام الأسماء الصحيحة للأعمدة في جدولك
    $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ? AND userID = ?");
    $stmt->bind_param("ii", $appointmentID, $userId);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Appointment not found.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid Request']);
}
?>