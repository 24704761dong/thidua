<?php
// File: src/controllers/KhenThuongController.php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /thidua/tracuu');
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../lib/lop_hoc_db.php';

$action = $_GET['action'] ?? 'index';
$db = get_db_connection();
$current_nam_hoc = $_SESSION['current_nam_hoc_id'] ?? 1;

// Phân quyền: Đa số tính năng cần quyền admin/user.
if (!in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    // Chỉ cho phép xem nếu có quyền thấp hơn, hoặc từ chối hết
    header('Location: /thidua/tracuu');
    exit();
}

switch ($action) {
    case 'index':
        // Lấy danh sách khen thưởng cá nhân
        $stmt_cn = $db->prepare("
            SELECT kt.*, hs.ma_hoc_sinh, CONCAT(hs.ho_dem, ' ', hs.ten) as ho_ten, lh.ten_lop
            FROM khen_thuong kt
            JOIN quatrinh_hoc_tap qt ON kt.hoc_sinh_id = qt.id
            JOIN ho_so_hoc_sinh hs ON qt.ma_hoc_sinh = hs.ma_hoc_sinh
            JOIN raw_lop_hoc lh ON qt.lop_hoc_id = lh.id AND lh.nam_hoc_id = ?
            WHERE kt.loai = 'ca_nhan' AND kt.nam_hoc_id = ?
            ORDER BY kt.ngay_khen_thuong DESC, kt.id DESC
        ");
        $stmt_cn->execute([$current_nam_hoc, $current_nam_hoc]);
        $khen_thuong_ca_nhan = $stmt_cn->fetchAll();

        // Lấy danh sách khen thưởng tập thể
        $stmt_tt = $db->prepare("
            SELECT kt.*, lh.ten_lop
            FROM khen_thuong kt
            LEFT JOIN raw_lop_hoc lh ON kt.lop_hoc_id = lh.id AND lh.nam_hoc_id = ?
            WHERE kt.loai = 'tap_the' AND kt.nam_hoc_id = ?
            ORDER BY kt.ngay_khen_thuong DESC, kt.id DESC
        ");
        $stmt_tt->execute([$current_nam_hoc, $current_nam_hoc]);
        $khen_thuong_tap_the = $stmt_tt->fetchAll();

        // Lấy danh sách lớp và học sinh cho các modal
        $danh_sach_lop = $db->prepare("SELECT * FROM raw_lop_hoc WHERE nam_hoc_id = ? ORDER BY ten_lop ASC");
        $danh_sach_lop->execute([$current_nam_hoc]);
        $danh_sach_lop = $danh_sach_lop->fetchAll();

        $stmt_hs = $db->prepare("
            SELECT qt.id, hs.ho_dem, hs.ten, lh.ten_lop 
            FROM quatrinh_hoc_tap qt 
            JOIN ho_so_hoc_sinh hs ON qt.ma_hoc_sinh = hs.ma_hoc_sinh 
            JOIN raw_lop_hoc lh ON qt.lop_hoc_id = lh.id AND lh.nam_hoc_id = ?
            WHERE qt.nam_hoc_id = ?
            ORDER BY lh.ten_lop, hs.ten
        ");
        $stmt_hs->execute([$current_nam_hoc, $current_nam_hoc]);
        $danh_sach_hoc_sinh = $stmt_hs->fetchAll();
        
        require_once __DIR__ . '/../views/quan_ly_khen_thuong.php';
        break;

    case 'api_add':
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
            exit();
        }
        
        try {
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
            if (empty($data) && !empty($_POST)) {
                $data = $_POST; // Fallback in case it's submitted as form-data
            }
            
            $loai = $data['loai'] ?? '';
            $ngay_khen = !empty($data['ngay_khen_thuong']) ? $data['ngay_khen_thuong'] : null;
            $ten_khen_thuong = $data['ten_khen_thuong'] ?? '';
            $so_quyet_dinh = $data['so_quyet_dinh'] ?? '';
            $cap_khen_thuong = $data['cap_khen_thuong'] ?? '';
            $ghi_chu = $data['ghi_chu'] ?? '';
            
            if ($loai === 'ca_nhan') {
                $hs_id = !empty($data['hoc_sinh_id']) ? $data['hoc_sinh_id'] : null;
                $sql = "INSERT INTO khen_thuong (loai, hoc_sinh_id, ngay_khen_thuong, ten_khen_thuong, so_quyet_dinh, cap_khen_thuong, ghi_chu, nam_hoc_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $db->prepare($sql);
                $stmt->execute(['ca_nhan', $hs_id, $ngay_khen, $ten_khen_thuong, $so_quyet_dinh, $cap_khen_thuong, $ghi_chu, $current_nam_hoc]);
            } elseif ($loai === 'tap_the') {
                $lop_id = !empty($data['lop_hoc_id']) ? $data['lop_hoc_id'] : null;
                $ten_tap_the = $data['ten_tap_the'] ?? null;
                $sql = "INSERT INTO khen_thuong (loai, lop_hoc_id, ten_tap_the, ngay_khen_thuong, ten_khen_thuong, so_quyet_dinh, cap_khen_thuong, ghi_chu, nam_hoc_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $db->prepare($sql);
                $stmt->execute(['tap_the', $lop_id, $ten_tap_the, $ngay_khen, $ten_khen_thuong, $so_quyet_dinh, $cap_khen_thuong, $ghi_chu, $current_nam_hoc]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Loại khen thưởng không hợp lệ.']);
                exit();
            }
            
            echo json_encode(['success' => true, 'message' => 'Thêm khen thưởng thành công!']);
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
        }
        break;

    case 'api_edit':
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
            exit();
        }
        try {
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
            $id = isset($data['id']) ? (int)$data['id'] : 0;
            $loai = isset($data['loai']) ? trim($data['loai']) : '';
            $ten = isset($data['ten_khen_thuong']) ? trim($data['ten_khen_thuong']) : '';
            $so_qd = isset($data['so_quyet_dinh']) ? trim($data['so_quyet_dinh']) : '';
            $cap = isset($data['cap_khen_thuong']) ? trim($data['cap_khen_thuong']) : '';
            $ghi_chu = isset($data['ghi_chu']) ? trim($data['ghi_chu']) : '';
            
            // Normalize date logic
            $ngay = null;
            if (!empty($data['ngay_khen_thuong'])) {
                $str = trim((string)$data['ngay_khen_thuong']);
                $dt = DateTime::createFromFormat('Y-m-d', $str);
                if ($dt && $dt->format('Y-m-d') === $str) {
                    $ngay = $str;
                } else {
                    $dt2 = DateTime::createFromFormat('d/m/Y', $str);
                    if ($dt2 && $dt2->format('d/m/Y') === $str) {
                        $ngay = $dt2->format('Y-m-d');
                    }
                }
            }

            if (!$id || !in_array($loai, ['ca_nhan', 'tap_the'], true)) {
                echo json_encode(['success' => false, 'message' => 'Thiếu ID hoặc loại không hợp lệ.']);
                exit();
            }
            if ($ten === '') {
                echo json_encode(['success' => false, 'message' => 'Tên khen thưởng không được trống.']);
                exit();
            }

            $hoc_sinh_id = null;
            $lop_hoc_id = null;
            $ten_tap_the = null;

            if ($loai === 'ca_nhan') {
                $hoc_sinh_id = isset($data['hoc_sinh_id']) ? (int)$data['hoc_sinh_id'] : 0;
                if (!$hoc_sinh_id) {
                    echo json_encode(['success' => false, 'message' => 'Vui lòng chọn học sinh.']);
                    exit();
                }
            } else {
                $lop_hoc_id = isset($data['lop_hoc_id']) && $data['lop_hoc_id'] !== '' ? (int)$data['lop_hoc_id'] : null;
                $ten_tap_the = isset($data['ten_tap_the']) ? trim($data['ten_tap_the']) : null;
                if (!$lop_hoc_id && ($ten_tap_the === null || $ten_tap_the === '')) {
                    echo json_encode(['success' => false, 'message' => 'Vui lòng chọn lớp hoặc nhập tên tập thể.']);
                    exit();
                }
            }

            $sql = "UPDATE khen_thuong SET loai = ?, hoc_sinh_id = ?, lop_hoc_id = ?, ten_tap_the = ?, ngay_khen_thuong = ?, ten_khen_thuong = ?, so_quyet_dinh = ?, cap_khen_thuong = ?, ghi_chu = ? WHERE id = ? AND nam_hoc_id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$loai, $hoc_sinh_id, $lop_hoc_id, $ten_tap_the, $ngay, $ten, $so_qd, $cap, $ghi_chu, $id, $current_nam_hoc]);

            echo json_encode(['success' => true, 'message' => 'Đã cập nhật khen thưởng.']);
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
        }
        break;

    case 'api_delete':
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ']);
            exit();
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu ID.']);
            exit();
        }
        try {
            $stmt = $db->prepare("DELETE FROM khen_thuong WHERE id = ? AND nam_hoc_id = ?");
            $stmt->execute([$id, $current_nam_hoc]);
            echo json_encode(['success' => true, 'message' => 'Đã xóa thành công.']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
        }
        break;

    case 'api_delete_all':
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ']);
            exit();
        }
        if (($_SESSION['user_vai_tro'] ?? '') !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa toàn bộ khen thưởng.']);
            exit();
        }
        try {
            $stmt = $db->prepare("DELETE FROM khen_thuong WHERE nam_hoc_id = ?");
            $stmt->execute([$current_nam_hoc]);
            $deleted = $stmt->rowCount();
            echo json_encode(['success' => true, 'message' => 'Đã xóa toàn bộ khen thưởng.', 'deleted' => $deleted]);
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
        }
        break;

    case 'import':
        // Xử lý upload file excel
        ini_set('display_errors', '0');
        ini_set('display_startup_errors', '0');
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['excelFile'])) {
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Yêu cầu không hợp lệ.'];
            header('Location: /thidua/admin/khen-thuong?iframe=1');
            exit();
        }
        try {
            require_once __DIR__ . '/../../vendor/autoload.php';
            
            $file = $_FILES['excelFile'];
            if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Tệp upload không hợp lệ hoặc vượt quá giới hạn của máy chủ.'];
                header('Location: /thidua/admin/khen-thuong?iframe=1');
                exit();
            }

            $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
            if (!in_array($ext, ['xls', 'xlsx'])) {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Định dạng tệp không hợp lệ. Vui lòng dùng .xls hoặc .xlsx'];
                header('Location: /thidua/admin/khen-thuong?iframe=1');
                exit();
            }

            $stmt_lop = $db->prepare("SELECT id, ten_lop FROM raw_lop_hoc WHERE nam_hoc_id = ?");
            $stmt_lop->execute([$current_nam_hoc]);
            $ds_lop_hoc = $stmt_lop->fetchAll(PDO::FETCH_KEY_PAIR);
            $ds_lop_hoc_by_name = array_flip($ds_lop_hoc);

            $normalizeDate = function ($value) {
                if ($value === null || $value === '') return '';
                if (is_numeric($value)) {
                    return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
                }
                $str = trim((string)$value);
                $dt = DateTime::createFromFormat('d/m/Y', $str);
                if ($dt && $dt->format('d/m/Y') === $str) {
                    return $dt->format('Y-m-d');
                }
                $dt2 = DateTime::createFromFormat('Y-m-d', $str);
                if ($dt2 && $dt2->format('Y-m-d') === $str) {
                    return $str;
                }
                return '';
            };

            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file['tmp_name']);

            // Xử lý Sheet Cá nhân
            $sheet_cn = $spreadsheet->getSheetByName('MauCaNhan');
            if (!$sheet_cn) {
                if ($spreadsheet->getSheetCount() > 0) {
                    $sheet_cn = $spreadsheet->getSheet(0);
                } else {
                    throw new Exception('Không tìm thấy sheet hợp lệ trong file Excel.');
                }
            }
            $data_cn = $sheet_cn->toArray();
            array_shift($data_cn); // Bỏ header
            $khen_thuong_ca_nhan_valid = [];
            $khen_thuong_ca_nhan_invalid = [];

            $find_student_stmt = $db->prepare("
                SELECT qt.id FROM quatrinh_hoc_tap qt
                JOIN ho_so_hoc_sinh hs ON qt.ma_hoc_sinh = hs.ma_hoc_sinh
                WHERE (CONCAT(hs.ho_dem, ' ', hs.ten)) = :ho_ten AND qt.lop_hoc_id = :lop_id AND qt.nam_hoc_id = :nam_hoc_id
            ");

            foreach ($data_cn as $index => $row) {
                if (empty(array_filter($row))) continue;
                
                $rowData = [
                    'ho_va_ten' => trim($row[0] ?? ''),
                    'ten_lop' => trim($row[1] ?? ''),
                    'ngay_khen_thuong' => $row[2] ?? '',
                    'ten_khen_thuong' => trim($row[3] ?? ''),
                    'so_quyet_dinh' => trim($row[4] ?? ''),
                    'cap_khen_thuong' => trim($row[5] ?? ''),
                    'ghi_chu' => trim($row[6] ?? ''),
                    'line_number' => $index + 2
                ];
                
                $errors = [];
                $hoc_sinh_id = null;

                if (empty($rowData['ho_va_ten'])) $errors[] = "Họ và tên không được trống.";
                if (empty($rowData['ten_lop'])) $errors[] = "Lớp không được trống.";
                if (empty($rowData['ten_khen_thuong'])) $errors[] = "Tên khen thưởng không được trống.";
                
                if (!isset($ds_lop_hoc_by_name[$rowData['ten_lop']])) {
                    $errors[] = "Lớp '{$rowData['ten_lop']}' không tồn tại.";
                } else {
                    $lop_id = $ds_lop_hoc_by_name[$rowData['ten_lop']];
                    $find_student_stmt->execute([
                        ':ho_ten' => $rowData['ho_va_ten'],
                        ':lop_id' => $lop_id,
                        ':nam_hoc_id' => $current_nam_hoc
                    ]);
                    $found_students = $find_student_stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (count($found_students) === 1) {
                        $hoc_sinh_id = $found_students[0]['id'];
                    } elseif (count($found_students) === 0) {
                        $errors[] = "Không tìm thấy HS '{$rowData['ho_va_ten']}' trong lớp '{$rowData['ten_lop']}'.";
                    } else {
                        $errors[] = "Tìm thấy nhiều HS trùng tên '{$rowData['ho_va_ten']}' trong lớp '{$rowData['ten_lop']}'.";
                    }
                }
                
                $rowData['ngay_khen_thuong'] = $normalizeDate($rowData['ngay_khen_thuong']);
                if ($rowData['ngay_khen_thuong'] === '' && trim((string)($row[2] ?? '')) !== '') {
                    $errors[] = "Ngày khen thưởng không hợp lệ (định dạng dd/mm/YYYY).";
                }

                if (empty($errors) && $hoc_sinh_id) {
                    if ($rowData['ngay_khen_thuong'] === '') {
                        $rowData['ngay_khen_thuong'] = null;
                    }
                    $rowData['hoc_sinh_id'] = $hoc_sinh_id;
                    $khen_thuong_ca_nhan_valid[] = $rowData;
                } else {
                    $rowData['errors'] = $errors;
                    $khen_thuong_ca_nhan_invalid[] = $rowData;
                }
            }

            // Xử lý Sheet Tập thể
            $sheet_tt = $spreadsheet->getSheetByName('MauTapThe');
            if ($sheet_tt) {
                $data_tt = $sheet_tt->toArray();
                array_shift($data_tt); 
                $khen_thuong_tap_the_valid = [];
                $khen_thuong_tap_the_invalid = [];
                
                foreach ($data_tt as $index => $row) {
                    if (empty(array_filter($row))) continue;
                    $rowData = [
                        'ten_lop_hoac_tap_the' => trim($row[0] ?? ''),
                        'ngay_khen_thuong' => $row[1] ?? '',
                        'ten_khen_thuong' => trim($row[2] ?? ''),
                        'so_quyet_dinh' => trim($row[3] ?? ''),
                        'cap_khen_thuong' => trim($row[4] ?? ''),
                        'ghi_chu' => trim($row[5] ?? ''),
                        'line_number' => $index + 2
                    ];
                    
                    $errors_tt = [];
                    if (empty($rowData['ten_lop_hoac_tap_the'])) $errors_tt[] = "Tên tập thể không được trống.";
                    if (empty($rowData['ten_khen_thuong'])) $errors_tt[] = "Tên khen thưởng không được trống.";

                    $rowData['ngay_khen_thuong'] = $normalizeDate($rowData['ngay_khen_thuong']);
                    if ($rowData['ngay_khen_thuong'] === '' && trim((string)($row[1] ?? '')) !== '') {
                        $errors_tt[] = "Ngày khen thưởng không hợp lệ (định dạng dd/mm/YYYY).";
                    }
                    
                    if (empty($errors_tt)) {
                         if (isset($ds_lop_hoc_by_name[$rowData['ten_lop_hoac_tap_the']])) {
                            $rowData['lop_hoc_id'] = $ds_lop_hoc_by_name[$rowData['ten_lop_hoac_tap_the']];
                            $rowData['ten_tap_the'] = null;
                        } else {
                            $rowData['lop_hoc_id'] = null;
                            $rowData['ten_tap_the'] = $rowData['ten_lop_hoac_tap_the'];
                        }
                        if ($rowData['ngay_khen_thuong'] === '') {
                            $rowData['ngay_khen_thuong'] = null;
                        }
                        $khen_thuong_tap_the_valid[] = $rowData;
                    } else {
                         $rowData['errors'] = $errors_tt;
                         $khen_thuong_tap_the_invalid[] = $rowData;
                    }
                }
            } else {
                $khen_thuong_tap_the_valid = [];
                $khen_thuong_tap_the_invalid = [];
            }

            if (empty($khen_thuong_ca_nhan_valid) && empty($khen_thuong_ca_nhan_invalid) && empty($khen_thuong_tap_the_valid) && empty($khen_thuong_tap_the_invalid)) {
                $_SESSION['flash_message'] = ['type' => 'info', 'message' => 'File Excel không chứa dữ liệu khen thưởng nào.'];
                header('Location: /thidua/admin/khen-thuong?iframe=1');
                exit();
            }

            $_SESSION['import_khen_thuong'] = [
                'valid_cn' => $khen_thuong_ca_nhan_valid,
                'invalid_cn' => $khen_thuong_ca_nhan_invalid,
                'valid_tt' => $khen_thuong_tap_the_valid,
                'invalid_tt' => $khen_thuong_tap_the_invalid
            ];

            header('Location: /thidua/admin/khen-thuong?action=api_preview_import&iframe=1');
            exit();

        } catch (Throwable $e) {
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Lỗi xử lý file: ' . $e->getMessage()];
            header('Location: /thidua/admin/khen-thuong?iframe=1');
            exit();
        }
        break;

    case 'api_preview_import':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_import'])) {
            try {
                if (!isset($_SESSION['import_khen_thuong'])) {
                    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Không có dữ liệu import để xử lý.'];
                    header('Location: /thidua/admin/khen-thuong?iframe=1');
                    exit();
                }

                $data = $_SESSION['import_khen_thuong'];
                $valid_cn = $data['valid_cn'] ?? [];
                $valid_tt = $data['valid_tt'] ?? [];

                $insert_stmt = $db->prepare("
                    INSERT INTO khen_thuong 
                    (loai, hoc_sinh_id, lop_hoc_id, ten_tap_the, ngay_khen_thuong, ten_khen_thuong, so_quyet_dinh, cap_khen_thuong, ghi_chu, nam_hoc_id) 
                    VALUES (:loai, :hoc_sinh_id, :lop_hoc_id, :ten_tap_the, :ngay_khen_thuong, :ten_khen_thuong, :so_quyet_dinh, :cap_khen_thuong, :ghi_chu, :nam_hoc_id)
                ");

                $db->beginTransaction();

                foreach ($valid_cn as $row) {
                    $insert_stmt->execute([
                        ':loai' => 'ca_nhan',
                        ':hoc_sinh_id' => $row['hoc_sinh_id'] ?? null,
                        ':lop_hoc_id' => null,
                        ':ten_tap_the' => null,
                        ':ngay_khen_thuong' => $row['ngay_khen_thuong'] ?? null,
                        ':ten_khen_thuong' => $row['ten_khen_thuong'] ?? null,
                        ':so_quyet_dinh' => $row['so_quyet_dinh'] ?? null,
                        ':cap_khen_thuong' => $row['cap_khen_thuong'] ?? null,
                        ':ghi_chu' => $row['ghi_chu'] ?? null,
                        ':nam_hoc_id' => $current_nam_hoc
                    ]);
                }

                foreach ($valid_tt as $row) {
                    $insert_stmt->execute([
                        ':loai' => 'tap_the',
                        ':hoc_sinh_id' => null,
                        ':lop_hoc_id' => $row['lop_hoc_id'] ?? null,
                        ':ten_tap_the' => $row['ten_tap_the'] ?? null,
                        ':ngay_khen_thuong' => $row['ngay_khen_thuong'] ?? null,
                        ':ten_khen_thuong' => $row['ten_khen_thuong'] ?? null,
                        ':so_quyet_dinh' => $row['so_quyet_dinh'] ?? null,
                        ':cap_khen_thuong' => $row['cap_khen_thuong'] ?? null,
                        ':ghi_chu' => $row['ghi_chu'] ?? null,
                        ':nam_hoc_id' => $current_nam_hoc
                    ]);
                }

                $db->commit();
                unset($_SESSION['import_khen_thuong']);

                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Import khen thưởng thành công.'];
                header('Location: /thidua/admin/khen-thuong?iframe=1');
                exit();

            } catch (Throwable $e) {
                if (isset($db) && $db && $db->inTransaction()) {
                    $db->rollBack();
                }
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Lỗi import dữ liệu: ' . $e->getMessage()];
                header('Location: /thidua/admin/khen-thuong?iframe=1');
                exit();
            }
        } else {
            if (!isset($_SESSION['import_khen_thuong'])) {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Không có dữ liệu import để xem trước.'];
                header('Location: /thidua/admin/khen-thuong?iframe=1');
                exit();
            }

            $data = $_SESSION['import_khen_thuong'];
            $valid_cn = $data['valid_cn'] ?? [];
            $invalid_cn = $data['invalid_cn'] ?? [];
            $valid_tt = $data['valid_tt'] ?? [];
            $invalid_tt = $data['invalid_tt'] ?? [];

            require_once __DIR__ . '/../views/xem_truoc_import_khen_thuong.php';
        }
        break;

    case 'export_excel':
        require_once __DIR__ . '/../../vendor/autoload.php';
        
        $sql = "
            SELECT 
                kt.id,
                kt.loai,
                kt.ngay_khen_thuong,
                kt.ten_khen_thuong,
                kt.so_quyet_dinh,
                kt.cap_khen_thuong,
                kt.ghi_chu,
                hs.ho_dem,
                hs.ten,
                lh.ten_lop,
                kt.ten_tap_the
            FROM khen_thuong kt
            LEFT JOIN quatrinh_hoc_tap qt ON kt.hoc_sinh_id = qt.id
            LEFT JOIN ho_so_hoc_sinh hs ON qt.ma_hoc_sinh = hs.ma_hoc_sinh
            LEFT JOIN raw_lop_hoc lh ON COALESCE(kt.lop_hoc_id, qt.lop_hoc_id) = lh.id AND lh.nam_hoc_id = ?
            WHERE kt.nam_hoc_id = ?
            ORDER BY kt.ngay_khen_thuong DESC, kt.id DESC
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([$current_nam_hoc, $current_nam_hoc]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            echo "<script>alert('Không có dữ liệu khen thưởng để xuất.'); window.close();</script>";
            exit();
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('KhenThuong');

        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman');
        $last_column = 'I';

        $sheet->mergeCells('A1:D1')->setCellValue('A1', 'TRƯỜNG THPT BÌNH SƠN');
        $sheet->mergeCells('A2:D2')->setCellValue('A2', 'HỆ THỐNG ĐÁNH GIÁ THI ĐUA');
        $sheet->getStyle('A1')->getFont()->setSize(11)->setBold(false);
        $sheet->getStyle('A2')->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('A1:D2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A4:{$last_column}4")->setCellValue('A4', 'DANH SÁCH KHEN THƯỞNG');
        $sheet->mergeCells("A5:{$last_column}5")->setCellValue('A5', 'CẬP NHẬT LÚC: ' . date('d/m/Y H:i'));
        $sheet->getStyle('A4')->getFont()->setSize(13)->setBold(true);
        $sheet->getStyle('A5')->getFont()->setSize(12)->setBold(true);
        $sheet->getStyle("A4:{$last_column}5")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $header_row = 7;
        $headers = ['STT', 'Loại', 'Đối tượng / Tập thể', 'Lớp', 'Ngày KT', 'Tên Khen thưởng', 'Số QĐ', 'Cấp KT', 'Ghi chú'];
        $sheet->fromArray($headers, null, 'A' . $header_row);
        $sheet->getStyle('A'.$header_row.':'.$last_column.$header_row)->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle('A'.$header_row.':'.$last_column.$header_row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $data_row_start = $header_row + 1;
        $rowIndex = $data_row_start;
        foreach ($rows as $idx => $r) {
            $doi_tuong = $r['loai'] === 'tap_the'
                ? ($r['ten_tap_the'] ?: $r['ten_lop'])
                : trim(($r['ho_dem'] ?? '') . ' ' . ($r['ten'] ?? ''));
            $lop = $r['ten_lop'] ?? '';
            $sheet->setCellValue('A' . $rowIndex, $idx + 1);
            $sheet->setCellValue('B' . $rowIndex, $r['loai'] === 'ca_nhan' ? 'Cá nhân' : 'Tập thể');
            $sheet->setCellValue('C' . $rowIndex, $doi_tuong);
            $sheet->setCellValue('D' . $rowIndex, $lop);
            $sheet->setCellValue('E' . $rowIndex, $r['ngay_khen_thuong'] ? date('d/m/Y', strtotime($r['ngay_khen_thuong'])) : '');
            $sheet->setCellValue('F' . $rowIndex, $r['ten_khen_thuong']);
            $sheet->setCellValue('G' . $rowIndex, $r['so_quyet_dinh']);
            $sheet->setCellValue('H' . $rowIndex, $r['cap_khen_thuong']);
            $sheet->setCellValue('I' . $rowIndex, $r['ghi_chu']);
            $sheet->getStyle('A'.$rowIndex.':'.$last_column.$rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $rowIndex++;
        }

        $last_row = $rowIndex - 1;
        if ($last_row >= $data_row_start) {
            $table_range = 'A'.$header_row.':'.$last_column.$last_row;
            $sheet->getStyle($table_range)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        }

        foreach (range('A', $last_column) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Danh_Sach_Khen_Thuong_'.date('Ymd_Hi').'.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
        break;

    default:
        header('Location: /thidua/admin/khen-thuong?iframe=1');
        exit();
}
