<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('POST, OPTIONS');
zalo_handle_options();

// File: src/controllers/api_zalo_hoat_dong_dang_ky.php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../lib/helpers.php';

$payload = zalo_authenticate_request();
$student_id = $payload['student_id'];
$input = file_get_contents('php://input');
$data = json_decode($input, true);

$hoat_dong_id = (int)($data['hoat_dong_id'] ?? 0);
$action = $data['action'] ?? 'register'; // 'register' or 'unregister'
$phuong_thuc = $data['phuong_thuc'] ?? 'zalo';

if (!$hoat_dong_id) {
    echo json_encode(['success' => false, 'message' => 'Thiếu ID hoạt động']);
    exit();
}

try {
    $db = get_db_connection();
    
    // Kiểm tra hoạt động
    $stmt = $db->prepare("
        SELECT hd.*, 
               (SELECT COUNT(*) FROM hoat_dong_dang_ky dk WHERE dk.hoat_dong_id = hd.id) as dang_ky_count
        FROM hoat_dong hd 
        WHERE hd.id = ? AND hd.trang_thai = 1
    ");
    $stmt->execute([$hoat_dong_id]);
    $hoat_dong = $stmt->fetch();
    
    if (!$hoat_dong) {
        echo json_encode(['success' => false, 'message' => 'Hoạt động không tồn tại hoặc đã bị khoá']);
        exit();
    }
    
    // Kiểm tra đã đăng ký chưa
    $stmtCheck = $db->prepare("SELECT id FROM hoat_dong_dang_ky WHERE hoat_dong_id = ? AND hoc_sinh_id = ?");
    $stmtCheck->execute([$hoat_dong_id, $student_id]);
    $exist = $stmtCheck->fetch();
    
    if ($action === 'register') {
        if ($exist) {
            echo json_encode(['success' => false, 'message' => 'Bạn đã đăng ký hoạt động này rồi']);
            exit();
        }
        
        // Kiểm tra thời hạn
        $now = date('Y-m-d H:i:s');
        if ($hoat_dong['thoi_gian_bd_dang_ky'] && $now < $hoat_dong['thoi_gian_bd_dang_ky']) {
            echo json_encode(['success' => false, 'message' => 'Chưa đến thời gian đăng ký']);
            exit();
        }
        if ($hoat_dong['thoi_gian_kt_dang_ky'] && $now > $hoat_dong['thoi_gian_kt_dang_ky']) {
            echo json_encode(['success' => false, 'message' => 'Đã hết hạn đăng ký']);
            exit();
        }
        
        // Kiểm tra số lượng
        if ($hoat_dong['so_luong_dang_ky'] > 0 && $hoat_dong['dang_ky_count'] >= $hoat_dong['so_luong_dang_ky']) {
            echo json_encode(['success' => false, 'message' => 'Hoạt động đã đủ số lượng đăng ký']);
            exit();
        }
        
        $stmtIns = $db->prepare("INSERT INTO hoat_dong_dang_ky (hoat_dong_id, hoc_sinh_id, trang_thai_diem_danh, diem_thuc_te, phuong_thuc) VALUES (?, ?, 0, 0, ?)");
        $stmtIns->execute([$hoat_dong_id, $student_id, $phuong_thuc]);
        
        // Thêm thông báo
        $stmtHsInfo = $db->prepare("SELECT hs.ten, hs.ho_dem, l.ten_lop FROM hoc_sinh hs LEFT JOIN lop_hoc l ON hs.lop_hoc_id = l.id WHERE hs.id = ?");
        $stmtHsInfo->execute([$student_id]);
        $hsInfo = $stmtHsInfo->fetch();
        $ho_ten = trim(($hsInfo['ho_dem'] ?? '') . ' ' . ($hsInfo['ten'] ?? ''));
        $ten_lop = $hsInfo['ten_lop'] ?? '';
        $ten_hd = $hoat_dong['ten_hoat_dong'] ?? '';
        
        // Cho Admin
        $noi_dung_admin = "Học sinh $ho_ten ($ten_lop) vừa đăng ký hoạt động $ten_hd";
        $stmtTbAdmin = $db->prepare("INSERT INTO thong_bao (loai_thong_bao, id_lien_quan, noi_dung, thoi_gian) VALUES ('hoat_dong', ?, ?, NOW())");
        $stmtTbAdmin->execute([$hoat_dong_id, $noi_dung_admin]);
        
        // Cho học sinh
        $noi_dung_hs = "Bạn đã đăng ký tham gia hoạt động $ten_hd thành công";
        create_student_notification($db, $student_id, 'Đăng ký thành công', $noi_dung_hs, 'hoat_dong');

        // Email cho học sinh
        $stmtHsEmail = $db->prepare("SELECT email FROM hoc_sinh WHERE id = ?");
        $stmtHsEmail->execute([$student_id]);
        $hsEmail = $stmtHsEmail->fetchColumn();
        
        if ($hsEmail && filter_var($hsEmail, FILTER_VALIDATE_EMAIL)) {
            $email_content = "Chào bạn,\n\nBạn đã đăng ký thành công hoạt động: " . $ten_hd . ".\n\nMô tả chi tiết:\n" . ($hoat_dong['mo_ta_ngan'] ?? 'Không có thông tin thêm');
            queue_email($hsEmail, $ho_ten, "Đăng ký hoạt động thành công", $email_content);
        }
        
        echo json_encode(['success' => true, 'message' => 'Đăng ký thành công!']);
    } else {
        // unregister - Chặn huỷ
        echo json_encode(['success' => false, 'message' => 'Bạn không thể huỷ khi đã đăng ký']);
        exit();
    }
    
} catch (Exception $e) {
    http_response_code(500);
    zalo_api_error('Lỗi hệ thống, vui lòng thử lại sau.', 500, $e);
}
