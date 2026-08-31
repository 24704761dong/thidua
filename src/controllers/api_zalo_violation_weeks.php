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

    // Lấy danh sách tuần (không quan trọng khóa hay chưa vì vi phạm có thể nhập cho tuần cũ nếu cần, nhưng tạm theo tuần học chưa khóa)
    // Actually, vi pham might be entered for past weeks. Let's just get all weeks of this year, or maybe just non-locked weeks?
    // Let's get all non-locked weeks
    $stmt_weeks = $db->prepare("SELECT id, ten_tuan, hoc_ky, ngay_bat_dau, ngay_ket_thuc FROM raw_tuan_hoc WHERE is_locked = 0 AND nam_hoc_id = ? ORDER BY ngay_bat_dau ASC");
    $stmt_weeks->execute([$nam_hoc_id]);
    $weeks = $stmt_weeks->fetchAll(PDO::FETCH_ASSOC);

    // Đếm số lượng vi phạm nháp và đã gửi
    $stmt_status = $db->prepare("
        SELECT tuan_hoc_id, trang_thai_gui, COUNT(*) as cnt 
        FROM vi_pham_tam_thoi 
        WHERE nguoi_nhap_id = ?
        GROUP BY tuan_hoc_id, trang_thai_gui
    ");
    $stmt_status->execute([$student_id]);
    $counts = [];
    while ($row = $stmt_status->fetch(PDO::FETCH_ASSOC)) {
        $counts[$row['tuan_hoc_id']][$row['trang_thai_gui']] = $row['cnt'];
    }

    // Gắn trạng thái vào tuần
    foreach ($weeks as &$week) {
        $week['draft_count'] = $counts[$week['id']]['nhap'] ?? 0;
        $week['pending_count'] = $counts[$week['id']]['da_gui'] ?? 0;
    }

    echo json_encode(['success' => true, 'data' => $weeks]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
