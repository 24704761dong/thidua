<?php
// File: src/controllers/api_2fa_generate.php (ĐÃ SỬA LỖI LOGIC rowCount)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/bootstrap.php'; 

use PragmaRX\Google2FA\Google2FA;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Lỗi không xác định.'];

function resolveTwoFaTarget(): array {
    $role_raw = $_SESSION['user_vai_tro'] ?? null;
    $role = $role_raw !== null ? strtolower((string) $role_raw) : null;

    $custom_table = $_SESSION['2fa_custom_table'] ?? null;
    $custom_id = $_SESSION['2fa_custom_id'] ?? null;
    if (!empty($custom_table) && !empty($custom_id)) {
        $safe_table = strtolower(preg_replace('/[^a-z0-9_]/i', '', (string) $custom_table));
        return [
            'id' => (int) $custom_id,
            'table' => $safe_table ?: 'users',
            'source' => 'custom_session',
            'role' => $role,
        ];
    }

    $ctv_roles = ['ctv', 'cong_tac_vien', 'congtacvien'];

    if (isset($_SESSION['student_id']) && ($role === 'hoc_sinh' || in_array($role, $ctv_roles, true) || $role === null)) {
        return [
            'id' => (int) $_SESSION['student_id'],
            'table' => 'hoc_sinh',
            'source' => 'student_session',
            'role' => $role,
        ];
    }

    if (isset($_SESSION['user_id'])) {
        return [
            'id' => (int) $_SESSION['user_id'],
            'table' => 'users',
            'source' => 'user_session',
            'role' => $role,
        ];
    }

    if (isset($_SESSION['student_id'])) {
        return [
            'id' => (int) $_SESSION['student_id'],
            'table' => 'hoc_sinh',
            'source' => 'student_fallback',
            'role' => $role,
        ];
    }

    return [
        'id' => null,
        'table' => null,
        'source' => 'unresolved',
        'role' => $role,
    ];
}

try {
    $db = get_db_connection();

    $session_snapshot = [
        'user_id' => $_SESSION['user_id'] ?? null,
        'student_id' => $_SESSION['student_id'] ?? null,
        'user_vai_tro' => $_SESSION['user_vai_tro'] ?? null,
        '2fa_pending_user_id' => $_SESSION['2fa_pending_user_id'] ?? null,
        '2fa_pending_user_type' => $_SESSION['2fa_pending_user_type'] ?? null,
        '2fa_custom_table' => $_SESSION['2fa_custom_table'] ?? null,
        '2fa_custom_id' => $_SESSION['2fa_custom_id'] ?? null,
    ];

    if (function_exists('log_to_file')) {
        log_to_file('[2FA GENERATE] Session snapshot: ' . json_encode($session_snapshot, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR));
    }

    $resolution = resolveTwoFaTarget();
    $user_id = $resolution['id'];
    $table = $resolution['table'];
    $user_role = $resolution['role'];
    $resolution_source = $resolution['source'];

    if (function_exists('log_to_file')) {
        log_to_file(sprintf('[2FA GENERATE] Target resolved: table=%s, id=%s, source=%s, role=%s', $table ?? 'null', (string) ($user_id ?? 'null'), $resolution_source, (string) ($user_role ?? 'null')));
    }

    if (!$user_id || !$table) {
        http_response_code(403);
        throw new Exception('Bạn phải đăng nhập để thực hiện thao tác này. (Session not found)');
    }

    $user_identifier = null;

    if ($table === 'users') {
        $stmt = $db->prepare("SELECT ten_dang_nhap, email, ho_ten FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_info = $stmt->fetch();
        if (!$user_info) {
            throw new Exception("Không tìm thấy tài khoản người dùng (ID {$user_id}).");
        }
        $user_identifier = $user_info['ho_ten'] ?: ($user_info['email'] ?: $user_info['ten_dang_nhap']);
    } else {
        $stmt = $db->prepare("SELECT ma_hoc_sinh, email, ho_dem, ten FROM hoc_sinh WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_info = $stmt->fetch();
        if (!$user_info) {
            throw new Exception("Không tìm thấy tài khoản học sinh (ID {$user_id}).");
        }
        $full_name = trim(($user_info['ho_dem'] ?? '') . ' ' . ($user_info['ten'] ?? ''));
        $user_identifier = $full_name ?: ($user_info['email'] ?: $user_info['ma_hoc_sinh']);
    }

    if (function_exists('log_to_file')) {
        log_to_file("[2FA GENERATE] Sử dụng bảng {$table} cho ID {$user_id} (vai trò: {$user_role}).");
    }

    $google2fa = new Google2FA();
    $secret_key = $google2fa->generateSecretKey();
    $app_name = "Binh Son Edu Progress"; 
    $qr_code_url = $google2fa->getQRCodeUrl($app_name, $user_identifier, $secret_key);

    // SỬ DỤNG pdoExecWithRetry VÀ KIỂM TRA rowCount()
    $sql_save = "UPDATE {$table} SET two_fa_secret = ? WHERE id = ?";
    
    // ===== BẮT ĐẦU SỬA LỖI (V4) =====
    // 1. Gọi hàm retry, nó trả về statement ĐÃ ĐƯỢC execute
    $executed_statement = pdoExecWithRetry($db, $sql_save, [$secret_key, $user_id]);
    
    // 2. Kiểm tra rowCount() trên statement vừa được trả về
    if ($executed_statement->rowCount() === 0) {
        $stmt_check = $db->prepare("SELECT two_fa_secret FROM {$table} WHERE id = ?");
        $stmt_check->execute([$user_id]);
        $existing_secret = $stmt_check->fetchColumn();
        if ($existing_secret !== $secret_key) {
            if (function_exists('log_to_file')) {
                log_to_file("LỖI 2FA Generate: pdoExecWithRetry thất bại khi LƯU SECRET KEY (rowCount=0) cho User ID {$user_id} (bảng {$table}).");
            }
            throw new Exception('Không thể lưu mã bí mật vào CSDL (CSDL đang bận hoặc ID người dùng không đúng). Vui lòng thử lại.');
        }
    }

    if (!class_exists(Builder::class) || !class_exists(PngWriter::class)) {
        throw new Exception('Thư viện tạo QR Code chưa được cài đặt. Vui lòng chạy composer install.');
    }

    $qr_result = Builder::create()
        ->writer(new PngWriter())
        ->data($qr_code_url)
        ->encoding(new Encoding('UTF-8'))
        ->errorCorrectionLevel(new ErrorCorrectionLevelLow())
        ->size(300)
        ->margin(10)
        ->build();
    $qr_image_base64 = $qr_result->getDataUri();

    $response = [
        'success' => true,
        'secret_key' => $secret_key,
        'qr_image_data_uri' => $qr_image_base64
    ];

} catch (Throwable $t) { 
    $error_message = $t->getMessage();
    $error_trace = $t->getTraceAsString();
    if (function_exists('log_to_file')) {
        log_to_file("LỖI API 2FA Generate (Throwable): " . $error_message . "\n" . $error_trace);
    }
    http_response_code(500);
    $response['message'] = $error_message;
    $response['debug_trace'] = $error_trace;
}

echo json_encode($response);
exit();