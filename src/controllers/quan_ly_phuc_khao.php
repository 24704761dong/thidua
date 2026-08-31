<?php
// File: src/controllers/quan_ly_phuc_khao.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) { 
    header('Location: /thidua/tracuu');
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$ky_thi_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$ky_thi_id) { 
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'ID Kỳ thi không hợp lệ.'];
    header('Location: /thidua/admin/exam-list');
    exit();
}

$db = get_db_connection();
$ky_thi_info = null;
$flat_appeal_list = []; // DANH SÁCH PHẲNG
$error_message = null;

// Ánh xạ tên cột DB sang tên hiển thị
$diem_columns_display = [
    'diem_toan' => 'Toán', 'diem_van' => 'Ngữ Văn', 'diem_ly' => 'Vật Lý',
    'diem_hoa' => 'Hóa Học', 'diem_sinh' => 'Sinh Học', 'diem_su' => 'Lịch Sử',
    'diem_dia' => 'Địa Lý', 'diem_gdktpl' => 'GDKT-PL', 'diem_ngoai_ngu' => 'Ngoại Ngữ',
    'diem_cn_nn' => 'CN-NN', 'dtb_mon' => 'ĐTB Môn', 'diem_xt_tn' => 'Điểm XT TN',
    'ket_qua' => 'Kết Quả'
];


try {
    // 1. Lấy thông tin kỳ thi
    $stmt_ky_thi = $db->prepare("SELECT * FROM ky_thi WHERE id = ?");
    $stmt_ky_thi->execute([$ky_thi_id]);
    $ky_thi_info = $stmt_ky_thi->fetch();
    if (!$ky_thi_info) { 
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Không tìm thấy kỳ thi này.'];
        header('Location: /thidua/admin/exam-list');
        exit();
    }

    // 2. Lấy tất cả đơn phúc khảo và chi tiết của kỳ thi này (DẠNG PHẲNG)
    // ================== BẮT ĐẦU SỬA LỖI (PROBLEM 2) ==================
    // Lấy tất cả dữ liệu, bao gồm cả cột `diem_goc` mới
    $sql = "
        SELECT
            pk.id as phuc_khao_id,
            pk.ky_thi_hoc_sinh_id,
            pk.thoi_gian_nop,
            pk.trang_thai,
            hs.ho_dem, hs.ten, lh.ten_lop, kths.so_bao_danh,
            
            pkct.id as chi_tiet_id,
            pkct.mon_hoc_db_col,
            pkct.minh_chung_path,
            
            pkct.diem_tn_cu,
            pkct.diem_tl_cu,
            pkct.diem_tong_cu,
            
            pkct.diem_tn_moi,
            pkct.diem_tl_moi,
            pkct.diem_tong_moi,

            pkct.diem_goc -- LẤY ĐIỂM GỐC TỪ CỘT MỚI

        FROM ky_thi_phuc_khao pk
        JOIN ky_thi_phuc_khao_chi_tiet pkct ON pk.id = pkct.phuc_khao_id
        JOIN ky_thi_hoc_sinh kths ON pk.ky_thi_hoc_sinh_id = kths.id
        JOIN hoc_sinh hs ON kths.hoc_sinh_id = hs.id
        JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id
        WHERE pk.ky_thi_id = ?
        ORDER BY pk.thoi_gian_nop DESC, hs.ten , hs.ho_dem , pkct.mon_hoc_db_col
    ";
    // ================== KẾT THÚC SỬA LỖI (PROBLEM 2) ==================
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$ky_thi_id]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. XỬ LÝ DỮ LIỆU PHẲNG
    foreach ($results as $row) {
        $item = $row; // Copy
        
        // Lấy tên môn
        $item['ten_mon'] = $diem_columns_display[$row['mon_hoc_db_col']] ?? $row['mon_hoc_db_col'];
        
        // SỬA LỖI (PROBLEM 2):
        // Giờ đây $item['diem_goc'] đã chứa điểm gốc thực sự,
        // được lấy từ cột `diem_goc` của bảng `ky_thi_phuc_khao_chi_tiet`.
        // (Không cần làm gì thêm ở đây)
        
        $flat_appeal_list[] = $item;
    }

} catch (Exception $e) { 
    error_log("Lỗi khi tải trang QL Phúc khảo: " . $e->getMessage());
    $error_message = "Lỗi CSDL khi tải dữ liệu phúc khảo.";
}

$page_title = 'Quản lý Phúc khảo: ' . htmlspecialchars($ky_thi_info['ten_ky_thi']);
require_once __DIR__ . '/../views/xem_quan_ly_phuc_khao.php';
?>