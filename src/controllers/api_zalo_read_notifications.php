<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('POST, OPTIONS');
zalo_handle_options();

// File: src/controllers/api_zalo_read_notifications.php
$payload = zalo_authenticate_request();
$student_id = $payload['student_id'];
$data = json_decode(file_get_contents('php://input'), true);

try {
    $db = get_db_connection();
    
    if (isset($data['id'])) {
        $stmt = $db->prepare("UPDATE thong_bao_hoc_sinh SET da_xem = 1 WHERE id = ? AND hoc_sinh_id = ?");
        $stmt->execute([$data['id'], $student_id]);
    } else {
        $stmt = $db->prepare("UPDATE thong_bao_hoc_sinh SET da_xem = 1 WHERE hoc_sinh_id = ? AND da_xem = 0");
        $stmt->execute([$student_id]);
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    zalo_api_error('Lỗi hệ thống, vui lòng thử lại sau.', 500, $e);
}
