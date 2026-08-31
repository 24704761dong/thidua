<?php
// File: src/controllers/api_regen_thumbnails.php
// Chuc nang: tao lai thumbnail cho minh chung local (thieu thumbnail)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Khong co quyen truy cap.']);
    exit();
}

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/database.php';

if (!function_exists('gd_info')) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'May chu khong ho tro GD de tao thumbnail.']);
    exit();
}

set_time_limit(300);

$logFile = __DIR__ . '/../../logs/regen_thumbnail_errors.log';

function log_regen_error($logFile, $message) {
    $timestamp = date('Y-m-d H:i:s');
    $line = '[' . $timestamp . '] ' . $message . PHP_EOL;
    $dir = dirname($logFile);
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    @file_put_contents($logFile, $line, FILE_APPEND);
}

function normalize_mime_type($fileType) {
    $normalized = strtolower(trim($fileType ?? ''));
    if ($normalized === '') {
        return '';
    }
    $semicolonPos = strpos($normalized, ';');
    if ($semicolonPos !== false) {
        $normalized = trim(substr($normalized, 0, $semicolonPos));
    }
    return $normalized;
}

function detect_effective_type($fileType, $localPath) {
    $effectiveType = normalize_mime_type($fileType);

    if ($effectiveType === '' || strpos($effectiveType, 'image/') !== 0) {
        $ext = strtolower(pathinfo($localPath, PATHINFO_EXTENSION));
        $extMap = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'heic' => 'image/heic',
            'heif' => 'image/heif',
        ];
        if (!empty($ext) && isset($extMap[$ext])) {
            $effectiveType = $extMap[$ext];
        } elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $detected = finfo_file($finfo, $localPath);
                finfo_close($finfo);
                if (is_string($detected) && $detected !== '') {
                    $effectiveType = normalize_mime_type($detected);
                }
            }
        }
    }

    return $effectiveType;
}

try {
    $db = get_db_connection();
    $base_path = realpath(__DIR__ . '/../../');

    $inputData = json_decode(file_get_contents('php://input'), true) ?: [];
    $tuan_id = $_POST['tuan_id'] ?? $inputData['tuan_id'] ?? $_GET['tuan_id'] ?? null;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : (isset($inputData['limit']) ? (int)$inputData['limit'] : 0);

    $params = [];
    $joins = '';
    $conditions = [
        "sm.storage_driver = 'local'",
        "(sm.thumbnail_path IS NULL OR sm.thumbnail_path = '')",
        "sm.file_path IS NOT NULL",
        "sm.file_path <> ''",
    ];

    if (!empty($tuan_id)) {
        $joins = ' JOIN so_nhat_ky_online snk ON sm.nhat_ky_id = snk.id';
        $conditions[] = 'snk.tuan_hoc_id = ?';
        $params[] = $tuan_id;
    }

    $query = 'SELECT sm.id, sm.file_path, sm.file_type, sm.original_filename FROM so_nhat_ky_minh_chung sm' . $joins;
    $query .= ' WHERE ' . implode(' AND ', $conditions) . ' ORDER BY sm.id DESC';
    if ($limit > 0) {
        $query .= ' LIMIT ' . $limit;
    }

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $scanned = 0;
    $created = 0;
    $skippedNonImage = 0;
    $missingFiles = 0;
    $errors = 0;

    foreach ($files as $file) {
        $scanned++;
        $localPath = $base_path . '/' . ltrim($file['file_path'], '/');

        if (!file_exists($localPath)) {
            $missingFiles++;
            log_regen_error($logFile, "missing_file id={$file['id']} local={$localPath}");
            continue;
        }

        $effectiveType = detect_effective_type($file['file_type'] ?? '', $localPath);
        if (strpos($effectiveType, 'image/') !== 0) {
            $skippedNonImage++;
            continue;
        }

        $pathInfo = pathinfo($localPath);
        $extension = strtolower($pathInfo['extension'] ?? '');
        $isHeic = in_array($extension, ['heic', 'heif'], true)
            || strpos($effectiveType, 'image/heic') === 0
            || strpos($effectiveType, 'image/heif') === 0;

        try {
            if ($isHeic) {
                if (!class_exists('Imagick')) {
                    $errors++;
                    log_regen_error($logFile, "heic_no_imagick id={$file['id']} local={$localPath}");
                    continue;
                }

                $relativeDir = trim(str_replace($base_path, '', $pathInfo['dirname']), '/\\');
                $jpgFilename = $pathInfo['filename'] . '.jpg';
                $jpgPhysical = $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $jpgFilename;
                $jpgRelativePath = ($relativeDir ? $relativeDir . '/' : '') . $jpgFilename;

                $thumbFilename = $pathInfo['filename'] . '_thumb.jpg';
                $thumbPhysical = $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $thumbFilename;
                $thumbnail_path_db = ($relativeDir ? $relativeDir . '/' : '') . $thumbFilename;

                $originalFilename = $file['original_filename'] ?? '';
                $updatedOriginalFilename = $originalFilename;
                if ($originalFilename !== '') {
                    $origInfo = pathinfo($originalFilename);
                    $origExt = strtolower($origInfo['extension'] ?? '');
                    if ($origExt === '' || in_array($origExt, ['heic', 'heif'], true)) {
                        $updatedOriginalFilename = $origInfo['filename'] . '.jpg';
                    }
                }

                $image = new Imagick();
                $image->readImage($localPath);
                if (method_exists($image, 'autoOrient')) {
                    $image->autoOrient();
                }
                $image->setImageFormat('jpeg');
                $image->setImageCompressionQuality(85);
                $image->writeImage($jpgPhysical);

                $origW = $image->getImageWidth();
                $origH = $image->getImageHeight();
                $maxW = 300;
                $thumbW = ($origW > 0 && $origW > $maxW) ? $maxW : $origW;
                $thumbH = ($origW > 0 && $origH > 0) ? (int)floor($origH * ($thumbW / $origW)) : $origH;

                if ($thumbW <= 0 || $thumbH <= 0) {
                    $image->clear();
                    $image->destroy();
                    $errors++;
                    log_regen_error($logFile, "invalid_size id={$file['id']} w={$origW} h={$origH}");
                    continue;
                }

                $image->thumbnailImage($thumbW, $thumbH, true);
                $image->setImageCompressionQuality(75);
                $image->writeImage($thumbPhysical);
                $image->clear();
                $image->destroy();

                if ($localPath !== $jpgPhysical && file_exists($localPath)) {
                    @unlink($localPath);
                }

                $update = $db->prepare('UPDATE so_nhat_ky_minh_chung SET file_path = ?, file_type = ?, original_filename = ?, thumbnail_path = ? WHERE id = ?');
                $update->execute([
                    $jpgRelativePath,
                    'image/jpeg',
                    $updatedOriginalFilename,
                    $thumbnail_path_db,
                    $file['id']
                ]);

                $created++;
                continue;
            }

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

            if (!$srcImg) {
                $imageData = @file_get_contents($localPath);
                if ($imageData !== false) {
                    $srcImg = @imagecreatefromstring($imageData);
                }
            }

            if (!$srcImg) {
                $errors++;
                log_regen_error($logFile, "open_failed id={$file['id']} local={$localPath} type={$effectiveType}");
                continue;
            }

            $origW = imagesx($srcImg);
            $origH = imagesy($srcImg);
            $maxW = 300;
            $thumbW = ($origW > 0 && $origW > $maxW) ? $maxW : $origW;
            $thumbH = ($origW > 0 && $origH > 0) ? (int)floor($origH * ($thumbW / $origW)) : $origH;

            if ($thumbW <= 0 || $thumbH <= 0) {
                imagedestroy($srcImg);
                $errors++;
                log_regen_error($logFile, "invalid_size id={$file['id']} w={$origW} h={$origH}");
                continue;
            }

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

            $relativeDir = trim(str_replace($base_path, '', $pathInfo['dirname']), '/\\');
            $thumbnail_path_db = ($relativeDir ? $relativeDir . '/' : '') . $thumbFilename;

            $update = $db->prepare('UPDATE so_nhat_ky_minh_chung SET thumbnail_path = ? WHERE id = ?');
            $update->execute([$thumbnail_path_db, $file['id']]);

            $created++;
        } catch (Exception $e) {
            $errors++;
            $safeError = str_replace(["\r", "\n"], ' ', $e->getMessage());
            log_regen_error($logFile, "thumb_failed id={$file['id']} local={$localPath} error={$safeError}");
        }
    }

    $message = "Da tao $created thumbnail. Bo qua $skippedNonImage file khong phai anh. Thieu $missingFiles file. Loi $errors file.";
    if ($errors > 0 || $missingFiles > 0) {
        $message .= ' Xem logs/regen_thumbnail_errors.log.';
    }

    echo json_encode([
        'success' => true,
        'message' => $message,
        'scanned' => $scanned,
        'created' => $created,
        'skipped_non_image' => $skippedNonImage,
        'missing_files' => $missingFiles,
        'errors' => $errors,
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Loi he thong: ' . $e->getMessage()]);
}
