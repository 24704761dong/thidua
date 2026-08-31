<?php
// File: src/controllers/api_get_violation_details.php (Đã sửa)
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

// <<-- BỎ QUA KIỂM TRA ĐĂNG NHẬP ĐỂ TEST -->>
// if (!isset($_SESSION['user_id'])) { http_response_code(403); exit(); } 

require_once __DIR__ . '/../../config/database.php';

$tuan_id = $_GET['tuan_id'] ?? 0;
$lop_id = $_GET['lop_id'] ?? 0;

if (!$tuan_id || !$lop_id) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Thiếu ID tuần hoặc ID lớp.']);
    exit();
}

$db = get_db_connection();

// ===== SỬA LẠI CÂU LỆNH SQL ĐỂ LẤY THÊM CỘT `ngay_vi_pham` =====
$sql = "SELECT 
            (CONCAT(hs.ho_dem, ' ', hs.ten)) as ho_ten, 
            chvp.ten_vi_pham, 
            vp.ghi_chu,
            vp.ngay_vi_pham 
        FROM vi_pham_hoc_sinh vp 
        JOIN quatrinh_hoc_tap qt ON vp.hoc_sinh_id = qt.id
        JOIN hoc_sinh hs ON hs.ma_hoc_sinh = qt.ma_hoc_sinh AND hs.nam_hoc_id = qt.nam_hoc_id
        JOIN cau_hinh_vi_pham chvp ON vp.vi_pham_id = chvp.id 
        WHERE vp.tuan_hoc_id = ? AND qt.lop_hoc_id = ?";
// ===== KẾT THÚC SỬA SQL =====

$stmt = $db->prepare($sql);
$stmt->execute([$tuan_id, $lop_id]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>