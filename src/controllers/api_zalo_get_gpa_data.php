<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('GET, OPTIONS');
zalo_handle_options();

// File: src/controllers/api_zalo_get_gpa_data.php
$payload = zalo_authenticate_request();
$student_id = $payload['student_id'];

// Lấy nam_hoc_id từ header hoặc query param
$nam_hoc_id = zalo_get_nam_hoc_id();

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

    // Nếu có tham số reset=1, xóa sạch dữ liệu cũ của học sinh này
    if (isset($_GET['reset']) && $_GET['reset'] == 1) {
        $stmt = $db->prepare("DELETE FROM hoc_sinh_ket_qua_hoc_tap WHERE hoc_sinh_id = ? AND nam_hoc_id = ?");
        $stmt->execute([$student_id, $nam_hoc_id]);
        echo json_encode(['success' => true, 'message' => 'Đã xóa sạch dữ liệu điểm.']);
        exit();
    }

    $stmt = $db->prepare("SELECT subjects_data, updated_at FROM hoc_sinh_ket_qua_hoc_tap WHERE hoc_sinh_id = ? AND nam_hoc_id = ?");
    $stmt->execute([$student_id, $nam_hoc_id]);
    $row = $stmt->fetch();

    if ($row) {
        echo json_encode([
            'success' => true, 
            'data' => json_decode($row['subjects_data'], true),
            'updated_at' => $row['updated_at']
        ]);
    } else {
        echo json_encode([
            'success' => true, 
            'data' => null,
            'message' => 'Chưa có dữ liệu, sử dụng mặc định'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    zalo_api_error('Lỗi hệ thống, vui lòng thử lại sau.', 500, $e);
}
