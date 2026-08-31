<?php
// File: src/controllers/ctv_nhat_ky_chon_tuan.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra session và quyền truy cập
if (!isset($_SESSION['student_id']) || !($_SESSION['student_permissions']['so_nhat_ky_online'] ?? false)) {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Bạn không có quyền truy cập chức năng này.'];
    header('Location: /thidua/giao-vu');
    exit();
}

require_once __DIR__ . '/../../config/database.php';

// Các biến này sẽ được sử dụng bởi view `ctv_chon_tuan.php`
$page_title = 'CHỌN TUẦN - NHẬT KỲ TRỰC TUYẾN';
$page_icon = 'bi-book-half';
$base_url = '/thidua/hocsinh/so-nhat-ky/nhap?tuan_id=';
$type = 'so_nhat_ky'; // Để view biết đang ở chức năng nào

$db = get_db_connection();
$current_nam_hoc = $_SESSION['current_nam_hoc_id'] ?? 1;

// Lấy tất cả các tuần học CHƯA BỊ KHÓA bởi Admin theo năm học
$stmt_all_weeks = $db->prepare("SELECT * FROM raw_tuan_hoc WHERE is_locked = 0 AND nam_hoc_id = ? ORDER BY ngay_bat_dau ASC");
$stmt_all_weeks->execute([$current_nam_hoc]);
$all_weeks = $stmt_all_weeks->fetchAll();

// Phân loại tuần vào các học kỳ
$weeks_hk1 = [];
$weeks_hk2 = [];
foreach ($all_weeks as $week) {
    if ($week['hoc_ky'] == 1) {
        $weeks_hk1[] = $week;
    } else {
        $weeks_hk2[] = $week;
    }
}

$stmt_year = $db->prepare("SELECT ten_nam_hoc FROM nam_hoc WHERE id = ?");
$stmt_year->execute([$current_nam_hoc]);
$school_year = $stmt_year->fetchColumn() ?: "2025 - 2026";

// Gọi view chung để hiển thị
require_once __DIR__ . '/../views/ctv_chon_tuan.php';