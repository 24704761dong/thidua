<?php
// File: src/lib/tuan_hoc_db.php
// Quản lý các hàm CSDL cho bảng tuan_hoc

/**
 * Lấy danh sách các tuần đã được bật công khai.
 *
 * @param PDO $db Đối tượng kết nối CSDL
 * @return array Mảng chứa các tuần công khai
 */
function get_public_weeks(PDO $db) {
    $stmt = $db->prepare("SELECT id, ten_tuan FROM tuan_hoc WHERE is_public = 1 ORDER BY ngay_bat_dau DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Lấy tất cả các tuần cùng trạng thái công khai.
 *
 * @param PDO $db Đối tượng kết nối CSDL
 * @return array Mảng chứa tất cả các tuần
 */
function get_all_weeks_with_public_status(PDO $db) {
    $stmt = $db->prepare("SELECT id, ten_tuan, is_public FROM tuan_hoc ORDER BY ngay_bat_dau DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Cập nhật trạng thái công khai cho một tuần.
 *
 * @param PDO $db Đối tượng kết nối CSDL
 * @param int $tuan_id ID của tuần cần cập nhật
 * @param int $status Trạng thái mới (1 là công khai, 0 là không)
 * @return bool True nếu thành công, False nếu thất bại
 */
function update_week_public_status(PDO $db, int $tuan_id, int $status) {
    $stmt = $db->prepare("UPDATE tuan_hoc SET is_public = ? WHERE id = ?");
    return $stmt->execute([$status, $tuan_id]);
}

?>