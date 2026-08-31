<?php
// File: src/controllers/ViPhamController.php
use PhpOffice\PhpSpreadsheet\IOFactory;
if (function_exists('opcache_invalidate')) { opcache_invalidate(__FILE__, true); }
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) {
    header('Location: /thidua/tracuu');
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$action = $_GET['action'] ?? 'index';
$db = get_db_connection();
$current_nam_hoc = $_SESSION['current_nam_hoc_id'] ?? 1;

switch ($action) {
    case 'index':
        // ==========================================
        // 1. HIỂN THỊ GIAO DIỆN NHẬP VI PHẠM
        // ==========================================
        if (!in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
            header('Location: /thidua/tracuu');
            exit();
        }
        $tuan_id = $_GET['tuan_id'] ?? null;
        if (!$tuan_id) {
            die("Lỗi: Không tìm thấy ID của tuần học.");
        }
        
        $stmt_tuan = $db->prepare("SELECT * FROM raw_tuan_hoc WHERE id = ? AND nam_hoc_id = ?");
        $stmt_tuan->execute([$tuan_id, $current_nam_hoc]);
        $tuan_hoc = $stmt_tuan->fetch();
        
        if (!$tuan_hoc) {
            die("Lỗi: Tuần học không tồn tại hoặc không thuộc năm học hiện tại.");
        }
        
        $stmt_cau_hinh = $db->prepare("SELECT id, ten_vi_pham, nhom_vi_pham FROM raw_cau_hinh_vi_pham WHERE nam_hoc_id = ? ORDER BY nhom_vi_pham ASC, ten_vi_pham ASC");
        $stmt_cau_hinh->execute([$current_nam_hoc]);
        $danh_sach_cau_hinh_vi_pham = $stmt_cau_hinh->fetchAll();
        
        $sql_get_violations = "
            SELECT 
                vp.*, 
                ho_so.ma_hoc_sinh, 
                ho_so.trang_thai_hoc_tap,
                (CONCAT(ho_so.ho_dem, ' ', ho_so.ten)) as ho_ten_day_du,
                lh.ten_lop,
                chvp.ten_vi_pham,
                CASE 
                    WHEN vp.nguoi_nhap_type = 'admin' THEN u.ho_ten
                    ELSE CONCAT(hs_ctv.ho_dem, ' ', hs_ctv.ten)
                END as nguoi_nhap_ten,
                vp.nguoi_nhap_type,
                lh_ctv.ten_lop as lop_ctv
            FROM vi_pham_hoc_sinh vp
            LEFT JOIN quatrinh_hoc_tap qt ON vp.hoc_sinh_id = qt.id
            LEFT JOIN ho_so_hoc_sinh ho_so ON qt.ma_hoc_sinh = ho_so.ma_hoc_sinh
            LEFT JOIN raw_lop_hoc lh ON qt.lop_hoc_id = lh.id
            LEFT JOIN raw_cau_hinh_vi_pham chvp ON vp.vi_pham_id = chvp.id
            LEFT JOIN users u ON vp.nguoi_nhap_id = u.id AND vp.nguoi_nhap_type = 'admin'
            LEFT JOIN hoc_sinh hs_ctv ON vp.nguoi_nhap_id = hs_ctv.id AND vp.nguoi_nhap_type = 'ctv'
            LEFT JOIN raw_lop_hoc lh_ctv ON hs_ctv.lop_hoc_id = lh_ctv.id
            WHERE vp.tuan_hoc_id = ?
            ORDER BY vp.id ASC
        ";
        $stmt_violations = $db->prepare($sql_get_violations);
        $stmt_violations->execute([$tuan_id]);
        $danh_sach_vi_pham_da_nhap = $stmt_violations->fetchAll();
        
        require_once __DIR__ . '/../views/nhap_vi_pham.php';
        break;

    case 'api_save':
        // ==========================================
        // 2. LƯU VI PHẠM (API)
        // ==========================================
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $violations = $data['violations'] ?? null;
        $response = ['success' => false, 'message' => 'Dữ liệu không hợp lệ.'];
        $current_user_id = $_SESSION['user_id'] ?? null;

        if (!$violations || !is_array($violations) || !$current_user_id) {
            echo json_encode($response);
            exit();
        }

        $created_count = 0;
        $updated_count = 0;
        $saved_ids = []; 

        try {
            $db->beginTransaction();
            $sql_insert = "INSERT INTO vi_pham_hoc_sinh (tuan_hoc_id, hoc_sinh_id, vi_pham_id, ngay_vi_pham, nguoi_nhap_id, ghi_chu, raw_ho_ten, raw_ten_lop, thoi_gian_nhap, nguoi_nhap_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'admin')";
            $stmt_insert = $db->prepare($sql_insert);
            $sql_update = "UPDATE vi_pham_hoc_sinh SET hoc_sinh_id=?, vi_pham_id=?, ngay_vi_pham=?, ghi_chu=?, raw_ho_ten=?, raw_ten_lop=? WHERE id=?";
            $stmt_update = $db->prepare($sql_update);

            foreach ($violations as $vp) {
                $hoc_sinh_id = !empty($vp['hoc_sinh_id']) ? (int)$vp['hoc_sinh_id'] : null;
                $vi_pham_id = !empty($vp['cau_hinh_vi_pham_id']) ? (int)$vp['cau_hinh_vi_pham_id'] : null;

                if (empty($vp['id'])) { 
                    $stmt_insert->execute([
                        $vp['tuan_hoc_id'],
                        $hoc_sinh_id,
                        $vi_pham_id,
                        $vp['ngay_vi_pham'],
                        $current_user_id,
                        $vp['ghi_chu'],
                        $vp['ten_hoc_sinh_raw'],
                        $vp['ten_lop_raw'],
                        date('Y-m-d H:i:s')
                    ]);
                    $created_count++;
                    $saved_ids[] = $db->lastInsertId();
                } else {
                    $stmt_update->execute([
                        $hoc_sinh_id,
                        $vi_pham_id,
                        $vp['ngay_vi_pham'],
                        $vp['ghi_chu'],
                        $vp['ten_hoc_sinh_raw'],
                        $vp['ten_lop_raw'],
                        $vp['id']
                    ]);
                    $updated_count++;
                    $saved_ids[] = $vp['id'];
                }
            }
            $db->commit();
            $response = [
                'success' => true, 
                'message' => "Lưu thành công! Tạo mới: {$created_count}, Cập nhật: {$updated_count}.",
                'saved_ids' => $saved_ids
            ];
        } catch (Exception $e) {
            if($db->inTransaction()) {
                $db->rollBack();
            }
            $response['message'] = 'Lỗi khi lưu vào CSDL: ' . $e->getMessage();
            http_response_code(500);
        }
        echo json_encode($response);
        break;

    case 'api_delete':
        // ==========================================
        // 3. XÓA VI PHẠM (API)
        // ==========================================
        header('Content-Type: application/json');
        $response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ.'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $ids_to_delete = $data['ids'] ?? [];

            if (!empty($ids_to_delete) && is_array($ids_to_delete)) {
                try {
                    $db->beginTransaction();
                    $placeholders = implode(',', array_fill(0, count($ids_to_delete), '?'));
                    $sql = "DELETE FROM vi_pham_hoc_sinh WHERE id IN ($placeholders)";
                    $stmt = $db->prepare($sql);
                    $stmt->execute($ids_to_delete);
                    $deleted_count = $stmt->rowCount();
                    $db->commit();
                    $response = ['success' => true, 'message' => "Đã xóa thành công {$deleted_count} mục."];
                } catch (Exception $e) {
                    if ($db->inTransaction()) {
                        $db->rollBack();
                    }
                    $response['message'] = 'Lỗi CSDL: ' . $e->getMessage();
                    http_response_code(500);
                }
            } else {
                $response['message'] = 'Không có ID nào được chọn để xóa.';
            }
        }
        echo json_encode($response);
        break;

    case 'import':
        // ==========================================
        // 4. IMPORT FILE EXCEL (Xử lý form)
        // ==========================================
        require_once __DIR__ . '/../../vendor/autoload.php';


        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excelFile'])) {
            $tuan_id = $_POST['tuan_id'] ?? null;
            $file = $_FILES['excelFile'];
            $redirect_url = '/thidua/admin/vi-pham?tuan_id=' . ($tuan_id ?: '');

            try {
                if (!$tuan_id || $file['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception('Yêu cầu không hợp lệ hoặc file tải lên bị lỗi.');
                }
                $spreadsheet = IOFactory::load($file['tmp_name']);
                $worksheet = $spreadsheet->getSheet(0);
                $raw_rows = [];
                $highestRow = $worksheet->getHighestRow();

                for ($row_index = 2; $row_index <= $highestRow; ++$row_index) {
                    $rowData = [
                        'line_number' => $row_index,
                        'ten_hs'      => trim($worksheet->getCell('B' . $row_index)->getValue() ?? ''),
                        'lop'         => trim($worksheet->getCell('C' . $row_index)->getValue() ?? ''),
                        'ngay_vp'     => $worksheet->getCell('D' . $row_index)->getValue(),
                        'ten_vp'      => trim($worksheet->getCell('E' . $row_index)->getValue() ?? ''),
                        'ghi_chu'     => trim($worksheet->getCell('F' . $row_index)->getValue() ?? ''),
                        'id_nhap'     => trim($worksheet->getCell('G' . $row_index)->getValue() ?? ''),
                    ];
                    if (!empty($rowData['ten_hs']) || !empty($rowData['lop']) || !empty($rowData['ten_vp'])) {
                        $raw_rows[] = $rowData;
                    }
                }
                $_SESSION['import_raw_rows'] = $raw_rows;
                $_SESSION['import_tuan_id'] = $tuan_id;
                header('Location: /thidua/admin/vi-pham?action=api_preview_import');
                exit();
            } catch (Exception $e) {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Lỗi xử lý file: ' . $e->getMessage()];
                header('Location: ' . $redirect_url);
                exit();
            }
        } else {
            header('Location: /thidua/admin/tuan-hoc');
            exit();
        }
        break;

    case 'api_preview_import':
        // ==========================================
        // 5. XEM TRƯỚC DỮ LIỆU IMPORT
        // ==========================================
        require_once __DIR__ . '/../../vendor/autoload.php';
        $tuan_id = $_SESSION['import_tuan_id'] ?? null;
        $raw_rows_from_session = $_SESSION['import_raw_rows'] ?? [];
        $tuan_info = null;
        $all_violations = [];

        if ($tuan_id) {
            $stmt_tuan = $db->prepare("SELECT * FROM raw_tuan_hoc WHERE id = ?");
            $stmt_tuan->execute([$tuan_id]);
            $tuan_info = $stmt_tuan->fetch();

            $stmt_vp = $db->prepare("SELECT id, ten_vi_pham FROM raw_cau_hinh_vi_pham WHERE nam_hoc_id = ?");
            $stmt_vp->execute([$current_nam_hoc]);
            foreach($stmt_vp->fetchAll() as $vp) {
                $key = mb_strtolower(trim($vp['ten_vi_pham']), 'UTF-8');
                $all_violations[$key] = $vp['id'];
            }
        }

        $raw_rows = [];
        foreach ($raw_rows_from_session as $row) {
            if (is_numeric($row['ngay_vp'])) {
                try {
                    $row['ngay_vp_formatted'] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['ngay_vp'])->format('d/m/Y');
                } catch (Exception $e) {
                    $row['ngay_vp_formatted'] = $row['ngay_vp'];
                }
            } else {
                $row['ngay_vp_formatted'] = $row['ngay_vp'];
            }
            $raw_rows[] = $row;
        }

        unset($_SESSION['import_raw_rows']);
        require_once __DIR__ . '/../views/xem_truoc_import_vi_pham.php';
        break;

    case 'api_save_import':
        // ==========================================
        // 6. LƯU DỮ LIỆU TỪ EXCEL VÀO DB
        // ==========================================
        header('Content-Type: application/json');
        $response = ['success' => false, 'message' => 'Dữ liệu không hợp lệ hoặc thiếu thông tin.'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $tuan_id = $data['tuan_id'] ?? null;
            $user_id = $data['user_id'] ?? null;
            $violations = $data['violations'] ?? [];

            if ($tuan_id && $user_id && !empty($violations)) {
                $success_count = 0;
                try {
                    $db->beginTransaction();
                    $sql_insert = "INSERT INTO vi_pham_hoc_sinh 
                                        (tuan_hoc_id, hoc_sinh_id, vi_pham_id, ngay_vi_pham, nguoi_nhap_id, ghi_chu, raw_ho_ten, raw_ten_lop, nguoi_nhap_type, thoi_gian_nhap) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'admin_import', ?)";
                    $stmt_insert = $db->prepare($sql_insert);
                    $current_timestamp = date('Y-m-d H:i:s');

                    foreach ($violations as $vp) {
                        $nguoi_nhap_id = !empty($vp['id_nhap']) ? (int)$vp['id_nhap'] : $user_id;
                        $raw_ho_ten = $vp['ten_hs'] ?? null;
                        $raw_ten_lop = $vp['ten_lop_chuan_hoa'] ?? null;

                        $stmt_insert->execute([
                            $tuan_id,
                            $vp['hoc_sinh_id'] ?: null,
                            $vp['vi_pham_id'] ?: null,
                            $vp['ngay_vp_iso'],
                            $nguoi_nhap_id,
                            $vp['ghi_chu'] ?: null,
                            $raw_ho_ten,
                            $raw_ten_lop,
                            $current_timestamp
                        ]);
                        $success_count++;
                    }
                    $db->commit();
                    unset($_SESSION['import_tuan_id']);
                    $response = ['success' => true, 'message' => "Đã import thành công {$success_count} vi phạm."];
                } catch (Exception $e) {
                    if ($db->inTransaction()) {
                        $db->rollBack();
                    }
                    $response['message'] = 'Lỗi CSDL: ' . $e->getMessage();
                    http_response_code(500);
                }
            }
        }
        echo json_encode($response);
        break;

    case 'api_cancel_import':
        // ==========================================
        // 7. HỦY IMPORT (Clear session)
        // ==========================================
        $batch_id = $_SESSION['import_batch_id'] ?? null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $batch_id) {
            $stmt = $db->prepare("DELETE FROM import_batches WHERE id = ?");
            $stmt->execute([$batch_id]);
            unset($_SESSION['import_batch_id']);
            $_SESSION['flash_message'] = ['type' => 'info', 'message' => 'Đã hủy phiên import.'];
        }
        header('Location: /thidua/admin/tuan-hoc');
        exit();
        break;

    case 'export_excel_ds':
        // ==========================================
        // 8. XUẤT DANH SÁCH VI PHẠM (Theo tuần)
        // ==========================================
        require_once __DIR__ . '/../../vendor/autoload.php';
        
        $tuan_id = $_GET['tuan_id'] ?? null;
        if (!$tuan_id) {
            die("Lỗi: Thiếu ID của tuần học.");
        }
        
        $templatePath = __DIR__ . '/../../public/templates/mau_import_vi_pham.xlsx';
        if (!file_exists($templatePath)) {
            die("Lỗi: Không tìm thấy file mẫu tại public/templates/mau_import_vi_pham.xlsx. Vui lòng đặt file mẫu vào đúng vị trí.");
        }
        
        try {
            $stmt_tuan = $db->prepare("SELECT ten_tuan FROM raw_tuan_hoc WHERE id = ?");
            $stmt_tuan->execute([$tuan_id]);
            $tuan_hoc = $stmt_tuan->fetch();
            $ten_tuan = 'Tuan_' . $tuan_id;
            if ($tuan_hoc) {
                $search = ['á','à','ả','ã','ạ','â','ấ','ầ','ẩ','ẫ','ậ','ă','ắ','ằ','ẳ','ẵ','ặ','đ','é','è','ẻ','ẽ','ẹ','ê','ế','ề','ể','ễ','ệ','í','ì','ỉ','ĩ','ị','ó','ò','ỏ','õ','ọ','ô','ố','ồ','ổ','ỗ','ộ','ơ','ớ','ờ','ở','ỡ','ợ','ú','ù','ủ','ũ','ụ','ư','ứ','ừ','ử','ữ','ự','ý','ỳ','ỷ','ỹ','ỵ','Á','À','Ả','Ã','Ạ','Â','Ấ','Ầ','Ẩ','Ẫ','Ậ','Ă','Ắ','Ằ','Ẳ','Ẵ','Ặ','Đ','É','È','Ẻ','Ẽ','Ẹ','Ê','Ế','Ề','Ể','Ễ','Ệ','Í','Ì','Ỉ','Ĩ','Ị','Ó','Ò','Ỏ','Õ','Ọ','Ô','Ố','Ồ','Ổ','Ỗ','Ộ','Ơ','Ớ','Ờ','Ở','Ỡ','Ợ','Ú','Ù','Ủ','Ũ','Ụ','Ư','Ứ','Ừ','Ử','Ữ','Ự','Ý','Ỳ','Ỷ','Ỹ','Ỵ'];
                $replace = ['a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','d','e','e','e','e','e','e','e','e','e','e','e','i','i','i','i','i','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','u','u','u','u','u','u','u','u','u','u','u','y','y','y','y','y','A','A','A','A','A','A','A','A','A','A','A','A','A','A','A','A','A','D','E','E','E','E','E','E','E','E','E','E','E','I','I','I','I','I','O','O','O','O','O','O','O','O','O','O','O','O','O','O','O','O','O','U','U','U','U','U','U','U','U','U','U','U','Y','Y','Y','Y','Y'];
                $ten_tuan_khong_dau = str_replace($search, $replace, $tuan_hoc['ten_tuan']);
                $ten_tuan = trim(preg_replace('/[^a-zA-Z0-9]+/', '_', $ten_tuan_khong_dau), '_');
            }
            
            $sql = "
                SELECT 
                    ho_so.ma_hoc_sinh, 
                    (CONCAT(ho_so.ho_dem, ' ', ho_so.ten)) as ho_ten_day_du,
                    lh.ten_lop,
                    vp.ngay_vi_pham,
                    chvp.ten_vi_pham,
                    vp.ghi_chu,
                    vp.nguoi_nhap_id
                FROM vi_pham_hoc_sinh vp
                LEFT JOIN quatrinh_hoc_tap qt ON vp.hoc_sinh_id = qt.id
                LEFT JOIN ho_so_hoc_sinh ho_so ON qt.ma_hoc_sinh = ho_so.ma_hoc_sinh
                LEFT JOIN raw_lop_hoc lh ON qt.lop_hoc_id = lh.id
                LEFT JOIN raw_cau_hinh_vi_pham chvp ON vp.vi_pham_id = chvp.id
                WHERE vp.tuan_hoc_id = ?
                ORDER BY vp.id ASC
            ";
            $stmt = $db->prepare($sql);
            $stmt->execute([$tuan_id]);
            $danh_sach_vi_pham = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($templatePath);
            $sheet = $spreadsheet->getActiveSheet();
            
            $startRow = 2;
            foreach ($danh_sach_vi_pham as $index => $vp) {
                $currentRow = $startRow + $index;
                $sheet->setCellValue('A' . $currentRow, $vp['ma_hoc_sinh']);
                $sheet->setCellValue('B' . $currentRow, $vp['ho_ten_day_du']);
                $sheet->setCellValue('C' . $currentRow, $vp['ten_lop']);
                $sheet->setCellValue('D' . $currentRow, date('d/m/Y', strtotime($vp['ngay_vi_pham'])));
                $sheet->setCellValue('E' . $currentRow, $vp['ten_vi_pham']);
                $sheet->setCellValue('F' . $currentRow, $vp['ghi_chu']);
                $sheet->setCellValue('G' . $currentRow, $vp['nguoi_nhap_id']);
            }
            
            $lastRow = $startRow + count($danh_sach_vi_pham) - 1;
            if ($lastRow >= $startRow) { 
                $tableRange = 'A1:G' . $lastRow;
                $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }
            
            foreach (range('A', 'G') as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }
            
            $filename = "DS_ViPham_{$ten_tuan}_" . date('Ymd') . ".xlsx";
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            ob_clean(); 
            flush();    
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            exit();
            
        } catch (Exception $e) {
            die("Lỗi khi tạo file Excel: " . $e->getMessage());
        }
        break;

    case 'export_excel_lop':
        // ==========================================
        // 9. XUẤT EXCEL VI PHẠM THEO LỚP
        // ==========================================
        require_once __DIR__ . '/../../vendor/autoload.php';
        
        $class_id = filter_input(INPUT_GET, 'class_id', FILTER_VALIDATE_INT);
        if (!$class_id) {
            die("Lỗi: ID lớp không hợp lệ.");
        }
        
        $stmt_class = $db->prepare("SELECT ten_lop FROM raw_lop_hoc WHERE id = ?");
        $stmt_class->execute([$class_id]);
        $class_name = $stmt_class->fetchColumn();
        if (!$class_name) {
            die("Lỗi: Không tìm thấy lớp học.");
        }
        
        $admin_name = $_SESSION['user_ten'] ?? 'Ban Thi Đua';
        
        // Cần kết hợp nam_hoc_id để đảm bảo lấy đúng.
        $stmt_violations = $db->prepare("
            SELECT 
                vp.ngay_vi_pham, 
                (CONCAT(hs.ho_dem, ' ', hs.ten)) as ho_ten,
                lh.ten_lop,
                chvp.ten_vi_pham,
                vp.ghi_chu,
                th.id as tuan_id,
                th.ten_tuan
            FROM vi_pham_hoc_sinh vp
            JOIN quatrinh_hoc_tap qt ON vp.hoc_sinh_id = qt.id
            JOIN ho_so_hoc_sinh hs ON qt.ma_hoc_sinh = hs.ma_hoc_sinh
            JOIN raw_lop_hoc lh ON qt.lop_hoc_id = lh.id
            JOIN raw_cau_hinh_vi_pham chvp ON vp.vi_pham_id = chvp.id
            JOIN raw_tuan_hoc th ON vp.tuan_hoc_id = th.id
            WHERE qt.lop_hoc_id = ? AND qt.nam_hoc_id = ?
            ORDER BY th.ngay_bat_dau, vp.ngay_vi_pham, ho_ten
        ");
        $stmt_violations->execute([$class_id, $current_nam_hoc]);
        $all_violations = $stmt_violations->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($all_violations)) {
            echo "<script>alert('Lớp " . htmlspecialchars($class_name) . " không có vi phạm nào để xuất file.'); window.close();</script>";
            exit;
        }
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        
        // Group violations by week
        $grouped_by_week = [];
        foreach ($all_violations as $vp) {
            $week_name = $vp['ten_tuan'];
            $grouped_by_week[$week_name][] = $vp;
        }
        
        function createViolationSheet($sheet, string $class_name, string $sheet_subtitle, array $violations, string $admin_name) {
            $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman');
            $sheet->mergeCells('A1:D1')->setCellValue('A1', 'TRƯỜNG THPT BÌNH SƠN');
            $sheet->mergeCells('A2:D2')->setCellValue('A2', 'HỆ THỐNG ĐÁNH GIÁ THI ĐUA');
            $sheet->getStyle('A1')->getFont()->setSize(11)->setBold(false);
            $sheet->getStyle('A2')->getFont()->setSize(11)->setBold(true);
            $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
            $main_title = "DANH SÁCH HỌC SINH VI PHẠM - LỚP " . mb_strtoupper($class_name, 'UTF-8');
            $sheet->mergeCells('A3:F3')->setCellValue('A3', $main_title);
            $sheet->mergeCells('A4:F4')->setCellValue('A4', $sheet_subtitle);
        
            $sheet->getStyle('A3')->getFont()->setSize(13)->setBold(true);
            $sheet->getStyle('A4')->getFont()->setSize(12)->setBold(true);
            $sheet->getStyle('A3:F4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
            $header_row = 6;
            $headers = ['STT', 'Họ và Tên', 'Tuần', 'Ngày VP', 'Tên nhóm vi phạm', 'Ghi chú'];
            $sheet->fromArray($headers, NULL, 'A' . $header_row);
            $sheet->getStyle('A'.$header_row.':F'.$header_row)->getFont()->setBold(true)->setSize(9);
            $sheet->getStyle('A'.$header_row.':F'.$header_row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        
            $data_row_start = $header_row + 1;
            $rowIndex = $data_row_start;
            $stt = 1;
        
            foreach ($violations as $vp) {
                $sheet->setCellValue('A' . $rowIndex, $stt++);
                $sheet->setCellValue('B' . $rowIndex, $vp['ho_ten']);
                $sheet->setCellValue('C' . $rowIndex, $vp['ten_tuan']);
                $sheet->setCellValue('D' . $rowIndex, date('d/m/Y', strtotime($vp['ngay_vi_pham'])));
                $sheet->setCellValue('E' . $rowIndex, $vp['ten_vi_pham']);
                $sheet->setCellValue('F' . $rowIndex, $vp['ghi_chu']);
                $rowIndex++;
            }
        
            $last_row = $rowIndex - 1;
            $table_range = 'A'.$header_row.':F'.$last_row;
            $sheet->getStyle($table_range)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $sheet->getStyle('A'.$data_row_start.':A'.$last_row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C'.$data_row_start.':D'.$last_row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
            foreach (range('A', 'F') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        
            $date_row = $rowIndex + 1;
            $sheet->setCellValue('D'.$date_row, 'Ngày .... tháng .... năm ......');
            $sheet->getStyle('D'.$date_row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D'.$date_row)->getFont()->setItalic(true);
        
            $sign_row = $date_row + 1;
            $sheet->setCellValue('D'.$sign_row, 'Người lập bảng');
            $sheet->getStyle('D'.$sign_row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D'.$sign_row)->getFont()->setBold(true);
        
            $name_row = $sign_row + 4;
            $sheet->setCellValue('D'.$name_row, $admin_name);
            $sheet->getStyle('D'.$name_row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D'.$name_row)->getFont()->setBold(true);
            
            $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
            $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
            $sheet->getPageSetup()->setFitToWidth(1);
            $sheet->getPageSetup()->setFitToHeight(0);
        }

        $sheetIndex = 0;
        
        $sheet_all = $spreadsheet->getActiveSheet();
        $sheet_all->setTitle('Tất cả các tuần');
        createViolationSheet($sheet_all, $class_name, 'TỔNG HỢP TẤT CẢ CÁC TUẦN', $all_violations, $admin_name);
        $sheetIndex++;
        
        foreach ($grouped_by_week as $week_name => $violations_in_week) {
            $sheet_week = $spreadsheet->createSheet($sheetIndex);
            $safe_week_name = substr(str_replace(['*', ':', '/', '\\', '?', '[', ']'], '', $week_name), 0, 31);
            $sheet_week->setTitle($safe_week_name);
            createViolationSheet($sheet_week, $class_name, mb_strtoupper($week_name, 'UTF-8'), $violations_in_week, $admin_name);
            $sheetIndex++;
        }
        
        $spreadsheet->setActiveSheetIndex(0);
        
        $file_name = "DS_Vi_Pham_Lop_" . preg_replace('/[^a-zA-Z0-9]/', '_', $class_name) . "_" . date('Ymd_His') . ".xlsx";
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $file_name . '"');
        header('Cache-Control: max-age=0');
        
        ob_clean();
        flush();
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
        break;

    default:
        header('Location: /thidua/admin/tuan-hoc');
        exit();
}
