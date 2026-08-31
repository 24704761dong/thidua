<?php
// File: src/controllers/api_save_user_settings.php (PHIÊN BẢN HOÀN CHỈNH ĐÃ SỬA LỖI)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

// Yêu cầu phải đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để thực hiện hành động này.']);
    exit();
}

// Chỉ cần kết nối đến CSDL Chính
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $setting = $data['setting'] ?? null;
    $value = $data['value'] ?? null;
    $userId = $_SESSION['user_id'];

    $column = '';
    
    // SỬA LỖI: Chấp nhận nhiều tên cài đặt và ánh xạ chúng vào đúng tên cột trong CSDL
    switch ($setting) {
        case 'loginAlert':
        case 'nhan_canh_bao_dang_nhap':
            $column = 'nhan_canh_bao_dang_nhap';
            break;
        case 'nhan_canh_bao_zalo':
            $column = 'nhan_canh_bao_zalo';
            break;
        case 'autoLogout':
        case 'auto_logout_enabled':
            $column = 'auto_logout_enabled';
            break;
        default:
            // Nếu không khớp với bất kỳ cài đặt nào, báo lỗi
            echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật cài đặt: Cài đặt không hợp lệ.']);
            exit();
    }

    $db = get_db_connection();

    // KIỂM TRA BẮT BUỘC: Khi bật cảnh báo Zalo, tài khoản admin phải có sdt
    if ($column === 'nhan_canh_bao_zalo' && ($value ? 1 : 0) === 1) {
        $stmtCheck = $db->prepare("SELECT sdt FROM users WHERE id = ?");
        $stmtCheck->execute([$userId]);
        $sdt = $stmtCheck->fetchColumn();
        if (empty($sdt) || trim($sdt) === '') {
            echo json_encode(['success' => false, 'message' => 'Tài khoản của bạn chưa cập nhật số điện thoại. Vui lòng bổ sung số điện thoại trước khi bật cảnh báo qua Zalo.']);
            exit();
        }
    }

    $sql = "UPDATE users SET $column = ? WHERE id = ?";
    
    try {
        $stmt = $db->prepare($sql);
        // Giá trị value là true/false từ JS, cần chuyển thành 1/0 cho CSDL
        $stmt->execute([$value ? 1 : 0, $userId]);
        
        // Luôn trả về thành công để giao diện người dùng cập nhật đúng
        echo json_encode(['success' => true, 'message' => 'Cài đặt đã được cập nhật.']);

    } catch (PDOException $e) {
        error_log("API Save User Settings Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ khi cập nhật cài đặt.']);
    }
}
?>