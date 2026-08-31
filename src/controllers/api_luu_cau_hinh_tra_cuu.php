<?php
// File: src/controllers/api_luu_cau_hinh_tra_cuu.php (Cập nhật cho Phúc khảo)
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) { /* ... */ }

require_once __DIR__ . '/../../config/database.php';
$db = get_db_connection();
$data = json_decode(file_get_contents('php://input'), true);

$ky_thi_id = $data['ky_thi_id'] ?? null;
$action = $data['action'] ?? 'save_config';

if (!$ky_thi_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Thiếu ID Kỳ thi.']);
    exit();
 }

// Các phương thức hợp lệ
$valid_methods = ['sbd', 'cccd', 'moet', 'ten_ngaysinh'];
// Các trường thông tin cá nhân hợp lệ
$valid_info_fields = ['ho_ten', 'ngay_sinh', 'lop', 'sbd', 'cccd', 'ma_moet'];


try {
    $db->beginTransaction();

    if ($action === 'save_config') {
        // Lấy phương thức tra cứu duy nhất
        $phuong_thuc = $data['phuong_thuc_tra_cuu'] ?? null; // vd: 'sbd'
        // Lấy cấu hình hiển thị trường thông tin
        $truong_hien_thi_input = $data['truong_hien_thi'] ?? []; 
        $phuc_khao_xac_minh_input = $data['phuc_khao_xac_minh'] ?? [];// vd: ['ho_ten' => true, 'lop' => true]

        // --- Kiểm tra và chuẩn hóa dữ liệu ---
        // 1. Kiểm tra phương thức
        if (empty($phuong_thuc) || !in_array($phuong_thuc, $valid_methods)) {
            throw new Exception('Vui lòng chọn một Phương thức Tra cứu Chính hợp lệ.');
        }

        // 2. Lọc và chuẩn hóa cấu hình hiển thị (chỉ lấy các trường hợp lệ)
        $truong_hien_thi_save = [];
        foreach ($truong_hien_thi_input as $field => $is_checked) {
            if (in_array($field, $valid_info_fields) && $is_checked) {
                $truong_hien_thi_save[$field] = true;
            }
        }
        $truong_hien_thi_json = json_encode($truong_hien_thi_save);
        $phuc_khao_xac_minh_save = [];
        foreach ($phuc_khao_xac_minh_input as $field => $is_checked) {
            if (in_array($field, $valid_info_fields) && $is_checked) {
                 $phuc_khao_xac_minh_save[$field] = true;
            }
        }
        $phuc_khao_xac_minh_json = json_encode($phuc_khao_xac_minh_save);
        // --- Kết thúc kiểm tra ---


        // Cập nhật vào bảng ky_thi
        $stmt = $db->prepare("
            UPDATE ky_thi
            SET phuong_thuc_tra_cuu = ?, truong_hien_thi = ?, phuc_khao_xac_minh = ?
            WHERE id = ?
        ");
        $stmt->execute([$phuong_thuc, $truong_hien_thi_json, $phuc_khao_xac_minh_json, $ky_thi_id]);

        echo json_encode(['success' => true, 'message' => 'Đã lưu cấu hình tra cứu và phúc khảo!']);

    } elseif ($action === 'toggle_public') {
        // (Logic bật/tắt công khai giữ nguyên)
        $new_status = isset($data['cong_khai']) && $data['cong_khai'] ? 1 : 0;
        $stmt = $db->prepare("UPDATE ky_thi SET tra_cuu_cong_khai = ? WHERE id = ?");
        $stmt->execute([$new_status, $ky_thi_id]);
        echo json_encode(['success' => true, 'message' => 'Đã cập nhật trạng thái công khai!', 'new_status' => $new_status]);
    } else {
        throw new Exception('Hành động không hợp lệ.');
    }

    $db->commit();
} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    http_response_code(500); // Internal Server Error cho lỗi CSDL
    echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
 }
?>