<?php

session_set_cookie_params(0); 


$host = "localhost";
$user = "root";
$pass = "";
$db = "projectdb";

// إنشاء الاتصال
$conn = new mysqli($host, $user, $pass, $db);

// التحقق من الاتصال وإرجاع JSON في حال الفشل لضمان عدم تعطل الجافا سكريبت
if ($conn->connect_error) {
    header('Content-Type: application/json');
    die(json_encode([
        "success" => false, 
        "errors" => ["Database connection failed: " . $conn->connect_error]
    ]));
}

$conn->set_charset('utf8mb4');
?>