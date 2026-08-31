<?php
// File: src/controllers/api_toggle_admin_login_alert.php (File mới)
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403); exit();
}
require_once __DIR__ . '/../../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);
$is_enabled = $data['enabled'] ?? false;
$user_id = $_SESSION['user_id'];

try {
    $db = get_db_connection();
    $stmt = $db->prepare("UPDATE users SET nhan_canh_bao_dang_nhap = ? WHERE id = ?");
    $stmt->execute([$is_enabled ? 1 : 0, $user_id]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}