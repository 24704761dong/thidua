<?php
// File: config/oauth_providers.php

// Nạp file khởi động chính để đọc file .env
require_once __DIR__ . '/bootstrap.php'; 

// Nạp thư viện đã cài bằng Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Định nghĩa URL chuyển hướng (PHẢI GIỐNG HỆT bạn đã khai báo trên Google Cloud Console)
// --- BẮT ĐẦU NÂNG CẤP: ÉP HTTP CHO LOCALHOST ---
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$is_local = ($host === 'localhost' || $host === '127.0.0.1');
$protocol = (!$is_local && isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';

$google_redirect_uri = $protocol . '://' . $host . '/thidua/public/index.php?route=/oauth-callback-google';
// --- KẾT THÚC NÂNG CẤP ---
                       
// Tương lai nếu làm Facebook, bạn sẽ thêm $facebook_redirect_uri ở đây

/**
 * Khởi tạo và trả về Google Provider.
 * @return League\OAuth2\Client\Provider\Google
 */
function get_google_provider() {
    return new League\OAuth2\Client\Provider\Google([
        'clientId'     => $_ENV['GOOGLE_CLIENT_ID'] ?? '',
        'clientSecret' => $_ENV['GOOGLE_CLIENT_SECRET'] ?? '',
        'redirectUri'  => $GLOBALS['google_redirect_uri'] ?? '', // Dùng biến toàn cục
        'accessType'   => 'offline',
        'prompt'       => 'consent' // Yêu cầu người dùng đồng ý (lấy refresh token)
    ]);
}

/**
 * (Sẽ dùng khi bạn làm Facebook)
 * Khởi tạo và trả về Facebook Provider.
 * @return League\OAuth2\Client\Provider\Facebook
 */
/*
function get_facebook_provider() {
    return new League\OAuth2\Client\Provider\Facebook([
        'clientId'     => $_ENV['FACEBOOK_CLIENT_ID'] ?? '',
        'clientSecret' => $_ENV['FACEBOOK_CLIENT_SECRET'] ?? '',
        'redirectUri'  => $GLOBALS['facebook_redirect_uri'] ?? '',
        'graphApiVersion' => 'v19.0'
    ]);
}
*/