<?php
// File: src/lib/helpers.php (PHIÊN BẢN HOÀN CHỈNH ĐÃ SỬA LỖI VÀ BỔ SUNG)

// Nạp các lớp của PHPMailer để sử dụng trong các hàm bên dưới
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . '/../../config/database.php';
/**
 * Ghi một thông điệp vào file log.
 * @param mixed $message Dữ liệu cần ghi (có thể là chuỗi, mảng, đối tượng).
 */
function log_to_file($message) {
    $log_file_path = __DIR__ . '/../../logs/app.log';
    $timestamp = date('Y-m-d H:i:s');
    
    if (is_array($message) || is_object($message)) {
        $message_str = print_r($message, true);
    } else {
        $message_str = $message;
    }
    
    $log_entry = "[{$timestamp}]" . PHP_EOL . $message_str . PHP_EOL . "--------------------" . PHP_EOL;
    
    file_put_contents($log_file_path, $log_entry, FILE_APPEND);
}

/**
 * Chuẩn hóa ngày tháng về định dạng dd/mm/yyyy để hiển thị.
 * Hỗ trợ cả đầu vào dạng Y-m-d, d/m/Y, d-m-Y.
 */
function format_date_display($date_str): string {
    $date_str = trim((string)($date_str ?? ''));
    if ($date_str === '') return '';

    $formats = ['Y-m-d', 'Y/m/d', 'd/m/Y', 'd-m-Y'];
    foreach ($formats as $fmt) {
        $dt = DateTime::createFromFormat($fmt, $date_str);
        if ($dt && $dt->format($fmt) === $date_str) {
            return $dt->format('d/m/Y');
        }
    }

    // Fallback: cố gắng parse bằng strtotime, trả về nguyên gốc nếu thất bại
    $ts = strtotime($date_str);
    return $ts ? date('d/m/Y', $ts) : $date_str;
}

/**
 * Trả về cấu hình mẫu thẻ mặc định được nhúng sẵn trong mã nguồn.
 * Được sử dụng làm phương án dự phòng khi JSON trong CSDL bị lỗi hoặc vượt quá giới hạn.
 */
function get_default_card_template(): array {
    return [
        'version' => 1,
        'generatedAt' => date('c'),
        'cardSize' => [
            'width' => 450,
            'height' => 284,
        ],
        'backgroundType' => 'image',
        'background' => '/thidua/public/assets/phoi_the_mac_dinh.png',
        'backgroundColor' => '#f3f4f6',
        'elements' => [
            'ten_truong' => [
                'id' => 'ten_truong',
                'type' => 'custom-text',
                'text' => 'TRƯỜNG THPT BÌNH SƠN',
                'x' => 160,
                'y' => 24,
                'width' => 250,
                'fontSize' => 16,
                'fontFamily' => 'Montserrat, sans-serif',
                'color' => '#0f172a',
                'isBold' => true,
                'isItalic' => false,
                'textAlign' => 'left',
                'zIndex' => 3,
            ],
            'khau_hieu' => [
                'id' => 'khau_hieu',
                'type' => 'custom-text',
                'text' => 'TIÊN HỌC LỄ - HẬU HỌC VĂN',
                'x' => 160,
                'y' => 50,
                'width' => 250,
                'fontSize' => 13,
                'fontFamily' => 'Lato, sans-serif',
                'color' => '#1f2937',
                'isBold' => false,
                'isItalic' => false,
                'textAlign' => 'left',
                'zIndex' => 3,
            ],
            'ho_ten' => [
                'id' => 'ho_ten',
                'type' => 'ho_ten',
                'x' => 160,
                'y' => 84,
                'width' => 255,
                'fontSize' => 22,
                'fontFamily' => 'Montserrat, sans-serif',
                'color' => '#0f172a',
                'isBold' => true,
                'isItalic' => false,
                'textAlign' => 'left',
                'dynamicSize' => true,
                'sizeRules' => [
                    ['maxChars' => 18, 'fontSize' => 22],
                    ['maxChars' => 24, 'fontSize' => 20],
                    ['maxChars' => 32, 'fontSize' => 18],
                    ['maxChars' => 48, 'fontSize' => 16],
                ],
                'zIndex' => 3,
            ],
            'ma_hoc_sinh_label' => [
                'id' => 'ma_hoc_sinh_label',
                'type' => 'custom-text',
                'text' => 'MÃ HỌC SINH:',
                'x' => 160,
                'y' => 128,
                'width' => 130,
                'fontSize' => 14,
                'fontFamily' => 'Lato, sans-serif',
                'color' => '#1f2937',
                'isBold' => true,
                'isItalic' => false,
                'textAlign' => 'left',
                'zIndex' => 3,
            ],
            'ma_hoc_sinh' => [
                'id' => 'ma_hoc_sinh',
                'type' => 'ma_hoc_sinh',
                'x' => 300,
                'y' => 128,
                'width' => 115,
                'fontSize' => 14,
                'fontFamily' => 'Lato, sans-serif',
                'color' => '#1f2937',
                'isBold' => true,
                'isItalic' => false,
                'textAlign' => 'right',
                'zIndex' => 3,
            ],
            'lop_label' => [
                'id' => 'lop_label',
                'type' => 'custom-text',
                'text' => 'LỚP:',
                'x' => 160,
                'y' => 158,
                'width' => 80,
                'fontSize' => 14,
                'fontFamily' => 'Lato, sans-serif',
                'color' => '#1f2937',
                'isBold' => true,
                'isItalic' => false,
                'textAlign' => 'left',
                'zIndex' => 3,
            ],
            'lop' => [
                'id' => 'lop',
                'type' => 'lop',
                'x' => 240,
                'y' => 158,
                'width' => 175,
                'fontSize' => 14,
                'fontFamily' => 'Lato, sans-serif',
                'color' => '#1f2937',
                'isBold' => true,
                'isItalic' => false,
                'textAlign' => 'left',
                'zIndex' => 3,
            ],
            'ngay_sinh_label' => [
                'id' => 'ngay_sinh_label',
                'type' => 'custom-text',
                'text' => 'NGÀY SINH:',
                'x' => 160,
                'y' => 188,
                'width' => 130,
                'fontSize' => 14,
                'fontFamily' => 'Lato, sans-serif',
                'color' => '#1f2937',
                'isBold' => true,
                'isItalic' => false,
                'textAlign' => 'left',
                'zIndex' => 3,
            ],
            'ngay_sinh' => [
                'id' => 'ngay_sinh',
                'type' => 'ngay_sinh',
                'x' => 300,
                'y' => 188,
                'width' => 115,
                'fontSize' => 14,
                'fontFamily' => 'Lato, sans-serif',
                'color' => '#1f2937',
                'isBold' => true,
                'isItalic' => false,
                'textAlign' => 'right',
                'zIndex' => 3,
            ],
            'anh_the' => [
                'id' => 'anh_the',
                'type' => 'anh_the',
                'x' => 32,
                'y' => 72,
                'width' => 96,
                'height' => 128,
                'zIndex' => 2,
            ],
            'qr_code' => [
                'id' => 'qr_code',
                'type' => 'qr_code',
                'x' => 340,
                'y' => 210,
                'width' => 78,
                'height' => 78,
                'zIndex' => 2,
            ],
        ],
    ];
}
// ===== BẮT ĐẦU NÂNG CẤP: HÀM pdoExecWithRetry CHO MYSQL =====
/**
 * Helper: thực thi câu lệnh ghi với retry khi CSDL (MySQL/SQLite) báo lỗi khóa.
 * Trả về statement ĐÃ THỰC THI để kiểm tra rowCount().
 */
function pdoExecWithRetry(PDO $pdo, string $sql, array $params = [], int $maxRetry = 5, int $baseSleepMs = 120): PDOStatement {
    for ($i = 0; $i <= $maxRetry; $i++) {
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt; // Trả về statement để kiểm tra rowCount()
        } catch (PDOException $e) {
            // Mã lỗi MySQL: 1205 (Lock wait timeout), 1213 (Deadlock)
            // Mã lỗi SQLite: 5 (SQLITE_BUSY)
            $ext = $e->errorInfo[1] ?? null;
            $msgHasLocked = stripos($e->getMessage(), 'database is locked') !== false;
            $isMySQLLock = ($ext === 1205 || $ext === 1213);
            $isBusy = ($ext === 5) || $msgHasLocked || $isMySQLLock; // Kiểm tra cả 3

            if ($isBusy && $i < $maxRetry) {
                usleep(($baseSleepMs * (1 << $i)) * 1000); // Thử lại
                continue;
            }
            throw $e; // Không phải lỗi lock hoặc đã quá số lần thử
        }
    }
    throw new Exception("Không thể thực thi CSDL (pdoExecWithRetry) sau $maxRetry lần thử.");
}
// ===== KẾT THÚC NÂNG CẤP =====



// ===== KẾT THÚC SỬA LỖI =====
/**
 * Lấy một cài đặt cụ thể từ CSDL.
 *
 * @param PDO $db Đối tượng kết nối CSDL.
 * @param string $setting_key Khóa của cài đặt cần lấy.
 * @param mixed $default_value Giá trị trả về nếu không tìm thấy cài đặt.
 * @return mixed Giá trị của cài đặt.
 */
function get_setting(PDO $db, $setting_key, $default_value = null, $nam_hoc_id = null)
{
    $nam_hoc_id = $nam_hoc_id !== null ? (int)$nam_hoc_id : 0;
    
    // Ưu tiên lấy theo nam_hoc_id, nếu không có thì lấy nam_hoc_id = 0 (global)
    $stmt = $db->prepare("SELECT setting_value FROM he_thong_cai_dat WHERE setting_key = ? AND nam_hoc_id IN (?, 0) ORDER BY nam_hoc_id DESC LIMIT 1");
    $stmt->execute([$setting_key, $nam_hoc_id]);
    $result = $stmt->fetchColumn();

    return $result !== false ? $result : $default_value;
}

/**
 * Lấy toàn bộ cài đặt từ CSDL và gán giá trị mặc định.
 * Đây là hàm tiện ích trung tâm để tránh lặp code.
 *
 * @param PDO $db Đối tượng kết nối CSDL.
 * @param int|null $nam_hoc_id ID năm học. Null sẽ lấy chung.
 * @return array Mảng chứa toàn bộ cài đặt của hệ thống.
 */
function get_all_settings(PDO $db, $nam_hoc_id = null): array
{
    if ($nam_hoc_id === null && session_status() === PHP_SESSION_ACTIVE) {
        $nam_hoc_id = $_SESSION['current_nam_hoc_id'] ?? null;
    }

    $per_year_keys = [
        'auto_approve_violations',
        'auto_approve_duty_roster',
        'auto_grant_permissions_on_duty_approve',
        'auto_approve_attendance',
        'student_can_edit_sdt',
        'student_can_edit_email',
        'student_can_edit_chuc_vu',
    ];

    // 1. Lấy tất cả cài đặt toàn cục (nam_hoc_id = 0 hoặc NULL)
    $stmt_global = $db->query("SELECT setting_key, setting_value FROM he_thong_cai_dat WHERE nam_hoc_id = 0 OR nam_hoc_id IS NULL");
    $settings_raw = $stmt_global->fetchAll(PDO::FETCH_KEY_PAIR);

    // 2. Nếu có năm học cụ thể, nạp cài đặt theo năm học (chỉ áp dụng cho các key được cách ly theo năm học)
    if ($nam_hoc_id) {
        $stmt_year = $db->prepare("SELECT setting_key, setting_value FROM he_thong_cai_dat WHERE nam_hoc_id = ?");
        $stmt_year->execute([$nam_hoc_id]);
        $year_rows = $stmt_year->fetchAll(PDO::FETCH_KEY_PAIR);
        foreach ($year_rows as $k => $v) {
            if (in_array($k, $per_year_keys)) {
                $settings_raw[$k] = $v;
            }
        }
    }

    // Định nghĩa tất cả các giá trị mặc định
    $defaults = [
        'report_diem_tiet_tot'    => 1,
        'report_diem_tiet_tb'     => 0,
        'report_sdb_tt_tich'      => 1,
        'report_sdb_ck_tich'      => 1,
        'report_sdb_nk_tich'      => 1,
        'report_nhat_ky_tich'     => 1,
        'report_sdb_tt_khong'     => 0,
        'report_sdb_ck_khong'     => 0,
        'report_sdb_nk_khong'     => 0,
        'report_nhat_ky_khong'    => 0,
        'report_sdb_use_tt'       => 'on',
        'report_sdb_use_ck'       => 'on',
        'report_sdb_use_nk'       => 'on',
        'report_sdb_use_nhat_ky'  => 'on',
        'report_vang_source'      => 'diem_danh',
        'report_tru_vang_p'       => -0.5,
        'report_tru_vang_kp'      => -1,
        'report_vang_p_vids'      => '[]',
        'report_vang_kp_vids'     => '[]',
    ];

    // Giá trị từ CSDL đè lên mặc định
    return array_merge($defaults, $settings_raw);
}
/**
 * Chuyển đổi một chuỗi thời gian (từ CSDL) thành định dạng "thời gian trôi qua"
 * một cách thân thiện và luôn đảm bảo tính toán dựa trên múi giờ Việt Nam.
 *
 * @param string $time_string Chuỗi thời gian từ CSDL.
 * @return string Chuỗi mô tả thời gian đã trôi qua.
 */
function time_ago_in_vietnamese($time_string) {
    if (empty($time_string)) {
        return '';
    }
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    try {
        $time = new DateTime($time_string, new DateTimeZone('Asia/Ho_Chi_Minh'));
        $now = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
        $diff = $now->getTimestamp() - $time->getTimestamp();
        if ($diff < 60) { return 'vài giây trước'; }
        $minutes = round($diff / 60);
        if ($minutes < 60) { return $minutes . ' phút trước'; }
        $hours = round($diff / 3600);
        if ($hours < 24) { return $hours . ' giờ trước'; }
        $days = round($diff / 86400);
        if ($days == 1) { return 'hôm qua lúc ' . $time->format('H:i'); }
        if ($days < 7) { return $days . ' ngày trước'; }
        return 'ngày ' . $time->format('d/m/Y');
    } catch (Exception $e) {
        error_log("Error in time_ago_in_vietnamese: " . $e->getMessage());
        return '';
    }
}

// =================== NHÀ MÁY SẢN XUẤT EMAIL MẪU (BẮT ĐẦU) ===================

/**
 * Tạo nội dung HTML cho email gửi mã OTP với giao diện chuyên nghiệp.
 * @param string $otp Mã OTP cần gửi.
 * @return string Nội dung HTML hoàn chỉnh của email.
 */
function generate_beautiful_otp_email($otp) {
    $email_id = date('YmdHis');
    $logo_url = "https://c3binhson.edu.vn/thidua/public/assets/img/22logoapp.png";
    return <<<HTML
    <!DOCTYPE html>
    <html lang="vi">
    <head>
            <meta charset="UTF-8">
            <meta name="color-scheme" content="light only">
            <meta name="supported-color-schemes" content="light">
            <style>
                    :root {
                            color-scheme: light only;
                            supported-color-schemes: light;
        }
    </style>
</head>
<body style="background-color: #f8fafc; margin: 0; padding: 0;">
    <div style="font-family: Inter, Arial, sans-serif; max-width: 640px; margin: auto; background: linear-gradient(to bottom right, #f8fafc, #E4F6FD); background-color: #E4F6FD; padding: 28px 20px; border-radius: 12px; border: 1px solid rgba(34, 67, 151, 0.25);">
        <div style="text-align: center; margin-bottom: 24px;">
            <img src="{$logo_url}" alt="Logo THPT Bình Sơn" style="height: 60px; max-width: 180px; object-fit: contain; margin-bottom: 12px;">
            <h2 style="color: #224397; margin: 0; font-size: 1.5rem;">XÁC THỰC TÀI KHOẢN EMAIL</h2>
            <p style="font-size: 0.95rem; color: #475569; margin-top: 8px;">
                Cảm ơn bạn đã sử dụng <strong style="color: #224397;">Hệ thống Đánh Giá Thi Đua</strong> của <strong style="color: #224397;">Trường THPT Bình Sơn</strong>.
            </p>
        </div>

        <div style="background-color: #ffffff; padding: 28px 24px; border: 2px dashed rgba(34, 67, 151, 0.4); border-radius: 12px; text-align: center; margin-bottom: 24px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
            <p style="margin: 0 0 12px; font-size: 1rem; color: #334155;">Mã xác thực của bạn là:</p>
            <div style="
                font-size: 2.4rem;
                font-weight: 700;
                letter-spacing: 8px;
                color: #224397;
                background: linear-gradient(90deg, #f8fafc, #e0f2fe);
                padding: 16px 28px;
                border-radius: 10px;
                display: inline-block;
                border: 1px solid rgba(34, 67, 151, 0.2);
                box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            ">
                {$otp}
            </div>
            <p style="margin-top: 16px; font-size: 0.95rem; color: #64748b;">
                Mã này sẽ hết hạn trong <strong style="color: #dc2626;">5 phút</strong>.
            </p>
        </div>

        <p style="font-size: 0.9rem; color: #64748b; text-align: center;">
            Nếu bạn không yêu cầu mã này, vui lòng bỏ qua email này.
        </p>

        <hr style="margin: 28px auto; border: none; border-top: 1px solid rgba(34, 67, 151, 0.15); width: 80%;">

        <p style="font-size: 0.85rem; color: #64748b; margin-top: 12px; text-align: center;">
            Nếu bạn gặp sự cố hoặc cần hỗ trợ, vui lòng liên hệ quản trị hệ thống qua:<br>
            <strong>Liên hệ hỗ trợ:</strong>
            <a href="https://zalo.me/0362566146" style="color:#224397; text-decoration:none; font-weight: 600;">Zalo 036.256.6146</a>
        </p>

        <p style="text-align: center; font-size: 0.85rem; color: #94a3b8; line-height: 1.6; margin-top: 24px;">
            Trân trọng,<br>
            <span style="font-weight: 600; color: #224397;">Hệ thống quản lý thi đua</span><br>
            <span style="font-style: italic; color:#64748b;">Trường THPT Bình Sơn</span><br>
            <span style="font-size: 0.75rem; color: #cbd5e1;">Email ID: {$email_id}</span>
        </p>
    </div>
    </body>
    </html>
HTML;
}



/**
 * Tạo nội dung HTML cho email cảnh báo đăng nhập.
 * @param string $recipient_name Tên người nhận.
 * @param string $time Thời gian đăng nhập.
 * @param string $ip_address Địa chỉ IP.
 * @param string $browser Trình duyệt và thiết bị.
 * @return string Nội dung HTML của email.
 */
function generate_beautiful_login_alert_email($recipient_name, $time, $ip_address, $browser, $vi_tri_ip = 'Không xác định', $vi_tri_gps = null) {
    $email_id = date('YmdHis');
    $recipient_name_safe = htmlspecialchars($recipient_name);
    $logo_url = "https://c3binhson.edu.vn/thidua/public/assets/img/22logoapp.png";

    // Xử lý link GPS bên ngoài HEREDOC để tránh lỗi hiển thị chuỗi
    $gps_html = "Không cấp quyền";
    if (!empty($vi_tri_gps)) {
        $gps_html = "<a href=\"{$vi_tri_gps}\" target=\"_blank\" style=\"color:#224397; text-decoration:none; font-weight:600;\">Xem trên Google Maps</a>";
    }

    return <<<HTML
    <!DOCTYPE html>
    <html lang="vi">
    <head>
            <meta charset="UTF-8">
            <meta name="color-scheme" content="light only">
            <meta name="supported-color-schemes" content="light">
            <style>
                    :root {
                            color-scheme: light only;
                            supported-color-schemes: light;
        }
    </style>
</head>
<body style="background-color: #f8fafc; margin: 0; padding: 0;">
    <div style="font-family: Inter, Arial, sans-serif; max-width: 640px; margin: auto; background: linear-gradient(to bottom right, #f8fafc, #E4F6FD); background-color: #E4F6FD; padding: 28px 20px; border-radius: 12px; border: 1px solid rgba(34, 67, 151, 0.25);">
        <div style="text-align: center; margin-bottom: 24px;">
            <img src="{$logo_url}" alt="Logo THPT Bình Sơn" style="height: 60px; max-width: 180px; object-fit: contain; margin-bottom: 12px;">
            <h2 style="color: #224397; margin: 0; font-size: 1.5rem;">CẢNH BÁO ĐĂNG NHẬP</h2>
            <p style="font-size: 0.95rem; color: #475569; margin-top: 8px;">
                Dành cho tài khoản quản trị: <strong style="color: #FAB723;">{$recipient_name_safe}</strong>
            </p>
        </div>

        <div style="background-color: #ffffff; padding: 24px; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 24px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
            <p style="margin: 0 0 16px; font-size: 1rem; color: #334155;">
                Hệ thống ghi nhận một lượt <strong style="color: #224397;">đăng nhập vào tài khoản</strong> của thầy/cô với thông tin chi tiết như sau:
            </p>
            <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.95rem;">
                <li style="padding: 10px 0; border-bottom: 1px solid #f1f5f9;">
                    <strong style="color: #64748b;">🕒 Thời gian:</strong> 
                    <span style="float: right; color: #1e293b; font-weight: 500;">{$time}</span>
                </li>
                <li style="padding: 10px 0; border-bottom: 1px solid #f1f5f9;">
                    <strong style="color: #64748b;">🌐 Địa chỉ IP:</strong> 
                    <span style="float: right; color: #1e293b; font-weight: 500;">{$ip_address}</span>
                </li>
                <li style="padding: 10px 0; border-bottom: 1px solid #f1f5f9;">
                    <strong style="color: #64748b;">💻 Thiết bị:</strong> 
                    <span style="float: right; color: #1e293b; font-weight: 500;">{$browser}</span>
                </li>
                <li style="padding: 10px 0; border-bottom: 1px solid #f1f5f9;">
                    <strong style="color: #64748b;">📍 Vị trí (IP):</strong> 
                    <span style="float: right; color: #1e293b; font-weight: 500;">{$vi_tri_ip}</span>
                </li>
                <li style="padding: 10px 0;">
                    <strong style="color: #64748b;">🧭 Tọa độ GPS:</strong> 
                    <span style="float: right; color: #1e293b;">
                        {$gps_html}
                    </span>
                </li>
            </ul>
        </div>

        <p style="font-size: 0.9rem; color: #dc2626; text-align: center; margin-bottom: 24px; padding: 12px; background-color: #fef2f2; border-radius: 8px; border: 1px solid #fecaca;">
            Nếu đây <strong>không phải là bạn</strong>, vui lòng 
            <strong>đổi mật khẩu ngay lập tức</strong> và kiểm tra lại các hoạt động gần đây.
        </p>

        <hr style="margin: 28px auto; border: none; border-top: 1px solid rgba(34, 67, 151, 0.15); width: 80%;">

        <p style="font-size: 0.85rem; color: #64748b; margin-top: 12px; text-align: center;">
            Nếu bạn cần hỗ trợ, vui lòng liên hệ quản trị hệ thống qua:<br>
            <strong>Liên hệ hỗ trợ:</strong> 
            <a href="https://zalo.me/0362566146" style="color:#224397; text-decoration:none; font-weight: 600;">Zalo 036.256.6146</a>
        </p>

        <p style="text-align: center; font-size: 0.85rem; color: #94a3b8; line-height: 1.6; margin-top: 24px;">
            Trân trọng,<br>
            <span style="font-weight: 600; color: #224397;">Hệ thống quản lý thi đua</span><br>
            <span style="font-style: italic; color:#64748b;">Trường THPT Bình Sơn</span><br>
            <span style="font-size: 0.75rem; color: #cbd5e1;">Email ID: {$email_id}</span>
        </p>
    </div>
    </body>
    </html>
HTML;
}

// =================== NHÀ MÁY SẢN XUẤT EMAIL MẪU (KẾT THÚC) ===================

/**
 * Gửi email cảnh báo khi có người đăng nhập vào tài khoản admin.
 * (Nâng cấp để sử dụng hàng đợi email)
 * @param string $recipient_email Email của người nhận (admin).
 * @param string $recipient_name Tên của người nhận (admin).
 */
function send_login_alert_email($recipient_email, $recipient_name, $vi_tri_ip = 'Không xác định', $vi_tri_gps = null) {
    // Nạp file phân tích user-agent
    require_once __DIR__ . '/user_agent_parser.php';

    // Lấy thông tin về lượt đăng nhập
    require_once __DIR__ . '/location_helpers.php';
    $ip_address = get_client_ip();
    $user_agent_string = $_SERVER['HTTP_USER_AGENT'] ?? 'Không xác định';
    $ua_info = parse_user_agent($user_agent_string);
    $platform = isset($ua_info['platform']) ? ' trên ' . $ua_info['platform'] : '';
    $browser = htmlspecialchars($ua_info['browser'] . $platform);
    $time = date('H:i:s d/m/Y');

    // Chuẩn bị nội dung email
    $subject = '[Cảnh Báo Bảo Mật] Ghi nhận đăng nhập tài khoản quản trị';
    $body = generate_beautiful_login_alert_email($recipient_name, $time, $ip_address, $browser, $vi_tri_ip, $vi_tri_gps);
    $alt_body = "Hệ thống ghi nhận một lượt đăng nhập vào tài khoản quản trị của bạn lúc {$time} từ IP {$ip_address} ({$vi_tri_ip}) trên thiết bị {$browser}. Nếu không phải bạn, hãy đổi mật khẩu ngay.";

    // Đưa email vào hàng đợi với độ ưu tiên cao (số 5)
    queue_email($recipient_email, $recipient_name, $subject, $body, $alt_body, 5, [
        'type' => 'admin_login_alert',
        'metadata' => [
            'ip' => $ip_address,
            'user_agent' => $user_agent_string,
            'browser' => $browser,
        ],
    ]);
}


/**
 * Tạo một khung email đẹp mắt để bọc nội dung do admin tự soạn.
 * @param string $admin_content Nội dung HTML do admin soạn thảo.
 * @param string $recipient_name Tên người nhận.
 * @return string Nội dung HTML hoàn chỉnh của email.
 */
function generate_beautiful_admin_reply_wrapper($admin_content, $recipient_name) {
    $email_id = date('YmdHis');
    $name_safe = htmlspecialchars($recipient_name);
    $logo_url = "https://c3binhson.edu.vn/thidua/public/assets/img/22logoapp.png";

    return <<<HTML
    <!DOCTYPE html>
    <html lang="vi">
    <head>
            <meta charset="UTF-8">
            <meta name="color-scheme" content="light only">
            <meta name="supported-color-schemes" content="light">
            <style>
                    :root {
                            color-scheme: light only;
                            supported-color-schemes: light;
        }
    </style>
</head>
<body style="background-color: #f8fafc; margin: 0; padding: 0;">
    <div style="font-family: Inter, Arial, sans-serif; max-width: 640px; margin: auto; background: linear-gradient(to bottom right, #f8fafc, #E4F6FD); background-color: #E4F6FD; padding: 28px 20px; border-radius: 12px; border: 1px solid rgba(34, 67, 151, 0.25);">
        <div style="text-align: center; margin-bottom: 24px;">
            <img src="{$logo_url}" alt="Logo THPT Bình Sơn" style="height: 60px; max-width: 180px; object-fit: contain; margin-bottom: 12px;">
            <h2 style="color: #224397; margin: 0; font-size: 1.5rem;">PHẢN HỒI YÊU CẦU HỖ TRỢ</h2>
            <p style="font-size: 0.95rem; color: #475569; margin-top: 8px;">
                Từ Ban Quản Trị Hệ Thống - Trường THPT Bình Sơn
            </p>
        </div>

        <div style="background-color: #ffffff; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 24px; font-size: 1rem; color: #334155; line-height: 1.7; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
            <p style="margin: 0 0 16px;">Xin chào <strong style="color: #FAB723;">{$name_safe}</strong>,</p>
            <div style="margin-left: 4px; color: #1e293b;">{$admin_content}</div>
        </div>

        <div style="font-size: 0.9rem; color: #334155; text-align: center; background-color: #e0f2fe; padding: 12px 16px; border-radius: 8px; border: 1px solid #bae6fd;">
            💬 Đây là email phản hồi chính thức từ <strong style="color: #224397;">Hệ thống quản lý thi đua</strong>.<br>
            Vui lòng không trả lời trực tiếp email này. Nếu cần hỗ trợ thêm, vui lòng liên hệ qua Zalo.
        </div>

        <hr style="margin: 28px auto; border: none; border-top: 1px solid rgba(34, 67, 151, 0.15); width: 80%;">

        <p style="font-size: 0.85rem; color: #64748b; margin-top: 12px; text-align: center;">
            <strong>Liên hệ hỗ trợ kỹ thuật:</strong>
            <a href="https://zalo.me/0362566146" style="color:#224397; text-decoration:none; font-weight: 600;">Zalo 036.256.6146</a> - Phạm Văn Thành Đồng (Quản Trị Viên)
        </p>

        <p style="text-align: center; font-size: 0.85rem; color: #94a3b8; line-height: 1.6; margin-top: 24px;">
            Trân trọng,<br>
            <span style="font-weight: 600; color: #224397;">Hệ thống quản lý thi đua</span><br>
            <span style="font-style: italic; color:#64748b;">Trường THPT Bình Sơn</span><br>
            <span style="font-size: 0.75rem; color: #cbd5e1;">Email ID: {$email_id}</span>
        </p>
    </div>
    </body>
    </html>
HTML;
}


/**
 * Tạo nội dung HTML cho email tự động phản hồi yêu cầu hỗ trợ (gửi cho Người dùng).
 * ĐÃ NÂNG CẤP: Hiển thị lại toàn bộ thông tin người dùng đã gửi.
 * * @param string $requester_name Tên của người gửi yêu cầu.
 * @param string $requester_email Email của người gửi.
 * @param string $requester_phone SĐT của người gửi.
 * @param string $content Nội dung đã gửi.
 * @return string Nội dung HTML của email.
 */
function generate_beautiful_support_reply_email($requester_name, $requester_email, $requester_phone, $content) {
    $email_id = date('YmdHis');
    $logo_url = "https://c3binhson.edu.vn/thidua/public/assets/img/22logoapp.png";

    // Sanitize dữ liệu chống XSS
    $name_safe = htmlspecialchars($requester_name);
    $email_safe = htmlspecialchars($requester_email);
    $phone_safe = htmlspecialchars($requester_phone);
    $content_safe = nl2br(htmlspecialchars($content));

    return <<<HTML
    <!DOCTYPE html>
    <html lang="vi">
    <head>
            <meta charset="UTF-8">
            <meta name="color-scheme" content="light only">
            <meta name="supported-color-schemes" content="light">
            <style>
                    :root {
                            color-scheme: light only;
                            supported-color-schemes: light;
        }
    </style>
</head>
<body style="background-color: #f8fafc; margin: 0; padding: 0;">
    <div style="font-family: Inter, Arial, sans-serif; max-width: 640px; margin: auto; background: linear-gradient(to bottom right, #f8fafc, #E4F6FD); background-color: #E4F6FD; padding: 28px 20px; border-radius: 12px; border: 1px solid rgba(34, 67, 151, 0.25);">
        <div style="text-align: center; margin-bottom: 24px;">
            <img src="{$logo_url}" alt="Logo THPT Bình Sơn" style="height: 60px; max-width: 180px; object-fit: contain; margin-bottom: 12px;">
            <h2 style="color: #224397; margin: 0; font-size: 1.5rem;">ĐÃ GHI NHẬN YÊU CẦU CỦA BẠN</h2>
            <p style="font-size: 0.95rem; color: #475569; margin-top: 8px;">
                Hệ thống Đánh Giá Thi Đua - Trường THPT Bình Sơn
            </p>
        </div>

        <div style="background-color: #ffffff; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 24px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
            <p style="margin: 0 0 16px; font-size: 1rem; color: #1e293b;">
                Xin chào <strong style="color: #FAB723;">{$name_safe}</strong>,
            </p>
            <p style="margin: 0; font-size: 1rem; color: #334155; line-height: 1.6;">
                Hệ thống xác nhận đã nhận được yêu cầu hỗ trợ của bạn và sẽ phản hồi trong thời gian sớm nhất.
            </p>
        </div>

        <div style="background-color: #ffffff; padding: 24px; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 24px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
            <p style="margin: 0 0 16px; font-size: 1rem; color: #1e293b; font-weight: 600;">📋 Thông tin bạn đã gửi:</p>
            <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.95rem; line-height: 1.8;">
                <li style="padding: 10px; background-color: #f8fafc; border-radius: 6px; margin-bottom: 8px;">
                    <strong style="color: #64748b;">👤 Họ và tên:</strong>
                    <span style="color: #1e293b; float: right; font-weight: 500;">{$name_safe}</span>
                </li>
                <li style="padding: 10px; background-color: #f8fafc; border-radius: 6px; margin-bottom: 8px;">
                    <strong style="color: #64748b;">📧 Email:</strong>
                    <span style="color: #1e293b; float: right; font-weight: 500;">{$email_safe}</span>
                </li>
                <li style="padding: 10px; background-color: #f8fafc; border-radius: 6px; margin-bottom: 16px;">
                    <strong style="color: #64748b;">📞 Số điện thoại:</strong>
                    <span style="color: #1e293b; float: right; font-weight: 500;">{$phone_safe}</span>
                </li>
                <li style="padding: 10px; border-top: 1px solid #f1f5f9;">
                    <strong style="color: #64748b;">📝 Nội dung:</strong>
                    <div style="margin-top: 8px; padding: 12px; background-color: #f8fafc; border-radius: 6px; color: #334155; white-space: pre-wrap; word-wrap: break-word;">{$content_safe}</div>
                </li>
            </ul>
        </div>

        <div style="font-size: 0.9rem; color: #334155; text-align: center; background-color: #e0f2fe; padding: 12px 16px; border-radius: 8px; border: 1px solid #bae6fd;">
            💬 Yêu cầu của bạn đã được hệ thống ghi nhận thành công.<br>
            <strong>Vui lòng không trả lời trực tiếp email này.</strong>
        </div>

        <hr style="margin: 28px auto; border: none; border-top: 1px solid rgba(34, 67, 151, 0.15); width: 80%;">

        <p style="font-size: 0.85rem; color: #64748b; margin-top: 12px; text-align: center;">
            Cần hỗ trợ thêm? Hãy liên hệ bộ phận kỹ thuật qua:<br>
            <strong>Liên hệ hỗ trợ:</strong>
            <a href="https://zalo.me/0362566146" style="color:#224397; text-decoration:none; font-weight: 600;">Zalo 036.256.6146</a>
        </p>

        <p style="text-align: center; font-size: 0.85rem; color: #94a3b8; line-height: 1.6; margin-top: 24px;">
            Trân trọng,<br>
            <span style="font-weight: 600; color: #224397;">Hệ thống quản lý thi đua</span><br>
            <span style="font-style: italic; color:#64748b;">Trường THPT Bình Sơn</span><br>
            <span style="font-size: 0.75rem; color: #cbd5e1;">Email ID: {$email_id}</span>
        </p>
    </div>
    </body>
    </html>
HTML;
}

/**
 * Thêm một email vào hàng đợi trong CSDL để được gửi sau.
 * @param string $recipient_email Email người nhận.
 * @param string $recipient_name Tên người nhận.
 * @param string $subject Tiêu đề email.
 * @param string $body Nội dung HTML của email.
 * @param string $alt_body Nội dung dạng text (dự phòng).
 * @param int $priority Độ ưu tiên (số nhỏ hơn được gửi trước).
 * @return bool True nếu thêm vào hàng đợi thành công.
 */
/**
 * Lấy Access Token cho FCM v1 API sử dụng Service Account JSON
 */
function get_fcm_v1_access_token($json_path) {
    if (!file_exists($json_path)) {
        if (function_exists("log_to_file")) log_to_file("[FCM v1] Không tìm thấy file JSON tại: " . $json_path);
        return false;
    }
    $json = json_decode(file_get_contents($json_path), true);
    if (!isset($json['client_email']) || !isset($json['private_key'])) {
        if (function_exists("log_to_file")) log_to_file("[FCM v1] File JSON không hợp lệ.");
        return false;
    }

    $client_email = $json['client_email'];
    $private_key = $json['private_key'];
    $scopes = 'https://www.googleapis.com/auth/firebase.messaging';

    $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
    $now = time();
    $payload = json_encode([
        'iss' => $client_email,
        'scope' => $scopes,
        'aud' => 'https://oauth2.googleapis.com/token',
        'exp' => $now + 3600,
        'iat' => $now
    ]);

    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
    $signatureInput = $base64UrlHeader . "." . $base64UrlPayload;

    $signature = '';
    $success = openssl_sign($signatureInput, $signature, $private_key, OPENSSL_ALGO_SHA256);
    if (!$success) {
         if (function_exists("log_to_file")) log_to_file("[FCM v1] Lỗi ký JWT bằng OpenSSL.");
         return false;
    }

    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    $jwt = $signatureInput . "." . $base64UrlSignature;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    if (isset($data['access_token'])) {
        return ['access_token' => $data['access_token'], 'project_id' => $json['project_id'] ?? ''];
    }
    
    if (function_exists("log_to_file")) log_to_file("[FCM v1] Lỗi lấy token: " . $response);
    return false;
}

/**
 * Gửi thông báo đẩy qua Firebase Cloud Messaging (FCM v1 API) cho Mobile App (APK/IPA).
 * @param array|string $device_tokens Danh sách FCM token của thiết bị.
 * @param string $title Tiêu đề thông báo.
 * @param string $body Nội dung thông báo.
 * @param array $data_payload Dữ liệu đính kèm (tùy chọn).
 * @return array Kết quả trả về.
 */
function send_fcm_push_notification($device_tokens, $title, $body, $data_payload = []) {
    if (empty($device_tokens)) return ['success' => false, 'message' => 'Không có token thiết bị.'];
    
    // JSON path lấy từ .env
    $json_path = $_ENV['FCM_SERVICE_ACCOUNT_JSON'] ?? ''; 
    if (empty($json_path)) {
        if (function_exists("log_to_file")) log_to_file("[FCM v1] Bỏ qua vì chưa cấu hình FCM_SERVICE_ACCOUNT_JSON trong .env");
        return ['success' => false, 'message' => 'Chưa cấu hình FCM_SERVICE_ACCOUNT_JSON'];
    }

    // Nếu đường dẫn là tương đối, nối với thư mục gốc
    if (!file_exists($json_path) && file_exists(__DIR__ . '/../../' . $json_path)) {
        $json_path = __DIR__ . '/../../' . $json_path;
    }

    $auth_data = get_fcm_v1_access_token($json_path);
    if (!$auth_data) {
        return ['success' => false, 'message' => 'Lỗi tạo Access Token v1 (hoặc sai đường dẫn JSON)'];
    }

    $access_token = $auth_data['access_token'];
    $project_id = $auth_data['project_id'];
    $url = "https://fcm.googleapis.com/v1/projects/{$project_id}/messages:send";

    $headers = [
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json'
    ];

    $success_count = 0;
    $errors = [];
    $device_tokens = is_array($device_tokens) ? $device_tokens : [$device_tokens];

    // FCM v1 chỉ cho phép gửi từng token trong 1 request thông thường
    foreach ($device_tokens as $token) {
        $message = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body
                ],
                'data' => empty($data_payload) ? new stdClass() : $data_payload
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($result, true);
        if ($http_code == 200 && isset($decoded['name'])) {
            $success_count++;
        } else {
            $errors[] = $result;
        }
    }

    if ($success_count > 0) {
        if (function_exists("log_to_file")) log_to_file("[FCM v1] Gửi thành công tới {$success_count} thiết bị.");
        return ['success' => true, 'success_count' => $success_count, 'errors' => $errors];
    }

    if (function_exists("log_to_file")) log_to_file("[FCM v1] Lỗi gửi: " . print_r($errors, true));
    return ['success' => false, 'errors' => $errors];
}


/**
 * Gửi danh sách email qua API (Batch)
 * @param array $emails Mảng các email [{'to': '...', 'subject': '...', 'html': '...'}, ...]
 * @return array Kết quả trả về từ API
 */
function send_email_via_api_batch(array $emails): array {
    $api_url = $_ENV['MAIL_API_URL'] ?? 'http://localhost:3001/';
    $api_key = $_ENV['MAIL_API_KEY'] ?? '';

    if (empty($api_key) || empty($emails)) {
        return ['success' => false, 'message' => 'API Key hoặc danh sách email trống.'];
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Tăng timeout lên 5s để tránh nghẽn
    // GIẢ LẬP TRÌNH DUYỆT ĐỂ VƯỢT QUA TƯỜNG LỬA (CLOUDFLARE BOT FIGHT MODE)
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $success_count = 0;
    $last_result = null;
    $curl_error_msg = null;

    foreach ($emails as $email) {
        $payload = json_encode([
            'api_key' => $api_key,
            'to' => $email['to'] ?? '',
            'subject' => $email['subject'] ?? '',
            'body' => $email['html'] ?? $email['body'] ?? ''
        ]);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $result = curl_exec($ch);
        
        if ($result === false) {
            $curl_error_msg = curl_error($ch);
        }

        $decoded = json_decode($result, true);
        if ($decoded && isset($decoded['status']) && $decoded['status'] !== 'error') {
            $success_count++;
        }
        $last_result = $decoded;
    }
    
    curl_close($ch);

    if ($success_count > 0) {
        return ['success' => true, 'message' => "Đã gửi $success_count email.", 'details' => $last_result];
    }

    $error_message = $last_result['message'] ?? 'Lỗi không xác định từ API';
    if ($curl_error_msg) {
        $error_message .= " (cURL Error: $curl_error_msg)";
    }

    return ['success' => false, 'message' => $error_message];
}

function queue_email(string $recipient_email, string $recipient_name, string $subject, string $body, string $alt_body = '', int $priority = 10, array $options = []): bool {
    try {
        $db = get_db_connection();
        if (php_sapi_name() !== 'cli' && session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $created_at = date('Y-m-d H:i:s');
        $status = $options['status'] ?? 'pending';

        $stmt = $db->prepare(
            "INSERT INTO email_queue (recipient_email, recipient_name, subject, body, alt_body, status, priority, created_at, sent_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $inserted = $stmt->execute([
            $recipient_email,
            $recipient_name,
            $subject,
            $body,
            $alt_body,
            $status,
            $priority,
            $created_at,
            ($status !== 'pending' ? $created_at : null) // Nếu đã gửi qua API thì lưu luôn sent_at
        ]);

        if (!$inserted) {
            return false;
        }

        $queue_id = (int) $db->lastInsertId();

        $email_type = $options['type'] ?? 'general';
        $metadata = $options['metadata'] ?? [];
        $triggered_by = $options['triggered_by'] ?? ($_SESSION['user_id'] ?? null);
        $triggered_name = $options['triggered_name'] ?? ($_SESSION['user_ten'] ?? null);
        $triggered_type = $options['triggered_type'] ?? ($_SESSION['user_vai_tro'] ?? 'system');
        $triggered_ip = $options['triggered_ip'] ?? ($_SERVER['REMOTE_ADDR'] ?? null);

        $metadata_json = null;
        if (!empty($metadata)) {
            $metadata_json = json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        try {
            $stmt_log = $db->prepare(
                "INSERT INTO system_email_logs (
                    queue_id, recipient_email, recipient_name, subject, body_html, alt_body,
                    status, email_type, priority, metadata, triggered_by, triggered_name,
                    triggered_type, triggered_ip, created_at, sent_at, last_updated_at, error_message
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            $stmt_log->execute([
                $queue_id ?: null,
                $recipient_email,
                $recipient_name,
                $subject,
                $body,
                $alt_body,
                $status, // Đã sửa
                $email_type,
                $priority,
                $metadata_json,
                $triggered_by,
                $triggered_name,
                $triggered_type,
                $triggered_ip,
                $created_at,
                ($status !== 'pending' ? $created_at : null), // Đã sửa
                $created_at,
                null
            ]);
        } catch (Exception $logException) {
            error_log('Không thể ghi log email hệ thống: ' . $logException->getMessage());
        }

        // --- GỬI API NGAY LẬP TỨC NẾU TRẠNG THÁI LÀ PENDING ---
        // Do đã gỡ bỏ CronJob theo yêu cầu, hệ thống sẽ đẩy thẳng email sang API Node.js.
        // API Node.js xử lý Queue riêng biệt rất nhanh nên sẽ không gây block.
        if ($status === 'pending') {
            $batch_emails = [[
                'to' => $recipient_email,
                'subject' => $subject,
                'html' => $body,
                'type' => $email_type,
                'name' => $recipient_name,
                'data' => []
            ]];
            $api_result = send_email_via_api_batch($batch_emails);
            $new_status = ($api_result['success'] ?? false) ? 'api_sent' : 'failed';
            $error_msg = '';
            if ($new_status === 'failed') {
                $error_msg = $api_result['message'] ?? 'Lỗi không xác định khi gọi API';
            }
            
            // Cập nhật lại db
            if ($queue_id > 0) {
                $stmt_update_queue = $db->prepare("UPDATE email_queue SET status = ?, sent_at = NOW() WHERE id = ?");
                $stmt_update_queue->execute([$new_status, $queue_id]);
                
                $stmt_update_log = $db->prepare("UPDATE system_email_logs SET status = ?, sent_at = NOW(), error_message = ? WHERE queue_id = ?");
                $stmt_update_log->execute([$new_status, $error_msg, $queue_id]);
            }
        }

        return true;
    } catch (Exception $e) {
        // Ghi lại lỗi nhưng không làm gián đoạn luồng của người dùng
        error_log("Lỗi khi đưa email vào hàng đợi: " . $e->getMessage());
        return false;
    }
}

/**
 * Tạo nội dung HTML cho email thông báo có Sổ Nhật Kỳ Online mới.
 *
 * @param string $ctv_name Tên CTV gửi.
 * @param string $class_name Tên lớp.
 * @param string $week_name Tên tuần.
 * @return array Mảng chứa 'subject' và 'body' của email.
 */
function generate_journal_submission_email($ctv_name, $class_name, $week_name) {
    $email_id = date('YmdHis');
    $logo_url = "https://c3binhson.edu.vn/thidua/public/assets/img/22logoapp.png";
    $subject = "[Nhật Kỳ Mới] Lớp {$class_name} vừa nộp báo cáo cho {$week_name}";

    // Xử lý an toàn cho REQUEST_SCHEME
    $scheme = $_SERVER['REQUEST_SCHEME'] ?? ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http');
    
    // Xử lý an toàn cho HTTP_HOST (Rất cần thiết nếu gửi email qua CLI/Cronjob)
    $host = $_SERVER['HTTP_HOST'] ?? 'dilys.id.vn';

    // Tạo link an toàn
    $link = "{$scheme}://{$host}/thidua/admin/duyet-so-nhat-ky";

    $body = <<<HTML
    <!DOCTYPE html>
    <html lang="vi">
    <head>
            <meta charset="UTF-8">
            <meta name="color-scheme" content="light only">
            <meta name="supported-color-schemes" content="light">
            <style>
                    :root {
                            color-scheme: light only;
                            supported-color-schemes: light;
        }
    </style>
</head>
<body style="background-color: #f8fafc; margin: 0; padding: 0;">
    <div style="font-family: Inter, Arial, sans-serif; max-width: 640px; margin: auto; background: linear-gradient(to bottom right, #f8fafc, #E4F6FD); background-color: #E4F6FD; padding: 28px 20px; border-radius: 12px; border: 1px solid rgba(34, 67, 151, 0.25);">
        <div style="text-align: center; margin-bottom: 24px;">
            <img src="{$logo_url}" alt="Logo THPT Bình Sơn" style="height: 60px; max-width: 180px; object-fit: contain; margin-bottom: 12px;">
            <h2 style="color: #224397; margin: 0; font-size: 1.5rem;">NHẬT KỲ TRỰC TUYẾN MỚI</h2>
            <p style="font-size: 0.95rem; color: #475569; margin-top: 8px;">
                Một báo cáo mới đang chờ được duyệt từ hệ thống QLĐG - Trường THPT Bình Sơn.
            </p>
        </div>

        <div style="background-color: #ffffff; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 24px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
            <p style="margin: 0 0 16px; font-size: 1rem; color: #1e293b;">Xin chào <strong style="color: #FAB723;">Quản Trị Viên</strong>,</p>
            <p style="margin-bottom: 20px; font-size: 0.95rem; color: #334155;">
                Hệ thống ghi nhận một <strong style="color: #224397;">Nhật Kỳ Trực Tuyến</strong> vừa được gửi lên với thông tin chi tiết như sau:
            </p>
            <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.95rem;">
                <li style="padding: 10px; background-color: #f8fafc; border-radius: 6px; margin-bottom: 8px;">
                    <strong style="color: #64748b;">👤 Người gửi:</strong>
                    <span style="font-weight: 600; color: #1e293b; float: right;">{$ctv_name}</span>
                </li>
                <li style="padding: 10px; background-color: #f8fafc; border-radius: 6px; margin-bottom: 8px;">
                    <strong style="color: #64748b;">🏫 Lớp:</strong>
                    <span style="font-weight: 600; color: #1e293b; float: right;">{$class_name}</span>
                </li>
                <li style="padding: 10px; background-color: #f8fafc; border-radius: 6px;">
                    <strong style="color: #64748b;">📅 Tuần báo cáo:</strong>
                    <span style="font-weight: 600; color: #1e293b; float: right;">{$week_name}</span>
                </li>
            </ul>

            <div style="text-align: center; margin-top: 28px;">
                <a href="{$link}" 
                   style="background-color: #224397; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: 600; display: inline-block; box-shadow: 0 2px 4px rgba(34,67,151,0.2);">
                    🔍 Xem và Duyệt Ngay
                </a>
            </div>
        </div>

        <div style="font-size: 0.9rem; color: #334155; text-align: center; background-color: #e0f2fe; padding: 12px 16px; border-radius: 8px; border: 1px solid #bae6fd;">
            Nhật Kỳ được gửi lên bởi <strong style="color: #224397;">{$ctv_name}</strong> – Lớp <strong style="color: #224397;">{$class_name}</strong> cho tuần <strong style="color: #224397;">{$week_name}</strong>.<br>
            Hãy duyệt để xác nhận nội dung báo cáo.
        </div>

        <hr style="margin: 28px auto; border: none; border-top: 1px solid rgba(34, 67, 151, 0.15); width: 80%;">

        <p style="font-size: 0.85rem; color: #64748b; margin-top: 12px; text-align: center;">
            Cần hỗ trợ kỹ thuật? Liên hệ ngay qua:<br>
            <strong>Liên hệ hỗ trợ:</strong>
            <a href="https://zalo.me/0362566146" style="color:#224397; text-decoration:none; font-weight: 600;">Zalo 036.256.6146</a>
        </p>

        <p style="text-align: center; font-size: 0.85rem; color: #94a3b8; line-height: 1.6; margin-top: 24px;">
            Trân trọng,<br>
            <span style="font-weight: 600; color: #224397;">Hệ thống quản lý thi đua</span><br>
            <span style="font-style: italic; color:#64748b;">Trường THPT Bình Sơn</span><br>
            <span style="font-size: 0.75rem; color: #cbd5e1;">Email ID: {$email_id}</span>
        </p>
    </div>
    </body>
    </html>
HTML;

    return ['subject' => $subject, 'body' => $body];
}
/**
 * Tạo nội dung HTML cho email thông báo trạng thái Sổ nhật kỳ (Gửi cho CTV).
 *
 * @param string $ctv_name Tên CTV nhận mail.
 * @param string $ten_lop Tên lớp.
 * @param string $ten_tuan Tên tuần.
 * @param string $status Trạng thái ('approved', 'rejected').
 * @param string $ghi_chu_admin Ghi chú từ admin (chỉ dùng khi rejected).
 * @return array Mảng chứa 'subject' và 'body' của email.
 */
function generate_journal_status_email(string $ctv_name, string $ten_lop, string $ten_tuan, string $status, string $ghi_chu_admin = '') {
    $email_id = date('YmdHis');
    $logo_url = "https://c3binhson.edu.vn/thidua/public/assets/img/22logoapp.png";
    $ctv_name_safe = htmlspecialchars($ctv_name);

    $isApproved = ($status === 'approved');
    $status_text = $isApproved ? 'ĐÃ DUYỆT' : 'ĐÃ TỪ CHỐI';
    $color = $isApproved ? '#16a34a' : '#dc2626';
    $emoji = $isApproved ? '✅' : '❌';

    $message = $isApproved
        ? "🎉Sổ Nhật Kỳ Trực Tuyến của lớp <strong>{$ten_lop}</strong> cho tuần <strong>{$ten_tuan}</strong> đã được phê duyệt thành công."
        : "⚠️Sổ Nhật Kỳ Trực Tuyến của lớp <strong>{$ten_lop}</strong> cho tuần <strong>{$ten_tuan}</strong> đã bị từ chối và cần chỉnh sửa lại.";

    $ghi_chu_html = '';
    if (!$isApproved && $ghi_chu_admin) {
        $ghi_chu_html = "
        <div style='margin-top: 20px;'>
            <h4 style='color: #dc2626; margin-bottom: 8px;'>💬 Ghi chú từ Ban Thi Đua:</h4>
            <div style='padding: 14px; background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; font-size: 0.95rem; color: #7f1d1d; line-height: 1.6;'>
                " . nl2br(htmlspecialchars($ghi_chu_admin)) . "
            </div>
        </div>";
    }

    $subject = "[Sổ Nhật Kỳ] {$status_text} - Lớp {$ten_lop} ({$ten_tuan})";
    $link = "{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}/thidua/hocsinh/so-nhat-ky/nhap?tuan_id=";

    $body = <<<HTML
    <!DOCTYPE html>
    <html lang="vi">
    <head>
            <meta charset="UTF-8">
            <meta name="color-scheme" content="light only">
            <meta name="supported-color-schemes" content="light">
            <style>
                    :root {
                            color-scheme: light only;
                            supported-color-schemes: light;
        }
    </style>
</head>
<body style="background-color: #f8fafc; margin: 0; padding: 0;">
    <div style="font-family: Inter, Arial, sans-serif; max-width: 640px; margin: auto; background: linear-gradient(to bottom right, #f8fafc, #E4F6FD); background-color: #E4F6FD; padding: 28px 20px; border-radius: 12px; border: 1px solid rgba(34, 67, 151, 0.25);">
        <!-- HEADER -->
        <div style="text-align: center; margin-bottom: 24px;">
            <img src="{$logo_url}" alt="Logo THPT Bình Sơn" style="height: 60px; max-width: 180px; object-fit: contain; margin-bottom: 12px;">
            <h2 style="color: {$color}; margin: 0; font-size: 1.5rem;">{$emoji} {$status_text}</h2>
            <p style="font-size: 0.95rem; color: #475569; margin-top: 8px;">
                Gửi đến học sinh: <strong style="color: #FAB723;">{$ctv_name_safe}</strong>
            </p>
        </div>

        <!-- BODY -->
        <div style="background-color: #ffffff; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
            <p style="margin: 0 0 12px; font-size: 1rem; color: #1e293b;">{$message}</p>
            {$ghi_chu_html}
            <div style="text-align: center; margin-top: 28px;">
                <a href="{$link}" 
                   style="background-color: #224397; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: 600; display: inline-block; box-shadow: 0 2px 4px rgba(34,67,151,0.2);">
                    📘 Xem Chi Tiết Sổ Nhật Kỳ
                </a>
            </div>
        </div>

        <!-- FOOTER -->
        <p style="font-size: 0.85rem; color: #64748b; margin-top: 24px; text-align: center;">
            Cần hỗ trợ? Liên hệ:
            <a href="https://zalo.me/0362566146" style="color:#224397; text-decoration:none; font-weight: 600;">Zalo 036.256.6146</a>
        </p>

        <hr style="margin: 20px auto; border: none; border-top: 1px solid rgba(34, 67, 151, 0.15); width: 80%;">
        <p style="text-align: center; font-size: 0.85rem; color: #94a3b8; line-height: 1.6; padding-bottom: 16px;">
            Trân trọng,<br>
            <span style="font-weight: 600; color: #224397;">Hệ thống quản lý thi đua</span><br>
            <span style="font-style: italic; color:#64748b;">Trường THPT Bình Sơn</span><br>
            <span style="font-size: 0.75rem; color: #cbd5e1;">Email ID: {$email_id}</span>
        </p>
    </div>
    </body>
    </html>
HTML;

    return ['subject' => $subject, 'body' => $body];
}

/**
 * Xóa một thư mục và toàn bộ nội dung bên trong nó (đệ quy).
 *
 * @param string $dir Đường dẫn đến thư mục cần xóa.
 */
function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $object) && !is_link($dir . DIRECTORY_SEPARATOR . $object)) {
                    rrmdir($dir . DIRECTORY_SEPARATOR . $object);
                } else {
                    unlink($dir . DIRECTORY_SEPARATOR . $object);
                }
            }
        }
        rmdir($dir);
    }
}

/**
 * Gửi email xác nhận đến CTV sau khi nộp Sổ nhật kỳ thành công.
 */
function generate_ctv_submission_confirmation_email(string $ctv_name, string $class_name, string $week_name, array $summary) {
    $email_id = date('YmdHis');
    $logo_url = "https://c3binhson.edu.vn/thidua/public/assets/img/22logoapp.png";

    // Tổng hợp số liệu
    $total_tiet = (int)($summary['totals']['tot'] ?? 0)
        + (int)($summary['totals']['kha'] ?? 0)
        + (int)($summary['totals']['tb'] ?? 0)
        + (int)($summary['totals']['yeu'] ?? 0);

    $subject = "[XÁC NHẬN] Đã gửi Báo cáo Nhật Kỳ Lớp {$class_name} thành công!";

    // Tình trạng minh chứng
    $summary_rows = '';
    $has_proofs_status = 'Chưa nộp';
    $has_proofs_color = '#dc2626'; // red

    if (!empty($summary['proof_counts'])) {
        $total_proofs = 0;
        foreach ($summary['proof_counts'] as $key => $count) {
            $total_proofs += $count;
            $label = '';
        switch ($key) {
            case 'sdb_ck':
                $label = 'Sổ Chính Khóa';
                break;
            case 'sdb_tt':
                $label = 'Sổ Tăng Tiết';
                break;
            case 'sdb_nk':
                $label = 'Sổ Ngoại Khóa';
                break;
            case 'khac':
                $label = 'Minh Chứng Khác';
                break;
            default:
                $label = $key;
                break;
        }

            if ($count > 0) {
                $summary_rows .= "<li style='margin:6px 0;'>📘 {$label}: 
                    <strong style='color:#2563eb;'>{$count} tệp</strong>
                </li>";
            }
        }

        if ($total_proofs > 0) {
            $has_proofs_status = 'Đã nộp đầy đủ';
            $has_proofs_color = '#16a34a'; // green
        } else {
            $has_proofs_status = 'Không có file đính kèm';
            $has_proofs_color = '#f59e0b'; // yellow
        }
    }

    $body = <<<HTML
    <!DOCTYPE html>
    <html lang="vi">
    <head>
            <meta charset="UTF-8">
            <meta name="color-scheme" content="light only">
            <meta name="supported-color-schemes" content="light">
            <style>
                    :root {
                            color-scheme: light only;
                            supported-color-schemes: light;
        }
    </style>
</head>
<body style="background-color: #f8fafc; margin: 0; padding: 0;">
    <div style="font-family: Inter, Arial, sans-serif; max-width: 640px; margin:auto; background: linear-gradient(to bottom right, #f8fafc, #E4F6FD); background-color: #E4F6FD; border-radius:12px; border:1px solid rgba(34, 67, 151, 0.25);">
        <!-- HEADER -->
        <div style="text-align:center; padding:28px 20px 12px;">
            <img src="{$logo_url}" alt="Logo THPT Bình Sơn" style="height:60px; max-width:180px; object-fit:contain; margin-bottom:12px;">
            <h2 style="color:#16a34a; margin:0; font-size:1.4rem;">✅ GỬI BÁO CÁO THÀNH CÔNG</h2>
            <p style="font-size:0.95rem; color:#475569; margin-top:8px;">
                Cảm ơn <strong style="color:#FAB723;">{$ctv_name}</strong>. Báo cáo của bạn đã được gửi lên hệ thống thành công!
            </p>
        </div>

        <!-- BODY -->
        <div style="background-color:#ffffff; padding:24px; border-radius:12px; border:1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
            <p style="margin:0 0 10px; font-size:1rem; color:#1e293b;">Chào bạn <strong style="color:#FAB723;">{$ctv_name}</strong>,</p>
            <p style="margin-bottom:18px; color:#334155; line-height: 1.6;">
                Hệ thống xác nhận đã nhận thành công <strong style="color:#224397;">Sổ Nhật Kỳ Trực Tuyến</strong> của lớp <strong style="color:#224397;">{$class_name}</strong> cho tuần <strong style="color:#224397;">{$week_name}</strong>. 
                Báo cáo này hiện đang chờ <strong style="color:#224397;">Ban Thi Đua</strong> duyệt.
            </p>

            <!-- Tóm tắt chấm tiết -->
            <h4 style="color:#224397; margin-top:18px; border-bottom:1px solid rgba(34,67,151,0.15); padding-bottom:5px;">1️⃣ Tóm tắt kết quả chấm tiết</h4>
            <table style="width:100%; border-collapse:collapse; font-size:0.95rem;">
                <tr style="background-color:#f0f9ff;">
                    <td style='padding:10px; border:1px solid #bae6fd; font-weight:600; color:#0c4a6e;'>Tổng số tiết được chấm</td>
                    <td style='padding:10px; border:1px solid #bae6fd; text-align:center; font-weight:700; color:#0c4a6e;'>{$total_tiet}</td>
                </tr>
                <tr><td style='padding:10px;border:1px solid #e2e8f0;color:#334155;'>Số tiết Tốt</td><td style='padding:10px;border:1px solid #e2e8f0;text-align:center;color:#16a34a;font-weight:700;'>{$summary['totals']['tot']}</td></tr>
                <tr><td style='padding:10px;border:1px solid #e2e8f0;color:#334155;'>Số tiết Khá</td><td style='padding:10px;border:1px solid #e2e8f0;text-align:center;color:#2563eb;font-weight:700;'>{$summary['totals']['kha']}</td></tr>
                <tr><td style='padding:10px;border:1px solid #e2e8f0;color:#334155;'>Số tiết TB</td><td style='padding:10px;border:1px solid #e2e8f0;text-align:center;color:#f59e0b;font-weight:700;'>{$summary['totals']['tb']}</td></tr>
                <tr><td style='padding:10px;border:1px solid #e2e8f0;color:#334155;'>Số tiết Yếu</td><td style='padding:10px;border:1px solid #e2e8f0;text-align:center;color:#dc2626;font-weight:700;'>{$summary['totals']['yeu']}</td></tr>
            </table>

            <!-- Minh chứng -->
            <h4 style="color:#224397; margin-top:22px; border-bottom:1px solid rgba(34,67,151,0.15); padding-bottom:5px;">2️⃣ Tình trạng minh chứng đính kèm</h4>
            <p style="margin-bottom:10px; color:#334155;">
                Trạng thái: 
                <span style='background-color:{$has_proofs_color}; color:white; padding:4px 10px; border-radius:6px; font-weight:600;'>{$has_proofs_status}</span>
            </p>
            <ul style="margin:8px 0 0 20px; color:#334155; font-size:0.95rem;">
                {$summary_rows}
            </ul>

            <p style="margin-top:20px; color:#64748b; font-size:0.9rem; padding: 12px; background-color: #f8fafc; border-radius: 8px;">
                Bạn sẽ nhận được email tiếp theo khi báo cáo này được <strong>duyệt</strong> hoặc <strong>yêu cầu chỉnh sửa</strong>. 
                Vui lòng theo dõi thường xuyên để cập nhật kịp thời 💡.
            </p>
        </div>  

        <!-- FOOTER -->
        <p style="font-size:0.85rem; color:#64748b; margin-top:24px; text-align:center;">
            Cần hỗ trợ liên hệ:
            <a href="https://zalo.me/0362566146" style="color:#224397; text-decoration:none; font-weight: 600;">Zalo 036.256.6146</a>
        </p>

        <hr style="margin:20px auto; border:none; border-top:1px solid rgba(34, 67, 151, 0.15); width:80%;">
        <p style="text-align:center; font-size:0.85rem; color:#94a3b8; line-height:1.6; padding-bottom:16px;">
            Trân trọng,<br>
            <span style="font-weight:600; color:#224397;">Hệ thống quản lý thi đua</span><br>
            <span style="font-style:italic; color:#64748b;">Trường THPT Bình Sơn</span><br>
            <span style="font-size:0.75rem; color:#cbd5e1;">Email ID: {$email_id}</span>
        </p>
    </div>
    </body>
    </html>
HTML;

    return ['subject' => $subject, 'body' => $body];
}

/**
 * Tạo nội dung HTML cho email thông báo cho CTV (Báo cáo vi phạm đã được duyệt)
 */
function generate_ctv_approved_violations_email($ctv_name, $approved_list, $week_name) {
    $email_id = date('YmdHis');
    $logo_url = 'https://c3binhson.edu.vn/thidua/public/assets/img/22logoapp.png';
    $subject = '[Đã Duyệt] Báo cáo vi phạm ' . $week_name . ' của bạn đã được duyệt';

    $table_rows = '';
    foreach ($approved_list as $index => $vp) {
        $stt = $index + 1;
        $table_rows .= "
        <tr>
            <td style='padding: 8px; border-bottom: 1px solid #e2e8f0; text-align: center;'>{$stt}</td>
            <td style='padding: 8px; border-bottom: 1px solid #e2e8f0;'><strong>{$vp['raw_ho_ten']}</strong><br><small style='color:#64748b;'>Lớp: {$vp['raw_ten_lop']}</small></td>
            <td style='padding: 8px; border-bottom: 1px solid #e2e8f0;'>{$vp['ten_vi_pham']}</td>
            <td style='padding: 8px; border-bottom: 1px solid #e2e8f0;'>" . date('d/m/Y', strtotime($vp['ngay_vi_pham'])) . "</td>
        </tr>";
    }

    $count = count($approved_list);
    $body = <<<HTML
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
    </head>
    <body style="background-color: #f8fafc; margin: 0; padding: 0;">
        <div style="font-family: Inter, Arial, sans-serif; max-width: 640px; margin: auto; background: #E4F6FD; padding: 28px 20px; border-radius: 12px; border: 1px solid rgba(34, 67, 151, 0.25);">
            <div style="text-align: center; margin-bottom: 24px;">
                <img src="{$logo_url}" alt="Logo" style="height: 60px; max-width: 180px; object-fit: contain; margin-bottom: 12px;">
                <h2 style="color: #16a34a; margin: 0; font-size: 1.5rem;">✅ BÁO CÁO ĐÃ ĐƯỢC DUYỆT</h2>
                <p style="font-size: 0.95rem; color: #475569; margin-top: 8px;">Gửi đến CTV: <strong style="color: #FAB723;">{$ctv_name}</strong></p>
            </div>
            <div style="background-color: #ffffff; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0;">
                <p style="margin: 0 0 16px; font-size: 1rem; color: #1e293b;">Xin chào <strong>{$ctv_name}</strong>,</p>
                <p style="margin-bottom: 20px; font-size: 0.95rem; color: #334155;">Hệ thống thông báo: Danh sách <strong>{$count}</strong> vi phạm bạn báo cáo cho <strong>{$week_name}</strong> đã được Ban thi đua duyệt chính thức.</p>
                <div style="overflow-x:auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem; text-align: left;">
                        <thead>
                            <tr style="background-color: #f1f5f9; color: #334155;">
                                <th style="padding: 10px; border-bottom: 2px solid #cbd5e1; text-align: center;">STT</th>
                                <th style="padding: 10px; border-bottom: 2px solid #cbd5e1;">Học sinh</th>
                                <th style="padding: 10px; border-bottom: 2px solid #cbd5e1;">Lỗi vi phạm</th>
                                <th style="padding: 10px; border-bottom: 2px solid #cbd5e1;">Ngày VP</th>
                            </tr>
                        </thead>
                        <tbody>
                            {$table_rows}
                        </tbody>
                    </table>
                </div>
            </div>
            <p style="text-align: center; font-size: 0.85rem; color: #94a3b8; margin-top: 24px;">Trân trọng,<br><span style="font-weight: 600; color: #224397;">Hệ thống quản lý thi đua</span><br><span style="font-size: 0.75rem;">Email ID: {$email_id}</span></p>
        </div>
    </body>
    </html>
HTML;
    return ['subject' => $subject, 'body' => $body];
}

/**
 * Tạo nội dung HTML cho email thông báo cảnh báo cho Học sinh vi phạm
 */
function generate_student_violations_notice_email($student_name, $class_name, $violations_list) {
    $email_id = date('YmdHis');
    $logo_url = 'https://c3binhson.edu.vn/thidua/public/assets/img/22logoapp.png';
    $subject = '[CẢNH BÁO KỶ LUẬT] Thông báo vi phạm mới - ' . $student_name;

    $table_rows = '';
    foreach ($violations_list as $index => $vp) {
        $stt = $index + 1;
        $table_rows .= "
        <tr>
            <td style='padding: 8px; border-bottom: 1px solid #e2e8f0; text-align: center;'>{$stt}</td>
            <td style='padding: 8px; border-bottom: 1px solid #e2e8f0; color:#dc2626; font-weight:600;'>{$vp['ten_vi_pham']}</td>
            <td style='padding: 8px; border-bottom: 1px solid #e2e8f0;'>" . date('d/m/Y', strtotime($vp['ngay_vi_pham'])) . "</td>
            <td style='padding: 8px; border-bottom: 1px solid #e2e8f0;'>{$vp['ghi_chu']}</td>
        </tr>";
    }

    $count = count($violations_list);
    $body = <<<HTML
    <!DOCTYPE html>
    <html lang="vi">
    <head><meta charset="UTF-8"></head>
    <body style="background-color: #f8fafc; margin: 0; padding: 0;">
        <div style="font-family: Inter, Arial, sans-serif; max-width: 640px; margin: auto; background: #fff1f2; padding: 28px 20px; border-radius: 12px; border: 1px solid rgba(220, 38, 38, 0.25);">
            <div style="text-align: center; margin-bottom: 24px;">
                <img src="{$logo_url}" alt="Logo" style="height: 60px; max-width: 180px; object-fit: contain; margin-bottom: 12px;">
                <h2 style="color: #dc2626; margin: 0; font-size: 1.5rem;">⚠️ CẢNH BÁO VI PHẠM</h2>
                <p style="font-size: 0.95rem; color: #475569; margin-top: 8px;">Gửi đến học sinh: <strong style="color: #dc2626;">{$student_name}</strong> - Lớp: <strong>{$class_name}</strong></p>
            </div>
            <div style="background-color: #ffffff; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0;">
                <p style="margin: 0 0 16px; font-size: 1rem; color: #1e293b;">Xin chào <strong>{$student_name}</strong>,</p>
                <p style="margin-bottom: 20px; font-size: 0.95rem; color: #334155;">Đoàn trường thông báo: Bạn vừa bị ghi nhận <strong>{$count}</strong> vi phạm kỷ luật. Các vi phạm này đã được duyệt và lưu vào hồ sơ cá nhân.</p>
                <div style="overflow-x:auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem; text-align: left;">
                        <thead>
                            <tr style="background-color: #fef2f2; color: #991b1b;">
                                <th style="padding: 10px; border-bottom: 2px solid #fecaca; text-align: center;">STT</th>
                                <th style="padding: 10px; border-bottom: 2px solid #fecaca;">Lỗi vi phạm</th>
                                <th style="padding: 10px; border-bottom: 2px solid #fecaca;">Ngày VP</th>
                                <th style="padding: 10px; border-bottom: 2px solid #fecaca;">Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>{$table_rows}</tbody>
                    </table>
                </div>
                <p style="margin-top: 20px; font-size: 0.9rem; color: #b91c1c; font-weight:600;">Yêu cầu học sinh nghiêm túc rút kinh nghiệm và không tái phạm.</p>
            </div>
            <p style="text-align: center; font-size: 0.85rem; color: #94a3b8; margin-top: 24px;">Trân trọng,<br><span style="font-weight: 600; color: #224397;">Đoàn Trường THPT Bình Sơn</span><br><span style="font-size: 0.75rem;">Email ID: {$email_id}</span></p>
        </div>
    </body>
    </html>
HTML;
    return ['subject' => $subject, 'body' => $body];
}

/**
 * Tạo nội dung HTML cho email thông báo đến GVCN
 */
function generate_gvcn_violations_notice_email($gvcn_name, $class_name, $violations_list) {
    $email_id = date('YmdHis');
    $logo_url = 'https://c3binhson.edu.vn/thidua/public/assets/img/22logoapp.png';
    $subject = '[Kỷ Luật] Học sinh lớp ' . $class_name . ' vừa bị duyệt vi phạm mới';

    $table_rows = '';
    foreach ($violations_list as $index => $vp) {
        $stt = $index + 1;
        $table_rows .= "
        <tr>
            <td style='padding: 8px; border-bottom: 1px solid #e2e8f0; text-align: center;'>{$stt}</td>
            <td style='padding: 8px; border-bottom: 1px solid #e2e8f0;'><strong>{$vp['raw_ho_ten']}</strong></td>
            <td style='padding: 8px; border-bottom: 1px solid #e2e8f0; color:#dc2626;'>{$vp['ten_vi_pham']}</td>
            <td style='padding: 8px; border-bottom: 1px solid #e2e8f0;'>" . date('d/m/Y', strtotime($vp['ngay_vi_pham'])) . "</td>
            <td style='padding: 8px; border-bottom: 1px solid #e2e8f0;'>{$vp['ghi_chu']}</td>
        </tr>";
    }

    $count = count($violations_list);
    $body = <<<HTML
    <!DOCTYPE html>
    <html lang="vi">
    <head><meta charset="UTF-8"></head>
    <body style="background-color: #f8fafc; margin: 0; padding: 0;">
        <div style="font-family: Inter, Arial, sans-serif; max-width: 640px; margin: auto; background: #fffbeb; padding: 28px 20px; border-radius: 12px; border: 1px solid rgba(217, 119, 6, 0.25);">
            <div style="text-align: center; margin-bottom: 24px;">
                <img src="{$logo_url}" alt="Logo" style="height: 60px; max-width: 180px; object-fit: contain; margin-bottom: 12px;">
                <h2 style="color: #d97706; margin: 0; font-size: 1.5rem;">THÔNG BÁO TỚI GVCN</h2>
                <p style="font-size: 0.95rem; color: #475569; margin-top: 8px;">Gửi GVCN Lớp: <strong style="color: #d97706;">{$class_name}</strong></p>
            </div>
            <div style="background-color: #ffffff; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0;">
                <p style="margin: 0 0 16px; font-size: 1rem; color: #1e293b;">Kính gửi Thầy/Cô <strong>{$gvcn_name}</strong>,</p>
                <p style="margin-bottom: 20px; font-size: 0.95rem; color: #334155;">Đoàn trường kính báo: Vừa qua có <strong>{$count}</strong> vi phạm kỷ luật của học sinh lớp <strong>{$class_name}</strong> đã được kiểm duyệt và ghi nhận vào hệ thống.</p>
                <div style="overflow-x:auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem; text-align: left;">
                        <thead>
                            <tr style="background-color: #fef3c7; color: #92400e;">
                                <th style="padding: 10px; border-bottom: 2px solid #fde68a; text-align: center;">STT</th>
                                <th style="padding: 10px; border-bottom: 2px solid #fde68a;">Học sinh</th>
                                <th style="padding: 10px; border-bottom: 2px solid #fde68a;">Lỗi vi phạm</th>
                                <th style="padding: 10px; border-bottom: 2px solid #fde68a;">Ngày VP</th>
                                <th style="padding: 10px; border-bottom: 2px solid #fde68a;">Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>{$table_rows}</tbody>
                    </table>
                </div>
                <p style="margin-top: 20px; font-size: 0.9rem; color: #334155;">Kính mong Thầy/Cô lưu ý và nhắc nhở học sinh lớp mình.</p>
            </div>
            <p style="text-align: center; font-size: 0.85rem; color: #94a3b8; margin-top: 24px;">Trân trọng,<br><span style="font-weight: 600; color: #224397;">Đoàn Trường THPT Bình Sơn</span><br><span style="font-size: 0.75rem;">Email ID: {$email_id}</span></p>
        </div>
    </body>
    </html>
HTML;
    return ['subject' => $subject, 'body' => $body];
}

/**
 * Tạo nội dung HTML cho email thông báo cho CTV (Báo cáo vi phạm bị từ chối)
 */
function generate_ctv_rejected_violations_email($ctv_name, $rejected_list, $week_name) {
    $email_id = date('YmdHis');
    $logo_url = 'https://c3binhson.edu.vn/thidua/public/assets/img/22logoapp.png';
    $subject = '[Từ chối] Một số vi phạm ' . $week_name . ' của bạn không được duyệt';

    $table_rows = '';
    foreach ($rejected_list as $index => $vp) {
        $stt = $index + 1;
        $table_rows .= "
        <tr>
            <td style='padding: 8px; border-bottom: 1px solid #e2e8f0; text-align: center;'>{$stt}</td>
            <td style='padding: 8px; border-bottom: 1px solid #e2e8f0;'><strong>{$vp['raw_ho_ten']}</strong><br><small style='color:#64748b;'>Lớp: {$vp['raw_ten_lop']}</small></td>
            <td style='padding: 8px; border-bottom: 1px solid #e2e8f0; color:#dc2626;'>{$vp['ten_vi_pham']}</td>
            <td style='padding: 8px; border-bottom: 1px solid #e2e8f0;'>" . date('d/m/Y', strtotime($vp['ngay_vi_pham'])) . "</td>
        </tr>";
    }

    $count = count($rejected_list);
    $body = <<<HTML
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
    </head>
    <body style="background-color: #f8fafc; margin: 0; padding: 0;">
        <div style="font-family: Inter, Arial, sans-serif; max-width: 640px; margin: auto; background: #fff1f2; padding: 28px 20px; border-radius: 12px; border: 1px solid rgba(220, 38, 38, 0.25);">
            <div style="text-align: center; margin-bottom: 24px;">
                <img src="{$logo_url}" alt="Logo" style="height: 60px; max-width: 180px; object-fit: contain; margin-bottom: 12px;">
                <h2 style="color: #dc2626; margin: 0; font-size: 1.5rem;">❌ BÁO CÁO BỊ TỪ CHỐI</h2>
                <p style="font-size: 0.95rem; color: #475569; margin-top: 8px;">Gửi đến CTV: <strong style="color: #FAB723;">{$ctv_name}</strong></p>
            </div>
            <div style="background-color: #ffffff; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0;">
                <p style="margin: 0 0 16px; font-size: 1rem; color: #1e293b;">Xin chào <strong>{$ctv_name}</strong>,</p>
                <p style="margin-bottom: 20px; font-size: 0.95rem; color: #334155;">Hệ thống thông báo: Danh sách <strong>{$count}</strong> vi phạm bạn báo cáo cho <strong>{$week_name}</strong> đã bị Ban thi đua <strong style="color: #dc2626;">từ chối duyệt</strong>.</p>
                <div style="overflow-x:auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem; text-align: left;">
                        <thead>
                            <tr style="background-color: #fef2f2; color: #991b1b;">
                                <th style="padding: 10px; border-bottom: 2px solid #fecaca; text-align: center;">STT</th>
                                <th style="padding: 10px; border-bottom: 2px solid #fecaca;">Học sinh</th>
                                <th style="padding: 10px; border-bottom: 2px solid #fecaca;">Lỗi vi phạm</th>
                                <th style="padding: 10px; border-bottom: 2px solid #fecaca;">Ngày VP</th>
                            </tr>
                        </thead>
                        <tbody>
                            {$table_rows}
                        </tbody>
                    </table>
                </div>
                <p style="margin-top: 20px; font-size: 0.9rem; color: #334155;">Vui lòng kiểm tra lại tính chính xác của các báo cáo này.</p>
            </div>
            <p style="text-align: center; font-size: 0.85rem; color: #94a3b8; margin-top: 24px;">Trân trọng,<br><span style="font-weight: 600; color: #224397;">Ban thi đua Trường THPT Bình Sơn</span><br><span style="font-size: 0.75rem;">Email ID: {$email_id}</span></p>
        </div>
    </body>
    </html>
HTML;
    return ['subject' => $subject, 'body' => $body];
}

/**
 * Lấy URL ảnh thẻ học sinh (hỗ trợ Local, OneDrive, R2)
 *
 * @param string|null $anh_the Tên file ảnh (local) hoặc URL đầy đủ
 * @param string|null $driver  'local', 'onedrive', 'r2', 'cloud'
 * @param string|null $cloud_key Object ID/Key trên Cloud
 * @return string Đường dẫn hợp lệ để hiển thị trong thẻ <img>
 */
function get_student_avatar_url($anh_the, $driver = 'local', $cloud_key = null) {
    if (empty($anh_the) && empty($cloud_key)) {
        return '/thidua/public/assets/img/anhthegoc.JPG';
    }

    // 1. Nếu lưu trên Cloudflare R2 -> Lấy trực tiếp link Presigned URL từ R2
    if (($driver === 'r2' || $driver === 'cloud') && !empty($cloud_key)) {
        require_once __DIR__ . '/StorageService.php';
        try {
            $storage = new StorageService();
            return $storage->getTemporaryUrl($cloud_key, '+60 minutes');
        } catch (Exception $e) {
            error_log("Lỗi tạo R2 URL cho ảnh thẻ: " . $e->getMessage());
        }
    }

    // 2. Nếu lưu trên OneDrive -> Đi qua endpoint proxy/cache của server
    if ($driver === 'onedrive' && !empty($cloud_key)) {
        return '/thidua/src/controllers/api_get_presigned_url.php?driver=onedrive&key=' . urlencode($cloud_key) . '&inline=1&v=2';
    }

    // Mặc định là local
    if (!empty($anh_the)) {
        if (preg_match('/^https?:\/\//', $anh_the)) {
            return $anh_the;
        }
        if (strpos($anh_the, 'public/assets/') !== false) {
            return '/thidua/' . ltrim($anh_the, '/');
        }
        return '/thidua/public/assets/anh_the/' . ltrim($anh_the, '/');
    }
    
    return '/thidua/public/assets/img/anhthegoc.JPG';
}

/**
 * Gửi Push Notification thông qua Expo
 *
 * @param string $expo_push_token
 * @param string $title
 * @param string $body
 * @param array $data_payload
 * @return array
 */
function send_expo_push_notification($expo_push_token, $title, $body, $data_payload = []) {
    if (empty($expo_push_token) || !str_starts_with($expo_push_token, 'ExponentPushToken') && !str_starts_with($expo_push_token, 'ExpoPushToken')) {
        return ['success' => false, 'message' => 'Invalid Expo push token'];
    }

    $ch = curl_init('https://exp.host/--/api/v2/push/send');
    $payload = json_encode([
        'to' => $expo_push_token,
        'title' => $title,
        'body' => $body,
        'sound' => 'default',
        'data' => $data_payload,
        '_displayInForeground' => true
    ]);

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Accept-encoding: gzip, deflate',
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'success' => $http_code == 200,
        'response' => $result
    ];
}

/**
 * Tạo thông báo mới cho học sinh và gửi Push Notification (nếu có token)
 *
 * @param PDO $db
 * @param int|string $hoc_sinh_id
 * @param string $tieu_de
 * @param string $noi_dung
 * @param string $loai_thong_bao
 * @return bool
 */
function create_student_notification($db, $hoc_sinh_id, $tieu_de, $noi_dung, $loai_thong_bao) {
    try {
        // 1. Lưu vào Database (Chuông thông báo In-App)
        $stmt_tb = $db->prepare("INSERT INTO thong_bao_hoc_sinh (hoc_sinh_id, tieu_de, noi_dung, loai_thong_bao, thoi_gian, da_xem) VALUES (?, ?, ?, ?, NOW(), 0)");
        $stmt_tb->execute([$hoc_sinh_id, $tieu_de, $noi_dung, $loai_thong_bao]);

        // 2. Lấy thông tin học sinh (Token push, Zalo Chat ID, Email)
        $stmt_info = $db->prepare("SELECT expo_push_token, email, zalo_chat_id, zalo_id, ho_dem, ten, ma_hoc_sinh FROM ho_so_hoc_sinh WHERE id = ?");
        $stmt_info->execute([$hoc_sinh_id]);
        $hs = $stmt_info->fetch(PDO::FETCH_ASSOC);

        if ($hs) {
            // Gửi Push nếu có token
            if (!empty($hs['expo_push_token'])) {
                send_expo_push_notification($hs['expo_push_token'], $tieu_de, $noi_dung, ['type' => $loai_thong_bao]);
            }

            // Gửi Bot Zalo nếu có liên kết
            $zaloTarget = $hs['zalo_chat_id'] ?: $hs['zalo_id'];
            if (!empty($zaloTarget)) {
                $botMsg = "THÔNG BÁO: {$tieu_de}\n\n"
                        . "Học sinh: {$hs['ho_dem']} {$hs['ten']} ({$hs['ma_hoc_sinh']})\n"
                        . "Nội dung: {$noi_dung}\n\n"
                        . "Thời gian: " . date('H:i d/m/Y');
                
                send_zalo_bot_direct_message($zaloTarget, $botMsg);
            }
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Lỗi gửi thông báo cho học sinh $hoc_sinh_id: " . $e->getMessage());
        return false;
    }
}

/**
 * Gửi tin nhắn trực tiếp qua Zalo Bot Platform API
 */
function send_zalo_bot_direct_message($chat_id, $message) {
    if (empty($chat_id) || empty($message)) return false;
    $bot_token = $_ENV['ZALO_BOT_TOKEN'] ?? '528220222251220927:cfSCnPkmesSRlprCpQgdphHYlzbKjojSajCzxdKXaMESSDMexlvHSRCGvUQllPyx';
    if (empty($bot_token)) return false;
    try {
        $url = "https://bot-api.zaloplatforms.com/bot{$bot_token}/sendMessage";
        $payload = [
            'chat_id' => (string)$chat_id,
            'text' => $message
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $resp = curl_exec($ch);
        curl_close($ch);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Gửi thông báo đa kênh cho Giáo viên / GVCN:
 * 1. Lưu chuông thông báo In-App (bảng thong_bao)
 * 2. Gửi Zalo Bot (nếu có liên kết Zalo)
 * 3. Gửi Email (nếu có email)
 */
function create_teacher_notification($db, $teacher_id_or_email_or_cccd, $tieu_de, $noi_dung, $loai_thong_bao = 'he_thong', $link_url = '') {
    try {
        if (!$db) $db = get_db_connection();
        $stmt = $db->prepare("
            SELECT gv.id, gv.ho_ten, gv.cccd, gv.email, gv.sdt, gv.zalo_id, gv.user_id, u.id as u_id, u.zalo_id as u_zalo_id, u.email as u_email 
            FROM giao_vien gv 
            LEFT JOIN users u ON gv.user_id = u.id OR gv.cccd = u.ten_dang_nhap 
            WHERE gv.id = ? OR gv.email = ? OR gv.cccd = ? OR u.id = ? 
            LIMIT 1
        ");
        $stmt->execute([$teacher_id_or_email_or_cccd, $teacher_id_or_email_or_cccd, $teacher_id_or_email_or_cccd, $teacher_id_or_email_or_cccd]);
        $gv = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$gv) return false;

        $target_user_id = $gv['u_id'] ?? $gv['user_id'];
        $target_email = $gv['email'] ?: $gv['u_email'];
        $target_zalo = $gv['zalo_id'] ?: $gv['u_zalo_id'];
        $teacher_name = $gv['ho_ten'] ?? 'Thầy/Cô';

        // 1. Chuông In-App
        try {
            $stmt_tb = $db->prepare("INSERT INTO thong_bao (nguoi_nhan_id, tieu_de, noi_dung, loai, da_xem, thoi_gian) VALUES (?, ?, ?, ?, 0, NOW())");
            $stmt_tb->execute([$target_user_id, $tieu_de, $noi_dung, $loai_thong_bao]);
        } catch (Exception $e) {}

        // 2. Gửi Zalo Bot
        if (!empty($target_zalo)) {
            $botMsg = "THÔNG BÁO: {$tieu_de}\n\n"
                    . "Kính gửi Thầy/Cô: {$teacher_name}\n"
                    . "Nội dung: {$noi_dung}\n"
                    . (!empty($link_url) ? "Chi tiết: {$link_url}\n" : "")
                    . "\nThời gian: " . date('H:i d/m/Y');
            send_zalo_bot_direct_message($target_zalo, $botMsg);
        }

        // 3. Gửi Email (nếu có)
        if (!empty($target_email) && filter_var($target_email, FILTER_VALIDATE_EMAIL)) {
            $email_body = "
                <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px;'>
                    <div style='text-align: center; margin-bottom: 20px;'>
                        <h2 style='color: #224397; margin: 0;'>TRƯỜNG THPT BÌNH SƠN</h2>
                        <p style='color: #64748b; font-size: 13px; margin: 4px 0;'>Hệ thống thông báo học tập & thi đua</p>
                    </div>
                    <div style='background-color: #f8fafc; padding: 15px; border-radius: 8px; border-left: 4px solid #224397; margin-bottom: 20px;'>
                        <h3 style='margin: 0 0 10px 0; color: #1e293b;'>{$tieu_de}</h3>
                        <p style='margin: 0; font-size: 14px;'><strong>Kính gửi:</strong> Thầy/Cô {$teacher_name}</p>
                        <p style='margin: 8px 0 0 0; font-size: 14px;'>{$noi_dung}</p>
                    </div>
                    " . (!empty($link_url) ? "<div style='text-align: center; margin: 25px 0;'><a href='{$link_url}' style='background-color: #224397; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px;'>Xem Chi Tiết</a></div>" : "") . "
                    <hr style='border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;'>
                    <p style='font-size: 12px; color: #94a3b8; text-align: center; margin: 0;'>Email này được gửi tự động từ hệ thống quản lý thi đua THPT Bình Sơn.</p>
                </div>
            ";
            queue_email($target_email, $teacher_name, "[THPT Bình Sơn] " . $tieu_de, $email_body, $noi_dung, 15);
        }

        return true;
    } catch (Exception $e) {
        return false;
    }
}
