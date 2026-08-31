<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('POST, OPTIONS');
zalo_handle_options();

try {
    $db = get_db_connection();
    
    $payload = zalo_authenticate_request();
    $student_id = $payload['student_id'];
    $data = json_decode(file_get_contents('php://input'), true);
    
    $hoc_sinh_id = $data['hoc_sinh_id'] ?? null;
    if (empty($hoc_sinh_id)) {
        throw new Exception("Lỗi: Không tìm thấy ID học sinh. Vui lòng chọn học sinh hợp lệ trước khi thêm.");
    }
    
    $ho_ten_raw = trim($data['ho_ten'] ?? '');
    $ten_lop_raw = trim($data['ten_lop'] ?? '');
    $ghi_chu = trim($data['ghi_chu'] ?? 'KL&LĐ');
    
    $sql = "INSERT INTO vi_pham_tam_thoi (tuan_hoc_id, nguoi_nhap_id, hoc_sinh_id, vi_pham_id, ngay_vi_pham, ghi_chu, raw_ho_ten, raw_ten_lop, trang_thai_gui, thoi_gian_nhap) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'nhap', ?)";
            
    $stmt_insert = $db->prepare($sql);
    $stmt_insert->execute([
        $data['tuan_hoc_id'],
        $student_id,
        $hoc_sinh_id,
        $data['vi_pham_id'],
        $data['ngay_vi_pham'],
        $ghi_chu,
        $ho_ten_raw,
        $ten_lop_raw,
        date('Y-m-d H:i:s')
    ]);
    
    $new_id = $db->lastInsertId();

    $stmt_get = $db->prepare("
        SELECT vptt.*, ho_so.ma_hoc_sinh, ho_so.trang_thai_hoc_tap, (CONCAT(ho_so.ho_dem, ' ', ho_so.ten)) as ho_ten_day_du, lh.ten_lop, chvp.ten_vi_pham, chvp.diem_tru
        FROM vi_pham_tam_thoi vptt
        LEFT JOIN quatrinh_hoc_tap qt ON vptt.hoc_sinh_id = qt.id
        LEFT JOIN ho_so_hoc_sinh ho_so ON qt.ma_hoc_sinh = ho_so.ma_hoc_sinh
        LEFT JOIN raw_lop_hoc lh ON qt.lop_hoc_id = lh.id
        LEFT JOIN raw_cau_hinh_vi_pham chvp ON vptt.vi_pham_id = chvp.id
        WHERE vptt.id = ?
    ");
    $stmt_get->execute([$new_id]);
    $new_violation_data = $stmt_get->fetch(PDO::FETCH_ASSOC);

    if (!$new_violation_data) {
        throw new Exception("Thêm thành công nhưng không thể lấy dữ liệu vi phạm mới (ID: $new_id).");
    }

    echo json_encode(['success' => true, 'data' => $new_violation_data, 'message' => 'Đã thêm vi phạm vào bản nháp.']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
