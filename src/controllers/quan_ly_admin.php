<?php
// File: src/controllers/quan_ly_admin.php (ĐÃ NÂNG CẤP PHÂN QUYỀN)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../lib/login_logger.php';

write_login_log('ADMIN_CONTROLLER_ENTER', [
    'session_id' => session_id(),
    'has_user_id' => isset($_SESSION['user_id']),
    'user_id' => $_SESSION['user_id'] ?? null,
    'user_vai_tro' => $_SESSION['user_vai_tro'] ?? null,
    'cookie_sessid' => $_COOKIE[session_name()] ?? 'NOT_SENT'
]);

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    write_login_log('ADMIN_CONTROLLER_KICK_OUT', [
        'reason' => 'user_id not set or role not admin/user',
        'has_user_id' => isset($_SESSION['user_id']),
        'user_id' => $_SESSION['user_id'] ?? null,
        'user_vai_tro' => $_SESSION['user_vai_tro'] ?? null
    ]);
    header('Location: /thidua/tracuu');
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$db = get_db_connection();
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_vai_tro'] ?? 'user';
$user_permissions = $_SESSION['user_permissions'] ?? [];

// Lấy các cài đặt cá nhân của người dùng từ CSDL
$stmt = $db->prepare("SELECT ho_ten, hinh_nen_desktop, vi_tri_icons, app_key, app_key_machine FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_settings = $stmt->fetch();

$header_app_key = $_SERVER['HTTP_X_DESKTOP_APP_KEY'] ?? '';
$header_machine = $_SERVER['HTTP_X_DESKTOP_MACHINE_NAME'] ?? '';

// KIỂM TRA BẢO MẬT APP KEY (Nếu truy cập từ Desktop App)
if (!empty($user_settings['app_key']) && !empty($header_app_key)) {
    if ($header_app_key !== $user_settings['app_key']) {
        write_login_log('ADMIN_CONTROLLER_KICK_OUT', ['reason' => 'App Key mismatch on Desktop App']);
        session_destroy();
        header('X-App-Key-Invalid: true');
        header('Location: /thidua/dang-nhap');
        exit();
    }
    
    // Kiểm tra máy: 1 Key chỉ cho phép 1 máy duy nhất (máy kích hoạt cuối cùng)
    if (!empty($user_settings['app_key_machine']) && !empty($header_machine)) {
        if ($header_machine !== $user_settings['app_key_machine']) {
            write_login_log('ADMIN_CONTROLLER_KICK_OUT', ['reason' => 'Machine mismatch on Desktop App']);
            session_destroy();
            header('X-App-Key-Invalid: true');
            header('Location: /thidua/dang-nhap');
            exit();
        }
    }
}

// BẢO ĐẢM GIÁ TRỊ AN TOÀN CHO JS
// Hình nền: là chuỗi DataURL hoặc null -> luôn đưa qua json_encode để thành literal hợp lệ
$user_background_json = json_encode($user_settings['hinh_nen_desktop'] ?? null);

// Vị trí icon: lưu TEXT JSON trong DB -> decode/encode lại để chắc chắn hợp lệ; nếu sai hoặc rỗng thì {}
$raw_positions = $user_settings['vi_tri_icons'] ?? null;
$user_icon_positions_json = '{}';
if (!empty($raw_positions)) {
    $decoded = json_decode($raw_positions, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $user_icon_positions_json = json_encode($decoded, JSON_UNESCAPED_UNICODE);
    }
}

// Danh sách TẤT CẢ các icon ứng dụng có thể có, với key là mã quyền
$all_menu_items = [
    'quan_ly_hoc_sinh' => ['name' => 'Quản Lý Học Sinh', 'icon_filename' => 'quanlylophoc.png', 'endpoint' => '/thidua/admin/hoc-sinh'],
     'quan_ly_ky_thi' => ['name' => 'Quản Lý Kỳ Thi', 'icon_filename' => 'test.png', 'endpoint' => '/thidua/admin/exam-list'],
    'nhap_vi_pham' => ['name' => 'Nhập Vi Phạm', 'icon_filename' => 'nenep.png', 'endpoint' => '/thidua/admin/tuan-hoc'],
    'nhap_diem_thi_dua' => ['name' => 'Nhập Điểm Thi Đua', 'icon_filename' => 'sodiem.png', 'endpoint' => '/thidua/admin/tuan-hoc?action=select_thidua'],
    'cai_dat_he_thong' => ['name' => 'Cài đặt', 'icon_filename' => 'caidat.png', 'endpoint' => '/thidua/admin/cai-dat'],
    'bao_cao_thong_ke' => ['name' => 'Báo Cáo Thống Kê', 'icon_filename' => 'thongkebaocao.png', 'endpoint' => '/thidua/bao-cao'],
    'lich_sinh_nhat' => ['name' => 'Sinh Nhật', 'icon_filename' => 'sinhnhat.png', 'endpoint' => '/thidua/admin/lich-sinh-nhat'],
    'trung_tam_duyet' => ['name' => 'Duyệt CTV', 'icon_filename' => 'duyet.png', 'endpoint' => '/thidua/admin/trung-tam-duyet'],
    'quan_ly_khen_thuong' => ['name' => 'Khen Thưởng', 'icon_filename' => 'quanlykhenthuong.png', 'endpoint' => '/thidua/admin/khen-thuong'],

    'nhat_ky_he_thong' => ['name' => 'Logs Hệ Thống', 'icon_filename' => 'nhatky.png', 'endpoint' => '/thidua/admin/nhat-ky'],
    'nhat_ky_email' => ['name' => 'Nhật kỳ Email', 'icon_filename' => 'sms_sender.png', 'endpoint' => '/thidua/admin/email-logs'],
    'quan_ly_the_hoc_sinh' => ['name' => 'Thẻ Học Sinh', 'icon_filename' => 'thehocsinh.png', 'endpoint' => '/thidua/admin/the-hoc-sinh'],
    'duyet_so_nhat_ky' => ['name' => 'Nhật kỳ Điện Tử', 'icon_filename' => 'sodaubai.png', 'endpoint' => '/thidua/admin/duyet-so-nhat-ky'],
    'duyet_vang_hoc' => ['name' => 'Duyệt Vắng Học', 'icon_filename' => 'duyet.png', 'endpoint' => '/thidua/admin/duyet-vang-hoc'],
];

// Lọc menu dựa trên quyền của người dùng đang đăng nhập
$menu_items = [];
if ($user_role === 'admin' || in_array('all', $user_permissions)) {
    // Nếu là Admin, hiển thị tất cả
    $menu_items = $all_menu_items;
} else {
    // Nếu là User, chỉ hiển thị những icon mà họ có quyền
    foreach ($all_menu_items as $key => $item) {
        if (in_array($key, $user_permissions)) {
            $menu_items[$key] = $item;
        }
    }
}

require_once __DIR__ . '/../views/admin.php';