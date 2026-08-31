<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('GET, OPTIONS');
zalo_handle_options();

// File: src/controllers/api_zalo_get_profile.php
// API lấy thông tin học sinh sau khi đã đăng nhập Zalo Mini App

// Lấy Header Authorization
$payload = zalo_authenticate_request();
$student_id = $payload['student_id'];
try {
    $db = get_db_connection();

    $nam_hoc_header = zalo_get_nam_hoc_id();

    $sql = "SELECT hs.id, hs.ma_hoc_sinh, hs.ho_dem, hs.ten, hs.ngay_sinh, hs.gioi_tinh, hs.trang_thai_tai_khoan, qt.chuc_vu, hs.anh_the, hs.anh_the_driver, hs.anh_the_cloud_key, hs.nien_khoa, hs.trang_thai_hoc_tap, hs.sdt, hs.email, hs.tinh_thanhpho, hs.xa_phuong, hs.ap_khupho, hs.dia_chi_chi_tiet, lh.ten_lop, gv.ho_ten as gvcn, nh.ten_nam_hoc, nh.id as nam_hoc_id, hs.quyen_truy_cap 
            FROM ho_so_hoc_sinh hs 
            JOIN quatrinh_hoc_tap qt ON hs.ma_hoc_sinh = qt.ma_hoc_sinh ";
    
    if ($nam_hoc_header) {
        $sql .= " AND qt.nam_hoc_id = ? ";
        $params = [$nam_hoc_header, $student_id];
    } else {
        $sql .= " AND qt.nam_hoc_id = (SELECT MAX(nam_hoc_id) FROM quatrinh_hoc_tap WHERE ma_hoc_sinh = hs.ma_hoc_sinh) ";
        $params = [$student_id];
    }

    $sql .= " LEFT JOIN raw_lop_hoc lh ON qt.lop_hoc_id = lh.id 
              LEFT JOIN giao_vien gv ON lh.giao_vien_id = gv.id
              LEFT JOIN nam_hoc nh ON qt.nam_hoc_id = nh.id
              WHERE hs.id = ?";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin học sinh ở năm học này.']);
        exit();
    }

    // Xử lý link avatar ảnh thẻ (luôn dùng HTTPS cho Zalo Mini App)
    require_once __DIR__ . '/../lib/helpers.php';
    $raw_avatar_url = get_student_avatar_url($user['anh_the'] ?? '', $user['anh_the_driver'] ?? 'local', $user['anh_the_cloud_key'] ?? null);
    if (!empty($raw_avatar_url)) {
        if (strpos($raw_avatar_url, 'http://') === 0) {
            $raw_avatar_url = 'https://' . substr($raw_avatar_url, 7);
        } elseif (strpos($raw_avatar_url, 'https://') !== 0) {
            $host = $_SERVER['HTTP_HOST'] ?? 'c3binhson.edu.vn';
            $raw_avatar_url = 'https://' . $host . $raw_avatar_url;
        }
    }
    $user['avatar_url'] = $raw_avatar_url;
    $user['anh_the_url'] = $raw_avatar_url;

    $stmt_max_year = $db->prepare("SELECT MAX(nam_hoc_id) FROM quatrinh_hoc_tap WHERE ma_hoc_sinh = ?");
    $stmt_max_year->execute([$user['ma_hoc_sinh']]);
    $max_year_id = $stmt_max_year->fetchColumn();
    
    // Fix: get_current_nam_hoc_id_mysql() trả về session nam hoc hien tai cua user request chứ ko phải global
    $global_active_year = $db->query("SELECT MAX(id) FROM nam_hoc WHERE trang_thai = 'active'")->fetchColumn();
    $user['is_latest_year'] = ($user['nam_hoc_id'] == $max_year_id && $user['nam_hoc_id'] == $global_active_year);
    $user['quyen_truy_cap'] = json_decode($user['quyen_truy_cap'] ?: '{}', true);
    $user['must_change_password'] = ($user['trang_thai_tai_khoan'] !== 'Đã đổi MK');

    $user['trang_thai_hien_thi'] = null;
    $stmt_next = $db->prepare("
        SELECT lh.ten_lop 
        FROM quatrinh_hoc_tap qt
        JOIN raw_lop_hoc lh ON qt.lop_hoc_id = lh.id
        JOIN nam_hoc nh ON qt.nam_hoc_id = nh.id
        WHERE qt.ma_hoc_sinh = ? AND nh.id > ?
        ORDER BY nh.id ASC LIMIT 1
    ");
    $stmt_next->execute([$user['ma_hoc_sinh'], $user['nam_hoc_id']]);
    $next_class = $stmt_next->fetchColumn();

    if ($next_class && $user['ten_lop']) {
        preg_match('/^(\d+)/', $user['ten_lop'], $old_match);
        preg_match('/^(\d+)/', $next_class, $new_match);
        $old_grade = isset($old_match[1]) ? (int)$old_match[1] : 0;
        $new_grade = isset($new_match[1]) ? (int)$new_match[1] : 0;
        
        if ($old_grade > 0 && $new_grade > 0) {
            if ($new_grade > $old_grade) {
                $user['trang_thai_hien_thi'] = "Được lên lớp " . $new_grade;
            } else if ($new_grade == $old_grade) {
                $user['trang_thai_hien_thi'] = "Học lại khối " . $old_grade;
            } else {
                $user['trang_thai_hien_thi'] = "Xuống khối " . $new_grade;
            }
        }
    }

    // Lấy tổng điểm trừ (nếu cần hiển thị trên Zalo Mini App)
    $stmt_diem = $db->prepare("SELECT SUM(chvp.diem_tru) as tong_diem_tru 
                               FROM vi_pham_hoc_sinh vp 
                               JOIN cau_hinh_vi_pham chvp ON vp.vi_pham_id = chvp.id 
                               WHERE vp.hoc_sinh_id = ?");
    $stmt_diem->execute([$user['id']]);
    $diem_tru = $stmt_diem->fetchColumn() ?: 0;

    $user['tong_diem_tru'] = (float)$diem_tru;

    // Ghi nhận lượt truy cập và thời gian hoạt động cuối cùng của học sinh trên Zalo Mini App
    if (!empty($user['id'])) {
        try {
            $stmt_track = $db->prepare("UPDATE ho_so_hoc_sinh SET zalo_last_active = NOW(), zalo_access_count = zalo_access_count + 1 WHERE id = ?");
            $stmt_track->execute([$user['id']]);
        } catch (Exception $e) {}
    }
    
    // Fetch default Zalo Mini ID Card Template
    $stmt_card = $db->query("SELECT cau_hinh_json FROM mau_the_hoc_sinh WHERE is_zalo_default = 1 LIMIT 1");
    $card_template_json = $stmt_card->fetchColumn();
    
    $card_image_url = null;

    if ($card_template_json) {
        $parsed_template = json_decode($card_template_json, true);
        
        if ($parsed_template && is_array($parsed_template)) {
            // Render thẻ thành ảnh PNG (có cache tự động)
            require_once __DIR__ . '/../lib/card_image_renderer.php';
            require_once __DIR__ . '/../../vendor/autoload.php';
            
            $base_path = realpath(__DIR__ . '/../../');
            $base_url = "https://" . $_SERVER['HTTP_HOST'];
            
            $web_path = render_student_card_image($parsed_template, $card_template_json, $user, $base_path);
            
            if ($web_path) {
                $card_image_url = $base_url . $web_path;
            }
        }
    }

    // Lấy cài đặt chỉnh sửa từ bảng settings
    $stmt_settings = $db->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('zalo_allow_edit_profile', 'zalo_editable_fields')");
    $zalo_settings = [];
    while ($row = $stmt_settings->fetch(PDO::FETCH_ASSOC)) {
        $zalo_settings[$row['setting_key']] = $row['setting_value'];
    }
    
    // Lấy dia_chi_options từ bảng he_thong_cai_dat
    $stmt_hethong = $db->prepare("SELECT setting_value FROM he_thong_cai_dat WHERE setting_key = 'dia_chi_options' AND nam_hoc_id = ? LIMIT 1");
    $stmt_hethong->execute([$user['nam_hoc_id']]);
    $dia_chi_options = $stmt_hethong->fetchColumn() ?: '';
    
    if (!$dia_chi_options) {
        // Fallback to the latest year if not found for current year
        $stmt_hethong_fallback = $db->query("SELECT setting_value FROM he_thong_cai_dat WHERE setting_key = 'dia_chi_options' ORDER BY nam_hoc_id DESC LIMIT 1");
        $dia_chi_options = $stmt_hethong_fallback->fetchColumn() ?: '';
    }
    
    $allow_edit_profile = (($zalo_settings['zalo_allow_edit_profile'] ?? '0') === '1') && ($user['trang_thai_hoc_tap'] !== 'da_tot_nghiep');

    $editable_fields = json_decode($zalo_settings['zalo_editable_fields'] ?? '[]', true);
    if (!is_array($editable_fields)) $editable_fields = [];

    // Kiểm tra có yêu cầu chờ duyệt không
    $stmt_pending = $db->prepare("SELECT thong_tin_moi FROM yeu_cau_chinh_sua_zalo WHERE hoc_sinh_id = ? AND trang_thai = 'cho_duyet' ORDER BY created_at DESC LIMIT 1");
    $stmt_pending->execute([$user['id']]);
    $pending_data_json = $stmt_pending->fetchColumn();
    
    $has_pending_edit = false;
    $pending_data = null;
    if ($pending_data_json) {
        $has_pending_edit = true;
        $pending_data = json_decode($pending_data_json, true) ?: null;
    }

    echo json_encode([
        'success' => true,
        'data' => $user,
        'card_image_url' => $card_image_url,
        'edit_config' => [
            'allow_edit' => $allow_edit_profile,
            'editable_fields' => $editable_fields,
            'dia_chi_options' => $dia_chi_options,
            'has_pending_edit' => $has_pending_edit,
            'pending_data' => $pending_data
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    zalo_api_error('Lỗi hệ thống, vui lòng thử lại sau.', 500, $e);
}
