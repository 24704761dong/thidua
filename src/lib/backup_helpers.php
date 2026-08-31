<?php
// File: src/lib/backup_helpers.php

/**
 * Lấy dữ liệu của một hoặc nhiều bảng từ CSDL.
 * @param PDO $db Đối tượng kết nối CSDL.
 * @param array $tables Mảng chứa tên các bảng cần lấy.
 * @return array Dữ liệu của các bảng.
 */
function get_tables_data($db, $tables) {
    $data = [];
    foreach ($tables as $table) {
        $stmt = $db->query("SELECT * FROM {$table}");
        $data[$table] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return $data;
}