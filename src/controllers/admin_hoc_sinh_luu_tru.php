<?php
// File: src/controllers/admin_hoc_sinh_luu_tru.php (File mới)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}
require_once __DIR__ . '/../../config/database.php';

$db = get_db_connection();

// Chỉ lấy những học sinh đã nghỉ học trên toàn hệ thống
$sql = "
    SELECT 
        hs.id, hs.ma_hoc_sinh, hs.ho_dem, hs.ten, hs.ngay_sinh, hs.gioi_tinh, hs.nien_khoa,
        lh.ten_lop,
        nh.ten_nam_hoc as nam_nghi_hoc,
        hs.ngay_nghi_hoc,
        hs.ly_do_nghi_hoc
    FROM ho_so_hoc_sinh hs
    LEFT JOIN (
        SELECT qt1.ma_hoc_sinh, qt1.lop_hoc_id, qt1.nam_hoc_id
        FROM quatrinh_hoc_tap qt1
        INNER JOIN (
            SELECT ma_hoc_sinh, MAX(nam_hoc_id) as max_nam
            FROM quatrinh_hoc_tap
            GROUP BY ma_hoc_sinh
        ) qt2 ON qt1.ma_hoc_sinh = qt2.ma_hoc_sinh AND qt1.nam_hoc_id = qt2.max_nam
    ) qt ON hs.ma_hoc_sinh = qt.ma_hoc_sinh
    LEFT JOIN lop_hoc lh ON qt.lop_hoc_id = lh.id
    LEFT JOIN nam_hoc nh ON qt.nam_hoc_id = nh.id
    WHERE hs.trang_thai_hoc_tap = 'nghi_hoc'
    ORDER BY hs.ngay_nghi_hoc DESC, hs.ten ASC
";

$stmt = $db->prepare($sql);
$stmt->execute();
$danh_sach_hoc_sinh_nghi_hoc = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Gọi file view
require_once __DIR__ . '/../views/admin_hoc_sinh_luu_tru.php';
?>