<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('GET, OPTIONS');
zalo_handle_options();

try {
    $db = get_db_connection();
    
    $payload = zalo_authenticate_request();
    $student_id = $payload['student_id'];
    
    $tuan_hoc_id = $_GET['tuan_id'] ?? $_GET['tuan_hoc_id'] ?? null;

    if (!$tuan_hoc_id || $tuan_hoc_id === 'undefined') {
        throw new Exception("Thiếu ID tuần học.");
    }

    // Lấy lớp học ID của học sinh
    $stmt_hs = $db->prepare("
        SELECT qt.lop_hoc_id, lh.ten_lop 
        FROM quatrinh_hoc_tap qt 
        JOIN ho_so_hoc_sinh hs ON qt.ma_hoc_sinh = hs.ma_hoc_sinh 
        LEFT JOIN raw_lop_hoc lh ON qt.lop_hoc_id = lh.id
        WHERE hs.id = ? 
        ORDER BY qt.nam_hoc_id DESC LIMIT 1
    ");
    $stmt_hs->execute([$student_id]);
    $hs_info = $stmt_hs->fetch(PDO::FETCH_ASSOC);
    $lop_hoc_id = $hs_info['lop_hoc_id'] ?? null;

    if (!$lop_hoc_id) {
        throw new Exception("Không tìm thấy thông tin lớp học của bạn.");
    }

    // Tìm hoặc tạo mới bản ghi Sổ Nhật Kỳ
    $stmt_nhat_ky = $db->prepare("SELECT * FROM so_nhat_ky_online WHERE tuan_hoc_id = ? AND lop_hoc_id = ?");
    $stmt_nhat_ky->execute([$tuan_hoc_id, $lop_hoc_id]);
    $nhat_ky = $stmt_nhat_ky->fetch(PDO::FETCH_ASSOC);

    if (!$nhat_ky) {
        // Tạo mới bản ghi trạng thái 'nhap'
        $stmt_create = $db->prepare("INSERT INTO so_nhat_ky_online (tuan_hoc_id, lop_hoc_id, nguoi_nhap_id, trang_thai) VALUES (?, ?, ?, 'nhap')");
        $stmt_create->execute([$tuan_hoc_id, $lop_hoc_id, $student_id]);
        $nhat_ky_id = $db->lastInsertId();

        // Tạo sẵn 3 dòng chi tiết nếu chưa có
        $stmt_create_details = $db->prepare("INSERT INTO so_nhat_ky_chi_tiet (nhat_ky_id, loai_so) VALUES (?, ?)");
        $stmt_create_details->execute([$nhat_ky_id, 'sdb_ck']);
        $stmt_create_details->execute([$nhat_ky_id, 'sdb_nk']);
        $stmt_create_details->execute([$nhat_ky_id, 'sdb_tt']);

        $stmt_nhat_ky->execute([$tuan_hoc_id, $lop_hoc_id]);
        $nhat_ky = $stmt_nhat_ky->fetch(PDO::FETCH_ASSOC);
    }

    $nhat_ky_id = $nhat_ky['id'];

    // Lấy thông tin tuần học
    $stmt_tuan = $db->prepare("SELECT ten_tuan, ngay_bat_dau, ngay_ket_thuc FROM raw_tuan_hoc WHERE id = ?");
    $stmt_tuan->execute([$tuan_hoc_id]);
    $tuan_info = $stmt_tuan->fetch(PDO::FETCH_ASSOC);
    $nhat_ky['ten_tuan'] = $tuan_info['ten_tuan'] ?? '';
    $nhat_ky['ten_lop'] = $hs_info['ten_lop'] ?? '';

    // Lấy chi tiết
    $stmt_chi_tiet = $db->prepare("SELECT * FROM so_nhat_ky_chi_tiet WHERE nhat_ky_id = ?");
    $stmt_chi_tiet->execute([$nhat_ky_id]);
    $chi_tiet_rows = $stmt_chi_tiet->fetchAll(PDO::FETCH_ASSOC);
    
    $details = [
        'sdb_ck' => ['so_tiet_tot' => 0, 'so_tiet_kha' => 0, 'so_tiet_tb' => 0, 'so_tiet_yeu' => 0],
        'sdb_nk' => ['so_tiet_tot' => 0, 'so_tiet_kha' => 0, 'so_tiet_tb' => 0, 'so_tiet_yeu' => 0],
        'sdb_tt' => ['so_tiet_tot' => 0, 'so_tiet_kha' => 0, 'so_tiet_tb' => 0, 'so_tiet_yeu' => 0]
    ];
    foreach ($chi_tiet_rows as $row) {
        if (!empty($row['loai_so'])) {
            $details[$row['loai_so']] = [
                'id' => $row['id'] ?? null,
                'so_tiet_tot' => (int)($row['so_tiet_tot'] ?? 0),
                'so_tiet_kha' => (int)($row['so_tiet_kha'] ?? 0),
                'so_tiet_tb' => (int)($row['so_tiet_tb'] ?? 0),
                'so_tiet_yeu' => (int)($row['so_tiet_yeu'] ?? 0),
                'nhan_xet_chung' => $row['nhan_xet_chung'] ?? '',
                'uu_diem' => $row['uu_diem'] ?? '',
                'khuyet_diem' => $row['khuyet_diem'] ?? ''
            ];
        }
    }

    // Lấy minh chứng
    $stmt_proofs = $db->prepare("SELECT id, nhat_ky_id, loai_minh_chung, original_filename as file_name, file_path, cloud_key, storage_driver FROM so_nhat_ky_minh_chung WHERE nhat_ky_id = ?");
    $stmt_proofs->execute([$nhat_ky_id]);
    $proofs_raw = $stmt_proofs->fetchAll(PDO::FETCH_ASSOC);

    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? 'https' : 'http';
    $host = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/thidua/';

    require_once __DIR__ . '/../lib/StorageService.php';
    $storage = null;

    $proofs = [
        'sdb_ck' => [],
        'sdb_nk' => [],
        'sdb_tt' => []
    ];

    foreach ($proofs_raw as $p) {
        $url = '';
        if (in_array($p['storage_driver'] ?? '', ['r2', 'cloud']) && !empty($p['cloud_key'])) {
            try {
                if (!$storage) $storage = new StorageService();
                $url = $storage->getTemporaryUrl($p['cloud_key'], '+60 minutes');
            } catch (Exception $e) {
                $url = $p['cloud_url'] ?: '';
            }
        } else if (($p['storage_driver'] ?? '') === 'onedrive' && !empty($p['cloud_key'])) {
            $url = $host . 'src/controllers/api_get_presigned_url.php?driver=onedrive&key=' . urlencode($p['cloud_key']) . '&inline=1';
        } else if (!empty($p['file_path'])) {
            $url = $host . ltrim($p['file_path'], '/');
        } else if (!empty($p['cloud_url'])) {
            $url = $p['cloud_url'];
        }

        $item = [
            'id' => (string)$p['id'],
            'url' => $url,
            'file_name' => $p['file_name'] ?? 'Minh chứng',
            'loai_minh_chung' => $p['loai_minh_chung']
        ];

        $loai = $p['loai_minh_chung'] ?: 'sdb_ck';
        if (!isset($proofs[$loai])) {
            $proofs[$loai] = [];
        }
        $proofs[$loai][] = $item;
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'nhat_ky' => $nhat_ky,
            'details' => $details,
            'proofs' => $proofs
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
