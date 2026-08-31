<?php
// File: src/controllers/api_get_kxtd_reason.php (PHIÊN BẢN ĐÃ SỬA LỖI)
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

// ==================
// BƯỚC 1: KÍCH HOẠT LẠI BẢO MẬT
// ==================
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) { 
    http_response_code(403); 
    echo json_encode(['reason' => 'Lỗi: Không có quyền truy cập.']);
    exit(); 
} 

require_once __DIR__ . '/../../config/database.php';
// Nạp "bộ não" tính toán để sử dụng logic
require_once __DIR__ . '/../lib/ThiDuaCalculator.php'; 

// ==================
// BƯỚC 2: LẤY DỮ LIỆU ĐẦU VÀO
// ==================
$tuan_id = $_GET['tuan_id'] ?? null;
$lop_id = $_GET['lop_id'] ?? null;

if (!$tuan_id || !$lop_id) {
    http_response_code(400); // Bad Request
    echo json_encode(['reason' => 'Lỗi: Thiếu ID tuần hoặc ID lớp.']);
    exit();
}

try {
    $db = get_db_connection();

    // ==================
    // BƯỚC 3: TẢI CẤU HÌNH VÀ TÍNH TOÁN DỮ LIỆU THÔ CHO 1 LỚP
    // (Logic được "mượn" từ ThiDuaCalculator.php)
    // ==================

    // 3.1. Lấy tên lớp từ ID
    $stmt_lop = $db->prepare("SELECT ten_lop FROM lop_hoc WHERE id = ?");
    $stmt_lop->execute([$lop_id]);
    $ten_lop = $stmt_lop->fetchColumn();
    if (!$ten_lop) {
        throw new Exception("Không tìm thấy lớp với ID $lop_id");
    }

    // 3.2. Tải cấu hình
    $stmt_settings = $db->query("SELECT setting_key, setting_value FROM he_thong_cai_dat");
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
        'report_vang_source' => $settings_raw['report_vang_source'] ?? 'diem_danh',
        'report_tru_vang_p' => (float)($settings_raw['report_tru_vang_p'] ?? 0),
        'report_tru_vang_kp' => (float)($settings_raw['report_tru_vang_kp'] ?? -1),
        'report_vang_p_vids' => json_decode($settings_raw['report_vang_p_vids'] ?? '[]', true),
        'report_vang_kp_vids' => json_decode($settings_raw['report_vang_kp_vids'] ?? '[]', true),
    ];

    $conditions_kxtd = $db->query("SELECT * FROM dieu_kien_kxtd WHERE kich_hoat = 1")->fetchAll(PDO::FETCH_ASSOC);

    // 3.3. Lấy dữ liệu thô của lớp này
    $data = [ 'lop_id' => $lop_id, 'lop' => $ten_lop, 'tong_diem' => 0, 'kxtd' => false ];

    $stmt_thi_dua = $db->prepare("SELECT * FROM thi_dua_tuan WHERE tuan_hoc_id = ? AND lop_hoc_id = ?");
    $stmt_thi_dua->execute([$tuan_id, $lop_id]);
    $thi_dua = $stmt_thi_dua->fetch(PDO::FETCH_ASSOC) ?: [];
    
    $stmt_noi_quy = $db->prepare("SELECT SUM(chvp.diem_tru) FROM vi_pham_hoc_sinh vphs JOIN cau_hinh_vi_pham chvp ON vphs.vi_pham_id = chvp.id LEFT JOIN quatrinh_hoc_tap qt ON vphs.hoc_sinh_id = qt.id LEFT JOIN hoc_sinh hs ON hs.ma_hoc_sinh = qt.ma_hoc_sinh AND hs.nam_hoc_id = qt.nam_hoc_id WHERE vphs.tuan_hoc_id = ? AND (hs.lop_hoc_id = ? OR vphs.raw_ten_lop = ?)");
    $stmt_noi_quy->execute([$tuan_id, $lop_id, $ten_lop]);
    $tong_diem_tru_noi_quy = (float)($stmt_noi_quy->fetchColumn() ?? 0);

    // --- LẤY DỮ LIỆU VẮNG (Logic từ thiduaCalculator) ---
    $vang = ['total_p' => 0, 'total_kp' => 0];
    if ($settings['report_vang_source'] === 'vi_pham') {
        $vang_p_vids = $settings['report_vang_p_vids'];
        $vang_kp_vids = $settings['report_vang_kp_vids'];
        if (!empty($vang_p_vids)) {
            $placeholders_p = implode(',', array_fill(0, count($vang_p_vids), '?'));
            $stmt_vang_p = $db->prepare("SELECT COUNT(*) FROM vi_pham_hoc_sinh vphs LEFT JOIN quatrinh_hoc_tap qt ON vphs.hoc_sinh_id = qt.id LEFT JOIN hoc_sinh hs ON hs.ma_hoc_sinh = qt.ma_hoc_sinh AND hs.nam_hoc_id = qt.nam_hoc_id WHERE vphs.tuan_hoc_id = ? AND (hs.lop_hoc_id = ? OR vphs.raw_ten_lop = ?) AND vphs.vi_pham_id IN ($placeholders_p)");
            $params_p = array_merge([$tuan_id, $lop_id, $ten_lop], $vang_p_vids);
            $stmt_vang_p->execute($params_p);
            $vang['total_p'] = (int)$stmt_vang_p->fetchColumn();
        }
        if (!empty($vang_kp_vids)) {
            $placeholders_kp = implode(',', array_fill(0, count($vang_kp_vids), '?'));
            $stmt_vang_kp = $db->prepare("SELECT COUNT(*) FROM vi_pham_hoc_sinh vphs LEFT JOIN quatrinh_hoc_tap qt ON vphs.hoc_sinh_id = qt.id LEFT JOIN hoc_sinh hs ON hs.ma_hoc_sinh = qt.ma_hoc_sinh AND hs.nam_hoc_id = qt.nam_hoc_id WHERE vphs.tuan_hoc_id = ? AND (hs.lop_hoc_id = ? OR vphs.raw_ten_lop = ?) AND vphs.vi_pham_id IN ($placeholders_kp)");
            $params_kp = array_merge([$tuan_id, $lop_id, $ten_lop], $vang_kp_vids);
            $stmt_vang_kp->execute($params_kp);
            $vang['total_kp'] = (int)$stmt_vang_kp->fetchColumn();
        }
    } else {
        $stmt_vang_dd = $db->prepare("SELECT SUM(vang_p) as total_p, SUM(vang_kp) as total_kp FROM diem_danh WHERE tuan_hoc_id = ? AND lop_hoc_id = ?");
        $stmt_vang_dd->execute([$tuan_id, $lop_id]);
        $vang_from_dd = $stmt_vang_dd->fetch(PDO::FETCH_ASSOC);
        $vang['total_p'] = (int)($vang_from_dd['total_p'] ?? 0);
        $vang['total_kp'] = (int)($vang_from_dd['total_kp'] ?? 0);
    }
    // --- KẾT THÚC LẤY VẮNG ---

    // 3.4. Gán các giá trị chi tiết vào mảng $data
    $data['so_tiet_tot'] = (int)($thi_dua['so_tiet_tot'] ?? 0);
    $data['so_tiet_tb'] = (int)($thi_dua['so_tiet_tb'] ?? 0);
    $data['diem_cong_tru'] = (float)($thi_dua['diem_cong_tru'] ?? 0);
    $data['sdb_tt'] = (int)($thi_dua['sdb_tt'] ?? 0);
    $data['sdb_ck'] = (int)($thi_dua['sdb_ck'] ?? 0);
    $data['sdb_nk'] = (int)($thi_dua['sdb_nk'] ?? 0);
    $data['nhat_ky'] = (int)($thi_dua['nhat_ky'] ?? 0);
    $data['vang_p'] = $vang['total_p'];
    $data['vang_kp'] = $vang['total_kp'];
    $data['diem_noi_quy'] = -$tong_diem_tru_noi_quy; // Lưu là số âm

    // ==================
    // BƯỚC 4: CHẠY LOGIC KIỂM TRA KXTĐ
    // (Sao chép 1-1 từ ThiDuaCalculator.php)
    // ==================
    
    foreach ($conditions_kxtd as $dk) {
         $dieu_kien_dung = false;
         $toan_tu = $dk['toan_tu'];

         if (strpos($toan_tu, 'SDB_') === 0) {
             $sdb_cols_to_check = json_decode($dk['danh_sach_sdb'] ?? '[]', true);
             if (empty($sdb_cols_to_check) && !empty($dk['truong_so_sanh'])) {
                 $sdb_cols_to_check = [$dk['truong_so_sanh']];
             }
             if (empty($sdb_cols_to_check)) continue;

             $ticked_count = 0;
             foreach ($sdb_cols_to_check as $col) {
                 if (isset($data[$col]) && $data[$col] == 1) $ticked_count++;
             }

             if ($toan_tu === 'SDB_IS_TICKED') {
                 if ($ticked_count > 0) $dieu_kien_dung = true;
             } elseif ($toan_tu === 'SDB_IS_NOT_TICKED') {
                 if ($ticked_count < count($sdb_cols_to_check)) $dieu_kien_dung = true;
             } elseif ($toan_tu === 'SDB_COMB_ALL_NOT_TICKED') {
                 if ($ticked_count === 0) $dieu_kien_dung = true;
             } elseif ($toan_tu === 'SDB_COUNT_TICKED_EQUALS') {
                 if ($ticked_count == (int)$dk['nguong_gia_tri']) $dieu_kien_dung = true;
             }
         } else {
             $gia_tri_so_sanh = $data[$dk['truong_so_sanh']] ?? null;
             if ($gia_tri_so_sanh === null) continue;
             
             if($dk['truong_so_sanh'] === 'diem_noi_quy'){
                 // 'diem_noi_quy' trong $data là số âm, nhưng quy tắc KXTĐ thường dùng số dương (ví dụ: trừ > 10 điểm)
                 $gia_tri_so_sanh = abs($gia_tri_so_sanh);
             }

             $nguong = (float)$dk['nguong_gia_tri'];
             switch ($toan_tu) {
                 case '>': $dieu_kien_dung = $gia_tri_so_sanh > $nguong; break; 
                 case '>=': $dieu_kien_dung = $gia_tri_so_sanh >= $nguong; break;
                 case '<': $dieu_kien_dung = $gia_tri_so_sanh < $nguong; break; 
                 case '<=': $dieu_kien_dung = $gia_tri_so_sanh <= $nguong; break;
                 case '==': $dieu_kien_dung = $gia_tri_so_sanh == $nguong; break; 
                 case '!=': $dieu_kien_dung = $gia_tri_so_sanh != $nguong; break;
             }
         }

         // ==================
         // BƯỚC 5: TRẢ VỀ LÝ DO NẾU TÌM THẤY
         // ==================
         if ($dieu_kien_dung) {
             // Tìm thấy lý do KXTĐ, trả về ngay lập tức
             echo json_encode(['reason' => htmlspecialchars($dk['ten_dieu_kien'])]);
             exit; // Kết thúc script
         }
    }

    // Nếu vòng lặp kết thúc mà không bị KXTĐ
    echo json_encode(['reason' => 'Không KXTĐ']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['reason' => 'Lỗi: ' . $e->getMessage()]);
}
?>