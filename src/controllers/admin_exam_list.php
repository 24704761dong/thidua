<?php
// File: src/controllers/admin_exam_list.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../lib/exam_permissions.php';

if (!isset($_SESSION['user_id']) || !can_current_user_manage_exams()) {
    // Chuyển hướng về trang dashboard hoặc đăng nhập
    header('Location: /thidua/admin'); 
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$db = get_db_connection();
$nam_hoc_id = $_SESSION['nam_hoc_id'] ?? $_SESSION['current_nam_hoc_id'] ?? null;
if (!$nam_hoc_id) {
    try {
        $stmt_nh = $db->query("SELECT id FROM nam_hoc WHERE is_mac_dinh = 1 LIMIT 1");
        $nam_hoc = $stmt_nh->fetch();
        if ($nam_hoc) {
            $nam_hoc_id = (int)$nam_hoc['id'];
        }
    } catch (Exception $e) {}
}

$ds_ky_thi = [];
try {
    // Lấy tất cả kỳ thi của năm học hiện tại kèm số lượng thí sinh tham gia
    $sql = "
        SELECT 
            kt.*,
            COUNT(kths.id) as so_luong_thi_sinh
        FROM ky_thi kt
        LEFT JOIN ky_thi_hoc_sinh kths ON kt.id = kths.ky_thi_id
        WHERE kt.nam_hoc_id = ?
        GROUP BY kt.id
        ORDER BY kt.ngay_bat_dau DESC, kt.id DESC
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute([$nam_hoc_id]);
    $ds_ky_thi = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Lỗi khi lấy danh sách kỳ thi: " . $e->getMessage());
    $error_message = "Không thể tải danh sách kỳ thi. Vui lòng thử lại.";
}

// Biến này sẽ được dùng trong file view
$page_title = 'Quản lý Kỳ thi'; 

// Nạp file view để hiển thị
require_once __DIR__ . '/../views/admin_exam_list.php';