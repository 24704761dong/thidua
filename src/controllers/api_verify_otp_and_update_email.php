<?php
// File: src/controllers/api_verify_otp_and_update_email.php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/database.php';

$response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp_input = $_POST['otp'];
    $class_id = $_POST['class_id'];
    $otp_data = $_SESSION['otp_data'] ?? null;

    if (!$otp_input || !$class_id) {
        $response['message'] = 'Vui lòng nhập mã OTP.';
    } elseif (!$otp_data) {
        $response['message'] = 'Mã OTP không tồn tại hoặc đã hết hạn. Vui lòng thử lại.';
    } elseif ((time() - $otp_data['timestamp']) > 300) { // 300 giây = 5 phút
        $response['message'] = 'Mã OTP đã hết hạn. Vui lòng yêu cầu mã mới.';
        unset($_SESSION['otp_data']);
    } elseif ($otp_data['code'] != $otp_input) {
        $response['message'] = 'Mã OTP không chính xác.';
    } elseif ($otp_data['class_id'] != $class_id) {
        $response['message'] = 'Lỗi xác thực ID lớp.';
    } else {
        // Mọi thứ đều hợp lệ
        try {
            $db = get_db_connection();
            $stmt = $db->prepare("UPDATE lop_hoc SET gvcn_email = ? WHERE id = ?");
            $stmt->execute([$otp_data['email'], $class_id]);
            
            $response = ['success' => true, 'message' => 'Cập nhật email thành công!'];
            unset($_SESSION['otp_data']); // Xóa OTP sau khi dùng

        } catch (Exception $e) {
            $response['message'] = 'Lỗi cơ sở dữ liệu: ' . $e->getMessage();
        }
    }
}

echo json_encode($response);