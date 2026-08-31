<?php
// File: src/controllers/api_diem_danh_nc_xu_ly_import.php
// PHIÊN BẢN HOÀN CHỈNH: Đã thêm 'code' để gửi về cho giao diện.

header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_FILES['attendance_file']) || !isset($_POST['tuan_id'])) {
    echo json_encode(['success' => false, 'message' => 'Thiếu file hoặc ID tuần.']);
    exit();
}

$tuan_id = $_POST['tuan_id'];
$file = $_FILES['attendance_file']['tmp_name'];

try {
    $db = get_db_connection();
    $stmt_tuan = $db->prepare("SELECT ngay_bat_dau, ngay_ket_thuc FROM tuan_hoc WHERE id = ?");
    $stmt_tuan->execute([$tuan_id]);
    $tuan = $stmt_tuan->fetch();
    if (!$tuan) throw new Exception("Tuần học không hợp lệ.");

    $tuan_start = new DateTime($tuan['ngay_bat_dau']);
    $tuan_end = new DateTime($tuan['ngay_ket_thuc']);
    $tuan_end->setTime(23, 59, 59);
    $year = $tuan_start->format('Y');

    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $highestRow = $sheet->getHighestRow();

    $processed_data = [];
    $errors = [];

    for ($row = 9; $row <= $highestRow; $row++) {
        $ho_ten = trim($sheet->getCell('C' . $row)->getValue());
        $lop = trim($sheet->getCell('D' . $row)->getValue());
        $noi_dung_vang_raw = trim($sheet->getCell('F' . $row)->getValue());

        if (empty($ho_ten) || empty($lop) || empty($noi_dung_vang_raw)) continue;

        $stmt_hs = $db->prepare("SELECT id, ma_hoc_sinh FROM hoc_sinh WHERE (CONCAT(ho_dem, ' ', ten)) = ? AND lop_hoc_id = (SELECT id FROM lop_hoc WHERE ten_lop = ?)");
        $stmt_hs->execute([$ho_ten, $lop]);
        $hoc_sinh = $stmt_hs->fetch();

        if (!$hoc_sinh) {
            $errors[] = "Không tìm thấy học sinh: {$ho_ten} - Lớp {$lop} (Dòng {$row})";
            continue;
        }

        $absences = [];
        $parts = explode(',', $noi_dung_vang_raw);

        foreach ($parts as $part) {
            $part = trim($part);

            if (preg_match('/(\d{1,2}\/\d{1,2})\s*\((S|C)(P|K)\)/i', $part, $matches_vang)) {
                $date_str = $matches_vang[1];
                $session = strtoupper($matches_vang[2]);
                $type = strtoupper($matches_vang[3]);
                $session_text = ($session === 'S') ? 'Sáng' : 'Chiều';
                $type_text = ($type === 'P') ? 'phép' : 'k.phép';
                $details = "{$session_text} {$type_text}";

                $full_date_str = $date_str . '/' . $year;
                $date = DateTime::createFromFormat('j/n/Y', $full_date_str);
                if (!$date) {
                    $errors[] = "Định dạng ngày không hợp lệ '{$date_str}' cho HS {$ho_ten} (Dòng {$row})";
                    continue;
                }
                $date->setTime(0, 0, 0);

                if ($date >= $tuan_start && $date <= $tuan_end) {
                    $absences[] = [
                        'date' => $date->format('Y-m-d'),
                        'session' => $session,
                        'type' => $type,
                        'details' => $details,
                        'code' => $session . $type // << DÒNG MỚI CHO VẮNG P/K
                    ];
                } else {
                    $errors[] = "Ngày {$date_str} không thuộc tuần học đang chọn cho HS {$ho_ten} (Dòng {$row})";
                }
            } else if (preg_match('/(\d{1,2}\/\d{1,2})\s*\((S|C)(\d+)\)/i', $part, $matches_bt)) {
                $date_str = $matches_bt[1];
                $session = strtoupper($matches_bt[2]);
                $tiet_str = $matches_bt[3];
                $tiet_array = str_split($tiet_str);
                $session_text = ($session === 'S') ? 'Sáng' : 'Chiều';
                $details = "{$session_text} bỏ tiết " . implode(', ', $tiet_array);

                $full_date_str = $date_str . '/' . $year;
                $date = DateTime::createFromFormat('j/n/Y', $full_date_str);
                if (!$date) {
                    $errors[] = "Định dạng ngày không hợp lệ '{$date_str}' cho HS {$ho_ten} (Dòng {$row})";
                    continue;
                }
                $date->setTime(0, 0, 0);

                if ($date >= $tuan_start && $date <= $tuan_end) {
                    $absences[] = [
                        'date' => $date->format('Y-m-d'),
                        'session' => $session,
                        'type' => 'BT',
                        'details' => $details,
                        'code' => $session . $tiet_str // << DÒNG MỚI CHO BỎ TIẾT
                    ];
                } else {
                    $errors[] = "Ngày {$date_str} không thuộc tuần học đang chọn cho HS {$ho_ten} (Dòng {$row})";
                }
            }
        }

        if (!empty($absences)) {
            $processed_data[] = [
                'hoc_sinh_id' => $hoc_sinh['id'],
                'ma_hs' => $hoc_sinh['ma_hoc_sinh'],
                'ho_ten' => $ho_ten,
                'lop' => $lop,
                'noi_dung_vang' => $absences
            ];
        }
    }

    $_SESSION['diem_danh_nc_data'] = [
        'tuan_id' => $tuan_id,
        'data' => $processed_data
    ];

    echo json_encode(['success' => true, 'data' => $processed_data, 'errors' => $errors]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}