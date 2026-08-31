<?php
// File: src/controllers/api_zalo_favorite_tasks.php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('GET, POST, OPTIONS');
zalo_handle_options();

$payload = zalo_authenticate_request();
$student_id = $payload['student_id'] ?? null;

if (!$student_id) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$default_tasks = ['lich-hoc-thi', 'vi-pham', 'thanh-tich', 'xin-vang-hoc'];

try {
    $db = get_db_connection();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $db->prepare("SELECT tasks FROM hoc_sinh_favorite_tasks WHERE hoc_sinh_id = ?");
        $stmt->execute([$student_id]);
        $raw = $stmt->fetchColumn();

        if ($raw) {
            $tasks = json_decode($raw, true);
            if (is_array($tasks) && !empty($tasks)) {
                echo json_encode(['success' => true, 'tasks' => array_values($tasks)]);
                exit;
            }
        }

        echo json_encode(['success' => true, 'tasks' => $default_tasks]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            $input = $_POST;
        }

        $tasks = $input['tasks'] ?? [];
        if (!is_array($tasks)) {
            $tasks = [];
        }

        // Lọc chuỗi hợp lệ và giới hạn tối đa 4 mục
        $clean_tasks = [];
        foreach ($tasks as $t) {
            if (is_string($t) && trim($t) !== '') {
                $clean_tasks[] = trim($t);
            }
        }
        $clean_tasks = array_slice(array_unique($clean_tasks), 0, 4);

        if (empty($clean_tasks)) {
            $clean_tasks = $default_tasks;
        }

        $stmt = $db->prepare("
            INSERT INTO hoc_sinh_favorite_tasks (hoc_sinh_id, tasks) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE tasks = VALUES(tasks)
        ");
        $stmt->execute([$student_id, json_encode($clean_tasks, JSON_UNESCAPED_UNICODE)]);

        echo json_encode([
            'success' => true, 
            'message' => 'Lưu tiện ích yêu thích thành công',
            'tasks' => $clean_tasks
        ]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
} catch (Exception $e) {
    http_response_code(500);
    zalo_api_error('Lỗi lưu cài đặt tiện ích.', 500, $e);
}
