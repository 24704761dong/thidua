<?php
// File: src/controllers/api_lookup_student.php (Đã nâng cấp logic "Nghỉ Học")

require_once __DIR__ . '/../../config/database.php';

// --- Thiết lập môi trường ---
header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Lỗi không xác định.'];

// --- Lấy dữ liệu đầu vào ---
$ho_ten_raw = trim($_GET['ho_ten'] ?? '');
$ten_lop_raw = trim($_GET['ten_lop'] ?? '');

// --- Xác thực đầu vào cơ bản ---
if (empty($ho_ten_raw) || empty($ten_lop_raw)) {
    $response['message'] = 'Thiếu thông tin họ tên hoặc lớp.';
    echo json_encode($response);
    exit();
}

try {
    $db = get_db_connection();
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
    $current_nam_hoc = $_SESSION['current_nam_hoc_id'] ?? 1;

    // --- Logic 1: Chuẩn hóa và tìm kiếm Lớp học ---
    $ten_lop_final = $ten_lop_raw;
    // Tự động chuẩn hóa các định dạng viết tắt (ví dụ: A1 -> 10A1, B2 -> 11A2, C10 -> 12A10)
    if (preg_match('/^([a-cA-C])(\d+)$/', $ten_lop_raw, $matches)) {
        $prefix = strtoupper($matches[1]);
        $number = $matches[2];
        $khoi_map = ['A' => '10', 'B' => '11', 'C' => '12'];
        $ten_lop_final = $khoi_map[$prefix] . 'A' . $number;
    }

    // Tìm lớp trong CSDL theo đúng năm học
    $stmt_lop = $db->prepare("SELECT id, ten_lop FROM raw_lop_hoc WHERE ten_lop = ? AND nam_hoc_id = ?");
    $stmt_lop->execute([$ten_lop_final, $current_nam_hoc]);
    $lop = $stmt_lop->fetch();

    if (!$lop) {
        $response['message'] = "Lớp '" . htmlspecialchars($ten_lop_final) . "' không tồn tại.";
        $response['validated_class'] = null;
        echo json_encode($response);
        exit();
    }
    
    // --- Logic 2: Tìm kiếm Học sinh trong lớp đã tìm thấy ---
    $ho_ten_search = trim($ho_ten_raw);
    $ho_ten_exact = preg_replace('/\s+/', ' ', $ho_ten_search);

    // SỬA 1: Lấy thêm cột `trang_thai_hoc_tap` VÀ xóa bộ lọc `trang_thai_hoc_tap`
    // Uu tien khop chinh xac ho ten (tranh trung khi ten la prefix)
    $sql_exact = "
    SELECT qt.id, ho_so.ma_hoc_sinh, ho_so.ho_dem, ho_so.ten, ho_so.trang_thai_hoc_tap 
    FROM quatrinh_hoc_tap qt
    JOIN ho_so_hoc_sinh ho_so ON qt.ma_hoc_sinh = ho_so.ma_hoc_sinh
    WHERE qt.lop_hoc_id = ? AND qt.nam_hoc_id = ? AND TRIM(CONCAT(ho_so.ho_dem, ' ', ho_so.ten)) = ?
";
    $stmt_exact = $db->prepare($sql_exact);
    $stmt_exact->execute([$lop['id'], $current_nam_hoc, $ho_ten_exact]);
    $students = $stmt_exact->fetchAll();

    if (count($students) === 0) {
        $sql_search_hs = "
        SELECT qt.id, ho_so.ma_hoc_sinh, ho_so.ho_dem, ho_so.ten, ho_so.trang_thai_hoc_tap 
        FROM quatrinh_hoc_tap qt
        JOIN ho_so_hoc_sinh ho_so ON qt.ma_hoc_sinh = ho_so.ma_hoc_sinh
        WHERE qt.lop_hoc_id = ? AND qt.nam_hoc_id = ? AND CONCAT(ho_so.ho_dem, ' ', ho_so.ten) LIKE ?
";
        $stmt_hs = $db->prepare($sql_search_hs);
        $stmt_hs->execute([$lop['id'], $current_nam_hoc, '%' . $ho_ten_search . '%']);
        $students = $stmt_hs->fetchAll();
    }
    
    // --- Logic 3: Xử lý kết quả và trả về phản hồi (ĐÃ NÂNG CẤP) ---
    if (count($students) === 1) {
        $student = $students[0];

        // === BẮT ĐẦU NÂNG CẤP: KIỂM TRA TRẠNG THÁI ===
        if ($student['trang_thai_hoc_tap'] === 'nghi_hoc') {
            // Vẫn tìm thấy, nhưng báo lỗi "Đã nghỉ học"
            $response['message'] = 'Học sinh này đã nghỉ học.';
            $response['success'] = false; // Vẫn là false
        } else {
            // Trường hợp thành công: Tìm thấy và đang học
            $response = [
                'success' => true,
                'student' => [
                    'id' => $student['id'],
                    'ma_hoc_sinh' => $student['ma_hoc_sinh'],
                    'ho_ten' => trim($student['ho_dem'] . ' ' . $student['ten']),
                    'lop_hoc_id' => $lop['id'],
                    'ten_lop' => $lop['ten_lop']
                ]
            ];
        }
        // === KẾT THÚC NÂNG CẤP ===

    } elseif (count($students) > 1) {
        // Trường hợp lỗi: Tìm thấy nhiều học sinh trùng tên
        $student_names = array_map(function($s) { 
            $ten = trim($s['ho_dem'].' '.$s['ten']);
            $ma = $s['ma_hoc_sinh'] ? ' (' . $s['ma_hoc_sinh'] . ')' : '';
            return $ten . $ma;
        }, $students);
        $response['message'] = 'Tìm thấy nhiều HS: ' . implode(', ', $student_names);
        $response['multiple'] = true;
    } else {
        // Trường hợp lỗi: Không tìm thấy học sinh nào
        $response['message'] = "KXD";
    }

} catch (PDOException $e) {
    // Xử lý các lỗi liên quan đến CSDL
    $response['message'] = 'Lỗi CSDL: ' . $e->getMessage();
    http_response_code(500); // Báo lỗi Server Error
}

// --- Trả kết quả về cho client ---
echo json_encode($response);
?>