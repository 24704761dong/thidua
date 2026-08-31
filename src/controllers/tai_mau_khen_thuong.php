<?php
// File: src/controllers/tai_mau_khen_thuong.php (ĐÃ CẬP NHẬT)
require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;

$spreadsheet = new Spreadsheet();

// --- SỬA LOGIC: Thay đổi tiêu đề cho Sheet Cá nhân ---
$sheet_cn = $spreadsheet->getActiveSheet();
$sheet_cn->setTitle('MauCaNhan');
$header_cn = ['Họ và tên', 'Lớp', 'Ngày khen thưởng', 'Tên khen thưởng', 'Số quyết định', 'Cấp khen thưởng', 'Ghi chú'];
$sheet_cn->fromArray($header_cn, NULL, 'A1');
// In đậm header
$sheet_cn->getStyle('A1:G1')->getFont()->setBold(true);

// Giữ nguyên Sheet Tập thể
$sheet_tt = $spreadsheet->createSheet();
$sheet_tt->setTitle('MauTapThe');
$header_tt = ['Tên lớp hoặc tập thể', 'Ngày khen thưởng', 'Tên khen thưởng', 'Số quyết định', 'Cấp khen thưởng', 'Ghi chú'];
$sheet_tt->fromArray($header_tt, NULL, 'A1');
// In đậm header
$sheet_tt->getStyle('A1:F1')->getFont()->setBold(true);


$spreadsheet->setActiveSheetIndex(0); // Quay về sheet đầu tiên
$filename = "mau-import-khen-thuong-" . date('Y-m-d') . ".xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');
ob_clean(); // Xóa mọi "ký tự rác"
flush();    // Đẩy header

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();