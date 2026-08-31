<?php

const THIDUA_TRACUU_VIEW_SETTING_KEY = 'tracuu_portal_view_count';

function thidua_increment_tracuu_view_count(PDO $db): int
{
    $key = THIDUA_TRACUU_VIEW_SETTING_KEY;
    $stmt = $db->prepare('SELECT setting_value FROM he_thong_cai_dat WHERE setting_key = ? LIMIT 1');
    $stmt->execute([$key]);
    $current = (int) ($stmt->fetchColumn() ?: 0);
    $next = $current + 1;

    $upsert = $db->prepare(
        'INSERT INTO he_thong_cai_dat (setting_key, setting_value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );
    $upsert->execute([$key, (string) $next]);

    return $next;
}

function thidua_get_tracuu_view_count(PDO $db): int
{
    $stmt = $db->prepare('SELECT setting_value FROM he_thong_cai_dat WHERE setting_key = ? LIMIT 1');
    $stmt->execute([THIDUA_TRACUU_VIEW_SETTING_KEY]);
    return (int) ($stmt->fetchColumn() ?: 0);
}
