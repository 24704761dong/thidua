<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}

$db = get_db_connection();
$current_nam_hoc_id = get_current_nam_hoc_id();

// Lấy danh sách các năm học KHÁC năm hiện tại để chọn "Năm cũ"
$stmt = $db->prepare("SELECT * FROM nam_hoc WHERE id != ? ORDER BY id DESC");
$stmt->execute([$current_nam_hoc_id]);
$nam_hoc_cu_list = $stmt->fetchAll();

// Lấy danh sách các lớp học của NĂM HỌC HIỆN TẠI để gán học sinh vào
$stmt_lop = $db->prepare("SELECT id, ten_lop FROM lop_hoc WHERE nam_hoc_id = ? ORDER BY ten_lop ASC");
$stmt_lop->execute([$current_nam_hoc_id]);
$lop_hoc_moi_list = $stmt_lop->fetchAll();

require_once __DIR__ . '/../views/admin_nhan_hoc_sinh.php';
