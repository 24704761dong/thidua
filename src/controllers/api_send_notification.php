<?php
// File: src/controllers/api_send_notification.php (Đã nâng cấp để sử dụng hàng đợi email)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Nạp các file cần thiết
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../lib/helpers.php'; // Nạp file helper để có hàm queue_email()
require_once __DIR__ . '/../lib/firebase_helpers.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'Yêu cầu không hợp lệ.',
    'sent_to_gvcn' => [],
    'sent_to_hs' => [],
    'failed_gvcn' => [],
    'failed_hs' => []
];

// Các hàm tạo nội dung email (giữ nguyên từ file gốc)
function generate_gvcn_violation_email($gvcn_ten, $ten_lop, $ten_tuan, $violations) {
    $email_id = date('YmdHis');
    $logo_url = "https://c3binhson.edu.vn/thidua/public/assets/img/22logoapp.png";

    $violation_rows = '';
    foreach ($violations as $index => $vp) {
        $ngay_vi_pham_formatted = date('d/m/Y', strtotime($vp['ngay_vi_pham']));
        $ho_ten_safe = htmlspecialchars($vp['ho_ten']);
        $ten_vi_pham_safe = htmlspecialchars($vp['ten_vi_pham']);
        $ghi_chu_safe = htmlspecialchars($vp['ghi_chu']);
        $violation_rows .= "
            <tr>
                <td style='padding: 8px; border: 1px solid #e5e7eb; text-align: center; background:#f9fafb;'>" . ($index + 1) . "</td>
                <td style='padding: 8px; border: 1px solid #e5e7eb;'>{$ho_ten_safe}</td>
                <td style='padding: 8px; border: 1px solid #e5e7eb; text-align:center;'>{$ngay_vi_pham_formatted}</td>
                <td style='padding: 8px; border: 1px solid #e5e7eb;'>{$ten_vi_pham_safe}</td>
                <td style='padding: 8px; border: 1px solid #e5e7eb;'>{$ghi_chu_safe}</td>
            </tr>";
    }

    return <<<HTML
    <div style="font-family: Inter, Arial, sans-serif; max-width: 760px; margin: auto; background: #fef2f2; padding: 28px 20px; border-radius: 12px; border: 1px solid #fecaca;">
        <div style="text-align: center; margin-bottom: 24px;">
            <img src="{$logo_url}" alt="Logo THPT Bình Sơn" style="height: 60px; max-width: 180px; object-fit: contain; margin-bottom: 12px;">
            <h2 style="color: #dc2626; margin: 0;">THÔNG BÁO VI PHẠM NỀ NẾP</h2>
            <p style="font-size: 0.95rem; color: #7f1d1d; margin-top: 8px;">
                Gửi đến GVCN lớp <strong>{$ten_lop}</strong> – Tuần <strong>{$ten_tuan}</strong>
            </p>
        </div>

        <div style="background-color: #ffffff; border-radius: 12px; border: 1px solid #e5e7eb; padding: 24px;">
            <p style="margin: 0 0 14px; font-size: 1rem; color: #111827;">Kính gửi Thầy/Cô <strong>{$gvcn_ten}</strong>,</p>
            <p style="margin-bottom: 20px; color: #374151; font-size: 0.95rem;">
                Hệ thống ghi nhận danh sách vi phạm của học sinh lớp trong tuần như sau:
            </p>

            <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                <thead style="background-color: #fee2e2;">
                    <tr style="color: #991b1b;">
                        <th style="padding: 10px; border: 1px solid #e5e7eb;">STT</th>
                        <th style="padding: 10px; border: 1px solid #e5e7eb;">Họ và tên</th>
                        <th style="padding: 10px; border: 1px solid #e5e7eb;">Ngày VP</th>
                        <th style="padding: 10px; border: 1px solid #e5e7eb;">Nhóm Vi Phạm</th>
                        <th style="padding: 10px; border: 1px solid #e5e7eb;">Ghi chú</th>
                    </tr>
                </thead>
                <tbody>{$violation_rows}</tbody>
            </table>

            <p style="margin-top: 18px; font-size: 0.9rem; color: #991b1b;">
                ⚠️ Vui lòng rà soát và cập nhật kết quả xử lý vi phạm của học sinh trong tuần này.
            </p>
        </div>

        <p style="font-size: 0.85rem; color: #6b7280; margin-top: 24px; text-align: center;">
            Cần hỗ trợ kỹ thuật? Liên hệ ngay qua:<br>
            <strong>Liên hệ hỗ trợ:</strong> <a href="https://zalo.me/0362566146" style="color:#2563eb; text-decoration:none;">Zalo 036.256.6146</a>
        </p>

        <hr style="margin: 24px auto; border: none; border-top: 1px solid #fecaca; width: 80%;">
        <p style="text-align: center; font-size: 0.85rem; color: #9ca3af; line-height: 1.6;">
            Trân trọng,<br>
            <span style="font-weight: 600; color: #7f1d1d;">Hệ thống quản lý thi đua</span><br>
            <span style="font-style: italic; color:#7f1d1d;">Trường THPT Bình Sơn</span><br>
            <span style="font-size: 0.75rem; color: #cbd5e1;">Email ID: {$email_id}</span>
        </p>
    </div>
    HTML;
}
function generate_hs_violation_email($ho_ten, $ten_tuan, $violations) {
    $email_id = date('YmdHis');
    $logo_url = "https://c3binhson.edu.vn/thidua/public/assets/img/22logoapp.png";

    $violation_rows = '';
    foreach ($violations as $vp) {
        $ngay_vi_pham_formatted = date('d/m/Y', strtotime($vp['ngay_vi_pham']));
        $ten_vi_pham_safe = htmlspecialchars($vp['ten_vi_pham']);
        $ghi_chu_safe = htmlspecialchars($vp['ghi_chu']);
        $violation_rows .= "
            <tr>
                <td style='padding: 8px; border: 1px solid #e5e7eb; text-align:center;'>{$ngay_vi_pham_formatted}</td>
                <td style='padding: 8px; border: 1px solid #e5e7eb;'>{$ten_vi_pham_safe}</td>
                <td style='padding: 8px; border: 1px solid #e5e7eb;'>{$ghi_chu_safe}</td>
            </tr>";
    }

    return <<<HTML
    <div style="font-family: Inter, Arial, sans-serif; max-width: 640px; margin: auto; background: #fef2f2; padding: 28px 20px; border-radius: 12px; border: 1px solid #fecaca;">
        <div style="text-align: center; margin-bottom: 24px;">
            <img src="{$logo_url}" alt="Logo THPT Bình Sơn" style="height: 60px; max-width: 180px; object-fit: contain; margin-bottom: 12px;">
            <h2 style="color: #dc2626; margin: 0;">THÔNG BÁO VI PHẠM NỘI QUY</h2>
            <p style="font-size: 0.95rem; color: #7f1d1d; margin-top: 8px;">
                Gửi đến học sinh <strong>{$ho_ten}</strong> – <strong>{$ten_tuan}</strong>
            </p>
        </div>

        <div style="background-color: #ffffff; border-radius: 12px; border: 1px solid #e5e7eb; padding: 24px;">
            <p style="margin-bottom: 20px; color: #374151; font-size: 0.95rem;">
                Hệ thống ghi nhận em có các vi phạm trong tuần <strong>{$ten_tuan}</strong> như sau:
            </p>

            <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                <thead style="background-color: #fee2e2;">
                    <tr style="color: #991b1b;">
                        <th style="padding: 10px; border: 1px solid #e5e7eb;">Ngày VP</th>
                        <th style="padding: 10px; border: 1px solid #e5e7eb;">Nhóm Vi Phạm</th>
                        <th style="padding: 10px; border: 1px solid #e5e7eb;">Ghi Chú</th>
                    </tr>
                </thead>
                <tbody>{$violation_rows}</tbody>
            </table>

            <p style="margin-top: 20px; font-size: 0.9rem; color:#991b1b;">
            Học sinh vui lòng xem xét lại và nghiêm túc rút kinh nghiệm để cải thiện trong các tuần tới.
            </p>
        </div>

        <p style="font-size: 0.85rem; color: #6b7280; margin-top: 24px; text-align: center;">
            Cần hỗ trợ kỹ thuật? Liên hệ:<br>
            <strong>Liên hệ hỗ trợ:</strong> <a href="https://zalo.me/0362566146" style="color:#2563eb; text-decoration:none;">Zalo 036.256.6146</a>
        </p>

        <hr style="margin: 24px auto; border: none; border-top: 1px solid #fecaca; width: 80%;">
        <p style="text-align: center; font-size: 0.85rem; color: #9ca3af; line-height: 1.6;">
            Trân trọng,<br>
            <span style="font-weight: 600; color: #7f1d1d;">Hệ thống quản lý thi đua</span><br>
            <span style="font-style: italic; color:#7f1d1d;">Trường THPT Bình Sơn</span><br>
            <span style="font-size: 0.75rem; color: #cbd5e1;">Email ID: {$email_id}</span>
        </p>
    </div>
    HTML;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $violation_ids = $data['violation_ids'] ?? [];
    $send_target = $data['send_target'] ?? 'both';

    if (empty($violation_ids) || !is_array($violation_ids)) {
        $response['message'] = 'Không có vi phạm nào được chọn để gửi thông báo.';
        echo json_encode($response);
        exit();
    }

    $db = get_db_connection();
    
    try {
        // Bước 1: Lấy và gom nhóm vi phạm (giữ nguyên logic)
        $placeholders = implode(',', array_fill(0, count($violation_ids), '?'));
        $sql = "SELECT vp.id, vp.trang_thai_thong_bao, hs.id as hoc_sinh_id, hs.email as hoc_sinh_email, hs.nhan_thong_bao_vi_pham, COALESCE(CONCAT(hs.ho_dem, ' ', hs.ten), vp.raw_ho_ten) as ho_ten, COALESCE(lh.ten_lop, vp.raw_ten_lop) as ten_lop, lh.gvcn_email, lh.gvcn_ten, vp.ngay_vi_pham, chvp.ten_vi_pham, vp.ghi_chu, t.ten_tuan FROM vi_pham_hoc_sinh vp LEFT JOIN quatrinh_hoc_tap qt ON vp.hoc_sinh_id = qt.id LEFT JOIN hoc_sinh hs ON hs.ma_hoc_sinh = qt.ma_hoc_sinh AND hs.nam_hoc_id = qt.nam_hoc_id LEFT JOIN lop_hoc lh ON qt.lop_hoc_id = lh.id OR vp.raw_ten_lop = lh.ten_lop LEFT JOIN cau_hinh_vi_pham chvp ON vp.vi_pham_id = chvp.id JOIN tuan_hoc t ON vp.tuan_hoc_id = t.id WHERE vp.id IN ($placeholders) GROUP BY vp.id";
        $stmt = $db->prepare($sql);
        $stmt->execute($violation_ids);
        $violations = $stmt->fetchAll();
        if (empty($violations)) throw new Exception("Không tìm thấy thông tin vi phạm hợp lệ.");
        
        $notifications_gvcn = [];
        $notifications_hs = [];
        $ten_tuan_chung = $violations[0]['ten_tuan'] ?? 'Chung';

        foreach ($violations as $vp) {
            if (in_array($send_target, ['gvcn', 'both'])) {
                $email_gvcn = $vp['gvcn_email'];
                if (!empty($email_gvcn) && filter_var($email_gvcn, FILTER_VALIDATE_EMAIL)) {
                    if (!isset($notifications_gvcn[$email_gvcn])) $notifications_gvcn[$email_gvcn] = ['gvcn_ten' => $vp['gvcn_ten'], 'ten_lop' => $vp['ten_lop'], 'violations' => []];
                    $notifications_gvcn[$email_gvcn]['violations'][] = $vp;
                } else $response['failed_gvcn'][] = "Lớp {$vp['ten_lop']}: Không có email GVCN hợp lệ.";
            }
            if (in_array($send_target, ['hs', 'both'])) {
                if (!empty($vp['hoc_sinh_id'])) {
                    $email_hs = $vp['hoc_sinh_email'];
                    if (empty($email_hs) || !filter_var($email_hs, FILTER_VALIDATE_EMAIL)) {
                        $response['failed_hs'][] = "HS {$vp['ho_ten']}: Không có email hợp lệ.";
                    } else {
                        if (!isset($notifications_hs[$email_hs])) $notifications_hs[$email_hs] = ['ho_ten' => $vp['ho_ten'], 'violations' => []];
                        $notifications_hs[$email_hs]['violations'][] = $vp;
                    }
                } else {
                    $response['failed_hs'][] = "HS '{$vp['ho_ten']}' (Lớp {$vp['ten_lop']}): KXD, không thể gửi mail.";
                }
            }
        }
        
        // =================== BẮT ĐẦU KHỐI NÂNG CẤP API ===================
        $successfully_queued_gvcn = [];
        $successfully_queued_hs = [];
        $batch_emails = [];

        // Chuẩn bị mảng email và gửi thông báo đa kênh cho GVCN
        foreach ($notifications_gvcn as $email => $data) {
            $subject = "[Thông Báo Vi Phạm] Lớp {$data['ten_lop']} - {$ten_tuan_chung}";
            $body = generate_gvcn_violation_email($data['gvcn_ten'], $data['ten_lop'], $ten_tuan_chung, $data['violations']);
            
            $batch_emails[] = [
                'to' => $email,
                'subject' => $subject,
                'html' => $body,
                'type' => 'gvcn',
                'name' => $data['gvcn_ten'],
                'data' => $data
            ];

            // Gửi đồng thời Chuông In-App và Zalo Bot cho GVCN
            $gv_msg = "Lớp {$data['ten_lop']} có " . count($data['violations']) . " lượt vi phạm nề nếp trong tuần {$ten_tuan_chung}. Vui lòng rà soát và cập nhật kết quả xử lý.";
            create_teacher_notification($db, $email, $subject, $gv_msg, 'vi_pham_lop');
        }
        
        // Chuẩn bị mảng email và gửi thông báo đa kênh cho Học sinh
        foreach ($notifications_hs as $email => $data) {
            $subject = "[Thông Báo Vi Phạm] Bạn có vi phạm mới - {$ten_tuan_chung}";
            $body = generate_hs_violation_email($data['ho_ten'], $ten_tuan_chung, $data['violations']);
            
            // Gửi đồng thời Chuông In-App, Zalo Bot và Push cho học sinh
            foreach ($data['violations'] as $v_item) {
                if (!empty($v_item['hoc_sinh_id'])) {
                    $stmt_hs_lookup = $db->prepare("SELECT hs.id FROM ho_so_hoc_sinh hs JOIN quatrinh_hoc_tap qt ON hs.ma_hoc_sinh = qt.ma_hoc_sinh WHERE qt.id = ? OR hs.id = ? LIMIT 1");
                    $stmt_hs_lookup->execute([$v_item['hoc_sinh_id'], $v_item['hoc_sinh_id']]);
                    $real_hs_id = $stmt_hs_lookup->fetchColumn();
                    if ($real_hs_id) {
                        $ngay_vp_fmt = date('d/m/Y', strtotime($v_item['ngay_vi_pham']));
                        $noi_dung_hs_tb = "Hệ thống ghi nhận bạn có vi phạm nề nếp:\n"
                                        . "- Lỗi: {$v_item['ten_vi_pham']}\n"
                                        . "- Ngày VP: {$ngay_vp_fmt}\n"
                                        . (!empty($v_item['ghi_chu']) ? "- Ghi chú: {$v_item['ghi_chu']}\n" : "")
                                        . "- Tuần: {$ten_tuan_chung}\n\n"
                                        . "Vui lòng nghiêm túc rút kinh nghiệm và không tái phạm.";
                        create_student_notification($db, $real_hs_id, "Thông báo vi phạm nề nếp", $noi_dung_hs_tb, 'vi_pham');
                    }
                }
            }
            
            $batch_emails[] = [
                'to' => $email,
                'subject' => $subject,
                'html' => $body,
                'type' => 'hs',
                'name' => $data['ho_ten'],
                'data' => $data
            ];
        }

        // Gọi API gửi toàn bộ danh sách email một lần
        $api_result = send_email_via_api_batch($batch_emails);
        
        // Cập nhật trạng thái sau khi gọi API
        // Mặc định, nếu gọi API thành công thì ta coi như đã gửi (dù kết quả chi tiết từng email có thể lỗi, ta sẽ phân tích logic trả về nếu cần)
        $is_api_success = $api_result['success'] ?? false;

        foreach ($batch_emails as $mail) {
            $status_log = $is_api_success ? 'api_sent' : 'failed';
            // Lưu lịch sử vào CSDL
            queue_email($mail['to'], $mail['name'], $mail['subject'], $mail['html'], '', 15, ['status' => $status_log]);

            if ($is_api_success) {
                if ($mail['type'] === 'gvcn') {
                    $response['sent_to_gvcn'][] = "{$mail['name']} (Lớp {$mail['data']['ten_lop']}) - {$mail['to']}";
                    $successfully_queued_gvcn[] = $mail['to'];
                } else {
                    $response['sent_to_hs'][] = "{$mail['name']} - {$mail['to']}";
                    $successfully_queued_hs[] = $mail['to'];
                }
            } else {
                $error_msg = $api_result['message'] ?? 'Lỗi không xác định khi gọi API';
                if ($mail['type'] === 'gvcn') {
                    $response['failed_gvcn'][] = "{$mail['name']} ({$mail['to']}): Lỗi API - " . $error_msg;
                } else {
                    $response['failed_hs'][] = "{$mail['name']} ({$mail['to']}): Lỗi API - " . $error_msg;
                }
            }
        }
        // =================== KẾT THÚC KHỐI NÂNG CẤP API ===================
        
        // Bước 4: Cập nhật trạng thái trong CSDL (logic cũ giữ nguyên nhưng kiểm tra theo danh sách đã đưa vào hàng đợi thành công)
        $stmt_update = $db->prepare("UPDATE vi_pham_hoc_sinh SET trang_thai_thong_bao = ? WHERE id = ?");
        foreach($violations as $vp) {
            $current_status = $vp['trang_thai_thong_bao'];
            $new_status = $current_status;
            
            $sent_to_gvcn_this_time = in_array($vp['gvcn_email'], $successfully_queued_gvcn);
            $sent_to_hs_this_time = in_array($vp['hoc_sinh_email'], $successfully_queued_hs);

            if ($send_target === 'both') {
                if($sent_to_gvcn_this_time && $sent_to_hs_this_time) $new_status = 'Đã TB';
                elseif($sent_to_gvcn_this_time) $new_status = 'Đã TB GV';
                elseif($sent_to_hs_this_time) $new_status = 'Đã TB HS';
            } elseif ($send_target === 'gvcn') {
                if ($sent_to_gvcn_this_time) $new_status = ($current_status === 'Đã TB HS') ? 'Đã TB' : 'Đã TB GV';
            } elseif ($send_target === 'hs') {
                if ($sent_to_hs_this_time) $new_status = ($current_status === 'Đã TB GV') ? 'Đã TB' : 'Đã TB HS';
            }
            if ($new_status !== $current_status) $stmt_update->execute([$new_status, $vp['id']]);
        }

        $response['success'] = true;
        $response['message'] = 'Hoàn tất! Các yêu cầu gửi thông báo đã được đưa vào hàng đợi.';
        
    } catch (Exception $e) {
        $response['message'] = 'Lỗi hệ thống: ' . $e->getMessage();
        http_response_code(500);
    }
}

echo json_encode($response);