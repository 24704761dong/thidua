<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('GET, POST, OPTIONS');
zalo_handle_options();

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../lib/zalo_zmp_helper.php';

try {
    $db = get_db_connection();
    
    $payload = zalo_authenticate_request();
    $student_id = $payload['student_id'];

    $sql = "SELECT ls.id, m.ma_kich_hoat, ls.ngay_kich_hoat, m.trang_thai, m.thoi_gian_het_han 
            FROM lich_su_su_dung_ma_ctv ls
            JOIN ma_cong_tac_vien m ON ls.ma_ctv_id = m.id
            WHERE ls.hoc_sinh_id = ?
            ORDER BY ls.ngay_kich_hoat DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$student_id]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $history]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
