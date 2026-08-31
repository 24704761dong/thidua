<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../lib/nam_hoc.php';

$db = get_db_connection();

$current_nam_hoc = current_nam_hoc_id();

// Lấy danh sách vi phạm theo đúng năm học hiện tại
$stmt = $db->prepare("SELECT * FROM raw_cau_hinh_vi_pham WHERE nam_hoc_id = ? ORDER BY nhom_vi_pham ASC, ten_vi_pham ASC");
$stmt->execute([$current_nam_hoc]);
$danh_sach_vi_pham = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh sách các nhóm đã có của năm học hiện tại để làm gợi ý
$stmt_nhom = $db->prepare("SELECT DISTINCT nhom_vi_pham FROM raw_cau_hinh_vi_pham WHERE nhom_vi_pham IS NOT NULL AND nhom_vi_pham != '' AND nam_hoc_id = ? ORDER BY nhom_vi_pham ASC");
$stmt_nhom->execute([$current_nam_hoc]);
$danh_sach_nhom = $stmt_nhom->fetchAll(PDO::FETCH_COLUMN);

// Gọi file View
require_once __DIR__ . '/../views/quan_ly_cau_hinh_vi_pham.php';