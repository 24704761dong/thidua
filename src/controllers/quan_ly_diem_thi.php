<?php
// File: src/controllers/quan_ly_diem_thi.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../lib/exam_permissions.php';

// Bảo mật
if (!isset($_SESSION['user_id']) || !can_current_user_manage_exams()) {
    header('Location: /thidua/admin');
    exit();
}

require_once __DIR__ . '/../../config/database.php';
$db = get_db_connection();

$nam_hoc_id = $_SESSION['nam_hoc_id'] ?? $_SESSION['current_nam_hoc_id'] ?? null;
if (!$nam_hoc_id) {
    try {
        $stmt_nh = $db->query("SELECT id FROM nam_hoc WHERE is_mac_dinh = 1 LIMIT 1");
        $nam_hoc = $stmt_nh->fetch(PDO::FETCH_ASSOC);
        if ($nam_hoc) {
            $nam_hoc_id = (int)$nam_hoc['id'];
        }
    } catch (Exception $e) {}
}

// 1. Lấy danh sách tất cả các kỳ thi trong năm học để người dùng chọn
$all_exams = [];
try {
    $stmt_all = $db->prepare("SELECT id, ten_ky_thi, ngay_bat_dau FROM ky_thi WHERE nam_hoc_id = ? ORDER BY ngay_bat_dau DESC, id DESC");
    $stmt_all->execute([$nam_hoc_id]);
    $all_exams = $stmt_all->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $all_exams = [];
}

// 2. Xác định kỳ thi cần hiển thị
$ky_thi_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$ky_thi_id && !empty($all_exams)) {
    $ky_thi_id = (int)$all_exams[0]['id'];
}

if (!$ky_thi_id) {
    // Nếu chưa có kỳ thi nào
    $page_title = 'Quản Lý Điểm Thi';
    $ky_thi_info = null;
    $ds_diem_thi = [];
    $ds_lop_hoc = [];
    $all_exams = [];
    require_once __DIR__ . '/../views/xem_diem_thi.php';
    exit();
}

$ky_thi_info = null;
$ds_diem_thi = [];
$ds_lop_hoc = [];

// Định nghĩa các cột điểm
$diem_columns = [
    'diem_toan' => 'Toán', 'diem_van' => 'Văn', 'diem_ly' => 'Lý',
    'diem_hoa' => 'Hóa', 'diem_sinh' => 'Sinh', 'diem_su' => 'Sử',
    'diem_dia' => 'Địa', 'diem_gdktpl' => 'GDKTPL', 'diem_ngoai_ngu' => 'N.Ngữ',
    'diem_cn_nn' => 'CN-NN', 'dtb_mon' => 'ĐTB Môn', 'diem_xt_tn' => 'Điểm XT',
    'ket_qua' => 'Kết Quả'
];

$reviewed_columns = [
    'reviewed_toan', 'reviewed_van', 'reviewed_ly', 'reviewed_hoa', 'reviewed_sinh',
    'reviewed_su', 'reviewed_dia', 'reviewed_gdktpl', 'reviewed_ngoai_ngu',
    'reviewed_cn_nn', 'reviewed_dtb_mon', 'reviewed_diem_xt_tn', 'reviewed_ket_qua'
];

try {
    // 3. Lấy thông tin kỳ thi
    $stmt_ky_thi = $db->prepare("SELECT * FROM ky_thi WHERE id = ?");
    $stmt_ky_thi->execute([$ky_thi_id]);
    $ky_thi_info = $stmt_ky_thi->fetch(PDO::FETCH_ASSOC);

    if ($ky_thi_info) {
        // Đảm bảo schema bảng điểm thi tồn tại
        $db->exec("
            CREATE TABLE IF NOT EXISTS ky_thi_diem_thi (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ky_thi_hoc_sinh_id INT NOT NULL UNIQUE,
                diem_toan DECIMAL(4,2) NULL,
                diem_van DECIMAL(4,2) NULL,
                diem_ly DECIMAL(4,2) NULL,
                diem_hoa DECIMAL(4,2) NULL,
                diem_sinh DECIMAL(4,2) NULL,
                diem_su DECIMAL(4,2) NULL,
                diem_dia DECIMAL(4,2) NULL,
                diem_gdktpl DECIMAL(4,2) NULL,
                diem_ngoai_ngu DECIMAL(4,2) NULL,
                diem_cn_nn DECIMAL(4,2) NULL,
                dtb_mon DECIMAL(4,2) NULL,
                diem_xt_tn DECIMAL(4,2) NULL,
                ket_qua VARCHAR(50) NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_ktdt_kths (ky_thi_hoc_sinh_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci
        ");

        // 4. Lấy danh sách học sinh và điểm thi
        $select_cols = [];
        foreach (array_keys($diem_columns) as $col) {
            $select_cols[] = "ktdt.$col";
        }
        foreach ($reviewed_columns as $col) {
            $select_cols[] = "ktdt.$col";
        }
        $select_cols_str = !empty($select_cols) ? ', ' . implode(', ', $select_cols) : '';

        $sql_ds_diem = "
            SELECT
                kths.id as ky_thi_hoc_sinh_id,
                kths.so_bao_danh,
                kths.dang_ky_mon_thi,
                hs.ma_moet,
                hs.ma_hoc_sinh,
                hs.ho_dem,
                hs.ten,
                hs.ngay_sinh,
                lh.ten_lop,
                ktdt.id as diem_thi_id
                {$select_cols_str}
            FROM ky_thi_hoc_sinh kths
            JOIN hoc_sinh hs ON kths.hoc_sinh_id = hs.id
            JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id
            LEFT JOIN ky_thi_diem_thi ktdt ON kths.id = ktdt.ky_thi_hoc_sinh_id
            WHERE kths.ky_thi_id = ?
            ORDER BY lh.ten_lop ASC, hs.ten ASC, hs.ho_dem ASC
        ";

        try {
            $stmt_ds_diem = $db->prepare($sql_ds_diem);
            $stmt_ds_diem->execute([$ky_thi_id]);
            $ds_diem_thi = $stmt_ds_diem->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // Fallback nếu thiếu cột reviewed
            $sql_fallback = "
                SELECT
                    kths.id as ky_thi_hoc_sinh_id,
                    kths.so_bao_danh,
                    kths.dang_ky_mon_thi,
                    hs.ma_moet,
                    hs.ma_hoc_sinh,
                    hs.ho_dem,
                    hs.ten,
                    hs.ngay_sinh,
                    lh.ten_lop,
                    ktdt.id as diem_thi_id,
                    ktdt.diem_toan, ktdt.diem_van, ktdt.diem_ly, ktdt.diem_hoa, ktdt.diem_sinh,
                    ktdt.diem_su, ktdt.diem_dia, ktdt.diem_gdktpl, ktdt.diem_ngoai_ngu, ktdt.diem_cn_nn,
                    ktdt.dtb_mon, ktdt.diem_xt_tn, ktdt.ket_qua
                FROM ky_thi_hoc_sinh kths
                JOIN hoc_sinh hs ON kths.hoc_sinh_id = hs.id
                JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id
                LEFT JOIN ky_thi_diem_thi ktdt ON kths.id = ktdt.ky_thi_hoc_sinh_id
                WHERE kths.ky_thi_id = ?
                ORDER BY lh.ten_lop ASC, hs.ten ASC, hs.ho_dem ASC
            ";
            $stmt_fallback = $db->prepare($sql_fallback);
            $stmt_fallback->execute([$ky_thi_id]);
            $ds_diem_thi = $stmt_fallback->fetchAll(PDO::FETCH_ASSOC);
        }

        // 5. Lấy danh sách lớp tham gia kỳ thi
        $stmt_lop = $db->prepare("
            SELECT DISTINCT lh.ten_lop 
            FROM lop_hoc lh 
            JOIN hoc_sinh hs ON lh.id = hs.lop_hoc_id 
            JOIN ky_thi_hoc_sinh kths ON hs.id = kths.hoc_sinh_id 
            WHERE kths.ky_thi_id = ? 
            ORDER BY lh.ten_lop ASC
        ");
        $stmt_lop->execute([$ky_thi_id]);
        $ds_lop_hoc = $stmt_lop->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    error_log("Lỗi khi tải trang điểm thi: " . $e->getMessage());
}

$page_title = 'Quản Lý Điểm Thi' . ($ky_thi_info ? ': ' . htmlspecialchars($ky_thi_info['ten_ky_thi']) : '');
require_once __DIR__ . '/../views/xem_diem_thi.php';