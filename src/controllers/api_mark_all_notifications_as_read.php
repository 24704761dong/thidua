<?php
// File: src/controllers/api_mark_all_notifications_as_read.php (File mới)
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) exit();

require_once __DIR__ . '/../../config/database.php';
$db = get_db_connection();
$db->exec("UPDATE thong_bao SET da_xem = 1 WHERE da_xem = 0");
echo json_encode(['success' => true]);