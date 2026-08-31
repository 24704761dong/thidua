<?php
// File: src/controllers/ctv_thong_tin_ca_nhan.php (ĐÃ NÂNG CẤP 2FA)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['student_id'])) {
    header('Location: /thidua/tracuu');
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$student_id = $_SESSION['student_id'];
$db = get_db_connection();

try {
    // --- NÂNG CẤP: Lấy thêm cột two_fa_enabled ---
    $stmt = $db->prepare("
        SELECT hs.*, lh.ten_lop, lh.gvcn_ten, hs.two_fa_enabled 
        FROM hoc_sinh hs
        JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id
        WHERE hs.id = ?
    ");
    $stmt->execute([$student_id]);
    $student_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student_info) {
        throw new Exception("Không tìm thấy thông tin học sinh.");
    }

    // Lấy cài đặt hệ thống xem HS có được phép sửa thông tin không
    $stmt_settings = $db->query("SELECT setting_key, setting_value FROM he_thong_cai_dat WHERE setting_key LIKE 'student_can_edit_%'");
    $settings_raw = $stmt_settings->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $settings = [
        'can_edit_sdt' => ($settings_raw['student_can_edit_sdt'] ?? 'off') === 'on',
        'can_edit_email' => ($settings_raw['student_can_edit_email'] ?? 'on') === 'on',
        'can_edit_chuc_vu' => ($settings_raw['student_can_edit_chuc_vu'] ?? 'off') === 'on',
    ];
    
    // --- BIẾN MỚI CHO VIEW ---
    $is_2fa_enabled = !empty($student_info['two_fa_enabled']) && $student_info['two_fa_enabled'] == 1;


} catch (Exception $e) {
    die("Lỗi CSDL: " . $e->getMessage());
}

$page_title = 'Thông Tin Cá Nhân';
require_once __DIR__ . '/../views/ctv_thong_tin_ca_nhan.php';