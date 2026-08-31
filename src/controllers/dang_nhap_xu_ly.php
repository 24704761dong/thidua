<?php
// File: src/controllers/dang_nhap_xu_ly.php (PHIÊN BẢN SỬA LỖI V7 - ĐỘC LẬP)
if (session_status() === PHP_SESSION_NONE) {
    if (!headers_sent()) {
        session_set_cookie_params([
            'lifetime' => 86400 * 30,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }
    session_start();
}

// 1. Luôn trả về JSON
header('Content-Type: application/json');

// 2. Nạp các file cần thiết (KHÔNG NẠP bootstrap.php)
require_once __DIR__ . '/../../config/oauth_providers.php';
require_once __DIR__ . '/../lib/recaptcha.php';
require_once __DIR__ . '/../../vendor/autoload.php';

// Đảm bảo nạp cấu hình từ .env để kết nối đúng CSDL (data)
if (class_exists(Dotenv\Dotenv::class)) {
    $envDir = __DIR__ . '/../../';
    if (file_exists($envDir . '.env')) {
        Dotenv\Dotenv::createImmutable($envDir)->safeLoad();
    }
}

require_once __DIR__ . '/../lib/helpers.php'; // helpers.php BẮT BUỘC phải require database.php ở bên trong nó
require_once __DIR__ . '/../lib/zalo_helpers.php';
require_once __DIR__ . '/../lib/login_logger.php';

// Ghi lại lỗi nghiêm trọng (fatal) để tránh mất dấu nguyên nhân trả về HTML rỗng
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true)) {
        if (function_exists('log_to_file')) {
            log_to_file('[LOGIN FATAL] ' . json_encode($error, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR));
        }
    }
});

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Hàm xử lý đăng nhập thành công
 */
function handleLoginSuccess($main_db, $user, $is_student = false, $return_json = false) {
    if (!$is_student && $user['vai_tro'] === 'admin') {
        if (!empty($user['app_key'])) {
            $header_app_key = $_SERVER['HTTP_X_DESKTOP_APP_KEY'] ?? '';
            // Nếu đăng nhập từ Desktop App thì phải khớp Key
            if (!empty($header_app_key) && $header_app_key !== $user['app_key']) {
                $msg = "Mã App Key trên ứng dụng Desktop không hợp lệ hoặc đã bị thay đổi!";
                if ($return_json) {
                    echo json_encode(['success' => false, 'message' => $msg]);
                } else {
                    echo "<script>alert('$msg'); window.location.href='/thidua/dang-nhap';</script>";
                }
                exit();
            }
        }
    }

    session_regenerate_id(true); 
    unset($_SESSION['session_recorded']); // Xóa flag này để tracking.php ghi nhận lại phiên mới vào CSDL

    if ($is_student) {
        $_SESSION['student_id'] = $user['id'];
        $_SESSION['student_name'] = $user['ho_dem'] . ' ' . $user['ten'];
        $_SESSION['student_permissions'] = json_decode($user['quyen_truy_cap'] ?? '{}', true);
        $_SESSION['user_vai_tro'] = 'hoc_sinh';
        $_SESSION['student_avatar'] = $user['anh_the'] ?? null;
        $redirect_url = '/thidua/hocsinh';
    } else { // Admin hoặc User
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_ten'] = $user['ho_ten'];
        $_SESSION['user_vai_tro'] = $user['vai_tro'];
        $_SESSION['user_ten_dang_nhap'] = $user['ten_dang_nhap'];
        $_SESSION['user_permissions'] = ($user['vai_tro'] === 'user') ? json_decode($user['quyen_han'] ?? '[]', true) : ['all'];
        $_SESSION['last_activity'] = time();
        $redirect_url = '/thidua/admin';
    }

    if (!empty($_SESSION['post_login_redirect'])) {
        $redirect_url = $_SESSION['post_login_redirect'];
        unset($_SESSION['post_login_redirect']);
    }

    write_login_log('HANDLE_LOGIN_SUCCESS', [
        'redirect_url' => $redirect_url,
        'session_id' => session_id(),
        'user_id' => $_SESSION['user_id'] ?? $_SESSION['student_id'] ?? null,
        'vai_tro' => $_SESSION['user_vai_tro'] ?? null
    ]);

    // Đọc "remember_me" từ $_POST
    $remember_me = $_POST['remember_me'] ?? false; 

    if ($remember_me) {
        $token = bin2hex(random_bytes(32));
        $token_hash = hash('sha256', $token);
        $table_to_update = $is_student ? 'hoc_sinh' : 'users';
        pdoExecWithRetry($main_db, "UPDATE $table_to_update SET remember_token = ? WHERE id = ?", [$token_hash, $user['id']]);
        setcookie('remember_me', $user['id'] . ':' . $token, time() + (86400 * 30), "/thidua/", "", false, true);
    }
    
    // Gọi helper định vị
    require_once __DIR__ . '/../lib/location_helpers.php';
    $ip_address = get_client_ip();
    $vi_tri_ip = get_ip_location($ip_address);
    $gps_lat = $_POST['gps_lat'] ?? '';
    $gps_lon = $_POST['gps_lon'] ?? '';
    $vi_tri_gps = format_gps_location($gps_lat, $gps_lon);

    // (Ghi log đăng nhập)
    try {
        if ($is_student) {
            try { 
                $main_db->exec("UPDATE lich_su_dang_nhap SET id = 1 WHERE id = 0");
                $main_db->exec("ALTER TABLE lich_su_dang_nhap MODIFY id INT AUTO_INCREMENT"); 
            } catch (\Throwable $e) {}
            pdoExecWithRetry($main_db, "INSERT INTO lich_su_dang_nhap (hoc_sinh_id, thoi_gian_dang_nhap, dia_chi_ip, user_agent, vi_tri_ip, vi_tri_gps) VALUES (?, ?, ?, ?, ?, ?)", [$user['id'], date('Y-m-d H:i:s'), $ip_address, $_SERVER['HTTP_USER_AGENT'] ?? 'N/A', $vi_tri_ip, $vi_tri_gps]);
        } else { 
            // Fix AUTO_INCREMENT: xóa record id=0 trước, rồi sửa AUTO_INCREMENT
            try { 
                $main_db->exec("DELETE FROM lich_su_dang_nhap_admin WHERE id = 0");
                $main_db->exec("ALTER TABLE lich_su_dang_nhap_admin MODIFY id INT NOT NULL AUTO_INCREMENT");
                // Reset AUTO_INCREMENT về giá trị đúng
                $stmtMaxFix = $main_db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM lich_su_dang_nhap_admin");
                $nextAutoInc = (int)$stmtMaxFix->fetchColumn();
                if ($nextAutoInc < 1) $nextAutoInc = 1;
                $main_db->exec("ALTER TABLE lich_su_dang_nhap_admin AUTO_INCREMENT = {$nextAutoInc}");
            } catch (\Throwable $e) {
                if (function_exists('log_to_file')) { log_to_file("Fix AUTO_INCREMENT failed: " . $e->getMessage()); }
            }
            try {
                pdoExecWithRetry($main_db, "INSERT INTO lich_su_dang_nhap_admin (user_id, thoi_gian_dang_nhap, dia_chi_ip, user_agent, vi_tri_ip, vi_tri_gps) VALUES (?, ?, ?, ?, ?, ?)", [$user['id'], date('Y-m-d H:i:s'), $ip_address, $_SERVER['HTTP_USER_AGENT'] ?? 'N/A', $vi_tri_ip, $vi_tri_gps]);
            } catch (\Throwable $e) {
                if (function_exists('log_to_file')) { log_to_file("Login Log Admin Insert Failed: " . $e->getMessage()); }
                try {
                    $stmtMax = $main_db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM lich_su_dang_nhap_admin");
                    $nextId = (int)$stmtMax->fetchColumn();
                    if ($nextId < 1) $nextId = 1;
                    pdoExecWithRetry($main_db, "INSERT INTO lich_su_dang_nhap_admin (id, user_id, thoi_gian_dang_nhap, dia_chi_ip, user_agent, vi_tri_ip, vi_tri_gps) VALUES (?, ?, ?, ?, ?, ?, ?)", [$nextId, $user['id'], date('Y-m-d H:i:s'), $ip_address, $_SERVER['HTTP_USER_AGENT'] ?? 'N/A', $vi_tri_ip, $vi_tri_gps]);
                } catch (\Throwable $e2) {
                    if (function_exists('log_to_file')) { log_to_file("Login Log Admin Fallback Insert Failed: " . $e2->getMessage()); }
                }
            }

            if (in_array($user['vai_tro'], ['admin', 'user'])) {
                if (!empty($user['email']) && !empty($user['nhan_canh_bao_dang_nhap']) && $user['nhan_canh_bao_dang_nhap'] == 1) {
                    send_login_alert_email($user['email'], $user['ho_ten'], $vi_tri_ip, $vi_tri_gps);
                }
                if (!empty($user['nhan_canh_bao_zalo']) && $user['nhan_canh_bao_zalo'] == 1) {
                    $browser = ($_SERVER['HTTP_USER_AGENT'] ?? 'Không rõ');
                    // Lấy số điện thoại từ user hoặc từ zalo_id (nếu đã liên kết Zalo)
                    $phone_for_zalo = $user['sdt'] ?? '';
                    if (empty($phone_for_zalo)) {
                        if (function_exists('log_to_file')) { log_to_file("[ZALO LOGIN ALERT] Bỏ qua - user {$user['ten_dang_nhap']} không có số điện thoại."); }
                    } else {
                        if (function_exists('log_to_file')) { log_to_file("[ZALO LOGIN ALERT] Đang gửi ZNS đến SĐT: {$phone_for_zalo} cho user: {$user['ten_dang_nhap']}"); }
                        $zalo_result = send_zalo_login_alert($phone_for_zalo, $user['ho_ten'], $user['ten_dang_nhap'], date('H:i d/m/Y'), $ip_address, $browser, $vi_tri_ip, $vi_tri_gps);
                        if (function_exists('log_to_file')) { log_to_file("[ZALO LOGIN ALERT] Kết quả gửi ZNS: " . ($zalo_result ? 'Thành công' : 'Thất bại')); }
                    }
                }
            }
        }
    } catch (\Throwable $e) { 
        if (function_exists('log_to_file')) { log_to_file("Login Log Failed: " . $e->getMessage()); }
    }
    
    // Đảm bảo ghi và đóng session file trước khi gửi phản hồi để tránh tình trạng Race Condition trên Windows
    session_write_close();
    
    if ($return_json) {
        echo json_encode(['success' => true, 'redirect_url' => $redirect_url]);
        exit();
    }
    
    header('Location: ' . $redirect_url);
    exit();
}

if (!defined('SKIP_LOGIN_PROCESS')) {
    try {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ.']);
            exit();
        }

        $ten_dang_nhap = $_POST['ten_dang_nhap'] ?? '';
        $mat_khau = $_POST['mat_khau'] ?? '';

        write_login_log('LOGIN_ATTEMPT', [
            'username' => $ten_dang_nhap,
            'session_id' => session_id(),
            'cookie_sessid' => $_COOKIE[session_name()] ?? 'NOT_SENT'
        ]);

        $is_desktop_request = !empty($_SERVER['HTTP_X_DESKTOP_APP_KEY']);

        $recaptchaConfig = require __DIR__ . '/../../config/recaptcha.php';
        $recaptchaResponse = (string) ($_POST['g-recaptcha-response'] ?? '');
        if (!$is_desktop_request && !empty($recaptchaConfig['enabled']) && !verify_recaptcha(
            $recaptchaResponse,
            (string) ($recaptchaConfig['secret_key'] ?? ''),
            get_client_ip()
        )) {
            write_login_log('LOGIN_FAILED', ['reason' => 'Recaptcha failed']);
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Xác thực reCAPTCHA thất bại. Vui lòng tích vào ô xác nhận và thử lại.']);
            exit();
        }

        $main_db = get_db_connection();

        // 1. Kiểm tra bảng users (Admin/User)
        $stmt_user = $main_db->prepare("SELECT * FROM users WHERE ten_dang_nhap = ?");
        $stmt_user->execute([$ten_dang_nhap]);
        $user = $stmt_user->fetch();

        if ($user && password_verify($mat_khau, $user['mat_khau_hash'])) {
            write_login_log('USER_PASSWORD_VERIFIED', [
                'username' => $user['ten_dang_nhap'],
                'role' => $user['vai_tro'],
                'user_id' => $user['id']
            ]);
            if (isset($user['trang_thai_tai_khoan']) && $user['trang_thai_tai_khoan'] === 'Đã khóa') {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ Quản trị viên.']);
                exit();
            }
            if (!empty($user['two_fa_enabled']) && (int) $user['two_fa_enabled'] === 1) {
                $_SESSION['2fa_pending_user_id'] = $user['id'];
                $_SESSION['2fa_pending_user_type'] = 'users';
                echo json_encode(['success' => true, 'requires_2fa' => true]);
                exit();
            }
            handleLoginSuccess($main_db, $user, false, true);
        }

        // 2. Kiểm tra học sinh
        $stmt_student = $main_db->prepare("SELECT * FROM hoc_sinh WHERE ma_hoc_sinh = ?");
        $stmt_student->execute([$ten_dang_nhap]);
        $student = $stmt_student->fetch();

        if ($student && isset($student['mat_khau_hash']) && password_verify($mat_khau, $student['mat_khau_hash'])) {
            $public_lookup_nam_hoc_id = get_setting($main_db, 'public_lookup_nam_hoc_id', 0);
            $allow_all_login = (get_setting($main_db, 'allow_all_students_login', $public_lookup_nam_hoc_id) === 'on');
            $permissions = json_decode($student['quyen_truy_cap'] ?? '{}', true);
            $has_permissions = !empty($permissions) && in_array(true, $permissions, true);

            if ($allow_all_login || $has_permissions) {
                if (!empty($student['two_fa_enabled']) && (int) $student['two_fa_enabled'] === 1) {
                    $_SESSION['2fa_pending_user_id'] = $student['id'];
                    $_SESSION['2fa_pending_user_type'] = 'hoc_sinh';
                    echo json_encode(['success' => true, 'requires_2fa' => true]);
                    exit();
                }
                handleLoginSuccess($main_db, $student, true, true);
            } else {
                echo json_encode(['success' => false, 'message' => 'Tài khoản của bạn không có quyền truy cập vào hệ thống lúc này.']);
                exit();
            }
        }

        // 3. Nếu thất bại (sai cả hai)
        http_response_code(401); // Lỗi 401 Unauthorized
        echo json_encode(['success' => false, 'message' => 'Tên đăng nhập hoặc mật khẩu không đúng!']);
        exit();

    } catch (Throwable $t) {
        if (function_exists('log_to_file')) {
            log_to_file('[LOGIN ERROR] ' . $t->getMessage() . "\n" . $t->getTraceAsString());
        }
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Máy chủ gặp lỗi nội bộ. Vui lòng thử lại sau.',
            'debug_message' => $t->getMessage()
        ]);
        exit();
    }
}