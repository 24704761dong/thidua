<?php
require_once __DIR__ . '/../../config/database.php';

if (isset($_GET['id'])) {
    $student_id = $_GET['id'];
    $db = get_db_connection();

    // Sửa câu lệnh SQL để JOIN với bảng lop_hoc và lấy ten_lop
    $stmt = $db->prepare("
        SELECT hs.*, lh.ten_lop 
        FROM hoc_sinh hs 
        JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id 
        WHERE hs.id = ?
    ");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch();

    if ($student) {
        // Không cần làm gì với ngày sinh vì CSDL đã lưu đúng định dạng
        header('Content-Type: application/json');
        echo json_encode($student);
    } else {
        http_response_code(404);
        echo json_encode(['message' => 'Học sinh không tồn tại.']);
    }
} else {
    http_response_code(400);
    echo json_encode(['message' => 'Thiếu ID học sinh.']);
}