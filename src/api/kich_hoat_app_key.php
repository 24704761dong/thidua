<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$app_key = $input['app_key'] ?? '';
$machine_name = $input['machine_name'] ?? 'Không rõ';
$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Không rõ';

if (empty($app_key)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng cung cấp App Key']);
    exit();
}

require_once __DIR__ . '/../../config/database.php';

try {
    $db = get_db_connection();
    
    // Kiểm tra xem key có tồn tại không
    $stmt = $db->prepare("SELECT id FROM users WHERE app_key = ?");
    $stmt->execute([$app_key]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'App Key không hợp lệ hoặc không tồn tại']);
        exit();
    }
    
    // Cập nhật thông tin kích hoạt
    $update_stmt = $db->prepare("UPDATE users SET app_key_ip = ?, app_key_machine = ?, app_key_activated_at = NOW() WHERE app_key = ?");
    $update_stmt->execute([$ip_address, $machine_name, $app_key]);
    
    echo json_encode(['success' => true, 'message' => 'Kích hoạt thành công']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
}
