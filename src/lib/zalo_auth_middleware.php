<?php
/**
 * File: src/lib/zalo_auth_middleware.php
 * Middleware tập trung cho tất cả API Zalo Mini App.
 * 
 * Chức năng:
 * - CORS headers chuẩn (restrict origin)
 * - Xác thực JWT token (bao gồm kiểm tra exp)
 * - Trả lỗi JSON chuẩn (không leak thông tin nhạy cảm)
 * - Lấy nam_hoc_id từ header X-Nam-Hoc-Id
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/zalo_jwt_helper.php';

/**
 * Thiết lập CORS headers chuẩn cho API Zalo.
 * Chỉ cho phép các origin hợp lệ thay vì wildcard '*'.
 * 
 * @param string $methods HTTP methods cho phép (mặc định: GET, POST, OPTIONS)
 */
function zalo_api_cors_headers(string $methods = 'GET, POST, OPTIONS'): void {
    $allowed_origins = [
        'https://c3binhson.edu.vn',
        'http://localhost',
        'http://localhost:3000',
        'http://localhost:5173',
    ];

    // Zalo Mini App webview gửi request từ các domain zalo
    $allowed_patterns = [
        '/^https?:\/\/.*\.zalo\.me$/',
        '/^https?:\/\/.*\.zaloapp\.com$/',
        '/^https?:\/\/.*\.zadn\.vn$/',
        '/^https?:\/\/h5\.zdn\.vn$/',
    ];

    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

    $is_allowed = false;
    if (in_array($origin, $allowed_origins)) {
        $is_allowed = true;
    } else {
        foreach ($allowed_patterns as $pattern) {
            if (preg_match($pattern, $origin)) {
                $is_allowed = true;
                break;
            }
        }
    }

    if ($is_allowed && !empty($origin)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Vary: Origin');
    } else {
        // Nếu origin không hợp lệ hoặc không có (request trực tiếp), 
        // vẫn cho phép vì Zalo webview có thể không gửi Origin header
        header('Access-Control-Allow-Origin: *');
    }

    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Methods: ' . $methods);
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Nam-Hoc-Id');
}

/**
 * Xử lý preflight OPTIONS request.
 * Gọi hàm này ngay sau zalo_api_cors_headers().
 */
function zalo_handle_options(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

/**
 * Xác thực JWT token từ header Authorization.
 * 
 * @return array Payload đã decode (chứa student_id, ma_hoc_sinh, role)
 * @throws Nếu token không hợp lệ, tự động trả 401 JSON và exit()
 */
function zalo_authenticate_request(): array {
    $headers = function_exists('apache_request_headers') ? apache_request_headers() : [];
    
    // Fallback cho server không hỗ trợ apache_request_headers
    if (empty($headers)) {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header_key = str_replace('_', '-', substr($key, 5));
                $headers[$header_key] = $value;
            }
        }
    }

    $auth_header = $headers['Authorization'] ?? $headers['authorization'] ?? $headers['AUTHORIZATION'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';

    if (!$auth_header || !preg_match('/Bearer\s+(\S+)$/i', $auth_header, $matches)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Token không hợp lệ hoặc bị thiếu.']);
        exit();
    }

    $jwt = $matches[1];
    $payload = zalo_jwt_decode($jwt);

    if (!$payload) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Token đã hết hạn hoặc không hợp lệ. Vui lòng đăng nhập lại.']);
        exit();
    }

    if (!isset($payload['student_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Token không chứa thông tin người dùng.']);
        exit();
    }

    return $payload;
}

/**
 * Lấy nam_hoc_id từ header X-Nam-Hoc-Id (nếu có).
 * 
 * @return int|null Nam hoc ID hoặc null nếu không gửi
 */
function zalo_get_nam_hoc_id(): ?int {
    $headers = function_exists('apache_request_headers') ? apache_request_headers() : [];
    if (empty($headers)) {
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header_key = str_replace('_', '-', substr($key, 5));
                $headers[$header_key] = $value;
            }
        }
    }
    
    // Tìm header không phân biệt hoa thường
    foreach ($headers as $key => $value) {
        if (strtolower($key) === 'x-nam-hoc-id' && is_numeric($value)) {
            return (int)$value;
        }
    }
    
    return null;
}

/**
 * Trả về JSON lỗi chuẩn và exit.
 * Không expose thông tin nhạy cảm từ Exception.
 * 
 * @param string $message Thông báo lỗi hiển thị cho user
 * @param int $http_code HTTP status code (mặc định 500)
 * @param \Throwable|null $exception Exception gốc (chỉ ghi log, không trả về client)
 */
function zalo_api_error(string $message = 'Lỗi hệ thống, vui lòng thử lại sau.', int $http_code = 500, ?\Throwable $exception = null): void {
    if ($exception) {
        error_log('[ZALO API ERROR] ' . $exception->getMessage() . ' | File: ' . $exception->getFile() . ':' . $exception->getLine());
    }
    
    http_response_code($http_code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit();
}

/**
 * Trả về JSON thành công chuẩn.
 * 
 * @param mixed $data Dữ liệu trả về
 * @param string $message Thông báo (tùy chọn)
 */
function zalo_api_success($data = null, string $message = ''): void {
    $response = ['success' => true];
    if ($message) $response['message'] = $message;
    if ($data !== null) $response['data'] = $data;
    echo json_encode($response);
    exit();
}
