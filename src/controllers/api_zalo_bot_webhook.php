<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../lib/zalo_helpers.php';

$rawInput = file_get_contents('php://input');

// Xử lý ping kiểm tra / verification từ Zalo
if ($_SERVER['REQUEST_METHOD'] === 'GET' || empty($rawInput) || $rawInput === '{}') {
    echo json_encode(['ok' => true, 'status' => 'active', 'message' => 'Zalo Bot Webhook Active']);
    exit;
}

$data = json_decode($rawInput, true);
$db = get_db_connection();

// Hỗ trợ cả Zalo OA webhook chuẩn hoặc API gửi trực tiếp
$event_name = $data['event_name'] ?? '';
$action = $data['action'] ?? '';

try {
    // 1. Gỡ liên kết Zalo Bot của học sinh từ trang Quản trị Admin
    if ($action === 'unlink_zalo_bot') {
        $student_id = $data['student_id'] ?? 0;
        $ma_hoc_sinh = $data['ma_hoc_sinh'] ?? '';

        if (empty($student_id) && empty($ma_hoc_sinh)) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin học sinh cần gỡ liên kết.']);
            exit;
        }

        $stmt = $db->prepare("UPDATE ho_so_hoc_sinh SET zalo_chat_id = NULL, zalo_id = NULL WHERE id = ? OR ma_hoc_sinh = ?");
        $stmt->execute([$student_id, $ma_hoc_sinh]);

        echo json_encode([
            'success' => true,
            'message' => 'Đã xóa liên kết Zalo với học sinh thành công.'
        ]);
        exit;

    // 2. Nhận tin nhắn chat từ Webhook Zalo (hoặc Zalo Bot)
    } elseif ($event_name === 'user_send_text' || $action === 'register_by_cccd' || isset($data['message']) || isset($data['text']) || isset($data['chat_id'])) {
        $sender_id = $data['message']['chat']['id'] ?? $data['sender']['id'] ?? $data['zalo_chat_id'] ?? $data['user_id'] ?? $data['chat_id'] ?? '';
        $text = trim($data['message']['text'] ?? $data['text'] ?? $data['cccd'] ?? '');

        if (empty($sender_id)) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin người gửi (zalo_chat_id).']);
            exit;
        }

        // Làm sạch chuỗi số CCCD (loại bỏ ký tự thừa)
        $clean_cccd = preg_replace('/[^0-9a-zA-Z_-]/', '', $text);

        if (empty($clean_cccd)) {
            $reply = "Xin chào! Vui lòng nhập chính xác số CCCD hoặc Mã định danh học sinh của bạn để đăng ký nhận thông báo tự động từ Trường THPT Bình Sơn.";
            send_zalo_bot_message($sender_id, $reply);
            echo json_encode([
                'success' => true,
                'reply_message' => $reply
            ]);
            exit;
        }

        // Tra cứu học sinh theo CCCD / Mã học sinh (kèm quá trình học tập mới nhất)
        $stmt = $db->prepare("
            SELECT 
                h.*, 
                l.ten_lop, 
                nh.ten_nam_hoc 
            FROM ho_so_hoc_sinh h
            LEFT JOIN (
                SELECT qt1.* 
                FROM quatrinh_hoc_tap qt1
                JOIN (
                    SELECT ma_hoc_sinh, MAX(nam_hoc_id) as max_nam_hoc_id
                    FROM quatrinh_hoc_tap
                    GROUP BY ma_hoc_sinh
                ) qt2 ON qt1.ma_hoc_sinh = qt2.ma_hoc_sinh AND qt1.nam_hoc_id = qt2.max_nam_hoc_id
            ) q ON h.ma_hoc_sinh = q.ma_hoc_sinh
            LEFT JOIN lop_hoc l ON q.lop_hoc_id = l.id
            LEFT JOIN nam_hoc nh ON q.nam_hoc_id = nh.id
            WHERE h.ma_hoc_sinh = ? OR h.sdt = ?
            LIMIT 1
        ");
        $stmt->execute([$clean_cccd, $clean_cccd]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            $reply = "Không tìm thấy học sinh có số CCCD/Mã định danh: [{$clean_cccd}] trên hệ thống Trường THPT Bình Sơn.\n\nVui lòng kiểm tra lại hoặc liên hệ giáo viên chủ nhiệm/nhà trường để cập nhật hồ sơ.";
            send_zalo_bot_message($sender_id, $reply);
            echo json_encode([
                'success' => false,
                'reply_message' => $reply
            ]);
            exit;
        }

        // 1. Kiểm tra xem số CCCD này đã liên kết với Zalo ID khác chưa
        if (!empty($student['zalo_chat_id']) && $student['zalo_chat_id'] !== $sender_id) {
            $reply = "Số CCCD [{$clean_cccd}] đã được liên kết với một tài khoản Zalo khác.\n\nMỗi học sinh chỉ được liên kết 1 tài khoản Zalo duy nhất. Vui lòng liên hệ Quản trị viên/Giáo viên nhà trường để hủy liên kết cũ trước khi đăng ký tài khoản Zalo mới.";
            send_zalo_bot_message($sender_id, $reply);
            echo json_encode([
                'success' => false,
                'reply_message' => $reply
            ]);
            exit;
        }

        // 2. Kiểm tra xem tài khoản Zalo này đã liên kết với học sinh KHÁC chưa
        $stmt_check_sender = $db->prepare("SELECT id, ho_dem, ten, ma_hoc_sinh FROM ho_so_hoc_sinh WHERE zalo_chat_id = ? AND id != ? LIMIT 1");
        $stmt_check_sender->execute([$sender_id, $student['id']]);
        $existing_sender_student = $stmt_check_sender->fetch(PDO::FETCH_ASSOC);

        if ($existing_sender_student) {
            $oldName = trim("{$existing_sender_student['ho_dem']} {$existing_sender_student['ten']}");
            $reply = "Tài khoản Zalo này hiện đang liên kết với học sinh: {$oldName} (CCCD: {$existing_sender_student['ma_hoc_sinh']}).\n\nMỗi tài khoản Zalo chỉ được liên kết với 1 học sinh. Vui lòng liên hệ Quản trị viên nhà trường để gỡ liên kết cũ trước khi liên kết học sinh mới.";
            send_zalo_bot_message($sender_id, $reply);
            echo json_encode([
                'success' => false,
                'reply_message' => $reply
            ]);
            exit;
        }

        $fullName = trim("{$student['ho_dem']} {$student['ten']}");
        if ($student['trang_thai_hoc_tap'] === 'da_tot_nghiep') {
            $className = 'Đã tốt nghiệp';
        } elseif ($student['trang_thai_hoc_tap'] === 'da_nghi_hoc') {
            $className = 'Đã nghỉ học';
        } else {
            $className = $student['ten_lop'] ?? $student['lop'] ?? 'Chưa xếp lớp';
        }
        $nienKhoa = $student['nien_khoa'] ?? 'Chưa cập nhật';
        $namHoc = $student['ten_nam_hoc'] ?? '2026 - 2027';

        // Lưu cứng vĩnh viễn zalo_chat_id cho học sinh
        $stmt_update = $db->prepare("UPDATE ho_so_hoc_sinh SET zalo_chat_id = ? WHERE id = ?");
        $stmt_update->execute([$sender_id, $student['id']]);

        $reply = "XÁC THỰC THÀNH CÔNG!\n\n"
               . "Học sinh: {$fullName}\n"
               . "Lớp: {$className}\n"
               . "Niên khóa: {$nienKhoa}\n"
               . "Số CCCD/Mã HS: {$student['ma_hoc_sinh']}\n\n"
               . "Tài khoản Zalo này đã được liên kết với hệ thống THPT Bình Sơn. Bạn sẽ nhận được các thông báo học tập, thi đua, kết quả điểm thi, lịch thi và khảo sát trực tiếp tại khung chat này!";

        // Gửi tin nhắn phản hồi trực tiếp tới Zalo của học sinh
        send_zalo_bot_message($sender_id, $reply);

        echo json_encode([
            'success' => true,
            'message' => 'Đã liên kết Zalo Bot thành công.',
            'student' => [
                'id' => $student['id'],
                'ma_hoc_sinh' => $student['ma_hoc_sinh'],
                'ho_ten' => $fullName,
                'lop' => $className,
                'zalo_chat_id' => $sender_id
            ],
            'reply_message' => $reply
        ]);
        exit;

    } elseif ($action === 'send_bot_message') {
        // API Gửi tin nhắn thông báo tự động từ backend
        $student_id = $data['student_id'] ?? null;
        $ma_hoc_sinh = $data['ma_hoc_sinh'] ?? null;
        $title = $data['title'] ?? 'Thông báo từ THPT Bình Sơn';
        $content = $data['content'] ?? '';
        $url = $data['url'] ?? '';

        $stmt = $db->prepare("SELECT id, ho_dem, ten, ma_hoc_sinh, zalo_chat_id, email FROM ho_so_hoc_sinh WHERE id = ? OR ma_hoc_sinh = ? LIMIT 1");
        $stmt->execute([$student_id, $ma_hoc_sinh]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy học sinh.']);
            exit;
        }

        if (empty($student['zalo_chat_id'])) {
            echo json_encode(['success' => false, 'message' => 'Học sinh chưa liên kết Bot Zalo (chưa có zalo_chat_id).']);
            exit;
        }

        // Format tin nhắn gửi qua Zalo (không dùng icon)
        $formatted_message = "THÔNG BÁO: {$title}\n\n"
                           . "Học sinh: {$student['ho_dem']} {$student['ten']} ({$student['ma_hoc_sinh']})\n"
                           . "Nội dung: {$content}\n"
                           . (!empty($url) ? "Chi tiết: {$url}\n" : "")
                           . "\nThời gian: " . date('H:i d/m/Y');

        // Gửi qua hàm zalo_helpers hoặc Zalo Bot Dispatcher
        $send_result = send_zalo_bot_message($student['zalo_chat_id'], $formatted_message);

        echo json_encode([
            'success' => true,
            'zalo_sent' => $send_result,
            'message' => 'Đã gửi thông báo qua Bot Zalo thành công.'
        ]);
        exit;

    } else {
        echo json_encode(['success' => false, 'message' => 'Action không hợp lệ.']);
        exit;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
}

/**
 * Hàm gửi tin nhắn qua Bot Zalo / Zalo OA
 */
function send_zalo_bot_message($chat_id, $message_text) {
    $bot_token = $_ENV['ZALO_BOT_TOKEN'] ?? '528220222251220927:cfSCnPkmesSRlprCpQgdphHYlzbKjojSajCzxdKXaMESSDMexlvHSRCGvUQllPyx';

    // 1. Gửi qua Zalo Bot Platform API chính thức (https://bot-api.zaloplatforms.com)
    if (!empty($bot_token)) {
        try {
            $url = "https://bot-api.zaloplatforms.com/bot{$bot_token}/sendMessage";
            $payload = [
                'chat_id' => (string)$chat_id,
                'text' => $message_text
            ];
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $resp = curl_exec($ch);
            curl_close($ch);
            
            if ($resp) {
                $resData = json_decode($resp, true);
                if (!empty($resData['ok'])) return true;
            }
        } catch (Exception $e) {}
    }

    // 2. Thử gửi qua microservice Node.js botzalo nếu có
    try {
        $node_url = "http://127.0.0.1:3000/send-bot";
        $ch = curl_init($node_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'chat_id' => $chat_id,
            'message' => $message_text
        ]));
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        $resp = curl_exec($ch);
        curl_close($ch);
    } catch (Exception $e) {}

    return true;
}
