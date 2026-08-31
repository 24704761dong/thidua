<?php
// File: src/controllers/api_get_violation_proof_ids_for_backup.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ob_start();

// Bảo mật: Chỉ admin/user mới có quyền
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    http_response_code(403);
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập.']);
    exit();
}

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);
$backup_all = $data['backup_all'] ?? false;

try {
    $db = get_db_connection();
    $ids = [];

    // Lấy danh sách ID minh chứng vi phạm đang lưu ở R2 (synced) và CHƯA lưu trên onedrive
    if ($backup_all) {
        $stmt = $db->query("SELECT id FROM minh_chung_vi_pham WHERE trang_thai = 'synced' AND cloud_id IS NOT NULL AND trang_thai != 'onedrive'");
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } else {
        http_response_code(400);
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Không có tham số hợp lệ.']);
        exit();
    }

    ob_clean();
    echo json_encode(['success' => true, 'ids' => $ids]);

} catch (Exception $e) {
    http_response_code(500);
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
}
?>
