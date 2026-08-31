<?php
// File: src/controllers/admin_duyet_vang_hoc.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (!isset($_SESSION['user_id'])) {
    header('Location: /thidua/tracuu');
    exit();
}

require_once __DIR__ . '/../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->safeLoad();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../lib/StorageService.php';

$db = get_db_connection();
$storage = new StorageService();

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_vai_tro'] ?? 'user';
$user_permissions = $_SESSION['user_permissions'] ?? [];

// Kiểm tra quyền
if ($user_role !== 'admin' && !in_array('duyet_vang_hoc', $user_permissions) && !in_array('all', $user_permissions)) {
    die('Bạn không có quyền truy cập trang này.');
}

// Lấy năm học hiện tại từ session
$nam_hoc_id = $_SESSION['nam_hoc_id'] ?? null;
if (!$nam_hoc_id) {
    // Nếu không có trong session, lấy năm học mặc định
    $stmt = $db->query("SELECT id FROM nam_hoc WHERE is_mac_dinh = 1 LIMIT 1");
    $nam_hoc_id = $stmt->fetchColumn();
}

$status_filter = $_GET['status'] ?? 'all';

// Xây dựng câu truy vấn
$sql = "SELECT x.*, CONCAT(hs.ho_dem, ' ', hs.ten) AS ho_ten, hs.ma_hoc_sinh, l.ten_lop 
        FROM xin_vang_hoc x 
        JOIN ho_so_hoc_sinh hs ON x.hoc_sinh_id = hs.id 
        LEFT JOIN lop_hoc l ON hs.lop_hoc_id = l.id
        WHERE 1=1";
$params = [];

if ($nam_hoc_id) {
    $sql .= " AND (x.nam_hoc_id = ? OR x.nam_hoc_id IS NULL)";
    $params[] = $nam_hoc_id;
}

if ($status_filter !== 'all') {
    $sql .= " AND x.trang_thai = ?";
    $params[] = (int)$status_filter;
}

// Nếu là user (không phải admin) -> Chỉ lấy học sinh thuộc lớp họ chủ nhiệm
if ($user_role !== 'admin' && !in_array('all', $user_permissions)) {
    $sql .= " AND l.giao_vien_id = ?";
    $params[] = $user_id;
}

$sql .= " ORDER BY x.ngay_tao DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tạo link minh chứng
foreach ($requests as &$req) {
    if (!empty($req['cloud_key'])) {
        try {
            $req['minh_chung_url'] = $storage->getTemporaryUrl($req['cloud_key'], '+60 minutes');
        } catch (Exception $e) {
            $req['minh_chung_url'] = null;
        }
    } else {
        $req['minh_chung_url'] = null;
    }
}

// Load view
require_once __DIR__ . '/../views/admin_duyet_vang_hoc.php';
