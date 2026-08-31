<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('GET, OPTIONS');
zalo_handle_options();

// File: src/controllers/api_zalo_get_survey_detail.php
$payload = zalo_authenticate_request();
$student_id = $payload['student_id'];
$survey_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$survey_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID khảo sát không hợp lệ.']);
    exit();
}

try {
    $db = get_db_connection();

    // Lấy thông tin bài khảo sát
    $stmt_s = $db->prepare("SELECT * FROM khao_sat WHERE id = ?");
    $stmt_s->execute([$survey_id]);
    $survey = $stmt_s->fetch(PDO::FETCH_ASSOC);

    if (!$survey) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy bài khảo sát.']);
        exit();
    }

    // Lấy danh sách câu hỏi
    $stmt_q = $db->prepare("SELECT * FROM khao_sat_cau_hoi WHERE khao_sat_id = ? ORDER BY thu_tu ASC");
    $stmt_q->execute([$survey_id]);
    $questions = $stmt_q->fetchAll(PDO::FETCH_ASSOC);

    $formatted_questions = [];
    foreach ($questions as $q) {
        $formatted_questions[] = [
            'id' => (string)$q['id'],
            'title' => $q['tieu_de'],
            'description' => $q['mo_ta'],
            'type' => $q['loai_cau_hoi'],
            'required' => (bool)$q['bat_buoc'],
            'options' => json_decode($q['tuy_chon'] ?: '{}', true),
            'order' => (int)$q['thu_tu']
        ];
    }

    // Kiểm tra bài làm của học sinh
    $stmt_sub = $db->prepare("SELECT id, ngay_nop FROM khao_sat_bai_lam WHERE khao_sat_id = ? AND hoc_sinh_id = ? LIMIT 1");
    $stmt_sub->execute([$survey_id, $student_id]);
    $submission = $stmt_sub->fetch(PDO::FETCH_ASSOC);

    $answers = [];
    if ($submission) {
        $stmt_ans = $db->prepare("SELECT cau_hoi_id, gia_tri FROM khao_sat_ket_qua WHERE bai_lam_id = ?");
        $stmt_ans->execute([$submission['id']]);
        while ($row = $stmt_ans->fetch(PDO::FETCH_ASSOC)) {
            $parsed = json_decode($row['gia_tri'], true);
            $answers[(string)$row['cau_hoi_id']] = (json_last_error() === JSON_ERROR_NONE) ? $parsed : $row['gia_tri'];
        }
    }

    // Kiểm tra hết hạn nộp
    $is_expired = false;
    if (!empty($survey['han_nop'])) {
        $expire_time = strtotime(str_replace('/', '-', $survey['han_nop']));
        if ($expire_time !== false && time() > $expire_time) {
            $is_expired = true;
        }
    }

    echo json_encode([
        'success' => true,
        'survey' => [
            'id' => (string)$survey['id'],
            'title' => $survey['tieu_de'],
            'description' => $survey['mo_ta'],
            'badge' => $survey['loai_khao_sat'] === 'bat_buoc' ? 'Bắt buộc' : 'Tự nguyện',
            'badgeType' => $survey['loai_khao_sat'] === 'bat_buoc' ? 'required' : 'optional',
            'dueDate' => !empty($survey['han_nop']) ? date('H:i - d/m/Y', strtotime(str_replace('/', '-', $survey['han_nop']))) : 'Không giới hạn',
            'isExpired' => $is_expired,
            'banner_url' => $survey['banner_url'] ?? '',
            'style' => isset($survey['style']) ? json_decode($survey['style'], true) : null,
            'completed' => $submission ? true : false,
            'submittedAt' => $submission ? date('d/m/Y H:i', strtotime($submission['ngay_nop'])) : null
        ],
        'questions' => $formatted_questions,
        'answers' => $answers
    ]);

} catch (Exception $e) {
    http_response_code(500);
    zalo_api_error('Lỗi hệ thống, vui lòng thử lại sau.', 500, $e);
}
