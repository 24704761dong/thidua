<?php
// File: src/controllers/api_ctv_luu_nhieu_vi_pham.php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['student_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập.']);
    exit();
}

require_once __DIR__ . '/../../config/database.php';
$data = json_decode(file_get_contents('php://input'), true);
$violations = $data['violations'] ?? null;
$response = ['success' => false, 'message' => 'Dữ liệu không hợp lệ.'];
$ctv_id = $_SESSION['student_id'];

if (!$violations || !is_array($violations)) {
    echo json_encode($response);
    exit();
}

$db = get_db_connection();
$created_count = 0;
$updated_count = 0;
$saved_ids = []; 

try {
    $db->beginTransaction();
    $sql_insert = "INSERT INTO vi_pham_tam_thoi (tuan_hoc_id, nguoi_nhap_id, hoc_sinh_id, vi_pham_id, ngay_vi_pham, ghi_chu, raw_ho_ten, raw_ten_lop, trang_thai_gui, thoi_gian_nhap) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'nhap', ?)";
    $stmt_insert = $db->prepare($sql_insert);
    
    // Chỉ cập nhật nếu vi phạm vẫn ở trạng thái nháp và là của chính người nhập
    $sql_update = "UPDATE vi_pham_tam_thoi SET hoc_sinh_id=?, vi_pham_id=?, ngay_vi_pham=?, ghi_chu=?, raw_ho_ten=?, raw_ten_lop=? 
                   WHERE id=? AND nguoi_nhap_id=? AND trang_thai_gui='nhap'";
    $stmt_update = $db->prepare($sql_update);

    foreach ($violations as $vp) {
        $hoc_sinh_id = !empty($vp['hoc_sinh_id']) ? (int)$vp['hoc_sinh_id'] : null;
        $vi_pham_id = !empty($vp['cau_hinh_vi_pham_id']) ? (int)$vp['cau_hinh_vi_pham_id'] : null;

        if (empty($vp['id'])) { 
            $stmt_insert->execute([
                $vp['tuan_hoc_id'],
                $ctv_id,
                $hoc_sinh_id,
                $vi_pham_id,
                $vp['ngay_vi_pham'],
                $vp['ghi_chu'],
                $vp['ten_hoc_sinh_raw'],
                $vp['ten_lop_raw'],
                date('Y-m-d H:i:s')
            ]);
            $created_count++;
            $saved_ids[] = $db->lastInsertId();
        } else {
            $stmt_update->execute([
                $hoc_sinh_id,
                $vi_pham_id,
                $vp['ngay_vi_pham'],
                $vp['ghi_chu'],
                $vp['ten_hoc_sinh_raw'],
                $vp['ten_lop_raw'],
                $vp['id'],
                $ctv_id
            ]);
            $updated_count++;
            $saved_ids[] = $vp['id'];
        }
    }
    $db->commit();
    $response = [
        'success' => true, 
        'message' => "Lưu nháp thành công! Tạo mới: {$created_count}, Cập nhật: {$updated_count}.",
        'saved_ids' => $saved_ids
    ];
} catch (Exception $e) {
    if($db->inTransaction()) {
        $db->rollBack();
    }
    $response['message'] = 'Lỗi khi lưu vào CSDL: ' . $e->getMessage();
    http_response_code(500);
}
echo json_encode($response);
