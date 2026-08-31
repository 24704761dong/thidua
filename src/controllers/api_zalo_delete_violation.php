<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('POST, OPTIONS');
zalo_handle_options();

try {
    $db = get_db_connection();
    
    $payload = zalo_authenticate_request();
    $student_id = $payload['student_id'];
    $raw_input = file_get_contents('php://input');
    $data = json_decode($raw_input, true);
    
    $vi_pham_id = $data['id'] ?? null;
    
    if (empty($vi_pham_id)) {
        throw new Exception("Thiếu ID vi phạm. Data: " . $raw_input);
    }
    
    // Chỉ cho phép xóa nếu là của mình nhập VÀ trạng thái đang là 'nhap'
    $stmt = $db->prepare("DELETE FROM vi_pham_tam_thoi WHERE id = ? AND nguoi_nhap_id = ? AND trang_thai_gui = 'nhap'");
    $stmt->execute([$vi_pham_id, $student_id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Đã xóa vi phạm nháp.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy vi phạm hoặc không có quyền xóa.']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
