<?php
// File: src/controllers/quan_ly_dang_ky_truc.php (Nâng cấp để hiển thị tất cả các tuần)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}

// Bảo mật: Chỉ admin mới được vào
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$db = get_db_connection();
$danh_sach_dang_ky = [];

try {
    // NÂNG CẤP: Bỏ logic tìm tuần hiện tại và bỏ điều kiện lọc theo tuần
    // Lấy tất cả các đăng ký (chưa bị lưu trữ) của tất cả các tuần
    $stmt_dang_ky = $db->prepare("
        SELECT 
            dt.id, 
            dt.trang_thai, 
            dt.thoi_gian_gui,
            lh.ten_lop,
            th.ten_tuan, -- Lấy thêm tên tuần
            CONCAT(hs.ho_dem, ' ', hs.ten) as ten_nguoi_gui,
            dt.trang_thai_luu_tru
        FROM dang_ky_truc_tuan dt
        JOIN lop_hoc lh ON dt.lop_hoc_id = lh.id
        JOIN hoc_sinh hs ON dt.nguoi_gui_id = hs.id
        JOIN tuan_hoc th ON dt.tuan_hoc_id = th.id -- Join với bảng tuần học
        ORDER BY th.ngay_bat_dau DESC, dt.thoi_gian_gui DESC -- Sắp xếp theo tuần mới nhất trước
    ");
    $stmt_dang_ky->execute();
    $danh_sach_dang_ky = $stmt_dang_ky->fetchAll();

} catch (Exception $e) {
    die("Lỗi CSDL: " . $e->getMessage());
}

// Gọi view và truyền dữ liệu sang
require_once __DIR__ . '/../views/quan_ly_dang_ky_truc.php';