<?php
// File: src/controllers/api_luu_thi_dua.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập.']);
    exit();
}

require_once __DIR__ . '/../../config/database.php';
$data = json_decode(file_get_contents('php://input'), true);

$tuan_id = $data['tuan_id'] ?? null;
$lop_id = $data['lop_id'] ?? null;
$field = $data['field'] ?? null;
$value = $data['value'] ?? null;
$user_id = $_SESSION['user_id'];

// Đóng session để tránh block request khi lưu liên tục (Auto-save)
session_write_close();

$allowed_fields = ['so_tiet_tot', 'so_tiet_tb', 'sdb_tt', 'sdb_ck', 'sdb_nk', 'nhat_ky', 'diem_cong_tru'];
if (!$tuan_id || !$lop_id || !in_array($field, $allowed_fields) || $value === null) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
    exit();
}

// Xử lý giá trị chuỗi rỗng thành NULL (hoặc 0) cho các cột kiểu số
if ($value === '') {
    $value = null; 
}

try {
    $db = get_db_connection();
    $sql = "
        INSERT INTO thi_dua_tuan (tuan_hoc_id, lop_hoc_id, nguoi_nhap_id, last_updated, {$field})
        VALUES (?, ?, ?, NOW(), ?)
        ON DUPLICATE KEY UPDATE
            {$field} = VALUES({$field}),
            nguoi_nhap_id = VALUES(nguoi_nhap_id),
            last_updated = NOW();
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute([$tuan_id, $lop_id, $user_id, $value]);
    
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}