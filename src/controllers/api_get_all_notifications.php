<?php
// File: src/controllers/api_get_all_notifications.php (PHIÊN BẢN CUỐI CÙNG - ĐÃ TÍCH HỢP SUPPORT REQUEST)

if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

// Chỉ admin mới có quyền xem thông báo này
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin'])) {
    echo json_encode(['new_count' => 0, 'history' => []]);
    exit();
}

// Nạp các file cần thiết
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../lib/helpers.php'; // Nạp file helper chứa hàm time_ago_in_vietnamese

try {
    $db = get_db_connection();

    // Lấy 10 thông báo gần nhất để có nhiều thông tin hơn
    $stmt_history = $db->query("SELECT * FROM thong_bao ORDER BY thoi_gian DESC LIMIT 10");
    $history_raw = $stmt_history->fetchAll();
    
    $history = [];

    foreach($history_raw as $item) {
        
        // === BẮT ĐẦU NÂNG CẤP LOGIC TẠO LINK ===
        $link = '#'; // Link mặc định
        
        switch ($item['loai_thong_bao']) {
            case 'dang_ky_truc':
                $link = '/thidua/quan-ly-dang-ky-truc';
                break;
            case 'vi_pham_ctv':
            case 'diem_danh_ctv':
                $link = '/thidua/admin/trung-tam-duyet';
                break;
            case 'support_request': // <-- ĐÂY LÀ PHẦN TÍCH HỢP MỚI
                // Tạm thời trỏ đến trang Nhật kỳ, sau này có thể tạo trang quản lý yêu cầu hỗ trợ riêng
                $link = '/thidua/admin/nhat-ky'; 
                break;
            case 'canh_bao_dang_nhap':
                 $link = '/thidua/quan-ly-tai-khoan-ca-nhan';
                 break;
            case 'email_hoc_sinh':
                 $link = '/thidua/admin?page=email-hoc-sinh';
                 break;
        }
        $item['link'] = $link;
        // === KẾT THÚC NÂNG CẤP LOGIC TẠO LINK ===
        
        // Sử dụng hàm time_ago_in_vietnamese để hiển thị thời gian thân thiện
        $item['time_ago'] = time_ago_in_vietnamese($item['thoi_gian']);
        
        // Thêm item đã xử lý vào mảng kết quả
        $history[] = $item;
    }

    // Đếm tổng số thông báo chưa đọc
    $stmt_count = $db->query("SELECT COUNT(id) FROM thong_bao WHERE da_xem = 0");
    $new_count = $stmt_count->fetchColumn();

    echo json_encode(['new_count' => $new_count, 'history' => $history]);

} catch (Exception $e) {
    http_response_code(500);
    error_log("API Get All Notifications Error: " . $e->getMessage());
    echo json_encode(['error' => 'Lỗi máy chủ khi lấy thông báo.']);
}