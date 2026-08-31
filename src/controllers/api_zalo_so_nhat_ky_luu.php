<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('POST, OPTIONS');
zalo_handle_options();

try {
    $db = get_db_connection();
    
    $payload = zalo_authenticate_request();
    $student_id = $payload['student_id'];
    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    
    $nhat_ky_id = $data['nhat_ky_id'] ?? null;
    $tuan_hoc_id = $data['tuan_hoc_id'] ?? null;
    $loai_so = $data['loai_so'] ?? 'sdb_ck';

    if (!$nhat_ky_id && !$tuan_hoc_id) {
        throw new Exception("Thiếu thông tin nhật ký.");
    }

    if ($nhat_ky_id) {
        $stmt_check = $db->prepare("SELECT id, trang_thai, tuan_hoc_id, lop_hoc_id FROM so_nhat_ky_online WHERE id = ?");
        $stmt_check->execute([$nhat_ky_id]);
        $nhat_ky = $stmt_check->fetch(PDO::FETCH_ASSOC);
    } else {
        $stmt_check = $db->prepare("SELECT id, trang_thai, tuan_hoc_id, lop_hoc_id FROM so_nhat_ky_online WHERE tuan_hoc_id = ? AND nguoi_nhap_id = ?");
        $stmt_check->execute([$tuan_hoc_id, $student_id]);
        $nhat_ky = $stmt_check->fetch(PDO::FETCH_ASSOC);
    }

    if (!$nhat_ky) {
        // Lấy lớp học ID
        $stmt_hs = $db->prepare("SELECT qt.lop_hoc_id FROM quatrinh_hoc_tap qt JOIN ho_so_hoc_sinh hs ON qt.ma_hoc_sinh = hs.ma_hoc_sinh WHERE hs.id = ? ORDER BY qt.nam_hoc_id DESC LIMIT 1");
        $stmt_hs->execute([$student_id]);
        $lop_hoc_id = $stmt_hs->fetchColumn();

        if (!$lop_hoc_id) {
            throw new Exception("Không tìm thấy thông tin lớp học của bạn.");
        }

        $stmt_ins = $db->prepare("INSERT INTO so_nhat_ky_online (tuan_hoc_id, lop_hoc_id, nguoi_nhap_id, trang_thai) VALUES (?, ?, ?, 'nhap')");
        $stmt_ins->execute([$tuan_hoc_id, $lop_hoc_id, $student_id]);
        $nhat_ky_id = $db->lastInsertId();
        $nhat_ky = ['id' => $nhat_ky_id, 'trang_thai' => 'nhap'];
    } else {
        $nhat_ky_id = $nhat_ky['id'];
    }

    if ($nhat_ky['trang_thai'] === 'da_duyet' || $nhat_ky['trang_thai'] === 'da_gui') {
        throw new Exception("Sổ nhật ký này đã gửi hoặc đã duyệt, không thể chỉnh sửa.");
    }

    $so_tiet_tot = max(0, (int)($data['so_tiet_tot'] ?? 0));
    $so_tiet_kha = max(0, (int)($data['so_tiet_kha'] ?? 0));
    $so_tiet_tb  = max(0, (int)($data['so_tiet_tb'] ?? 0));
    $so_tiet_yeu = max(0, (int)($data['so_tiet_yeu'] ?? 0));

    // Kiểm tra xem đã có dòng cho loai_so này chưa
    $stmt_det = $db->prepare("SELECT id FROM so_nhat_ky_chi_tiet WHERE nhat_ky_id = ? AND loai_so = ?");
    $stmt_det->execute([$nhat_ky_id, $loai_so]);
    $det_id = $stmt_det->fetchColumn();

    if ($det_id) {
        $stmt_up = $db->prepare("UPDATE so_nhat_ky_chi_tiet SET so_tiet_tot = ?, so_tiet_kha = ?, so_tiet_tb = ?, so_tiet_yeu = ? WHERE id = ?");
        $stmt_up->execute([$so_tiet_tot, $so_tiet_kha, $so_tiet_tb, $so_tiet_yeu, $det_id]);
    } else {
        $stmt_ins_det = $db->prepare("INSERT INTO so_nhat_ky_chi_tiet (nhat_ky_id, loai_so, so_tiet_tot, so_tiet_kha, so_tiet_tb, so_tiet_yeu) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_ins_det->execute([$nhat_ky_id, $loai_so, $so_tiet_tot, $so_tiet_kha, $so_tiet_tb, $so_tiet_yeu]);
    }

    // Cập nhật trạng thái 'nhap' nếu là 'chua_nhap'
    $db->prepare("UPDATE so_nhat_ky_online SET trang_thai = 'nhap' WHERE id = ? AND trang_thai = 'chua_nhap'")->execute([$nhat_ky_id]);

    echo json_encode([
        'success' => true, 
        'message' => 'Đã lưu sổ nhật ký',
        'data' => [
            'nhat_ky_id' => $nhat_ky_id,
            'loai_so' => $loai_so
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
