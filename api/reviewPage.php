<?php
header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', '0');
error_reporting(E_ALL);

/* ✅ يطلع سبب 500 كـ JSON بدل رد فاضي */
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

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

function respond($arr, $code = 200) {
    http_response_code($code);
    echo json_encode($arr, JSON_UNESCAPED_UNICODE);
    exit;
}

/* Helpers: تحقق وجود جدول/عمود */
function tableExists(mysqli $conn, string $table): bool {
    $table = $conn->real_escape_string($table);
    $sql = "SELECT 1 FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$table' LIMIT 1";
    $res = $conn->query($sql);
    return $res && $res->num_rows > 0;
}

function columnExists(mysqli $conn, string $table, string $column): bool {
    $table = $conn->real_escape_string($table);
    $column = $conn->real_escape_string($column);
    $sql = "SELECT 1 FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$table' AND COLUMN_NAME = '$column' LIMIT 1";
    $res = $conn->query($sql);
    return $res && $res->num_rows > 0;
}

$hasApproved = columnExists($conn, 'reviews', 'approved');
$hasUsers    = tableExists($conn, 'users');
$hasUserName = $hasUsers && columnExists($conn, 'users', 'name');

/* 1) POST: إضافة تقييم */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (empty($_SESSION['loggedIn']) || empty($_SESSION['user']['id'])) {
        respond(['success' => false, 'message' => 'Please log in to leave a review.'], 401);
    }

    $userId  = (int)$_SESSION['user']['id'];
    $rating  = isset($_POST['rating']) ? (int)$_POST['rating'] : 5;
    $comment = trim($_POST['comment'] ?? '');

    if ($rating < 1 || $rating > 5) $rating = 5;
    if ($comment === '') {
        respond(['success' => false, 'message' => 'Comment cannot be empty.'], 422);
    }

    if ($hasApproved) {
        $stmt = $conn->prepare("
            INSERT INTO reviews (user_id, rating, comment, created_at, approved)
            VALUES (?, ?, ?, NOW(), 0)
        ");
    } else {
        // إذا ما عندك عمود approved لسه
        $stmt = $conn->prepare("
            INSERT INTO reviews (user_id, rating, comment, created_at)
            VALUES (?, ?, ?, NOW())
        ");
    }

    if (!$stmt) {
        respond(['success' => false, 'message' => 'Prepare failed', 'details' => $conn->error], 500);
    }

    $stmt->bind_param("iis", $userId, $rating, $comment);

    if ($stmt->execute()) {
        // نفس رسالتك المطلوبة (الفرونت يسوي alert)
        respond(['success' => true, 'message' => 'Submitted for approval']);
    } else {
        respond(['success' => false, 'message' => 'Insert failed', 'details' => $stmt->error], 500);
    }
}

/* 2) GET: جلب التقييمات */
$whereApproved = $hasApproved ? "WHERE reviews.approved = 1" : ""; // إذا ما فيه approved، يعرض الكل
if ($hasUsers && $hasUserName) {
    $sql = "
        SELECT users.name AS userName, reviews.rating, reviews.comment, reviews.created_at
        FROM reviews
        LEFT JOIN users ON reviews.user_id = users.id
        $whereApproved
        ORDER BY reviews.created_at DESC
    ";
} else {
    // إذا جدول users أو عمود name غير موجود
    $sql = "
        SELECT 'Customer' AS userName, reviews.rating, reviews.comment, reviews.created_at
        FROM reviews
        $whereApproved
        ORDER BY reviews.created_at DESC
    ";
}

$result = $conn->query($sql);
if (!$result) {
    respond(['success' => false, 'message' => 'Query failed', 'details' => $conn->error], 500);
}

$reviews = [];
while ($row = $result->fetch_assoc()) {
    $reviews[] = $row;
}

respond(['success' => true, 'reviews' => $reviews]);
