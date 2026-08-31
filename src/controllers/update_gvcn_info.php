<?php
require_once __DIR__ . '/../../config/database.php';

$response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = get_db_connection();

    try {
        $db->beginTransaction();

        // === THÊM gvcn_email = ? VÀO CÂU LỆNH UPDATE ===
        $sql = "UPDATE lop_hoc SET gvcn_ten = ?, gvcn_ma = ?, gvcn_email = ?, gvcn_ngay_sinh = ?, gvcn_ghi_chu = ? WHERE id = ?";
        $stmt = $db->prepare($sql);

        // Lặp qua từng lớp được gửi lên từ form
        foreach ($_POST['lop_id'] as $index => $lop_id) {
            $gvcn_ten = $_POST['gvcn_ten'][$index] ?? '';
            $gvcn_ma = $_POST['gvcn_ma'][$index] ?? '';
            $gvcn_email = $_POST['gvcn_email'][$index] ?? ''; // Lấy dữ liệu email từ form
            $gvcn_ngay_sinh = $_POST['gvcn_ngay_sinh'][$index] ?? ''; // THÊM DÒNG NÀY
            $gvcn_ghi_chu = $_POST['gvcn_ghi_chu'][$index] ?? '';
            
            // === THÊM biến $gvcn_email VÀO MẢNG DỮ LIỆU ĐỂ THỰC THI ===
            $stmt->execute([$gvcn_ten, $gvcn_ma, $gvcn_email, $gvcn_ngay_sinh, $gvcn_ghi_chu, $lop_id]);
        }

        $db->commit();
        $response = ['success' => true];

    } catch (PDOException $e) {
        $db->rollBack();
        $response['message'] = 'Lỗi CSDL: ' . $e->getMessage();
    }
}

header('Content-Type: application/json');
echo json_encode($response);