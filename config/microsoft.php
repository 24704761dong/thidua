<?php
// File: config/microsoft.php

// ============================================================
// CẤU HÌNH MICROSOFT 365 - CẤP MAIL HỌC SINH
// ============================================================
// Để lấy các thông số này, Admin thực hiện các bước sau:
// 1. Truy cập Azure portal (https://portal.azure.com/) bằng tài khoản Admin (Microsoft 365)
// 2. Vào mục "Microsoft Entra ID" (hoặc Azure Active Directory).
// 3. Chọn "App registrations" -> "New registration".
// 4. Đặt tên (vd: "Thidua App"). Redirect URI để trống -> Bấm "Register".
// 5. Sau khi tạo xong, copy "Application (client) ID" và "Directory (tenant) ID" dán vào file .env.
// 6. Vào "Certificates & secrets" -> "New client secret" -> Thêm. Copy phần "Value" dán vào .env.
// 7. Quan trọng: Vào "API permissions" -> "Add a permission" -> "Microsoft Graph" -> "Application permissions".
// 8. Chọn các quyền: `User.ReadWrite.All`, `Directory.ReadWrite.All`. 
// 9. Bấm "Grant admin consent for..." và xác nhận (cột Status hiện dấu check xanh là OK).

// Đọc từ biến môi trường (.env) - Biến riêng cho chức năng cấp mail
define('MS_TENANT_ID', $_ENV['MS_MAIL_TENANT_ID'] ?? '');
define('MS_CLIENT_ID', $_ENV['MS_MAIL_CLIENT_ID'] ?? '');
define('MS_CLIENT_SECRET', $_ENV['MS_MAIL_CLIENT_SECRET'] ?? '');
define('MS_DOMAIN', $_ENV['MS_MAIL_DOMAIN'] ?? 'c3binhson.edu.vn');

function getMsGraphToken() {
    $url = "https://login.microsoftonline.com/" . MS_TENANT_ID . "/oauth2/v2.0/token";
    $data = http_build_query([
        'client_id' => MS_CLIENT_ID,
        'client_secret' => MS_CLIENT_SECRET,
        'scope' => 'https://graph.microsoft.com/.default',
        'grant_type' => 'client_credentials'
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $json = json_decode($response, true);
    return $json['access_token'] ?? null;
}

/**
 * Lấy Token sử dụng MS_CLIENT_ID của App chính (Dùng cho chức năng backup OneDrive minh chứng, ảnh thẻ, v.v)
 */
function getMsGraphMainToken() {
    $tenantId = $_ENV['MS_TENANT_ID'] ?? '';
    $clientId = $_ENV['MS_CLIENT_ID'] ?? '';
    $clientSecret = $_ENV['MS_CLIENT_SECRET'] ?? '';
    
    if (empty($tenantId) || empty($clientId) || empty($clientSecret)) return null;

    $url = "https://login.microsoftonline.com/" . $tenantId . "/oauth2/v2.0/token";
    $data = http_build_query([
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'scope' => 'https://graph.microsoft.com/.default',
        'grant_type' => 'client_credentials'
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    $json = json_decode($response, true);
    return $json['access_token'] ?? null;
}

function removeAccentsAndLowercase($str) {
    $str = mb_strtolower($str, 'UTF-8');
    $unicode = [
        'a'=>'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
        'd'=>'đ',
        'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
        'i'=>'í|ì|ỉ|ĩ|ị',
        'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
        'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
        'y'=>'ý|ỳ|ỷ|ỹ|ỵ',
    ];
    foreach($unicode as $nonUnicode=>$uni){
        $str = preg_replace("/($uni)/i", $nonUnicode, $str);
    }
    $str = str_replace(' ', '', $str);
    return preg_replace('/[^a-z0-9]/', '', $str);
}

function getFirstName($fullName) {
    $parts = explode(' ', trim($fullName));
    $lastName = array_pop($parts);
    return removeAccentsAndLowercase($lastName);
}

function generateRandomPassword() {
    $lower = "abcdefghjkmnpqrstuvwxyz"; // removed l
    $upper = "ABCDEFGHJKMNPQRSTUVWXYZ"; // removed I, O
    $numbers = "23456789"; // removed 1, 0
    $symbols = "!@#$%^&*"; // removed () for easier double-click selection
    
    // Ensure at least one of each to meet MS complexity rules
    $password = $lower[rand(0, strlen($lower) - 1)] . 
                $upper[rand(0, strlen($upper) - 1)] . 
                $numbers[rand(0, strlen($numbers) - 1)] . 
                $symbols[rand(0, strlen($symbols) - 1)];
                
    $all = $lower . $upper . $numbers . $symbols;
    for ($i = 0; $i < 4; $i++) {
        $password .= $all[rand(0, strlen($all) - 1)];
    }
    
    return str_shuffle($password);
}

/**
 * Upload ảnh thẻ lên Microsoft 365
 * Tách riêng thành hàm để sau này dễ dàng nâng cấp (ví dụ: kéo ảnh từ R2, OneDrive).
 */
function uploadMsUserAvatar($msUserId, $token, $avatarPath) {
    if (empty($avatarPath)) return false;

    // Tạm thời xử lý ảnh local và link public. 
    // Sau này nếu $avatarPath là key của R2/OneDrive, bạn chỉ cần dùng StorageService::download($avatarPath) tại đây.
    $realPath = $avatarPath;
    if (!preg_match('/^https?:\/\//', $realPath)) {
        // Đường dẫn tương đối
        $baseDir = dirname(__DIR__); // e:\VPS\htdocs\thidua
        $realPath = $baseDir . '/' . ltrim($realPath, '/');
    }

    $imgData = @file_get_contents($realPath);
    if (!$imgData) return false;

    $mime = 'image/jpeg';
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_buffer($finfo, $imgData);
        finfo_close($finfo);
    }
    if (strpos($mime, 'image/') !== 0) $mime = 'image/jpeg';
    
    $chPhoto = curl_init("https://graph.microsoft.com/v1.0/users/$msUserId/photo/\$value");
    curl_setopt($chPhoto, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: ' . $mime
    ]);
    curl_setopt($chPhoto, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($chPhoto, CURLOPT_POSTFIELDS, $imgData);
    curl_setopt($chPhoto, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($chPhoto);
    $httpcode = curl_getinfo($chPhoto, CURLINFO_HTTP_CODE);
    curl_close($chPhoto);

    return ($httpcode >= 200 && $httpcode < 300);
}

/**
 * Upload file lên OneDrive (dành cho file nhỏ dưới 4MB, ví dụ: ảnh thẻ)
 */
function uploadFileToOneDrive($token, $userEmail, $remotePath, $localPath) {
    if (!file_exists($localPath)) return false;
    
    $encodedPath = rawurlencode($remotePath);
    $encodedPath = str_replace('%2F', '/', $encodedPath);
    $baseUrl = "https://graph.microsoft.com/v1.0/users/" . rawurlencode($userEmail) . "/drive/root:/{$encodedPath}:/content";
    
    $imgData = @file_get_contents($localPath);
    if (!$imgData) return false;
    
    $ch = curl_init($baseUrl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/octet-stream'
    ]);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $imgData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $res = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpcode >= 200 && $httpcode < 300) {
        $data = json_decode($res, true);
        return $data['id'] ?? true;
    }
    return false;
}
