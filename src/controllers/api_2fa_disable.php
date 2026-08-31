<?php
// File: src/controllers/api_2fa_disable.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../lib/helpers.php';

use PragmaRX\Google2FA\Google2FA;

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Lỗi không xác định.'];

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $code_to_verify = $data['code'] ?? '';

    if (empty($code_to_verify)) {
        throw new Exception('Vui lòng nhập mã 6 số để xác nhận tắt 2FA.');
    }

    // 1. Xác định người dùng và bảng
    $db = get_db_connection();
    $user_id = null;
    $table = null;

    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $table = 'users';
    } elseif (isset($_SESSION['student_id'])) {
        $user_id = $_SESSION['student_id'];
        $table = 'hoc_sinh';
    } else {
        http_response_code(403);
        throw new Exception('Phiên đăng nhập không hợp lệ.');
    }

    // 2. Lấy secret key đã lưu
    $stmt_get = $db->prepare("SELECT two_fa_secret FROM {$table} WHERE id = ?");
    $stmt_get->execute([$user_id]);
    $secret_key = $stmt_get->fetchColumn();

    if (empty($secret_key)) {
        // Điều này không nên xảy ra nếu 2FA đang bật, nhưng là một bước kiểm tra an toàn
        throw new Exception('Không tìm thấy mã bí mật đã lưu.');
    }

    // 3. Xác thực mã 6 số
    $google2fa = new Google2FA();
    $is_valid = $google2fa->verifyKey($secret_key, $code_to_verify);

    if ($is_valid) {
        // 4. TẮT 2FA: Xóa secret key và đặt cờ enabled = 0
        $stmt_disable = $db->prepare("UPDATE {$table} SET two_fa_enabled = 0, two_fa_secret = NULL WHERE id = ?");
        $stmt_disable->execute([$user_id]);
        
        $response = ['success' => true, 'message' => 'Xác thực 2 yếu tố đã được TẮT thành công!'];
    } else {
        // 5. Báo lỗi
        http_response_code(400); // Bad Request
        $response = ['success' => false, 'message' => 'Mã 6 số không chính xác. Tắt 2FA thất bại.'];
    }

} catch (Throwable $t) {
    if (function_exists('log_to_file')) {
        log_to_file("LỖI API 2FA Disable: " . $t->getMessage() . "\n" . $t->getTraceAsString());
    }
    http_response_code(500);
    $response['message'] = $t->getMessage();
}

echo json_encode($response);
exit();