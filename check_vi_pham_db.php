<?php
require_once __DIR__ . '/config/database.php';
$db = get_db_connection();

echo "cau_hinh_vi_pham contents:\n";
$stmt = $db->query("SELECT * FROM cau_hinh_vi_pham");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\nraw_cau_hinh_vi_pham contents:\n";
try {
    $stmt = $db->query("SELECT * FROM raw_cau_hinh_vi_pham");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Error on raw_cau_hinh_vi_pham: " . $e->getMessage() . "\n";
}

echo "\nnam_hoc table contents:\n";
$stmt = $db->query("SELECT * FROM nam_hoc");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
