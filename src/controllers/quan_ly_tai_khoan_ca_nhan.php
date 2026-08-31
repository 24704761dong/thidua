<?php
// File: src/controllers/quan_ly_tai_khoan_ca_nhan.php (ĐÃ SỬA LỖI)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: /thidua/tracuu');
    exit();
}

// Chỉ cần nạp CSDL chính vì mọi dữ liệu đều ở đây
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/lib/user_agent_parser.php';

// Kết nối đến CSDL chính (app_td.db)
$db = get_db_connection(); 
$user_id = $_SESSION['user_id'];

try {
    // 1. Lấy thông tin chi tiết của user đang đăng nhập từ CSDL chính
    // === SỬA LỖI QUAN TRỌNG: Thêm 'two_fa_enabled' vào câu SELECT ===
    $stmt_user = $db->prepare("
        SELECT id, ten_dang_nhap, ho_ten, email, vai_tro, 
               nhan_canh_bao_dang_nhap, nhan_canh_bao_zalo, auto_logout_enabled, two_fa_enabled, zalo_id, zalo_name, google_id 
        FROM users 
        WHERE id = ?
    ");
    $stmt_user->execute([$user_id]);
    $user_info = $stmt_user->fetch();

    if (!$user_info) {
        // Nếu không tìm thấy user, có thể session cũ, cần đăng xuất
        header('Location: /thidua/dang-xuat');
        exit();
    }

    // 2. Lấy cài đặt thời gian chờ chung của hệ thống từ CSDL chính
    $stmt_settings = $db->query("SELECT setting_value FROM he_thong_cai_dat WHERE setting_key = 'auto_logout_duration'");
    $auto_logout_duration = (int)($stmt_settings->fetchColumn() ?: 1800); // Mặc định 30 phút

    // 3. Lấy toàn bộ lịch sử đăng nhập của user này từ CSDL chính
    $stmt_logs = $db->prepare("SELECT * FROM lich_su_dang_nhap_admin WHERE user_id = ? ORDER BY id DESC");
    $stmt_logs->execute([$user_id]);
    $login_history = $stmt_logs->fetchAll();

    // 4. Lấy các phiên đang hoạt động từ CSDL chính (đã lọc trùng lặp session_id)
    $five_minutes_ago = time() - (5 * 60); // Lấy các phiên hoạt động trong 5 phút gần đây
    $stmt_active = $db->prepare("SELECT * FROM phien_truy_cap WHERE user_id = ? AND user_type IN ('admin', 'user') AND last_activity > ? ORDER BY last_activity DESC");
    $stmt_active->execute([$user_id, $five_minutes_ago]);
    $raw_sessions = $stmt_active->fetchAll();
    $active_sessions = [];
    $seen_sessions = [];
    foreach ($raw_sessions as $sess) {
        if (!isset($seen_sessions[$sess['session_id']])) {
            $seen_sessions[$sess['session_id']] = true;
            $active_sessions[] = $sess;
        }
    }
    
    // === BIẾN MỚI CHO VIEW (Bây giờ đã chạy đúng) ===
    $is_2fa_enabled = !empty($user_info['two_fa_enabled']) && $user_info['two_fa_enabled'] == 1;

} catch (Exception $e) {
    // Nếu có bất kỳ lỗi nào xảy ra với CSDL, báo lỗi nghiêm trọng
    die("Lỗi nghiêm trọng: Không thể truy vấn dữ liệu tài khoản. " . $e->getMessage());
}

// Gọi file view để hiển thị
require_once __DIR__ . '/../views/quan_ly_tai_khoan_ca_nhan.php';