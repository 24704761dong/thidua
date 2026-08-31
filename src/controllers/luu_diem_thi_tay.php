<?php
// File: src/controllers/luu_diem_thi_tay.php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) { /* ... */ }

require_once __DIR__ . '/../../config/database.php';
$db = get_db_connection();
$data = json_decode(file_get_contents('php://input'), true);

$kths_id = $data['ky_thi_hoc_sinh_id'] ?? null;
$column_name = $data['column_name'] ?? null;
$value = $data['value'] ?? null; // Có thể là null, số, hoặc text

// Danh sách các cột điểm hợp lệ (phải khớp với CSDL và $diem_columns_map)
$allowed_columns = [
    'diem_toan', 'diem_van', 'diem_ly', 'diem_hoa', 'diem_sinh', 'diem_su', 'diem_dia',
    'diem_gdktpl', 'diem_ngoai_ngu', 'diem_cn_nn', 'dtb_mon', 'diem_xt_tn', 'ket_qua'
];

if (!$kths_id || !$column_name || !in_array($column_name, $allowed_columns)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
    exit();
}

// Xử lý giá trị NULL đặc biệt
if ($value === null) {
    $param_value = null;
} elseif ($column_name !== 'ket_qua') { // Nếu là cột điểm số
    // Chuyển đổi thành float và kiểm tra lại (JavaScript đã kiểm tra nhưng PHP nên kiểm tra lại)
    $float_val = filter_var($value, FILTER_VALIDATE_FLOAT);
    if ($float_val === false || $float_val < 0 || $float_val > 10) {
        // Nếu không phải số hợp lệ hoặc ngoài khoảng, lưu là NULL
        $param_value = null;
    } else {
        $param_value = round($float_val, 2); // Làm tròn 2 chữ số
    }
} else { // Nếu là cột kết quả (text)
    $param_value = trim(strval($value));
}


try {
    $db->beginTransaction();

    // Dùng INSERT OR IGNORE để tạo dòng điểm nếu chưa có
    $stmt_insert = $db->prepare("INSERT IGNORE INTO ky_thi_diem_thi (ky_thi_hoc_sinh_id) VALUES (?)");
    $stmt_insert->execute([$kths_id]);

    // Sau đó UPDATE cột điểm cụ thể
    // !! Quan trọng: Phải kiểm tra $column_name để tránh SQL Injection !!
    // (Đã kiểm tra bằng in_array ở trên)
    $stmt_update = $db->prepare("UPDATE ky_thi_diem_thi SET {$column_name} = ? WHERE ky_thi_hoc_sinh_id = ?");
    $stmt_update->execute([$param_value, $kths_id]);

    $db->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
}
?>