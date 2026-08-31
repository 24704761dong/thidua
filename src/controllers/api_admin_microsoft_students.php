<?php
// File: src/controllers/api_admin_microsoft_students.php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_vai_tro'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../lib/hoc_sinh_db.php';

try {
    $db = get_db_connection();

    $filters = [
        'keyword' => trim($_GET['keyword'] ?? ''),
        'khoi'    => $_GET['khoi'] ?? 'all',
        'lop_id'  => $_GET['lop_id'] ?? 'all',
    ];

    $page  = max(1, (int)($_GET['page'] ?? 1));
    $limit = (int)($_GET['limit'] ?? 50);
    if ($limit < 10) $limit = 10;
    if ($limit > 200) $limit = 200;
    $offset = ($page - 1) * $limit;

    $students = get_all_hoc_sinh($db, $filters, ['limit' => $limit, 'offset' => $offset]);
    $total = count_hoc_sinh($db, $filters);

    $primaryDomain = $_ENV['MS_PRIMARY_DOMAIN'] ?? '';

    $data = array_map(function ($row) use ($primaryDomain) {
        $full_name = trim(($row['ho_dem'] ?? '') . ' ' . ($row['ten'] ?? ''));
        $personal = $row['email'] ?? '';
        $eduEmail = ($primaryDomain && !empty($row['ma_hoc_sinh']))
            ? strtolower($row['ma_hoc_sinh']) . '@' . $primaryDomain
            : null;

        return [
            'id' => (int)$row['id'],
            'ho_ten' => $full_name,
            'ten_lop' => $row['ten_lop'] ?? '',
            'email_ca_nhan' => $personal,
            'email_edu' => $eduEmail,
            'ma_hoc_sinh' => $row['ma_hoc_sinh'] ?? '',
            'trang_thai_tai_khoan' => $row['trang_thai_tai_khoan'] ?? '',
            'gioi_tinh' => $row['gioi_tinh'] ?? '',
            'chuc_vu' => $row['chuc_vu'] ?? '',
            'anh_the' => $row['anh_the'] ?? null,
        ];
    }, $students);

    $ms_ready = !empty($_ENV['MS_TENANT_ID'])
        && !empty($_ENV['MS_CLIENT_ID'])
        && !empty($_ENV['MS_CLIENT_SECRET'])
        && !empty($_ENV['MS_PRIMARY_DOMAIN']);

    echo json_encode([
        'success' => true,
        'data' => $data,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => $limit > 0 ? ceil($total / $limit) : 1,
        ],
        'config' => [
            'ms_ready' => $ms_ready,
            'primary_domain' => $_ENV['MS_PRIMARY_DOMAIN'] ?? null,
        ],
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ', 'error' => $e->getMessage()]);
}
