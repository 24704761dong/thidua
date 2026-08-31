<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Nam-Hoc-Id');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../lib/zalo_jwt_helper.php';

$headers = function_exists('apache_request_headers') ? apache_request_headers() : (function_exists('getallheaders') ? getallheaders() : []);
$auth_header = $headers['Authorization'] ?? $headers['authorization'] ?? '';

if (!$auth_header || !preg_match('/Bearer\s(\S+)/i', $auth_header, $matches)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Token không hợp lệ hoặc bị thiếu.']);
    exit();
}

$jwt = $matches[1];
$payload = zalo_jwt_decode($jwt);

if (!$payload || !isset($payload['student_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Token đã hết hạn hoặc không hợp lệ.']);
    exit();
}

$student_id = $payload['student_id'];
$nam_hoc_header = null;
$headers_lower = array_change_key_case($headers, CASE_LOWER);
if (isset($headers_lower['x-nam-hoc-id'])) {
    $nam_hoc_header = $headers_lower['x-nam-hoc-id'];
}

try {
    $db = get_db_connection();

    // Xác định nam_hoc_id
    $nam_hoc_to_query = $nam_hoc_header;
    if (!$nam_hoc_to_query) {
        $stmt_nh = $db->prepare("SELECT MAX(nam_hoc_id) FROM quatrinh_hoc_tap WHERE ma_hoc_sinh = (SELECT ma_hoc_sinh FROM ho_so_hoc_sinh WHERE id = ?)");
        $stmt_nh->execute([$student_id]);
        $nam_hoc_to_query = $stmt_nh->fetchColumn();
    }

    $stmt = $db->prepare("SELECT id, ten_tuan FROM tuan_hoc WHERE nam_hoc_id = ? AND is_public = 1 ORDER BY ngay_bat_dau DESC");
    $stmt->execute([$nam_hoc_to_query]);
    $weeks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $weeks
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi Server: ' . $e->getMessage()]);
}
