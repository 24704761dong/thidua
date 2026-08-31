<?php
// File: src/controllers/admin_xem_minh_chung.php (Đã sửa lỗi cú pháp)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$db = get_db_connection();

// 1. Lấy tất cả các tuần cho bộ lọc
$all_weeks = $db->query("SELECT id, ten_tuan FROM tuan_hoc ORDER BY ngay_bat_dau DESC")->fetchAll(PDO::FETCH_ASSOC);

// 2. Xác định tuần được chọn (mặc định là tuần mới nhất)
$selected_tuan_id = $_GET['tuan_id'] ?? ($all_weeks[0]['id'] ?? null);
$selected_tuan_info = null;

$proofs_by_class = [];

if ($selected_tuan_id) {
    // Tìm thông tin của tuần đã chọn
    foreach($all_weeks as $week) {
        if ($week['id'] == $selected_tuan_id) {
            $selected_tuan_info = $week;
            break;
        }
    }

    // 3. Lấy tất cả minh chứng của tuần đã chọn và thông tin lớp liên quan
    $stmt_proofs = $db->prepare("
        SELECT
            sm.id, sm.file_path, sm.original_filename, sm.file_type,
            sm.thumbnail_path, 
            sm.storage_driver,
            sm.cloud_key,   -- <-- ĐÃ THÊM DẤU PHẨY
            lh.ten_lop
        FROM so_nhat_ky_minh_chung sm
        JOIN so_nhat_ky_online snk ON sm.nhat_ky_id = snk.id
        JOIN lop_hoc lh ON snk.lop_hoc_id = lh.id
        WHERE snk.tuan_hoc_id = ?
        ORDER BY lh.ten_lop, sm.original_filename
    ");
    $stmt_proofs->execute([$selected_tuan_id]);
    $all_proofs = $stmt_proofs->fetchAll(PDO::FETCH_ASSOC);

    // 4. Gom nhóm các minh chứng theo từng lớp
    foreach ($all_proofs as $proof) {
        // Kiểm tra xem ten_lop có tồn tại không
        if (isset($proof['ten_lop'])) {
            $proofs_by_class[$proof['ten_lop']][] = $proof;
        } else {
            // Xử lý trường hợp không có ten_lop (nếu cần)
            $proofs_by_class['Khong_Xac_Dinh'][] = $proof;
        }
    }
} // <-- **ĐÃ THÊM DẤU } ĐÓNG KHỐI IF**

// Gọi view để hiển thị
require_once __DIR__ . '/../views/admin_xem_minh_chung.php';
?>