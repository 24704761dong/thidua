<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // --- Validation ---
    if (empty($user_id) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Vui lòng điền đầy đủ thông tin.'];
        header('Location: /thidua/quan-ly-tai-khoan');
        exit();
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Mật khẩu mới và mật khẩu xác nhận không khớp.'];
        header('Location: /thidua/quan-ly-tai-khoan');
        exit();
    }

    // Mã hóa mật khẩu mới
    $mat_khau_hash = password_hash($new_password, PASSWORD_DEFAULT);

    $db = get_db_connection();

    try {
        $sql = "UPDATE users SET mat_khau_hash = ? WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$mat_khau_hash, $user_id]);
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Đổi mật khẩu thành công!'];
    } catch (PDOException $e) {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Đổi mật khẩu thất bại: ' . $e->getMessage()];
    }

    header('Location: /thidua/quan-ly-tai-khoan');
    exit();
}