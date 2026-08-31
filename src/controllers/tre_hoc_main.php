<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu'); exit();
}

$tuan_id = $_GET['tuan_id'] ?? null;
if (!$tuan_id) die('Vui lòng chọn tuần học.');

require_once __DIR__ . '/../../config/database.php';
$db = get_db_connection();

$stmt = $db->prepare("SELECT * FROM tuan_hoc WHERE id = ?");
$stmt->execute([$tuan_id]);
$tuan_hoc = $stmt->fetch();

if (!$tuan_hoc) die('Tuần học không hợp lệ.');

unset($_SESSION['tre_hoc_data']);

require_once __DIR__ . '/../views/admin_tre_hoc_main.php';