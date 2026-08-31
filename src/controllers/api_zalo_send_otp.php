<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('POST, OPTIONS');
zalo_handle_options();

// File: src/controllers/api_zalo_send_otp.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../lib/helpers.php'; // For send_email_via_api_batch and generate_beautiful_otp_email
require_once __DIR__ . '/../../vendor/autoload.php';

$payload = zalo_authenticate_request();
$student_id = $payload['student_id'];
$data = json_decode(file_get_contents('php://input'), true);
$new_email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);

if (!$new_email) {
    echo json_encode(['success' => false, 'message' => 'Email không hợp lệ.']);
    exit();
}

try {
    $db = get_db_connection();

    // Kiểm tra email trùng lặp (trước khi gửi OTP)
    $stmt_check = $db->prepare("SELECT id FROM hoc_sinh WHERE email = ? AND id != ?");
    $stmt_check->execute([$new_email, $student_id]);
    if ($stmt_check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email này đã được sử dụng bởi học sinh khác!']);
        exit();
    }

    // Generate 6 digit OTP
    $otp_code = (string) rand(100000, 999999);
    
    // Save to database
    $stmt = $db->prepare("INSERT INTO zalo_otp_codes (student_id, email, otp_code, expires_at) VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE))");
    $stmt->execute([$student_id, $new_email, $otp_code]);

    // Send email using direct API batch function
    $mail_body = generate_beautiful_otp_email($otp_code);
    $subject = 'Mã xác thực thay đổi Email - Hệ thống QLĐG';

    $batch_emails = [
        [
            'to' => $new_email,
            'name' => 'Học sinh',
            'subject' => $subject,
            'body' => $mail_body
        ]
    ];

    // Log OTP to file for debugging
    $log_msg = date('Y-m-d H:i:s') . " - Yêu cầu gửi OTP Zalo - Email: {$new_email} - Mã OTP: {$otp_code}\n";
    file_put_contents(__DIR__ . '/../../logs/app.log', $log_msg, FILE_APPEND);

    $api_result = send_email_via_api_batch($batch_emails);

    if ($api_result['success']) {
        echo json_encode(['success' => true, 'message' => 'Mã OTP đã được gửi đến email của bạn.']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Lỗi gửi Mail. Mã OTP đã được ghi ra file logs/app.log để test.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống khi tạo yêu cầu gửi OTP.']);
}
