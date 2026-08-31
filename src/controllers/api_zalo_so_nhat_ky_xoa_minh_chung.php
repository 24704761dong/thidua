<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('POST, OPTIONS');
zalo_handle_options();

// File: src/controllers/api_zalo_so_nhat_ky_xoa_minh_chung.php
require_once __DIR__ . '/../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->safeLoad();

try {
    $db = get_db_connection();
    
    $payload = zalo_authenticate_request();
    $student_id = $payload['student_id'];
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Phương thức không hợp lệ.");
    }

    $nam_hoc_id = zalo_get_nam_hoc_id();
    if (!$nam_hoc_id) {
        $stmt_hs = $db->prepare("
            SELECT (SELECT MAX(nam_hoc_id) FROM quatrinh_hoc_tap WHERE ma_hoc_sinh = hs.ma_hoc_sinh) as nam_hoc_id 
            FROM ho_so_hoc_sinh hs 
            WHERE hs.id = ?
        ");
        $stmt_hs->execute([$student_id]);
        $nam_hoc_id = $stmt_hs->fetchColumn();
    }

    $raw_input = file_get_contents('php://input');
    $data = json_decode($raw_input, true) ?: [];
    $proof_id = $data['proof_id'] ?? $_POST['proof_id'] ?? null;

    if ($proof_id === null || $proof_id === '') {
        throw new Exception("Thiếu ID minh chứng.");
    }

    // Lấy đường dẫn file trước khi xóa + kiểm tra quyền
    $stmt_get = $db->prepare("
        SELECT mc.file_path, mc.storage_driver, mc.cloud_key
        FROM so_nhat_ky_minh_chung mc
        JOIN so_nhat_ky_online snk ON mc.nhat_ky_id = snk.id
        WHERE mc.id = ? AND (snk.nguoi_nhap_id = ? OR snk.lop_hoc_id IN (SELECT qt.lop_hoc_id FROM quatrinh_hoc_tap qt JOIN ho_so_hoc_sinh hs ON qt.ma_hoc_sinh = hs.ma_hoc_sinh WHERE hs.id = ?))
    ");
    $stmt_get->execute([$proof_id, $student_id, $student_id]);
    $proof_data = $stmt_get->fetch(PDO::FETCH_ASSOC);

    if (!$proof_data) {
        throw new Exception("Minh chứng không hợp lệ hoặc không có quyền xóa.");
    }

    // Xóa file trên Cloud R2 nếu có
    if (in_array($proof_data['storage_driver'], ['r2', 'cloud']) && !empty($proof_data['cloud_key'])) {
        require_once __DIR__ . '/../lib/StorageService.php';
        try {
            $storage = new StorageService();
            $storage->delete($proof_data['cloud_key']);
        } catch (Exception $e) {
            // Log lỗi xóa mây nhưng vẫn tiếp tục xóa CSDL
            error_log("Failed to delete cloud key: " . $proof_data['cloud_key'] . " - " . $e->getMessage());
        }
    } else if ($proof_data['storage_driver'] === 'onedrive' && !empty($proof_data['cloud_key'])) {
        try {
            $ms_email = $_ENV['MS_ONEDRIVE_BACKUP_EMAIL'] ?? '';
            if (!empty($ms_email)) {
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
                if (!empty($data['access_token'])) {
                    $token = $data['access_token'];
                    $url = "https://graph.microsoft.com/v1.0/users/" . rawurlencode($ms_email) . "/drive/items/" . rawurlencode($proof_data['cloud_key']);
                    $client->delete($url, [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $token,
                        ]
                    ]);
                }
            }
        } catch (Exception $e) {
            error_log("Failed to delete from OneDrive: " . $proof_data['cloud_key'] . " - " . $e->getMessage());
        }
    }

    // Xóa file vật lý (thumbnail) trên server local nếu có
    $file_path = $proof_data['file_path'];
    if ($file_path && !str_starts_with($file_path, 'http')) {
        $doc_root = $_SERVER['DOCUMENT_ROOT'] ?? 'C:/xampp/htdocs';
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $doc_root = 'E:/VPS/htdocs'; 
        }
        $absolute_path = $doc_root . $file_path;
        if (file_exists($absolute_path)) {
            unlink($absolute_path);
        }
    }

    // Xóa trong CSDL
    $stmt_delete = $db->prepare("DELETE FROM so_nhat_ky_minh_chung WHERE id = ?");
    $stmt_delete->execute([$proof_id]);

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    error_log("Delete Proof Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    file_put_contents(__DIR__ . '/../../public_lookup_error.log', date('Y-m-d H:i:s') . ' Delete Error: ' . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
