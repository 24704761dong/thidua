<?php
// File: src/controllers/admin_duyet_vi_pham.php (File mới)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$db = get_db_connection();
$vi_pham_cho_duyet_grouped = [];

try {
    $nam_hoc_id = function_exists('get_current_nam_hoc_id') ? get_current_nam_hoc_id() : ($_SESSION['current_nam_hoc_id'] ?? 1);
    
    
    // Lấy danh sách các tuần học để làm bộ lọc
    $stmt_weeks = $db->prepare("SELECT id, ten_tuan FROM tuan_hoc WHERE nam_hoc_id = ? ORDER BY ngay_bat_dau DESC");
    $stmt_weeks->execute([$nam_hoc_id]);
    $weeks = $stmt_weeks->fetchAll();

    $filter_tuan_id = $_GET['tuan_id'] ?? '';
    $filter_trang_thai = $_GET['trang_thai'] ?? 'da_gui';
    
    // Lấy vi phạm theo trạng thái lọc
    $sql = "
        SELECT 
            vptt.id, vptt.ngay_vi_pham, vptt.ghi_chu, vptt.raw_ho_ten, vptt.raw_ten_lop,
            vptt.batch_id, vptt.thoi_gian_gui, vptt.tuan_hoc_id, vptt.trang_thai_gui,
            violator.trang_thai_hoc_tap,
            CONCAT(hs.ho_dem, ' ', hs.ten) as ten_ctv,
            lh.ten_lop as lop_ctv,
            chvp.ten_vi_pham,
            t.ten_tuan
        FROM vi_pham_tam_thoi vptt
        JOIN hoc_sinh hs ON vptt.nguoi_nhap_id = hs.id
        JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id
        JOIN tuan_hoc t ON vptt.tuan_hoc_id = t.id
        LEFT JOIN cau_hinh_vi_pham chvp ON vptt.vi_pham_id = chvp.id
        LEFT JOIN quatrinh_hoc_tap qt ON vptt.hoc_sinh_id = qt.id
        LEFT JOIN ho_so_hoc_sinh violator ON qt.ma_hoc_sinh = violator.ma_hoc_sinh
        WHERE t.nam_hoc_id = ?
    ";
    $params = [$nam_hoc_id];
    
    if ($filter_trang_thai !== 'tat_ca') {
        $sql .= " AND vptt.trang_thai_gui = ?";
        $params[] = $filter_trang_thai;
    }
    
    if ($filter_tuan_id !== '') {
        $sql .= " AND vptt.tuan_hoc_id = ?";
        $params[] = $filter_tuan_id;
    }

    $sql .= " ORDER BY vptt.thoi_gian_gui DESC, ten_ctv";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
    
    file_put_contents(__DIR__ . '/../../scratch/debug_duyet_vi_pham.log', "results count: " . count($results) . "\n", FILE_APPEND);

    // Gom nhóm các vi phạm theo batch_id (Nếu không có batch_id thì gom chung vào 1 nhóm legacy)
    foreach ($results as $item) {
        if ($item['batch_id']) {
            $key = $item['batch_id'];
        } else {
            $key = 'legacy_' . $item['ten_ctv'] . '_' . $item['lop_ctv'] . '_' . $item['tuan_hoc_id'];
        }
        
        if (!isset($vi_pham_cho_duyet_grouped[$key])) {
            $vi_pham_cho_duyet_grouped[$key] = [
                'batch_id' => $item['batch_id'],
                'ten_ctv' => $item['ten_ctv'],
                'lop_ctv' => $item['lop_ctv'],
                'ten_tuan' => $item['ten_tuan'],
                'thoi_gian_gui' => $item['thoi_gian_gui'],
                'items' => []
            ];
        }
        $vi_pham_cho_duyet_grouped[$key]['items'][] = $item;
    }

    // Lấy minh chứng cho các batch
    $batch_ids = array_filter(array_column($vi_pham_cho_duyet_grouped, 'batch_id'));
    if (!empty($batch_ids)) {
        $in_clause = str_repeat('?,', count($batch_ids) - 1) . '?';
        $stmt_proof = $db->prepare("SELECT id, batch_id, file_name, file_path, cloud_id, cloud_url, trang_thai FROM minh_chung_vi_pham WHERE batch_id IN ($in_clause)");
        $stmt_proof->execute(array_values($batch_ids));
        $proofs = $stmt_proof->fetchAll();
        
        $host = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/thidua/';
        $storage = null;
        foreach ($proofs as $p) {
            $url = '';
            if ($p['trang_thai'] === 'synced' && !empty($p['cloud_id'])) {
                if (!$storage) {
                    require_once __DIR__ . '/../lib/StorageService.php';
                    $storage = new StorageService();
                }
                try {
                    $url = $storage->getTemporaryUrl($p['cloud_id'], '+60 minutes');
                } catch (Exception $e) {
                    $url = $host . ltrim($p['file_path'], '/');
                }
            } elseif ($p['cloud_url']) {
                $url = $p['cloud_url'];
            } else {
                $url = $host . ltrim($p['file_path'], '/');
            }
            
            $vi_pham_cho_duyet_grouped[$p['batch_id']]['proofs'][] = [
                'file_name' => $p['file_name'],
                'url' => $url
            ];
        }
    }

} catch (Exception $e) {
    die("Lỗi CSDL: " . $e->getMessage());
}

require_once __DIR__ . '/../views/admin_duyet_vi_pham.php';