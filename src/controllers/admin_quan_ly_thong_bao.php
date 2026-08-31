<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin'])) {
    header('Location: /thidua/dang-nhap');
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../lib/helpers.php';

$db = get_db_connection();

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 50;
$offset = ($page - 1) * $limit;

$stmt_total = $db->query("SELECT COUNT(*) FROM thong_bao");
$total_records = $stmt_total->fetchColumn();
$total_pages = max(1, (int)ceil($total_records / $limit));

$stmt = $db->prepare("SELECT * FROM thong_bao ORDER BY thoi_gian DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../views/admin_quan_ly_thong_bao.php';
