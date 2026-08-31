<?php
// FILE: src/controllers/import_gvcn.php (ĐÃ NÂNG CẤP ĐỂ IMPORT NGÀY SINH)

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date; // Thư viện để xử lý ngày tháng từ Excel

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Không có file nào được tải lên.'];

if (isset($_FILES['gvcnFile'])) {
    $file = $_FILES['gvcnFile'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $response['message'] = 'Lỗi trong quá trình tải file.';
        echo json_encode($response);
        exit();
    }

    try {
        // 1. Đọc dữ liệu từ file Excel
        $spreadsheet = IOFactory::load($file['tmp_name']);
        $worksheet = $spreadsheet->getActiveSheet();
        $data = $worksheet->toArray();
        
        array_shift($data); // Bỏ qua dòng tiêu đề

        $db = get_db_connection();
        $db->beginTransaction();

        $current_nam_hoc = $_SESSION['current_nam_hoc_id'] ?? 1;

        // 2. Chuẩn bị câu lệnh UPDATE (Thêm gvcn_ngay_sinh và lọc theo nam_hoc_id)
        $sql = "UPDATE lop_hoc SET gvcn_ten = ?, gvcn_ma = ?, gvcn_email = ?, gvcn_ngay_sinh = ? WHERE ten_lop = ? AND nam_hoc_id = ?";
        $stmt = $db->prepare($sql);
        $updated_count = 0;

        // 3. Lặp qua từng dòng và cập nhật CSDL
        foreach ($data as $row) {
            if (empty($row[0])) continue; 

            $ten_lop = trim($row[0]);
            $gvcn_ten = $row[1] ?? '';
            $gvcn_ma = $row[2] ?? '';
            $gvcn_email = $row[3] ?? '';
            $gvcn_ngay_sinh_raw = $row[4] ?? '';
            $gvcn_ngay_sinh = '';

            // Xử lý ngày sinh (giống như import học sinh)
            if (!empty($gvcn_ngay_sinh_raw)) {
                if (is_numeric($gvcn_ngay_sinh_raw)) {
                    $gvcn_ngay_sinh = Date::excelToDateTimeObject($gvcn_ngay_sinh_raw)->format('d/m/Y');
                } else {
                    $gvcn_ngay_sinh = trim($gvcn_ngay_sinh_raw);
                }
            }

            // Thực thi câu lệnh
            $stmt->execute([$gvcn_ten, $gvcn_ma, $gvcn_email, $gvcn_ngay_sinh, $ten_lop, $current_nam_hoc]);
            
            if ($stmt->rowCount() > 0) {
                $updated_count++;
            }
        }

        $db->commit();
        $response = ['success' => true, 'updated_count' => $updated_count];

    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        $response['message'] = 'Lỗi xử lý file: ' . $e->getMessage();
        http_response_code(500);
    }
}

echo json_encode($response);