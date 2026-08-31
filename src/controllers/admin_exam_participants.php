<?php
// File: src/controllers/admin_exam_participants.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../lib/exam_permissions.php';
require_once __DIR__ . '/../lib/exam_subjects.php';

// Bảo mật
if (!isset($_SESSION['user_id']) || !can_current_user_manage_exams()) {
    header('Location: /thidua/admin');
    exit();
}

require_once __DIR__ . '/../../config/database.php';

// Lấy ID kỳ thi từ URL
$ky_thi_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$ky_thi_id) {
    header('Location: /thidua/admin/exam-list');
    exit();
}

$db = get_db_connection();
ensure_exam_subject_registration_schema($db);
$ky_thi_info = null;
$ds_hoc_sinh = [];
$ds_lop_hoc = [];

try {
    // 1. Lấy thông tin kỳ thi
    $stmt_ky_thi = $db->prepare("SELECT * FROM ky_thi WHERE id = ?");
    $stmt_ky_thi->execute([$ky_thi_id]);
    $ky_thi_info = $stmt_ky_thi->fetch();

    if (!$ky_thi_info) {
        // Nếu không tìm thấy kỳ thi
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Không tìm thấy kỳ thi này.'];
        header('Location: /thidua/admin/exam-list');
        exit();
    }
    
    // 2. Lấy danh sách học sinh đã tham gia
    // Câu truy vấn này JOIN 4 bảng để lấy đủ thông tin bạn yêu cầu
    $sql_ds_hoc_sinh = "
        SELECT 
            kths.id as kths_id, -- ID của bản ghi trong bảng ky_thi_hoc_sinh (dùng để xóa)
            kths.so_bao_danh, 
            kths.ghi_chu,
            kths.dang_ky_mon_thi,
            hs.ma_moet, 
            hs.ma_hoc_sinh, 
            hs.ho_dem, 
            hs.ten, 
            hs.ngay_sinh, 
            hs.gioi_tinh,
            lh.ten_lop,
            ktpt.ten_phong
        FROM ky_thi_hoc_sinh kths
        JOIN hoc_sinh hs ON kths.hoc_sinh_id = hs.id
        JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id
        LEFT JOIN ky_thi_phong_thi ktpt ON kths.phong_thi_id = ktpt.id
        WHERE kths.ky_thi_id = ?
        -- Sắp xếp theo lớp, sau đó theo Tên (đã có collation Tiếng Việt)
        ORDER BY lh.ten_lop, hs.ten , hs.ho_dem 
    ";
    $stmt_ds_hoc_sinh = $db->prepare($sql_ds_hoc_sinh);
    $stmt_ds_hoc_sinh->execute([$ky_thi_id]);
    $ds_hoc_sinh = $stmt_ds_hoc_sinh->fetchAll();

    foreach ($ds_hoc_sinh as &$hoc_sinh) {
        $hoc_sinh['dang_ky_mon_labels'] = exam_subject_display_labels_from_raw($hoc_sinh['dang_ky_mon_thi'] ?? '');
    }
    unset($hoc_sinh);

    // 3. Lấy danh sách tất cả các lớp của năm học tương ứng (dùng cho modal)
    $nam_hoc_id = $ky_thi_info['nam_hoc_id'] ?? ($_SESSION['nam_hoc_id'] ?? 1);
    $stmt_lop = $db->prepare("SELECT id, ten_lop FROM lop_hoc WHERE nam_hoc_id = ? ORDER BY CAST(SUBSTR(ten_lop, 1, 2) AS INTEGER), SUBSTR(ten_lop, 3)");
    $stmt_lop->execute([$nam_hoc_id]);
    $ds_lop_hoc = $stmt_lop->fetchAll();

} catch (Exception $e) {
    error_log("Lỗi khi lấy DS học sinh kỳ thi: " . $e->getMessage());
    $error_message = "Lỗi CSDL khi tải dữ liệu.";
}

$page_title = 'DS Học sinh: ' . htmlspecialchars($ky_thi_info['ten_ky_thi']);

// Nạp file view
require_once __DIR__ . '/../views/admin_exam_participants.php';
?>