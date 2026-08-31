<?php
require_once __DIR__ . '/../../config/database.php';

$db = get_db_connection();

if (session_status() === PHP_SESSION_NONE) session_start();
$current_nam_hoc = $_SESSION['current_nam_hoc_id'] ?? 1;

// Thêm gvcn_email vào câu lệnh SELECT
$stmt = $db->prepare("
    SELECT 
        lh.id,
        lh.ten_lop,
        lh.gvcn_ten,
        lh.gvcn_ma,
        lh.gvcn_email,
        lh.gvcn_ngay_sinh,
        lh.gvcn_ghi_chu,
        COUNT(hs.id) as si_so
    FROM lop_hoc lh
    LEFT JOIN hoc_sinh hs ON lh.id = hs.lop_hoc_id AND hs.trang_thai_hoc_tap = 'dang_hoc'
    WHERE lh.nam_hoc_id = ?
    GROUP BY lh.id
    ORDER BY lh.ten_lop ASC
");
$stmt->execute([$current_nam_hoc]);

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($results);