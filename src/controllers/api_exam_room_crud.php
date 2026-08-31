<?php
// File: src/controllers/api_exam_room_crud.php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../lib/exam_permissions.php';

// Bảo mật
if (!isset($_SESSION['user_id']) || !can_current_user_manage_exams()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Lỗi xác thực.']);
    exit();
}

require_once __DIR__ . '/../../config/database.php';
$db = get_db_connection();
$data = json_decode(file_get_contents('php://input'), true);

$action = $data['action'] ?? '';

try {
    $db->beginTransaction();

    switch ($action) {
        case 'create_or_update':
            $id = $data['id'] ?? null;
            $ky_thi_id = $data['ky_thi_id'] ?? null;
            $ten_phong = trim($data['ten_phong'] ?? '');
            $si_so_toi_da = filter_var($data['si_so_toi_da'] ?? 40, FILTER_VALIDATE_INT);

            if (empty($ten_phong) || !$ky_thi_id || $si_so_toi_da <= 0) {
                throw new Exception('Tên phòng, ID kỳ thi, hoặc sĩ số không hợp lệ.');
            }
            
            if (empty($id)) {
                // Tạo mới
                $stmt = $db->prepare("INSERT INTO ky_thi_phong_thi (ky_thi_id, ten_phong, si_so_toi_da) VALUES (?, ?, ?)");
                $stmt->execute([$ky_thi_id, $ten_phong, $si_so_toi_da]);
                $id = $db->lastInsertId();
            } else {
                // Cập nhật
                $stmt = $db->prepare("UPDATE ky_thi_phong_thi SET ten_phong = ?, si_so_toi_da = ? WHERE id = ? AND ky_thi_id = ?");
                $stmt->execute([$ten_phong, $si_so_toi_da, $id, $ky_thi_id]);
            }
            
            // Lấy lại dữ liệu phòng vừa cập nhật/thêm mới (bao gồm cả si_so_hien_tai)
            $stmt_get = $db->prepare("
                SELECT pt.*, COUNT(kths.id) as si_so_hien_tai
                FROM ky_thi_phong_thi pt
                LEFT JOIN ky_thi_hoc_sinh kths ON pt.id = kths.phong_thi_id
                WHERE pt.id = ?
                GROUP BY pt.id
            ");
            $stmt_get->execute([$id]);
            $room = $stmt_get->fetch();

            echo json_encode(['success' => true, 'message' => 'Đã lưu phòng thi!', 'data' => $room]);
            break;

        case 'delete':
            $id = $data['id'] ?? null;
            $ky_thi_id = $data['ky_thi_id'] ?? null;
            if (!$id || !$ky_thi_id) throw new Exception('Thiếu ID phòng hoặc ID kỳ thi.');
            
            // Cập nhật lại các học sinh đang ở phòng này về NULL
            $stmt_update = $db->prepare("UPDATE ky_thi_hoc_sinh SET phong_thi_id = NULL WHERE phong_thi_id = ? AND ky_thi_id = ?");
            $stmt_update->execute([$id, $ky_thi_id]);
            
            // Xóa phòng
            $stmt_delete = $db->prepare("DELETE FROM ky_thi_phong_thi WHERE id = ? AND ky_thi_id = ?");
            $stmt_delete->execute([$id, $ky_thi_id]);

            if ($stmt_delete->rowCount() === 0) {
                throw new Exception('Không tìm thấy phòng thi trong kỳ thi này.');
            }
            
            echo json_encode(['success' => true, 'message' => 'Đã xóa phòng thi.']);
            break;

        default:
            throw new Exception('Hành động không hợp lệ.');
    }

    $db->commit();
} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>