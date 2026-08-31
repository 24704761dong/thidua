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
    
    $nhat_ky_id = $_POST['nhat_ky_id'] ?? null;
    $tuan_hoc_id = $_POST['tuan_hoc_id'] ?? null;
    $loai_minh_chung = $_POST['loai_minh_chung'] ?? 'sdb_ck';
    
    if (!$nhat_ky_id && !$tuan_hoc_id) {
        throw new Exception("Thiếu thông tin sổ nhật ký.");
    }

    if (!isset($_FILES['file'])) {
        throw new Exception("Vui lòng chọn file minh chứng.");
    }

    if ($nhat_ky_id) {
        $stmt_check = $db->prepare("
            SELECT snk.id, snk.trang_thai, t.ten_tuan, lh.ten_lop 
            FROM so_nhat_ky_online snk 
            JOIN raw_tuan_hoc t ON snk.tuan_hoc_id = t.id 
            JOIN raw_lop_hoc lh ON snk.lop_hoc_id = lh.id 
            WHERE snk.id = ?
        ");
        $stmt_check->execute([$nhat_ky_id]);
    } else {
        $stmt_check = $db->prepare("
            SELECT snk.id, snk.trang_thai, t.ten_tuan, lh.ten_lop 
            FROM so_nhat_ky_online snk 
            JOIN raw_tuan_hoc t ON snk.tuan_hoc_id = t.id 
            JOIN raw_lop_hoc lh ON snk.lop_hoc_id = lh.id 
            WHERE snk.tuan_hoc_id = ? AND snk.nguoi_nhap_id = ?
        ");
        $stmt_check->execute([$tuan_hoc_id, $student_id]);
    }
    $nhat_ky = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if (!$nhat_ky) {
        throw new Exception("Sổ nhật ký chưa được tạo. Vui lòng tải lại trang.");
    }

    if ($nhat_ky['trang_thai'] === 'da_duyet' || $nhat_ky['trang_thai'] === 'da_gui') {
        throw new Exception("Sổ nhật ký này đã gửi hoặc đã duyệt, không thể tải thêm ảnh.");
    }

    $file = $_FILES['file'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Lỗi khi tải file lên.");
    }

    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'jfif', 'pdf', 'heic'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($file_extension, $allowed_extensions)) {
        throw new Exception("Chỉ cho phép tải lên file ảnh (jpg, png, webp) hoặc PDF.");
    }

    if ($file['size'] > 10 * 1024 * 1024) {
        throw new Exception("Kích thước file không được vượt quá 10MB.");
    }

    // Đặt tên file theo cấu trúc chuẩn: [Tên lớp] - [Tên tuần] - [Loại sổ].ext
    $loai_so_map = [
        'sdb_ck' => 'Chính khóa',
        'sdb_nk' => 'Ngoại khóa',
        'sdb_tt' => 'Nhật kỳ'
    ];
    $ten_loai_so = $loai_so_map[$loai_minh_chung] ?? 'Minh chứng';

    $stmt_count = $db->prepare("SELECT COUNT(*) FROM so_nhat_ky_minh_chung WHERE nhat_ky_id = ? AND loai_minh_chung = ?");
    $stmt_count->execute([$nhat_ky['id'], $loai_minh_chung]);
    $exist_count = (int)$stmt_count->fetchColumn();
    $suffix = $exist_count > 0 ? (' (' . ($exist_count + 1) . ')') : '';

    $formatted_filename = sprintf("%s - %s - %s%s.%s",
        $nhat_ky['ten_lop'],
        $nhat_ky['ten_tuan'],
        $ten_loai_so,
        $suffix,
        $file_extension
    );

    $new_filename = uniqid('nk_') . '_' . time() . '.' . $file_extension;
    $cloud_key = "MinhChung/SoNhatKy/" . $nhat_ky['id'] . "/" . $loai_minh_chung . "/" . $new_filename;
    
    // Tải trực tiếp lên Cloudflare R2
    require_once __DIR__ . '/../lib/StorageService.php';
    $storage = new StorageService();
    $storage->upload($file['tmp_name'], $cloud_key);
    $url = $storage->getTemporaryUrl($cloud_key, '+60 minutes');

    $stmt_ins = $db->prepare("INSERT INTO so_nhat_ky_minh_chung (nhat_ky_id, loai_minh_chung, file_path, original_filename, file_type, storage_driver, cloud_key) VALUES (?, ?, ?, ?, ?, 'r2', ?)");
    $stmt_ins->execute([
        $nhat_ky['id'],
        $loai_minh_chung,
        $cloud_key,
        $formatted_filename,
        $file['type'] ?: 'image/' . $file_extension,
        $cloud_key
    ]);

    $new_id = $db->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Tải minh chứng thành công',
        'data' => [
            'id' => (string)$new_id,
            'url' => $url,
            'file_name' => $formatted_filename,
            'loai_minh_chung' => $loai_minh_chung
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
