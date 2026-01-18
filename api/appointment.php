<?php
// تأكدي أن db.php لا يحتوي على session_start مكرر
require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');

// تشغيل الجلسة إذا لم تكن مفعلة للوصول لبيانات المستخدم
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// التأكد من أن المستخدم سجل دخوله لرؤية مواعيده
if (!isset($_SESSION['loggedIn'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$userId = $_SESSION['user']['id'];

// تحديث الاستعلام ليطابق أسماء الأعمدة الجديدة في قاعدة بياناتك
$sql = "SELECT a.id, s.name as service_name, s.price as service_price, a.appointment_date, t.startTime 
        FROM appointments a
        JOIN SalonService s ON a.serviceID = s.serviceID
        JOIN TimeSlot t ON a.slotID = t.slotID
        WHERE a.userID = ? AND a.status = 'confirmed'
        ORDER BY a.appointment_date ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$appointments = [];
while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}

echo json_encode(['success' => true, 'appointments' => $appointments]);
?>