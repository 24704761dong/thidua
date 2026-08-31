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
require_once __DIR__ . '/../lib/ThiDuaCalculator.php';

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
$tuan_id = $_GET['tuan_id'] ?? 0;

if (!$tuan_id) {
    echo json_encode(['success' => false, 'message' => 'Thiếu tuan_id']);
    exit();
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

    // Kiểm tra tuần này có public không
    $stmt_check = $db->prepare("SELECT is_public FROM tuan_hoc WHERE id = ? AND nam_hoc_id = ?");
    $stmt_check->execute([$tuan_id, $nam_hoc_to_query]);
    $is_public = $stmt_check->fetchColumn();

    if (!$is_public) {
        echo json_encode(['success' => false, 'message' => 'Tuần này chưa được công khai.']);
        exit();
    }

    $calculator = new ThiDuaCalculator($db, $nam_hoc_to_query);
    $raw_data = $calculator->calculateRawDataForWeek($tuan_id);
    $ranked_data = $calculator->rankWeeklyData($raw_data);

    // Format data cho frontend
    $results = [];
    foreach ($ranked_data as $lop_id => $data) {
        $results[] = [
            'lop_id' => $lop_id,
            'ten_lop' => $data['lop'], // Sửa từ ten_lop thành lop
            'khoi' => (int)substr($data['lop'], 0, 2),
            'so_tiet_tot' => $data['so_tiet_tot'] ?? 0,
            'so_tiet_tb' => $data['so_tiet_tb'] ?? 0,
            'diem_sdb' => $data['diem_sdb_thanh_phan'] ?? 0,
            'diem_cong_tru' => $data['diem_cong_tru'] ?? 0,
            'vang_kp' => $data['vang_kp'] ?? 0,
            'vang_p' => $data['vang_p'] ?? 0,
            'diem_noi_quy' => $data['diem_noi_quy'] ?? 0,
            'tong_diem' => round($data['tong_diem'], 2),
            'kxtd' => $data['kxtd'] ?? false,
            'xep_hang' => $data['xep_hang'] ?? '-'
        ];
    }
    
    // Sắp xếp theo tên lớp
    usort($results, function($a, $b) {
        return strnatcasecmp($a['ten_lop'], $b['ten_lop']);
    });

    echo json_encode([
        'success' => true,
        'data' => $results
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi Server: ' . $e->getMessage()]);
}
