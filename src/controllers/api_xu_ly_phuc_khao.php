<?php
// File: src/controllers/api_xu_ly_phuc_khao.php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) { 
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập.']);
    exit();
}

require_once __DIR__ . '/../../config/database.php';
$db = get_db_connection();
$data = json_decode(file_get_contents('php://input'), true);

$action = $data['action'] ?? '';
$phuc_khao_id = $data['phuc_khao_id'] ?? null;
$chi_tiet_id = $data['chi_tiet_id'] ?? null; // ID của dòng chi tiết môn
$ky_thi_hoc_sinh_id = $data['ky_thi_hoc_sinh_id'] ?? null; // Cần để cập nhật bảng điểm chính
$mon_hoc_db_col = $data['mon_hoc_db_col'] ?? null; // Tên cột điểm chính (vd: 'diem_toan')

if (!$phuc_khao_id) {
    http_response_code(400); echo json_encode(['success' => false, 'message' => 'Thiếu ID đơn phúc khảo.']); exit();
}

try {
    $db->beginTransaction();

    if ($action === 'save_score') {
        // Lưu điểm phúc khảo cho 1 môn cụ thể
        $diem_tn_moi = filter_var($data['diem_tn_moi'] ?? null, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
        $diem_tl_moi = filter_var($data['diem_tl_moi'] ?? null, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
        
        // ================== BẮT ĐẦU NÂNG CẤP (RULE 1) ==================
        // TỰ ĐỘNG TÍNH TỔNG ĐIỂM
        // Nếu cả 2 đều null (do admin xóa trắng) thì tổng là null
        // Nếu 1 trong 2 có số, thì tổng là (số + 0)
        $diem_tong_moi = null;
        if ($diem_tn_moi !== null || $diem_tl_moi !== null) {
            $diem_tong_moi = (float)($diem_tn_moi ?? 0) + (float)($diem_tl_moi ?? 0);
            // Làm tròn 2 chữ số
            $diem_tong_moi = round($diem_tong_moi, 2);
        }
        // ================== KẾT THÚC NÂNG CẤP (RULE 1) ==================


        if ($chi_tiet_id === null || $ky_thi_hoc_sinh_id === null || $mon_hoc_db_col === null) {
            throw new Exception('Thiếu thông tin chi tiết môn hoặc ID học sinh.');
        }
        
        // 1. Cập nhật điểm mới vào bảng chi tiết phúc khảo
        $stmt_update_chitiet = $db->prepare("
            UPDATE ky_thi_phuc_khao_chi_tiet
            SET diem_tn_moi = ?, diem_tl_moi = ?, diem_tong_moi = ?
            WHERE id = ? AND phuc_khao_id = ?
        ");
        $stmt_update_chitiet->execute([$diem_tn_moi, $diem_tl_moi, $diem_tong_moi, $chi_tiet_id, $phuc_khao_id]);

        
        // 2. Cập nhật điểm TỔNG MỚI vào bảng điểm chính VÀ đánh dấu đã review
        
        // Logic tạo tên cột cờ (flag)
        $reviewed_col = '';
        if (in_array($mon_hoc_db_col, ['dtb_mon', 'diem_xt_tn', 'ket_qua'])) {
            $reviewed_col = 'reviewed_' . $mon_hoc_db_col;
        } else {
            $reviewed_col = 'reviewed_' . str_replace('diem_', '', $mon_hoc_db_col);
        }

        // Danh sách các cột cờ hợp lệ
        $allowed_reviewed_cols = [
            'reviewed_toan', 'reviewed_van', 'reviewed_ly', 'reviewed_hoa', 'reviewed_sinh',
            'reviewed_su', 'reviewed_dia', 'reviewed_gdktpl', 'reviewed_ngoai_ngu',
            'reviewed_cn_nn', 'reviewed_dtb_mon', 'reviewed_diem_xt_tn', 'reviewed_ket_qua'
        ];
        
         if (!in_array($reviewed_col, $allowed_reviewed_cols)) {
              throw new Exception("Cột đánh dấu phúc khảo không hợp lệ: $reviewed_col");
         }

        // Tạo dòng điểm chính nếu chưa có
         $stmt_insert_diem = $db->prepare("INSERT IGNORE INTO ky_thi_diem_thi (ky_thi_hoc_sinh_id) VALUES (?)");
         $stmt_insert_diem->execute([$ky_thi_hoc_sinh_id]);

         // Cập nhật điểm chính và cờ reviewed
        $stmt_update_main = $db->prepare("
            UPDATE ky_thi_diem_thi
            SET {$mon_hoc_db_col} = ?, {$reviewed_col} = 1
            WHERE ky_thi_hoc_sinh_id = ?
        ");
        $stmt_update_main->execute([$diem_tong_moi, $ky_thi_hoc_sinh_id]);

        // ================== BẮT ĐẦU NÂNG CẤP (RULE 3) ==================
        // 3. TỰ ĐỘNG CẬP NHẬT TRẠNG THÁI ĐƠN PHÚC KHẢO CHÍNH
        $stmt_mark = $db->prepare("UPDATE ky_thi_phuc_khao SET trang_thai = 'da_xu_ly' WHERE id = ?");
        $stmt_mark->execute([$phuc_khao_id]);
        // ================== KẾT THÚC NÂNG CẤP (RULE 3) ==================

        echo json_encode(['success' => true, 'message' => 'Đã lưu điểm phúc khảo và đánh dấu "Đã xử lý".']);

    } 
    // (Bỏ action 'mark_processed' vì đã gộp chung)
    else {
        throw new Exception('Hành động không hợp lệ.');
    }

    $db->commit();
} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>