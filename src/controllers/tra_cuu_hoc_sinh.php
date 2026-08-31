<?php
// File: src/controllers/tra_cuu_hoc_sinh.php

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}

// Chỉ cần gọi view để hiển thị
require_once __DIR__ . '/../views/tra_cuu_hoc_sinh.php';