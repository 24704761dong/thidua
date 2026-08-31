<?php
// File: src/controllers/api_exam_crud.php (ĐÃ NÂNG CẤP)
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../lib/exam_permissions.php';

// Bảo mật
if (!isset($_SESSION['user_id']) || !can_current_user_manage_exams()) {
    http_response_code(403); // Forbidden
    echo json_encode(['success' => false, 'message' => 'Lỗi xác thực.']);
    exit();
}

require_once __DIR__ . '/../../config/database.php';
$db = get_db_connection();
$data = json_decode(file_get_contents('php://input'), true);

$action = $data['action'] ?? '';

try {
    $db->beginTransaction(); // Bắt đầu transaction

    switch ($action) {
        case 'create':
            $ten_ky_thi = trim($data['ten_ky_thi'] ?? '');
            $ngay_bat_dau = $data['ngay_bat_dau'] ? $data['ngay_bat_dau'] : null;
            $ngay_ket_thuc = $data['ngay_ket_thuc'] ? $data['ngay_ket_thuc'] : null;
            
            $nam_hoc_id = $_SESSION['nam_hoc_id'] ?? $_SESSION['current_nam_hoc_id'] ?? null;
            if (!$nam_hoc_id) {
                $stmt_nh = $db->query("SELECT id FROM nam_hoc WHERE is_mac_dinh = 1 LIMIT 1");
                $nh = $stmt_nh->fetch();
                $nam_hoc_id = $nh['id'] ?? 1;
            }

            if (empty($ten_ky_thi)) {
                throw new Exception('Tên kỳ thi là bắt buộc.');
            }

            $stmt = $db->prepare("INSERT INTO ky_thi (ten_ky_thi, ngay_bat_dau, ngay_ket_thuc, nam_hoc_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$ten_ky_thi, $ngay_bat_dau, $ngay_ket_thuc, $nam_hoc_id]);
            
            $new_id = $db->lastInsertId();
            $stmt_get = $db->prepare("SELECT * FROM ky_thi WHERE id = ?");
            $stmt_get->execute([$new_id]);
            $new_exam = $stmt_get->fetch();
            
            echo json_encode(['success' => true, 'message' => 'Tạo kỳ thi thành công!', 'data' => $new_exam]);
            break;
        
        case 'update':
            $id = $data['id'] ?? null;
            $ten_ky_thi = trim($data['ten_ky_thi'] ?? '');
            $ngay_bat_dau = $data['ngay_bat_dau'] ? $data['ngay_bat_dau'] : null;
            $ngay_ket_thuc = $data['ngay_ket_thuc'] ? $data['ngay_ket_thuc'] : null;

            if (empty($id) || empty($ten_ky_thi)) {
                throw new Exception('ID hoặc Tên kỳ thi không hợp lệ.');
            }

            $stmt = $db->prepare("UPDATE ky_thi SET ten_ky_thi = ?, ngay_bat_dau = ?, ngay_ket_thuc = ? WHERE id = ?");
            $stmt->execute([$ten_ky_thi, $ngay_bat_dau, $ngay_ket_thuc, $id]);

            $stmt_get = $db->prepare("SELECT * FROM ky_thi WHERE id = ?");
            $stmt_get->execute([$id]);
            $updated_exam = $stmt_get->fetch();

            echo json_encode(['success' => true, 'message' => 'Cập nhật kỳ thi thành công!', 'data' => $updated_exam]);
            break;

        case 'delete':
            $id = $data['id'] ?? null;
            if (empty($id)) {
                throw new Exception('ID kỳ thi không hợp lệ.');
            }

            // Do đã cài đặt 'ON DELETE CASCADE' ở CSDL (Bước 1),
            // nên khi xóa kỳ thi, tất cả học sinh và phòng thi liên quan cũng sẽ tự động bị xóa.
            $stmt = $db->prepare("DELETE FROM ky_thi WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'Xóa kỳ thi thành công!']);
            break;

        default:
            throw new Exception('Hành động không hợp lệ.');
    }
    
    $db->commit(); // Hoàn tất transaction

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack(); // Hoàn tác nếu có lỗi
    }
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>