<?php
// File: src/controllers/api_admin_khao_sat.php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập.']);
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');
$input = json_decode(file_get_contents('php://input'), true) ?: [];
if (!$action && isset($input['action'])) {
    $action = $input['action'];
}

try {
    $db = get_db_connection();

    // Tự động tạo cột style nếu chưa có
    try {
        $db->exec("ALTER TABLE khao_sat ADD COLUMN style TEXT NULL");
    } catch (Exception $e) {}

    if ($action === 'create') {
        $title = $input['tieu_de'] ?? '';
        $desc = $input['mo_ta'] ?? '';
        $type = $input['loai_khao_sat'] ?? 'tu_nguyen';
        $due_date = $input['han_nop'] ?? '';
        $banner_url = $input['banner_url'] ?? '';
        $style = isset($input['style']) ? json_encode($input['style'], JSON_UNESCAPED_UNICODE) : null;
        $questions = $input['questions'] ?? [];

        if (empty($title) || empty($questions)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền tiêu đề và thêm ít nhất 1 câu hỏi.']);
            exit();
        }

        $db->beginTransaction();

        $stmt_s = $db->prepare("INSERT INTO khao_sat (tieu_de, mo_ta, loai_khao_sat, han_nop, banner_url, style, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
        $stmt_s->execute([$title, $desc, $type, $due_date, $banner_url, $style]);
        $survey_id = $db->lastInsertId();

        $stmt_q = $db->prepare("INSERT INTO khao_sat_cau_hoi (khao_sat_id, tieu_de, mo_ta, loai_cau_hoi, bat_buoc, tuy_chon, thu_tu) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($questions as $idx => $q) {
            $q_title = $q['tieu_de'] ?? '';
            $q_desc = $q['mo_ta'] ?? '';
            $q_type = $q['loai_cau_hoi'] ?? 'short_text';
            $q_req = isset($q['bat_buoc']) && $q['bat_buoc'] ? 1 : 0;
            $q_opt = isset($q['tuy_chon']) ? json_encode($q['tuy_chon'], JSON_UNESCAPED_UNICODE) : json_encode([]);

            $stmt_q->execute([$survey_id, $q_title, $q_desc, $q_type, $q_req, $q_opt, $idx + 1]);
        }

        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Tạo bài khảo sát thành công!']);
        exit();
    }

    if ($action === 'update') {
        $survey_id = isset($input['survey_id']) ? (int)$input['survey_id'] : 0;
        $title = $input['tieu_de'] ?? '';
        $desc = $input['mo_ta'] ?? '';
        $type = $input['loai_khao_sat'] ?? 'tu_nguyen';
        $due_date = $input['han_nop'] ?? '';
        $banner_url = $input['banner_url'] ?? '';
        $style = isset($input['style']) ? json_encode($input['style'], JSON_UNESCAPED_UNICODE) : null;
        $questions = $input['questions'] ?? [];

        if (!$survey_id || empty($title) || empty($questions)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
            exit();
        }

        $db->beginTransaction();

        $stmt_s = $db->prepare("UPDATE khao_sat SET tieu_de = ?, mo_ta = ?, loai_khao_sat = ?, han_nop = ?, banner_url = ?, style = ? WHERE id = ?");
        $stmt_s->execute([$title, $desc, $type, $due_date, $banner_url, $style, $survey_id]);

        // Lấy danh sách ID câu hỏi hiện có
        $stmt_existing = $db->query("SELECT id FROM khao_sat_cau_hoi WHERE khao_sat_id = $survey_id");
        $existing_ids = $stmt_existing->fetchAll(PDO::FETCH_COLUMN);
        $keep_ids = [];

        $stmt_ins = $db->prepare("INSERT INTO khao_sat_cau_hoi (khao_sat_id, tieu_de, mo_ta, loai_cau_hoi, bat_buoc, tuy_chon, thu_tu) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_upd = $db->prepare("UPDATE khao_sat_cau_hoi SET tieu_de = ?, mo_ta = ?, loai_cau_hoi = ?, bat_buoc = ?, tuy_chon = ?, thu_tu = ? WHERE id = ?");

        foreach ($questions as $idx => $q) {
            $q_id = isset($q['id']) ? (int)$q['id'] : 0;
            $q_title = $q['tieu_de'] ?? '';
            $q_desc = $q['mo_ta'] ?? '';
            $q_type = $q['loai_cau_hoi'] ?? 'short_text';
            $q_req = isset($q['bat_buoc']) && $q['bat_buoc'] ? 1 : 0;
            $q_opt = isset($q['tuy_chon']) ? (is_string($q['tuy_chon']) ? $q['tuy_chon'] : json_encode($q['tuy_chon'], JSON_UNESCAPED_UNICODE)) : json_encode([]);

            if ($q_id > 0 && in_array($q_id, $existing_ids)) {
                $stmt_upd->execute([$q_title, $q_desc, $q_type, $q_req, $q_opt, $idx + 1, $q_id]);
                $keep_ids[] = $q_id;
            } else {
                $stmt_ins->execute([$survey_id, $q_title, $q_desc, $q_type, $q_req, $q_opt, $idx + 1]);
                $keep_ids[] = $db->lastInsertId();
            }
        }

        // Xóa các câu hỏi không còn tồn tại
        $delete_ids = array_diff($existing_ids, $keep_ids);
        if (!empty($delete_ids)) {
            $ids_str = implode(',', $delete_ids);
            $db->exec("DELETE FROM khao_sat_cau_hoi WHERE id IN ($ids_str)");
        }

        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Cập nhật bài khảo sát thành công!']);
        exit();
    }

    if ($action === 'get_detail') {
        $survey_id = isset($_GET['survey_id']) ? (int)$_GET['survey_id'] : 0;
        if (!$survey_id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID không hợp lệ.']);
            exit();
        }

        $stmt_s = $db->prepare("SELECT * FROM khao_sat WHERE id = ?");
        $stmt_s->execute([$survey_id]);
        $survey = $stmt_s->fetch(PDO::FETCH_ASSOC);

        if (!$survey) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy khảo sát.']);
            exit();
        }

        $stmt_q = $db->prepare("SELECT * FROM khao_sat_cau_hoi WHERE khao_sat_id = ? ORDER BY thu_tu ASC");
        $stmt_q->execute([$survey_id]);
        $questions = $stmt_q->fetchAll(PDO::FETCH_ASSOC);

        $formatted_questions = [];
        foreach ($questions as $q) {
            $formatted_questions[] = [
                'id' => $q['id'],
                'tieu_de' => $q['tieu_de'],
                'mo_ta' => $q['mo_ta'],
                'loai_cau_hoi' => $q['loai_cau_hoi'],
                'bat_buoc' => (bool)$q['bat_buoc'],
                'tuy_chon' => json_decode($q['tuy_chon'] ?: '{}', true),
                'thu_tu' => (int)$q['thu_tu']
            ];
        }

        echo json_encode([
            'success' => true,
            'survey' => [
                'id' => $survey['id'],
                'tieu_de' => $survey['tieu_de'],
                'mo_ta' => $survey['mo_ta'],
                'loai_khao_sat' => $survey['loai_khao_sat'],
                'han_nop' => $survey['han_nop'],
                'banner_url' => $survey['banner_url'] ?? '',
                'style' => isset($survey['style']) ? json_decode($survey['style'], true) : null
            ],
            'questions' => $formatted_questions
        ]);
        exit();
    }

    if ($action === 'delete') {
        $survey_id = isset($input['survey_id']) ? (int)$input['survey_id'] : 0;
        if (!$survey_id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID không hợp lệ.']);
            exit();
        }

        $db->beginTransaction();
        $db->exec("DELETE FROM khao_sat_ket_qua WHERE bai_lam_id IN (SELECT id FROM khao_sat_bai_lam WHERE khao_sat_id = $survey_id)");
        $db->exec("DELETE FROM khao_sat_bai_lam WHERE khao_sat_id = $survey_id");
        $db->exec("DELETE FROM khao_sat_cau_hoi WHERE khao_sat_id = $survey_id");
        $db->exec("DELETE FROM khao_sat WHERE id = $survey_id");
        $db->commit();

        echo json_encode(['success' => true, 'message' => 'Đã xóa bài khảo sát.']);
        exit();
    }

    if ($action === 'report') {
        $survey_id = isset($_GET['survey_id']) ? (int)$_GET['survey_id'] : 0;
        $lop_hoc_id = isset($_GET['lop_hoc_id']) ? (int)$_GET['lop_hoc_id'] : 0;
        $khoi = isset($_GET['khoi']) ? $_GET['khoi'] : '';

        if (!$survey_id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID khảo sát không hợp lệ.']);
            exit();
        }

        // Lấy danh sách học sinh của lớp hoặc toàn bộ
        $sql_hs = "SELECT hs.id, hs.ma_hoc_sinh, hs.ho_dem, hs.ten, lh.ten_lop 
                   FROM ho_so_hoc_sinh hs 
                   JOIN quatrinh_hoc_tap qt ON hs.ma_hoc_sinh = qt.ma_hoc_sinh 
                   JOIN raw_lop_hoc lh ON qt.lop_hoc_id = lh.id 
                   WHERE qt.nam_hoc_id = (SELECT MAX(nam_hoc_id) FROM quatrinh_hoc_tap WHERE ma_hoc_sinh = hs.ma_hoc_sinh) ";
        $params_hs = [];
        if ($khoi) {
            $sql_hs .= " AND lh.ten_lop LIKE ? ";
            $params_hs[] = $khoi . '%';
        }
        if ($lop_hoc_id) {
            $sql_hs .= " AND qt.lop_hoc_id = ? ";
            $params_hs[] = $lop_hoc_id;
        }
        $sql_hs .= " ORDER BY lh.ten_lop ASC, hs.ten ASC";

        $stmt_hs = $db->prepare($sql_hs);
        $stmt_hs->execute($params_hs);
        $students = $stmt_hs->fetchAll(PDO::FETCH_ASSOC);

        // Lấy bài làm
        $stmt_sub = $db->prepare("SELECT id, hoc_sinh_id, ngay_nop FROM khao_sat_bai_lam WHERE khao_sat_id = ?");
        $stmt_sub->execute([$survey_id]);
        $submissions = [];
        $submission_map = [];
        while ($row = $stmt_sub->fetch(PDO::FETCH_ASSOC)) {
            $submissions[$row['hoc_sinh_id']] = $row;
            $submission_map[$row['id']] = $row['hoc_sinh_id'];
        }

        // Lấy kết quả để thống kê biểu đồ
        $stmt_q = $db->prepare("SELECT * FROM khao_sat_cau_hoi WHERE khao_sat_id = ? ORDER BY thu_tu ASC");
        $stmt_q->execute([$survey_id]);
        $questions = $stmt_q->fetchAll(PDO::FETCH_ASSOC);

        $stmt_ans = $db->prepare("SELECT kq.bai_lam_id, kq.cau_hoi_id, kq.gia_tri 
                                  FROM khao_sat_ket_qua kq 
                                  JOIN khao_sat_bai_lam bl ON kq.bai_lam_id = bl.id 
                                  WHERE bl.khao_sat_id = ?");
        $stmt_ans->execute([$survey_id]);
        $all_answers = $stmt_ans->fetchAll(PDO::FETCH_ASSOC);

        // Chuẩn bị dữ liệu học sinh (đã làm / chưa làm) và chi tiết bài làm
        $da_lam = [];
        $chua_lam = [];

        // Tổ chức map câu trả lời theo học sinh
        $student_answers = [];
        foreach ($all_answers as $ans) {
            $hs_id = $submission_map[$ans['bai_lam_id']] ?? 0;
            if ($hs_id) {
                $parsed = json_decode($ans['gia_tri'], true);
                $student_answers[$hs_id][$ans['cau_hoi_id']] = (json_last_error() === JSON_ERROR_NONE) ? $parsed : $ans['gia_tri'];
            }
        }

        foreach ($students as $hs) {
            $item = [
                'id' => $hs['id'],
                'ma_hoc_sinh' => $hs['ma_hoc_sinh'],
                'ho_ten' => trim(($hs['ho_dem'] ?? '') . ' ' . ($hs['ten'] ?? '')),
                'ten_lop' => $hs['ten_lop']
            ];
            if (isset($submissions[$hs['id']])) {
                $item['ngay_nop'] = date('d/m/Y H:i', strtotime($submissions[$hs['id']]['ngay_nop']));
                $item['answers'] = $student_answers[$hs['id']] ?? [];
                $da_lam[] = $item;
            } else {
                $chua_lam[] = $item;
            }
        }

        // Tạo dữ liệu biểu đồ cho các câu hỏi trắc nghiệm / lựa chọn
        $charts = [];
        foreach ($questions as $q) {
            $q_id = $q['id'];
            $q_type = $q['loai_cau_hoi'];
            if ($q_type === 'section_header') continue;

            $chart_data = ['title' => $q['tieu_de'], 'type' => $q_type, 'series' => [], 'labels' => [], 'responses' => []];

            if (in_array($q_type, ['radio', 'checkbox', 'dropdown'])) {
                $opts = json_decode($q['tuy_chon'], true);
                $counts = [];
                if (!empty($opts['options'])) {
                    foreach ($opts['options'] as $opt) {
                        $counts[$opt] = 0;
                    }
                }

                foreach ($all_answers as $ans) {
                    if ($ans['cau_hoi_id'] == $q_id) {
                        $val = json_decode($ans['gia_tri'], true);
                        if (is_array($val)) {
                            foreach ($val as $v) {
                                if (!isset($counts[$v])) $counts[$v] = 0;
                                $counts[$v]++;
                            }
                        } else {
                            $v = $ans['gia_tri'];
                            if ($v !== '') {
                                if (!isset($counts[$v])) $counts[$v] = 0;
                                $counts[$v]++;
                            }
                        }
                    }
                }

                foreach ($counts as $label => $count) {
                    $chart_data['labels'][] = $label;
                    $chart_data['series'][] = $count;
                }
            } elseif (in_array($q_type, ['linear_scale', 'star_rating'])) {
                $counts = [];
                foreach ($all_answers as $ans) {
                    if ($ans['cau_hoi_id'] == $q_id) {
                        $v = (int)$ans['gia_tri'];
                        if ($v > 0) {
                            if (!isset($counts[$v])) $counts[$v] = 0;
                            $counts[$v]++;
                        }
                    }
                }
                ksort($counts);
                foreach ($counts as $label => $count) {
                    $chart_data['labels'][] = $label . ($q_type === 'star_rating' ? ' Sao' : ' Điểm');
                    $chart_data['series'][] = $count;
                }
            } else {
                // Các câu hỏi text, file, date, time
                foreach ($all_answers as $ans) {
                    if ($ans['cau_hoi_id'] == $q_id && !empty($ans['gia_tri'])) {
                        $hs_id = $submission_map[$ans['bai_lam_id']] ?? 0;
                        $hs_name = 'Học sinh';
                        $hs_class = '';
                        foreach ($students as $s) {
                            if ($s['id'] == $hs_id) {
                                $hs_name = trim(($s['ho_dem'] ?? '') . ' ' . ($s['ten'] ?? ''));
                                $hs_class = $s['ten_lop'];
                                break;
                            }
                        }
                        $chart_data['responses'][] = [
                            'ho_ten' => $hs_name . ($hs_class ? " ($hs_class)" : ''),
                            'gia_tri' => $ans['gia_tri']
                        ];
                    }
                }
            }

            $charts[] = $chart_data;
        }

        echo json_encode([
            'success' => true,
            'da_lam' => $da_lam,
            'chua_lam' => $chua_lam,
            'charts' => $charts,
            'questions' => $questions
        ]);
        exit();
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ.']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
