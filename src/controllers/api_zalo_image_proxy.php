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

// 1. Phục vụ ảnh thẻ từ file cục bộ (nhanh nhất và không bao giờ bị Cloudflare chặn)
if (!empty($path) || (!empty($url) && strpos($url, '/public/assets/') !== false)) {
    $targetPath = !empty($path) ? $path : parse_url($url, PHP_URL_PATH);
    // Loại bỏ prefix /thidua/
    $relPath = preg_replace('#^/thidua/#', '', $targetPath);
    $fullPath = realpath(__DIR__ . '/../../' . ltrim($relPath, '/'));

    if ($fullPath && file_exists($fullPath) && strpos($fullPath, realpath(__DIR__ . '/../../public/assets/')) === 0) {
        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp'
        ];
        header('Content-Type: ' . ($mimeTypes[$ext] ?? 'image/jpeg'));
        header('Cache-Control: public, max-age=604800, immutable');
        readfile($fullPath);
        exit();
    }
}

// 2. Phục vụ ảnh từ URL tuyệt đối (như banner tin tức c3binhson.edu.vn/storage/...)
if (!empty($url)) {
    if (strpos($url, 'c3binhson.edu.vn') !== false || strpos($url, 'r2.cloudflarestorage.com') !== false) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
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

// 3. Phục vụ logo trường
if ($type === 'logo') {
    $logoPath = __DIR__ . '/../../public/assets/img/logo.png';
    if (file_exists($logoPath)) {
        header('Content-Type: image/png');
        header('Cache-Control: public, max-age=604800, immutable');
        readfile($logoPath);
        exit();
    }
}

// 4. Fallback: Trả về logo trường
$fallback = __DIR__ . '/../../public/assets/img/logo.png';
if (file_exists($fallback)) {
    header('Content-Type: image/png');
    header('Cache-Control: public, max-age=86400');
    readfile($fallback);
    exit();
}

http_response_code(404);
echo "Image not found";
