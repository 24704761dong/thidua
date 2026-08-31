<?php
// File: src/controllers/api_ctv_submit_violations.php (Đã nâng cấp để sử dụng hàng đợi email)
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['student_id'])) exit();

// Nạp các file cần thiết
require_once __DIR__ . '/../../config/bootstrap.php'; // Sử dụng bootstrap để có đủ môi trường
require_once __DIR__ . '/../../vendor/autoload.php';
// Nạp file helper để có hàm queue_email()
require_once __DIR__ . '/../lib/helpers.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Hàm tạo nội dung email (giữ nguyên từ file gốc)
function generate_ctv_submission_email($ctv_name, $ctv_class, $week_name, $is_auto_approved) {
    $email_id = date('YmdHis');
    $logo_url = "https://c3binhson.edu.vn/thidua/public/assets/img/22logoapp.png";

    $status_html = $is_auto_approved
        ? '<span style="color: #16a34a; font-weight: 600;">✅ Đã tự động duyệt</span>'
        : '<span style="color: #f97316; font-weight: 600;">⏳ Chờ duyệt</span>';

    $subject_prefix = $is_auto_approved ? "[Đã Duyệt Tự Động]" : "[Cần Duyệt Mới]";
    $subject = "{$subject_prefix} Lớp {$ctv_class} vừa nộp báo cáo {$week_name}";

    $link = "{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}/thidua/admin/trung-tam-duyet";

    $body = <<<HTML
    <div style="font-family: Inter, Arial, sans-serif; max-width: 640px; margin: auto; background: #f4f7f9; padding: 28px 20px; border-radius: 12px; border: 1px solid #e5e7eb;">
        <div style="text-align: center; margin-bottom: 24px;">
            <img src="{$logo_url}" alt="Logo THPT Bình Sơn" style="height: 60px; max-width: 180px; object-fit: contain; margin-bottom: 12px;">
            <h2 style="color: #2563eb; margin: 0;">THÔNG BÁO VI PHẠM MỚI</h2>
            <p style="font-size: 0.95rem; color: #4b5563; margin-top: 8px;">
                Một báo cáo vi phạm vừa được gửi lên từ Cộng Tác Viên.
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
                    <strong>🏫 Lớp trực:</strong>
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

        <p style="font-size: 0.85rem; color: #6b7280; margin-top: 12px; text-align: center;">
            Cần hỗ trợ kỹ thuật? Liên hệ:<br>
            <strong>Liên hệ hỗ trợ:</strong>
            <a href="https://zalo.me/0362566146" style="color:#2563eb; text-decoration:none;">Zalo 036.256.6146</a>
        </p>

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


$data = json_decode(file_get_contents('php://input'), true);
$tuan_id = $data['tuan_id'] ?? null;
$ctv_id = $_SESSION['student_id'];

if ($tuan_id) {
    $db = get_db_connection();
    try {
        $db->beginTransaction();
        
        // Logic xử lý CSDL của bạn được giữ nguyên
        $stmt_settings = $db->query("SELECT setting_value FROM he_thong_cai_dat WHERE setting_key = 'auto_approve_violations'");
        $auto_approve_violations = ($stmt_settings->fetchColumn() === 'on');
        $message = '';

        if ($auto_approve_violations) {
            $stmt_get = $db->prepare("SELECT * FROM vi_pham_tam_thoi WHERE nguoi_nhap_id = ? AND tuan_hoc_id = ? AND trang_thai_gui = 'nhap'");
            $stmt_get->execute([$ctv_id, $tuan_id]);
            $violations_to_approve = $stmt_get->fetchAll();

            if (!empty($violations_to_approve)) {
                $stmt_insert = $db->prepare("INSERT INTO vi_pham_hoc_sinh (tuan_hoc_id, hoc_sinh_id, vi_pham_id, ngay_vi_pham, nguoi_nhap_id, ghi_chu, raw_ho_ten, raw_ten_lop, thoi_gian_nhap) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                foreach ($violations_to_approve as $vp) {
                    $stmt_insert->execute([$vp['tuan_hoc_id'], $vp['hoc_sinh_id'], $vp['vi_pham_id'], $vp['ngay_vi_pham'], $vp['nguoi_nhap_id'], $vp['ghi_chu'], $vp['raw_ho_ten'], $vp['raw_ten_lop'], $vp['thoi_gian_nhap']]);
                }
                $ids_to_update = array_column($violations_to_approve, 'id');
                $placeholders = implode(',', array_fill(0, count($ids_to_update), '?'));
                $stmt_update = $db->prepare("UPDATE vi_pham_tam_thoi SET trang_thai_gui = 'da_duyet' WHERE id IN ($placeholders)");
                $stmt_update->execute($ids_to_update);
                $message = 'Gửi thành công! ' . count($violations_to_approve) . ' mục đã được hệ thống tự động duyệt.';
            } else {
                $message = 'Không có mục nháp nào để gửi.';
            }
        } else {
            $stmt_update = $db->prepare("UPDATE vi_pham_tam_thoi SET trang_thai_gui = 'da_gui' WHERE nguoi_nhap_id = ? AND tuan_hoc_id = ? AND trang_thai_gui = 'nhap'");
            $stmt_update->execute([$ctv_id, $tuan_id]);
            $message = 'Đã gửi danh sách thành công! Admin sẽ sớm xem xét và duyệt.';
        }
        
        $stmt_ctv_info = $db->prepare("SELECT (CONCAT(hs.ho_dem, ' ', hs.ten)) as ten_ctv, lh.ten_lop, th.ten_tuan FROM hoc_sinh hs JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id JOIN tuan_hoc th ON th.id = ? WHERE hs.id = ?");
        $stmt_ctv_info->execute([$tuan_id, $ctv_id]);
        $ctv_info = $stmt_ctv_info->fetch();
        
        $noi_dung_tb = "CTV " . $ctv_info['ten_ctv'] . " (Lớp " . $ctv_info['ten_lop'] . ") vừa gửi một danh sách vi phạm của " . $ctv_info['ten_tuan'] . ($auto_approve_violations ? ' (đã tự động duyệt).' : ' chờ duyệt.');
        $stmt_tb = $db->prepare("INSERT INTO thong_bao (loai_thong_bao, id_lien_quan, noi_dung, thoi_gian) VALUES ('vi_pham_ctv', ?, ?, ?)");
        $stmt_tb->execute([$ctv_id, $noi_dung_tb, date('Y-m-d H:i:s')]);

        // =================== BẮT ĐẦU KHỐI NÂNG CẤP ===================
        try {
            $stmt_admins = $db->query("SELECT email, ho_ten FROM users WHERE vai_tro = 'admin'");
            $admins = $stmt_admins->fetchAll();

            if (!empty($admins)) {
                $email_content = generate_ctv_submission_email($ctv_info['ten_ctv'], $ctv_info['ten_lop'], $ctv_info['ten_tuan'], $auto_approve_violations);
                
                foreach ($admins as $admin) {
                    if (!empty($admin['email'])) {
                        queue_email($admin['email'], $admin['ho_ten'], $email_content['subject'], $email_content['body'], '', 15);
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Lỗi khi đưa email thông báo vi phạm vào hàng đợi: " . $e->getMessage());
        }
        // =================== KẾT THÚC KHỐI NÂNG CẤP ===================

        $db->commit();
        echo json_encode(['success' => true, 'message' => $message]);

    } catch (Exception $e) {
        if($db->inTransaction()) {
            $db->rollBack();
        }
        error_log("API Error (api_ctv_submit_violations): " . $e->getMessage());
        $response['message'] = 'Đã xảy ra lỗi phía máy chủ. Vui lòng thử lại.';
        http_response_code(500);
        echo json_encode($response);
    }
}