<?php
// File: src/controllers/api_admin_dang_ky_truc_duyet.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_vai_tro'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../lib/helpers.php'; 

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$id = $_POST['id'] ?? null;
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Thiếu ID']);
    exit();
}

try {
    $db = get_db_connection();
    $db->beginTransaction();

    // Lấy thông tin tuần trực
    $stmt = $db->prepare("SELECT dkt.*, lh.ten_lop, th.ten_tuan 
                          FROM dang_ky_truc_tuan dkt 
                          JOIN raw_lop_hoc lh ON dkt.lop_hoc_id = lh.id 
                          JOIN raw_tuan_hoc th ON dkt.tuan_hoc_id = th.id 
                          WHERE dkt.id = ?");
    $stmt->execute([$id]);
    $roster = $stmt->fetch();

    if (!$roster) {
        throw new Exception("Không tìm thấy danh sách trực.");
    }

    if ($roster['trang_thai'] === 'Đã duyệt' || $roster['trang_thai'] === 'Da duyet') {
        throw new Exception("Danh sách này đã được duyệt trước đó.");
    }

    $permissions_to_grant = $_POST['permissions'] ?? [];
    if (!is_array($permissions_to_grant)) $permissions_to_grant = [];
    $allowed_perms = ['nhap_vi_pham', 'nhap_diem_danh', 'dang_ky_truc'];
    $permissions_to_grant = array_intersect($permissions_to_grant, $allowed_perms);
    
    $quyen_da_cap_json = json_encode($permissions_to_grant);

    // Cập nhật trạng thái
    $stmt_update = $db->prepare("UPDATE dang_ky_truc_tuan SET trang_thai = 'Đã duyệt', quyen_da_cap = ? WHERE id = ?");
    $stmt_update->execute([$quyen_da_cap_json, $id]);

    // Lấy các học sinh trong danh sách trực để cấp quyền
    $stmt_chi_tiet = $db->prepare("SELECT DISTINCT hoc_sinh_id FROM dang_ky_truc_chi_tiet WHERE dang_ky_truc_tuan_id = ?");
    $stmt_chi_tiet->execute([$id]);
    $student_ids = $stmt_chi_tiet->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($student_ids)) {
        $stmt_get_perm = $db->prepare("SELECT quyen_truy_cap FROM hoc_sinh WHERE id = ?");
        $stmt_update_perm = $db->prepare("UPDATE hoc_sinh SET quyen_truy_cap = ? WHERE id = ?");
        foreach ($student_ids as $hs_id) {
            $stmt_get_perm->execute([$hs_id]);
            $current_permissions = json_decode($stmt_get_perm->fetchColumn() ?: '{}', true);
            foreach ($permissions_to_grant as $perm) $current_permissions[$perm] = true;
            $stmt_update_perm->execute([json_encode($current_permissions), $hs_id]);
        }
    }

    // Lấy thông tin người gửi
    $nguoi_gui_id = $roster['nguoi_gui_id'];
    $stmt_hs_gui = $db->prepare("SELECT hs.ma_hoc_sinh, COALESCE(hs.email, u.email) as email, (CONCAT(hs.ho_dem, ' ', hs.ten)) as ho_ten 
                                 FROM hoc_sinh hs 
                                 LEFT JOIN users u ON hs.ma_hoc_sinh = u.ten_dang_nhap 
                                 WHERE hs.id = ?");
    $stmt_hs_gui->execute([$nguoi_gui_id]);
    $hs_gui = $stmt_hs_gui->fetch();

    $tieu_de = "Lịch trực " . $roster['ten_tuan'] . " đã được duyệt";
    $noi_dung = "Danh sách đăng ký trực " . $roster['ten_tuan'] . " của lớp " . $roster['ten_lop'] . " đã được Admin duyệt.";

    // Gửi TB cho người gửi
    if ($hs_gui) {
        create_student_notification($db, $nguoi_gui_id, $tieu_de, $noi_dung, 'dang_ky_truc');

        if (!empty($hs_gui['email'])) {
            $email_body = "<div style='font-family: Arial, sans-serif; padding: 20px;'>
                <h2 style='color: #16a34a;'>Đã duyệt danh sách trực</h2>
                <p>Chào {$hs_gui['ho_ten']},</p>
                <p>Danh sách đăng ký trực của lớp <strong>{$roster['ten_lop']}</strong> cho <strong>{$roster['ten_tuan']}</strong> mà bạn gửi đã được Admin phê duyệt thành công.</p>
                <p>Cảm ơn bạn đã cộng tác!</p>
            </div>";
            queue_email($hs_gui['email'], $hs_gui['ho_ten'], "[Thi Đua] " . $tieu_de, $email_body, $noi_dung, 15);
        }
    }

    // Prepare permission text
    $perm_names = [
        'nhap_vi_pham' => 'Nhập vi phạm',
        'nhap_diem_danh' => 'Nhập điểm danh',
        'dang_ky_truc' => 'Đăng ký trực'
    ];
    $granted_texts = array_map(function($p) use ($perm_names) { return $perm_names[$p] ?? $p; }, $permissions_to_grant);
    $perm_str = empty($granted_texts) ? "Không có quyền mới nào được cấp thêm." : "Hệ thống đã cấp thêm quyền: " . implode(', ', $granted_texts) . ".";

    // Lấy các học sinh trong danh sách trực để TB
    $stmt_chi_tiet = $db->prepare("SELECT DISTINCT ct.hoc_sinh_id, hs.ma_hoc_sinh, COALESCE(hs.email, u.email) as email, (CONCAT(hs.ho_dem, ' ', hs.ten)) as ho_ten 
                                   FROM dang_ky_truc_chi_tiet ct
                                   JOIN hoc_sinh hs ON ct.hoc_sinh_id = hs.id
                                   LEFT JOIN users u ON hs.ma_hoc_sinh = u.ten_dang_nhap
                                   WHERE ct.dang_ky_truc_tuan_id = ?");
    $stmt_chi_tiet->execute([$id]);
    $ds_hoc_sinh = $stmt_chi_tiet->fetchAll();

    $tieu_de_hs_truc = "Bạn được phân lịch trực " . $roster['ten_tuan'];
    $noi_dung_hs_truc = "Bạn đã được phân lịch trực trong tuần " . $roster['ten_tuan'] . ". " . $perm_str . " Vui lòng xem lịch chi tiết.";

    foreach ($ds_hoc_sinh as $hs) {
        create_student_notification($db, $hs['hoc_sinh_id'], $tieu_de_hs_truc, $noi_dung_hs_truc, 'dang_ky_truc');
        
        if (!empty($hs['email'])) {
            $email_body = "<div style='font-family: Arial, sans-serif; padding: 20px;'>
                <h2 style='color: #2563eb;'>Thông báo Lịch trực mới</h2>
                <p>Chào {$hs['ho_ten']},</p>
                <p>Bạn đã được phân lịch trực trong <strong>{$roster['ten_tuan']}</strong>.</p>
                <p><strong>{$perm_str}</strong> Hãy truy cập ứng dụng Zalo để hoàn thành tốt nhiệm vụ của mình nhé.</p>
            </div>";
            queue_email($hs['email'], $hs['ho_ten'], "[Thi Đua] " . $tieu_de_hs_truc, $email_body, $noi_dung_hs_truc, 15);
        }
    }

    $db->commit();
    echo json_encode(['success' => true, 'message' => 'Duyệt thành công']);
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
