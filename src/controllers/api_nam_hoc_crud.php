<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$db = get_db_connection();
$data = json_decode(file_get_contents('php://input'), true);

$action = $data['action'] ?? '';

try {
    if ($action === 'add') {
        $ten = trim($data['ten_nam_hoc'] ?? '');
        $bd = !empty($data['ngay_bat_dau']) ? $data['ngay_bat_dau'] : null;
        $kt = !empty($data['ngay_ket_thuc']) ? $data['ngay_ket_thuc'] : null;

        if (empty($ten)) {
            echo json_encode(['success' => false, 'message' => 'Tên năm học không được để trống']);
            exit;
        }

        $stmt = $db->prepare("INSERT INTO nam_hoc (ten_nam_hoc, ngay_bat_dau, ngay_ket_thuc) VALUES (?, ?, ?)");
        $stmt->execute([$ten, $bd, $kt]);
        echo json_encode(['success' => true]);
        
    } elseif ($action === 'edit') {
        $id = (int)$data['id'];
        $ten = trim($data['ten_nam_hoc'] ?? '');
        $bd = !empty($data['ngay_bat_dau']) ? $data['ngay_bat_dau'] : null;
        $kt = !empty($data['ngay_ket_thuc']) ? $data['ngay_ket_thuc'] : null;

        $stmt = $db->prepare("UPDATE nam_hoc SET ten_nam_hoc = ?, ngay_bat_dau = ?, ngay_ket_thuc = ? WHERE id = ?");
        $stmt->execute([$ten, $bd, $kt, $id]);
        echo json_encode(['success' => true]);

    } elseif ($action === 'delete') {
        $id = (int)$data['id'];
        
        try {
            $db->beginTransaction();
            
            // First check if we can delete this nam_hoc
            $stmt = $db->prepare("SELECT id FROM nam_hoc WHERE id = ? AND is_default = 0");
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                $db->rollBack();
                echo json_encode(['success' => false, 'message' => 'Không thể xóa (có thể đây là năm học mặc định hoặc không tồn tại)']);
                exit;
            }

            // Find all tuan_hoc_id
            $stmtTuan = $db->prepare("SELECT id FROM raw_tuan_hoc WHERE nam_hoc_id = ? UNION SELECT id FROM tuan_hoc WHERE nam_hoc_id = ?");
            $stmtTuan->execute([$id, $id]);
            $tuanHocIds = $stmtTuan->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($tuanHocIds)) {
                $inQuery = implode(',', array_fill(0, count($tuanHocIds), '?'));
                
                // 1. Delete so_nhat_ky_chi_tiet via nhat_ky_id
                $stmtNK = $db->prepare("SELECT id FROM so_nhat_ky_online WHERE tuan_hoc_id IN ($inQuery)");
                $stmtNK->execute($tuanHocIds);
                $nhatKyIds = $stmtNK->fetchAll(PDO::FETCH_COLUMN);
                if (!empty($nhatKyIds)) {
                    $inQueryNK = implode(',', array_fill(0, count($nhatKyIds), '?'));
                    $db->prepare("DELETE FROM so_nhat_ky_chi_tiet WHERE nhat_ky_id IN ($inQueryNK)")->execute($nhatKyIds);
                }

                // 2. Delete related records by tuan_hoc_id
                $db->prepare("DELETE FROM vi_pham_hoc_sinh WHERE tuan_hoc_id IN ($inQuery)")->execute($tuanHocIds);
                $db->prepare("DELETE FROM so_nhat_ky_online WHERE tuan_hoc_id IN ($inQuery)")->execute($tuanHocIds);
                $db->prepare("DELETE FROM diem_danh_chi_tiet WHERE tuan_hoc_id IN ($inQuery)")->execute($tuanHocIds);
                $db->prepare("DELETE FROM diem_danh WHERE tuan_hoc_id IN ($inQuery)")->execute($tuanHocIds);
                $db->prepare("DELETE FROM thi_dua_tuan WHERE tuan_hoc_id IN ($inQuery)")->execute($tuanHocIds);
                
                $db->prepare("DELETE FROM raw_tuan_hoc WHERE nam_hoc_id = ?")->execute([$id]);
                $db->prepare("DELETE FROM tuan_hoc WHERE nam_hoc_id = ?")->execute([$id]);
            }

            // 3. Delete exam related data
            try {
                $stmtKyThi = $db->prepare("SELECT id FROM ky_thi WHERE nam_hoc_id = ?");
                $stmtKyThi->execute([$id]);
                $kyThiIds = $stmtKyThi->fetchAll(PDO::FETCH_COLUMN);
                if (!empty($kyThiIds)) {
                    $inKyThi = implode(',', array_fill(0, count($kyThiIds), '?'));
                    $db->prepare("DELETE FROM phuc_khao WHERE ky_thi_id IN ($inKyThi)")->execute($kyThiIds);
                    $db->prepare("DELETE FROM hoc_sinh_diem_thi WHERE ky_thi_id IN ($inKyThi)")->execute($kyThiIds);
                }
                $db->prepare("DELETE FROM ky_thi WHERE nam_hoc_id = ?")->execute([$id]);
            } catch (Exception $e) {}

            // 4. Delete other direct dependencies
            $tables = [
                'quatrinh_hoc_tap',
                'hoc_sinh',
                'ho_so_hoc_sinh',
                'lop_hoc',
                'raw_lop_hoc',
                'cau_hinh_vi_pham',
                'raw_cau_hinh_vi_pham',
                'dieu_kien_kxtd',
                'raw_dieu_kien_kxtd',
                'khen_thuong',
                'raw_khen_thuong',
                'he_thong_cai_dat',
                'hoat_dong',
                'hoc_sinh_ket_qua_hoc_tap',
                'khao_sat',
                'khao_sat_bai_lam',
                'ma_kich_hoat_ctv',
                'xin_vang_hoc'
            ];
            foreach($tables as $tbl) {
                try {
                    $db->prepare("DELETE FROM $tbl WHERE nam_hoc_id = ?")->execute([$id]);
                } catch (Exception $e) {}
            }

            // 5. Finally delete nam_hoc
            $stmt = $db->prepare("DELETE FROM nam_hoc WHERE id = ?");
            $stmt->execute([$id]);
            
            $db->commit();
            echo json_encode(['success' => true, 'message' => 'Đã xóa năm học và toàn bộ dữ liệu liên quan.']);
        } catch (Exception $e) {
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => 'Lỗi xóa năm học: ' . $e->getMessage()]);
        }
    } elseif ($action === 'set_default') {
        $id = (int)$data['id'];
        
        $db->beginTransaction();
        $db->exec("UPDATE nam_hoc SET is_default = 0");
        $stmt = $db->prepare("UPDATE nam_hoc SET is_default = 1 WHERE id = ?");
        $stmt->execute([$id]);
        $db->commit();
        
        // Tự động set session
        $_SESSION['working_nam_hoc_id'] = $id;
        $_SESSION['current_nam_hoc_id'] = $id;

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
    }
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
