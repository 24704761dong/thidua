<?php
// File: src/controllers/api_ctv_delete_violation.php (File mới)
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['student_id'])) exit();

require_once __DIR__ . '/../../config/database.php';
$data = json_decode(file_get_contents('php://input'), true);
$id_to_delete = $data['id'] ?? null;
$ctv_id = $_SESSION['student_id'];

if ($id_to_delete) {
    $db = get_db_connection();
    // Thêm điều kiện `nguoi_nhap_id` để đảm bảo CTV chỉ xóa được vi phạm của chính mình
    $stmt = $db->prepare("DELETE FROM vi_pham_tam_thoi WHERE id = ? AND nguoi_nhap_id = ? AND trang_thai_gui = 'nhap'");
    $stmt->execute([$id_to_delete, $ctv_id]);
    echo json_encode(['success' => true]);
}