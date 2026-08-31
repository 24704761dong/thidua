<?php
// File: src/controllers/api_ctv_xoa_minh_chung_nhat_ky.php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['student_id']) || !($_SESSION['student_permissions']['so_nhat_ky_online'] ?? false)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập.']);
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);
$proof_id = $data['proof_id'] ?? null;

if ($proof_id === null || $proof_id === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Thiếu ID minh chứng.']);
    exit();
}

try {
    $db = get_db_connection();

    $current_nam_hoc = $_SESSION['current_nam_hoc_id'] ?? 1;

    // Lấy đường dẫn file trước khi xóa + kiểm tra quyền
    $stmt_get = $db->prepare("
        SELECT mc.file_path, mc.storage_driver, mc.cloud_key
        FROM so_nhat_ky_minh_chung mc
        JOIN so_nhat_ky_online snk ON mc.nhat_ky_id = snk.id
        JOIN raw_tuan_hoc t ON snk.tuan_hoc_id = t.id
        WHERE mc.id = ? AND snk.nguoi_nhap_id = ? AND t.nam_hoc_id = ?
    ");
    $stmt_get->execute([$proof_id, $_SESSION['student_id'], $current_nam_hoc]);
    $proof_data = $stmt_get->fetch(PDO::FETCH_ASSOC);

    if (!$proof_data) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Minh chứng không hợp lệ hoặc không có quyền xóa.']);
        exit();
    }

    // Xóa file trên Cloud R2 nếu có
    if ($proof_data['storage_driver'] === 'r2' && !empty($proof_data['cloud_key'])) {
        require_once __DIR__ . '/../lib/StorageService.php';
        try {
            $storage = new StorageService();
            $storage->delete($proof_data['cloud_key']);
        } catch (Exception $e) {
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
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi CSDL khi xóa.']);
}