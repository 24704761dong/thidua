<?php
// File: src/lib/tracking.php (ĐÃ SỬA LỖI HOÀN CHỈNH)

// Sửa lỗi: Đi ngược lên 2 cấp thư mục để tìm thấy folder 'config'
require_once __DIR__ . '/../../config/database.php';
// Sửa lỗi: Gọi helpers.php thay vì logging_db.php
require_once __DIR__ . '/helpers.php';

function update_activity_log() {
    // BỘ LỌC SỐ 1: Dành cho Cron Job chạy bằng dòng lệnh (CLI)
    if (php_sapi_name() === 'cli') {
        return;
    }

    // BỘ LỌC SỐ 2: Dành cho Cron Job chạy bằng URL
    $cron_job_paths = [
        '/thidua/run-email-cron',
        '/thidua/run-cron',
        '/thidua/run-tasks',

        '/thidua/run_cron_tasks.php',
        '/thidua/run_email_cron.php',
        '/thidua/send_birthday_wishes.php'
    ];
    
    $current_path = strtok($_SERVER["REQUEST_URI"], '?');
    
    if (in_array($current_path, $cron_job_paths)) {
        return;
    }
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Kết nối CSDL chính để ghi log PHIÊN TRUY CẬP
    $db = get_db_connection();
    if (!$db) return;

    try {
        $session_id = session_id();
        $current_time = time();

        $user_id = null;
        $user_type = 'guest';
        $user_name = 'Khách';

        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $user_type = $_SESSION['user_vai_tro'] ?? 'admin'; 
            $user_name = $_SESSION['user_ten'] ?? 'Admin';
        } elseif (isset($_SESSION['student_id'])) {
            $user_id = $_SESSION['student_id'];
            $user_type = 'ctv'; 
            $user_name = $_SESSION['student_name'] ?? 'Học sinh'; 
        }

        // Kiểm tra xem session này đã được ghi nhận trong CSDL chính chưa
        $stmt_check = $db->prepare("SELECT session_id FROM phien_truy_cap WHERE session_id = ?");
        $stmt_check->execute([$session_id]);
        $exists = $stmt_check->fetch();

        $is_logged_in = isset($_SESSION['user_id']) || isset($_SESSION['student_id']);
        if ($is_logged_in) {
            $_SESSION['session_recorded'] = true;
        }

        // Nếu là phiên mới, TĂNG TỔNG LƯỢT TRUY CẬP trong CSDL CHÍNH
        if (!$exists) {
            try {
                $db->exec("UPDATE he_thong_thong_ke SET stat_value = CAST(stat_value AS INTEGER) + 1 WHERE stat_key = 'tong_so_luot_truy_cap'");
            } catch (Exception $e) {
                error_log("Failed to increment total visits: " . $e->getMessage());
            }
        }

        // Cập nhật hoặc thêm mới phiên truy cập vào CSDL chính
        if ($exists) {
            $stmt_update = $db->prepare("
                UPDATE phien_truy_cap 
                SET user_id = ?, user_type = ?, user_name = ?, ip_address = ?, last_activity = ?
                WHERE session_id = ?
            ");
            $stmt_update->execute([
                $user_id, $user_type, $user_name,
                $_SERVER['REMOTE_ADDR'] ?? 'N/A',
                $current_time, $session_id
            ]);
        } else {
            $stmt_insert = $db->prepare("
                INSERT INTO phien_truy_cap (session_id, user_id, user_type, user_name, ip_address, last_activity)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt_insert->execute([
                $session_id, $user_id, $user_type, $user_name,
                $_SERVER['REMOTE_ADDR'] ?? 'N/A',
                $current_time
            ]);
        }

        // Xóa các phiên cũ (chạy ngẫu nhiên để giảm tải)
        if (rand(1, 100) === 1) {
            try {
                $stmt_settings = $db->query("SELECT setting_value FROM he_thong_cai_dat WHERE setting_key = 'auto_logout_duration'");
                $auto_logout_duration = (int)($stmt_settings->fetchColumn() ?: 1800);
            } catch (Exception $e) {
                $auto_logout_duration = 1800; // Mặc định 30 phút
            }
            $timeout = $current_time - $auto_logout_duration;
            $db->exec("DELETE FROM phien_truy_cap WHERE last_activity < $timeout");
        }

    } catch (Exception $e) {
        error_log("Activity tracking failed: " . $e->getMessage());
    }
}