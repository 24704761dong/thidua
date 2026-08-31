<?php
// File: src/controllers/nhat_ky_su_dung.php (PHIÊN BẢN HOÀN CHỈNH ĐÃ CẬP NHẬT)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}

// Chỉ cần kết nối đến CSDL chính là đủ
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../lib/user_agent_parser.php';

$main_db = get_db_connection();

try {
    // Lấy danh sách học sinh và đếm số lần đăng nhập, tra cứu từ CSDL chính.
    // Đã bỏ `ATTACH DATABASE` và các kết nối thừa.
    // Đã đổi `JOIN` thành `LEFT JOIN` để không bỏ sót học sinh chưa có lớp.
    $sql = "
        SELECT
            hs.id, hs.ma_hoc_sinh, lh.ten_lop, hs.ho_dem, hs.ten, hs.ngay_sinh, hs.gioi_tinh, hs.email, hs.trang_thai_tai_khoan,
            (SELECT COUNT(*) FROM lich_su_dang_nhap WHERE hoc_sinh_id = hs.id) as login_count,
            (SELECT COUNT(*) FROM nhat_ky_tra_cuu WHERE ma_tra_cuu = hs.ma_hoc_sinh AND loai_tra_cuu = 'hoc_sinh') as lookup_count
        FROM hoc_sinh hs
        LEFT JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id
        ORDER BY lh.ten_lop, hs.ten 
    ";

    $student_logs = $main_db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    // Nếu có lỗi, hiển thị thông báo chi tiết để dễ dàng gỡ lỗi
    die("Lỗi CSDL: " . $e->getMessage());
}

require_once __DIR__ . '/../views/nhat_ky_su_dung.php';
