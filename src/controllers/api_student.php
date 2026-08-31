<?php
// File: src/controllers/api_student.php (File mới)
// Đây là "bộ não" backend cho ứng dụng di động của học sinh.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

// === BỘ ĐIỀU KHIỂN ROUTER CHO API ===
$action = $_GET['action'] ?? null;
switch ($action) {
    case 'login':
        handle_login();
        break;
    case 'profile':
        get_current_student_data('profile');
        break;
    case 'violations':
        get_current_student_data('violations');
        break;
    case 'commendations':
        get_current_student_data('commendations');
        break;
    case 'update_contact':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ.']);
            break;
        }
        update_student_contact();
        break;
    case 'change_password':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ.']);
            break;
        }
        change_student_password();
        break;
    case 'logout':
        handle_logout();
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
// === KẾT THÚC BỘ ĐIỀU KHIỂN ===


/**
 * Xử lý đăng nhập cho học sinh
 */
function handle_login()
{
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';

    if (empty($username) || empty($password)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đủ thông tin.']);
        return;
    }

    $db = get_db_connection();
    $stmt = $db->prepare("SELECT * FROM hoc_sinh WHERE ma_hoc_sinh = ?");
    $stmt->execute([$username]);
    $student = $stmt->fetch();

    if ($student && isset($student['mat_khau_hash']) && password_verify($password, $student['mat_khau_hash'])) {
        // Đăng nhập thành công, tạo session
        session_regenerate_id(true);
        $_SESSION['student_api_id'] = $student['id']; // Dùng session key riêng cho API
        echo json_encode(['success' => true, 'message' => 'Đăng nhập thành công!']);
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Tên tài khoản hoặc mật khẩu không đúng.']);
    }
}

/**
 * Lấy dữ liệu của học sinh đang đăng nhập
 * @param string $type Loại dữ liệu cần lấy: 'profile', 'violations', 'commendations'
 */
function get_current_student_data($type)
{
    if (!isset($_SESSION['student_api_id'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Phiên đăng nhập không hợp lệ.']);
        return;
    }

    $db = get_db_connection();
    $student_id = $_SESSION['student_api_id'];
    $response_data = [];

    try {
        if ($type === 'profile') {
            $response_data = fetch_student_profile($db, $student_id);
        } elseif ($type === 'violations') {
            $stmt = $db->prepare("SELECT vp.ngay_vi_pham, chvp.ten_vi_pham, chvp.diem_tru, vp.ghi_chu, t.ten_tuan FROM vi_pham_hoc_sinh vp JOIN cau_hinh_vi_pham chvp ON vp.vi_pham_id = chvp.id JOIN quatrinh_hoc_tap qt ON vp.hoc_sinh_id = qt.id JOIN hoc_sinh hs ON hs.ma_hoc_sinh = qt.ma_hoc_sinh AND hs.nam_hoc_id = qt.nam_hoc_id JOIN tuan_hoc t ON vp.tuan_hoc_id = t.id WHERE hs.id = ? ORDER BY vp.ngay_vi_pham DESC");
            $stmt->execute([$student_id]);
            $response_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($type === 'commendations') {
            $stmt_student_info = $db->prepare("SELECT hs.id, hs.lop_hoc_id, hs.ma_hoc_sinh FROM hoc_sinh hs WHERE hs.id = ?");
            $stmt_student_info->execute([$student_id]);
            $st_info = $stmt_student_info->fetch(PDO::FETCH_ASSOC);
            $lop_hoc_id = $st_info['lop_hoc_id'] ?? null;
            $ma_hs = $st_info['ma_hoc_sinh'] ?? '';

            $stmt = $db->prepare("
                SELECT *, 'Cá nhân' as doi_tuong FROM khen_thuong 
                WHERE (hoc_sinh_id = :hoc_sinh_id OR hoc_sinh_id IN (SELECT id FROM quatrinh_hoc_tap WHERE ma_hoc_sinh = :ma_hs)) AND loai = 'ca_nhan' 
                UNION ALL 
                SELECT *, 'Tập thể lớp' as doi_tuong FROM khen_thuong 
                WHERE lop_hoc_id = :lop_hoc_id AND loai = 'tap_the' 
                ORDER BY ngay_khen_thuong DESC
            ");
            $stmt->execute([':hoc_sinh_id' => $student_id, ':ma_hs' => $ma_hs, ':lop_hoc_id' => $lop_hoc_id]);
            $response_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        echo json_encode(['success' => true, 'data' => $response_data]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi server: ' . $e->getMessage()]);
    }
}

/**
 * Xử lý đăng xuất
 */
function handle_logout()
{
    unset($_SESSION['student_api_id']);
    echo json_encode(['success' => true, 'message' => 'Đã đăng xuất.']);
}

/**
 * Lấy thông tin hồ sơ học sinh
 */
function fetch_student_profile(PDO $db, $student_id)
{
    $stmt = $db->prepare("SELECT 
            hs.id,
            hs.ho_dem,
            hs.ten,
            hs.ma_hoc_sinh,
            hs.ngay_sinh,
            hs.gioi_tinh,
            hs.chuc_vu,
            hs.sdt,
            hs.email,
            hs.anh_the,
            hs.trang_thai_tai_khoan,
            hs.trang_thai_hoc_tap,
            lh.ten_lop,
            lh.gvcn_ten
        FROM hoc_sinh hs
        JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id
        WHERE hs.id = ?");
    $stmt->execute([$student_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Cập nhật thông tin liên hệ của học sinh hiện tại
 */
function update_student_contact()
{
    if (!isset($_SESSION['student_api_id'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Phiên đăng nhập không hợp lệ.']);
        return;
    }

    $payload = json_decode(file_get_contents('php://input'), true) ?? [];
    $student_id = $_SESSION['student_api_id'];

    $chuc_vu = isset($payload['chuc_vu']) ? trim($payload['chuc_vu']) : '';
    $sdt = isset($payload['sdt']) ? trim($payload['sdt']) : '';
    $email = isset($payload['email']) ? trim($payload['email']) : '';

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Địa chỉ email không hợp lệ.']);
        return;
    }

    if (mb_strlen($chuc_vu) > 100) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Chức vụ tối đa 100 ký tự.']);
        return;
    }

    if (mb_strlen($sdt) > 30) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Số điện thoại tối đa 30 ký tự.']);
        return;
    }

    if (mb_strlen($email) > 191) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email tối đa 191 ký tự.']);
        return;
    }

    $db = get_db_connection();

    $stmt_get = $db->prepare("SELECT ma_hoc_sinh, nam_hoc_id FROM hoc_sinh WHERE id = :id");
    $stmt_get->execute([':id' => $student_id]);
    $hs_info = $stmt_get->fetch(PDO::FETCH_ASSOC);

    if ($hs_info) {
        $db->beginTransaction();
        try {
            $stmt_hs = $db->prepare("UPDATE ho_so_hoc_sinh SET sdt = :sdt, email = :email WHERE ma_hoc_sinh = :ma");
            $stmt_hs->execute([
                ':sdt' => $sdt !== '' ? $sdt : null,
                ':email' => $email !== '' ? $email : null,
                ':ma' => $hs_info['ma_hoc_sinh']
            ]);

            $stmt_qt = $db->prepare("UPDATE quatrinh_hoc_tap SET chuc_vu = :chuc_vu WHERE ma_hoc_sinh = :ma AND nam_hoc_id = :nam");
            $stmt_qt->execute([
                ':chuc_vu' => $chuc_vu !== '' ? $chuc_vu : null,
                ':ma' => $hs_info['ma_hoc_sinh'],
                ':nam' => $hs_info['nam_hoc_id']
            ]);

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi cập nhật CSDL.']);
            return;
        }
    }

    $updated_profile = fetch_student_profile($db, $student_id);

    echo json_encode([
        'success' => true,
        'message' => 'Đã cập nhật thông tin thành công.',
        'data' => $updated_profile
    ]);
}

/**
 * Đổi mật khẩu tài khoản học sinh hiện tại
 */
function change_student_password()
{
    if (!isset($_SESSION['student_api_id'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Phiên đăng nhập không hợp lệ.']);
        return;
    }

    $payload = json_decode(file_get_contents('php://input'), true) ?? [];

    $current_password = $payload['current_password'] ?? '';
    $new_password = $payload['new_password'] ?? '';
    $confirm_password = $payload['confirm_password'] ?? '';

    if ($current_password === '' || $new_password === '' || $confirm_password === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin.']);
        return;
    }

    if ($new_password !== $confirm_password) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Mật khẩu mới và xác nhận không trùng khớp.']);
        return;
    }

    if (strlen($new_password) < 8) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Mật khẩu mới phải có ít nhất 8 ký tự.']);
        return;
    }

    $db = get_db_connection();
    $student_id = $_SESSION['student_api_id'];

    $stmt = $db->prepare("SELECT mat_khau_hash FROM hoc_sinh WHERE id = ?");
    $stmt->execute([$student_id]);
    $stored_hash = $stmt->fetchColumn();

    if (!$stored_hash || !password_verify($current_password, $stored_hash)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Mật khẩu hiện tại không chính xác.']);
        return;
    }

    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
    $update_stmt = $db->prepare("UPDATE hoc_sinh SET mat_khau_hash = ? WHERE id = ?");
    $update_stmt->execute([$new_hash, $student_id]);

    echo json_encode(['success' => true, 'message' => 'Đã đổi mật khẩu thành công.']);
}

?>
