<?php
// File: src/controllers/api_check_version.php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Nam-Hoc-Id');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Giả lập dữ liệu version (Có thể chuyển vào Database sau nếu muốn động)
$version_data = [
    'success' => true,
    'data' => [
        'latest_version' => '1.0.1', // Phiên bản mới nhất đang có
        'min_required_version' => '1.0.1', // Bất kì máy nào < 1.0.1 sẽ bị khóa màn hình
        'download_url_android' => 'https://thptbinhson.edu.vn/app/zalo-mini-app-latest.apk', // Sửa link này thành link file APK thực tế
        'download_url_ios' => 'https://testflight.apple.com/join/xxxxx', // Link tải iOS (nếu có)
        'release_notes' => '• Sửa lỗi hiển thị sai năm học.\n• Tối ưu tốc độ tải danh sách vi phạm.\n• Thêm xác thực sinh trắc học.',
        'force_update_message' => 'Phiên bản bạn đang sử dụng đã quá cũ. Vui lòng cập nhật để tiếp tục sử dụng ứng dụng ổn định và an toàn hơn.'
    ]
];

echo json_encode($version_data);
exit();
