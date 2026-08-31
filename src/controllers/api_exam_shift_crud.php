<?php
// File: src/controllers/api_exam_shift_crud.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');
require_once __DIR__ . '/../lib/exam_permissions.php';
require_once __DIR__ . '/../lib/exam_subjects.php';
require_once __DIR__ . '/../lib/exam_shift_manager.php';
require_once __DIR__ . '/../lib/exam_room_assignment.php';

// Bảo mật
if (!isset($_SESSION['user_id']) || !can_current_user_manage_exams()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Lỗi xác thực.']);
    exit();
}

require_once __DIR__ . '/../../config/database.php';
$db = get_db_connection();
ensure_exam_shift_schema($db);
ensure_exam_room_assignment_schema($db);

$data = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $data['action'] ?? ($_GET['action'] ?? '');
$ky_thi_id = (int)($data['ky_thi_id'] ?? ($_GET['ky_thi_id'] ?? 0));

if (!$ky_thi_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Thiếu ID Kỳ thi.']);
    exit();
}

try {
    switch ($action) {
        case 'get_shifts':
            $shifts = get_exam_shifts($db, $ky_thi_id);
            if (empty($shifts)) {
                create_default_exam_shifts($db, $ky_thi_id);
                $shifts = get_exam_shifts($db, $ky_thi_id);
            }

            // Đếm số học sinh và số phòng thi đã xếp trong từng ca
            foreach ($shifts as &$s) {
                $stmt_stat = $db->prepare("
                    SELECT 
                        COUNT(DISTINCT ky_thi_hoc_sinh_id) as total_students,
                        COUNT(DISTINCT phong_thi_id) as total_rooms
                    FROM ky_thi_xep_phong
                    WHERE ky_thi_id = ? AND ca_thi_id = ?
                ");
                $stmt_stat->execute([$ky_thi_id, $s['id']]);
                $stat = $stmt_stat->fetch(PDO::FETCH_ASSOC);
                $s['assigned_students'] = (int)($stat['total_students'] ?? 0);
                $s['assigned_rooms'] = (int)($stat['total_rooms'] ?? 0);
            }
            unset($s);

            echo json_encode(['success' => true, 'shifts' => $shifts]);
            break;

        case 'save_shift':
            $id = !empty($data['id']) ? (int)$data['id'] : null;
            $ten_ca = trim($data['ten_ca'] ?? '');
            $ngay_thi = !empty($data['ngay_thi']) ? $data['ngay_thi'] : null;
            $gio_thi = !empty($data['gio_thi']) ? $data['gio_thi'] : null;
            $so_luot_thi = max(1, min(4, (int)($data['so_luot_thi'] ?? 1)));
            $thu_tu = (int)($data['thu_tu'] ?? 1);
            $mon_hoc_list = is_array($data['mon_hoc_list'] ?? null) ? $data['mon_hoc_list'] : [];

            if (empty($ten_ca)) {
                throw new Exception('Vui lòng nhập tên ca thi.');
            }
            if (empty($mon_hoc_list)) {
                throw new Exception('Vui lòng chọn ít nhất 1 môn thi cho ca này.');
            }

            $danh_sach_mon_json = json_encode(array_values(array_unique($mon_hoc_list)), JSON_UNESCAPED_UNICODE);

            if ($id) {
                $stmt = $db->prepare("
                    UPDATE ky_thi_ca_thi
                    SET ten_ca = ?, ngay_thi = ?, gio_thi = ?, so_luot_thi = ?, danh_sach_mon = ?, thu_tu = ?
                    WHERE id = ? AND ky_thi_id = ?
                ");
                $stmt->execute([$ten_ca, $ngay_thi, $gio_thi, $so_luot_thi, $danh_sach_mon_json, $thu_tu, $id, $ky_thi_id]);
                $message = 'Cập nhật thông tin ca thi thành công!';
            } else {
                $stmt = $db->prepare("
                    INSERT INTO ky_thi_ca_thi (ky_thi_id, ten_ca, ngay_thi, gio_thi, so_luot_thi, danh_sach_mon, thu_tu)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$ky_thi_id, $ten_ca, $ngay_thi, $gio_thi, $so_luot_thi, $danh_sach_mon_json, $thu_tu]);
                $message = 'Thêm mới ca thi thành công!';
            }

            echo json_encode(['success' => true, 'message' => $message]);
            break;

        case 'delete_shift':
            $id = (int)($data['id'] ?? 0);
            if (!$id) throw new Exception('Thiếu ID Ca thi.');

            $db->beginTransaction();
            // Xóa kết quả phân phòng của ca này
            $stmt_del_assign = $db->prepare("DELETE FROM ky_thi_xep_phong WHERE ky_thi_id = ? AND ca_thi_id = ?");
            $stmt_del_assign->execute([$ky_thi_id, $id]);

            // Xóa ca thi
            $stmt_del_shift = $db->prepare("DELETE FROM ky_thi_ca_thi WHERE id = ? AND ky_thi_id = ?");
            $stmt_del_shift->execute([$id, $ky_thi_id]);

            $db->commit();
            echo json_encode(['success' => true, 'message' => 'Đã xóa ca thi thành công!']);
            break;

        case 'reset_default_shifts':
            $db->beginTransaction();
            $db->prepare("DELETE FROM ky_thi_xep_phong WHERE ky_thi_id = ?")->execute([$ky_thi_id]);
            $db->prepare("DELETE FROM ky_thi_ca_thi WHERE ky_thi_id = ?")->execute([$ky_thi_id]);
            create_default_exam_shifts($db, $ky_thi_id);
            $db->commit();

            echo json_encode(['success' => true, 'message' => 'Đã khôi phục danh sách ca thi mặc định!']);
            break;

        default:
            throw new Exception('Hành động không hợp lệ.');
    }
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
