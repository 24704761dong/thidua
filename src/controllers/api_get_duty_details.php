<?php
// File: src/controllers/api_get_duty_details.php (File mới)
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) exit();
require_once __DIR__ . '/../../config/database.php';

$registration_id = $_GET['id'] ?? null;
if (!$registration_id) exit();

$db = get_db_connection();
$stmt = $db->prepare("
    SELECT dtd.ngay_trong_tuan, hs.ho_dem, hs.ten
    FROM dang_ky_truc_chi_tiet dtd
    JOIN hoc_sinh hs ON dtd.hoc_sinh_id = hs.id
    WHERE dtd.dang_ky_truc_tuan_id = ?
    ORDER BY dtd.ngay_trong_tuan, hs.ten
");
$stmt->execute([$registration_id]);
$details = $stmt->fetchAll();

header('Content-Type: application/json');
echo json_encode($details);