<?php
// File: src/controllers/xac_thuc_2fa.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Nếu người dùng chưa nhập mật khẩu (chưa ở trạng thái chờ 2FA) mà vào thẳng đây -> đá về
if (!isset($_SESSION['2fa_pending_user_id'])) {
    header('Location: /thidua/tracuu');
    exit();
}

// Lấy thông báo lỗi (nếu có) từ API (sẽ tạo ở file tiếp theo)
$error_message = $_SESSION['2fa_error'] ?? null;
unset($_SESSION['2fa_error']); // Xóa lỗi sau khi hiển thị

// Nạp file View
require_once __DIR__ . '/../views/xac_thuc_2fa.php';