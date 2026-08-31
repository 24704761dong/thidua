<?php
// File: src/controllers/api_get_all_cloud_proof_ids.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập.']);
    exit();
}

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/database.php';

try {
    $db = get_db_connection();
    $stmt = $db->query("SELECT id FROM so_nhat_ky_minh_chung WHERE storage_driver = 'cloud'");
    $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode(['success' => true, 'ids' => $ids]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu: ' . $e->getMessage()]);
}
