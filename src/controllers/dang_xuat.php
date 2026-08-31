<?php
// File: src/controllers/dang_xuat.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';

$db = get_db_connection();

$rememberCookie = $_COOKIE['remember_me'] ?? null;
$sessionUserId = $_SESSION['user_id'] ?? null;
$sessionStudentId = $_SESSION['student_id'] ?? null;

$idsToClear = [];
if ($sessionUserId) {
    $idsToClear['users'][] = (int) $sessionUserId;
}
if ($sessionStudentId) {
    $idsToClear['hoc_sinh'][] = (int) $sessionStudentId;
}

if (!empty($rememberCookie)) {
    $parts = explode(':', $rememberCookie, 2);
    if (count($parts) === 2) {
        $cookieId = (int) $parts[0];
        if ($cookieId > 0) {
            $idsToClear['users'][] = $cookieId;
            $idsToClear['hoc_sinh'][] = $cookieId;
        }
    }
}

// Xóa token remember_me trong CSDL
foreach ($idsToClear as $table => $idList) {
    $uniqueIds = array_unique(array_filter($idList, fn($id) => $id > 0));
    if (empty($uniqueIds)) {
        continue;
    }
    $placeholders = implode(',', array_fill(0, count($uniqueIds), '?'));
    $sql = "UPDATE {$table} SET remember_token = NULL WHERE id IN ({$placeholders})";
    $stmt = $db->prepare($sql);
    $stmt->execute($uniqueIds);
}

// Xóa toàn bộ biến session
$_SESSION = [];

// Xóa cookie remember_me nếu tồn tại
setcookie('remember_me', '', time() - 3600, '/thidua/', '', false, true);
setcookie('remember_me', '', time() - 3600, '/', '', false, true);
setcookie('student_remember_me', '', time() - 3600, '/thidua/', '', false, true);
setcookie('student_remember_me', '', time() - 3600, '/', '', false, true);

// Hủy session hiện hành và cookie session_id
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

session_destroy();

if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit();
}

$reason = $_GET['reason'] ?? '';
if ($reason === 'inactive') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash_message'] = [
        'type' => 'warning',
        'message' => 'Phiên làm việc đã tự động đăng xuất do không có hoạt động.'
    ];
}

header('Location: /thidua/tracuu?show_login=1' . ($reason === 'inactive' ? '&reason=inactive' : ''));
exit();
