<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/lib/helpers.php';

$db = get_db_connection();

// Lấy danh sách tất cả vi phạm để hiển thị trong dropdown
$stmt_vp = $db->query("SELECT id, ten_vi_pham FROM cau_hinh_vi_pham ORDER BY ten_vi_pham  ASC");
$danh_sach_vi_pham = $stmt_vp->fetchAll(PDO::FETCH_ASSOC);

// Lấy cài đặt hiện tại
$cai_dat_vang_p = get_setting($db, 'diemdanh_loi_vang_p');
$cai_dat_vang_kp = get_setting($db, 'diemdanh_loi_vang_kp');

// ==========================================================
//           SỬA LỖI TẠI DÒNG NÀY: THÊM BIẾN $db
// ==========================================================
$cai_dat_bo_tiet = get_setting($db, 'diemdanh_loi_bo_tiet');

require_once __DIR__ . '/../views/admin_diem_danh_nc_cai_dat.php';