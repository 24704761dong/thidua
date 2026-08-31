<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('POST, GET, OPTIONS');
zalo_handle_options();

require_once __DIR__ . '/../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->safeLoad();

try {
    $db = get_db_connection();
    
    $payload = zalo_authenticate_request();
    $student_id = $payload['student_id'];
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Phương thức không hợp lệ.");
    }

    if (!isset($_FILES['file'])) {
        throw new Exception("Vui lòng chọn file minh chứng.");
    }

    $file = $_FILES['file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Lỗi khi tải file lên.");
    }

    if ($file['size'] > 10 * 1024 * 1024) {
        throw new Exception("Dung lượng file tối đa là 10MB.");
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'jfif', 'webp', 'heic'])) {
        throw new Exception("Chỉ hỗ trợ file ảnh (jpg, jpeg, png, jfif, webp, heic).");
    }

    $new_filename = uniqid('xvh_') . '_' . time() . '.' . $ext;
    
    // Upload directly to R2
    $cloud_key = null;
    $response_url = '';
    
    try {
        require_once __DIR__ . '/../lib/StorageService.php';
        $storage = new StorageService();
        $r2_folder = "MinhChung/XinVangHoc/" . $student_id . "/" . date('Y-m-d');
        $cloud_key = $r2_folder . "/" . $new_filename;
        
        // Upload the temp file directly
        $storage->upload($file['tmp_name'], $cloud_key);
        
        // Lấy URL tạm để hiển thị trước (preview)
        $response_url = $storage->getTemporaryUrl($cloud_key, '+60 minutes');
    } catch (Exception $e) {
        throw new Exception("Lỗi khi tải ảnh lên máy chủ lưu trữ (R2). Vui lòng thử lại sau.");
    }

    echo json_encode([
        'success' => true,
        'message' => 'Upload minh chứng thành công',
        'data' => [
            'cloud_key' => $cloud_key,
            'url' => $response_url,
            'original_name' => $file['name']
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
