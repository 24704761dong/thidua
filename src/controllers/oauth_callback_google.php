<?php
// File: src/controllers/oauth_callback_google.php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../config/oauth_providers.php';
require_once __DIR__ . '/../../config/database.php';
if (!defined('SKIP_LOGIN_PROCESS')) {
    define('SKIP_LOGIN_PROCESS', true);
}
require_once __DIR__ . '/dang_nhap_xu_ly.php'; // Để gọi hàm handleLoginSuccess()

$provider = get_google_provider();
$db = get_db_connection();

/**
 * Kiểm tra xem google_id / email đã được dùng bởi tài khoản khác chưa.
 * Trả về chuỗi thông báo lỗi nếu phát hiện xung đột, ngược lại trả về null.
 */
function checkGoogleAvailability(\PDO $db, string $googleId, string $googleEmail, ?int $excludeStudentId = null, ?int $excludeUserId = null): ?string
{
    // Kiểm tra google_id đã gắn cho học sinh khác hay chưa
    $sqlStudent = "SELECT id FROM hoc_sinh WHERE google_id = ?";
    $paramsStudent = [$googleId];
    if ($excludeStudentId !== null) {
        $sqlStudent .= " AND id != ?";
        $paramsStudent[] = $excludeStudentId;
    }
    $stmt = $db->prepare($sqlStudent);
    $stmt->execute($paramsStudent);
    if ($stmt->fetchColumn()) {
        return 'Tài khoản Google này đã được liên kết với một học sinh khác.';
    }

    // Kiểm tra google_id với bảng users
    $sqlUser = "SELECT id FROM users WHERE google_id = ?";
    $paramsUser = [$googleId];
    if ($excludeUserId !== null) {
        $sqlUser .= " AND id != ?";
        $paramsUser[] = $excludeUserId;
    }
    $stmt = $db->prepare($sqlUser);
    $stmt->execute($paramsUser);
    if ($stmt->fetchColumn()) {
        return 'Tài khoản Google này đã được liên kết với một tài khoản quản trị khác.';
    }

    $normalizedEmail = strtolower(trim($googleEmail));
    if ($normalizedEmail === '') {
        return null;
    }

    // Kiểm tra email trên bảng học sinh
    $sqlStudentEmail = "SELECT id FROM hoc_sinh WHERE (LOWER(email) = LOWER(?) OR LOWER(verified_email) = LOWER(?))";
    $paramsStudentEmail = [$normalizedEmail, $normalizedEmail];
    if ($excludeStudentId !== null) {
        $sqlStudentEmail .= " AND id != ?";
        $paramsStudentEmail[] = $excludeStudentId;
    }
    $stmt = $db->prepare($sqlStudentEmail);
    $stmt->execute($paramsStudentEmail);
    if ($stmt->fetchColumn()) {
        return 'Email Google này đã được lưu cho một học sinh khác.';
    }

    // Kiểm tra email trên bảng users
    $sqlUserEmail = "SELECT id FROM users WHERE (LOWER(email) = LOWER(?) OR LOWER(verified_email) = LOWER(?))";
    $paramsUserEmail = [$normalizedEmail, $normalizedEmail];
    if ($excludeUserId !== null) {
        $sqlUserEmail .= " AND id != ?";
        $paramsUserEmail[] = $excludeUserId;
    }
    $stmt = $db->prepare($sqlUserEmail);
    $stmt->execute($paramsUserEmail);
    if ($stmt->fetchColumn()) {
        return 'Email Google này đã được lưu cho một tài khoản khác.';
    }

    return null;
}

function handleOAuthError($message, $redirectUrl = '/thidua/tracuu?show_login=1') {
    if (isset($_SESSION['user_id']) || isset($_SESSION['student_id'])) {
        header('Content-Type: text/html; charset=utf-8');
        echo "<script>
            let targetWin = window.opener || window.parent;
            if (targetWin && typeof targetWin.showSystemAlert === 'function') {
                targetWin.showSystemAlert(" . json_encode($message) . ", 'Lỗi liên kết');
                window.close();
            } else {
                alert(" . json_encode($message) . ");
                window.close();
            }
        </script>";
        exit();
    } else {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => $message];
        header('Location: ' . $redirectUrl);
        exit();
    }
}

if (!empty($_GET['error'])) {
    // Người dùng bấm "Hủy" trên trang của Google
    handleOAuthError('Đã hủy thao tác liên kết tài khoản Google.');
} elseif (empty($_GET['code'])) {
    // Thiếu mã code
    handleOAuthError('Thiếu mã xác thực từ Google.');
} elseif (isset($_GET['code'])) {
    
    try {
        // 1. Dùng mã 'code' để lấy 'access_token'
        $token = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);

        // 2. Dùng 'access_token' để lấy thông tin người dùng
        $ownerDetails = $provider->getResourceOwner($token);
        
    $google_id = $ownerDetails->getId();
    $google_email = strtolower(trim($ownerDetails->getEmail()));
        $google_name = $ownerDetails->getName();

        if (empty($google_id) || empty($google_email)) {
             throw new Exception("Không thể lấy ID hoặc Email từ Google.");
        }

        // 3. LOGIC XỬ LÝ: Có 2 trường hợp
        
        // TRƯỜNG HỢP 1: ĐANG LIÊN KẾT (Người dùng đã đăng nhập)
        if (isset($_SESSION['student_id'])) {
            // TẠM THỜI VÔ HIỆU HÓA LIÊN KẾT GOOGLE CHO HỌC SINH
            header('Content-Type: text/html; charset=utf-8');
            echo "<script>
                alert('Chức năng liên kết Google cho học sinh đang được bảo trì.');
                window.close();
            </script>";
            exit();
        }
        
        // (Tùy chọn: Thêm logic cho Admin/User tự liên kết)
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];

            if ($conflictMessage = checkGoogleAvailability($db, $google_id, $google_email, null, (int)$user_id)) {
                handleOAuthError($conflictMessage);
            }

            $stmt = $db->prepare("UPDATE users SET google_id = ?, verified_email = ?, email = ? WHERE id = ?");
            $stmt->execute([$google_id, $google_email, $google_email, $user_id]);

            header('Content-Type: text/html; charset=utf-8');
            echo "<script>
                let targetWin = window.opener || window.parent;
                if (targetWin && typeof targetWin.showSystemAlert === 'function') {
                    targetWin.showSystemAlert('Liên kết tài khoản Google thành công!', 'Thành công', () => {
                        targetWin.location.reload();
                    });
                    window.close();
                } else {
                    alert('Liên kết tài khoản Google thành công!');
                    if (window.opener) {
                        window.opener.location.reload();
                    } else {
                        window.parent.location.reload();
                    }
                    window.close();
                }
            </script>";
            exit();
        }

        // TRƯỜNG HỢP 2: ĐANG ĐĂNG NHẬP (Người dùng ở trang tracuu/dang_nhap)
        
        // TẠM THỜI VÔ HIỆU HÓA TÌM HỌC SINH THEO GOOGLE ID
        /*
        $stmt_student = $db->prepare("SELECT * FROM hoc_sinh WHERE google_id = ?");
        $stmt_student->execute([$google_id]);
        $student = $stmt_student->fetch();
        
        if ($student) {
            handleLoginSuccess($db, $student, true);
            exit();
        }
        */

        // Tìm xem có ADMIN/USER nào đã liên kết với Google ID này chưa
        $stmt_user = $db->prepare("SELECT * FROM users WHERE google_id = ?");
        $stmt_user->execute([$google_id]);
        $user = $stmt_user->fetch();

        if ($user) {
            // Tìm thấy! Kiểm tra 2FA
            if (!empty($user['two_fa_enabled']) && (int) $user['two_fa_enabled'] === 1) {
                $_SESSION['2fa_pending_user_id'] = $user['id'];
                $_SESSION['2fa_pending_user_type'] = 'users';
                header('Location: /thidua/tracuu?show_login=1&trigger_2fa=1');
                exit();
            }
            // Đăng nhập thành công
            handleLoginSuccess($db, $user, false);
            exit();
        }

        // TẠM THỜI VÔ HIỆU HÓA TÌM HỌC SINH THEO EMAIL
        /*
        $stmt_student_email = $db->prepare("SELECT * FROM hoc_sinh WHERE LOWER(email) = LOWER(?) OR LOWER(verified_email) = LOWER(?)");
        $stmt_student_email->execute([$google_email, $google_email]);
        ...
        */

        // Thử khớp theo email của admin/user để tự động liên kết
        $stmt_user_email = $db->prepare("SELECT * FROM users WHERE LOWER(email) = LOWER(?) OR LOWER(verified_email) = LOWER(?)");
        $stmt_user_email->execute([$google_email, $google_email]);
        $user_matches = $stmt_user_email->fetchAll(PDO::FETCH_ASSOC);

        if (count($user_matches) === 1) {
            $matched_user = $user_matches[0];
            if (!empty($matched_user['google_id']) && $matched_user['google_id'] !== $google_id) {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Email Google này đã liên kết với tài khoản quản trị khác.'];
                header('Location: /thidua/tracuu?show_login=1');
                exit();
            }

            if ($conflictMessage = checkGoogleAvailability($db, $google_id, $google_email, null, (int)$matched_user['id'])) {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => $conflictMessage];
                header('Location: /thidua/tracuu?show_login=1');
                exit();
            }

            if (empty($matched_user['google_id'])) {
                $stmt = $db->prepare("UPDATE users SET google_id = ?, verified_email = ? WHERE id = ?");
                $stmt->execute([$google_id, $google_email, $matched_user['id']]);
                $matched_user['google_id'] = $google_id;
                $matched_user['verified_email'] = $google_email;
            }

            if (!empty($matched_user['two_fa_enabled']) && (int) $matched_user['two_fa_enabled'] === 1) {
                $_SESSION['2fa_pending_user_id'] = $matched_user['id'];
                $_SESSION['2fa_pending_user_type'] = 'users';
                header('Location: /thidua/tracuu?show_login=1&trigger_2fa=1');
                exit();
            }

            handleLoginSuccess($db, $matched_user, false);
            exit();
        } elseif (count($user_matches) > 1) {
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Email Google này đang được sử dụng bởi nhiều tài khoản quản trị. Vui lòng liên hệ quản trị viên để được hỗ trợ.'];
            header('Location: /thidua/tracuu?show_login=1');
            exit();
        }

        // Nếu chạy đến đây -> Tài khoản Google này chưa được liên kết
        handleOAuthError('Tài khoản Google này chưa được liên kết với hệ thống.');

    } catch (Exception $e) {
        // Lỗi (ví dụ: hết hạn code, token hỏng...)
        handleOAuthError('Lỗi xác thực OAuth: ' . $e->getMessage(), '/thidua/dang-nhap');
    }
}