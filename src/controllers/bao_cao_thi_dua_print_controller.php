<?php
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

// File: src/controllers/bao_cao_thi_dua_print_controller.php (PHIÊN BẢN LOGIC "LAI")
// Kết hợp sức mạnh của thiduaCalculator và sự ổn định của logic gốc.

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}

require_once __DIR__ . '/../../vendor/autoload.php';



// Lấy ID tuần từ URL
$tuan_id = $_GET['tuan_id'] ?? null;
if (!$tuan_id) {
    die("Lỗi: Thiếu ID của tuần học.");
}



// Lấy thông tin tuần học
$stmt_tuan = $db->prepare("SELECT * FROM tuan_hoc WHERE id = ?");
$stmt_tuan->execute([$tuan_id]);
$tuan_hoc = $stmt_tuan->fetch();
if (!$tuan_hoc) die("Lỗi: Tuần học không tồn tại.");

$ghi_chu_bao_cao = $tuan_hoc['ghi_chu_bao_cao'] ?? '';

// ==============================================================================
// BƯỚC 1: SỬ DỤNG LOGIC GỐC ĐỂ LẤY DỮ LIỆU ĐIỂM THÀNH PHẦN ĐƠN GIẢN
// ==============================================================================
$diem_thanh_phan_goc = [];
$lop_hoc = $db->query("SELECT id, ten_lop FROM lop_hoc")->fetchAll();

// Lấy tất cả cài đặt cần thiết cho logic gốc (bao gồm cả SĐB-NK)
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
    // Lấy tất cả các cột cần thiết từ thi_dua_tuan
    $stmt_thi_dua = $db->prepare("SELECT so_tiet_tot, so_tiet_tb, diem_cong_tru, sdb_tt, sdb_ck, sdb_nk, nhat_ky FROM thi_dua_tuan WHERE tuan_hoc_id = ? AND lop_hoc_id = ?");
    $stmt_thi_dua->execute([$tuan_id, $lop['id']]);
    $thi_dua = $stmt_thi_dua->fetch(PDO::FETCH_ASSOC);

    // Tính điểm thành phần
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

    // Lưu dữ liệu vào mảng tạm
    $diem_thanh_phan_goc[$lop['ten_lop']] = [
        'diem_tiet_tot' => $diem_tiet_tot,
        'diem_tiet_tb' => $diem_tiet_tb,
        'diem_cong_tru' => $diem_cong_tru,
        'diem_sdb_nk' => $diem_sdb_nk
    ];
}

// ==============================================================================
// BƯỚC 2: SỬ DỤNG thiduaCALCULATOR ĐỂ TÍNH TOÁN CÁC HẠNG MỤC PHỨC TẠP
// ==============================================================================
$calculator = new thiduaCalculator($db);
$raw_data = $calculator->calculateRawDataForWeek((int)$tuan_id);
$report_data = $calculator->rankWeeklyData($raw_data);

// ==============================================================================
// BƯỚC 3: KẾT HỢP DỮ LIỆU ĐỂ TRUYỀN SANG VIEW
// ==============================================================================
$final_report_data = [];
foreach ($report_data as $data_calculator) {
    $ten_lop = $data_calculator['lop'];
    $data_goc = $diem_thanh_phan_goc[$ten_lop] ?? [];
    
    // Gộp hai mảng, ưu tiên dữ liệu từ calculator cho các mục tính toán phức tạp
    $final_report_data[] = array_merge($data_goc, $data_calculator);
}
// Gán lại biến $report_data để view sử dụng
$report_data = $final_report_data;


// ==============================================================================
// BƯỚC 4: TẠO MÃ QR VÀ GỌI VIEW
// ==============================================================================
$qr_code_base64 = null;

// **CẬP NHẬT**: Tự động lấy domain của server
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https" : "http";
$domain = $_SERVER['HTTP_HOST'];
$base_url = $protocol . '://' . $domain;

// **CẬP NHẬT**: Tạo token để bảo mật URL, tránh việc đoán ID tuần
// Bạn cần định nghĩa hằng số 'REPORT_SECRET_KEY' trong file /config/bootstrap.php
// Ví dụ: define('REPORT_SECRET_KEY', 'một-chuỗi-ký-tự-bí-mật-và-dài');
if (!defined('REPORT_SECRET_KEY')) {
    // Tốt nhất là die() và yêu cầu người dùng định nghĩa nó để đảm bảo an toàn.
    die("Lỗi: Vui lòng định nghĩa hằng số 'REPORT_SECRET_KEY' trong file config/bootstrap.php.");
}
$token = hash_hmac('sha256', (string)$tuan_id, REPORT_SECRET_KEY);
$public_report_url = $base_url . '/thidua/bao-cao/cong-khai?tuan_id=' . $tuan_id . '&token=' . $token;

try {
    $result = Builder::create()
        ->writer(new PngWriter())
        ->data($public_report_url)
        ->build();
    $qr_code_base64 = $result->getDataUri();
} catch (Exception $e) {
    // Bỏ qua nếu có lỗi
}


// Fetch nam hoc ten
$current_nam_hoc_id = $_SESSION['current_nam_hoc_id'] ?? 1;
$stmt_nam_hoc = $db->prepare("SELECT ten_nam_hoc FROM nam_hoc WHERE id = ?");
$stmt_nam_hoc->execute([$current_nam_hoc_id]);
$ten_nam_hoc = $stmt_nam_hoc->fetchColumn() ?: '2025 - 2026';

require_once __DIR__ . '/../views/bao_cao_thi_dua_print.php';


