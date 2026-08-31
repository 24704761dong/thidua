<?php
// File: src/controllers/cau_hinh_tra_cuu_diem_thi.php (Phiên bản chuẩn)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Bảo mật: Kiểm tra quyền admin/user
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/admin'); // Hoặc trang đăng nhập
    exit();
 }

require_once __DIR__ . '/../../config/database.php';

// Lấy ID kỳ thi từ URL
$ky_thi_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$ky_thi_id) {
    // Nếu không có ID hoặc ID không hợp lệ, quay về trang danh sách
    $_SESSION['flash_message'] = ['type' => 'warning', 'message' => 'ID Kỳ thi không hợp lệ.'];
    header('Location: /thidua/admin/exam-list');
    exit();
 }

$db = get_db_connection();
$ky_thi_info = null;
$config = [
    'phuong_thuc_tra_cuu' => '', // Lưu chuỗi đơn (vd: 'sbd')
    'truong_hien_thi' => [],      // Chỉ lưu cấu hình trường thông tin cá nhân
    'phuc_khao_xac_minh' => []   // Cấu hình xác minh phúc khảo
];
$error_message = null; // Biến báo lỗi

// Định nghĩa các tùy chọn cấu hình - PHẢI CÓ ĐẦY ĐỦ
$available_methods = [
    'sbd' => 'Số Báo Danh',
    'cccd' => 'Số CCCD',
    'moet' => 'Mã MOET',
    'ten_ngaysinh' => 'Họ Tên & Ngày Sinh'
];

// Chỉ các trường thông tin cá nhân - PHẢI CÓ ĐẦY ĐỦ
$available_fields = [
    'ho_ten' => 'Họ và Tên',
    'ngay_sinh' => 'Ngày Sinh',
    'lop' => 'Lớp',
    'sbd' => 'Số Báo Danh',
    'cccd' => 'Số CCCD',
    'ma_moet' => 'Mã MOET'
];

// Các trường xác minh phúc khảo - PHẢI CÓ ĐẦY ĐỦ
$available_verification_fields = [
    'ho_ten' => 'Họ và Tên',
    'lop' => 'Lớp',
    'ngay_sinh' => 'Ngày Sinh',
    'sbd' => 'Số Báo Danh',
    'cccd' => 'Số CCCD',
    'ma_moet' => 'Mã MOET'
];


try {
    // Lấy thông tin kỳ thi và tất cả cấu hình JSON
    $stmt = $db->prepare("
        SELECT id, ten_ky_thi, tra_cuu_cong_khai, phuong_thuc_tra_cuu, truong_hien_thi, phuc_khao_xac_minh
        FROM ky_thi WHERE id = ?
    ");
    $stmt->execute([$ky_thi_id]);
    $ky_thi_info = $stmt->fetch();

    // Kiểm tra nếu không tìm thấy kỳ thi
    if (!$ky_thi_info) {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Không tìm thấy kỳ thi với ID này.'];
        header('Location: /thidua/admin/exam-list');
        exit();
     }

    // Đọc và decode cấu hình từ CSDL
    $config['phuong_thuc_tra_cuu'] = $ky_thi_info['phuong_thuc_tra_cuu'] ?? 'sbd'; // Mặc định là SBD nếu chưa có
    $config['truong_hien_thi'] = json_decode($ky_thi_info['truong_hien_thi'] ?: '{}', true);
    $config['phuc_khao_xac_minh'] = json_decode($ky_thi_info['phuc_khao_xac_minh'] ?: '{}', true);

} catch (Exception $e) {
    error_log("Lỗi khi lấy Cấu hình Tra cứu & Phúc khảo: " . $e->getMessage());
    $error_message = "Lỗi CSDL khi tải dữ liệu cấu hình.";
 }

$page_title = 'Cấu hình Tra cứu & Phúc khảo: ' . htmlspecialchars($ky_thi_info['ten_ky_thi']);
// Gọi file View
require_once __DIR__ . '/../views/xem_cau_hinh_tra_cuu_diem_thi.php';
?>