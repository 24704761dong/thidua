<?php
// src/controllers/admin_trung_tam_duyet.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}
// Yêu cầu bootstrap (đi lên 2 cấp ra thư mục gốc, vào config)
require_once __DIR__ . '/../../config/bootstrap.php';

// Yêu cầu helper (đi lên 1 cấp ra src, vào lib)
require_once __DIR__ . '/../lib/helpers.php';

// Bảo vệ trang, chỉ cho phép admin truy cập


// Dữ liệu cho view
$page_title = 'Trung Tâm Phê Duyệt';

// Gọi view để hiển thị (đi lên 1 cấp ra src, vào views)
require_once __DIR__ . '/../views/admin_trung_tam_duyet.php';