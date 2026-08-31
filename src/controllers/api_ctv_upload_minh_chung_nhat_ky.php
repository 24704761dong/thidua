<?php
// File: src/controllers/api_ctv_upload_minh_chung_nhat_ky.php (BẢN SỬA LỖI & THÊM VALIDATION V3 - 3MB LIMIT)
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

// --- Upload debug logging ---
function upload_log($message, $context = []) {
    $log_file = __DIR__ . '/../../logs/app.log';
    $timestamp = date('Y-m-d H:i:s');
    $payload = $context ? ' | ' . json_encode($context, JSON_UNESCAPED_SLASHES) : '';
    @file_put_contents($log_file, "[{$timestamp}] [UPLOAD MINH CHUNG] {$message}{$payload}\n", FILE_APPEND);
}

// ==== KIỂM TRA PHÂN QUYỀN ====
if (!isset($_SESSION['student_id']) || !($_SESSION['student_permissions']['so_nhat_ky_online'] ?? false)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập.']);
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$nhat_ky_id = $_POST['nhat_ky_id'] ?? null;
$loai_minh_chung = $_POST['loai_minh_chung'] ?? null;
$file = $_FILES['file'] ?? null;

upload_log('Request received', [
    'student_id' => $_SESSION['student_id'] ?? null,
    'nhat_ky_id' => $nhat_ky_id,
    'loai_minh_chung' => $loai_minh_chung,
    'file_name' => $file['name'] ?? null,
    'file_size' => $file['size'] ?? null,
    'file_type' => $file['type'] ?? null,
    'file_error' => $file['error'] ?? null,
    'tmp_name' => $file['tmp_name'] ?? null,
    'is_uploaded_file' => isset($file['tmp_name']) ? is_uploaded_file($file['tmp_name']) : null,
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'memory_limit' => ini_get('memory_limit'),
    'max_file_uploads' => ini_get('max_file_uploads')
]);

if (!$nhat_ky_id || !$loai_minh_chung || !$file) {
    upload_log('Invalid request payload');
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
    exit();
}

if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
    $upload_error_map = [
        UPLOAD_ERR_INI_SIZE => 'Kích thước file vượt quá giới hạn cấu hình máy chủ.',
        UPLOAD_ERR_FORM_SIZE => 'Kích thước file vượt quá giới hạn của biểu mẫu.',
        UPLOAD_ERR_PARTIAL => 'File tải lên chưa hoàn tất, vui lòng thử lại.',
        UPLOAD_ERR_NO_FILE => 'Bạn chưa chọn file để tải lên.',
        UPLOAD_ERR_NO_TMP_DIR => 'Máy chủ thiếu thư mục tạm, vui lòng thử lại sau.',
        UPLOAD_ERR_CANT_WRITE => 'Máy chủ không thể ghi file, vui lòng thử lại sau.',
        UPLOAD_ERR_EXTENSION => 'Tải lên bị chặn bởi cấu hình máy chủ.'
    ];

    $error_code = $file['error'] ?? UPLOAD_ERR_NO_FILE;
    $error_message = $upload_error_map[$error_code] ?? 'Lỗi tải lên không xác định.';

    upload_log('Upload error from PHP', [
        'error_code' => $error_code,
        'error_message' => $error_message
    ]);

    http_response_code(in_array($error_code, [UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE, UPLOAD_ERR_EXTENSION], true) ? 500 : 400);
    echo json_encode(['success' => false, 'message' => $error_message]);
    exit();
}

$file_size = $file['size'];
$max_file_size_bytes = 7 * 1024 * 1024; 

if ($file_size > $max_file_size_bytes) {
    upload_log('Rejected by app size limit', [
        'file_size' => $file_size,
        'max_file_size_bytes' => $max_file_size_bytes
    ]);
    http_response_code(400); 
    echo json_encode([
        'success' => false, 
        'message' => 'Lỗi: Kích thước file vượt quá 7MB. Vui lòng nén file hoặc chọn file khác.'
    ]);
    exit();
}

$original_filename = basename($file["name"]);
$file_extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
// Cung cấp một MIME type mặc định an toàn nếu trình duyệt không gửi
$file_type = $file['type'] ?? 'application/octet-stream'; 

// Danh sách các đuôi file (extension) được phép
$allowed_extensions = [
    // Ảnh
    'jpg', 'jpeg', 'png', 'gif', 'webp', 'heic', 
    // Văn bản & PDF
    'pdf', 'doc', 'docx',
    // Bảng tính
    'xls', 'xlsx', 
    // Trình chiếu
    'ppt', 'pptx',
    // Đa phương tiện
    'mp4', 'mp3',
    // Nén
    'rar', 'zip'
];

// Kiểm tra đuôi file
if (!in_array($file_extension, $allowed_extensions)) {
    // Nếu file không được phép, ném lỗi
    http_response_code(400); // Bad Request
    echo json_encode([
        'success' => false, 
        'message' => "Định dạng file .{$file_extension} không được chấp nhận."
    ]);
    exit();
}

// Nâng cấp: Nếu browser báo là file lạ, nhưng đuôi file là ảnh -> tin đuôi file
$image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heic'];
if (($file_type === 'application/octet-stream' || empty($file_type)) && in_array($file_extension, $image_extensions)) {
    $file_type = 'image/' . $file_extension;
}
// === KẾT THÚC NÂNG CẤP V2 ===


try {
    $db = get_db_connection();

    // === LẤY THÔNG TIN TUẦN VÀ LỚP ===
    $current_nam_hoc = $_SESSION['current_nam_hoc_id'] ?? 1;
    $stmt_info = $db->prepare("
        SELECT t.ten_tuan, l.ten_lop 
        FROM so_nhat_ky_online s 
        JOIN raw_tuan_hoc t ON s.tuan_hoc_id = t.id AND t.nam_hoc_id = ?
        JOIN raw_lop_hoc l ON s.lop_hoc_id = l.id AND l.nam_hoc_id = ?
        WHERE s.id = ? AND s.nguoi_nhap_id = ?
    ");
    $stmt_info->execute([$current_nam_hoc, $current_nam_hoc, $nhat_ky_id, $_SESSION['student_id']]);
    $info = $stmt_info->fetch();
    if (!$info) throw new Exception("Không tìm thấy thông tin sổ nhật kỳ.");

    // === TẠO THƯ MỤC LƯU FILE ===
    $physical_upload_dir = __DIR__ . '/../../public/minhchung/';
    if (!is_dir($physical_upload_dir)) {
        mkdir($physical_upload_dir, 0777, true);
    }

    $target_file_url_base = 'public/minhchung/';

    // === HÀM LỌC TÊN FILE ===
    function sanitize_for_filename($string) { 
        return preg_replace('/[\s\/\\:*?"<>|]+/', '-', $string); 
    }

    $loai_so_map = [
        'sdb_tt' => 'Tăng tiết', 
        'sdb_ck' => 'Chính khóa', 
        'sdb_nk' => 'Ngoại khóa', 
        'khac' => 'Nhật kỳ'
    ];
    $ten_loai_so = $loai_so_map[$loai_minh_chung] ?? 'Unknown';

    // === KIỂM TRA FILE GỐC ===
    if (empty($original_filename) || $original_filename === '.') {
        throw new Exception("Tên file không hợp lệ.");
    }

    $filename_base = pathinfo($original_filename, PATHINFO_FILENAME);
    $new_user_friendly_name = sprintf("%s - %s - %s.%s",
        sanitize_for_filename($info['ten_lop']),
        sanitize_for_filename($info['ten_tuan']),
        sanitize_for_filename($ten_loai_so),
        $file_extension
    );

    $unique_server_filename = uniqid() . '_' . $new_user_friendly_name;
    $target_file_physical = $physical_upload_dir . $unique_server_filename;
    $final_url_for_db = $target_file_url_base . $unique_server_filename;

    // === DI CHUYỂN FILE LÊN SERVER ===
    if (move_uploaded_file($file["tmp_name"], $target_file_physical)) {
        // === BẮT ĐẦU CODE TẠO THUMBNAIL (Đã sửa) ===
        $thumbnail_path_db = null;
        $thumbnail_physical_path = '';

        // Chỉ tạo thumbnail nếu là ảnh VÀ CÓ HỖ TRỢ GD
        // Sử dụng $file_type (đã chuẩn hóa) thay vì $file['type']
        if (strpos($file_type, 'image/') === 0 && function_exists('gd_info')) {
            try {
                $max_thumb_width = 300; 
                $source_image = null;

                // Sử dụng $file_type (đã chuẩn hóa)
                if ($file_type == 'image/jpeg' || $file_type == 'image/jpg') {
                    $source_image = imagecreatefromjpeg($target_file_physical);
                } elseif ($file_type == 'image/png') {
                    $source_image = imagecreatefrompng($target_file_physical);
                } elseif ($file_type == 'image/gif') {
                    $source_image = imagecreatefromgif($target_file_physical);
                }
                // Bỏ qua .heic vì GD mặc định không đọc được

                if ($source_image) {
                    $orig_width = imagesx($source_image);
                    $orig_height = imagesy($source_image);

                    if ($orig_width > $max_thumb_width) {
                        $thumb_height = floor($orig_height * ($max_thumb_width / $orig_width));
                        $thumbnail_image = imagecreatetruecolor($max_thumb_width, $thumb_height);

                        // Xử lý PNG transparency
                        if ($file_type == 'image/png') {
                            imagealphablending($thumbnail_image, false);
                            imagesavealpha($thumbnail_image, true);
                            $transparent = imagecolorallocatealpha($thumbnail_image, 255, 255, 255, 127);
                            imagefilledrectangle($thumbnail_image, 0, 0, $max_thumb_width, $thumb_height, $transparent);
                        }

                        imagecopyresampled($thumbnail_image, $source_image, 0, 0, 0, 0, 
                            $max_thumb_width, $thumb_height, $orig_width, $orig_height);

                        // Tạo tên file thumbnail
                        $path_parts = pathinfo($unique_server_filename);
                        $thumb_filename = $path_parts['filename'] . '_thumb.' . $path_parts['extension'];
                        $thumbnail_physical_path = $physical_upload_dir . $thumb_filename;

                        // Lưu ảnh thumbnail (sử dụng $file_type)
                        if ($file_type == 'image/jpeg' || $file_type == 'image/jpg') {
                            imagejpeg($thumbnail_image, $thumbnail_physical_path, 75);
                        } elseif ($file_type == 'image/png') {
                            imagepng($thumbnail_image, $thumbnail_physical_path, 6);
                        } elseif ($file_type == 'image/gif') {
                            imagegif($thumbnail_image, $thumbnail_physical_path);
                        }

                        $thumbnail_path_db = $target_file_url_base . $thumb_filename;
                        imagedestroy($thumbnail_image);
                    }

                    imagedestroy($source_image);
                }
            } catch (Exception $imgEx) {
                error_log("Lỗi tạo thumbnail cho {$original_filename}: " . $imgEx->getMessage());
                $thumbnail_path_db = null;
            }
        }
        // === KẾT THÚC CODE TẠO THUMBNAIL ===

        // === GHI DỮ LIỆU VÀO DB (Đã sửa) ===
        $stmt = $db->prepare("
            INSERT INTO so_nhat_ky_minh_chung 
            (nhat_ky_id, loai_minh_chung, file_path, original_filename, file_type, thumbnail_path) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $nhat_ky_id,
            $loai_minh_chung,
            $final_url_for_db,
            $new_user_friendly_name,
            $file_type, // <-- SỬ DỤNG BIẾN ĐÃ CHUẨN HÓA
            $thumbnail_path_db
        ]);

        $proof_id = $db->lastInsertId();

        // === TRẢ VỀ JSON (Đã sửa) ===
        echo json_encode([
            'success' => true,
            'proof' => [
                'id' => $proof_id,
                'file_path' => $final_url_for_db,
                'original_filename' => $new_user_friendly_name,
                'file_type' => $file_type, // <-- SỬ DỤNG BIẾN ĐÃ CHUẨN HÓA
                'thumbnail_path' => $thumbnail_path_db 
            ]
        ]);

    } else {
        $last_error = error_get_last();
        upload_log('move_uploaded_file failed', [
            'target_file_physical' => $target_file_physical,
            'last_error' => $last_error,
            'dir_writable' => is_writable($physical_upload_dir),
            'disk_free_bytes' => @disk_free_space($physical_upload_dir)
        ]);
        throw new Exception("Lỗi khi di chuyển file đã tải lên. Vui lòng kiểm tra quyền ghi của thư mục trên server.");
    }

} catch (Exception $e) {
    upload_log('Exception caught', [
        'message' => $e->getMessage()
    ]);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi Server: ' . $e->getMessage()]);
}
?>