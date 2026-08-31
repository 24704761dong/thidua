<?php
// File: src/controllers/api_restore_selected_proofs.php
// Chức năng: tải file minh chứng từ mây về local và xóa khỏi mây

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập.']);
    exit();
}

session_write_close();

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/StorageService.php';

$data = json_decode(file_get_contents('php://input'), true);
$ids = $data['ids'] ?? [];
$tuan_id = $data['tuan_id'] ?? null;
$restore_all = $data['restore_all'] ?? false;

if (empty($ids) && empty($tuan_id) && !$restore_all) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Không có ID hoặc tuần hợp lệ.']);
    exit();
}

try {
    set_time_limit(180);
    $db = get_db_connection();
    $storage = new StorageService();
    $base_path = realpath(__DIR__ . '/../../');
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi khởi tạo dịch vụ: ' . $e->getMessage()]);
    exit();
}

use GuzzleHttp\Client;

function getGraphToken_forRestore() {
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

function downloadFromOneDrive($token, $userEmail, $driveItemId, $localPath) {
    $client = new Client(['timeout' => 60, 'verify' => false]);
    $client->get("https://graph.microsoft.com/v1.0/users/" . rawurlencode($userEmail) . "/drive/items/" . $driveItemId . "/content", [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
        ],
        'sink' => $localPath
    ]);
}

function deleteFromOneDrive($token, $userEmail, $driveItemId) {
    $client = new Client(['timeout' => 30, 'verify' => false]);
    $client->delete("https://graph.microsoft.com/v1.0/users/" . rawurlencode($userEmail) . "/drive/items/" . $driveItemId, [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
        ]
    ]);
}

if ($restore_all) {
    $stmt = $db->prepare("SELECT * FROM so_nhat_ky_minh_chung WHERE storage_driver IN ('cloud', 'r2', 'onedrive')");
    $stmt->execute();
} elseif ($tuan_id) {
    $stmt = $db->prepare("SELECT * FROM so_nhat_ky_minh_chung WHERE storage_driver IN ('cloud', 'r2', 'onedrive') AND nhat_ky_id IN (SELECT id FROM so_nhat_ky WHERE tuan_id = ?)");
    $stmt->execute([$tuan_id]);
} else {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $db->prepare("SELECT * FROM so_nhat_ky_minh_chung WHERE id IN ($placeholders) AND storage_driver IN ('cloud', 'r2', 'onedrive')");
    $stmt->execute($ids);
}
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($files)) {
    echo json_encode(['success' => false, 'message' => 'Không có file nào đang ở trên mây để tải về.']);
    exit();
}

$updated = 0;
$errors = [];
$logFile = __DIR__ . '/../../logs/restore_cloud_errors.log';

function log_restore_error($logFile, $message) {
    $timestamp = date('Y-m-d H:i:s');
    $line = '[' . $timestamp . '] ' . $message . PHP_EOL;
    $dir = dirname($logFile);
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    @file_put_contents($logFile, $line, FILE_APPEND);
}

foreach ($files as $file) {
    $cloudKey = $file['cloud_key'] ?? '';
    $filePathInDb = $file['file_path'] ?? '';
    if (empty($filePathInDb) && !empty($cloudKey)) {
        $filePathInDb = '/uploads/' . $cloudKey;
    }
    $localPath = $base_path . '/' . ltrim($filePathInDb, '/');
    $fileType = strtolower($file['file_type'] ?? '');
    $effectiveType = $fileType;

    if (empty($cloudKey)) {
        $errors[] = "File ID {$file['id']} thiếu cloud_key.";
        log_restore_error($logFile, "missing_cloud_key id={$file['id']} local={$localPath}");
        continue;
    }

    $dir = dirname($localPath);
    if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
        $errors[] = "Không tạo được thư mục cho file ID {$file['id']}.";
        log_restore_error($logFile, "mkdir_failed id={$file['id']} dir={$dir}");
        continue;
    }

    try {
        // 1. Tải file về local và xóa trên mây
        if ($file['storage_driver'] === 'onedrive') {
            $ms_email = trim($_ENV['MS_ONEDRIVE_BACKUP_EMAIL'] ?? '');
            if (empty($ms_email)) {
                throw new Exception("Chưa cấu hình tài khoản OneDrive.");
            }
            $token = getGraphToken_forRestore();
            downloadFromOneDrive($token, $ms_email, $cloudKey, $localPath);
            try {
                deleteFromOneDrive($token, $ms_email, $cloudKey);
            } catch (Exception $e) {
                log_restore_error($logFile, "Failed to delete OneDrive file {$cloudKey}: " . $e->getMessage());
            }
        } else {
            $storage->downloadToPath($cloudKey, $localPath);
            try {
                $storage->delete($cloudKey);
            } catch (Exception $e) {
                log_restore_error($logFile, "Failed to delete R2 file {$cloudKey}: " . $e->getMessage());
            }
        }

        // 3. Tạo thumbnail (chỉ cho ảnh và khi có GD)
        $thumbnail_path_db = null;
        $existingThumbPath = trim((string)($file['thumbnail_path'] ?? ''));
        $skipThumbCreation = false;
        if ($existingThumbPath !== '') {
            $thumbLocalPath = $base_path . '/' . ltrim($existingThumbPath, '/');
            if (file_exists($thumbLocalPath)) {
                $thumbnail_path_db = $existingThumbPath;
                $skipThumbCreation = true;
            }
        }

        if (!$skipThumbCreation) {
            if (empty($effectiveType) || strpos($effectiveType, 'image/') !== 0) {
                $ext = strtolower(pathinfo($localPath, PATHINFO_EXTENSION));
                $extMap = [
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    'webp' => 'image/webp',
                ];
                if (!empty($ext) && isset($extMap[$ext])) {
                    $effectiveType = $extMap[$ext];
                } elseif (function_exists('finfo_open')) {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    if ($finfo) {
                        $detected = finfo_file($finfo, $localPath);
                        finfo_close($finfo);
                        if (is_string($detected) && $detected !== '') {
                            $effectiveType = strtolower($detected);
                        }
                    }
                }
            }

            if (strpos($effectiveType, 'image/') === 0 && function_exists('gd_info')) {
                try {
                    $pathInfo = pathinfo($localPath);
                    $thumbExtension = strtolower($pathInfo['extension'] ?? '');
                    if ($effectiveType === 'image/webp' && !function_exists('imagewebp')) {
                        $thumbExtension = 'jpg';
                        $effectiveType = 'image/jpeg';
                    }
                    if ($thumbExtension === '') {
                        $thumbExtension = ($effectiveType === 'image/png') ? 'png' : 'jpg';
                    }

                    $thumbFilename = $pathInfo['filename'] . '_thumb.' . $thumbExtension;
                    $thumbPhysical = $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $thumbFilename;

                    // Đọc ảnh gốc
                    $srcImg = null;
                    if ($effectiveType === 'image/jpeg' || $effectiveType === 'image/jpg') {
                        $srcImg = @imagecreatefromjpeg($localPath);
                    } elseif ($effectiveType === 'image/png') {
                        $srcImg = @imagecreatefrompng($localPath);
                    } elseif ($effectiveType === 'image/gif') {
                        $srcImg = @imagecreatefromgif($localPath);
                    } elseif ($effectiveType === 'image/webp' && function_exists('imagecreatefromwebp')) {
                        $srcImg = @imagecreatefromwebp($localPath);
                    }

                    if ($srcImg) {
                        $origW = imagesx($srcImg);
                        $origH = imagesy($srcImg);
                        $maxW = 300;
                        $thumbW = ($origW > 0 && $origW > $maxW) ? $maxW : $origW;
                        $thumbH = ($origW > 0 && $origH > 0) ? (int)floor($origH * ($thumbW / $origW)) : $origH;

                        $thumbImg = imagecreatetruecolor($thumbW, $thumbH);
                        if ($effectiveType === 'image/png') {
                            imagealphablending($thumbImg, false);
                            imagesavealpha($thumbImg, true);
                            $transparent = imagecolorallocatealpha($thumbImg, 255, 255, 255, 127);
                            imagefilledrectangle($thumbImg, 0, 0, $thumbW, $thumbH, $transparent);
                        }

                        imagecopyresampled($thumbImg, $srcImg, 0, 0, 0, 0, $thumbW, $thumbH, $origW, $origH);

                        if ($effectiveType === 'image/jpeg' || $effectiveType === 'image/jpg') {
                            imagejpeg($thumbImg, $thumbPhysical, 75);
                        } elseif ($effectiveType === 'image/png') {
                            imagepng($thumbImg, $thumbPhysical, 6);
                        } elseif ($effectiveType === 'image/gif') {
                            imagegif($thumbImg, $thumbPhysical);
                        } elseif ($effectiveType === 'image/webp' && function_exists('imagewebp')) {
                            imagewebp($thumbImg, $thumbPhysical, 75);
                        }

                        imagedestroy($thumbImg);
                        imagedestroy($srcImg);

                        // Lưu path DB (dạng relative như file_path)
                        $relativeDir = trim(str_replace($base_path, '', $pathInfo['dirname']), '/\\');
                        $thumbnail_path_db = ($relativeDir ? $relativeDir . '/' : '') . $thumbFilename;
                    }
                } catch (Exception $thumbEx) {
                    error_log('Loi tao thumbnail khi restore file ID ' . $file['id'] . ': ' . $thumbEx->getMessage());
                    $thumbnail_path_db = null;
                }
            }
        }

        // 3. Cập nhật trạng thái về local
        $update = $db->prepare("UPDATE so_nhat_ky_minh_chung SET storage_driver = 'local', cloud_key = NULL, thumbnail_path = ?, file_path = ? WHERE id = ?");
        $update->execute([$thumbnail_path_db, $filePathInDb, $file['id']]);
        $updated++;
    } catch (Exception $e) {
        $errors[] = "ID {$file['id']}: " . $e->getMessage();
        $safeError = str_replace(["\r", "\n"], ' ', $e->getMessage());
        log_restore_error($logFile, "restore_failed id={$file['id']} key={$cloudKey} local={$localPath} error={$safeError}");
    }
}

$errorCount = count($errors);
$success = ($updated > 0) || ($errorCount > 0);
$message = "Đã tải về $updated file từ mây.";
if ($errorCount > 0) {
    $message .= " Bỏ qua $errorCount file lỗi (xem logs/restore_cloud_errors.log).";
}

$response = [
    'success' => $success,
    'message' => $message,
    'restored_count' => $updated,
    'error_count' => $errorCount,
    'errors' => $errors,
];

echo json_encode($response);
