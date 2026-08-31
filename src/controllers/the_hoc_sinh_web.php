<?php
// File: src/controllers/the_hoc_sinh_web.php
// Gom chung các giao diện (views) liên quan đến Thẻ học sinh

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}

require_once __DIR__ . '/../../config/database.php';
$db = get_db_connection();

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (strpos($uri, '/admin/the-hoc-sinh/danh-sach') !== false) {
    // --- DANH SÁCH & IN THẺ ---
    require_once __DIR__ . '/../lib/hoc_sinh_db.php'; 
    require_once __DIR__ . '/../lib/lop_hoc_db.php'; 
    
    $filter_khoi = $_GET['khoi'] ?? 'all';
    $filter_lop_id = $_GET['lop_id'] ?? 'all';
    
    $filters = [
        'khoi' => $filter_khoi,
        'lop_id' => $filter_lop_id
    ];
    
    $danh_sach_hoc_sinh = get_all_hoc_sinh($db, $filters);
    $danh_sach_lop = get_all_lop_hoc($db);
    
    $stmt_mau_the = $db->query("SELECT id, ten_mau, is_default FROM mau_the_hoc_sinh ORDER BY ten_mau");
    $danh_sach_tat_ca_mau_the = $stmt_mau_the->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt_nk = $db->query("SELECT DISTINCT nien_khoa FROM hoc_sinh WHERE nien_khoa IS NOT NULL AND nien_khoa != '' ORDER BY nien_khoa DESC");
    $danh_sach_nien_khoa = $stmt_nk->fetchAll(PDO::FETCH_COLUMN);
    
    require_once __DIR__ . '/../views/the_hoc_sinh_danh_sach.php';

} elseif (strpos($uri, '/admin/the-hoc-sinh/cai-dat') !== false) {
    // --- THIẾT KẾ MẪU THẺ ---
    require_once __DIR__ . '/../lib/helpers.php'; 
    
    $stmt_all = $db->query("SELECT id, ten_mau, is_default FROM mau_the_hoc_sinh ORDER BY ten_mau ASC");
    $danh_sach_mau_the = $stmt_all->fetchAll(PDO::FETCH_ASSOC);
    
    $mau_the_id = $_GET['id'] ?? null;
    $mau_the_dang_chon = null;
    
    // Sửa lỗi: id = 0 (hoặc chuỗi '0') bị tính là false trong PHP
    if ($mau_the_id !== null && $mau_the_id !== '') {
        $stmt_current = $db->prepare("SELECT * FROM mau_the_hoc_sinh WHERE id = ?");
        $stmt_current->execute([$mau_the_id]);
        $mau_the_dang_chon = $stmt_current->fetch(PDO::FETCH_ASSOC);
    } 
    
    // Nếu vẫn không tìm thấy mẫu nào (do id sai hoặc chưa chọn id), load mẫu mặc định
    if (!$mau_the_dang_chon && !empty($danh_sach_mau_the)) {
        foreach($danh_sach_mau_the as $mau) {
            if ($mau['is_default']) {
                $mau_the_id = $mau['id'];
                break;
            }
        }
        if (!$mau_the_id) {
            $mau_the_id = $danh_sach_mau_the[0]['id'];
        }
        $stmt_current = $db->prepare("SELECT * FROM mau_the_hoc_sinh WHERE id = ?");
        $stmt_current->execute([$mau_the_id]);
        $mau_the_dang_chon = $stmt_current->fetch(PDO::FETCH_ASSOC);
    }
    
    $card_template_warning = null;
    
    if (!empty($mau_the_dang_chon['cau_hinh_json'])) {
        $decoded = json_decode($mau_the_dang_chon['cau_hinh_json'], true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            $card_template_warning = 'Không đọc được dữ liệu mẫu thẻ hiện tại. Hệ thống tự động nạp phôi mặc định.';
            log_to_file('Card template JSON decode failed: ' . json_last_error_msg());
            $decoded = get_default_card_template();
        }
    } else {
        $decoded = get_default_card_template();
    }
    
    if (empty($decoded['background'])) {
        $decoded['background'] = '/thidua/public/assets/phoi_the_mac_dinh.png';
    }
    if (!isset($decoded['elements']) || !is_array($decoded['elements'])) {
        $decoded['elements'] = [];
    }
    
    require_once __DIR__ . '/../views/the_hoc_sinh_cai_dat.php';

} elseif (strpos($uri, '/admin/quan-ly-anh-the') !== false) {
    // --- QUẢN LÝ THƯ VIỆN ẢNH THẺ ---
    $stmt_students = $db->query("
        SELECT hs.id, hs.ma_hoc_sinh, hs.ho_dem, hs.ten, hs.anh_the, hs.anh_the_driver, hs.anh_the_cloud_key, hs.nien_khoa, hs.trang_thai_hoc_tap,
               (SELECT lh.ten_lop FROM quatrinh_hoc_tap qt JOIN raw_lop_hoc lh ON qt.lop_hoc_id = lh.id WHERE qt.ma_hoc_sinh = hs.ma_hoc_sinh ORDER BY qt.nam_hoc_id DESC LIMIT 1) as ten_lop
        FROM ho_so_hoc_sinh hs
    ");
    $all_students = $stmt_students->fetchAll(PDO::FETCH_ASSOC);
    
    $lookup = [];
    foreach ($all_students as $s) {
        if (!empty($s['anh_the'])) {
            $lookup[strtolower($s['anh_the'])] = $s;
        }
    }
    
    $stmt_lops = $db->query("SELECT DISTINCT ten_lop FROM raw_lop_hoc ORDER BY ten_lop");
    $lop_list = $stmt_lops->fetchAll(PDO::FETCH_ASSOC);

    $stmt_nk = $db->query("SELECT DISTINCT nien_khoa FROM ho_so_hoc_sinh WHERE nien_khoa IS NOT NULL AND nien_khoa != '' ORDER BY nien_khoa DESC");
    $danh_sach_nien_khoa = $stmt_nk->fetchAll(PDO::FETCH_COLUMN);
    
    $image_dir = __DIR__ . '/../../public/assets/anh_the/';
    if(!is_dir($image_dir)) { @mkdir($image_dir, 0777, true); }
    $image_files = array_diff(scandir($image_dir), ['.', '..']);
    
    $student_data = [];
    foreach ($image_files as $f) {
        $f_lower = strtolower($f);
        if (isset($lookup[$f_lower])) {
            $s = $lookup[$f_lower];
            $student_data[$f] = [
                'id' => $s['id'],
                'ma_hoc_sinh' => $s['ma_hoc_sinh'],
                'ho_dem' => $s['ho_dem'],
                'ten' => $s['ten'],
                'ten_lop' => $s['ten_lop'] ?? 'Chưa rõ',
                'nien_khoa' => $s['nien_khoa'] ?? 'Chưa rõ',
                'anh_the' => $f,
                'anh_the_driver' => $s['anh_the_driver'] ?? 'local',
                'anh_the_cloud_key' => $s['anh_the_cloud_key'] ?? null,
                'trang_thai_hoc_tap' => $s['trang_thai_hoc_tap'] ?? 'dang_hoc',
                'on_disk' => true,
                'is_cloud' => false
            ];
        } else {
            $student_data[$f] = [
                'ma_hoc_sinh' => 'Không khớp',
                'ho_dem' => 'Ảnh Đơn',
                'ten' => '',
                'ten_lop' => 'N/A',
                'nien_khoa' => 'N/A',
                'anh_the' => $f,
                'anh_the_driver' => 'local',
                'anh_the_cloud_key' => null,
                'trang_thai_hoc_tap' => 'dang_hoc',
                'on_disk' => true,
                'is_cloud' => false
            ];
        }
    }
    
    // Tìm các học sinh có ảnh trong DB nhưng mất file vật lý
    foreach ($all_students as $s) {
        if (!empty($s['anh_the']) && !isset($student_data[$s['anh_the']])) {
            $is_cloud = (!empty($s['anh_the_driver']) && $s['anh_the_driver'] !== 'local');
            $student_data[$s['anh_the']] = [
                'id' => $s['id'],
                'ma_hoc_sinh' => $s['ma_hoc_sinh'],
                'ho_dem' => $s['ho_dem'],
                'ten' => $s['ten'],
                'ten_lop' => $s['ten_lop'] ?? 'Chưa rõ',
                'nien_khoa' => $s['nien_khoa'] ?? 'Chưa rõ',
                'anh_the' => $s['anh_the'],
                'anh_the_driver' => $s['anh_the_driver'] ?? 'local',
                'anh_the_cloud_key' => $s['anh_the_cloud_key'] ?? null,
                'trang_thai_hoc_tap' => $s['trang_thai_hoc_tap'] ?? 'dang_hoc',
                'on_disk' => $is_cloud ? true : false,
                'is_cloud' => $is_cloud
            ];
        }
    }
    
    uksort($student_data, function($a, $b) use ($student_data) {
        $typeA = $student_data[$a]['ten'] !== '' ? 'matched' : 'orphan';
        $typeB = $student_data[$b]['ten'] !== '' ? 'matched' : 'orphan';
        if ($typeA !== $typeB) return $typeA === 'orphan' ? -1 : 1;
        
        $lopA = $student_data[$a]['ten_lop'];
        $lopB = $student_data[$b]['ten_lop'];
        if ($lopA !== $lopB) return strcmp($lopA, $lopB);
        
        return strcmp($a, $b);
    });
    
    require_once __DIR__ . '/../views/admin_quan_ly_anh_the.php';

} else {
    // --- TRANG CHỦ MODULE THẺ HỌC SINH (HUB) ---
    require_once __DIR__ . '/../views/the_hoc_sinh_hub.php';
}
