<?php
// File: src/controllers/admin_exam_detail.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../lib/exam_permissions.php';

// Bảo mật
if (!isset($_SESSION['user_id']) || !can_current_user_manage_exams()) {
    header('Location: /thidua/admin');
    exit();
}

require_once __DIR__ . '/../../config/database.php';

// Lấy ID kỳ thi từ URL
$ky_thi_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$ky_thi_id) {
    // Nếu không có ID, quay về trang danh sách
    header('Location: /thidua/admin/exam-list');
    exit();
}

$db = get_db_connection();
$ky_thi_info = null;

try {
    // Truy vấn thông tin của kỳ thi
    $stmt = $db->prepare("SELECT * FROM ky_thi WHERE id = ?");
    $stmt->execute([$ky_thi_id]);
    $ky_thi_info = $stmt->fetch();
} catch (Exception $e) {
    error_log("Lỗi khi lấy thông tin kỳ thi: " . $e->getMessage());
    $error_message = "Lỗi CSDL khi tải thông tin kỳ thi.";
}

// Nếu không tìm thấy kỳ thi với ID này
if (!$ky_thi_info) {
    // Có thể set flash message lỗi
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Không tìm thấy kỳ thi với ID này.'];
    header('Location: /thidua/admin/exam-list');
    exit();
}

// Dùng cho thẻ <title> và tiêu đề trang
$page_title = 'Chi tiết: ' . htmlspecialchars($ky_thi_info['ten_ky_thi']);

// Nạp file view
require_once __DIR__ . '/../views/admin_exam_detail.php';
?>