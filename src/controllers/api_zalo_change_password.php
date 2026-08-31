<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('POST, OPTIONS');
zalo_handle_options();

$payload = zalo_authenticate_request();
$student_id = $payload['student_id'];
$input = json_decode(file_get_contents('php://input'), true);

$old_password = $input['old_password'] ?? '';
$new_password = $input['new_password'] ?? '';
$confirm_password = $input['confirm_password'] ?? '';

if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin.']);
    exit();
}

if ($new_password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'Mật khẩu mới và xác nhận mật khẩu không khớp.']);
    exit();
}

try {
    $db = get_db_connection();

    // Lấy mật khẩu và ngày sinh hiện tại của học sinh
    $stmt = $db->prepare("SELECT id, ngay_sinh, mat_khau_hash FROM ho_so_hoc_sinh WHERE id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch();

    $is_valid_old_password = false;
    if ($student) {
        // 1. Kiểm tra trực tiếp
        if (!empty($student['mat_khau_hash']) && password_verify($old_password, $student['mat_khau_hash'])) {
            $is_valid_old_password = true;
        }
        // 2. Chuyển đổi định dạng DDMMYYYY -> YYYY-MM-DD
        if (!$is_valid_old_password && preg_match('/^(\d{2})(\d{2})(\d{4})$/', $old_password, $m)) {
            $iso_old = "{$m[3]}-{$m[2]}-{$m[1]}";
            if (!empty($student['mat_khau_hash']) && password_verify($iso_old, $student['mat_khau_hash'])) {
                $is_valid_old_password = true;
            }
        }
        // 3. Chuyển đổi DD/MM/YYYY -> YYYY-MM-DD
        if (!$is_valid_old_password && preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $old_password, $m)) {
            $d = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $mo = str_pad($m[2], 2, '0', STR_PAD_LEFT);
            $iso_old = "{$m[3]}-{$mo}-{$d}";
            if (!empty($student['mat_khau_hash']) && password_verify($iso_old, $student['mat_khau_hash'])) {
                $is_valid_old_password = true;
            }
        }
        // 4. So khớp linh hoạt với ngày sinh trong hồ sơ
        if (!$is_valid_old_password && !empty($student['ngay_sinh'])) {
            $dob = trim($student['ngay_sinh']);
            $dob_clean = str_replace(['/', '-', '.'], '', $dob);
            $old_clean = str_replace(['/', '-', '.'], '', $old_password);
            if ($old_clean === $dob_clean || $old_password === $dob) {
                $is_valid_old_password = true;
            } elseif (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $dob, $dm)) {
                $ddmmyyyy = $dm[3] . $dm[2] . $dm[1];
                if ($old_clean === $ddmmyyyy) {
                    $is_valid_old_password = true;
                }
            }
        }
    }

    if (!$student || !$is_valid_old_password) {
        echo json_encode(['success' => false, 'message' => 'Mật khẩu hiện tại không chính xác.']);
        exit();
    }

    // Cập nhật mật khẩu mới
    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt_update = $db->prepare("UPDATE ho_so_hoc_sinh SET mat_khau_hash = ?, trang_thai_tai_khoan = 'Đã đổi MK' WHERE id = ?");
    $stmt_update->execute([$new_password_hash, $student_id]);

    echo json_encode(['success' => true, 'message' => 'Đổi mật khẩu thành công.']);

} catch (Exception $e) {
    http_response_code(500);
    zalo_api_error('Lỗi hệ thống, vui lòng thử lại sau.', 500, $e);
}
