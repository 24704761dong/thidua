<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
// File: src/controllers/tra_cuu_vi_pham.php (ĐÃ SỬA LỖI 2FA VÀ LỖI FATAL ERROR)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// === BẮT ĐẦU NÂNG CẤP SOCIAL LOGIN (SỬA LỖI FATAL ERROR) ===
// Nạp file cấu hình OAuth (định nghĩa hàm get_google_provider)
// ĐẶT LÊN TRƯỚC file database.php để đảm bảo file .env được nạp
require_once __DIR__ . '/../../config/oauth_providers.php'; 
// Bây giờ mới gọi hàm
$google_provider = get_google_provider();
$google_login_url = $google_provider->getAuthorizationUrl(['scope' => ['email', 'profile']]);
// === KẾT THÚC NÂNG CẤP ===

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../lib/recaptcha.php';
require_once __DIR__ . '/../lib/lookup_helpers.php';
require_once __DIR__ . '/../lib/helpers.php'; // Include helpers

$recaptchaConfig = require __DIR__ . '/../../config/recaptcha.php';

$db = get_db_connection();

try {
    // Lấy thông báo đang được bật (trang_thai = 1)
// Sửa câu lệnh SELECT này
    $stmt_thong_bao = $db->query("SELECT tieu_de, noi_dung, loai_thong_bao, hinh_anh, link_url, link_text FROM thong_bao_cong_khai WHERE trang_thai = 1 ORDER BY id DESC LIMIT 1");
    $public_notification = $stmt_thong_bao->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Nếu có lỗi (ví dụ: bảng chưa được tạo), bỏ qua và không hiển thị thông báo
    $public_notification = null;
}
// Lấy cài đặt hệ thống
$public_lookup_nam_hoc_id = get_setting($db, 'public_lookup_nam_hoc_id', 0);
$settings = get_all_settings($db, $public_lookup_nam_hoc_id);

require_once __DIR__ . '/../lib/nam_hoc.php';
$public_nam_hoc_ten = 'HỆ THỐNG TRA CỨU';
if ($public_lookup_nam_hoc_id > 0) {
    $nh = get_nam_hoc_by_id($db, $public_lookup_nam_hoc_id);
    if ($nh) {
        $public_nam_hoc_ten = 'NĂM HỌC ' . mb_strtoupper($nh['ten_nam_hoc'], 'UTF-8');
    }
}
$school_year_display = $public_nam_hoc_ten;

// --- LOGIC XỬ LÝ KHI FORM ĐƯỢC GỬI LÊN (METHOD POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $student_lookup_allowed = ($settings['allow_student_lookup'] ?? 'on') === 'on';
    $teacher_lookup_allowed = ($settings['allow_teacher_lookup'] ?? 'on') === 'on';

    $search_code = $_POST['search_code'] ?? null;
    $last_validated_code = $_SESSION['last_validated_search_code'] ?? null;
    $is_filtering = (isset($_POST['action']) && $_POST['action'] === 'filter');

    $captcha_is_valid = false;
    if ($is_filtering && $search_code === $last_validated_code) {
        $captcha_is_valid = true;
    } else {
        $recaptchaResponse = (string) ($_POST['g-recaptcha-response'] ?? '');
        
        $recaptcha_valid = true;
        if (!empty($recaptchaConfig['enabled'])) {
            $recaptcha_valid = $recaptchaResponse !== '' && verify_recaptcha(
                $recaptchaResponse,
                (string) ($recaptchaConfig['secret_key'] ?? ''),
                $_SERVER['REMOTE_ADDR'] ?? null
            );
        }

        if ($recaptcha_valid) {
            $captcha_is_valid = true;
            $_SESSION['last_validated_search_code'] = $search_code;
        }
    }

    if (!$captcha_is_valid) {
        $_SESSION['lookup_info'] = "<strong class='text-danger'>Lỗi: Xác thực bảo mật không hợp lệ. Vui lòng tích vào ô reCAPTCHA và thử lại.</strong>";
        $_SESSION['lookup_results'] = [];
        $_SESSION['lookup_commendations'] = []; // Bổ sung
        $_SESSION['search_code'] = $search_code;
        unset($_SESSION['last_validated_search_code']);
    } else {
        $tuan_id = $_POST['tuan_id'] ?? 'all';
        $results = [];
        $commendations = []; // Bổ sung
        $search_info = null;
        unset($_SESSION['student_info'], $_SESSION['teacher_info']);

        // Khởi tạo biến để ghi log
        $found = false;
        $loai_tra_cuu = 'khong_hop_le';

        // ĐẢM BẢO VIEW hoc_sinh TRẢ VỀ DỮ LIỆU CỦA NĂM HỌC TRA CỨU CÔNG KHAI
        if ($public_lookup_nam_hoc_id > 0) {
            $db->exec("SET @current_nam_hoc_id = " . (int)$public_lookup_nam_hoc_id);
        }

        // Logic tra cứu được nâng cấp
        $stmt_hs_check = $db->prepare("SELECT id, lop_hoc_id FROM hoc_sinh WHERE ma_hoc_sinh = ?");
        $stmt_hs_check->execute([$search_code]);
        $is_student_code = $stmt_hs_check->fetch(PDO::FETCH_ASSOC);

        if ($public_lookup_nam_hoc_id > 0) {
            $stmt_gv_check = $db->prepare("SELECT id FROM lop_hoc WHERE gvcn_ma = ? AND nam_hoc_id = ?");
            $stmt_gv_check->execute([$search_code, $public_lookup_nam_hoc_id]);
        } else {
            $stmt_gv_check = $db->prepare("SELECT id FROM lop_hoc WHERE gvcn_ma = ?");
            $stmt_gv_check->execute([$search_code]);
        }
        $is_teacher_code = $stmt_gv_check->fetch(PDO::FETCH_ASSOC);

        if ($is_student_code) { // Nếu mã này là của học sinh
            $found = true;
            $loai_tra_cuu = 'hoc_sinh';
            if (!$student_lookup_allowed) {
                $search_info = "Hiện tại hệ thống không cho phép tra cứu bằng mã tra cứu. Vui lòng thử lại sau.";
            } else {
                // SỬA LỖI: Dùng LEFT JOIN để luôn trả về thông tin học sinh, kể cả khi chưa có lớp
                $stmt_hs = $db->prepare("SELECT hs.id, hs.ho_dem, hs.ten, hs.ma_hoc_sinh, hs.ngay_sinh, hs.lop_hoc_id, lh.ten_lop, lh.gvcn_ten, hs.trang_thai_hoc_tap FROM hoc_sinh hs LEFT JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id WHERE hs.id = ?");
                $stmt_hs->execute([$is_student_code['id']]);
                $student = $stmt_hs->fetch(PDO::FETCH_ASSOC);
                
                // === NÂNG CẤP: LẤY KHEN THƯỞNG CÁ NHÂN VÀ TẬP THỂ CỦA LỚP ===
                $sql_kt = "
                    SELECT *, 'ca_nhan' as loai_hien_thi FROM khen_thuong 
                    WHERE (hoc_sinh_id = :hoc_sinh_id OR hoc_sinh_id IN (SELECT id FROM quatrinh_hoc_tap WHERE ma_hoc_sinh = :ma_hs)) AND loai = 'ca_nhan'
                    UNION ALL
                    SELECT *, 'tap_the' as loai_hien_thi FROM khen_thuong 
                    WHERE lop_hoc_id = :lop_hoc_id AND loai = 'tap_the'
                ";
                
                // Nếu đang lọc theo năm, chỉ lấy khen thưởng trong thời gian của năm đó
                if ($public_lookup_nam_hoc_id > 0) {
                    $stmt_nh = $db->prepare("SELECT ngay_bat_dau, ngay_ket_thuc FROM nam_hoc WHERE id = ?");
                    $stmt_nh->execute([$public_lookup_nam_hoc_id]);
                    $nam_hoc = $stmt_nh->fetch();
                    if ($nam_hoc && $nam_hoc['ngay_bat_dau'] && $nam_hoc['ngay_ket_thuc']) {
                        $sql_kt = "SELECT * FROM ($sql_kt) AS sub WHERE ngay_khen_thuong BETWEEN :ngay_bd AND :ngay_kt";
                    }
                }
                
                $sql_kt .= " ORDER BY ngay_khen_thuong DESC";
                
                $stmt_kt = $db->prepare($sql_kt);
                $params_kt = [
                    ':hoc_sinh_id' => $student['id'],
                    ':ma_hs' => $student['ma_hoc_sinh'] ?? '',
                    ':lop_hoc_id' => $student['lop_hoc_id']
                ];
                
                if ($public_lookup_nam_hoc_id > 0 && isset($nam_hoc) && $nam_hoc['ngay_bat_dau']) {
                    $params_kt[':ngay_bd'] = $nam_hoc['ngay_bat_dau'];
                    $params_kt[':ngay_kt'] = $nam_hoc['ngay_ket_thuc'];
                }
                
                $stmt_kt->execute($params_kt);
                $commendations = $stmt_kt->fetchAll(PDO::FETCH_ASSOC);
                
                // === BẮT ĐẦU NÂNG CẤP KIỂM TRA SINH NHẬT ===
                date_default_timezone_set('Asia/Ho_Chi_Minh');
                $is_birthday = false;
                if (!empty($student['ngay_sinh'])) {
                    $birthday_month_day = substr($student['ngay_sinh'], 0, 5);
                    $current_month_day = date('d/m');
                    if ($birthday_month_day === $current_month_day) {
                        $is_birthday = true;
                    }
                }
                $_SESSION['is_birthday'] = $is_birthday;
                // === KẾT THÚC NÂNG CẤP ===
                
                $_SESSION['student_info'] = $student;
                $search_info = "Kết quả tra cứu cho học sinh.";
                $sql = "SELECT vp.ngay_vi_pham, chvp.ten_vi_pham, vp.ghi_chu, t.ten_tuan, (CONCAT(hs.ho_dem, ' ', hs.ten)) as ho_ten, COALESCE(vp.raw_ten_lop, lh.ten_lop) as ten_lop FROM vi_pham_hoc_sinh vp JOIN quatrinh_hoc_tap qt ON vp.hoc_sinh_id = qt.id JOIN hoc_sinh hs ON hs.ma_hoc_sinh = qt.ma_hoc_sinh AND hs.nam_hoc_id = qt.nam_hoc_id JOIN lop_hoc lh ON qt.lop_hoc_id = lh.id JOIN cau_hinh_vi_pham chvp ON vp.vi_pham_id = chvp.id JOIN tuan_hoc t ON vp.tuan_hoc_id = t.id WHERE hs.id = ?";
                $params = [$student['id']];
                if ($public_lookup_nam_hoc_id > 0) { $sql .= " AND t.nam_hoc_id = ?"; $params[] = $public_lookup_nam_hoc_id; }
                if ($tuan_id !== 'all') { $sql .= " AND vp.tuan_hoc_id = ?"; $params[] = $tuan_id; }
                $sql .= " ORDER BY t.ngay_bat_dau DESC, vp.ngay_vi_pham DESC";
                $stmt_vp = $db->prepare($sql);
                $stmt_vp->execute($params);
                $results = $stmt_vp->fetchAll();
            }
        } elseif ($is_teacher_code) { // Nếu mã này là của giáo viên
            $found = true;
            $loai_tra_cuu = 'giao_vien';
            if (!$teacher_lookup_allowed) {
                $search_info = "Hiện tại hệ thống không cho phép tra cứu bằng Mã Giáo Viên. Vui lòng thử lại sau.";
            } else {
                // SỬA LỖI: Dùng LEFT JOIN để luôn trả về thông tin GVCN
                $stmt_gv = $db->prepare("SELECT lh.id, lh.ten_lop, lh.gvcn_ten, lh.gvcn_ma, lh.gvcn_email, lh.gvcn_ngay_sinh, COUNT(hs.id) as si_so FROM lop_hoc lh LEFT JOIN hoc_sinh hs ON lh.id = hs.lop_hoc_id AND hs.trang_thai_hoc_tap = 'dang_hoc' WHERE lh.id = ? GROUP BY lh.id");
                $stmt_gv->execute([$is_teacher_code['id']]);
                $class_info = $stmt_gv->fetch(PDO::FETCH_ASSOC);

                // ================== BẮT ĐẦU SỬA LỖI ==================
                $sql_kt_gv = "
                    SELECT kt.*, (CONCAT(hs.ho_dem, ' ', hs.ten)) as ho_ten_hs,
                           CASE kt.loai WHEN 'tap_the' THEN 'tap_the' ELSE 'ca_nhan' END as loai_hien_thi
                    FROM khen_thuong kt
                    LEFT JOIN hoc_sinh hs ON kt.hoc_sinh_id = hs.id
                    WHERE (kt.lop_hoc_id = :lop_id1 OR kt.hoc_sinh_id IN (SELECT id FROM hoc_sinh WHERE lop_hoc_id = :lop_id2))
                ";
                
                $params_gv = [
                    ':lop_id1' => $class_info['id'],
                    ':lop_id2' => $class_info['id']
                ];

                if ($public_lookup_nam_hoc_id > 0) {
                    $stmt_nh = $db->prepare("SELECT ngay_bat_dau, ngay_ket_thuc FROM nam_hoc WHERE id = ?");
                    $stmt_nh->execute([$public_lookup_nam_hoc_id]);
                    $nam_hoc = $stmt_nh->fetch();
                    if ($nam_hoc && $nam_hoc['ngay_bat_dau'] && $nam_hoc['ngay_ket_thuc']) {
                        $sql_kt_gv .= " AND kt.ngay_khen_thuong BETWEEN :ngay_bd AND :ngay_kt";
                        $params_gv[':ngay_bd'] = $nam_hoc['ngay_bat_dau'];
                        $params_gv[':ngay_kt'] = $nam_hoc['ngay_ket_thuc'];
                    }
                }
                
                $sql_kt_gv .= " ORDER BY kt.loai DESC, kt.ngay_khen_thuong DESC";

                $stmt_kt_lop = $db->prepare($sql_kt_gv);
                $stmt_kt_lop->execute($params_gv);
                // ================== KẾT THÚC SỬA LỖI ==================
                $commendations = $stmt_kt_lop->fetchAll(PDO::FETCH_ASSOC);



                // === BẮT ĐẦU NÂNG CẤP KIỂM TRA SINH NHẬT GIÁO VIÊN ===
                date_default_timezone_set('Asia/Ho_Chi_Minh');
                $is_birthday = false;
                if (!empty($class_info['gvcn_ngay_sinh'])) {
                    $birthday_month_day = substr($class_info['gvcn_ngay_sinh'], 0, 5);
                    $current_month_day = date('d/m');
                    if ($birthday_month_day === $current_month_day) {
                        $is_birthday = true;
                    }
                }
                $_SESSION['is_birthday'] = $is_birthday;
                // === KẾT THÚC NÂNG CẤP ===

                $_SESSION['teacher_info'] = $class_info;
                $search_info = "Kết quả tra cứu cho GVCN.";
                $sql = "SELECT vp.ngay_vi_pham, chvp.ten_vi_pham, vp.ghi_chu, t.ten_tuan, (CONCAT(hs.ho_dem, ' ', hs.ten)) as ho_ten, COALESCE(vp.raw_ten_lop, lh.ten_lop) as ten_lop FROM vi_pham_hoc_sinh vp JOIN quatrinh_hoc_tap qt ON vp.hoc_sinh_id = qt.id JOIN hoc_sinh hs ON hs.ma_hoc_sinh = qt.ma_hoc_sinh AND hs.nam_hoc_id = qt.nam_hoc_id JOIN lop_hoc lh ON qt.lop_hoc_id = lh.id JOIN cau_hinh_vi_pham chvp ON vp.vi_pham_id = chvp.id JOIN tuan_hoc t ON vp.tuan_hoc_id = t.id WHERE qt.lop_hoc_id = ?";
                $params = [$class_info['id']];
                if ($public_lookup_nam_hoc_id > 0) { $sql .= " AND t.nam_hoc_id = ?"; $params[] = $public_lookup_nam_hoc_id; }
                if ($tuan_id !== 'all') { $sql .= " AND vp.tuan_hoc_id = ?"; $params[] = $tuan_id; }
                $sql .= " ORDER BY t.ngay_bat_dau DESC, vp.ngay_vi_pham DESC, ho_ten ASC";
                $stmt_vp = $db->prepare($sql);
                $stmt_vp->execute($params);
                $results = $stmt_vp->fetchAll();
            }
        } else { // Nếu mã không tồn tại
            $found = false;
            $loai_tra_cuu = 'khong_hop_le';
            $search_info = "Không tìm thấy thông tin \"" . htmlspecialchars($search_code) . "\" trong dữ liệu nhà trường, vui lòng kiểm tra và nhập lại.";
        }
        
        // ===== BẮT ĐẦU PHẦN BỔ SUNG: GHI LẠI LỊCH SỬ TRA CỨU =====
        if (!$is_filtering) {
            try {
                $stmt_log = $db->prepare(
                    "INSERT INTO nhat_ky_tra_cuu (ma_tra_cuu, loai_tra_cuu, ket_qua_tim_thay, thoi_gian_tra_cuu, dia_chi_ip, user_agent) 
                     VALUES (?, ?, ?, ?, ?, ?)"
                );
                $stmt_log->execute([
                    $search_code,
                    $loai_tra_cuu,
                    $found ? 1 : 0,
                    date('Y-m-d H:i:s'),
                    $_SERVER['REMOTE_ADDR'] ?? 'N/A',
                    $_SERVER['HTTP_USER_AGENT'] ?? 'N/A'
                ]);
            } catch (PDOException $e) {
                error_log("Lỗi khi ghi Nhật kỳ tra cứu: " . $e->getMessage());
            }
        }
        $violation_summary = [];
    if ($is_teacher_code && !empty($class_info['id'])) {
        try {
            $sql_summary = "
                SELECT 
                    chvp.nhom_vi_pham, 
                    COUNT(vp.id) as so_luong
                FROM vi_pham_hoc_sinh vp
                JOIN quatrinh_hoc_tap qt ON vp.hoc_sinh_id = qt.id
                JOIN hoc_sinh hs ON hs.ma_hoc_sinh = qt.ma_hoc_sinh AND hs.nam_hoc_id = qt.nam_hoc_id
                JOIN cau_hinh_vi_pham chvp ON vp.vi_pham_id = chvp.id
                WHERE qt.lop_hoc_id = :class_id
            ";
            
            $params_summary = [':class_id' => $class_info['id']];

            if ($tuan_id !== 'all') {
                $sql_summary .= " AND vp.tuan_hoc_id = :tuan_id";
                $params_summary[':tuan_id'] = $tuan_id;
            }

            $sql_summary .= " GROUP BY chvp.nhom_vi_pham ORDER BY so_luong DESC";
            
            $stmt_summary = $db->prepare($sql_summary);
            $stmt_summary->execute($params_summary);
            $violation_summary = $stmt_summary->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Lỗi khi thống kê vi phạm theo nhóm: " . $e->getMessage());
            // Không làm gì thêm, mảng $violation_summary sẽ rỗng và không hiển thị ở view
        }
    }
    $_SESSION['violation_summary'] = $violation_summary; // << Lưu vào session
        // ===== KẾT THÚC PHẦN BỔ SUNG =====
        
        $_SESSION['lookup_info'] = $search_info;
        $_SESSION['lookup_results'] = $results;
        $_SESSION['lookup_commendations'] = $commendations; // Bổ sung
        $_SESSION['search_code'] = $search_code;
        $_SESSION['tuan_id'] = $tuan_id;
    }

    if ($is_filtering && tracuu_is_ajax_filter_request()) {
        require_once __DIR__ . '/../lib/lookup_helpers.php';
        header('Content-Type: text/html; charset=UTF-8');
        if (!$captcha_is_valid) {
            http_response_code(400);
            echo '<div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">Không thể lọc dữ liệu. Vui lòng tra cứu lại.</div>';
            exit();
        }
        $student_info = $_SESSION['student_info'] ?? null;
        $teacher_info = $_SESSION['teacher_info'] ?? null;
        $results = $_SESSION['lookup_results'] ?? [];
        $commendations = $_SESSION['lookup_commendations'] ?? [];
        $violation_summary = $_SESSION['violation_summary'] ?? [];
        $stmt_tuan_ajax = $db->query("SELECT id, ten_tuan FROM tuan_hoc ORDER BY ngay_bat_dau DESC");
        $danh_sach_tuan = $stmt_tuan_ajax->fetchAll();
        $school_year = $school_year_display;
        $current_scope_label = label_tuan_hien_tai($danh_sach_tuan, $tuan_id ?? 'all', $school_year);
        require __DIR__ . '/../views/partials/tracuu_filter_results.php';
        exit();
    }

    header('Location: /thidua/tracuu');
    exit();
}

// --- LOGIC HIỂN THỊ KHI TẢI TRANG (METHOD GET) ---
$search_info = $_SESSION['lookup_info'] ?? null;
$results = $_SESSION['lookup_results'] ?? [];
$commendations = $_SESSION['lookup_commendations'] ?? []; // Bổ sung
$search_code = $_SESSION['search_code'] ?? null;
$tuan_id = $_SESSION['tuan_id'] ?? 'all';
$student_info = $_SESSION['student_info'] ?? null;
$teacher_info = $_SESSION['teacher_info'] ?? null;
$is_search_performed = ($search_code !== null); 
$is_birthday = $_SESSION['is_birthday'] ?? false; 
// GIỮ NGUYÊN LOGIC GỐC: KHÔNG UNSET `is_birthday` ở đây.
unset($_SESSION['lookup_info'], $_SESSION['lookup_results'], $_SESSION['search_code'], $_SESSION['tuan_id'], $_SESSION['student_info'], $_SESSION['teacher_info'], $_SESSION['lookup_commendations'], $_SESSION['is_birthday']);

unset($_SESSION['lookup_info'], $_SESSION['lookup_results'], $_SESSION['lookup_commendations'], $_SESSION['search_code'], $_SESSION['tuan_id'], $_SESSION['student_info'], $_SESSION['teacher_info']);

$stmt_tuan = $db->query("SELECT id, ten_tuan FROM tuan_hoc ORDER BY ngay_bat_dau DESC");
$danh_sach_tuan = $stmt_tuan->fetchAll();

require_once __DIR__ . '/../lib/portal_views.php';
$tracuuViewCount = thidua_increment_tracuu_view_count($db);

// SỬA LỖI: Gọi đúng file view
require_once __DIR__ . '/../views/dang_nhap.php';