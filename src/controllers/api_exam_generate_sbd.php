<?php
// File: src/controllers/api_exam_generate_sbd.php
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

$ky_thi_id = $data['ky_thi_id'] ?? null;
$sort_method = $data['sort_method'] ?? 'by_class'; // 'by_class' hoặc 'by_grade_name'

if (!$ky_thi_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Thiếu ID Kỳ thi.']);
    exit();
}

try {
    $db->beginTransaction();
    $messages = [];

    // Lặp qua 3 khối 10, 11, 12
    foreach (['10', '11', '12'] as $khoi) {
        $prefix = '48' . $khoi;
        $stt = 1;

        // Xây dựng câu truy vấn cơ bản
        $sql = "
            SELECT kths.id
            FROM ky_thi_hoc_sinh kths
            JOIN hoc_sinh hs ON kths.hoc_sinh_id = hs.id
            JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id
            WHERE kths.ky_thi_id = ? AND lh.ten_lop LIKE ?
        ";

        // Thêm logic sắp xếp dựa trên yêu cầu
        if ($sort_method === 'by_class') {
            // Sắp xếp theo Lớp (10A1 -> 10A2), sau đó đến Tên
            $sql .= " 
                ORDER BY 
                    CAST(SUBSTR(lh.ten_lop, 1, 2) AS INTEGER), -- Khối
                    SUBSTR(lh.ten_lop, 3, 1), -- Chữ cái (A, B, C)
                    CAST(SUBSTR(lh.ten_lop, 4) AS INTEGER), -- Số (1, 2... 10)
                    hs.ten , 
                    hs.ho_dem 
            ";
        } else {
            // Sắp xếp theo Tên (A-Z) toàn khối
            $sql .= " 
                ORDER BY 
                    hs.ten , 
                    hs.ho_dem , 
                    lh.ten_lop
            ";
        }

        $stmt_select = $db->prepare($sql);
        $stmt_select->execute([$ky_thi_id, $khoi . '%']);
        $students = $stmt_select->fetchAll();
        
        if (empty($students)) {
            $messages[] = "Khối {$khoi}: Không có học sinh.";
            continue;
        }

        // Bắt đầu cập nhật SBD
        $stmt_update = $db->prepare("UPDATE ky_thi_hoc_sinh SET so_bao_danh = ? WHERE id = ?");
        
        foreach ($students as $student) {
            $sbd = $prefix . str_pad($stt, 4, '0', STR_PAD_LEFT); // Ví dụ: 48100001
            $stmt_update->execute([$sbd, $student['id']]);
            $stt++;
        }
        
        $messages[] = "Khối {$khoi}: Đã tạo SBD cho " . ($stt - 1) . " học sinh.";
    }

    $db->commit();
    echo json_encode(['success' => true, 'message' => "Tạo SBD thành công!", 'details' => $messages]);

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
}
?>