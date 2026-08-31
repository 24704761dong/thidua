<?php
// File: src/controllers/oauth_redirect_zalo_oa.php
// Luồng OAuth để Admin lấy Zalo OA Access Token (khác với user OAuth)
if (session_status() === PHP_SESSION_NONE) session_start();

// Chỉ admin mới được phép
if (!isset($_SESSION['user_id']) || $_SESSION['user_vai_tro'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập.']);
    exit;
}

require_once __DIR__ . '/../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->safeLoad();

$app_id = $_ENV['ZALO_APP_ID'] ?? '';

if (empty($app_id)) {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Chưa cấu hình ZALO_APP_ID trong file .env'];
    header('Location: /thidua/admin/cai-dat');
    exit;
}

// Callback URL riêng cho OA OAuth
$callback_url = $_ENV['ZALO_OA_CALLBACK_URL'] ?? '';
if (empty($callback_url)) {
    // Tự xây dựng từ callback URL người dùng nếu chưa cấu hình riêng
    $base = rtrim($_ENV['ZALO_CALLBACK_URL'] ?? 'https://swapping-amplifier-apostle.ngrok-free.dev/thidua/oauth-callback-zalo', '/');
    $callback_url = str_replace('oauth-callback-zalo', 'oauth-callback-zalo-oa', $base);
}

// Tạo state để chống CSRF
$state = bin2hex(random_bytes(16));
$_SESSION['zalo_oa_oauth_state'] = $state;
$_SESSION['zalo_oa_oauth_initiated_by'] = $_SESSION['user_id'];

// PKCE
$code_verifier = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
$_SESSION['zalo_oa_code_verifier'] = $code_verifier;
$code_challenge = rtrim(strtr(base64_encode(hash('sha256', $code_verifier, true)), '+/', '-_'), '=');

// Zalo OA OAuth URL (dùng endpoint /v4/oa/permission để lấy OA token)
$url = "https://oauth.zaloapp.com/v4/oa/permission"
    . "?app_id=" . urlencode($app_id)
    . "&redirect_uri=" . urlencode($callback_url)
    . "&state=" . urlencode($state)
    . "&code_challenge=" . urlencode($code_challenge)
    . "&code_challenge_method=S256";

$urlJson = json_encode($url); // Encode an toàn cho JavaScript

// Thoát khỏi iframe (nếu đang trong window manager) rồi redirect sang Zalo OAuth
// Không dùng header('Location:') vì nếu trong iframe sẽ chỉ redirect iframe, không phải full page
header('Content-Type: text/html; charset=utf-8');
echo <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đang chuyển đến Zalo...</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; background:#f1f5f9; }
        .card { text-align:center; background:#fff; border-radius:16px; padding:40px; max-width:360px; box-shadow:0 8px 32px rgba(0,0,0,.1); }
        .spinner { width:40px; height:40px; border:4px solid #e2e8f0; border-top-color:#0068ff; border-radius:50%; animation:spin .8s linear infinite; margin:0 auto 16px; }
        @keyframes spin { to { transform: rotate(360deg); } }
        p { color:#475569; font-size:.95rem; margin:0; }
    </style>
</head>
<body>
    <div class="card">
        <div class="spinner"></div>
        <p>Đang chuyển đến trang xác thực Zalo...</p>
    </div>
    <script>
        // Luôn điều hướng toàn trang (thoát khỏi iframe nếu đang bị nhúng)
        window.top.location.href = {$urlJson};
    </script>
</body>
</html>
HTML;
exit;
