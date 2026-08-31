<?php
// File: src/controllers/api_get_students_to_provision.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../config/database.php';
header('Content-Type: application/json');

// Bảo mật
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập.']);
    exit();
}

$scope = $_GET['scope'] ?? 'all';
$lop_id = $_GET['lop_id'] ?? null;

try {
    $db = get_db_connection();
    
    // Câu lệnh này chỉ LẤY DANH SÁCH, không CẬP NHẬT
    // Nó lấy những học sinh chưa có TK và CÓ ngày sinh
    $sql_select = "SELECT id, ho_dem, ten FROM hoc_sinh 
                   WHERE trang_thai_tai_khoan = 'Chưa cấp TK' 
                   AND ngay_sinh IS NOT NULL AND ngay_sinh != ''";
    $params = [];
    
    if ($scope === 'class' && !empty($lop_id)) {
        $sql_select .= " AND lop_hoc_id = ?";
        $params[] = $lop_id;
    } elseif ($scope !== 'all') {
        throw new Exception('Lựa chọn không hợp lệ.');
    }

    $stmt_select = $db->prepare($sql_select);
    $stmt_select->execute($params);
    $students_to_provision = $stmt_select->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'students' => $students_to_provision]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>