<?php
// File: src/controllers/api_luu_dang_ky_truc.php (Đã nâng cấp để sử dụng hàng đợi email)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Nạp các file cần thiết
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../vendor/autoload.php';
// Nạp file helper để có hàm queue_email()
require_once __DIR__ . '/../lib/helpers.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ.'];

// Hàm tạo nội dung email (giữ nguyên từ file gốc của bạn)
function generate_duty_roster_email($ctv_name, $class_name, $week_name, $is_auto_approved) {
    $email_id = date('YmdHis');
    $logo_url = "https://c3binhson.edu.vn/thidua/public/assets/img/22logoapp.png";

    $status_html = $is_auto_approved
        ? '<span style="color: #16a34a; font-weight: 600;">✅ Đã tự động duyệt</span>'
        : '<span style="color: #f97316; font-weight: 600;">⏳ Chờ duyệt</span>';

    $subject_prefix = $is_auto_approved ? "[Lịch Trực - Đã Duyệt Tự Động]" : "[Lịch Trực - Cần Duyệt Mới]";
    $subject = "{$subject_prefix} Lớp {$class_name} vừa gửi lịch trực {$week_name}";

    $link = "{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}/thidua/quan-ly-dang-ky-truc";

    $body = <<<HTML
    <div style="font-family: Inter, Arial, sans-serif; max-width: 640px; margin: auto; background: #f4f7f9; padding: 28px 20px; border-radius: 12px; border: 1px solid #e5e7eb;">
        <div style="text-align: center; margin-bottom: 24px;">
            <img src="{$logo_url}" alt="Logo THPT Bình Sơn" style="height: 60px; max-width: 180px; object-fit: contain; margin-bottom: 12px;">
            <h2 style="color: #2563eb; margin: 0;">THÔNG BÁO</h2>
            <p style="font-size: 0.95rem; color: #4b5563; margin-top: 8px;">
                Một lịch trực mới đã được gửi lên từ Cộng Tác Viên của Trường THPT Bình Sơn.
            </p>
        </div>

        <div style="background-color: #ffffff; padding: 24px; border-radius: 12px; border: 1px solid #e5e7eb; margin-bottom: 24px;">
            <p style="margin: 0 0 16px; font-size: 1rem; color: #1f2937;">Xin chào Quản Trị Viên,</p>
            <p style="margin-bottom: 20px; font-size: 0.95rem; color: #374151;">
                Hệ thống ghi nhận một <strong>lịch trực </strong> vừa được gửi lên với thông tin chi tiết như sau:
            </p>
            <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.95rem;">
                <li style="padding: 10px; background-color: #f9fafb; border-radius: 6px; margin-bottom: 8px;">
                    <strong>👤 Người gửi:</strong>
                    <span style="float: right; color: #111827;">{$ctv_name}</span>
                </li>
                <li style="padding: 10px; background-color: #f9fafb; border-radius: 6px; margin-bottom: 8px;">
                    <strong>🏫 Lớp đăng ký:</strong>
                    <span style="float: right; color: #111827;">{$class_name}</span>
                </li>
                <li style="padding: 10px; background-color: #f9fafb; border-radius: 6px; margin-bottom: 8px;">
                    <strong>📅 Lịch trực cho tuần:</strong>
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
                    📋 Xem chi tiết và quản lý
                </a>
            </div>
        </div>

        <div style="font-size: 0.9rem; color: #334155; text-align: center; background-color: #e0f2fe; padding: 12px 16px; border-radius: 8px; border: 1px solid #bae6fd;">
            📅 Lịch trực được gửi bởi <strong>{$ctv_name}</strong> – Lớp <strong>{$class_name}</strong> cho tuần <strong>{$week_name}</strong>.<br>
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


// --- Logic chính ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['student_id'])) {
    echo json_encode($response);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$tuan_hoc_id = $data['tuan_hoc_id'] ?? null;
$schedule = $data['schedule'] ?? [];
$student_id = $_SESSION['student_id'];

if (!$tuan_hoc_id) {
    echo json_encode($response);
    exit();
}

try {
    $db = get_db_connection();
    $db->beginTransaction();

    // Toàn bộ logic xử lý CSDL của bạn được giữ nguyên
    $stmt_settings = $db->query("SELECT setting_key, setting_value FROM he_thong_cai_dat");
    $settings = $stmt_settings->fetchAll(PDO::FETCH_KEY_PAIR);
    $auto_approve_duty = ($settings['auto_approve_duty_roster'] ?? 'off') === 'on';
    $permissions_to_grant = json_decode($settings['auto_grant_permissions_on_duty_approve'] ?? '[]', true);
    $new_status = $auto_approve_duty ? 'Đã duyệt' : 'Chờ duyệt';
    $stmt_info = $db->prepare("SELECT hs.lop_hoc_id, lh.ten_lop, th.ten_tuan, (CONCAT(hs.ho_dem, ' ', hs.ten)) as ten_ctv FROM hoc_sinh hs JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id JOIN tuan_hoc th ON th.id = ? WHERE hs.id = ?");
    $stmt_info->execute([$tuan_hoc_id, $student_id]);
    $info = $stmt_info->fetch();
    if (!$info) throw new Exception("Không thể xác định thông tin lớp hoặc tuần học.");
    $lop_hoc_id = $info['lop_hoc_id'];
    $stmt_check = $db->prepare("SELECT id FROM dang_ky_truc_tuan WHERE lop_hoc_id = ? AND tuan_hoc_id = ? AND trang_thai_luu_tru = 0");
    $stmt_check->execute([$lop_hoc_id, $tuan_hoc_id]);
    $existing_registration = $stmt_check->fetch();
    $registration_id = null;
    $quyen_da_cap_json = ($auto_approve_duty && !empty($permissions_to_grant)) ? json_encode($permissions_to_grant) : NULL;
    if ($existing_registration) {
        $registration_id = $existing_registration['id'];
        $stmt_update = $db->prepare("UPDATE dang_ky_truc_tuan SET nguoi_gui_id = ?, thoi_gian_gui = ?, trang_thai = ?, da_xem = 0, quyen_da_cap = ? WHERE id = ?");
        $stmt_update->execute([$student_id, date('Y-m-d H:i:s'), $new_status, $quyen_da_cap_json, $registration_id]);
        $stmt_delete_details = $db->prepare("DELETE FROM dang_ky_truc_chi_tiet WHERE dang_ky_truc_tuan_id = ?");
        $stmt_delete_details->execute([$registration_id]);
    } else {
        $stmt_insert = $db->prepare("INSERT INTO dang_ky_truc_tuan (tuan_hoc_id, lop_hoc_id, nguoi_gui_id, thoi_gian_gui, trang_thai, quyen_da_cap) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_insert->execute([$tuan_hoc_id, $lop_hoc_id, $student_id, date('Y-m-d H:i:s'), $new_status, $quyen_da_cap_json]);
        $registration_id = $db->lastInsertId();
    }
    $all_student_ids_in_roster = [];
    $stmt_insert_detail = $db->prepare("INSERT INTO dang_ky_truc_chi_tiet (dang_ky_truc_tuan_id, ngay_trong_tuan, hoc_sinh_id) VALUES (?, ?, ?)");
    foreach ($schedule as $day_index => $student_ids) {
        foreach ($student_ids as $hs_id) {
            $stmt_insert_detail->execute([$registration_id, $day_index, $hs_id]);
            if (!in_array($hs_id, $all_student_ids_in_roster)) $all_student_ids_in_roster[] = $hs_id;
        }
    }
    if ($auto_approve_duty && !empty($permissions_to_grant) && !empty($all_student_ids_in_roster)) {
        $stmt_get_perm = $db->prepare("SELECT quyen_truy_cap FROM hoc_sinh WHERE id = ?");
        $stmt_update_perm = $db->prepare("UPDATE hoc_sinh SET quyen_truy_cap = ? WHERE id = ?");
        foreach ($all_student_ids_in_roster as $hs_id_to_grant) {
            $stmt_get_perm->execute([$hs_id_to_grant]);
            $current_permissions = json_decode($stmt_get_perm->fetchColumn() ?: '{}', true);
            foreach ($permissions_to_grant as $perm) $current_permissions[$perm] = true;
            $stmt_update_perm->execute([json_encode($current_permissions), $hs_id_to_grant]);
        }
    }
    $noi_dung_tb = "Lớp " . $info['ten_lop'] . " vừa gửi DS trực cho " . $info['ten_tuan'] . ($auto_approve_duty ? ' (đã tự động duyệt).' : '.');
    $stmt_tb = $db->prepare("INSERT INTO thong_bao (loai_thong_bao, id_lien_quan, noi_dung, thoi_gian) VALUES ('dang_ky_truc', ?, ?, ?)");
    $stmt_tb->execute([$registration_id, $noi_dung_tb, date('Y-m-d H:i:s')]);

    // Thong bao cho hoc sinh gui
    $stmt_hs_info = $db->prepare("SELECT hs.ma_hoc_sinh, COALESCE(hs.email, u.email) as email FROM hoc_sinh hs LEFT JOIN users u ON hs.ma_hoc_sinh = u.ten_dang_nhap WHERE hs.id = ?");
    $stmt_hs_info->execute([$student_id]);
    $hs_info = $stmt_hs_info->fetch();
    
    if ($hs_info) {
        $tieu_de_hs = "Đã gửi danh sách trực tuần";
        $noi_dung_hs = "Bạn đã gửi thành công danh sách trực cho " . $info['ten_tuan'] . ($auto_approve_duty ? ' (hệ thống đã tự động duyệt).' : '. Vui lòng chờ duyệt.');
        create_student_notification($db, $student_id, $tieu_de_hs, $noi_dung_hs, 'dang_ky_truc');
        
        if (!empty($hs_info['email'])) {
            try {
                $hs_email_content = generate_duty_roster_email($info['ten_ctv'], $info['ten_lop'], $info['ten_tuan'], $auto_approve_duty);
                $hs_email_content['subject'] = "[Hệ Thống Thi Đua] Xác nhận gửi danh sách trực tuần";
                queue_email($hs_info['email'], $info['ten_ctv'], $hs_email_content['subject'], $hs_email_content['body'], $noi_dung_hs, 15);
            } catch (Exception $e) {
                error_log("Lỗi gui email hoc sinh: " . $e->getMessage());
            }
        }
    }
    
    // =================== BẮT ĐẦU KHỐI NÂNG CẤP ===================
    try {
        $stmt_admins = $db->query("SELECT email, ho_ten FROM users WHERE vai_tro = 'admin'");
        $admins = $stmt_admins->fetchAll();

        if (!empty($admins)) {
            $email_content = generate_duty_roster_email($info['ten_ctv'], $info['ten_lop'], $info['ten_tuan'], $auto_approve_duty);
            $alt_body = $noi_dung_tb; // Sử dụng nội dung thông báo ngắn gọn

            foreach ($admins as $admin) {
                if (!empty($admin['email'])) {
                    // Gửi vào hàng đợi với độ ưu tiên trung bình (số 15)
                    queue_email($admin['email'], $admin['ho_ten'], $email_content['subject'], $email_content['body'], $alt_body, 15);
                }
            }
        }
    } catch (Exception $e) {
        // Ghi lại lỗi nếu có, nhưng không làm gián đoạn luồng chính
        error_log("Lỗi khi đưa email thông báo trực vào hàng đợi: " . $e->getMessage());
    }
    // =================== KẾT THÚC KHỐI NÂNG CẤP ===================

    $db->commit();
    $message = $auto_approve_duty ? 'Đã gửi và tự động duyệt thành công!' : 'Đã gửi danh sách đăng ký thành công! Admin sẽ sớm xem xét.';
    $response = ['success' => true, 'message' => $message];

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log("API Error (api_luu_dang_ky_truc): " . $e->getMessage());
    $response['message'] = 'Đã xảy ra lỗi phía máy chủ. Vui lòng thử lại.';
    http_response_code(500);
}

echo json_encode($response);