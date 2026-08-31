<?php
// File: src/controllers/ctv_nhap_nhat_ky.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['student_id']) || !($_SESSION['student_permissions']['so_nhat_ky_online'] ?? false)) {
    header('Location: /thidua/giao-vu');
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$tuan_id = $_GET['tuan_id'] ?? null;
$student_id = $_SESSION['student_id'];

if (!$tuan_id) {
    header('Location: /thidua/hocsinh/so-nhat-ky/chon-tuan');
    exit();
}

$db = get_db_connection();

$current_nam_hoc = $_SESSION['current_nam_hoc_id'] ?? 1;

// Lấy thông tin tuần và lớp của CTV
$stmt_info = $db->prepare("
    SELECT t.ten_tuan, qt.lop_hoc_id, l.ten_lop
    FROM raw_tuan_hoc t, quatrinh_hoc_tap qt
    JOIN raw_lop_hoc l ON qt.lop_hoc_id = l.id AND l.nam_hoc_id = ?
    WHERE t.id = ? AND t.nam_hoc_id = ? AND qt.id = ? AND qt.nam_hoc_id = ?
");
$stmt_info->execute([$current_nam_hoc, $tuan_id, $current_nam_hoc, $student_id, $current_nam_hoc]);
$info = $stmt_info->fetch();

if (!$info) {
    die("Lỗi: Không tìm thấy thông tin tuần hoặc lớp.");
}

$lop_hoc_id = $info['lop_hoc_id'];
$ten_lop = $info['ten_lop'];
$ten_tuan = $info['ten_tuan'];

// Tìm hoặc tạo mới bản ghi Sổ Nhật Kỳ cho tuần và lớp này
$stmt_nhat_ky = $db->prepare("SELECT * FROM so_nhat_ky_online WHERE tuan_hoc_id = ? AND lop_hoc_id = ?");
$stmt_nhat_ky->execute([$tuan_id, $lop_hoc_id]);
$nhat_ky = $stmt_nhat_ky->fetch();

if (!$nhat_ky) {
    // Nếu chưa có, tạo bản ghi mới ở trạng thái 'nhap'
    $stmt_create = $db->prepare("INSERT INTO so_nhat_ky_online (tuan_hoc_id, lop_hoc_id, nguoi_nhap_id) VALUES (?, ?, ?)");
    $stmt_create->execute([$tuan_id, $lop_hoc_id, $student_id]);
    $nhat_ky_id = $db->lastInsertId();

    // Tạo sẵn 3 dòng chi tiết
    $stmt_create_details = $db->prepare("INSERT INTO so_nhat_ky_chi_tiet (nhat_ky_id, loai_so) VALUES (?, ?)");
    $stmt_create_details->execute([$nhat_ky_id, 'sdb_tt']);
    $stmt_create_details->execute([$nhat_ky_id, 'sdb_ck']);
    $stmt_create_details->execute([$nhat_ky_id, 'sdb_nk']);

    // Tải lại dữ liệu
    $stmt_nhat_ky->execute([$tuan_id, $lop_hoc_id]);
    $nhat_ky = $stmt_nhat_ky->fetch();
}

$nhat_ky_id = $nhat_ky['id'];

// Lấy dữ liệu chi tiết và minh chứng đã lưu
$stmt_details = $db->prepare("SELECT * FROM so_nhat_ky_chi_tiet WHERE nhat_ky_id = ?");
$stmt_details->execute([$nhat_ky_id]);
$details_raw = $stmt_details->fetchAll();
$details = [];
foreach ($details_raw as $d) {
    $details[$d['loai_so']] = $d;
}
$stmt_proofs = $db->prepare("SELECT *, storage_driver, cloud_key FROM so_nhat_ky_minh_chung WHERE nhat_ky_id = ?");
$stmt_proofs = $db->prepare("SELECT * FROM so_nhat_ky_minh_chung WHERE nhat_ky_id = ?");
$stmt_proofs->execute([$nhat_ky_id]);
$proofs_raw = $stmt_proofs->fetchAll();
$proofs = [];
foreach ($proofs_raw as $p) {
    $proofs[$p['loai_minh_chung']][] = $p;
}

$is_locked = ($nhat_ky['trang_thai'] === 'da_duyet');

// Gọi view để hiển thị
require_once __DIR__ . '/../views/ctv_nhap_nhat_ky.php';