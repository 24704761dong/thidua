<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('POST, GET, OPTIONS');
zalo_handle_options();

try {
    $db = get_db_connection();
    
    $payload = zalo_authenticate_request();
    $student_id = $payload['student_id'];
    $data = json_decode(file_get_contents('php://input'), true) ?? $_GET;
    $tuan_hoc_id = $data['tuan_hoc_id'] ?? null;

    if (!$tuan_hoc_id) {
        throw new Exception("Thiếu thông tin tuần học.");
    }

    $stmt_hs = $db->prepare("SELECT lop_hoc_id FROM hoc_sinh WHERE id = ?");
    $stmt_hs->execute([$student_id]);
    $lop_hoc_id = $stmt_hs->fetchColumn();

    if (!$lop_hoc_id) {
        throw new Exception("Không xác định được lớp học.");
    }

    $stmt_week = $db->prepare("SELECT ten_tuan, ngay_bat_dau, ngay_ket_thuc FROM raw_tuan_hoc WHERE id = ?");
    $stmt_week->execute([$tuan_hoc_id]);
    $week = $stmt_week->fetch(PDO::FETCH_ASSOC);

    if (!$week) {
        throw new Exception("Tuần học không tồn tại.");
    }

    // Lấy thông tin trạng thái nộp
    $stmt_status = $db->prepare("SELECT id, trang_thai FROM dang_ky_truc_tuan WHERE lop_hoc_id = ? AND tuan_hoc_id = ? AND trang_thai_luu_tru = 0");
    $stmt_status->execute([$lop_hoc_id, $tuan_hoc_id]);
    $registration = $stmt_status->fetch(PDO::FETCH_ASSOC);
    $is_locked = false;
    $status = 'Chưa nộp';
    $registration_id = null;
    if ($registration) {
        $registration_id = $registration['id'];
        $status = $registration['trang_thai'];
        if ($status === 'Đã duyệt') {
            $is_locked = true;
        }
    }

    // Danh sách học sinh trong lớp
    $stmt_students = $db->prepare("SELECT id, ho_dem, ten FROM hoc_sinh WHERE lop_hoc_id = ? ORDER BY ten, ho_dem");
    $stmt_students->execute([$lop_hoc_id]);
    $students_raw = $stmt_students->fetchAll(PDO::FETCH_ASSOC);
    $students = [];
    foreach ($students_raw as $s) {
        $students[] = [
            'id' => $s['id'],
            'name' => trim($s['ho_dem'] . ' ' . $s['ten'])
        ];
    }

    // Lịch trình theo ngày
    $days_of_week = ['Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy', 'Chủ Nhật'];
    $start_date = new DateTime($week['ngay_bat_dau']);
    $end_date = new DateTime($week['ngay_ket_thuc']);
    // Thêm 1 ngày vào end_date để DatePeriod bao gồm cả ngày kết thúc
    $end_date->modify('+1 day');
    $interval = DateInterval::createFromDateString('1 day');
    $period = new DatePeriod($start_date, $interval, $end_date);
    
    $schedule_days = [];
    foreach ($period as $dt) {
        $day_index = $dt->format('N') - 1; // 0 to 6
        $schedule_days[] = [
            'index' => $day_index,
            'name' => $days_of_week[$day_index],
            'date' => $dt->format('d/m'),
            'students' => []
        ];
    }

    // Lấy chi tiết đã đăng ký nếu có
    if ($registration_id) {
        $stmt_details = $db->prepare("SELECT ngay_trong_tuan, hoc_sinh_id FROM dang_ky_truc_chi_tiet WHERE dang_ky_truc_tuan_id = ?");
        $stmt_details->execute([$registration_id]);
        $details = $stmt_details->fetchAll(PDO::FETCH_ASSOC);
        
        $assigned = [];
        foreach ($details as $d) {
            $assigned[$d['ngay_trong_tuan']][] = $d['hoc_sinh_id'];
        }

        foreach ($schedule_days as &$sd) {
            if (isset($assigned[$sd['index']])) {
                $sd['students'] = $assigned[$sd['index']];
            }
        }
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'week_name' => $week['ten_tuan'],
            'status' => $status,
            'is_locked' => $is_locked,
            'students' => $students,
            'schedule' => $schedule_days
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
