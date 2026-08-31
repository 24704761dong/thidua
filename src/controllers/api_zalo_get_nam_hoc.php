<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('GET, OPTIONS');
zalo_handle_options();

// File: src/controllers/api_zalo_get_nam_hoc.php
// Lấy danh sách các năm học mà học sinh có dữ liệu học tập

$payload = zalo_authenticate_request();
$student_id = $payload['student_id'];
try {
    $db = get_db_connection();

    // 1. Lấy mã học sinh từ id
    $stmt = $db->prepare("SELECT ma_hoc_sinh FROM ho_so_hoc_sinh WHERE id = ?");
    $stmt->execute([$student_id]);
    $ma_hoc_sinh = $stmt->fetchColumn();

    if (!$ma_hoc_sinh) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy hồ sơ học sinh.']);
        exit();
    }

    // 2. Truy vấn các năm học mà học sinh này có trong bảng quatrinh_hoc_tap
    $query = "
        SELECT DISTINCT nh.id, nh.ten_nam_hoc
        FROM nam_hoc nh
        JOIN quatrinh_hoc_tap qt ON nh.id = qt.nam_hoc_id
        WHERE qt.ma_hoc_sinh = ?
        ORDER BY nh.id DESC
    ";

    $stmt_nh = $db->prepare($query);
    $stmt_nh->execute([$ma_hoc_sinh]);
    $nam_hoc_list = $stmt_nh->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $nam_hoc_list
    ]);

} catch (Exception $e) {
    http_response_code(500);
    zalo_api_error('Lỗi hệ thống, vui lòng thử lại sau.', 500, $e);
}
