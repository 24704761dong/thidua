<?php
// File: src/controllers/api_admin_microsoft_account.php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

use GuzzleHttp\Client;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_vai_tro'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$ms_ready = !empty($_ENV['MS_TENANT_ID'])
    && !empty($_ENV['MS_CLIENT_ID'])
    && !empty($_ENV['MS_CLIENT_SECRET'])
    && !empty($_ENV['MS_PRIMARY_DOMAIN']);

$action = $_POST['action'] ?? '';
$student_id = isset($_POST['student_id']) ? (int)$_POST['student_id'] : 0;

if (!$ms_ready) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Chưa cấu hình Microsoft EDU (MS_TENANT_ID, MS_CLIENT_ID, MS_CLIENT_SECRET, MS_PRIMARY_DOMAIN) trong .env',
        'ms_ready' => false,
    ]);
    exit();
}

if (!$student_id || !in_array($action, ['create', 'reset', 'sync_photo'], true)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Thiếu student_id hoặc action không hợp lệ']);
    exit();
}

try {
    $db = get_db_connection();
    $stmt = $db->prepare("SELECT id, ma_hoc_sinh, ho_dem, ten, anh_the FROM hoc_sinh WHERE id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$student || empty($student['ma_hoc_sinh'])) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy học sinh hoặc thiếu mã học sinh']);
        exit();
    }

    $fullName = trim(($student['ho_dem'] ?? '') . ' ' . ($student['ten'] ?? '')) ?: $student['ma_hoc_sinh'];
    $username = strtolower($student['ma_hoc_sinh']);
    $upn = $username . '@' . $_ENV['MS_PRIMARY_DOMAIN'];

    $token = getGraphToken();

    if ($action === 'create') {
        $password = generatePassword();
        $result = createEduUser($token, $upn, $fullName, $username, $password);
        $licenseStatus = null; $licenseError = null;
        $sku = trim($_ENV['MS_LICENSE_SKU'] ?? '');
        if ($sku !== '') {
            try {
                $assignRes = assignLicense($token, $result['id'] ?? $upn, $sku);
                $licenseStatus = 'assigned';
            } catch (Exception $le) {
                $licenseStatus = 'failed';
                $licenseError = $le->getMessage();
            }
        }
        echo json_encode([
            'success' => true,
            'message' => 'Đã tạo tài khoản EDU',
            'upn' => $upn,
            'password' => $password,
            'graph_id' => $result['id'] ?? null,
            'license' => [
                'sku' => $sku ?: null,
                'status' => $licenseStatus,
                'error' => $licenseError,
            ],
        ]);
        exit();
    }

    if ($action === 'reset') {
        $password = generatePassword();
        resetEduPassword($token, $upn, $password);
        echo json_encode([
            'success' => true,
            'message' => 'Đã đặt lại mật khẩu',
            'upn' => $upn,
            'password' => $password,
        ]);
        exit();
    }

    if ($action === 'sync_photo') {
        if (empty($student['anh_the'])) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Học sinh chưa có ảnh thẻ']);
            exit();
        }
        $photoPath = __DIR__ . '/../../public/assets/anh_the/' . $student['anh_the'];
        if (!is_file($photoPath)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy file ảnh thẻ trên máy chủ']);
            exit();
        }
        uploadProfilePhoto($token, $upn, $photoPath);
        echo json_encode([
            'success' => true,
            'message' => 'Đã đồng bộ ảnh thẻ',
            'upn' => $upn,
        ]);
        exit();
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi Microsoft/Server', 'error' => $e->getMessage()]);
    exit();
}


function getGraphToken() {
    $client = new Client(['timeout' => 10]);
    $res = $client->post('https://login.microsoftonline.com/' . $_ENV['MS_TENANT_ID'] . '/oauth2/v2.0/token', [
        'form_params' => [
            'grant_type' => 'client_credentials',
            'client_id' => $_ENV['MS_CLIENT_ID'],
            'client_secret' => $_ENV['MS_CLIENT_SECRET'],
            'scope' => 'https://graph.microsoft.com/.default',
        ]
    ]);
    $data = json_decode($res->getBody(), true);
    if (empty($data['access_token'])) throw new Exception('Không lấy được access_token');
    return $data['access_token'];
}

function createEduUser($token, $upn, $displayName, $mailNickname, $password) {
    $client = new Client(['timeout' => 10]);
    $body = [
        'accountEnabled' => true,
        'displayName' => $displayName,
        'mailNickname' => $mailNickname,
        'userPrincipalName' => $upn,
        'passwordProfile' => [
            'forceChangePasswordNextSignIn' => true,
            'password' => $password,
        ],
        'usageLocation' => 'VN'
    ];

    $res = $client->post('https://graph.microsoft.com/v1.0/users', [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json'
        ],
        'json' => $body,
    ]);
    return json_decode($res->getBody(), true);
}

function resetEduPassword($token, $upn, $password) {
    $client = new Client(['timeout' => 10]);
    $body = [
        'passwordProfile' => [
            'forceChangePasswordNextSignIn' => true,
            'password' => $password,
        ]
    ];

    $client->patch('https://graph.microsoft.com/v1.0/users/' . rawurlencode($upn), [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json'
        ],
        'json' => $body,
    ]);
}

function assignLicense($token, $userIdOrUpn, $skuId) {
    $client = new Client(['timeout' => 10]);
    $body = [
        'addLicenses' => [
            ['skuId' => $skuId]
        ],
        'removeLicenses' => []
    ];

    $res = $client->post('https://graph.microsoft.com/v1.0/users/' . rawurlencode($userIdOrUpn) . '/assignLicense', [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json'
        ],
        'json' => $body,
    ]);
    $data = json_decode($res->getBody(), true);
    if (isset($data['error'])) {
        throw new Exception($data['error']['message'] ?? 'Assign license failed');
    }
    return $data;
}

function uploadProfilePhoto($token, $userIdOrUpn, $filePath) {
    $client = new Client(['timeout' => 20]);
    $mime = detectMime($filePath);
    // Graph yêu cầu <4MB
    if (filesize($filePath) > 4 * 1024 * 1024) {
        throw new Exception('Ảnh vượt quá 4MB, hãy giảm dung lượng.');
    }
    $stream = fopen($filePath, 'rb');
    if (!$stream) {
        throw new Exception('Không mở được file ảnh.');
    }
    try {
        $client->put('https://graph.microsoft.com/v1.0/users/' . rawurlencode($userIdOrUpn) . '/photo/$value', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => $mime
            ],
            'body' => $stream,
        ]);
    } catch (\GuzzleHttp\Exception\RequestException $ex) {
        $resp = $ex->getResponse();
        $status = $resp ? $resp->getStatusCode() : 0;
        $body = $resp ? (string)$resp->getBody() : '';
        if (is_resource($stream)) fclose($stream);
        throw new Exception('Upload ảnh lỗi (Graph) status ' . $status . ': ' . substr($body, 0, 300));
    } catch (Exception $ex) {
        if (is_resource($stream)) fclose($stream);
        throw $ex;
    }
    if (is_resource($stream)) fclose($stream);
}

function detectMime($filePath) {
    if (function_exists('mime_content_type')) {
        $m = @mime_content_type($filePath);
        if ($m) return $m;
    }
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $m = finfo_file($finfo, $filePath);
            finfo_close($finfo);
            if ($m) return $m;
        }
    }
    return 'image/jpeg';
}

function generatePassword() {
    // Đảm bảo phức tạp: chữ hoa, thường, số, ký tự đặc biệt
    $upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    $lower = 'abcdefghijkmnpqrstuvwxyz';
    $digits = '23456789';
    $special = '@#$%';
    $all = $upper . $lower . $digits . $special;

    $pick = function($chars, $len) {
        $out = '';
        for ($i = 0; $i < $len; $i++) {
            $out .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $out;
    };

    $password =
        $pick($upper, 2) .
        $pick($lower, 4) .
        $pick($digits, 2) .
        $pick($special, 1);
    // Thêm ngẫu nhiên 3 ký tự bất kỳ để tăng độ dài
    $password .= $pick($all, 3);

    return str_shuffle($password);
}
