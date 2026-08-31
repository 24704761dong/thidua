<?php
// File: src/controllers/api_zalo_exam_schedule.php
// API Lấy lịch học / lịch thi và phòng thi của học sinh theo năm học

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

    // Lấy danh sách kỳ thi trong năm học mà học sinh tham gia
    $stmt = $db->prepare("
        SELECT 
            kt.id as ky_thi_id,
            kt.ten_ky_thi,
            kt.ngay_bat_dau,
            kt.ngay_ket_thuc,
            kt.trang_thai,
            kths.id as ky_thi_hoc_sinh_id,
            kths.so_bao_danh,
            kths.phong_thi_id,
            kths.dang_ky_mon_thi,
            kths.ghi_chu,
            pt.ten_phong,
            pt.si_so_toi_da
        FROM ky_thi kt
        JOIN ky_thi_hoc_sinh kths ON kt.id = kths.ky_thi_id
        LEFT JOIN ky_thi_phong_thi pt ON kths.phong_thi_id = pt.id
        WHERE kt.nam_hoc_id = ? AND kths.hoc_sinh_id = ?
        ORDER BY kt.ngay_bat_dau DESC, kt.id DESC
    ");
    $stmt->execute([$nam_hoc_id, $student_id]);
    $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $results = [];
    foreach ($exams as $exam) {
        // Lấy danh sách ca thi của kỳ thi này
        $stmt_shifts = $db->prepare("
            SELECT id, ten_ca, ngay_thi, gio_thi, so_luot_thi, danh_sach_mon, thu_tu
            FROM ky_thi_ca_thi
            WHERE ky_thi_id = ?
            ORDER BY ngay_thi ASC, gio_thi ASC, thu_tu ASC
        ");
        $stmt_shifts->execute([$exam['ky_thi_id']]);
        $shifts = $stmt_shifts->fetchAll(PDO::FETCH_ASSOC);

        $formatted_shifts = [];
        foreach ($shifts as $s) {
            $mon_list = json_decode($s['danh_sach_mon'] ?: '[]', true);
            $formatted_shifts[] = [
                'id' => $s['id'],
                'ten_ca' => $s['ten_ca'],
                'ngay_thi' => $s['ngay_thi'],
                'gio_thi' => $s['gio_thi'],
                'so_luot_thi' => $s['so_luot_thi'],
                'danh_sach_mon' => is_array($mon_list) ? $mon_list : []
            ];
        }

        $dang_ky_mon = json_decode($exam['dang_ky_mon_thi'] ?: '[]', true);

        $results[] = [
            'ky_thi_id' => (int)$exam['ky_thi_id'],
            'ten_ky_thi' => $exam['ten_ky_thi'],
            'ngay_bat_dau' => $exam['ngay_bat_dau'],
            'ngay_ket_thuc' => $exam['ngay_ket_thuc'],
            'trang_thai' => $exam['trang_thai'],
            'so_bao_danh' => $exam['so_bao_danh'] ?: 'Chưa có',
            'ten_phong' => $exam['ten_phong'] ?: 'Chưa xếp phòng',
            'dang_ky_mon_thi' => is_array($dang_ky_mon) ? $dang_ky_mon : [],
            'ghi_chu' => $exam['ghi_chu'] ?: '',
            'ca_thi' => $formatted_shifts
        ];
    }

    echo json_encode([
        'success' => true,
        'nam_hoc_id' => (int)$nam_hoc_id,
        'data' => $results
    ]);

} catch (Exception $e) {
    zalo_api_error('Lỗi khi tải lịch thi.', 500, $e);
}
