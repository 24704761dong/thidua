<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('POST, OPTIONS');
zalo_handle_options();

$payload = zalo_authenticate_request();
$student_id = $payload['student_id'];
$nam_hoc_header = zalo_get_nam_hoc_id();

$input = json_decode(file_get_contents('php://input'), true);
$ma_kich_hoat = $input['ma_kich_hoat'] ?? null;

if (empty($ma_kich_hoat)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mã kích hoạt.']);
    exit();
}

try {
    $db = get_db_connection();
    
    if (!$nam_hoc_header) {
        $stmt_nh = $db->query("SELECT id FROM nam_hoc WHERE is_current = 1 LIMIT 1");
        $nam_hoc_header = $stmt_nh->fetchColumn();
    }
    
    $db->beginTransaction();
    $stmt = $db->prepare("SELECT * FROM ma_kich_hoat_ctv WHERE ma_kich_hoat = ? AND nam_hoc_id = ? FOR UPDATE");
    $stmt->execute([$ma_kich_hoat, $nam_hoc_header]);
    $code = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$code) { throw new Exception("Mã không hợp lệ."); }
    if ($code['trang_thai'] === 'inactive') { throw new Exception("Mã đã bị ngừng hoạt động hoặc đã hết hạn."); }

    $now = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
    if (!empty($code['thoi_gian_bat_dau'])) {
        $startTime = new DateTime($code['thoi_gian_bat_dau'], new DateTimeZone('Asia/Ho_Chi_Minh'));
        if ($now < $startTime) {
            throw new Exception("Chưa đến thời gian hiệu lực của mã.");
        }
        // Nếu đã đến thời gian bắt đầu nhưng trạng thái vẫn pending -> tự động chuyển sang active
        if ($code['trang_thai'] === 'pending') {
            $code['trang_thai'] = 'active';
            $db->prepare("UPDATE ma_kich_hoat_ctv SET trang_thai = 'active' WHERE id = ?")->execute([$code['id']]);
        }
    }

    if (!empty($code['thoi_gian_het_han'])) {
        $endTime = new DateTime($code['thoi_gian_het_han'], new DateTimeZone('Asia/Ho_Chi_Minh'));
        if ($now > $endTime) {
            $db->prepare("UPDATE ma_kich_hoat_ctv SET trang_thai = 'inactive' WHERE id = ?")->execute([$code['id']]);
            throw new Exception("Mã này đã hết hạn sử dụng.");
        }
    }
    
    $stmt_count = $db->prepare("SELECT COUNT(*) FROM lich_su_su_dung_ma_ctv WHERE ma_ctv_id = ?");
    $stmt_count->execute([$code['id']]);
    if ($stmt_count->fetchColumn() >= $code['so_luong_toi_da']) {
        throw new Exception("Mã đã hết lượt sử dụng.");
    }

    // Kiểm tra xem user này đã dùng mã này chưa
    $stmt_used = $db->prepare("SELECT id FROM lich_su_su_dung_ma_ctv WHERE ma_ctv_id = ? AND hoc_sinh_id = ?");
    $stmt_used->execute([$code['id'], $student_id]);
    if ($stmt_used->fetch()) {
        throw new Exception("Bạn đã kích hoạt mã này rồi.");
    }

    $stmt_student = $db->prepare("SELECT qt.lop_hoc_id, SUBSTR(lh.ten_lop, 1, 2) as khoi 
                                  FROM quatrinh_hoc_tap qt 
                                  JOIN lop_hoc lh ON qt.lop_hoc_id = lh.id 
                                  WHERE qt.ma_hoc_sinh = (SELECT ma_hoc_sinh FROM ho_so_hoc_sinh WHERE id = ?) AND qt.nam_hoc_id = ?");
    $stmt_student->execute([$student_id, $nam_hoc_header]);
    $student_info = $stmt_student->fetch(PDO::FETCH_ASSOC);
    
    if (!$student_info) {
        throw new Exception("Không tìm thấy thông tin lớp học của bạn trong năm học này.");
    }
    
    $is_eligible = false;
    if ($code['doi_tuong_ap_dung'] === 'all') $is_eligible = true;
    elseif (strpos($code['doi_tuong_ap_dung'], 'khoi_') === 0 && 'khoi_' . $student_info['khoi'] === $code['doi_tuong_ap_dung']) $is_eligible = true;
    elseif (strpos($code['doi_tuong_ap_dung'], 'lop_') === 0 && 'lop_' . $student_info['lop_hoc_id'] === $code['doi_tuong_ap_dung']) $is_eligible = true;
    if (!$is_eligible) { throw new Exception("Bạn không thuộc đối tượng áp dụng của mã này."); }
    
    $stmt_quyen = $db->prepare("SELECT quyen_truy_cap FROM ho_so_hoc_sinh WHERE id = ?");
    $stmt_quyen->execute([$student_id]);
    $current_permissions = json_decode($stmt_quyen->fetchColumn() ?: '{}', true);
    $current_permissions['nhap_vi_pham'] = true;

    $stmt_update = $db->prepare("UPDATE ho_so_hoc_sinh SET quyen_truy_cap = ? WHERE id = ?");
    $stmt_update->execute([json_encode($current_permissions), $student_id]);

    $stmt_log = $db->prepare("INSERT INTO lich_su_su_dung_ma_ctv (ma_ctv_id, hoc_sinh_id, ngay_kich_hoat) VALUES (?, ?, ?)");
    $stmt_log->execute([$code['id'], $student_id, date('Y-m-d H:i:s')]);

    $db->commit();

    echo json_encode(['success' => true, 'message' => 'Kích hoạt quyền thành công!', 'permissions' => $current_permissions]);
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) $db->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
