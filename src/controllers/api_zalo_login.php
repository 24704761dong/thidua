<?php
// File: src/controllers/api_zalo_login.php
// API đăng nhập bằng Số điện thoại (giải mã từ Zalo Open API) hoặc Zalo ID từ Zalo Mini App

require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('POST, OPTIONS');
zalo_handle_options();

$data = json_decode(file_get_contents('php://input'), true) ?? [];

$phone_token = $data['phone_token'] ?? $data['token'] ?? null;
$access_token = $data['access_token'] ?? null;
$phone = $data['phone'] ?? null;
$zalo_id = $data['zalo_id'] ?? null;

$log_file = __DIR__ . '/../../logs/zalo_login_debug.log';
$log_dir = dirname($log_file);
if (!is_dir($log_dir)) @mkdir($log_dir, 0777, true);

$zalo_api_raw_res = null;

try {
    $db = get_db_connection();

    // 1. Nếu có token SĐT và access_token từ Zalo SDK -> Giải mã số điện thoại thực qua Zalo Open API
    if (!empty($phone_token) && !empty($access_token)) {
        $secret_key = $_ENV['ZALO_APP_SECRET'] ?? '';
        if (!empty($secret_key)) {
            $ch = curl_init('https://graph.zalo.me/v2.0/me/info');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'access_token: ' . $access_token,
                'code: ' . $phone_token,
                'secret_key: ' . $secret_key
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 8);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $res = curl_exec($ch);
            $curl_err = curl_error($ch);
            curl_close($ch);
            $zalo_api_raw_res = $res ?: $curl_err;

            if ($res) {
                $zalo_res = json_decode($res, true);
                if (!empty($zalo_res['data']['number'])) {
                    $phone = $zalo_res['data']['number'];
                }
            }
        }
    }

    file_put_contents($log_file, date('Y-m-d H:i:s') . " | REQ: " . json_encode($data, JSON_UNESCAPED_UNICODE) . " | ZALO_RESP: " . ($zalo_api_raw_res ?? 'N/A') . " | RESOLVED_PHONE: " . ($phone ?? 'NULL') . "\n", FILE_APPEND);

    // 2. Nếu có Zalo ID -> Kiểm tra xem tài khoản đã được liên kết từ trước chưa (Silent Auto-Login)
    if (!empty($zalo_id)) {
        $stmt = $db->prepare("SELECT id, ma_hoc_sinh, ho_dem, ten, quyen_truy_cap, trang_thai_hoc_tap, trang_thai_tai_khoan FROM ho_so_hoc_sinh WHERE zalo_id = ? AND zalo_id IS NOT NULL AND zalo_id != '' LIMIT 1");
        $stmt->execute([$zalo_id]);
        $user = $stmt->fetch();
        
        if ($user) {
            if (!in_array($user['trang_thai_hoc_tap'], ['dang_hoc', 'da_tot_nghiep'])) {
                echo json_encode(['success' => false, 'message' => 'Tài khoản không còn hiệu lực trên hệ thống. Vui lòng liên hệ nhà trường để được hỗ trợ.']);
                exit();
            }

            $must_change_password = ($user['trang_thai_tai_khoan'] !== 'Đã đổi MK');

            // Đăng nhập thành công, tạo JWT
            $jwt_token = zalo_jwt_encode([
                'student_id' => $user['id'],
                'ma_hoc_sinh' => $user['ma_hoc_sinh'],
                'role' => 'student'
            ]);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Đăng nhập Zalo thành công!',
                'token' => $jwt_token,
                'must_change_password' => $must_change_password,
                'user' => [
                    'id' => $user['id'],
                    'name' => trim($user['ho_dem'] . ' ' . $user['ten']),
                    'must_change_password' => $must_change_password,
                    'quyen_truy_cap' => json_decode($user['quyen_truy_cap'] ?: '{}', true)
                ]
            ]);
            exit();
        }
    }

    // 3. Nếu có Số điện thoại (giải mã từ Zalo hoặc truyền lên) -> Tìm học sinh theo SĐT
    if (!empty($phone)) {
        // Chuẩn hóa SĐT: loại bỏ prefix +84 hoặc 84, đảm bảo bắt đầu bằng 0
        $clean_phone = preg_replace('/[^0-9]/', '', $phone);
        $clean_phone = preg_replace('/^(84)/', '0', $clean_phone);
        if (strlen($clean_phone) > 0 && $clean_phone[0] !== '0') {
            $clean_phone = '0' . $clean_phone;
        }

        // Tìm chính xác theo SĐT trong hồ sơ học sinh
        $stmt = $db->prepare("SELECT id, ma_hoc_sinh, ho_dem, ten, quyen_truy_cap, trang_thai_hoc_tap, trang_thai_tai_khoan FROM ho_so_hoc_sinh WHERE sdt = ? OR REPLACE(REPLACE(REPLACE(sdt, ' ', ''), '.', ''), '-', '') = ? LIMIT 1");
        $stmt->execute([$clean_phone, $clean_phone]);
        $user = $stmt->fetch();

        if ($user) {
            if (!in_array($user['trang_thai_hoc_tap'], ['dang_hoc', 'da_tot_nghiep'])) {
                echo json_encode(['success' => false, 'message' => 'Tài khoản không còn hiệu lực trên hệ thống. Vui lòng liên hệ nhà trường để được hỗ trợ.']);
                exit();
            }

            // Nếu khớp SĐT, tự động liên kết zalo_id cho học sinh này để các lần sau vào app tự động
            if (!empty($zalo_id)) {
                $update_stmt = $db->prepare("UPDATE ho_so_hoc_sinh SET zalo_id = ? WHERE id = ?");
                $update_stmt->execute([$zalo_id, $user['id']]);
            }

            $must_change_password = ($user['trang_thai_tai_khoan'] !== 'Đã đổi MK');

            // Tạo JWT
            $jwt_token = zalo_jwt_encode([
                'student_id' => $user['id'],
                'ma_hoc_sinh' => $user['ma_hoc_sinh'],
                'role' => 'student'
            ]);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Đăng nhập Zalo thành công qua SĐT!',
                'token' => $jwt_token,
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
            echo json_encode([
                'success' => false, 
                'message' => "Số điện thoại Zalo ({$clean_phone}) chưa trùng khớp với hồ sơ học sinh. Vui lòng đăng nhập bằng Mã học sinh và Mật khẩu để liên kết tài khoản."
            ]);
            exit();
        }
    }

    // 4. Nếu không tìm thấy
    echo json_encode([
        'success' => false, 
        'message' => 'Không tìm thấy tài khoản học sinh liên kết với Zalo này. Vui lòng đăng nhập bằng Mã học sinh và Mật khẩu.'
    ]);
    exit();

} catch (Exception $e) {
    zalo_api_error('Đăng nhập thất bại, vui lòng thử lại sau.', 500, $e);
}
