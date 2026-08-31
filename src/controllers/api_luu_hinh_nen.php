<?php
// File: src/controllers/api_luu_hinh_nen.php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403); exit();
}

require_once __DIR__ . '/../../config/database.php';
$data = json_decode(file_get_contents('php://input'), true);
$background_data_url = $data['backgroundDataUrl'] ?? '';
$user_id = $_SESSION['user_id'];

// Giới hạn kích thước (10MB sau base64 decode)
$MAX_BYTES = 10 * 1024 * 1024;

try {
    if (empty($background_data_url) || !is_string($background_data_url)) {
        throw new Exception('Dữ liệu hình nền không hợp lệ.');
    }

    if ($background_data_url === 'RESET') {
        $db = get_db_connection();
        $stmt = $db->prepare("UPDATE users SET hinh_nen_desktop = NULL WHERE id = ?");
        $stmt->execute([$user_id]);
        echo json_encode(['success' => true, 'background_url' => '/thidua/public/assets/img/desktop_bg.jpg']);
        exit();
    }

    // Parse data URL
    if (!preg_match('#^data:image/(png|jpeg|jpg|webp);base64,(.+)$#i', $background_data_url, $m)) {
        throw new Exception('Định dạng hình nền không được hỗ trợ (chỉ png/jpeg/webp).');
    }
    $ext = strtolower($m[1]) === 'jpeg' ? 'jpg' : strtolower($m[1]);
    $base64 = $m[2];
    $binary = base64_decode($base64, true);
    if ($binary === false) {
        throw new Exception('Không giải mã được dữ liệu hình nền.');
    }
    if (strlen($binary) > $MAX_BYTES) {
        throw new Exception('Kích thước ảnh vượt quá 10MB. Vui lòng chọn ảnh nhỏ hơn.');
    }

    // Lưu file vào thư mục public/uploads/backgrounds
    $uploadDir = realpath(__DIR__ . '/../../public') . '/uploads/backgrounds';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
            throw new Exception('Không tạo được thư mục lưu hình nền.');
        }
    }

    $filename = 'bg_' . $user_id . '_' . time() . '.' . $ext;
    $filePath = $uploadDir . '/' . $filename;

    if (file_put_contents($filePath, $binary) === false) {
        throw new Exception('Không ghi được file hình nền.');
    }

    // Lưu path (dạng URL) vào DB để giảm kích thước cột
    $publicUrl = '/thidua/public/uploads/backgrounds/' . $filename;

    $db = get_db_connection();
    $stmt = $db->prepare("UPDATE users SET hinh_nen_desktop = ? WHERE id = ?");
    $stmt->execute([$publicUrl, $user_id]);

    echo json_encode(['success' => true, 'background_url' => $publicUrl]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}