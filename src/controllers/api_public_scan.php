<?php
// File: src/controllers/api_public_scan.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data = json_decode($input, true) ?? $_POST;
$token = $data['token'] ?? '';

if (empty($token)) {
    echo json_encode(['success' => false, 'message' => 'Mã liên kết không hợp lệ']);
    exit;
}

require_once __DIR__ . '/../../config/database.php';
$db = get_db_connection();

$stmt = $db->prepare("SELECT * FROM hoat_dong WHERE scan_token = ?");
$stmt->execute([$token]);
$hoat_dong = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$hoat_dong) {
    echo json_encode(['success' => false, 'message' => 'Liên kết quét mã đã hết hạn hoặc không tồn tại']);
    exit;
}

// Kiểm tra mật khẩu (nếu có)
$auth_key = 'public_scan_' . $token;
if (!empty($hoat_dong['scan_password']) && (!isset($_SESSION[$auth_key]) || $_SESSION[$auth_key] !== true)) {
    echo json_encode(['success' => false, 'message' => 'Bạn chưa xác thực mật khẩu. Vui lòng tải lại trang.']);
    exit;
}

// Truyền tham số hoat_dong_id vào $data để api_hoat_dong_diem_danh.php có thể đọc
if (!isset($data['hoat_dong_id'])) {
    $data['hoat_dong_id'] = $hoat_dong['id'];
}
$_POST['hoat_dong_id'] = $hoat_dong['id']; // fallback

define('IS_PUBLIC_SCAN', true);

// Tái sử dụng toàn bộ logic quét mã của API admin
require_once __DIR__ . '/api_hoat_dong_diem_danh.php';
