<?php
// File: src/controllers/xuat_so_nhat_ky_zip.php

set_time_limit(0);
ini_set('memory_limit', '512M');

require_once __DIR__ . '/../../config/bootstrap.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}

// GIẢI PHÓNG KHÓA SESSION ĐỂ TRÁNH LAG HỆ THỐNG KHI ĐANG XUẤT FILE NẶNG
session_write_close();

require_once __DIR__ . '/../../config/database.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

function to_unsigned_string($str) {
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

function sanitize_sheet_title($title) {
    $title = preg_replace('/[\\[\\]\/*?:]/', '', $title);
    return mb_substr($title, 0, 31);
}

try {
    $db = get_db_connection();

    $current_nam_hoc = $_SESSION['current_nam_hoc_id'] ?? 1;

    $stmt_weeks = $db->prepare("SELECT id, ten_tuan, ngay_bat_dau FROM raw_tuan_hoc WHERE nam_hoc_id = ? ORDER BY ngay_bat_dau ASC");
    $stmt_weeks->execute([$current_nam_hoc]);
    $weeks = $stmt_weeks->fetchAll(PDO::FETCH_ASSOC);
    if (empty($weeks)) {
        die('Chưa có tuần học nào để xuất.');
    }

    $stmt_classes = $db->prepare("SELECT id, ten_lop FROM raw_lop_hoc WHERE nam_hoc_id = ? ORDER BY CAST(SUBSTR(ten_lop, 1, 2) AS INTEGER) ASC, SUBSTR(ten_lop, 3, 1) ASC, CAST(SUBSTR(ten_lop, 4) AS INTEGER) ASC");
    $stmt_classes->execute([$current_nam_hoc]);
    $classes = $stmt_classes->fetchAll(PDO::FETCH_ASSOC);
    if (empty($classes)) {
        die('Chưa có lớp học nào để xuất.');
    }

    $details_raw = $db->query("
        SELECT
            snk.id AS nhat_ky_id,
            snk.tuan_hoc_id,
            snk.lop_hoc_id,
            snk.trang_thai,
            snkc.loai_so,
            snkc.so_tiet_tot,
            snkc.so_tiet_kha,
            snkc.so_tiet_tb,
            snkc.so_tiet_yeu
        FROM so_nhat_ky_online snk
        LEFT JOIN so_nhat_ky_chi_tiet snkc ON snk.id = snkc.nhat_ky_id
    ")->fetchAll(PDO::FETCH_ASSOC);

    $journals = [];
    $journal_index = [];

    foreach ($details_raw as $row) {
        $lop_id = (int)$row['lop_hoc_id'];
        $tuan_id = (int)$row['tuan_hoc_id'];
        $nhat_ky_id = (int)$row['nhat_ky_id'];

        if (!isset($journals[$lop_id][$tuan_id])) {
            $journals[$lop_id][$tuan_id] = [
                'id' => $nhat_ky_id,
                'trang_thai' => $row['trang_thai'] ?? null,
                'details' => [],
                'proofs' => ['sdb_ck' => 0, 'sdb_nk' => 0, 'sdb_tt' => 0, 'khac' => 0]
            ];
            $journal_index[$nhat_ky_id] = ['lop_id' => $lop_id, 'tuan_id' => $tuan_id];
        }

        if (!empty($row['loai_so'])) {
            $journals[$lop_id][$tuan_id]['details'][$row['loai_so']] = $row;
        }
    }

    $proofs_raw = $db->query("
        SELECT
            snk.id AS nhat_ky_id,
            MAX(CASE WHEN snkm.loai_minh_chung = 'sdb_ck' THEN 1 ELSE 0 END) AS has_ck,
            MAX(CASE WHEN snkm.loai_minh_chung = 'sdb_nk' THEN 1 ELSE 0 END) AS has_nk,
            MAX(CASE WHEN snkm.loai_minh_chung = 'sdb_tt' THEN 1 ELSE 0 END) AS has_tt,
            MAX(CASE WHEN snkm.loai_minh_chung IN ('khac', 'minh_chung_khac', 'sdb_tt') THEN 1 ELSE 0 END) AS has_other
        FROM so_nhat_ky_online snk
        LEFT JOIN so_nhat_ky_minh_chung snkm ON snk.id = snkm.nhat_ky_id
        GROUP BY snk.id
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($proofs_raw as $row) {
        $nhat_ky_id = (int)$row['nhat_ky_id'];
        if (!isset($journal_index[$nhat_ky_id])) {
            continue;
        }
        $lop_id = $journal_index[$nhat_ky_id]['lop_id'];
        $tuan_id = $journal_index[$nhat_ky_id]['tuan_id'];
        $journals[$lop_id][$tuan_id]['proofs'] = [
            'sdb_ck' => (int)($row['has_ck'] ?? 0),
            'sdb_nk' => (int)($row['has_nk'] ?? 0),
            'sdb_tt' => (int)($row['has_tt'] ?? 0),
            'khac' => (int)($row['has_other'] ?? 0)
        ];
    }

    $zip_filename = 'SoNhatKy_TatCaLop_' . date('Ymd_His') . '.zip';
    $zip_filepath = sys_get_temp_dir() . '/' . $zip_filename;
    $zip = new ZipArchive();
    if ($zip->open($zip_filepath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        die('Lỗi: Không thể tạo file nén.');
    }

    $headers = [
        'Tuần học',
        'CK - Tốt', 'CK - Khá', 'CK - TB', 'CK - Yếu', 'CK - Nộp',
        'NK - Tốt', 'NK - Khá', 'NK - TB', 'NK - Yếu', 'NK - Nộp',
        'TT - Tốt', 'TT - Khá', 'TT - TB', 'TT - Yếu', 'TT - Nộp',
        'Nộp Sổ nhật kỳ',
        'Minh chứng khác',
        'Đã duyệt'
    ];
    $column_widths = [
        20,
        9, 9, 9, 9, 10,
        9, 9, 9, 9, 10,
        9, 9, 9, 9, 10,
        16,
        16,
        10
    ];

    foreach ($classes as $class) {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Times New Roman')->setSize(11);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(sanitize_sheet_title($class['ten_lop']));

        $sheet->fromArray($headers, null, 'A1');
        $last_column = Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle("A1:{$last_column}1")->getFont()->setBold(true);
        $sheet->getStyle("A1:{$last_column}1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);

        foreach ($column_widths as $index => $width) {
            $col_letter = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->getColumnDimension($col_letter)->setWidth($width);
        }

        $row_index = 2;
        $lop_id = (int)$class['id'];

        foreach ($weeks as $week) {
            $tuan_id = (int)$week['id'];
            $journal = $journals[$lop_id][$tuan_id] ?? null;
            $details = $journal['details'] ?? [];
            $proofs = $journal['proofs'] ?? ['sdb_ck' => 0, 'sdb_nk' => 0, 'sdb_tt' => 0, 'khac' => 0];
            $has_journal = !empty($journal);

            $ck = $details['sdb_ck'] ?? [];
            $nk = $details['sdb_nk'] ?? [];
            $tt = $details['sdb_tt'] ?? [];

            $submitted = $has_journal && in_array($journal['trang_thai'], ['da_gui', 'da_duyet'], true) ? 'X' : '';
            $approved = $has_journal && ($journal['trang_thai'] === 'da_duyet') ? 'X' : '';

            $row = [
                $week['ten_tuan'],
                $has_journal ? (int)($ck['so_tiet_tot'] ?? 0) : '',
                $has_journal ? (int)($ck['so_tiet_kha'] ?? 0) : '',
                $has_journal ? (int)($ck['so_tiet_tb'] ?? 0) : '',
                $has_journal ? (int)($ck['so_tiet_yeu'] ?? 0) : '',
                $has_journal && !empty($proofs['sdb_ck']) ? 'X' : '',
                $has_journal ? (int)($nk['so_tiet_tot'] ?? 0) : '',
                $has_journal ? (int)($nk['so_tiet_kha'] ?? 0) : '',
                $has_journal ? (int)($nk['so_tiet_tb'] ?? 0) : '',
                $has_journal ? (int)($nk['so_tiet_yeu'] ?? 0) : '',
                $has_journal && !empty($proofs['sdb_nk']) ? 'X' : '',
                $has_journal ? (int)($tt['so_tiet_tot'] ?? 0) : '',
                $has_journal ? (int)($tt['so_tiet_kha'] ?? 0) : '',
                $has_journal ? (int)($tt['so_tiet_tb'] ?? 0) : '',
                $has_journal ? (int)($tt['so_tiet_yeu'] ?? 0) : '',
                $has_journal && !empty($proofs['sdb_tt']) ? 'X' : '',
                $submitted,
                $has_journal && !empty($proofs['khac']) ? 'X' : '',
                $approved
            ];

            $sheet->fromArray($row, null, 'A' . $row_index);
            $row_index++;
        }

        $last_row = $row_index - 1;
        if ($last_row >= 2) {
            $sheet->getStyle("A1:{$last_column}{$last_row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $sheet->getStyle("A1:{$last_column}{$last_row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            $sheet->getStyle("B1:F1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9EAF7');
            $sheet->getStyle("G1:K1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEADCF7');
            $sheet->getStyle("L1:P1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFCE8D5');
            $sheet->getStyle("A1:A1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE3F4E7');
            $sheet->getStyle("Q1:S1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE3F4E7');

            $sheet->getStyle("B2:F{$last_row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE6F2FF');
            $sheet->getStyle("G2:K{$last_row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF3E8FF');
            $sheet->getStyle("L2:P{$last_row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFF2E0');

            for ($row = 2; $row <= $last_row; $row++) {
                if ($row % 2 === 0) {
                    $sheet->getStyle("A{$row}:A{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE8F5E9');
                    $sheet->getStyle("Q{$row}:S{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE8F5E9');
                }
            }

            $sheet->getStyle("F1:F{$last_row}")->getBorders()->getRight()->setBorderStyle(Border::BORDER_MEDIUM);
            $sheet->getStyle("K1:K{$last_row}")->getBorders()->getRight()->setBorderStyle(Border::BORDER_MEDIUM);
            $sheet->getStyle("P1:P{$last_row}")->getBorders()->getRight()->setBorderStyle(Border::BORDER_MEDIUM);
        }

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $excel_content = ob_get_clean();

        $excel_filename = 'SoNhatKy_' . to_unsigned_string($class['ten_lop']) . '.xlsx';
        $zip->addFromString($excel_filename, $excel_content);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
    }

    $zip->close();

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . basename($zip_filepath) . '"');
    header('Content-Length: ' . filesize($zip_filepath));
    header('Connection: close');
    if (ob_get_length()) {
        ob_clean();
    }
    flush();
    readfile($zip_filepath);
    unlink($zip_filepath);
    exit();

} catch (Exception $e) {
    die('Lỗi khi tạo file nén: ' . $e->getMessage());
}
