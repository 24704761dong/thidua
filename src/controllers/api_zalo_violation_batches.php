<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('GET, OPTIONS');
zalo_handle_options();

try {
    $db = get_db_connection();
    
    $payload = zalo_authenticate_request();
    $student_id = $payload['student_id'];
    $tuan_hoc_id = $_GET['tuan_id'] ?? null;

    if (!$tuan_hoc_id) {
        throw new Exception("Thiếu ID tuần học");
    }

    // Lấy danh sách các đợt đã gửi
    $query = "
        SELECT 
            batch_id, 
            trang_thai_gui,
            thoi_gian_gui,
            COUNT(id) as so_luong_vi_pham
        FROM vi_pham_tam_thoi
        WHERE tuan_hoc_id = ? AND nguoi_nhap_id = ? AND batch_id IS NOT NULL
        GROUP BY batch_id, trang_thai_gui, thoi_gian_gui
        ORDER BY thoi_gian_gui DESC
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$tuan_hoc_id, $student_id]);
    $batches = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Bổ sung minh chứng cho từng batch
    if (count($batches) > 0) {
        $batch_ids = array_column($batches, 'batch_id');
        $in_clause = str_repeat('?,', count($batch_ids) - 1) . '?';
        
        $proof_query = "
            SELECT id, batch_id, file_name, file_path, cloud_id, cloud_url, trang_thai 
            FROM minh_chung_vi_pham
            WHERE batch_id IN ($in_clause)
        ";
        $stmt_proof = $db->prepare($proof_query);
        $stmt_proof->execute($batch_ids);
        $proofs = $stmt_proof->fetchAll(PDO::FETCH_ASSOC);

        $proof_map = [];
        $storage = null;
        
        foreach ($proofs as $p) {
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
            
            $proof_map[$p['batch_id']][] = [
                'id' => $p['id'],
                'file_name' => $p['file_name'],
                'url' => $url
            ];
        }

        foreach ($batches as &$b) {
            $b['proofs'] = $proof_map[$b['batch_id']] ?? [];
        }
    }

    echo json_encode([
        'success' => true,
        'data' => $batches
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
