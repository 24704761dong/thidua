<?php
// File: src/controllers/api_get_attendance_details.php (Đã sửa)
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

// <<-- XÓA BỎ HOẶC VÔ HIỆU HÓA KHỐI LỆNH NÀY -->>
// if (!isset($_SESSION['user_id'])) { http_response_code(403); exit(); }

require_once __DIR__ . '/../../config/database.php';
$tuan_id = $_GET['tuan_id'] ?? 0;
$lop_id = $_GET['lop_id'] ?? 0;
$db = get_db_connection();
$sql = "SELECT ngay_diem_danh, vang_p, vang_kp, bo_tiet FROM diem_danh WHERE tuan_hoc_id = ? AND lop_hoc_id = ? ORDER BY ngay_diem_danh";
$stmt = $db->prepare($sql);
$stmt->execute([$tuan_id, $lop_id]);
echo json_encode($stmt->fetchAll());
?>