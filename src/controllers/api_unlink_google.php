<?php
// File: src/controllers/api_unlink_google.php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../config/database.php';
try {
    $db = get_db_connection();
    $stmt = $db->prepare("UPDATE users SET google_id = NULL, email = NULL, verified_email = NULL WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    echo json_encode(['success' => true, 'message' => 'Đã hủy liên kết Google thành công.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
