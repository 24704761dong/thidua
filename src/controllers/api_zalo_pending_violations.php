<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('POST, GET, OPTIONS');
zalo_handle_options();

try {
    $db = get_db_connection();
    
    $payload = zalo_authenticate_request();
    $student_id = $payload['student_id'];
    $tuan_hoc_id = $_GET['tuan_id'] ?? null;

    if (!$tuan_hoc_id) {
        throw new Exception("Thiếu thông tin tuần học.");
    }

    $stmt_nhap = $db->prepare("
        SELECT vptt.*, ho_so.ma_hoc_sinh, ho_so.trang_thai_hoc_tap, (CONCAT(ho_so.ho_dem, ' ', ho_so.ten)) as ho_ten_day_du, lh.ten_lop, chvp.ten_vi_pham, chvp.diem_tru
        FROM vi_pham_tam_thoi vptt
        LEFT JOIN quatrinh_hoc_tap qt ON vptt.hoc_sinh_id = qt.id
        LEFT JOIN ho_so_hoc_sinh ho_so ON qt.ma_hoc_sinh = ho_so.ma_hoc_sinh
        LEFT JOIN raw_lop_hoc lh ON qt.lop_hoc_id = lh.id
        LEFT JOIN raw_cau_hinh_vi_pham chvp ON vptt.vi_pham_id = chvp.id
        WHERE vptt.nguoi_nhap_id = ? AND vptt.tuan_hoc_id = ? AND vptt.trang_thai_gui = 'nhap'
        ORDER BY vptt.id DESC
    ");
    $stmt_nhap->execute([$student_id, $tuan_hoc_id]);
    $pending_violations = $stmt_nhap->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $pending_violations]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
