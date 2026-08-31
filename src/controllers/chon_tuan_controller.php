<?php
// File: src/controllers/chon_tuan_controller.php
if (function_exists('opcache_invalidate')) { opcache_invalidate(__FILE__, true); }
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../lib/week_permissions.php';

$db = get_db_connection();
$current_nam_hoc_id = $_SESSION['current_nam_hoc_id'] ?? 1;

// Lấy thông tin năm học
$stmt_nam = $db->prepare("SELECT ten_nam_hoc FROM nam_hoc WHERE id = ?");
$stmt_nam->execute([$current_nam_hoc_id]);
$nam_hoc_row = $stmt_nam->fetch(PDO::FETCH_ASSOC);
$school_year = $nam_hoc_row ? $nam_hoc_row['ten_nam_hoc'] : 'Năm học hiện tại';

// Quyền quản lý tuần (có thể thêm/sửa/xoá/khóa)
$can_manage_weeks = can_current_user_manage_weeks();

// Lấy danh sách tuần của năm học hiện tại
$stmt_tuan = $db->prepare("SELECT * FROM raw_tuan_hoc WHERE nam_hoc_id = ? ORDER BY ngay_bat_dau ASC");
$stmt_tuan->execute([$current_nam_hoc_id]);
$all_weeks = $stmt_tuan->fetchAll(PDO::FETCH_ASSOC);

$weeks_hk1 = [];
$weeks_hk2 = [];

foreach ($all_weeks as $week) {
    if ($week['hoc_ky'] == 1) {
        $weeks_hk1[] = $week;
    } else {
        $weeks_hk2[] = $week;
    }
}

// Gọi view
require_once __DIR__ . '/../views/chon_tuan_chung.php';
