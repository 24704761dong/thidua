<?php
// File: src/controllers/ctv_danh_sach_da_gui.php (NÂNG CẤP HOÀN CHỈNH)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['student_id']) || !($_SESSION['student_permissions']['nhap_vi_pham'] ?? false)) {
    header('Location: /thidua/giao-vu');
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$tuan_id = $_GET['tuan_id'] ?? null;
if (!$tuan_id) {
    header('Location: /thidua/ctv/chon-tuan-vi-pham');
    exit();
}

$db = get_db_connection();
$danh_sach_tong_hop = [];
$tuan_hoc = null;

try {
    $stmt_tuan = $db->prepare("SELECT ten_tuan FROM tuan_hoc WHERE id = ?");
    $stmt_tuan->execute([$tuan_id]);
    $tuan_hoc = $stmt_tuan->fetch();

    if (!$tuan_hoc) {
        throw new Exception("Tuần học không hợp lệ.");
    }

    // --- CÂU TRUY VẤN CUỐI CÙNG: SẮP XẾP VÀ ĐỊNH DẠNG TÊN NGƯỜI NHẬP ---
    $sql = "
        SELECT 
            vp.thoi_gian_nhap, vp.ngay_vi_pham, vp.ghi_chu,
            COALESCE(CONCAT(hs_vp.ho_dem, ' ', hs_vp.ten), vp.raw_ho_ten) as ho_ten,
            COALESCE(lh.ten_lop, vp.raw_ten_lop) as ten_lop,
            chvp.ten_vi_pham,
            CASE
                WHEN u_nhap.id IS NOT NULL THEN u_nhap.ho_ten
                WHEN ctv_nhap.id IS NOT NULL THEN
                    CONCAT(ctv_nhap.ten, ' - ', (
                        CASE SUBSTRING(lh_ctv.ten_lop, 1, 2)
                            WHEN '10' THEN CONCAT('A', SUBSTRING(lh_ctv.ten_lop, 4))
                            WHEN '11' THEN CONCAT('B', SUBSTRING(lh_ctv.ten_lop, 4))
                            WHEN '12' THEN CONCAT('C', SUBSTRING(lh_ctv.ten_lop, 4))
                            ELSE lh_ctv.ten_lop
                        END
                    ))
                ELSE 'Không rõ'
            END as ten_nguoi_gui,
            'Đã duyệt' as trang_thai
        FROM vi_pham_hoc_sinh vp
        LEFT JOIN hoc_sinh hs_vp ON vp.hoc_sinh_id = hs_vp.id
        LEFT JOIN lop_hoc lh ON hs_vp.lop_hoc_id = lh.id
        LEFT JOIN cau_hinh_vi_pham chvp ON vp.vi_pham_id = chvp.id
        LEFT JOIN users u_nhap ON vp.nguoi_nhap_id = u_nhap.id
        LEFT JOIN hoc_sinh ctv_nhap ON vp.nguoi_nhap_id = ctv_nhap.id
        LEFT JOIN lop_hoc lh_ctv ON ctv_nhap.lop_hoc_id = lh_ctv.id
        WHERE vp.tuan_hoc_id = :tuan_id

        UNION ALL

        SELECT 
            vptt.thoi_gian_nhap, vptt.ngay_vi_pham, vptt.ghi_chu, vptt.raw_ho_ten as ho_ten, vptt.raw_ten_lop as ten_lop,
            chvp.ten_vi_pham,
            CONCAT(sender.ten, ' - ', (
                CASE SUBSTRING(lh_sender.ten_lop, 1, 2)
                    WHEN '10' THEN CONCAT('A', SUBSTRING(lh_sender.ten_lop, 4))
                    WHEN '11' THEN CONCAT('B', SUBSTRING(lh_sender.ten_lop, 4))
                    WHEN '12' THEN CONCAT('C', SUBSTRING(lh_sender.ten_lop, 4))
                    ELSE lh_sender.ten_lop
                END
            )) as ten_nguoi_gui,
            CASE vptt.trang_thai_gui
                WHEN 'da_gui' THEN 'Chờ duyệt'
                WHEN 'da_loai_bo' THEN 'Bị loại bỏ'
                ELSE 'Không xác định'
            END as trang_thai
        FROM vi_pham_tam_thoi vptt
        LEFT JOIN cau_hinh_vi_pham chvp ON vptt.vi_pham_id = chvp.id
        JOIN hoc_sinh sender ON vptt.nguoi_nhap_id = sender.id
        JOIN lop_hoc lh_sender ON sender.lop_hoc_id = lh_sender.id
        WHERE vptt.tuan_hoc_id = :tuan_id2 AND vptt.trang_thai_gui IN ('da_gui', 'da_loai_bo')

        ORDER BY thoi_gian_nhap ASC
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute([':tuan_id' => $tuan_id, ':tuan_id2' => $tuan_id]);
    $danh_sach_tong_hop = $stmt->fetchAll();

} catch (Exception $e) {
    die("Lỗi CSDL: " . $e->getMessage());
}

require_once __DIR__ . '/../views/ctv_danh_sach_da_gui.php';