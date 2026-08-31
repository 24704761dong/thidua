<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit();
}

if (!isset($_SESSION['tre_hoc_data'])) {
    echo json_encode(['success' => false, 'message' => 'Không có dữ liệu để xử lý.']); exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/lib/helpers.php';

$input = json_decode(file_get_contents('php://input'), true);
$tuan_id = $input['tuan_id'] ?? null;
$data_to_process = $_SESSION['tre_hoc_data'];

try {
    $db = get_db_connection();
    $loi_di_tre_id = get_setting($db, 'trehoc_loi_vi_pham');

    if (!$loi_di_tre_id) {
        throw new Exception("Vui lòng vào Cài đặt để liên kết lỗi vi phạm cho hành vi 'Đi trễ'.");
    }

    $db->beginTransaction();
    $stmt_insert = $db->prepare(
        "INSERT INTO vi_pham_hoc_sinh (tuan_hoc_id, hoc_sinh_id, vi_pham_id, ngay_vi_pham, nguoi_nhap_id, nguoi_nhap_type, ghi_chu) 
         VALUES (?, ?, ?, ?, ?, 'admin', 'Điểm danh')"
    );

    $violation_count = 0;
    foreach ($data_to_process as $student) {
        $stmt_insert->execute([
            $tuan_id,
            $student['hoc_sinh_id'],
            $loi_di_tre_id,
            $student['ngay_tre'],
            $_SESSION['user_id']
        ]);
        $violation_count++;
    }
    $db->commit();

    unset($_SESSION['tre_hoc_data']);

    echo json_encode(['success' => true, 'message' => "Hoàn tất! Đã tạo thành công {$violation_count} vi phạm 'Đi trễ'."]);

} catch (Exception $e) {
    if(isset($db) && $db->inTransaction()) $db->rollBack();
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}