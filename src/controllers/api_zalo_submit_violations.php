<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('POST, OPTIONS');
zalo_handle_options();


require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/firebase_helpers.php';

function generate_ctv_submission_email($ctv_name, $ctv_class, $week_name) {
    $email_id = date('YmdHis');
    $logo_url = "https://c3binhson.edu.vn/thidua/public/assets/img/22logoapp.png";

    $status_html = '<span style="color: #f97316; font-weight: 600;">⏳ Chờ duyệt</span>';
    $subject = "[Cần Duyệt Mới] Lớp {$ctv_class} vừa nộp báo cáo vi phạm {$week_name}";
    $scheme = $_SERVER['REQUEST_SCHEME'] ?? 'https';
    $host = $_SERVER['HTTP_HOST'] ?? 'c3binhson.edu.vn';
    $link = "{$scheme}://{$host}/thidua/admin/trung-tam-duyet";

    $body = <<<HTML
    <div style="font-family: Inter, Arial, sans-serif; max-width: 640px; margin: auto; background: #f4f7f9; padding: 28px 20px; border-radius: 12px; border: 1px solid #e5e7eb;">
        <div style="text-align: center; margin-bottom: 24px;">
            <img src="{$logo_url}" alt="Logo THPT Bình Sơn" style="height: 60px; max-width: 180px; object-fit: contain; margin-bottom: 12px;">
            <h2 style="color: #2563eb; margin: 0;">THÔNG BÁO VI PHẠM MỚI</h2>
            <p style="font-size: 0.95rem; color: #4b5563; margin-top: 8px;">
                Một báo cáo vi phạm vừa được gửi lên qua Zalo App.
            </p>
        </div>

        <div style="background-color: #ffffff; padding: 24px; border-radius: 12px; border: 1px solid #e5e7eb; margin-bottom: 24px;">
            <p style="margin: 0 0 16px; font-size: 1rem; color: #1f2937;">Xin chào Quản Trị Viên,</p>
            <p style="margin-bottom: 20px; font-size: 0.95rem; color: #374151;">
                Hệ thống ghi nhận một <strong>báo cáo vi phạm</strong> mới được gửi lên với thông tin chi tiết:
            </p>
            <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.95rem;">
                <li style="padding: 10px; background-color: #f9fafb; border-radius: 6px; margin-bottom: 8px;">
                    <strong>👤 Người gửi:</strong>
                    <span style="float: right; color: #111827;">{$ctv_name}</span>
                </li>
                <li style="padding: 10px; background-color: #f9fafb; border-radius: 6px; margin-bottom: 8px;">
                    <strong>🏫 Lớp:</strong>
                    <span style="float: right; color: #111827;">{$ctv_class}</span>
                </li>
                <li style="padding: 10px; background-color: #f9fafb; border-radius: 6px; margin-bottom: 8px;">
                    <strong>📅 Báo cáo cho:</strong>
                    <span style="float: right; color: #111827;">{$week_name}</span>
                </li>
                <li style="padding: 10px; border-top: 1px solid #f3f4f6;">
                    <strong>📌 Trạng thái:</strong>
                    <span style="float: right;">{$status_html}</span>
                </li>
            </ul>

            <div style="text-align: center; margin-top: 28px;">
                <a href="{$link}" 
                   style="background-color: #2563eb; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: 600; display: inline-block; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    🔍 Xem chi tiết và xử lý
                </a>
            </div>
        </div>

        <div style="font-size: 0.9rem; color: #334155; text-align: center; background-color: #e0f2fe; padding: 12px 16px; border-radius: 8px; border: 1px solid #bae6fd;">
            📝 Báo cáo được gửi bởi <strong>{$ctv_name}</strong> – Lớp <strong>{$ctv_class}</strong> cho tuần <strong>{$week_name}</strong>.<br>
            Trạng thái hiện tại: {$status_html}.
        </div>

        <hr style="margin: 28px auto; border: none; border-top: 1px solid #e5e7eb; width: 80%;">

        <p style="text-align: center; font-size: 0.85rem; color: #9ca3af; line-height: 1.6; margin-top: 24px;">
            Trân trọng,<br>
            <span style="font-weight: 600; color: #6b7280;">Hệ thống quản lý thi đua</span><br>
            <span style="font-style: italic; color:#6b7280;">Trường THPT Bình Sơn</span><br>
            <span style="font-size: 0.75rem; color: #cbd5e1;">Email ID: {$email_id}</span>
        </p>
    </div>
    HTML;

    return [
        'subject' => $subject,
        'body' => $body
    ];
}

try {
    $db = get_db_connection();
    
    $payload = zalo_authenticate_request();
    $student_id = $payload['student_id'];
    $data = json_decode(file_get_contents('php://input'), true);
    $tuan_hoc_id = $data['tuan_hoc_id'] ?? null;
    
    // 1. Kiểm tra danh sách vi phạm đang nháp
    $stmt_check = $db->prepare("SELECT COUNT(*) FROM vi_pham_tam_thoi WHERE nguoi_nhap_id = ? AND tuan_hoc_id = ? AND trang_thai_gui = 'nhap'");
    $stmt_check->execute([$student_id, $tuan_hoc_id]);
    $draft_count = (int)$stmt_check->fetchColumn();
    if ($draft_count === 0) {
        throw new Exception("Không có vi phạm nào đang nháp để gửi.");
    }

    // 2. BẮT BUỘC có ít nhất 1 ảnh minh chứng chưa gán batch
    $stmt_count_proofs = $db->prepare("SELECT COUNT(*) FROM minh_chung_vi_pham WHERE nguoi_nhap_id = ? AND nguoi_nhap_type = 'student' AND tuan_hoc_id = ? AND batch_id IS NULL");
    $stmt_count_proofs->execute([$student_id, $tuan_hoc_id]);
    $pending_proofs_count = (int)$stmt_count_proofs->fetchColumn();

    if ($pending_proofs_count === 0) {
        throw new Exception("Vui lòng tải lên ít nhất 1 ảnh minh chứng trước khi gửi báo cáo vi phạm.");
    }
    
    $batch_id = 'B_' . $tuan_hoc_id . '_' . $student_id . '_' . time();
    $stmt = $db->prepare("UPDATE vi_pham_tam_thoi SET trang_thai_gui = 'da_gui', batch_id = ?, thoi_gian_gui = NOW() WHERE nguoi_nhap_id = ? AND tuan_hoc_id = ? AND trang_thai_gui = 'nhap'");
    $stmt->execute([$batch_id, $student_id, $tuan_hoc_id]);
    $affected = $stmt->rowCount();
    
    if ($affected > 0) {
        // Link any unassigned proofs to this batch
        $stmt_proof = $db->prepare("UPDATE minh_chung_vi_pham SET batch_id = ? WHERE nguoi_nhap_id = ? AND nguoi_nhap_type = 'student' AND tuan_hoc_id = ? AND batch_id IS NULL");
        $stmt_proof->execute([$batch_id, $student_id, $tuan_hoc_id]);

        // Lấy thông tin CTV để gửi thông báo
        $stmt_ctv = $db->prepare("
            SELECT (CONCAT(hs.ho_dem, ' ', hs.ten)) as ten_ctv, lh.ten_lop, t.ten_tuan
            FROM ho_so_hoc_sinh hs
            JOIN quatrinh_hoc_tap qt ON hs.ma_hoc_sinh = qt.ma_hoc_sinh
            JOIN raw_lop_hoc lh ON qt.lop_hoc_id = lh.id
            JOIN tuan_hoc t ON t.id = ?
            WHERE hs.id = ?
            ORDER BY qt.nam_hoc_id DESC LIMIT 1
        ");
        $stmt_ctv->execute([$tuan_hoc_id, $student_id]);
        $ctv_info = $stmt_ctv->fetch(PDO::FETCH_ASSOC);

        if ($ctv_info) {
            // 1. Ghi thông báo cho Admin
            $noi_dung_tb = "Zalo: " . $ctv_info['ten_ctv'] . " (Lớp " . $ctv_info['ten_lop'] . ") vừa gửi $affected vi phạm của " . $ctv_info['ten_tuan'] . " chờ duyệt.";
            $stmt_tb = $db->prepare("INSERT INTO thong_bao (loai_thong_bao, id_lien_quan, noi_dung, thoi_gian, da_xem) VALUES ('vi_pham_ctv', ?, ?, NOW(), 0)");
            $stmt_tb->execute([$student_id, $noi_dung_tb]);

            // 2. Ghi thông báo cho Học sinh (chuông Zalo)
            $tieu_de_hs = "Gửi vi phạm thành công";
            $noi_dung_hs = "Bạn đã gửi $affected vi phạm của " . $ctv_info['ten_tuan'] . " lên Đoàn trường thành công. Admin sẽ sớm xem xét và duyệt.";
            create_student_notification($db, $student_id, $tieu_de_hs, $noi_dung_hs, 'gui_vi_pham');
            
            // FCM Push Notification
            send_fcm_notification($student_id, $tieu_de_hs, $noi_dung_hs, [
                'type' => 'gui_vi_pham',
                'url' => '/notifications'
            ]);

            // 3. Gửi email cho các Admin
            try {
                $stmt_admins = $db->query("SELECT email, ho_ten FROM users WHERE vai_tro = 'admin'");
                $admins = $stmt_admins->fetchAll(PDO::FETCH_ASSOC);

                $email_log = date('Y-m-d H:i:s') . " - Tìm thấy " . count($admins) . " admin\n";

                if (!empty($admins)) {
                    $email_content = generate_ctv_submission_email($ctv_info['ten_ctv'], $ctv_info['ten_lop'], $ctv_info['ten_tuan']);
                    foreach ($admins as $admin) {
                        if (!empty($admin['email'])) {
                            $email_log .= "  Gửi email đến: {$admin['email']} ({$admin['ho_ten']})\n";
                            queue_email($admin['email'], $admin['ho_ten'], $email_content['subject'], $email_content['body'], '', 15);
                            $email_log .= "  ✅ queue_email OK\n";
                        } else {
                            $email_log .= "  ⏩ Bỏ qua {$admin['ho_ten']} (không có email)\n";
                        }
                    }
                }
                file_put_contents(__DIR__ . '/../../scratch/email_debug.log', $email_log, FILE_APPEND);
            } catch (\Throwable $e) {
                $err_msg = date('Y-m-d H:i:s') . " - LỖI: " . $e->getMessage() . "\n  File: " . $e->getFile() . ":" . $e->getLine() . "\n  Trace: " . $e->getTraceAsString() . "\n";
                file_put_contents(__DIR__ . '/../../scratch/email_debug.log', $err_msg, FILE_APPEND);
                error_log("Lỗi gửi email admin: " . $e->getMessage());
            }
        }

        echo json_encode(['success' => true, 'message' => "Đã gửi $affected vi phạm lên Đoàn trường thành công."]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không có vi phạm nào đang nháp để gửi.']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
