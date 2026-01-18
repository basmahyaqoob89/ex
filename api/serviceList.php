<?php
require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');

$serviceID = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($serviceID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid Service ID']);
    exit();
}

$sqlService = "SELECT * FROM SalonService WHERE serviceID = $serviceID";
$resService = $conn->query($sqlService);
$service = $resService->fetch_assoc();

if (!$service) {
    echo json_encode(['success' => false, 'message' => 'Service not found']);
    exit();
}

$sqlSlots = "SELECT slotID, startTime FROM TimeSlot WHERE available = TRUE";
$resSlots = $conn->query($sqlSlots);
$slots = [];
while($slot = $resSlots->fetch_assoc()) {
    $slots[] = $slot;
}

echo json_encode([
    'success' => true,
    'service' => $service,
    'available_slots' => $slots
]);
exit();
?>