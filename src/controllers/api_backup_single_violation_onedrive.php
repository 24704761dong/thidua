<?php
// File: src/controllers/api_backup_single_violation_onedrive.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ob_start(); // Bắt đầu đệm đầu ra để chặn mọi warning in ra màn hình làm hỏng JSON

// Bảo mật: Chỉ admin/user mới có quyền
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    http_response_code(403);
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập.']);
    exit();
}

// GIẢI PHÓNG KHÓA SESSION NGAY LẬP TỨC!
session_write_close();

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../lib/StorageService.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;

if (!$id) {
    http_response_code(400);
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Thiếu ID minh chứng.']);
    exit();
}

$ms_email = $_ENV['MS_ONEDRIVE_BACKUP_EMAIL'] ?? '';
if (empty($ms_email)) {
    http_response_code(400);
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Chưa cấu hình MS_ONEDRIVE_BACKUP_EMAIL trong .env']);
    exit();
}

try {
    $db = get_db_connection();
    
    // Lấy thông tin minh chứng vi phạm
    $stmt = $db->prepare("
        SELECT id, batch_id, file_name, file_path, cloud_id, cloud_url, trang_thai
        FROM minh_chung_vi_pham
        WHERE id = ?
    ");
    $stmt->execute([$id]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$file) {
        throw new Exception("Không tìm thấy minh chứng vi phạm.");
    }

    if ($file['trang_thai'] === 'onedrive') {
        ob_clean();
        echo json_encode(['success' => true, 'message' => 'Đã sao lưu trước đó.']);
        exit();
    }

    $base_path = realpath(__DIR__ . '/../../');
    $localPath = $base_path . '/' . ltrim($file['file_path'], '/');
    $isTempDownloaded = false;

    // 1. Tải về từ R2 nếu ở Cloud
    if ($file['trang_thai'] === 'synced' && !empty($file['cloud_id'])) {
        $storage = new StorageService();
        $tempDir = $base_path . '/public/uploads/temp_onedrive';
        if (!is_dir($tempDir)) mkdir($tempDir, 0777, true);
        
        // Sinh tên file tạm
        $ext = pathinfo($file['file_name'] ?: $file['file_path'], PATHINFO_EXTENSION);
        $tempFile = $tempDir . '/' . uniqid() . '.' . $ext;
        
        // Download from R2
        $storage->downloadToPath($file['cloud_id'], $tempFile);
        
        $localPath = $tempFile;
        $isTempDownloaded = true;
    }

    if (!file_exists($localPath)) {
        throw new Exception("Không tìm thấy file nguồn (Local/R2).");
    }

    // 2. Lấy Token Graph API
    $token = getGraphToken();

    // 3. Upload lên OneDrive
    $safe_name = preg_replace('/[^a-zA-Z0-9_\-\x{00A0}-\x{FFFF} \.]/u', '_', $file['file_name'] ?: basename($file['file_path']));
    $ext = pathinfo($safe_name, PATHINFO_EXTENSION);
    $filename_without_ext = pathinfo($safe_name, PATHINFO_FILENAME);
    $safe_name = $filename_without_ext . '_' . $file['id'] . '.' . $ext;
    
    // Path trên OneDrive: Minh_Chung_Vi_Pham/Batch_.../filename
    $safe_batch = preg_replace('/[^a-zA-Z0-9_\-\x{00A0}-\x{FFFF} \.]/u', '_', $file['batch_id'] ?? 'Khong_xac_dinh');
    $onedrivePath = "Minh_Chung_Vi_Pham/{$safe_batch}/{$safe_name}";
    
    $onedriveId = uploadToOneDrive($token, $ms_email, $onedrivePath, $localPath);

    // 4. Cập nhật Database
    $cloud_url = '/thidua/api/get-presigned-url?key=' . urlencode($onedriveId) . '&driver=onedrive';
    $stmtUpdate = $db->prepare("UPDATE minh_chung_vi_pham SET trang_thai = 'onedrive', cloud_id = ?, cloud_url = ? WHERE id = ?");
    $stmtUpdate->execute([$onedriveId, $cloud_url, $id]);

    // 5. Dọn dẹp Local
    if ($isTempDownloaded) {
        @unlink($localPath);
    } else {
        @unlink($localPath); // Xóa bản local gốc để giải phóng dung lượng
    }
    
    // Xóa trên R2
    if ($file['trang_thai'] === 'synced' && !empty($file['cloud_id'])) {
        try {
            $storage = new StorageService();
            $storage->delete($file['cloud_id']);
        } catch (Exception $e) {
            error_log("Không thể xóa R2: " . $e->getMessage());
        }
    }

    ob_clean();
    echo json_encode(['success' => true, 'message' => 'Upload thành công.', 'onedrive_id' => $onedriveId]);

} catch (Exception $e) {
    http_response_code(500);
    error_log("Lỗi OneDrive: " . $e->getMessage());
    file_put_contents(__DIR__.'/../../onedrive_error_log.txt', date('Y-m-d H:i:s') . ' - Lỗi: ' . $e->getMessage() . "\n", FILE_APPEND);
    ob_clean();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    file_put_contents(__DIR__.'/../../onedrive_error_log.txt', date('Y-m-d H:i:s') . ' - FATAL Lỗi: ' . $e->getMessage() . "\n", FILE_APPEND);
    ob_clean();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// --- Helper Functions ---

function getGraphToken() {
    $client = new Client(['timeout' => 10, 'verify' => false]);
    $res = $client->post('https://login.microsoftonline.com/' . $_ENV['MS_TENANT_ID'] . '/oauth2/v2.0/token', [
        'form_params' => [
            'grant_type' => 'client_credentials',
            'client_id' => $_ENV['MS_CLIENT_ID'],
            'client_secret' => $_ENV['MS_CLIENT_SECRET'],
            'scope' => 'https://graph.microsoft.com/.default',
        ]
    ]);
    $data = json_decode($res->getBody(), true);
    if (empty($data['access_token'])) throw new Exception('Không lấy được access_token Microsoft.');
    return $data['access_token'];
}

function uploadToOneDrive($token, $userEmail, $remotePath, $localPath) {
    $client = new Client(['timeout' => 60, 'verify' => false]);
    $fileSize = filesize($localPath);
    
    // Mã hoá path để dùng trong URL
    $encodedPath = rawurlencode($remotePath);
    $encodedPath = str_replace('%2F', '/', $encodedPath); // Giữ nguyên dấu /

    $baseUrl = "https://graph.microsoft.com/v1.0/users/" . rawurlencode($userEmail) . "/drive/root:/{$encodedPath}:/content";
    
    if ($fileSize <= 4 * 1024 * 1024) { // Dưới 4MB dùng simple upload
        $stream = fopen($localPath, 'rb');
        $res = $client->put($baseUrl, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/octet-stream'
            ],
            'body' => $stream
        ]);
        if (is_resource($stream)) fclose($stream);
        
        $data = json_decode($res->getBody(), true);
        return $data['id']; // Trả về DriveItem ID
    } else {
        // Upload Session cho file lớn > 4MB
        $sessionUrl = "https://graph.microsoft.com/v1.0/users/" . rawurlencode($userEmail) . "/drive/root:/{$encodedPath}:/createUploadSession";
        $sessionRes = $client->post($sessionUrl, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'item' => [
                    '@microsoft.graph.conflictBehavior' => 'replace'
                ]
            ]
        ]);
        $sessionData = json_decode($sessionRes->getBody(), true);
        $uploadUrl = $sessionData['uploadUrl'];

        $chunkSize = 320 * 1024 * 10; // 3.2MB (must be multiple of 320 KB)
        $handle = fopen($localPath, 'rb');
        $uploaded = 0;
        
        $lastResData = null;

        while (!feof($handle)) {
            $chunk = fread($handle, $chunkSize);
            $bytes = strlen($chunk);
            $start = $uploaded;
            $end = $uploaded + $bytes - 1;

            $res = $client->put($uploadUrl, [
                'headers' => [
                    'Content-Length' => $bytes,
                    'Content-Range' => "bytes {$start}-{$end}/{$fileSize}"
                ],
                'body' => $chunk
            ]);
            
            if ($res->getStatusCode() == 201 || $res->getStatusCode() == 200) {
                $lastResData = json_decode($res->getBody(), true);
            }
            
            $uploaded += $bytes;
        }
        fclose($handle);
        return $lastResData['id'];
    }
}
?>
