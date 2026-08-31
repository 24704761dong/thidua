<?php
// File: src/controllers/api_tra_cuu_diem.php (ĐÃ THÊM XÁC THỰC CAPTCHA)

// LUÔN BẮT ĐẦU SESSION ĐẦU TIÊN
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/database.php';
$db = get_db_connection();
$data = json_decode(file_get_contents('php://input'), true);

// === CAPTCHA VERIFICATION START ===
// 1. Lấy CAPTCHA người dùng gửi lên
$user_captcha = $data['captcha'] ?? null;

// 2. Lấy CAPTCHA đúng trong Session (do file captcha_image.php tạo ra)
$correct_captcha = $_SESSION['captcha_text'] ?? null;

// 3. Xóa CAPTCHA khỏi session ngay lập tức để chống tấn công (replay attack)
unset($_SESSION['captcha_text']);

// 4. Kiểm tra
if (!$user_captcha || !$correct_captcha || strtolower($user_captcha) !== strtolower($correct_captcha)) {
    // Nếu CAPTCHA sai -> Dừng lại và báo lỗi
    http_response_code(401); // 401 Unauthorized
    echo json_encode([
        'success' => false, 
        'message' => 'Mã xác nhận không chính xác. Vui lòng thử lại.'
    ]);
    exit(); // Dừng hoàn toàn
}
// === CAPTCHA VERIFICATION END ===

// 5. NẾU CAPTCHA ĐÚNG -> Tiếp tục logic tra cứu như cũ
$ky_thi_id = $data['ky_thi_id'] ?? null;
$search_method = $data['search_method'] ?? null; // Phương thức duy nhất được chọn khi gọi API
$search_value1 = trim($data['search_value1'] ?? '');
$search_value2 = trim($data['search_value2'] ?? '');

// Định nghĩa các cột điểm và tên hiển thị
$diem_columns_db = [
    'diem_toan', 'diem_van', 'diem_ly', 'diem_hoa', 'diem_sinh', 'diem_su',
    'diem_dia', 'diem_gdktpl', 'diem_ngoai_ngu', 'diem_cn_nn',
    'dtb_mon', 'diem_xt_tn', 'ket_qua'
];
$diem_columns_display = [
    'diem_toan' => 'Toán', 'diem_van' => 'Ngữ Văn', 'diem_ly' => 'Vật Lý',
    'diem_hoa' => 'Hóa Học', 'diem_sinh' => 'Sinh Học', 'diem_su' => 'Lịch Sử',
    'diem_dia' => 'Địa Lý', 'diem_gdktpl' => 'GDKT-PL', 'diem_ngoai_ngu' => 'Ngoại Ngữ',
    'diem_cn_nn' => 'CN-NN', 'dtb_mon' => 'ĐTB Môn', 'diem_xt_tn' => 'Điểm XT TN',
    'ket_qua' => 'Kết Quả'
];
// Các cột reviewed hợp lệ (từ CSDL phiên bản 30)
$allowed_reviewed_cols = [
    'reviewed_toan', 'reviewed_van', 'reviewed_ly', 'reviewed_hoa', 'reviewed_sinh',
    'reviewed_su', 'reviewed_dia', 'reviewed_gdktpl', 'reviewed_ngoai_ngu',
    'reviewed_cn_nn', 'reviewed_dtb_mon', 'reviewed_diem_xt_tn', 'reviewed_ket_qua'
];


if (!$ky_thi_id || !$search_method || empty($search_value1)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin tra cứu.']);
    exit();
}

try {
    // 1. Lấy cấu hình của kỳ thi
    $stmt_config = $db->prepare("
        SELECT ten_ky_thi, tra_cuu_cong_khai, phuong_thuc_tra_cuu, truong_hien_thi
        FROM ky_thi
        WHERE id = ?
    ");
    $stmt_config->execute([$ky_thi_id]);
    $config = $stmt_config->fetch();

    if (!$config || !$config['tra_cuu_cong_khai']) {
        throw new Exception('Kỳ thi này không tồn tại hoặc chưa được mở tra cứu công khai.');
    }

    // 2. Decode cấu hình hiển thị và Lấy phương thức cho phép DUY NHẤT
    $allowed_method = $config['phuong_thuc_tra_cuu']; // Đây là chuỗi đơn
    $display_fields_config = json_decode($config['truong_hien_thi'] ?: '{}', true);

    // 3. Kiểm tra phương thức tra cứu có khớp không
    if ($search_method !== $allowed_method) {
        throw new Exception('Phương thức tra cứu không đúng với cấu hình của kỳ thi này.');
    }

    // 4. Xây dựng câu truy vấn
    // Thêm kths.id và các cột reviewed_...
    $sql_select = " SELECT kths.id as ky_thi_hoc_sinh_id, hs.ho_dem, hs.ten, hs.ngay_sinh, lh.ten_lop, kths.so_bao_danh, hs.ma_hoc_sinh, hs.ma_moet ";
    foreach ($diem_columns_db as $col) {
        $sql_select .= ", ktdt.$col";
        // Thêm cột reviewed tương ứng
        $reviewed_col = 'reviewed_' . str_replace('diem_', '', $col);
        if (in_array($col, ['dtb_mon', 'diem_xt_tn', 'ket_qua'])) {
             $reviewed_col = 'reviewed_' . $col;
        }
        // Chỉ thêm nếu cột đó tồn tại trong $allowed_reviewed_cols (để an toàn)
        if (in_array($reviewed_col, $allowed_reviewed_cols)) {
             $sql_select .= ", ktdt.$reviewed_col";
        }
    }
    $sql_from_join = " FROM ky_thi_hoc_sinh kths JOIN hoc_sinh hs ON kths.hoc_sinh_id = hs.id JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id LEFT JOIN ky_thi_diem_thi ktdt ON kths.id = ktdt.ky_thi_hoc_sinh_id WHERE kths.ky_thi_id = :ky_thi_id ";
    $params = [':ky_thi_id' => $ky_thi_id];
    $sql_where_condition = "";

    // Switch case cho $allowed_method
    switch ($allowed_method) {
        case 'sbd':
            $sql_where_condition = " AND kths.so_bao_danh = :value1";
            $params[':value1'] = $search_value1;
            break;
        case 'cccd':
            $sql_where_condition = " AND hs.ma_hoc_sinh = :value1";
            $params[':value1'] = $search_value1;
            break;
        case 'moet':
             $sql_where_condition = " AND hs.ma_moet = :value1";
             $params[':value1'] = $search_value1;
            break;
        case 'ten_ngaysinh':
            if (empty($search_value2)) throw new Exception('Vui lòng nhập Ngày sinh.');
            // Chuẩn hóa ngày sinh
            try {
                $date_obj = new DateTime($search_value2);
                $ngay_sinh_db = $date_obj->format('Y-m-d');
            } catch (Exception $e) { throw new Exception('Định dạng Ngày sinh không hợp lệ. Vui lòng nhập dạng YYYY-MM-DD hoặc DD/MM/YYYY.'); }
            // Tách họ tên
            $ho_ten = explode(' ', $search_value1);
            $ten = array_pop($ho_ten);
            $ho_dem = implode(' ', $ho_ten);

            $sql_where_condition = " AND hs.ten = :ten AND hs.ho_dem LIKE :ho_dem AND hs.ngay_sinh = :ngay_sinh";
            $params[':ten'] = $ten;
            $params[':ho_dem'] = $ho_dem . '%';
            $params[':ngay_sinh'] = $ngay_sinh_db;
            break;
        default: throw new Exception('Phương thức tra cứu cấu hình không hợp lệ.');
    }

    // 5. Thực hiện truy vấn
    $stmt_result = $db->prepare($sql_select . $sql_from_join . $sql_where_condition);
    $stmt_result->execute($params);
    $result_data = $stmt_result->fetch(PDO::FETCH_ASSOC);

    // 6. Xử lý kết quả
    if (!$result_data) {
        echo json_encode(['success' => true, 'found' => false, 'message' => 'Không tìm thấy thông tin thí sinh phù hợp.']);
    } else {
        $display_data = [];
        $kths_id = $result_data['ky_thi_hoc_sinh_id']; // Lấy ID quan trọng này
        $display_data['ky_thi'] = $config['ten_ky_thi'];

        // Thêm các trường thông tin cá nhân ĐƯỢC PHÉP hiển thị
        if ($display_fields_config['ho_ten'] ?? false) $display_data['Họ và Tên'] = $result_data['ho_dem'] . ' ' . $result_data['ten'];
        if ($display_fields_config['ngay_sinh'] ?? false) $display_data['Ngày Sinh'] = $result_data['ngay_sinh'] ? date('d/m/Y', strtotime($result_data['ngay_sinh'])) : '';
        if ($display_fields_config['lop'] ?? false) $display_data['Lớp'] = $result_data['ten_lop'];
        if ($display_fields_config['sbd'] ?? false) $display_data['Số Báo Danh'] = $result_data['so_bao_danh'];
        if ($display_fields_config['cccd'] ?? false) $display_data['Số CCCD'] = $result_data['ma_hoc_sinh'];
        if ($display_fields_config['ma_moet'] ?? false) $display_data['Mã MOET'] = $result_data['ma_moet'];

        // Thêm điểm các môn (LUÔN hiển thị nếu có điểm, thêm dấu * nếu reviewed)
        $diem_thi = [];
        foreach ($diem_columns_db as $col_db) {
            if ($result_data[$col_db] !== null && $result_data[$col_db] !== '') {
                $diem_value = $result_data[$col_db];

                // Tạo tên cột reviewed
                $reviewed_col = 'reviewed_' . str_replace('diem_', '', $col_db);
                if (in_array($col_db, ['dtb_mon', 'diem_xt_tn', 'ket_qua'])) {
                     $reviewed_col = 'reviewed_' . $col_db;
                }

                // Kiểm tra cờ reviewed và thêm dấu *
                if (isset($result_data[$reviewed_col]) && $result_data[$reviewed_col] == 1) {
                     $diem_value .= '*'; // Thêm dấu sao
                }

                $diem_thi[ $diem_columns_display[$col_db] ] = $diem_value; // Gán giá trị có thể có dấu *
            }
        }
         // Chỉ thêm mục "Điểm thi" nếu có ít nhất 1 môn có điểm hoặc có kết quả
        if (!empty($diem_thi)) {
             $display_data['Điểm thi'] = $diem_thi;
        }


        echo json_encode([
            'success' => true,
            'found' => true,
            'data' => $display_data,
            'kths_id' => $kths_id // Trả về ID này cho JavaScript
        ]);
    }

} catch (Exception $e) {
    http_response_code(400); // Bad Request hoặc lỗi logic
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>