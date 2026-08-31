<?php
// File: src/controllers/giao_vu.php (Đã nâng cấp khen thưởng & sinh nhật)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Giữ nguyên logic bảo vệ trang gốc của bạn
if (!isset($_SESSION['student_id'])) {
    header('Location: /thidua/tracuu');
    exit();
}

require_once __DIR__ . '/../../config/database.php';
$db = get_db_connection();
$student_id = $_SESSION['student_id'];

// 1. Lấy thông tin chi tiết của học sinh (giữ nguyên)
$stmt_student = $db->prepare("
    SELECT hs.*, lh.ten_lop, lh.gvcn_ten
    FROM hoc_sinh hs
    JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id
    WHERE hs.id = ?
");
$stmt_student->execute([$student_id]);
$student_info = $stmt_student->fetch();

if (!$student_info) {
    session_destroy();
    header('Location: /thidua/tracuu');
    exit();
}

// 2. Lấy lịch sử vi phạm của học sinh (giữ nguyên)
$stmt_violations = $db->prepare("
    SELECT vp.ngay_vi_pham, chvp.ten_vi_pham, chvp.diem_tru, vp.ghi_chu, th.ten_tuan
    FROM vi_pham_hoc_sinh vp
    JOIN cau_hinh_vi_pham chvp ON vp.vi_pham_id = chvp.id
    JOIN tuan_hoc th ON vp.tuan_hoc_id = th.id
    WHERE vp.hoc_sinh_id = ?
    ORDER BY vp.ngay_vi_pham DESC
");
$stmt_violations->execute([$student_id]);
$violations_list = $stmt_violations->fetchAll();

// ===== BẮT ĐẦU KHỐI NÂNG CẤP =====

// 3. LẤY KHEN THƯỞNG (Cả cá nhân và tập thể của lớp)
$stmt_commendations = $db->prepare("
    SELECT *, 'Cá nhân' as doi_tuong FROM khen_thuong 
    WHERE (hoc_sinh_id = :hoc_sinh_id OR hoc_sinh_id IN (SELECT id FROM quatrinh_hoc_tap WHERE ma_hoc_sinh = :ma_hs)) AND loai = 'ca_nhan'
    UNION ALL
    SELECT *, 'Tập thể lớp' as doi_tuong FROM khen_thuong 
    WHERE lop_hoc_id = :lop_hoc_id AND loai = 'tap_the'
    ORDER BY ngay_khen_thuong DESC
");
$stmt_commendations->execute([
    ':hoc_sinh_id' => $student_id,
    ':ma_hs' => $student_info['ma_hoc_sinh'] ?? '',
    ':lop_hoc_id' => $student_info['lop_hoc_id']
]);
$commendations_list = $stmt_commendations->fetchAll();

// 4. KIỂM TRA SINH NHẬT
date_default_timezone_set('Asia/Ho_Chi_Minh');
$is_birthday = false;
if (!empty($student_info['ngay_sinh'])) {
    // Dùng substr để lấy 'dd/mm' (an toàn hơn strtotime)
    // Nó sẽ lấy 5 ký tự đầu, ví dụ: "29/10"
    $birthday_month_day = substr($student_info['ngay_sinh'], 0, 5);
    
    // Lấy ngày tháng hiện tại, ví dụ: "29/10"
    $current_month_day = date('d/m');
    
    if ($birthday_month_day === $current_month_day) {
        $is_birthday = true;
    }
}
// ===== KẾT THÚC KHỐI NÂNG CẤP =====

// 5. Lấy quyền truy cập từ session (giữ nguyên)
$permissions = $_SESSION['student_permissions'] ?? [];

// Kiem tra quyen tam thoi cho viec nhap vi pham dua tren lich truc
if (empty($permissions['nhap_vi_pham'])) {
    $stmt_check_duty = $db->prepare("
        SELECT 1 
        FROM dang_ky_truc_tuan dkt
        JOIN dang_ky_truc_chi_tiet ct ON dkt.id = ct.dang_ky_truc_tuan_id
        JOIN raw_tuan_hoc th ON dkt.tuan_hoc_id = th.id
        WHERE ct.hoc_sinh_id = ? 
          AND dkt.trang_thai = 'Da duyet'
          AND CURDATE() BETWEEN th.ngay_bat_dau AND th.ngay_ket_thuc
    ");
    $stmt_check_duty->execute([$student_id]);
    if ($stmt_check_duty->fetchColumn()) {
        $permissions['nhap_vi_pham'] = true;
    }
}

// Gọi view để hiển thị
require_once __DIR__ . '/../views/giao_vu.php';