<?php
// File: src/lib/zalo_notification_helper.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/zalo_helpers.php';

/**
 * Gửi thông báo đa kênh đồng thời cho học sinh:
 * 1. Lưu thông báo chuông App (Zalo Mini App / Web)
 * 2. Gửi Email (nếu học sinh có email)
 * 3. Gửi tin nhắn qua Bot Zalo (nếu học sinh đã liên kết CCCD với Bot Zalo)
 */
function send_multichannel_student_notification($db, $student_id_or_code, $title, $content, $type = 'he_thong', $link_url = '') {
    try {
        if (!$db) {
            $db = get_db_connection();
        }

        // 1. Lấy thông tin học sinh
        $stmt = $db->prepare("
            SELECT id, ma_hoc_sinh, ho_dem, ten, email, sdt, zalo_chat_id, zalo_id 
            FROM ho_so_hoc_sinh 
            WHERE id = ? OR ma_hoc_sinh = ? 
            LIMIT 1
        ");
        $stmt->execute([$student_id_or_code, $student_id_or_code]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) return false;

        $fullName = trim("{$student['ho_dem']} {$student['ten']}");

        // ==========================================
        // KÊNH 1: LƯU THÔNG BÁO CHUÔNG TRÊN APP (BELL)
        // ==========================================
        try {
            // Kiểm tra bảng thong_bao_hoc_sinh
            $stmt_bell = $db->prepare("
                INSERT INTO thong_bao_hoc_sinh (hoc_sinh_id, tieu_de, noi_dung, loai_thong_bao, link_dieu_huong, da_doc, created_at)
                VALUES (?, ?, ?, ?, ?, 0, NOW())
            ");
            $stmt_bell->execute([$student['id'], $title, $content, $type, $link_url]);
        } catch (Exception $e) {
            // Nếu cấu trúc bảng là thong_bao
            try {
                $stmt_bell2 = $db->prepare("
                    INSERT INTO thong_bao (nguoi_nhan_id, tieu_de, noi_dung, loai, da_xem, created_at)
                    VALUES (?, ?, ?, ?, 0, NOW())
                ");
                $stmt_bell2->execute([$student['id'], $title, $content, $type]);
            } catch (Exception $e2) {}
        }

        // ==========================================
        // KÊNH 2: GỬI EMAIL (NẾU CÓ EMAIL)
        // ==========================================
        if (!empty($student['email'])) {
            try {
                $mail_data = [
                    'api_key' => $_ENV['API_KEY'] ?? 'thptbinhson_secret_key',
                    'to' => $student['email'],
                    'subject' => "[THPT Bình Sơn] " . $title,
                    'html' => "
                        <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px;'>
                            <div style='text-align: center; margin-bottom: 20px;'>
                                <h2 style='color: #224397; margin: 0;'>TRƯỜNG THPT BÌNH SƠN</h2>
                                <p style='color: #64748b; font-size: 13px; margin: 4px 0;'>Hệ thống thông báo học tập & thi đua</p>
                            </div>
                            <div style='background-color: #f8fafc; padding: 15px; border-radius: 8px; border-left: 4px solid #224397; margin-bottom: 20px;'>
                                <h3 style='margin: 0 0 10px 0; color: #1e293b;'>{$title}</h3>
                                <p style='margin: 0; font-size: 14px;'><strong>Học sinh:</strong> {$fullName} ({$student['ma_hoc_sinh']})</p>
                                <p style='margin: 8px 0 0 0; font-size: 14px;'>{$content}</p>
                            </div>
                            " . (!empty($link_url) ? "<div style='text-align: center; margin: 25px 0;'><a href='{$link_url}' style='background-color: #224397; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px;'>Xem Chi Tiết</a></div>" : "") . "
                            <hr style='border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;'>
                            <p style='font-size: 12px; color: #94a3b8; text-align: center; margin: 0;'>Email này được gửi tự động từ hệ thống quản lý học sinh THPT Bình Sơn.</p>
                        </div>
                    "
                ];

                // Gửi qua service nodejs botzalo port 3000
                $ch = curl_init("http://127.0.0.1:3000/apimaill");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($mail_data));
                curl_setopt($ch, CURLOPT_TIMEOUT, 2);
                curl_exec($ch);
                curl_close($ch);
            } catch (Exception $e) {}
        }

        // ==========================================
        // KÊNH 3: GỬI QUA BOT ZALO (NẾU ĐÃ LIÊN KẾT)
        // ==========================================
        $zaloTargetId = $student['zalo_chat_id'] ?: $student['zalo_id'];
        if (!empty($zaloTargetId)) {
            $bot_token = $_ENV['ZALO_BOT_TOKEN'] ?? '528220222251220927:cfSCnPkmesSRlprCpQgdphHYlzbKjojSajCzxdKXaMESSDMexlvHSRCGvUQllPyx';
            
            $botMessage = "THÔNG BÁO: {$title}\n\n"
                        . "Học sinh: {$fullName} ({$student['ma_hoc_sinh']})\n"
                        . "Nội dung: {$content}\n"
                        . (!empty($link_url) ? "Chi tiết: {$link_url}\n" : "")
                        . "\nThời gian: " . date('H:i d/m/Y');

            if (!empty($bot_token)) {
                try {
                    $url = "https://bot-api.zaloplatforms.com/bot{$bot_token}/sendMessage";
                    $payload = [
                        'chat_id' => (string)$zaloTargetId,
                        'text' => $botMessage
                    ];
                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                    $resp = curl_exec($ch);
                    curl_close($ch);
                } catch (Exception $e) {}
            }
        }

        return true;

    } catch (Exception $e) {
        error_log("Error in send_multichannel_student_notification: " . $e->getMessage());
        return false;
    }
}
