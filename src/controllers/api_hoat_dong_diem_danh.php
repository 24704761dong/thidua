<?php
// File: src/controllers/api_hoat_dong_diem_danh.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || ($_SESSION['user_vai_tro'] !== 'admin' && !in_array('quan_ly_hoat_dong', $_SESSION['user_permissions'] ?? []) && !in_array('all', $_SESSION['user_permissions'] ?? []))) {
    if (!defined('IS_PUBLIC_SCAN') || !IS_PUBLIC_SCAN) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }
}
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../lib/helpers.php';
$db = get_db_connection();

// Handle GET export
if (isset($_GET['action']) && $_GET['action'] === 'export') {
    $hoat_dong_id = (int)($_GET['hoat_dong_id'] ?? 0);
    
    $stmt = $db->prepare("
        SELECT dk.*, TRIM(CONCAT(COALESCE(hs.ho_dem, ''), ' ', COALESCE(hs.ten, ''))) as ho_ten, l.ten_lop as lop, hs.ma_hoc_sinh as cccd
        FROM hoat_dong_dang_ky dk
        JOIN hoc_sinh hs ON dk.hoc_sinh_id = hs.id
        LEFT JOIN lop_hoc l ON hs.lop_hoc_id = l.id
        WHERE dk.hoat_dong_id = ?
        ORDER BY l.ten_lop, hs.ten, hs.ho_dem
    ");
    $stmt->execute([$hoat_dong_id]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get hoat dong info for title
    $stmt_hd = $db->prepare("SELECT ten_hoat_dong FROM hoat_dong WHERE id = ?");
    $stmt_hd->execute([$hoat_dong_id]);
    $hd = $stmt_hd->fetch(PDO::FETCH_ASSOC);
    $ten_hoat_dong = $hd ? $hd['ten_hoat_dong'] : 'Danh sách điểm danh';

    require_once __DIR__ . '/../../vendor/autoload.php';
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Điểm danh');

    // Title
    $sheet->setCellValue('A1', mb_strtoupper($ten_hoat_dong, 'UTF-8'));
    $sheet->mergeCells('A1:G1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // Header
    $sheet->setCellValue('A3', 'STT');
    $sheet->setCellValue('B3', 'Lớp');
    $sheet->setCellValue('C3', 'Họ và tên');
    $sheet->setCellValue('D3', 'CCCD');
    $sheet->setCellValue('E3', 'Trạng thái đánh giá');
    $sheet->setCellValue('F3', 'Điểm');
    $sheet->setCellValue('G3', 'Phương thức');

    $sheet->getStyle('A3:G3')->getFont()->setBold(true);
    $sheet->getStyle('A3:G3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFEFEFEF');
    $sheet->getStyle('A3:G3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A3:G3')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

    $stt = 1;
    $row = 4;
    foreach ($data as $r) {
        $trang_thai_text = '';
        if ($r['trang_thai_diem_danh'] == 1) $trang_thai_text = 'Tham gia (+100%)';
        elseif ($r['trang_thai_diem_danh'] == 2) $trang_thai_text = 'Tham gia (+50%)';
        else $trang_thai_text = 'Không tham gia (0đ)';

        $sheet->setCellValue('A'.$row, $stt++);
        $sheet->setCellValue('B'.$row, $r['lop']);
        $sheet->setCellValue('C'.$row, $r['ho_ten']);
        $sheet->setCellValueExplicit('D'.$row, $r['cccd'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('E'.$row, $trang_thai_text);
        $sheet->setCellValue('F'.$row, $r['diem_thuc_te']);
        $sheet->setCellValue('G'.$row, $r['phuong_thuc_diem_danh'] == 'QR' ? 'Quét mã' : ($r['phuong_thuc_diem_danh'] == 'Thủ công' ? 'Thủ công' : ''));
        
        $sheet->getStyle("A{$row}:G{$row}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $row++;
    }

    foreach (range('A', 'G') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="Danh_sach_diem_danh_' . $hoat_dong_id . '.xlsx"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit();
}

header('Content-Type: application/json');
if (!isset($data) || empty($data['action'])) {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true) ?? $_POST;
}
$action = $data['action'] ?? '';

try {
    if ($action === 'list') {
        $hoat_dong_id = (int)($data['hoat_dong_id'] ?? 0);
        $stmt = $db->prepare("
            SELECT dk.id, dk.trang_thai_diem_danh, dk.diem_thuc_te, dk.phuong_thuc, 
                   TRIM(CONCAT(COALESCE(hs.ho_dem, ''), ' ', COALESCE(hs.ten, ''))) as ho_ten, hs.ma_hoc_sinh as cccd, l.ten_lop as lop
            FROM hoat_dong_dang_ky dk
            JOIN hoc_sinh hs ON dk.hoc_sinh_id = hs.id
            LEFT JOIN lop_hoc l ON hs.lop_hoc_id = l.id
            WHERE dk.hoat_dong_id = ?
            ORDER BY l.ten_lop ASC, hs.ten ASC, hs.ho_dem ASC
        ");
        $stmt->execute([$hoat_dong_id]);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
    }
    elseif ($action === 'scan_qr') {
        $hoat_dong_id = (int)($data['hoat_dong_id'] ?? 0);
        $cccd = trim($data['cccd'] ?? '');
        
        if (empty($cccd)) {
            echo json_encode(['success' => false, 'message' => 'Mã QR không hợp lệ']);
            exit;
        }

        // Lấy thông tin hoạt động
        $stmtHd = $db->prepare("SELECT diem_tich_luy, ten_hoat_dong FROM hoat_dong WHERE id = ?");
        $stmtHd->execute([$hoat_dong_id]);
        $hoat_dong = $stmtHd->fetch();
        if (!$hoat_dong) {
            echo json_encode(['success' => false, 'message' => 'Hoạt động không tồn tại']);
            exit;
        }

        // Tìm học sinh theo CCCD
        $stmtHs = $db->prepare("SELECT hs.id, TRIM(CONCAT(COALESCE(hs.ho_dem, ''), ' ', COALESCE(hs.ten, ''))) as ho_ten, l.ten_lop FROM hoc_sinh hs LEFT JOIN lop_hoc l ON hs.lop_hoc_id = l.id WHERE hs.ma_hoc_sinh = ?");
        $stmtHs->execute([$cccd]);
        $hs = $stmtHs->fetch();
        
        if (!$hs) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy học sinh với CCCD này']);
            exit;
        }
        
        $hoc_sinh_id = $hs['id'];
        $ten_lop = $hs['ten_lop'] ?? 'Chưa xếp lớp';
        
        // Kiểm tra xem đã đăng ký chưa
        $stmtCheck = $db->prepare("SELECT id, trang_thai_diem_danh FROM hoat_dong_dang_ky WHERE hoat_dong_id = ? AND hoc_sinh_id = ?");
        $stmtCheck->execute([$hoat_dong_id, $hoc_sinh_id]);
        $exist = $stmtCheck->fetch();
        
        $diem_goc = (float)$hoat_dong['diem_tich_luy'];
        $diem_thuc_te = $diem_goc;

        if ($exist) {
            if ($exist['trang_thai_diem_danh'] == 1) {
                echo json_encode(['success' => false, 'error_type' => 'already_scanned', 'message' => $hs['ho_ten'] . ' đã điểm danh trước đó rồi']);
                exit;
            }
            
            // Cập nhật trạng thái điểm danh
            $stmtUpd = $db->prepare("UPDATE hoat_dong_dang_ky SET trang_thai_diem_danh = 1, diem_thuc_te = ?, phuong_thuc = 'qr' WHERE id = ?");
            $stmtUpd->execute([$diem_thuc_te, $exist['id']]);
            
            // Add notification
            $noi_dung = "Bạn đã được điểm danh tham gia hoạt động " . $hoat_dong['ten_hoat_dong'];
            create_student_notification($db, $hoc_sinh_id, 'Điểm danh thành công', $noi_dung, 'hoat_dong');

            // Email
            $stmtEmail = $db->prepare("SELECT email FROM hoc_sinh WHERE id = ?");
            $stmtEmail->execute([$hoc_sinh_id]);
            $hsEmail = $stmtEmail->fetchColumn();
            if ($hsEmail && filter_var($hsEmail, FILTER_VALIDATE_EMAIL)) {
                queue_email($hsEmail, $hs['ho_ten'], "Điểm danh hoạt động thành công", "Chào bạn, bạn đã được điểm danh tham gia hoạt động " . $hoat_dong['ten_hoat_dong'] . " thành công.");
            }
            
            echo json_encode(['success' => true, 'message' => 'Học sinh ' . $hs['ho_ten'] . ' - Lớp ' . $ten_lop . ' đã điểm danh thành công']);
        } else {
            echo json_encode(['success' => false, 'error_type' => 'not_registered', 'message' => 'Học sinh này chưa đăng ký tham gia, bạn có muốn thêm vào danh sách không?']);
            exit;
        }
    }
    elseif ($action === 'add_and_scan_qr') {
        $hoat_dong_id = (int)($data['hoat_dong_id'] ?? 0);
        $cccd = trim($data['cccd'] ?? '');
        
        // Lấy thông tin hoạt động
        $stmtHd = $db->prepare("SELECT diem_tich_luy, ten_hoat_dong FROM hoat_dong WHERE id = ?");
        $stmtHd->execute([$hoat_dong_id]);
        $hoat_dong = $stmtHd->fetch();
        
        // Tìm học sinh theo CCCD
        $stmtHs = $db->prepare("SELECT hs.id, TRIM(CONCAT(COALESCE(hs.ho_dem, ''), ' ', COALESCE(hs.ten, ''))) as ho_ten, l.ten_lop FROM hoc_sinh hs LEFT JOIN lop_hoc l ON hs.lop_hoc_id = l.id WHERE hs.ma_hoc_sinh = ?");
        $stmtHs->execute([$cccd]);
        $hs = $stmtHs->fetch();
        
        $diem_goc = (float)$hoat_dong['diem_tich_luy'];
        $ten_lop = $hs['ten_lop'] ?? 'Chưa xếp lớp';
        
        // Thêm mới
        $stmtIns = $db->prepare("INSERT INTO hoat_dong_dang_ky (hoat_dong_id, hoc_sinh_id, trang_thai_diem_danh, diem_thuc_te, phuong_thuc) VALUES (?, ?, 1, ?, 'qr')");
        $stmtIns->execute([$hoat_dong_id, $hs['id'], $diem_goc]);
        
        // Add notification
        $noi_dung = "Bạn đã được điểm danh tham gia hoạt động " . $hoat_dong['ten_hoat_dong'];
        create_student_notification($db, $hs['id'], 'Điểm danh thành công', $noi_dung, 'hoat_dong');

        // Email
        $stmtEmail = $db->prepare("SELECT email FROM hoc_sinh WHERE id = ?");
        $stmtEmail->execute([$hs['id']]);
        $hsEmail = $stmtEmail->fetchColumn();
        if ($hsEmail && filter_var($hsEmail, FILTER_VALIDATE_EMAIL)) {
            queue_email($hsEmail, $hs['ho_ten'], "Điểm danh hoạt động thành công", "Chào bạn, bạn đã được điểm danh tham gia hoạt động " . $hoat_dong['ten_hoat_dong'] . " thành công.");
        }
        
        echo json_encode(['success' => true, 'message' => 'Học sinh ' . $hs['ho_ten'] . ' - Lớp ' . $ten_lop . ' đã điểm danh thành công']);
    }
    elseif ($action === 'update_status') {
        $id = (int)($data['id'] ?? 0);
        $status = (int)($data['trang_thai'] ?? 0);
        
        // Lấy thông tin đăng ký
        $stmt = $db->prepare("SELECT dk.*, hd.diem_tich_luy, hd.ten_hoat_dong FROM hoat_dong_dang_ky dk JOIN hoat_dong hd ON dk.hoat_dong_id = hd.id WHERE dk.id = ?");
        $stmt->execute([$id]);
        $dk = $stmt->fetch();
        
        if ($dk) {
            $diem_goc = (float)$dk['diem_tich_luy'];
            $diem_thuc_te = 0;
            
            if ($status == 1) $diem_thuc_te = $diem_goc;
            elseif ($status == 2) $diem_thuc_te = 0;
            elseif ($status == 3) $diem_thuc_te = $diem_goc > 0 ? -($diem_goc * 0.5) : ($diem_goc * 0.5); 
            elseif ($status == 4) $diem_thuc_te = $diem_goc > 0 ? -$diem_goc : $diem_goc;
            elseif ($status == 5) $diem_thuc_te = $diem_goc > 0 ? -($diem_goc * 2) : ($diem_goc * 2);

            $stmtUpd = $db->prepare("UPDATE hoat_dong_dang_ky SET trang_thai_diem_danh = ?, diem_thuc_te = ? WHERE id = ?");
            $stmtUpd->execute([$status, $diem_thuc_te, $id]);
            
            // Notification
            $status_text = '';
            if ($status == 1 || $status == 2) $status_text = 'tham gia';
            else $status_text = 'vắng mặt hoặc vi phạm';
            
            $noi_dung = "Trạng thái của bạn tại hoạt động " . $dk['ten_hoat_dong'] . " đã được cập nhật thành: " . $status_text;
            create_student_notification($db, $dk['hoc_sinh_id'], 'Cập nhật điểm danh', $noi_dung, 'hoat_dong');

            // Email
            $stmtHs = $db->prepare("SELECT email, TRIM(CONCAT(COALESCE(ho_dem, ''), ' ', COALESCE(ten, ''))) as ho_ten FROM hoc_sinh WHERE id = ?");
            $stmtHs->execute([$dk['hoc_sinh_id']]);
            $hs = $stmtHs->fetch();
            if ($hs && $hs['email'] && filter_var($hs['email'], FILTER_VALIDATE_EMAIL)) {
                $email_content = "Chào bạn,\n\nTrạng thái của bạn tại hoạt động " . $dk['ten_hoat_dong'] . " đã được cập nhật thành: " . $status_text . ".\n\nĐiểm thực tế của bạn tại hoạt động này là: " . $diem_thuc_te . "đ.";
                queue_email($hs['email'], $hs['ho_ten'], "Cập nhật kết quả hoạt động", $email_content);
            }
            
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy dữ liệu']);
        }
    }
    elseif ($action === 'delete') {
        $id = (int)($data['id'] ?? 0);
        $stmt = $db->prepare("DELETE FROM hoat_dong_dang_ky WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
    }
    elseif ($action === 'import_targets') {
        $hoat_dong_id = (int)($data['hoat_dong_id'] ?? 0);
        $targets_str = $data['targets'] ?? 'Tất cả';
        
        $where_clauses = [];
        $params = [];
        
        $arr = array_map('trim', explode(',', $targets_str));
        
        if (in_array('Tất cả', $arr)) {
            $where_clauses[] = "1=1";
        } else {
            foreach ($arr as $t) {
                if ($t === 'Khối 10' || $t === 'Khối 11' || $t === 'Khối 12') {
                    $khoi = str_replace('Khối ', '', $t);
                    $where_clauses[] = "l.ten_lop LIKE ?";
                    $params[] = $khoi . '%';
                } else {
                    $where_clauses[] = "l.ten_lop = ?";
                    $params[] = $t;
                    $where_clauses[] = "hs.chuc_vu = ?";
                    $params[] = $t;
                }
            }
        }
        
        if (count($where_clauses) > 0) {
            $where = implode(' OR ', $where_clauses);
            $sql = "SELECT hs.id FROM hoc_sinh hs LEFT JOIN lop_hoc l ON hs.lop_hoc_id = l.id WHERE hs.trang_thai_hoc_tap = 'dang_hoc' AND ($where)";
            $stmtHs = $db->prepare($sql);
            $stmtHs->execute($params);
            $hsList = $stmtHs->fetchAll();
            
            $count = 0;
            foreach($hsList as $hs) {
                $stmtCheck = $db->prepare("SELECT id FROM hoat_dong_dang_ky WHERE hoat_dong_id = ? AND hoc_sinh_id = ?");
                $stmtCheck->execute([$hoat_dong_id, $hs['id']]);
                if (!$stmtCheck->fetch()) {
                    $stmtIns = $db->prepare("INSERT INTO hoat_dong_dang_ky (hoat_dong_id, hoc_sinh_id, trang_thai_diem_danh, diem_thuc_te, phuong_thuc) VALUES (?, ?, 0, 0, 'thu_cong')");
                    $stmtIns->execute([$hoat_dong_id, $hs['id']]);
                    $count++;
                }
            }
            echo json_encode(['success' => true, 'message' => "Đã thêm thành công $count học sinh vào danh sách"]);
        } else {
            echo json_encode(['success' => false, 'message' => "Vui lòng chọn ít nhất 1 đối tượng"]);
        }
    }
    elseif ($action === 'import_cccd_list') {
        $hoat_dong_id = (int)($data['hoat_dong_id'] ?? 0);
        $cccd_list = explode("\n", str_replace("\r", "", $data['cccd_list'] ?? ''));
        
        $count = 0;
        $not_found = [];
        foreach($cccd_list as $cccd) {
            $cccd = trim($cccd);
            if (empty($cccd)) continue;
            
            $stmtHs = $db->prepare("SELECT id FROM hoc_sinh WHERE ma_hoc_sinh = ?");
            $stmtHs->execute([$cccd]);
            $hs = $stmtHs->fetch();
            
            if ($hs) {
                $stmtCheck = $db->prepare("SELECT id FROM hoat_dong_dang_ky WHERE hoat_dong_id = ? AND hoc_sinh_id = ?");
                $stmtCheck->execute([$hoat_dong_id, $hs['id']]);
                if (!$stmtCheck->fetch()) {
                    $stmtIns = $db->prepare("INSERT INTO hoat_dong_dang_ky (hoat_dong_id, hoc_sinh_id, trang_thai_diem_danh, diem_thuc_te, phuong_thuc) VALUES (?, ?, 0, 0, 'thu_cong')");
                    $stmtIns->execute([$hoat_dong_id, $hs['id']]);
                    $count++;
                }
            } else {
                $not_found[] = $cccd;
            }
        }
        
        $msg = "Đã thêm thành công $count học sinh.";
        if (count($not_found) > 0) {
            $msg .= " Không tìm thấy " . count($not_found) . " mã CCCD: " . implode(', ', array_slice($not_found, 0, 3)) . (count($not_found) > 3 ? '...' : '');
        }
        echo json_encode(['success' => true, 'message' => $msg]);
    }
    elseif ($action === 'generate_scan_link') {
        $hoat_dong_id = (int)($data['hoat_dong_id'] ?? 0);
        $password = $data['password'] ?? '';
        
        $token = bin2hex(random_bytes(16));
        $hashed_password = empty($password) ? null : password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("UPDATE hoat_dong SET scan_token = ?, scan_password = ? WHERE id = ?");
        $stmt->execute([$token, $hashed_password, $hoat_dong_id]);
        
        $link = "/thidua/public-scan?token=" . $token;
        echo json_encode(['success' => true, 'link' => $link]);
    }
    elseif ($action === 'delete_scan_link') {
        $hoat_dong_id = (int)($data['hoat_dong_id'] ?? 0);
        $stmt = $db->prepare("UPDATE hoat_dong SET scan_token = NULL, scan_password = NULL WHERE id = ?");
        $stmt->execute([$hoat_dong_id]);
        echo json_encode(['success' => true]);
    }
    elseif ($action === 'get_scan_link') {
        $hoat_dong_id = (int)($data['hoat_dong_id'] ?? 0);
        $stmt = $db->prepare("SELECT scan_token FROM hoat_dong WHERE id = ?");
        $stmt->execute([$hoat_dong_id]);
        $hd = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($hd && $hd['scan_token']) {
            $link = "/thidua/public-scan?token=" . $hd['scan_token'];
            echo json_encode(['success' => true, 'link' => $link]);
        } else {
            echo json_encode(['success' => false]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
