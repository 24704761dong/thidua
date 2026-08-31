<?php
// File: src/controllers/xuat_mau_import_the.php (Đã nâng cấp)

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../lib/hoc_sinh_db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Cell\DataType; // <-- Thêm dòng này

try {
    $db = get_db_connection();
    $danh_sach_hoc_sinh = get_all_hoc_sinh($db);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('DuLieuTheHocSinh');

    // Thiết lập tiêu đề cột
    $sheet->setCellValue('A1', 'ma_hoc_sinh');
    $sheet->setCellValue('B1', 'ho_ten');
    $sheet->setCellValue('C1', 'ten_lop');
    $sheet->setCellValue('D1', 'anh_the');
    $sheet->setCellValue('E1', 'ma_moet');

    // In đậm và tô màu nền cho tiêu đề
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4285F4']]
    ];
    $sheet->getStyle('A1:E1')->applyFromArray($headerStyle);

    // --- NÂNG CẤP QUAN TRỌNG ---
    // Định dạng cột A (ma_hoc_sinh) là TEXT để chống lỗi Excel
    $sheet->getStyle('A')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
    // --- KẾT THÚC NÂNG CẤP ---

    // Điền dữ liệu học sinh
    $row = 2;
    foreach ($danh_sach_hoc_sinh as $hs) {
        // Sử dụng setCellValueExplicit để đảm bảo dữ liệu được ghi dưới dạng Text
        $sheet->setCellValueExplicit('A' . $row, $hs['ma_hoc_sinh'], DataType::TYPE_STRING);
        
        $sheet->setCellValue('B' . $row, $hs['ho_dem'] . ' ' . $hs['ten']);
        $sheet->setCellValue('C' . $row, $hs['ten_lop']);
        $sheet->setCellValue('D' . $row, $hs['anh_the']);
        $sheet->setCellValue('E' . $row, $hs['ma_moet']);
        $row++;
    }

    // Tự động điều chỉnh độ rộng cột
    foreach (range('A', 'E') as $columnID) {
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }
    
    // Khóa các cột không cần sửa
    $sheet->getProtection()->setSheet(true);
    $sheet->getStyle('D2:E' . ($row - 1))->getProtection()->setLocked(Protection::PROTECTION_UNPROTECTED);

    // Gửi file về cho người dùng
    $writer = new Xlsx($spreadsheet);
    $filename = 'Mau_Cap_Nhat_Thong_Tin_The_' . date('Y-m-d') . '.xlsx';

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    ob_clean(); // Xóa mọi "ký tự rác"
flush();    // Đẩy header
    $writer->save('php://output');
    exit();

} catch (Exception $e) {
    die('Lỗi khi tạo file Excel: ' . $e->getMessage());
}