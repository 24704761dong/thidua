<?php
// File: src/controllers/CtvController.php
if (function_exists('opcache_invalidate')) { opcache_invalidate(__FILE__, true); }
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Kiểm tra quyền (Ngoại trừ action kích hoạt CTV dành cho học sinh)
$action = $_GET['action'] ?? 'manage';

if ($action === 'api_kich_hoat_ctv') {
    if (!isset($_SESSION['student_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập.']);
        exit();
    }
} else {
    // Các action còn lại dành cho admin/user
    if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' || strpos($_SERVER['REQUEST_URI'], '/api/') !== false || strpos($action, 'api_') === 0) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập.']);
            exit();
        }
        header('Location: /thidua/tracuu');
        exit();
    }
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../lib/hoc_sinh_db.php';
require_once __DIR__ . '/../lib/lop_hoc_db.php';
require_once __DIR__ . '/../lib/helpers.php';

$db = get_db_connection();
$current_nam_hoc = $_SESSION['current_nam_hoc_id'] ?? 1;

switch ($action) {
    case 'manage':
        // ==========================================
        // 1. GIAO DIỆN QUẢN LÝ CTV
        // ==========================================
        $filter_khoi = $_GET['khoi'] ?? 'all';
        $filter_lop_id = $_GET['lop_id'] ?? 'all';
        $filter_chuc_vu = $_GET['chuc_vu'] ?? 'all';
        $filter_keyword = trim($_GET['keyword'] ?? '');
        $filter_has_permission = isset($_GET['has_permission']);

        $filters = [
            'khoi' => $filter_khoi,
            'lop_id' => $filter_lop_id,
            'chuc_vu' => $filter_chuc_vu,
            'keyword' => $filter_keyword,
            'has_permission' => $filter_has_permission,
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

        require_once __DIR__ . '/../views/quan_ly_ctv.php';
        break;

    case 'manage_codes':
        // ==========================================
        // 2. GIAO DIỆN QUẢN LÝ MÃ CTV
        // ==========================================
        $ds_lop = get_all_lop_hoc($db);
        $stmt_khoi = $db->query("SELECT DISTINCT SUBSTR(ten_lop, 1, 2) as khoi FROM lop_hoc ORDER BY khoi ASC");
        $ds_khoi = $stmt_khoi->fetchAll(PDO::FETCH_ASSOC);
        $stmt_tuan = $db->query("SELECT id, ten_tuan FROM tuan_hoc ORDER BY ngay_bat_dau DESC");
        $ds_tuan = $stmt_tuan->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../views/admin_quan_ly_ma_ctv.php';
        break;

    case 'api_create_code':
        // ==========================================
        // 3. API TẠO MÃ CTV (TÙY CHỈNH)
        // ==========================================
        header('Content-Type: application/json');
        function ctv_generate_unique_code($db) {
            do {
                $code = mt_rand(100000, 999999);
                $stmt = $db->prepare("SELECT id FROM ma_kich_hoat_ctv WHERE ma_kich_hoat = ?");
                $stmt->execute([$code]);
            } while ($stmt->fetch());
            return $code;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        try {
            $new_code = ctv_generate_unique_code($db);
            $start_time = empty($input['thoi_gian_bat_dau']) ? null : $input['thoi_gian_bat_dau'];
            $end_time = empty($input['thoi_gian_het_han']) ? null : $input['thoi_gian_het_han'];

            $now = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
            $status = 'active'; 
            if ($start_time && new DateTime($start_time) > $now) {
                $status = 'pending';
            }

            $stmt = $db->prepare(
                "INSERT INTO ma_kich_hoat_ctv 
                    (ma_kich_hoat, ten_dot_kich_hoat, doi_tuong_ap_dung, so_luong_toi_da, thoi_gian_bat_dau, thoi_gian_het_han, trang_thai, ngay_tao, nam_hoc_id) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            $stmt->execute([
                $new_code,
                $input['ten_dot_kich_hoat'],
                $input['doi_tuong_ap_dung'],
                $input['so_luong_toi_da'],
                $start_time,
                $end_time,
                $status,
                date('Y-m-d H:i:s'),
                $current_nam_hoc
            ]);

            echo json_encode(['success' => true, 'message' => 'Tạo mã tùy chỉnh thành công!', 'new_code' => $new_code]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
        }
        break;

    case 'api_create_daily_codes':
        // ==========================================
        // 4. API TẠO MÃ CTV HẰNG NGÀY
        // ==========================================
        header('Content-Type: application/json');
        function ctv_generate_unique_code_daily($db) {
            do {
                $code = mt_rand(100000, 999999);
                $stmt = $db->prepare("SELECT id FROM ma_kich_hoat_ctv WHERE ma_kich_hoat = ?");
                $stmt->execute([$code]);
            } while ($stmt->fetch());
            return $code;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $tuan_id = $input['tuan_id'] ?? null;
        $doi_tuong = $input['doi_tuong_ap_dung'] ?? null;
        $so_luong = $input['so_luong_toi_da'] ?? null;

        if (!$tuan_id || !$doi_tuong || !$so_luong) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin.']);
            exit();
        }

        try {
            $db->beginTransaction();
            $stmt_week = $db->prepare("SELECT ten_tuan, ngay_bat_dau, ngay_ket_thuc FROM tuan_hoc WHERE id = ?");
            $stmt_week->execute([$tuan_id]);
            $tuan = $stmt_week->fetch();

            if (!$tuan) throw new Exception("Không tìm thấy tuần học.");

            $start_date = new DateTime($tuan['ngay_bat_dau']);
            $end_date = new DateTime($tuan['ngay_ket_thuc']);
            $end_date->modify('+1 day');

            $interval = new DateInterval('P1D');
            $date_range = new DatePeriod($start_date, $interval, $end_date);

            $today_str = (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d');
            $codes_created = 0;

            $stmt_insert = $db->prepare(
                "INSERT INTO ma_kich_hoat_ctv (ma_kich_hoat, ten_dot_kich_hoat, doi_tuong_ap_dung, so_luong_toi_da, thoi_gian_bat_dau, thoi_gian_het_han, trang_thai, ngay_tao, nam_hoc_id) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            foreach ($date_range as $date) {
                $current_day_str = $date->format('Y-m-d');
                $new_code = ctv_generate_unique_code_daily($db);
                $ten_dot = "Mã CTV ngày " . $date->format('d/m') . " - " . $tuan['ten_tuan'];
                $start_time = $current_day_str . ' 00:01:00';
                $end_time = $current_day_str . ' 23:59:59';
                
                if ($current_day_str < $today_str) {
                    $status = 'inactive';
                } elseif ($current_day_str == $today_str) {
                    $status = 'active';
                } else {
                    $status = 'pending';
                }

                $stmt_insert->execute([
                    $new_code, $ten_dot, $doi_tuong, $so_luong, $start_time, $end_time, $status, date('Y-m-d H:i:s'), $current_nam_hoc
                ]);
                $codes_created++;
            }

            $db->commit();
            echo json_encode(['success' => true, 'message' => "Đã tạo thành công {$codes_created} mã kích hoạt cho tuần đã chọn."]);
        } catch (Exception $e) {
            if ($db->inTransaction()) $db->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
        break;

    case 'api_get_codes':
        // ==========================================
        // 5. API LẤY DANH SÁCH MÃ CTV
        // ==========================================
        header('Content-Type: application/json');
        try {
            $sql = "
                SELECT 
                    m.*, 
                    COUNT(l.id) as so_luong_da_dung
                FROM ma_kich_hoat_ctv m
                LEFT JOIN lich_su_su_dung_ma_ctv l ON m.id = l.ma_ctv_id
                WHERE m.nam_hoc_id = ?
                GROUP BY m.id
                ORDER BY m.ngay_tao DESC
            ";
            $stmt = $db->prepare($sql);
            $stmt->execute([$current_nam_hoc]);
            $codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $codes]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    case 'api_delete_codes':
        // ==========================================
        // 6. API XÓA MÃ CTV
        // ==========================================
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $ids = $input['ids'] ?? null;
        if (empty($ids) || !is_array($ids)) {
            echo json_encode(['success' => false, 'message' => 'Không có mã nào được chọn để xóa.']);
            exit();
        }
        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $db->prepare("DELETE FROM ma_kich_hoat_ctv WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            $deleted_count = $stmt->rowCount();
            echo json_encode(['success' => true, 'message' => "Đã xóa thành công {$deleted_count} mã kích hoạt."]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
        }
        break;

    case 'api_toggle_code':
        // ==========================================
        // 7. API ĐỔI TRẠNG THÁI MÃ CTV
        // ==========================================
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $code_id = $input['id'] ?? null;
        if (!$code_id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu ID của mã.']);
            exit();
        }
        try {
            $db->beginTransaction();
            $stmt = $db->prepare("SELECT trang_thai FROM ma_kich_hoat_ctv WHERE id = ?");
            $stmt->execute([$code_id]);
            $current_status = $stmt->fetchColumn();
            if ($current_status === false) throw new Exception('Không tìm thấy mã.');

            $new_status = ($current_status === 'active') ? 'inactive' : 'active';
            $message = '';

            if ($new_status === 'inactive') {
                $stmt_users = $db->prepare("SELECT hoc_sinh_id FROM lich_su_su_dung_ma_ctv WHERE ma_ctv_id = ?");
                $stmt_users->execute([$code_id]);
                $student_ids = $stmt_users->fetchAll(PDO::FETCH_COLUMN);

                $revoked_count = 0;
                if (!empty($student_ids)) {
                    $stmt_get_perm = $db->prepare("SELECT quyen_truy_cap FROM hoc_sinh WHERE id = ?");
                    $stmt_update_perm = $db->prepare("UPDATE hoc_sinh SET quyen_truy_cap = ? WHERE id = ?");
                    foreach ($student_ids as $student_id) {
                        $stmt_get_perm->execute([$student_id]);
                        $permissions = json_decode($stmt_get_perm->fetchColumn() ?: '{}', true);
                        if (isset($permissions['nhap_vi_pham']) && $permissions['nhap_vi_pham'] === true) {
                            unset($permissions['nhap_vi_pham']); 
                            $stmt_update_perm->execute([json_encode($permissions), $student_id]);
                            $revoked_count++;
                        }
                    }
                }
                $message = "Đã ngừng hoạt động mã và thu hồi quyền của " . $revoked_count . " học sinh.";
            } else {
                $message = "Đã kích hoạt lại mã. Mã này giờ có thể được sử dụng bởi học sinh mới.";
            }

            $stmt_update_code = $db->prepare("UPDATE ma_kich_hoat_ctv SET trang_thai = ? WHERE id = ?");
            $stmt_update_code->execute([$new_status, $code_id]);

            $db->commit();
            echo json_encode(['success' => true, 'message' => $message, 'new_status' => $new_status]);
        } catch (Exception $e) {
            if ($db->inTransaction()) $db->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
        }
        break;

    case 'api_get_code_users':
        // ==========================================
        // 8. API LẤY DANH SÁCH HỌC SINH ĐÃ DÙNG MÃ
        // ==========================================
        header('Content-Type: application/json');
        $code_id = $_GET['id'] ?? null;
        if (!$code_id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu ID của mã.']);
            exit();
        }
        try {
            $sql = "
                SELECT hs.ma_hoc_sinh, hs.ho_dem, hs.ten, lh.ten_lop, l.ngay_kich_hoat
                FROM lich_su_su_dung_ma_ctv l
                JOIN hoc_sinh hs ON l.hoc_sinh_id = hs.id
                JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id
                WHERE l.ma_ctv_id = ?
                ORDER BY l.ngay_kich_hoat DESC
            ";
            $stmt = $db->prepare($sql);
            $stmt->execute([$code_id]);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $users]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
        }
        break;

    case 'api_kich_hoat_ctv':
        // ==========================================
        // 9. API HỌC SINH KÍCH HOẠT MÃ CTV
        // ==========================================
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $ma_kich_hoat = $input['ma_kich_hoat'] ?? null;
        $student_id = $_SESSION['student_id'];

        if (empty($ma_kich_hoat)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mã.']);
            exit();
        }
        try {
            $db->beginTransaction();
            $stmt = $db->prepare("SELECT * FROM ma_kich_hoat_ctv WHERE ma_kich_hoat = ? AND nam_hoc_id = ?");
            $stmt->execute([$ma_kich_hoat, $current_nam_hoc]);
            $code = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$code) { throw new Exception("Mã không hợp lệ."); }
            if ($code['trang_thai'] === 'inactive') { throw new Exception("Mã đã bị ngừng hoạt động."); }
            if ($code['trang_thai'] === 'pending') { throw new Exception("Mã này chưa đến thời gian kích hoạt."); }
            
            $now = new DateTime();
            if ($code['thoi_gian_bat_dau'] && $now < new DateTime($code['thoi_gian_bat_dau'])) {
                throw new Exception("Chưa đến thời gian hiệu lực của mã.");
            }
            if ($code['thoi_gian_het_han'] && $now > new DateTime($code['thoi_gian_het_han'])) {
                throw new Exception("Mã này đã hết hạn sử dụng.");
            }
            
            $stmt_count = $db->prepare("SELECT COUNT(*) FROM lich_su_su_dung_ma_ctv WHERE ma_ctv_id = ?");
            $stmt_count->execute([$code['id']]);
            if ($stmt_count->fetchColumn() >= $code['so_luong_toi_da']) {
                throw new Exception("Mã đã hết lượt sử dụng.");
            }

            $stmt_student = $db->prepare("SELECT lop_hoc_id, SUBSTR(lh.ten_lop, 1, 2) as khoi FROM hoc_sinh hs JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id WHERE hs.id = ?");
            $stmt_student->execute([$student_id]);
            $student_info = $stmt_student->fetch(PDO::FETCH_ASSOC);
            
            $is_eligible = false;
            if ($code['doi_tuong_ap_dung'] === 'all') $is_eligible = true;
            elseif (strpos($code['doi_tuong_ap_dung'], 'khoi_') === 0 && 'khoi_' . $student_info['khoi'] === $code['doi_tuong_ap_dung']) $is_eligible = true;
            elseif (strpos($code['doi_tuong_ap_dung'], 'lop_') === 0 && 'lop_' . $student_info['lop_hoc_id'] === $code['doi_tuong_ap_dung']) $is_eligible = true;
            if (!$is_eligible) { throw new Exception("Bạn không thuộc đối tượng áp dụng của mã này."); }
            
            $stmt_quyen = $db->prepare("SELECT quyen_truy_cap FROM hoc_sinh WHERE id = ?");
            $stmt_quyen->execute([$student_id]);
            $current_permissions = json_decode($stmt_quyen->fetchColumn() ?: '{}', true);
            $current_permissions['nhap_vi_pham'] = true;

            $stmt_update = $db->prepare("UPDATE hoc_sinh SET quyen_truy_cap = ? WHERE id = ?");
            $stmt_update->execute([json_encode($current_permissions), $student_id]);

            $stmt_log = $db->prepare("INSERT INTO lich_su_su_dung_ma_ctv (ma_ctv_id, hoc_sinh_id, ngay_kich_hoat) VALUES (?, ?, ?)");
            $stmt_log->execute([$code['id'], $student_id, date('Y-m-d H:i:s')]);

            $db->commit();
            $_SESSION['student_permissions'] = $current_permissions;

            echo json_encode(['success' => true, 'message' => 'Kích hoạt thành công!']);
        } catch (Exception $e) {
            if ($db->inTransaction()) $db->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'api_bulk_grant_permissions':
        // ==========================================
        // 10. API CẤP QUYỀN HÀNG LOẠT
        // ==========================================
        header('Content-Type: application/json');
        $response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ.'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $target_type = $data['target_type'] ?? null;
            $target_value = $data['target_value'] ?? null;
            $permissions_to_grant = $data['permissions'] ?? [];
            
            if ($target_type && ($target_type === 'all' || $target_value)) {
                try {
                    $student_ids = [];
                    if ($target_type === 'all') {
                        $stmt = $db->query("SELECT id FROM hoc_sinh");
                        $student_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    } elseif ($target_type === 'chuc_vu') {
                        $stmt = $db->prepare("SELECT id FROM hoc_sinh WHERE chuc_vu = ?");
                        $stmt->execute([$target_value]);
                        $student_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    } elseif ($target_type === 'lop') {
                        $stmt = $db->prepare("SELECT id FROM hoc_sinh WHERE lop_hoc_id = ?");
                        $stmt->execute([$target_value]);
                        $student_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    } elseif ($target_type === 'hoc_sinh' && is_array($target_value)) {
                        $student_ids = $target_value;
                    }

                    if (empty($student_ids)) throw new Exception('Không tìm thấy học sinh nào phù hợp với tiêu chí.');

                    $db->beginTransaction();
                    $stmt_get = $db->prepare("SELECT quyen_truy_cap FROM hoc_sinh WHERE id = ?");
                    $stmt_update = $db->prepare("UPDATE hoc_sinh SET quyen_truy_cap = ? WHERE id = ?");

                    foreach($student_ids as $id) {
                        $stmt_get->execute([$id]);
                        $current_permissions = json_decode($stmt_get->fetchColumn() ?? '{}', true);
                        foreach($permissions_to_grant as $key => $value) {
                            if ($value === true) $current_permissions[$key] = true;
                        }
                        $stmt_update->execute([json_encode($current_permissions), $id]);
                    }
                    $db->commit();
                    $response = ['success' => true, 'message' => 'Đã cấp quyền thành công cho ' . count($student_ids) . ' học sinh.'];
                } catch (Exception $e) {
                    if ($db->inTransaction()) $db->rollBack();
                    $response['message'] = 'Lỗi: ' . $e->getMessage();
                }
            }
        }
        echo json_encode($response);
        break;

    case 'api_bulk_revoke_permissions':
        // ==========================================
        // 11. API THU HỒI QUYỀN HÀNG LOẠT
        // ==========================================
        header('Content-Type: application/json');
        $response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ.'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $target_type = $data['target_type'] ?? null;
            $target_value = $data['target_value'] ?? null;
            $permissions_to_revoke = $data['permissions'] ?? [];
            $revoke_action = $data['revoke_action'] ?? null;
            
            if ($target_type && ($target_type === 'all' || $target_value)) {
                try {
                    $student_ids = [];
                    if ($target_type === 'all') {
                        $stmt = $db->query("SELECT id FROM hoc_sinh");
                        $student_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    } elseif ($target_type === 'chuc_vu') {
                        $stmt = $db->prepare("SELECT id FROM hoc_sinh WHERE chuc_vu = ?");
                        $stmt->execute([$target_value]);
                        $student_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    } elseif ($target_type === 'lop') {
                        $stmt = $db->prepare("SELECT id FROM hoc_sinh WHERE lop_hoc_id = ?");
                        $stmt->execute([$target_value]);
                        $student_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    } elseif ($target_type === 'hoc_sinh' && is_array($target_value)) {
                        $student_ids = $target_value;
                    }

                    if (empty($student_ids)) throw new Exception('Không tìm thấy học sinh nào phù hợp với tiêu chí.');

                    $db->beginTransaction();
                    $stmt_get = $db->prepare("SELECT quyen_truy_cap FROM hoc_sinh WHERE id = ?");
                    $stmt_update = $db->prepare("UPDATE hoc_sinh SET quyen_truy_cap = ? WHERE id = ?");

                    foreach($student_ids as $id) {
                        $stmt_get->execute([$id]);
                        $current_permissions = json_decode($stmt_get->fetchColumn() ?? '{}', true);
                        
                        if ($revoke_action) {
                            if ($revoke_action === 'all') {
                                $current_permissions = [];
                            } else {
                                if (isset($current_permissions[$revoke_action])) {
                                    unset($current_permissions[$revoke_action]);
                                }
                            }
                        } else {
                            foreach($permissions_to_revoke as $key => $value) {
                                if ($value === true && isset($current_permissions[$key])) {
                                    unset($current_permissions[$key]);
                                }
                            }
                        }
                        $stmt_update->execute([json_encode($current_permissions), $id]);
                    }
                    $db->commit();
                    $response = ['success' => true, 'message' => 'Đã thu hồi quyền thành công cho ' . count($student_ids) . ' học sinh.'];
                } catch (Exception $e) {
                    if ($db->inTransaction()) $db->rollBack();
                    $response['message'] = 'Lỗi: ' . $e->getMessage();
                }
            }
        }
        echo json_encode($response);
        break;

    case 'api_revoke_all':
        // ==========================================
        // 12. API THU HỒI TOÀN BỘ QUYỀN CTV
        // ==========================================
        header('Content-Type: application/json');
        $response = ['success' => false, 'message' => 'Có lỗi xảy ra.'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $stmt = $db->exec("UPDATE hoc_sinh SET quyen_truy_cap = NULL");
                $response = ['success' => true, 'message' => 'Đã thu hồi toàn bộ quyền CTV của tất cả học sinh.'];
            } catch (Exception $e) {
                $response['message'] = 'Lỗi CSDL: ' . $e->getMessage();
            }
        }
        echo json_encode($response);
        break;

    case 'api_toggle_permission':
        // ==========================================
        // 13. API BẬT/TẮT QUYỀN CTV CHO 1 HỌC SINH
        // ==========================================
        header('Content-Type: application/json');
        $response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ.'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $student_id = $data['student_id'] ?? $data['id'] ?? null;
            $permission = $data['permission'] ?? null;
            $status = isset($data['action']) ? ($data['action'] === 'grant') : ($data['status'] ?? false);
            
            if ($student_id && $permission) {
                try {
                    $stmt_get = $db->prepare("SELECT quyen_truy_cap FROM hoc_sinh WHERE id = ?");
                    $stmt_get->execute([$student_id]);
                    $current_permissions = json_decode($stmt_get->fetchColumn() ?? '{}', true);

                    if ($status) {
                        $current_permissions[$permission] = true;
                    } else {
                        unset($current_permissions[$permission]);
                    }

                    $stmt_update = $db->prepare("UPDATE hoc_sinh SET quyen_truy_cap = ? WHERE id = ?");
                    $stmt_update->execute([json_encode($current_permissions), $student_id]);

                    $response = ['success' => true, 'message' => 'Cập nhật quyền thành công.'];
                } catch (Exception $e) {
                    $response['message'] = 'Lỗi: ' . $e->getMessage();
                }
            }
        }
        echo json_encode($response);
        break;

    case 'export_codes':
        // ==========================================
        // 14. XUẤT EXCEL MÃ CTV
        // ==========================================
        require_once __DIR__ . '/../../vendor/autoload.php';
        $stmt = $db->query("
            SELECT m.*, COUNT(l.id) as so_luong_da_dung
            FROM ma_kich_hoat_ctv m
            LEFT JOIN lich_su_su_dung_ma_ctv l ON m.id = l.ma_ctv_id
            WHERE m.trang_thai = 'active' OR m.trang_thai = 'pending'
            GROUP BY m.id
            ORDER BY m.ngay_tao DESC
        ");
        $codes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Danh sách Mã CTV');

        $sheet->setCellValue('A1', 'Mã Kích Hoạt');
        $sheet->setCellValue('B1', 'Tên Đợt');
        $sheet->setCellValue('C1', 'Đối Tượng');
        $sheet->setCellValue('D1', 'Đã Dùng');
        $sheet->setCellValue('E1', 'Tối Đa');
        $sheet->setCellValue('F1', 'Thời Gian Bắt Đầu');
        $sheet->setCellValue('G1', 'Thời Gian Kết Thúc');
        $sheet->setCellValue('H1', 'Trạng Thái');

        $row = 2;
        foreach ($codes as $code) {
            $sheet->setCellValue('A' . $row, $code['ma_kich_hoat']);
            $sheet->setCellValue('B' . $row, $code['ten_dot_kich_hoat']);
            $sheet->setCellValue('C' . $row, $code['doi_tuong_ap_dung']);
            $sheet->setCellValue('D' . $row, $code['so_luong_da_dung']);
            $sheet->setCellValue('E' . $row, $code['so_luong_toi_da']);
            $sheet->setCellValue('F' . $row, $code['thoi_gian_bat_dau']);
            $sheet->setCellValue('G' . $row, $code['thoi_gian_het_han']);
            $sheet->setCellValue('H' . $row, $code['trang_thai']);
            $row++;
        }

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'danh_sach_ma_ctv_' . date('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        ob_clean();
        flush();
        $writer->save('php://output');
        exit();

    case 'export_accounts':
        // ==========================================
        // 15. XUẤT DS TÀI KHOẢN CTV
        // ==========================================
        require_once __DIR__ . '/../../vendor/autoload.php';
        require_once __DIR__ . '/../lib/hoc_sinh_db.php'; 

        $export_scope = $_GET['export_scope'] ?? 'all';
        $selected_columns_param = $_GET['columns'] ?? '';
        $selected_columns = [];
        if (!empty($selected_columns_param)) {
            if (is_array($selected_columns_param)) {
                $selected_columns = $selected_columns_param;
            } else {
                $selected_columns = explode(',', $selected_columns_param);
            }
        } else {
            $selected_columns = ['khoi', 'lop', 'ma_hs', 'ho_ten', 'ngay_sinh', 'gioi_tinh', 'chuc_vu', 'sdt', 'gmail', 'trang_thai_tk', 'quyen_vp', 'quyen_dd', 'quyen_truc', 'ghi_chu'];
        }

        $filters = [];
        if ($export_scope === 'has_account') {
            $filters['has_account'] = true;
            $title = 'DANH SÁCH HỌC SINH ĐÃ ĐƯỢC CẤP TÀI KHOẢN';
        } elseif ($export_scope === 'has_permission') {
            $filters['has_permission'] = true;
            $title = 'DANH SÁCH HỌC SINH CÓ QUYỀN CTV';
        } else {
            $title = 'DANH SÁCH TÀI KHOẢN HỌC SINH';
        }

        $danh_sach = get_all_hoc_sinh($db, $filters);

        if (empty($danh_sach)) {
            echo "<script>alert('Không có dữ liệu phù hợp với bộ lọc.'); history.back();</script>";
            exit;
        }

        $column_map = [
            'khoi' => ['header' => 'Khối', 'width' => 8, 'align' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'lop' => ['header' => 'Lớp', 'width' => 8, 'align' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'nien_khoa' => ['header' => 'Niên khóa', 'width' => 12, 'align' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'ma_hs' => ['header' => 'Số CCCD', 'width' => 15, 'align' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'ho_ten' => ['header' => 'Họ và tên', 'width' => 25, 'align' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT],
            'ngay_sinh' => ['header' => 'Ngày sinh', 'width' => 12, 'align' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'gioi_tinh' => ['header' => 'Giới Tính', 'width' => 10, 'align' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'chuc_vu' => ['header' => 'Chức vụ', 'width' => 12, 'align' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'sdt' => ['header' => 'SĐT', 'width' => 12, 'align' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'gmail' => ['header' => 'Gmail', 'width' => 30, 'align' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT],
            'dia_chi' => ['header' => 'Địa chỉ', 'width' => 40, 'align' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT],
            'trang_thai_tk' => ['header' => 'Trạng thái TK', 'width' => 15, 'align' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'quyen_vp' => ['header' => 'Q.Nhập VP', 'width' => 12, 'align' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'quyen_dd' => ['header' => 'Q.Điểm danh', 'width' => 12, 'align' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'quyen_truc' => ['header' => 'Q.Trực', 'width' => 10, 'align' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'ghi_chu' => ['header' => 'Ghi chú', 'width' => 15, 'align' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT],
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

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Tài Khoản CTV');

        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman')->setSize(12);
        $sheet->getColumnDimension('A')->setWidth(5); 
        foreach ($active_columns as $key => $details) {
            $col_letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(array_search($key, array_keys($active_columns)) + 2);
            $sheet->getColumnDimension($col_letter)->setWidth($details['width']);
        }

        $sheet->mergeCells('A1:D1')->setCellValue('A1', 'TRƯỜNG THPT BÌNH SƠN');
        $sheet->mergeCells('A2:D2')->setCellValue('A2', 'HỆ THỐNG ĐÁNH GIÁ THI ĐUA');
        
        $sheet->mergeCells("A3:{$last_column_letter}3")->setCellValue('A3', $title);
        
        $sheet->getStyle("A1:{$last_column_letter}3")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFont()->setSize(11);
        $sheet->getStyle('A2')->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('A3')->getFont()->setSize(13)->setBold(true);

        $header_row = 5;
        $data_row_start = $header_row + 1;
        $sheet->fromArray($headers, NULL, 'A' . $header_row);
        $sheet->getStyle('A'.$header_row.':'.$last_column_letter.$header_row)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => '0070C0']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ]);

        $rowIndex = $data_row_start;
        foreach ($danh_sach as $index => $hs) {
            $rowData = [$index + 1];
            $quyen = json_decode($hs['quyen_truy_cap'] ?? '{}', true);
            
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
                    case 'trang_thai_tk': $rowData[] = $hs['trang_thai_tai_khoan'] ?? ''; break;
                    case 'quyen_vp': $rowData[] = !empty($quyen['nhap_vi_pham']) ? 'x' : ''; break;
                    case 'quyen_dd': $rowData[] = !empty($quyen['so_nhat_ky_online']) ? 'x' : ''; break;
                    case 'quyen_truc': $rowData[] = !empty($quyen['dang_ky_truc']) ? 'x' : ''; break;
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
        $sheet->mergeCells("A{$footer_start_row}:{$last_column_letter}{$footer_start_row}")->setCellValue('A'.$footer_start_row, 'Danh sách trên có ' . count($danh_sach) . ' học sinh./.');
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

        $admin_name = $_SESSION['user_ten'] ?? 'Quản trị viên';
        $signature_row += 4; 
        $sheet->mergeCells("{$signature_col_start_letter}{$signature_row}:{$last_column_letter}{$signature_row}")->setCellValue($signature_col_start_letter.$signature_row, $admin_name);
        $sheet->getStyle("{$signature_col_start_letter}{$signature_row}:{$last_column_letter}{$signature_row}")->getFont()->setBold(true);
        $sheet->getStyle("{$signature_col_start_letter}{$signature_row}:{$last_column_letter}{$signature_row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $search = ['á','à','ả','ã','ạ','â','ấ','ầ','ẩ','ẫ','ậ','ă','ắ','ằ','ẳ','ẵ','ặ','đ','é','è','ẻ','ẽ','ẹ','ê','ế','ề','ể','ễ','ệ','í','ì','ỉ','ĩ','ị','ó','ò','ỏ','õ','ọ','ô','ố','ồ','ổ','ỗ','ộ','ơ','ớ','ờ','ở','ỡ','ợ','ú','ù','ủ','ũ','ụ','ư','ứ','ừ','ử','ữ','ự','ý','ỳ','ỷ','ỹ','ỵ','Á','À','Ả','Ã','Ạ','Â','Ấ','Ầ','Ẩ','Ẫ','Ậ','Ă','Ắ','Ằ','Ẳ','Ẵ','Ặ','Đ','É','È','Ẻ','Ẽ','Ẹ','Ê','Ế','Ề','Ể','Ễ','Ệ','Í','Ì','Ỉ','Ĩ','Ị','Ó','Ò','Ỏ','Õ','Ọ','Ô','Ố','Ồ','Ổ','Ỗ','Ộ','Ơ','Ớ','Ờ','Ở','Ỡ','Ợ','Ú','Ù','Ủ','Ũ','Ụ','Ư','Ứ','Ừ','Ử','Ữ','Ự','Ý','Ỳ','Ỷ','Ỹ','Ỵ'];
        $replace = ['a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','d','e','e','e','e','e','e','e','e','e','e','e','i','i','i','i','i','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','u','u','u','u','u','u','u','u','u','u','u','y','y','y','y','y','A','A','A','A','A','A','A','A','A','A','A','A','A','A','A','A','A','D','E','E','E','E','E','E','E','E','E','E','E','I','I','I','I','I','O','O','O','O','O','O','O','O','O','O','O','O','O','O','O','O','O','U','U','U','U','U','U','U','U','U','U','U','Y','Y','Y','Y','Y'];
        $title_khong_dau = str_replace($search, $replace, $title);
        $filename = "DS_CTV_" . trim(preg_replace('/[^a-zA-Z0-9]+/', '_', $title_khong_dau), '_') . "_" . date('Ymd') . ".xlsx";
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        ob_clean();
        flush();
        $writer->save('php://output');
        exit();


    case 'api_get_unprovisioned_students':
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập.']);
            exit();
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $scope = $data['scope'] ?? 'all';
        $lop_id = $data['lop_id'] ?? null;

        try {
            $sql_select = "SELECT id, ho_dem, ten FROM hoc_sinh 
                           WHERE (trang_thai_tai_khoan = 'Chưa có TK' OR trang_thai_tai_khoan = 'Chưa cấp TK' OR trang_thai_tai_khoan IS NULL OR TRIM(trang_thai_tai_khoan) = '') 
                           AND ngay_sinh IS NOT NULL AND TRIM(ngay_sinh) != ''";
            $params = [];
            
            if ($scope === 'class' && !empty($lop_id)) {
                $sql_select .= " AND lop_hoc_id = ?";
                $params[] = $lop_id;
            } elseif ($scope !== 'all') {
                throw new Exception('Phạm vi không hợp lệ.');
            }

            $stmt = $db->prepare($sql_select);
            $stmt->execute($params);
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $students]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
        break;

    case 'api_provision_batch':
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập.']);
            exit();
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $students = $data['students'] ?? [];
        
        $updated = 0;
        $skipped = 0;

        try {
            $stmt_hs = $db->prepare("SELECT ngay_sinh FROM hoc_sinh WHERE id = ?");
            $update_stmt = $db->prepare("UPDATE hoc_sinh SET mat_khau_hash = ?, trang_thai_tai_khoan = 'Đã cấp TK' WHERE id = ?");

            foreach ($students as $student) {
                $stmt_hs->execute([$student['id']]);
                $hs_data = $stmt_hs->fetch();

                if ($hs_data && !empty($hs_data['ngay_sinh'])) {
                    $password = str_replace('/', '', $hs_data['ngay_sinh']);
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    if ($update_stmt->execute([$password_hash, $student['id']])) {
                        $updated++;
                    } else {
                        $skipped++;
                    }
                } else {
                    $skipped++;
                }
            }
            echo json_encode(['success' => true, 'updated' => $updated, 'skipped' => $skipped]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
        break;

    default:
        header('Location: /thidua/quan-ly-ctv');
        exit();
}
