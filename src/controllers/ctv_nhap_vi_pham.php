<?php
// File: src/controllers/ctv_nhap_vi_pham.php (ĐÃ NÂNG CẤP HOÀN CHỈNH)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['student_id'])) {
    header('Location: /thidua/giao-vu');
    exit();
}

require_once __DIR__ . '/../../config/database.php';
$db = get_db_connection();

$has_permission = $_SESSION['student_permissions']['nhap_vi_pham'] ?? false;

if (!$has_permission) {
    $stmt_check_duty = $db->prepare("
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

if (!$has_permission) {
    header('Location: /thidua/giao-vu');
    exit();
}

$tuan_id = $_GET['tuan_id'] ?? null;
if (!$tuan_id) {
    header('Location: /thidua/hocsinh/chon-tuan-vi-pham');
    exit();
}

$db = get_db_connection();
$ctv_id = $_SESSION['student_id'];
$current_nam_hoc = $_SESSION['current_nam_hoc_id'] ?? 1;

try {
    // Lấy thông tin tuần học (bảng gốc)
    $stmt_tuan = $db->prepare("SELECT * FROM raw_tuan_hoc WHERE id = ? AND nam_hoc_id = ?");
    $stmt_tuan->execute([$tuan_id, $current_nam_hoc]);
    $tuan_hoc = $stmt_tuan->fetch();
    if (!$tuan_hoc) {
        header('Location: /thidua/hocsinh/chon-tuan-vi-pham');
        exit();
    }

    // Lấy danh sách cấu hình vi phạm theo đúng năm học
    $stmt_cau_hinh = $db->prepare("SELECT id, ten_vi_pham, nhom_vi_pham FROM raw_cau_hinh_vi_pham WHERE nam_hoc_id = ? ORDER BY nhom_vi_pham, ten_vi_pham");
    $stmt_cau_hinh->execute([$current_nam_hoc]);
    $danh_sach_cau_hinh_vi_pham = $stmt_cau_hinh->fetchAll();

    // Lấy ghi chú mặc định của CTV
    $stmt_ctv = $db->prepare("SELECT ghi_chu_mac_dinh FROM ho_so_hoc_sinh WHERE ma_hoc_sinh = (SELECT ma_hoc_sinh FROM quatrinh_hoc_tap WHERE id = ?)");
    $stmt_ctv->execute([$ctv_id]);
    $ghi_chu_mac_dinh = $stmt_ctv->fetchColumn();

    // Lấy danh sách các vi phạm đang ở trạng thái 'nhap'
    $stmt_nhap = $db->prepare("
        SELECT vptt.*, ho_so.ma_hoc_sinh, ho_so.trang_thai_hoc_tap, (CONCAT(ho_so.ho_dem, ' ', ho_so.ten)) as ho_ten_day_du, lh.ten_lop, chvp.ten_vi_pham
        FROM vi_pham_tam_thoi vptt
        LEFT JOIN quatrinh_hoc_tap qt ON vptt.hoc_sinh_id = qt.id
        LEFT JOIN ho_so_hoc_sinh ho_so ON qt.ma_hoc_sinh = ho_so.ma_hoc_sinh
        LEFT JOIN raw_lop_hoc lh ON qt.lop_hoc_id = lh.id
        LEFT JOIN raw_cau_hinh_vi_pham chvp ON vptt.vi_pham_id = chvp.id
        WHERE vptt.nguoi_nhap_id = ? AND vptt.tuan_hoc_id = ? AND vptt.trang_thai_gui = 'nhap'
        ORDER BY vptt.id DESC
    ");
    $stmt_nhap->execute([$ctv_id, $tuan_id]);
    $danh_sach_vi_pham_da_nhap = $stmt_nhap->fetchAll();

    // === LOGIC MỚI: TÌM VI PHẠM CUỐI CÙNG CTV ĐÃ NHẬP ===
    $last_violation_id = null;
    $stmt_last_vp = $db->prepare("
        SELECT vi_pham_id FROM vi_pham_tam_thoi 
        WHERE nguoi_nhap_id = ? AND vi_pham_id IS NOT NULL 
        ORDER BY id DESC LIMIT 1
    ");
    $stmt_last_vp->execute([$ctv_id]);
    $result = $stmt_last_vp->fetch();
    if ($result) {
        $last_violation_id = $result['vi_pham_id'];
    }

} catch (Exception $e) {
    die("Lỗi CSDL: " . $e->getMessage());
}

require_once __DIR__ . '/../views/ctv_nhap_vi_pham.php';