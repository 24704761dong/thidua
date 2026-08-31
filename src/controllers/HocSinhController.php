<?php
// File: src/controllers/HocSinhController.php
if (function_exists('opcache_invalidate')) { opcache_invalidate(__FILE__, true); }
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$action = $_GET['action'] ?? 'index';
$db = get_db_connection();
$current_nam_hoc = $_SESSION['current_nam_hoc_id'] ?? 1;

/**
 * Tách họ tên thành họ đệm và tên.
 */
function split_full_name($fullName) {
    $fullName = trim($fullName);
    $lastSpacePos = strrpos($fullName, ' ');
    if ($lastSpacePos === false) { return ['ho_dem' => '', 'ten' => $fullName]; }
    $ho_dem = substr($fullName, 0, $lastSpacePos);
    $ten = substr($fullName, $lastSpacePos + 1);
    return ['ho_dem' => $ho_dem, 'ten' => $ten];
}

switch ($action) {
    case 'index':
        // ==========================================
        // 1. HIỂN THỊ DANH SÁCH HỌC SINH
        // ==========================================
        require_once __DIR__ . '/../lib/hoc_sinh_db.php';
        require_once __DIR__ . '/../lib/lop_hoc_db.php';
        require_once __DIR__ . '/../lib/helpers.php';
        
        $filter_khoi = $_GET['khoi'] ?? 'all';
        $filter_lop_id = $_GET['lop_id'] ?? 'all';
        $filter_chuc_vu = $_GET['chuc_vu'] ?? 'all';
        $filter_keyword = trim($_GET['keyword'] ?? '');
        $filter_has_permission = isset($_GET['has_permission']) && $_GET['has_permission'] === '1';
        
        $filters = [
            'khoi' => $filter_khoi,
            'lop_id' => $filter_lop_id,
            'chuc_vu' => $filter_chuc_vu,
            'keyword' => $filter_keyword,
            'has_permission' => $filter_has_permission
        ];
        
        $page = max(1, (int)($_GET['page'] ?? 1));
        $page_size = (int)($_GET['page_size'] ?? 100);
        if ($page_size <= 0) { $page_size = 100; }
        if ($page_size > 500) { $page_size = 500; } 
        $offset = ($page - 1) * $page_size;
        
        $danh_sach_hoc_sinh = get_all_hoc_sinh($db, $filters, [
            'limit' => $page_size,
            'offset' => $offset,
        ]);
        
        foreach ($danh_sach_hoc_sinh as &$hs) {
            $hs['ngay_sinh'] = format_date_display($hs['ngay_sinh'] ?? '');
        }
        unset($hs);
        
        $total_records = count_hoc_sinh($db, $filters);
        $total_pages = max(1, (int)ceil($total_records / $page_size));
        
        $danh_sach_lop = get_all_lop_hoc($db);
        
        $stmt_chucvu = $db->query("SELECT DISTINCT chuc_vu FROM hoc_sinh WHERE chuc_vu IS NOT NULL AND chuc_vu != '' ORDER BY chuc_vu");
        $danh_sach_chuc_vu = $stmt_chucvu->fetchAll(PDO::FETCH_COLUMN);
        
        $pagination = [
            'page' => $page,
            'page_size' => $page_size,
            'offset' => $offset,
            'total' => $total_records,
            'total_pages' => $total_pages,
        ];
        
        $settings = get_all_settings($db);
        
        require_once __DIR__ . '/../views/quan_ly_hoc_sinh.php';
        break;

    case 'add':
        // ==========================================
        // 2. THÊM HỌC SINH
        // ==========================================
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ma_hoc_sinh = $_POST['ma_hoc_sinh'];
            $ho_ten = $_POST['ho_ten'];
            $ten_lop = trim($_POST['ten_lop']); 
            $ngay_sinh = $_POST['ngay_sinh'];
            $gioi_tinh = $_POST['gioi_tinh'];
            $chuc_vu = $_POST['chuc_vu'] ?? '';
            $sdt = $_POST['sdt'] ?? '';
            $email = $_POST['email'] ?? ''; 
            $nien_khoa = $_POST['nien_khoa'] ?? ''; 
            $tinh_thanhpho = $_POST['tinh_thanhpho'] ?? 'Thành phố Đồng Nai';
            $xa_phuong = $_POST['xa_phuong'] ?? '';
            $ap_khupho = $_POST['ap_khupho'] ?? '';
            $dia_chi_chi_tiet = $_POST['dia_chi_chi_tiet'] ?? '';
        
            $stmt_check_ma = $db->prepare("SELECT id FROM ho_so_hoc_sinh WHERE ma_hoc_sinh = ?");
            $stmt_check_ma->execute([$ma_hoc_sinh]);
            if ($stmt_check_ma->fetch()) {
                $_SESSION['flash_message'] = "Thêm thất bại: Mã học sinh '$ma_hoc_sinh' đã tồn tại trong hệ thống.";
                $redirect_url = '/thidua/admin/hoc-sinh' . (isset($_GET['iframe']) ? '?iframe=1' : '');
                header("Location: $redirect_url");
                exit();
            }
        
            $lop_hoc_id = null;
            $stmt_check = $db->prepare("SELECT id FROM lop_hoc WHERE ten_lop = ?");
            $stmt_check->execute([$ten_lop]);
            $lop = $stmt_check->fetch();
        
            if ($lop) {
                $lop_hoc_id = $lop['id'];
            } else {
                try {
                    $stmt_insert = $db->prepare("INSERT INTO raw_lop_hoc (ten_lop, nam_hoc_id) VALUES (?, ?)");
                    $stmt_insert->execute([$ten_lop, $current_nam_hoc]);
                    $lop_hoc_id = $db->lastInsertId();
                } catch (PDOException $e) {
                    $_SESSION['flash_message'] = "Lỗi khi tự động tạo lớp mới: " . $e->getMessage();
                    $redirect_url = '/thidua/admin/hoc-sinh' . (isset($_GET['iframe']) ? '?iframe=1' : '');
                header("Location: $redirect_url");
                    exit();
                }
            }
        
            $ten_da_tach = split_full_name($ho_ten);
            $sql_hoso = "INSERT INTO ho_so_hoc_sinh (ma_hoc_sinh, ho_dem, ten, ngay_sinh, gioi_tinh, sdt, email, nien_khoa, tinh_thanhpho, xa_phuong, ap_khupho, dia_chi_chi_tiet) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $sql_quatrinh = "INSERT INTO quatrinh_hoc_tap (ma_hoc_sinh, nam_hoc_id, lop_hoc_id, chuc_vu) VALUES (?, ?, ?, ?)";
            
            try {
                $db->beginTransaction();
                $stmt_hoso = $db->prepare($sql_hoso);
                $stmt_hoso->execute([
                    $ma_hoc_sinh, $ten_da_tach['ho_dem'], $ten_da_tach['ten'], 
                    $ngay_sinh, $gioi_tinh, $sdt, $email, $nien_khoa,
                    $tinh_thanhpho, $xa_phuong, $ap_khupho, $dia_chi_chi_tiet
                ]);
        
                $stmt_quatrinh = $db->prepare($sql_quatrinh);
                $stmt_quatrinh->execute([$ma_hoc_sinh, $current_nam_hoc, $lop_hoc_id, $chuc_vu]);
        
                $db->commit();
                $_SESSION['flash_message'] = "Thêm học sinh thành công!";
            } catch (PDOException $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                $_SESSION['flash_message'] = "Thêm học sinh thất bại: " . $e->getMessage();
            }
            $redirect_url = '/thidua/admin/hoc-sinh' . (isset($_GET['iframe']) ? '?iframe=1' : '');
            header("Location: $redirect_url");
            exit();
        }
        break;

    case 'edit':
        // ==========================================
        // 3. SỬA HỌC SINH
        // ==========================================
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $student_id = $_POST['student_id'];
            $ma_hoc_sinh = $_POST['ma_hoc_sinh'];
            $ho_ten = $_POST['ho_ten'];
            $ten_lop = trim($_POST['ten_lop']); 
            $ngay_sinh = $_POST['ngay_sinh'];
            $gioi_tinh = $_POST['gioi_tinh'];
            $chuc_vu = $_POST['chuc_vu'] ?? '';
            $sdt = $_POST['sdt'] ?? '';
            $email = $_POST['email'] ?? ''; 
            $nien_khoa = $_POST['nien_khoa'] ?? ''; 
            $tinh_thanhpho = $_POST['tinh_thanhpho'] ?? 'Thành phố Đồng Nai';
            $xa_phuong = $_POST['xa_phuong'] ?? '';
            $ap_khupho = $_POST['ap_khupho'] ?? '';
            $dia_chi_chi_tiet = $_POST['dia_chi_chi_tiet'] ?? '';
        
            $lop_hoc_id = null;
            $stmt_check = $db->prepare("SELECT id FROM lop_hoc WHERE ten_lop = ?");
            $stmt_check->execute([$ten_lop]);
            $lop = $stmt_check->fetch();
        
            if ($lop) {
                $lop_hoc_id = $lop['id'];
            } else {
                try {
                    $stmt_insert = $db->prepare("INSERT INTO raw_lop_hoc (ten_lop, nam_hoc_id) VALUES (?, ?)");
                    $stmt_insert->execute([$ten_lop, $current_nam_hoc]);
                    $lop_hoc_id = $db->lastInsertId();
                } catch (PDOException $e) {
                    $_SESSION['flash_message'] = "Lỗi khi tự động tạo lớp mới: " . $e->getMessage();
                    $redirect_url = '/thidua/admin/hoc-sinh' . (isset($_GET['iframe']) ? '?iframe=1' : '');
                header("Location: $redirect_url");
                    exit();
                }
            }
        
            $ten_da_tach = split_full_name($ho_ten);
            $stmt_old = $db->prepare("SELECT ma_hoc_sinh FROM ho_so_hoc_sinh WHERE id = ?");
            $stmt_old->execute([$student_id]);
            $old_ma_hoc_sinh = $stmt_old->fetchColumn();
        
            $sql_hoso = "UPDATE ho_so_hoc_sinh SET 
                        ma_hoc_sinh = ?, ho_dem = ?, ten = ?, ngay_sinh = ?, gioi_tinh = ?, 
                        sdt = ?, email = ?, nien_khoa = ?, tinh_thanhpho = ?, xa_phuong = ?, ap_khupho = ?, dia_chi_chi_tiet = ?
                    WHERE id = ?";
            $sql_quatrinh_ma = "UPDATE quatrinh_hoc_tap SET ma_hoc_sinh = ? WHERE ma_hoc_sinh = ?";
            $sql_quatrinh_lop = "UPDATE quatrinh_hoc_tap SET lop_hoc_id = ?, chuc_vu = ? WHERE ma_hoc_sinh = ? AND nam_hoc_id = ?";
            
            try {
                $db->beginTransaction();
                $stmt_hoso = $db->prepare($sql_hoso);
                $stmt_hoso->execute([
                    $ma_hoc_sinh, $ten_da_tach['ho_dem'], $ten_da_tach['ten'], 
                    $ngay_sinh, $gioi_tinh, $sdt, $email, $nien_khoa, 
                    $tinh_thanhpho, $xa_phuong, $ap_khupho, $dia_chi_chi_tiet,
                    $student_id
                ]);
        
                if ($ma_hoc_sinh !== $old_ma_hoc_sinh) {
                    $stmt_quatrinh_ma = $db->prepare($sql_quatrinh_ma);
                    $stmt_quatrinh_ma->execute([$ma_hoc_sinh, $old_ma_hoc_sinh]);
                }
        
                $stmt_quatrinh_lop = $db->prepare($sql_quatrinh_lop);
                $stmt_quatrinh_lop->execute([$lop_hoc_id, $chuc_vu, $ma_hoc_sinh, $current_nam_hoc]);
        
                $db->commit();
                $_SESSION['flash_message'] = "Cập nhật thông tin học sinh thành công!";
            } catch (PDOException $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                $_SESSION['flash_message'] = "Cập nhật thất bại: " . $e->getMessage();
            }
            
            $redirect_url = '/thidua/admin/hoc-sinh' . (isset($_GET['iframe']) ? '?iframe=1' : '');
            header("Location: $redirect_url");
            exit();
        }
        break;

    case 'api_delete':
        // ==========================================
        // 4. XÓA HỌC SINH (CHUYỂN NGHỈ HỌC)
        // ==========================================
        header('Content-Type: application/json');
        $response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ.'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $ids = $data['ids'] ?? null;
            $ngay_nghi_hoc = $data['ngay_nghi_hoc'] ?? null;
            $ly_do_nghi_hoc = $data['ly_do_nghi_hoc'] ?? null;
        
            if (empty($ids) || !is_array($ids) || empty($ngay_nghi_hoc) || empty($ly_do_nghi_hoc)) {
                $response['message'] = 'Thiếu thông tin ID, ngày nghỉ, hoặc lý do.';
                echo json_encode($response);
                exit();
            }
        
            try {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $sql = "UPDATE hoc_sinh 
                        SET trang_thai_hoc_tap = 'nghi_hoc', 
                            quyen_truy_cap = NULL,
                            ngay_nghi_hoc = ?,
                            ly_do_nghi_hoc = ?
                        WHERE id IN ($placeholders)";
                $stmt = $db->prepare($sql);
                $params = array_merge([$ngay_nghi_hoc, $ly_do_nghi_hoc], $ids);
                $stmt->execute($params);
        
                $updated_count = $stmt->rowCount();
                if ($updated_count > 0) {
                    $response = ['success' => true, 'message' => "Đã chuyển trạng thái Nghỉ Học cho {$updated_count} học sinh!"];
                } else {
                    $response['message'] = 'Không có học sinh nào được cập nhật.';
                }
            } catch (PDOException $e) {
                $response['message'] = 'Lỗi CSDL: ' . $e->getMessage();
            }
        }
        echo json_encode($response);
        break;

    case 'api_graduate':
        // ==========================================
        // 4.2. ĐÁNH DẤU HỌC SINH TỐT NGHIỆP
        // ==========================================
        header('Content-Type: application/json');
        $response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ.'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $ids = $data['ids'] ?? null;
            $nam_tot_nghiep = $data['nam_tot_nghiep'] ?? date('Y');
        
            if (empty($ids) || !is_array($ids)) {
                $response['message'] = 'Thiếu thông tin ID học sinh.';
                echo json_encode($response);
                exit();
            }
        
            try {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));

                // TỰ ĐỘNG ĐẨY ẢNH THẺ LÊN CLOUD (ONEDRIVE) KHI TỐT NGHIỆP
                try {
                    require_once __DIR__ . '/../../config/microsoft.php';
                    $ms_email = $_ENV['MS_ONEDRIVE_BACKUP_EMAIL'] ?? '';
                    $token = getMsGraphMainToken();
                    
                    $stmtHs = $db->prepare("SELECT id, anh_the, anh_the_driver FROM ho_so_hoc_sinh WHERE id IN ($placeholders) AND (anh_the_driver IS NULL OR anh_the_driver = 'local')");
                    $stmtHs->execute($ids);
                    $hsList = $stmtHs->fetchAll(PDO::FETCH_ASSOC);
                    $baseDir = dirname(dirname(__DIR__)) . '/public/assets/anh_the/';
                    
                    if ($token && !empty($ms_email)) {
                        foreach ($hsList as $hs) {
                            if (!empty($hs['anh_the'])) {
                                $filename = basename($hs['anh_the']);
                                $anh_the_clean = ltrim($hs['anh_the'], '/');
                                if (strpos($anh_the_clean, 'public/assets/') !== false) {
                                    $localPath = dirname(dirname(__DIR__)) . '/' . $anh_the_clean;
                                } else {
                                    $localPath = dirname(dirname(__DIR__)) . '/public/assets/anh_the/' . $anh_the_clean;
                                }
                                if (file_exists($localPath)) {
                                    $cloudKey = 'avatars/tot_nghiep/' . $nam_tot_nghiep . '/' . $filename;
                                    try {
                                        $fileId = uploadFileToOneDrive($token, $ms_email, $cloudKey, $localPath);
                                        if ($fileId) {
                                            $db->prepare("UPDATE ho_so_hoc_sinh SET anh_the_driver = 'onedrive', anh_the_cloud_key = ? WHERE id = ?")
                                               ->execute([$fileId, $hs['id']]);
                                            @unlink($localPath);
                                        }
                                    } catch (Exception $e) {
                                        error_log("Lỗi upload OneDrive avatar học sinh ID {$hs['id']}: " . $e->getMessage());
                                    }
                                }
                            }
                        }
                    }
                } catch (Exception $e) {
                    error_log("Lỗi khởi tạo OneDrive: " . $e->getMessage());
                }

                // Cập nhật bảng gốc ho_so_hoc_sinh
                $sql = "UPDATE ho_so_hoc_sinh 
                        SET trang_thai_hoc_tap = 'da_tot_nghiep', 
                            quyen_truy_cap = NULL,
                            nam_tot_nghiep = ?
                        WHERE id IN ($placeholders)";
                $stmt = $db->prepare($sql);
                $params = array_merge([$nam_tot_nghiep], $ids);
                $stmt->execute($params);
        
                $updated_count = $stmt->rowCount();
                if ($updated_count > 0) {
                    $response = ['success' => true, 'message' => "Đã chuyển trạng thái Đã Tốt Nghiệp cho {$updated_count} học sinh!"];
                } else {
                    $response['message'] = 'Không có học sinh nào được cập nhật.';
                }
            } catch (PDOException $e) {
                $response['message'] = 'Lỗi CSDL: ' . $e->getMessage();
            }
        }
        echo json_encode($response);
        break;

    case 'import_form':
        // ==========================================
        // 4.5. HIỂN THỊ FORM IMPORT
        // ==========================================
        require_once __DIR__ . '/../views/nhap_hoc_sinh.php';
        break;

    case 'import_process':
        // ==========================================
        // 5. XỬ LÝ FILE IMPORT EXCEL
        // ==========================================
        require_once __DIR__ . '/../../vendor/autoload.php';
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excelFile'])) {
            $file = $_FILES['excelFile'];
            if ($file['error'] !== UPLOAD_ERR_OK) { die("Lỗi trong quá trình tải file lên."); }
            if (\PhpOffice\PhpSpreadsheet\IOFactory::identify($file['tmp_name']) !== 'Xlsx') { die("Lỗi: Chỉ chấp nhận file định dạng .xlsx"); }
            
            $stmt_lop = $db->query("SELECT ten_lop, id FROM lop_hoc");
            $ds_lop_hoc = $stmt_lop->fetchAll(PDO::FETCH_KEY_PAIR);
            $stmt_hs = $db->query("SELECT ma_hoc_sinh FROM ho_so_hoc_sinh");
            $ds_ma_hs_da_ton_tai = $stmt_hs->fetchAll(PDO::FETCH_COLUMN);
        
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file['tmp_name']);
            $worksheet = $spreadsheet->getActiveSheet();
            $data = $worksheet->toArray();
            $header = array_shift($data);
        
            $danh_sach_hop_le = [];
            $danh_sach_khong_hop_le = [];
            $ds_ma_hs_trong_file = [];
            
            foreach ($data as $rowIndex => $row) {
                if (empty(array_filter($row))) continue;
        
                $rowData = array_combine($header, $row);
        
                $ngay_sinh_excel = $rowData['ngay_sinh'];
                if (!empty($ngay_sinh_excel) && is_numeric($ngay_sinh_excel)) {
                    $rowData['ngay_sinh'] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($ngay_sinh_excel)->format('d/m/Y');
                } 
        
                $ma_hoc_sinh = trim($rowData['ma_hoc_sinh']);
                $ho_ten_day_du = trim($rowData['ho_ten']);
                $ten_lop = trim($rowData['ten_lop']);
                $rowData['gmail'] = isset($rowData['gmail']) ? trim($rowData['gmail']) : '';
                $rowData['tinh_thanhpho'] = isset($rowData['tinh_thanhpho']) && trim($rowData['tinh_thanhpho']) !== '' ? trim($rowData['tinh_thanhpho']) : 'Thành phố Đồng Nai';
                $rowData['xa_phuong'] = isset($rowData['xa_phuong']) ? trim($rowData['xa_phuong']) : '';
                $rowData['ap_khupho'] = isset($rowData['ap_khupho']) ? trim($rowData['ap_khupho']) : '';
                $rowData['dia_chi_chi_tiet'] = isset($rowData['dia_chi_chi_tiet']) ? trim($rowData['dia_chi_chi_tiet']) : '';
                
                $ten_da_tach = split_full_name($ho_ten_day_du);
                $rowData['ho_dem'] = $ten_da_tach['ho_dem'];
                $rowData['ten'] = $ten_da_tach['ten'];
                $rowData['nien_khoa'] = isset($rowData['nien_khoa']) ? trim($rowData['nien_khoa']) : '';
                
                $errors = [];
                if (empty($ten_lop)) {
                    $errors[] = "Tên lớp không được để trống.";
                } elseif (!isset($ds_lop_hoc[$ten_lop])) {
                    try {
                        $stmt_insert_lop = $db->prepare("INSERT INTO raw_lop_hoc (ten_lop, nam_hoc_id) VALUES (?, ?)");
                        $stmt_insert_lop->execute([$ten_lop, $current_nam_hoc]);
                        $new_lop_id = $db->lastInsertId();
                        $ds_lop_hoc[$ten_lop] = $new_lop_id;
                    } catch (PDOException $e) {
                        $errors[] = "Lỗi khi tự động tạo lớp '$ten_lop'.";
                    }
                }
                
                if (empty($ma_hoc_sinh)) { 
                    $errors[] = "Số CCCD/Mã HS không được để trống."; 
                } else {
                    if (in_array($ma_hoc_sinh, $ds_ma_hs_da_ton_tai)) { 
                        $errors[] = "Dữ liệu học sinh '$ma_hoc_sinh' đã có trên hệ thống. Vui lòng dùng tính năng Nhận học sinh."; 
                    } elseif (in_array($ma_hoc_sinh, $ds_ma_hs_trong_file)) {
                        $errors[] = "CCCD/Mã HS '$ma_hoc_sinh' bị trùng lặp trong file excel.";
                    } else {
                        $ds_ma_hs_trong_file[] = $ma_hoc_sinh;
                    }
                }
                
                if (empty($errors)) {
                    $danh_sach_hop_le[] = $rowData;
                } else {
                    $rowData['loi'] = $errors;
                    $rowData['dong'] = $rowIndex + 2; 
                    $danh_sach_khong_hop_le[] = $rowData;
                }
            }
        
            $_SESSION['import_preview_valid'] = $danh_sach_hop_le;
            $_SESSION['import_preview_invalid'] = $danh_sach_khong_hop_le;
        
            $redirect_url = '/thidua/admin/hoc-sinh?action=preview_import' . (isset($_GET['iframe']) ? '&iframe=1' : '');
            header("Location: $redirect_url");
            exit();
        }
        break;

    case 'preview_import':
        // ==========================================
        // 6. XEM TRƯỚC IMPORT
        // ==========================================
        $danh_sach_hop_le = $_SESSION['import_preview_valid'] ?? [];
        $danh_sach_khong_hop_le = $_SESSION['import_preview_invalid'] ?? [];
        require_once __DIR__ . '/../views/xem_truoc_hoc_sinh.php';
        break;

    case 'api_save_import':
        // ==========================================
        // 7. LƯU IMPORT HỌC SINH VÀO DB
        // ==========================================
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SESSION['import_preview_valid'])) {
            $danh_sach_hop_le = $_SESSION['import_preview_valid'];
            $stmt_lop = $db->query("SELECT ten_lop, id FROM lop_hoc");
            $ds_lop_hoc = $stmt_lop->fetchAll(PDO::FETCH_KEY_PAIR);
        
            $sql_hoso = "INSERT IGNORE INTO ho_so_hoc_sinh (ma_hoc_sinh, ho_dem, ten, ngay_sinh, gioi_tinh, sdt, email, nien_khoa, tinh_thanhpho, xa_phuong, ap_khupho, dia_chi_chi_tiet) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_hoso = $db->prepare($sql_hoso);
        
            $sql_quatrinh = "INSERT IGNORE INTO quatrinh_hoc_tap (ma_hoc_sinh, nam_hoc_id, lop_hoc_id, chuc_vu) VALUES (?, ?, ?, ?)";
            $stmt_quatrinh = $db->prepare($sql_quatrinh);
        
            try {
                $db->beginTransaction();
                foreach ($danh_sach_hop_le as $hs) {
                    $lop_hoc_id = isset($ds_lop_hoc[$hs['ten_lop']]) ? $ds_lop_hoc[$hs['ten_lop']] : null;
                    if ($lop_hoc_id === null) {
                        throw new Exception("Không tìm thấy ID cho lớp: " . $hs['ten_lop']);
                    }
        
                    $stmt_hoso->execute([
                        $hs['ma_hoc_sinh'], $hs['ho_dem'], $hs['ten'], $hs['ngay_sinh'],
                        $hs['gioi_tinh'], $hs['sdt'], $hs['gmail'] ?? '', $hs['nien_khoa'] ?? '',
                        $hs['tinh_thanhpho'] ?? 'Thành phố Đồng Nai', $hs['xa_phuong'] ?? '', $hs['ap_khupho'] ?? '', $hs['dia_chi_chi_tiet'] ?? ''
                    ]);
        
                    $stmt_quatrinh->execute([
                        $hs['ma_hoc_sinh'], $current_nam_hoc, $lop_hoc_id, $hs['chuc_vu'] ?? ''
                    ]);
                }
                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
                die("Lỗi khi lưu dữ liệu vào CSDL: " . $e->getMessage());
            }
        
            unset($_SESSION['import_preview_valid']);
            unset($_SESSION['import_preview_invalid']);
        
            $_SESSION['flash_message'] = "Đã nhập thành công " . count($danh_sach_hop_le) . " học sinh!";
            $redirect_url = '/thidua/admin/hoc-sinh' . (isset($_GET['iframe']) ? '?iframe=1' : '');
            header("Location: $redirect_url");
            exit();
        } else {
            $redirect_url = '/thidua/admin/hoc-sinh' . (isset($_GET['iframe']) ? '?iframe=1' : '');
            header("Location: $redirect_url");
            exit();
        }
        break;

    case 'export_excel':
        // ==========================================
        // 8. XUẤT EXCEL DANH SÁCH HỌC SINH
        // ==========================================
        require_once __DIR__ . '/../../vendor/autoload.php';
        require_once __DIR__ . '/../lib/hoc_sinh_db.php'; 

        $selected_columns_param = $_GET['columns'] ?? '';
        $selected_columns = [];
        if (!empty($selected_columns_param)) {
            if (is_array($selected_columns_param)) {
                $selected_columns = $selected_columns_param;
            } else {
                $selected_columns = explode(',', $selected_columns_param);
            }
        } else {
            $selected_columns = ['khoi', 'lop', 'ma_hs', 'ho_ten', 'ngay_sinh', 'gioi_tinh', 'chuc_vu', 'sdt', 'gmail', 'ghi_chu'];
        }
        
        $filter_khoi = $_GET['khoi'] ?? 'all';
        $filter_lop_id = $_GET['lop_id'] ?? 'all';
        $filter_chuc_vu = $_GET['chuc_vu'] ?? 'all';
        $filter_keyword = trim($_GET['keyword'] ?? '');
        $filter_has_permission = isset($_GET['has_permission']) && $_GET['has_permission'] === '1';
        
        $filters = [
            'khoi' => $filter_khoi,
            'lop_id' => $filter_lop_id,
            'chuc_vu' => $filter_chuc_vu,
            'keyword' => $filter_keyword,
            'has_permission' => $filter_has_permission
        ];
        
        $danh_sach_hoc_sinh = get_all_hoc_sinh($db, $filters);
        
        if (empty($danh_sach_hoc_sinh)) {
            echo "<script>alert('Không có dữ liệu học sinh nào phù hợp với bộ lọc.'); window.close();</script>";
            exit;
        }
        
        $title = 'TẤT CẢ HỌC SINH';
        $is_class_sheet = false;
        $gvcn_name = '';
        if ($filter_lop_id !== 'all') {
            $stmt_lop = $db->prepare("SELECT ten_lop, gvcn_ten FROM lop_hoc WHERE id = ?");
            $stmt_lop->execute([$filter_lop_id]);
            $lop_info = $stmt_lop->fetch();
            if ($lop_info) {
                $title = 'LỚP ' . mb_strtoupper($lop_info['ten_lop'], 'UTF-8');
                $is_class_sheet = true;
                $gvcn_name = $lop_info['gvcn_ten'] ?? '';
            }
        } elseif ($filter_khoi !== 'all') {
            $title = 'KHỐI ' . mb_strtoupper($filter_khoi, 'UTF-8');
        }
        
        $admin_name = $_SESSION['user_ten'] ?? 'Quản trị viên';
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        
        if (!function_exists('populateSheet')) {
            function populateSheet($sheet, $title, $data, $is_class_sheet, $gvcn_name, $admin_name, $selected_columns) {
                $column_map = [
                    'khoi' => ['header' => 'Khối', 'width' => 8, 'align' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    'lop' => ['header' => 'Lớp', 'width' => 8, 'align' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    'nien_khoa' => ['header' => 'Niên khóa', 'width' => 12, 'align' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    'ma_hs' => ['header' => 'Số CCCD', 'width' => 12, 'align' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    'ho_ten' => ['header' => 'Họ và tên', 'width' => 23, 'align' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT],
                    'ngay_sinh' => ['header' => 'Ngày sinh', 'width' => 12, 'align' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    'gioi_tinh' => ['header' => 'Giới Tính', 'width' => 10, 'align' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    'chuc_vu' => ['header' => 'Chức vụ', 'width' => 10, 'align' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    'sdt' => ['header' => 'SĐT', 'width' => 12, 'align' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    'gmail' => ['header' => 'Gmail', 'width' => 30, 'align' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT],
                    'dia_chi' => ['header' => 'Địa chỉ', 'width' => 40, 'align' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT],
                    'ghi_chu' => ['header' => 'Ghi chú', 'width' => 10, 'align' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT],
                ];
            
                $active_columns = [];
                $headers = ['STT']; 
                foreach ($selected_columns as $key) {
                    if (isset($column_map[$key])) {
                        $active_columns[$key] = $column_map[$key];
                        $headers[] = $column_map[$key]['header'];
                    }
                }
                $column_count = count($headers);
                $last_column_letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($column_count);
            
                $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman')->setSize(12);
                $sheet->getColumnDimension('A')->setWidth(5); 
                foreach ($active_columns as $key => $details) {
                    $col_letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(array_search($key, array_keys($active_columns)) + 2);
                    $sheet->getColumnDimension($col_letter)->setWidth($details['width']);
                }
            
                $sheet->mergeCells('A1:D1')->setCellValue('A1', 'TRƯỜNG THPT BÌNH SƠN');
                $sheet->mergeCells('A2:D2')->setCellValue('A2', 'HỆ THỐNG ĐÁNH GIÁ THI ĐUA');
                
                $sheet->mergeCells("A3:{$last_column_letter}3")->setCellValue('A3', 'DANH SÁCH HỌC SINH');
                global $ten_nam_hoc;
                $sheet->mergeCells("A4:{$last_column_letter}4")->setCellValue('A4', mb_strtoupper($title, 'UTF-8') . ' - Năm học ' . ($ten_nam_hoc ?? 'Hiện tại'));
            
                $sheet->getStyle("A1:{$last_column_letter}4")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A1')->getFont()->setSize(11);
                $sheet->getStyle('A2')->getFont()->setSize(11)->setBold(true);
                $sheet->getStyle('A3:A4')->getFont()->setSize(13)->setBold(true);
            
                $header_row = 6;
                if ($is_class_sheet && !empty($gvcn_name)) {
                    $sheet->mergeCells("A5:{$last_column_letter}5")->setCellValue('A5', 'GVCN: ' . $gvcn_name);
                    $sheet->getStyle('A5')->getFont()->setBold(true)->setItalic(true);
                    $header_row = 7;
                }
            
                $data_row_start = $header_row + 1;
                $sheet->fromArray($headers, NULL, 'A' . $header_row);
                $sheet->getStyle('A'.$header_row.':'.$last_column_letter.$header_row)->getFont()->setBold(true);
                $sheet->getStyle('A'.$header_row.':'.$last_column_letter.$header_row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
                $rowIndex = $data_row_start;
                foreach ($data as $index => $hs) {
                    $rowData = [$index + 1];
                    foreach ($active_columns as $key => $details) {
                        switch ($key) {
                            case 'khoi': $rowData[] = substr($hs['ten_lop'], 0, 2); break;
                            case 'lop': $rowData[] = $hs['ten_lop']; break;
                            case 'nien_khoa': $rowData[] = $hs['nien_khoa'] ?? ''; break;
                            case 'ma_hs': $rowData[] = $hs['ma_hoc_sinh']; break;
                            case 'ho_ten': $rowData[] = $hs['ho_dem'] . ' ' . $hs['ten']; break;
                            case 'gmail': $rowData[] = $hs['email'] ?? ''; break;
                            case 'dia_chi':
                                $dc_parts = [];
                                if (!empty($hs['dia_chi_chi_tiet'])) $dc_parts[] = $hs['dia_chi_chi_tiet'];
                                if (!empty($hs['ap_khupho'])) $dc_parts[] = $hs['ap_khupho'];
                                if (!empty($hs['xa_phuong'])) $dc_parts[] = $hs['xa_phuong'];
                                if (!empty($hs['tinh_thanhpho'])) $dc_parts[] = $hs['tinh_thanhpho'];
                                $rowData[] = implode(', ', $dc_parts);
                                break;
                            case 'ghi_chu': $rowData[] = $hs['ghi_chu'] ?? ''; break; 
                            default: $rowData[] = $hs[$key] ?? '';
                        }
                    }
                    $sheet->fromArray($rowData, NULL, 'A' . $rowIndex);
                    $rowIndex++;
                }
                
                $last_row = $rowIndex - 1;
                if ($last_row >= $data_row_start) {
                    foreach ($active_columns as $key => $details) {
                        $col_letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(array_search($key, array_keys($active_columns)) + 2);
                        $sheet->getStyle($col_letter.$data_row_start.':'.$col_letter.$last_row)->getAlignment()->setHorizontal($details['align']);
                    }
                    $sheet->getStyle('A'.$data_row_start.':A'.$last_row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                }
            
                $table_range = 'A'.$header_row.':'.$last_column_letter.$last_row;
                $sheet->getStyle($table_range)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            
                $footer_start_row = $last_row + 1;
                $sheet->mergeCells("A{$footer_start_row}:{$last_column_letter}{$footer_start_row}")->setCellValue('A'.$footer_start_row, 'Danh sách trên có ' . count($data) . ' học sinh./.');
                $sheet->getStyle('A'.$footer_start_row)->getFont()->setItalic(true);
            
                $signature_row = $footer_start_row + 2;
                $signature_col_start_index = max(1, $column_count - 2);
                $signature_col_start_letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($signature_col_start_index);
                
                $sheet->mergeCells("{$signature_col_start_letter}{$signature_row}:{$last_column_letter}{$signature_row}")->setCellValue($signature_col_start_letter.$signature_row, 'Đồng Nai, ngày '.date('d').' tháng '.date('m').' năm '.date('Y'));
                $style_range_date = "{$signature_col_start_letter}{$signature_row}:{$last_column_letter}{$signature_row}";
                $sheet->getStyle($style_range_date)->getFont()->setItalic(true);
                $sheet->getStyle($style_range_date)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
                $signature_row++; 
                $sheet->mergeCells("{$signature_col_start_letter}{$signature_row}:{$last_column_letter}{$signature_row}")->setCellValue($signature_col_start_letter.$signature_row, 'NGƯỜI LẬP BẢNG');
                $style_range_signer_title = "{$signature_col_start_letter}{$signature_row}:{$last_column_letter}{$signature_row}";
                $sheet->getStyle($style_range_signer_title)->getFont()->setBold(true);
                $sheet->getStyle($style_range_signer_title)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                
                $signature_row += 4; 
                $sheet->mergeCells("{$signature_col_start_letter}{$signature_row}:{$last_column_letter}{$signature_row}")->setCellValue($signature_col_start_letter.$signature_row, $admin_name);
                $style_range_signer_name = "{$signature_col_start_letter}{$signature_row}:{$last_column_letter}{$signature_row}";
                $sheet->getStyle($style_range_signer_name)->getFont()->setBold(true);
                $sheet->getStyle($style_range_signer_name)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            }
        }

        $spreadsheet->removeSheetByIndex(0);
        $stmt_gvcn = $db->query("SELECT ten_lop, gvcn_ten FROM lop_hoc");
        $gvcn_list = $stmt_gvcn->fetchAll(PDO::FETCH_KEY_PAIR);

        if ($filter_lop_id !== 'all') {
            $ten_lop = $danh_sach_hoc_sinh[0]['ten_lop'];
            $gvcn_name = $gvcn_list[$ten_lop] ?? '';
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($ten_lop);
            populateSheet($sheet, 'LỚP ' . $ten_lop, $danh_sach_hoc_sinh, true, $gvcn_name, $admin_name, $selected_columns);
        } else {
            $main_sheet_title = ($filter_khoi !== 'all') ? 'Khối ' . $filter_khoi : 'Toàn Trường';
            $sheet_main = $spreadsheet->createSheet();
            $sheet_main->setTitle($main_sheet_title);
            populateSheet($sheet_main, $main_sheet_title, $danh_sach_hoc_sinh, false, null, $admin_name, $selected_columns);
            
            $students_by_class = [];
            foreach ($danh_sach_hoc_sinh as $student) {
                $students_by_class[$student['ten_lop']][] = $student;
            }
            ksort($students_by_class);
        
            foreach ($students_by_class as $ten_lop => $ds_lop) {
                $gvcn_name = $gvcn_list[$ten_lop] ?? '';
                $sheet_lop = $spreadsheet->createSheet();
                $sheet_lop->setTitle($ten_lop);
                populateSheet($sheet_lop, 'LỚP ' . $ten_lop, $ds_lop, true, $gvcn_name, $admin_name, $selected_columns);
            }
        }

        $spreadsheet->setActiveSheetIndex(0);
        $search = ['á','à','ả','ã','ạ','â','ấ','ầ','ẩ','ẫ','ậ','ă','ắ','ằ','ẳ','ẵ','ặ','đ','é','è','ẻ','ẽ','ẹ','ê','ế','ề','ể','ễ','ệ','í','ì','ỉ','ĩ','ị','ó','ò','ỏ','õ','ọ','ô','ố','ồ','ổ','ỗ','ộ','ơ','ớ','ờ','ở','ỡ','ợ','ú','ù','ủ','ũ','ụ','ư','ứ','ừ','ử','ữ','ự','ý','ỳ','ỷ','ỹ','ỵ','Á','À','Ả','Ã','Ạ','Â','Ấ','Ầ','Ẩ','Ẫ','Ậ','Ă','Ắ','Ằ','Ẳ','Ẵ','Ặ','Đ','É','È','Ẻ','Ẽ','Ẹ','Ê','Ế','Ề','Ể','Ễ','Ệ','Í','Ì','Ỉ','Ĩ','Ị','Ó','Ò','Ỏ','Õ','Ọ','Ô','Ố','Ồ','Ổ','Ỗ','Ộ','Ơ','Ớ','Ờ','Ở','Ỡ','Ợ','Ú','Ù','Ủ','Ũ','Ụ','Ư','Ứ','Ừ','Ử','Ữ','Ự','Ý','Ỳ','Ỷ','Ỹ','Ỵ'];
        $replace = ['a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','d','e','e','e','e','e','e','e','e','e','e','e','i','i','i','i','i','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','u','u','u','u','u','u','u','u','u','u','u','y','y','y','y','y','A','A','A','A','A','A','A','A','A','A','A','A','A','A','A','A','A','D','E','E','E','E','E','E','E','E','E','E','E','I','I','I','I','I','O','O','O','O','O','O','O','O','O','O','O','O','O','O','O','O','O','U','U','U','U','U','U','U','U','U','U','U','Y','Y','Y','Y','Y'];
        $title_khong_dau = str_replace($search, $replace, $title);
        $file_name = "DS_HocSinh_" . trim(preg_replace('/[^a-zA-Z0-9]+/', '_', $title_khong_dau), '_') . "_" . date('Ymd_His') . ".xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $file_name . '"');
        header('Cache-Control: max-age=0');
        ob_clean();
        flush();
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
        break;

    case 'view_profile':
        // ==========================================
        // 9. XEM HỒ SƠ HỌC SINH
        // ==========================================
        $student_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$student_id) {
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'ID học sinh không hợp lệ.'];
            $redirect_url = '/thidua/admin/tra-cuu-hoc-sinh' . (isset($_GET['iframe']) ? '?iframe=1' : '');
            header("Location: $redirect_url");
            exit();
        }
        try {
            $stmt_info = $db->prepare("
                SELECT hs.*, lh.ten_lop, lh.gvcn_ten 
                FROM hoc_sinh hs 
                LEFT JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id 
                WHERE hs.id = ?
            ");
            $stmt_info->execute([$student_id]);
            $hoc_sinh = $stmt_info->fetch();
        
            if (!$hoc_sinh) {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Không tìm thấy học sinh.'];
                $redirect_url = '/thidua/admin/tra-cuu-hoc-sinh' . (isset($_GET['iframe']) ? '?iframe=1' : '');
                header("Location: $redirect_url");
                exit();
            }
        
            $stmt_logins = $db->prepare("SELECT * FROM lich_su_dang_nhap WHERE hoc_sinh_id = ? ORDER BY id DESC");
            $stmt_logins->execute([$student_id]);
            $login_history = $stmt_logins->fetchAll();
        
            $stmt_violations = $db->prepare("
                SELECT vphs.ngay_vi_pham, chvp.ten_vi_pham, chvp.diem_tru, vphs.ghi_chu, th.ten_tuan, nh.ten_nam_hoc, COALESCE(vphs.raw_ten_lop, lh.ten_lop) as ten_lop 
                FROM vi_pham_hoc_sinh vphs 
                JOIN quatrinh_hoc_tap qt ON vphs.hoc_sinh_id = qt.id
                JOIN ho_so_hoc_sinh hs_main ON qt.ma_hoc_sinh = hs_main.ma_hoc_sinh
                LEFT JOIN lop_hoc lh ON qt.lop_hoc_id = lh.id 
                JOIN cau_hinh_vi_pham chvp ON vphs.vi_pham_id = chvp.id 
                JOIN tuan_hoc th ON vphs.tuan_hoc_id = th.id
                LEFT JOIN nam_hoc nh ON th.nam_hoc_id = nh.id
                WHERE hs_main.ma_hoc_sinh = ?
                ORDER BY th.ngay_bat_dau DESC, vphs.ngay_vi_pham DESC
            ");
            $stmt_violations->execute([$hoc_sinh['ma_hoc_sinh']]);
            $violations_list = $stmt_violations->fetchAll();
        
            $stmt_rewards = $db->prepare("
                SELECT kt.ngay_khen_thuong, kt.ten_khen_thuong, kt.so_quyet_dinh, kt.cap_khen_thuong, kt.ghi_chu, nh.ten_nam_hoc, lh.ten_lop 
                FROM khen_thuong kt 
                JOIN quatrinh_hoc_tap qt ON kt.hoc_sinh_id = qt.id 
                JOIN ho_so_hoc_sinh hs_main ON qt.ma_hoc_sinh = hs_main.ma_hoc_sinh 
                LEFT JOIN lop_hoc lh ON qt.lop_hoc_id = lh.id 
                LEFT JOIN nam_hoc nh ON kt.nam_hoc_id = nh.id 
                WHERE kt.loai = 'ca_nhan' AND hs_main.ma_hoc_sinh = ? 
                ORDER BY kt.ngay_khen_thuong DESC, kt.id DESC
            ");
            $stmt_rewards->execute([$hoc_sinh['ma_hoc_sinh']]);
            $rewards_list = $stmt_rewards->fetchAll();

            $stmt_activities = $db->prepare("
                SELECT hd.ten_hoat_dong, hddk.created_at as ngay_tham_gia, hddk.trang_thai_diem_danh, hddk.diem_thuc_te, hd.diem_tich_luy 
                FROM hoat_dong_dang_ky hddk
                JOIN hoat_dong hd ON hddk.hoat_dong_id = hd.id
                JOIN ho_so_hoc_sinh hs_main ON (hddk.hoc_sinh_id = hs_main.id OR hddk.hoc_sinh_id IN (SELECT id FROM quatrinh_hoc_tap WHERE ma_hoc_sinh = hs_main.ma_hoc_sinh))
                WHERE hs_main.ma_hoc_sinh = ?
                ORDER BY hddk.created_at DESC
            ");
            $stmt_activities->execute([$hoc_sinh['ma_hoc_sinh']]);
            $activities_list = $stmt_activities->fetchAll();
        } catch (Exception $e) {
            die("Lỗi CSDL khi tải hồ sơ học sinh: " . $e->getMessage());
        }
        require_once __DIR__ . '/../views/xem_ho_so_hoc_sinh.php';
        break;

    case 'export_profile':
        // ==========================================
        // 10. XUẤT EXCEL HỒ SƠ HỌC SINH
        // ==========================================
        require_once __DIR__ . '/../../vendor/autoload.php';
        
        $hoc_sinh_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$hoc_sinh_id) { exit('ID học sinh không hợp lệ.'); }
        
        try {
            $admin_name = $_SESSION['user_ten'] ?? 'Quản trị viên';
            
            $stmt_hs = $db->prepare("SELECT hs.*, lh.ten_lop, lh.gvcn_ten FROM hoc_sinh hs LEFT JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id WHERE hs.id = ?");
            $stmt_hs->execute([$hoc_sinh_id]);
            $hoc_sinh = $stmt_hs->fetch(PDO::FETCH_ASSOC);
        
            if (!$hoc_sinh) exit('Không tìm thấy học sinh.');
        
            $stmt_kt = $db->prepare("SELECT kt.ngay_khen_thuong, kt.ten_khen_thuong, kt.cap_khen_thuong, kt.so_quyet_dinh FROM khen_thuong kt WHERE (kt.hoc_sinh_id = ? OR kt.hoc_sinh_id IN (SELECT id FROM quatrinh_hoc_tap WHERE ma_hoc_sinh = ?)) AND kt.loai = 'ca_nhan' ORDER BY kt.ngay_khen_thuong DESC");
            $stmt_kt->execute([$hoc_sinh_id, $hoc_sinh['ma_hoc_sinh']]);
            $lich_su_khen_thuong = $stmt_kt->fetchAll(PDO::FETCH_ASSOC);
        
            $stmt_vp = $db->prepare("
                SELECT vphs.ngay_vi_pham, (CONCAT(hs.ho_dem, ' ', hs.ten)) as ho_ten, chvp.ten_vi_pham, vphs.ghi_chu 
                FROM vi_pham_hoc_sinh vphs 
                JOIN cau_hinh_vi_pham chvp ON vphs.vi_pham_id = chvp.id 
                JOIN quatrinh_hoc_tap qt ON vphs.hoc_sinh_id = qt.id JOIN hoc_sinh hs ON hs.ma_hoc_sinh = qt.ma_hoc_sinh AND hs.nam_hoc_id = qt.nam_hoc_id
                WHERE hs.id = ? ORDER BY vphs.ngay_vi_pham DESC
            ");
            $stmt_vp->execute([$hoc_sinh_id]);
            $lich_su_vi_pham = $stmt_vp->fetchAll(PDO::FETCH_ASSOC);
        
            $stmt_hd = $db->prepare("
                SELECT hd.ten_hoat_dong, hddk.created_at as ngay_tham_gia, hddk.trang_thai_diem_danh, hddk.diem_thuc_te, hd.diem_tich_luy 
                FROM hoat_dong_dang_ky hddk
                JOIN hoat_dong hd ON hddk.hoat_dong_id = hd.id
                JOIN ho_so_hoc_sinh hs_main ON (hddk.hoc_sinh_id = hs_main.id OR hddk.hoc_sinh_id IN (SELECT id FROM quatrinh_hoc_tap WHERE ma_hoc_sinh = hs_main.ma_hoc_sinh))
                WHERE hs_main.ma_hoc_sinh = ?
                ORDER BY hddk.created_at DESC
            ");
            $stmt_hd->execute([$hoc_sinh['ma_hoc_sinh']]);
            $lich_su_hoat_dong = $stmt_hd->fetchAll(PDO::FETCH_ASSOC);
        
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $spreadsheet->getDefaultStyle()->getFont()->setName('Times New Roman')->setSize(11);
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('HoSoTongHop');
            
            $sheet->mergeCells('A1:C1')->setCellValue('A1', 'TRƯỜNG THPT BÌNH SƠN');
            $sheet->mergeCells('A2:C2')->setCellValue('A2', 'HỆ THỐNG ĐÁNH GIÁ THI ĐUA');
            $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A2')->getFont()->setBold(true);
        
            $sheet->mergeCells('A4:E4')->setCellValue('A4', 'HỒ SƠ HỌC SINH TỔNG HỢP');
            $sheet->getStyle('A4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(14);
            
            $current_row = 6;
            $sheet->mergeCells("A{$current_row}:E{$current_row}")->setCellValue("A{$current_row}", 'I. THÔNG TIN CÁ NHÂN');
            $sheet->getStyle("A{$current_row}")->getFont()->setBold(true)->setSize(12);
            $current_row++;
            
            if (!empty($hoc_sinh['anh_the']) || !empty($hoc_sinh['anh_the_cloud_key'])) {
                $imagePath = null;
                $driver = $hoc_sinh['anh_the_driver'] ?? 'local';
                
                if ($driver === 'local' || empty($driver)) {
                    $localCandidate = __DIR__ . '/../../public/assets/anh_the/' . ltrim($hoc_sinh['anh_the'], '/');
                    if (file_exists($localCandidate)) {
                        $imagePath = $localCandidate;
                    }
                } else {
                    // Fetch from cloud using the local API
                    $key = $hoc_sinh['anh_the_cloud_key'] ?? $hoc_sinh['anh_the'];
                    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
                    $url = "{$protocol}://{$host}/thidua/api/get-presigned-url?key=" . urlencode($key) . "&driver=" . urlencode($driver) . "&inline=1";
                    
                    if (session_status() === PHP_SESSION_ACTIVE) {
                        session_write_close();
                    }
                    
                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
                    $imgData = curl_exec($ch);
                    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    
                    if ($httpcode == 200 && $imgData) {
                        $tempPath = sys_get_temp_dir() . '/' . uniqid('avatar_') . '.jpg';
                        file_put_contents($tempPath, $imgData);
                        $imagePath = $tempPath;
                        // Temp file will be deleted later if needed, but OS cleans it up
                    }
                }
                
                if ($imagePath && file_exists($imagePath)) {
                    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                    $drawing->setName('AnhThe');
                    $drawing->setDescription('Ảnh thẻ học sinh');
                    $drawing->setPath($imagePath);
                    $drawing->setCoordinates('D' . $current_row); 
                    $drawing->setHeight(160); 
                    $drawing->setOffsetX(10);
                    $drawing->setOffsetY(5);
                    $drawing->setWorksheet($sheet);
                    $sheet->mergeCells("D{$current_row}:E" . ($current_row + 4));
                }
            }
            
            $dia_chi_parts = [];
            if (!empty($hoc_sinh['dia_chi_chi_tiet'])) $dia_chi_parts[] = $hoc_sinh['dia_chi_chi_tiet'];
            if (!empty($hoc_sinh['ap_khupho'])) $dia_chi_parts[] = $hoc_sinh['ap_khupho'];
            if (!empty($hoc_sinh['xa_phuong'])) $dia_chi_parts[] = $hoc_sinh['xa_phuong'];
            if (!empty($hoc_sinh['tinh_thanhpho'])) $dia_chi_parts[] = $hoc_sinh['tinh_thanhpho'];
            $dia_chi_full = !empty($dia_chi_parts) ? implode(', ', $dia_chi_parts) : 'Chưa có';

            $info_labels = ['Họ và tên:', 'Số CCCD:', 'Lớp:', 'Niên khóa:', 'GVCN:', 'Ngày sinh:', 'Địa chỉ:', 'Trạng thái:'];
            $info_values = [
                $hoc_sinh['ho_dem'] . ' ' . $hoc_sinh['ten'],
                $hoc_sinh['ma_hoc_sinh'],
                $hoc_sinh['ten_lop'],
                $hoc_sinh['nien_khoa'] ?? 'Chưa cập nhật',
                $hoc_sinh['gvcn_ten'] ?? 'Chưa có',
                (function($date_str) {
                    $date = DateTime::createFromFormat('Y-m-d', $date_str) ?: DateTime::createFromFormat('d/m/Y', $date_str);
                    return $date ? $date->format('d/m/Y') : 'Không hợp lệ';
                })($hoc_sinh['ngay_sinh']),
                $dia_chi_full,
                ($hoc_sinh['trang_thai_hoc_tap'] === 'nghi_hoc' ? 'Đã nghỉ học' : 'Đang học')
            ];
        
            foreach ($info_labels as $index => $label) {
                $sheet->setCellValue("A{$current_row}", $label);
                $sheet->mergeCells("B{$current_row}:C{$current_row}")->setCellValue("B{$current_row}", $info_values[$index]);
                $current_row++;
            }
            $sheet->getStyle("A".($current_row - count($info_labels)).":A".($current_row-1))->getFont()->setBold(true);
            $sheet->getStyle("B".($current_row - count($info_labels)).":C".($current_row-1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $current_row++;
        
            $sheet->mergeCells("A{$current_row}:E{$current_row}")->setCellValue("A{$current_row}", 'II. LỊCH SỬ KHEN THƯỞNG');
            $sheet->getStyle("A{$current_row}")->getFont()->setBold(true)->setSize(12);
            $current_row++;
            if (!empty($lich_su_khen_thuong)) {
                $header_kt = ['STT', 'Ngày KT', 'Tên KT', 'Cấp KT', 'Số QĐ'];
                $sheet->fromArray($header_kt, NULL, "A{$current_row}");
                $header_range = "A{$current_row}:E{$current_row}";
                $sheet->getStyle($header_range)->getFont()->setBold(true);
                $sheet->getStyle($header_range)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $start_data_row = ++$current_row;
                foreach($lich_su_khen_thuong as $index => $item) {
                    $sheet->fromArray([$index + 1, date('d/m/Y', strtotime($item['ngay_khen_thuong'])), $item['cap_khen_thuong'], $item['ten_khen_thuong'], $item['so_quyet_dinh']], NULL, "A{$current_row}");
                    $current_row++;
                }
                $table_range = "A".($start_data_row-1).":E".($current_row-1);
                $sheet->getStyle($table_range)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            } else {
                $sheet->setCellValue("A{$current_row}", 'Chưa có khen thưởng nào.');
                $current_row++;
            }
            $current_row++;
        
            $sheet->mergeCells("A{$current_row}:E{$current_row}")->setCellValue("A{$current_row}", 'III. LỊCH SỬ VI PHẠM');
            $sheet->getStyle("A{$current_row}")->getFont()->setBold(true)->setSize(12);
            $current_row++;
            if (!empty($lich_su_vi_pham)) {
                $header_vp = ['STT', 'Ngày VP', 'Họ và tên', 'Tên Lỗi', 'Ghi Chú'];
                $sheet->fromArray($header_vp, NULL, "A{$current_row}");
                $header_range = "A{$current_row}:E{$current_row}"; 
                $sheet->getStyle($header_range)->getFont()->setBold(true);
                $sheet->getStyle($header_range)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $start_data_row = ++$current_row;
                foreach($lich_su_vi_pham as $index => $item) {
                    $sheet->fromArray([$index + 1, date('d/m/Y', strtotime($item['ngay_vi_pham'])), $item['ho_ten'], $item['ten_vi_pham'], $item['ghi_chu']], NULL, "A{$current_row}");
                    $current_row++;
                }
                $table_range = "A".($start_data_row-1).":E".($current_row-1); 
                $sheet->getStyle($table_range)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            } else {
                $sheet->setCellValue("A{$current_row}", 'Không có vi phạm nào.');
                $current_row++;
            }
            $current_row++;

            $sheet->mergeCells("A{$current_row}:E{$current_row}")->setCellValue("A{$current_row}", 'IV. HOẠT ĐỘNG THAM GIA');
            $sheet->getStyle("A{$current_row}")->getFont()->setBold(true)->setSize(12);
            $current_row++;
            if (!empty($lich_su_hoat_dong)) {
                $header_hd = ['STT', 'Ngày tham gia', 'Tên hoạt động', 'Trạng thái', 'Điểm cộng'];
                $sheet->fromArray($header_hd, NULL, "A{$current_row}");
                $header_range = "A{$current_row}:E{$current_row}";
                $sheet->getStyle($header_range)->getFont()->setBold(true);
                $sheet->getStyle($header_range)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $start_data_row = ++$current_row;
                foreach($lich_su_hoat_dong as $index => $item) {
                    $trang_thai_text = $item['trang_thai_diem_danh'] == 1 ? 'Đã tham gia' : 'Chưa điểm danh';
                    $diem_text = $item['trang_thai_diem_danh'] == 1 ? ('+' . (float)$item['diem_thuc_te'] . ' đ') : '0 đ';
                    $sheet->fromArray([$index + 1, date('d/m/Y', strtotime($item['ngay_tham_gia'])), $item['ten_hoat_dong'], $trang_thai_text, $diem_text], NULL, "A{$current_row}");
                    $current_row++;
                }
                $table_range = "A".($start_data_row-1).":E".($current_row-1);
                $sheet->getStyle($table_range)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            } else {
                $sheet->setCellValue("A{$current_row}", 'Chưa tham gia hoạt động nào.');
                $current_row++;
            }
            
            $current_row += 2;
            $sheet->mergeCells("D{$current_row}:E{$current_row}")->setCellValue('D'.$current_row, 'Đồng Nai, ngày '.date('d').' tháng '.date('m').' năm '.date('Y'));
            $sheet->getStyle("D{$current_row}")->getFont()->setItalic(true);
            $sheet->getStyle("D{$current_row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $current_row++;
            $sheet->mergeCells("D{$current_row}:E{$current_row}")->setCellValue('D'.$current_row, 'NGƯỜI LẬP BẢNG');
            $sheet->getStyle("D{$current_row}")->getFont()->setBold(true);
            $sheet->getStyle("D{$current_row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $current_row += 5;
            $sheet->mergeCells("D{$current_row}:E{$current_row}")->setCellValue('D'.$current_row, $admin_name);
            $sheet->getStyle("D{$current_row}")->getFont()->setBold(true);
            $sheet->getStyle("D{$current_row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
            $sheet->getColumnDimension('A')->setWidth(15); 
            $sheet->getColumnDimension('B')->setWidth(25); 
            $sheet->getColumnDimension('C')->setWidth(20); 
            $sheet->getColumnDimension('D')->setWidth(20); 
            $sheet->getColumnDimension('E')->setWidth(20); 
        
            $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
            $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
            $sheet->getPageMargins()->setTop(0.4)->setBottom(0.4)->setLeft(0.2)->setRight(0.2);
        
            $raw_name = ($hoc_sinh['ho_dem'] ?? '') . ' ' . ($hoc_sinh['ten'] ?? '');
            $ascii_name = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/u", "a", $raw_name);
            $ascii_name = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/u", "e", $ascii_name);
            $ascii_name = preg_replace("/(ì|í|ị|ỉ|ĩ)/u", "i", $ascii_name);
            $ascii_name = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/u", "o", $ascii_name);
            $ascii_name = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/u", "u", $ascii_name);
            $ascii_name = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/u", "y", $ascii_name);
            $ascii_name = preg_replace("/(đ)/u", "d", $ascii_name);
            $ascii_name = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/u", "A", $ascii_name);
            $ascii_name = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/u", "E", $ascii_name);
            $ascii_name = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/u", "I", $ascii_name);
            $ascii_name = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/u", "O", $ascii_name);
            $ascii_name = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/u", "U", $ascii_name);
            $ascii_name = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/u", "Y", $ascii_name);
            $ascii_name = preg_replace("/(Đ)/u", "D", $ascii_name);
            $safe_name = preg_replace('/[^A-Za-z0-9\-]/', '_', $ascii_name);
            $safe_name = preg_replace('/_+/', '_', trim($safe_name, '_'));

            $filename = "HoSoHocSinh_" . $hoc_sinh['ma_hoc_sinh'] . "_" . $safe_name . ".xlsx";
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

    default:
        $redirect_url = '/thidua/admin/hoc-sinh' . (isset($_GET['iframe']) ? '?iframe=1' : '');
        header("Location: $redirect_url");
        exit();
}
