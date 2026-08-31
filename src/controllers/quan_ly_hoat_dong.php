<?php
// File: src/controllers/quan_ly_hoat_dong.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || ($_SESSION['user_vai_tro'] !== 'admin' && !in_array('quan_ly_hoat_dong', $_SESSION['user_permissions'] ?? []) && !in_array('all', $_SESSION['user_permissions'] ?? []))) {
    header('Location: /thidua/admin');
    exit();
}

require_once __DIR__ . '/../../config/database.php';
$db = get_db_connection();

$nam_hoc_id = $_SESSION['nam_hoc_id'] ?? null;
if (!$nam_hoc_id) {
    try {
        $stmt = $db->query("SELECT id FROM nam_hoc WHERE is_mac_dinh = 1 LIMIT 1");
        $nam_hoc = $stmt->fetch();
        if ($nam_hoc) {
            $nam_hoc_id = $nam_hoc['id'];
        }
    } catch (Exception $e) {}
}

$danh_sach_lop = [];
try {
    $stmt = $db->prepare("SELECT ten_lop FROM lop_hoc WHERE nam_hoc_id = ? ORDER BY ten_lop ASC");
    $stmt->execute([$nam_hoc_id]);
    $danh_sach_lop = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

$page_title = 'Quản lý Hoạt động';
require_once __DIR__ . '/../views/quan_ly_hoat_dong.php';
?>
