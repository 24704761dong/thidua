<?php
// File: src/lib/login_logger.php

function write_login_log($action, $data = []) {
    $log_dir = __DIR__ . '/../../logs';
    if (!is_dir($log_dir)) {
        @mkdir($log_dir, 0777, true);
    }
    $log_file = $log_dir . '/login_debug.log';
    
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $sess_id = session_id() ?: 'NO_SESSION';
    $uid = $_SESSION['user_id'] ?? 'NO_UID';
    $role = $_SESSION['user_vai_tro'] ?? 'NO_ROLE';
    $uri = $_SERVER['REQUEST_URI'] ?? '';

    $detail = is_string($data) ? $data : json_encode($data, JSON_UNESCAPED_UNICODE);
    
    $line = "[$timestamp] [IP:$ip] [SESS:$sess_id] [USER:$uid|$role] [ACTION:$action] [URI:$uri]\n   -> $detail\n\n";
    @file_put_contents($log_file, $line, FILE_APPEND);
}
