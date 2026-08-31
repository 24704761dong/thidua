<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('POST, OPTIONS');
zalo_handle_options();

// File: src/controllers/api_zalo_submit_survey.php
$payload = zalo_authenticate_request();
$student_id = $payload['student_id'];
$input = json_decode(file_get_contents('php://input'), true);
$survey_id = isset($input['survey_id']) ? (int)$input['survey_id'] : 0;
$answers = isset($input['answers']) ? $input['answers'] : [];

if (!$survey_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dữ liệu nộp không hợp lệ (Thiếu ID khảo sát).']);
    exit();
}

try {
    $db = get_db_connection();

    // Tự động tạo bảng nếu chưa có và điều chỉnh kiểu dữ liệu hoc_sinh_id thành VARCHAR(100)
    $db->exec("
        CREATE TABLE IF NOT EXISTS khao_sat_bai_lam (
            id INT AUTO_INCREMENT PRIMARY KEY,
            khao_sat_id INT NOT NULL,
            hoc_sinh_id VARCHAR(100) NOT NULL,
            nam_hoc_id INT,
            ngay_nop DATETIME DEFAULT CURRENT_TIMESTAMP,
            status VARCHAR(50) DEFAULT 'completed'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    $db->exec("
        CREATE TABLE IF NOT EXISTS khao_sat_ket_qua (
            id INT AUTO_INCREMENT PRIMARY KEY,
            bai_lam_id INT NOT NULL,
            cau_hoi_id INT NOT NULL,
            gia_tri TEXT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    try {
        $db->exec("ALTER TABLE khao_sat_bai_lam MODIFY COLUMN hoc_sinh_id VARCHAR(100) NOT NULL");
    } catch (Exception $e) {}

    // Lấy thông tin khảo sát
    $stmt_s = $db->prepare("SELECT tieu_de, han_nop, status FROM khao_sat WHERE id = ?");
    $stmt_s->execute([$survey_id]);
    $survey = $stmt_s->fetch(PDO::FETCH_ASSOC);

    if (!$survey || $survey['status'] !== 'active') {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy khảo sát hoặc bài khảo sát đã bị đóng.']);
        exit();
    }
    
    // Kiểm tra hết hạn nộp
    if (!empty($survey['han_nop'])) {
        $expire_time = strtotime(str_replace('/', '-', $survey['han_nop']));
        if ($expire_time !== false && time() > $expire_time) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Rất tiếc, bài khảo sát này đã hết hạn nộp!']);
            exit();
        }
    }
    
    $survey_title = $survey['tieu_de'];

    // Lấy thông tin học sinh
    $stmt_hs = $db->prepare("SELECT hs.ho_dem, hs.ten, lh.ten_lop 
                             FROM ho_so_hoc_sinh hs 
                             LEFT JOIN quatrinh_hoc_tap qt ON hs.ma_hoc_sinh = qt.ma_hoc_sinh 
                             LEFT JOIN raw_lop_hoc lh ON qt.lop_hoc_id = lh.id 
                             WHERE hs.id = ? 
                             ORDER BY qt.nam_hoc_id DESC LIMIT 1");
    $stmt_hs->execute([$student_id]);
    $hs = $stmt_hs->fetch(PDO::FETCH_ASSOC);
    $hs_name = $hs ? trim(($hs['ho_dem'] ?? '') . ' ' . ($hs['ten'] ?? '')) : 'Học sinh';
    $hs_class = $hs ? ($hs['ten_lop'] ?? 'Chưa rõ') : 'Chưa rõ';

    // Kiểm tra đã có bài làm chưa, nếu có thì xóa kết quả cũ
    $stmt_check = $db->prepare("SELECT id FROM khao_sat_bai_lam WHERE khao_sat_id = ? AND hoc_sinh_id = ? LIMIT 1");
    $stmt_check->execute([$survey_id, $student_id]);
    $bai_lam_id = $stmt_check->fetchColumn();

    if ($bai_lam_id) {
        $stmt_del = $db->prepare("DELETE FROM khao_sat_ket_qua WHERE bai_lam_id = ?");
        $stmt_del->execute([$bai_lam_id]);
        $stmt_up = $db->prepare("UPDATE khao_sat_bai_lam SET ngay_nop = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt_up->execute([$bai_lam_id]);
    } else {
        $stmt_ins = $db->prepare("INSERT INTO khao_sat_bai_lam (khao_sat_id, hoc_sinh_id) VALUES (?, ?)");
        $stmt_ins->execute([$survey_id, $student_id]);
        $bai_lam_id = $db->lastInsertId();
    }

    // Lưu từng kết quả câu hỏi
    if (!empty($answers) && is_array($answers)) {
        $stmt_ans = $db->prepare("INSERT INTO khao_sat_ket_qua (bai_lam_id, cau_hoi_id, gia_tri) VALUES (?, ?, ?)");
        foreach ($answers as $q_id => $val) {
            try {
                $val_str = is_array($val) ? json_encode($val, JSON_UNESCAPED_UNICODE) : (string)$val;
                $stmt_ans->execute([$bai_lam_id, (int)$q_id, $val_str]);
            } catch (Exception $e) {
                error_log("Lỗi lưu kết quả câu hỏi {$q_id}: " . $e->getMessage());
            }
        }
    }

    // Gửi thông báo cho học sinh và Admin (Bọc trong try-catch riêng để đảm bảo nộp bài luôn thành công)
    try {
        $notif_title_hs = "Đã nộp bài khảo sát thành công";
        $notif_content_hs = "Hệ thống đã ghi nhận câu trả lời của bạn cho bài khảo sát: " . $survey_title;
        create_student_notification($db, $student_id, $notif_title_hs, $notif_content_hs, 'khao_sat');
    } catch (Exception $ex) {
        error_log("Gửi thông báo hs thất bại: " . $ex->getMessage());
    }

    try {
        // 4. Tạo thông báo (notification) cho admin
        $noi_dung_tb = "Học sinh <b>{$hs_name}</b> (Lớp {$hs_class}) vừa hoàn thành bài khảo sát: {$survey_title}.";
        $stmt_notif_admin = $db->prepare("INSERT INTO thong_bao (loai_thong_bao, id_lien_quan, noi_dung, thoi_gian) VALUES ('khao_sat', ?, ?, CURRENT_TIMESTAMP)");
        $stmt_notif_admin->execute([$survey_id, $noi_dung_tb]);
    } catch (Exception $ex) {
        error_log("Gửi thông báo admin thất bại: " . $ex->getMessage());
    }

    echo json_encode(['success' => true, 'message' => 'Nộp bài khảo sát thành công!']);

} catch (Exception $e) {
    http_response_code(500);
    zalo_api_error('Lỗi hệ thống, vui lòng thử lại sau.', 500, $e);
}
