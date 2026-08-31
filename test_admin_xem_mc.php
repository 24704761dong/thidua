<?php
require_once __DIR__ . '/config/database.php';
$db = get_db_connection();

try {
    $r1 = $db->query("SELECT id, ten_tuan FROM tuan_hoc ORDER BY ngay_bat_dau DESC")->fetchAll(PDO::FETCH_ASSOC);
    echo "tuan_hoc OK, count: " . count($r1) . "\n";
} catch (Exception $e) {
    echo "tuan_hoc ERROR: " . $e->getMessage() . "\n";
}

try {
    $r2 = $db->query("
        SELECT
            sm.id, sm.file_path, sm.original_filename, sm.file_type,
            sm.thumbnail_path, 
            sm.storage_driver,
            sm.cloud_key,
            lh.ten_lop
        FROM so_nhat_ky_minh_chung sm
        JOIN so_nhat_ky_online snk ON sm.nhat_ky_id = snk.id
        JOIN lop_hoc lh ON snk.lop_hoc_id = lh.id
        WHERE snk.tuan_hoc_id = 1
        ORDER BY lh.ten_lop, sm.original_filename
    ")->fetchAll(PDO::FETCH_ASSOC);
    echo "stmt_proofs OK, count: " . count($r2) . "\n";
} catch (Exception $e) {
    echo "stmt_proofs ERROR: " . $e->getMessage() . "\n";
}
