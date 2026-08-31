<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('GET, OPTIONS');
zalo_handle_options();

// File: src/controllers/api_zalo_hoat_dong.php
$payload = zalo_authenticate_request();
$student_id = $payload['student_id'];
$nam_hoc_id = zalo_get_nam_hoc_id();

try {
    $db = get_db_connection();
    
    // Lấy thông tin lớp và chức vụ của học sinh
    $stmtHs = $db->prepare("SELECT hs.chuc_vu, l.ten_lop FROM hoc_sinh hs LEFT JOIN lop_hoc l ON hs.lop_hoc_id = l.id WHERE hs.id = ?");
    $stmtHs->execute([$student_id]);
    $hsInfo = $stmtHs->fetch();
    $ten_lop = $hsInfo['ten_lop'] ?? '';
    $chuc_vu = $hsInfo['chuc_vu'] ?? '';

    // Lấy danh sách hoạt động đang hiển thị trên app
    $sql = "
        SELECT hd.*, 
               (SELECT COUNT(*) FROM hoat_dong_dang_ky dk WHERE dk.hoat_dong_id = hd.id) as dang_ky_count,
               (SELECT dk2.trang_thai_diem_danh FROM hoat_dong_dang_ky dk2 WHERE dk2.hoat_dong_id = hd.id AND dk2.hoc_sinh_id = ?) as user_status,
               (SELECT dk3.diem_thuc_te FROM hoat_dong_dang_ky dk3 WHERE dk3.hoat_dong_id = hd.id AND dk3.hoc_sinh_id = ?) as diem_thuc_te
        FROM hoat_dong hd
        WHERE hd.trang_thai = 1 
    ";
    
    $params = [$student_id, $student_id];
    if ($nam_hoc_id) {
        $sql .= " AND hd.nam_hoc_id = ? ";
        $params[] = $nam_hoc_id;
    }
    
    $sql .= " ORDER BY hd.id DESC ";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $filtered_activities = [];
    foreach ($activities as $hd) {
        // LUÔN CHO PHÉP NẾU HỌC SINH ĐÃ ĐƯỢC THÊM VÀO (TỨC LÀ ĐÃ THAM GIA)
        if ($hd['user_status'] !== null) {
            $filtered_activities[] = $hd;
            continue;
        }
        
        // NẾU CHƯA THAM GIA VÀ BỊ ẨN TRÊN APP THÌ BỎ QUA
        if ($hd['show_tren_app'] == 0) {
            continue;
        }

        $doi_tuong = $hd['doi_tuong'] ?? 'Tất cả';
        $allow = false;
        
        if ($doi_tuong === 'Tất cả') {
            $allow = true;
        } else {
            $arr = array_map('trim', explode(',', $doi_tuong));
            if (in_array('Tất cả', $arr)) {
                $allow = true;
            } else {
                foreach ($arr as $t) {
                    if ($t === $ten_lop || $t === $chuc_vu) {
                        $allow = true;
                        break;
                    }
                    if (strpos($t, 'Khối') !== false) {
                        $khoi = trim(str_replace('Khối', '', $t));
                        if (strpos($ten_lop, $khoi) === 0) {
                            $allow = true;
                            break;
                        }
                    }
                }
            }
        }
        
        if ($allow) {
            $filtered_activities[] = $hd;
        }
    }

    echo json_encode(['success' => true, 'data' => $filtered_activities]);
} catch (Exception $e) {
    http_response_code(500);
    zalo_api_error('Lỗi hệ thống, vui lòng thử lại sau.', 500, $e);
}
