<?php
// File: src/controllers/api_2fa_verify.php (ĐÃ SỬA LỖI LOGIC rowCount v4)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/bootstrap.php'; // Nạp bootstrap ĐỂ CÓ CẢ SESSION VÀ HELPERS
require_once __DIR__ . '/../../vendor/autoload.php';

use PragmaRX\Google2FA\Google2FA;

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
    $data = json_decode(file_get_contents('php://input'), true);
    $code_to_verify = $data['code'] ?? '';

    if (empty($code_to_verify)) {
        throw new Exception('Vui lòng nhập mã 6 số.');
    }

    $db = get_db_connection();

    $session_snapshot = [
        'user_id' => $_SESSION['user_id'] ?? null,
        'student_id' => $_SESSION['student_id'] ?? null,
        'user_vai_tro' => $_SESSION['user_vai_tro'] ?? null,
        '2fa_custom_table' => $_SESSION['2fa_custom_table'] ?? null,
        '2fa_custom_id' => $_SESSION['2fa_custom_id'] ?? null,
    ];

    if (function_exists('log_to_file')) {
        log_to_file('[2FA VERIFY] Session snapshot: ' . json_encode($session_snapshot, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR));
    }

    $resolution = resolveTwoFaTarget();
    $user_id = $resolution['id'];
    $table = $resolution['table'];
    $user_role = $resolution['role'];
    $resolution_source = $resolution['source'];

    if (function_exists('log_to_file')) {
        log_to_file(sprintf('[2FA VERIFY] Target resolved: table=%s, id=%s, source=%s, role=%s', $table ?? 'null', (string) ($user_id ?? 'null'), $resolution_source, (string) ($user_role ?? 'null')));
    }

    if (empty($user_id) || empty($table)) {
        if (function_exists('log_to_file')) {
            log_to_file('LỖI 2FA Verify: Không tìm thấy user_id hoặc student_id trong SESSION.');
        }
        http_response_code(403);
        throw new Exception('Phiên đăng nhập không hợp lệ. Vui lòng F5 và đăng nhập lại.');
    }

    if (function_exists('log_to_file')) {
        log_to_file("[2FA VERIFY] Sử dụng bảng {$table} cho ID {$user_id} (vai trò: {$user_role}).");
    }

    $stmt_get = $db->prepare("SELECT two_fa_secret FROM {$table} WHERE id = ?");
    $stmt_get->execute([$user_id]);
    $secret_key = $stmt_get->fetchColumn();

    if (empty($secret_key)) {
        log_to_file("LỖI 2FA Verify: User ID {$user_id} (bảng {$table}) có two_fa_secret = NULL. Không thể xác minh.");
        throw new Exception('Không tìm thấy mã bí mật. Vui lòng thử lại quá trình "Bật 2FA" từ đầu.');
    }

    $google2fa = new Google2FA();
    $is_valid = $google2fa->verifyKey($secret_key, $code_to_verify);

    if ($is_valid) {
        
        // ===== BẮT ĐẦU SỬA LỖI (V4) =====
        $sql_enable = "UPDATE {$table} SET two_fa_enabled = 1 WHERE id = ?";
        
        // 1. Gọi hàm retry, nó trả về statement ĐÃ ĐƯỢC execute
        $executed_statement = pdoExecWithRetry($db, $sql_enable, [$user_id]); 
        
        // 2. Kiểm tra rowCount() trên statement vừa được trả về
        $affected_rows = $executed_statement->rowCount();

        if ($affected_rows > 0) {
            // THÀNH CÔNG THỰC SỰ
            $response = ['success' => true, 'message' => 'Xác thực 2 yếu tố đã được bật thành công!'];
        } else {
            $stmt_check = $db->prepare("SELECT two_fa_enabled FROM {$table} WHERE id = ?");
            $stmt_check->execute([$user_id]);
            $current_enabled = (int) $stmt_check->fetchColumn();

            if ($current_enabled === 1) {
                $response = ['success' => true, 'message' => 'Xác thực 2 yếu tố đã được bật thành công!'];
            } else {
                if (function_exists('log_to_file')) {
                    log_to_file("LỖI 2FA Verify: Mã 6 số HỢP LỆ, nhưng pdoExecWithRetry thất bại (rowCount=0) khi BẬT cờ 2FA cho User ID {$user_id} (bảng {$table}).");
                }
                throw new Exception('Mã 6 số đúng, nhưng không thể cập nhật trạng thái CSDL (CSDL đang bận). Vui lòng thử lại.');
            }
        }

    } else {
        http_response_code(400); // Bad Request
        $response = ['success' => false, 'message' => 'Mã 6 số không chính xác. Vui lòng thử lại.'];
    }

} catch (Throwable $t) {
    if (function_exists('log_to_file')) {
        log_to_file("LỖI API 2FA Verify: " . $t->getMessage() . "\n" . $t->getTraceAsString());
    }
    http_response_code(500);
    $response['message'] = $t->getMessage();
    $response['debug_trace'] = $t->getTraceAsString();
}

echo json_encode($response);
exit();