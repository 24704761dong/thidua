<?php
// File: src/controllers/api_2fa_login.php (V7)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Nạp các file cần thiết
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../lib/helpers.php'; 
// Chỉ nạp các hàm hỗ trợ từ dang_nhap_xu_ly.php, không thực thi toàn bộ luồng đăng nhập
if (!defined('SKIP_LOGIN_PROCESS')) {
    define('SKIP_LOGIN_PROCESS', true);
}
require_once __DIR__ . '/dang_nhap_xu_ly.php'; 

use PragmaRX\Google2FA\Google2FA;

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $code_to_verify = $data['code'] ?? '';

    $user_id = $_SESSION['2fa_pending_user_id'] ?? null;
    $user_type = $_SESSION['2fa_pending_user_type'] ?? null;

    if (!$user_id || !$user_type) {
        throw new Exception('Phiên xác thực không hợp lệ. Vui lòng đăng nhập lại.');
    }

    $db = get_db_connection();
    $table = ($user_type === 'hoc_sinh') ? 'hoc_sinh' : 'users';

    $stmt_get = $db->prepare("SELECT * FROM {$table} WHERE id = ? AND two_fa_enabled = 1");
    $stmt_get->execute([$user_id]);
    $user_data = $stmt_get->fetch();
    $secret_key = $user_data['two_fa_secret'] ?? null;

    if (!$user_data || empty($secret_key)) {
        throw new Exception('Không tìm thấy thông tin 2FA cho tài khoản này.');
    }

    $google2fa = new Google2FA();
    $is_valid = $google2fa->verifyKey($secret_key, $code_to_verify);

    if ($is_valid) {
        unset($_SESSION['2fa_pending_user_id']);
        unset($_SESSION['2fa_pending_user_type']);
        
        // Gọi hàm (từ file dang_nhap_xu_ly.php V7)
        handleLoginSuccess($db, $user_data, ($user_type === 'hoc_sinh'), true);
        exit(); 

    } else {
        throw new Exception('Mã 6 số không chính xác. Vui lòng thử lại.');
    }

} catch (Exception $e) {
    http_response_code(401); // Lỗi 401 Unauthorized
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit();
}