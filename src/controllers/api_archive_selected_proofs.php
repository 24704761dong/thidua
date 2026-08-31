<?php
// File: src/controllers/api_archive_selected_proofs.php
// (PHIÊN BẢN ĐÃ SỬA LỖI - BỔ SUNG BOOTSTRAP VÀ CÁC THƯ VIỆN)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Bảo mật: Chỉ admin/user mới có quyền
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Không có quyền truy cập.']);
    exit();
}

session_write_close();

header('Content-Type: application/json');

// === BẮT ĐẦU SỬA LỖI: THÊM CÁC DÒNG NẠP FILE QUAN TRỌNG ===
// Nạp file .env, session, CSDL, và các hàm helper
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../lib/helpers.php';
// === KẾT THÚC SỬA LỖI ===

// Nạp dịch vụ mây (bắt buộc sau bootstrap)
require_once __DIR__ . '/../lib/StorageService.php'; 

// Nhận danh sách ID từ JSON payload
$data = json_decode(file_get_contents('php://input'), true);
$ids_to_archive = $data['ids'] ?? [];

if (empty($ids_to_archive) || !is_array($ids_to_archive)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Không có file ID nào được chọn.']);
    exit();
}

// Khởi tạo các dịch vụ
try {
    set_time_limit(120); // Cho phép chạy 2 phút
    $db = get_db_connection();
    $storage = new StorageService(); // Giờ đây new StorageService() sẽ hoạt động
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi khởi tạo dịch vụ: ' . $e->getMessage()]);
    exit();
}

$archived_count = 0;
$skipped_count = 0;
$errors = [];

// Đường dẫn thư mục gốc của dự án
$base_path = realpath(__DIR__ . '/../../'); 

try {
    $db->beginTransaction();

    // 1. Tạo câu lệnh SQL với các placeholder (?)
    $placeholders = implode(',', array_fill(0, count($ids_to_archive), '?'));
    $stmt_get = $db->prepare(
        "SELECT * FROM so_nhat_ky_minh_chung 
         WHERE id IN ($placeholders) AND storage_driver = 'local'"
    );
    // Bind các ID vào câu lệnh
    $stmt_get->execute($ids_to_archive);
    $files = $stmt_get->fetchAll(PDO::FETCH_ASSOC);

    $stmt_update = $db->prepare(
        "UPDATE so_nhat_ky_minh_chung 
         SET storage_driver = 'cloud', cloud_key = ? 
         WHERE id = ?"
    );

    foreach ($files as $file) {
        $localPath = $base_path . '/' . $file['file_path'];
        // Tên file trên mây (bỏ 'public/uploads/')
        $cloudKey = str_replace('public/uploads/', '', $file['file_path']); 

        if (file_exists($localPath)) {
            // 2. Tải file local lên R2
            $storage->upload($localPath, $cloudKey);
            
            // 3. Cập nhật CSDL: đánh dấu là 'cloud' và lưu 'key'
            $stmt_update->execute([$cloudKey, $file['id']]);
            
            // 4. Xóa file local để giải phóng dung lượng
            @unlink($localPath);
            if (!empty($file['thumbnail_path'])) {
                $thumbLocalPath = $base_path . '/' . $file['thumbnail_path'];
                if (file_exists($thumbLocalPath)) {
                    @unlink($thumbLocalPath);
                }
            }
            $archived_count++;
        } else {
            // File không tồn tại vật lý? Chỉ cần đánh dấu
            $stmt_update->execute(['FILE_NOT_FOUND', $file['id']]);
            $skipped_count++;
        }
    }
    
    $db->commit();
    echo json_encode([
        'success' => true, 
        'message' => "Di chuyển thành công {$archived_count} file. Bỏ qua {$skipped_count} file (không tìm thấy local).",
        'archived_count' => $archived_count,
        'skipped_count' => $skipped_count
    ]);

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi nghiêm trọng: ' . $e->getMessage()]);
}
?>