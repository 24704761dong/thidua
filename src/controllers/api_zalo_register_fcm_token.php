<?php
// File: src/controllers/api_zalo_register_fcm_token.php

require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('POST, OPTIONS');
zalo_handle_options();

$payload = zalo_require_auth();

$data = json_decode(file_get_contents('php://input'), true);
$fcm_token = $data['fcm_token'] ?? $data['token'] ?? null;

if (!$fcm_token) {
    echo json_encode(['success' => false, 'message' => 'FCM Token is required']);
    exit();
}

try {
    $db = get_db_connection();
    $student_id = $payload['student_id'];

    // Insert or Update token
    $stmt = $db->prepare("
        INSERT INTO fcm_tokens (user_id, user_type, token, created_at, updated_at) 
        VALUES (?, 'hoc_sinh', ?, NOW(), NOW()) 
        ON DUPLICATE KEY UPDATE updated_at = NOW()
    ");
    $stmt->execute([$student_id, $fcm_token]);

    echo json_encode(['success' => true, 'message' => 'FCM Token registered successfully']);
} catch (Exception $e) {
    zalo_api_error('Failed to register FCM token', 500, $e);
}
