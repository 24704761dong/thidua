<?php
// File: src/controllers/api_admin_dang_ky_truc_xoa.php
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

    // Lấy thông tin
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

    // Thu hồi quyền nếu danh sách này đã được duyệt và có cấp quyền
    if ($roster['trang_thai'] === 'Đã duyệt' || $roster['trang_thai'] === 'Da duyet') {
        $quyen_da_cap = json_decode($roster['quyen_da_cap'] ?: '[]', true);
        if (!empty($quyen_da_cap) && is_array($quyen_da_cap)) {
            $stmt_chi_tiet = $db->prepare("SELECT DISTINCT hoc_sinh_id FROM dang_ky_truc_chi_tiet WHERE dang_ky_truc_tuan_id = ?");
            $stmt_chi_tiet->execute([$id]);
            $student_ids = $stmt_chi_tiet->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($student_ids)) {
                $stmt_get_perm = $db->prepare("SELECT quyen_truy_cap FROM hoc_sinh WHERE id = ?");
                $stmt_update_perm = $db->prepare("UPDATE hoc_sinh SET quyen_truy_cap = ? WHERE id = ?");
                foreach ($student_ids as $hs_id) {
                    $stmt_get_perm->execute([$hs_id]);
                    $current_permissions = json_decode($stmt_get_perm->fetchColumn() ?: '{}', true);
                    foreach ($quyen_da_cap as $perm) {
                        unset($current_permissions[$perm]);
                    }
                    $stmt_update_perm->execute([json_encode($current_permissions), $hs_id]);
                }
            }
        }
    }

    // Lấy thông tin người gửi để TB
    $nguoi_gui_id = $roster['nguoi_gui_id'];
    $stmt_hs_gui = $db->prepare("SELECT hs.ma_hoc_sinh, u.email, (CONCAT(hs.ho_dem, ' ', hs.ten)) as ho_ten 
                                 FROM hoc_sinh hs 
                                 LEFT JOIN users u ON hs.ma_hoc_sinh = u.ten_dang_nhap 
                                 WHERE hs.id = ?");
    $stmt_hs_gui->execute([$nguoi_gui_id]);
    $hs_gui = $stmt_hs_gui->fetch();

    // Thay đổi logic: Thay vì xóa hoàn toàn khỏi DB, ta chuyển trạng thái lưu trữ (Soft Delete)
    // Để có thể giữ lại lịch sử. Không xóa bảng chi tiết để bảng Excel xuất ra vẫn có dữ liệu học sinh trực.
    $stmt_del = $db->prepare("UPDATE dang_ky_truc_tuan SET trang_thai_luu_tru = 1 WHERE id = ?");
    $stmt_del->execute([$id]);

    $tieu_de = "Lịch trực " . $roster['ten_tuan'] . " bị từ chối/xóa";
    $noi_dung = "Danh sách đăng ký trực " . $roster['ten_tuan'] . " của lớp " . $roster['ten_lop'] . " đã bị Admin từ chối hoặc xóa. Vui lòng xếp lại lịch trực nếu cần thiết.";

    // Gửi TB cho người gửi
    if ($hs_gui) {
        create_student_notification($db, $nguoi_gui_id, $tieu_de, $noi_dung, 'dang_ky_truc');

        if (!empty($hs_gui['email'])) {
            $email_body = "<div style='font-family: Arial, sans-serif; padding: 20px;'>
                <h2 style='color: #ef4444;'>Từ chối/Xóa danh sách trực</h2>
                <p>Chào {$hs_gui['ho_ten']},</p>
                <p>{$noi_dung}</p>
            </div>";
            queue_email($hs_gui['email'], $hs_gui['ho_ten'], "[Thi Đua] " . $tieu_de, $email_body, $noi_dung, 15);
        }
    }

    $db->commit();
    echo json_encode(['success' => true, 'message' => 'Xóa thành công']);
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
