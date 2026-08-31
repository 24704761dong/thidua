<?php
/**
 * Lấy tất cả người dùng từ CSDL.
 */
function get_all_users(PDO $db) {
    $stmt = $db->query("SELECT * FROM users ORDER BY id ASC");
    return $stmt->fetchAll();
}