<?php
// File: src/controllers/admin_hoc_sinh_tot_nghiep.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}
require_once __DIR__ . '/../../config/database.php';

$db = get_db_connection();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'api_reset_pass') {
    header('Content-Type: application/json');
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $student_id = $data['id'] ?? null;
        if (!$student_id) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy ID học sinh.']);
            exit();
        }
        try {
            $stmt = $db->prepare("SELECT ma_hoc_sinh FROM ho_so_hoc_sinh WHERE id = ?");
            $stmt->execute([$student_id]);
            $ma_hoc_sinh = $stmt->fetchColumn();
            if (!$ma_hoc_sinh) {
                echo json_encode(['success' => false, 'message' => 'Học sinh không tồn tại.']);
                exit();
            }
            $new_hash = password_hash($ma_hoc_sinh, PASSWORD_DEFAULT);
            $stmt_up = $db->prepare("UPDATE ho_so_hoc_sinh SET mat_khau_hash = ? WHERE id = ?");
            $stmt_up->execute([$new_hash, $student_id]);
            echo json_encode(['success' => true, 'message' => "Đã reset mật khẩu thành công về mặc định (Số CCCD: $ma_hoc_sinh)."]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
    }
    exit();
}

if ($action === 'api_get_details') {
    header('Content-Type: application/json');
    $student_id = $_GET['id'] ?? null;
    if (!$student_id) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy ID học sinh.']);
        exit();
    }
    try {
        $stmt = $db->prepare("SELECT id, ma_hoc_sinh, ho_dem, ten, ngay_sinh, sdt, email, nien_khoa, trang_thai_hoc_tap, nam_tot_nghiep, anh_the, anh_the_driver, anh_the_cloud_key, google_id FROM ho_so_hoc_sinh WHERE id = ?");
        $stmt->execute([$student_id]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$student) {
            echo json_encode(['success' => false, 'message' => 'Học sinh không tồn tại.']);
            exit();
        }
        
        $student['anh_the_url'] = get_student_avatar_url($student['anh_the'] ?? '', $student['anh_the_driver'] ?? 'local', $student['anh_the_cloud_key'] ?? null);

        $ma_hoc_sinh = $student['ma_hoc_sinh'];

        // Lấy thông tin lớp 10, 11, 12 và GVCN
        $stmt_qt = $db->prepare("
            SELECT lh.ten_lop, COALESCE(lh.gvcn_ten, 'Không rõ') as gvcn, nh.ten_nam_hoc 
            FROM quatrinh_hoc_tap qt
            LEFT JOIN lop_hoc lh ON qt.lop_hoc_id = lh.id
            LEFT JOIN nam_hoc nh ON qt.nam_hoc_id = nh.id
            WHERE qt.ma_hoc_sinh = ?
            ORDER BY nh.ngay_bat_dau ASC
        ");
        $stmt_qt->execute([$ma_hoc_sinh]);
        $quatrinh = $stmt_qt->fetchAll(PDO::FETCH_ASSOC);

        $lop10 = ['lop' => 'Chưa có', 'gvcn' => 'Không rõ', 'nam' => ''];
        $lop11 = ['lop' => 'Chưa có', 'gvcn' => 'Không rõ', 'nam' => ''];
        $lop12 = ['lop' => 'Chưa có', 'gvcn' => 'Không rõ', 'nam' => ''];

        foreach ($quatrinh as $qt) {
            $ten_lop = $qt['ten_lop'] ?? '';
            if (strpos($ten_lop, '10') === 0) {
                $lop10 = ['lop' => $ten_lop, 'gvcn' => $qt['gvcn'], 'nam' => $qt['ten_nam_hoc'] ?? ''];
            } elseif (strpos($ten_lop, '11') === 0) {
                $lop11 = ['lop' => $ten_lop, 'gvcn' => $qt['gvcn'], 'nam' => $qt['ten_nam_hoc'] ?? ''];
            } elseif (strpos($ten_lop, '12') === 0) {
                $lop12 = ['lop' => $ten_lop, 'gvcn' => $qt['gvcn'], 'nam' => $qt['ten_nam_hoc'] ?? ''];
            }
        }

        // Lần đăng nhập cuối & Số lần đăng nhập
        $stmt_login = $db->prepare("SELECT COUNT(*) as count, MAX(thoi_gian_dang_nhap) as last_login FROM lich_su_dang_nhap WHERE hoc_sinh_id = ?");
        $stmt_login->execute([$student_id]);
        $login_info = $stmt_login->fetch(PDO::FETCH_ASSOC);

        // Số lần tra cứu vi phạm
        $stmt_lookup = $db->prepare("SELECT COUNT(*) as count FROM nhat_ky_tra_cuu WHERE ma_tra_cuu = ?");
        $stmt_lookup->execute([$ma_hoc_sinh]);
        $lookup_count = $stmt_lookup->fetchColumn();

        // Lịch sử vi phạm qua các năm học
        $stmt_vp = $db->prepare("
            SELECT vp.ngay_vi_pham, chvp.ten_vi_pham, chvp.diem_tru, vp.ghi_chu, t.ten_tuan, nh.ten_nam_hoc, COALESCE(vp.raw_ten_lop, lh.ten_lop) as ten_lop 
            FROM vi_pham_hoc_sinh vp 
            JOIN quatrinh_hoc_tap qt ON vp.hoc_sinh_id = qt.id JOIN hoc_sinh hs ON hs.ma_hoc_sinh = qt.ma_hoc_sinh AND hs.nam_hoc_id = qt.nam_hoc_id 
            LEFT JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id 
            JOIN cau_hinh_vi_pham chvp ON vp.vi_pham_id = chvp.id 
            JOIN tuan_hoc t ON vp.tuan_hoc_id = t.id 
            LEFT JOIN nam_hoc nh ON t.nam_hoc_id = nh.id
            WHERE hs.ma_hoc_sinh = ?
            ORDER BY t.ngay_bat_dau DESC, vp.ngay_vi_pham DESC
        ");
        $stmt_vp->execute([$ma_hoc_sinh]);
        $vi_pham = $stmt_vp->fetchAll(PDO::FETCH_ASSOC);

        // Lịch sử khen thưởng qua các năm học
        $stmt_kt = $db->prepare("
            SELECT kt.ngay_khen_thuong, kt.ten_khen_thuong, kt.so_quyet_dinh, kt.cap_khen_thuong, kt.ghi_chu, nh.ten_nam_hoc, lh.ten_lop 
            FROM khen_thuong kt 
            JOIN quatrinh_hoc_tap qt ON kt.hoc_sinh_id = qt.id 
            JOIN ho_so_hoc_sinh hs ON qt.ma_hoc_sinh = hs.ma_hoc_sinh 
            LEFT JOIN lop_hoc lh ON qt.lop_hoc_id = lh.id 
            LEFT JOIN nam_hoc nh ON kt.nam_hoc_id = nh.id 
            WHERE kt.loai = 'ca_nhan' AND hs.ma_hoc_sinh = ? 
            ORDER BY kt.ngay_khen_thuong DESC, kt.id DESC
        ");
        $stmt_kt->execute([$ma_hoc_sinh]);
        $khen_thuong = $stmt_kt->fetchAll(PDO::FETCH_ASSOC);

        // Lịch sử hoạt động tham gia
        $stmt_hd = $db->prepare("
            SELECT hd.ten_hoat_dong, hddk.created_at as ngay_tham_gia, hddk.trang_thai_diem_danh, hddk.diem_thuc_te, hd.diem_tich_luy 
            FROM hoat_dong_dang_ky hddk
            JOIN hoat_dong hd ON hddk.hoat_dong_id = hd.id
            JOIN ho_so_hoc_sinh hs ON (hddk.hoc_sinh_id = hs.id OR hddk.hoc_sinh_id IN (SELECT id FROM quatrinh_hoc_tap WHERE ma_hoc_sinh = hs.ma_hoc_sinh))
            WHERE hs.ma_hoc_sinh = ?
            ORDER BY hddk.created_at DESC
        ");
        $stmt_hd->execute([$ma_hoc_sinh]);
        $hoat_dong = $stmt_hd->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'student' => $student,
            'lop10' => $lop10,
            'lop11' => $lop11,
            'lop12' => $lop12,
            'login_count' => $login_info['count'] ?? 0,
            'last_login' => $login_info['last_login'] ? date('d/m/Y H:i:s', strtotime($login_info['last_login'])) : 'Chưa đăng nhập',
            'lookup_count' => $lookup_count ?? 0,
            'vi_pham' => $vi_pham,
            'khen_thuong' => $khen_thuong,
            'hoat_dong' => $hoat_dong
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
    }
    exit();
}

// Xử lý bộ lọc cho giao diện chính
$filter_nien_khoa = $_GET['nien_khoa'] ?? 'all';
$filter_nam_tot_nghiep = $_GET['nam_tot_nghiep'] ?? 'all';
$filter_keyword = trim($_GET['keyword'] ?? '');

$where_clauses = ["hs.trang_thai_hoc_tap = 'da_tot_nghiep'"];
$params = [];

if ($filter_nien_khoa !== 'all') {
    $where_clauses[] = "hs.nien_khoa = ?";
    $params[] = $filter_nien_khoa;
}

if ($filter_nam_tot_nghiep !== 'all') {
    $where_clauses[] = "hs.nam_tot_nghiep = ?";
    $params[] = $filter_nam_tot_nghiep;
}

if ($filter_keyword !== '') {
    $where_clauses[] = "(hs.ma_hoc_sinh LIKE ? OR CONCAT(TRIM(hs.ho_dem), ' ', TRIM(hs.ten)) LIKE ?)";
    $like = '%' . $filter_keyword . '%';
    $params[] = $like;
    $params[] = $like;
}

$sql = "
    SELECT id, ma_hoc_sinh, ho_dem, ten, ngay_sinh, sdt, email, nien_khoa, trang_thai_hoc_tap, nam_tot_nghiep, anh_the, anh_the_driver, anh_the_cloud_key, google_id
    FROM ho_so_hoc_sinh hs
    WHERE " . implode(' AND ', $where_clauses) . "
    ORDER BY nam_tot_nghiep DESC, ten ASC, ho_dem ASC
";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$danh_sach_tot_nghiep = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh sách mã học sinh để truy vấn một lần toàn bộ quá trình học tập
$ds_ma = array_column($danh_sach_tot_nghiep, 'ma_hoc_sinh');
$quatrinh_map = [];
if (!empty($ds_ma)) {
    $placeholders = implode(',', array_fill(0, count($ds_ma), '?'));
    $stmt_qt = $db->prepare("
        SELECT qt.ma_hoc_sinh, lh.ten_lop, COALESCE(lh.gvcn_ten, 'Không rõ') as gvcn 
        FROM quatrinh_hoc_tap qt
        LEFT JOIN lop_hoc lh ON qt.lop_hoc_id = lh.id
        WHERE qt.ma_hoc_sinh IN ($placeholders)
    ");
    $stmt_qt->execute($ds_ma);
    $all_qt = $stmt_qt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($all_qt as $qt) {
        $ma = $qt['ma_hoc_sinh'];
        $ten_lop = $qt['ten_lop'] ?? '';
        if (!isset($quatrinh_map[$ma])) {
            $quatrinh_map[$ma] = ['lop10' => '', 'lop11' => '', 'lop12' => ''];
        }
        if (strpos($ten_lop, '10') === 0) $quatrinh_map[$ma]['lop10'] = $ten_lop;
        elseif (strpos($ten_lop, '11') === 0) $quatrinh_map[$ma]['lop11'] = $ten_lop;
        elseif (strpos($ten_lop, '12') === 0) $quatrinh_map[$ma]['lop12'] = $ten_lop;
    }
}

// Lấy danh sách niên khóa và năm tốt nghiệp để đưa vào bộ lọc select
$stmt_nk = $db->query("SELECT DISTINCT nien_khoa FROM ho_so_hoc_sinh WHERE trang_thai_hoc_tap = 'da_tot_nghiep' AND nien_khoa IS NOT NULL AND nien_khoa != '' ORDER BY nien_khoa DESC");
$ds_nien_khoa = $stmt_nk->fetchAll(PDO::FETCH_COLUMN);

$stmt_nam = $db->query("SELECT DISTINCT nam_tot_nghiep FROM ho_so_hoc_sinh WHERE trang_thai_hoc_tap = 'da_tot_nghiep' AND nam_tot_nghiep IS NOT NULL ORDER BY nam_tot_nghiep DESC");
$ds_nam_tot_nghiep = $stmt_nam->fetchAll(PDO::FETCH_COLUMN);

// Gọi file view
require_once __DIR__ . '/../views/admin_hoc_sinh_tot_nghiep.php';
?>
