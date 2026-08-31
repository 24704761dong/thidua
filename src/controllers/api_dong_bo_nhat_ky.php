<?php
// File: src/controllers/api_dong_bo_nhat_ky.php (File mới)

if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

// Bảo mật: Chỉ admin mới có quyền
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập.']);
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);
$tuan_id = $data['tuan_id'] ?? null;

if (!$tuan_id) {
    echo json_encode(['success' => false, 'message' => 'Thiếu ID của tuần học.']);
    exit();
}

try {
    $db = get_db_connection();
    $db->beginTransaction();

    $current_nam_hoc = $_SESSION['current_nam_hoc_id'] ?? 1;

    // 1. Lấy tất cả các Sổ nhật kỳ đã được duyệt trong tuần
    $stmt_journals = $db->prepare("
        SELECT snk.id, snk.lop_hoc_id 
        FROM so_nhat_ky_online snk
        JOIN raw_tuan_hoc t ON snk.tuan_hoc_id = t.id
        WHERE snk.tuan_hoc_id = ? AND snk.trang_thai = 'da_duyet' AND t.nam_hoc_id = ?
    ");
    $stmt_journals->execute([$tuan_id, $current_nam_hoc]);
    $approved_journals = $stmt_journals->fetchAll(PDO::FETCH_ASSOC);

    if (empty($approved_journals)) {
        throw new Exception('Không có Sổ Nhật Kỳ nào đã được duyệt trong tuần này để đồng bộ.');
    }

    $updated_class_count = 0;
    
    // 2. Chuẩn bị các câu lệnh để tái sử dụng
    $stmt_details = $db->prepare("SELECT so_tiet_tot, so_tiet_tb, so_tiet_yeu FROM so_nhat_ky_chi_tiet WHERE nhat_ky_id = ?");
    $stmt_proofs = $db->prepare("SELECT DISTINCT loai_minh_chung FROM so_nhat_ky_minh_chung WHERE nhat_ky_id = ?");
    
    $sql_update_thi_dua = "
        INSERT INTO thi_dua_tuan (tuan_hoc_id, lop_hoc_id, nguoi_nhap_id, last_updated, so_tiet_tot, so_tiet_tb, sdb_tt, sdb_ck, sdb_nk, nhat_ky)
        VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            so_tiet_tot = VALUES(so_tiet_tot),
            so_tiet_tb = VALUES(so_tiet_tb),
            sdb_tt = VALUES(sdb_tt),
            sdb_ck = VALUES(sdb_ck),
            sdb_nk = VALUES(sdb_nk),
            nhat_ky = VALUES(nhat_ky),
            nguoi_nhap_id = VALUES(nguoi_nhap_id),
            last_updated = NOW();
    ";
    $stmt_update_thi_dua = $db->prepare($sql_update_thi_dua);

    // 3. Lặp qua từng sổ đã duyệt để tính toán và cập nhật
    foreach ($approved_journals as $journal) {
        $nhat_ky_id = $journal['id'];
        $lop_hoc_id = $journal['lop_hoc_id'];

        // Tính tổng số tiết
        $stmt_details->execute([$nhat_ky_id]);
        $details = $stmt_details->fetchAll(PDO::FETCH_ASSOC);
        
        $total_tiet_tot = 0;
        $total_tiet_tb = 0;
        foreach ($details as $detail) {
            $total_tiet_tot += (int)($detail['so_tiet_tot'] ?? 0);
            $total_tiet_tb += (int)($detail['so_tiet_tb'] ?? 0);
            $total_tiet_tb += ((int)($detail['so_tiet_yeu'] ?? 0) * 2); // Tiết yếu = 2 tiết TB
        }

        // Kiểm tra minh chứng
        $stmt_proofs->execute([$nhat_ky_id]);
        $proof_types = $stmt_proofs->fetchAll(PDO::FETCH_COLUMN);
        
        $sdb_tt = in_array('sdb_tt', $proof_types) ? 1 : 0;
        $sdb_ck = in_array('sdb_ck', $proof_types) ? 1 : 0;
        $sdb_nk = in_array('sdb_nk', $proof_types) ? 1 : 0;
        $nhat_ky = in_array('khac', $proof_types) ? 1 : 0;
        
        // Cập nhật vào bảng thi_dua_tuan
        $stmt_update_thi_dua->execute([
            $tuan_id,
            $lop_hoc_id,
            $_SESSION['user_id'],
            $total_tiet_tot,
            $total_tiet_tb,
            $sdb_tt,
            $sdb_ck,
            $sdb_nk,
            $nhat_ky
        ]);
        $updated_class_count++;
    }

    $db->commit();

    echo json_encode(['success' => true, 'message' => "Đồng bộ thành công! Đã cập nhật dữ liệu cho {$updated_class_count} lớp."]);

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi Server: ' . $e->getMessage()]);
}