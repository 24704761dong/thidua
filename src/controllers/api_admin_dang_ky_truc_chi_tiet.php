<?php
// File: src/controllers/api_admin_dang_ky_truc_chi_tiet.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_vai_tro'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

$id = $_GET['id'] ?? null;
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Thiếu ID']);
    exit();
}

try {
    $db = get_db_connection();
    
    // Check if the roster exists
    $stmt = $db->prepare("SELECT id FROM dang_ky_truc_tuan WHERE id = ?");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        throw new Exception("Không tìm thấy danh sách đăng ký trực.");
    }

    $stmt_ct = $db->prepare("
        SELECT hs.ho_dem, hs.ten, '' as ten_khu_vuc, GROUP_CONCAT(ct.ngay_trong_tuan ORDER BY ct.ngay_trong_tuan ASC SEPARATOR ', ') as thu_trong_tuan
        FROM dang_ky_truc_chi_tiet ct
        JOIN hoc_sinh hs ON ct.hoc_sinh_id = hs.id
        JOIN raw_lop_hoc lh ON hs.lop_hoc_id = lh.id
        WHERE ct.dang_ky_truc_tuan_id = ?
        GROUP BY hs.id
        ORDER BY hs.ten ASC, hs.ho_dem ASC
    ");
    $stmt_ct->execute([$id]);
    $details = $stmt_ct->fetchAll();

    echo json_encode(['success' => true, 'data' => $details]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
