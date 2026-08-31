<?php


use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
// File: src/controllers/bao_cao_export.php
// Gom chung các API xuất dữ liệu (Excel, PDF, In ấn, Zip) liên quan đến Báo Cáo

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

if ($uri === '/thidua/xuat-bao-cao-thi-dua') {

// File: src/controllers/xuat_bao_cao_thi_dua.php (PHIÊN BẢN LOGIC "LAI")
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

$templatePath = __DIR__ . '/../../public/templates/mau_bao_cao_thi_dua.xlsx';
if (!file_exists($templatePath)) {
    die("Lỗi: Không tìm thấy file mẫu tại public/templates/mau_bao_cao_thi_dua.xlsx.");
}

try {
    

    // Lấy thông tin tuần học
    $stmt_tuan = $db->prepare("SELECT * FROM tuan_hoc WHERE id = ?");
    $stmt_tuan->execute([$tuan_id]);
    $tuan_hoc = $stmt_tuan->fetch();
    if (!$tuan_hoc) die("Lỗi: Tuần học không tồn tại.");

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
    
    // *** DÒNG MỚI ĐƯỢC BỔ SUNG: LOGIC TÍNH ĐIỂM SĐB-NK TỪ FILE GỐC ***
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
        'diem_sdb_nk' => $diem_sdb_nk // *** DÒNG MỚI: LƯU ĐIỂM SĐB-NK VỪA TÍNH
    ];
}
    
    // ==============================================================================
    // BƯỚC 2: SỬ DỤNG thiduaCALCULATOR ĐỂ TÍNH TOÁN CÁC HẠNG MỤC PHỨC TẠP
    // ==============================================================================
    $calculator = new thiduaCalculator($db);
    $raw_data = $calculator->calculateRawDataForWeek((int)$tuan_id);
    $report_data = $calculator->rankWeeklyData($raw_data);

    // ==============================================================================
    // BƯỚC 3: MỞ FILE MẪU VÀ ĐIỀN DỮ LIỆU ĐÃ KẾT HỢP
    // ==============================================================================
    $spreadsheet = IOFactory::load($templatePath);
    $sheet = $spreadsheet->getActiveSheet();

    // Điền thông tin chung
    $tieu_de_tuan = '' . htmlspecialchars($tuan_hoc['ten_tuan']) . ' (Từ ngày ' . date('d/m/Y', strtotime($tuan_hoc['ngay_bat_dau'])) . ' đến ' . date('d/m/Y', strtotime($tuan_hoc['ngay_ket_thuc'])) . ')';
    $sheet->setCellValue('A3', $tieu_de_tuan);
    
    $startRow = 6;

    foreach ($report_data as $index => $data_calculator) {
        $currentRow = $startRow + $index;
        $ten_lop = $data_calculator['lop'];

        // Lấy dữ liệu từ mảng tạm của logic gốc
        $data_goc = $diem_thanh_phan_goc[$ten_lop] ?? [
            'diem_tiet_tot' => 0, 'diem_tiet_tb' => 0, 'diem_cong_tru' => 0
        ];
        
        // --- Hàm điền dữ liệu (ẩn số 0) ---
        $fillCell = function($cell, $value) use ($sheet) {
            if ($value != 0) {
                $sheet->setCellValue($cell, $value);
            } else {
                $sheet->setCellValue($cell, '');
            }
        };
        
        // --- Bắt đầu điền dữ liệu kết hợp ---
        $sheet->setCellValue('A' . $currentRow, $ten_lop);
        
        // Lấy từ logic GỐC
        $fillCell('B' . $currentRow, $data_goc['diem_tiet_tot']);
        $fillCell('C' . $currentRow, $data_goc['diem_tiet_tb']);
        $fillCell('G' . $currentRow, $data_goc['diem_cong_tru']);
        
        // Lấy từ thiduaCalculator
        $fillCell('D' . $currentRow, $data_goc['diem_sdb_nk']); 
        $fillCell('E' . $currentRow, $data_calculator['vang_kp']);
        $fillCell('F' . $currentRow, $data_calculator['vang_p']);
        $fillCell('H' . $currentRow, $data_calculator['diem_noi_quy']);

        
        // Tổng điểm và Xếp hạng luôn lấy từ thiduaCalculator để đảm bảo chính xác tuyệt đối
        $sheet->setCellValue('I' . $currentRow, $data_calculator['tong_diem']);
        if ($data_calculator['kxtd']) {
            $sheet->setCellValue('J' . $currentRow, 'KXTĐ');
        } else {
            $sheet->setCellValue('J' . $currentRow, $data_calculator['xep_hang']);
        }
    }

    // Gửi file cho người dùng
    $ten_tuan_filename = str_replace([' ', '/'], '_', $tuan_hoc['ten_tuan']);
    $filename = "BaoCao_thidua_{$ten_tuan_filename}.xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"; filename*=UTF-8\'\'' . rawurlencode($filename));
    header('Cache-Control: max-age=0');
    ob_clean(); // Xóa mọi "ký tự rác"
flush();    // Đẩy header
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();

} catch (Exception $e) {
    error_log("Excel Export Error: " . $e->getMessage());
    die("Lỗi nghiêm trọng khi tạo file Excel. Vui lòng liên hệ quản trị viên.");
}
    exit();
}

if ($uri === '/thidua/xuat-bao-cao-thi-dua-pdf') {

// File: src/controllers/xuat_bao_cao_thi_dua_pdf.php
// Xuất PDF trực tiếp bằng mPDF (không dùng API chuyển đổi).

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}

$tuan_id = isset($_GET['tuan_id']) ? (int)$_GET['tuan_id'] : 0;
if ($tuan_id <= 0) {
    http_response_code(400);
    die('Lỗi: Thiếu ID của tuần học.');
}

require_once __DIR__ . '/../../vendor/autoload.php';

try {
    // Tái sử dụng toàn bộ logic dữ liệu + giao diện in hiện có để đảm bảo đồng nhất.
    $_GET['tuan_id'] = $tuan_id;

    ob_start();
    require __DIR__ . '/bao_cao_thi_dua_print_controller.php';
    $html = ob_get_clean();

    if (!is_string($html) || trim($html) === '') {
        throw new RuntimeException('Không tạo được nội dung báo cáo để xuất PDF.');
    }

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($host !== '') {
        $baseUrl = $protocol . '://' . $host;
        $html = str_replace('src="/', 'src="' . $baseUrl . '/', $html);
        $html = str_replace("src='/", "src='" . $baseUrl . '/', $html);
        $html = str_replace('href="/', 'href="' . $baseUrl . '/', $html);
        $html = str_replace("href='/", "href='" . $baseUrl . '/', $html);
    }

    $weekName = isset($tuan_hoc['ten_tuan']) ? (string)$tuan_hoc['ten_tuan'] : ('Tuan_' . $tuan_id);
    $safeWeekName = preg_replace('/[^\p{L}\p{N}_-]+/u', '_', $weekName);
    $safeWeekName = trim((string)$safeWeekName, '_');
    if ($safeWeekName === '') {
        $safeWeekName = 'Tuan_' . $tuan_id;
    }
    $fileName = 'BaoCao_thidua_' . $safeWeekName . '.pdf';

    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'margin_left' => 6,
        'margin_right' => 6,
        'margin_top' => 6,
        'margin_bottom' => 6,
    ]);

    $mpdf->SetTitle('Bao Cao Thi Dua Tuan');
    $mpdf->WriteHTML($html);
    $mpdf->Output($fileName, \Mpdf\Output\Destination::DOWNLOAD);
    exit();
} catch (Throwable $e) {
    error_log('Weekly PDF export error: ' . $e->getMessage());

    // Fallback để người dùng vẫn có thể lưu PDF bằng hộp thoại in của trình duyệt.
    $printUrl = '/thidua/print/bao-cao-thi-dua?tuan_id=' . urlencode((string)$tuan_id) . '&export_pdf=1';
    header('Location: ' . $printUrl);
    exit();
}

    exit();
}


if ($uri === '/thidua/print/bao-cao-thi-dua') {

    require __DIR__ . '/bao_cao_thi_dua_print_controller.php';
    exit();
}

if ($uri === '/thidua/xuat-bao-cao-vi-pham') {

// File: src/controllers/xuat_bao_cao_vi_pham.php (Nâng cấp cuối cùng - Tinh chỉnh font chữ tiêu đề)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../vendor/autoload.php';







$tuan_id = $_GET['tuan_id'] ?? null;
if (!$tuan_id) die("Lỗi: Thiếu ID của tuần học.");

try {
    
    
    // 1. Lấy thông tin cần thiết
    $stmt_tuan = $db->prepare("SELECT * FROM tuan_hoc WHERE id = ?");
    $stmt_tuan->execute([$tuan_id]);
    $tuan_hoc = $stmt_tuan->fetch();
    if (!$tuan_hoc) die("Lỗi: Tuần học không tồn tại.");

    $admin_name = $_SESSION['user_ten'] ?? 'Quản trị viên';

    // 2. Lấy dữ liệu vi phạm
    $sql = "
        SELECT 
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

    // 3. Bắt đầu tạo file Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('BaoCaoViPham');

    // --- ĐỊNH DẠNG HEADER ---
    $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman');
    $sheet->mergeCells('A1:D1')->setCellValue('A1', 'TRƯỜNG THPT BÌNH SƠN');
    $sheet->mergeCells('A2:D2')->setCellValue('A2', 'HỆ THỐNG ĐÁNH GIÁ THI ĐUA');
    $sheet->getStyle('A1')->getFont()->setSize(11)->setBold(false);
    $sheet->getStyle('A2')->getFont()->setSize(11)->setBold(true);
    $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
    $sheet->mergeCells('A3:F3')->setCellValue('A3', 'BẢNG DANH SÁCH HỌC SINH VI PHẠM NỘI QUY NHÀ TRƯỜNG');
    $ngay_bd = date('d/m/Y', strtotime($tuan_hoc['ngay_bat_dau']));
    $ngay_kt = date('d/m/Y', strtotime($tuan_hoc['ngay_ket_thuc']));
    $tieu_de_tuan = mb_strtoupper($tuan_hoc['ten_tuan'], 'UTF-8') . " (Từ ngày {$ngay_bd} đến ngày {$ngay_kt})";
    $sheet->mergeCells('A4:F4')->setCellValue('A4', $tieu_de_tuan);
    
    // === NÂNG CẤP: Chỉnh size chữ tiêu đề theo yêu cầu ===
$sheet->getStyle('A3')->getFont()->setSize(13)->setBold(true);
    $sheet->getStyle('A4')->getFont()->setSize(12)->setBold(true);
    $sheet->getStyle('A3:F4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // --- ĐỊNH DẠNG BẢNG DỮ LIỆU ---
    $header_row = 6;
    $headers = ['STT', 'Họ và Tên', 'Lớp', 'Ngày VP', 'Danh mục vi phạm', 'Ghi chú'];
    $sheet->fromArray($headers, NULL, 'A' . $header_row);
    $sheet->getStyle('A'.$header_row.':F'.$header_row)->getFont()->setBold(true)->setSize(9);
    $sheet->getStyle('A'.$header_row.':F'.$header_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
    
    // Điền dữ liệu
    $data_row_start = $header_row + 1;
    $rowIndex = $data_row_start;
    foreach ($danh_sach_vi_pham as $index => $vp) {
        $sheet->fromArray([
            $index + 1, $vp['ho_ten'], $vp['ten_lop'],
            date('d/m/Y', strtotime($vp['ngay_vi_pham'])),
            $vp['ten_vi_pham'], $vp['ghi_chu'] ?? ''
        ], NULL, 'A' . $rowIndex);
        $rowIndex++;
    }

    // --- ĐỊNH DẠNG VÀ CĂN CHỈNH ---
        $last_row = $rowIndex - 1;
    if ($last_row >= $data_row_start) {
        $table_range = 'A'.$header_row.':F'.$last_row;
        $sheet->getStyle($table_range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A'.$data_row_start.':F'.$last_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('B'.$data_row_start.':B'.$last_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        
        // Font size cho từng cột
        $sheet->getStyle("A{$data_row_start}:B{$last_row}")->getFont()->setSize(8);
        $sheet->getStyle("C{$data_row_start}:C{$last_row}")->getFont()->setSize(8.2);
        $sheet->getStyle("D{$data_row_start}:E{$last_row}")->getFont()->setSize(8);
        $sheet->getStyle("F{$data_row_start}:F{$last_row}")->getFont()->setSize(7.5);
    }
    
    // Cố định độ rộng cột
    $sheet->getColumnDimension('A')->setWidth(4);   // STT
    $sheet->getColumnDimension('B')->setWidth(18);  // Họ và Tên
    $sheet->getColumnDimension('C')->setWidth(5);   // Lớp
    $sheet->getColumnDimension('D')->setWidth(9);   // Ngày Vi phạm
    $sheet->getColumnDimension('E')->setWidth(60);  // Tên Vi phạm
    $sheet->getColumnDimension('F')->setWidth(6);   // Ghi chú

    // --- FOOTER ---
    $footer_start_row = $last_row + 2;
    $sheet->mergeCells("D{$footer_start_row}:F{$footer_start_row}")->setCellValue('D'.$footer_start_row, 'Đồng Nai, ngày '.date('d').' tháng '.date('m').' năm '.date('Y'));
    $sheet->getStyle("D{$footer_start_row}")->getFont()->setItalic(true);
    $sheet->getStyle("D{$footer_start_row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $footer_start_row++;
    $sheet->mergeCells("D{$footer_start_row}:F{$footer_start_row}")->setCellValue('D'.$footer_start_row, 'NGƯỜI LẬP BẢNG');
    $sheet->getStyle("D{$footer_start_row}")->getFont()->setBold(true);
    $sheet->getStyle("D{$footer_start_row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $footer_start_row += 4;
    $sheet->mergeCells("D{$footer_start_row}:F{$footer_start_row}")->setCellValue('D'.$footer_start_row, $admin_name);
    $sheet->getStyle("D{$footer_start_row}")->getFont()->setBold(true);
    $sheet->getStyle("D{$footer_start_row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // === NÂNG CẤP: THIẾT LẬP TRANG IN ===
    $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
    $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
    
    // Bỏ tính năng Fit to Width
    // $sheet->getPageSetup()->setFitToWidth(1); 


    $sheet->getPageSetup()->setHorizontalCentered(true);
    $sheet->getPageMargins()->setTop(0.4);
    $sheet->getPageMargins()->setBottom(0.4);
    $sheet->getPageMargins()->setLeft(0.2);
    $sheet->getPageMargins()->setRight(0.2);

    // 4. Xuất file cho người dùng
    $ten_tuan_filename = str_replace(' ', '_', $tuan_hoc['ten_tuan']);
    $filename = "BaoCao_ViPham_{$ten_tuan_filename}.xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"; filename*=UTF-8\'\'' . rawurlencode($filename));
    header('Cache-Control: max-age=0');
    ob_clean(); // Xóa mọi "ký tự rác"
flush();    // Đẩy header
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();

} catch (Exception $e) {
    die("Lỗi khi tạo file Excel: " . $e->getMessage());
}
    exit();
}

if ($uri === '/thidua/xuat-bao-cao-vi-pham-chung') {

// File: src/controllers/xuat_bao_cao_vi_pham_chung.php (ĐÃ SỬA LỖI SQL & SYNTAX)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}

require_once __DIR__ . '/../../vendor/autoload.php';






 // Thư viện này cần thiết cho các hàm Excel

$tuan_id = $_GET['tuan_id'] ?? null;
if (!$tuan_id) die("Lỗi: Thiếu ID của tuần học.");

try {
    
    
    // --- (PHẦN 1: LẤY VÀ XỬ LÝ DỮ LIỆU) ---
    $stmt_tuan = $db->prepare("SELECT * FROM tuan_hoc WHERE id = ?");
    $stmt_tuan->execute([$tuan_id]);
    $tuan_hoc = $stmt_tuan->fetch();
    if (!$tuan_hoc) die("Lỗi: Tuần học không tồn tại.");

    $admin_name = $_SESSION['user_ten'] ?? 'Quản trị viên';
    $stmt_diem = $db->query("SELECT DISTINCT diem_tru FROM cau_hinh_vi_pham ORDER BY diem_tru ASC");
    $diem_tru_levels = $stmt_diem->fetchAll(PDO::FETCH_COLUMN);

    $lop_hoc = $db->query("
        SELECT lh.id, lh.ten_lop, COUNT(hs.id) as si_so 
        FROM lop_hoc lh 
        LEFT JOIN hoc_sinh hs ON hs.lop_hoc_id = lh.id AND hs.trang_thai_hoc_tap = 'dang_hoc'
        GROUP BY lh.id, lh.ten_lop -- Sửa lỗi: Thêm lh.ten_lop vào GROUP BY
        ORDER BY CAST(SUBSTR(lh.ten_lop, 1, 2) AS INTEGER), SUBSTR(lh.ten_lop, 3, 1), CAST(SUBSTR(lh.ten_lop, 4) AS INTEGER) ASC
    ")->fetchAll();

    // ================== SỬA LỖI SQL GỐC (từ lần trước) ==================
    $sql_vp = "
        SELECT COALESCE(lh.ten_lop, vp.raw_ten_lop) as ten_lop_final, chvp.diem_tru, COUNT(vp.id) as so_luong
        FROM vi_pham_hoc_sinh vp
        LEFT JOIN quatrinh_hoc_tap qt ON vp.hoc_sinh_id = qt.id LEFT JOIN hoc_sinh hs ON hs.ma_hoc_sinh = qt.ma_hoc_sinh AND hs.nam_hoc_id = qt.nam_hoc_id
        LEFT JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id
        JOIN cau_hinh_vi_pham chvp ON vp.vi_pham_id = chvp.id
        WHERE vp.tuan_hoc_id = ? 
          -- SỬA LỖI: Thay thế alias 'ten_lop_final' bằng biểu thức COALESCE gốc
          AND COALESCE(lh.ten_lop, vp.raw_ten_lop) IS NOT NULL 
          AND COALESCE(lh.ten_lop, vp.raw_ten_lop) != ''
        GROUP BY ten_lop_final, chvp.diem_tru
    ";
    // ================== KẾT THÚC SỬA LỖI SQL ==================

    $stmt_vp = $db->prepare($sql_vp);
    $stmt_vp->execute([$tuan_id]);
    $vi_pham_counts_raw = $stmt_vp->fetchAll();

    $report_data = [];
    foreach ($lop_hoc as $lop) {
        $report_data[$lop['ten_lop']] = ['ten_lop' => $lop['ten_lop'], 'si_so' => $lop['si_so'], 'vi_pham' => [], 'tong_so_luong' => 0];
    }
    foreach ($vi_pham_counts_raw as $count) {
        $ten_lop = $count['ten_lop_final'];
        if (!isset($report_data[$ten_lop])) {
            // Trường hợp lớp KXD (ví dụ: lớp đã bị xóa)
            $report_data[$ten_lop] = ['ten_lop' => $ten_lop, 'si_so' => 'N/A', 'vi_pham' => [], 'tong_so_luong' => 0];
        }
        $report_data[$ten_lop]['vi_pham'][$count['diem_tru']] = $count['so_luong'];
        $report_data[$ten_lop]['tong_so_luong'] += $count['so_luong'];
    }

    // --- (PHẦN 2: TẠO FILE EXCEL) ---
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('BC_VP_Chung');
    
    $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman');
    $sheet->mergeCells('A1:D1')->setCellValue('A1', 'TRƯỜNG THPT BÌNH SƠN');
    $sheet->mergeCells('A2:D2')->setCellValue('A2', 'HỆ THỐNG ĐÁNH GIÁ THI ĐUA');
    $sheet->getStyle('A1')->getFont()->setSize(11)->setBold(false);
    $sheet->getStyle('A2')->getFont()->setSize(11)->setBold(true);
    $sheet->getStyle('A1:D2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
    $last_column_index = count($diem_tru_levels) + 5; // Tính toán chỉ số cột cuối
    $last_column = Coordinate::stringFromColumnIndex($last_column_index); // Chuyển 7 thành 'G', 8 thành 'H', v.v.
    
    $sheet->mergeCells("A4:{$last_column}4")->setCellValue('A4', 'BÁO CÁO TỔNG HỢP VI PHẠM CHUNG THEO LỚP');
    $ngay_bd = date('d/m/Y', strtotime($tuan_hoc['ngay_bat_dau']));
    $ngay_kt = date('d/m/Y', strtotime($tuan_hoc['ngay_ket_thuc']));
    $tieu_de_tuan = mb_strtoupper($tuan_hoc['ten_tuan'], 'UTF-8') . " (TỪ NGÀY {$ngay_bd} ĐẾN NGÀY {$ngay_kt})";
    $sheet->mergeCells("A5:{$last_column}5")->setCellValue('A5', $tieu_de_tuan);
    $sheet->getStyle("A4")->getFont()->setSize(13)->setBold(true);
    $sheet->getStyle("A5")->getFont()->setSize(12)->setBold(true);
    $sheet->getStyle("A4:{$last_column}5")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $header_row = 7;
    $headers = ['STT', 'Lớp', 'Sĩ Số'];
    foreach ($diem_tru_levels as $diem) {
         // Chuyển điểm trừ (số dương trong DB) thành số âm khi hiển thị
        $headers[] = 'SL VP ('.(-$diem).'đ)';
    }
    $headers[] = 'Tổng SLVP';
    $headers[] = 'Tổng Điểm Trừ';
    
    $sheet->fromArray($headers, NULL, 'A' . $header_row);
    $sheet->getStyle('A'.$header_row.':'. $sheet->getHighestColumn() . $header_row)->getFont()->setBold(true)->setSize(10);
    $sheet->getStyle('A'.$header_row.':'. $sheet->getHighestColumn() . $header_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
    
    $data_row_start = $header_row + 1;
    $rowIndex = $data_row_start;
    $stt_counter = 1;
    foreach ($report_data as $data) {
        $rowData = [$stt_counter++, $data['ten_lop'], $data['si_so']];
        $tong_diem_tru = 0;
        foreach ($diem_tru_levels as $diem) {
            $so_luong = $data['vi_pham'][$diem] ?? 0;
            $rowData[] = $so_luong ?: '';
            $tong_diem_tru += $so_luong * $diem;
        }
        $rowData[] = $data['tong_so_luong'] ?: '';
        $rowData[] = $tong_diem_tru ? -$tong_diem_tru : ''; // Hiển thị là số âm
        $sheet->fromArray($rowData, NULL, 'A' . $rowIndex);
        $sheet->getStyle('A'.$rowIndex.':'.$sheet->getHighestColumn().$rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $rowIndex++;
    }

    $last_row = $rowIndex - 1;
    if($last_row >= $data_row_start) {
        $table_range = 'A'.$header_row.':'.$sheet->getHighestColumn().$last_row;
        $sheet->getStyle($table_range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }
    
    foreach (range('A', $sheet->getHighestColumn()) as $columnID) {
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }

    $footer_start_row = $last_row + 2;
    // Tính toán cột bắt đầu cho footer
    $footer_column_index_start = count($diem_tru_levels) + 2; // STT, Lớp, Sĩ số, [diem_tru...], Tổng SLVP, Tổng Điểm
    $footer_column_start = Coordinate::stringFromColumnIndex($footer_column_index_start);
    
    $sheet->mergeCells("{$footer_column_start}{$footer_start_row}:{$last_column}{$footer_start_row}")->setCellValue("{$footer_column_start}{$footer_start_row}", 'Đồng Nai, ngày '.date('d').' tháng '.date('m').' năm '.date('Y'));
    $sheet->getStyle("{$footer_column_start}{$footer_start_row}")->getFont()->setItalic(true);
    $sheet->getStyle("{$footer_column_start}{$footer_start_row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $footer_start_row++;
    $sheet->mergeCells("{$footer_column_start}{$footer_start_row}:{$last_column}{$footer_start_row}")->setCellValue("{$footer_column_start}{$footer_start_row}", 'NGƯỜI LẬP BẢNG');
    $sheet->getStyle("{$footer_column_start}{$footer_start_row}")->getFont()->setBold(true);
    $sheet->getStyle("{$footer_column_start}{$footer_start_row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $footer_start_row += 4;
    $sheet->mergeCells("{$footer_column_start}{$footer_start_row}:{$last_column}{$footer_start_row}")->setCellValue("{$footer_column_start}{$footer_start_row}", $admin_name);
    $sheet->getStyle("{$footer_column_start}{$footer_start_row}")->getFont()->setBold(true);
    $sheet->getStyle("{$footer_column_start}{$footer_start_row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
    $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
    $sheet->getPageSetup()->setFitToWidth(1);
    $sheet->getPageSetup()->setFitToHeight(0);
    $sheet->getPageSetup()->setHorizontalCentered(true);
    $sheet->getPageMargins()->setTop(0.4)->setBottom(0.4)->setLeft(0.2)->setRight(0.2);

    $ten_tuan_filename = str_replace([' ', '/'], '_', $tuan_hoc['ten_tuan']);
    $filename = "BaoCao_ViPhamChung_{$ten_tuan_filename}.xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"; filename*=UTF-8\'\'' . rawurlencode($filename));
    header('Cache-Control: max-age=0');
    ob_clean(); // Xóa mọi "ký tự rác"
    flush();    // Đẩy header
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();

} catch (Exception $e) {
    // ================== SỬA LỖI SYNTAX ERROR ==================
    // Lỗi của tôi là ở dòng die() bên dưới, tôi đã để một dấu " thừa
    die("Lỗi khi tạo file Excel: " . $e->getMessage());
    // ================== KẾT THÚC SỬA LỖI SYNTAX ERROR ==================
}
    exit();
}

if ($uri === '/thidua/xuat-bao-cao-theo-ten-vi-pham') {

// File: src/controllers/xuat_bao_cao_theo_ten_vi_pham.php (ĐÃ NÂNG CẤP BỐ CỤC)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../vendor/autoload.php';








$tuan_id = $_GET['tuan_id'] ?? null;
if (!$tuan_id) die("Lỗi: Thiếu ID của tuần học.");

try {
    
    
    // 1. Lấy thông tin và dữ liệu cần thiết (logic cũ giữ nguyên)
    $stmt_tuan = $db->prepare("SELECT * FROM tuan_hoc WHERE id = ?");
    $stmt_tuan->execute([$tuan_id]);
    $tuan_hoc = $stmt_tuan->fetch();
    if (!$tuan_hoc) die("Lỗi: Tuần học không tồn tại.");

    $admin_name = $_SESSION['user_ten'] ?? 'Quản trị viên';
    
    $sql_summary = "
        SELECT chvp.id as vi_pham_id, chvp.ten_vi_pham, COUNT(vp.id) as so_lan_vi_pham
        FROM vi_pham_hoc_sinh vp
        JOIN cau_hinh_vi_pham chvp ON vp.vi_pham_id = chvp.id
        WHERE vp.tuan_hoc_id = ?
        GROUP BY chvp.id ORDER BY so_lan_vi_pham DESC, ten_vi_pham ASC
    ";
    $stmt_summary = $db->prepare($sql_summary);
    $stmt_summary->execute([$tuan_id]);
    $violation_summary = $stmt_summary->fetchAll();

    $stmt_details = $db->prepare("
        SELECT 
            hs.ma_hoc_sinh, 
            COALESCE(CONCAT(hs.ho_dem, ' ', hs.ten), vp.raw_ho_ten) as ho_ten, 
            COALESCE(lh.ten_lop, vp.raw_ten_lop) as ten_lop,
            vp.ngay_vi_pham, 
            vp.ghi_chu
        FROM vi_pham_hoc_sinh vp
        LEFT JOIN quatrinh_hoc_tap qt ON vp.hoc_sinh_id = qt.id LEFT JOIN hoc_sinh hs ON hs.ma_hoc_sinh = qt.ma_hoc_sinh AND hs.nam_hoc_id = qt.nam_hoc_id
        LEFT JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id
        WHERE vp.tuan_hoc_id = ? AND vp.vi_pham_id = ?
        ORDER BY ten_lop, ho_ten , vp.ngay_vi_pham
    ");

    $report_data = [];
    foreach ($violation_summary as $summary) {
        $stmt_details->execute([$tuan_id, $summary['vi_pham_id']]);
        $details = $stmt_details->fetchAll();
        $report_data[] = ['summary' => $summary, 'details' => $details];
    }

    // 2. Bắt đầu tạo file Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('BC_Theo_Ten_VP');

    // --- ĐỊNH DẠNG HEADER CHUNG ---
    $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman');
    $sheet->mergeCells('A1:c1')->setCellValue('A1', 'TRƯỜNG THPT BÌNH SƠN');
    $sheet->mergeCells('A2:c2')->setCellValue('A2', 'HỆ THỐNG ĐÁNH GIÁ THI ĐUA');
    $sheet->getStyle('A1')->getFont()->setSize(11)->setBold(false);
    $sheet->getStyle('A2')->getFont()->setSize(11)->setBold(true);
    $sheet->getStyle('A1:F2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
    // --- TIÊU ĐỀ BÁO CÁO ---
    $sheet->mergeCells('A4:F4')->setCellValue('A4', 'BÁO CÁO THỐNG KÊ VI PHẠM THEO TỪNG NỘI DUNG');
    $ngay_bd = date('d/m/Y', strtotime($tuan_hoc['ngay_bat_dau']));
    $ngay_kt = date('d/m/Y', strtotime($tuan_hoc['ngay_ket_thuc']));
    $tieu_de_tuan = mb_strtoupper($tuan_hoc['ten_tuan'], 'UTF-8') . " (Từ ngày {$ngay_bd} đến ngày {$ngay_kt})";
    $sheet->mergeCells('A5:F5')->setCellValue('A5', $tieu_de_tuan);
    $sheet->getStyle('A4')->getFont()->setSize(13)->setBold(true);
    $sheet->getStyle('A5')->getFont()->setSize(12)->setBold(true);
    $sheet->getStyle('A4:F5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // --- BẮT ĐẦU ĐIỀN DỮ LIỆU ---
    $rowIndex = 7;
    $stt_chung = 1;

    foreach($report_data as $item) {
        $summary = $item['summary'];
        $details = $item['details'];
        
        // Dòng tiêu đề của nhóm vi phạm
        $sheet->mergeCells("A{$rowIndex}:F{$rowIndex}");
        $group_title = ($stt_chung++).'. '.htmlspecialchars(mb_strtoupper($summary['ten_vi_pham'], 'UTF-8')) . ' ('. $summary['so_lan_vi_pham'] .' LƯỢT)';
        $sheet->setCellValue("A{$rowIndex}", $group_title);
        $sheet->getStyle("A{$rowIndex}")->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle("A{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');
        $sheet->getStyle("A{$rowIndex}")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        $rowIndex++;

        // Dòng header cho bảng chi tiết
        $detail_header_row = $rowIndex;
        $headers = ['STT', 'Số CCCD', 'Họ và Tên', 'Lớp', 'Ngày Vi phạm', 'Ghi chú'];
        // === SỬA LỖI 1: Bắt đầu từ cột 'A' ===
        $sheet->fromArray($headers, NULL, 'A' . $detail_header_row); 
        $sheet->getStyle('A'.$detail_header_row.':F'.$detail_header_row)->getFont()->setBold(true);
        $sheet->getStyle('A'.$detail_header_row.':F'.$detail_header_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $rowIndex++;

        // Dòng dữ liệu chi tiết
        $detail_data_start_row = $rowIndex;
        foreach($details as $detail_idx => $detail_item) {
            // === SỬA LỖI 1: Bắt đầu từ cột 'A' ===
            $sheet->fromArray([
                $detail_idx + 1,
                $detail_item['ma_hoc_sinh'] ?? 'KXD',
                $detail_item['ho_ten'],
                $detail_item['ten_lop'],
                date('d/m/Y', strtotime($detail_item['ngay_vi_pham'])),
                $detail_item['ghi_chu'] ?? ''
            ], NULL, 'A' . $rowIndex);
            $sheet->getStyle('A'.$rowIndex.':F'.$rowIndex)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $rowIndex++;
        }
        
        // Kẻ khung cho bảng chi tiết
        $last_detail_row = $rowIndex - 1;
        if($last_detail_row >= $detail_data_start_row) {
             $sheet->getStyle('A'.$detail_header_row.':F'.$last_detail_row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
             // Căn lề
             $sheet->getStyle('A'.$detail_data_start_row.':B'.$last_detail_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
             $sheet->getStyle('D'.$detail_data_start_row.':F'.$last_detail_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $rowIndex++; // Thêm một dòng trống để ngăn cách các nhóm
    }
    
    // Cố định độ rộng cột
    $sheet->getColumnDimension('A')->setWidth(5);   // STT
    $sheet->getColumnDimension('B')->setWidth(12);  // Số CCCD
    $sheet->getColumnDimension('C')->setWidth(25);  // Họ tên
    $sheet->getColumnDimension('D')->setWidth(8);   // Lớp
    $sheet->getColumnDimension('E')->setWidth(12);  // Ngày VP
    $sheet->getColumnDimension('F')->setWidth(30);  // Ghi chú
    $sheet->getStyle('F7:F'.$rowIndex)->getAlignment()->setWrapText(true); // Cho phép xuống dòng ở cột Ghi chú

    // --- FOOTER ---
    $footer_start_row = $rowIndex + 1;
    // === SỬA LỖI 1: Điều chỉnh vị trí footer ===
    $sheet->mergeCells("D{$footer_start_row}:F{$footer_start_row}")->setCellValue('D'.$footer_start_row, 'Long Thành, ngày '.date('d').' tháng '.date('m').' năm '.date('Y'));
    $sheet->getStyle("D{$footer_start_row}")->getFont()->setItalic(true);
    $sheet->getStyle("D{$footer_start_row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $footer_start_row++;
    $sheet->mergeCells("D{$footer_start_row}:F{$footer_start_row}")->setCellValue('D'.$footer_start_row, 'NGƯỜI LẬP BẢNG');
    $sheet->getStyle("D{$footer_start_row}")->getFont()->setBold(true);
    $sheet->getStyle("D{$footer_start_row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $footer_start_row += 4;
    $sheet->mergeCells("D{$footer_start_row}:F{$footer_start_row}")->setCellValue('D'.$footer_start_row, $admin_name);
    $sheet->getStyle("D{$footer_start_row}")->getFont()->setBold(true);
    $sheet->getStyle("D{$footer_start_row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // --- THIẾT LẬP TRANG IN ---
    // === SỬA LỖI 2: Chuyển sang trang dọc ===
    $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
    $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
    $sheet->getPageSetup()->setFitToWidth(1);
    $sheet->getPageSetup()->setFitToHeight(0);
    $sheet->getPageSetup()->setHorizontalCentered(true);
    $sheet->getPageMargins()->setTop(0.4)->setBottom(0.4)->setLeft(0.2)->setRight(0.2);
    
    // --- XUẤT FILE ---
    $ten_tuan_filename = str_replace(' ', '_', $tuan_hoc['ten_tuan']);
    $filename = "BaoCao_TheoTenViPham_{$ten_tuan_filename}.xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"; filename*=UTF-8\'\'' . rawurlencode($filename));
    header('Cache-Control: max-age=0');
    ob_clean(); // Xóa mọi "ký tự rác"
flush();    // Đẩy header
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();

} catch (Exception $e) {
    die("Lỗi khi tạo file Excel: " . $e->getMessage());
}
    exit();
}

if ($uri === '/thidua/xuat-bao-cao-chi-tiet-lop') {

// File: src/controllers/xuat_bao_cao_chi_tiet_lop.php (ĐÃ NÂNG CẤP GIAO DIỆN EXCEL)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../vendor/autoload.php';








$tuan_ids = $_POST['tuan_ids'] ?? [];
if (empty($tuan_ids)) {
    die("Lỗi: Vui lòng chọn ít nhất một tuần để xuất báo cáo.");
}

try {
    
    $admin_name = $_SESSION['user_ten'] ?? 'Quản trị viên';

    // --- (PHẦN 1: LẤY VÀ TÍNH TOÁN DỮ LIỆU - GIỮ NGUYÊN HOÀN TOÀN LOGIC CŨ CỦA BẠN) ---
    $summary_data_all_weeks = [];
    // ... (Toàn bộ code tính điểm, xếp hạng, KXTĐ của bạn vẫn giữ nguyên ở đây)
    // ... (Kết thúc bằng việc gom nhóm dữ liệu vào biến $data_by_class)

    // 2. Lấy dữ liệu vi phạm chi tiết cho các tuần đã chọn
    $placeholders = implode(',', array_fill(0, count($tuan_ids), '?'));
    $sql_violations = "
        SELECT 
            t.id as tuan_id, t.ten_tuan,
            COALESCE(lh.ten_lop, vp.raw_ten_lop) as ten_lop,
            lh.gvcn_ten, hs.ma_hoc_sinh,
            COALESCE(CONCAT(hs.ho_dem, ' ', hs.ten), vp.raw_ho_ten) as ho_ten,
            vp.ngay_vi_pham, chvp.ten_vi_pham, vp.ghi_chu
        FROM vi_pham_hoc_sinh vp
        LEFT JOIN quatrinh_hoc_tap qt ON vp.hoc_sinh_id = qt.id LEFT JOIN hoc_sinh hs ON hs.ma_hoc_sinh = qt.ma_hoc_sinh AND hs.nam_hoc_id = qt.nam_hoc_id
        LEFT JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id
        JOIN cau_hinh_vi_pham chvp ON vp.vi_pham_id = chvp.id
        JOIN tuan_hoc t ON vp.tuan_hoc_id = t.id
        WHERE vp.tuan_hoc_id IN ($placeholders)
        ORDER BY ten_lop, tuan_id, ho_ten , vp.ngay_vi_pham
    ";
    $stmt_violations = $db->prepare($sql_violations);
    $stmt_violations->execute($tuan_ids);
    $all_violations = $stmt_violations->fetchAll();

    if (empty($all_violations)) {
        die("Không có dữ liệu vi phạm nào trong các tuần đã chọn.");
    }

    // 3. Gom nhóm dữ liệu theo Lớp -> Tuần -> Vi phạm
    $data_by_class = [];
    foreach($all_violations as $vp) {
        $ten_lop = $vp['ten_lop'];
        if (empty($ten_lop)) continue;
        
        if (!isset($data_by_class[$ten_lop])) {
            $data_by_class[$ten_lop] = [
                'gvcn_ten' => $vp['gvcn_ten'],
                'weeks' => []
            ];
        }
        $data_by_class[$ten_lop]['weeks'][$vp['tuan_id']]['ten_tuan'] = $vp['ten_tuan'];
        $data_by_class[$ten_lop]['weeks'][$vp['tuan_id']]['violations'][] = $vp;
    }
    ksort($data_by_class);


    // === BẮT ĐẦU NÂNG CẤP PHẦN TẠO FILE EXCEL ===
    
    $spreadsheet = new Spreadsheet();
    $spreadsheet->removeSheetByIndex(0); // Xóa sheet mặc định

    foreach($data_by_class as $ten_lop => $data) {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle(str_replace(' ', '', $ten_lop));
        
        // --- ĐỊNH DẠNG HEADER CHUNG ---
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman');
        $sheet->mergeCells('A1:C1')->setCellValue('A1', 'TRƯỜNG THPT BÌNH SƠN');
        $sheet->mergeCells('A2:C2')->setCellValue('A2', 'HỆ THỐNG ĐÁNH GIÁ THI ĐUA');
        $sheet->getStyle('A1')->getFont()->setSize(11)->setBold(false);
        $sheet->getStyle('A2')->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('A1:F2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // --- TIÊU ĐỀ BÁO CÁO ---
        $sheet->mergeCells('A4:F4')->setCellValue('A4', 'BÁO CÁO CHI TIẾT VI PHẠM NỀ NẾP');
        $sheet->mergeCells('A5:F5')->setCellValue('A5', 'LỚP: ' . mb_strtoupper($ten_lop, 'UTF-8'));
        $sheet->getStyle('A4:F5')->getFont()->setSize(13)->setBold(true);
        $sheet->getStyle('A4:F5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('A6', 'GVCN: ' . ($data['gvcn_ten'] ?? 'Chưa cập nhật'));
        $sheet->getStyle('A6')->getFont()->setItalic(true);

        $rowIndex = 8; // Bắt đầu từ dòng 8
        
        // --- VÒNG LẶP QUA TỪNG TUẦN CỦA LỚP ---
        foreach($data['weeks'] as $tuan_id_from_data => $week_data) {
            $summary = $summary_data_all_weeks[$tuan_id_from_data][$ten_lop] ?? null;
            
            // Dòng tóm tắt của tuần
            $sheet->mergeCells("A{$rowIndex}:F{$rowIndex}");
            $summary_text = mb_strtoupper($week_data['ten_tuan'] ?? '', 'UTF-8');
            if ($summary) {
                $summary_text .= " | TỔNG ĐIỂM: " . round($summary['tong_diem'], 2);
                $summary_text .= " | HẠNG: " . ($summary['kxtd'] ? 'KXTĐ' : ($summary['xep_hang'] ?? 'N/A'));
                $summary_text .= " | VẮNG P: " . $summary['vang_p'] . " | VẮNG KP: " . $summary['vang_kp'];
            }
            $sheet->setCellValue('A'.$rowIndex, $summary_text);
            $sheet->getStyle('A'.$rowIndex)->getFont()->setBold(true)->setSize(11);
            $sheet->getStyle('A'.$rowIndex)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');
            $rowIndex++;

            // Tiêu đề bảng vi phạm của tuần
            $header_row_start = $rowIndex;
            $headers = ['STT', 'Số CCCD', 'Họ và Tên', 'Ngày Vi phạm', 'Tên Nhóm Vi phạm', 'Ghi chú'];
            $sheet->fromArray($headers, NULL, 'A' . $rowIndex);
            $sheet->getStyle('A'.$rowIndex.':F'.$rowIndex)->getFont()->setBold(true)->setSize(10);
            $sheet->getStyle('A'.$rowIndex.':F'.$rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $rowIndex++;
            
            // Điền dữ liệu vi phạm của tuần
            $data_row_start = $rowIndex;
            $stt_counter = 1;
            if (!empty($week_data['violations'])) {
                foreach($week_data['violations'] as $vp) {
                    $sheet->fromArray([
                        $stt_counter++,
                        $vp['ma_hoc_sinh'] ?? 'KXD',
                        $vp['ho_ten'],
                        date('d/m/Y', strtotime($vp['ngay_vi_pham'])),
                        $vp['ten_vi_pham'],
                        $vp['ghi_chu'] ?? ''
                    ], NULL, 'A' . $rowIndex);
                    $sheet->getStyle('A'.$rowIndex.':F'.$rowIndex)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
                    $rowIndex++;
                }
            } else {
                $sheet->mergeCells("A{$rowIndex}:F{$rowIndex}")->setCellValue('A'.$rowIndex, 'Không có vi phạm trong tuần.');
                $sheet->getStyle('A'.$rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $rowIndex++;
            }
            
            // Kẻ khung cho bảng của tuần
            $last_data_row = $rowIndex - 1;
            $sheet->getStyle("A{$header_row_start}:F{$last_data_row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            $rowIndex++; // Thêm một dòng trống để ngăn cách các tuần
        }
        
        // --- ĐỊNH DẠNG CỘT VÀ FOOTER ---
        $sheet->getColumnDimension('A')->setWidth(5);   // STT
        $sheet->getColumnDimension('B')->setWidth(12);  // Số CCCD
        $sheet->getColumnDimension('C')->setWidth(25);  // Họ và Tên
        $sheet->getColumnDimension('D')->setWidth(10);  // Ngày VP
        $sheet->getColumnDimension('E')->setWidth(57);  // Tên Vi phạm
        $sheet->getColumnDimension('F')->setWidth(10);  // Ghi chú

        $footer_start_row = $rowIndex + 1;
        $sheet->mergeCells("e{$footer_start_row}:F{$footer_start_row}")->setCellValue('e'.$footer_start_row, 'Đồng Nai, ngày '.date('d').' tháng '.date('m').' năm '.date('Y'));
        $sheet->getStyle("e{$footer_start_row}")->getFont()->setItalic(true);
        $sheet->getStyle("e{$footer_start_row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $footer_start_row++;
        $sheet->mergeCells("e{$footer_start_row}:F{$footer_start_row}")->setCellValue('e'.$footer_start_row, 'NGƯỜI LẬP BẢNG');
        $sheet->getStyle("e{$footer_start_row}")->getFont()->setBold(true);
        $sheet->getStyle("e{$footer_start_row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $footer_start_row += 4;
        $sheet->mergeCells("e{$footer_start_row}:F{$footer_start_row}")->setCellValue('e'.$footer_start_row, $admin_name);
        $sheet->getStyle("e{$footer_start_row}")->getFont()->setBold(true);
        $sheet->getStyle("e{$footer_start_row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // --- THIẾT LẬP TRANG IN ---
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        $sheet->getPageSetup()->setHorizontalCentered(true);
        $sheet->getPageMargins()->setTop(0.4)->setBottom(0.4)->setLeft(0.2)->setRight(0.2);
    }

    $spreadsheet->setActiveSheetIndex(0);
    $filename = "BaoCao_ChiTiet_TheoLop_" . date('Ymd_His') . ".xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"; filename*=UTF-8\'\'' . rawurlencode($filename));
    header('Cache-Control: max-age=0');
    ob_clean(); // Xóa mọi "ký tự rác"
flush();    // Đẩy header
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();

} catch (Exception $e) {
    die("Lỗi khi tạo file Excel: " . $e->getMessage());
}
    exit();
}

if ($uri === '/thidua/xuat-bao-cao/thanh-tich-toan-dien') {

// FILE: src/controllers/xuat_bao_cao_thanh_tich_toan_dien.php (Đã sửa lỗi hiển thị dữ liệu)

set_time_limit(0);
ini_set('memory_limit', '512M');


if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    http_response_code(403);
    exit('Không có quyền truy cập.');
}








try {
    
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $admin_name = $_SESSION['user_ten'] ?? 'Quản trị viên';

    // === BƯỚC 1: LẤY DỮ LIỆU CƠ BẢN ===

    $all_weeks = $db->query("SELECT id, ten_tuan FROM tuan_hoc ORDER BY ngay_bat_dau ASC")->fetchAll(PDO::FETCH_ASSOC);
    $all_classes = $db->query("
        SELECT 
            id, ten_lop, gvcn_ten, 
            (SELECT COUNT(id) FROM hoc_sinh WHERE lop_hoc_id = lop_hoc.id) as si_so 
        FROM lop_hoc 
        ORDER BY 
            CAST(SUBSTR(ten_lop, 1, 2) AS INTEGER), 
            SUBSTR(ten_lop, 3, 1), 
            CAST(SUBSTR(ten_lop, 4) AS INTEGER) ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    $report_data = [];
    foreach ($all_classes as $class) {
        $report_data[$class['id']] = [
            'info' => $class, 'weeks' => [],
            'rank_summary' => ['1' => 0, '2' => 0, '3' => 0, 'chot' => 0, 'kxtd' => 0]
        ];
    }
    
    $calculator = new thiduaCalculator($db);

    // === BƯỚC 2: TÍNH TOÁN ĐIỂM VÀ XẾP HẠNG (LOGIC ĐÃ SỬA LỖI) ===
    
    foreach ($all_weeks as $week) {
        $raw_data_for_week = $calculator->calculateRawDataForWeek((int)$week['id']);
        $ranked_data_for_week = $calculator->rankWeeklyData($raw_data_for_week);
        
        $weekly_results_lookup = [];
        foreach ($ranked_data_for_week as $class_result) {
            $weekly_results_lookup[$class_result['lop']] = $class_result;
        }

        foreach ($all_classes as $class) {
            $ten_lop = $class['ten_lop'];
            
            if (isset($weekly_results_lookup[$ten_lop])) {
                $result = $weekly_results_lookup[$ten_lop];
                
                // ✨ SỬA LỖI: Sử dụng đúng key 'xep_hang' và 'is_kxtd'
                $rank = $result['is_kxtd'] ? 'KXTĐ' : $result['xep_hang'];
                
                $report_data[$class['id']]['weeks'][$week['id']] = [
                    // ✨ SỬA LỖI: Sử dụng đúng key 'tong_diem'
                    'score' => isset($result['tong_diem']) ? round($result['tong_diem'], 2) : 'N/A',
                    'rank' => $rank
                ];

                // Cập nhật bảng tổng kết
                if ($rank === 1) $report_data[$class['id']]['rank_summary']['1']++;
                elseif ($rank === 2) $report_data[$class['id']]['rank_summary']['2']++;
                elseif ($rank === 3) $report_data[$class['id']]['rank_summary']['3']++;
                elseif ($rank === 'KXTĐ') $report_data[$class['id']]['rank_summary']['kxtd']++;
                
                // ✨ SỬA LỖI: Logic hạng chót dùng key 'xep_hang'
                if (!$result['is_kxtd'] && isset($result['total_ranked_classes']) && $result['xep_hang'] === $result['total_ranked_classes']) {
                    $report_data[$class['id']]['rank_summary']['chot']++;
                }

            } else {
                $report_data[$class['id']]['weeks'][$week['id']] = ['score' => '', 'rank' => ''];
            }
        }
    }

    // === BƯỚC 3: TẠO FILE EXCEL (Không thay đổi) ===
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('BaoCaoThanhTichToanDien');

    // Header & tiêu đề
    $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman')->setSize(11);
    $sheet->mergeCells('A1:E1')->setCellValue('A1', 'HỆ THỐNG ĐÁNH GIÁ THI ĐUA');
    $sheet->mergeCells('A2:E2')->setCellValue('A2', 'BÁO CÁO THÀNH TÍCH THI ĐUA TOÀN DIỆN');
    $sheet->getStyle('A1:A2')->getFont()->setBold(true);
    $sheet->getStyle('A1:E2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Header bảng động
    $header_row = 4;
    $headers = ['STT', 'Khối', 'Lớp', 'Sĩ Số', 'GVCN'];
    foreach ($all_weeks as $week) {
        $headers[] = 'Điểm ' . $week['ten_tuan'];
        $headers[] = 'Hạng ' . $week['ten_tuan'];
    }
    $summary_headers = ['Tổng Hạng 1', 'Tổng Hạng 2', 'Tổng Hạng 3', 'Tổng Hạng Chót', 'Tổng KXTĐ'];
    $headers = array_merge($headers, $summary_headers);

    $sheet->fromArray($headers, NULL, 'A' . $header_row);
    $last_column_letter = Coordinate::stringFromColumnIndex(count($headers));
    $sheet->getStyle('A'.$header_row.':'.$last_column_letter.$header_row)->getFont()->setBold(true);
    $sheet->getStyle('A'.$header_row.':'.$last_column_letter.$header_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);

    // ĐIỀN DỮ LIỆU
    $rowIndex = $header_row + 1;
    $stt = 1;
    foreach ($report_data as $class_data) {
        $row = [
            $stt++,
            substr($class_data['info']['ten_lop'], 0, 2),
            $class_data['info']['ten_lop'],
            $class_data['info']['si_so'],
            $class_data['info']['gvcn_ten']
        ];
        foreach ($all_weeks as $week) {
            $week_data = $class_data['weeks'][$week['id']] ?? ['score' => '', 'rank' => ''];
            $row[] = $week_data['score'];
            $row[] = $week_data['rank'];
        }
        $row = array_merge($row, [
            $class_data['rank_summary']['1'] ?: '',
            $class_data['rank_summary']['2'] ?: '',
            $class_data['rank_summary']['3'] ?: '',
            $class_data['rank_summary']['chot'] ?: '',
            $class_data['rank_summary']['kxtd'] ?: ''
        ]);
        $sheet->fromArray($row, NULL, 'A'.$rowIndex++);
    }

    // ĐỊNH DẠNG BẢNG
    $last_row = $rowIndex - 1;
    $table_range = 'A'.$header_row.':'.$last_column_letter.$last_row;
    $sheet->getStyle($table_range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $sheet->getStyle('A'.$header_row.':'.$last_column_letter.$last_row)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    $sheet->getStyle('F'.$header_row.':'.$last_column_letter.$last_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    foreach (range('A', $last_column_letter) as $columnID) {
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }

    // --- FOOTER VÀ TRANG IN ---
    $footer_start_row = $last_row + 2;
    $footer_col_start_index = count($headers) - 2; // bắt đầu cách 2 cột so với cuối
    $footer_col_start = Coordinate::stringFromColumnIndex($footer_col_start_index);

    $sheet->mergeCells("{$footer_col_start}{$footer_start_row}:{$last_column_letter}{$footer_start_row}")
          ->setCellValue("{$footer_col_start}{$footer_start_row}", 'Đồng Nai, ngày ' . date('d') . ' tháng ' . date('m') . ' năm ' . date('Y'));
    $sheet->getStyle("{$footer_col_start}{$footer_start_row}")->getFont()->setItalic(true)->setSize(11);
    $sheet->getStyle("{$footer_col_start}{$footer_start_row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $footer_start_row++;

    $sheet->mergeCells("{$footer_col_start}{$footer_start_row}:{$last_column_letter}{$footer_start_row}")
          ->setCellValue("{$footer_col_start}{$footer_start_row}", 'NGƯỜI LẬP BẢNG');
    $sheet->getStyle("{$footer_col_start}{$footer_start_row}")->getFont()->setBold(true)->setSize(11);
    $sheet->getStyle("{$footer_col_start}{$footer_start_row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $footer_start_row += 4;

    $sheet->mergeCells("{$footer_col_start}{$footer_start_row}:{$last_column_letter}{$footer_start_row}")
          ->setCellValue("{$footer_col_start}{$footer_start_row}", $admin_name);
    $sheet->getStyle("{$footer_col_start}{$footer_start_row}")->getFont()->setBold(true)->setSize(11);
    $sheet->getStyle("{$footer_col_start}{$footer_start_row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
    $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A3);
    $sheet->getPageSetup()->setFitToWidth(1);
    $sheet->getPageSetup()->setFitToHeight(0);

    // --- XUẤT FILE ---
    $filename = "BaoCaothiduaCaNam_" . date('Ymd_His') . ".xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"; filename*=UTF-8\'\'' . rawurlencode($filename));
    header('Cache-Control: max-age=0');
    ob_clean(); // Xóa mọi "ký tự rác"
flush();    // Đẩy header
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();

} catch (Exception $e) {
    die("Lỗi khi tạo file Excel: " . $e->getMessage() . " --- TRACE: " . $e->getTraceAsString());
}
    exit();
}

if ($uri === '/thidua/xuat-bao-cao/chi-tiet-tuan-theo-lop') {

// File: src/controllers/xuat_bao_cao_chi_tiet_tuan_theo_lop.php (ĐÃ NÂNG CẤP SỬ DỤNG thiduaCALCULATOR - BẢN ĐẦY ĐỦ)


if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu'); exit();
}

// === NHÚNG CÁC THƯ VIỆN CẦN THIẾT ===

require_once __DIR__ . '/../lib/lop_hoc_db.php';
require_once __DIR__ . '/../lib/hoc_sinh_db.php';
// >>> THÊM BỘ NÃO MỚI VÀO <<<








$tuan_hoc_id = $_GET['tuan_hoc_id'] ?? null;
if (!$tuan_hoc_id) die("Lỗi: Vui lòng chọn một tuần học.");

try {
    
    $admin_name = $_SESSION['user_ten'] ?? 'Quản trị viên';

    // Lấy thông tin tuần học
    $tuan_hoc = get_tuan_hoc_by_id($db, $tuan_hoc_id);
    if (!$tuan_hoc) {
        die("Lỗi: Tuần học với ID {$tuan_hoc_id} không tồn tại.");
    }

    // === BƯỚC 1: SỬ DỤNG "BỘ NÃO" ĐỂ TÍNH TOÁN VÀ XẾP HẠNG (LOGIC MỚI) ===
    $calculator = new thiduaCalculator($db);
    
    // Tính toán toàn bộ dữ liệu thô cho tuần
    $report_data_raw = $calculator->calculateRawDataForWeek((int)$tuan_hoc_id);
    
    // Xếp hạng dữ liệu
    $report_data_ranked = $calculator->rankWeeklyData($report_data_raw);
    
    // Tạo một mảng tra cứu nhanh thông tin tổng kết của từng lớp
    $final_summary = [];
    foreach ($report_data_ranked as $data) {
        // Sử dụng 'ten_lop' làm key để tương thích với code phía sau
        $final_summary[$data['lop']] = $data;
    }

    // === BƯỚC 2: BẮT ĐẦU TẠO FILE EXCEL ===
    $spreadsheet = new Spreadsheet();
    $spreadsheet->removeSheetByIndex(0); // Xóa sheet mặc định

    $all_classes = get_all_lop_hoc($db); // Lấy danh sách lớp để tạo sheet

    foreach ($all_classes as $lop_hoc) {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle(str_replace(' ', '', $lop_hoc['ten_lop']));

        // Lấy dữ liệu chi tiết cho từng lớp
        $violation_records = get_vi_pham_by_lop_and_tuan($db, $lop_hoc['id'], $tuan_hoc_id);
        $attendance_summary = get_diem_danh_summary_by_lop_and_tuan($db, $lop_hoc['id'], $tuan_hoc_id);
        $danh_sach_hoc_sinh = get_all_hoc_sinh($db, ['lop_id' => $lop_hoc['id']]);
        
        // Lấy thông tin tổng kết từ mảng đã tính toán trước đó
        $class_summary = $final_summary[$lop_hoc['ten_lop']] ?? null;

        // Tạo mảng tra cứu tên học sinh
        $student_names = [];
        foreach($danh_sach_hoc_sinh as $hs) { 
            $student_names[$hs['id']] = $hs['ho_dem'] . ' ' . $hs['ten']; 
        }
        
        // === PHẦN TẠO GIAO DIỆN EXCEL (ĐẦY ĐỦ) ===
        
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman');
        $sheet->mergeCells('A1:C1')->setCellValue('A1', 'TRƯỜNG THPT BÌNH SƠN')->getStyle('A1')->getFont()->setSize(11);
        $sheet->mergeCells('A2:C2')->setCellValue('A2', 'HỆ THỐNG ĐÁNH GIÁ THI ĐUA')->getStyle('A2')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A1:F2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A4:F4')->setCellValue('A4', 'BÁO CÁO CHI TIẾT VI PHẠM NỀ NẾP');
        $sheet->getStyle('A4')->getFont()->setSize(13)->setBold(true);
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $rowIndex = 5;
        $sheet->mergeCells("A{$rowIndex}:F{$rowIndex}");
        $sheet->setCellValue('A'.$rowIndex, 'LỚP: ' . mb_strtoupper($lop_hoc['ten_lop'], 'UTF-8') . ' - ' . mb_strtoupper($tuan_hoc['ten_tuan'], 'UTF-8'));
        $sheet->getStyle('A'.$rowIndex)->getFont()->setSize(13)->setBold(true);
        $sheet->getStyle('A'.$rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $rowIndex = 7;
        $sheet->mergeCells("A{$rowIndex}:B{$rowIndex}")->setCellValue('A'.$rowIndex, 'GVCN:')->getStyle("A{$rowIndex}:B{$rowIndex}")->getFont()->setBold(true);
        $sheet->setCellValue('C'.$rowIndex, $lop_hoc['gvcn_ten'] ?? 'Chưa cập nhật');
        $rowIndex++;

        $sheet->mergeCells("A{$rowIndex}:B{$rowIndex}")->setCellValue('A'.$rowIndex, 'Tổng điểm:')->getStyle("A{$rowIndex}:B{$rowIndex}")->getFont()->setBold(true);
        $sheet->setCellValue('C'.$rowIndex, isset($class_summary['tong_diem']) ? round($class_summary['tong_diem'], 2) : 'N/A');
        $rowIndex++;
        
        $sheet->mergeCells("A{$rowIndex}:B{$rowIndex}")->setCellValue('A'.$rowIndex, 'Xếp hạng:')->getStyle("A{$rowIndex}:B{$rowIndex}")->getFont()->setBold(true);
        $sheet->setCellValue('C'.$rowIndex, $class_summary['xep_hang'] ?? 'N/A');
        $rowIndex++;
        
        $sheet->mergeCells("A{$rowIndex}:B{$rowIndex}")->setCellValue('A'.$rowIndex, 'Vắng (P):')->getStyle("A{$rowIndex}:B{$rowIndex}")->getFont()->setBold(true);
        $sheet->setCellValue('C'.$rowIndex, ($attendance_summary['vang_p'] ?? 0) . ' lượt');
        $rowIndex++;

        $sheet->mergeCells("A{$rowIndex}:B{$rowIndex}")->setCellValue('A'.$rowIndex, 'Vắng (KP):')->getStyle("A{$rowIndex}:B{$rowIndex}")->getFont()->setBold(true);
        $sheet->setCellValue('C'.$rowIndex, ($attendance_summary['vang_kp'] ?? 0) . ' lượt');
        $rowIndex += 2;
        
        $header_row_start = $rowIndex;
        $headers = ['STT', 'Số CCCD', 'Họ và Tên', 'Ngày Vi phạm', 'Tên Nhóm Vi phạm', 'Ghi chú'];
        $sheet->fromArray($headers, NULL, 'A' . $rowIndex);
        $sheet->getStyle('A'.$rowIndex.':F'.$rowIndex)->applyFromArray(['font' => ['bold' => true, 'size' => 10], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]]);
        $rowIndex++;
        
        $stt_counter = 1;
        if (!empty($violation_records)) {
            foreach($violation_records as $vp) {
                $sheet->fromArray([
                    $stt_counter++, $vp['ma_hoc_sinh'] ?? 'KXD',
                    $vp['ho_ten'] ?? ($student_names[$vp['hoc_sinh_id']] ?? 'Không rõ'),
                    date('d/m/Y', strtotime($vp['ngay_vi_pham'])),
                    $vp['ten_vi_pham'], $vp['ghi_chu'] ?? ''
                ], NULL, 'A' . $rowIndex);
                $sheet->getStyle('A'.$rowIndex.':F'.$rowIndex)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
                $rowIndex++;
            }
        } else {
            $sheet->mergeCells("A{$rowIndex}:F{$rowIndex}")->setCellValue('A'.$rowIndex, 'Không có vi phạm trong tuần.');
            $sheet->getStyle('A'.$rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $rowIndex++;
        }
        
        $last_data_row = $rowIndex - 1;
        $sheet->getStyle("A{$header_row_start}:F{$last_data_row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(52);
        $sheet->getColumnDimension('F')->setWidth(8);

        $footer_start_row = $rowIndex + 1;
        $sheet->mergeCells("E{$footer_start_row}:F{$footer_start_row}")->setCellValue("E{$footer_start_row}", 'Long Thành, ngày '.date('d').' tháng '.date('m').' năm '.date('Y'));
        $style_footer_date = $sheet->getStyle("E{$footer_start_row}");
        $style_footer_date->getFont()->setItalic(true);
        $style_footer_date->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $footer_start_row++;
        $sheet->mergeCells("E{$footer_start_row}:F{$footer_start_row}")->setCellValue("E{$footer_start_row}", 'NGƯỜI LẬP BẢNG');
        $style_footer_signer = $sheet->getStyle("E{$footer_start_row}");
        $style_footer_signer->getFont()->setBold(true);
        $style_footer_signer->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $footer_start_row += 3;
        $sheet->mergeCells("E{$footer_start_row}:F{$footer_start_row}")->setCellValue("E{$footer_start_row}", $admin_name);
        $style_footer_name = $sheet->getStyle("E{$footer_start_row}");
        $style_footer_name->getFont()->setBold(true);
        $style_footer_name->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
    }

    $spreadsheet->setActiveSheetIndex(0);
    $ten_tuan_filename = str_replace(' ', '_', $tuan_hoc['ten_tuan']);
    $filename = "BaoCaoChiTiet_TatCaLop_{$ten_tuan_filename}.xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"; filename*=UTF-8\'\'' . rawurlencode($filename));
    header('Cache-Control: max-age=0');
    ob_clean(); // Xóa mọi "ký tự rác"
flush();    // Đẩy header
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();

} catch (Exception $e) {
    die("Lỗi khi tạo file Excel: " . $e->getMessage());
}
    exit();
}

if ($uri === '/thidua/xuat-bao-cao/ds-vi-pham') {

// File: src/controllers/xuat_ds_vi_pham_nhieu_tuan.php (Bản nâng cấp toàn diện)

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu'); exit();
}


require_once __DIR__ . '/../lib/lop_hoc_db.php'; // Nạp file lib đã cập nhật








$tuan_ids = $_POST['tuan_ids'] ?? [];
if (empty($tuan_ids) || !is_array($tuan_ids)) {
    die("Lỗi: Vui lòng chọn ít nhất một tuần học.");
}

try {
    
    $admin_name = $_SESSION['user_ten'] ?? 'Quản trị viên';

    // 1. Lấy tất cả vi phạm và gom nhóm theo lớp
    $all_violations = get_violations_by_week_ids($db, $tuan_ids);

    if (empty($all_violations)) {
        die("Không có dữ liệu vi phạm nào trong các tuần đã chọn.");
    }

    $data_by_class = [];
    foreach($all_violations as $vp) {
        $ten_lop = $vp['ten_lop'];
        if (!isset($data_by_class[$ten_lop])) {
            $data_by_class[$ten_lop] = [
                'gvcn_ten' => $vp['gvcn_ten'],
                'violations' => []
            ];
        }
        $data_by_class[$ten_lop]['violations'][] = $vp;
    }

    // 2. Tạo file Excel
    $spreadsheet = new Spreadsheet();

    // --- TẠO SHEET "TOÀN TRƯỜNG" ĐẦU TIÊN ---
    $sheet_all = $spreadsheet->getActiveSheet();
    $sheet_all->setTitle('Toan Truong');

    // Dựng giao diện cho sheet Toàn Trường
    $sheet_all->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman')->setSize(11);
    $sheet_all->mergeCells('A1:G1')->setCellValue('A1', 'HỆ THỐNG ĐÁNH GIÁ THI ĐUA');
    $sheet_all->mergeCells('A2:G2')->setCellValue('A2', 'DANH SÁCH VI PHẠM TOÀN TRƯỜNG');
    $sheet_all->getStyle('A2')->getFont()->setBold(true);
    $sheet_all->getStyle('A1:G2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $header_row_all = 4;
    $headers_all = ['STT', 'Lớp', 'Số CCCD', 'Họ và Tên', 'Ngày Vi phạm', 'Tên Nhóm Vi phạm', 'Ghi chú'];
    $sheet_all->fromArray($headers_all, NULL, 'A' . $header_row_all);
    $sheet_all->getStyle('A'.$header_row_all.':G'.$header_row_all)->getFont()->setBold(true);
    $sheet_all->getStyle('A'.$header_row_all.':G'.$header_row_all)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9EAD3');
    
    $rowIndex_all = $header_row_all + 1;
    $stt_all = 1;
    foreach($all_violations as $vp) {
        $sheet_all->fromArray([
            $stt_all++, $vp['ten_lop'], $vp['ma_hoc_sinh'], $vp['ho_ten'], 
            date('d/m/Y', strtotime($vp['ngay_vi_pham'])), $vp['ten_vi_pham'], $vp['ghi_chu']
        ], NULL, 'A'.$rowIndex_all++);
    }

    $last_row_all = $rowIndex_all - 1;
    $sheet_all->getStyle('A'.$header_row_all.':G'.$last_row_all)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $sheet_all->getColumnDimension('D')->setWidth(25);
    $sheet_all->getColumnDimension('F')->setWidth(40);
    $sheet_all->getColumnDimension('G')->setWidth(30);

    // --- VÒNG LẶP TẠO SHEET CHO TỪNG LỚP ---
    foreach ($data_by_class as $ten_lop => $class_data) {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle(str_replace(' ', '', $ten_lop));

        // Dựng giao diện
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman')->setSize(11);
        $sheet->mergeCells('A1:G1')->setCellValue('A1', 'TRƯỜNG THPT BÌNH SƠN');
        $sheet->mergeCells('A2:G2')->setCellValue('A2', 'DANH SÁCH VI PHẠM LỚP ' . mb_strtoupper($ten_lop, 'UTF-8'));
        $sheet->getStyle('A2')->getFont()->setBold(true);
        $sheet->getStyle('A1:G2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A4', 'GVCN:')->getStyle('A4')->getFont()->setBold(true);
        $sheet->setCellValue('B4', $class_data['gvcn_ten']);

        $header_row = 6;
        $headers = ['STT', 'Lớp', 'Số CCCD', 'Họ và Tên', 'Ngày Vi phạm', 'Tên Nhóm Vi phạm', 'Ghi chú'];
        $sheet->fromArray($headers, NULL, 'A' . $header_row);
        $sheet->getStyle('A'.$header_row.':G'.$header_row)->getFont()->setBold(true);
        $sheet->getStyle('A'.$header_row.':G'.$header_row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9EAD3');

        $rowIndex = $header_row + 1;
        $stt = 1;
        foreach($class_data['violations'] as $vp) {
            $sheet->fromArray([
                $stt++, $vp['ten_lop'], $vp['ma_hoc_sinh'], $vp['ho_ten'], 
                date('d/m/Y', strtotime($vp['ngay_vi_pham'])), $vp['ten_vi_pham'], $vp['ghi_chu']
            ], NULL, 'A'.$rowIndex++);
        }

        $last_row = $rowIndex - 1;
        $sheet->getStyle('A'.$header_row.':G'.$last_row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('F')->setWidth(40);
        $sheet->getColumnDimension('G')->setWidth(30);
    }

    // 3. Xuất file
    $spreadsheet->setActiveSheetIndex(0); // Mở sheet đầu tiên ("Toàn Trường") khi người dùng mở file
    $filename = "DanhSachViPham_TheoLop_" . date('Ymd_His') . ".xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"; filename*=UTF-8\'\'' . rawurlencode($filename));
    header('Cache-Control: max-age=0');
    ob_clean(); // Xóa mọi "ký tự rác"
flush();    // Đẩy header
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();

} catch (Exception $e) {
    die('Lỗi khi tạo file Excel: ' . $e->getMessage());
}
    exit();
}

if ($uri === '/thidua/xuat-bao-cao/hs-sl-vp-ca-nhan') {

// File: src/controllers/xuat_bao_cao_hs_sl_vp_ca_nhan.php


if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}

require_once __DIR__ . '/../../vendor/autoload.php';

require_once __DIR__ . '/../lib/lop_hoc_db.php';








$tuan_ids = $_POST['tuan_ids'] ?? [];
$tuan_ids = array_values(array_filter(array_map('intval', $tuan_ids), function ($id) {
    return $id > 0;
}));

if (empty($tuan_ids)) {
    die('Lỗi: Vui lòng chọn ít nhất một tuần học.');
}

try {
    
    $admin_name = $_SESSION['user_ten'] ?? 'Quản trị viên';

    $placeholders = implode(',', array_fill(0, count($tuan_ids), '?'));

    // Lấy thông tin tuần để hiển thị tiêu đề
    $stmt_weeks = $db->prepare("SELECT id, ten_tuan, ngay_bat_dau, ngay_ket_thuc FROM tuan_hoc WHERE id IN ($placeholders) ORDER BY ngay_bat_dau");
    $stmt_weeks->execute($tuan_ids);
    $weeks = $stmt_weeks->fetchAll(PDO::FETCH_ASSOC);
    if (empty($weeks)) {
        die('Không tìm thấy thông tin tuần học đã chọn.');
    }
    $week_names = array_map(function ($week) {
        return $week['ten_tuan'];
    }, $weeks);
    $weeks_text = implode(', ', $week_names);

        // Lấy danh sách nhóm vi phạm và mức điểm trừ xuất hiện trong các tuần đã chọn (dựng cột động theo Nhóm x Điểm trừ)
        $stmt_groups = $db->prepare("
            SELECT DISTINCT chvp.nhom_vi_pham
            FROM vi_pham_hoc_sinh vp
            JOIN cau_hinh_vi_pham chvp ON vp.vi_pham_id = chvp.id
            WHERE vp.tuan_hoc_id IN ($placeholders)
              AND vp.hoc_sinh_id IS NOT NULL
              AND chvp.nhom_vi_pham IS NOT NULL AND chvp.nhom_vi_pham <> ''
            ORDER BY chvp.nhom_vi_pham ASC
        ");
        $stmt_groups->execute($tuan_ids);
        $violation_groups = $stmt_groups->fetchAll(PDO::FETCH_COLUMN);

        $stmt_penalties = $db->prepare("
            SELECT DISTINCT chvp.diem_tru
            FROM vi_pham_hoc_sinh vp
            JOIN cau_hinh_vi_pham chvp ON vp.vi_pham_id = chvp.id
            WHERE vp.tuan_hoc_id IN ($placeholders)
              AND vp.hoc_sinh_id IS NOT NULL
            ORDER BY chvp.diem_tru ASC
        ");
        $stmt_penalties->execute($tuan_ids);
        $penalty_levels = $stmt_penalties->fetchAll(PDO::FETCH_COLUMN);

        if (empty($violation_groups) || empty($penalty_levels)) {
            die('Không có vi phạm nào trong các tuần đã chọn.');
        }

    // Danh sách học sinh đang học kèm thông tin lớp
    $students = get_all_students_with_class_info($db);
    if (empty($students)) {
        die('Không tìm thấy dữ liệu học sinh đang học.');
    }

    $students_by_class = [];
    foreach ($students as $student) {
        $class_name = $student['ten_lop'] ?? 'KXD';
        $students_by_class[$class_name][] = $student;
    }
    ksort($students_by_class);

    // Đếm số lần vi phạm theo học sinh, nhóm và mức điểm trừ trong các tuần đã chọn
    $sql_counts = "
        SELECT vp.hoc_sinh_id, chvp.nhom_vi_pham, chvp.diem_tru, COUNT(vp.id) as so_luong
        FROM vi_pham_hoc_sinh vp
        JOIN cau_hinh_vi_pham chvp ON vp.vi_pham_id = chvp.id
        WHERE vp.tuan_hoc_id IN ($placeholders)
          AND vp.hoc_sinh_id IS NOT NULL
          AND chvp.nhom_vi_pham IS NOT NULL AND chvp.nhom_vi_pham <> ''
        GROUP BY vp.hoc_sinh_id, chvp.nhom_vi_pham, chvp.diem_tru
    ";
    $stmt_counts = $db->prepare($sql_counts);
    $stmt_counts->execute($tuan_ids);
    $violation_counts = [];
    $group_penalty_usage = [];
    foreach ($stmt_counts->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $hsId = (int)$row['hoc_sinh_id'];
        $groupName = (string)$row['nhom_vi_pham'];
        $penalty = (int)$row['diem_tru'];
        $countVal = (int)$row['so_luong'];
        $violation_counts[$hsId][$groupName][$penalty] = $countVal;
        if ($countVal > 0) {
            $group_penalty_usage[$groupName][$penalty] = true;
        }
    }

    // Xác định danh sách cột động: chỉ giữ các mức điểm trừ có dữ liệu trong nhóm, kèm cột tổng từng nhóm
    $groupColumns = [];
    foreach ($violation_groups as $groupName) {
        $usedPenalties = [];
        foreach ($penalty_levels as $penalty) {
            if (!empty($group_penalty_usage[$groupName][$penalty])) {
                $usedPenalties[] = $penalty;
            }
        }
        if (!empty($usedPenalties)) {
            // Rút gọn tên nhóm: lấy chữ cái đầu của từng từ
            $short = implode('', array_map(function ($part) {
                $part = trim($part);
                return $part === '' ? '' : mb_substr($part, 0, 1, 'UTF-8');
            }, preg_split('/\s+/', $groupName)));
            $groupColumns[] = [
                'name' => $groupName,
                'short' => $short ?: $groupName,
                'penalties' => $usedPenalties
            ];
        }
    }
    if (empty($groupColumns)) {
        die('Không có vi phạm nào trong các tuần đã chọn.');
    }

    $spreadsheet = new Spreadsheet();
    $spreadsheet->getDefaultStyle()->getFont()->setName('Times New Roman')->setSize(11);

    // Sheet 1: Toàn trường
    $sheetAll = $spreadsheet->getActiveSheet();
    $sheetAll->setTitle('Toan Truong');

    // Dựng tiêu đề cột: cho mỗi Nhóm, tạo các cột tương ứng từng mức điểm trừ đã phát sinh, sau đó là cột tổng nhóm
    $dynamicHeaders = [];
    foreach ($groupColumns as $groupMeta) {
        foreach ($groupMeta['penalties'] as $penalty) {
            $dynamicHeaders[] = [
                'type' => 'penalty',
                'group' => $groupMeta['name'],
                'penalty' => $penalty,
                'label' => $groupMeta['short'] . ' (-' . abs((int)$penalty) . ')'
            ];
        }
        $dynamicHeaders[] = [
            'type' => 'group_total',
            'group' => $groupMeta['name'],
            'label' => 'Tổng ' . $groupMeta['short']
        ];
    }

    $headersAll = ['STT', 'Lớp', 'CCCD', 'Tên', 'Ngày sinh'];
    foreach ($dynamicHeaders as $col) {
        $headersAll[] = $col['label'];
    }
    $headersAll = array_merge($headersAll, ['Tổng cộng', 'Ghi chú']);

    $lastColAll = Coordinate::stringFromColumnIndex(count($headersAll));

    $sheetAll->mergeCells('A1:' . $lastColAll . '1')->setCellValue('A1', 'TRƯỜNG THPT BÌNH SƠN');
    $sheetAll->mergeCells('A2:' . $lastColAll . '2')->setCellValue('A2', 'BÁO CÁO SỐ LƯỢNG VI PHẠM CÁ NHÂN');
    $sheetAll->mergeCells('A3:' . $lastColAll . '3')->setCellValue('A3', 'Các tuần: ' . $weeks_text);
    $sheetAll->getStyle('A1:' . $lastColAll . '3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheetAll->getStyle('A2')->getFont()->setBold(true)->setSize(13);

    $headerRowAll = 5;
    $sheetAll->fromArray($headersAll, null, 'A' . $headerRowAll);
    $sheetAll->getStyle('A' . $headerRowAll . ':' . $lastColAll . $headerRowAll)->getFont()->setBold(true);
    $sheetAll->getStyle('A' . $headerRowAll . ':' . $lastColAll . $headerRowAll)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9EAD3');
    $sheetAll->getStyle('A' . $headerRowAll . ':' . $lastColAll . $headerRowAll)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $dataRowAll = $headerRowAll + 1;
    $sttAll = 1;
    foreach ($students_by_class as $class_name => $class_students) {
        foreach ($class_students as $student) {
            $full_name = trim(($student['ho_dem'] ?? '') . ' ' . ($student['ten'] ?? ''));
            $dob = !empty($student['ngay_sinh']) ? date('d/m/Y', strtotime($student['ngay_sinh'])) : '';
            $counts = $violation_counts[(int)$student['id']] ?? [];

            $row = [$sttAll++, $class_name, $student['ma_hoc_sinh'] ?? '', $full_name, $dob];
            $total = 0;
            foreach ($groupColumns as $groupMeta) {
                $groupSum = 0;
                foreach ($groupMeta['penalties'] as $penalty) {
                    $count = $counts[$groupMeta['name']][$penalty] ?? 0;
                    $row[] = $count;
                    $groupSum += $count;
                }
                $row[] = $groupSum; // Tổng theo nhóm
                $total += $groupSum;
            }
            $row[] = $total; // Tổng tất cả nhóm
            $row[] = '';

            $sheetAll->fromArray($row, null, 'A' . $dataRowAll);
            $dataRowAll++;
        }
    }

    $lastDataRowAll = $dataRowAll - 1;

    // Đặt độ rộng cột cho sheet toàn trường (đồng thời tạo sẵn tên cột động)
    $sheetAll->getColumnDimension('A')->setWidth(5);
    $sheetAll->getColumnDimension('B')->setWidth(8);
    $sheetAll->getColumnDimension('C')->setWidth(15);
    $sheetAll->getColumnDimension('D')->setWidth(24);
    $sheetAll->getColumnDimension('E')->setWidth(12);
    $dynamicColIndex = 6;
    foreach ($dynamicHeaders as $colMeta) {
        $col = Coordinate::stringFromColumnIndex($dynamicColIndex++);
        $sheetAll->getColumnDimension($col)->setWidth(10);
    }
    $totalCol = Coordinate::stringFromColumnIndex($dynamicColIndex++);
    $sheetAll->getColumnDimension($totalCol)->setWidth(8);
    $noteCol = Coordinate::stringFromColumnIndex($dynamicColIndex);
    $sheetAll->getColumnDimension($noteCol)->setWidth(20);

    if ($lastDataRowAll >= $headerRowAll + 1) {
        $sheetAll->getStyle('A' . $headerRowAll . ':' . $lastColAll . $lastDataRowAll)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheetAll->getStyle('A' . ($headerRowAll + 1) . ':' . $lastColAll . $lastDataRowAll)
            ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheetAll->getStyle('D' . ($headerRowAll + 1) . ':D' . $lastDataRowAll)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheetAll->getStyle('A' . ($headerRowAll + 1) . ':C' . $lastDataRowAll)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheetAll->getStyle('E' . ($headerRowAll + 1) . ':' . $lastColAll . $lastDataRowAll)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheetAll->getStyle($noteCol . ($headerRowAll + 1) . ':' . $noteCol . $lastDataRowAll)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    }

    $sheetAll->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
    $sheetAll->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
    $sheetAll->getPageSetup()->setFitToWidth(1)->setFitToHeight(0);
    $sheetAll->getPageMargins()->setTop(0.4)->setBottom(0.4)->setLeft(0.2)->setRight(0.2);
    $sheetAll->getPageSetup()->setHorizontalCentered(true);

    // Các sheet lớp
    foreach ($students_by_class as $class_name => $class_students) {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle(str_replace(' ', '', $class_name));

        $headersClass = ['STT', 'CCCD', 'Tên', 'Ngày sinh'];
        foreach ($dynamicHeaders as $col) {
            $headersClass[] = $col['label'];
        }
        $headersClass = array_merge($headersClass, ['Tổng cộng', 'Ghi chú']);

        $lastColClass = Coordinate::stringFromColumnIndex(count($headersClass));

        $sheet->mergeCells('A1:' . $lastColClass . '1')->setCellValue('A1', 'TRƯỜNG THPT BÌNH SƠN');
        $sheet->mergeCells('A2:' . $lastColClass . '2')->setCellValue('A2', 'BÁO CÁO VI PHẠM CÁ NHÂN - LỚP ' . mb_strtoupper($class_name, 'UTF-8'));
        $sheet->mergeCells('A3:' . $lastColClass . '3')->setCellValue('A3', 'Các tuần: ' . $weeks_text);
        $sheet->getStyle('A1:' . $lastColClass . '3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(13);

        $headerRowClass = 5;
        $sheet->fromArray($headersClass, null, 'A' . $headerRowClass);
        $sheet->getStyle('A' . $headerRowClass . ':' . $lastColClass . $headerRowClass)->getFont()->setBold(true);
        $sheet->getStyle('A' . $headerRowClass . ':' . $lastColClass . $headerRowClass)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9EAD3');
        $sheet->getStyle('A' . $headerRowClass . ':' . $lastColClass . $headerRowClass)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $dataRow = $headerRowClass + 1;
        $stt = 1;
        foreach ($class_students as $student) {
            $full_name = trim(($student['ho_dem'] ?? '') . ' ' . ($student['ten'] ?? ''));
            $dob = !empty($student['ngay_sinh']) ? date('d/m/Y', strtotime($student['ngay_sinh'])) : '';
            $counts = $violation_counts[(int)$student['id']] ?? [];

            $row = [$stt++, $student['ma_hoc_sinh'] ?? '', $full_name, $dob];
            $total = 0;
            foreach ($groupColumns as $groupMeta) {
                $groupSum = 0;
                foreach ($groupMeta['penalties'] as $penalty) {
                    $count = $counts[$groupMeta['name']][$penalty] ?? 0;
                    $row[] = $count;
                    $groupSum += $count;
                }
                $row[] = $groupSum;
                $total += $groupSum;
            }
            $row[] = $total;
            $row[] = '';

            $sheet->fromArray($row, null, 'A' . $dataRow);
            $dataRow++;
        }

        $lastDataRow = $dataRow - 1;

        // Đặt độ rộng cột cho từng sheet lớp (đồng thời chuẩn bị tên cột ghi chú)
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(24);
        $sheet->getColumnDimension('D')->setWidth(12);
        $dynColIdx = 5;
        foreach ($dynamicHeaders as $colMeta) {
            $col = Coordinate::stringFromColumnIndex($dynColIdx++);
            $sheet->getColumnDimension($col)->setWidth(10);
        }
        $totalClassCol = Coordinate::stringFromColumnIndex($dynColIdx++);
        $sheet->getColumnDimension($totalClassCol)->setWidth(8);
        $noteClassCol = Coordinate::stringFromColumnIndex($dynColIdx);
        $sheet->getColumnDimension($noteClassCol)->setWidth(20);

        if ($lastDataRow >= $headerRowClass + 1) {
            $sheet->getStyle('A' . $headerRowClass . ':' . $lastColClass . $lastDataRow)
                ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('A' . ($headerRowClass + 1) . ':' . $lastColClass . $lastDataRow)
                ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('C' . ($headerRowClass + 1) . ':C' . $lastDataRow)
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('A' . ($headerRowClass + 1) . ':B' . $lastDataRow)
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . ($headerRowClass + 1) . ':' . $lastColClass . $lastDataRow)
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($noteClassCol . ($headerRowClass + 1) . ':' . $noteClassCol . $lastDataRow)
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }

        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setFitToWidth(1)->setFitToHeight(0);
        $sheet->getPageMargins()->setTop(0.4)->setBottom(0.4)->setLeft(0.2)->setRight(0.2);
        $sheet->getPageSetup()->setHorizontalCentered(true);
    }

    $spreadsheet->setActiveSheetIndex(0);
    $filename = 'BC_HS_SL_VP_CA_NHAN_' . date('Ymd_His') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"; filename*=UTF-8\'\'' . rawurlencode($filename));
    header('Cache-Control: max-age=0');
    ob_clean();
    flush();
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();
} catch (Exception $e) {
    die('Lỗi khi tạo file Excel: ' . $e->getMessage());
}

    exit();
}

if ($uri === '/thidua/xuat-bao-cao/toan-bo-ho-so-zip') {

// FILE: src/controllers/xuat_toan_bo_ho_so_zip.php (Đã nâng cấp ảnh thẻ & ngày sinh)

// Cấu hình để script chạy lâu không bị timeout
set_time_limit(0); 
ini_set('memory_limit', '512M'); 

// Nạp các thư viện và cấu hình cần thiết

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    exit('Bạn không có quyền truy cập.');
}


require_once __DIR__ . '/../lib/hoc_sinh_db.php'; 

// Sử dụng các lớp từ thư viện PhpSpreadsheet






// ✨ NẠP THÊM THƯ VIỆN ĐỂ VẼ ẢNH VÀO EXCEL


/**
 * Hàm trợ giúp chuyển đổi chuỗi có dấu thành không dấu để đặt tên file.
 * @param string $str Chuỗi cần chuyển đổi.
 * @return string Chuỗi đã được chuyển đổi và làm sạch.
 */
function to_unsigned_string($str){
    $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", "a", $str);
    $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", "e", $str);
    $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", "i", $str);
    $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", "o", $str);
    $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", "u", $str);
    $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", "y", $str);
    $str = preg_replace("/(đ)/", "d", $str);
    $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", "A", $str);
    $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", "E", $str);
    $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", "I", $str);
    $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", "O", $str);
    $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", "U", $str);
    $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", "Y", $str);
    $str = preg_replace("/(Đ)/", "D", $str);
    $str = preg_replace('/[^A-Za-z0-9\-_.]/', '_', $str);
    $str = preg_replace('/_+/', '_', $str);
    return $str;
}

/**
 * ✨ HÀM MỚI: Định dạng ngày sinh một cách an toàn
 */
function format_safe_date($date_str) {
    if (empty(trim($date_str))) {
        return ''; // Trả về rỗng nếu không có dữ liệu
    }
    // Thử đọc định dạng Y-m-d trước
    $date = DateTime::createFromFormat('Y-m-d', $date_str);
    // Nếu không được, thử đọc định dạng d/m/Y
    if (!$date) {
        $date = DateTime::createFromFormat('d/m/Y', $date_str);
    }
    // Trả về ngày đã định dạng hoặc chuỗi gốc nếu không hợp lệ
    return $date ? $date->format('d/m/Y') : $date_str;
}

try {
    
    $admin_name = $_SESSION['user_ten'] ?? 'Quản trị viên';

    // 1. Lấy danh sách tất cả học sinh
    $all_students = get_all_hoc_sinh($db); 
    if (empty($all_students)) {
        die("Không có học sinh nào trong hệ thống.");
    }

    // 2. Tạo file ZIP tạm thời
    $zip_filename = "ToanBoHoSoHocSinh_" . date('Ymd_His') . ".zip";
    $zip_filepath = sys_get_temp_dir() . '/' . $zip_filename;
    $zip = new ZipArchive();
    if ($zip->open($zip_filepath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        die("Lỗi: Không thể tạo file nén.");
    }

    // 3. Vòng lặp qua từng học sinh để tạo file Excel
    foreach ($all_students as $hoc_sinh) {
        // Lấy dữ liệu chi tiết của học sinh (khen thưởng, vi phạm)
        $stmt_kt = $db->prepare("SELECT kt.ngay_khen_thuong, kt.ten_khen_thuong, kt.cap_khen_thuong, kt.so_quyet_dinh FROM khen_thuong kt WHERE (kt.hoc_sinh_id = ? OR kt.hoc_sinh_id IN (SELECT id FROM quatrinh_hoc_tap WHERE ma_hoc_sinh = ?)) AND kt.loai = 'ca_nhan' ORDER BY kt.ngay_khen_thuong DESC");
        $stmt_kt->execute([$hoc_sinh['id'], $hoc_sinh['ma_hoc_sinh']]);
        $lich_su_khen_thuong = $stmt_kt->fetchAll(PDO::FETCH_ASSOC);

        $stmt_vp = $db->prepare("SELECT vphs.ngay_vi_pham, chvp.ten_vi_pham, vphs.ghi_chu FROM vi_pham_hoc_sinh vphs JOIN cau_hinh_vi_pham chvp ON vphs.vi_pham_id = chvp.id JOIN quatrinh_hoc_tap qt ON vphs.hoc_sinh_id = qt.id JOIN hoc_sinh hs ON hs.ma_hoc_sinh = qt.ma_hoc_sinh AND hs.nam_hoc_id = qt.nam_hoc_id WHERE hs.id = ? ORDER BY vphs.ngay_vi_pham DESC");
        $stmt_vp->execute([$hoc_sinh['id']]);
        $lich_su_vi_pham = $stmt_vp->fetchAll(PDO::FETCH_ASSOC);

        $stmt_hd = $db->prepare("
            SELECT hd.ten_hoat_dong, hddk.created_at as ngay_tham_gia, hddk.trang_thai_diem_danh, hddk.diem_thuc_te, hd.diem_tich_luy 
            FROM hoat_dong_dang_ky hddk
            JOIN hoat_dong hd ON hddk.hoat_dong_id = hd.id
            JOIN ho_so_hoc_sinh hs_main ON (hddk.hoc_sinh_id = hs_main.id OR hddk.hoc_sinh_id IN (SELECT id FROM quatrinh_hoc_tap WHERE ma_hoc_sinh = hs_main.ma_hoc_sinh))
            WHERE hs_main.ma_hoc_sinh = ?
            ORDER BY hddk.created_at DESC
        ");
        $stmt_hd->execute([$hoc_sinh['ma_hoc_sinh']]);
        $lich_su_hoat_dong = $stmt_hd->fetchAll(PDO::FETCH_ASSOC);

        // --- BẮT ĐẦU TẠO FILE EXCEL ĐƯỢC NÂNG CẤP ---
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Times New Roman')->setSize(11);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('HoSoTongHop');
        
        // --- Tiêu đề file ---
        $sheet->mergeCells('A1:C1')->setCellValue('A1', 'TRƯỜNG THPT BÌNH SƠN');
        $sheet->mergeCells('A2:C2')->setCellValue('A2', 'HỆ THỐNG ĐÁNH GIÁ THI ĐUA');
        $sheet->mergeCells('A4:E4')->setCellValue('A4', 'HỒ SƠ HỌC SINH');
        $sheet->getStyle('A1:A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2:A4')->getFont()->setBold(true);
        $sheet->getStyle('A4')->getFont()->setSize(14);
        
        $current_row = 6;
        
        // --- I. THÔNG TIN CÁ NHÂN ---
        $sheet->mergeCells("A{$current_row}:E{$current_row}")->setCellValue("A{$current_row}", 'I. THÔNG TIN CÁ NHÂN');
        $sheet->getStyle("A{$current_row}")->getFont()->setBold(true)->setSize(12);
        $current_row++;

        // ✨ NÂNG CẤP: Chèn ảnh thẻ vào file Excel
        if (!empty($hoc_sinh['anh_the'])) {
            $imagePath = __DIR__ . '/../../public/assets/anh_the/' . $hoc_sinh['anh_the'];
            if (file_exists($imagePath)) {
                $drawing = new Drawing();
                $drawing->setPath($imagePath);
                $drawing->setCoordinates('D' . $current_row);
                $drawing->setHeight(160);
                $drawing->setWorksheet($sheet);
                $sheet->mergeCells("D{$current_row}:E" . ($current_row + 5)); // Merge ô để chứa ảnh
            }
        }
        
        // ✨ NÂNG CẤP: Sử dụng hàm format_safe_date
        $info_data = [
    ['Họ và tên:', $hoc_sinh['ho_dem'] . ' ' . $hoc_sinh['ten']],
    ['Số CCCD:', $hoc_sinh['ma_hoc_sinh']],
    ['Lớp:', $hoc_sinh['ten_lop']],
    ['GVCN:', $hoc_sinh['gvcn_ten'] ?? 'Chưa có'],
    ['Ngày sinh:', format_safe_date($hoc_sinh['ngay_sinh'])],
    ['Chức vụ:', $hoc_sinh['chuc_vu'] ?? 'Học sinh'],
    
    // === DÒNG MỚI THÊM VÀO ===
    ['Trạng thái:', ($hoc_sinh['trang_thai_hoc_tap'] === 'nghi_hoc' ? 'Đã nghỉ học' : 'Đang học')]
];
        foreach ($info_data as $info) {
            $sheet->setCellValue("A{$current_row}", $info[0])->getStyle("A{$current_row}")->getFont()->setBold(true);
            $sheet->mergeCells("B{$current_row}:C{$current_row}")->setCellValue("B{$current_row}", $info[1]);
            $current_row++;
        }
        $current_row++;
// --- II. LỊCH SỬ KHEN THƯỞNG ---
        $sheet->mergeCells("A{$current_row}:E{$current_row}")->setCellValue("A{$current_row}", 'II. DANH SÁCH KHEN THƯỞNG');
        $sheet->getStyle("A{$current_row}")->getFont()->setBold(true)->setSize(12);
        $current_row++;
        if (!empty($lich_su_khen_thuong)) {
            $header_kt = ['STT', 'Ngày KT', 'Tên Khen Thưởng', 'Cấp Khen Thưởng', 'Số QĐ'];
            $sheet->fromArray($header_kt, NULL, "A{$current_row}");
            $start_data_row = ++$current_row;
            foreach($lich_su_khen_thuong as $index => $item) {
                $sheet->fromArray([$index + 1, date('d/m/Y', strtotime($item['ngay_khen_thuong'])), $item['ten_khen_thuong'], $item['cap_khen_thuong'], $item['so_quyet_dinh']], NULL, "A{$current_row}");
                $current_row++;
            }
            $sheet->getStyle("A".($start_data_row-1).":E".($current_row-1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        } else {
            $sheet->setCellValue("A{$current_row}", 'Chưa có khen thưởng nào.'); $current_row++;
        }
        $current_row++;

        // --- III. LỊCH SỬ VI PHẠM ---
        $sheet->mergeCells("A{$current_row}:E{$current_row}")->setCellValue("A{$current_row}", 'III. DANH SÁCH VI PHẠM');
        $sheet->getStyle("A{$current_row}")->getFont()->setBold(true)->setSize(12);
        $current_row++;
        if (!empty($lich_su_vi_pham)) {
             $header_vp = ['STT', 'Ngày Vi Phạm', 'Tên Lỗi', 'Ghi Chú'];
             $sheet->fromArray($header_vp, NULL, "A{$current_row}");
             $sheet->mergeCells("D{$current_row}:E{$current_row}");
             $start_data_row = ++$current_row;
             foreach($lich_su_vi_pham as $index => $item) {
                $sheet->fromArray([$index + 1, date('d/m/Y', strtotime($item['ngay_vi_pham'])), $item['ten_vi_pham'], $item['ghi_chu']], NULL, "A{$current_row}");
                $sheet->mergeCells("D{$current_row}:E{$current_row}");
                $current_row++;
             }
             $sheet->getStyle("A".($start_data_row-1).":E".($current_row-1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        } else {
            $sheet->setCellValue("A{$current_row}", 'Không có vi phạm nào.'); $current_row++;
        }
        $current_row++;

        // --- IV. HOẠT ĐỘNG THAM GIA ---
        $sheet->mergeCells("A{$current_row}:E{$current_row}")->setCellValue("A{$current_row}", 'IV. HOẠT ĐỘNG THAM GIA');
        $sheet->getStyle("A{$current_row}")->getFont()->setBold(true)->setSize(12);
        $current_row++;
        if (!empty($lich_su_hoat_dong)) {
            $header_hd = ['STT', 'Ngày Tham Gia', 'Tên Hoạt Động', 'Trạng Thái', 'Điểm Cộng'];
            $sheet->fromArray($header_hd, NULL, "A{$current_row}");
            $header_range = "A{$current_row}:E{$current_row}";
            $sheet->getStyle($header_range)->getFont()->setBold(true);
            $sheet->getStyle($header_range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $start_data_row = ++$current_row;
            foreach($lich_su_hoat_dong as $index => $item) {
                $trang_thai_text = $item['trang_thai_diem_danh'] == 1 ? 'Đã tham gia' : 'Chưa điểm danh';
                $diem_text = $item['trang_thai_diem_danh'] == 1 ? ('+' . (float)$item['diem_thuc_te'] . ' đ') : '0 đ';
                $sheet->fromArray([$index + 1, date('d/m/Y H:i', strtotime($item['ngay_tham_gia'])), $item['ten_hoat_dong'], $trang_thai_text, $diem_text], NULL, "A{$current_row}");
                $current_row++;
            }
            $sheet->getStyle("A".($start_data_row-1).":E".($current_row-1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        } else {
            $sheet->setCellValue("A{$current_row}", 'Chưa tham gia hoạt động nào.'); $current_row++;
        }
        
        // --- Chân trang ký tên (Giống các báo cáo khác) ---
        $current_row += 2;
        $sheet->mergeCells("b{$current_row}:E{$current_row}")->setCellValue('b'.$current_row, 'Đồng Nai, ngày '.date('d').' tháng '.date('m').' năm '.date('Y'));
        $sheet->getStyle("b{$current_row}")->getFont()->setItalic(true);
        $sheet->getStyle("b{$current_row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $current_row++;
        $sheet->mergeCells("b{$current_row}:E{$current_row}")->setCellValue('b'.$current_row, 'XÁC NHẬN CỦA GVCN');
        $sheet->getStyle("b{$current_row}")->getFont()->setBold(true);
        $sheet->getStyle("b{$current_row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $current_row += 4;
        $sheet->mergeCells("b{$current_row}:E{$current_row}")->setCellValue('b'.$current_row, $admin_name);
        $sheet->getStyle("b{$current_row}")->getFont()->setBold(true);
        $sheet->getStyle("b{$current_row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // --- Căn chỉnh cột & trang in ---
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT)->setPaperSize(PageSetup::PAPERSIZE_A4);
        $sheet->getPageMargins()->setTop(0.4)->setBottom(0.4)->setLeft(0.2)->setRight(0.2);

        // --- 4. Lưu file Excel vào bộ nhớ đệm ---
        $writer = new Xlsx($spreadsheet);
        $excel_filename = to_unsigned_string("{$hoc_sinh['ma_hoc_sinh']}_{$hoc_sinh['ho_dem']}_{$hoc_sinh['ten']}.xlsx");
        
        ob_start();
        $writer->save('php://output');
        $excel_content = ob_get_clean();

        // Thêm thư mục ảo và file Excel vào ZIP
        $zip->addEmptyDir($hoc_sinh['ten_lop']);
        $zip->addFromString($hoc_sinh['ten_lop'] . '/' . $excel_filename, $excel_content);

        // ✨ NÂNG CẤP: Thêm file ảnh thẻ vào ZIP
        if (!empty($hoc_sinh['anh_the'])) {
            $photo_path = __DIR__ . '/../../public/assets/anh_the/' . $hoc_sinh['anh_the'];
            if (file_exists($photo_path)) {
                $extension = pathinfo($photo_path, PATHINFO_EXTENSION);
                $photo_zip_filename = to_unsigned_string("{$hoc_sinh['ma_hoc_sinh']}_{$hoc_sinh['ho_dem']}_{$hoc_sinh['ten']}_ANHTHE") . '.' . $extension;
                $zip->addFile($photo_path, $hoc_sinh['ten_lop'] . '/' . $photo_zip_filename);
            }
        }
    }

    // 5. Đóng file nén và gửi cho người dùng
    $zip->close();
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . basename($zip_filepath) . '"; filename*=UTF-8\'\'' . rawurlencode(basename($zip_filepath)));
    header('Content-Length: ' . filesize($zip_filepath));
    ob_clean(); // Xóa mọi "ký tự rác"
flush();    // Đẩy header
    readfile($zip_filepath);

    // 6. Dọn dẹp file tạm
    unlink($zip_filepath);

    exit();

} catch (Exception $e) {
    die('Lỗi khi tạo file nén: ' . $e->getMessage());
}
    exit();
}

if ($uri === '/thidua/xuat-bao-cao/chi-tiet-tuan-zip') {

// File: src/controllers/xuat_bao_cao_chi_tiet_zip.php (ĐÃ NÂNG CẤP SỬ DỤNG thiduaCALCULATOR - BẢN ĐẦY ĐỦ)

set_time_limit(0); // Cho phép script chạy lâu hơn để tạo nhiều file
ini_set('memory_limit', '512M'); // Tăng giới hạn bộ nhớ


if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu'); exit();
}

// === NHÚNG CÁC THƯ VIỆN CẦN THIẾT ===

require_once __DIR__ . '/../lib/lop_hoc_db.php';
require_once __DIR__ . '/../lib/hoc_sinh_db.php';
// >>> THÊM BỘ NÃO MỚI VÀO <<<








$tuan_hoc_id = $_GET['tuan_hoc_id'] ?? null;
if (!$tuan_hoc_id) die("Lỗi: Vui lòng chọn một tuần học.");

try {
    
    $admin_name = $_SESSION['user_ten'] ?? 'Quản trị viên';

    // Lấy thông tin tuần học
    $tuan_hoc = get_tuan_hoc_by_id($db, $tuan_hoc_id);
    if (!$tuan_hoc) {
        die("Lỗi: Tuần học với ID {$tuan_hoc_id} không tồn tại.");
    }

    // === BƯỚC 1: SỬ DỤNG "BỘ NÃO" ĐỂ TÍNH TOÁN VÀ XẾP HẠNG (LOGIC MỚI) ===
    $calculator = new thiduaCalculator($db);
    
    // Tính toán toàn bộ dữ liệu thô cho tuần
    $report_data_raw = $calculator->calculateRawDataForWeek((int)$tuan_hoc_id);
    
    // Xếp hạng dữ liệu
    $report_data_ranked = $calculator->rankWeeklyData($report_data_raw);
    
    // Tạo một mảng tra cứu nhanh thông tin tổng kết của từng lớp
    $final_summary = [];
    foreach ($report_data_ranked as $data) {
        $final_summary[$data['lop']] = $data;
    }

    // === BƯỚC 2: BẮT ĐẦU TẠO CÁC FILE EXCEL VÀ NÉN ZIP ===
    $temp_dir = sys_get_temp_dir() . '/export_' . time() . '_' . uniqid();
    if (!mkdir($temp_dir, 0777, true)) die("Lỗi: Không thể tạo thư mục tạm thời.");
    $generated_files = [];

    $all_classes = get_all_lop_hoc($db);

    foreach ($all_classes as $lop_hoc) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet_name = preg_replace('/[^A-Za-z0-9\-]/', '', $lop_hoc['ten_lop']);
        $sheet->setTitle($sheet_name);

        // Lấy dữ liệu chi tiết cho từng lớp
        $violation_records = get_vi_pham_by_lop_and_tuan($db, $lop_hoc['id'], $tuan_hoc_id);
        $attendance_summary = get_diem_danh_summary_by_lop_and_tuan($db, $lop_hoc['id'], $tuan_hoc_id);
        $danh_sach_hoc_sinh = get_all_hoc_sinh($db, ['lop_id' => $lop_hoc['id']]);
        
        // Lấy thông tin tổng kết từ mảng đã tính toán trước đó
        $class_summary = $final_summary[$lop_hoc['ten_lop']] ?? null;

        // Tạo mảng tra cứu tên học sinh
        $student_names = [];
        foreach($danh_sach_hoc_sinh as $hs) { 
            $student_names[$hs['id']] = $hs['ho_dem'] . ' ' . $hs['ten']; 
        }
        
        // === PHẦN TẠO GIAO DIỆN EXCEL (ĐẦY ĐỦ) ===
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman');
        $sheet->mergeCells('A1:C1')->setCellValue('A1', 'TRƯỜNG THPT BÌNH SƠN')->getStyle('A1')->getFont()->setSize(11);
        $sheet->mergeCells('A2:C2')->setCellValue('A2', 'HỆ THỐNG ĐÁNH GIÁ THI ĐUA')->getStyle('A2')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A1:F2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A4:F4')->setCellValue('A4', 'BÁO CÁO CHI TIẾT VI PHẠM NỀ NẾP');
        $sheet->getStyle('A4')->getFont()->setSize(13)->setBold(true);
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $rowIndex = 5;
        $sheet->mergeCells("A{$rowIndex}:F{$rowIndex}");
        $sheet->setCellValue('A'.$rowIndex, 'LỚP: ' . mb_strtoupper($lop_hoc['ten_lop'], 'UTF-8') . ' - ' . mb_strtoupper($tuan_hoc['ten_tuan'], 'UTF-8'));
        $sheet->getStyle('A'.$rowIndex)->getFont()->setSize(13)->setBold(true);
        $sheet->getStyle('A'.$rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $rowIndex = 7;
        $sheet->mergeCells("A{$rowIndex}:B{$rowIndex}")->setCellValue('A'.$rowIndex, 'GVCN:')->getStyle("A{$rowIndex}:B{$rowIndex}")->getFont()->setBold(true);
        $sheet->setCellValue('C'.$rowIndex, $lop_hoc['gvcn_ten'] ?? 'Chưa cập nhật');
        $rowIndex++;

        $sheet->mergeCells("A{$rowIndex}:B{$rowIndex}")->setCellValue('A'.$rowIndex, 'Tổng điểm:')->getStyle("A{$rowIndex}:B{$rowIndex}")->getFont()->setBold(true);
        $sheet->setCellValue('C'.$rowIndex, isset($class_summary['tong_diem']) ? round($class_summary['tong_diem'], 2) : 'N/A');
        $rowIndex++;
        
        $sheet->mergeCells("A{$rowIndex}:B{$rowIndex}")->setCellValue('A'.$rowIndex, 'Xếp hạng:')->getStyle("A{$rowIndex}:B{$rowIndex}")->getFont()->setBold(true);
        $sheet->setCellValue('C'.$rowIndex, $class_summary['xep_hang'] ?? 'N/A');
        $rowIndex++;
        
        $sheet->mergeCells("A{$rowIndex}:B{$rowIndex}")->setCellValue('A'.$rowIndex, 'Vắng (P):')->getStyle("A{$rowIndex}:B{$rowIndex}")->getFont()->setBold(true);
        $sheet->setCellValue('C'.$rowIndex, ($attendance_summary['vang_p'] ?? 0) . ' lượt');
        $rowIndex++;

        $sheet->mergeCells("A{$rowIndex}:B{$rowIndex}")->setCellValue('A'.$rowIndex, 'Vắng (KP):')->getStyle("A{$rowIndex}:B{$rowIndex}")->getFont()->setBold(true);
        $sheet->setCellValue('C'.$rowIndex, ($attendance_summary['vang_kp'] ?? 0) . ' lượt');
        $rowIndex += 2;
        
        $header_row_start = $rowIndex;
        $headers = ['STT', 'Số CCCD', 'Họ và Tên', 'Ngày Vi phạm', 'Tên Nhóm Vi phạm', 'Ghi chú'];
        $sheet->fromArray($headers, NULL, 'A' . $rowIndex);
        $sheet->getStyle('A'.$rowIndex.':F'.$rowIndex)->applyFromArray(['font' => ['bold' => true, 'size' => 10], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]]);
        $rowIndex++;
        
        $stt_counter = 1;
        if (!empty($violation_records)) {
            foreach($violation_records as $vp) {
                $sheet->fromArray([
                    $stt_counter++, $vp['ma_hoc_sinh'] ?? 'KXD',
                    $vp['ho_ten'] ?? ($student_names[$vp['hoc_sinh_id']] ?? 'Không rõ'),
                    date('d/m/Y', strtotime($vp['ngay_vi_pham'])),
                    $vp['ten_vi_pham'], $vp['ghi_chu'] ?? ''
                ], NULL, 'A' . $rowIndex);
                $sheet->getStyle('A'.$rowIndex.':F'.$rowIndex)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
                $rowIndex++;
            }
        } else {
            $sheet->mergeCells("A{$rowIndex}:F{$rowIndex}")->setCellValue('A'.$rowIndex, 'Không có vi phạm trong tuần.');
            $sheet->getStyle('A'.$rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $rowIndex++;
        }
        
        $last_data_row = $rowIndex - 1;
        $sheet->getStyle("A{$header_row_start}:F{$last_data_row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(52);
        $sheet->getColumnDimension('F')->setWidth(8);

        $footer_start_row = $rowIndex + 1;
        $sheet->mergeCells("E{$footer_start_row}:F{$footer_start_row}")->setCellValue("E{$footer_start_row}", 'Long Thành, ngày '.date('d').' tháng '.date('m').' năm '.date('Y'));
        $style_footer_date = $sheet->getStyle("E{$footer_start_row}");
        $style_footer_date->getFont()->setItalic(true);
        $style_footer_date->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $footer_start_row++;
        $sheet->mergeCells("E{$footer_start_row}:F{$footer_start_row}")->setCellValue("E{$footer_start_row}", 'NGƯỜI LẬP BẢNG');
        $style_footer_signer = $sheet->getStyle("E{$footer_start_row}");
        $style_footer_signer->getFont()->setBold(true);
        $style_footer_signer->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $footer_start_row += 3;
        $sheet->mergeCells("E{$footer_start_row}:F{$footer_start_row}")->setCellValue("E{$footer_start_row}", $admin_name);
        $style_footer_name = $sheet->getStyle("E{$footer_start_row}");
        $style_footer_name->getFont()->setBold(true);
        $style_footer_name->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);

        // Lưu file Excel vào thư mục tạm
        $writer = new Xlsx($spreadsheet);
        $excel_filename = "BaoCao_" . $sheet_name . ".xlsx";
        $excel_filepath = $temp_dir . '/' . $excel_filename;
        $writer->save($excel_filepath);
        $generated_files[] = $excel_filepath;
    }

    if (empty($generated_files)) die("Không có dữ liệu để tạo báo cáo.");

    // Nén thư mục tạm thành file ZIP
    $zip = new ZipArchive();
    $ten_tuan_ascii = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '_', $tuan_hoc['ten_tuan']));
    $zip_filename = "BaoCao_TatCaLop_{$ten_tuan_ascii}.zip";
    $zip_filepath = $temp_dir . '/' . $zip_filename;
    if ($zip->open($zip_filepath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        die("Lỗi: Không thể tạo file nén.");
    }
    foreach ($generated_files as $file) {
        $zip->addFile($file, basename($file));
    }
    $zip->close();

    // Gửi file ZIP cho người dùng tải về
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . basename($zip_filepath) . '"; filename*=UTF-8\'\'' . rawurlencode(basename($zip_filepath)));
    header('Content-Length: ' . filesize($zip_filepath));
    header('Connection: close');
    ob_clean(); // Xóa mọi "ký tự rác"
flush();    // Đẩy header
    readfile($zip_filepath);
    

    // Dọn dẹp file tạm
    array_map('unlink', $generated_files);
    unlink($zip_filepath);
    rmdir($temp_dir);
    exit();

} catch (Exception $e) {
    die('Lỗi khi tạo file nén: ' . $e->getMessage());
}
    exit();
}





if ($uri === '/thidua/xuat-bao-cao/chot-kxtd-lt2t') {
    set_time_limit(0);
    ini_set('memory_limit', '512M');

    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
        http_response_code(403);
        exit('Không có quyền truy cập.');
    }

    try {
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $nam_hoc_id = $_SESSION['current_nam_hoc_id'] ?? 1;
        $all_weeks = $db->query("SELECT id, ten_tuan FROM tuan_hoc WHERE nam_hoc_id = $nam_hoc_id ORDER BY ngay_bat_dau ASC")->fetchAll(PDO::FETCH_ASSOC);
        
        $calculator = new thiduaCalculator($db, $nam_hoc_id);

        $consecutive_bad = []; // Lưu số tuần vi phạm (hạng chót hoặc KXTĐ) liên tiếp của từng lớp
        $danh_sach_vi_pham_theo_lop = []; // Danh sách xuất ra (nhóm theo lớp)

        foreach ($all_weeks as $week) {
            $raw_data = $calculator->calculateRawDataForWeek((int)$week['id']);
            $ranked_data = $calculator->rankWeeklyData($raw_data);

            $max_rank_by_khoi = [];
            foreach ($ranked_data as $class_result) {
                if (!$class_result['kxtd']) {
                    $khoi = substr($class_result['lop'], 0, 2);
                    $rank = (int)$class_result['xep_hang'];
                    if (!isset($max_rank_by_khoi[$khoi]) || $rank > $max_rank_by_khoi[$khoi]) {
                        $max_rank_by_khoi[$khoi] = $rank;
                    }
                }
            }

            foreach ($ranked_data as $class_result) {
                $lop = $class_result['lop'];
                $khoi = substr($lop, 0, 2);

                $is_bad = false;
                $trang_thai = '';

                // Kiểm tra KXTĐ hoặc Hạng chót
                if ($class_result['kxtd']) {
                    $is_bad = true;
                    $trang_thai = 'KXTĐ';
                } else if ((int)$class_result['xep_hang'] === ($max_rank_by_khoi[$khoi] ?? -1)) {
                    $is_bad = true;
                    $trang_thai = 'Hạng chót';
                }

                if ($is_bad) {
                    if (!isset($consecutive_bad[$lop])) {
                        $consecutive_bad[$lop] = [];
                    }
                    $consecutive_bad[$lop][] = $week['ten_tuan'] . ' (' . $trang_thai . ')';
                } else {
                    // Nếu đã có >= 2 tuần vi phạm liên tiếp trước đó thì lưu lại thành 1 đợt
                    if (isset($consecutive_bad[$lop]) && count($consecutive_bad[$lop]) >= 2) {
                        if (!isset($danh_sach_vi_pham_theo_lop[$lop])) {
                            $danh_sach_vi_pham_theo_lop[$lop] = ['khoi' => $khoi, 'cac_dot' => []];
                        }
                        $danh_sach_vi_pham_theo_lop[$lop]['cac_dot'][] = [
                            'chuoi_tuan' => implode(', ', $consecutive_bad[$lop]),
                            'so_tuan' => count($consecutive_bad[$lop])
                        ];
                    }
                    $consecutive_bad[$lop] = []; // Reset chuỗi
                }
            }
        }

        // Kiểm tra nốt những lớp đang vi phạm liên tiếp đến tuần cuối cùng
        foreach ($consecutive_bad as $lop => $chuoi_tuan) {
            if (count($chuoi_tuan) >= 2) {
                $khoi = substr($lop, 0, 2);
                if (!isset($danh_sach_vi_pham_theo_lop[$lop])) {
                    $danh_sach_vi_pham_theo_lop[$lop] = ['khoi' => $khoi, 'cac_dot' => []];
                }
                $danh_sach_vi_pham_theo_lop[$lop]['cac_dot'][] = [
                    'chuoi_tuan' => implode(', ', $chuoi_tuan),
                    'so_tuan' => count($chuoi_tuan)
                ];
            }
        }

        // Tạo file Excel
        require_once __DIR__ . '/../../vendor/autoload.php';
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        
        // Sheet 1: Danh sách vi phạm liên tiếp
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Vi Phạm Liên Tiếp');
        $sheet1->setCellValue('A1', 'DANH SÁCH LỚP HẠNG CHÓT HOẶC KXTĐ TỪ 2 TUẦN LIÊN TIẾP TRỞ LÊN');
        $sheet1->mergeCells('A1:E1');
        $sheet1->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        
        $sheet1->setCellValue('A3', 'STT');
        $sheet1->setCellValue('B3', 'Lớp');
        $sheet1->setCellValue('C3', 'Khối');
        $sheet1->setCellValue('D3', 'Tổng số đợt');
        $sheet1->setCellValue('E3', 'Chi tiết các đợt (>= 2 tuần liên tiếp)');
        $sheet1->getStyle('A3:E3')->getFont()->setBold(true);
        $sheet1->getStyle('A3:E3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFEFEFEF');
        
        $row_idx = 4;
        $stt = 1;
        foreach ($danh_sach_vi_pham_theo_lop as $lop => $data) {
            $tong_so_dot = count($data['cac_dot']);
            $chi_tiet_arr = [];
            foreach ($data['cac_dot'] as $idx => $dot) {
                $chi_tiet_arr[] = "Đợt " . ($idx + 1) . " (" . $dot['so_tuan'] . " tuần): " . $dot['chuoi_tuan'];
            }
            $chi_tiet_str = implode("\n", $chi_tiet_arr);

            $sheet1->setCellValue('A' . $row_idx, $stt++);
            $sheet1->setCellValue('B' . $row_idx, $lop);
            $sheet1->setCellValue('C' . $row_idx, $data['khoi']);
            $sheet1->setCellValue('D' . $row_idx, $tong_so_dot);
            $sheet1->setCellValue('E' . $row_idx, $chi_tiet_str);
            $sheet1->getStyle('E' . $row_idx)->getAlignment()->setWrapText(true);
            $row_idx++;
        }
        foreach (range('A', 'D') as $col) {
            $sheet1->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet1->getColumnDimension('E')->setWidth(80);

        $spreadsheet->setActiveSheetIndex(0);
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="BC_Chot_KXTD_' . date('Ymd_His') . '.xlsx"');
        header('Cache-Control: max-age=0');
        ob_clean(); // Xóa buffer
        $writer->save('php://output');
        exit();

    } catch (Exception $e) {
        die('Lỗi khi phân tích dữ liệu: ' . $e->getMessage());
    }
}
