<?php
// File: src/controllers/TaiKhoanController.php
if (function_exists('opcache_invalidate')) { opcache_invalidate(__FILE__, true); }
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin'])) {
    header('Location: /thidua/tracuu');
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$action = $_GET['action'] ?? 'index';
$db = get_db_connection();

// Kiểm tra quyền: Chỉ duy nhất tài khoản có username là 'admin' mới được vào trang và quản lý tài khoản hệ thống
$current_username = $_SESSION['user_ten_dang_nhap'] ?? '';
if (empty($current_username) && isset($_SESSION['user_id'])) {
    $stmt_u = $db->prepare("SELECT ten_dang_nhap FROM users WHERE id = ?");
    $stmt_u->execute([$_SESSION['user_id']]);
    $current_username = $stmt_u->fetchColumn();
    $_SESSION['user_ten_dang_nhap'] = $current_username;
}

if ($action !== 'api_reset_password' && strtolower((string)$current_username) !== 'admin') {
    if (isset($_GET['action']) && strpos($_GET['action'], 'api_') === 0) {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này!']);
        exit();
    }
    echo "<script>alert('Truy cập bị từ chối! Chức năng Quản lý tài khoản hệ thống chỉ dành riêng cho tài khoản Admin tối cao (admin).'); window.location.href = '/thidua/admin';</script>";
    exit();
}

switch ($action) {
    case 'index':
        // ==========================================
        // 1. HIỂN THỊ DANH SÁCH TÀI KHOẢN ADMIN/CTV
        // ==========================================
        require_once __DIR__ . '/../lib/user_db.php';
        $danh_sach_user = get_all_users($db);
        require_once __DIR__ . '/../views/quan_ly_tai_khoan.php';
        break;

    case 'generate_app_key':
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_POST['user_id'] ?? null;
            if (!$user_id) {
                echo json_encode(['success' => false, 'message' => 'Thiếu user_id']);
                exit();
            }
            try {
                $new_key = 'APP-KEY-' . strtoupper(bin2hex(random_bytes(8)));
                $stmt = $db->prepare("UPDATE users SET app_key = ?, app_key_ip = NULL, app_key_machine = NULL, app_key_activated_at = NULL WHERE id = ?");
                $stmt->execute([$new_key, $user_id]);
                echo json_encode(['success' => true, 'app_key' => $new_key]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
            }
        }
        break;

    case 'api_get':
        header('Content-Type: application/json');
        $user_id = $_GET['id'] ?? null;
        if (!$user_id) {
            http_response_code(400);
            echo json_encode(['message' => 'Thiếu ID người dùng.']);
            exit();
        }
        $stmt = $db->prepare("SELECT id, ten_dang_nhap, ho_ten, email, sdt, vai_tro, ghi_chu, avatar, quyen_han FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            echo json_encode($user);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Người dùng không tồn tại.']);
        }
        exit();

    case 'add':
        // ==========================================
        // 2. THÊM TÀI KHOẢN (Admin/CTV)
        // ==========================================
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ten_dang_nhap = $_POST['ten_dang_nhap'];
            $ho_ten = $_POST['ho_ten'];
            $email = $_POST['email'] ?? null;
            $sdt = $_POST['sdt'] ?? null;
            $mat_khau = $_POST['mat_khau'];
            $vai_tro = $_POST['vai_tro'];
            $ghi_chu = $_POST['ghi_chu'] ?? '';
            
            $quyen_han_json = null;
            if ($vai_tro === 'user' && !empty($_POST['permissions']) && is_array($_POST['permissions'])) {
                $quyen_han_json = json_encode($_POST['permissions']);
            }
        
            $mat_khau_hash = password_hash($mat_khau, PASSWORD_DEFAULT);
        
            try {
                // Đảm bảo cột id có AUTO_INCREMENT nếu bị thiếu trong quá trình chuyển đổi CSDL
                try {
                    $db->exec("ALTER TABLE users MODIFY id INT AUTO_INCREMENT");
                } catch (Exception $e) {
                    // Bỏ qua nếu đã có hoặc không thể alter
                }

                $sql = "INSERT INTO users (ten_dang_nhap, ho_ten, email, sdt, mat_khau_hash, vai_tro, ghi_chu, quyen_han) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $db->prepare($sql);
                $stmt->execute([$ten_dang_nhap, $ho_ten, $email, $sdt, $mat_khau_hash, $vai_tro, $ghi_chu, $quyen_han_json]);
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Thêm tài khoản thành công!'];
            } catch (PDOException $e) {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Thêm tài khoản thất bại: ' . $e->getMessage()];
            }
        
            $iframe = isset($_GET['iframe']) ? '?iframe=1' : '';
            header('Location: /thidua/admin/tai-khoan' . $iframe);
            exit();
        }
        break;

    case 'edit':
        // ==========================================
        // 3. SỬA TÀI KHOẢN (Admin/CTV)
        // ==========================================
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_POST['user_id'];
            $ten_dang_nhap = $_POST['ten_dang_nhap'];
            $ho_ten = $_POST['ho_ten'];
            $email = $_POST['email'] ?? null;
            $sdt = $_POST['sdt'] ?? null;
            $vai_tro = $_POST['vai_tro'];
            $ghi_chu = $_POST['ghi_chu'] ?? '';
            
            $quyen_han_json = null;
            if ($vai_tro === 'user' && !empty($_POST['permissions']) && is_array($_POST['permissions'])) {
                $quyen_han_json = json_encode($_POST['permissions']);
            }
            if ($vai_tro === 'admin') {
                $quyen_han_json = null;
            }
            
            try {
                $sql = "UPDATE users SET ten_dang_nhap = ?, ho_ten = ?, email = ?, sdt = ?, vai_tro = ?, ghi_chu = ?, quyen_han = ? WHERE id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$ten_dang_nhap, $ho_ten, $email, $sdt, $vai_tro, $ghi_chu, $quyen_han_json, $user_id]);
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Cập nhật tài khoản thành công!'];
            } catch (PDOException $e) {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Cập nhật thất bại: ' . $e->getMessage()];
            }
            
            $iframe = isset($_GET['iframe']) ? '?iframe=1' : '';
            header('Location: /thidua/admin/tai-khoan' . $iframe);
            exit();
        }
        break;

    case 'change_password':
        // ==========================================
        // SỬA MẬT KHẨU (Admin/CTV)
        // ==========================================
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_POST['user_id'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            if (empty($user_id) || empty($new_password) || empty($confirm_password)) {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Vui lòng điền đầy đủ thông tin.'];
            } elseif ($new_password !== $confirm_password) {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Mật khẩu mới và mật khẩu xác nhận không khớp.'];
            } else {
                $mat_khau_hash = password_hash($new_password, PASSWORD_DEFAULT);
                try {
                    $sql = "UPDATE users SET mat_khau_hash = ? WHERE id = ?";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$mat_khau_hash, $user_id]);
                    $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Đổi mật khẩu thành công!'];
                } catch (PDOException $e) {
                    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Đổi mật khẩu thất bại: ' . $e->getMessage()];
                }
            }

            $iframe = isset($_GET['iframe']) ? '?iframe=1' : '';
            header('Location: /thidua/admin/tai-khoan' . $iframe);
            exit();
        }
        break;

    case 'api_delete':
        // ==========================================
        // 4. API XÓA TÀI KHOẢN (Admin/CTV)
        // ==========================================
        header('Content-Type: application/json');
        $response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ.'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $id_to_delete = $data['id'] ?? null;
            $username_to_delete = $data['username'] ?? null;
            $current_user_id = $_SESSION['user_id'] ?? null;
        
            if (empty($id_to_delete) && empty($username_to_delete)) {
                $response['message'] = 'Thiếu ID hoặc Tên đăng nhập của tài khoản cần xóa.';
            } elseif (!empty($id_to_delete) && $id_to_delete == $current_user_id) {
                $response['message'] = 'Xóa thất bại: Bạn không thể tự xóa tài khoản của chính mình.';
            } else {
                try {
                    if (!empty($username_to_delete)) {
                        $sql = "DELETE FROM users WHERE ten_dang_nhap = ?";
                        $stmt = $db->prepare($sql);
                        $stmt->execute([$username_to_delete]);
                    } else {
                        $sql = "DELETE FROM users WHERE id = ?";
                        $stmt = $db->prepare($sql);
                        $stmt->execute([$id_to_delete]);
                    }
        
                    if ($stmt->rowCount() > 0) {
                        $response = ['success' => true, 'message' => 'Đã xóa tài khoản thành công!'];
                    } else {
                        $response['message'] = 'Xóa thất bại. Tài khoản không tồn tại.';
                    }
                } catch (PDOException $e) {
                    $response['message'] = 'Lỗi CSDL: ' . $e->getMessage();
                }
            }
        }
        echo json_encode($response);
        break;

    case 'api_provision_student':
        // ==========================================
        // 5. API CẤP TÀI KHOẢN CHO 1 HỌC SINH
        // ==========================================
        header('Content-Type: application/json');
        $response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ.'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;
        
            if (empty($id)) {
                $response['message'] = 'Thiếu ID của học sinh.';
                echo json_encode($response);
                exit();
            }
        
            $stmt = $db->prepare("SELECT ngay_sinh FROM hoc_sinh WHERE id = ?");
            $stmt->execute([$id]);
            $student = $stmt->fetch();
        
            if (!$student || empty($student['ngay_sinh'])) {
                $response['message'] = 'Không tìm thấy học sinh hoặc học sinh chưa có ngày sinh. Không thể tạo mật khẩu.';
            } else {
                $password = str_replace('/', '', $student['ngay_sinh']);
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                $sql = "UPDATE hoc_sinh SET mat_khau_hash = ?, trang_thai_tai_khoan = 'Đã cấp TK' WHERE id = ?";
                $update_stmt = $db->prepare($sql);
                if ($update_stmt->execute([$password_hash, $id])) {
                    $response = ['success' => true, 'message' => 'Cấp tài khoản thành công! Mật khẩu mặc định là ngày sinh của học sinh (ví dụ: 18012006).'];
                } else {
                    $response['message'] = 'Lỗi khi cập nhật CSDL.';
                }
            }
        }
        echo json_encode($response);
        break;

    case 'api_reset_password':
        // ==========================================
        // 6. API RESET MẬT KHẨU HỌC SINH
        // ==========================================
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $hoc_sinh_id = $data['student_id'] ?? null; 
        
        if (!$hoc_sinh_id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu ID học sinh.']);
            exit();
        }
        
        try {
            $stmt_hs = $db->prepare("SELECT id FROM hoc_sinh WHERE id = ?");
            $stmt_hs->execute([$hoc_sinh_id]);
            $hoc_sinh = $stmt_hs->fetch(PDO::FETCH_ASSOC);
        
            if (!$hoc_sinh) {
                throw new Exception('Không tìm thấy hồ sơ học sinh với ID được cung cấp.');
            }
        
            $new_password = "Abc@1234";
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
            $stmt_update = $db->prepare("UPDATE hoc_sinh SET mat_khau_hash = ?, trang_thai_tai_khoan = 'Đã đổi MK' WHERE id = ?");
            $stmt_update->execute([$hashed_password, $hoc_sinh_id]);
        
            if ($stmt_update->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => "Đặt lại mật khẩu thành công!\n\nMật khẩu mới là: {$new_password}"]);
            } else {
                throw new Exception('Cập nhật mật khẩu thất bại. Không có hồ sơ nào được thay đổi.');
            }
        
        } catch (Exception $e) {
            http_response_code(500);
            error_log("Reset Password Error: " . $e->getMessage()); 
            echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
        }
        break;

    default:
        header('Location: /thidua/admin/tai-khoan');
        exit();
}
