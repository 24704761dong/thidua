<?php
// File: src/controllers/admin_exam_export_template.php

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../lib/exam_permissions.php';
require_once __DIR__ . '/../lib/exam_subjects.php';

// Bảo mật
if (!isset($_SESSION['user_id']) || !can_current_user_manage_exams()) {
    die('Lỗi xác thực.');
}

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$ky_thi_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$import_type = filter_input(INPUT_GET, 'type', FILTER_UNSAFE_RAW);

if (!$ky_thi_id || !in_array($import_type, ['moet', 'sbd', 'subjects'], true)) {
    die('Thiếu ID Kỳ thi hoặc loại import.');
}

try {
    $db = get_db_connection();
    ensure_exam_subject_registration_schema($db);
    
    // 1. Lấy thông tin kỳ thi
    $stmt_ky_thi = $db->prepare("SELECT ten_ky_thi FROM ky_thi WHERE id = ?");
    $stmt_ky_thi->execute([$ky_thi_id]);
    $ten_ky_thi = $stmt_ky_thi->fetchColumn();
    
    // 2. Lấy danh sách học sinh
    $sql_ds_hoc_sinh = "
        SELECT 
            hs.ma_hoc_sinh, hs.ho_dem, hs.ten, lh.ten_lop, hs.ngay_sinh,
            hs.ma_moet, kths.so_bao_danh, kths.dang_ky_mon_thi
        FROM ky_thi_hoc_sinh kths
        JOIN hoc_sinh hs ON kths.hoc_sinh_id = hs.id
        JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id
        WHERE kths.ky_thi_id = ?
        ORDER BY lh.ten_lop, hs.ten , hs.ho_dem 
    ";
    $stmt_ds_hoc_sinh = $db->prepare($sql_ds_hoc_sinh);
    $stmt_ds_hoc_sinh->execute([$ky_thi_id]);
    $ds_hoc_sinh = $stmt_ds_hoc_sinh->fetchAll();

    // 3. Tạo file Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Danh sach Hoc sinh');

    if ($import_type === 'subjects') {
        // 4A. Header mẫu đăng ký tổ hợp môn
        $headers = exam_subject_template_headers();
        foreach ($headers as $col => $title) {
            $sheet->getCell($col . '1')->setValue($title);
        }
        $sheet->getStyle('A1:V1')->getFont()->setBold(true);
        $sheet->getStyle('A1:V1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');

        // 5A. Đổ dữ liệu + đánh dấu x theo dữ liệu hiện có
        $subject_column_map = exam_subject_column_map();
        $row_index = 2;
        foreach ($ds_hoc_sinh as $hs) {
            $sheet->getCell('A' . $row_index)->setValue($hs['ma_hoc_sinh']);
            $sheet->getCell('B' . $row_index)->setValue($hs['ho_dem']);
            $sheet->getCell('C' . $row_index)->setValue($hs['ten']);
            $sheet->getCell('D' . $row_index)->setValue($hs['ten_lop']);
            $sheet->getCell('E' . $row_index)->setValue($hs['ngay_sinh']);

            $registered_subjects = exam_decode_subject_registration($hs['dang_ky_mon_thi'] ?? '');
            $registered_set = array_fill_keys($registered_subjects, true);

            foreach ($subject_column_map as $subject_code => $column) {
                $sheet->getCell($column . $row_index)->setValue(isset($registered_set[$subject_code]) ? 'x' : '');
            }

            $row_index++;
        }

        // 6A. Đánh dấu vùng cần chỉnh sửa: toàn bộ cột môn thi
        $last_data_row = max(2, $row_index - 1);
        $sheet->getStyle('F1:V' . $last_data_row)->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00');

        foreach (range('A', 'V') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    } else {
        // 4B. Header mẫu import MOET/SBD
        $headers = [
            'ma_hoc_sinh (CCCD)', // Cột A
            'ho_dem',             // Cột B
            'ten',                // Cột C
            'ten_lop',            // Cột D
            'ngay_sinh (YYYY-MM-DD)', // Cột E
            'ma_moet',            // Cột F
            'so_bao_danh'         // Cột G
        ];
        $sheet->fromArray($headers, NULL, 'A1');
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getStyle('A1:G1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');

        // 5B. Đổ dữ liệu
        $row_index = 2;
        foreach ($ds_hoc_sinh as $hs) {
            $sheet->getCell('A' . $row_index)->setValue($hs['ma_hoc_sinh']);
            $sheet->getCell('B' . $row_index)->setValue($hs['ho_dem']);
            $sheet->getCell('C' . $row_index)->setValue($hs['ten']);
            $sheet->getCell('D' . $row_index)->setValue($hs['ten_lop']);
            $sheet->getCell('E' . $row_index)->setValue($hs['ngay_sinh']);
            $sheet->getCell('F' . $row_index)->setValue($hs['ma_moet']);
            $sheet->getCell('G' . $row_index)->setValue($hs['so_bao_danh']);
            $row_index++;
        }

        // 6B. Đánh dấu cột cần nhập
        $target_column = ($import_type === 'moet') ? 'F' : 'G';
        $last_data_row = max(2, $row_index - 1);
        $sheet->getStyle($target_column . '1:' . $target_column . $last_data_row)->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00');

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    // 7. Cấu hình file tải về sạch, không lỗi ký tự trên mọi trình duyệt
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

    $ascii_filename = "Mau_Import_{$import_type}_{$clean_exam_name}.xlsx";
    $utf8_filename = "Mau_Import_{$import_type}_" . str_replace([' ', '/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $ten_ky_thi) . ".xlsx";

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $ascii_filename . '"; filename*=UTF-8\'\'' . rawurlencode($utf8_filename));
    header('Cache-Control: max-age=0');
    if (ob_get_length()) {
        ob_clean(); // Xóa mọi ký tự rác
    }
    flush();
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();

} catch (Exception $e) {
    die("Lỗi khi tạo file Excel: " . $e->getMessage());
}
?>