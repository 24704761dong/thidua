<?php
// File: src/controllers/admin_exam_rooms.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../lib/exam_permissions.php';
require_once __DIR__ . '/../lib/exam_subjects.php';
require_once __DIR__ . '/../lib/exam_shift_manager.php';
require_once __DIR__ . '/../lib/exam_room_assignment.php';

// Bảo mật
if (!isset($_SESSION['user_id']) || !can_current_user_manage_exams()) {
    header('Location: /thidua/admin');
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$ky_thi_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$ky_thi_id) {
    header('Location: /thidua/admin/exam-list');
    exit();
}

$db = get_db_connection();
ensure_exam_subject_registration_schema($db);
ensure_exam_shift_schema($db);
ensure_exam_room_assignment_schema($db);

$ky_thi_info = null;
$ds_phong_thi = [];
$ds_ca_thi = [];
$stats = [
    'total_students' => 0,
    'assigned_students' => 0,
    'unassigned_students' => 0,
    'total_rooms' => 0,
    'total_shifts' => 0
];
$subject_catalog = exam_subject_catalog();

try {
    // 1. Lấy thông tin kỳ thi
    $stmt_ky_thi = $db->prepare("SELECT * FROM ky_thi WHERE id = ?");
    $stmt_ky_thi->execute([$ky_thi_id]);
    $ky_thi_info = $stmt_ky_thi->fetch();

    if (!$ky_thi_info) {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Không tìm thấy kỳ thi này.'];
        header('Location: /thidua/admin/exam-list');
        exit();
    }

    // 2. Lấy danh sách ca thi
    $ds_ca_thi = get_exam_shifts($db, $ky_thi_id);
    if (empty($ds_ca_thi)) {
        create_default_exam_shifts($db, $ky_thi_id);
        $ds_ca_thi = get_exam_shifts($db, $ky_thi_id);
    }
    $stats['total_shifts'] = count($ds_ca_thi);

    // 3. Lấy danh sách phòng thi
    $stmt_phong = $db->prepare("
        SELECT pt.*, COUNT(DISTINCT ktxp.ky_thi_hoc_sinh_id) as si_so_hien_tai
        FROM ky_thi_phong_thi pt
        LEFT JOIN ky_thi_xep_phong ktxp ON pt.id = ktxp.phong_thi_id AND ktxp.ky_thi_id = ?
        WHERE pt.ky_thi_id = ?
        GROUP BY pt.id
        ORDER BY pt.ten_phong ASC
    ");
    $stmt_phong->execute([$ky_thi_id, $ky_thi_id]);
    $ds_phong_thi = $stmt_phong->fetchAll(PDO::FETCH_ASSOC);
    $stats['total_rooms'] = count($ds_phong_thi);

    // 4. Lấy thống kê thí sinh
    $stmt_total = $db->prepare("SELECT COUNT(*) FROM ky_thi_hoc_sinh WHERE ky_thi_id = ?");
    $stmt_total->execute([$ky_thi_id]);
    $stats['total_students'] = (int)$stmt_total->fetchColumn();

    $stmt_assigned = $db->prepare("SELECT COUNT(DISTINCT ky_thi_hoc_sinh_id) FROM ky_thi_xep_phong WHERE ky_thi_id = ?");
    $stmt_assigned->execute([$ky_thi_id]);
    $stats['assigned_students'] = (int)$stmt_assigned->fetchColumn();
    $stats['unassigned_students'] = max(0, $stats['total_students'] - $stats['assigned_students']);

    // 5. Lấy ma trận phân bổ môn thi và lượt thi chi tiết theo từng phòng và từng ca thi
    $stmt_assignments = $db->prepare("
        SELECT 
            ktxp.ca_thi_id,
            ktxp.phong_thi_id,
            ktxp.luot_thi,
            ktxp.mon_thi,
            COUNT(DISTINCT ktxp.ky_thi_hoc_sinh_id) as count_hs
        FROM ky_thi_xep_phong ktxp
        WHERE ktxp.ky_thi_id = ?
        GROUP BY ktxp.ca_thi_id, ktxp.phong_thi_id, ktxp.luot_thi, ktxp.mon_thi
        ORDER BY ktxp.ca_thi_id ASC, ktxp.phong_thi_id ASC, ktxp.luot_thi ASC
    ");
    $stmt_assignments->execute([$ky_thi_id]);
    $raw_assignments = $stmt_assignments->fetchAll(PDO::FETCH_ASSOC);

    $room_shift_matrix = [];
    foreach ($raw_assignments as $row) {
        $cid = (int)($row['ca_thi_id'] ?: 0);
        $pid = (int)$row['phong_thi_id'];
        $luot = (int)$row['luot_thi'];
        $mon = $row['mon_thi'];
        $c_hs = (int)$row['count_hs'];

        $room_shift_matrix[$cid][$pid][$luot][$mon] = $c_hs;
    }

} catch (Exception $e) {
    error_log("Lỗi khi tải trang phòng thi: " . $e->getMessage());
    $error_message = "Lỗi CSDL khi tải dữ liệu.";
}

$page_title = 'Quản Lý Phòng Thi & Ca Thi: ' . htmlspecialchars($ky_thi_info['ten_ky_thi'] ?? '');
require_once __DIR__ . '/../views/admin_exam_rooms.php';