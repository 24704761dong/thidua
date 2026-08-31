<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('POST, OPTIONS');
zalo_handle_options();

try {
    $db = get_db_connection();
    
    $payload = zalo_authenticate_request();
    $student_id = $payload['student_id'];
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;

    if (!$id) {
        throw new Exception("Thiếu ID minh chứng");
    }

    // Check ownership and batch status
    $stmt = $db->prepare("SELECT batch_id, file_path, cloud_id FROM minh_chung_vi_pham WHERE id = ? AND nguoi_nhap_id = ? AND nguoi_nhap_type = 'student'");
    $stmt->execute([$id, $student_id]);
    $proof = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$proof) {
        throw new Exception("Không tìm thấy minh chứng hoặc không có quyền xóa");
    }

    if ($proof['batch_id']) {
        // If it's already assigned to a batch, check if batch is approved
        $stmt_batch = $db->prepare("SELECT trang_thai_gui FROM vi_pham_tam_thoi WHERE batch_id = ? LIMIT 1");
        $stmt_batch->execute([$proof['batch_id']]);
        $batch = $stmt_batch->fetch(PDO::FETCH_ASSOC);
        if ($batch && in_array($batch['trang_thai_gui'], ['da_duyet', 'da_loai_bo'])) {
            throw new Exception("Không thể xóa minh chứng của đợt vi phạm đã được duyệt/xử lý");
        }
    }

    // Delete file locally
    $local_path = __DIR__ . '/../../' . $proof['file_path'];
    if (file_exists($local_path)) {
        unlink($local_path);
    }

    // Delete from R2 if applicable
    if (!empty($proof['cloud_id'])) {
        try {
            require_once __DIR__ . '/../lib/StorageService.php';
            $storage = new StorageService();
            $storage->delete($proof['cloud_id']);
        } catch (Exception $e) {
            // Ignore if cloud delete fails
        }
    }

    $stmt_del = $db->prepare("DELETE FROM minh_chung_vi_pham WHERE id = ?");
    $stmt_del->execute([$id]);

    echo json_encode([
        'success' => true,
        'message' => 'Đã xóa minh chứng'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
