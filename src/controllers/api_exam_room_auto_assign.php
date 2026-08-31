<?php
// File: src/controllers/api_exam_room_auto_assign.php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function send_json_response($data, $status_code = 200) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    http_response_code($status_code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

require_once __DIR__ . '/../lib/exam_permissions.php';
require_once __DIR__ . '/../lib/exam_subjects.php';
require_once __DIR__ . '/../lib/exam_shift_manager.php';
require_once __DIR__ . '/../lib/exam_room_assignment.php';

// Bảo mật
if (!isset($_SESSION['user_id']) || !can_current_user_manage_exams()) {
    send_json_response(['success' => false, 'message' => 'Lỗi xác thực.'], 403);
}

require_once __DIR__ . '/../../config/database.php';
$db = get_db_connection();
ensure_exam_subject_registration_schema($db);
ensure_exam_shift_schema($db);
ensure_exam_room_assignment_schema($db);

$data = json_decode(file_get_contents('php://input'), true) ?? [];
$ky_thi_id = (int)($data['ky_thi_id'] ?? 0);
$ca_thi_id = !empty($data['ca_thi_id']) ? (int)$data['ca_thi_id'] : null; // Nếu null thì xếp tất cả ca thi
$max_students_per_room = max(1, min(100, (int)($data['max_students_per_room'] ?? 24)));
$max_subjects_per_room = max(1, min(10, (int)($data['max_subjects_per_room'] ?? 2)));
$min_students_per_room = max(1, min(100, (int)($data['min_students_per_room'] ?? 12)));

if (!$ky_thi_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Thiếu ID Kỳ thi.']);
    exit();
}

try {
    $db->beginTransaction();

    // 1. Lấy thông tin ca thi cần xếp
    $all_shifts = get_exam_shifts($db, $ky_thi_id);
    if (empty($all_shifts)) {
        create_default_exam_shifts($db, $ky_thi_id);
        $all_shifts = get_exam_shifts($db, $ky_thi_id);
    }

    $shifts_to_process = [];
    if ($ca_thi_id !== null && $ca_thi_id > 0) {
        foreach ($all_shifts as $s) {
            if ((int)$s['id'] === $ca_thi_id) {
                $shifts_to_process[] = $s;
            }
        }
        if (empty($shifts_to_process)) {
            throw new Exception('Không tìm thấy ca thi đã chọn.');
        }
    } else {
        $shifts_to_process = $all_shifts;
    }

    // 2. Lấy toàn bộ danh sách thí sinh trong kỳ thi
    $stmt_hs = $db->prepare("
        SELECT 
            kths.id as kths_id,
            kths.so_bao_danh,
            kths.dang_ky_mon_thi,
            hs.ma_hoc_sinh,
            hs.ho_dem,
            hs.ten,
            lh.ten_lop
        FROM ky_thi_hoc_sinh kths
        JOIN hoc_sinh hs ON kths.hoc_sinh_id = hs.id
        JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id
        WHERE kths.ky_thi_id = ?
        ORDER BY lh.ten_lop ASC, hs.ten ASC, hs.ho_dem ASC
    ");
    $stmt_hs->execute([$ky_thi_id]);
    $all_students = $stmt_hs->fetchAll(PDO::FETCH_ASSOC);

    if (empty($all_students)) {
        throw new Exception('Chưa có thí sinh nào trong kỳ thi này. Vui lòng thêm học sinh trước khi xếp phòng.');
    }

    // Chuẩn hóa môn thi đăng ký của từng học sinh
    foreach ($all_students as &$hs) {
        $hs['registered_subjects'] = exam_decode_subject_registration($hs['dang_ky_mon_thi'] ?? '');
    }
    unset($hs);

    $total_rooms_created = 0;
    $total_assignments = 0;
    $summary_details = [];

    // 3. Tiến hành xếp phòng thi cho từng ca thi
    foreach ($shifts_to_process as $shift) {
        $current_shift_id = (int)$shift['id'];
        $shift_name = $shift['ten_ca'];
        $allowed_subjects = $shift['mon_hoc_list'] ?? [];
        $max_slots = max(1, (int)($shift['so_luot_thi'] ?? 1));

        // Xóa kết quả phân phòng cũ của ca này
        exam_room_assignment_clear($db, $ky_thi_id, $current_shift_id);

        if (empty($allowed_subjects)) {
            continue;
        }

        // Lọc danh sách học sinh tham gia ca thi này kèm danh sách môn thi của ca
        $shift_students = [];
        foreach ($all_students as $hs) {
            $student_shift_subjects = array_values(array_intersect($hs['registered_subjects'], $allowed_subjects));
            if (!empty($student_shift_subjects)) {
                $hs_copy = $hs;
                $hs_copy['shift_subjects'] = $student_shift_subjects;
                // Chuẩn hóa key tổ hợp môn
                sort($student_shift_subjects);
                $hs_copy['to_hop_key'] = implode(',', $student_shift_subjects);
                $shift_students[] = $hs_copy;
            }
        }

        if (empty($shift_students)) {
            continue;
        }

        // =========================================================================
        // THUẬT TOÁN XẾP PHÒNG THI TỐI ƯU ĐA LƯỢT (OPTIMIZATION ENGINE)
        // =========================================================================
        $allocated_rooms = [];

        // TRƯỜNG HỢP A: Ca thi chỉ có 1 môn (ví dụ Toán hoặc Ngữ văn) hoặc số lượt thi = 1
        if (count($allowed_subjects) === 1 || $max_slots === 1) {
            $chunks = array_chunk($shift_students, $max_students_per_room);
            foreach ($chunks as $chunk) {
                $room_slots = [1 => $allowed_subjects[0] ?? $chunk[0]['shift_subjects'][0]];
                $room_students_assigned = [];

                foreach ($chunk as $idx => $st) {
                    $subject = $st['shift_subjects'][0] ?? $allowed_subjects[0];
                    $room_students_assigned[] = [
                        'kths_id' => $st['kths_id'],
                        'mon_thi' => $subject,
                        'luot_thi' => 1,
                        'seat_no' => $idx + 1
                    ];
                }

                $allocated_rooms[] = [
                    'slots' => $room_slots,
                    'students' => $room_students_assigned
                ];
            }
        } else {
            // TRƯỜNG HỢP B: Ca thi nhiều môn tự chọn và nhiều lượt thi (ví dụ 2 lượt thi)
            // 1. Phân nhóm học sinh theo tổ hợp môn thi
            $grouped_by_tohop = [];
            foreach ($shift_students as $st) {
                $key = $st['to_hop_key'];
                $grouped_by_tohop[$key][] = $st;
            }

            // Sắp xếp các tổ hợp theo số lượng giảm dần
            uasort($grouped_by_tohop, function ($a, $b) {
                return count($b) - count($a);
            });

            $remaining_groups = [];

            // 2. Tách các tổ hợp lớn (>= max_students_per_room) thành các phòng riêng đủ sĩ số
            foreach ($grouped_by_tohop as $tohop_str => $students_in_group) {
                $count = count($students_in_group);
                $tohop_subs = explode(',', $tohop_str);

                // Số phòng tròn đủ sĩ số
                $full_rooms_count = intdiv($count, $max_students_per_room);
                $offset = 0;

                for ($r = 0; $r < $full_rooms_count; $r++) {
                    $chunk = array_slice($students_in_group, $offset, $max_students_per_room);
                    $offset += $max_students_per_room;

                    // Gán lượt thi cho từng môn của tổ hợp
                    $room_slots = [];
                    foreach ($tohop_subs as $s_idx => $sub_code) {
                        $slot_num = min($max_slots, $s_idx + 1);
                        $room_slots[$slot_num] = $sub_code;
                    }

                    $room_students_assigned = [];
                    foreach ($chunk as $seat_idx => $st) {
                        foreach ($st['shift_subjects'] as $s_idx => $sub_code) {
                            $slot_num = min($max_slots, $s_idx + 1);
                            $room_students_assigned[] = [
                                'kths_id' => $st['kths_id'],
                                'mon_thi' => $sub_code,
                                'luot_thi' => $slot_num,
                                'seat_no' => $seat_idx + 1
                            ];
                        }
                    }

                    $allocated_rooms[] = [
                        'slots' => $room_slots,
                        'students' => $room_students_assigned
                    ];
                }

                // Phần học sinh dư ra sẽ cho vào hàng đợi ghép tổ hợp nhỏ
                $remainder = array_slice($students_in_group, $offset);
                if (!empty($remainder)) {
                    $remaining_groups[$tohop_str] = $remainder;
                }
            }

            // 3. Ghép các tổ hợp nhỏ còn lại vào các phòng đa môn (Greedy Bin Packing with Conflict Check)
            // Biến đổi các nhóm còn lại thành danh sách các túi học sinh
            $unassigned_students = [];
            foreach ($remaining_groups as $tohop_str => $st_list) {
                foreach ($st_list as $st) {
                    $unassigned_students[] = $st;
                }
            }

            // Tiến hành đóng gói học sinh vào phòng sao cho mỗi phòng không vượt quá max_students_per_room
            // và phân bổ slot môn thi không bị va chạm lượt thi
            while (!empty($unassigned_students)) {
                $current_room_students = [];
                $current_room_subjects = [];
                $unassigned_temp = [];

                foreach ($unassigned_students as $st) {
                    // Kiểm tra nếu thêm học sinh này vào phòng hiện tại có hợp lệ không
                    if (count($current_room_students) >= $max_students_per_room) {
                        $unassigned_temp[] = $st;
                        continue;
                    }

                    $merged_subjects = array_unique(array_merge($current_room_subjects, $st['shift_subjects']));
                    if (count($merged_subjects) > $max_subjects_per_room && !empty($current_room_students)) {
                        $unassigned_temp[] = $st;
                        continue;
                    }

                    // Thêm vào phòng
                    $current_room_students[] = $st;
                    $current_room_subjects = $merged_subjects;
                }

                // Xây dựng sơ đồ phân chia lượt thi (Slot Assignment) cho phòng này
                // Mỗi học sinh trong phòng có 1 hoặc 2 môn. Cần gán slot 1..max_slots sao cho không môn nào của cùng 1 HS bị trùng slot.
                $room_slots_assignment = solve_room_slot_assignment($current_room_students, $max_slots);

                $room_students_assigned = [];
                foreach ($current_room_students as $seat_idx => $st) {
                    foreach ($st['shift_subjects'] as $sub_code) {
                        $assigned_slot = $room_slots_assignment[$st['kths_id']][$sub_code] ?? 1;
                        $room_students_assigned[] = [
                            'kths_id' => $st['kths_id'],
                            'mon_thi' => $sub_code,
                            'luot_thi' => $assigned_slot,
                            'seat_no' => $seat_idx + 1
                        ];
                    }
                }

                $allocated_rooms[] = [
                    'slots' => $room_slots_assignment['room_slot_labels'] ?? [],
                    'students' => $room_students_assigned
                ];

                if (count($unassigned_temp) === count($unassigned_students)) {
                    // Tránh vòng lặp vô tận: lấy học sinh đầu tiên tạo phòng mới
                    $first_st = array_shift($unassigned_temp);
                    $room_slots_assignment = solve_room_slot_assignment([$first_st], $max_slots);
                    $room_students_assigned = [];
                    foreach ($first_st['shift_subjects'] as $s_idx => $sub_code) {
                        $room_students_assigned[] = [
                            'kths_id' => $first_st['kths_id'],
                            'mon_thi' => $sub_code,
                            'luot_thi' => min($max_slots, $s_idx + 1),
                            'seat_no' => 1
                        ];
                    }
                    $allocated_rooms[] = [
                        'slots' => $room_slots_assignment['room_slot_labels'] ?? [],
                        'students' => $room_students_assigned
                    ];
                }

                $unassigned_students = $unassigned_temp;
            }
        }

        // =========================================================================
        // 4. LƯU KẾT QUẢ PHÂN PHÒNG VÀO DATABASE
        // =========================================================================
        // Đảm bảo đủ số lượng phòng thi trong bảng ky_thi_phong_thi
        $stmt_existing_rooms = $db->prepare("SELECT id, ten_phong FROM ky_thi_phong_thi WHERE ky_thi_id = ? ORDER BY id ASC");
        $stmt_existing_rooms->execute([$ky_thi_id]);
        $existing_rooms = $stmt_existing_rooms->fetchAll(PDO::FETCH_ASSOC);

        $needed_rooms_count = count($allocated_rooms);
        while (count($existing_rooms) < $needed_rooms_count) {
            $new_index = count($existing_rooms) + 1;
            $new_room_name = 'Phòng ' . str_pad((string)$new_index, 2, '0', STR_PAD_LEFT);
            $stmt_ins_room = $db->prepare("INSERT INTO ky_thi_phong_thi (ky_thi_id, ten_phong, si_so_toi_da) VALUES (?, ?, ?)");
            $stmt_ins_room->execute([$ky_thi_id, $new_room_name, $max_students_per_room]);
            $existing_rooms[] = [
                'id' => $db->lastInsertId(),
                'ten_phong' => $new_room_name
            ];
        }

        $stmt_insert_assign = $db->prepare("
            INSERT INTO ky_thi_xep_phong (ky_thi_id, ca_thi_id, ca_thi, phong_thi_id, ky_thi_hoc_sinh_id, mon_thi, luot_thi, seat_no)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $shift_assigned_count = 0;
        foreach ($allocated_rooms as $r_idx => $room_data) {
            $target_room_id = (int)$existing_rooms[$r_idx]['id'];
            foreach ($room_data['students'] as $assign_item) {
                $stmt_insert_assign->execute([
                    $ky_thi_id,
                    $current_shift_id,
                    $shift['thu_tu'],
                    $target_room_id,
                    $assign_item['kths_id'],
                    $assign_item['mon_thi'],
                    $assign_item['luot_thi'],
                    $assign_item['seat_no']
                ]);
                $shift_assigned_count++;
            }
        }

        $summary_details[] = "{$shift_name}: Đã xếp thành công " . count($allocated_rooms) . " phòng thi cho " . count($shift_students) . " thí sinh.";
        $total_rooms_created = max($total_rooms_created, count($allocated_rooms));
        $total_assignments += $shift_assigned_count;
    }

    if ($db->inTransaction()) {
        $db->commit();
    }

    send_json_response([
        'success' => true,
        'message' => 'Xếp phòng thi tự động hoàn tất!',
        'total_rooms' => $total_rooms_created,
        'total_assignments' => $total_assignments,
        'details' => $summary_details
    ]);

} catch (\Throwable $e) {
    if ($db && $db->inTransaction()) {
        $db->rollBack();
    }
    send_json_response(['success' => false, 'message' => $e->getMessage()], 400);
}

/**
 * Thuật toán phân chia lượt thi (Slot Assignment Solver) cho một phòng thi
 * Đảm bảo 1 học sinh thi 2 môn trong cùng 1 ca sẽ được xếp vào 2 lượt (slot) khác nhau
 */
function solve_room_slot_assignment(array $students, int $max_slots): array
{
    $assignment = [];
    $slot_subject_map = [];

    // Đếm tần suất các môn trong phòng này
    $subject_freq = [];
    foreach ($students as $st) {
        foreach ($st['shift_subjects'] as $sub) {
            $subject_freq[$sub] = ($subject_freq[$sub] ?? 0) + 1;
        }
    }
    arsort($subject_freq);
    $distinct_subjects = array_keys($subject_freq);

    // Gán mặc định các môn phổ biến nhất vào các slot 1, 2...
    foreach ($distinct_subjects as $s_idx => $sub) {
        $default_slot = min($max_slots, ($s_idx % $max_slots) + 1);
        $slot_subject_map[$default_slot][] = $sub;
    }

    // Gán cụ thể cho từng học sinh và tránh xung đột
    foreach ($students as $st) {
        $st_subs = $st['shift_subjects'];
        $used_slots = [];

        foreach ($st_subs as $s_idx => $sub) {
            // Tìm slot phù hợp chưa bị học sinh này dùng
            $chosen_slot = null;
            for ($slot = 1; $slot <= $max_slots; $slot++) {
                if (!isset($used_slots[$slot])) {
                    $chosen_slot = $slot;
                    break;
                }
            }
            if ($chosen_slot === null) {
                $chosen_slot = 1;
            }

            $used_slots[$chosen_slot] = true;
            $assignment[$st['kths_id']][$sub] = $chosen_slot;
        }
    }

    $assignment['room_slot_labels'] = $slot_subject_map;
    return $assignment;
}
