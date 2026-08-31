<?php

function tuan_label_ngan($t)
{
    if (preg_match('/(\d{1,2})/', (string) $t, $m)) {
        return 'Tuần ' . $m[1];
    }
    return (string) $t;
}

function label_tuan_hien_tai($ds, $id, $y)
{
    if (empty($id) || $id === 'all') {
        return $y;
    }
    foreach ((array) $ds as $t) {
        if ((string) $t['id'] === (string) $id) {
            return tuan_label_ngan($t['ten_tuan'] ?? '');
        }
    }
    return $y;
}

function tracuu_is_ajax_filter_request(): bool
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}
