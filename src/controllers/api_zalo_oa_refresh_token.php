<?php
// File: src/controllers/api_zalo_oa_refresh_token.php
// API: Thử refresh Zalo OA Access Token bằng Refresh Token hiện có
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

// Chỉ admin mới được phép
if (!isset($_SESSION['user_id']) || $_SESSION['user_vai_tro'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập.']);
    exit;
}

require_once __DIR__ . '/../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->safeLoad();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/zalo_helpers.php';

$new_token = refresh_zalo_oa_token();

if ($new_token) {
    // Đồng bộ vào .env
    try {
        $env_path = __DIR__ . '/../../.env';
        $env_content = file_get_contents($env_path);
        if ($env_content !== false) {
            $env_content = preg_replace(
                '/^ZALO_OA_ACCESS_TOKEN=.*/m',
                'ZALO_OA_ACCESS_TOKEN="' . $new_token . '"',
                $env_content
            );
            file_put_contents($env_path, $env_content);
        }
    } catch (\Throwable $e) {}

    echo json_encode([
        'success' => true,
        'message' => 'Refresh Token thành công! Token mới đã được lưu.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Refresh Token thất bại. Cả Access Token và Refresh Token đều đã hết hạn. Vui lòng kết nối lại qua OAuth.'
    ]);
}
exit;
