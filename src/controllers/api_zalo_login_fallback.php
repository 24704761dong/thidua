<?php
// File: src/controllers/api_zalo_login_fallback.php
// API đăng nhập bằng Mã HS/CCCD và Mật khẩu (Ngày Sinh) cho Zalo Mini App

require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('POST, OPTIONS');
zalo_handle_options();

$raw_input = file_get_contents('php://input');
$data = json_decode($raw_input, true);

// DEBUG TẠM THỜI - Ghi log ra file để kiểm tra
$debug_log = [
    'time' => date('Y-m-d H:i:s'),
    'raw_input' => $raw_input,
    'raw_length' => strlen($raw_input),
    'parsed_username' => $data['username'] ?? 'NULL',
    'parsed_password' => isset($data['password']) ? '***SET***' : 'NULL',
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? 'N/A',
    'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
    'get_params' => $_GET,
    'post_params' => array_keys($_POST),
];
file_put_contents(
    __DIR__ . '/../../zalo_login_debug.log',
    json_encode($debug_log, JSON_UNESCAPED_UNICODE) . "\n",
    FILE_APPEND
);


$username = trim($data['username'] ?? ''); // Mã HS hoặc CCCD (nếu có cột cccd)
$password = trim($data['password'] ?? ''); // Mật khẩu (Ngày sinh ddmmyyyy)
$zalo_id = trim($data['zalo_id'] ?? ''); // Zalo ID để liên kết nếu login thành công

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập tài khoản và mật khẩu.']);
    exit();
}

try {
    $db = get_db_connection();

    // Tìm kiếm theo mã học sinh (bảng hoc_sinh chứa tài khoản đăng nhập)
    $stmt = $db->prepare("SELECT id, ma_hoc_sinh, ho_dem, ten, ngay_sinh, mat_khau_hash, quyen_truy_cap, trang_thai_hoc_tap, trang_thai_tai_khoan FROM hoc_sinh WHERE ma_hoc_sinh = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    $is_valid_password = false;

    if ($user) {
        // 1. Kiểm tra trực tiếp mật khẩu hash
        if (!empty($user['mat_khau_hash']) && password_verify($password, $user['mat_khau_hash'])) {
            $is_valid_password = true;
        }

        // 2. Thử chuyển đổi định dạng ngày sinh DDMMYYYY -> YYYY-MM-DD nếu user nhập 8 chữ số
        if (!$is_valid_password && preg_match('/^(\d{2})(\d{2})(\d{4})$/', $password, $m)) {
            $iso_pass = "{$m[3]}-{$m[2]}-{$m[1]}";
            if (!empty($user['mat_khau_hash']) && password_verify($iso_pass, $user['mat_khau_hash'])) {
                $is_valid_password = true;
            }
        }

        // 3. Thử chuyển đổi DD/MM/YYYY -> YYYY-MM-DD
        if (!$is_valid_password && preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $password, $m)) {
            $d = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $mo = str_pad($m[2], 2, '0', STR_PAD_LEFT);
            $iso_pass = "{$m[3]}-{$mo}-{$d}";
            if (!empty($user['mat_khau_hash']) && password_verify($iso_pass, $user['mat_khau_hash'])) {
                $is_valid_password = true;
            }
        }

        // 4. So khớp linh hoạt với ngày sinh trong hồ sơ học sinh
        if (!$is_valid_password && !empty($user['ngay_sinh'])) {
            $dob = trim($user['ngay_sinh']);
            $dob_clean = str_replace(['/', '-', '.'], '', $dob);
            $pass_clean = str_replace(['/', '-', '.'], '', $password);
            
            if ($pass_clean === $dob_clean || $password === $dob) {
                $is_valid_password = true;
            } elseif (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $dob, $dm)) {
                $ddmmyyyy = $dm[3] . $dm[2] . $dm[1];
                if ($pass_clean === $ddmmyyyy) {
                    $is_valid_password = true;
                }
            }
        }
    }

    if ($user && $is_valid_password) {
        
        // Chặn học sinh đã nghỉ học
        if (!in_array($user['trang_thai_hoc_tap'], ['dang_hoc', 'da_tot_nghiep'])) {
            echo json_encode(['success' => false, 'message' => 'Tài khoản không còn hiệu lực trên hệ thống. Vui lòng liên hệ nhà trường để được hỗ trợ.']);
            exit();
        }

        // Nếu đăng nhập thành công và có truyền zalo_id -> tự động liên kết
        if (!empty($zalo_id)) {
            $update_stmt = $db->prepare("UPDATE hoc_sinh SET zalo_id = ? WHERE ma_hoc_sinh = ?");
            $update_stmt->execute([$zalo_id, $user['ma_hoc_sinh']]);
        }

        // Kiểm tra xem có phải đăng nhập lần đầu / chưa đổi mật khẩu mặc định
        $must_change_password = ($user['trang_thai_tai_khoan'] !== 'Đã đổi MK');

        // Tạo JWT
        $token = zalo_jwt_encode([
            'student_id' => $user['id'],
            'ma_hoc_sinh' => $user['ma_hoc_sinh'],
            'role' => 'student'
        ]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Đăng nhập thành công!',
            'token' => $token,
            'must_change_password' => $must_change_password,
            'user' => [
                'id' => $user['id'],
                'name' => trim($user['ho_dem'] . ' ' . $user['ten']),
                'must_change_password' => $must_change_password,
                'quyen_truy_cap' => json_decode($user['quyen_truy_cap'] ?: '{}', true)
            ]
        ]);
        exit();
    } else {
        // DEBUG TẠM THỜI - Xóa sau khi fix xong
        $debug = [
            'user_found' => ($user !== false && $user !== null),
            'username_sent' => $username,
            'is_valid_password' => $is_valid_password,
        ];
        if ($user) {
            $debug['ngay_sinh_db'] = $user['ngay_sinh'];
            $debug['has_hash'] = !empty($user['mat_khau_hash']);
            $debug['trang_thai_hoc_tap'] = $user['trang_thai_hoc_tap'];
            $debug['trang_thai_tai_khoan'] = $user['trang_thai_tai_khoan'];
        }
        echo json_encode(['success' => false, 'message' => 'Tài khoản hoặc mật khẩu không chính xác.', '_debug' => $debug]);
        exit();
    }

} catch (Exception $e) {
    zalo_api_error('Đăng nhập thất bại, vui lòng thử lại sau.', 500, $e);
}
