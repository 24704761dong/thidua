<?php
if (function_exists('opcache_invalidate')) { opcache_invalidate(__FILE__, true); }
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$action = $_GET['action'] ?? 'index';
$db = get_db_connection();
$current_nam_hoc = $_SESSION['current_nam_hoc_id'] ?? 1;

switch ($action) {
    case 'index':
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['page_size']) ? (int)$_GET['page_size'] : 100;
        $offset = ($page - 1) * $limit;
        $keyword = trim($_GET['keyword'] ?? '');

        $where = "1=1";
        $params = [];

        if ($keyword !== '') {
            $where .= " AND (g.ho_ten LIKE ? OR g.cccd LIKE ? OR g.email LIKE ? OR g.sdt LIKE ?)";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
        }

        $count_stmt = $db->prepare("SELECT COUNT(*) FROM giao_vien g WHERE $where");
        $count_stmt->execute($params);
        $total_records = $count_stmt->fetchColumn();
        $total_pages = ceil($total_records / $limit);

        // Fetch teachers + account status + string of all assigned classes
        $stmt = $db->prepare("
            SELECT g.*, 
                   IF(g.mat_khau_hash IS NOT NULL AND g.mat_khau_hash != '', g.id, NULL) as account_id,
                   IF(g.avatar IS NOT NULL AND g.avatar != '', g.avatar, '/thidua/public/assets/img/anhthegoc.JPG') as final_avatar,
                   (
                       SELECT GROUP_CONCAT(CONCAT(l.ten_lop, ' (', n.ten_nam_hoc, ')') ORDER BY n.id DESC SEPARATOR ', ')
                       FROM raw_lop_hoc l 
                       JOIN nam_hoc n ON l.nam_hoc_id = n.id 
                       WHERE l.giao_vien_id = g.id
                   ) as cac_lop_chu_nhiem
            FROM giao_vien g 
            WHERE $where 
            ORDER BY SUBSTRING_INDEX(g.ho_ten, ' ', -1) ASC, g.ho_ten ASC 
            LIMIT $limit OFFSET $offset
        ");
        $stmt->execute($params);
        $danh_sach_giao_vien = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $danh_sach_giao_vien,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $total_pages,
                    'total_records' => $total_records
                ]
            ]);
            exit;
        }

        require __DIR__ . '/../views/quan_ly_giao_vien.php';
        break;

    case 'api_save':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            $id = $_POST['id'] ?? '';
            $cccd = trim($_POST['cccd'] ?? '');
            $sdt = trim($_POST['sdt'] ?? '');
            $ho_ten = trim($_POST['ho_ten'] ?? '');
            $ngay_sinh = trim($_POST['ngay_sinh'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $ghi_chu = trim($_POST['ghi_chu'] ?? '');

            if (empty($ho_ten)) {
                echo json_encode(['success' => false, 'message' => 'Họ tên không được để trống']);
                exit;
            }

            // Handle Avatar Upload
            $avatar_path = null;
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/../../public/uploads/avatars/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                $filename = 'gv_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                $target_path = $upload_dir . $filename;
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target_path)) {
                    $avatar_path = '/thidua/public/uploads/avatars/' . $filename;
                }
            }

            try {
                if (empty($id)) {
                    $sql = "INSERT INTO giao_vien (cccd, sdt, ho_ten, ngay_sinh, email, ghi_chu" . ($avatar_path ? ", avatar" : "") . ") VALUES (?, ?, ?, ?, ?, ?" . ($avatar_path ? ", ?" : "") . ")";
                    $params = [empty($cccd)?null:$cccd, empty($sdt)?null:$sdt, $ho_ten, $ngay_sinh, $email, $ghi_chu];
                    if ($avatar_path) $params[] = $avatar_path;
                    $db->prepare($sql)->execute($params);
                } else {
                    $sql = "UPDATE giao_vien SET cccd = ?, sdt = ?, ho_ten = ?, ngay_sinh = ?, email = ?, ghi_chu = ?";
                    $params = [empty($cccd)?null:$cccd, empty($sdt)?null:$sdt, $ho_ten, $ngay_sinh, $email, $ghi_chu];
                    if ($avatar_path) {
                        $sql .= ", avatar = ?";
                        $params[] = $avatar_path;
                    }
                    $sql .= " WHERE id = ?";
                    $params[] = $id;
                    $db->prepare($sql)->execute($params);
                    
                    // (Lược bỏ sync sang bảng users vì tài khoản đã chuyển về bảng giao_vien)
                }
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
        }
        break;

    case 'api_phan_lop_data':
        header('Content-Type: application/json');
        try {
            $stmt_l = $db->prepare("SELECT id, ten_lop, giao_vien_id FROM raw_lop_hoc WHERE nam_hoc_id = ? ORDER BY ten_lop ASC");
            $stmt_l->execute([$current_nam_hoc]);
            $classes = $stmt_l->fetchAll(PDO::FETCH_ASSOC);

            $stmt_g = $db->prepare("SELECT id, ho_ten FROM giao_vien ORDER BY ho_ten ASC");
            $stmt_g->execute();
            $teachers = $stmt_g->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'classes' => $classes, 'teachers' => $teachers]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;

    case 'api_save_phan_lop':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            $data = json_decode(file_get_contents('php://input'), true);
            $assignments = $data['assignments'] ?? []; // Array of {lop_id: 1, giao_vien_id: 2}

            try {
                $db->beginTransaction();
                foreach ($assignments as $a) {
                    $lop_id = $a['lop_id'];
                    $gv_id = !empty($a['giao_vien_id']) ? $a['giao_vien_id'] : null;
                    
                    // Kiểm tra giáo viên này có đang chủ nhiệm lớp khác trong năm học này không? 
                    // Tạm thời cho phép cập nhật đè (1 GV có thể chủ nhiệm nhiều lớp hoặc hệ thống sẽ reset lớp cũ nếu thiết kế bắt buộc 1-1). 
                    // Theo quy trình: cứ gán giáo viên vào lớp đó.
                    
                    $stmt_set = $db->prepare("UPDATE raw_lop_hoc SET giao_vien_id = ? WHERE id = ? AND nam_hoc_id = ?");
                    $stmt_set->execute([$gv_id, $lop_id, $current_nam_hoc]);
                }
                $db->commit();
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                $db->rollBack();
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
        }
        break;

    case 'api_bulk_delete':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            $data = json_decode(file_get_contents('php://input'), true);
            $ids = $data['ids'] ?? [];
            if (is_array($ids) && count($ids) > 0) {
                try {
                    $db->beginTransaction();
                    $placeholders = implode(',', array_fill(0, count($ids), '?'));
                    
                    // Tìm user_ids để xóa
                    $stmt_u = $db->prepare("SELECT user_id FROM giao_vien WHERE id IN ($placeholders) AND user_id IS NOT NULL");
                    $stmt_u->execute($ids);
                    $user_ids = $stmt_u->fetchAll(PDO::FETCH_COLUMN);
                    
                    if (count($user_ids) > 0) {
                        $u_placeholders = implode(',', array_fill(0, count($user_ids), '?'));
                        $db->prepare("DELETE FROM users WHERE id IN ($u_placeholders)")->execute($user_ids);
                    }
                    
                    $db->prepare("UPDATE raw_lop_hoc SET giao_vien_id = NULL WHERE giao_vien_id IN ($placeholders)")->execute($ids);
                    $db->prepare("DELETE FROM giao_vien WHERE id IN ($placeholders)")->execute($ids);
                    
                    $db->commit();
                    echo json_encode(['success' => true, 'message' => 'Đã xóa các giáo viên được chọn.']);
                } catch (Exception $e) {
                    $db->rollBack();
                    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Vui lòng chọn giáo viên']);
            }
            exit;
        }
        break;

    case 'api_bulk_create_account':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            $data = json_decode(file_get_contents('php://input'), true);
            $ids = $data['ids'] ?? [];
            if (!is_array($ids) || count($ids) === 0) {
                echo json_encode(['success' => false, 'message' => 'Chưa chọn giáo viên nào.']);
                exit;
            }
            
            $success_count = 0;
            $fail_count = 0;
            $fail_reasons = [];

            try {
                $db->beginTransaction();
                foreach ($ids as $id) {
                    $stmt = $db->prepare("SELECT * FROM giao_vien WHERE id = ?");
                    $stmt->execute([$id]);
                    $gv = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$gv) { $fail_count++; continue; }
                    if (!empty($gv['mat_khau_hash'])) { $fail_count++; continue; } // Đã có tài khoản
                    if (empty($gv['cccd'])) { $fail_count++; $fail_reasons[] = $gv['ho_ten'] . " (Thiếu CCCD)"; continue; }
                    if (empty($gv['ngay_sinh'])) { $fail_count++; $fail_reasons[] = $gv['ho_ten'] . " (Thiếu Ngày sinh)"; continue; }

                    $password_raw = str_replace('/', '', $gv['ngay_sinh']);
                    $password_hash = password_hash($password_raw, PASSWORD_DEFAULT);

                    // Cập nhật record giáo viên trực tiếp
                    $db->prepare("UPDATE giao_vien SET mat_khau_hash = ?, trang_thai_tai_khoan = 'Hoạt động' WHERE id = ?")->execute([$password_hash, $id]);
                    $success_count++;
                }
                $db->commit();
                
                $msg = "Đã tạo tài khoản cho $success_count giáo viên.";
                if ($fail_count > 0) $msg .= " Lỗi $fail_count trường hợp: " . implode(", ", array_slice($fail_reasons, 0, 3)) . (count($fail_reasons)>3 ? "..." : "");
                echo json_encode(['success' => true, 'message' => $msg]);
            } catch (Exception $e) {
                if ($db->inTransaction()) $db->rollBack();
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
        }
        break;

    case 'api_bulk_reset_password':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            $data = json_decode(file_get_contents('php://input'), true);
            $ids = $data['ids'] ?? [];
            if (!is_array($ids) || count($ids) === 0) {
                echo json_encode(['success' => false, 'message' => 'Chưa chọn giáo viên nào.']);
                exit;
            }

            try {
                $success = 0;
                $db->beginTransaction();
                foreach ($ids as $id) {
                    $stmt = $db->prepare("SELECT ngay_sinh FROM giao_vien WHERE id = ?");
                    $stmt->execute([$id]);
                    $ns = $stmt->fetchColumn();
                    if ($ns) {
                        $password_raw = str_replace('/', '', $ns);
                        $password_hash = password_hash($password_raw, PASSWORD_DEFAULT);
                        $db->prepare("UPDATE giao_vien SET mat_khau_hash = ? WHERE id = ?")->execute([$password_hash, $id]);
                        $success++;
                    }
                }
                $db->commit();
                echo json_encode(['success' => true, 'message' => "Đã khôi phục mật khẩu thành công cho $success giáo viên."]);
            } catch (Exception $e) {
                $db->rollBack();
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
        }
        break;

    case 'export_excel':
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Danh_Sach_Giao_Vien');
        
        $headers = ['STT', 'SỐ CCCD', 'HỌ VÀ TÊN', 'NGÀY SINH', 'SĐT', 'EMAIL', 'CÁC LỚP ĐÃ CHỦ NHIỆM', 'GHI CHÚ'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getStyle($col . '1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9E1F2');
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        $keyword = trim($_GET['keyword'] ?? '');
        $where = "1=1";
        $params = [];
        if ($keyword !== '') {
            $where .= " AND (g.ho_ten LIKE ? OR g.cccd LIKE ? OR g.email LIKE ? OR g.sdt LIKE ?)";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
        }

        $stmt = $db->prepare("
            SELECT g.*, 
                   (
                       SELECT GROUP_CONCAT(CONCAT(l.ten_lop, ' (', n.ten_nam_hoc, ')') ORDER BY n.id DESC SEPARATOR ', ')
                       FROM raw_lop_hoc l 
                       JOIN nam_hoc n ON l.nam_hoc_id = n.id 
                       WHERE l.giao_vien_id = g.id
                   ) as cac_lop_chu_nhiem
            FROM giao_vien g 
            WHERE $where 
            ORDER BY SUBSTRING_INDEX(g.ho_ten, ' ', -1) ASC, g.ho_ten ASC 
        ");
        $stmt->execute($params);
        $ds_gv = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $rowNum = 2;
        foreach ($ds_gv as $index => $gv) {
            $sheet->setCellValue('A' . $rowNum, $index + 1);
            $sheet->setCellValueExplicit('B' . $rowNum, $gv['cccd'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $rowNum, $gv['ho_ten']);
            $sheet->setCellValueExplicit('D' . $rowNum, $gv['ngay_sinh'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('E' . $rowNum, $gv['sdt'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('F' . $rowNum, $gv['email']);
            $sheet->setCellValue('G' . $rowNum, $gv['cac_lop_chu_nhiem'] ?? '');
            $sheet->setCellValue('H' . $rowNum, $gv['ghi_chu'] ?? '');
            $rowNum++;
        }

        $filename = "Danh_Sach_Giao_Vien_" . date('Ymd_His') . ".xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;

    // ... [download_import_template & import routes unchanged, keeping them intact for context but truncated here to save space]
    // Due to context limits, I will retain the old ones properly.
    case 'download_import_template':
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data');
        
        $headers = ['STT', 'CCCD', 'SĐT', 'HỌ TÊN', 'NGÀY SINH', 'EMAIL', 'GHI CHÚ'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getStyle($col . '1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9E1F2');
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }
        
        $sheet->setCellValue('A2', '1');
        $sheet->setCellValue('B2', '012345678912');
        $sheet->setCellValueExplicit('C2', '0987654321', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('D2', 'Nguyễn Văn A');
        $sheet->setCellValue('E2', '15/08/1990');
        $sheet->setCellValue('F2', 'nguyenvana@gmail.com');
        $sheet->setCellValue('G2', 'Giáo viên mới');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Mau_Import_Giao_Vien.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;

    case 'import':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'Lỗi tải lên file.']);
                exit;
            }

            try {
                $inputFileName = $_FILES['file']['tmp_name'];
                $spreadsheet = IOFactory::load($inputFileName);
                $sheet = $spreadsheet->getActiveSheet();
                $highestRow = $sheet->getHighestDataRow();
                
                $count_new = 0; $count_update = 0;
                $db->beginTransaction();

                for ($row = 2; $row <= $highestRow; $row++) {
                    $cccd = trim($sheet->getCell('B' . $row)->getFormattedValue());
                    $sdt = trim($sheet->getCell('C' . $row)->getFormattedValue());
                    $ho_ten = trim($sheet->getCell('D' . $row)->getFormattedValue());
                    $ngay_sinh = trim($sheet->getCell('E' . $row)->getFormattedValue());
                    $email = trim($sheet->getCell('F' . $row)->getFormattedValue());
                    $ghi_chu = trim($sheet->getCell('G' . $row)->getFormattedValue());
                    if (empty($ho_ten)) continue;

                    $gv_id = null;
                    if (!empty($cccd)) {
                        $stmt_find = $db->prepare("SELECT id FROM giao_vien WHERE cccd = ? LIMIT 1");
                        $stmt_find->execute([$cccd]);
                        $gv_id = $stmt_find->fetchColumn();
                    }
                    if (!$gv_id && !empty($email)) {
                        $stmt_find = $db->prepare("SELECT id FROM giao_vien WHERE ho_ten = ? AND email = ? LIMIT 1");
                        $stmt_find->execute([$ho_ten, $email]);
                        $gv_id = $stmt_find->fetchColumn();
                    }

                    if ($gv_id) {
                        $db->prepare("UPDATE giao_vien SET cccd = ?, sdt = ?, ho_ten = ?, ngay_sinh = ?, email = ?, ghi_chu = ? WHERE id = ?")->execute([empty($cccd)?null:$cccd, empty($sdt)?null:$sdt, $ho_ten, $ngay_sinh, $email, $ghi_chu, $gv_id]);
                        $count_update++;
                    } else {
                        $db->prepare("INSERT INTO giao_vien (cccd, sdt, ho_ten, ngay_sinh, email, ghi_chu) VALUES (?, ?, ?, ?, ?, ?)")->execute([empty($cccd)?null:$cccd, empty($sdt)?null:$sdt, $ho_ten, $ngay_sinh, $email, $ghi_chu]);
                        $count_new++;
                    }
                }
                $db->commit();
                echo json_encode(['success' => true, 'message' => "Import thành công! Thêm mới $count_new, cập nhật $count_update giáo viên."]);
            } catch (Exception $e) {
                if ($db->inTransaction()) $db->rollBack();
                echo json_encode(['success' => false, 'message' => 'Lỗi xử lý file: ' . $e->getMessage()]);
            }
            exit;
        }
        break;
}
