<?php
// File: src/controllers/api_xac_minh_phuc_khao.php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/database.php';
$db = get_db_connection();
$data = json_decode(file_get_contents('php://input'), true);

$ky_thi_id = $data['ky_thi_id'] ?? null;
$kths_id = $data['kths_id'] ?? null; // ID học sinh trong kỳ thi (từ API tra cứu)
$verification_data = $data['verification_data'] ?? []; // Dữ liệu HS nhập {'ho_ten': '...', 'sbd': '...'}

if (!$ky_thi_id || !$kths_id || empty($verification_data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin xác minh.']);
    exit();
}

try {
    // 1. Lấy cấu hình xác minh của kỳ thi
    $stmt_config = $db->prepare("SELECT phuc_khao_xac_minh FROM ky_thi WHERE id = ? AND tra_cuu_cong_khai = 1");
    $stmt_config->execute([$ky_thi_id]);
    $config_json = $stmt_config->fetchColumn();
    if (!$config_json) throw new Exception('Kỳ thi không hợp lệ hoặc chưa bật phúc khảo.');

    $required_fields = json_decode($config_json ?: '{}', true);
    if (empty($required_fields)) throw new Exception('Kỳ thi chưa cấu hình trường xác minh phúc khảo.');

    // 2. Lấy thông tin thực tế của học sinh từ CSDL
    $stmt_hs = $db->prepare("
        SELECT hs.ho_dem, hs.ten, lh.ten_lop, hs.ngay_sinh, kths.so_bao_danh, hs.ma_hoc_sinh, hs.ma_moet
        FROM ky_thi_hoc_sinh kths
        JOIN hoc_sinh hs ON kths.hoc_sinh_id = hs.id
        JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id
        WHERE kths.id = ? AND kths.ky_thi_id = ?
    ");
    $stmt_hs->execute([$kths_id, $ky_thi_id]);
    $student_info = $stmt_hs->fetch(PDO::FETCH_ASSOC);

    if (!$student_info) throw new Exception('Không tìm thấy thông tin học sinh.');

    // 3. Đối chiếu thông tin nhập với CSDL cho các trường bắt buộc
    $errors = [];
    foreach ($required_fields as $field_key => $is_required) {
        if ($is_required) {
            $user_value = trim($verification_data[$field_key] ?? '');
            $db_value = null;

            switch ($field_key) {
                case 'ho_ten':    $db_value = trim($student_info['ho_dem'] . ' ' . $student_info['ten']); break;
                case 'lop':       $db_value = trim($student_info['ten_lop']); break;
                case 'ngay_sinh': $db_value = $student_info['ngay_sinh']; break; // Sẽ so sánh sau khi chuẩn hóa
                case 'sbd':       $db_value = trim($student_info['so_bao_danh']); break;
                case 'cccd':      $db_value = trim($student_info['ma_hoc_sinh']); break;
                case 'ma_moet':   $db_value = trim($student_info['ma_moet']); break;
            }

            // So sánh (chú ý ngày sinh và bỏ qua chữ hoa/thường)
            if ($field_key === 'ngay_sinh') {
                try {
                    $user_date = (new DateTime($user_value))->format('Y-m-d');
                    if ($user_date !== $db_value) {
                         $errors[] = "Ngày sinh không khớp.";
                    }
                } catch (Exception $e) {
                     $errors[] = "Định dạng Ngày sinh không hợp lệ.";
                }
            } elseif (strcasecmp($user_value, $db_value ?? '') !== 0) { // So sánh không phân biệt hoa thường
                $errors[] = ($available_verification_fields[$field_key] ?? $field_key) . " không khớp.";
            }
        }
    }

    // 4. Trả kết quả
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'message' => 'Thông tin xác minh không chính xác:', 'errors' => $errors]);
    } else {
        // Xác minh thành công! Tạo token tạm thời để cho phép truy cập trang nộp đơn
        // (Cách đơn giản: dùng session)
        $_SESSION['phuckhao_verified_kths_id'] = $kths_id;
        $_SESSION['phuckhao_verified_timestamp'] = time(); // Token chỉ có hiệu lực 5 phút

        echo json_encode(['success' => true, 'message' => 'Xác minh thành công!', 'redirect_url' => '/thidua/nop-don-phuc-khao']); // URL trang nộp đơn
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// Lấy lại định nghĩa các trường xác minh từ controller cài đặt (hoặc định nghĩa lại ở đây)
$available_verification_fields = [
    'ho_ten' => 'Họ và Tên', 'lop' => 'Lớp', 'ngay_sinh' => 'Ngày Sinh',
    'sbd' => 'Số Báo Danh', 'cccd' => 'Số CCCD', 'ma_moet' => 'Mã MOET'
];
?>