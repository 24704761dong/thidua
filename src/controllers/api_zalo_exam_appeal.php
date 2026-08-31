<?php
// File: src/controllers/api_zalo_exam_appeal.php
// API Quản lý và Nộp đơn phúc khảo bài thi của học sinh theo năm học

require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('GET, POST, OPTIONS');
zalo_handle_options();

$payload = zalo_authenticate_request();
$student_id = $payload['student_id'];

try {
    $db = get_db_connection();

    // Lấy năm học
    $nam_hoc_id = zalo_get_nam_hoc_id();
    if (!$nam_hoc_id) {
        $stmt_nh = $db->prepare("SELECT nam_hoc_id FROM quatrinh_hoc_tap WHERE ma_hoc_sinh = ? ORDER BY nam_hoc_id DESC LIMIT 1");
        $stmt_nh->execute([$payload['ma_hoc_sinh']]);
        $nam_hoc_id = $stmt_nh->fetchColumn();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Lấy danh sách các đơn phúc khảo học sinh đã nộp trong năm học này
        $stmt_appeals = $db->prepare("
            SELECT 
                pk.id as appeal_id,
                pk.ky_thi_id,
                pk.ky_thi_hoc_sinh_id,
                pk.thoi_gian_nop,
                pk.trang_thai,
                kt.ten_ky_thi,
                kths.so_bao_danh
            FROM ky_thi_phuc_khao pk
            JOIN ky_thi kt ON pk.ky_thi_id = kt.id
            JOIN ky_thi_hoc_sinh kths ON pk.ky_thi_hoc_sinh_id = kths.id
            WHERE kt.nam_hoc_id = ? AND kths.hoc_sinh_id = ?
            ORDER BY pk.thoi_gian_nop DESC
        ");
        $stmt_appeals->execute([$nam_hoc_id, $student_id]);
        $appeals = $stmt_appeals->fetchAll(PDO::FETCH_ASSOC);

        $formatted_appeals = [];
        foreach ($appeals as $app) {
            // Lấy chi tiết các môn phúc khảo
            $stmt_details = $db->prepare("
                SELECT id, mon_hoc_db_col, diem_goc, diem_tong_cu, diem_tong_moi, minh_chung_path
                FROM ky_thi_phuc_khao_chi_tiet
                WHERE phuc_khao_id = ?
            ");
            $stmt_details->execute([$app['appeal_id']]);
            $details = $stmt_details->fetchAll(PDO::FETCH_ASSOC);

            $formatted_appeals[] = [
                'appeal_id' => (int)$app['appeal_id'],
                'ky_thi_id' => (int)$app['ky_thi_id'],
                'ten_ky_thi' => $app['ten_ky_thi'],
                'so_bao_danh' => $app['so_bao_danh'],
                'thoi_gian_nop' => $app['thoi_gian_nop'],
                'trang_thai' => $app['trang_thai'], // 'cho_xu_ly', 'da_xu_ly', 'tu_choi'
                'subjects' => $details
            ];
        }

        // Lấy danh sách kỳ thi trong năm học có mở phúc khảo và học sinh có điểm
        $stmt_available = $db->prepare("
            SELECT 
                kt.id as ky_thi_id,
                kt.ten_ky_thi,
                kths.id as ky_thi_hoc_sinh_id,
                kths.so_bao_danh,
                ktdt.*
            FROM ky_thi kt
            JOIN ky_thi_hoc_sinh kths ON kt.id = kths.ky_thi_id
            JOIN ky_thi_diem_thi ktdt ON kths.id = ktdt.ky_thi_hoc_sinh_id
            WHERE kt.nam_hoc_id = ? AND kths.hoc_sinh_id = ?
            ORDER BY kt.ngay_bat_dau DESC
        ");
        $stmt_available->execute([$nam_hoc_id, $student_id]);
        $available_exams = $stmt_available->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'nam_hoc_id' => (int)$nam_hoc_id,
            'appeals' => $formatted_appeals,
            'available_exams' => $available_exams
        ]);
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $ky_thi_hoc_sinh_id = (int)($input['ky_thi_hoc_sinh_id'] ?? 0);
        $subjects = $input['subjects'] ?? []; // [{ mon_col: 'diem_toan', diem_goc: 6.5, reason: '' }]

        if (!$ky_thi_hoc_sinh_id || empty($subjects)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng chọn môn thi cần phúc khảo.']);
            exit();
        }

        // Kiểm tra quyền sở hữu bài thi của học sinh
        $stmt_check = $db->prepare("SELECT id, ky_thi_id FROM ky_thi_hoc_sinh WHERE id = ? AND hoc_sinh_id = ?");
        $stmt_check->execute([$ky_thi_hoc_sinh_id, $student_id]);
        $kths = $stmt_check->fetch();

        if (!$kths) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin bài thi của bạn.']);
            exit();
        }

        // Kiểm tra xem đã nộp đơn cho kỳ thi này chưa
        $stmt_existing = $db->prepare("SELECT id FROM ky_thi_phuc_khao WHERE ky_thi_hoc_sinh_id = ? AND trang_thai = 'cho_xu_ly'");
        $stmt_existing->execute([$ky_thi_hoc_sinh_id]);
        if ($stmt_existing->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Bạn đã gửi đơn phúc khảo cho kỳ thi này và đang chờ xử lý.']);
            exit();
        }

        $db->beginTransaction();

        $stmt_pk = $db->prepare("
            INSERT INTO ky_thi_phuc_khao (ky_thi_id, ky_thi_hoc_sinh_id, thoi_gian_nop, trang_thai)
            VALUES (?, ?, NOW(), 'cho_xu_ly')
        ");
        $stmt_pk->execute([$kths['ky_thi_id'], $ky_thi_hoc_sinh_id]);
        $phuc_khao_id = $db->lastInsertId();

        $stmt_detail = $db->prepare("
            INSERT INTO ky_thi_phuc_khao_chi_tiet (phuc_khao_id, mon_hoc_db_col, diem_goc, diem_tong_cu)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($subjects as $s) {
            $mon_col = trim($s['mon_col'] ?? '');
            $diem_goc = (float)($s['diem_goc'] ?? 0);
            if ($mon_col) {
                $stmt_detail->execute([$phuc_khao_id, $mon_col, $diem_goc, $diem_goc]);
            }
        }

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Gửi đơn phúc khảo thành công!'
        ]);
        exit();
    }

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    zalo_api_error('Lỗi khi xử lý phúc khảo.', 500, $e);
}
