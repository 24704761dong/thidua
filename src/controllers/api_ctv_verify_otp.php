<?php
// File: src/controllers/api_ctv_verify_otp.php (File mới)
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'Lỗi xác thực.']);
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);
$otp_input = $data['otp'] ?? '';
$otp_data = $_SESSION['student_otp_data'] ?? null;

if (!$otp_input || !$otp_data) {
    echo json_encode(['success' => false, 'message' => 'Mã OTP không tồn tại hoặc đã hết hạn.']);
    exit();
}

if ((time() - $otp_data['timestamp']) > 300) { // 5 phút
    unset($_SESSION['student_otp_data']);
    echo json_encode(['success' => false, 'message' => 'Mã OTP đã hết hạn.']);
    exit();
}

if ($otp_data['code'] != $otp_input) {
    echo json_encode(['success' => false, 'message' => 'Mã OTP không chính xác.']);
    exit();
}

// Mọi thứ hợp lệ, cập nhật CSDL
try {
    $db = get_db_connection();  
    $studentId = (int) $_SESSION['student_id'];

    $normalizedEmail = strtolower(trim($otp_data['email']));

    $params = [$normalizedEmail];
    $sql = "SELECT id FROM hoc_sinh WHERE LOWER(email) = LOWER(?)";
    if (columnExists($db, 'hoc_sinh', 'verified_email')) {
        $sql .= " OR LOWER(verified_email) = LOWER(?)";
        $params[] = $normalizedEmail;
    }
    $sql .= " AND id != ? LIMIT 1";
    $params[] = $studentId;

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    if ($stmt->fetchColumn()) {
        echo json_encode(['success' => false, 'message' => 'Email này đã được sử dụng bởi một học sinh khác.']);
        exit();
    }

    $userSql = "SELECT id FROM users WHERE LOWER(email) = LOWER(?)";
    $userParams = [$normalizedEmail];
    if (columnExists($db, 'users', 'verified_email')) {
        $userSql .= " OR LOWER(verified_email) = LOWER(?)";
        $userParams[] = $normalizedEmail;
    }
    $userSql .= " LIMIT 1";

    $stmt = $db->prepare($userSql);
    $stmt->execute($userParams);
    if ($stmt->fetchColumn()) {
        echo json_encode(['success' => false, 'message' => 'Email này đã được sử dụng bởi một tài khoản quản trị khác.']);
        exit();
    }

    $stmt = $db->prepare("UPDATE hoc_sinh SET email = ? WHERE id = ?");
    
    // Thực thi câu lệnh
    $stmt->execute([$otp_data['email'], $_SESSION['student_id']]);

    // === GIẢI PHÁP SỬA LỖI: KIỂM TRA ROWCOUNT() ===
    if ($stmt->rowCount() > 0) {
        // Nếu có ít nhất 1 dòng được cập nhật, thì mới là thành công thật
        unset($_SESSION['student_otp_data']); // Xóa OTP sau khi dùng
        echo json_encode(['success' => true, 'message' => 'Cập nhật email thành công!']);
    } else {
        // Nếu không có dòng nào được cập nhật, báo lỗi
        throw new Exception('Không tìm thấy tài khoản để cập nhật. Dữ liệu không thay đổi.');
    }
    // ============================================

} catch (\Throwable $e) {
    // Ghi log để debug sẽ tốt hơn
    error_log("API Verify OTP Error: " . $e->getMessage()); 
    echo json_encode(['success' => false, 'message' => 'Đã xảy ra lỗi phía máy chủ khi cập nhật dữ liệu.']);
}

function columnExists(\PDO $db, string $table, string $column): bool
{
    static $cache = [];
    $key = $table . ':' . $column;
    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }

    try {
        $stmt = $db->prepare("SHOW COLUMNS FROM {$table} LIKE ?");
        $stmt->execute([$column]);
        $exists = (bool) $stmt->fetch();
        $cache[$key] = $exists;
        return $exists;
    } catch (\Throwable $e) {
        $cache[$key] = false;
        return false;
    }
}