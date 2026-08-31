<?php
// File: src/controllers/xuat_mau_diem_thi.php
ini_set('display_errors', 1); // Bật hiển thị lỗi (nếu cần gỡ lỗi)
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) { die('Lỗi xác thực.'); }

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Protection;

$ky_thi_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$ky_thi_id) { die('Thiếu ID Kỳ thi.'); }

// Định nghĩa các cột điểm và tiêu đề tương ứng (GIỐNG HỆT TRONG CONTROLLER CHÍNH)
$diem_columns_map = [
    'diem_toan' => 'Toán', 'diem_van' => 'Văn', 'diem_ly' => 'Lý',
    'diem_hoa' => 'Hóa', 'diem_sinh' => 'Sinh', 'diem_su' => 'Sử',
    'diem_dia' => 'Địa', 'diem_gdktpl' => 'GDKTPL', 'diem_ngoai_ngu' => 'N.Ngữ',
    'diem_cn_nn' => 'CN-NN', 'dtb_mon' => 'ĐTB Môn', 'diem_xt_tn' => 'Điểm XT',
    'ket_qua' => 'Kết Quả'
];
$diem_keys = array_keys($diem_columns_map);

try {
    $db = get_db_connection();
    // 1. Lấy tên kỳ thi
    $stmt_ky_thi = $db->prepare("SELECT ten_ky_thi FROM ky_thi WHERE id = ?");
    $stmt_ky_thi->execute([$ky_thi_id]);
    $ten_ky_thi = $stmt_ky_thi->fetchColumn();

    // 2. Lấy danh sách học sinh và điểm (nếu có)
    $sql_ds = "
        SELECT
            kths.id as ky_thi_hoc_sinh_id,
            hs.ma_moet, hs.ma_hoc_sinh, kths.so_bao_danh,
            hs.ho_dem, hs.ten, hs.ngay_sinh, lh.ten_lop";
    // Thêm các cột điểm vào SELECT
    foreach ($diem_keys as $col) { $sql_ds .= ", ktdt.$col"; }
    $sql_ds .= "
        FROM ky_thi_hoc_sinh kths
        JOIN hoc_sinh hs ON kths.hoc_sinh_id = hs.id
        JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id
        LEFT JOIN ky_thi_diem_thi ktdt ON kths.id = ktdt.ky_thi_hoc_sinh_id
        WHERE kths.ky_thi_id = ?
        ORDER BY kths.so_bao_danh, lh.ten_lop, hs.ten , hs.ho_dem 
    ";
    $stmt_ds = $db->prepare($sql_ds);
    $stmt_ds->execute([$ky_thi_id]);
    $ds_diem_thi = $stmt_ds->fetchAll();

    // 3. Tạo file Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Bang Diem Thi');

    // 4. Đặt tiêu đề (Header)
    $headers_info = ['STT', 'Mã MOET', 'Số CCCD', 'SBD', 'Họ và Tên', 'Ngày Sinh', 'Lớp'];
    $headers_diem = array_values($diem_columns_map);
    $all_headers = array_merge($headers_info, $headers_diem);
    $sheet->fromArray($all_headers, NULL, 'A1');
    $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->getFont()->setBold(true);
    $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');

    // 5. Đổ dữ liệu
    $row_index = 2;
    foreach ($ds_diem_thi as $index => $hs) {
        $sheet->getCell('A' . $row_index)->setValue($index + 1);
        $sheet->getCell('B' . $row_index)->setValue($hs['ma_moet']);
        $sheet->getCell('C' . $row_index)->setValue($hs['ma_hoc_sinh']); // CCCD là cột C
        $sheet->getCell('D' . $row_index)->setValue($hs['so_bao_danh']);
        $sheet->getCell('E' . $row_index)->setValue($hs['ho_dem'] . ' ' . $hs['ten']);
        $sheet->getCell('F' . $row_index)->setValue($hs['ngay_sinh']);
        $sheet->getCell('G' . $row_index)->setValue($hs['ten_lop']);

        // Đổ điểm
        $col_index = 'H'; // Bắt đầu từ cột H
        foreach ($diem_keys as $col_name) {
            $sheet->getCell($col_index . $row_index)->setValue($hs[$col_name]);
            // Định dạng cột điểm là Number (cho phép thập phân) ngoại trừ cột Kết Quả
            if ($col_name !== 'ket_qua') {
                $sheet->getStyle($col_index . $row_index)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);
            }
            $col_index++;
        }
        $row_index++;
    }

    // 6. Định dạng và Bảo vệ
    // Tự động giãn cột
    foreach (range('A', $sheet->getHighestColumn()) as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    // Tô màu vàng các cột điểm để nhập
    $first_score_col = 'H';
    $last_score_col = $sheet->getHighestColumn();
    $sheet->getStyle($first_score_col . '1:' . $last_score_col . $row_index)->getFill()
          ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00'); // Màu vàng

    // Bật bảo vệ sheet
    $sheet->getProtection()->setSheet(true);
    // Cho phép chỉnh sửa các ô điểm (từ H2 đến cuối)
    $sheet->getStyle($first_score_col . '2:' . $last_score_col . ($row_index - 1))->getProtection()->setLocked(Protection::PROTECTION_UNPROTECTED);

    // 7. Xuất file
    $clean_exam_name = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/u', 'a', $ten_ky_thi);
    $clean_exam_name = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/u', 'e', $clean_exam_name);
    $clean_exam_name = preg_replace('/(ì|í|ị|ỉ|ĩ)/u', 'i', $clean_exam_name);
    $clean_exam_name = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/u', 'o', $clean_exam_name);
    $clean_exam_name = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/u', 'u', $clean_exam_name);
    $clean_exam_name = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/u', 'y', $clean_exam_name);
    $clean_exam_name = preg_replace('/(đ)/u', 'd', $clean_exam_name);
    $clean_exam_name = preg_replace('/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/u', 'A', $clean_exam_name);
    $clean_exam_name = preg_replace('/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/u', 'E', $clean_exam_name);
    $clean_exam_name = preg_replace('/(Ì|Í|Ị|Ỉ|Ĩ)/u', 'I', $clean_exam_name);
    $clean_exam_name = preg_replace('/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/u', 'O', $clean_exam_name);
    $clean_exam_name = preg_replace('/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/u', 'U', $clean_exam_name);
    $clean_exam_name = preg_replace('/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/u', 'Y', $clean_exam_name);
    $clean_exam_name = preg_replace('/(Đ)/u', 'D', $clean_exam_name);
    $clean_exam_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $clean_exam_name);
    $clean_exam_name = trim(preg_replace('/_+/', '_', $clean_exam_name), '_');

    $ascii_filename = "Mau_Nhap_Diem_{$clean_exam_name}.xlsx";
    $utf8_filename = "Mau_Nhap_Diem_" . str_replace([' ', '/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $ten_ky_thi) . ".xlsx";

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $ascii_filename . '"; filename*=UTF-8\'\'' . rawurlencode($utf8_filename));
    header('Cache-Control: max-age=0');
    if (ob_get_length()) {
        ob_clean();
    }
    flush();
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();

} catch (Exception $e) {
    die("Lỗi khi tạo file Excel: " . $e->getMessage());
}
?>