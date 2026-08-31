<?php
// File: src/controllers/oauth_callback_zalo.php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->safeLoad();

require_once __DIR__ . '/../../config/database.php';
if (!defined('SKIP_LOGIN_PROCESS')) {
    define('SKIP_LOGIN_PROCESS', true);
}
require_once __DIR__ . '/dang_nhap_xu_ly.php';

function handleOAuthError($message, $redirectUrl = '/thidua/tracuu?show_login=1') {
    if (isset($_SESSION['user_id']) || isset($_SESSION['student_id'])) {
        header('Content-Type: text/html; charset=utf-8');
        $msgJson = json_encode($message);
        echo "<script>
            (function() {
                var errMsg = {$msgJson};
                try {
                    if (window.opener && !window.opener.closed) {
                        window.opener.postMessage({ type: 'ZALO_LINK_ERROR', message: errMsg }, '*');
                        setTimeout(function() { window.close(); }, 300);
                        return;
                    }
                } catch(e) {}
                // Fallback nếu không có opener
                alert(errMsg);
                window.location.href = '/thidua/quan-ly-tai-khoan-ca-nhan';
            })();
        </script>";
        exit();
    } else {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => $message];
        header('Location: ' . $redirectUrl);
        exit();
    }
}

$app_id = $_ENV['ZALO_APP_ID'] ?? '';
$app_secret = $_ENV['ZALO_APP_SECRET'] ?? '';
$code = $_GET['code'] ?? null;

if (!$code) {
    handleOAuthError('Lỗi: Không nhận được mã xác thực từ Zalo.');
}

$code_verifier = $_SESSION['zalo_code_verifier'] ?? '';

// 1. Lấy Access Token
$token_url = "https://oauth.zaloapp.com/v4/access_token";
$data = [
    'code' => $code,
    'app_id' => $app_id,
    'grant_type' => 'authorization_code',
    'code_verifier' => $code_verifier
];

$ch = curl_init($token_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "secret_key: " . trim($app_secret),
    "Content-Type: application/x-www-form-urlencoded"
]);
$response = curl_exec($ch);
curl_close($ch);

$token_data = json_decode($response, true);
if (empty($token_data['access_token'])) {
    handleOAuthError('Lỗi kết nối Zalo: Không thể lấy token. Vui lòng kiểm tra lại cấu hình ZALO_APP_ID và ZALO_APP_SECRET.');
}
$access_token = $token_data['access_token'];

// 2. Lấy thông tin người dùng
$appsecret_proof = hash_hmac('sha256', $access_token, trim($app_secret));
$profile_url = "https://graph.zalo.me/v2.0/me?fields=id,name,picture,phone&appsecret_proof=" . $appsecret_proof;
$ch2 = curl_init($profile_url);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_HTTPHEADER, [
    "access_token: " . $access_token,
    "appsecret_proof: " . $appsecret_proof
]);
$profile_res = curl_exec($ch2);
curl_close($ch2);

$profile = json_decode($profile_res, true);

if (!isset($profile['id'])) {
    $errorMsg = $profile['message'] ?? ($profile['error_name'] ?? ($profile['error'] ?? 'Không rõ lỗi API Zalo.'));
    handleOAuthError('Lỗi lấy thông tin Profile Zalo: ' . $errorMsg . ' (' . json_encode($profile) . ')');
}

$zalo_id = $profile['id'];
$zalo_name = $profile['name'] ?? null;
$phone = $profile['phone'] ?? null;
if ($phone) {
    if (strpos($phone, '84') === 0) {
        $phone = '0' . substr($phone, 2);
    }
}

$db = get_db_connection();

// Trường hợp 1: Người dùng ĐANG ĐĂNG NHẬP và muốn LIÊN KẾT Zalo
if (isset($_SESSION['user_id'])) {
    // Kiểm tra xem zalo_id này đã được liên kết với user khác chưa
    $stmt_check = $db->prepare("SELECT id FROM users WHERE zalo_id = ? AND id != ?");
    $stmt_check->execute([$zalo_id, $_SESSION['user_id']]);
    if ($stmt_check->fetchColumn()) {
        handleOAuthError('Lỗi: Tài khoản Zalo này đã được liên kết với một tài khoản khác trên hệ thống.');
    }

    $stmt = $db->prepare("UPDATE users SET zalo_id = ?, zalo_name = ? WHERE id = ?");
    $stmt->execute([$zalo_id, $zalo_name, $_SESSION['user_id']]);
    header('Content-Type: text/html; charset=utf-8');
    echo "<script>
        (function() {
            var msg = { type: 'ZALO_LINK_SUCCESS', message: 'Liên kết tài khoản Zalo thành công!' };
            // Thử postMessage trước (an toàn với cross-origin)
            try {
                if (window.opener && !window.opener.closed) {
                    window.opener.postMessage(msg, '*');
                    setTimeout(function() { window.close(); }, 300);
                    return;
                }
            } catch(e) {}
            // Fallback: nếu không có opener, redirect về trang tài khoản
            window.location.href = '/thidua/quan-ly-tai-khoan-ca-nhan?zalo_linked=1';
        })();
    </script>";
    exit;
}

// Trường hợp 2: Đăng nhập bằng Zalo
$user = null;

// Ưu tiên tìm theo zalo_id trước
$stmt = $db->prepare("SELECT * FROM users WHERE zalo_id = ?");
$stmt->execute([$zalo_id]);
$user = $stmt->fetch();

// Nếu không tìm thấy bằng zalo_id, thử tìm bằng sdt (nếu Zalo API trả về sdt)
if (!$user && $phone) {
    $stmt = $db->prepare("SELECT * FROM users WHERE sdt = ?");
    $stmt->execute([$phone]);
    $user = $stmt->fetch();
    
    // Nếu tìm thấy bằng sdt, tự động cập nhật zalo_id cho lần sau
    if ($user) {
        $stmt_update = $db->prepare("UPDATE users SET zalo_id = ?, zalo_name = ? WHERE id = ?");
        $stmt_update->execute([$zalo_id, $zalo_name, $user['id']]);
    }
}

if ($user) {
    // Đăng nhập thành công!
    if (!empty($user['two_fa_enabled']) && (int) $user['two_fa_enabled'] === 1) {
        $_SESSION['2fa_pending_user_id'] = $user['id'];
        $_SESSION['2fa_pending_user_type'] = 'users';
        header('Location: /thidua/tracuu?show_login=1&trigger_2fa=1');
        exit();
    }
    handleLoginSuccess($db, $user, false);
    exit;
}

// Nếu không tìm thấy
handleOAuthError('Tài khoản không tồn tại trên hệ thống. Ứng dụng Zalo của bạn không trả về Số điện thoại để hệ thống có thể tự động nhận diện. Vui lòng đăng nhập bằng mật khẩu, sau đó bấm nút Liên kết Zalo ở trong tài khoản của bạn.');
