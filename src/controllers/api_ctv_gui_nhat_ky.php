<?php
// File: src/controllers/api_ctv_gui_nhat_ky.php (ĐÃ NÂNG CẤP GỬI EMAIL CHO ADMIN + MAIL CÁ NHÂN BỔ SUNG)
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['student_id']) || !($_SESSION['student_permissions']['so_nhat_ky_online'] ?? false)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập.']);
    exit();
}

// Nạp các file cần thiết
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../lib/helpers.php'; 

$data = json_decode(file_get_contents('php://input'), true);
$nhat_ky_id = $data['nhat_ky_id'] ?? null;

if (!$nhat_ky_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Thiếu ID Sổ nhật kỳ.']);
    exit();
}

$db = get_db_connection();
try {
    $db->beginTransaction();

    // Cập nhật trạng thái và ngày gửi
    $stmt = $db->prepare("UPDATE so_nhat_ky_online SET trang_thai = 'da_gui', ngay_gui = ? WHERE id = ?");
    $stmt->execute([date('Y-m-d H:i:s'), $nhat_ky_id]);

    $current_nam_hoc = $_SESSION['current_nam_hoc_id'] ?? 1;

    // Lấy thông tin cần thiết: EMAIL, tên, lớp, tuần
    $stmt_info = $db->prepare("
        SELECT 
            l.ten_lop, t.ten_tuan, h.email, h.ho_dem, h.ten,
            (CONCAT(h.ho_dem, ' ', h.ten)) as ten_ctv 
        FROM so_nhat_ky_online s
        LEFT JOIN ho_so_hoc_sinh h ON s.nguoi_nhap_id = h.id
        JOIN raw_lop_hoc l ON s.lop_hoc_id = l.id AND l.nam_hoc_id = ?
        JOIN raw_tuan_hoc t ON s.tuan_hoc_id = t.id AND t.nam_hoc_id = ?
        WHERE s.id = ?
    ");
    $stmt_info->execute([$current_nam_hoc, $current_nam_hoc, $nhat_ky_id]);
    $info = $stmt_info->fetch();

    if ($info) {
        // 1. Tạo thông báo trên chuông cho Admin
        $noi_dung = sprintf(
            "CTV %s (Lớp %s) vừa gửi Sổ nhật kỳ tuần %s.",
            $info['ten_ctv'], $info['ten_lop'], $info['ten_tuan']
        );
        $stmt_notify = $db->prepare("
            INSERT INTO thong_bao (loai_thong_bao, id_lien_quan, noi_dung, thoi_gian, da_xem) 
            VALUES ('so_nhat_ky_online', ?, ?, ?, 0)
        ");
        $stmt_notify->execute([$nhat_ky_id, $noi_dung, date('Y-m-d H:i:s')]);

        // 2. Tạo thông báo cho học sinh trên Zalo App/Web App
        $zalo_id = $_SESSION['student_id'] ?? 0;
        if ($zalo_id) {
            $hs_msg = "Sổ nhật kỳ tuần {$info['ten_tuan']} đã được gửi thành công. Vui lòng chờ duyệt.";
            $tieu_de = "Nộp sổ thành công";
            create_student_notification($db, $zalo_id, $tieu_de, $hs_msg, 'so_nhat_ky_online');
            
            require_once __DIR__ . '/../lib/zalo_helpers.php';
            send_zalo_push_notification($zalo_id, $hs_msg, $tieu_de);
        }
    }

    $db->commit();
    echo json_encode(['success' => true, 'message' => 'Đã gửi Sổ nhật kỳ thành công!']);

    // ===== GỬI EMAIL SAU KHI PHẢN HỒI =====
    if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();

    if ($info) {
        try {
            // Dữ liệu tổng hợp
            $stmt_totals = $db->prepare("SELECT SUM(so_tiet_tot) as tot, SUM(so_tiet_kha) as kha, SUM(so_tiet_tb) as tb, SUM(so_tiet_yeu) as yeu FROM so_nhat_ky_chi_tiet WHERE nhat_ky_id = ?");
            $stmt_totals->execute([$nhat_ky_id]);
            $totals = $stmt_totals->fetch(PDO::FETCH_ASSOC);
            
            $stmt_proof_counts = $db->prepare("SELECT loai_minh_chung, COUNT(id) as count FROM so_nhat_ky_minh_chung WHERE nhat_ky_id = ? GROUP BY loai_minh_chung");
            $stmt_proof_counts->execute([$nhat_ky_id]);
            $proof_counts_raw = $stmt_proof_counts->fetchAll(PDO::FETCH_KEY_PAIR);

            $summary = [
                'totals' => $totals,
                'proof_counts' => [
                    'hinh_anh' => $proof_counts_raw['hinh_anh'] ?? 0,
                    'pdf' => $proof_counts_raw['pdf'] ?? 0,
                    'khac' => $proof_counts_raw['khac'] ?? 0,
                ]
            ];

            // 1️⃣ GỬI EMAIL CHO CTV
            $ctv_email = $info['email'] ?? null;
            if ($ctv_email && filter_var($ctv_email, FILTER_VALIDATE_EMAIL)) {
                $ctv_mail_content = generate_ctv_submission_confirmation_email(
                    $info['ten_ctv'], $info['ten_lop'], $info['ten_tuan'], $summary
                );
                queue_email($ctv_email, $info['ten_ctv'], $ctv_mail_content['subject'], $ctv_mail_content['body'], '', 2);
            }

            // 2️⃣ GỬI EMAIL CHO ADMIN + MAIL BỔ SUNG
            $stmt_admins = $db->query("SELECT email, ho_ten FROM users WHERE vai_tro = 'admin'");
            $admins = $stmt_admins->fetchAll();

            // Gọi file cấu hình danh sách email bổ sung
            $mail_receivers = require __DIR__ . '/../../config/mail_receivers.php';
            $extra_admins = $mail_receivers['admin_extra'] ?? [];

            // Hợp nhất danh sách người nhận
            $all_receivers = array_merge($admins, $extra_admins);

            if (!empty($all_receivers)) {
                $email_content = generate_journal_submission_email($info['ten_ctv'], $info['ten_lop'], $info['ten_tuan']);
                $alt_body = "Sổ nhật kỳ mới từ {$info['ten_ctv']} (Lớp {$info['ten_lop']}) cho {$info['ten_tuan']}.";

                foreach ($all_receivers as $receiver) {
                    if (!empty($receiver['email']) && filter_var($receiver['email'], FILTER_VALIDATE_EMAIL)) {
                        queue_email($receiver['email'], $receiver['ho_ten'], $email_content['subject'], $email_content['body'], $alt_body, 15);
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Lỗi khi đưa email thông báo Sổ nhật kỳ vào hàng đợi: " . $e->getMessage());
        }
    }

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
}
