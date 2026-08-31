<?php
// File: src/controllers/admin_hoat_dong_diem_danh.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || ($_SESSION['user_vai_tro'] !== 'admin' && !in_array('quan_ly_hoat_dong', $_SESSION['user_permissions'] ?? []) && !in_array('all', $_SESSION['user_permissions'] ?? []))) {
    header('Location: /thidua/admin');
    exit();
}

require_once __DIR__ . '/../../config/database.php';
$db = get_db_connection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: /thidua/admin/hoat-dong');
    exit();
}

$stmt = $db->prepare("SELECT * FROM hoat_dong WHERE id = ?");
$stmt->execute([$id]);
$hoat_dong = $stmt->fetch();

if (!$hoat_dong) {
    header('Location: /thidua/admin/hoat-dong');
    exit();
}

$nam_hoc_id = $_SESSION['nam_hoc_id'] ?? null;
if (!$nam_hoc_id) {
    try {
        $stmt_nh = $db->query("SELECT id FROM nam_hoc WHERE is_mac_dinh = 1 LIMIT 1");
        $nh = $stmt_nh->fetch();
        if ($nh) $nam_hoc_id = $nh['id'];
    } catch (Exception $e) {}
}

$danh_sach_lop = [];
if ($nam_hoc_id) {
    try {
        $stmt_lop = $db->prepare("SELECT id, ten_lop FROM lop_hoc WHERE nam_hoc_id = ? ORDER BY ten_lop ASC");
        $stmt_lop->execute([$nam_hoc_id]);
        $danh_sach_lop = $stmt_lop->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}
}

$danh_sach_chuc_vu = [];
try {
    $stmt_chuc_vu = $db->query("SELECT DISTINCT chuc_vu FROM hoc_sinh WHERE chuc_vu IS NOT NULL AND chuc_vu != '' ORDER BY chuc_vu");
    $danh_sach_chuc_vu = $stmt_chuc_vu->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {}

$page_title = 'Điểm danh - ' . htmlspecialchars($hoat_dong['ten_hoat_dong']);
require_once __DIR__ . '/../views/admin_hoat_dong_diem_danh.php';
?>
