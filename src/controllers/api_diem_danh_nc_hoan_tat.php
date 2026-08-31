<?php
// File: src/controllers/api_diem_danh_nc_hoan_tat.php
// PHIÊN BẢN SỬA LỖI: Đọc ID tuần và dữ liệu từ session một cách chính xác.

header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Kiểm tra sự tồn tại của session data
if (!isset($_SESSION['diem_danh_nc_data']) || !is_array($_SESSION['diem_danh_nc_data'])) {
    echo json_encode(['success' => false, 'message' => 'Không có dữ liệu điểm danh để xử lý.']);
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/lib/helpers.php';

// ===================================================================
//              BẮT ĐẦU PHẦN SỬA LỖI QUAN TRỌNG
// ===================================================================
// Lấy cả ID tuần và dữ liệu từ session, không dùng $input nữa
$session_data = $_SESSION['diem_danh_nc_data'];
$tuan_id = $session_data['tuan_id'] ?? null;
$data_to_process = $session_data['data'] ?? [];

// Thêm bước kiểm tra để đảm bảo ID tuần tồn tại
if (!$tuan_id) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ID Tuần học không được tìm thấy. Vui lòng thử import lại file.']);
    exit();
}
// ===================================================================
//               KẾT THÚC PHẦN SỬA LỖI
// ===================================================================

try {
    $db = get_db_connection();

    $loi_vang_p_id = get_setting($db, 'diemdanh_loi_vang_p');
    $loi_vang_kp_id = get_setting($db, 'diemdanh_loi_vang_kp');
    $loi_bo_tiet_id = get_setting($db, 'diemdanh_loi_bo_tiet');

    if (!$loi_vang_p_id || !$loi_vang_kp_id || !$loi_bo_tiet_id) {
        throw new Exception("Vui lòng vào Cài đặt để liên kết lỗi vi phạm cho cả 3 trường hợp: Vắng P, Vắng KP và Bỏ Tiết.");
    }

    $db->beginTransaction();

    $stmt_insert = $db->prepare(
        "INSERT INTO vi_pham_hoc_sinh (tuan_hoc_id, hoc_sinh_id, vi_pham_id, ngay_vi_pham, nguoi_nhap_id, nguoi_nhap_type, ghi_chu) 
         VALUES (?, ?, ?, ?, ?, 'admin', ?)"
    );

    $violation_count = 0;
    foreach ($data_to_process as $student) {
        foreach ($student['noi_dung_vang'] as $absence) {
            
            $violation_id = null;
            switch (strtoupper($absence['type'])) {
                case 'P':
                    $violation_id = $loi_vang_p_id;
                    break;
                case 'K':
                    $violation_id = $loi_vang_kp_id;
                    break;
                case 'BT':
                    $violation_id = $loi_bo_tiet_id;
                    break;
            }

            if ($violation_id) {
                $date_db_format = $absence['date'];
                $ghi_chu_chi_tiet = $absence['details'];

                $stmt_insert->execute([
                    $tuan_id,
                    $student['hoc_sinh_id'],
                    $violation_id,
                    $date_db_format,
                    $_SESSION['user_id'],
                    $ghi_chu_chi_tiet
                ]);
                $violation_count++;
            }
        }
    }

    $db->commit();
    unset($_SESSION['diem_danh_nc_data']);

    echo json_encode(['success' => true, 'message' => "Hoàn tất! Đã tạo thành công {$violation_count} vi phạm từ dữ liệu điểm danh."]);

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) $db->rollBack();
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}