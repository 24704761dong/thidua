<?php
// File: src/controllers/api_get_student_by_cccd.php
// API để tìm kiếm thông tin học sinh dựa trên Số CCCD (mã QR)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

// Bảo mật: Đảm bảo chỉ người dùng đã đăng nhập (CTV) mới có thể truy cập
if (!isset($_SESSION['student_id'])) { // <--- SỬA LẠI THÀNH 'student_id'
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập.']);
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$cccd = $_GET['cccd'] ?? '';

if (empty($cccd)) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin mã học sinh.']);
    exit();
}

try {
    $db = get_db_connection();
    $current_nam_hoc = $_SESSION['current_nam_hoc_id'] ?? 1;
    
    // Truy vấn để lấy thông tin học sinh và tên lớp theo đúng năm học
    $stmt = $db->prepare(
        "SELECT 
            qt.id, 
            CONCAT(ho_so.ho_dem, ' ', ho_so.ten) AS ho_ten, 
            lh.ten_lop 
         FROM quatrinh_hoc_tap qt
         JOIN ho_so_hoc_sinh ho_so ON qt.ma_hoc_sinh = ho_so.ma_hoc_sinh
         LEFT JOIN raw_lop_hoc lh ON qt.lop_hoc_id = lh.id
         WHERE ho_so.ma_hoc_sinh = ? AND qt.nam_hoc_id = ?"
    );
    
    $stmt->execute([$cccd, $current_nam_hoc]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($student) {
        // Nếu tìm thấy, trả về dữ liệu thành công
        echo json_encode(['success' => true, 'student' => $student]);
    } else {
        // Nếu không tìm thấy, trả về lỗi 404
        http_response_code(404); // Not Found
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin học sinh này.']);
    }

} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    error_log('API Error in api_get_student_by_cccd: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ nội bộ.']);
}
?>