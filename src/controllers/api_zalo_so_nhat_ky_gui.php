<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';
require_once __DIR__ . '/../lib/firebase_helpers.php';
require_once __DIR__ . '/../lib/helpers.php';

zalo_api_cors_headers('POST, OPTIONS');
zalo_handle_options();

try {
    $db = get_db_connection();
    
    $payload = zalo_authenticate_request();
    $student_id = $payload['student_id'];
    $data = json_decode(file_get_contents('php://input'), true);
    
    $nhat_ky_id = $data['nhat_ky_id'] ?? null;
    $tuan_hoc_id = $data['tuan_hoc_id'] ?? null;
    if (!$nhat_ky_id && !$tuan_hoc_id) {
        throw new Exception("Thiếu thông tin sổ nhật ký.");
    }

    if ($nhat_ky_id) {
        $stmt_check = $db->prepare("SELECT snk.id, snk.trang_thai, t.ten_tuan, lh.ten_lop 
                                    FROM so_nhat_ky_online snk 
                                    JOIN raw_tuan_hoc t ON snk.tuan_hoc_id = t.id 
                                    JOIN raw_lop_hoc lh ON snk.lop_hoc_id = lh.id
                                    WHERE snk.id = ?");
        $stmt_check->execute([$nhat_ky_id]);
    } else {
        $stmt_check = $db->prepare("SELECT snk.id, snk.trang_thai, t.ten_tuan, lh.ten_lop 
                                    FROM so_nhat_ky_online snk 
                                    JOIN raw_tuan_hoc t ON snk.tuan_hoc_id = t.id 
                                    JOIN raw_lop_hoc lh ON snk.lop_hoc_id = lh.id
                                    WHERE snk.tuan_hoc_id = ? AND snk.nguoi_nhap_id = ?");
        $stmt_check->execute([$tuan_hoc_id, $student_id]);
    }
    $nhat_ky = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if (!$nhat_ky) {
        throw new Exception("Sổ nhật ký chưa được tạo. Vui lòng lưu nháp trước khi gửi.");
    }

    if ($nhat_ky['trang_thai'] !== 'nhap' && $nhat_ky['trang_thai'] !== 'tu_choi') {
        throw new Exception("Sổ nhật ký này đã gửi hoặc đã duyệt, không thể gửi lại.");
    }

    $stmt_up = $db->prepare("UPDATE so_nhat_ky_online SET trang_thai = 'da_gui', ngay_gui = NOW() WHERE id = ?");
    $stmt_up->execute([$nhat_ky['id']]);

    // Gửi thông báo
    try {
        $tieu_de = "Sổ nhật ký mới";
        $hs_msg = $nhat_ky['ten_lop'] . " vừa gửi sổ nhật ký của " . $nhat_ky['ten_tuan'] . " chờ duyệt.";
        
        // 1. Tạo thông báo trong hệ thống cho Admin
        $stmt_notify = $db->prepare("
            INSERT INTO thong_bao (loai_thong_bao, id_lien_quan, noi_dung, thoi_gian, da_xem) 
            VALUES ('so_nhat_ky_online', ?, ?, NOW(), 0)
        ");
        $stmt_notify->execute([$nhat_ky['id'], $hs_msg]);

        // 2. Tạo thông báo cho học sinh
        if ($student_id) {
            create_student_notification($db, $student_id, "Nộp sổ thành công", "Sổ nhật kỳ của " . $nhat_ky['ten_lop'] . " (" . $nhat_ky['ten_tuan'] . ") đã được gửi thành công. Vui lòng chờ duyệt.", 'so_nhat_ky_online');
        }

        // 3. Gửi FCM cho admin nếu có zalo_id
        $stmt_admins = $db->query("SELECT zalo_id FROM users WHERE vai_tro = 'admin' AND zalo_id IS NOT NULL");
        while ($admin = $stmt_admins->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($admin['zalo_id']) && function_exists('send_fcm_notification')) {
                send_fcm_notification($admin['zalo_id'], $tieu_de, $hs_msg, ['type' => 'so_nhat_ky_online']);
            }
        }
    } catch (Throwable $ignore) {
        error_log("Notification error in api_zalo_so_nhat_ky_gui: " . $ignore->getMessage());
    }

    echo json_encode(['success' => true, 'message' => 'Đã gửi sổ nhật ký lên Đoàn trường thành công.']);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
