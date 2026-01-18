<?php
require_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['adminLoggedIn'])) {
    $name = $_POST['service_name'] ?? '';
    $price = $_POST['price'] ?? '';
    $image = $_POST['image_path'] ?? 'imgs/default.jpg';
    
    $stmt = $conn->prepare("INSERT INTO SalonService (name, price, image_path) VALUES (?, ?, ?)");
    $stmt->bind_param("sds", $name, $price, $image);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Service added successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error adding service.']);
    }
    $stmt->close();
}
?>