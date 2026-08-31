<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('GET, OPTIONS');
zalo_handle_options();

try {
    $db = get_db_connection();
    
    $payload = zalo_authenticate_request();
    $hoc_sinh_id = $payload['student_id'];
    
    $nam_hoc_header = zalo_get_nam_hoc_id();
    
    $sql = "SELECT id, ly_do, tu_ngay, den_ngay, trang_thai, ngay_tao, ly_do_tu_choi, cloud_key 
            FROM xin_vang_hoc 
            WHERE hoc_sinh_id = ?";
            
    $params = [$hoc_sinh_id];
    
    if ($nam_hoc_header) {
        $sql .= " AND (nam_hoc_id = ? OR nam_hoc_id IS NULL)";
        $params[] = $nam_hoc_header;
    }
    
    $sql .= " ORDER BY ngay_tao DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $danh_sach = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    require_once __DIR__ . '/../lib/StorageService.php';
    $storage = null;
    try {
        $storage = new StorageService();
    } catch (Exception $e) {
        $storage = null;
    }

    foreach ($danh_sach as &$item) {
        $item['minh_chung_url'] = null;
        if (!empty($item['cloud_key'])) {
            if ($storage) {
                try {
                    $item['minh_chung_url'] = $storage->getTemporaryUrl($item['cloud_key'], '+120 minutes');
                } catch (Exception $e) {
                    $item['minh_chung_url'] = '/thidua/api/get-presigned-url?key=' . urlencode($item['cloud_key']);
                }
            } else {
                $item['minh_chung_url'] = '/thidua/api/get-presigned-url?key=' . urlencode($item['cloud_key']);
            }
        }
    }
    unset($item);
    
    zalo_api_success($danh_sach);

} catch (Exception $e) {
    zalo_api_error('Lỗi khi lấy danh sách xin vắng học', 500, $e);
}
