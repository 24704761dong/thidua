<?php
// File: src/controllers/trang_nop_don_phuc_khao.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Kiểm tra Session Xác minh
$kths_id = $_SESSION['phuckhao_verified_kths_id'] ?? null;
$timestamp = $_SESSION['phuckhao_verified_timestamp'] ?? 0;
$validity_period = 5 * 60; // 5 phút

if (!$kths_id || (time() - $timestamp > $validity_period)) {
    // Nếu session không hợp lệ hoặc hết hạn
    unset($_SESSION['phuckhao_verified_kths_id']);
    unset($_SESSION['phuckhao_verified_timestamp']);
    $_SESSION['flash_message_public'] = ['type' => 'danger', 'message' => 'Phiên xác minh đã hết hạn hoặc không hợp lệ. Vui lòng tra cứu lại.'];
    header('Location: /thidua/diemthi'); // Quay lại trang tra cứu
    exit();
}

// Nếu session hợp lệ, tiếp tục lấy dữ liệu
require_once __DIR__ . '/../../config/database.php';
$db = get_db_connection();
$student_data = null;
$error_message = null;

// ================== BẮT ĐẦU NÂNG CẤP (RULE 1, 2) ==================

// Định nghĩa các cột điểm và tên hiển thị
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
// Nhóm 2: Các cột hệ thống (Không được phúc khảo)
$diem_columns_he_thong = [
    'dtb_mon' => 'ĐTB Môn', 
    'diem_xt_tn' => 'Điểm XT TN',
    'ket_qua' => 'Kết Quả'
];

// Gộp cả 2 mảng để dùng cho truy vấn SQL
$diem_columns_display = $diem_columns_mon_hoc + $diem_columns_he_thong;
$diem_columns_db = array_keys($diem_columns_display);
// ================== KẾT THÚC NÂNG CẤP ==================


try {
    // 2. Lấy thông tin học sinh và điểm hiện tại
    $sql = "
        SELECT
            kt.id as ky_thi_id, kt.ten_ky_thi,
            hs.ho_dem, hs.ten, lh.ten_lop, kths.so_bao_danh";
            
    // Thêm các cột điểm vào câu SELECT
    foreach ($diem_columns_db as $col) { $sql .= ", ktdt.$col"; }
    
    $sql .= "
        FROM ky_thi_hoc_sinh kths
        JOIN hoc_sinh hs ON kths.hoc_sinh_id = hs.id
        JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id
        JOIN ky_thi kt ON kths.ky_thi_id = kt.id
        LEFT JOIN ky_thi_diem_thi ktdt ON kths.id = ktdt.ky_thi_hoc_sinh_id
        WHERE kths.id = ?
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute([$kths_id]);
    $student_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student_data) {
        throw new Exception("Không tìm thấy thông tin học sinh.");
    }
    
    // ================== BẮT ĐẦU NÂNG CẤP (RULE 3, 4, 5) ==================
    // 3. Lấy thông tin các môn đang chờ phúc khảo (để khóa lại)
    $stmt_pending = $db->prepare("
        SELECT 
            pkct.mon_hoc_db_col, 
            pkct.minh_chung_path, 
            pkct.diem_tn_cu, 
            pkct.diem_tl_cu, 
            pkct.diem_tong_cu
        FROM ky_thi_phuc_khao pk
        JOIN ky_thi_phuc_khao_chi_tiet pkct ON pk.id = pkct.phuc_khao_id
        WHERE pk.ky_thi_hoc_sinh_id = ? AND pk.trang_thai = 'cho_xu_ly'
    ");
    $stmt_pending->execute([$kths_id]);
    $pending_appeals_raw = $stmt_pending->fetchAll();
    
    // Tạo 2 bản đồ (map) để View sử dụng
    $pending_appeals_map = []; // Map để khóa (Rule 3)
    $appeal_details_map = []; // Map để hiển thị chi tiết (Rule 4)

    foreach($pending_appeals_raw as $item) {
        $pending_appeals_map[$item['mon_hoc_db_col']] = true;
        $appeal_details_map[$item['mon_hoc_db_col']] = [
            'path' => $item['minh_chung_path'],
            'tn_hs' => $item['diem_tn_cu'],
            'tl_hs' => $item['diem_tl_cu'],
            'tong_hs' => $item['diem_tong_cu']
        ];
    }
    // ================== KẾT THÚC NÂNG CẤP ==================


} catch (Exception $e) {
    error_log("Lỗi khi tải trang nộp đơn phúc khảo: " . $e->getMessage());
    $error_message = "Đã xảy ra lỗi khi tải thông tin. Vui lòng thử lại.";
    // Có thể unset session ở đây nếu lỗi nghiêm trọng
    unset($_SESSION['phuckhao_verified_kths_id']);
    unset($_SESSION['phuckhao_verified_timestamp']);
}

$page_title = 'Nộp đơn Phúc khảo Điểm thi';
require_once __DIR__ . '/../views/giao_dien_nop_don_phuc_khao.php';
?>