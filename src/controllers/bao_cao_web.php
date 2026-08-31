<?php

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
// File: src/controllers/bao_cao_web.php
// Gom chung các giao diện liên quan đến Báo Cáo

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/ThiDuaCalculator.php';
$db = get_db_connection();
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($uri === '/thidua/bao-cao') {

// File: src/controllers/bao_cao_thong_ke.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}

// Controller này chỉ đơn giản là gọi view
require_once __DIR__ . '/../views/bao_cao_thong_ke.php';    exit();
}

if ($uri === '/thidua/bao-cao/thi-dua') {

// File: src/controllers/bao_cao_thi_dua.php (PHIÊN BẢN SỬA LỖI N/A)
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}







// 1. Lấy tuần và cài đặt
$all_weeks = $db->query("SELECT * FROM tuan_hoc ORDER BY ngay_bat_dau DESC")->fetchAll(PDO::FETCH_ASSOC);
$tuan_id = $_GET['tuan_id'] ?? ($_SESSION['current_week_id'] ?? null);
if (!$tuan_id && !empty($all_weeks)) {
    $tuan_id = $all_weeks[0]['id'];
}
$_SESSION['current_week_id'] = $tuan_id;

$tuan_hoc = null;
if ($tuan_id) {
    $stmt = $db->prepare("SELECT * FROM tuan_hoc WHERE id = ?");
    $stmt->execute([$tuan_id]);
    $tuan_hoc = $stmt->fetch(PDO::FETCH_ASSOC);
    $ghi_chu_bao_cao = $tuan_hoc['ghi_chu_bao_cao'] ?? '';
}

$settings = get_all_settings($db);

if (!$tuan_hoc) {
    $report_data = [];
    require_once __DIR__ . '/../views/bao_cao_thi_dua.php';
    exit();
}

// 2. Tính toán và xếp hạng
$calculator = new thiduaCalculator($db);
$raw_data = $calculator->calculateRawDataForWeek((int)$tuan_id);
// Dữ liệu cuối cùng là một danh sách phẳng đã được xếp hạng
$report_data = $calculator->rankWeeklyData($raw_data); 

// 3. Nạp file view
require_once __DIR__ . '/../views/bao_cao_thi_dua.php';    exit();
}

if ($uri === '/thidua/bao-cao/vi-pham') {

// File: src/controllers/bao_cao_vi_pham.php

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}





$all_weeks = $db->query("SELECT * FROM tuan_hoc ORDER BY ngay_bat_dau DESC")->fetchAll();
$tuan_id = $_GET['tuan_id'] ?? null;
if (!$tuan_id && !empty($all_weeks)) {
    $tuan_id = $all_weeks[1]['id'] ?? $all_weeks[0]['id'];
}

$tuan_hoc = null;
$danh_sach_vi_pham = [];

if ($tuan_id) {
    $stmt_tuan = $db->prepare("SELECT * FROM tuan_hoc WHERE id = ?");
    $stmt_tuan->execute([$tuan_id]);
    $tuan_hoc = $stmt_tuan->fetch();

    // === NÂNG CẤP LẦN 2: Thêm `ma_hoc_sinh` và `hoc_sinh_id` ===
    $sql = "
        SELECT 
            vp.hoc_sinh_id, -- Dùng để kiểm tra KXD chính xác
            hs.ma_hoc_sinh,  -- Cột mới để hiển thị
            hs.trang_thai_hoc_tap,
            COALESCE(CONCAT(hs.ho_dem, ' ', hs.ten), vp.raw_ho_ten) as ho_ten,
            COALESCE(lh.ten_lop, vp.raw_ten_lop) as ten_lop,
            vp.ngay_vi_pham,
            chvp.ten_vi_pham,
            vp.ghi_chu
        FROM vi_pham_hoc_sinh vp
        LEFT JOIN quatrinh_hoc_tap qt ON vp.hoc_sinh_id = qt.id LEFT JOIN hoc_sinh hs ON hs.ma_hoc_sinh = qt.ma_hoc_sinh AND hs.nam_hoc_id = qt.nam_hoc_id
        LEFT JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id
        LEFT JOIN cau_hinh_vi_pham chvp ON vp.vi_pham_id = chvp.id
        WHERE vp.tuan_hoc_id = ?
        ORDER BY
            CAST(SUBSTR(COALESCE(lh.ten_lop, vp.raw_ten_lop), 1, 2) AS INTEGER) ASC,
            SUBSTR(COALESCE(lh.ten_lop, vp.raw_ten_lop), 3, 1) ASC,
            CAST(SUBSTR(COALESCE(lh.ten_lop, vp.raw_ten_lop), 4) AS INTEGER) ASC,
            ho_ten  ASC,
            vp.ngay_vi_pham ASC
    ";
    
    $stmt_vp = $db->prepare($sql);
    $stmt_vp->execute([$tuan_id]);
    $danh_sach_vi_pham = $stmt_vp->fetchAll();
}

require_once __DIR__ . '/../views/bao_cao_vi_pham.php';    exit();
}

if ($uri === '/thidua/bao-cao/vi-pham-chung-theo-lop') {

// File: src/controllers/bao_cao_vi_pham_chung_theo_lop.php (Đã được kiểm tra và tối ưu hóa)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. BẢO MẬT: Luôn kiểm tra quyền truy cập đầu tiên
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}

// 2. NẠP CÁC TÀI NGUYÊN CẦN THIẾT


// 3. KHỞI TẠO KẾT NỐI VÀ BIẾN


// 4. LẤY DỮ LIỆU TỪ URL VÀ XÁC ĐỊNH TUẦN MẶC ĐỊNH
// Lấy danh sách tất cả các tuần để hiển thị trong bộ lọc
$all_weeks = $db->query("SELECT * FROM tuan_hoc ORDER BY ngay_bat_dau DESC")->fetchAll();

// Xác định tuần mặc định: tuần thứ 2 mới nhất (tuần trước đó)
$tuan_id = $_GET['tuan_id'] ?? null;
if (!$tuan_id && !empty($all_weeks)) {
    $tuan_id = $all_weeks[1]['id'] ?? $all_weeks[0]['id']; // Ưu tiên tuần thứ 2, nếu không có thì lấy tuần đầu tiên
}

$tuan_hoc = null;
$report_data = [];

// 5. NẾU CÓ TUẦN HỢP LỆ, BẮT ĐẦU TRUY VẤN DỮ LIỆU BÁO CÁO
if ($tuan_id) {
    // Lấy thông tin tuần đang xem
    $stmt_tuan = $db->prepare("SELECT * FROM tuan_hoc WHERE id = ?");
    $stmt_tuan->execute([$tuan_id]);
    $tuan_hoc = $stmt_tuan->fetch();

    // Lấy danh sách các mức điểm trừ có trong hệ thống để làm cột
    $stmt_diem = $db->query("SELECT DISTINCT diem_tru FROM cau_hinh_vi_pham ORDER BY diem_tru ASC");
    $diem_tru_levels = $stmt_diem->fetchAll(PDO::FETCH_COLUMN);

    // Lấy danh sách lớp và sĩ số để làm sườn cho báo cáo
    $lop_hoc = $db->query("
        SELECT lh.id, lh.ten_lop, COUNT(hs.id) as si_so 
        FROM lop_hoc lh 
        LEFT JOIN hoc_sinh hs ON hs.lop_hoc_id = lh.id 
        GROUP BY lh.id
           ORDER BY CAST(SUBSTRING(lh.ten_lop, 1, 2) AS UNSIGNED), SUBSTRING(lh.ten_lop, 3, 1), CAST(SUBSTRING(lh.ten_lop, 4) AS UNSIGNED) ASC
    ")->fetchAll();

    // Truy vấn mới để đếm vi phạm, bao gồm cả KXD
    $sql_vp = "
        SELECT 
            COALESCE(lh.ten_lop, vp.raw_ten_lop) as ten_lop_final,
            chvp.diem_tru,
            COUNT(vp.id) as so_luong
        FROM vi_pham_hoc_sinh vp
        LEFT JOIN quatrinh_hoc_tap qt ON vp.hoc_sinh_id = qt.id LEFT JOIN hoc_sinh hs ON hs.ma_hoc_sinh = qt.ma_hoc_sinh AND hs.nam_hoc_id = qt.nam_hoc_id
        LEFT JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id
        JOIN cau_hinh_vi_pham chvp ON vp.vi_pham_id = chvp.id
           WHERE vp.tuan_hoc_id = ? 
             AND COALESCE(lh.ten_lop, vp.raw_ten_lop) IS NOT NULL 
             AND COALESCE(lh.ten_lop, vp.raw_ten_lop) <> ''
        GROUP BY ten_lop_final, chvp.diem_tru
    ";
    $stmt_vp = $db->prepare($sql_vp);
    $stmt_vp->execute([$tuan_id]);
    $vi_pham_counts_raw = $stmt_vp->fetchAll();

    // Gom dữ liệu lại để view dễ xử lý
    // Khởi tạo dữ liệu từ danh sách lớp chuẩn
    foreach ($lop_hoc as $lop) {
        $report_data[$lop['ten_lop']] = [
            'ten_lop' => $lop['ten_lop'],
            'si_so' => $lop['si_so'],
            'vi_pham' => [],
            'tong_so_luong' => 0 // Thêm cột tổng số lượng
        ];
    }

    // Điền dữ liệu vi phạm vào
    foreach ($vi_pham_counts_raw as $count) {
        $ten_lop = $count['ten_lop_final'];
        // Nếu có lớp vi phạm mà không có trong danh sách lớp chính thức (ví dụ KXD), thêm nó vào báo cáo
        if (!isset($report_data[$ten_lop])) {
            $report_data[$ten_lop] = [
                'ten_lop' => $ten_lop,
                'si_so' => 'N/A',
                'vi_pham' => [],
                'tong_so_luong' => 0
            ];
        }
        $report_data[$ten_lop]['vi_pham'][$count['diem_tru']] = $count['so_luong'];
        $report_data[$ten_lop]['tong_so_luong'] += $count['so_luong'];
    }
}

// 6. GỌI VIEW ĐỂ HIỂN THỊ
require_once __DIR__ . '/../views/bao_cao_vi_pham_chung_theo_lop.php';    exit();
}

if ($uri === '/thidua/bao-cao/theo-ten-vi-pham') {

// File: src/controllers/bao_cao_theo_ten_vi_pham.php (Nâng cấp toàn diện)

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}





// Lấy danh sách tuần để hiển thị bộ lọc
$all_weeks = $db->query("SELECT * FROM tuan_hoc ORDER BY ngay_bat_dau DESC")->fetchAll();

// Xác định tuần đang xem
$tuan_id = $_GET['tuan_id'] ?? null;
if (!$tuan_id && !empty($all_weeks)) {
    $tuan_id = $all_weeks[1]['id'] ?? $all_weeks[0]['id'];
}

$tuan_hoc = null;
$report_data = [];

if ($tuan_id) {
    // Lấy thông tin tuần học
    $stmt_tuan = $db->prepare("SELECT * FROM tuan_hoc WHERE id = ?");
    $stmt_tuan->execute([$tuan_id]);
    $tuan_hoc = $stmt_tuan->fetch();

    // 1. Lấy danh sách các loại vi phạm và đếm số lần xuất hiện trong tuần
    $sql_summary = "
        SELECT
            chvp.id as vi_pham_id,
            chvp.ten_vi_pham,
            chvp.diem_tru,
            COUNT(vp.id) as so_lan_vi_pham
        FROM vi_pham_hoc_sinh vp
        JOIN cau_hinh_vi_pham chvp ON vp.vi_pham_id = chvp.id
        WHERE vp.tuan_hoc_id = ?
        GROUP BY chvp.id, chvp.ten_vi_pham, chvp.diem_tru
        ORDER BY so_lan_vi_pham DESC, chvp.diem_tru ASC
    ";
    $stmt_summary = $db->prepare($sql_summary);
    $stmt_summary->execute([$tuan_id]);
    $violation_summary = $stmt_summary->fetchAll();

    // 2. Với mỗi loại vi phạm, lấy danh sách chi tiết học sinh vi phạm (bao gồm cả KXD)
    $stmt_details = $db->prepare("
        SELECT 
            hs.ma_hoc_sinh,
            COALESCE(CONCAT(hs.ho_dem, ' ', hs.ten), vp.raw_ho_ten) as ho_ten,
            vp.ngay_vi_pham,
            vp.ghi_chu
        FROM vi_pham_hoc_sinh vp
        LEFT JOIN quatrinh_hoc_tap qt ON vp.hoc_sinh_id = qt.id LEFT JOIN hoc_sinh hs ON hs.ma_hoc_sinh = qt.ma_hoc_sinh AND hs.nam_hoc_id = qt.nam_hoc_id
        WHERE vp.tuan_hoc_id = ? AND vp.vi_pham_id = ?
        ORDER BY vp.ngay_vi_pham, ho_ten 
    ");

    foreach ($violation_summary as $summary) {
        $stmt_details->execute([$tuan_id, $summary['vi_pham_id']]);
        $details = $stmt_details->fetchAll();
        
        $report_data[] = [
            'summary' => $summary,
            'details' => $details
        ];
    }
}

require_once __DIR__ . '/../views/bao_cao_theo_ten_vi_pham.php';    exit();
}

if ($uri === '/thidua/bao-cao/vi-pham-chi-tiet-theo-lop') {

// File: src/controllers/bao_cao_chi_tiet_theo_lop.php (Nâng cấp toàn diện và sửa lỗi)

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}

require_once __DIR__ . '/../lib/lop_hoc_db.php';



// 1. LẤY DỮ LIỆU BỘ LỌC VÀ TUẦN MẶC ĐỊNH
$all_weeks = $db->query("SELECT * FROM tuan_hoc ORDER BY ngay_bat_dau DESC")->fetchAll();
$danh_sach_lop_all = get_all_lop_hoc($db);
$tuan_id = $_GET['tuan_id'] ?? ($all_weeks[1]['id'] ?? $all_weeks[0]['id'] ?? null);
$filter_khoi = $_GET['khoi'] ?? 'all';
$filter_lop_id = $_GET['lop_id'] ?? 'all';

$tuan_hoc = null;
$report_data = [];
$summary_data = []; // Mảng mới chứa thông tin tổng hợp (Điểm, Hạng, Vắng...)

if ($tuan_id) {
    $stmt_tuan = $db->prepare("SELECT * FROM tuan_hoc WHERE id = ?");
    $stmt_tuan->execute([$tuan_id]);
    $tuan_hoc = $stmt_tuan->fetch();

    // 2. TÍNH TOÁN ĐIỂM THI ĐUA VÀ XẾP HẠNG CHO TOÀN BỘ CÁC LỚP
    $stmt_settings = $db->query("SELECT setting_key, setting_value FROM he_thong_cai_dat");
    $settings_raw = $stmt_settings->fetchAll(PDO::FETCH_KEY_PAIR);
    $settings = [
        'report_diem_tiet_tot' => (float)($settings_raw['report_diem_tiet_tot'] ?? 1),
        'report_diem_tiet_tb' => (float)($settings_raw['report_diem_tiet_tb'] ?? 0),
        'report_sdb_tt_tich' => (float)($settings_raw['report_sdb_tt_tich'] ?? 0),
        'report_sdb_ck_tich' => (float)($settings_raw['report_sdb_ck_tich'] ?? 0),
        'report_sdb_nk_tich' => (float)($settings_raw['report_sdb_nk_tich'] ?? 0),
        'report_nhat_ky_tich' => (float)($settings_raw['report_nhat_ky_tich'] ?? 0),
        'report_sdb_tt_khong' => (float)($settings_raw['report_sdb_tt_khong'] ?? 0),
        'report_sdb_ck_khong' => (float)($settings_raw['report_sdb_ck_khong'] ?? 0),
        'report_sdb_nk_khong' => (float)($settings_raw['report_sdb_nk_khong'] ?? 0),
        'report_nhat_ky_khong' => (float)($settings_raw['report_nhat_ky_khong'] ?? 0),
        'report_sdb_use_tt' => ($settings_raw['report_sdb_use_tt'] ?? 'off') === 'on',
        'report_sdb_use_ck' => ($settings_raw['report_sdb_use_ck'] ?? 'off') === 'on',
        'report_sdb_use_nk' => ($settings_raw['report_sdb_use_nk'] ?? 'off') === 'on',
        'report_sdb_use_nhat_ky' => ($settings_raw['report_sdb_use_nhat_ky'] ?? 'off') === 'on',
        'report_tru_vang_p' => (float)($settings_raw['report_tru_vang_p'] ?? 0),
        'report_tru_vang_kp' => (float)($settings_raw['report_tru_vang_kp'] ?? -1),
    ];
    $conditions_kxtd = $db->query("SELECT * FROM dieu_kien_kxtd WHERE kich_hoat = 1")->fetchAll();
    
    $all_class_scores = [];
    $lop_hoc_all = $db->query("SELECT id, ten_lop FROM lop_hoc")->fetchAll();

    foreach ($lop_hoc_all as $lop) {
        $data = ['lop' => $lop['ten_lop'], 'tong_diem' => 0, 'kxtd' => false, 'sdb_tt' => 0, 'sdb_ck' => 0, 'sdb_nk' => 0, 'nhat_ky' => 0];
        $stmt_thi_dua = $db->prepare("SELECT * FROM thi_dua_tuan WHERE tuan_hoc_id = ? AND lop_hoc_id = ?");
        $stmt_thi_dua->execute([$tuan_id, $lop['id']]);
        $thi_dua = $stmt_thi_dua->fetch();

        $data['sdb_tt'] = (int)($thi_dua['sdb_tt'] ?? 0);
        $data['sdb_ck'] = (int)($thi_dua['sdb_ck'] ?? 0);
        $data['sdb_nk'] = (int)($thi_dua['sdb_nk'] ?? 0);
        $data['nhat_ky'] = (int)($thi_dua['nhat_ky'] ?? 0);
        $data['diem_sdb_nk'] = 0;
        if ($settings['report_sdb_use_tt']) $data['diem_sdb_nk'] += $data['sdb_tt'] == 1 ? $settings['report_sdb_tt_tich'] : $settings['report_sdb_tt_khong'];
        if ($settings['report_sdb_use_ck']) $data['diem_sdb_nk'] += $data['sdb_ck'] == 1 ? $settings['report_sdb_ck_tich'] : $settings['report_sdb_ck_khong'];
        if ($settings['report_sdb_use_nk']) $data['diem_sdb_nk'] += $data['sdb_nk'] == 1 ? $settings['report_sdb_nk_tich'] : $settings['report_sdb_nk_khong'];
        if ($settings['report_sdb_use_nhat_ky']) $data['diem_sdb_nk'] += $data['nhat_ky'] == 1 ? $settings['report_nhat_ky_tich'] : $settings['report_nhat_ky_khong'];

        $stmt_vang = $db->prepare("SELECT SUM(vang_p) as total_p, SUM(vang_kp) as total_kp FROM diem_danh WHERE tuan_hoc_id = ? AND lop_hoc_id = ?");
        $stmt_vang->execute([$tuan_id, $lop['id']]);
        $vang = $stmt_vang->fetch();
        $data['vang_p'] = (int)($vang['total_p'] ?? 0);
        $data['vang_kp'] = (int)($vang['total_kp'] ?? 0);

        // NÂNG CẤP LOGIC: Tính điểm trừ nội quy bao gồm cả học sinh KXD
        $stmt_noi_quy = $db->prepare("
            SELECT SUM(chvp.diem_tru) 
            FROM vi_pham_hoc_sinh vp
            JOIN cau_hinh_vi_pham chvp ON vp.vi_pham_id = chvp.id
            LEFT JOIN quatrinh_hoc_tap qt ON vp.hoc_sinh_id = qt.id LEFT JOIN hoc_sinh hs ON hs.ma_hoc_sinh = qt.ma_hoc_sinh AND hs.nam_hoc_id = qt.nam_hoc_id
            WHERE vp.tuan_hoc_id = ? AND (hs.lop_hoc_id = ? OR vp.raw_ten_lop = ?)
        ");
        $stmt_noi_quy->execute([$tuan_id, $lop['id'], $lop['ten_lop']]);
        $data['diem_noi_quy'] = -(float)($stmt_noi_quy->fetchColumn() ?? 0);

        $data['diem_cong_tru'] = (float)($thi_dua['diem_cong_tru'] ?? 0);
        $data['tru_vang'] = ($data['vang_p'] * $settings['report_tru_vang_p']) + ($data['vang_kp'] * $settings['report_tru_vang_kp']);
        
        // SỬA LỖI: Thêm `?? 0` để tránh warning
        $diem_tiet_tot = (int)($thi_dua['so_tiet_tot'] ?? 0) * $settings['report_diem_tiet_tot'];
        $diem_tiet_tb = (int)($thi_dua['so_tiet_tb'] ?? 0) * $settings['report_diem_tiet_tb'];

        $data['tong_diem'] = $diem_tiet_tot + $diem_tiet_tb + $data['diem_sdb_nk'] + $data['diem_cong_tru'] + $data['diem_noi_quy'] + $data['tru_vang'];
        $all_class_scores[$lop['ten_lop']] = $data;
    }
    
    // Xếp hạng (logic giữ nguyên)
    $lop_theo_khoi = []; 
    foreach ($all_class_scores as $ten_lop => $data) { $khoi = substr($ten_lop, 0, 2); $lop_theo_khoi[$khoi][] = $data; }
    $ranks_by_khoi = [];
    foreach ($lop_theo_khoi as $khoi => $ds_lop) { 
        $lop_can_xep_hang = array_filter($ds_lop, function($lop) { return !$lop['kxtd']; }); 
        usort($lop_can_xep_hang, function($a, $b) { return $b['tong_diem'] <=> $a['tong_diem']; }); 
        $current_rank = 0; $last_score = -99999; $skip = 1; 
        foreach ($lop_can_xep_hang as $lop_data) { 
            if ($lop_data['tong_diem'] != $last_score) { $current_rank += $skip; $skip = 1; } else { $skip++; } 
            $ranks_by_khoi[$lop_data['lop']] = $current_rank; 
            $last_score = $lop_data['tong_diem']; 
        } 
    }
    foreach ($all_class_scores as &$data) { 
        if (!$data['kxtd']) { $data['xep_hang'] = $ranks_by_khoi[$data['lop']] ?? 'N/A'; } 
    } 
    unset($data);
    $summary_data = $all_class_scores;

    // 3. LẤY DỮ LIỆU VI PHẠM CHI TIẾT DỰA TRÊN BỘ LỌC
    $sql_params = [':tuan_id' => $tuan_id];
    $sql = "
        SELECT 
            COALESCE(lh.ten_lop, vp.raw_ten_lop) as ten_lop,
            lh.gvcn_ten,
            hs.ma_hoc_sinh,
            COALESCE(CONCAT(hs.ho_dem, ' ', hs.ten), vp.raw_ho_ten) as ho_ten,
            vp.ngay_vi_pham,
            chvp.ten_vi_pham,
            vp.ghi_chu
        FROM vi_pham_hoc_sinh vp
        LEFT JOIN quatrinh_hoc_tap qt ON vp.hoc_sinh_id = qt.id LEFT JOIN hoc_sinh hs ON hs.ma_hoc_sinh = qt.ma_hoc_sinh AND hs.nam_hoc_id = qt.nam_hoc_id
        LEFT JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id
        JOIN cau_hinh_vi_pham chvp ON vp.vi_pham_id = chvp.id
        WHERE vp.tuan_hoc_id = :tuan_id
    ";

    if ($filter_khoi !== 'all') {
        $sql .= " AND SUBSTR(COALESCE(lh.ten_lop, vp.raw_ten_lop), 1, 2) = :khoi";
        $sql_params[':khoi'] = $filter_khoi;
    }
    if ($filter_lop_id !== 'all') {
        $sql .= " AND COALESCE(lh.id, (SELECT id FROM lop_hoc WHERE ten_lop = vp.raw_ten_lop)) = :lop_id";
        $sql_params[':lop_id'] = $filter_lop_id;
    }

    $sql .= " ORDER BY CAST(SUBSTR(ten_lop, 1, 2) AS INTEGER), SUBSTR(ten_lop, 3, 1), CAST(SUBSTR(ten_lop, 4) AS INTEGER) ASC, ho_ten , vp.ngay_vi_pham";

    $stmt_vp = $db->prepare($sql);
    $stmt_vp->execute($sql_params);
    $vi_pham_list = $stmt_vp->fetchAll();

    foreach ($vi_pham_list as $vp) {
        $ten_lop = $vp['ten_lop'];
        if (!isset($report_data[$ten_lop])) {
            $report_data[$ten_lop] = [
                'gvcn_ten' => $vp['gvcn_ten'],
                'students' => []
            ];
        }
        $report_data[$ten_lop]['students'][$vp['ho_ten']][] = $vp;
    }
}

// 4. GỌI VIEW ĐỂ HIỂN THỊ
require_once __DIR__ . '/../views/bao_cao_chi_tiet_theo_lop.php';    exit();
}

if ($uri === '/thidua/bao-cao/nang-cap') {

// File: src/controllers/bao_cao_nang_cap.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}



// 1. Lấy danh sách tuần học để đưa vào modal
$all_weeks = $db->query("SELECT id, ten_tuan FROM tuan_hoc ORDER BY ngay_bat_dau DESC")->fetchAll(PDO::FETCH_ASSOC);
// 1. Lấy danh sách lớp học để đưa vào modal (cần cho các báo cáo khác)
$all_classes = $db->query("SELECT id, ten_lop FROM lop_hoc ORDER BY ten_lop")->fetchAll(PDO::FETCH_ASSOC);


// 2. Định nghĩa danh sách các báo cáo có sẵn
$danh_sach_bao_cao = [
    [
        'ma' => 'BC_01_Nam',
        'mieu_ta' => 'Báo cáo tổng hợp tổng điểm thi đua và xếp hạng tất cả tuần trong năm.',
        'action_type' => 'link',
        'url_tai_ve' => '/thidua/xuat-bao-cao/thanh-tich-toan-dien'
    ],
    [
        'ma' => 'BC_02_Tuan',
        'mieu_ta' => 'Báo cáo chi tiết danh sách vi phạm theo từng tuần của mỗi lớp một sheet.',
        'action_type' => 'modal',
        'modal_id' => '#chonTuanModal',
        'url_tai_ve' => '/thidua/xuat-bao-cao/chi-tiet-tuan-theo-lop'
    ],
    [
        'ma' => 'BC_02_Tuan_v2',
        'mieu_ta' => 'Báo cáo chi tiết danh sách vi phạm theo từng tuần của mỗi lớp (File zip).',
        'action_type' => 'modal',
        'modal_id' => '#chonTuanModal', // Vẫn dùng chung modal chọn tuần
        'url_tai_ve' => '/thidua/xuat-bao-cao/chi-tiet-tuan-zip' // Trỏ đến controller mới
    ],
    [
        'ma' => 'BC_03_VI_PHAM',
        'mieu_ta' => 'Xuất Danh sách Vi phạm: Liệt kê tất cả vi phạm của học sinh theo nhiều tuần đã chọn.',
        'action_type' => 'modal',
        'modal_id' => '#chonNhieuTuanModal', // Trỏ đến Modal mới
        'url_tai_ve' => '/thidua/xuat-bao-cao/ds-vi-pham' // Trỏ đến controller mới
    ],
    [
        'ma' => 'BC_HS_SL_VP_CA_NHAN',
        'mieu_ta' => 'Xuất số lần vi phạm theo từng học sinh; gồm sheet toàn trường và sheet từng lớp, chọn nhiều tuần.',
        'action_type' => 'modal',
        'modal_id' => '#chonNhieuTuanModal',
        'url_tai_ve' => '/thidua/xuat-bao-cao/hs-sl-vp-ca-nhan'
    ],
    [
        'ma' => 'BC_HS_TOAN_TRUONG',
        'mieu_ta' => 'Xuất Toàn Bộ Hồ Sơ Học Sinh (ZIP): Tạo một file Excel hồ sơ cho mỗi học sinh, sắp xếp theo từng lớp và nén tất cả lại thành một file .zip duy nhất.',
        'action_type' => 'link', // Bấm là tải ngay
        'url_tai_ve' => '/thidua/xuat-bao-cao/toan-bo-ho-so-zip' // Trỏ đến controller mới
    ],
    [
        'ma' => 'BC_04_KXTD_LT2T',
        'mieu_ta' => 'Phân tích tự động: Xuất danh sách các lớp xếp hạng chót trong từng khối hoặc bị Không Xét Thi Đua liên tục 2 tuần liên tiếp trong năm.',
        'action_type' => 'link', // Tải tự động tất cả các tuần
        'url_tai_ve' => '/thidua/xuat-bao-cao/chot-kxtd-lt2t'
    ]
];

// 3. Gọi view để hiển thị
$page_title = "Trung Tâm Báo Cáo Nâng Cấp";
require_once __DIR__ . '/../views/bao_cao_nang_cap.php';    exit();
}

if ($uri === '/thidua/bao-cao/phan-tich-lop') {

// FILE: src/controllers/bao_cao_phan_tich_lop.php 
// (PHIÊN BẢN GỐC - SỬA LỖI PDO 2014)

// *** BẮT ĐẦU CODE HIỂN THỊ LỖI ***
// (Bạn có thể xóa 3 dòng này sau khi trang đã chạy)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// *** KẾT THÚC CODE HIỂN THỊ LỖI ***

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}



$lop_id = $_GET['lop_id'] ?? null;
if (!$lop_id) die("Lỗi: Thiếu ID của lớp học.");



// 1. Lấy thông tin lớp học và khối
$stmt_lop = $db->prepare("SELECT ten_lop FROM lop_hoc WHERE id = ?");
$stmt_lop->execute([$lop_id]);
$lop_hoc = $stmt_lop->fetch();
$stmt_lop->closeCursor(); // <-- Thêm vào
if (!$lop_hoc) die("Lỗi: Lớp học không tồn tại.");
$khoi = substr($lop_hoc['ten_lop'], 0, 2);

// 2. Lấy toàn bộ dữ liệu cần thiết cho việc tính toán
$all_weeks = $db->query("SELECT * FROM tuan_hoc ORDER BY ngay_bat_dau ASC")->fetchAll();
$stmt_settings = $db->query("SELECT setting_key, setting_value FROM he_thong_cai_dat");
$settings_raw = $stmt_settings->fetchAll(PDO::FETCH_KEY_PAIR);
$conditions_kxtd = $db->query("SELECT * FROM dieu_kien_kxtd WHERE kich_hoat = 1")->fetchAll();

$all_classes_in_block = $db->prepare("SELECT id, ten_lop FROM lop_hoc WHERE SUBSTR(ten_lop, 1, 2) = ? ORDER BY CAST(SUBSTR(ten_lop, 1, 2) AS INTEGER), SUBSTR(ten_lop, 3, 1), CAST(SUBSTR(ten_lop, 4) AS INTEGER) ASC");
$all_classes_in_block->execute([$khoi]);
$lop_hoc_cung_khoi = $all_classes_in_block->fetchAll();
$all_classes_in_block->closeCursor(); // <-- Thêm vào

// 3. Chuẩn bị các câu lệnh truy vấn (để tái sử dụng trong vòng lặp)
$stmt_thi_dua = $db->prepare("SELECT * FROM thi_dua_tuan WHERE tuan_hoc_id = ? AND lop_hoc_id = ?");
$stmt_vang = $db->prepare("SELECT SUM(vang_p) as total_p, SUM(vang_kp) as total_kp FROM diem_danh WHERE tuan_hoc_id = ? AND lop_hoc_id = ?");
$stmt_noi_quy = $db->prepare("SELECT SUM(chvp.diem_tru) FROM vi_pham_hoc_sinh vphs JOIN cau_hinh_vi_pham chvp ON vphs.vi_pham_id = chvp.id LEFT JOIN quatrinh_hoc_tap qt ON vphs.hoc_sinh_id = qt.id LEFT JOIN hoc_sinh hs ON hs.ma_hoc_sinh = qt.ma_hoc_sinh AND hs.nam_hoc_id = qt.nam_hoc_id WHERE vphs.tuan_hoc_id = ? AND (hs.lop_hoc_id = ? OR vphs.raw_ten_lop = ?)");

// 4. Mảng để lưu kết quả cuối cùng
$history_data = [];

// 5. VÒNG LẶP LỚN: TÍNH TOÁN ĐIỂM VÀ HẠNG CHO TỪNG TUẦN
foreach ($all_weeks as $tuan) {
    $tuan_id = $tuan['id'];
    $weekly_scores = [];

    // Tính điểm cho tất cả các lớp trong khối
    foreach ($lop_hoc_cung_khoi as $lop) {
        // *** LOGIC TÍNH ĐIỂM (SAO CHÉP TỪ BAO_CAO_THI_DUA.PHP) ***
        $settings = [
            'report_diem_tiet_tot' => (float)($settings_raw['report_diem_tiet_tot'] ?? 1),
            'report_diem_tiet_tb' => (float)($settings_raw['report_diem_tiet_tb'] ?? 0),
            'report_sdb_tt_tich' => (float)($settings_raw['report_sdb_tt_tich'] ?? 0),
            'report_sdb_ck_tich' => (float)($settings_raw['report_sdb_ck_tich'] ?? 0),
            'report_sdb_nk_tich' => (float)($settings_raw['report_sdb_nk_tich'] ?? 0),
            'report_nhat_ky_tich' => (float)($settings_raw['report_nhat_ky_tich'] ?? 0),
            'report_sdb_tt_khong' => (float)($settings_raw['report_sdb_tt_khong'] ?? 0),
            'report_sdb_ck_khong' => (float)($settings_raw['report_sdb_ck_khong'] ?? 0),
            'report_sdb_nk_khong' => (float)($settings_raw['report_sdb_nk_khong'] ?? 0),
            'report_nhat_ky_khong' => (float)($settings_raw['report_nhat_ky_khong'] ?? 0),
            'report_sdb_use_tt' => ($settings_raw['report_sdb_use_tt'] ?? 'off') === 'on',
            'report_sdb_use_ck' => ($settings_raw['report_sdb_use_ck'] ?? 'off') === 'on',
            'report_sdb_use_nk' => ($settings_raw['report_sdb_use_nk'] ?? 'off') === 'on',
            'report_sdb_use_nhat_ky' => ($settings_raw['report_sdb_use_nhat_ky'] ?? 'off') === 'on',
            'report_tru_vang_p' => (float)($settings_raw['report_tru_vang_p'] ?? 0),
            'report_tru_vang_kp' => (float)($settings_raw['report_tru_vang_kp'] ?? -1),
        ];

        $data = ['lop' => $lop['ten_lop'], 'tong_diem' => 0, 'kxtd' => false, 'sdb_tt' => 0, 'sdb_ck' => 0, 'sdb_nk' => 0, 'nhat_ky' => 0];
        
        // DÒNG 83 (LỖI Ở ĐÂY)
        $stmt_thi_dua->execute([$tuan_id, $lop['id']]);
        $thi_dua = $stmt_thi_dua->fetch();
        $stmt_thi_dua->closeCursor(); // *** SỬA LỖI 1: THÊM DÒNG NÀY ***
        
        $data['sdb_tt'] = (int)($thi_dua['sdb_tt'] ?? 0);
        $data['sdb_ck'] = (int)($thi_dua['sdb_ck'] ?? 0);
        $data['sdb_nk'] = (int)($thi_dua['sdb_nk'] ?? 0);
        $data['nhat_ky'] = (int)($thi_dua['nhat_ky'] ?? 0);
        $data['diem_sdb_nk'] = 0;
        if ($settings['report_sdb_use_tt']) $data['diem_sdb_nk'] += $data['sdb_tt'] == 1 ? $settings['report_sdb_tt_tich'] : $settings['report_sdb_tt_khong'];
        if ($settings['report_sdb_use_ck']) $data['diem_sdb_nk'] += $data['sdb_ck'] == 1 ? $settings['report_sdb_ck_tich'] : $settings['report_sdb_ck_khong'];
        if ($settings['report_sdb_use_nk']) $data['diem_sdb_nk'] += $data['sdb_nk'] == 1 ? $settings['report_sdb_nk_tich'] : $settings['report_sdb_nk_khong'];
        if ($settings['report_sdb_use_nhat_ky']) $data['diem_sdb_nk'] += $data['nhat_ky'] == 1 ? $settings['report_nhat_ky_tich'] : $settings['report_nhat_ky_khong'];
        
        $stmt_vang->execute([$tuan_id, $lop['id']]);
        $vang = $stmt_vang->fetch();
        $stmt_vang->closeCursor(); // *** SỬA LỖI 2: THÊM DÒNG NÀY ***
        
        $data['vang_p'] = (int)($vang['total_p'] ?? 0);
        $data['vang_kp'] = (int)($vang['total_kp'] ?? 0);
        
        $stmt_noi_quy->execute([$tuan_id, $lop['id'], $lop['ten_lop']]);
        $data['diem_noi_quy'] = -(float)($stmt_noi_quy->fetchColumn() ?? 0);
        $stmt_noi_quy->closeCursor(); // *** SỬA LỖI 3: THÊM DÒNG NÀY ***
        
        $data['diem_cong_tru'] = (float)($thi_dua['diem_cong_tru'] ?? 0);
        $data['tru_vang'] = ($data['vang_p'] * $settings['report_tru_vang_p']) + ($data['vang_kp'] * $settings['report_tru_vang_kp']);
        foreach ($conditions_kxtd as $dk) { 
            if ($data['kxtd']) break; 
            $dieu_kien_dung = false; 
            $toan_tu = $dk['toan_tu']; 
            if (strpos($toan_tu, 'SDB_') === 0) { 
                $sdb_cols_to_check = json_decode($dk['danh_sach_sdb'] ?? '[]', true); 
                if (empty($sdb_cols_to_check) && !empty($dk['truong_so_sanh'])) {
                    $sdb_cols_to_check = [$dk['truong_so_sanh']];
                }
                if (empty($sdb_cols_to_check)) continue; 
                $ticked_count = 0; 
                foreach ($sdb_cols_to_check as $col) { 
                    if (isset($data[$col]) && $data[$col] == 1) $ticked_count++; 
                } 
                if ($toan_tu === 'SDB_IS_TICKED') { 
                    if ($ticked_count > 0) $dieu_kien_dung = true; 
                } elseif ($toan_tu === 'SDB_IS_NOT_TICKED') { 
                    if ($ticked_count < count($sdb_cols_to_check)) $dieu_kien_dung = true; 
                } elseif ($toan_tu === 'SDB_COMB_ALL_NOT_TICKED') { 
                    if ($ticked_count === 0) $dieu_kien_dung = true; 
                } elseif ($toan_tu === 'SDB_COUNT_TICKED_EQUALS') { 
                    if ($ticked_count == (int)$dk['nguong_gia_tri']) $dieu_kien_dung = true; 
                } 
            } else { 
                $gia_tri_so_sanh = $data[$dk['truong_so_sanh']] ?? null; 
                if ($gia_tri_so_sanh === null) continue; 
                $nguong = (float)$dk['nguong_gia_tri']; 
                switch ($toan_tu) { 
                    case '>': $dieu_kien_dung = $gia_tri_so_sanh > $nguong; break; 
                    case '>=': $dieu_kien_dung = $gia_tri_so_sanh >= $nguong; break; 
                    case '<': $dieu_kien_dung = $gia_tri_so_sanh < $nguong; break; 
                    case '<=': $dieu_kien_dung = $gia_tri_so_sanh <= $nguong; break; 
                    case '==': $dieu_kien_dung = $gia_tri_so_sanh == $nguong; break; 
                    case '!=': $dieu_kien_dung = $gia_tri_so_sanh != $nguong; break; 
                } 
            } 
            if ($dieu_kien_dung) $data['kxtd'] = true; 
        }
        $weekly_scores[$lop['id']] = $data;
    }

    // Xếp hạng cho tuần hiện tại
    
    // *** SỬA LỖI TƯƠNG THÍCH PHP 7.4 (fn) ***
    $lop_can_xep_hang = array_filter($weekly_scores, function($lop) {
        return !$lop['kxtd'];
    });
    
    // *** SỬA LỖI TƯƠNG THÍCH PHP 7.0 (<=>) ***
    usort($lop_can_xep_hang, function($a, $b) {
        // Sắp xếp điểm giảm dần (cao xuống thấp)
        if ($b['tong_diem'] > $a['tong_diem']) {
            return 1;
        } elseif ($b['tong_diem'] < $a['tong_diem']) {
            return -1;
        } else {
            return 0;
        }
    });

    $weekly_ranks = [];
    $current_rank = 0; $last_score = -99999; $skip = 1;
    foreach ($lop_can_xep_hang as $lop_data) {
        if ($lop_data['tong_diem'] != $last_score) { $current_rank += $skip; $skip = 1; } else { $skip++; }
        $weekly_ranks[$lop_data['lop']] = $current_rank;
        $last_score = $lop_data['tong_diem'];
    }

    // Tính điểm trung bình khối
    $total_score_in_block = array_sum(array_column($lop_can_xep_hang, 'tong_diem'));
    $average_score = count($lop_can_xep_hang) > 0 ? $total_score_in_block / count($lop_can_xep_hang) : 0;
    
    // Lưu dữ liệu của lớp đang phân tích vào mảng lịch sử
    if (isset($weekly_scores[$lop_id])) {
        $class_weekly_data = $weekly_scores[$lop_id];
        $history_data[] = [
            'ten_tuan' => $tuan['ten_tuan'],
            'tong_diem' => round($class_weekly_data['tong_diem'], 2),
            'xep_hang' => $class_weekly_data['kxtd'] ? null : ($weekly_ranks[$class_weekly_data['lop']] ?? null),
            'diem_trung_binh_khoi' => round($average_score, 2),
            // Lưu chi tiết điểm của tuần này
            'details' => $class_weekly_data
        ];
    }
}

// 6. Chuẩn bị dữ liệu cho Chart.js và View
$chart_data = [
    'labels' => array_column($history_data, 'ten_tuan'),
    'scoreData' => array_column($history_data, 'tong_diem'),
    'rankData' => array_column($history_data, 'xep_hang'),
    'averageScoreData' => array_column($history_data, 'diem_trung_binh_khoi'),
];
$tuan_hien_tai_data = end($history_data)['details'] ?? null; // Lấy dữ liệu chi tiết của tuần cuối cùng


require_once __DIR__ . '/../views/bao_cao_phan_tich_lop.php';    exit();
}

if ($uri === '/thidua/bao-cao/cong-khai') {

// File: src/controllers/bao_cao_cong_khai.php (Phiên bản nâng cấp cuối cùng)

// Nạp các file cần thiết
require_once __DIR__ . '/../../vendor/autoload.php';
 // Sử dụng bootstrap để có đủ môi trường
require_once __DIR__ . '/../../src/lib/tuan_hoc_db.php'; // Thư viện quản lý tuần
require_once __DIR__ . '/../../src/lib/ThiDuaCalculator.php'; // "Bộ não" tính toán



try {
    

    // 1. LẤY DANH SÁCH CÁC TUẦN ĐƯỢC PHÉP CÔNG KHAI
    $public_weeks = get_public_weeks($db);

    // Xử lý trường hợp không có tuần nào được bật
    if (empty($public_weeks)) {
        // Vẫn tải trang nhưng với dữ liệu rỗng và thông báo
        $tuan_id = 0;
        $tuan_hoc = ['ten_tuan' => 'Chưa có tuần nào được công khai', 'ngay_bat_dau' => date('Y-m-d'), 'ngay_ket_thuc' => date('Y-m-d')];
        $report_data = [];
        $ghi_chu_bao_cao = 'Vui lòng liên hệ quản trị viên để bật tính năng xem báo cáo công khai.';
        $qr_code_base64 = null;
        require_once __DIR__ . '/../../src/views/bao_cao_cong_khai.php';
        exit();
    }

    // 2. XÁC ĐỊNH VÀ XÁC THỰC TUẦN HIỂN THỊ
    $tuan_id = $_GET['tuan_id'] ?? $public_weeks[0]['id'];

    $is_valid_public_week = false;
    foreach ($public_weeks as $week) {
        if ($week['id'] == $tuan_id) {
            $is_valid_public_week = true;
            break;
        }
    }
    if (!$is_valid_public_week) {
        die("Tuần này không được phép xem công khai.");
    }

    // 3. LẤY THÔNG TIN CHI TIẾT CỦA TUẦN HỌC
    $stmt_tuan = $db->prepare("SELECT * FROM tuan_hoc WHERE id = ?");
    $stmt_tuan->execute([$tuan_id]);
    $tuan_hoc = $stmt_tuan->fetch();
    if (!$tuan_hoc) die("Lỗi: Tuần học không tồn tại.");
    $ghi_chu_bao_cao = $tuan_hoc['ghi_chu_bao_cao'] ?? '';

    // ==============================================================================
    // 4. LOGIC TÍNH TOÁN "LAI" (HYBRID) - Y HỆT FILE IN BÁO CÁO
    // ==============================================================================

    // BƯỚC 4.1: LẤY DỮ LIỆU ĐIỂM THÀNH PHẦN ĐƠN GIẢN
    $diem_thanh_phan_goc = [];
    $lop_hoc = $db->query("SELECT id, ten_lop FROM lop_hoc")->fetchAll();

    $stmt_settings = $db->query("SELECT setting_key, setting_value FROM he_thong_cai_dat");
    $settings_raw = $stmt_settings->fetchAll(PDO::FETCH_KEY_PAIR);
    $settings = [
        'report_diem_tiet_tot' => (float)($settings_raw['report_diem_tiet_tot'] ?? 1),
        'report_diem_tiet_tb'  => (float)($settings_raw['report_diem_tiet_tb'] ?? 0),
        'report_sdb_tt_tich' => (float)($settings_raw['report_sdb_tt_tich'] ?? 0),
        'report_sdb_ck_tich' => (float)($settings_raw['report_sdb_ck_tich'] ?? 0),
        'report_sdb_nk_tich' => (float)($settings_raw['report_sdb_nk_tich'] ?? 0),
        'report_nhat_ky_tich' => (float)($settings_raw['report_nhat_ky_tich'] ?? 0),
        'report_sdb_tt_khong' => (float)($settings_raw['report_sdb_tt_khong'] ?? 0),
        'report_sdb_ck_khong' => (float)($settings_raw['report_sdb_ck_khong'] ?? 0),
        'report_sdb_nk_khong' => (float)($settings_raw['report_sdb_nk_khong'] ?? 0),
        'report_nhat_ky_khong' => (float)($settings_raw['report_nhat_ky_khong'] ?? 0),
        'report_sdb_use_tt' => ($settings_raw['report_sdb_use_tt'] ?? 'off') === 'on',
        'report_sdb_use_ck' => ($settings_raw['report_sdb_use_ck'] ?? 'off') === 'on',
        'report_sdb_use_nk' => ($settings_raw['report_sdb_use_nk'] ?? 'off') === 'on',
        'report_sdb_use_nhat_ky' => ($settings_raw['report_sdb_use_nhat_ky'] ?? 'off') === 'on',
    ];

    foreach ($lop_hoc as $lop) {
        $stmt_thi_dua = $db->prepare("SELECT so_tiet_tot, so_tiet_tb, diem_cong_tru, sdb_tt, sdb_ck, sdb_nk, nhat_ky FROM thi_dua_tuan WHERE tuan_hoc_id = ? AND lop_hoc_id = ?");
        $stmt_thi_dua->execute([$tuan_id, $lop['id']]);
        $thi_dua = $stmt_thi_dua->fetch(PDO::FETCH_ASSOC);

        $diem_tiet_tot = (int)($thi_dua['so_tiet_tot'] ?? 0) * $settings['report_diem_tiet_tot'];
        $diem_tiet_tb = (int)($thi_dua['so_tiet_tb'] ?? 0) * $settings['report_diem_tiet_tb'];
        $diem_cong_tru = (float)($thi_dua['diem_cong_tru'] ?? 0);
        
        $sdb_tt = (int)($thi_dua['sdb_tt'] ?? 0);
        $sdb_ck = (int)($thi_dua['sdb_ck'] ?? 0);
        $sdb_nk = (int)($thi_dua['sdb_nk'] ?? 0);
        $nhat_ky = (int)($thi_dua['nhat_ky'] ?? 0);
        $diem_sdb_nk = 0;
        if ($settings['report_sdb_use_tt']) $diem_sdb_nk += $sdb_tt == 1 ? $settings['report_sdb_tt_tich'] : $settings['report_sdb_tt_khong'];
        if ($settings['report_sdb_use_ck']) $diem_sdb_nk += $sdb_ck == 1 ? $settings['report_sdb_ck_tich'] : $settings['report_sdb_ck_khong'];
        if ($settings['report_sdb_use_nk']) $diem_sdb_nk += $sdb_nk == 1 ? $settings['report_sdb_nk_tich'] : $settings['report_sdb_nk_khong'];
        if ($settings['report_sdb_use_nhat_ky']) $diem_sdb_nk += $nhat_ky == 1 ? $settings['report_nhat_ky_tich'] : $settings['report_nhat_ky_khong'];

        $diem_thanh_phan_goc[$lop['ten_lop']] = [
            'diem_tiet_tot' => $diem_tiet_tot,
            'diem_tiet_tb' => $diem_tiet_tb,
            'diem_cong_tru' => $diem_cong_tru,
            'diem_sdb_nk' => $diem_sdb_nk
        ];
    }

    // BƯỚC 4.2: SỬ DỤNG thiduaCALCULATOR ĐỂ TÍNH TOÁN CÁC HẠNG MỤC PHỨC TẠP
    $calculator = new thiduaCalculator($db);
    $raw_data = $calculator->calculateRawDataForWeek((int)$tuan_id);
    $report_data_ranked = $calculator->rankWeeklyData($raw_data);

    // BƯỚC 4.3: KẾT HỢP DỮ LIỆU ĐỂ TRUYỀN SANG VIEW
    $final_report_data = [];
    foreach ($report_data_ranked as $data_calculator) {
        $ten_lop = $data_calculator['lop'];
        $data_goc = $diem_thanh_phan_goc[$ten_lop] ?? [];
        $final_report_data[] = array_merge($data_goc, $data_calculator);
    }
    $report_data = $final_report_data; 

    // 5. TẠO MÃ QR VÀ TẢI GIAO DIỆN
    $qr_code_url = "https://" . ($_SERVER['HTTP_HOST'] ?? '') . "/thidua/bao-cao/cong-khai?tuan_id=" . $tuan_id;
    try {
        $qr_result = Builder::create()
            ->writer(new PngWriter())
            ->data($qr_code_url)
            ->build();
        $qr_code_base64 = $qr_result->getDataUri();
    } catch (Exception $e) {
        $qr_code_base64 = null;
    }

    require_once __DIR__ . '/../../src/views/bao_cao_cong_khai.php';

} catch (Exception $e) {
    die("Lỗi hệ thống: " . $e->getMessage());
}
    exit();
}

if ($uri === '/thidua/admin/cau-hinh-bao-cao') {

// File: src/controllers/cau_hinh_bao_cao.php (PHIÊN BẢN ĐÃ CẬP NHẬT CHUẨN)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}





require_once __DIR__ . '/../lib/nam_hoc.php';
$current_nam_hoc = current_nam_hoc_id();

// XỬ LÝ POST: LƯU CẤU HÌNH
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data)) {
        echo json_encode(['success' => false, 'message' => 'Không nhận được dữ liệu.']);
        exit();
    }

    try {
        $db->beginTransaction();

        $settings_to_save = $data['settings'] ?? [];
        $kxtd_conditions = $data['kxtd_conditions'] ?? [];

        // 1. Lưu các cài đặt thông thường
        if (!empty($settings_to_save)) {
            $stmt_setting = $db->prepare("
                INSERT INTO he_thong_cai_dat (setting_key, setting_value, nam_hoc_id) VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
            ");
            foreach ($settings_to_save as $key => $value) {
                if (is_array($value)) {
                    $value = json_encode($value);
                }
                $stmt_setting->execute([$key, $value, $current_nam_hoc]);
            }
        }

        // 2. Xử lý các điều kiện KXTĐ
        if (!empty($kxtd_conditions)) {
            $stmt_insert_kxtd = $db->prepare(
                "INSERT INTO raw_dieu_kien_kxtd (ten_dieu_kien, truong_so_sanh, toan_tu, nguong_gia_tri, kich_hoat, danh_sach_sdb, nam_hoc_id) VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt_update_kxtd = $db->prepare(
                "UPDATE raw_dieu_kien_kxtd SET ten_dieu_kien = ?, truong_so_sanh = ?, toan_tu = ?, nguong_gia_tri = ?, kich_hoat = ?, danh_sach_sdb = ? WHERE id = ? AND nam_hoc_id = ?"
            );
            $stmt_delete_kxtd = $db->prepare("DELETE FROM raw_dieu_kien_kxtd WHERE id = ? AND nam_hoc_id = ?");

            foreach ($kxtd_conditions as $condition) {
                $id = $condition['id'] ?? null;
                $danh_sach_sdb_json = json_encode($condition['danh_sach_sdb'] ?? []);
                
                if (isset($condition['delete']) && $condition['delete'] == '1' && $id) {
                    $stmt_delete_kxtd->execute([$id, $current_nam_hoc]);
                } elseif ($id) {
                    $stmt_update_kxtd->execute([
                        $condition['ten_dieu_kien'],
                        $condition['truong_so_sanh'],
                        $condition['toan_tu'],
                        $condition['nguong_gia_tri'] ?: null,
                        $condition['kich_hoat'] ?? 0,
                        $danh_sach_sdb_json,
                        $id,
                        $current_nam_hoc
                    ]);
                } else {
                    if (!empty($condition['ten_dieu_kien'])) {
                        $stmt_insert_kxtd->execute([
                            $condition['ten_dieu_kien'],
                            $condition['truong_so_sanh'],
                            $condition['toan_tu'],
                            $condition['nguong_gia_tri'] ?: null,
                            $condition['kich_hoat'] ?? 0,
                            $danh_sach_sdb_json,
                            $current_nam_hoc
                        ]);
                    }
                }
            }
        }

        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Đã lưu cấu hình thành công!']);
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
    }
    exit();
}

// XỬ LÝ GET: HIỂN THỊ GIAO DIỆN
// Lấy tất cả cài đặt hiện tại từ CSDL theo năm học (ưu tiên năm học hiện tại, fallback nam_hoc_id = 0)
$stmt_settings = $db->prepare("SELECT setting_key, setting_value FROM he_thong_cai_dat WHERE nam_hoc_id = 0 OR nam_hoc_id = ? ORDER BY CASE WHEN nam_hoc_id = ? THEN 1 ELSE 0 END ASC");
$stmt_settings->execute([$current_nam_hoc, $current_nam_hoc]);
$settings_raw = $stmt_settings->fetchAll(PDO::FETCH_KEY_PAIR);

// Lấy danh sách tất cả vi phạm (theo năm học)
$stmt_vi_pham = $db->prepare("SELECT id, ten_vi_pham FROM raw_cau_hinh_vi_pham WHERE nam_hoc_id = ? ORDER BY ten_vi_pham ASC");
$stmt_vi_pham->execute([$current_nam_hoc]);
$danh_sach_vi_pham = $stmt_vi_pham->fetchAll(PDO::FETCH_ASSOC);

// Lấy tất cả điều kiện KXTĐ (theo năm học)
$stmt_dieu_kien = $db->prepare("SELECT * FROM raw_dieu_kien_kxtd WHERE nam_hoc_id = ? ORDER BY id ASC");
$stmt_dieu_kien->execute([$current_nam_hoc]);
$dieu_kien_kxtd = $stmt_dieu_kien->fetchAll(PDO::FETCH_ASSOC);

// Gán giá trị mặc định cho từng cài đặt nếu chúng chưa tồn tại
$settings = [
    'report_diem_tiet_tot' => $settings_raw['report_diem_tiet_tot'] ?? 1,
    'report_diem_tiet_tb' => $settings_raw['report_diem_tiet_tb'] ?? 0,
    
    // Cài đặt điểm KHI TÍCH 'X'
    'report_sdb_tt_tich' => $settings_raw['report_sdb_tt_tich'] ?? 1,
    'report_sdb_ck_tich' => $settings_raw['report_sdb_ck_tich'] ?? 1,
    'report_sdb_nk_tich' => $settings_raw['report_sdb_nk_tich'] ?? 1,
    'report_nhat_ky_tich' => $settings_raw['report_nhat_ky_tich'] ?? 1,

    // Cài đặt điểm KHI KHÔNG TÍCH 'X'
    'report_sdb_tt_khong' => $settings_raw['report_sdb_tt_khong'] ?? 0,
    'report_sdb_ck_khong' => $settings_raw['report_sdb_ck_khong'] ?? 0,
    'report_sdb_nk_khong' => $settings_raw['report_sdb_nk_khong'] ?? 0,
    'report_nhat_ky_khong' => $settings_raw['report_nhat_ky_khong'] ?? 0,

    // Cài đặt có sử dụng mục đó để cộng dồn không
    'report_sdb_use_tt' => $settings_raw['report_sdb_use_tt'] ?? 'on',
    'report_sdb_use_ck' => $settings_raw['report_sdb_use_ck'] ?? 'on',
    'report_sdb_use_nk' => $settings_raw['report_sdb_use_nk'] ?? 'on',
    'report_sdb_use_nhat_ky' => $settings_raw['report_sdb_use_nhat_ky'] ?? 'on',
    
    // Các cài đặt khác cho chức năng Vắng
    'report_vang_source' => $settings_raw['report_vang_source'] ?? 'diem_danh',
    'report_tru_vang_p' => $settings_raw['report_tru_vang_p'] ?? -0.5,
    'report_tru_vang_kp' => $settings_raw['report_tru_vang_kp'] ?? -1,

    // ===== BỔ SUNG CÀI ĐẶT MỚI CHO VIỆC CHỌN VI PHẠM VẮNG =====
    'report_vang_p_vids' => $settings_raw['report_vang_p_vids'] ?? '[]', // Lưu dưới dạng chuỗi JSON
    'report_vang_kp_vids' => $settings_raw['report_vang_kp_vids'] ?? '[]', // Lưu dưới dạng chuỗi JSON
];

require_once __DIR__ . '/../views/cau_hinh_bao_cao.php';    exit();
}

if ($uri === '/thidua/admin/huong-dan-cau-hinh-bao-cao') {

// File: src/controllers/huong_dan_cau_hinh_bao_cao.php (File mới)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}



// Đây là danh sách các mã cột mà hệ thống hỗ trợ để so sánh trong điều kiện KXTĐ
$ma_cot_tham_khao = [
    'so_tiet_tot' => 'Tổng số Tiết Tốt của lớp trong tuần.',
    'so_tiet_tb' => 'Tổng số Tiết Trung Bình của lớp trong tuần.',
    'sdb_tt' => 'Trạng thái Sổ Đầu Bài - Thường Xuyên (1 là có tick, 0 là không).',
    'sdb_ck' => 'Trạng thái Sổ Đầu Bài - Có Kiểm tra (1 là có tick, 0 là không).',
    'sdb_nk' => 'Trạng thái Sổ Đầu Bài - Ngoại Khóa (1 là có tick, 0 là không).',
    'nhat_ky' => 'Trạng thái Nhật kỳ (1 là có tick, 0 là không).',
    'diem_cong_tru' => 'Điểm Cộng/Trừ Khác nhập tay.',
    'vang_kp' => 'Tổng số buổi Vắng Không Phép trong tuần (từ nguồn đã chọn).',
    'vang_p' => 'Tổng số buổi Vắng Có Phép trong tuần (từ nguồn đã chọn).',
    'diem_noi_quy' => 'Tổng điểm trừ từ tất cả các vi phạm của lớp trong tuần.',
    'tong_diem' => 'Tổng điểm thi đua cuối cùng của lớp (đã tổng hợp tất cả các mục).'
];


require_once __DIR__ . '/../views/huong_dan_cau_hinh_bao_cao.php';    exit();
}

