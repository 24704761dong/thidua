<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit();
}

require_once __DIR__ . '/../../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);

try {
    $db = get_db_connection();
    $stmt = $db->prepare("INSERT INTO he_thong_cai_dat (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $stmt->execute(['trehoc_loi_vi_pham', $input['trehoc_loi_vi_pham']]);
    echo json_encode(['success' => true, 'message' => 'Đã lưu cài đặt thành công!']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}