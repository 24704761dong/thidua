<?php
// File: src/controllers/api_get_dashboard_stats.php (BẢN ĐẦY ĐỦ CUỐI CÙNG - XỬ LÝ ĐỒNG HẠNG)
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit();
}

// Nạp cả hai file kết nối CSDL
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../lib/helpers.php';

try {
    // 1. Kết nối đến CSDL chính và lấy hầu hết dữ liệu
    $db = get_db_connection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stats = [];
    
    // Lấy nam_hoc_id hiện tại (từ session hoặc mặc định)
    $nam_hoc_id = $_SESSION['nam_hoc_id'] ?? null;
    if (!$nam_hoc_id) {
        $nam_hoc_id = $db->query("SELECT id FROM nam_hoc WHERE is_mac_dinh = 1")->fetchColumn();
    }

    $stats['total_students'] = $db->query("SELECT COUNT(id) FROM hoc_sinh WHERE nam_hoc_id = " . (int)$nam_hoc_id)->fetchColumn();
    $stats['total_teachers'] = $db->query("SELECT COUNT(DISTINCT gvcn_ma) FROM lop_hoc WHERE nam_hoc_id = " . (int)$nam_hoc_id . " AND gvcn_ma IS NOT NULL AND gvcn_ma != ''")->fetchColumn();
    $stats['students_with_email'] = $db->query("SELECT COUNT(id) FROM hoc_sinh WHERE nam_hoc_id = " . (int)$nam_hoc_id . " AND email IS NOT NULL AND email != ''")->fetchColumn();
    $stats['students_receiving_mail'] = $db->query("SELECT COUNT(id) FROM hoc_sinh WHERE nam_hoc_id = " . (int)$nam_hoc_id . " AND nhan_thong_bao_vi_pham = 1")->fetchColumn();
    $stats['total_ctv'] = $db->query("SELECT COUNT(id) FROM hoc_sinh WHERE nam_hoc_id = " . (int)$nam_hoc_id . " AND quyen_truy_cap IS NOT NULL AND quyen_truy_cap != '{}' AND quyen_truy_cap != '[]'")->fetchColumn();
    
    // Dữ liệu theo hệ thống (không lọc theo năm học)
    $stats['total_lookups'] = $db->query("SELECT COUNT(id) FROM nhat_ky_tra_cuu")->fetchColumn();
    $five_minutes_ago = time() - (5 * 60);
    $stmt_active = $db->prepare("SELECT COUNT(DISTINCT session_id) FROM phien_truy_cap WHERE last_activity > ?");
    $stmt_active->execute([$five_minutes_ago]);
    $stats['active_now'] = $stmt_active->fetchColumn();
    
    // Các yêu cầu chờ duyệt (liên kết qua tuan_hoc_id để lọc theo nam_hoc_id)
    $pending_violations = $db->query("SELECT COUNT(v.id) FROM vi_pham_tam_thoi v JOIN tuan_hoc t ON v.tuan_hoc_id = t.id WHERE t.nam_hoc_id = " . (int)$nam_hoc_id . " AND v.trang_thai_gui = 'da_gui'")->fetchColumn();
    $pending_attendance = $db->query("SELECT COUNT(d.id) FROM diem_danh_chi_tiet d JOIN tuan_hoc t ON d.tuan_hoc_id = t.id WHERE t.nam_hoc_id = " . (int)$nam_hoc_id . " AND d.trang_thai = 'cho_duyet'")->fetchColumn();
    $pending_duty = $db->query("SELECT COUNT(d.id) FROM dang_ky_truc_tuan d JOIN tuan_hoc t ON d.tuan_hoc_id = t.id WHERE t.nam_hoc_id = " . (int)$nam_hoc_id . " AND d.trang_thai = 'Chờ duyệt' AND d.trang_thai_luu_tru = 0")->fetchColumn();
    $stats['pending_requests'] = $pending_violations + $pending_attendance + $pending_duty;

    // Dung lượng ổ cục bộ
    $free = @disk_free_space(__DIR__ . '/../../');
    $total = @disk_total_space(__DIR__ . '/../../');
    if ($free !== false && $total !== false && $total > 0) {
        $used = $total - $free;
        $stats['disk'] = [
            'used_bytes' => $used,
            'total_bytes' => $total,
            'used_percent' => round(($used / $total) * 100, 2)
        ];
    } else {
        $stats['disk'] = null;
    }

    // Dung lượng R2 (xấp xỉ): liệt kê tối đa 5.000 object để cộng size, tránh quá nặng
    $stats['r2'] = null;
    $stats['r2_error'] = null;
    try {
        require_once __DIR__ . '/../lib/StorageService.php';
        $storage = new StorageService();
        $clientProp = (new ReflectionClass($storage))->getProperty('client');
        $clientProp->setAccessible(true);
        $s3 = $clientProp->getValue($storage);
        $bucketProp = (new ReflectionClass($storage))->getProperty('bucket');
        $bucketProp->setAccessible(true);
        $bucketName = $bucketProp->getValue($storage);

        $maxObjects = 5000; // giới hạn để tránh tốn thời gian
        $count = 0; $bytes = 0; $isTruncated = false; $token = null;
        $start = microtime(true);
        do {
            $resp = $s3->listObjectsV2([
                'Bucket' => $bucketName,
                'ContinuationToken' => $token,
                'MaxKeys' => 1000,
            ]);
            $contents = $resp['Contents'] ?? [];
            foreach ($contents as $obj) {
                $bytes += ($obj['Size'] ?? 0);
                $count++;
                if ($count >= $maxObjects) {
                    $isTruncated = true;
                    break 2;
                }
            }
            $token = $resp['NextContinuationToken'] ?? null;
            $isTruncated = $resp['IsTruncated'] ?? false;
        } while ($isTruncated && $token && (microtime(true) - $start) < 5);

        $stats['r2'] = [
            'total_bytes' => $bytes,
            'object_count' => $count,
            'approx' => $isTruncated,
        ];
    } catch (Throwable $e) {
        $stats['r2'] = null;
        $stats['r2_error'] = $e->getMessage();
    }

    // Lấy dung lượng OneDrive
    $stats['onedrive'] = null;
    $stats['onedrive_error'] = null;
    try {
        $ms_email = $_ENV['MS_ONEDRIVE_BACKUP_EMAIL'] ?? '';
        if (!empty($ms_email) && !empty($_ENV['MS_TENANT_ID'])) {
            require_once __DIR__ . '/../../vendor/autoload.php';
            $client = new \GuzzleHttp\Client(['timeout' => 10, 'verify' => false]);
            $res = $client->post('https://login.microsoftonline.com/' . $_ENV['MS_TENANT_ID'] . '/oauth2/v2.0/token', [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $_ENV['MS_CLIENT_ID'],
                    'client_secret' => $_ENV['MS_CLIENT_SECRET'],
                    'scope' => 'https://graph.microsoft.com/.default',
                ]
            ]);
            $data = json_decode($res->getBody(), true);
            $token = $data['access_token'];
            
            $resDrive = $client->get('https://graph.microsoft.com/v1.0/users/' . rawurlencode($ms_email) . '/drive', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json'
                ]
            ]);
            $driveData = json_decode($resDrive->getBody(), true);
            if (isset($driveData['quota'])) {
                $stats['onedrive'] = [
                    'total_bytes' => $driveData['quota']['total'],
                    'used_bytes' => $driveData['quota']['used'],
                    'remaining_bytes' => $driveData['quota']['remaining'],
                    'used_percent' => round(($driveData['quota']['used'] / $driveData['quota']['total']) * 100, 2)
                ];
            }
        }
    } catch (Throwable $e) {
        $stats['onedrive_error'] = $e->getMessage();
    }

    // 2. Mở kết nối RIÊNG BIỆT đến CSDL log chỉ để lấy tổng lượt truy cập
    $stats['total_visits'] = 0; // Đặt giá trị mặc định
    try {
        
        // Sửa tên bảng theo file ví dụ: `he_thong_thong_ke` và `stat_key`
        $stmt_total = $db->query("SELECT stat_value FROM he_thong_thong_ke WHERE stat_key = 'tong_so_luot_truy_cap'");
        if ($stmt_total) {
            $stats['total_visits'] = $stmt_total->fetchColumn() ?: 0;
        }

    } catch (Throwable $e) {
        // Nếu file log lỗi, không làm ảnh hưởng đến các số liệu khác
    }

    // 3. Tính toán danh sách sinh nhật sắp tới (trong vòng 7 ngày)
    $stmt_bday = $db->query("SELECT id, ho_dem, ten, ngay_sinh FROM hoc_sinh WHERE nam_hoc_id = " . (int)$nam_hoc_id . " AND ngay_sinh IS NOT NULL");
    $all_students = $stmt_bday->fetchAll(PDO::FETCH_ASSOC);
    $upcoming_birthdays = [];
    $today_md = date('md');
    $next_week_md = date('md', strtotime('+7 days'));

    foreach ($all_students as $st) {
        $dob_md = date('md', strtotime($st['ngay_sinh']));
        $is_upcoming = false;
        
        if ($today_md <= $next_week_md) {
            if ($dob_md >= $today_md && $dob_md <= $next_week_md) $is_upcoming = true;
        } else {
            // Trường hợp vắt ngang qua năm mới (vd: 28/12 đến 04/01)
            if ($dob_md >= $today_md || $dob_md <= $next_week_md) $is_upcoming = true;
        }

        if ($is_upcoming) {
            $sort_key = ($dob_md < $today_md) ? '1'.$dob_md : '0'.$dob_md;
            $upcoming_birthdays[] = [
                'ho_ten' => trim($st['ho_dem'] . ' ' . $st['ten']),
                'ngay_sinh_formatted' => date('d/m', strtotime($st['ngay_sinh'])),
                'sort_key' => $sort_key
            ];
        }
    }
    usort($upcoming_birthdays, function($a, $b) { return strcmp($a['sort_key'], $b['sort_key']); });
    $stats['upcoming_birthdays'] = array_slice($upcoming_birthdays, 0, 15);


    // 4. Tiếp tục tính toán các thông số còn lại từ CSDL chính
    $stmt_weeks = $db->query("SELECT * FROM tuan_hoc ORDER BY ngay_bat_dau DESC LIMIT 2");
    $weeks = $stmt_weeks->fetchAll(PDO::FETCH_ASSOC);
    $current_week = $weeks[0] ?? null;
    $previous_week = $weeks[1] ?? null;

    // Khởi tạo là MẢNG để chứa nhiều lớp
    $stats['top_class_last_week'] = [];
    $stats['bottom_class_last_week'] = [];
    $stats['previous_week_violations'] = 0;
    $stats['current_week_violations'] = 0;

    if ($current_week) {
        $stmt_curr_violations = $db->prepare("SELECT COUNT(id) FROM vi_pham_hoc_sinh WHERE tuan_hoc_id = ?");
        $stmt_curr_violations->execute([$current_week['id']]);
        $stats['current_week_violations'] = (int)($stmt_curr_violations->fetchColumn() ?? 0);
    }

    if ($previous_week) {
        $tuan_id = $previous_week['id'];
        $stmt_prev_violations = $db->prepare("SELECT COUNT(id) FROM vi_pham_hoc_sinh WHERE tuan_hoc_id = ?");
        $stmt_prev_violations->execute([$tuan_id]);
        $stats['previous_week_violations'] = $stmt_prev_violations->fetchColumn();

        $stmt_settings = $db->query("SELECT setting_key, setting_value FROM he_thong_cai_dat WHERE setting_key LIKE 'report_%'");
        $settings_raw = $stmt_settings->fetchAll(PDO::FETCH_KEY_PAIR);
        $settings = [
            'report_diem_tiet_tot' => (float)($settings_raw['report_diem_tiet_tot'] ?? 1),
            'report_diem_tiet_tb' => (float)($settings_raw['report_diem_tiet_tb'] ?? 0),
            'report_sdb_tt_tich' => (float)($settings_raw['report_sdb_tt_tich'] ?? 0),
            'report_sdb_ck_tich' => (float)($settings_raw['report_sdb_ck_tich'] ?? 0),
            'report_sdb_nk_tich' => (float)($settings_raw['report_sdb_nk_tich'] ?? 0),
            'report_nhat_ky_tich' => (float)($settings_raw['report_nhat_ky_tich'] ?? 0),
            'report_sdb_tt_khong' => (float)($settings_raw['report_sdb_tt_khong'] ?? 0),
            'report_sdb_ck_khong' => (float)($settings_raw['report_sdb_ck_khong'] ?? 0),
            'report_sdb_nk_khong' => (float)($settings_raw['report_sdb_nk_khong'] ?? 0),
            'report_nhat_ky_khong' => (float)($settings_raw['report_nhat_ky_khong'] ?? 0),
            'report_sdb_use_tt' => ($settings_raw['report_sdb_use_tt'] ?? 'off') === 'on',
            'report_sdb_use_ck' => ($settings_raw['report_sdb_use_ck'] ?? 'off') === 'on',
            'report_sdb_use_nk' => ($settings_raw['report_sdb_use_nk'] ?? 'off') === 'on',
            'report_sdb_use_nhat_ky' => ($settings_raw['report_sdb_use_nhat_ky'] ?? 'off') === 'on',
            'report_tru_vang_p' => (float)($settings_raw['report_tru_vang_p'] ?? 0),
            'report_tru_vang_kp' => (float)($settings_raw['report_tru_vang_kp'] ?? -1),
        ];

        $conditions_kxtd = $db->query("SELECT * FROM dieu_kien_kxtd WHERE kich_hoat = 1")->fetchAll(PDO::FETCH_ASSOC);
        $lop_hoc = $db->query("SELECT id, ten_lop FROM lop_hoc")->fetchAll(PDO::FETCH_ASSOC);
        $report_data = [];
        
        foreach ($lop_hoc as $lop) {
            $data = ['lop' => $lop['ten_lop'], 'tong_diem' => 0, 'kxtd' => false, 'ly_do_kxtd' => []];
            
            $stmt_thi_dua = $db->prepare("SELECT * FROM thi_dua_tuan WHERE tuan_hoc_id = ? AND lop_hoc_id = ?");
            $stmt_thi_dua->execute([$tuan_id, $lop['id']]);
            $thi_dua = $stmt_thi_dua->fetch(PDO::FETCH_ASSOC) ?: [];

            $data['sdb_tt'] = (int)($thi_dua['sdb_tt'] ?? 0);
            $data['sdb_ck'] = (int)($thi_dua['sdb_ck'] ?? 0);
            $data['sdb_nk'] = (int)($thi_dua['sdb_nk'] ?? 0);
            $data['nhat_ky'] = (int)($thi_dua['nhat_ky'] ?? 0);

            $diem_sdb = 0;
            if ($settings['report_sdb_use_tt']) $diem_sdb += $data['sdb_tt'] == 1 ? $settings['report_sdb_tt_tich'] : $settings['report_sdb_tt_khong'];
            if ($settings['report_sdb_use_ck']) $diem_sdb += $data['sdb_ck'] == 1 ? $settings['report_sdb_ck_tich'] : $settings['report_sdb_ck_khong'];
            if ($settings['report_sdb_use_nk']) $diem_sdb += $data['sdb_nk'] == 1 ? $settings['report_sdb_nk_tich'] : $settings['report_sdb_nk_khong'];
            if ($settings['report_sdb_use_nhat_ky']) $diem_sdb += $data['nhat_ky'] == 1 ? $settings['report_nhat_ky_tich'] : $settings['report_nhat_ky_khong'];

            $stmt_vang = $db->prepare("SELECT SUM(vang_p) as total_p, SUM(vang_kp) as total_kp FROM diem_danh WHERE tuan_hoc_id = ? AND lop_hoc_id = ?");
            $stmt_vang->execute([$tuan_id, $lop['id']]);
            $vang = $stmt_vang->fetch(PDO::FETCH_ASSOC);
            $data['vang_p'] = (int)($vang['total_p'] ?? 0);
            $data['vang_kp'] = (int)($vang['total_kp'] ?? 0);

            $stmt_noi_quy = $db->prepare("SELECT SUM(chvp.diem_tru) FROM vi_pham_hoc_sinh vp JOIN cau_hinh_vi_pham chvp ON vp.vi_pham_id = chvp.id LEFT JOIN quatrinh_hoc_tap qt ON vp.hoc_sinh_id = qt.id LEFT JOIN hoc_sinh hs ON hs.ma_hoc_sinh = qt.ma_hoc_sinh AND hs.nam_hoc_id = qt.nam_hoc_id WHERE vp.tuan_hoc_id = ? AND (hs.lop_hoc_id = ? OR vp.raw_ten_lop = ?)");
            $stmt_noi_quy->execute([$tuan_id, $lop['id'], $lop['ten_lop']]);
            $diem_noi_quy = -(float)($stmt_noi_quy->fetchColumn() ?? 0);

            $diem_cong_tru = (float)($thi_dua['diem_cong_tru'] ?? 0);
            $tru_vang = ($data['vang_p'] * $settings['report_tru_vang_p']) + ($data['vang_kp'] * $settings['report_tru_vang_kp']);
            $diem_tiet_tot = (int)($thi_dua['so_tiet_tot'] ?? 0) * $settings['report_diem_tiet_tot'];
            $diem_tiet_tb = (int)($thi_dua['so_tiet_tb'] ?? 0) * $settings['report_diem_tiet_tb'];
            $data['tong_diem'] = 100 + $diem_tiet_tot + $diem_tiet_tb + $diem_sdb + $diem_cong_tru + $diem_noi_quy + $tru_vang;

            foreach ($conditions_kxtd as $dk) {
                $value_to_check = 0;
                if (isset($dk['truong_so_sanh'])) {
                    if ($dk['truong_so_sanh'] === 'tong_diem_tru') $value_to_check = abs($diem_noi_quy);
                    if ($dk['truong_so_sanh'] === 'vang_kp') $value_to_check = $data['vang_kp'];
                }
                
                $violated = false;
                if (isset($dk['nguong_gia_tri'])) {
                    if ($dk['toan_tu'] === 'lon_hon' && $value_to_check > $dk['nguong_gia_tri']) $violated = true;
                    if ($dk['toan_tu'] === 'lon_hon_bang' && $value_to_check >= $dk['nguong_gia_tri']) $violated = true;
                }

                if ($violated) {
                    $data['kxtd'] = true;
                }
            }
            $report_data[] = $data;
        }
        
        // === BẮT ĐẦU NÂNG CẤP LOGIC XỬ LÝ ĐỒNG HẠNG ===

        // 1. Gom nhóm các lớp theo khối
        $lop_theo_khoi = [];
        foreach ($report_data as $lop_data) {
            $khoi = substr($lop_data['lop'], 0, 2); // Lấy 2 ký tự đầu làm khối (10, 11, 12)
            if (!isset($lop_theo_khoi[$khoi])) {
                $lop_theo_khoi[$khoi] = [];
            }
            $lop_theo_khoi[$khoi][] = $lop_data;
        }

        $all_top_classes = [];
        $all_bottom_classes = []; // Sẽ chứa cả hạng chót và KXTĐ

        // 2. Lặp qua từng khối để xử lý
        foreach ($lop_theo_khoi as $khoi => $cac_lop_trong_khoi) {
            $lop_can_xep_hang = [];
            
            // Tách riêng các lớp KXTĐ của khối này
            foreach ($cac_lop_trong_khoi as $lop) {
                if ($lop['kxtd']) {
                    // Thêm hậu tố (KXTĐ) vào tên lớp
                    $all_bottom_classes[] = $lop['lop'] . ' (KXTĐ)';
                } else {
                    $lop_can_xep_hang[] = $lop;
                }
            }

            // Nếu có lớp để xếp hạng trong khối này
            if (!empty($lop_can_xep_hang)) {
                // Tìm điểm cao nhất và thấp nhất trong các lớp cần xếp hạng của khối
                $max_score = -99999;
                $min_score = 99999;
                foreach ($lop_can_xep_hang as $lop) {
                    if ($lop['tong_diem'] > $max_score) {
                        $max_score = $lop['tong_diem'];
                    }
                    if ($lop['tong_diem'] < $min_score) {
                        $min_score = $lop['tong_diem'];
                    }
                }

                // Tìm tất cả các lớp có điểm bằng điểm cao nhất (đồng hạng nhất)
                foreach ($lop_can_xep_hang as $lop) {
                    if ($lop['tong_diem'] == $max_score) {
                        $all_top_classes[] = ['lop' => $lop['lop'], 'tong_diem' => $lop['tong_diem']];
                    }
                }

                // Tìm tất cả các lớp có điểm bằng điểm thấp nhất (đồng hạng chót)
                foreach ($lop_can_xep_hang as $lop) {
                    if ($lop['tong_diem'] == $min_score) {
                        $all_bottom_classes[] = $lop['lop'];
                    }
                }
            }
        }
        
        // 3. Gán kết quả cuối cùng
        $stats['top_class_last_week'] = $all_top_classes;
        $stats['bottom_class_last_week'] = $all_bottom_classes;
    }
    
    // === KẾT THÚC NÂNG CẤP ===
    
    // 4. DỮ LIỆU BIỂU ĐỒ
    $chart_data_result = [];
    if($current_week){
        $stmt_chart = $db->prepare("
            SELECT COALESCE(lh.ten_lop, vp.raw_ten_lop) as ten_lop, COUNT(vp.id) as total_violations
            FROM vi_pham_hoc_sinh vp
            LEFT JOIN quatrinh_hoc_tap qt ON vp.hoc_sinh_id = qt.id LEFT JOIN hoc_sinh hs ON hs.ma_hoc_sinh = qt.ma_hoc_sinh AND hs.nam_hoc_id = qt.nam_hoc_id
            LEFT JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id
            WHERE vp.tuan_hoc_id = ? AND ten_lop IS NOT NULL
            GROUP BY ten_lop
            ORDER BY total_violations DESC
            LIMIT 5
        ");
        $stmt_chart->execute([$current_week['id']]);
        $chart_data_result = $stmt_chart->fetchAll(PDO::FETCH_KEY_PAIR);
    }
    $stats['chart_data'] = [
        'labels' => array_keys($chart_data_result),
        'data' => array_values($chart_data_result)
    ];


    // Gộp trạng thái success vào $stats
    $stats['success'] = true;
    echo json_encode($stats);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi Server: ' . $e->getMessage(), 'trace' => $e->getTraceAsString()]);
}
?>