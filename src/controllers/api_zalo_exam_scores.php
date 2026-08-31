<?php
// File: src/controllers/api_zalo_exam_scores.php
// API Lấy điểm thi của học sinh theo năm học

require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('GET, OPTIONS');
zalo_handle_options();

$payload = zalo_authenticate_request();
$student_id = $payload['student_id'];

try {
    $db = get_db_connection();

    // Lấy năm học đang chọn từ header hoặc năm học mới nhất của học sinh
    $nam_hoc_id = zalo_get_nam_hoc_id();
    if (!$nam_hoc_id) {
        $stmt_nh = $db->prepare("SELECT nam_hoc_id FROM quatrinh_hoc_tap WHERE ma_hoc_sinh = ? ORDER BY nam_hoc_id DESC LIMIT 1");
        $stmt_nh->execute([$payload['ma_hoc_sinh']]);
        $nam_hoc_id = $stmt_nh->fetchColumn();
    }

    if (!$nam_hoc_id) {
        echo json_encode(['success' => true, 'data' => []]);
        exit();
    }

    // Danh sách tên môn hiển thị
    $subject_names = [
        'diem_toan' => 'Toán',
        'diem_van' => 'Ngữ Văn',
        'diem_ly' => 'Vật Lý',
        'diem_hoa' => 'Hóa Học',
        'diem_sinh' => 'Sinh Học',
        'diem_su' => 'Lịch Sử',
        'diem_dia' => 'Địa Lý',
        'diem_gdktpl' => 'GDKT-PL',
        'diem_ngoai_ngu' => 'Ngoại Ngữ',
        'diem_cn_nn' => 'CN-NN',
        'dtb_mon' => 'ĐTB Môn',
        'diem_xt_tn' => 'Điểm XT TN',
        'ket_qua' => 'Kết Quả'
    ];

    // Lấy danh sách kỳ thi và điểm thi của học sinh trong năm học này
    $stmt = $db->prepare("
        SELECT 
            kt.id as ky_thi_id,
            kt.ten_ky_thi,
            kt.ngay_bat_dau,
            kt.ngay_ket_thuc,
            kt.phuc_khao_xac_minh,
            kths.id as ky_thi_hoc_sinh_id,
            kths.so_bao_danh,
            pt.ten_phong,
            ktdt.*
        FROM ky_thi kt
        JOIN ky_thi_hoc_sinh kths ON kt.id = kths.ky_thi_id
        LEFT JOIN ky_thi_phong_thi pt ON kths.phong_thi_id = pt.id
        LEFT JOIN ky_thi_diem_thi ktdt ON kths.id = ktdt.ky_thi_hoc_sinh_id
        WHERE kt.nam_hoc_id = ? AND kths.hoc_sinh_id = ?
        ORDER BY kt.ngay_bat_dau DESC, kt.id DESC
    ");
    $stmt->execute([$nam_hoc_id, $student_id]);
    $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $results = [];
    foreach ($exams as $exam) {
        $has_score = false;
        $scores = [];

        foreach ($subject_names as $col => $name) {
            $val = $exam[$col] ?? null;
            if ($val !== null && $val !== '') {
                $has_score = true;
                $is_reviewed = !empty($exam['reviewed_' . str_replace('diem_', '', $col)]);
                $scores[] = [
                    'key' => $col,
                    'name' => $name,
                    'score' => is_numeric($val) ? (float)$val : $val,
                    'is_reviewed' => $is_reviewed
                ];
            }
        }

        // Cấu hình phúc khảo
        $phuc_khao_config = json_decode($exam['phuc_khao_xac_minh'] ?: '{}', true);

        // Kiểm tra học sinh đã nộp đơn phúc khảo kỳ thi này chưa
        $stmt_pk = $db->prepare("SELECT id, trang_thai, thoi_gian_nop FROM ky_thi_phuc_khao WHERE ky_thi_hoc_sinh_id = ? LIMIT 1");
        $stmt_pk->execute([$exam['ky_thi_hoc_sinh_id']]);
        $appeal_info = $stmt_pk->fetch(PDO::FETCH_ASSOC);

        $results[] = [
            'ky_thi_id' => (int)$exam['ky_thi_id'],
            'ky_thi_hoc_sinh_id' => (int)$exam['ky_thi_hoc_sinh_id'],
            'ten_ky_thi' => $exam['ten_ky_thi'],
            'ngay_bat_dau' => $exam['ngay_bat_dau'],
            'ngay_ket_thuc' => $exam['ngay_ket_thuc'],
            'so_bao_danh' => $exam['so_bao_danh'] ?: 'Chưa có',
            'ten_phong' => $exam['ten_phong'] ?: 'Chưa xếp phòng',
            'has_score' => $has_score,
            'scores' => $scores,
            'appeal_status' => $appeal_info ? $appeal_info['trang_thai'] : null,
            'appeal_time' => $appeal_info ? $appeal_info['thoi_gian_nop'] : null,
            'can_appeal' => !empty($phuc_khao_config) && $has_score
        ];
    }

    echo json_encode([
        'success' => true,
        'nam_hoc_id' => (int)$nam_hoc_id,
        'data' => $results
    ]);

} catch (Exception $e) {
    zalo_api_error('Lỗi khi tải điểm thi.', 500, $e);
}
