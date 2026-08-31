<?php
// File: src/controllers/run_cron_tasks.php

// QUAN TRỌNG: Hãy thay đổi 'SECRET_KEY_123' thành một chuỗi ngẫu nhiên, khó đoán của riêng bạn
$secret_key = '1'; 

if (!isset($_GET['secret']) || $_GET['secret'] !== $secret_key) {
    http_response_code(403);
    die('Forbidden: Invalid secret key.');
}

// Bắt đầu chạy các tác vụ
header('Content-Type: text/plain; charset=utf-8');
echo "=================================================\n";
echo "       BẮT ĐẦU CHẠY CÁC TÁC VỤ TỰ ĐỘNG          \n";
echo "=================================================\n\n";

// Tác vụ 1: Cập nhật trạng thái mã CTV
echo "--- [1] CẬP NHẬT TRẠNG THÁI MÃ CTV ---\n";
$ctv_log = require __DIR__ . '/../tasks/cron_update_ctv_codes_status.php';
echo $ctv_log . "\n\n";

// Đã chuyển sang dùng API Batch để gửi mail nên tác vụ gửi mail tuần tự bằng Cron đã được gỡ bỏ.

// Tác vụ 2: Thu hồi quyền lịch trực tuần đã kết thúc
echo "--- [2] THU HỒI QUYỀN LỊCH TRỰC HẾT HẠN ---\n";
$duty_log = require __DIR__ . '/../tasks/cron_revoke_expired_duty_rosters.php';
echo $duty_log . "\n\n";
echo "=================================================\n";
echo "              HOÀN TẤT TẤT CẢ TÁC VỤ             \n";
echo "=================================================\n";