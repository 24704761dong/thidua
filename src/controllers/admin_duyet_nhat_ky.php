<?php
// File: src/controllers/admin_duyet_nhat_ky.php (Nâng cấp)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (
    !isset($_SESSION['user_id']) ||
    !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])
) {
    header('Location: /thidua/tracuu');
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$db = get_db_connection();

$current_nam_hoc = $_SESSION['current_nam_hoc_id'] ?? 1;

// 1. Lấy tất cả các tuần cho bộ lọc
$stmt_all_weeks = $db->prepare("SELECT id, ten_tuan FROM raw_tuan_hoc WHERE nam_hoc_id = ? ORDER BY ngay_bat_dau DESC");
$stmt_all_weeks->execute([$current_nam_hoc]);
$all_weeks = $stmt_all_weeks->fetchAll(PDO::FETCH_ASSOC);

// 2. Xác định tuần được chọn (hoặc tuần gần nhất nếu không có)
$selected_tuan_id = $_GET['tuan_id'] ?? ($all_weeks[0]['id'] ?? null);
$selected_tuan_info = null;

$data_for_view = [];

if ($selected_tuan_id) {
    foreach($all_weeks as $week) {
        if ($week['id'] == $selected_tuan_id) {
            $selected_tuan_info = $week;
            break;
        }
    }

    // 3. Lấy tất cả các lớp
    $stmt_all_classes = $db->prepare("SELECT id, ten_lop FROM raw_lop_hoc WHERE nam_hoc_id = ? ORDER BY CAST(SUBSTR(ten_lop, 1, 2) AS INTEGER), SUBSTR(ten_lop, 3, 1), CAST(SUBSTR(ten_lop, 4) AS INTEGER) ASC");
    $stmt_all_classes->execute([$current_nam_hoc]);
    $all_classes = $stmt_all_classes->fetchAll(PDO::FETCH_ASSOC);

    // 4. Lấy dữ liệu sổ nhật kỳ của tuần đã chọn
    $stmt_nhat_ky = $db->prepare("
        SELECT 
            snk.id, snk.lop_hoc_id, snk.trang_thai,
            snkc.loai_so, snkc.so_tiet_tot, snkc.so_tiet_kha, snkc.so_tiet_tb, snkc.so_tiet_yeu,
            (SELECT 1 FROM so_nhat_ky_minh_chung snkm WHERE snkm.nhat_ky_id = snk.id AND snkm.loai_minh_chung IN ('khac', 'minh_chung_khac', 'sdb_tt') LIMIT 1) as has_other_proofs
        FROM so_nhat_ky_online snk
        LEFT JOIN so_nhat_ky_chi_tiet snkc ON snk.id = snkc.nhat_ky_id
        WHERE snk.tuan_hoc_id = ?
    ");
    $stmt_nhat_ky->execute([$selected_tuan_id]);
    $nhat_ky_data_raw = $stmt_nhat_ky->fetchAll(PDO::FETCH_ASSOC);

    // 5. Gom nhóm dữ liệu đã lấy để dễ xử lý
    $nhat_ky_by_class = [];
    foreach ($nhat_ky_data_raw as $row) {
        $lop_id = $row['lop_hoc_id'];
        if (!isset($nhat_ky_by_class[$lop_id])) {
            $nhat_ky_by_class[$lop_id] = [
                'id' => $row['id'],
                'trang_thai' => $row['trang_thai'],
                'has_other_proofs' => $row['has_other_proofs'],
                'details' => []
            ];
        }
        $nhat_ky_by_class[$lop_id]['details'][$row['loai_so']] = $row;
    }

    // 6. Tạo mảng dữ liệu cuối cùng cho view
    foreach ($all_classes as $class) {
        $lop_id = $class['id'];
        $item_data = [];

        if (isset($nhat_ky_by_class[$lop_id])) {
            $item_data = [
                'lop_id' => $lop_id,
                'ten_lop' => $class['ten_lop'],
                'nhat_ky_id' => $nhat_ky_by_class[$lop_id]['id'],
                'trang_thai' => $nhat_ky_by_class[$lop_id]['trang_thai'],
                'has_other_proofs' => $nhat_ky_by_class[$lop_id]['has_other_proofs'],
                'details' => $nhat_ky_by_class[$lop_id]['details']
            ];
        } else {
            $item_data = [
                'lop_id' => $lop_id,
                'ten_lop' => $class['ten_lop'],
                'nhat_ky_id' => null,
                'trang_thai' => 'chua_nop',
                'has_other_proofs' => 0,
                'details' => []
            ];
        }

        // ================== BẮT ĐẦU TÍNH TOÁN TỔNG ==================
        $total_tot = 0; $total_kha = 0; $total_tb = 0; $total_yeu = 0;
        if (!empty($item_data['details'])) {
            foreach ($item_data['details'] as $detail) {
                $total_tot += (int)($detail['so_tiet_tot'] ?? 0);
                $total_kha += (int)($detail['so_tiet_kha'] ?? 0);
                $total_tb  += (int)($detail['so_tiet_tb'] ?? 0);
                $total_yeu += (int)($detail['so_tiet_yeu'] ?? 0);
            }
        }
        $item_data['total_tot'] = $total_tot;
        $item_data['total_kha'] = $total_kha;
        $item_data['total_tb']  = $total_tb;
        $item_data['total_yeu'] = $total_yeu;
        // =================== KẾT THÚC TÍNH TOÁN TỔNG ===================

        $data_for_view[] = $item_data;
    }
}

// Gọi view để hiển thị
require_once __DIR__ . '/../views/admin_duyet_nhat_ky.php';