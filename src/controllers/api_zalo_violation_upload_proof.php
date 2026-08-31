<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('POST, OPTIONS');
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

    $tuan_hoc_id = $_POST['tuan_hoc_id'] ?? null;
    if (!$tuan_hoc_id) {
        throw new Exception("Thiếu tham số tuần học.");
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
        throw new Exception("Chỉ hỗ trợ file ảnh.");
    }

    $new_filename = uniqid('vp_') . '_' . time() . '.' . $ext;
    
    $cloud_key = null;
    $response_url = '';
    
    try {
        require_once __DIR__ . '/../lib/StorageService.php';
        $storage = new StorageService();
        $r2_folder = "MinhChung/ViPham/" . $tuan_hoc_id . "/" . $student_id;
        $cloud_key = $r2_folder . "/" . $new_filename;
        
        $storage->upload($file['tmp_name'], $cloud_key);
        $response_url = $storage->getTemporaryUrl($cloud_key, '+60 minutes');
    } catch (Exception $e) {
        throw new Exception("Lỗi khi tải ảnh lên máy chủ lưu trữ (R2). Vui lòng thử lại sau.");
    }

    // Save to DB
    $stmt = $db->prepare("INSERT INTO minh_chung_vi_pham (tuan_hoc_id, file_name, file_path, cloud_id, cloud_url, trang_thai, nguoi_nhap_id, nguoi_nhap_type) VALUES (?, ?, ?, ?, ?, 'synced', ?, 'student')");
    $stmt->execute([
        $tuan_hoc_id,
        $file['name'],
        '', // local path empty
        $cloud_key,
        null, // cloud_url empty because we generate temp url
        $student_id
    ]);
    
    $new_id = $db->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Upload minh chứng thành công',
        'data' => [
            'id' => $new_id,
            'url' => $response_url,
            'file_name' => $file['name']
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
