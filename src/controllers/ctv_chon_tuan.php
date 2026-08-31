<?php
// File: src/controllers/ctv_chon_tuan.php (Controller chung mới cho CTV)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['student_id'])) {
    header('Location: /thidua/tracuu');
    exit();
}

require_once __DIR__ . '/../../config/database.php';

// Lấy "loại" chức năng từ URL (vi_pham, diem_danh, dang_ky_truc)
$type = $_GET['type'] ?? '';
$permissions = $_SESSION['student_permissions'] ?? [];

// Xác định thông tin cho từng loại chức năng
$page_configs = [
    'vi_pham' => [
        'permission' => 'nhap_vi_pham',
        'title' => 'CHỌN TUẦN - NHẬP VI PHẠM',
        'icon' => 'bi-pencil-square',
        'base_url' => '/thidua/hocsinh/nhap-vi-pham?tuan_id='
    ],
    'diem_danh' => [
        'permission' => 'nhap_diem_danh',
        'title' => 'Chọn TUẦN - NHẬP ĐIỂM DANH',
        'icon' => 'bi-calendar-check-fill',
        'base_url' => '/thidua/hocsinh/diem-danh/nhap?tuan_id='
    ],
    'dang_ky_truc' => [
        'permission' => 'dang_ky_truc',
        'title' => 'CHỌN TUẦN - ĐĂNG KÝ TRỰC',
        'icon' => 'bi-calendar-plus-fill',
        'base_url' => '/thidua/dang-ky-truc?tuan_id='
    ]
];

$has_permission = $permissions[$page_configs[$type]['permission']] ?? false;

// Kiem tra quyen tam thoi cho viec nhap vi pham dua tren lich truc
if ($type === 'vi_pham' && !$has_permission) {
    $db_check = get_db_connection();
    $stmt_check_duty = $db_check->prepare("
        SELECT 1 
        FROM dang_ky_truc_tuan dkt
        JOIN dang_ky_truc_chi_tiet ct ON dkt.id = ct.dang_ky_truc_tuan_id
        JOIN raw_tuan_hoc th ON dkt.tuan_hoc_id = th.id
        WHERE ct.hoc_sinh_id = ? 
          AND dkt.trang_thai = 'Da duyet'
          AND CURDATE() BETWEEN th.ngay_bat_dau AND th.ngay_ket_thuc
    ");
    $stmt_check_duty->execute([$_SESSION['student_id']]);
    if ($stmt_check_duty->fetchColumn()) {
        $has_permission = true;
    }
}

// Kiểm tra xem type có hợp lệ và CTV có quyền truy cập không
if (!isset($page_configs[$type]) || !$has_permission) {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Bạn không có quyền truy cập chức năng này.'];
    header('Location: /thidua/giao-vu');
    exit();
}

// Lấy các thông tin cấu hình tương ứng
$page_title = $page_configs[$type]['title'];
$page_icon = $page_configs[$type]['icon'];
$base_url = $page_configs[$type]['base_url'];

$db = get_db_connection();

// Lấy tất cả các tuần học CHƯA BỊ KHÓA (is_locked = 0)
$all_weeks = $db->query("SELECT * FROM tuan_hoc WHERE is_locked = 0 ORDER BY ngay_bat_dau ASC")->fetchAll();

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
$school_year = "2025 - 2026";

// Gọi view chung để hiển thị
require_once __DIR__ . '/../views/ctv_chon_tuan.php';