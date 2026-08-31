<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}

$db = get_db_connection();

// Lấy danh sách
$stmt = $db->query("SELECT * FROM nam_hoc ORDER BY id DESC");
$nam_hocs = $stmt->fetchAll();

require_once __DIR__ . '/../views/admin_quan_ly_nam_hoc.php';
