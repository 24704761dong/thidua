<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('POST, OPTIONS');
zalo_handle_options();

$payload = zalo_authenticate_request();
$student_id = $payload['student_id'];
$data = json_decode(file_get_contents('php://input'), true);

$push_token = $data['expo_push_token'] ?? '';

if (empty($push_token)) {
    echo json_encode(['success' => false, 'message' => 'Thiếu push token.']);
    exit();
}

try {
    $db = get_db_connection();
    
    // Cập nhật token cho học sinh
    $stmt = $db->prepare("UPDATE ho_so_hoc_sinh SET expo_push_token = ? WHERE id = ?");
    $stmt->execute([$push_token, $student_id]);
    
    echo json_encode(['success' => true, 'message' => 'Đã lưu push token.']);
} catch (Exception $e) {
    http_response_code(500);
    zalo_api_error('Lỗi hệ thống khi lưu push token.', 500, $e);
}
