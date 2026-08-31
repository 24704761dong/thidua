<?php
// File: src/controllers/trang_tra_cuu_diem.php (ĐÃ SỬA LỖI LẤY CẤU HÌNH PHÚC KHẢO)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';

$db = get_db_connection();
$ds_ky_thi_cong_khai = []; // Sẽ chứa mảng các kỳ thi đã xử lý
$error_message = null;

try {
    // Lấy danh sách các kỳ thi đang được bật tra cứu công khai
    // *** SỬA LỖI: Thêm cột phuc_khao_xac_minh vào SELECT ***
    $stmt = $db->query("
        SELECT id, ten_ky_thi, phuong_thuc_tra_cuu, phuc_khao_xac_minh
        FROM ky_thi
        WHERE tra_cuu_cong_khai = 1
        ORDER BY ngay_bat_dau DESC, id DESC
    ");
    $raw_ky_thi_list = $stmt->fetchAll(PDO::FETCH_ASSOC); // Lấy danh sách thuần túy

    // Xử lý dữ liệu cho từng kỳ thi    
    // Xử lý dữ liệu cho từng kỳ thi
    foreach ($raw_ky_thi_list as $ky_thi) {
        // Lấy phương thức tra cứu (chuỗi đơn)
        $phuong_thuc = $ky_thi['phuong_thuc_tra_cuu'] ?? 'sbd'; // Mặc định là 'sbd'

        // Decode cấu hình xác minh phúc khảo (JSON) thành MẢNG PHP
        $xac_minh_config = json_decode($ky_thi['phuc_khao_xac_minh'] ?: '{}', true);
        // Đảm bảo $xac_minh_config luôn là mảng, kể cả khi JSON không hợp lệ
        if (!is_array($xac_minh_config)) {
             $xac_minh_config = [];
             error_log("Cảnh báo: Không thể decode phuc_khao_xac_minh JSON cho kỳ thi ID {$ky_thi['id']}. Dữ liệu rå: " . $ky_thi['phuc_khao_xac_minh']);
        }

        // Lưu lại dữ liệu đã xử lý vào mảng kết quả
        $ds_ky_thi_cong_khai[$ky_thi['id']] = [
            'id' => $ky_thi['id'],
            'ten_ky_thi' => $ky_thi['ten_ky_thi'],
            'phuong_thuc_tra_cuu' => $phuong_thuc,
            // *** SỬA LỖI: Truyền trực tiếp MẢNG PHP, không encode lại ***
            'phuc_khao_xac_minh' => $xac_minh_config
        ];
    }


} catch (Exception $e) {
    error_log("Lỗi khi lấy DS Kỳ thi công khai: " . $e->getMessage());
    $error_message = "Không thể tải danh sách kỳ thi. Vui lòng thử lại sau.";
}

$page_title = 'Tra cứu Điểm thi';
require_once __DIR__ . '/../views/giao_dien_tra_cuu_diem.php';
?>