<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('POST, OPTIONS');
zalo_handle_options();

// File: src/controllers/api_zalo_save_gpa_data.php
$payload = zalo_authenticate_request();
$student_id = $payload['student_id'];

// Lấy nam_hoc_id từ header hoặc query param
$nam_hoc_id = zalo_get_nam_hoc_id();

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['subjects_data'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
    exit();
}

$subjects_data_json = is_string($input['subjects_data']) ? $input['subjects_data'] : json_encode($input['subjects_data'], JSON_UNESCAPED_UNICODE);

try {
    $db = get_db_connection();
    
    // Tự động tạo bảng nếu chưa tồn tại
    $db->exec("
        CREATE TABLE IF NOT EXISTS hoc_sinh_ket_qua_hoc_tap (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hoc_sinh_id INT NOT NULL,
            nam_hoc_id INT NOT NULL,
            subjects_data LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci NOT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY student_year (hoc_sinh_id, nam_hoc_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;
    ");

    $stmt = $db->prepare("
        INSERT INTO hoc_sinh_ket_qua_hoc_tap (hoc_sinh_id, nam_hoc_id, subjects_data) 
        VALUES (?, ?, ?) 
        ON DUPLICATE KEY UPDATE subjects_data = VALUES(subjects_data)
    ");
    $stmt->execute([$student_id, $nam_hoc_id, $subjects_data_json]);

    echo json_encode(['success' => true, 'message' => 'Lưu dữ liệu thành công.']);
} catch (Exception $e) {
    http_response_code(500);
    zalo_api_error('Lỗi hệ thống, vui lòng thử lại sau.', 500, $e);
}
