<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
if (isset($data['nam_hoc_id']) && is_numeric($data['nam_hoc_id'])) {
    $_SESSION['working_nam_hoc_id'] = (int)$data['nam_hoc_id'];
    $_SESSION['current_nam_hoc_id'] = (int)$data['nam_hoc_id']; // For backwards compatibility
    $_SESSION['nam_hoc_id'] = (int)$data['nam_hoc_id']; // For admin_header.php
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'ID Năm học không hợp lệ']);
}
