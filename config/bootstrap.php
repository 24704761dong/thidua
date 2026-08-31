<?php
// File: config/bootstrap.php (PHIÊN BẢN HOÀN CHỈNH - ĐÃ TÍCH HỢP ĐỒNG BỘ QUYỀN)
require_once __DIR__ . '/../src/lib/maintenance.php';
// Nạp file autoload của Composer
require_once __DIR__ . '/../vendor/autoload.php';

// NẠP FILE KẾT NỐI CSDL NGAY TỪ ĐẦU
require_once __DIR__ . '/database.php';
date_default_timezone_set('Asia/Ho_Chi_Minh');
// Nạp các biến môi trường từ file .env (nếu tồn tại)
if (class_exists(Dotenv\Dotenv::class)) {
    $envDir = __DIR__ . '/../';
    if (file_exists($envDir . '.env')) {
        Dotenv\Dotenv::createImmutable($envDir)->safeLoad();
    }
}

// Định nghĩa hằng số từ .env (phải sau khi .env được nạp)
define('REPORT_SECRET_KEY', $_ENV['REPORT_SECRET_KEY'] ?? 'fallback-key-hay-doi-trong-env');

// Bắt đầu session (nếu chưa có)
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

// Chạy hàm tự động đăng nhập bằng cookie (để khôi phục session nếu có)
auto_login_from_cookie();

// Khởi tạo Năm học hiện tại vào Session nếu chưa có
if (!isset($_SESSION['current_nam_hoc_id'])) {
    try {
        $db = get_db_connection();
        $stmt = $db->query("SELECT id FROM nam_hoc WHERE is_default = 1 LIMIT 1");
        $defaultNamHocId = $stmt->fetchColumn();
        if ($defaultNamHocId) {
            $_SESSION['current_nam_hoc_id'] = $defaultNamHocId;
        } else {
            // Fallback nếu không có năm học mặc định, lấy năm học đầu tiên
            $stmt = $db->query("SELECT id FROM nam_hoc ORDER BY id DESC LIMIT 1");
            $_SESSION['current_nam_hoc_id'] = $stmt->fetchColumn() ?: 1;
        }
    } catch (Exception $e) {
        error_log("Lỗi khởi tạo năm học: " . $e->getMessage());
    }
}

// đồng bộ quyền real-time
if (isset($_SESSION['user_id'])) {
    try {
        $db_perm = get_db_connection();
        $stmt_perm = $db_perm->prepare("SELECT vai_tro, quyen_han FROM users WHERE id = ?");
        $stmt_perm->execute([$_SESSION['user_id']]);
        $user_data = $stmt_perm->fetch();
        if ($user_data) {
            $_SESSION['user_vai_tro'] = $user_data['vai_tro'];
            $_SESSION['user_permissions'] = ($user_data['vai_tro'] === 'user') ? json_decode($user_data['quyen_han'] ?? '[]', true) : ['all'];
        } else {
            require_once __DIR__ . '/../src/lib/login_logger.php';
            write_login_log('BOOTSTRAP_USER_NOT_FOUND_LOGOUT', [
                'attempted_user_id' => $_SESSION['user_id']
            ]);
            session_unset();
            session_destroy();
            setcookie('remember_me', '', time() - 3600, '/');
            header("Location: /thidua/dang-xuat");
            exit();
        }
    } catch (Exception $e) {
        error_log("Lỗi đồng bộ quyền: " . $e->getMessage());
    }
}

if (isset($db)) {
    $db->exec("SET @current_nam_hoc_id = " . get_current_nam_hoc_id());
}

/**
 * Hàm lấy ID năm học hiện tại đang được chọn trong phiên.
 */
function get_current_nam_hoc_id() {
    return $_SESSION['current_nam_hoc_id'] ?? 1;
}


// ===== BƯỚC 1: GHI NHẬN HOẠT ĐỘNG (LUÔN CHẠY TRƯỚC ĐỂ ĐẢM BẢO PHIÊN MỚI ĐƯỢC GHI NHẬN) =====
require_once __DIR__ . '/../src/lib/tracking.php'; 
update_activity_log(); 

// ===== BƯỚC 2: KIỂM TRA TÍNH HỢP LỆ VÀ ĐỒNG BỘ PHIÊN =====
validate_and_sync_session(); // <-- ĐÃ ĐỔI TÊN HÀM

/**
 * Hàm kiểm tra xem session hiện tại có còn hợp lệ trong CSDL không,
 * và đồng bộ quyền của học sinh theo thời gian thực.
 * (PHIÊN BẢN ĐÃ SỬA LỖI: Không redirect khi chạy API)
 */
function validate_and_sync_session() {
    $db = get_db_connection();
    $current_path = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
    
    // Kiểm tra xem đây có phải là một API call hay không
    // (Giả định tất cả API đều nằm trong /thidua/api/)
    $is_api_call = (strpos($current_path, '/thidua/api/') === 0);

    // --- Phần 1: Kiểm tra session có bị đăng xuất từ xa không ---
    if (isset($_SESSION['user_id']) || isset($_SESSION['student_id'])) {
        $current_session_id = session_id();

        try {
            $stmt = $db->prepare("SELECT session_id FROM phien_truy_cap WHERE session_id = ?");
            $stmt->execute([$current_session_id]);
            $session_exists = $stmt->fetch();

            if (!$session_exists) {
                // Nếu session không tồn tại trong CSDL (đã bị logout từ xa)
                session_unset();
                session_destroy();

                if (isset($_COOKIE['remember_me'])) {
                    setcookie('remember_me', '', time() - 3600, '/thidua/');
                }
                
                // *** NÂNG CẤP CHỐNG LỖI ***
                // Chỉ redirect nếu đây là một trang bình thường
                // Nếu là API call, không làm gì cả, để file API tự xử lý auth
                if (!$is_api_call) {
                    header("Location: " . $current_path); // Dùng $current_path để tránh lặp query string
                    exit();
                }
                // Nếu là API call, session đã bị hủy, script sẽ tiếp tục
                // và file API sẽ thất bại ở bước kiểm tra auth của chính nó (trả về JSON 403)
            }
        } catch (Exception $e) {
            error_log("Session validation failed: " . $e->getMessage());
        }
    }
    
    // --- BẮT ĐẦU PHẦN NÂNG CẤP: Đồng bộ quyền của học sinh theo thời gian thực ---
    if (isset($_SESSION['student_id'])) {
        try {
            $stmt_perm = $db->prepare("SELECT quyen_truy_cap FROM hoc_sinh WHERE id = ?");
            $stmt_perm->execute([$_SESSION['student_id']]);
            $permissions_from_db_json = $stmt_perm->fetchColumn();
            
            // Dữ liệu quyền thực tế từ CSDL
            $permissions_from_db = json_decode($permissions_from_db_json ?: '{}', true);
            
            // Dữ liệu quyền hiện tại trong session
            $permissions_from_session = $_SESSION['student_permissions'] ?? [];

            // Chỉ cập nhật lại session nếu có sự khác biệt
            if ($permissions_from_db !== $permissions_from_session) {
                $_SESSION['student_permissions'] = $permissions_from_db;
            }
        } catch (Exception $e) {
            error_log("Lỗi đồng bộ quyền học sinh: " . $e->getMessage());
        }
    }
    // --- KẾT THÚC PHẦN NÂNG CẤP ĐỒNG BỘ HỌC SINH ---
    
    // --- BẮT ĐẦU PHẦN NÂNG CẤP: Đồng bộ quyền của Giáo viên/Admin theo thời gian thực ---
    if (isset($_SESSION['user_id'])) {
        try {
            $stmt_user = $db->prepare("SELECT vai_tro, quyen_han FROM users WHERE id = ?");
            $stmt_user->execute([$_SESSION['user_id']]);
            $user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);
            
            if ($user_data) {
                // Đồng bộ vai trò (admin/user)
                if (($_SESSION['user_vai_tro'] ?? '') !== $user_data['vai_tro']) {
                    $_SESSION['user_vai_tro'] = $user_data['vai_tro'];
                }
                
                // Đồng bộ mảng quyền hạn
                $permissions_from_db = json_decode($user_data['quyen_han'] ?: '{}', true);
                $permissions_from_session = $_SESSION['user_permissions'] ?? [];
                
                if ($permissions_from_db !== $permissions_from_session) {
                    $_SESSION['user_permissions'] = $permissions_from_db;
                }
            }
        } catch (Throwable $e) {
            error_log("Lỗi đồng bộ quyền giáo viên: " . $e->getMessage());
        }
    }
    // --- KẾT THÚC PHẦN NÂNG CẤP ĐỒNG BỘ GIÁO VIÊN ---
}


/**
 * Hàm tự động đăng nhập bằng cookie
 */
function auto_login_from_cookie() {
    if (isset($_SESSION['user_id']) || isset($_SESSION['student_id'])) {
        return;
    }

    if (empty($_COOKIE['remember_me'])) {
        return;
    }

    list($user_id, $token) = explode(':', $_COOKIE['remember_me'], 2);
    if (empty($user_id) || empty($token)) {
        return;
    }

    $db = get_db_connection();
    $token_hash = hash('sha256', $token);

    $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND remember_token IS NOT NULL");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if ($user && hash_equals($user['remember_token'], $token_hash)) {
         $_SESSION['user_id'] = $user['id'];
         $_SESSION['user_ten'] = $user['ho_ten'];
         $_SESSION['user_vai_tro'] = $user['vai_tro'];
         $_SESSION['last_activity'] = time();
         return;
    }

    $stmt = $db->prepare("SELECT * FROM hoc_sinh WHERE id = ? AND remember_token IS NOT NULL");
    $stmt->execute([$user_id]);
    $student = $stmt->fetch();
    if ($student && hash_equals($student['remember_token'], $token_hash)) {
        $_SESSION['student_id'] = $student['id'];
        $_SESSION['student_name'] = $student['ho_dem'] . ' ' . $student['ten'];
        $_SESSION['student_permissions'] = json_decode($student['quyen_truy_cap'] ?? '{}', true);
        $_SESSION['user_vai_tro'] = 'hoc_sinh';
        return;
    }

    setcookie('remember_me', '', time() - 3600, '/thidua/');
}
?>