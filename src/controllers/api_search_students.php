<?php
// File: src/controllers/api_search_students.php

if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../lib/helpers.php';

$query = $_GET['query'] ?? '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit();
}

try {
    $db = get_db_connection();
    $sql = "
        SELECT 
            hs.id, 
            hs.ma_hoc_sinh, 
            (CONCAT(hs.ho_dem, ' ', hs.ten)) as ho_ten, 
            lh.ten_lop, 
            hs.ngay_sinh
        FROM hoc_sinh hs
        JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id
        WHERE (CONCAT(hs.ho_dem, ' ', hs.ten)) LIKE ?
        ORDER BY hs.ten , hs.ho_dem 
        LIMIT 25
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute(['%' . $query . '%']);
    $results = $stmt->fetchAll();

    foreach ($results as &$row) {
        $row['ngay_sinh'] = format_date_display($row['ngay_sinh'] ?? '');
    }
    unset($row);

    echo json_encode($results);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}