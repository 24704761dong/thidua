<?php
// File: src/controllers/tai_mau_import_vi_pham.php
$filePath = __DIR__ . '/../../public/templates/mau_import_vi_pham.xlsx';

if (file_exists($filePath)) {
    // Đặt cookie để JS biết khi nào file bắt đầu tải về
    setcookie("fileDownloadToken", "success", ['expires' => time() + 20, 'path' => '/', 'samesite' => 'Strict']);
    
    header('Content-Description: File Transfer');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="Mau_Import_Vi_Pham.xlsx"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filePath));
ob_clean(); // Xóa mọi "ký tự rác"

    flush();
    readfile($filePath);
    exit();
} else {
    die('Lỗi: Không tìm thấy file mẫu tại public/templates/mau_import_vi_pham.xlsx');
}