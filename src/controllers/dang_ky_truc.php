<?php
// File: src/controllers/dang_ky_truc.php (Đã sửa lỗi hiển thị lịch đã duyệt)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['student_id']) || !($_SESSION['student_permissions']['dang_ky_truc'] ?? false)) {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Bạn không có quyền truy cập chức năng này.'];
    header('Location: /thidua/giao-vu');
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$tuan_id_dang_ky = $_GET['tuan_id'] ?? null;
if (!$tuan_id_dang_ky) {
    header('Location: /thidua/chon-tuan-dang-ky-truc');
    exit();
}

$db = get_db_connection();
$student_id = $_SESSION['student_id'];

try {
    $stmt_tuan = $db->prepare("SELECT * FROM tuan_hoc WHERE id = ?");
    $stmt_tuan->execute([$tuan_id_dang_ky]);
    $tuan_hien_tai = $stmt_tuan->fetch();

    if (!$tuan_hien_tai) {
        $_SESSION['flash_message'] = ['type' => 'warning', 'message' => 'Tuần học không hợp lệ.'];
        header('Location: /thidua/chon-tuan-dang-ky-truc');
        exit();
    }

    $stmt_lop_hs = $db->prepare("
        SELECT hs.lop_hoc_id, lh.ten_lop 
        FROM hoc_sinh hs 
        JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id
        WHERE hs.id = ?
    ");
    $stmt_lop_hs->execute([$student_id]);
    $lop_cua_ctv = $stmt_lop_hs->fetch();

    if (!$lop_cua_ctv) {
        throw new Exception("Không tìm thấy thông tin lớp của bạn.");
    }

    $stmt_ds_lop = $db->prepare("SELECT id, ho_dem, ten FROM hoc_sinh WHERE lop_hoc_id = ? AND trang_thai_hoc_tap = 'dang_hoc' ORDER BY ten , ho_dem ");
    $stmt_ds_lop->execute([$lop_cua_ctv['lop_hoc_id']]);
    $danh_sach_hoc_sinh_trong_lop = $stmt_ds_lop->fetchAll();

    $stmt_lich_truc = $db->prepare("
        SELECT dt.id, dt.trang_thai, dtd.ngay_trong_tuan, dtd.hoc_sinh_id
        FROM dang_ky_truc_tuan dt
        LEFT JOIN dang_ky_truc_chi_tiet dtd ON dt.id = dtd.dang_ky_truc_tuan_id
        WHERE dt.lop_hoc_id = ? AND dt.tuan_hoc_id = ? AND dt.trang_thai_luu_tru = 0
    ");
    $stmt_lich_truc->execute([$lop_cua_ctv['lop_hoc_id'], $tuan_hien_tai['id']]);
    $lich_truc_da_dang_ky_raw = $stmt_lich_truc->fetchAll();

    $lich_truc_da_dang_ky = [];
    $trang_thai_dang_ky = 'Chưa gửi';
    
    // Nâng cấp logic xử lý
    $ds_hoc_sinh_da_truc_ids = []; // Lưu ID các HS đã được phân công

    if (!empty($lich_truc_da_dang_ky_raw)) {
        $trang_thai_dang_ky = $lich_truc_da_dang_ky_raw[0]['trang_thai'];
        foreach($lich_truc_da_dang_ky_raw as $item) {
            if ($item['ngay_trong_tuan'] !== null) {
                // Thêm học sinh vào lịch của ngày tương ứng
                $lich_truc_da_dang_ky[$item['ngay_trong_tuan']][] = $item['hoc_sinh_id'];
                // Thêm ID của học sinh này vào danh sách đã trực
                $ds_hoc_sinh_da_truc_ids[] = $item['hoc_sinh_id'];
            }
        }
    }

    // Nếu lịch đã được duyệt, tạo lại danh sách học sinh chưa được phân công
    if ($trang_thai_dang_ky === 'Đã duyệt') {
        $danh_sach_hoc_sinh_chua_truc = [];
        foreach ($danh_sach_hoc_sinh_trong_lop as $hs) {
            if (!in_array($hs['id'], $ds_hoc_sinh_da_truc_ids)) {
                $danh_sach_hoc_sinh_chua_truc[] = $hs;
            }
        }
        // Ghi đè danh sách học sinh ban đầu bằng danh sách những người chưa trực
        $danh_sach_hoc_sinh_trong_lop = $danh_sach_hoc_sinh_chua_truc;
    }

} catch (Exception $e) {
    die("Lỗi truy vấn CSDL: " . $e->getMessage());
}

require_once __DIR__ . '/../views/dang_ky_truc.php';