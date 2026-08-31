<?php
// File: src/controllers/nhap_diem_thi_excel.php

ini_set('display_errors', '0');
error_reporting(0);
set_time_limit(300);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function send_json_score_response(array $data, int $status_code = 200): void
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    http_response_code($status_code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

require_once __DIR__ . '/../lib/exam_permissions.php';
if (!isset($_SESSION['user_id']) || !can_current_user_manage_exams()) {
    send_json_score_response(['success' => false, 'message' => 'Lỗi xác thực quyền truy cập.'], 403);
}

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$ky_thi_id = (int)($_POST['ky_thi_id'] ?? 0);
$file_info = $_FILES['importFile'] ?? $_FILES['excel_file'] ?? null;

if (empty($file_info) || (int)($file_info['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !$ky_thi_id) {
    send_json_score_response(['success' => false, 'message' => 'Vui lòng chọn file Excel và ID kỳ thi hợp lệ.'], 400);
}

$file_path = $file_info['tmp_name'];
$db = get_db_connection();
$updated_count = 0;
$skipped_rows = 0;
$errors = [];

// Map tên cột Excel sang tên cột CSDL
$excel_col_map = [
    'H' => 'diem_toan', 'I' => 'diem_van', 'J' => 'diem_ly', 'K' => 'diem_hoa',
    'L' => 'diem_sinh', 'M' => 'diem_su', 'N' => 'diem_dia', 'O' => 'diem_gdktpl',
    'P' => 'diem_ngoai_ngu', 'Q' => 'diem_cn_nn', 'R' => 'dtb_mon', 'S' => 'diem_xt_tn',
    'T' => 'ket_qua'
];
$score_cols_db = array_values($excel_col_map);

try {
    $spreadsheet = IOFactory::load($file_path);
    $sheet = $spreadsheet->getActiveSheet();
    $highest_row = $sheet->getHighestRow();

    if ($highest_row < 2) {
        throw new Exception('File không có dữ liệu (chỉ có dòng tiêu đề hoặc rỗng).');
    }

    $db->beginTransaction();

    // Chuẩn bị câu lệnh tìm ky_thi_hoc_sinh_id
    $stmt_find_kths = $db->prepare("
        SELECT kths.id 
        FROM ky_thi_hoc_sinh kths
        JOIN hoc_sinh hs ON kths.hoc_sinh_id = hs.id
        WHERE kths.ky_thi_id = ? AND hs.ma_hoc_sinh = ?
        LIMIT 1
    ");

    // Chuẩn bị câu lệnh INSERT ... ON DUPLICATE KEY UPDATE cho MySQL/MariaDB
    $cols_list = implode(', ', $score_cols_db);
    $placeholders = implode(', ', array_map(function($c) { return ":$c"; }, $score_cols_db));
    $duplicate_updates = implode(', ', array_map(function($c) { return "$c = VALUES($c)"; }, $score_cols_db));

    $sql_upsert = "
        INSERT INTO ky_thi_diem_thi (ky_thi_hoc_sinh_id, {$cols_list})
        VALUES (:ky_thi_hoc_sinh_id, {$placeholders})
        ON DUPLICATE KEY UPDATE
            {$duplicate_updates}
    ";
    $stmt_upsert = $db->prepare($sql_upsert);

    // Lặp qua từng dòng trong Excel (từ hàng 2)
    for ($row = 2; $row <= $highest_row; $row++) {
        $ma_hoc_sinh = trim((string)($sheet->getCell('C' . $row)->getFormattedValue() ?: $sheet->getCell('C' . $row)->getValue()));
        if (empty($ma_hoc_sinh)) {
            // Thử đọc ở cột A nếu cột C trống
            $ma_hoc_sinh = trim((string)($sheet->getCell('A' . $row)->getFormattedValue() ?: $sheet->getCell('A' . $row)->getValue()));
        }

        if (empty($ma_hoc_sinh)) {
            $skipped_rows++;
            continue;
        }

        $stmt_find_kths->execute([$ky_thi_id, $ma_hoc_sinh]);
        $kths_id = $stmt_find_kths->fetchColumn();

        if (!$kths_id) {
            $errors[] = "Dòng {$row}: Không tìm thấy thí sinh có mã CCCD '{$ma_hoc_sinh}' trong kỳ thi.";
            $skipped_rows++;
            continue;
        }

        $params = [':ky_thi_hoc_sinh_id' => (int)$kths_id];
        $has_data = false;

        foreach ($excel_col_map as $col_excel => $col_db) {
            $cell_value = $sheet->getCell($col_excel . $row)->getFormattedValue() ?: $sheet->getCell($col_excel . $row)->getValue();
            
            if ($cell_value === null || trim((string)$cell_value) === '') {
                $params[":$col_db"] = null;
            } elseif ($col_db !== 'ket_qua' && is_numeric(str_replace(',', '.', trim((string)$cell_value)))) {
                $score = round(floatval(str_replace(',', '.', trim((string)$cell_value))), 2);
                $params[":$col_db"] = ($score >= 0 && $score <= 10) ? $score : null;
                if ($params[":$col_db"] !== null) $has_data = true;
            } else {
                $params[":$col_db"] = trim((string)$cell_value);
                if (!empty($params[":$col_db"])) $has_data = true;
            }
        }

        if ($has_data) {
            $stmt_upsert->execute($params);
            $updated_count++;
        }
    }

    $db->commit();
    $message = "Import hoàn tất! Đã cập nhật điểm cho {$updated_count} học sinh.";
    if ($skipped_rows > 0) {
        $message .= " Bỏ qua {$skipped_rows} dòng.";
    }

    send_json_score_response([
        'success' => true,
        'message' => $message,
        'errors' => $errors
    ]);

} catch (\Throwable $e) {
    if ($db && $db->inTransaction()) {
        $db->rollBack();
    }
    send_json_score_response(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
}