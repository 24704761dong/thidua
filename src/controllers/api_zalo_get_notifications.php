<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('GET, OPTIONS');
zalo_handle_options();

// File: src/controllers/api_zalo_get_notifications.php
$payload = zalo_authenticate_request();
$student_id = $payload['student_id'];
try {
    $db = get_db_connection();
    
    // Đếm số thông báo chưa đọc
    $stmt_count = $db->prepare("SELECT COUNT(*) FROM thong_bao_hoc_sinh WHERE hoc_sinh_id = ? AND da_xem = 0");
    $stmt_count->execute([$student_id]);
    $unread_count = (int)$stmt_count->fetchColumn();

    $stmt = $db->prepare("SELECT id, tieu_de, noi_dung, loai_thong_bao, thoi_gian, da_xem 
                          FROM thong_bao_hoc_sinh 
                          WHERE hoc_sinh_id = ? 
                          ORDER BY thoi_gian DESC");
    $stmt->execute([$student_id]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($notifications as &$notif) {
        $notif['da_xem'] = (int)$notif['da_xem'];
    }

    echo json_encode([
        'success' => true, 
        'data' => $notifications,
        'unread_count' => $unread_count
    ]);
} catch (Exception $e) {
    http_response_code(500);
    zalo_api_error('Lỗi hệ thống, vui lòng thử lại sau.', 500, $e);
}
