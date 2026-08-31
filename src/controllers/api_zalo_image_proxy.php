<?php
// File: src/controllers/api_zalo_image_proxy.php
// Proxy an toàn phục vụ ảnh thẻ và banner tin tức cho Zalo Mini App (vượt qua Cloudflare Hotlink Protection)

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$type = $_GET['type'] ?? '';
$path = $_GET['path'] ?? '';
$url = $_GET['url'] ?? '';

// 1. Phục vụ ảnh từ URL tuyệt đối (như banner tin tức c3binhson.edu.vn/storage/...)
if (!empty($url)) {
    // Chỉ cho phép tải từ domain c3binhson.edu.vn hoặc r2 storage
    if (strpos($url, 'c3binhson.edu.vn') !== false || strpos($url, 'r2.cloudflarestorage.com') !== false) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // Không gửi Referer để tránh Hotlink Protection
        curl_setopt($ch, CURLOPT_REFERER, '');
        $data = curl_exec($ch);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $data) {
            header('Content-Type: ' . ($contentType ?: 'image/jpeg'));
            header('Cache-Control: public, max-age=604800, immutable');
            echo $data;
            exit();
        }
    }
}

// 2. Phục vụ ảnh thẻ hoặc logo trường từ local
if ($type === 'logo') {
    $logoPath = __DIR__ . '/../../public/assets/img/logoapp.png';
    if (file_exists($logoPath)) {
        header('Content-Type: image/png');
        header('Cache-Control: public, max-age=604800, immutable');
        readfile($logoPath);
        exit();
    }
}

// 3. Fallback: Trả về logo trường
$fallback = __DIR__ . '/../../public/assets/img/logoapp.png';
if (file_exists($fallback)) {
    header('Content-Type: image/png');
    header('Cache-Control: public, max-age=86400');
    readfile($fallback);
    exit();
}

http_response_code(404);
echo "Image not found";
