<?php
// File: src/controllers/xuat_minh_chung_zip.php

set_time_limit(0);
ini_set('memory_limit', '512M');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['user_vai_tro'] ?? $_SESSION['vai_tro'] ?? $_SESSION['role'] ?? '';
if (!isset($_SESSION['user_id']) || !in_array($role, ['admin', 'user'])) {
    header('HTTP/1.1 403 Forbidden');
    exit('Bạn không có quyền truy cập.');
}

// GIẢI PHÓNG KHÓA SESSION ĐỂ TRÁNH LAG HỆ THỐNG KHI ĐANG TẢI ZIP
session_write_close();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../lib/StorageService.php';

$tuan_id = $_GET['tuan_id'] ?? null;
if (!$tuan_id) {
    echo "<script>alert('Thiếu ID của tuần học.'); history.back();</script>";
    exit();
}

try {
    $db = get_db_connection();
    
    // Lấy thông tin tuần
    $stmt_tuan = $db->prepare("SELECT ten_tuan FROM tuan_hoc WHERE id = ?");
    $stmt_tuan->execute([$tuan_id]);
    $ten_tuan = $stmt_tuan->fetchColumn();
    if (!$ten_tuan) {
        echo "<script>alert('Tuần học không tồn tại.'); history.back();</script>";
        exit();
    }

    // Lấy tất cả minh chứng của tuần
    $stmt_proofs = $db->prepare("
        SELECT sm.file_path, sm.original_filename, sm.storage_driver, sm.cloud_key, lh.ten_lop
        FROM so_nhat_ky_minh_chung sm
        JOIN so_nhat_ky_online snk ON sm.nhat_ky_id = snk.id
        JOIN lop_hoc lh ON snk.lop_hoc_id = lh.id
        WHERE snk.tuan_hoc_id = ?
    ");
    $stmt_proofs->execute([$tuan_id]);
    $all_proofs = $stmt_proofs->fetchAll(PDO::FETCH_ASSOC);

    if (empty($all_proofs)) {
        echo "<script>alert('Không có file minh chứng nào để tải về cho tuần này.'); history.back();</script>";
        exit();
    }

    $zip = new ZipArchive();
    $zipFileName = 'MinhChung_' . preg_replace('/[\s\/\\:*?"<>|]+/', '-', $ten_tuan) . '.zip';
    $zipFilePath = sys_get_temp_dir() . '/' . $zipFileName;

    if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        echo "<script>alert('Không thể khởi tạo file nén trên máy chủ.'); history.back();</script>";
        exit();
    }

    $storage = null;
    $files_added = 0;

    // Thêm file vào zip với cấu trúc thư mục
    foreach ($all_proofs as $file) {
        $khoi = 'Khoi ' . substr($file['ten_lop'], 0, 2);
        $lop = $file['ten_lop'];
        $filename = $file['original_filename'] ?: basename($file['file_path'] ?: 'minh_chung.jpg');
        $entry_name = "Toan truong/{$khoi}/{$lop}/{$filename}";

        // Trường hợp file trên Cloudflare R2
        if (in_array($file['storage_driver'] ?? '', ['r2', 'cloud']) && !empty($file['cloud_key'])) {
            try {
                if (!$storage) {
                    $storage = new StorageService();
                }
                $content = $storage->getFileContent($file['cloud_key']);
                if ($content !== false && $content !== null && strlen($content) > 0) {
                    $zip->addFromString($entry_name, $content);
                    $files_added++;
                }
            } catch (Exception $e) {
                error_log("Failed to download R2 proof for zip: " . $file['cloud_key'] . " - " . $e->getMessage());
            }
        } else {
            // Trường hợp file ở Local
            $physical_path = __DIR__ . '/../../' . ltrim($file['file_path'], '/');
            if (file_exists($physical_path)) {
                $zip->addFile($physical_path, $entry_name);
                $files_added++;
            }
        }
    }

    if ($files_added === 0) {
        $zip->close();
        if (file_exists($zipFilePath)) {
            @unlink($zipFilePath);
        }
        echo "<script>alert('Không tìm thấy file vật lý hoặc file cloud nào để tải về cho tuần này.'); history.back();</script>";
        exit();
    }

    $zip->close();

    // Gửi file về trình duyệt
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
    header('Content-Length: ' . filesize($zipFilePath));
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    if (ob_get_length()) {
        ob_clean();
    }
    flush();
    readfile($zipFilePath);

    // Xóa file tạm
    @unlink($zipFilePath);
    exit();

} catch (Exception $e) {
    echo "<script>alert('Lỗi hệ thống: " . addslashes($e->getMessage()) . "'); history.back();</script>";
    exit();
}