<?php
// File: src/controllers/api_luu_vi_tri_icons.php (PHIÊN BẢN CHUẨN)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');



if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$json_data = file_get_contents('php://input');
$icon_positions = json_decode($json_data, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dữ liệu gửi lên không hợp lệ.']);
    exit();
}

try {
    $db = get_db_connection();
    $user_id = $_SESSION['user_id'];
    
    // Đảm bảo cột đủ rộng để chứa cấu hình (fix lỗi Data too long)
    try {
        $db->exec("ALTER TABLE users MODIFY vi_tri_icons LONGTEXT");
    } catch (Exception $e) {
        // Ignore if error
    }

    $stmt = $db->prepare("UPDATE users SET vi_tri_icons = ? WHERE id = ?");
    
    if ($stmt->execute([json_encode($icon_positions), $user_id])) {
        echo json_encode(['success' => true, 'message' => 'Đã lưu vị trí thành công!']);
    } else {
        $err = implode(", ", $stmt->errorInfo());
        throw new Exception('Lỗi khi cập nhật cơ sở dữ liệu: ' . $err);
    }
} catch (Exception $e) {
    error_log("Lỗi lưu vị trí icon: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ nội bộ.']);
}