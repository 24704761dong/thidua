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

    $stmt = $db->prepare("SELECT id, ten_vi_pham, nhom_vi_pham FROM raw_cau_hinh_vi_pham WHERE nam_hoc_id = ? ORDER BY nhom_vi_pham, ten_vi_pham");
    $stmt->execute([$nam_hoc_id]);
    $errors = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $errors]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
