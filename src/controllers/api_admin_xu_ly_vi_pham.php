<?php
// File: src/controllers/api_admin_xu_ly_vi_pham.php (ĐÃ NÂNG CẤP LƯU THỜI GIAN VÀ BATCH NOTIFICATIONS)
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập.']);
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/bootstrap.php'; // Đảm bảo gọi bootstrap để lấy ENV (MAIL_API_KEY)
require_once __DIR__ . '/../lib/helpers.php';

$data = json_decode(file_get_contents('php://input'), true);

$action = $data['action'] ?? null;
$ids = $data['ids'] ?? [];

if (empty($action) || empty($ids) || !is_array($ids)) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
    exit();
}

$db = get_db_connection();
try {
    $db->beginTransaction();
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    if ($action === 'approve') {
        // Lấy thông tin chi tiết các vi phạm được duyệt kèm thông tin liên quan
        $query = "
            SELECT 
                vpt.id,
                vpt.hoc_sinh_id AS qt_hs_id,
                vpt.nguoi_nhap_id AS ctv_ho_so_id,
                vpt.vi_pham_id,
                vpt.ngay_vi_pham,
                vpt.ghi_chu,
                vpt.raw_ho_ten,
                vpt.raw_ten_lop,
                vpt.tuan_hoc_id,
                vpt.thoi_gian_nhap,
                cvp.ten_vi_pham,
                
                hs.id AS hs_ho_so_id,
                hs.email AS hs_email,
                hs.ho_dem AS hs_ho_dem,
                hs.ten AS hs_ten,
                
                ctv.email AS ctv_email,
                ctv.ho_dem AS ctv_ho_dem,
                ctv.ten AS ctv_ten,
                
                rlh.gvcn_email,
                rlh.gvcn_ten,
                rlh.id AS lop_hoc_id,
                th.ten_tuan
            FROM vi_pham_tam_thoi vpt
            LEFT JOIN cau_hinh_vi_pham cvp ON vpt.vi_pham_id = cvp.id
            
            LEFT JOIN quatrinh_hoc_tap qt_hs ON vpt.hoc_sinh_id = qt_hs.id
            LEFT JOIN hoc_sinh hs ON hs.ma_hoc_sinh = qt_hs.ma_hoc_sinh AND hs.nam_hoc_id = qt_hs.nam_hoc_id
            
            LEFT JOIN hoc_sinh ctv ON ctv.id = vpt.nguoi_nhap_id
            
            LEFT JOIN raw_lop_hoc rlh ON qt_hs.lop_hoc_id = rlh.id
            LEFT JOIN tuan_hoc th ON vpt.tuan_hoc_id = th.id
            WHERE vpt.id IN ($placeholders)
        ";
        
        $stmt_get = $db->prepare($query);
        $stmt_get->execute($ids);
        $violations_to_approve = $stmt_get->fetchAll(PDO::FETCH_ASSOC);

        $stmt_insert = $db->prepare(
            "INSERT INTO vi_pham_hoc_sinh (tuan_hoc_id, hoc_sinh_id, vi_pham_id, ngay_vi_pham, nguoi_nhap_id, ghi_chu, raw_ho_ten, raw_ten_lop, thoi_gian_nhap, nguoi_nhap_type, trang_thai_thong_bao) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'ctv', ?)"
        );

        $grouped_by_ctv = [];
        $grouped_by_hs = [];
        $grouped_by_gvcn = [];

        foreach ($violations_to_approve as $vp) {
            // Xác định trạng thái thông báo tùy thuộc vào việc ai có email
            $has_hs_email = !empty($vp['hs_email']);
            $has_gvcn_email = !empty($vp['gvcn_email']);
            
            $trang_thai_thong_bao = 'Chưa TB';
            if ($has_hs_email && $has_gvcn_email) {
                $trang_thai_thong_bao = 'Đã TB';
            } elseif ($has_hs_email) {
                $trang_thai_thong_bao = 'Đã TB HS';
            } elseif ($has_gvcn_email) {
                $trang_thai_thong_bao = 'Đã TB GV';
            }

            // Insert vào bảng chính (Lưu ý: hoc_sinh_id là qt.id, nguoi_nhap_id là ho_so.id)
            $stmt_insert->execute([
                $vp['tuan_hoc_id'], $vp['qt_hs_id'], $vp['vi_pham_id'],
                $vp['ngay_vi_pham'], $vp['ctv_ho_so_id'], $vp['ghi_chu'],
                $vp['raw_ho_ten'], $vp['raw_ten_lop'],
                $vp['thoi_gian_nhap'],
                $trang_thai_thong_bao
            ]);

            // Gom nhóm cho CTV
            $ctv_id = $vp['ctv_ho_so_id'];
            if (!isset($grouped_by_ctv[$ctv_id])) {
                $grouped_by_ctv[$ctv_id] = [
                    'info' => $vp,
                    'violations' => []
                ];
            }
            $grouped_by_ctv[$ctv_id]['violations'][] = $vp;

            // Gom nhóm cho Học sinh (Dùng hs_ho_so_id cho Zalo Notification)
            $hs_id = $vp['hs_ho_so_id'];
            if (!isset($grouped_by_hs[$hs_id])) {
                $grouped_by_hs[$hs_id] = [
                    'info' => $vp,
                    'violations' => []
                ];
            }
            $grouped_by_hs[$hs_id]['violations'][] = $vp;

            // Gom nhóm cho GVCN
            $lop_id = $vp['lop_hoc_id'];
            if (!empty($vp['gvcn_email'])) {
                if (!isset($grouped_by_gvcn[$lop_id])) {
                    $grouped_by_gvcn[$lop_id] = [
                        'info' => $vp,
                        'violations' => []
                    ];
                }
                $grouped_by_gvcn[$lop_id]['violations'][] = $vp;
            }
        }

        // Cập nhật trạng thái duyệt ở bảng tạm
        $stmt_update = $db->prepare("UPDATE vi_pham_tam_thoi SET trang_thai_gui = 'da_duyet' WHERE id IN ($placeholders)");
        $stmt_update->execute($ids);

        // --- XỬ LÝ BATCH NOTIFICATIONS ---
        $batch_emails = [];

        // 1. Xử lý cho CTV
        foreach ($grouped_by_ctv as $ctv_id => $data) {
            $info = $data['info'];
            $ctv_name = trim($info['ctv_ho_dem'] . ' ' . $info['ctv_ten']);
            $count = count($data['violations']);
            
            // Notification Zalo App (Chuông)
            
            $tieu_de_tb = "Báo cáo vi phạm đã được duyệt";
            $noi_dung_tb = "Có {$count} vi phạm bạn báo cáo cho " . $info['ten_tuan'] . " đã được Ban thi đua duyệt.";
            create_student_notification($db, $ctv_id, $tieu_de_tb, $noi_dung_tb, 'duyet_vi_pham_ctv');

            // Chuẩn bị Email (chỉ khi có email)
            if (!empty($info['ctv_email'])) {
                $email_content = generate_ctv_approved_violations_email($ctv_name, $data['violations'], $info['ten_tuan']);
                // Ghi vào queue nhưng không gửi ngay lập tức
                queue_email($info['ctv_email'], $ctv_name, $email_content['subject'], $email_content['body'], '', 15, ['status' => 'queued']);
                
                $batch_emails[] = [
                    'to' => $info['ctv_email'],
                    'subject' => $email_content['subject'],
                    'html' => $email_content['body'],
                    'name' => $ctv_name
                ];
            }
        }

        // 2. Xử lý cho Học sinh
        foreach ($grouped_by_hs as $hs_id => $data) {
            $info = $data['info'];
            $hs_name = trim($info['hs_ho_dem'] . ' ' . $info['hs_ten']);
            $count = count($data['violations']);
            
            // Notification Zalo App (Chuông + Zalo Bot)
            if (!empty($hs_id)) {
                $tieu_de_tb = "Cảnh báo kỷ luật!";
                
                $violation_lines = [];
                foreach ($data['violations'] as $idx => $v) {
                    $ngay_vp = date('d/m/Y', strtotime($v['ngay_vi_pham']));
                    $line = "- " . $v['ten_vi_pham'] . "\n  + Ngày VP: {$ngay_vp}";
                    if (!empty($v['ghi_chu'])) {
                        $line .= "\n  + Ghi chú: " . $v['ghi_chu'];
                    }
                    $violation_lines[] = $line;
                }
                
                $noi_dung_tb = "Đoàn trường thông báo: Bạn vừa bị ghi nhận {$count} vi phạm kỷ luật:\n\n"
                             . implode("\n\n", $violation_lines) . "\n\n"
                             . "Yêu cầu học sinh nghiêm túc rút kinh nghiệm và không tái phạm.";
                
                create_student_notification($db, $hs_id, $tieu_de_tb, $noi_dung_tb, 'vi_pham_ca_nhan');
            }

            // Chuẩn bị Email
            if (!empty($info['hs_email'])) {
                $email_content = generate_student_violations_notice_email($hs_name, $info['raw_ten_lop'], $data['violations']);
                queue_email($info['hs_email'], $hs_name, $email_content['subject'], $email_content['body'], '', 15, ['status' => 'queued']);
                
                $batch_emails[] = [
                    'to' => $info['hs_email'],
                    'subject' => $email_content['subject'],
                    'html' => $email_content['body'],
                    'name' => $hs_name
                ];
            }
        }

        // 3. Xử lý cho GVCN
        foreach ($grouped_by_gvcn as $lop_id => $data) {
            $info = $data['info'];
            if (!empty($info['gvcn_email'])) {
                $email_content = generate_gvcn_violations_notice_email($info['gvcn_ten'], $info['raw_ten_lop'], $data['violations']);
                queue_email($info['gvcn_email'], $info['gvcn_ten'], $email_content['subject'], $email_content['body'], '', 15, ['status' => 'queued']);
                
                $batch_emails[] = [
                    'to' => $info['gvcn_email'],
                    'subject' => $email_content['subject'],
                    'html' => $email_content['body'],
                    'name' => $info['gvcn_ten']
                ];
            }
        }

        $db->commit();

        // 4. Bắn batch_emails một cục qua API nếu có
        if (!empty($batch_emails)) {
            // Không nên throw exception ở đây vì DB đã commit rồi
            try {
                $api_result = send_email_via_api_batch($batch_emails);
                // Update email queue status
                $db_conn = get_db_connection();
                $new_status = ($api_result['success'] ?? false) ? 'api_sent' : 'failed';
                $db_conn->exec("UPDATE email_queue SET status = '{$new_status}', sent_at = NOW() WHERE status = 'queued'");
                $db_conn->exec("UPDATE system_email_logs SET status = '{$new_status}', sent_at = NOW() WHERE status = 'queued'");
            } catch (\Throwable $e) {
                error_log("Batch email send failed: " . $e->getMessage());
            }
        }
        
        $message = 'Đã duyệt thành công ' . count($violations_to_approve) . ' mục.';

    } elseif ($action === 'reject') {
        // Lấy thông tin vi phạm bị từ chối và CTV để gửi thông báo
        $query = "
            SELECT 
                vpt.id,
                vpt.nguoi_nhap_id AS ctv_ho_so_id,
                vpt.vi_pham_id,
                vpt.ngay_vi_pham,
                vpt.raw_ho_ten,
                vpt.raw_ten_lop,
                cvp.ten_vi_pham,
                ctv.email AS ctv_email,
                ctv.ho_dem AS ctv_ho_dem,
                ctv.ten AS ctv_ten,
                th.ten_tuan
            FROM vi_pham_tam_thoi vpt
            LEFT JOIN cau_hinh_vi_pham cvp ON vpt.vi_pham_id = cvp.id
            LEFT JOIN hoc_sinh ctv ON ctv.id = vpt.nguoi_nhap_id
            LEFT JOIN tuan_hoc th ON vpt.tuan_hoc_id = th.id
            WHERE vpt.id IN ($placeholders)
        ";
        
        $stmt_get = $db->prepare($query);
        $stmt_get->execute($ids);
        $violations_to_reject = $stmt_get->fetchAll(PDO::FETCH_ASSOC);

        $grouped_by_ctv = [];
        foreach ($violations_to_reject as $vp) {
            $ctv_id = $vp['ctv_ho_so_id'];
            if (!isset($grouped_by_ctv[$ctv_id])) {
                $grouped_by_ctv[$ctv_id] = [
                    'info' => $vp,
                    'violations' => []
                ];
            }
            $grouped_by_ctv[$ctv_id]['violations'][] = $vp;
        }

        $stmt_update = $db->prepare("UPDATE vi_pham_tam_thoi SET trang_thai_gui = 'da_loai_bo' WHERE id IN ($placeholders)");
        $stmt_update->execute($ids);

        $batch_emails = [];
        foreach ($grouped_by_ctv as $ctv_id => $data) {
            $info = $data['info'];
            $ctv_name = trim($info['ctv_ho_dem'] . ' ' . $info['ctv_ten']);
            $count = count($data['violations']);
            
            // Notification Zalo App (Chuông)
            
            $tieu_de_tb = "Báo cáo vi phạm BỊ TỪ CHỐI";
            $noi_dung_tb = "Có {$count} vi phạm bạn báo cáo cho " . $info['ten_tuan'] . " KHÔNG được duyệt. Vui lòng xem lại.";
            create_student_notification($db, $ctv_id, $tieu_de_tb, $noi_dung_tb, 'tu_choi_vi_pham_ctv');

            // Chuẩn bị Email
            if (!empty($info['ctv_email'])) {
                $email_content = generate_ctv_rejected_violations_email($ctv_name, $data['violations'], $info['ten_tuan']);
                queue_email($info['ctv_email'], $ctv_name, $email_content['subject'], $email_content['body'], '', 15, ['status' => 'queued']);
                
                $batch_emails[] = [
                    'to' => $info['ctv_email'],
                    'subject' => $email_content['subject'],
                    'html' => $email_content['body'],
                    'name' => $ctv_name
                ];
            }
        }

        // Gọi API gửi mail hàng loạt
        if (!empty($batch_emails)) {
            send_email_via_api_batch($batch_emails);
        }

        $db->commit();
        $message = 'Đã loại bỏ ' . count($ids) . ' mục.';
    } else {
        throw new Exception("Hành động không hợp lệ.");
    }

    echo json_encode(['success' => true, 'message' => $message]);

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}