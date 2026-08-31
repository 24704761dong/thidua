<?php
// File: src/lib/thiduaCalculator.php (PHIÊN BẢN HOÀN CHỈNH - LOGIC VẮNG KÉP)

class thiduaCalculator
{
    private $db;
    private $settings;
    private $conditions_kxtd;

    private $nam_hoc_id;

    /**
     * Khởi tạo bộ tính toán với kết nối CSDL.
     * @param PDO $db_connection Kết nối PDO tới CSDL.
     * @param int|null $nam_hoc_id ID năm học. Nếu null, sẽ lấy từ session.
     */
    public function __construct(PDO $db_connection, $nam_hoc_id = null)
    {
        $this->db = $db_connection;
        if ($nam_hoc_id === null && session_status() === PHP_SESSION_ACTIVE) {
            $this->nam_hoc_id = $_SESSION['current_nam_hoc_id'] ?? 1;
        } else {
            $this->nam_hoc_id = $nam_hoc_id ?? 1;
        }
        $this->loadConfig();
    }

    /**
     * Tải cấu hình tính điểm và điều kiện KXTĐ từ CSDL một lần duy nhất.
     */
    private function loadConfig()
    {
        $stmt_settings = $this->db->prepare("
            SELECT setting_key, setting_value 
            FROM he_thong_cai_dat 
            WHERE nam_hoc_id = 0 OR nam_hoc_id IS NULL OR nam_hoc_id = ?
            ORDER BY CASE WHEN nam_hoc_id = ? THEN 1 ELSE 0 END ASC
        ");
        $stmt_settings->execute([$this->nam_hoc_id, $this->nam_hoc_id]);
        $settings_raw = [];
        foreach ($stmt_settings->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $settings_raw[$row['setting_key']] = $row['setting_value'];
        }
        
        $this->settings = [
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
            // Nạp cấu hình mới: Giải mã chuỗi JSON từ CSDL thành mảng PHP
            'report_vang_p_vids' => json_decode($settings_raw['report_vang_p_vids'] ?? '[]', true),
            'report_vang_kp_vids' => json_decode($settings_raw['report_vang_kp_vids'] ?? '[]', true),
        ];

        $stmt_kxtd = $this->db->prepare("SELECT * FROM raw_dieu_kien_kxtd WHERE kich_hoat = 1 AND nam_hoc_id = ? ORDER BY id ASC");
        $stmt_kxtd->execute([$this->nam_hoc_id]);
        $this->conditions_kxtd = $stmt_kxtd->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tính toán toàn bộ dữ liệu thi đua (chưa xếp hạng) cho một tuần cụ thể.
     * @param int $tuan_id ID của tuần cần tính.
     * @return array Mảng chứa dữ liệu đã tính toán của tất cả các lớp.
     */
    public function calculateRawDataForWeek(int $tuan_id): array
    {
        $report_data_raw = [];
        $lop_hoc = $this->db->query("SELECT id, ten_lop FROM lop_hoc ORDER BY CAST(SUBSTR(ten_lop, 1, 2) AS INTEGER), SUBSTR(ten_lop, 3, 1), CAST(SUBSTR(ten_lop, 4) AS INTEGER) ASC")->fetchAll(PDO::FETCH_ASSOC);

        foreach ($lop_hoc as $lop) {
            $data = [ 'lop_id' => $lop['id'], 'lop' => $lop['ten_lop'], 'tong_diem' => 0, 'kxtd' => false ];

            // --- Lấy dữ liệu thi đua & nội quy (không đổi) ---
            $stmt_thi_dua = $this->db->prepare("SELECT * FROM thi_dua_tuan WHERE tuan_hoc_id = ? AND lop_hoc_id = ?");
            $stmt_thi_dua->execute([$tuan_id, $lop['id']]);
            $thi_dua = $stmt_thi_dua->fetch(PDO::FETCH_ASSOC) ?: [];
            
            $stmt_noi_quy = $this->db->prepare("SELECT SUM(chvp.diem_tru) FROM vi_pham_hoc_sinh vphs JOIN cau_hinh_vi_pham chvp ON vphs.vi_pham_id = chvp.id LEFT JOIN quatrinh_hoc_tap qt ON vphs.hoc_sinh_id = qt.id LEFT JOIN hoc_sinh hs ON hs.ma_hoc_sinh = qt.ma_hoc_sinh AND hs.nam_hoc_id = qt.nam_hoc_id WHERE vphs.tuan_hoc_id = ? AND (hs.lop_hoc_id = ? OR vphs.raw_ten_lop = ?)");
            $stmt_noi_quy->execute([$tuan_id, $lop['id'], $lop['ten_lop']]);
            $tong_diem_tru_noi_quy = (float)($stmt_noi_quy->fetchColumn() ?? 0);

            // --- LOGIC MỚI: LẤY DỮ LIỆU VẮNG DỰA TRÊN CẤU HÌNH ---
            $vang = ['total_p' => 0, 'total_kp' => 0];
            if ($this->settings['report_vang_source'] === 'vi_pham') {
                $vang_p_vids = $this->settings['report_vang_p_vids'];
                $vang_kp_vids = $this->settings['report_vang_kp_vids'];

                // Đếm số lượt vắng P từ các vi phạm đã chọn
                if (!empty($vang_p_vids)) {
                    $placeholders_p = implode(',', array_fill(0, count($vang_p_vids), '?'));
                    $stmt_vang_p = $this->db->prepare("SELECT COUNT(*) FROM vi_pham_hoc_sinh vphs LEFT JOIN quatrinh_hoc_tap qt ON vphs.hoc_sinh_id = qt.id LEFT JOIN hoc_sinh hs ON hs.ma_hoc_sinh = qt.ma_hoc_sinh AND hs.nam_hoc_id = qt.nam_hoc_id WHERE vphs.tuan_hoc_id = ? AND (hs.lop_hoc_id = ? OR vphs.raw_ten_lop = ?) AND vphs.vi_pham_id IN ($placeholders_p)");
                    $params_p = array_merge([$tuan_id, $lop['id'], $lop['ten_lop']], $vang_p_vids);
                    $stmt_vang_p->execute($params_p);
                    $vang['total_p'] = (int)$stmt_vang_p->fetchColumn();
                }

                // Đếm số lượt vắng KP từ các vi phạm đã chọn
                if (!empty($vang_kp_vids)) {
                    $placeholders_kp = implode(',', array_fill(0, count($vang_kp_vids), '?'));
                    $stmt_vang_kp = $this->db->prepare("SELECT COUNT(*) FROM vi_pham_hoc_sinh vphs LEFT JOIN quatrinh_hoc_tap qt ON vphs.hoc_sinh_id = qt.id LEFT JOIN hoc_sinh hs ON hs.ma_hoc_sinh = qt.ma_hoc_sinh AND hs.nam_hoc_id = qt.nam_hoc_id WHERE vphs.tuan_hoc_id = ? AND (hs.lop_hoc_id = ? OR vphs.raw_ten_lop = ?) AND vphs.vi_pham_id IN ($placeholders_kp)");
                    $params_kp = array_merge([$tuan_id, $lop['id'], $lop['ten_lop']], $vang_kp_vids);
                    $stmt_vang_kp->execute($params_kp);
                    $vang['total_kp'] = (int)$stmt_vang_kp->fetchColumn();
                }
            } else {
                // Cách 1 (Mặc định): Lấy từ module Điểm danh
                $stmt_vang_dd = $this->db->prepare("SELECT SUM(vang_p) as total_p, SUM(vang_kp) as total_kp FROM diem_danh WHERE tuan_hoc_id = ? AND lop_hoc_id = ?");
                $stmt_vang_dd->execute([$tuan_id, $lop['id']]);
                $vang_from_dd = $stmt_vang_dd->fetch(PDO::FETCH_ASSOC);
                $vang['total_p'] = (int)($vang_from_dd['total_p'] ?? 0);
                $vang['total_kp'] = (int)($vang_from_dd['total_kp'] ?? 0);
            }

            // --- Gán các giá trị chi tiết vào mảng $data ---
            $data['so_tiet_tot'] = (int)($thi_dua['so_tiet_tot'] ?? 0);
            $data['so_tiet_tb'] = (int)($thi_dua['so_tiet_tb'] ?? 0);
            $data['diem_cong_tru'] = (float)($thi_dua['diem_cong_tru'] ?? 0);
            $data['sdb_tt'] = (int)($thi_dua['sdb_tt'] ?? 0);
            $data['sdb_ck'] = (int)($thi_dua['sdb_ck'] ?? 0);
            $data['sdb_nk'] = (int)($thi_dua['sdb_nk'] ?? 0);
            $data['nhat_ky'] = (int)($thi_dua['nhat_ky'] ?? 0);
            $data['vang_p'] = $vang['total_p'];
            $data['vang_kp'] = $vang['total_kp'];
            $data['diem_noi_quy'] = -$tong_diem_tru_noi_quy;

            // --- LOGIC MỚI: TÍNH ĐIỂM TRỪ VẮNG DỰA TRÊN CẤU HÌNH ---
            $tru_vang = 0;
            if ($this->settings['report_vang_source'] === 'diem_danh') {
                // Nếu nguồn là Điểm danh, tính điểm trừ theo cấu hình
                $tru_vang = ($data['vang_p'] * $this->settings['report_tru_vang_p']) + ($data['vang_kp'] * $this->settings['report_tru_vang_kp']);
            }
            // Nếu nguồn là Vi phạm, $tru_vang mặc định là 0 vì điểm trừ đã được tính trong $data['diem_noi_quy']
            
            // Tính điểm Sổ Đầu Bài
            $diem_sdb = 0;
            if ($this->settings['report_sdb_use_tt']) $diem_sdb += $data['sdb_tt'] == 1 ? $this->settings['report_sdb_tt_tich'] : $this->settings['report_sdb_tt_khong'];
            if ($this->settings['report_sdb_use_ck']) $diem_sdb += $data['sdb_ck'] == 1 ? $this->settings['report_sdb_ck_tich'] : $this->settings['report_sdb_ck_khong'];
            if ($this->settings['report_sdb_use_nk']) $diem_sdb += $data['sdb_nk'] == 1 ? $this->settings['report_sdb_nk_tich'] : $this->settings['report_sdb_nk_khong'];
            if ($this->settings['report_sdb_use_nhat_ky']) $diem_sdb += $data['nhat_ky'] == 1 ? $this->settings['report_nhat_ky_tich'] : $this->settings['report_nhat_ky_khong'];

            // TÍNH TỔNG ĐIỂM CUỐI CÙNG
            $data['tong_diem'] = ($data['so_tiet_tot'] * $this->settings['report_diem_tiet_tot']) + ($data['so_tiet_tb'] * $this->settings['report_diem_tiet_tb']) + $diem_sdb + $data['diem_cong_tru'] + $data['diem_noi_quy'] + $tru_vang;

            // Gán lại các điểm thành phần
            $data['diem_tiet_tot_thanh_phan'] = ($data['so_tiet_tot'] * $this->settings['report_diem_tiet_tot']);
            $data['diem_tiet_tb_thanh_phan'] = ($data['so_tiet_tb'] * $this->settings['report_diem_tiet_tb']);
            $data['diem_sdb_thanh_phan'] = $diem_sdb;
            $data['tru_vang_thanh_phan'] = $tru_vang;

            // --- KIỂM TRA KXTĐ (Giữ nguyên) ---
            foreach ($this->conditions_kxtd as $dk) {
                 if ($data['kxtd']) break;
                 
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
                         $gia_tri_so_sanh = abs($gia_tri_so_sanh);
                     }

                     $nguong = (float)$dk['nguong_gia_tri'];
                     switch ($toan_tu) {
                         case '>': $dieu_kien_dung = $gia_tri_so_sanh > $nguong; break; case '>=': $dieu_kien_dung = $gia_tri_so_sanh >= $nguong; break;
                         case '<': $dieu_kien_dung = $gia_tri_so_sanh < $nguong; break; case '<=': $dieu_kien_dung = $gia_tri_so_sanh <= $nguong; break;
                         case '==': $dieu_kien_dung = $gia_tri_so_sanh == $nguong; break; case '!=': $dieu_kien_dung = $gia_tri_so_sanh != $nguong; break;
                     }
                 }

                 if ($dieu_kien_dung) {
                     $data['kxtd'] = true;
                 }
            }
            $report_data_raw[] = $data;
        }
        return $report_data_raw;
    }

    /**
     * Xếp hạng dữ liệu thi đua đã được tính toán.
     * @param array $report_data Dữ liệu thô từ hàm calculateRawDataForWeek.
     * @return array Dữ liệu đã được thêm key 'xep_hang'.
     */
    public function rankWeeklyData(array $report_data): array
    {
        // ... (Logic xếp hạng giữ nguyên vì nó đã đúng) ...
        $lop_theo_khoi = [];
        foreach ($report_data as $data) {
            $khoi = substr($data['lop'], 0, 2);
            $lop_theo_khoi[$khoi][] = $data;
        }

        $ranks_by_khoi = [];
        foreach ($lop_theo_khoi as $khoi => $ds_lop) {
            $lop_can_xep_hang = array_filter($ds_lop, function ($lop) {
                return !$lop['kxtd'];
            });

            usort($lop_can_xep_hang, function ($a, $b) {
                return $b['tong_diem'] <=> $a['tong_diem'];
            });

            $current_rank = 0;
            $last_score = -999999; // Dùng số cực nhỏ để đảm bảo lần so sánh đầu tiên luôn khác
            $skip = 1;
            foreach ($lop_can_xep_hang as $lop_data) {
                // Làm tròn trước khi so sánh để tránh sai số do số thực
                if (round($lop_data['tong_diem'], 5) != round($last_score, 5)) {
                    $current_rank += $skip;
                    $skip = 1;
                } else {
                    $skip++;
                }
                $ranks_by_khoi[$lop_data['lop']] = $current_rank;
                $last_score = $lop_data['tong_diem'];
            }
        }

        foreach ($report_data as &$data) {
            if (!$data['kxtd']) {
                $data['xep_hang'] = $ranks_by_khoi[$data['lop']] ?? 'N/A';
            } else {
                $data['xep_hang'] = 'KXTĐ';
            }
        }
        unset($data);

        return $report_data;
    }
}