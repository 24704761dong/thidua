<?php
// File: src/controllers/api_toggle_setting.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

// Bảo mật: Chỉ Admin/User mới có quyền thay đổi cài đặt
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập.']);
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../lib/nam_hoc.php';

$data  = json_decode(file_get_contents('php://input'), true);
$key   = $data['key']   ?? null;
$value = $data['value'] ?? null;

if (empty($key)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Thiếu khóa cài đặt (key).']);
    exit();
}

// Danh sách các khóa CÁCH LY THEO NĂM HỌC (Tự Động Hóa & Quyền Hạn Học Sinh)
$per_year_keys = [
    // Tự Động Hóa
    'auto_approve_violations',
    'auto_approve_duty_roster',
    'auto_grant_permissions_on_duty_approve',
    'auto_approve_attendance',

    // Quyền Hạn Học Sinh
    'student_can_edit_sdt',
    'student_can_edit_email',
    'student_can_edit_chuc_vu',
];

$is_per_year = in_array($key, $per_year_keys);
$nam_hoc_id = $is_per_year ? (current_nam_hoc_id() ?: 1) : 0;

// Validation theo kiểu dữ liệu của từng key
$is_valid = false;

switch ($key) {
    // Các setting on/off
    case 'allow_all_students_login':
    case 'allow_student_lookup':
    case 'allow_teacher_lookup':
    case 'student_can_edit_sdt':
    case 'student_can_edit_email':
    case 'student_can_edit_chuc_vu':
    case 'auto_approve_violations':
    case 'auto_approve_attendance':
    case 'auto_approve_duty_roster':
        if ($value === 'on' || $value === 'off') {
            $is_valid = true;
        }
        break;

    // Các setting số nguyên
    case 'auto_logout_duration':
    case 'public_lookup_nam_hoc_id':
        if (is_numeric($value) && $value >= 0) {
            $is_valid = true;
        }
        break;

    // Chuỗi tự do
    case 'week_lock_password':
        if (is_string($value) && $value !== '') {
            $is_valid = true;
        }
        break;

    case 'dia_chi_options':
        if (is_string($value)) {
            $is_valid = true;
        }
        break;

    // Mảng JSON
    case 'auto_grant_permissions_on_duty_approve':
        if (is_array($value)) {
            $value    = json_encode($value);
            $is_valid = true;
        }
        break;
}

if (!$is_valid) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => "Dữ liệu không hợp lệ cho cài đặt '{$key}'."]);
    exit();
}

try {
    $db = get_db_connection();
    
    if (!$is_per_year) {
        // Nếu là cài đặt TOÀN HỆ THỐNG (nam_hoc_id = 0), xóa các bản ghi rác có nam_hoc_id != 0
        $stmt_clean = $db->prepare("DELETE FROM he_thong_cai_dat WHERE setting_key = ? AND (nam_hoc_id != 0 OR nam_hoc_id IS NULL)");
        $stmt_clean->execute([$key]);
    }

    $stmt = $db->prepare(
        "INSERT INTO he_thong_cai_dat (setting_key, setting_value, nam_hoc_id)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
    );
    $stmt->execute([$key, $value, $nam_hoc_id]);

    echo json_encode(['success' => true, 'message' => 'Cài đặt đã được cập nhật thành công.']);

} catch (Exception $e) {
    http_response_code(500);
    error_log("API Setting Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ khi lưu cài đặt.']);
}