<?php
// File: src/controllers/ctv_doi_mat_khau.php (File mới)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['student_id'])) {
    header('Location: /thidua/tracuu');
    exit();
}

require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_SESSION['student_id'];
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Vui lòng điền đầy đủ các trường mật khẩu.'];
        header('Location: /thidua/hocsinh/thong-tin-ca-nhan');
        exit();
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Mật khẩu mới và xác nhận mật khẩu không khớp.'];
        header('Location: /thidua/hocsinh/thong-tin-ca-nhan');
        exit();
    }

    $db = get_db_connection();

    try {
        // Lấy mật khẩu hiện tại của học sinh
        $stmt = $db->prepare("SELECT mat_khau_hash FROM hoc_sinh WHERE id = ?");
        $stmt->execute([$student_id]);
        $student = $stmt->fetch();

        if (!$student || !password_verify($old_password, $student['mat_khau_hash'])) {
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Mật khẩu cũ không chính xác.'];
            header('Location: /thidua/hocsinh/thong-tin-ca-nhan');
            exit();
        }

        // Cập nhật mật khẩu mới
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt_update = $db->prepare("UPDATE hoc_sinh SET mat_khau_hash = ?, trang_thai_tai_khoan = 'Đã đổi MK' WHERE id = ?");
        $stmt_update->execute([$new_password_hash, $student_id]);

        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Đổi mật khẩu thành công!'];

    } catch (Exception $e) {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Lỗi CSDL: ' . $e->getMessage()];
    }

    header('Location: /thidua/hocsinh/thong-tin-ca-nhan');
    exit();
}