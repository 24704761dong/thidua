<?php
// File: src/controllers/lich_sinh_nhat.php
// Lịch sinh nhật + lịch sử gửi mail (đọc từ nhat_ky_sinh_nhat)

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}

require_once __DIR__ . '/../../config/database.php';
$db = get_db_connection();
$driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
date_default_timezone_set('Asia/Ho_Chi_Minh');

/** Helper: chuyển chuỗi ngày sinh về dd/mm (hỗ trợ dd/mm/YYYY và YYYY-mm-dd) */
function birthday_to_ddmm(?string $s): ?string {
    $s = trim((string)$s);
    if ($s === '') return null;
    if (strpos($s, '/') !== false) {
        $p = explode('/', $s);
        if (count($p) === 3) return sprintf('%02d/%02d', (int)$p[0], (int)$p[1]);
    } elseif (strpos($s, '-') !== false) {
        $p = explode('-', $s);
        if (count($p) === 3) return sprintf('%02d/%02d', (int)$p[2], (int)$p[1]);
    }
    return null;
}

// --- 1) LẤY & XỬ LÝ DỮ LIỆU SINH NHẬT (SQL tương thích) ---
$sql_birthdays = "
    SELECT 
        'hoc_sinh' AS doi_tuong,
        hs.id, 
        hs.ma_hoc_sinh AS ma, 
        TRIM(COALESCE(hs.ho_dem,'')) AS ho_dem,
        TRIM(COALESCE(hs.ten,'')) AS ten,
        lh.ten_lop AS lop, 
        hs.ngay_sinh
    FROM hoc_sinh hs
    JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id
    WHERE hs.ngay_sinh IS NOT NULL AND hs.ngay_sinh <> ''

    UNION ALL

    SELECT 
        'giao_vien' AS doi_tuong,
        lh.id, 
        lh.gvcn_ma AS ma, 
        '' AS ho_dem,
        TRIM(COALESCE(lh.gvcn_ten,'')) AS ten,
        'GVCN' AS lop,
        lh.gvcn_ngay_sinh AS ngay_sinh
    FROM lop_hoc lh
    WHERE lh.gvcn_ngay_sinh IS NOT NULL AND lh.gvcn_ngay_sinh <> ''
";
$all_birthdays_raw = $db->query($sql_birthdays)->fetchAll(PDO::FETCH_ASSOC);

$birthdays_by_month = array_fill(1, 12, []);
$upcoming_birthdays = [];

$today = new DateTime('today');
$today_str = $today->format('d/m');
$tomorrow_str = (clone $today)->modify('+1 day')->format('d/m');
$day_after_tomorrow_str = (clone $today)->modify('+2 days')->format('d/m');

foreach ($all_birthdays_raw as $person) {
    $ten_day_du = trim(trim($person['ho_dem'] ?? '') . ' ' . trim($person['ten'] ?? ''));
    $person['ten'] = $ten_day_du; // gán về key 'ten' để view dùng chung

    $ngay_sinh_trim = trim($person['ngay_sinh'] ?? '');
    $day = null; $month = null;

    if (strpos($ngay_sinh_trim, '/') !== false) {
        $parts = explode('/', $ngay_sinh_trim);
        if (count($parts) === 3) { $day = (int)$parts[0]; $month = (int)$parts[1]; }
    } elseif (strpos($ngay_sinh_trim, '-') !== false) {
        $parts = explode('-', $ngay_sinh_trim);
        if (count($parts) === 3) { $day = (int)$parts[2]; $month = (int)$parts[1]; }
    }

    if ($day && $month) {
        $person_birthday_str = sprintf('%02d/%02d', $day, $month);

        if ($month >= 1 && $month <= 12) {
            $birthdays_by_month[$month][$day][] = $person;
        }

        if (in_array($person_birthday_str, [$today_str, $tomorrow_str, $day_after_tomorrow_str], true)) {
            $upcoming_birthdays[] = $person;
        }
    }
}

// Sắp xếp
foreach ($birthdays_by_month as $month => &$days) {
    ksort($days);
    foreach ($days as $day => &$people) {
        usort($people, function($a, $b) {
            if (($a['lop'] ?? '') === ($b['lop'] ?? '')) {
                return strcmp($a['ten'] ?? '', $b['ten'] ?? '');
            }
            return strcmp($a['lop'] ?? '', $b['lop'] ?? '');
        });
    }
}
unset($days, $people);

// --- 2) LỊCH SỬ GỬI MAIL: đọc từ nhat_ky_sinh_nhat ---
$sql_logs = "
    SELECT
        l.id,
        l.sent_at,                 -- đã có sẵn trong bảng
        l.person_type AS doi_tuong,
        l.person_id,
        l.person_name AS ten_day_du,
        l.birthday_date,
        COALESCE(hs.email, lh.gvcn_email) AS email,
        CASE 
            WHEN l.person_type = 'hoc_sinh' THEN lh2.ten_lop
            ELSE 'GVCN'
        END AS lop,
        l.status,
        l.error_message AS message
    FROM nhat_ky_sinh_nhat l
    LEFT JOIN hoc_sinh hs ON l.person_type = 'hoc_sinh' AND hs.id = l.person_id
    LEFT JOIN lop_hoc lh2  ON l.person_type = 'hoc_sinh' AND lh2.id = hs.lop_hoc_id
    LEFT JOIN lop_hoc lh   ON l.person_type = 'giao_vien' AND lh.id = l.person_id
    ORDER BY l.sent_at DESC
    LIMIT 100
";
$logs = $db->query($sql_logs)->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../views/lich_sinh_nhat.php';
