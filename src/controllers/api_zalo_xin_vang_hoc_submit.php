<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('POST, OPTIONS');
zalo_handle_options();

error_reporting(0);
ob_start();

try {
    $db = get_db_connection();
    
    $payload = zalo_authenticate_request();
    $hoc_sinh_id = $payload['student_id'];
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $ly_do = trim($data['ly_do'] ?? '');
    $tu_ngay = trim($data['tu_ngay'] ?? '');
    $den_ngay = trim($data['den_ngay'] ?? '');
    $cloud_key = trim($data['cloud_key'] ?? '');
    
    $nam_hoc_header = zalo_get_nam_hoc_id();

    if (empty($ly_do)) {
        throw new Exception("Vui lòng nhập lý do xin vắng học.");
    }
    if (empty($tu_ngay) || empty($den_ngay)) {
        throw new Exception("Vui lòng chọn thời gian xin vắng học.");
    }
    
    if (strtotime($tu_ngay) > strtotime($den_ngay)) {
        throw new Exception("Ngày kết thúc không thể nhỏ hơn ngày bắt đầu.");
    }
    
    $sql = "INSERT INTO xin_vang_hoc (hoc_sinh_id, nam_hoc_id, ly_do, tu_ngay, den_ngay, cloud_key, trang_thai) 
            VALUES (?, ?, ?, ?, ?, ?, 0)";
            
    $stmt_insert = $db->prepare($sql);
    $stmt_insert->execute([
        $hoc_sinh_id,
        $nam_hoc_header,
        $ly_do,
        $tu_ngay,
        $den_ngay,
        $cloud_key ?: null
    ]);
    
    $new_id = $db->lastInsertId();

    // --- BẮT ĐẦU: GỬI THÔNG BÁO VÀ EMAIL ---
    require_once __DIR__ . '/../lib/helpers.php';
    
    // Lấy thông tin học sinh - Dùng JOIN đúng bảng
    $stmt_hs = $db->prepare("SELECT hs.ho_dem, hs.ten, lh.ten_lop, e.email 
                             FROM ho_so_hoc_sinh hs 
                             JOIN quatrinh_hoc_tap qt ON hs.ma_hoc_sinh = qt.ma_hoc_sinh
                             LEFT JOIN raw_lop_hoc lh ON qt.lop_hoc_id = lh.id 
                             LEFT JOIN email_hoc_sinh e ON hs.id = e.hoc_sinh_id 
                             WHERE hs.id = ? AND qt.nam_hoc_id = ?");
    $stmt_hs->execute([$hoc_sinh_id, $nam_hoc_header]);
    $hs = $stmt_hs->fetch(PDO::FETCH_ASSOC);
    
    if ($hs) {
        $ho_ten = trim($hs['ho_dem'] . ' ' . $hs['ten']);
        $ten_lop = $hs['ten_lop'] ?? 'Chưa xếp lớp';
        $email_hs = $hs['email'];

        // 1. Thông báo đến icon học sinh (Zalo Mini App)
        $tb_hs_tieu_de = "Đơn xin vắng học đã được gửi";
        $tb_hs_noi_dung = "Đơn xin vắng học của bạn (từ ngày " . date('d/m/Y', strtotime($tu_ngay)) . " đến " . date('d/m/Y', strtotime($den_ngay)) . ") đã được gửi thành công. Vui lòng chờ admin duyệt.";
        create_student_notification($db, $hoc_sinh_id, $tb_hs_tieu_de, $tb_hs_noi_dung, 'xin_vang_hoc');

        // 2. Thông báo mail đến học sinh
        if (!empty($email_hs)) {
            $mail_body = "<p>Chào <strong>{$ho_ten}</strong>,</p>
                          <p>Đơn xin vắng học của bạn đã được hệ thống ghi nhận thành công.</p>
                          <ul>
                              <li><strong>Lý do:</strong> " . htmlspecialchars($ly_do) . "</li>
                              <li><strong>Thời gian:</strong> " . date('d/m/Y', strtotime($tu_ngay)) . " - " . date('d/m/Y', strtotime($den_ngay)) . "</li>
                          </ul>
                          <p>Trạng thái hiện tại: <strong>Đang chờ duyệt</strong>.</p>";
            if (function_exists('queue_email')) {
                queue_email($email_hs, $ho_ten, "Xác nhận nộp đơn xin vắng học", $mail_body);
            }
        }

        // 3. Thông báo đến icon Admin
        $tb_admin_noi_dung = "Học sinh <strong>{$ho_ten}</strong> (Lớp {$ten_lop}) vừa nộp đơn xin vắng học mới.";
        $stmt_tb_admin = $db->prepare("INSERT INTO thong_bao (loai_thong_bao, id_lien_quan, noi_dung, thoi_gian, da_xem) VALUES ('xin_vang_hoc', ?, ?, NOW(), 0)");
        $stmt_tb_admin->execute([$new_id, $tb_admin_noi_dung]);
    }
    // --- KẾT THÚC: GỬI THÔNG BÁO VÀ EMAIL ---

    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'data' => ['id' => $new_id], 'message' => 'Đã gửi đơn xin vắng học thành công.']);

} catch (Exception $e) {
    ob_end_clean();
    zalo_api_error($e->getMessage(), 500, $e);
}
