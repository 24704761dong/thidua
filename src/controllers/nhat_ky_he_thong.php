<?php
// File: src/controllers/nhat_ky_he_thong.php (PHIÊN BẢN ĐÃ SỬA LỖI)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}

// Nạp kết nối CSDL chính VÀ file helpers (chứa hàm get_support_request_count)
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../lib/helpers.php'; 

// Tạo kết nối đến CSDL chính
$main_db = get_db_connection();    

try {
    // 1. Lấy tổng lượt truy cập (từ CSDL CHÍNH, bảng he_thong_thong_ke)
    $stmt_total = $main_db->query("SELECT stat_value FROM he_thong_thong_ke WHERE stat_key = 'tong_so_luot_truy_cap'");
    $total_visits = $stmt_total ? $stmt_total->fetchColumn() : 0;

    // 2. Lấy danh sách đang truy cập (từ CSDL chính, bảng phien_truy_cap)
    $five_minutes_ago = time() - (5 * 60);
    $stmt_active = $main_db->prepare("SELECT * FROM phien_truy_cap WHERE last_activity > ? ORDER BY last_activity DESC");
    $stmt_active->execute([$five_minutes_ago]);
    $active_sessions = $stmt_active->fetchAll();

    // 3. Lấy TOÀN BỘ lịch sử tra cứu (từ CSDL chính, bảng nhat_ky_tra_cuu)
    $lookup_history = $main_db->query("
        SELECT
            nkt.*,
            CASE
                WHEN nkt.loai_tra_cuu = 'hoc_sinh' THEN (CONCAT(hs.ho_dem, ' ', hs.ten))
                WHEN nkt.loai_tra_cuu = 'giao_vien' THEN lh.gvcn_ten
                ELSE NULL
            END as ten_doi_tuong
        FROM nhat_ky_tra_cuu nkt
        LEFT JOIN hoc_sinh hs ON nkt.ma_tra_cuu COLLATE utf8mb4_unicode_ci = hs.ma_hoc_sinh COLLATE utf8mb4_unicode_ci AND nkt.loai_tra_cuu = 'hoc_sinh'
        LEFT JOIN lop_hoc lh ON nkt.ma_tra_cuu COLLATE utf8mb4_unicode_ci = lh.gvcn_ma COLLATE utf8mb4_unicode_ci AND nkt.loai_tra_cuu = 'giao_vien'
        ORDER BY nkt.id DESC
    ")->fetchAll();

    // 4. Lấy TOÀN BỘ lượt đăng nhập của HỌC SINH (từ CSDL chính)
    $login_history = $main_db->query("
        SELECT
            lsdn.thoi_gian_dang_nhap,
            lsdn.dia_chi_ip,
            CONCAT(hs.ho_dem, ' ', hs.ten) as ten_hoc_sinh,
            lh.ten_lop
        FROM lich_su_dang_nhap lsdn
        JOIN hoc_sinh hs ON lsdn.hoc_sinh_id = hs.id
        JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id
        ORDER BY lsdn.id DESC
    ")->fetchAll();

} catch (Exception $e) {
    // Xử lý lỗi nếu có
    die("Lỗi khi truy vấn dữ liệu Nhật ký hệ thống: " . $e->getMessage());
}
// 5. Lấy số lượng yêu cầu hỗ trợ (truy vấn trực tiếp)
try {
    $stmt_support = $main_db->query("SELECT COUNT(*) FROM ho_tro_khan_cap WHERE trang_thai = 'cho_xu_ly'");
    $support_request_count = (int)($stmt_support ? $stmt_support->fetchColumn() : 0);
} catch (Exception $e) {
    $support_request_count = 0;
}

// Sau khi có đủ dữ liệu, gọi file view để hiển thị
require_once __DIR__ . '/../views/nhat_ky_he_thong.php';