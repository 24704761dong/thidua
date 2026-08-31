<?php
// File: src/controllers/api_submit_phuc_khao.php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

// --- (Nạp file log, kiểm tra session) ---
require_once __DIR__ . '/../lib/helpers.php';
log_to_file("[PHUC KHAO SUBMIT] POST DATA: ". print_r($_POST, true));
log_to_file("[PHUC KHAO SUBMIT] FILES DATA: ". print_r($_FILES, true));

// 1. Kiểm tra Session Xác minh (lặp lại để bảo mật API)
$kths_id = $_SESSION['phuckhao_verified_kths_id'] ?? null;
$timestamp = $_SESSION['phuckhao_verified_timestamp'] ?? 0;
$validity_period = 5 * 60; // 5 phút

if (!$kths_id || (time() - $timestamp > $validity_period)) {
    http_response_code(403); // Forbidden
    echo json_encode(['success' => false, 'message' => 'Phiên làm việc đã hết hạn. Vui lòng tra cứu lại.']);
    exit();
}

require_once __DIR__ . '/../../config/database.php';
$db = get_db_connection();

// --- Cấu hình Upload ---
$upload_dir_base = __DIR__ . '/../../public/uploads/phuckhao/'; // Thư mục lưu file phúc khảo
$allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
$max_file_size = 5 * 1024 * 1024; // 5MB
// ---------------------

// Định nghĩa các cột điểm (phải khớp)
// Nhóm 1: Các môn học (Được phép phúc khảo)
$diem_columns_mon_hoc = [
    'diem_toan' => 'Toán', 
    'diem_van' => 'Ngữ Văn', 
    'diem_ly' => 'Vật Lý',
    'diem_hoa' => 'Hóa Học', 
    'diem_sinh' => 'Sinh Học', 
    'diem_su' => 'Lịch Sử',
    'diem_dia' => 'Địa Lý', 
    'diem_gdktpl' => 'GDKT-PL', 
    'diem_ngoai_ngu' => 'Ngoại Ngữ',
    'diem_cn_nn' => 'CN-NN'
];
// Chỉ các MÔN HỌC (keys của nhóm 1) mới là hợp lệ để phúc khảo
$diem_columns_db_allowed = array_keys($diem_columns_mon_hoc);
// Gộp 2 mảng để dùng cho truy vấn SQL
$diem_columns_he_thong = [
    'dtb_mon' => 'ĐTB Môn', 
    'diem_xt_tn' => 'Điểm XT TN',
    'ket_qua' => 'Kết Quả'
];
$diem_columns_display = $diem_columns_mon_hoc + $diem_columns_he_thong;


try {
    // Lấy ky_thi_id và toàn bộ điểm gốc CÙNG LÚC
    $stmt_info = $db->prepare("
        SELECT kths.ky_thi_id, ktdt.*
        FROM ky_thi_hoc_sinh kths
        LEFT JOIN ky_thi_diem_thi ktdt ON kths.id = ktdt.ky_thi_hoc_sinh_id
        WHERE kths.id = ?
    ");
    $stmt_info->execute([$kths_id]);
    $student_exam_data = $stmt_info->fetch(PDO::FETCH_ASSOC);
    
    if (!$student_exam_data) throw new Exception("Không tìm thấy thông tin học sinh trong kỳ thi.");
    $ky_thi_id = $student_exam_data['ky_thi_id'];

    // ================== BẮT ĐẦU NÂNG CẤP (RULE 3 - Server side) ==================
    // Lấy danh sách các môn đang chờ xử lý của học sinh này
    $stmt_pending_check = $db->prepare("
        SELECT pkct.mon_hoc_db_col
        FROM ky_thi_phuc_khao pk
        JOIN ky_thi_phuc_khao_chi_tiet pkct ON pk.id = pkct.phuc_khao_id
        WHERE pk.ky_thi_hoc_sinh_id = ? AND pk.trang_thai = 'cho_xu_ly'
    ");
    $stmt_pending_check->execute([$kths_id]);
    $pending_subjects = $stmt_pending_check->fetchAll(PDO::FETCH_COLUMN);
    // ================== KẾT THÚC NÂNG CẤP ==================


    // Lấy dữ liệu từ POST và FILES
    $selected_subjects = $_POST['subjects'] ?? []; 
    if (empty($selected_subjects)) {
        throw new Exception("Vui lòng chọn ít nhất một môn để phúc khảo.");
    }
    $uploaded_files = $_FILES['minhchung'] ?? [];
    $diem_tn_hs_array = $_POST['diem_tn_hs'] ?? [];
    $diem_tl_hs_array = $_POST['diem_tl_hs'] ?? [];
    $diem_tong_hs_array = $_POST['diem_tong_hs'] ?? [];

    $processed_subjects = []; 
    foreach ($selected_subjects as $subject_col) {
        
        if (!in_array($subject_col, $diem_columns_db_allowed)) {
            throw new Exception("Môn học không hợp lệ: $subject_col");
        }
        
        // ================== BẮT ĐẦU NÂNG CẤP (RULE 3 - Server side) ==================
        // Nếu môn này đã có trong danh sách chờ, ném lỗi
        if (in_array($subject_col, $pending_subjects)) {
            throw new Exception("Môn ".($diem_columns_display[$subject_col] ?? $subject_col)." đã được nộp và đang chờ xử lý. Bạn không thể nộp lại.");
        }
        // ================== KẾT THÚC NÂNG CẤP ==================

        if (!isset($uploaded_files['name'][$subject_col]) || $uploaded_files['error'][$subject_col] !== UPLOAD_ERR_OK) {
             throw new Exception("Vui lòng tải lên file minh chứng cho môn: ". ($diem_columns_mon_hoc[$subject_col] ?? $subject_col));
        }

        // ... (Validate file, tạo đường dẫn, di chuyển file - logic này giữ nguyên) ...
        $file_name = $uploaded_files['name'][$subject_col];
        $file_tmp = $uploaded_files['tmp_name'][$subject_col];
        $file_size = $uploaded_files['size'][$subject_col];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if (!in_array($file_ext, $allowed_extensions)) throw new Exception("Minh chứng môn ". ($diem_columns_mon_hoc[$subject_col] ?? $subject_col) . ": Chỉ chấp nhận file ". implode(', ', $allowed_extensions));
        if ($file_size > $max_file_size) throw new Exception("Minh chứng môn ". ($diem_columns_mon_hoc[$subject_col] ?? $subject_col) . ": Kích thước file không được vượt quá ". ($max_file_size / 1024 / 1024) . "MB");
        $upload_subdir = $upload_dir_base . $ky_thi_id . '/' . $kths_id . '/';
        if (!is_dir($upload_subdir)) { if (!mkdir($upload_subdir, 0777, true)) { throw new Exception("Không thể tạo thư mục lưu file."); } }
        $new_file_name = uniqid($subject_col . '_', true) . '.'. $file_ext;
        $destination = $upload_subdir . $new_file_name;
        $relative_path = 'uploads/phuckhao/' . $ky_thi_id . '/' . $kths_id . '/'. $new_file_name;
        if (!move_uploaded_file($file_tmp, $destination)) { throw new Exception("Lỗi khi di chuyển file minh chứng."); }

        // Lấy 3 điểm HS nhập (điểm mong muốn)
        $diem_tn_hs = $diem_tn_hs_array[$subject_col] ?? null;
        $diem_tl_hs = $diem_tl_hs_array[$subject_col] ?? null;
        $diem_tong_hs = $diem_tong_hs_array[$subject_col] ?? null;
        
        if ($diem_tong_hs === null || $diem_tong_hs === '') {
            throw new Exception("Vui lòng nhập Tổng điểm (dự kiến) cho môn: ". ($diem_columns_mon_hoc[$subject_col] ?? $subject_col));
        }

        // Lấy ĐIỂM GỐC
        $diem_goc_original = $student_exam_data[$subject_col] ?? null;
        
        // Lưu thông tin môn này để chuẩn bị insert CSDL
        $processed_subjects[$subject_col] = [
            'path' => $relative_path,
            'tn_hs' => $diem_tn_hs === '' ? null : (float)$diem_tn_hs,
            'tl_hs' => $diem_tl_hs === '' ? null : (float)$diem_tl_hs,
            'tong_hs' => (float)$diem_tong_hs,
            'goc_original' => $diem_goc_original, // Lưu điểm gốc
        ];
    }

    // --- Bắt đầu lưu vào CSDL ---
    $db->beginTransaction();

    // 1. Tạo đơn phúc khảo chính
    $stmt_phuckhao = $db->prepare("
        INSERT INTO ky_thi_phuc_khao (ky_thi_id, ky_thi_hoc_sinh_id, thoi_gian_nop, trang_thai)
        VALUES (?, ?, ?, 'cho_xu_ly')
    ");
    $stmt_phuckhao->execute([$ky_thi_id, $kths_id, date('Y-m-d H:i:s')]);
    $phuc_khao_id = $db->lastInsertId();

    // 2. Lưu chi tiết (lưu cả điểm gốc và điểm mong muốn)
    $stmt_chitiet = $db->prepare("
        INSERT INTO ky_thi_phuc_khao_chi_tiet
        (phuc_khao_id, mon_hoc_db_col, minh_chung_path, 
         diem_tn_cu, diem_tl_cu, diem_tong_cu, diem_goc) -- Thêm cột diem_goc
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    foreach ($processed_subjects as $subject_col => $data) {
        $stmt_chitiet->execute([
            $phuc_khao_id, 
            $subject_col, 
            $data['path'], 
            // Lưu điểm mong muốn của HS vào cột _cu
            $data['tn_hs'], 
            $data['tl_hs'], 
            $data['tong_hs'],
            // Lưu điểm gốc vào cột diem_goc
            $data['goc_original']
        ]);
    }

    $db->commit();
    // --- Kết thúc lưu CSDL ---

    // 3. Xóa Session Xác minh
    unset($_SESSION['phuckhao_verified_kths_id']);
    unset($_SESSION['phuckhao_verified_timestamp']);

    echo json_encode(['success' => true, 'message' => 'Đã gửi yêu cầu phúc khảo thành công!']);

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    // Cố gắng xóa file đã upload nếu có lỗi CSDL
    if (!empty($processed_subjects)) {
        foreach($processed_subjects as $data) {
            $full_path = $upload_dir_base . substr($data['path'], strlen('uploads/phuckhao/'));
            if (file_exists($full_path)) @unlink($full_path);
        }
    }
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>