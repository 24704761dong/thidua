<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('GET, OPTIONS');
zalo_handle_options();

$payload = zalo_authenticate_request();
$student_id = $payload['student_id'];
$nam_hoc_header = zalo_get_nam_hoc_id();

try {
    $db = get_db_connection();

    // Determine the exact nam_hoc_id to query
    $nam_hoc_to_query = $nam_hoc_header;
    if (!$nam_hoc_to_query) {
        $stmt_nh = $db->prepare("SELECT MAX(nam_hoc_id) FROM quatrinh_hoc_tap WHERE ma_hoc_sinh = (SELECT ma_hoc_sinh FROM ho_so_hoc_sinh WHERE id = ?)");
        $stmt_nh->execute([$student_id]);
        $nam_hoc_to_query = $stmt_nh->fetchColumn();
    }

    // Lấy thông tin quá trình học tập và mã học sinh trong năm học mục tiêu
    $stmt_info = $db->prepare("
        SELECT qt.id as qt_id, qt.lop_hoc_id, hs.ma_hoc_sinh 
        FROM ho_so_hoc_sinh hs
        LEFT JOIN quatrinh_hoc_tap qt ON hs.ma_hoc_sinh = qt.ma_hoc_sinh AND qt.nam_hoc_id = ?
        WHERE hs.id = ?
    ");
    $stmt_info->execute([$nam_hoc_to_query, $student_id]);
    $st_info = $stmt_info->fetch(PDO::FETCH_ASSOC);

    $qt_id = $st_info['qt_id'] ?? 0;
    $lop_hoc_id = $st_info['lop_hoc_id'] ?? 0;
    $ma_hs = $st_info['ma_hoc_sinh'] ?? '';

    $params = [$nam_hoc_to_query, $student_id, $qt_id, $ma_hs];
    $sql = "SELECT ngay_khen_thuong, ten_khen_thuong, cap_khen_thuong, ghi_chu, loai, ten_tap_the 
            FROM raw_khen_thuong 
            WHERE nam_hoc_id = ? 
            AND (
                (loai = 'ca_nhan' AND (hoc_sinh_id = ? OR hoc_sinh_id = ? OR hoc_sinh_id IN (SELECT id FROM quatrinh_hoc_tap WHERE ma_hoc_sinh = ?)))";

    if ($lop_hoc_id) {
        $sql .= " OR (loai = 'tap_the' AND lop_hoc_id = ?)";
        $params[] = $lop_hoc_id;
    }
    $sql .= ") ORDER BY ngay_khen_thuong DESC, id DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $achievements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $achievements
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    zalo_api_error('Lỗi hệ thống, vui lòng thử lại sau.', 500, $e);
} catch (Exception $e) {
    http_response_code(500);
    zalo_api_error('Lỗi hệ thống, vui lòng thử lại sau.', 500, $e);
}
