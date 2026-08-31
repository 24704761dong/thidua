<?php
// File: src/controllers/api_get_student_log_details.php (Đã sửa lỗi)
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập.']);
    exit();
}

// Chỉ cần nạp CSDL chính và các thư viện cần thiết
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../lib/user_agent_parser.php';

$student_id = $_GET['id'] ?? null;
if (!$student_id) {
    echo json_encode(['success' => false, 'message' => 'Thiếu ID học sinh.']);
    exit();
}

try {
    // Chỉ sử dụng kết nối đến CSDL chính
    $db = get_db_connection();

    // Lấy thông tin học sinh để lấy Số CCCD
    $stmt_hs_code = $db->prepare("SELECT ma_hoc_sinh FROM hoc_sinh WHERE id = ?");
    $stmt_hs_code->execute([$student_id]);
    $ma_hoc_sinh = $stmt_hs_code->fetchColumn();

    // Lấy lịch sử đăng nhập từ CSDL chính
    $stmt_logins = $db->prepare("SELECT * FROM lich_su_dang_nhap WHERE hoc_sinh_id = ? ORDER BY id DESC");
    $stmt_logins->execute([$student_id]);
    $logins_raw = $stmt_logins->fetchAll(PDO::FETCH_ASSOC);
    
    $logins = array_map(function($log) {
        $ua_info = parse_user_agent($log['user_agent'] ?? '');
        return [
            'thoi_gian' => date('d/m/Y H:i:s', strtotime($log['thoi_gian_dang_nhap'])),
            'ip' => htmlspecialchars($log['dia_chi_ip']),
            'thiet_bi' => htmlspecialchars($ua_info['full_string'])
        ];
    }, $logins_raw);

    // Lấy lịch sử bị tra cứu TỪ CSDL CHÍNH
    $lookups = [];
    if ($ma_hoc_sinh) {
        $stmt_lookups = $db->prepare("SELECT * FROM nhat_ky_tra_cuu WHERE ma_tra_cuu = ? AND loai_tra_cuu = 'hoc_sinh' ORDER BY id DESC");
        $stmt_lookups->execute([$ma_hoc_sinh]);
        $lookups_raw = $stmt_lookups->fetchAll(PDO::FETCH_ASSOC);

        $lookups = array_map(function($log) {
            $ua_info = parse_user_agent($log['user_agent'] ?? '');
            return [
                'thoi_gian' => date('d/m/Y H:i:s', strtotime($log['thoi_gian_tra_cuu'])),
                'ip' => htmlspecialchars($log['dia_chi_ip']),
                'thiet_bi' => htmlspecialchars($ua_info['full_string'])
            ];
        }, $lookups_raw);
    }

    echo json_encode(['success' => true, 'logins' => $logins, 'lookups' => $lookups]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
}
?>