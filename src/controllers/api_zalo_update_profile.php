<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('POST, OPTIONS');
zalo_handle_options();

require_once __DIR__ . '/../lib/zalo_helpers.php';

$payload = zalo_authenticate_request();
$student_id = $payload['student_id'];
try {
    $db = get_db_connection();

    // Kiểm tra cấu hình có cho phép sửa không
    $stmt = $db->query("SELECT setting_value FROM settings WHERE setting_key = 'zalo_allow_edit_profile'");
    $allow = $stmt->fetchColumn();
    if ($allow !== '1') {
        echo json_encode(['success' => false, 'message' => 'Chức năng chỉnh sửa thông tin đang tạm khóa.']);
        exit();
    }

    // Chặn học sinh đã tốt nghiệp chỉnh sửa thông tin
    $stmt_tt = $db->prepare("SELECT trang_thai_hoc_tap FROM ho_so_hoc_sinh WHERE id = ?");
    $stmt_tt->execute([$student_id]);
    $trang_thai_hoc_tap = $stmt_tt->fetchColumn();
    if ($trang_thai_hoc_tap === 'da_tot_nghiep') {
        echo json_encode(['success' => false, 'message' => 'Học sinh đã tốt nghiệp không thể chỉnh sửa thông tin.']);
        exit();
    }

    $stmt = $db->query("SELECT setting_value FROM settings WHERE setting_key = 'zalo_auto_approve_edit'");
    $auto_approve = $stmt->fetchColumn() === '1';

    $stmt = $db->query("SELECT setting_value FROM settings WHERE setting_key = 'zalo_editable_fields'");
    $editable_fields = json_decode($stmt->fetchColumn() ?: '[]', true);

    // Lấy thông tin cũ của học sinh
    $stmt_hs = $db->prepare("SELECT id, ma_hoc_sinh, ho_dem, ten, anh_the, sdt, email, chuc_vu, ngay_sinh, gioi_tinh, tinh_thanhpho, xa_phuong, ap_khupho, dia_chi_chi_tiet FROM hoc_sinh WHERE id = ?");
    $stmt_hs->execute([$student_id]);
    $hs = $stmt_hs->fetch(PDO::FETCH_ASSOC);
    if (!$hs) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy học sinh.']);
        exit();
    }
    $ho_so_id = $hs['id'];

    $contentType = $_SERVER["CONTENT_TYPE"] ?? '';
    if (strpos($contentType, 'multipart/form-data') !== false && empty($_POST) && empty($_FILES) && isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {
        echo json_encode(['success' => false, 'message' => 'Dung lượng file tải lên quá lớn. Vui lòng chọn ảnh có kích thước nhỏ hơn (tối đa 2MB).']);
        exit();
    }

    // Nhận dữ liệu
    $data_str = $_POST['data'] ?? '{}';
    error_log("Raw POST data: " . $data_str);
    error_log("Raw FILES: " . json_encode(array_keys($_FILES)));
    $data = json_decode($data_str, true) ?: [];

    // Lọc ra các trường có sửa và được phép sửa
    $thong_tin_moi = [];
    $thong_tin_cu = [];

    // Xác thực OTP nếu có đổi email
    if (in_array('email', $editable_fields) && isset($data['email']) && $data['email'] !== $hs['email']) {
        $otp_code = $data['otp'] ?? '';
        if (!$otp_code) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng cung cấp mã OTP để đổi email.']);
            exit();
        }
        
        $stmt_otp = $db->prepare("SELECT id FROM zalo_otp_codes WHERE student_id = ? AND email = ? AND otp_code = ? AND expires_at > NOW() ORDER BY id DESC LIMIT 1");
        $stmt_otp->execute([$student_id, $data['email'], $otp_code]);
        $otp_id = $stmt_otp->fetchColumn();
        
        if (!$otp_id) {
            echo json_encode(['success' => false, 'message' => 'Mã OTP không chính xác hoặc đã hết hạn.']);
            exit();
        }
        
        $db->prepare("DELETE FROM zalo_otp_codes WHERE id = ?")->execute([$otp_id]);
    }
    foreach ($editable_fields as $field) {
        if ($field === 'dia_chi') {
            $address_fields = ['tinh_thanhpho', 'xa_phuong', 'ap_khupho', 'dia_chi_chi_tiet'];
            foreach ($address_fields as $addr_field) {
                if (isset($data[$addr_field])) {
                    $thong_tin_moi[$addr_field] = $data[$addr_field];
                    $thong_tin_cu[$addr_field] = $hs[$addr_field] ?? '';
                }
            }
        } elseif (isset($data[$field]) && $field !== 'anh_the') {
            $thong_tin_moi[$field] = $data[$field];
            $thong_tin_cu[$field] = $hs[$field] ?? '';
        }
    }

    // Xử lý upload ảnh
    if (in_array('anh_the', $editable_fields) && isset($_FILES['anh_the'])) {
        if ($_FILES['anh_the']['error'] === UPLOAD_ERR_INI_SIZE) {
            echo json_encode(['success' => false, 'message' => 'Dung lượng ảnh tải lên quá lớn. Vui lòng chọn ảnh nhỏ hơn (tối đa 2MB).']);
            exit();
        }
        
        if ($_FILES['anh_the']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../../public/assets/anh_the/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $file_name = $_FILES['anh_the']['name'];
        $file_tmp = $_FILES['anh_the']['tmp_name'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            echo json_encode(['success' => false, 'message' => 'Định dạng ảnh không hợp lệ.']);
            exit();
        }
        
        $new_name = 'zalo_hs_' . $ho_so_id . '_' . time() . '.' . $ext;
        if (move_uploaded_file($file_tmp, $upload_dir . $new_name)) {
            $thong_tin_moi['anh_the'] = $new_name;
            $thong_tin_cu['anh_the'] = $hs['anh_the'];
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi upload ảnh.']);
            exit();
        }
        }
    }

    error_log("Update Profile Debug: data=" . json_encode($data));
    error_log("Update Profile Debug: hs=" . json_encode($hs));
    error_log("Update Profile Debug: thong_tin_moi=" . json_encode($thong_tin_moi));

    if (empty($thong_tin_moi)) {
        echo json_encode(['success' => true, 'message' => 'Không có thông tin nào thay đổi.']);
        exit();
    }

    // Kiểm tra email trùng lặp nếu có thay đổi email
    if (isset($thong_tin_moi['email']) && $thong_tin_moi['email'] !== $hs['email']) {
        $stmt_check = $db->prepare("SELECT id FROM hoc_sinh WHERE email = ? AND id != ?");
        $stmt_check->execute([$thong_tin_moi['email'], $ho_so_id]);
        if ($stmt_check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Email này đã được sử dụng bởi học sinh khác!']);
            exit();
        }
    }

    $db->beginTransaction();

    if ($auto_approve) {
        // Tự động duyệt: Update trực tiếp
        $set_clauses = [];
        $values = [];
        $has_chuc_vu = false;
        $chuc_vu_val = '';
        foreach ($thong_tin_moi as $key => $val) {
            if ($key === 'chuc_vu') {
                $has_chuc_vu = true;
                $chuc_vu_val = $val;
            } else {
                $set_clauses[] = "$key = ?";
                $values[] = $val;
            }
        }
        
        if (!empty($set_clauses)) {
            $values[] = $ho_so_id;
            $sql = "UPDATE ho_so_hoc_sinh SET " . implode(', ', $set_clauses) . " WHERE id = ?";
            $stmt_upd = $db->prepare($sql);
            $stmt_upd->execute($values);
        }
        
        if ($has_chuc_vu) {
            $sql_qt = "UPDATE quatrinh_hoc_tap SET chuc_vu = ? WHERE ma_hoc_sinh = ? AND nam_hoc_id = get_current_nam_hoc_id_mysql()";
            $stmt_qt = $db->prepare($sql_qt);
            $stmt_qt->execute([$chuc_vu_val, $hs['ma_hoc_sinh']]);
        }

        // Clear cache ảnh thẻ (nếu có update ảnh)
        if (isset($thong_tin_moi['anh_the'])) {
            $cache_dir = __DIR__ . '/../../public/assets/card_cache/';
            $pattern = $cache_dir . $hs['ma_hoc_sinh'] . '_*.png';
            array_map('unlink', glob($pattern));
        }
        
        // Gửi thông báo học sinh
        $msg_hs = "Yêu cầu cập nhật thông tin hồ sơ của bạn đã được cập nhật thành công (duyệt tự động).";
        create_student_notification($db, $ho_so_id, 'Cập nhật thành công', $msg_hs, 'cap_nhat_ho_so');

        if (!empty($hs['zalo_id'])) {
            send_zalo_push_notification($hs['zalo_id'], $msg_hs, 'Cập nhật thành công');
        }

        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Cập nhật thông tin thành công.']);
    } else {
        // Ghi đè yêu cầu cũ (xóa nếu có)
        $stmt_del = $db->prepare("DELETE FROM yeu_cau_chinh_sua_zalo WHERE hoc_sinh_id = ? AND trang_thai = 'cho_duyet'");
        $stmt_del->execute([$ho_so_id]);

        $stmt_ins = $db->prepare("INSERT INTO yeu_cau_chinh_sua_zalo (hoc_sinh_id, thong_tin_cu, thong_tin_moi, trang_thai, created_at) VALUES (?, ?, ?, 'cho_duyet', NOW())");
        $stmt_ins->execute([
            $ho_so_id, 
            json_encode($thong_tin_cu),
            json_encode($thong_tin_moi)
        ]);

        $noi_dung_tb = "Học sinh " . $hs['ho_dem'] . " " . $hs['ten'] . " vừa gửi yêu cầu thay đổi thông tin hồ sơ Zalo.";
        $stmt_tb = $db->prepare("INSERT INTO thong_bao (loai_thong_bao, id_lien_quan, noi_dung, thoi_gian) VALUES ('yeu_cau_zalo', ?, ?, NOW())");
        $stmt_tb->execute([$ho_so_id, $noi_dung_tb]);
        
        // Gửi thông báo học sinh
        $msg_hs = "Yêu cầu cập nhật thông tin hồ sơ của bạn đã được gửi và đang chờ Admin xét duyệt.";
        create_student_notification($db, $ho_so_id, 'Đã gửi yêu cầu', $msg_hs, 'gui_yeu_cau_ho_so');

        if (!empty($hs['zalo_id'])) {
            send_zalo_push_notification($hs['zalo_id'], $msg_hs, 'Cập nhật thành công');
        }

        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Yêu cầu của bạn đã được gửi và đang chờ quản trị viên duyệt.']);
    }

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    http_response_code(500);
    zalo_api_error('Lỗi hệ thống, vui lòng thử lại sau.', 500, $e);
}
