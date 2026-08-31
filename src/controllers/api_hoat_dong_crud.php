<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['user_vai_tro'] !== 'admin' && !in_array('quan_ly_hoat_dong', $_SESSION['user_permissions'] ?? []) && !in_array('all', $_SESSION['user_permissions'] ?? []))) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once __DIR__ . '/../../config/database.php';
$db = get_db_connection();

$input = file_get_contents('php://input');
$data = json_decode($input, true);
$action = $data['action'] ?? ($_POST['action'] ?? '');

$nam_hoc_id = $_SESSION['nam_hoc_id'] ?? null;
if (!$nam_hoc_id) {
    $stmt = $db->query("SELECT id FROM nam_hoc WHERE is_mac_dinh = 1 LIMIT 1");
    $nam_hoc = $stmt->fetch();
    $nam_hoc_id = $nam_hoc ? $nam_hoc['id'] : 0;
}

try {
    if ($action === 'list') {
        $stmt = $db->prepare("
            SELECT hd.*, 
                   (SELECT COUNT(*) FROM hoat_dong_dang_ky hdk WHERE hdk.hoat_dong_id = hd.id) as dang_ky_count 
            FROM hoat_dong hd 
            WHERE hd.nam_hoc_id = ? 
            ORDER BY hd.id DESC
        ");
        $stmt->execute([$nam_hoc_id]);
        $activities = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $activities]);
    } 
    elseif ($action === 'get') {
        $id = $data['id'] ?? 0;
        $stmt = $db->prepare("SELECT * FROM hoat_dong WHERE id = ?");
        $stmt->execute([$id]);
        $activity = $stmt->fetch();
        if ($activity) {
            echo json_encode(['success' => true, 'data' => $activity]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy hoạt động']);
        }
    }
    elseif ($action === 'add' || $action === 'edit') {
        $ten_hoat_dong = trim($data['ten_hoat_dong'] ?? '');
        $mo_ta_ngan = trim($data['mo_ta_ngan'] ?? '');
        $diem_tich_luy = (float)($data['diem_tich_luy'] ?? 0);
        $so_luong_dang_ky = (int)($data['so_luong_dang_ky'] ?? 0);
        $doi_tuong = trim($data['doi_tuong'] ?? 'Tất cả');
        $thoi_gian_bd_dang_ky = !empty($data['thoi_gian_bd_dang_ky']) ? $data['thoi_gian_bd_dang_ky'] : null;
        $thoi_gian_kt_dang_ky = !empty($data['thoi_gian_kt_dang_ky']) ? $data['thoi_gian_kt_dang_ky'] : null;
        $show_tren_app = (int)($data['show_tren_app'] ?? 0);
        $trang_thai = (int)($data['trang_thai'] ?? 1);

        if (empty($ten_hoat_dong)) {
            echo json_encode(['success' => false, 'message' => 'Tên hoạt động không được để trống']);
            exit;
        }

        if ($action === 'add') {
            $stmt = $db->prepare("
                INSERT INTO hoat_dong 
                (nam_hoc_id, ten_hoat_dong, mo_ta_ngan, diem_tich_luy, so_luong_dang_ky, doi_tuong, thoi_gian_bd_dang_ky, thoi_gian_kt_dang_ky, show_tren_app, trang_thai) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $nam_hoc_id, $ten_hoat_dong, $mo_ta_ngan, $diem_tich_luy, $so_luong_dang_ky, 
                $doi_tuong, $thoi_gian_bd_dang_ky, $thoi_gian_kt_dang_ky, $show_tren_app, $trang_thai
            ]);
            echo json_encode(['success' => true, 'message' => 'Đã thêm hoạt động mới']);
        } else {
            $id = $data['id'] ?? 0;
            $stmt = $db->prepare("
                UPDATE hoat_dong SET 
                ten_hoat_dong = ?, mo_ta_ngan = ?, diem_tich_luy = ?, so_luong_dang_ky = ?, 
                doi_tuong = ?, thoi_gian_bd_dang_ky = ?, thoi_gian_kt_dang_ky = ?, show_tren_app = ?, trang_thai = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $ten_hoat_dong, $mo_ta_ngan, $diem_tich_luy, $so_luong_dang_ky, 
                $doi_tuong, $thoi_gian_bd_dang_ky, $thoi_gian_kt_dang_ky, $show_tren_app, $trang_thai, $id
            ]);
            echo json_encode(['success' => true, 'message' => 'Đã cập nhật hoạt động']);
        }
    }
    elseif ($action === 'delete') {
        $id = $data['id'] ?? 0;
        
        $db->beginTransaction();
        
        // Xoá danh sách đăng ký
        $stmt1 = $db->prepare("DELETE FROM hoat_dong_dang_ky WHERE hoat_dong_id = ?");
        $stmt1->execute([$id]);
        
        // Xoá hoạt động
        $stmt2 = $db->prepare("DELETE FROM hoat_dong WHERE id = ?");
        $stmt2->execute([$id]);
        
        $db->commit();
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log("Lỗi api_hoat_dong_crud: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
