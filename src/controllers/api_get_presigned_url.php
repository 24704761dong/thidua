<?php
// File: src/controllers/api_get_presigned_url.php
// Cho phép tải / xem ảnh thẻ và minh chứng từ OneDrive/R2 thông qua opaque key

// Không cần session để tránh deadlock khi curl nội bộ

// Nạp các file cần thiết
require_once __DIR__ . '/../../config/bootstrap.php'; 
require_once __DIR__ . '/../lib/StorageService.php'; // Nạp dịch vụ mây

$key = $_GET['key'] ?? null;
$driver = $_GET['driver'] ?? 'cloud'; // cloud = R2, onedrive = OneDrive

if (empty($key)) {
    http_response_code(400);
    die('Lỗi: Thiếu "key" của file.');
}

try {
    if ($driver === 'onedrive') {
        $ms_email = $_ENV['MS_ONEDRIVE_BACKUP_EMAIL'] ?? '';
        if (empty($ms_email)) throw new Exception("Thiếu cấu hình MS_ONEDRIVE_BACKUP_EMAIL");

        $cacheDir = __DIR__ . '/../../logs/cache';
        if (!is_dir($cacheDir)) @mkdir($cacheDir, 0775, true);
        
        $tokenCacheFile = $cacheDir . '/ms_token.json';
        $urlCacheFile = $cacheDir . '/ms_url_' . md5($key) . '.json';
        
        // 1. Lấy URL từ cache nếu còn hạn
        if (file_exists($urlCacheFile)) {
            $cachedUrl = json_decode(file_get_contents($urlCacheFile), true);
            if ($cachedUrl && isset($cachedUrl['url']) && $cachedUrl['expires_at'] > time() + 60) {
                if (isset($_GET['inline']) && $_GET['inline'] == 1) {
                    $ch = curl_init($cachedUrl['url']);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HEADER, true);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
                    $response = curl_exec($ch);
                    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                    $header = substr($response, 0, $header_size);
                    $body = substr($response, $header_size);
                    curl_close($ch);
                    
                    $contentType = 'image/jpeg';
                    if (preg_match('/Content-Type:\s*([a-zA-Z0-9\/\-\+]+)/i', $header, $matches)) {
                        $ct = strtolower(trim($matches[1]));
                        if ($ct !== 'application/octet-stream') {
                            $contentType = $ct;
                        }
                    }
                    header("Content-Type: $contentType");
                    header("Cache-Control: public, max-age=86400");
                    echo $body;
                    exit();
                } else {
                    header('Location: ' . $cachedUrl['url'], true, 302);
                    exit();
                }
            }
        }

        // 2. Lấy Token từ cache nếu còn hạn
        $token = null;
        if (file_exists($tokenCacheFile)) {
            $cachedToken = json_decode(file_get_contents($tokenCacheFile), true);
            if ($cachedToken && isset($cachedToken['access_token']) && $cachedToken['expires_at'] > time() + 60) {
                $token = $cachedToken['access_token'];
            }
        }
        
        if (!$token) {
            $client = new \GuzzleHttp\Client(['timeout' => 10, 'verify' => false]);
            $resToken = $client->post('https://login.microsoftonline.com/' . $_ENV['MS_TENANT_ID'] . '/oauth2/v2.0/token', [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $_ENV['MS_CLIENT_ID'],
                    'client_secret' => $_ENV['MS_CLIENT_SECRET'],
                    'scope' => 'https://graph.microsoft.com/.default',
                ]
            ]);
            $tokenData = json_decode($resToken->getBody(), true);
            $token = $tokenData['access_token'] ?? null;
            if ($token) {
                file_put_contents($tokenCacheFile, json_encode([
                    'access_token' => $token,
                    'expires_at' => time() + ($tokenData['expires_in'] ?? 3600)
                ]));
            }
        }

        if (!$token) throw new Exception("Không lấy được token Microsoft Graph.");

        // Lấy thông tin file từ Graph
        $client = new \GuzzleHttp\Client(['timeout' => 10, 'verify' => false]);
        $resItem = $client->get("https://graph.microsoft.com/v1.0/users/" . rawurlencode($ms_email) . "/drive/items/" . rawurlencode($key), [
            'headers' => [
                'Authorization' => 'Bearer ' . $token
            ]
        ]);
        $itemData = json_decode($resItem->getBody(), true);

        if (isset($itemData['@microsoft.graph.downloadUrl'])) {
            $downloadUrl = $itemData['@microsoft.graph.downloadUrl'];
            // Cache link download (thường có hạn 1 tiếng, ta cache 45 phút)
            file_put_contents($urlCacheFile, json_encode([
                'url' => $downloadUrl,
                'expires_at' => time() + 2700 
            ]));
            if (isset($_GET['inline']) && $_GET['inline'] == 1) {
                // Stream content directly for inline images (avatars)
                $ch = curl_init($downloadUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
                $response = curl_exec($ch);
                $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                $header = substr($response, 0, $header_size);
                $body = substr($response, $header_size);
                curl_close($ch);
                
                // Extract Content-Type
                $contentType = 'image/jpeg';
                if (preg_match('/Content-Type:\s*([a-zA-Z0-9\/\-\+]+)/i', $header, $matches)) {
                    $ct = strtolower(trim($matches[1]));
                    if ($ct !== 'application/octet-stream') {
                        $contentType = $ct;
                    }
                }
                
                header("Content-Type: $contentType");
                header("Cache-Control: public, max-age=86400");
                file_put_contents(__DIR__ . '/../../logs/inline_test.log', "URL: $downloadUrl\nSize: " . strlen($body) . "\nCT: $contentType\nHeader: $header");
                echo $body;
                exit();
            } else {
                header('Location: ' . $downloadUrl, true, 302);
                exit();
            }
        } else {
            throw new Exception("Không lấy được link download từ OneDrive.");
        }
    } else {
        // 1. Khởi tạo dịch vụ kết nối R2
        $storage = new StorageService();
        
        // Nếu yêu cầu tải trực tiếp về máy
        if (isset($_GET['download']) && $_GET['download'] == 1) {
            $filename = $_GET['filename'] ?? basename($key);
            $content = $storage->getFileContent($key);
            if ($content !== false && $content !== null) {
                $ascii_filename = preg_replace('/[^\x20-\x7E]/', '_', $filename);
                header('Content-Type: application/octet-stream');
                header("Content-Disposition: attachment; filename=\"{$ascii_filename}\"; filename*=UTF-8''" . rawurlencode($filename));
                header('Content-Length: ' . strlen($content));
                header('Cache-Control: no-cache, no-store, must-revalidate');
                header('Pragma: no-cache');
                header('Expires: 0');
                if (ob_get_length()) {
                    ob_clean();
                }
                flush();
                echo $content;
                exit();
            }
        }

        // 2. Tạo một link tạm thời (chỉ có hiệu lực 10 phút)
        $temporaryUrl = $storage->getTemporaryUrl($key, '+10 minutes');
        
        // 3. Chuyển hướng trình duyệt của người dùng đến link mây
        header('Location: ' . $temporaryUrl, true, 302);
        exit();
    }
} catch (Exception $e) {
    http_response_code(500);
    error_log("Lỗi tạo Presigned URL: " . $e->getMessage());
    die('Lỗi: Không thể tạo link truy cập file. ' . $e->getMessage());
}
?>