<?php
// File: src/controllers/admin_cai_dat.php (Đã nâng cấp)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../lib/helpers.php'; // Sử dụng helpers để lấy get_all_settings
require_once __DIR__ . '/../lib/nam_hoc.php';

$db = get_db_connection();

// Lấy năm học đang được chọn trên toàn hệ thống (từ footer)
$nam_hoc_id = current_nam_hoc_id();

// Lấy danh sách năm học để hiển thị Dropdown năm học tra cứu công khai
$stmt_nam = $db->query("SELECT id, ten_nam_hoc, is_mac_dinh FROM nam_hoc ORDER BY id DESC");
$danh_sach_nam_hoc = $stmt_nam->fetchAll(PDO::FETCH_ASSOC);

try {
    // Sử dụng helper đã nâng cấp để lấy settings (ưu tiên năm học hiện tại)
    $settings_raw = get_all_settings($db, $nam_hoc_id);

    // Gán giá trị mặc định cho từng cài đặt nếu chúng chưa tồn tại trong CSDL
    $settings['allow_all_students_login'] = $settings_raw['allow_all_students_login'] ?? 'off';
    $settings['allow_student_lookup'] = $settings_raw['allow_student_lookup'] ?? 'on';
    $settings['allow_teacher_lookup'] = $settings_raw['allow_teacher_lookup'] ?? 'on';
    $settings['student_can_edit_sdt'] = $settings_raw['student_can_edit_sdt'] ?? 'off'; // Mới
    $settings['student_can_edit_email'] = $settings_raw['student_can_edit_email'] ?? 'on';  // Mới
    $settings['student_can_edit_chuc_vu'] = $settings_raw['student_can_edit_chuc_vu'] ?? 'off'; // THÊM DÒNG NÀY
    $settings['diemdanh_violation_p'] = $settings_raw['diemdanh_violation_p'] ?? null;
    $settings['diemdanh_violation_kp'] = $settings_raw['diemdanh_violation_kp'] ?? null;
    $settings['diemdanh_violation_bt'] = $settings_raw['diemdanh_violation_bt'] ?? null;
    $stmt_vp = $db->query("SELECT id, ten_vi_pham FROM cau_hinh_vi_pham ORDER BY ten_vi_pham");
    $danh_sach_vi_pham = $stmt_vp->fetchAll();
$settings['auto_approve_violations'] = $settings_raw['auto_approve_violations'] ?? 'off';
    $settings['auto_approve_attendance'] = $settings_raw['auto_approve_attendance'] ?? 'off';
    $settings['auto_approve_duty_roster'] = $settings_raw['auto_approve_duty_roster'] ?? 'off';

    $settings['week_lock_password'] = $settings_raw['week_lock_password'] ?? '1'; // Mật khẩu mặc định là '1'
    $settings['auto_grant_permissions_on_duty_approve'] = json_decode($settings_raw['auto_grant_permissions_on_duty_approve'] ?? '[]', true);
    
    // Thêm cài đặt năm học tra cứu công khai (lấy từ cài đặt toàn hệ thống)
    $settings['public_lookup_nam_hoc_id'] = $settings_raw['public_lookup_nam_hoc_id'] ?? '';
    $settings['dia_chi_options'] = $settings_raw['dia_chi_options'] ?? '';
} catch (Exception $e) {
    // Nếu có lỗi, mặc định là tắt để đảm bảo an toàn
    $settings['allow_all_students_login'] = 'off';
    $settings['allow_student_lookup'] = 'off';
    $settings['allow_teacher_lookup'] = 'off';
    $settings['student_can_edit_sdt'] = 'off';
    $settings['student_can_edit_email'] = 'off';
    $settings['student_can_edit_chuc_vu'] = 'off'; 
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Lỗi không thể đọc cài đặt hệ thống.'];
}

require_once __DIR__ . '/../views/admin_cai_dat.php';