<?php
// File: src/controllers/api_admin_xu_ly_nhat_ky.php (ĐÃ NÂNG CẤP TÍNH NĂNG HỦY DUYỆT)
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');


require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);
$nhat_ky_id = $data['nhat_ky_id'] ?? null;
$action = $data['action'] ?? null; // 'approve', 'reject', hoặc 'unapprove'
$ghi_chu = $data['ghi_chu'] ?? null;
$admin_id = $_SESSION['user_id'];

if (!$nhat_ky_id || !$action) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
    exit();
}

try {
    $db = get_db_connection();
    $db->beginTransaction();

    $current_nam_hoc = $_SESSION['current_nam_hoc_id'] ?? 1;

    // 1. Lấy thông tin cần thiết của Sổ nhật kỳ và CTV
    $stmt_info = $db->prepare("
        SELECT 
            snk.trang_thai, snk.tuan_hoc_id, snk.lop_hoc_id, snk.ghi_chu_admin,
            snk.nguoi_nhap_id, h.email, CONCAT(h.ho_dem, ' ', h.ten) as ten_ctv,
            l.ten_lop, t.ten_tuan
        FROM so_nhat_ky_online snk
        LEFT JOIN ho_so_hoc_sinh h ON snk.nguoi_nhap_id = h.id
        JOIN raw_lop_hoc l ON snk.lop_hoc_id = l.id AND l.nam_hoc_id = ?
        JOIN raw_tuan_hoc t ON snk.tuan_hoc_id = t.id AND t.nam_hoc_id = ?
        WHERE snk.id = ?
    ");
    $stmt_info->execute([$current_nam_hoc, $current_nam_hoc, $nhat_ky_id]);
    $journal_info = $stmt_info->fetch(PDO::FETCH_ASSOC);

    if (!$journal_info) {
        throw new Exception("Không tìm thấy Sổ Nhật Kỳ.");
    }

    $ctv_email = $journal_info['email'];
    $ctv_name = $journal_info['ten_ctv'];
    $ten_lop = $journal_info['ten_lop'];
    $ten_tuan = $journal_info['ten_tuan'];
    $old_note = $journal_info['ghi_chu_admin'] ?? '';

    $status_to_mail = null;
    $message = 'Thao tác không thay đổi trạng thái.';

    if ($action === 'approve') {
        $stmt = $db->prepare("UPDATE so_nhat_ky_online SET trang_thai = 'da_duyet', ngay_duyet = ?, admin_duyet_id = ? WHERE id = ?");
        $stmt->execute([date('Y-m-d H:i:s'), $admin_id, $nhat_ky_id]);
        $message = 'Đã duyệt Sổ Nhật Kỳ thành công!';
        $status_to_mail = 'approved';
        $push_msg = "Sổ nhật kỳ {$ten_tuan} của lớp {$ten_lop} đã được đã được duyệt.";
    } elseif ($action === 'reject') {
        if (!$ghi_chu) throw new Exception("Vui lòng nhập lý do từ chối.");
        
        $lan = substr_count($old_note, '[Lần ') + 1;
        $new_note = trim($old_note . "\n\n[Lần $lan - " . date('d/m/Y H:i') . "]:\n" . $ghi_chu);
        
        $stmt = $db->prepare("UPDATE so_nhat_ky_online SET trang_thai = 'tu_choi', ghi_chu_admin = ? WHERE id = ?");
        $stmt->execute([$new_note, $nhat_ky_id]);
        $message = 'Đã từ chối và trả lại cho CTV chỉnh sửa.';
        $status_to_mail = 'rejected';
        $push_msg = "Sổ nhật kỳ {$ten_tuan} của lớp {$ten_lop} đã bị từ chối. Lý do: {$ghi_chu}. Vui lòng sửa và nộp lại.";
    } 
    elseif ($action === 'unapprove') {
        $stmt = $db->prepare("UPDATE so_nhat_ky_online SET trang_thai = 'da_gui', ngay_duyet = NULL, admin_duyet_id = NULL WHERE id = ?");
        $stmt->execute([$nhat_ky_id]);
        $message = 'Đã hủy duyệt thành công. Sổ nhật kỳ đã được chuyển về trạng thái "Chờ duyệt".';
        // Không cần gửi mail khi hủy duyệt, vì nó quay lại trạng thái chờ duyệt
    } 
    else {
        throw new Exception("Hành động không hợp lệ.");
    }
    
    // 2. GỬI MAIL THÔNG BÁO CHO CTV (chỉ khi duyệt/từ chối)
    if ($status_to_mail && filter_var($ctv_email, FILTER_VALIDATE_EMAIL)) {
        $mail_content = generate_journal_status_email(
            $ctv_name, 
            $ten_lop, 
            $ten_tuan, 
            $status_to_mail, 
            $ghi_chu ?? ''
        );
        // Đưa vào hàng đợi với ưu tiên trung bình
        queue_email($ctv_email, $ctv_name, $mail_content['subject'], $mail_content['body'], '', 15, [
            'type' => 'journal_review_notice',
            'metadata' => [
                'journal_id' => $nhat_ky_id,
                'status' => $status_to_mail,
            ],
        ]);
    }

    // 3. THÔNG BÁO PUSH ZALO, APP, DB CHO HỌC SINH
    if ($status_to_mail && isset($journal_info['nguoi_nhap_id'])) {
        $zalo_id = $journal_info['nguoi_nhap_id'];

        if ($zalo_id) {
            // Thêm vào bảng thong_bao_hoc_sinh cho App Zalo và Web của học sinh đọc
            $tieu_de = $status_to_mail === 'approved' ? "Sổ nhật kỳ đã duyệt" : "Sổ nhật kỳ bị từ chối";
            create_student_notification($db, $zalo_id, $tieu_de, $push_msg, 'so_nhat_ky_online');

            // Push Zalo
            require_once __DIR__ . '/../lib/zalo_helpers.php';
            send_zalo_push_notification($zalo_id, $push_msg, $tieu_de);

            // Push FCM (Chuẩn bị sẵn, nếu sau này bảng có fcm_token)
            // Lấy token thiết bị nếu có (giả định cột fcm_token tồn tại trong bảng ho_so_hoc_sinh)
            try {
                $stmt_fcm = $db->prepare("SELECT fcm_token FROM ho_so_hoc_sinh WHERE id = ? AND fcm_token IS NOT NULL");
                $stmt_fcm->execute([$zalo_id]);
                $fcm_token = $stmt_fcm->fetchColumn();
                if ($fcm_token) {
                    send_fcm_push_notification([$fcm_token], "Kết quả duyệt sổ nhật kỳ", $push_msg);
                }
            } catch (\Throwable $e) {} // Bỏ qua nếu cột fcm_token chưa có
        }
    }
    
    $db->commit();
    echo json_encode(['success' => true, 'message' => $message]);

} catch (\Throwable $e) {
    if (isset($db) && $db->inTransaction()) $db->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}