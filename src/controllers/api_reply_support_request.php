<?php
// File: src/controllers/api_reply_support_request.php (Đã nâng cấp để sử dụng hàng đợi)
require_once __DIR__ . '/../../config/database.php';

require_once __DIR__ . '/../lib/helpers.php'; // Nạp file helper để dùng queue_email() và mẫu email
require_once __DIR__ . '/../../vendor/autoload.php';

header('Content-Type: application/json');
session_start();

// Bảo mật: Chỉ Admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập.']);
    exit;
}

$request_id = $_POST['request_id'] ?? null;
$recipient_email = $_POST['recipient_email'] ?? null;
$recipient_name = $_POST['recipient_name'] ?? ''; 
$subject = $_POST['subject'] ?? 'Phản hồi Yêu cầu Hỗ trợ';
$body_content = $_POST['body'] ?? '';

if (!$request_id || !$recipient_email || empty($body_content)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin cần thiết.']);
    exit;
}

try {
    // Bọc nội dung admin soạn thảo vào khung email chung
    $final_body = generate_beautiful_admin_reply_wrapper($body_content, $recipient_name);

    // Đưa email vào hàng đợi
    $is_queued = queue_email($recipient_email, $recipient_name, $subject, $final_body, strip_tags($body_content), 10, [
        'type' => 'support_request_reply',
        'metadata' => [
            'request_id' => $request_id,
        ],
    ]);

    if (!$is_queued) {
        throw new Exception("Không thể đưa email vào hàng đợi.");
    }

    // Cập nhật trạng thái trong CSDL
    update_support_request_status($request_id, 'replied');

    echo json_encode(['success' => true, 'message' => 'Email trả lời đã được đưa vào hàng đợi để gửi đi.']);

} catch (Exception $e) {
    http_response_code(500);
    error_log("Gửi mail trả lời thất bại: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Gửi email thất bại: ' . $e->getMessage()]);
}