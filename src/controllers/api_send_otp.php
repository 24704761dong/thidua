<?php
// File: src/controllers/api_send_otp.php (Đã nâng cấp để sử dụng hàng đợi email)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

// Nạp các file cần thiết
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../lib/helpers.php'; // Nạp file helper chứa hàm queue_email() và mẫu email

$response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $class_id = $_POST['class_id'];

    if ($new_email && $class_id) {
        try {
            // 1. Tạo mã OTP ngẫu nhiên
            $otp_code = rand(100000, 999999);

            // 2. Lưu OTP vào session
            $_SESSION['otp_data'] = [
                'code' => $otp_code,
                'email' => $new_email,
                'class_id' => $class_id,
                'timestamp' => time()
            ];

            // 3. Tạo nội dung và đưa email vào hàng đợi
            $mail_body = generate_beautiful_otp_email($otp_code);
            $alt_body = "Mã xác thực của bạn là: {$otp_code}. Mã này sẽ hết hạn trong 5 phút.";
            $subject = 'Mã xác thực thay đổi Email - Hệ thống QLĐG';
            
            // Gửi vào hàng đợi với độ ưu tiên cao nhất (số 1)
            queue_email($new_email, '', $subject, $mail_body, $alt_body, 1, [
                'type' => 'student_email_change_otp',
                'metadata' => [
                    'class_id' => $class_id,
                ],
            ]);
            
            $response = ['success' => true, 'message' => 'Mã OTP sẽ được gửi đến email của bạn trong ít phút. Vui lòng kiểm tra hộp thư (cả mục Spam).'];

        } catch (Exception $e) {
            $response['message'] = 'Lỗi hệ thống khi tạo yêu cầu gửi OTP: ' . $e->getMessage();
        }
    } else {
        $response['message'] = 'Email không hợp lệ hoặc thiếu ID lớp.';
    }
}

echo json_encode($response);