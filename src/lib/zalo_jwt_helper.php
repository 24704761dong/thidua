<?php
// File: src/lib/zalo_jwt_helper.php
// Tiện ích tạo và giải mã JWT cho Zalo Mini App
// Secret key được đọc từ biến môi trường ZALO_JWT_SECRET

/**
 * Lấy JWT secret key từ biến môi trường.
 * Fallback về hardcode nếu chưa cấu hình (cần đổi trong .env).
 */
function zalo_jwt_get_secret(): string {
    return $_ENV['ZALO_JWT_SECRET'] ?? 'ThiDua_BinhSon_Secret_Key_2026!';
}

/**
 * Lấy thời hạn token (ngày) từ biến môi trường.
 */
function zalo_jwt_get_expiry_days(): int {
    return (int)($_ENV['ZALO_JWT_EXPIRY_DAYS'] ?? 30);
}

/**
 * Tạo JWT token.
 * 
 * @param array $payload Dữ liệu cần mã hóa (student_id, ma_hoc_sinh, role...)
 * @param string|null $secret Secret key (mặc định lấy từ .env)
 * @return string JWT token
 */
function zalo_jwt_encode(array $payload, ?string $secret = null): string {
    $secret = $secret ?? zalo_jwt_get_secret();
    
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $payload['iat'] = time();
    $payload['exp'] = time() + (86400 * zalo_jwt_get_expiry_days());
    $payload = json_encode($payload);

    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

    return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
}

/**
 * Giải mã và xác minh JWT token.
 * Kiểm tra cả signature và thời hạn (exp).
 * 
 * @param string $jwt Token JWT
 * @param string|null $secret Secret key (mặc định lấy từ .env)
 * @return array|null Payload đã decode, hoặc null nếu token không hợp lệ/hết hạn
 */
function zalo_jwt_decode(string $jwt, ?string $secret = null): ?array {
    $secret = $secret ?? zalo_jwt_get_secret();
    
    $tokenParts = explode('.', $jwt);
    if (count($tokenParts) !== 3) {
        return null;
    }

    $header = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[0]));
    $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1]));
    $signature_provided = $tokenParts[2];

    // Verify signature
    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

    if (!hash_equals($base64UrlSignature, $signature_provided)) {
        return null;
    }

    $decoded = json_decode($payload, true);
    if (!$decoded) {
        return null;
    }

    // Kiểm tra thời hạn token (exp)
    if (isset($decoded['exp']) && $decoded['exp'] < time()) {
        return null;
    }

    return $decoded;
}
