<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('GET, OPTIONS');
zalo_handle_options();

try {
    $db = get_db_connection();
    
    $payload = zalo_authenticate_request();
    $student_id = $payload['student_id'];
    $tuan_hoc_id = $_GET['tuan_id'] ?? null;
    $batch_id = $_GET['batch_id'] ?? null;

    if (!$tuan_hoc_id) {
        throw new Exception("Thiếu ID tuần học");
    }

    $query = "
        SELECT id, file_name, file_path, cloud_id, cloud_url, trang_thai, thoi_gian_tao 
        FROM minh_chung_vi_pham 
        WHERE tuan_hoc_id = ? AND nguoi_nhap_id = ? AND nguoi_nhap_type = 'student'
    ";
    $params = [$tuan_hoc_id, $student_id];

    if ($batch_id === 'null') {
        $query .= " AND batch_id IS NULL";
    } elseif ($batch_id) {
        $query .= " AND batch_id = ?";
        $params[] = $batch_id;
    } else {
        $query .= " AND batch_id IS NULL"; 
    }

    $query .= " ORDER BY thoi_gian_tao DESC";

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $proofs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Xử lý URL
    $storage = null;
    foreach ($proofs as &$p) {
        $url = '';
        if (!empty($p['cloud_id']) && $p['trang_thai'] === 'synced') {
            if (!$storage) {
                require_once __DIR__ . '/../lib/StorageService.php';
                $storage = new StorageService();
            }
            try {
                $url = $storage->getTemporaryUrl($p['cloud_id'], '+60 minutes');
            } catch (Exception $e) {
                $url = '';
            }
        }
        
        if (empty($url) && !empty($p['cloud_url'])) {
            if (strpos($p['cloud_url'], 'http') === 0) {
                $url = $p['cloud_url'];
            } else {
                $url = 'https://c3binhson.edu.vn' . $p['cloud_url'];
            }
        }
        
        if (empty($url) && !empty($p['file_path'])) {
            $url = 'https://c3binhson.edu.vn/thidua/' . ltrim($p['file_path'], '/');
        }
        $p['url'] = $url;
    }

    echo json_encode([
        'success' => true,
        'data' => $proofs
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
