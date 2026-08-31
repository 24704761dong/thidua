<?php
// File: src/controllers/api_ctv_xoa_nhieu_vi_pham.php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['student_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập.']);
    exit();
}

require_once __DIR__ . '/../../config/database.php';
$data = json_decode(file_get_contents('php://input'), true);
$ids_to_delete = $data['ids'] ?? [];
$response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ.'];
$ctv_id = $_SESSION['student_id'];

if (!empty($ids_to_delete) && is_array($ids_to_delete)) {
    try {
        $db = get_db_connection();
        $db->beginTransaction();
        $placeholders = implode(',', array_fill(0, count($ids_to_delete), '?'));
        
        // Chỉ xóa được khi người nhập là chính CTV này và trạng thái là 'nhap'
        $sql = "DELETE FROM vi_pham_tam_thoi WHERE id IN ($placeholders) AND nguoi_nhap_id = ? AND trang_thai_gui = 'nhap'";
        $stmt = $db->prepare($sql);
        
        $params = $ids_to_delete;
        $params[] = $ctv_id;
        
        $stmt->execute($params);
        $deleted_count = $stmt->rowCount();
        $db->commit();
        $response = ['success' => true, 'message' => "Đã xóa thành công {$deleted_count} mục."];
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        $response['message'] = 'Lỗi CSDL: ' . $e->getMessage();
        http_response_code(500);
    }
} else {
    $response['message'] = 'Không có ID nào được chọn để xóa.';
}
echo json_encode($response);
