<?php
// File: src/controllers/api_get_proof_ids_for_backup.php

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

$ids_selected = $data['ids'] ?? [];
$tuan_id = $data['tuan_id'] ?? null;
$backup_all = $data['backup_all'] ?? false;

try {
    $db = get_db_connection();
    $ids = [];

    // Lấy danh sách ID mà CHƯA lưu trên onedrive
    if (!empty($ids_selected) && is_array($ids_selected)) {
        // Lọc lại các ID đã gửi lên
        $placeholders = implode(',', array_fill(0, count($ids_selected), '?'));
        $stmt = $db->prepare("SELECT id FROM so_nhat_ky_minh_chung WHERE id IN ($placeholders) AND storage_driver != 'onedrive'");
        $stmt->execute($ids_selected);
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    } elseif (!empty($tuan_id)) {
        // Lấy theo tuần
        $stmt = $db->prepare("
            SELECT sm.id 
            FROM so_nhat_ky_minh_chung sm
            JOIN so_nhat_ky_online snk ON sm.nhat_ky_id = snk.id
            WHERE snk.tuan_hoc_id = ? AND sm.storage_driver != 'onedrive'
        ");
        $stmt->execute([$tuan_id]);
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    } elseif ($backup_all) {
        // Lấy tất cả
        $stmt = $db->query("SELECT id FROM so_nhat_ky_minh_chung WHERE storage_driver != 'onedrive'");
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
