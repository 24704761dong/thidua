<?php
// File: src/controllers/oauth_callback_zalo_oa.php
// Nhận authorization code từ Zalo OA OAuth → đổi lấy OA Access Token + Refresh Token → lưu vào DB
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->safeLoad();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/zalo_helpers.php';

// === Hàm hiển thị kết quả ===
function showOaResult(bool $success, string $message): void {
    $color  = $success ? '#16a34a' : '#dc2626';
    $icon   = $success ? '✅' : '❌';
    $title  = $success ? 'Kết nối Zalo OA thành công!' : 'Kết nối Zalo OA thất bại';
    $redirect = '/thidua/admin/cai-dat';
    header('Content-Type: text/html; charset=utf-8');
    echo <<<HTML
    <!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8">
    <title>{$title}</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; background: #f1f5f9; }
        .card { background: #fff; border-radius: 16px; padding: 40px; max-width: 460px; width: 90%; box-shadow: 0 8px 32px rgba(0,0,0,.1); text-align: center; }
        .icon { font-size: 3rem; margin-bottom: 16px; }
        h2 { color: {$color}; margin: 0 0 12px; font-size: 1.3rem; }
        p { color: #475569; font-size: 0.95rem; margin: 0 0 20px; line-height: 1.6; }
        a { display: inline-block; padding: 10px 28px; background: #224397; color: #fff; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.9rem; transition: background .2s; }
        a:hover { background: #1a337a; }
        small { display: block; color: #94a3b8; margin-top: 14px; font-size: 0.8rem; }
        .progress { width: 100%; height: 4px; background: #e2e8f0; border-radius: 4px; margin-top: 16px; overflow: hidden; }
        .progress-bar { height: 100%; background: #224397; border-radius: 4px; animation: shrink 3s linear forwards; }
        @keyframes shrink { from { width: 100%; } to { width: 0%; } }
    </style>
    </head><body>
    <div class="card">
        <div class="icon">{$icon}</div>
        <h2>{$title}</h2>
        <p>{$message}</p>
        <a href="{$redirect}" onclick="window.top.location.href=this.href; return false;">← Quay lại Cài đặt</a>
        <div class="progress"><div class="progress-bar"></div></div>
        <small>Tự động chuyển sau 3 giây...</small>
    </div>
    <script>
        // Luôn điều hướng ở top-level (thoát khỏi iframe nếu có)
        setTimeout(function() {
            window.top.location.href = '{$redirect}';
        }, 3000);
    </script>
    </body></html>
HTML;
    exit;
}

// === Kiểm tra quyền: phiên này phải do admin khởi tạo ===
if (empty($_SESSION['zalo_oa_oauth_initiated_by'])) {
    showOaResult(false, 'Phiên OAuth không hợp lệ. Vui lòng đăng nhập Admin và thử lại.');
}

// === Xác minh state (chống CSRF) ===
$received_state = $_GET['state'] ?? '';
$stored_state   = $_SESSION['zalo_oa_oauth_state'] ?? '';
if (empty($received_state) || $received_state !== $stored_state) {
    showOaResult(false, 'Mã xác thực (state) không khớp. Có thể bị tấn công CSRF. Vui lòng thử lại.');
}

// === Lấy authorization code ===
$code = $_GET['code'] ?? '';
if (empty($code)) {
    $err = $_GET['error_description'] ?? ($_GET['error'] ?? 'Không rõ lỗi');
    showOaResult(false, 'Zalo không cấp quyền: ' . htmlspecialchars($err));
}

$app_id       = $_ENV['ZALO_APP_ID'] ?? '';
$app_secret   = $_ENV['ZALO_APP_SECRET'] ?? '';
$callback_url = $_ENV['ZALO_OA_CALLBACK_URL'] ?? '';
if (empty($callback_url)) {
    $base = rtrim($_ENV['ZALO_CALLBACK_URL'] ?? '', '/');
    $callback_url = str_replace('oauth-callback-zalo', 'oauth-callback-zalo-oa', $base);
}
$code_verifier = $_SESSION['zalo_oa_code_verifier'] ?? '';

// Xóa session không cần nữa
unset($_SESSION['zalo_oa_oauth_state'], $_SESSION['zalo_oa_code_verifier'], $_SESSION['zalo_oa_oauth_initiated_by']);

if (empty($app_id) || empty($app_secret)) {
    showOaResult(false, 'Chưa cấu hình ZALO_APP_ID hoặc ZALO_APP_SECRET trong .env');
}

// === Đổi code lấy OA Access Token ===
$ch = curl_init('https://oauth.zaloapp.com/v4/oa/access_token');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query([
        'code'          => $code,
        'app_id'        => $app_id,
        'grant_type'    => 'authorization_code',
        'code_verifier' => $code_verifier,
    ]),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/x-www-form-urlencoded',
        'secret_key: ' . $app_secret,
    ],
    CURLOPT_TIMEOUT => 15,
]);
$response  = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_err  = curl_error($ch);
curl_close($ch);

if (function_exists('log_to_file')) {
    log_to_file("[ZALO OA OAUTH] HTTP {$http_code} - Response: " . substr($response, 0, 500));
}

if ($curl_err) {
    showOaResult(false, 'Lỗi kết nối đến Zalo: ' . htmlspecialchars($curl_err));
}

$token_data = json_decode($response, true);

if (empty($token_data['access_token'])) {
    $errMsg = $token_data['error_description'] ?? ($token_data['error_name'] ?? ($token_data['error'] ?? 'Không rõ lỗi'));
    if (function_exists('log_to_file')) {
        log_to_file("[ZALO OA OAUTH] Thất bại: " . $response);
    }
    showOaResult(false, 'Zalo trả về lỗi: ' . htmlspecialchars((string)$errMsg) . '<br><small style="color:#94a3b8">Raw: ' . htmlspecialchars(substr($response, 0, 200)) . '</small>');
}

$new_access_token  = $token_data['access_token'];
$new_refresh_token = $token_data['refresh_token'] ?? '';
$expires_in        = $token_data['expires_in'] ?? 'N/A';

// === Lưu vào CSDL ===
update_zalo_oa_tokens($new_access_token, $new_refresh_token);

// === Cũng cập nhật vào .env để đồng bộ ===
try {
    $env_path = __DIR__ . '/../../.env';
    $env_content = file_get_contents($env_path);
    if ($env_content !== false) {
        // Cập nhật access token
        $env_content = preg_replace(
            '/^ZALO_OA_ACCESS_TOKEN=.*/m',
            'ZALO_OA_ACCESS_TOKEN="' . $new_access_token . '"',
            $env_content
        );
        // Cập nhật refresh token nếu có
        if (!empty($new_refresh_token)) {
            $env_content = preg_replace(
                '/^ZALO_OA_REFRESH_TOKEN=.*/m',
                'ZALO_OA_REFRESH_TOKEN="' . $new_refresh_token . '"',
                $env_content
            );
        }
        file_put_contents($env_path, $env_content);
        if (function_exists('log_to_file')) {
            log_to_file("[ZALO OA OAUTH] Đã cập nhật .env với token mới.");
        }
    }
} catch (\Throwable $e) {
    if (function_exists('log_to_file')) {
        log_to_file("[ZALO OA OAUTH] Không cập nhật được .env: " . $e->getMessage());
    }
    // Không cần báo lỗi vì đã lưu DB rồi
}

if (function_exists('log_to_file')) {
    log_to_file("[ZALO OA OAUTH] ✅ Lấy OA Token thành công! Expires in: {$expires_in}s");
}

showOaResult(true, "Token Zalo OA đã được cập nhật thành công!<br><small>Access Token mới có hiệu lực <strong>25 giờ</strong> (~90.000 giây). Hệ thống sẽ tự động dùng Refresh Token để gia hạn mỗi khi gửi ZNS. Refresh Token có hiệu lực <strong>3 tháng</strong> và tự động được lưu mới sau mỗi lần dùng.</small>");
