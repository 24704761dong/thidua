<?php
// File: src/controllers/api_tre_hoc_xu_ly_import.php
// PHIÊN BẢN SỬA LỖI: Thêm logic ghi nhớ ID tuần vào session.

header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit();
}

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

if (!isset($_FILES['tardiness_file']) || !isset($_POST['tuan_id'])) {
    echo json_encode(['success' => false, 'message' => 'Thiếu file hoặc ID tuần.']); exit();
}

$tuan_id = $_POST['tuan_id'];
$file = $_FILES['tardiness_file']['tmp_name'];

try {
    // ===== BẮT ĐẦU PHẦN SỬA LỖI =====
    // Ghi nhớ ngay ID của tuần học vào session để file "hoan_tat" có thể sử dụng
    $_SESSION['latest_tuan_id_for_import'] = $tuan_id;
    // ===== KẾT THÚC PHẦN SỬA LỖI =====

    $db = get_db_connection();
    $stmt_tuan = $db->prepare("SELECT ngay_bat_dau, ngay_ket_thuc FROM tuan_hoc WHERE id = ?");
    $stmt_tuan->execute([$tuan_id]);
    $tuan = $stmt_tuan->fetch();
    if (!$tuan) throw new Exception("Tuần học không hợp lệ.");

    $tuan_start = (new DateTime($tuan['ngay_bat_dau']))->setTime(0, 0, 0);
    $tuan_end = (new DateTime($tuan['ngay_ket_thuc']))->setTime(23, 59, 59);

    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $highestRow = $sheet->getHighestRow();

    $processed_data = [];
    $errors = [];

    for ($row = 2; $row <= $highestRow; $row++) {
        $the_loai = trim($sheet->getCell('G' . $row)->getValue());
        
        if ($the_loai !== 'Đi trễ') continue;

        $lop = trim($sheet->getCell('B' . $row)->getValue());
        $ho_ten = trim($sheet->getCell('D' . $row)->getValue());
        $thoi_gian_raw = $sheet->getCell('F' . $row)->getValue();

        if (empty($ho_ten) || empty($lop) || empty($thoi_gian_raw)) continue;
        
        $date_obj = null;
        if (is_numeric($thoi_gian_raw)) {
            $date_obj = Date::excelToDateTimeObject($thoi_gian_raw);
        } else if (is_string($thoi_gian_raw)) {
            preg_match('/(\d{2}\/\d{2}\/\d{4})/', $thoi_gian_raw, $matches);
            if (isset($matches[1])) {
                $date_obj = DateTime::createFromFormat('d/m/Y', $matches[1]);
            }
        }

        if (!$date_obj) {
            $errors[] = "Không thể đọc định dạng ngày ở dòng {$row}.";
            continue;
        }
        
        $date_obj->setTime(0, 0, 0);

        if ($date_obj < $tuan_start || $date_obj > $tuan_end) {
            $errors[] = "Ngày đi trễ {$date_obj->format('d-m-Y')} của HS {$ho_ten} (dòng {$row}) không thuộc tuần học.";
            continue;
        }

        $stmt_hs = $db->prepare("SELECT id, ma_hoc_sinh FROM hoc_sinh WHERE (CONCAT(ho_dem, ' ', ten)) = ? AND lop_hoc_id = (SELECT id FROM lop_hoc WHERE ten_lop = ?)");
        $stmt_hs->execute([$ho_ten, $lop]);
        $hoc_sinh = $stmt_hs->fetch();

        if (!$hoc_sinh) {
            $errors[] = "Không tìm thấy HS: {$ho_ten} - Lớp {$lop} (dòng {$row})";
            continue;
        }

        $processed_data[] = [
            'hoc_sinh_id' => $hoc_sinh['id'],
            'ma_hs' => $hoc_sinh['ma_hoc_sinh'],
            'ho_ten' => $ho_ten,
            'lop' => $lop,
            'ngay_tre' => $date_obj->format('Y-m-d'),
        ];
    }
    
    $_SESSION['tre_hoc_data'] = $processed_data;
    echo json_encode(['success' => true, 'data' => $processed_data, 'errors' => $errors]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
}