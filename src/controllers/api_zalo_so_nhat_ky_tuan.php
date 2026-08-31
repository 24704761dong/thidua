<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('GET, OPTIONS');
zalo_handle_options();

try {
    $db = get_db_connection();
    
    $payload = zalo_authenticate_request();
    $student_id = $payload['student_id'];
    $nam_hoc_header = zalo_get_nam_hoc_id();
    $nam_hoc_id = $nam_hoc_header;

    if (!$nam_hoc_id) {
        $stmt_hs = $db->prepare("
            SELECT (SELECT MAX(nam_hoc_id) FROM quatrinh_hoc_tap WHERE ma_hoc_sinh = hs.ma_hoc_sinh) as nam_hoc_id 
            FROM ho_so_hoc_sinh hs 
            WHERE hs.id = ?
        ");
        $stmt_hs->execute([$student_id]);
        $nam_hoc_id = $stmt_hs->fetchColumn();
    }

    if (!$nam_hoc_id) {
        throw new Exception("Không xác định được năm học.");
    }

    // Lấy danh sách tuần
    $stmt_weeks = $db->prepare("SELECT id, id as tuan_hoc_id, ten_tuan, hoc_ky, ngay_bat_dau, ngay_bat_dau as tu_ngay, ngay_ket_thuc, ngay_ket_thuc as den_ngay, is_locked as trang_thai_khoa FROM raw_tuan_hoc WHERE nam_hoc_id = ? ORDER BY ngay_bat_dau ASC");
    $stmt_weeks->execute([$nam_hoc_id]);
    $weeks = $stmt_weeks->fetchAll(PDO::FETCH_ASSOC);

    // Get current week
    $now = date('Y-m-d');
    
    // Lấy thông tin nhật ký để biết trạng thái
    $stmt_status = $db->prepare("
        SELECT tuan_hoc_id, trang_thai 
        FROM so_nhat_ky_online 
        WHERE nguoi_nhap_id = ?
    ");
    $stmt_status->execute([$student_id]);
    $status_map = [];
    while ($row = $stmt_status->fetch(PDO::FETCH_ASSOC)) {
        $status_map[$row['tuan_hoc_id']] = $row['trang_thai'];
    }

    foreach ($weeks as &$week) {
        $week['is_current'] = ($now >= $week['ngay_bat_dau'] && $now <= $week['ngay_ket_thuc']) ? true : false;
        $status = $status_map[$week['id']] ?? 'chua_nhap';
        $week['trang_thai'] = $status;
        $week['trang_thai_nhat_ky'] = $status;
    }

    echo json_encode(['success' => true, 'data' => $weeks]);

} catch (Exception $e) {
    http_response_code(200);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
