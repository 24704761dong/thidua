<?php
require_once __DIR__ . '/../../config/database.php';

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $db = get_db_connection();

    // NÂNG CẤP: Lấy thêm cột `quyen_han` và `sdt`
    $stmt = $db->prepare("SELECT id, ten_dang_nhap, ho_ten, email, sdt, vai_tro, ghi_chu, avatar, quyen_han FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if ($user) {
        header('Content-Type: application/json');
        echo json_encode($user);
    } else {
        http_response_code(404);
        echo json_encode(['message' => 'Người dùng không tồn tại.']);
    }
}