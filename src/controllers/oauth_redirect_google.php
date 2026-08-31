<?php
// File: src/controllers/oauth_redirect_google.php
if (session_status() === PHP_SESSION_NONE) session_start();

/**
 * Chỉ người đã đăng nhập hoặc đang ở trang đăng nhập
 * mới được khởi tạo quy trình đăng nhập / liên kết Google.
 * Nếu không, quay lại trang tra cứu.
 */
if (!isset($_SESSION['student_id']) && !isset($_SESSION['user_id']) && !isset($_GET['mode'])) {
    header('Location: /thidua/tracuu');
    exit();
}

require_once __DIR__ . '/../../config/oauth_providers.php';

// === Tạo provider Google ===
$provider = get_google_provider();

if (isset($_GET['pwa']) && $_GET['pwa'] === 'admin') {
    $_SESSION['post_login_redirect'] = '/thidua/public/admin-app/index.html';
}

// === Tham số tùy chọn (tự động đăng nhập, không ép xác nhận lại) ===
// Giải thích:
// - 'prompt=select_account'  : chỉ hiển thị danh sách tài khoản Google, bỏ qua bước xác nhận quyền.
// - 'include_granted_scopes' : giữ lại quyền đã cấp cho domain (giúp auto-login sau lần đầu).
// - 'access_type=online'     : không yêu cầu refresh_token, tăng tốc redirect.
// - 'scope'                  : quyền tối thiểu cần thiết để lấy email và tên người dùng.
// - 'state'                  : tránh CSRF, tự sinh nếu cần.
//
// Bạn có thể thêm 'login_hint' nếu muốn Google gợi ý tài khoản cụ thể.
$options = [
    'scope' => ['openid', 'email', 'profile'],
    'prompt' => 'select_account',
    'include_granted_scopes' => 'true',
    'access_type' => 'online'
];

// === Nếu đang ở chế độ “đăng nhập từ trang đăng nhập chính” ===
// (ví dụ: /thidua/dang-nhap?method=google)
if (isset($_GET['mode']) && $_GET['mode'] === 'login') {
    $options['state'] = 'login_flow';
}

// === Nếu đã biết email người dùng (đã login tạm trước đó) ===
if (!empty($_SESSION['verified_email'])) {
    $options['login_hint'] = $_SESSION['verified_email'];
}

// === Tạo URL ủy quyền của Google ===
try {
    $authUrl = $provider->getAuthorizationUrl($options);
    $_SESSION['oauth2state'] = $provider->getState(); // lưu để kiểm tra sau này

    // === Chuyển hướng người dùng đến trang đăng nhập Google ===
    header('Location: ' . $authUrl);
    exit();

} catch (Exception $e) {
    // === Nếu có lỗi khi tạo URL OAuth ===
    error_log('Lỗi tạo URL Google OAuth: ' . $e->getMessage());
    $_SESSION['flash_message'] = [
        'type' => 'danger',
        'message' => 'Không thể khởi tạo đăng nhập Google. Vui lòng thử lại sau.'
    ];
    header('Location: /thidua/tracuu');
    exit();
}
