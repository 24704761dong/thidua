<?php
// File: src/lib/firebase_helpers.php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Exception\FirebaseException;

/**
 * Khởi tạo Firebase Messaging object
 */
function get_firebase_messaging() {
    $serviceAccountPath = __DIR__ . '/../config/firebase/service-account.json';
    if (!file_exists($serviceAccountPath)) {
        return null;
    }

    $factory = (new Factory)->withServiceAccount($serviceAccountPath);
    return $factory->createMessaging();
}

/**
 * Gửi Push Notification cho một học sinh (gửi tới tất cả các token của họ)
 * @param int $student_id ID của học sinh trong bảng ho_so_hoc_sinh / hoc_sinh
 * @param string $title Tiêu đề thông báo
 * @param string $body Nội dung thông báo
 * @param array $data Dữ liệu thêm (tuỳ chọn)
 * @return bool Trạng thái gửi
 */
function send_fcm_notification($student_id, $title, $body, $data = []) {
    $db = get_db_connection();
    
    // Lấy tất cả token của học sinh này
    $stmt = $db->prepare("SELECT token FROM fcm_tokens WHERE user_id = ? AND user_type = 'hoc_sinh'");
    $stmt->execute([$student_id]);
    $tokens = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($tokens)) {
        return false; // Học sinh chưa đăng ký token nào
    }

    $messaging = get_firebase_messaging();
    if (!$messaging) {
        // Có thể log lỗi không tìm thấy cấu hình Firebase
        return false;
    }

    $notification = Notification::create($title, $body);

    $message = CloudMessage::new()
        ->withNotification($notification)
        ->withData($data);

    $successCount = 0;

    // Gửi Multicast tới nhiều thiết bị
    try {
        $sendReport = $messaging->sendMulticast($message, $tokens);
        $successCount = $sendReport->successes()->count();

        // Xóa các token không hợp lệ / đã hết hạn
        if ($sendReport->hasFailures()) {
            $invalidTokens = [];
            foreach ($sendReport->failures()->getItems() as $failure) {
                $errorMsg = $failure->error()->getMessage();
                // Nếu token bị xoá hoặc không đăng ký
                if (strpos($errorMsg, 'Requested entity was not found') !== false || 
                    strpos($errorMsg, 'invalid') !== false ||
                    strpos($errorMsg, 'NotRegistered') !== false) {
                    $invalidTokens[] = $failure->target()->value();
                }
            }

            if (!empty($invalidTokens)) {
                $placeholders = implode(',', array_fill(0, count($invalidTokens), '?'));
                $deleteStmt = $db->prepare("DELETE FROM fcm_tokens WHERE token IN ($placeholders)");
                $deleteStmt->execute($invalidTokens);
            }
        }
    } catch (Exception $e) {
        // Log error
        error_log("FCM Send Error: " . $e->getMessage());
        return false;
    }

    return $successCount > 0;
}
