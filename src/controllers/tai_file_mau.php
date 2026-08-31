<?php
// FILE: src/controllers/tai_file_mau.php (ĐÃ NÂNG CẤP ĐỂ TỰ TẠO FILE)

// Nạp các thư viện cần thiết
require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

try {
    // 1. Khởi tạo một đối tượng Spreadsheet mới
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('MauNhapHocSinh');

    // 2. Định nghĩa các cột tiêu đề cho file mẫu
    $header = [
        'ma_hoc_sinh', 
        'ho_ten', 
        'ten_lop', 
        'nien_khoa', // Thêm cột niên khóa
        'ngay_sinh', 
        'gioi_tinh', 
        'chuc_vu', 
        'sdt',
        'gmail', // Thêm cột gmail
        'tinh_thanhpho',
        'xa_phuong',
        'ap_khupho',
        'dia_chi_chi_tiet'
    ];
    
    // 3. Ghi dòng tiêu đề vào hàng đầu tiên (A1)
    $sheet->fromArray($header, NULL, 'A1');

    // 4. Định dạng cho dòng tiêu đề (in đậm)
    $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->getFont()->setBold(true);

    // 5. Tự động căn chỉnh độ rộng các cột cho đẹp
    foreach (range('A', $sheet->getHighestColumn()) as $columnID) {
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }
    
    // 6. Thiết lập các header để trình duyệt tải file về
    $filename = "File_mau_import_moi_hoc_sinh.xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    ob_clean(); // Xóa mọi "ký tự rác"
flush();    // Đẩy header

    // 7. Tạo đối tượng Writer và xuất file
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();

} catch (Exception $e) {
    die("Có lỗi xảy ra khi tạo file Excel mẫu: " . $e->getMessage());
}