<?php
// File: src/controllers/api_exam_import_data.php

ini_set('display_errors', '0');
error_reporting(0);
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', '300');
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function send_json_response(array $data, int $status_code = 200): void
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
require_once __DIR__ . '/../lib/exam_subjects.php';

// Bảo mật
if (!isset($_SESSION['user_id']) || !can_current_user_manage_exams()) {
    send_json_response(['success' => false, 'message' => 'Lỗi xác thực quyền truy cập.'], 403);
}

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$ky_thi_id = (int)($_POST['ky_thi_id'] ?? 0);
$import_type = trim((string)($_POST['import_type'] ?? '')); // 'moet', 'sbd' hoặc 'subjects'

if (!in_array($import_type, ['moet', 'sbd', 'subjects'], true)) {
    send_json_response(['success' => false, 'message' => 'Loại import không hợp lệ: ' . htmlspecialchars($import_type)], 400);
}

if (empty($_FILES['importFile']) || (int)($_FILES['importFile']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !$ky_thi_id) {
    $upload_err = (int)($_FILES['importFile']['error'] ?? UPLOAD_ERR_NO_FILE);
    $err_msg = 'Thiếu file tải lên hoặc ID kỳ thi.';
    if ($upload_err === UPLOAD_ERR_INI_SIZE || $upload_err === UPLOAD_ERR_FORM_SIZE) {
        $err_msg = 'Dung lượng file vượt quá giới hạn cho phép của máy chủ.';
    } elseif ($upload_err === UPLOAD_ERR_NO_FILE) {
        $err_msg = 'Vui lòng chọn file Excel để tải lên.';
    }
    send_json_response(['success' => false, 'message' => $err_msg], 400);
}

$file_path = $_FILES['importFile']['tmp_name'];
$db = get_db_connection();
ensure_exam_subject_registration_schema($db);

$updated_count = 0;
$processed_count = 0;
$errors = [];

try {
    // Tối ưu hóa bộ nhớ: chỉ đọc dữ liệu thô (không nạp style, font, format)
    $reader = IOFactory::createReaderForFile($file_path);
    $reader->setReadDataOnly(true);
    $spreadsheet = $reader->load($file_path);
    $sheet = $spreadsheet->getActiveSheet();
    $highest_row = $sheet->getHighestRow();

    if ($highest_row < 2) {
        throw new Exception('File Excel không có dữ liệu (chỉ có dòng tiêu đề hoặc rỗng).');
    }

    $db->beginTransaction();

    if ($import_type === 'moet') {
        // Cập nhật Mã MOET vào bảng `hoc_sinh`
        $stmt_update = $db->prepare("UPDATE hoc_sinh SET ma_moet = ? WHERE ma_hoc_sinh = ?");
        for ($row = 2; $row <= $highest_row; $row++) {
            $ma_hoc_sinh = trim((string)$sheet->getCell('A' . $row)->getValue());
            $ma_moet = trim((string)$sheet->getCell('F' . $row)->getValue());

            if (!empty($ma_hoc_sinh)) {
                $stmt_update->execute([$ma_moet, $ma_hoc_sinh]);
                if ($stmt_update->rowCount() > 0) {
                    $updated_count++;
                }
            }
        }
    } elseif ($import_type === 'sbd') {
        // Cập nhật SBD vào bảng `ky_thi_hoc_sinh`
        $stmt_update = $db->prepare(
            "UPDATE ky_thi_hoc_sinh kths
             JOIN hoc_sinh hs ON kths.hoc_sinh_id = hs.id
             SET kths.so_bao_danh = ?
             WHERE kths.ky_thi_id = ? AND hs.ma_hoc_sinh = ?"
        );

        for ($row = 2; $row <= $highest_row; $row++) {
            $ma_hoc_sinh = trim((string)$sheet->getCell('A' . $row)->getValue());
            $so_bao_danh = trim((string)$sheet->getCell('G' . $row)->getValue());

            if (!empty($ma_hoc_sinh)) {
                $stmt_update->execute([$so_bao_danh, $ky_thi_id, $ma_hoc_sinh]);
                if ($stmt_update->rowCount() > 0) {
                    $updated_count++;
                }
            }
        }
    } elseif ($import_type === 'subjects') {
        // Cập nhật tổ hợp môn thi vào bảng `ky_thi_hoc_sinh`
        $subject_columns = exam_subject_column_map();
        $all_subject_codes = array_keys($subject_columns);
        $optional_subject_codes = exam_optional_subject_codes();
        $optional_subject_set = array_fill_keys($optional_subject_codes, true);

        $stmt_update = $db->prepare(
            "UPDATE ky_thi_hoc_sinh kths
             JOIN hoc_sinh hs ON kths.hoc_sinh_id = hs.id
             SET kths.dang_ky_mon_thi = ?
             WHERE kths.ky_thi_id = ? AND hs.ma_hoc_sinh = ?"
        );

        $stmt_exists = $db->prepare(
            "SELECT kths.id
             FROM ky_thi_hoc_sinh kths
             JOIN hoc_sinh hs ON hs.id = kths.hoc_sinh_id
             WHERE kths.ky_thi_id = ? AND hs.ma_hoc_sinh = ?
             LIMIT 1"
        );

        for ($row = 2; $row <= $highest_row; $row++) {
            $ma_hoc_sinh = trim((string)$sheet->getCell('A' . $row)->getValue());
            if ($ma_hoc_sinh === '') {
                continue;
            }

            $processed_count++;
            $selected_subject_codes = [];
            $selected_optional = [];

            foreach ($all_subject_codes as $subject_code) {
                $column = $subject_columns[$subject_code] ?? '';
                if ($column === '') {
                    continue;
                }

                $cell_value = $sheet->getCell($column . $row)->getValue();
                if (exam_is_marked_cell($cell_value)) {
                    $selected_subject_codes[] = $subject_code;
                    if (isset($optional_subject_set[$subject_code])) {
                        $selected_optional[] = $subject_code;
                    }
                }
            }

            $validation_error = exam_validate_optional_subject_selection($selected_optional);
            if ($validation_error !== null) {
                $errors[] = "Dòng {$row} ({$ma_hoc_sinh}): {$validation_error}";
                continue;
            }

            $subject_registration_json = exam_encode_subject_registration($selected_subject_codes);
            $stmt_update->execute([$subject_registration_json, $ky_thi_id, $ma_hoc_sinh]);

            if ($stmt_update->rowCount() > 0) {
                $updated_count++;
            } else {
                $stmt_exists->execute([$ky_thi_id, $ma_hoc_sinh]);
                if (!$stmt_exists->fetchColumn()) {
                    $errors[] = "Dòng {$row} ({$ma_hoc_sinh}): Thí sinh chưa có trong danh sách kỳ thi.";
                } else {
                    $updated_count++;
                }
            }
        }
    }

    $db->commit();

    $message = "Import hoàn tất! Đã cập nhật {$updated_count} học sinh.";
    if ($import_type === 'subjects') {
        $message = "Import tổ hợp môn hoàn tất! Đã xử lý {$processed_count} dòng, cập nhật {$updated_count} học sinh.";
        if (!empty($errors)) {
            $message .= ' Có một số dòng chưa hợp lệ, vui lòng kiểm tra chi tiết bên dưới.';
        }
    }

    send_json_response([
        'success' => true,
        'message' => $message,
        'errors' => $errors
    ]);

} catch (\Throwable $e) {
    if ($db && $db->inTransaction()) {
        $db->rollBack();
    }
    send_json_response([
        'success' => false,
        'message' => 'Lỗi xử lý file Excel: ' . $e->getMessage()
    ], 500);
}