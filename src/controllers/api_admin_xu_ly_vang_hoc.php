<?php
// File: src/controllers/api_admin_xu_ly_vang_hoc.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(0);
ob_start();

if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Bạn chưa đăng nhập.']);
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$db = get_db_connection();
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_vai_tro'] ?? 'user';
$user_permissions = $_SESSION['user_permissions'] ?? [];

// Kiểm tra quyền
if ($user_role !== 'admin' && !in_array('duyet_vang_hoc', $user_permissions) && !in_array('all', $user_permissions)) {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện chức năng này.']);
    exit();
}

$request_id = $_POST['id'] ?? null;
$status = $_POST['status'] ?? null;

$log_file = __DIR__ . '/debug_api.txt';
file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] BẮT ĐẦU API, POST: " . print_r($_POST, true) . "\n", FILE_APPEND);

if (!$request_id || !isset($status) || !in_array($status, [1, 2])) {
    ob_end_clean();
    header('Content-Type: application/json');
    file_put_contents($log_file, "Dữ liệu không hợp lệ\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
    exit();
}

try {
    file_put_contents($log_file, "Bắt đầu cập nhật DB...\n", FILE_APPEND);
    // Nếu là user thường, kiểm tra xem đơn này có thuộc học sinh của lớp họ chủ nhiệm không
    if ($user_role !== 'admin' && !in_array('all', $user_permissions)) {
        $check_sql = "SELECT x.id FROM xin_vang_hoc x 
                      JOIN ho_so_hoc_sinh hs ON x.hoc_sinh_id = hs.id 
                      JOIN lop_hoc l ON hs.lop_hoc_id = l.id
                      WHERE x.id = ? AND l.giao_vien_id = ?";
        $stmt_check = $db->prepare($check_sql);
        $stmt_check->execute([$request_id, $user_id]);
        if (!$stmt_check->fetch()) {
            file_put_contents($log_file, "Lỗi quyền\n", FILE_APPEND);
            throw new Exception("Bạn không có quyền duyệt đơn của học sinh này.");
        }
    }

    $sql = "UPDATE xin_vang_hoc SET trang_thai = ?, nguoi_duyet_id = ?, ngay_cap_nhat = NOW() WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$status, $user_id, $request_id]);
    file_put_contents($log_file, "Cập nhật xin_vang_hoc xong.\n", FILE_APPEND);
    
    // --- BẮT ĐẦU: GỬI THÔNG BÁO CHO HỌC SINH ---
    require_once __DIR__ . '/../lib/helpers.php';
    
    // Lấy thông tin chi tiết của đơn và học sinh
    $stmt_info = $db->prepare("SELECT hs.id as hoc_sinh_id, hs.ho_dem, hs.ten, x.tu_ngay, x.den_ngay, x.ly_do, e.email 
                               FROM xin_vang_hoc x
                               JOIN ho_so_hoc_sinh hs ON x.hoc_sinh_id = hs.id
                               LEFT JOIN email_hoc_sinh e ON hs.id = e.hoc_sinh_id
                               WHERE x.id = ?");
    $stmt_info->execute([$request_id]);
    $info = $stmt_info->fetch(PDO::FETCH_ASSOC);

    if ($info) {
        $hs_id = $info['hoc_sinh_id'];
        $ho_ten = trim($info['ho_dem'] . ' ' . $info['ten']);
        $email_hs = $info['email'];
        $tu_ngay_str = date('d/m/Y', strtotime($info['tu_ngay']));
        $den_ngay_str = date('d/m/Y', strtotime($info['den_ngay']));
        
        $status_text = $status == 1 ? 'đã được DUYỆT' : 'đã BỊ TỪ CHỐI';
        $status_color = $status == 1 ? 'green' : 'red';
        
        file_put_contents($log_file, "Bắt đầu insert thong_bao_hoc_sinh...\n", FILE_APPEND);
        // 1. Thông báo đến icon học sinh (Zalo Mini App)
        $tieu_de_tb = "Kết quả duyệt đơn xin vắng học";
        $noi_dung_tb = "Đơn xin vắng học của bạn (từ {$tu_ngay_str} đến {$den_ngay_str}) {$status_text}.";
        create_student_notification($db, $hs_id, $tieu_de_tb, $noi_dung_tb, 'xin_vang_hoc');
        file_put_contents($log_file, "Insert thong_bao_hoc_sinh xong.\n", FILE_APPEND);

        // 2. Thông báo mail đến học sinh
        if (!empty($email_hs)) {
            file_put_contents($log_file, "Bắt đầu queue_email...\n", FILE_APPEND);
            $mail_body = "<p>Chào <strong>{$ho_ten}</strong>,</p>
                          <p>Hệ thống xin thông báo kết quả đơn xin vắng học của bạn như sau:</p>
                          <ul>
                              <li><strong>Thời gian vắng:</strong> {$tu_ngay_str} - {$den_ngay_str}</li>
                              <li><strong>Trạng thái:</strong> <strong style='color: {$status_color};'>{$status_text}</strong></li>
                          </ul>
                          <p>Cảm ơn bạn đã sử dụng hệ thống.</p>";
            queue_email($email_hs, $ho_ten, "Kết quả duyệt đơn xin vắng học", $mail_body);
            file_put_contents($log_file, "queue_email xong.\n", FILE_APPEND);
        }
    }
    // --- KẾT THÚC: GỬI THÔNG BÁO CHO HỌC SINH ---
    
    file_put_contents($log_file, "Thành công.\n", FILE_APPEND);
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Đã cập nhật trạng thái thành công.']);
} catch (Exception $e) {
    file_put_contents($log_file, "Ngoại lệ: " . $e->getMessage() . "\n", FILE_APPEND);
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
