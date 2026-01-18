<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

// تعطيل عرض الأخطاء النصية لضمان وصول JSON نظيف للمتصفح
error_reporting(0);
ini_set('display_errors', 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? ''); 
    $password = $_POST['password'] ?? '';

    // البحث عن الأدمن بناءً على صورة الداتابيس التي أرسلتِها (full_name)
    $stmt = $conn->prepare("SELECT id, full_name, password FROM users WHERE email = ? AND role = 'admin'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        // مقارنة نصية مباشرة (لأن الباسورد في الداتا بيس عندك 1234)
        if ($password === $row['password']) {
            $_SESSION['loggedIn'] = true;
            $_SESSION['user'] = [
                'id' => $row['id'],
                'name' => $row['full_name'],
                'role' => 'admin'
            ];
            echo json_encode(['success' => true, 'redirect' => 'admin.html']);
        } else {
            echo json_encode(['success' => false, 'errors' => ["كلمة المرور غير صحيحة"]]);
        }
    } else {
        echo json_encode(['success' => false, 'errors' => ["حساب الأدمن غير موجود بهذا الإيميل"]]);
    }
    $stmt->close();
    exit();
}