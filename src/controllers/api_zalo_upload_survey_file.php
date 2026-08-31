<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('POST, OPTIONS');
zalo_handle_options();

// File: src/controllers/api_zalo_upload_survey_file.php
require_once __DIR__ . '/../lib/StorageService.php';

if (session_status() === PHP_SESSION_NONE) session_start();
$is_admin = isset($_SESSION['user_id']) && isset($_SESSION['user_vai_tro']) && $_SESSION['user_vai_tro'] === 'admin';

$student_id = null;

if (!$is_admin) {
    $payload = zalo_authenticate_request();
    $student_id = $payload['student_id'];
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Không có file nào được tải lên hoặc có lỗi xảy ra.']);
    exit();
}

$file = $_FILES['file'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];

if (!in_array($ext, $allowed)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Định dạng file không được hỗ trợ.']);
    exit();
}

$unique_name = uniqid('khao_sat_') . '_' . time() . '.' . $ext;
$cloud_key = 'surveys/' . $unique_name;
$local_tmp = $file['tmp_name'];

$uploaded_url = null;

try {
    // Thử tải lên Cloudflare R2 thông qua StorageService
    if (!empty($_ENV['R2_BUCKET_NAME']) && !empty($_ENV['R2_ACCESS_KEY_ID'])) {
        $storage = new StorageService();
        $storage->upload($local_tmp, $cloud_key);
        // Tùy theo cấu hình public URL của R2, hoặc dùng getTemporaryUrl
        $uploaded_url = $storage->getTemporaryUrl($cloud_key, '+7 days');
    }
} catch (Exception $e) {
    // Log lỗi R2 và chuyển sang fallback local
    error_log("R2 upload failed: " . $e->getMessage());
}

// Fallback lưu local nếu R2 chưa cấu hình hoặc thất bại
if (!$uploaded_url) {
    $upload_dir = __DIR__ . '/../../public/uploads/surveys/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    $dest_path = $upload_dir . $unique_name;
    if (move_uploaded_file($local_tmp, $dest_path)) {
        $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
        $uploaded_url = $base_url . '/thidua/public/uploads/surveys/' . $unique_name;
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Không thể lưu file trên server.']);
        exit();
    }
}

echo json_encode([
    'success' => true,
    'file_url' => $uploaded_url,
    'file_name' => $file['name']
]);
