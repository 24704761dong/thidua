<?php
// File: src/controllers/api_cau_hinh_vi_pham.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Bỏ qua kiểm tra quyền đối với import/export/thêm/sửa/xóa nếu là truy cập trái phép
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../lib/nam_hoc.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

$db = get_db_connection();
$current_nam_hoc = current_nam_hoc_id();
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

switch ($uri) {
    case '/thidua/them-cau-hinh-vi-pham':
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
            exit();
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $nhom_vi_pham = isset($data['nhom_vi_pham']) ? trim($data['nhom_vi_pham']) : null;
        $ten_vi_pham = isset($data['ten_vi_pham']) ? trim($data['ten_vi_pham']) : null;
        $diem_tru = $data['diem_tru'] ?? null;

        if (empty($ten_vi_pham) || !is_numeric($diem_tru)) {
            echo json_encode(['success' => false, 'message' => 'Tên vi phạm và Điểm trừ là bắt buộc và điểm phải là số.']);
            exit();
        }
        
        try {
            $stmt = $db->prepare("INSERT INTO raw_cau_hinh_vi_pham (nhom_vi_pham, ten_vi_pham, diem_tru, nam_hoc_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([empty($nhom_vi_pham) ? null : $nhom_vi_pham, $ten_vi_pham, $diem_tru, $current_nam_hoc]);
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Thêm cấu hình vi phạm thành công!'];
            echo json_encode(['success' => true, 'message' => 'Thêm cấu hình vi phạm thành công!']);
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                echo json_encode(['success' => false, 'message' => 'Thêm thất bại: Tên vi phạm đã tồn tại.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Thêm thất bại: ' . $e->getMessage()]);
            }
        }
        exit();

    case '/thidua/sua-cau-hinh-vi-pham':
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
            exit();
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;
        $nhom_vi_pham = isset($data['nhom_vi_pham']) ? trim($data['nhom_vi_pham']) : null;
        $ten_vi_pham = isset($data['ten_vi_pham']) ? trim($data['ten_vi_pham']) : null;
        $diem_tru = $data['diem_tru'] ?? null;

        if (empty($id) || empty($ten_vi_pham) || !is_numeric($diem_tru)) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ. Tên và Điểm trừ là bắt buộc.']);
            exit();
        }
        try {
            $sql = "UPDATE raw_cau_hinh_vi_pham SET nhom_vi_pham = ?, ten_vi_pham = ?, diem_tru = ? WHERE id = ? AND nam_hoc_id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([empty($nhom_vi_pham) ? null : $nhom_vi_pham, $ten_vi_pham, $diem_tru, $id, $current_nam_hoc]);
            
            if ($stmt->rowCount() > 0) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Cập nhật vi phạm thành công!'];
                echo json_encode(['success' => true, 'message' => 'Cập nhật vi phạm thành công!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Cập nhật thất bại hoặc không có gì thay đổi (có thể sai năm học).']);
            }
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                echo json_encode(['success' => false, 'message' => 'Cập nhật thất bại: Tên vi phạm đã tồn tại.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
            }
        }
        exit();

    case '/thidua/xoa-cau-hinh-vi-pham':
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
            exit();
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'Thiếu ID của vi phạm cần xóa.']);
            exit();
        }
        try {
            $sql = "DELETE FROM raw_cau_hinh_vi_pham WHERE id = ? AND nam_hoc_id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$id, $current_nam_hoc]);
            if ($stmt->rowCount() > 0) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Đã xóa vi phạm thành công!'];
                echo json_encode(['success' => true, 'message' => 'Đã xóa vi phạm thành công!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Xóa thất bại. Vi phạm không tồn tại hoặc sai năm học.']);
            }
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                 echo json_encode(['success' => false, 'message' => 'Không thể xóa: Vi phạm này đã được sử dụng.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
            }
        }
        exit();

    case '/thidua/tai-mau-cau-hinh-vi-pham':
        $filePath = __DIR__ . '/../../public/templates/mau_cau_hinh_vi_pham.xlsx';
        if (file_exists($filePath)) {
            setcookie("fileDownloadToken", "success", ["expires" => time() + 20, "path" => "/", "samesite" => "Strict"]);
            header('Content-Description: File Transfer');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="Mau_Import_Cau_Hinh_Vi_Pham.xlsx"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            ob_clean();
            flush();
            readfile($filePath);
            exit();
        } else {
            die('Lỗi: Không tìm thấy file mẫu. Vui lòng tạo file tại public/templates/mau_cau_hinh_vi_pham.xlsx');
        }
        break;

    case '/thidua/import-cau-hinh-vi-pham':
        $iframe_param = isset($_GET['iframe']) ? '?iframe=1' : '';
        unset($_SESSION['import_preview_valid_chvp']);
        unset($_SESSION['import_preview_invalid_chvp']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excelFile'])) {
            $file = $_FILES['excelFile'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Lỗi trong quá trình tải file lên.'];
                header('Location: /thidua/admin/cau-hinh-vi-pham' . $iframe_param);
                exit();
            }
            $fileType = IOFactory::identify($file['tmp_name']);
            if ($fileType !== 'Xlsx') {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Lỗi: Chỉ chấp nhận file định dạng .xlsx'];
                header('Location: /thidua/admin/cau-hinh-vi-pham' . $iframe_param);
                exit();
            }

            $stmt_existing = $db->prepare("SELECT LOWER(ten_vi_pham) FROM raw_cau_hinh_vi_pham WHERE nam_hoc_id = ?");
            $stmt_existing->execute([$current_nam_hoc]);
            $existing_violations = $stmt_existing->fetchAll(PDO::FETCH_COLUMN);

            $spreadsheet = IOFactory::load($file['tmp_name']);
            $worksheet = $spreadsheet->getActiveSheet();
            $data = $worksheet->toArray();
            array_shift($data); // Bỏ qua dòng tiêu đề

            $danh_sach_hop_le = [];
            $danh_sach_khong_hop_le = [];
            $ten_vi_pham_trong_file = [];

            foreach ($data as $rowIndex => $row) {
                if (empty(array_filter($row))) continue;
                $rowData = [
                    'nhom' => isset($row[0]) ? trim($row[0]) : null,
                    'ten_vp' => isset($row[1]) ? trim($row[1]) : null,
                    'diem_tru' => isset($row[2]) ? $row[2] : null,
                    'line_number' => $rowIndex + 2
                ];
                
                $errors = [];
                if (empty($rowData['ten_vp'])) {
                    $errors[] = "Tên vi phạm không được để trống.";
                } else {
                    $ten_vp_lower = mb_strtolower($rowData['ten_vp'], 'UTF-8');
                    if (in_array($ten_vp_lower, $existing_violations)) {
                        $errors[] = "Tên vi phạm đã tồn tại trên hệ thống.";
                    }
                    if (in_array($ten_vp_lower, $ten_vi_pham_trong_file)) {
                        $errors[] = "Tên vi phạm bị trùng lặp trong file Excel.";
                    } else {
                        $ten_vi_pham_trong_file[] = $ten_vp_lower;
                    }
                }

                if (!is_numeric($rowData['diem_tru'])) {
                    $errors[] = "Điểm trừ phải là một số hợp lệ.";
                }

                if (empty($errors)) {
                    $danh_sach_hop_le[] = $rowData;
                } else {
                    $rowData['errors'] = $errors;
                    $danh_sach_khong_hop_le[] = $rowData;
                }
            }

            $_SESSION['import_preview_valid_chvp'] = $danh_sach_hop_le;
            $_SESSION['import_preview_invalid_chvp'] = $danh_sach_khong_hop_le;
            header('Location: /thidua/xem-truoc-import-cau-hinh-vi-pham' . $iframe_param);
            exit();
        }
        header('Location: /thidua/admin/cau-hinh-vi-pham' . $iframe_param);
        exit();

    case '/thidua/luu-import-cau-hinh-vi-pham':
        $iframe_param = isset($_GET['iframe']) ? '?iframe=1' : '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SESSION['import_preview_valid_chvp'])) {
            $danh_sach_hop_le = $_SESSION['import_preview_valid_chvp'];
            $sql = "INSERT INTO raw_cau_hinh_vi_pham (nhom_vi_pham, ten_vi_pham, diem_tru, nam_hoc_id) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE nhom_vi_pham = VALUES(nhom_vi_pham), diem_tru = VALUES(diem_tru)";
            $stmt = $db->prepare($sql);
            $success_count = 0;

            try {
                $db->beginTransaction();
                foreach ($danh_sach_hop_le as $row) {
                    $stmt->execute([
                        empty($row['nhom']) ? null : $row['nhom'],
                        $row['ten_vp'],
                        $row['diem_tru'],
                        $current_nam_hoc
                    ]);
                    $success_count++;
                }
                $db->commit();
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => "Đã import thành công {$success_count} mục."];

            } catch (Exception $e) {
                $db->rollBack();
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Lỗi khi lưu dữ liệu: ' . $e->getMessage()];
            }

            unset($_SESSION['import_preview_valid_chvp']);
            unset($_SESSION['import_preview_invalid_chvp']);
            header('Location: /thidua/admin/cau-hinh-vi-pham' . $iframe_param);
            exit();

        } else {
            $_SESSION['flash_message'] = ['type' => 'warning', 'message' => 'Không có dữ liệu hợp lệ để import.'];
            header('Location: /thidua/admin/cau-hinh-vi-pham' . $iframe_param);
            exit();
        }
        break;

    case '/thidua/xem-truoc-import-cau-hinh-vi-pham':
        $iframe_param = isset($_GET['iframe']) ? '?iframe=1' : '';
        if (!isset($_SESSION['import_preview_valid_chvp']) && !isset($_SESSION['import_preview_invalid_chvp'])) {
            $_SESSION['flash_message'] = ['type' => 'warning', 'message' => 'Không có dữ liệu import để xem trước.'];
            header('Location: /thidua/admin/cau-hinh-vi-pham' . $iframe_param);
            exit();
        }
        $danh_sach_hop_le = $_SESSION['import_preview_valid_chvp'] ?? [];
        $danh_sach_khong_hop_le = $_SESSION['import_preview_invalid_chvp'] ?? [];
        require_once __DIR__ . '/../views/xem_truoc_import_cau_hinh_vi_pham.php';
        exit();

    case '/thidua/xuat-cau-hinh-vi-pham':
        $stmt_nam_hoc = $db->prepare("SELECT ten_nam_hoc FROM nam_hoc WHERE id = ?");
        $stmt_nam_hoc->execute([$current_nam_hoc]);
        $nam_hoc_hien_tai = $stmt_nam_hoc->fetchColumn() ?: '';

        $stmt = $db->prepare("SELECT nhom_vi_pham, ten_vi_pham, diem_tru FROM raw_cau_hinh_vi_pham WHERE nam_hoc_id = ? ORDER BY nhom_vi_pham ASC, ten_vi_pham ASC");
        $stmt->execute([$current_nam_hoc]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $admin_name = $_SESSION['user_ho_ten'] ?? 'Quản trị viên';
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // --- ĐỊNH DẠNG CHUNG ---
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman')->setSize(12);
        $sheet->getColumnDimension('A')->setWidth(5);   // STT
        $sheet->getColumnDimension('B')->setWidth(25);  // Nhóm
        $sheet->getColumnDimension('C')->setWidth(70);  // Tên Vi Phạm
        $sheet->getColumnDimension('D')->setWidth(15);  // Điểm Trừ

        $sheet->mergeCells('A1:B1')->setCellValue('A1', 'ĐOÀN TRƯỜNG THPT BÌNH SƠN');
        $sheet->getStyle('A1')->getFont()->setSize(11);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:B2')->setCellValue('A2', 'HỆ THỐNG ĐÁNH GIÁ THI ĐUA');
        $sheet->getStyle('A2')->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->mergeCells('A3:D3')->setCellValue('A3', 'DANH SÁCH VI PHẠM HIỂN THỊ TRÊN PHẦN MỀM - ' . $nam_hoc_hien_tai);
        $sheet->getStyle('A3')->getFont()->setSize(13)->setBold(true);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // --- BẢNG DỮ LIỆU ---
        $header_row = 5; 
        $data_row_start = $header_row + 1;
        
        $headers = ['STT', 'Nhóm', 'Tên Nhóm Vi Phạm', 'Điểm Trừ'];
        $sheet->fromArray($headers, NULL, 'A' . $header_row);
        $sheet->getStyle('A'.$header_row.':D'.$header_row)->getFont()->setBold(true);
        $sheet->getStyle('A'.$header_row.':D'.$header_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $rowIndex = $data_row_start;
        foreach ($data as $index => $vp) {
            $rowData = [
                $index + 1,
                $vp['nhom_vi_pham'] ?? '',
                $vp['ten_vi_pham'],
                $vp['diem_tru']
            ];
            $sheet->fromArray($rowData, NULL, 'A' . $rowIndex);
            $rowIndex++;
        }

        $last_row = $rowIndex - 1;
        if ($last_row >= $data_row_start) {
            $styleArray = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => '00000000'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ]
            ];
            $sheet->getStyle('A'.$header_row.':D'.$last_row)->applyFromArray($styleArray);
            $sheet->getStyle('A'.$data_row_start.':A'.$last_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D'.$data_row_start.':D'.$last_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $footer_row = $last_row + 2;
        $sheet->mergeCells('C'.$footer_row.':D'.$footer_row);
        $sheet->setCellValue('C'.$footer_row, 'Long Thành, ngày ' . date('d') . ' tháng ' . date('m') . ' năm ' . date('Y'));
        $sheet->getStyle('C'.$footer_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C'.$footer_row)->getFont()->setItalic(true);

        $footer_row++;
        $sheet->mergeCells('C'.$footer_row.':D'.$footer_row);
        $sheet->setCellValue('C'.$footer_row, 'NGƯỜI XUẤT');
        $sheet->getStyle('C'.$footer_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C'.$footer_row)->getFont()->setBold(true);

        $footer_row += 4;
        $sheet->mergeCells('C'.$footer_row.':D'.$footer_row);
        $sheet->setCellValue('C'.$footer_row, $admin_name);
        $sheet->getStyle('C'.$footer_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C'.$footer_row)->getFont()->setBold(true);

        $filename = "Danh_Sach_Vi_Pham_" . date('Ymd_His') . ".xlsx";
        setcookie("fileDownloadToken", "success", ["expires" => time() + 20, "path" => "/", "samesite" => "Strict"]);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        ob_clean();
        flush();
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();

    default:
        // Nếu không khớp đường dẫn nào, về trang danh sách
        header('Location: /thidua/admin/cau-hinh-vi-pham');
        exit();
}
