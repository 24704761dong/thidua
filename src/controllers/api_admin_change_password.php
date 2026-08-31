<?php
// File: src/controllers/api_admin_change_password.php (ĐÃ SỬA ĐỂ DÙNG CSDL CHÍNH)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

// 1. Kiểm tra quyền truy cập và dữ liệu đầu vào
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập.']);
    exit();
}

// BƯỚC 1: Đổi sang kết nối CSDL chính
require_once __DIR__ . '/../../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

$current_password = $data['current_password'] ?? '';
$new_password = $data['new_password'] ?? '';
$user_id = $_SESSION['user_id'];

if (empty($current_password) || empty($new_password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ mật khẩu cũ và mới.']);
    exit();
}

try {
    // BƯỚC 2: Lấy kết nối đến CSDL chính (app_td.db)
    $db = get_db_connection();

    // 3. Lấy mật khẩu đã mã hóa hiện tại từ CSDL chính
    $stmt = $db->prepare("SELECT mat_khau_hash FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    // 4. So sánh mật khẩu cũ người dùng nhập với mật khẩu trong CSDL
    if (!$user || !password_verify($current_password, $user['mat_khau_hash'])) {
        echo json_encode(['success' => false, 'message' => 'Mật khẩu hiện tại không chính xác.']);
        exit();
    }

    // 5. Nếu đúng, mã hóa và cập nhật mật khẩu mới vào CSDL chính
    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt_update = $db->prepare("UPDATE users SET mat_khau_hash = ? WHERE id = ?");
    $stmt_update->execute([$new_password_hash, $user_id]);

    echo json_encode(['success' => true, 'message' => 'Đổi mật khẩu thành công!']);

} catch (Exception $e) {
    http_response_code(500);
    error_log("Admin Change Password Error: " . $e->getMessage()); // Ghi lại lỗi để dễ debug
    echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ, không thể đổi mật khẩu.']);
}