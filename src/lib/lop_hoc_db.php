<?php
/**
 * File này chứa các hàm tương tác với bảng lop_hoc trong CSDL.
 */

/**
 * Lấy tất cả các lớp học từ CSDL theo năm học hiện tại, sắp xếp theo khối và tên lớp.
 * @param PDO $db Đối tượng kết nối CSDL PDO.
 * @return array Mảng chứa tất cả các lớp học.
 */
if (!function_exists('get_all_lop_hoc')) {
    function get_all_lop_hoc(PDO $db) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $current_nam_hoc = $_SESSION['current_nam_hoc_id'] ?? 1;
        
        $stmt = $db->prepare("SELECT * FROM lop_hoc WHERE nam_hoc_id = ? ORDER BY CAST(SUBSTR(ten_lop, 1, 2) AS INTEGER) ASC, SUBSTR(ten_lop, 3, 1) ASC, CAST(SUBSTR(ten_lop, 4) AS INTEGER) ASC");
        $stmt->execute([$current_nam_hoc]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
if (!function_exists('get_lop_hoc_by_id')) {
    function get_lop_hoc_by_id(PDO $db, $id) {
        $stmt = $db->prepare("SELECT * FROM lop_hoc WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
function get_tuan_hoc_by_id(PDO $db, $tuan_hoc_id) {
    $stmt = $db->prepare("SELECT * FROM tuan_hoc WHERE id = ?");
    $stmt->execute([$tuan_hoc_id]);
    return $stmt->fetch();
}
// *** ĐÃ SỬA LẠI HÀM NÀY ***
function get_vi_pham_by_lop_and_tuan(PDO $db, $lop_hoc_id, $tuan_hoc_id) {
    $sql = "
        SELECT 
            vphs.ngay_vi_pham, 
            vphs.hoc_sinh_id,
            hs.ma_hoc_sinh,
            COALESCE(CONCAT(hs.ho_dem, ' ', hs.ten), vphs.raw_ho_ten) as ho_ten,
            chvp.ten_vi_pham,
            chvp.diem_tru,
            vphs.ghi_chu
        FROM vi_pham_hoc_sinh vphs 
        LEFT JOIN quatrinh_hoc_tap qt ON vphs.hoc_sinh_id = qt.id 
        LEFT JOIN ho_so_hoc_sinh hs ON qt.ma_hoc_sinh = hs.ma_hoc_sinh 
        JOIN cau_hinh_vi_pham chvp ON vphs.vi_pham_id = chvp.id 
        WHERE qt.lop_hoc_id = ? AND vphs.tuan_hoc_id = ? 
        ORDER BY vphs.ngay_vi_pham ASC";
    $stmt = $db->prepare($sql);
    $stmt->execute([$lop_hoc_id, $tuan_hoc_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function get_diem_danh_summary_by_lop_and_tuan(PDO $db, $lop_hoc_id, $tuan_hoc_id) {
    $sql = "SELECT SUM(vang_p) as total_p, SUM(vang_kp) as total_kp, SUM(bo_tiet) as total_bt FROM diem_danh WHERE lop_hoc_id = ? AND tuan_hoc_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$lop_hoc_id, $tuan_hoc_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return [
        'vang_p' => (int)($result['total_p'] ?? 0),
        'vang_kp' => (int)($result['total_kp'] ?? 0),
        'bo_tiet' => (int)($result['total_bt'] ?? 0),
    ];
}
function get_violations_by_week_ids(PDO $db, array $tuan_ids) {
    if (empty($tuan_ids)) {
        return [];
    }
    // Tạo placeholders cho các ID tuần (?,?,?)
    $placeholders = implode(',', array_fill(0, count($tuan_ids), '?'));
    
    $sql = "
        SELECT 
            lh.ten_lop,
            lh.gvcn_ten,
            hs.ma_hoc_sinh,
            COALESCE(CONCAT(hs.ho_dem, ' ', hs.ten), vp.raw_ho_ten) as ho_ten,
            vp.ngay_vi_pham,
            chvp.ten_vi_pham,
            vp.ghi_chu
        FROM vi_pham_hoc_sinh vp
        LEFT JOIN quatrinh_hoc_tap qt ON vp.hoc_sinh_id = qt.id 
        LEFT JOIN ho_so_hoc_sinh hs ON qt.ma_hoc_sinh = hs.ma_hoc_sinh
        LEFT JOIN lop_hoc lh ON qt.lop_hoc_id = lh.id
        JOIN cau_hinh_vi_pham chvp ON vp.vi_pham_id = chvp.id
        WHERE vp.tuan_hoc_id IN ($placeholders)
        ORDER BY lh.ten_lop, vp.ngay_vi_pham ASC, ho_ten ASC
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute($tuan_ids);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function get_all_students_with_class_info(PDO $db) {
    $sql = "
        SELECT 
            qt.id as id,
            hs.id as ho_so_id,
            hs.ma_hoc_sinh, hs.ho_dem, hs.ten, hs.ngay_sinh,
            lh.ten_lop, lh.gvcn_ten
        FROM ho_so_hoc_sinh hs
        JOIN quatrinh_hoc_tap qt ON hs.ma_hoc_sinh = qt.ma_hoc_sinh AND qt.nam_hoc_id = get_current_nam_hoc_id_mysql()
        JOIN raw_lop_hoc lh ON qt.lop_hoc_id = lh.id AND lh.nam_hoc_id = qt.nam_hoc_id
        WHERE hs.trang_thai_hoc_tap = 'dang_hoc'
        ORDER BY lh.ten_lop, hs.ten ASC
    ";
    $stmt = $db->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function get_all_classes() {
    $db = get_db_connection();
    // Lấy tất cả các lớp học để hiển thị trong bộ lọc
    $stmt = $db->query("SELECT id, ten_lop, khoi FROM lop_hoc ORDER BY thu_tu");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
