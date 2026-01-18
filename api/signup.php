<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
error_reporting(E_ALL);

/* ✅ لو صار Fatal Error يرجّع JSON بدل HTML */
register_shutdown_function(function () {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Fatal error',
            'details' => $e['message'],
            'file'    => basename($e['file']),
            'line'    => $e['line'],
        ], JSON_UNESCAPED_UNICODE);
    }
});

require_once __DIR__ . '/db.php';

function respond($arr, $code = 200) {
    http_response_code($code);
    echo json_encode($arr, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['success' => false, 'message' => 'Invalid request method.'], 405);
}

$name     = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$phone    = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm-password'] ?? '';

$errors = [];
if ($name === '' || $email === '' || $phone === '' || $password === '' || $confirm === '') {
    $errors[] = "Please fill in all fields.";
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format.";
}
if (strlen($password) < 8) {
    $errors[] = "Password must be at least 8 characters.";
}
if ($password !== $confirm) {
    $errors[] = "Passwords do not match.";
}
if ($errors) {
    respond(['success' => false, 'errors' => $errors], 422);
}

/* ✅ تحقق من التكرار */
$check = $conn->prepare("SELECT id FROM users WHERE email = ? OR phone = ? LIMIT 1");
if (!$check) {
    respond(['success' => false, 'message' => 'Prepare failed', 'details' => $conn->error], 500);
}
$check->bind_param("ss", $email, $phone);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    $check->close();
    respond([
        'success' => false,
        'code'    => 'account_exists',
        'message' => 'Your account already exists. Please log in to continue.'
    ]);
}
$check->close();

/* ✅ إنشاء الحساب */
$hash = password_hash($password, PASSWORD_DEFAULT);

$ins = $conn->prepare("INSERT INTO users (full_name, email, phone, password, role) VALUES (?, ?, ?, ?, 'user')");
if (!$ins) {
    respond(['success' => false, 'message' => 'Prepare failed', 'details' => $conn->error], 500);
}

$ins->bind_param("ssss", $name, $email, $phone, $hash);

if ($ins->execute()) {
    $ins->close();
    respond([
        'success' => true,
        'message' => 'Welcome to AMARA! Your account has been created successfully. Please log in to continue.'
    ]);
} else {
    $details = $ins->error;
    $ins->close();
    respond(['success' => false, 'message' => 'Insert failed', 'details' => $details], 500);
}
