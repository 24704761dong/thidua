<?php
// File: src/controllers/api_exam_manage_students.php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../lib/exam_permissions.php';
require_once __DIR__ . '/../lib/exam_subjects.php';

// Bảo mật
if (!isset($_SESSION['user_id']) || !can_current_user_manage_exams()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Lỗi xác thực.']);
    exit();
}

require_once __DIR__ . '/../../config/database.php';
$db = get_db_connection();
ensure_exam_subject_registration_schema($db);
$data = json_decode(file_get_contents('php://input'), true);

$action = $data['action'] ?? '';

try {
    $db->beginTransaction();

    switch ($action) {
        case 'add':
            $ky_thi_id = $data['ky_thi_id'] ?? null;
            $add_type = $data['add_type'] ?? null;
            $value = $data['value'] ?? null;

            if (!$ky_thi_id) throw new Exception('Thiếu ID Kỳ thi.');

            // Lấy năm học của kỳ thi
            $stmt_kt = $db->prepare("SELECT nam_hoc_id FROM ky_thi WHERE id = ?");
            $stmt_kt->execute([$ky_thi_id]);
            $exam_nam_hoc_id = $stmt_kt->fetchColumn();
            if (!$exam_nam_hoc_id) {
                $exam_nam_hoc_id = $_SESSION['nam_hoc_id'] ?? 1;
            }

            // Chỉ lấy học sinh đang học tập trong năm học của kỳ thi (bỏ học sinh nghỉ học, đã tốt nghiệp)
            $sql_select_hoc_sinh = "
                SELECT hs.id 
                FROM hoc_sinh hs
                JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id
                WHERE hs.trang_thai_hoc_tap = 'dang_hoc'
                  AND lh.nam_hoc_id = ?
            ";
            $params = [$exam_nam_hoc_id];

            if ($add_type === 'grade') {
                $sql_select_hoc_sinh .= " AND lh.ten_lop LIKE ?";
                $params[] = $value . '%'; // Ví dụ: '10%'
            } elseif ($add_type === 'class') {
                $sql_select_hoc_sinh .= " AND hs.lop_hoc_id = ?";
                $params[] = $value; // ID của lớp
            } elseif ($add_type !== 'all') {
                throw new Exception('Loại thêm không hợp lệ.');
            }

            // MySQL/MariaDB: dùng INSERT IGNORE và BẮT BUỘC phải đặt bí danh cho subquery
            // Chèn ky_thi_id cùng với danh sách id học_sinh phù hợp điều kiện
            $empty_subject_registration = exam_encode_subject_registration([]);
            $sql_insert = "INSERT IGNORE INTO ky_thi_hoc_sinh (ky_thi_id, hoc_sinh_id, dang_ky_mon_thi)
                           SELECT ?, hs.id, ? FROM ($sql_select_hoc_sinh) AS hs";
            
            $stmt = $db->prepare($sql_insert);
            // Hai tham số đầu tiên luôn là ky_thi_id và tổ hợp môn ban đầu
            array_unshift($params, $empty_subject_registration);
            array_unshift($params, $ky_thi_id);
            
            $stmt->execute($params);
            $count = $stmt->rowCount();

            echo json_encode([
                'success' => true, 
                'message' => "Đã thêm thành công {$count} học sinh đang học tập vào kỳ thi."
            ]);
            break;

        case 'remove':
            $kths_id = $data['kths_id'] ?? null; // ID của bảng ky_thi_hoc_sinh
            $ky_thi_id = $data['ky_thi_id'] ?? null;
            if (!$kths_id || !$ky_thi_id) throw new Exception('Thiếu ID học sinh hoặc ID kỳ thi.');
            
            $stmt = $db->prepare("DELETE FROM ky_thi_hoc_sinh WHERE id = ? AND ky_thi_id = ?");
            $stmt->execute([$kths_id, $ky_thi_id]);

            if ($stmt->rowCount() === 0) {
                throw new Exception('Không tìm thấy học sinh trong kỳ thi này.');
            }
            
            echo json_encode(['success' => true, 'message' => 'Đã xóa học sinh khỏi kỳ thi.']);
            break;
            
        case 'remove_all':
            $ky_thi_id = $data['ky_thi_id'] ?? null;
            if (!$ky_thi_id) throw new Exception('Thiếu ID Kỳ thi.');
            
            $stmt = $db->prepare("DELETE FROM ky_thi_hoc_sinh WHERE ky_thi_id = ?");
            $stmt->execute([$ky_thi_id]);
            $count = $stmt->rowCount();
            
            echo json_encode(['success' => true, 'message' => "Đã xóa toàn bộ {$count} học sinh khỏi kỳ thi."]);
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