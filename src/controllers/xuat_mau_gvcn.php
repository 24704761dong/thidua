<?php
// FILE: src/controllers/xuat_mau_gvcn.php (ĐÃ NÂNG CẤP ĐỂ TỰ TẠO FILE)

require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

try {
    // 1. Khởi tạo Spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('MauNhapGVCN');

    // 2. Định nghĩa các cột tiêu đề (Thêm gvcn_ngay_sinh)
    $header = [
        'ten_lop', 
        'gvcn_ten', 
        'gvcn_ma', 
        'gvcn_email',
        'gvcn_ngay_sinh'
    ];
    
    // 3. Ghi tiêu đề vào file
    $sheet->fromArray($header, NULL, 'A1');

    // 4. Định dạng tiêu đề
    $sheet->getStyle('A1:E1')->getFont()->setBold(true);

    // 5. Tự động giãn cột
    foreach (range('A', 'E') as $columnID) {
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }
    
    // 6. Xuất file
    $filename = "Mau_Nhap_GVCN_" . date('Ymd') . ".xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
ob_clean(); // Xóa mọi "ký tự rác"
flush();    // Đẩy header
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();

} catch (Exception $e) {
    die("Lỗi khi tạo file Excel mẫu: " . $e->getMessage());
}