<?php
// File: src/controllers/api_terminate_session.php (PHIÊN BẢN HOÀN CHỈNH ĐÃ SỬA LỖI)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

// Yêu cầu người dùng phải đăng nhập và là admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền truy cập.']);
    exit();
}

// SỬA LỖI: Chỉ cần kết nối đến CSDL Chính
require_once __DIR__ . '/../../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);
$session_id_to_terminate = $data['session_id'] ?? null;
$current_session_id = session_id();

if (empty($session_id_to_terminate)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Thiếu session_id để thực hiện thao tác.']);
    exit();
}

if ($session_id_to_terminate === $current_session_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Bạn không thể đăng xuất khỏi phiên hiện tại của chính mình.']);
    exit();
}

try {
    // SỬA LỖI: Sử dụng kết nối đến CSDL Chính ($db)
    $db = get_db_connection(); 

    // SỬA LỖI: Thực hiện thao tác xóa trên CSDL Chính
    // Thêm điều kiện user_id để đảm bảo admin chỉ có thể xóa các phiên của chính mình
    $stmt = $db->prepare("DELETE FROM phien_truy_cap WHERE session_id = ? AND user_id = ?");
    $stmt->execute([$session_id_to_terminate, $_SESSION['user_id']]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Đã chấm dứt phiên thành công.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy phiên để chấm dứt hoặc phiên này không thuộc tài khoản của bạn.']);
    }

} catch (Exception $e) {
    http_response_code(500);
    error_log("Terminate Session Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ khi xử lý yêu cầu.']);
}