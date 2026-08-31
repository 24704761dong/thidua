<?php
// File: src/controllers/api_ctv_send_otp.php (Đã nâng cấp để sử dụng hàng đợi email)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

// Kiểm tra session
if (!isset($_SESSION['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'Lỗi xác thực.']);
    exit();
}

// Nạp các file cần thiết
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../lib/helpers.php'; // Nạp file helper chứa hàm queue_email() và mẫu email

$data = json_decode(file_get_contents('php://input'), true);
$new_email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);

function checkEmailUsageForStudent(\PDO $db, string $email, int $studentId): ?string
{
    $normalized = strtolower(trim($email));
    if ($normalized === '') {
        return 'Email không hợp lệ.';
    }

    $sqlStudent = "SELECT id FROM hoc_sinh WHERE LOWER(email) = LOWER(?)";
    $paramsStudent = [$normalized];
    if (columnExists($db, 'hoc_sinh', 'verified_email')) {
        $sqlStudent .= " OR LOWER(verified_email) = LOWER(?)";
        $paramsStudent[] = $normalized;
    }
    $sqlStudent .= " AND id != ? LIMIT 1";
    $paramsStudent[] = $studentId;

    $stmt = $db->prepare($sqlStudent);
    $stmt->execute($paramsStudent);
    if ($stmt->fetchColumn()) {
        return 'Email này đã được sử dụng bởi một học sinh khác.';
    }

    $sqlUser = "SELECT id FROM users WHERE LOWER(email) = LOWER(?)";
    $paramsUser = [$normalized];
    if (columnExists($db, 'users', 'verified_email')) {
        $sqlUser .= " OR LOWER(verified_email) = LOWER(?)";
        $paramsUser[] = $normalized;
    }
    $sqlUser .= " LIMIT 1";

    $stmt = $db->prepare($sqlUser);
    $stmt->execute($paramsUser);
    if ($stmt->fetchColumn()) {
        return 'Email này đã được sử dụng bởi một tài khoản quản trị khác.';
    }

    return null;
}

function columnExists(\PDO $db, string $table, string $column): bool
{
    static $cache = [];
    $key = $table . ':' . $column;
    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }

    try {
        $stmt = $db->prepare("SHOW COLUMNS FROM {$table} LIKE ?");
        $stmt->execute([$column]);
        $exists = (bool) $stmt->fetch();
        $cache[$key] = $exists;
        return $exists;
    } catch (\Throwable $e) {
        $cache[$key] = false;
        return false;
    }
}

if ($new_email) {
    try {
        $db = get_db_connection();
        $studentId = (int) $_SESSION['student_id'];

        if ($conflictMessage = checkEmailUsageForStudent($db, $new_email, $studentId)) {
            echo json_encode(['success' => false, 'message' => $conflictMessage]);
            exit();
        }

        // 1. Tạo và lưu OTP vào session
        $otp_code = rand(100000, 999999);
        $_SESSION['student_otp_data'] = [
            'code' => $otp_code,
            'email' => $new_email,
            'timestamp' => time()
        ];

        // 2. Tạo nội dung và đưa email vào hàng đợi
        $mail_body = generate_beautiful_otp_email($otp_code);
        $alt_body = "Mã xác thực của bạn là: {$otp_code}. Mã này sẽ hết hạn trong 5 phút.";
        $subject = 'Mã xác thực thay đổi Email - Hệ thống QLĐG';
        
        // Gửi vào hàng đợi với độ ưu tiên cao nhất (số 1)
        queue_email($new_email, '', $subject, $mail_body, $alt_body, 1, [
            'type' => 'ctv_email_change_otp',
            'metadata' => [
                'ctv_id' => $studentId,
            ],
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Mã OTP sẽ được gửi đến email mới của bạn trong ít phút.']);

    } catch (\Throwable $e) {
        echo json_encode(['success' => false, 'message' => 'Không thể tạo yêu cầu gửi email OTP: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Email không hợp lệ.']);
}