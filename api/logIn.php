<?php
// إيقاف إظهار الأخطاء كنصوص HTML لضمان عدم إفساد الـ JSON
error_reporting(0);
ini_set('display_errors', 0);

session_start(); 
require_once 'db.php'; 

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? ''; 

    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'errors' => ["Please fill in all fields."]]);
        exit();
    }

    $stmt = $conn->prepare("SELECT id, full_name, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['loggedIn'] = true;
            $_SESSION['user'] = [
                'id' => $row['id'],
                'username' => $row['full_name'],
                'email' => $row['email']
            ];
            echo json_encode(['success' => true, 'redirect' => 'Home-pega.html']);
        } else {
            echo json_encode(['success' => false, 'errors' => ["Incorrect password."]]);
        }
    } else {
        echo json_encode(['success' => false, 'errors' => ["User not found."]]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'errors' => ["Invalid Request"]]);
}
?>