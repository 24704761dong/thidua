<?php
// File: src/controllers/api_xoa_minh_chung_tuan.php (File mới)

if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập.']);
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);
$tuan_id = $data['tuan_id'] ?? null;

if (!$tuan_id) {
    echo json_encode(['success' => false, 'message' => 'Thiếu ID của tuần học.']);
    exit();
}

try {
    $db = get_db_connection();
    $db->beginTransaction();

    // 1. Lấy danh sách ID của các Sổ nhật kỳ trong tuần
    $stmt_ids = $db->prepare("SELECT id FROM so_nhat_ky_online WHERE tuan_hoc_id = ?");
    $stmt_ids->execute([$tuan_id]);
    $nhat_ky_ids = $stmt_ids->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($nhat_ky_ids)) {
        $placeholders = implode(',', array_fill(0, count($nhat_ky_ids), '?'));

        // 2. Lấy đường dẫn của tất cả các file vật lý cần xóa
        $stmt_files = $db->prepare("SELECT file_path FROM so_nhat_ky_minh_chung WHERE nhat_ky_id IN ($placeholders)");
        $stmt_files->execute($nhat_ky_ids);
        $files_to_delete = $stmt_files->fetchAll(PDO::FETCH_COLUMN);

        // 3. Xóa các bản ghi minh chứng trong CSDL
        $stmt_delete = $db->prepare("DELETE FROM so_nhat_ky_minh_chung WHERE nhat_ky_id IN ($placeholders)");
        $stmt_delete->execute($nhat_ky_ids);
        $deleted_db_records = $stmt_delete->rowCount();

        // 4. Xóa các file vật lý trên server
        $deleted_files_count = 0;
        foreach ($files_to_delete as $file_path) {
            $physical_path = __DIR__ . '/../../' . $file_path;
            if (file_exists($physical_path)) {
                if (unlink($physical_path)) {
                    $deleted_files_count++;
                }
            }
        }
    } else {
        $deleted_db_records = 0;
        $deleted_files_count = 0;
    }
    
    $db->commit();
    echo json_encode(['success' => true, 'message' => "Xóa thành công {$deleted_files_count} file minh chứng."]);

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi Server: ' . $e->getMessage()]);
}