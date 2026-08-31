<?php
// File: src/controllers/api_ctv_add_violation.php (ĐÃ NÂNG CẤP LƯU THỜI GIAN)
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'Lỗi xác thực.']);
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

$db = get_db_connection();
$ctv_id = $_SESSION['student_id'];

try {
    $hoc_sinh_id = $data['hoc_sinh_id'] ?? null;
    if (empty($hoc_sinh_id)) {
        echo json_encode(['success' => false, 'message' => 'Lỗi: Không tìm thấy ID học sinh. Vui lòng đảm bảo đã tìm thấy Số CCCD hợp lệ trước khi thêm.']);
        exit();
    }
    
    $ho_ten_raw = trim($data['ho_ten'] ?? '');
    $ten_lop_raw = trim($data['ten_lop'] ?? '');
    $ghi_chu = trim($data['ghi_chu'] ?? 'KL&LĐ');
    
    // Thêm cột thoi_gian_nhap vào câu lệnh INSERT
    $sql = "INSERT INTO vi_pham_tam_thoi (tuan_hoc_id, nguoi_nhap_id, hoc_sinh_id, vi_pham_id, ngay_vi_pham, ghi_chu, raw_ho_ten, raw_ten_lop, trang_thai_gui, thoi_gian_nhap) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'nhap', ?)";
            
    $stmt_insert = $db->prepare($sql);
    $stmt_insert->execute([
        $data['tuan_hoc_id'],
        $ctv_id,
        $hoc_sinh_id,
        $data['vi_pham_id'],
        $data['ngay_vi_pham'],
        $ghi_chu,
        $ho_ten_raw,
        $ten_lop_raw,
        date('Y-m-d H:i:s') // Lấy thời gian hiện tại
    ]);
    $new_id = $db->lastInsertId();

    $stmt_get = $db->prepare("
        SELECT vptt.*, hs.ma_hoc_sinh, (CONCAT(hs.ho_dem, ' ', hs.ten)) as ho_ten_day_du, lh.ten_lop, chvp.ten_vi_pham
        FROM vi_pham_tam_thoi vptt
        LEFT JOIN quatrinh_hoc_tap qt ON vptt.hoc_sinh_id = qt.id
        LEFT JOIN ho_so_hoc_sinh hs ON qt.ma_hoc_sinh = hs.ma_hoc_sinh
        LEFT JOIN lop_hoc lh ON qt.lop_hoc_id = lh.id
        LEFT JOIN cau_hinh_vi_pham chvp ON vptt.vi_pham_id = chvp.id
        WHERE vptt.id = ?
    ");
    $stmt_get->execute([$new_id]);
    $new_violation_data = $stmt_get->fetch();

    echo json_encode(['success' => true, 'data' => $new_violation_data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}