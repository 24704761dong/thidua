<?php
// File: src/controllers/api_ctv_luu_nhat_ky.php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['student_id']) || !($_SESSION['student_permissions']['so_nhat_ky_online'] ?? false)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập.']);
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);
$nhat_ky_id = $data['nhat_ky_id'] ?? null;
$loai_so = $data['loai_so'] ?? null;
// (int) để chuỗi rỗng / JSON từ client luôn thành số hợp lệ
$so_tiet_tot = (int)($data['so_tiet_tot'] ?? 0);
$so_tiet_kha = (int)($data['so_tiet_kha'] ?? 0);
$so_tiet_tb = (int)($data['so_tiet_tb'] ?? 0);
$so_tiet_yeu = (int)($data['so_tiet_yeu'] ?? 0);

if (!$nhat_ky_id || !$loai_so) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
    exit();
}

try {
    $db = get_db_connection();
    $current_nam_hoc = $_SESSION['current_nam_hoc_id'] ?? 1;

    $stmt_check = $db->prepare("
        SELECT 1 FROM so_nhat_ky_online snk 
        JOIN raw_tuan_hoc t ON snk.tuan_hoc_id = t.id 
        WHERE snk.id = ? AND snk.nguoi_nhap_id = ? AND t.nam_hoc_id = ?
    ");
    $stmt_check->execute([$nhat_ky_id, $_SESSION['student_id'], $current_nam_hoc]);
    if (!$stmt_check->fetch()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Sổ nhật kỳ không hợp lệ hoặc không thuộc năm học hiện tại.']);
        exit();
    }
    $stmt = $db->prepare(
        "UPDATE so_nhat_ky_chi_tiet 
         SET so_tiet_tot = ?, so_tiet_kha = ?, so_tiet_tb = ?, so_tiet_yeu = ?
         WHERE nhat_ky_id = ? AND loai_so = ?"
    );
    $stmt->execute([$so_tiet_tot, $so_tiet_kha, $so_tiet_tb, $so_tiet_yeu, $nhat_ky_id, $loai_so]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
}