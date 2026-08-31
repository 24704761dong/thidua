<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('GET, OPTIONS');
zalo_handle_options();

$payload = zalo_authenticate_request();
$student_id = $payload['student_id'];
$nam_hoc_header = zalo_get_nam_hoc_id();

try {
    $db = get_db_connection();

    $params = [$student_id];
    $sql = "SELECT vp.ngay_vi_pham, ch.ten_vi_pham, ch.diem_tru, vp.ghi_chu, th.ten_tuan 
            FROM vi_pham_hoc_sinh vp 
            JOIN raw_cau_hinh_vi_pham ch ON vp.vi_pham_id = ch.id 
            JOIN tuan_hoc th ON vp.tuan_hoc_id = th.id 
            JOIN quatrinh_hoc_tap qt ON vp.hoc_sinh_id = qt.id
            JOIN ho_so_hoc_sinh hs ON qt.ma_hoc_sinh = hs.ma_hoc_sinh
            WHERE hs.id = ?";

    if ($nam_hoc_header) {
        $sql .= " AND th.nam_hoc_id = ?";
        $params[] = $nam_hoc_header;
    } else {
        $sql .= " AND th.nam_hoc_id = (SELECT MAX(nam_hoc_id) FROM quatrinh_hoc_tap WHERE ma_hoc_sinh = (SELECT ma_hoc_sinh FROM ho_so_hoc_sinh WHERE id = ?))";
        $params[] = $student_id;
    }

    $sql .= " ORDER BY vp.ngay_vi_pham DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $violations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $violations
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    zalo_api_error('Lỗi hệ thống, vui lòng thử lại sau.', 500, $e);
} catch (Exception $e) {
    http_response_code(500);
    zalo_api_error('Lỗi hệ thống, vui lòng thử lại sau.', 500, $e);
}
