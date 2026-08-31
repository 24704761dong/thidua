<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('POST, GET, OPTIONS');
zalo_handle_options();

try {
    $db = get_db_connection();
    
    $payload = zalo_authenticate_request();
    $student_id = $payload['student_id'];
    $nam_hoc_header = zalo_get_nam_hoc_id();
    $nam_hoc_id = $nam_hoc_header;

    if (!$nam_hoc_id) {
        $stmt_hs = $db->prepare("
            SELECT (SELECT MAX(nam_hoc_id) FROM quatrinh_hoc_tap WHERE ma_hoc_sinh = hs.ma_hoc_sinh) as nam_hoc_id, lop_hoc_id
            FROM ho_so_hoc_sinh hs 
            WHERE hs.id = ?
        ");
        $stmt_hs->execute([$student_id]);
        $hs_info = $stmt_hs->fetch(PDO::FETCH_ASSOC);
        $nam_hoc_id = $hs_info['nam_hoc_id'] ?? null;
        $lop_hoc_id = $hs_info['lop_hoc_id'] ?? null;
    } else {
        $stmt_hs = $db->prepare("SELECT lop_hoc_id FROM hoc_sinh WHERE id = ?");
        $stmt_hs->execute([$student_id]);
        $lop_hoc_id = $stmt_hs->fetchColumn();
    }

    if (!$nam_hoc_id) {
        throw new Exception("Không xác định được năm học.");
    }
    if (!$lop_hoc_id) {
        throw new Exception("Không xác định được lớp học.");
    }

    $stmt_weeks = $db->prepare("SELECT id, ten_tuan, hoc_ky, ngay_bat_dau, ngay_ket_thuc FROM raw_tuan_hoc WHERE is_locked = 0 AND nam_hoc_id = ? ORDER BY ngay_bat_dau ASC");
    $stmt_weeks->execute([$nam_hoc_id]);
    $weeks = $stmt_weeks->fetchAll(PDO::FETCH_ASSOC);

    // Lấy trạng thái nộp lịch trực của lớp
    $stmt_status = $db->prepare("
        SELECT tuan_hoc_id, trang_thai 
        FROM dang_ky_truc_tuan 
        WHERE lop_hoc_id = ? AND trang_thai_luu_tru = 0
    ");
    $stmt_status->execute([$lop_hoc_id]);
    $statuses = [];
    while ($row = $stmt_status->fetch(PDO::FETCH_ASSOC)) {
        $statuses[$row['tuan_hoc_id']] = $row['trang_thai'];
    }

    foreach ($weeks as &$week) {
        $week['status'] = $statuses[$week['id']] ?? 'Chưa nộp';
    }

    echo json_encode(['success' => true, 'data' => $weeks]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
