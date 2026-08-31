<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu'); exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/lib/helpers.php';

$db = get_db_connection();

$stmt_vp = $db->query("SELECT id, ten_vi_pham FROM cau_hinh_vi_pham ORDER BY ten_vi_pham  ASC");
$danh_sach_vi_pham = $stmt_vp->fetchAll(PDO::FETCH_ASSOC);

$cai_dat_di_tre = get_setting($db, 'trehoc_loi_vi_pham');

require_once __DIR__ . '/../views/admin_tre_hoc_cai_dat.php';