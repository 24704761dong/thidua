<?php
// File: src/controllers/admin_xem_chi_tiet_nhat_ky.php (Đã làm sạch ký tự ẩn)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (
    !isset($_SESSION['user_id']) ||
    !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])
) {
    header('Location: /thidua/tracuu');
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$nhat_ky_id = $_GET['id'] ?? null;
if (!$nhat_ky_id) {
    die("ID Sổ Nhật Kỳ không hợp lệ.");
}

$db = get_db_connection();

$current_nam_hoc = $_SESSION['current_nam_hoc_id'] ?? 1;

// Lấy thông tin chính
$stmt_main = $db->prepare("
    SELECT snk.*, t.ten_tuan, l.ten_lop, (CONCAT(h.ho_dem, ' ', h.ten)) as ten_ctv
    FROM so_nhat_ky_online snk
    JOIN raw_tuan_hoc t ON snk.tuan_hoc_id = t.id AND t.nam_hoc_id = ?
    JOIN raw_lop_hoc l ON snk.lop_hoc_id = l.id AND l.nam_hoc_id = ?
    LEFT JOIN ho_so_hoc_sinh h ON snk.nguoi_nhap_id = h.id
    WHERE snk.id = ?
");
$stmt_main->execute([$current_nam_hoc, $current_nam_hoc, $nhat_ky_id]);
$nhat_ky = $stmt_main->fetch();

if (!$nhat_ky) {
    die("Không tìm thấy Sổ Nhật Kỳ.");
}

// Lấy chi tiết số tiết
$stmt_details = $db->prepare("SELECT * FROM so_nhat_ky_chi_tiet WHERE nhat_ky_id = ?");
$stmt_details->execute([$nhat_ky_id]);
$details_raw = $stmt_details->fetchAll();
$details = [];
foreach ($details_raw as $d) {
    $details[$d['loai_so']] = $d;
}

// Lấy danh sách minh chứng (Đã nâng cấp để lấy cột cloud)
$stmt_proofs = $db->prepare("SELECT *, storage_driver, cloud_key FROM so_nhat_ky_minh_chung WHERE nhat_ky_id = ?");
$stmt_proofs->execute([$nhat_ky_id]);
$proofs_raw = $stmt_proofs->fetchAll();
$proofs = [];
foreach ($proofs_raw as $p) {
    $loai = $p['loai_minh_chung'];
    if (in_array($loai, ['khac', 'minh_chung_khac'])) {
        $loai = 'sdb_tt';
    }
    $proofs[$loai][] = $p;
}

$submitted_at_formatted = null;
if (!empty($nhat_ky['ngay_gui']) && $nhat_ky['ngay_gui'] !== '0000-00-00 00:00:00') {
    try {
        $timezone_name = date_default_timezone_get() ?: 'Asia/Ho_Chi_Minh';
        $submitted_at = new \DateTime($nhat_ky['ngay_gui'], new \DateTimeZone($timezone_name));
        $submitted_at_formatted = $submitted_at->format('d/m/Y H:i');
    } catch (Throwable $e) {
        $submitted_at_formatted = null;
    }
}

// ================== BẮT ĐẦU TÍNH TOÁN TỔNG ==================
$totals = ['tot' => 0, 'kha' => 0, 'tb' => 0, 'yeu' => 0];
foreach ($details as $detail) {
    $totals['tot'] += (int)($detail['so_tiet_tot'] ?? 0);
    $totals['kha'] += (int)($detail['so_tiet_kha'] ?? 0);
    $totals['tb']  += (int)($detail['so_tiet_tb'] ?? 0);
    $totals['yeu'] += (int)($detail['so_tiet_yeu'] ?? 0);
}
// =================== KẾT THÚC TÍNH TOÁN TỔNG ===================

require_once __DIR__ . '/../views/admin_xem_chi_tiet_nhat_ky.php';
?>