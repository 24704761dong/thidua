<?php
// File: src/controllers/api_admin_pwa.php
// API phục vụ Progressive Web App dành cho quản trị viên

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

function admin_pwa_log(string $message): void
{
    $logFile = __DIR__ . '/../../logs/app.log';
    $timestamp = date('Y-m-d H:i:s');
    @file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

admin_pwa_log('--- admin_pwa.php request start --- action=' . ($_GET['action'] ?? ''));

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/bootstrap.php';
admin_pwa_log('Bootstrap loaded successfully');
require_once __DIR__ . '/../lib/ThiDuaCalculator.php';
require_once __DIR__ . '/../lib/tracking.php';
require_once __DIR__ . '/../lib/helpers.php';

const ADMIN_PWA_SESSION_LIFETIME = 60 * 60 * 24 * 30; // 30 ngày

function refresh_session_cookie(int $lifetime = ADMIN_PWA_SESSION_LIFETIME): void
{
    if (headers_sent()) {
        return;
    }

    $params = session_get_cookie_params();
    $options = [
        'expires' => time() + $lifetime,
        'path' => $params['path'] ?? '/',
        'secure' => ($params['secure'] ?? false) || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => $params['httponly'] ?? true
    ];

    if (!empty($params['domain'])) {
        $options['domain'] = $params['domain'];
    }

    if (isset($params['samesite'])) {
        $options['samesite'] = $params['samesite'];
    } elseif (PHP_VERSION_ID >= 70300) {
        $options['samesite'] = 'Lax';
    }

    set_session_cookie_value(session_id(), $options);
}

function clear_session_cookie(): void
{
    if (headers_sent()) {
        return;
    }

    $params = session_get_cookie_params();
    $options = [
        'expires' => time() - 3600,
        'path' => $params['path'] ?? '/',
        'secure' => ($params['secure'] ?? false) || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => $params['httponly'] ?? true
    ];

    if (!empty($params['domain'])) {
        $options['domain'] = $params['domain'];
    }

    if (isset($params['samesite'])) {
        $options['samesite'] = $params['samesite'];
    } elseif (PHP_VERSION_ID >= 70300) {
        $options['samesite'] = 'Lax';
    }

    set_session_cookie_value('', $options);
}

function set_session_cookie_value(string $value, array $options): void
{
    $name = session_name();
    if (PHP_VERSION_ID >= 70300) {
        setcookie($name, $value, $options);
        return;
    }

    $expires = $options['expires'] ?? 0;
    $path = $options['path'] ?? '/';
    $domain = $options['domain'] ?? '';
    $secure = $options['secure'] ?? false;
    $httponly = $options['httponly'] ?? true;

    setcookie($name, $value, $expires, $path, $domain, $secure, $httponly);
}

$action = $_GET['action'] ?? null;
try {
    switch ($action) {
        case 'login':
            handle_login();
            break;
        case 'session':
            handle_session();
            break;
        case 'dashboard':
            require_auth();
            handle_dashboard();
            break;
        case 'violations_config':
            require_auth();
            handle_violation_config();
            break;
        case 'student_lookup':
            require_auth();
            handle_student_lookup();
            break;
        case 'reports_overview':
            require_auth();
            handle_reports_overview();
            break;
        case 'reports_fetch':
            require_auth();
            handle_reports_fetch();
            break;
        case 'submit_violation':
            require_auth();
            handle_submit_violation();
            break;
        case 'keepalive':
            require_auth();
            handle_keepalive();
            break;
        case 'logout':
            handle_logout();
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Throwable $e) {
    admin_pwa_log('Unhandled exception: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi máy chủ: ' . $e->getMessage()
    ]);
}

function get_db(): PDO
{
    static $db = null;
    if ($db === null) {
        $db = get_db_connection();
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    return $db;
}

function require_auth(): void
{
    if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'] ?? '', ['admin', 'user'], true)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập']);
        exit();
    }

    refresh_session_cookie();
}

function handle_login(): void
{
    $payload = json_decode(file_get_contents('php://input'), true) ?? [];
    $username = trim($payload['username'] ?? '');
    $password = $payload['password'] ?? '';

    if ($username === '' || $password === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đủ tên đăng nhập và mật khẩu.']);
        return;
    }

    $db = get_db();
    $stmt = $db->prepare('SELECT * FROM users WHERE ten_dang_nhap = ? OR email = ?');
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['mat_khau_hash'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Tên đăng nhập hoặc mật khẩu không đúng.']);
        return;
    }

    if (!in_array($user['vai_tro'], ['admin', 'user'], true)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Tài khoản này không có quyền truy cập ứng dụng PWA quản trị.']);
        return;
    }

    if (!empty($user['two_fa_enabled']) && (int)$user['two_fa_enabled'] === 1) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Tài khoản đang bật 2FA. Vui lòng đăng nhập qua cổng quản trị để xác thực.']);
        return;
    }

    establish_admin_session($db, $user);

    echo json_encode([
        'success' => true,
        'message' => 'Đăng nhập thành công.',
        'user' => get_session_user_payload()
    ]);
}

function establish_admin_session(PDO $db, array $user): void
{
    session_regenerate_id(true);

    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['user_ten'] = $user['ho_ten'];
    $_SESSION['user_vai_tro'] = $user['vai_tro'];
    $_SESSION['user_permissions'] = ($user['vai_tro'] === 'user') ? json_decode($user['quyen_han'] ?? '[]', true) : ['all'];
    $_SESSION['last_activity'] = time();
    $_SESSION['admin_pwa'] = true;

    refresh_session_cookie();

    update_activity_log();

    try {
        $stmt = $db->prepare('INSERT INTO lich_su_dang_nhap_admin (user_id, thoi_gian_dang_nhap, dia_chi_ip, user_agent) VALUES (?, ?, ?, ?)');
        $stmt->execute([
            $user['id'],
            date('Y-m-d H:i:s'),
            $_SERVER['REMOTE_ADDR'] ?? 'N/A',
            $_SERVER['HTTP_USER_AGENT'] ?? 'N/A'
        ]);
    } catch (Exception $e) {
        if (function_exists('log_to_file')) {
            log_to_file('[PWA LOGIN LOG FAIL] ' . $e->getMessage());
        }
    }
}

function handle_session(): void
{
    if (isset($_SESSION['user_id']) && in_array($_SESSION['user_vai_tro'] ?? '', ['admin', 'user'], true)) {
        refresh_session_cookie();
        echo json_encode([
            'loggedIn' => true,
            'user' => get_session_user_payload()
        ]);
    } else {
        echo json_encode(['loggedIn' => false]);
    }
}

function get_session_user_payload(): array
{
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'name' => $_SESSION['user_ten'] ?? null,
        'role' => $_SESSION['user_vai_tro'] ?? null
    ];
}

function handle_dashboard(): void
{
    $db = get_db();

    $weeks = $db->query('SELECT id, ten_tuan, ngay_bat_dau, ngay_ket_thuc FROM tuan_hoc ORDER BY ngay_bat_dau DESC LIMIT 2')->fetchAll(PDO::FETCH_ASSOC);
    $currentWeek = $weeks[0] ?? null;
    $previousWeek = $weeks[1] ?? null;

    $calculator = $currentWeek ? new thiduaCalculator($db) : null;

    $currentRanking = $currentWeek ? summarize_ranking($calculator, (int)$currentWeek['id']) : ['top' => [], 'bottom' => [], 'kxtd' => []];
    $previousRanking = $previousWeek ? summarize_ranking($calculator, (int)$previousWeek['id']) : ['top' => [], 'bottom' => [], 'kxtd' => []];

    $stats = [
        'current_week' => $currentWeek,
        'previous_week' => $previousWeek,
        'top_classes' => $currentRanking['top'],
        'bottom_classes' => array_merge($currentRanking['bottom'], $currentRanking['kxtd']),
        'top_classes_prev' => $previousRanking['top'],
        'bottom_classes_prev' => $previousRanking['bottom'],
        'kxtd_prev' => $previousRanking['kxtd']
    ];

    $stats['violations_current_week'] = $currentWeek ? count_violations_for_week($db, (int)$currentWeek['id']) : 0;
    $stats['violations_previous_week'] = $previousWeek ? count_violations_for_week($db, (int)$previousWeek['id']) : 0;
    $stats['pending_requests'] = calculate_pending_requests($db);
    $stats['traffic'] = get_traffic_snapshot($db);
    $stats['journal_summary'] = get_journal_summary($db, $currentWeek ? (int)$currentWeek['id'] : null);

    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
}

function summarize_ranking(?thiduaCalculator $calculator, int $weekId): array
{
    if (!$calculator) {
        return ['top' => [], 'bottom' => [], 'kxtd' => []];
    }

    $raw = $calculator->calculateRawDataForWeek($weekId);
    $ranked = $calculator->rankWeeklyData($raw);

    $top = [];
    $bottom = [];
    $kxtd = [];
    $maxRank = 0;

    foreach ($ranked as $item) {
        if ($item['kxtd']) {
            $kxtd[] = $item['lop'];
            continue;
        }
        $rank = (int)($item['xep_hang'] ?? 0);
        if ($rank === 1) {
            $top[] = $item['lop'];
        }
        if ($rank > $maxRank) {
            $maxRank = $rank;
        }
    }

    if ($maxRank > 1) {
        foreach ($ranked as $item) {
            if ($item['kxtd']) {
                continue;
            }
            if ((int)($item['xep_hang'] ?? 0) === $maxRank) {
                $bottom[] = $item['lop'];
            }
        }
    }

    return [
        'top' => $top,
        'bottom' => $bottom,
        'kxtd' => $kxtd
    ];
}

function count_violations_for_week(PDO $db, int $weekId): int
{
    $stmt = $db->prepare('SELECT COUNT(*) FROM vi_pham_hoc_sinh WHERE tuan_hoc_id = ?');
    $stmt->execute([$weekId]);
    return (int)$stmt->fetchColumn();
}

function calculate_pending_requests(PDO $db): array
{
    $pendingViolations = (int)$db->query("SELECT COUNT(*) FROM vi_pham_tam_thoi WHERE trang_thai_gui = 'da_gui'")->fetchColumn();
    $pendingAttendance = (int)$db->query("SELECT COUNT(*) FROM diem_danh_chi_tiet WHERE trang_thai = 'cho_duyet'")->fetchColumn();
    $pendingDuty = (int)$db->query("SELECT COUNT(*) FROM dang_ky_truc_tuan WHERE trang_thai = 'Chờ duyệt' AND trang_thai_luu_tru = 0")->fetchColumn();

    return [
        'total' => $pendingViolations + $pendingAttendance + $pendingDuty,
        'violations' => $pendingViolations,
        'attendance' => $pendingAttendance,
        'duty' => $pendingDuty
    ];
}

function get_traffic_snapshot(PDO $db): array
{
    $totalVisits = 0;
    $stmt = $db->query("SELECT stat_value FROM he_thong_thong_ke WHERE stat_key = 'tong_so_luot_truy_cap'");
    if ($stmt) {
        $totalVisits = (int)($stmt->fetchColumn() ?: 0);
    }

    $activeThreshold = time() - (5 * 60);
    $stmtActive = $db->prepare('SELECT COUNT(DISTINCT session_id) FROM phien_truy_cap WHERE last_activity > ?');
    $stmtActive->execute([$activeThreshold]);
    $activeNow = (int)$stmtActive->fetchColumn();

    return [
        'total_visits' => $totalVisits,
        'active_now' => $activeNow
    ];
}

function get_journal_summary(PDO $db, ?int $weekId): array
{
    $classMap = $db->query('SELECT id, ten_lop FROM lop_hoc ORDER BY ten_lop')->fetchAll(PDO::FETCH_KEY_PAIR);
    $totalClasses = count($classMap);

    if (!$weekId) {
        return [
            'total_classes' => $totalClasses,
            'submitted' => 0,
            'approved' => 0,
            'pending' => 0,
            'reverted' => 0,
            'draft' => 0,
            'approved_list' => [],
            'pending_list' => [],
            'reverted_list' => [],
            'draft_list' => [],
            'not_submitted' => $totalClasses,
            'not_submitted_list' => array_values($classMap)
        ];
    }

    $stmt = $db->prepare('SELECT snk.lop_hoc_id, snk.trang_thai, snk.ghi_chu_admin FROM so_nhat_ky_online snk WHERE snk.tuan_hoc_id = ?');
    $stmt->execute([$weekId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $approvedList = [];
    $pendingList = [];
    $revertedList = [];
    $draftList = [];
    $submittedIds = [];

    foreach ($rows as $row) {
        $classId = (int)$row['lop_hoc_id'];
        $submittedIds[$classId] = true;
        $className = $classMap[$classId] ?? ('Lop #' . $classId);

        switch ($row['trang_thai']) {
            case 'da_duyet':
                $approvedList[] = $className;
                break;
            case 'da_gui':
                if (!empty($row['ghi_chu_admin'])) {
                    $revertedList[] = $className;
                } else {
                    $pendingList[] = $className;
                }
                break;
            default:
                $draftList[] = $className;
                break;
        }
    }

    $submittedCount = count($submittedIds);
    $notSubmittedList = array_values(array_diff_key($classMap, $submittedIds));

    return [
        'total_classes' => $totalClasses,
        'submitted' => $submittedCount,
        'approved' => count($approvedList),
        'pending' => count($pendingList),
        'reverted' => count($revertedList),
        'draft' => count($draftList),
        'approved_list' => $approvedList,
        'pending_list' => $pendingList,
        'reverted_list' => $revertedList,
        'draft_list' => $draftList,
        'not_submitted' => count($notSubmittedList),
        'not_submitted_list' => $notSubmittedList
    ];
}

function handle_violation_config(): void
{
    $db = get_db();
    $data = $db->query('SELECT id, ten_vi_pham, nhom_vi_pham, diem_tru FROM cau_hinh_vi_pham ORDER BY nhom_vi_pham, ten_vi_pham')->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'violations' => $data]);
}

function handle_student_lookup(): void
{
    $code = trim($_GET['code'] ?? '');
    $studentName = trim($_GET['student_name'] ?? '');
    $classNameRaw = trim($_GET['class_name'] ?? '');

    if ($code === '' && ($studentName === '' || $classNameRaw === '')) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu tra cứu.']);
        return;
    }

    $db = get_db();
    $student = null;

    if ($code !== '') {
        $student = resolve_student_by_code($db, $code);
        if (!$student) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy học sinh với mã QR này.']);
            return;
        }
    } else {
        $normalizedClass = normalize_class_label($classNameRaw);
        $classInfo = find_class_by_label($db, $normalizedClass);
        if (!$classInfo) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => "Không tìm thấy lớp $normalizedClass trong hệ thống."
            ]);
            return;
        }

        $matches = search_students_by_name_in_class($db, (int)$classInfo['id'], $studentName);
        if (count($matches) === 0) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Không tìm thấy học sinh khớp với thông tin vừa nhập.'
            ]);
            return;
        }

        if (count($matches) > 1) {
            $suggestions = array_map(static function (array $row): string {
                $fullName = trim($row['ho_ten'] ?? '');
                $studentCode = $row['ma_hoc_sinh'] ?? '';
                return $studentCode !== '' ? ($fullName . ' - ' . $studentCode) : $fullName;
            }, $matches);

            http_response_code(409);
            echo json_encode([
                'success' => false,
                'message' => 'Có nhiều hơn 1 học sinh phù hợp, vui lòng bổ sung thông tin hoặc quét QR.',
                'options' => $suggestions
            ]);
            return;
        }

        $student = find_student($db, ['id' => (int)$matches[0]['id']]);
        if (!$student) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy học sinh.']);
            return;
        }

        if (($student['trang_thai_hoc_tap'] ?? '') === 'nghi_hoc') {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'message' => 'Học sinh này đã nghỉ học. Vui lòng kiểm tra lại thông tin.'
            ]);
            return;
        }
    }

    $violations = get_recent_violations($db, (int)$student['id']);
    $commendations = get_recent_commendations($db, (int)$student['id'], (int)$student['lop_hoc_id']);
    $exams = get_recent_exam_results($db, (int)$student['id']);

    echo json_encode([
        'success' => true,
        'profile' => $student,
        'violations' => $violations,
        'commendations' => $commendations,
        'exams' => $exams
    ]);
}

function handle_reports_overview(): void
{
    $db = get_db();
    $stmt = $db->query('SELECT id, ten_tuan, hoc_ky, ngay_bat_dau, ngay_ket_thuc FROM tuan_hoc ORDER BY ngay_bat_dau DESC');
    $weeks = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $today = date('Y-m-d');
    $currentWeekId = null;
    foreach ($weeks as $week) {
        $start = $week['ngay_bat_dau'] ?? null;
        $end = $week['ngay_ket_thuc'] ?? null;
        if ($start && $end && $start <= $today && $end >= $today) {
            $currentWeekId = (int)$week['id'];
            break;
        }
    }
    if ($currentWeekId === null && $weeks) {
        $currentWeekId = (int)$weeks[0]['id'];
    }

    $normalizedWeeks = array_map(static function (array $week): array {
        return [
            'id' => (int)$week['id'],
            'ten_tuan' => $week['ten_tuan'],
            'hoc_ky' => isset($week['hoc_ky']) ? (int)$week['hoc_ky'] : null,
            'ngay_bat_dau' => $week['ngay_bat_dau'],
            'ngay_ket_thuc' => $week['ngay_ket_thuc']
        ];
    }, $weeks);

    echo json_encode([
        'success' => true,
        'weeks' => $normalizedWeeks,
        'current_week_id' => $currentWeekId
    ]);
}

function handle_reports_fetch(): void
{
    $type = $_GET['type'] ?? '';
    $weekId = isset($_GET['week_id']) ? (int)$_GET['week_id'] : 0;

    $allowed = ['weekly_score', 'weekly_violations', 'journal'];
    if ($weekId <= 0 || !in_array($type, $allowed, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Thiếu tham số báo cáo hợp lệ.']);
        return;
    }

    $db = get_db();
    $week = fetch_week_by_id($db, $weekId);
    if (!$week) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy tuần học.']);
        return;
    }

    switch ($type) {
        case 'weekly_score':
            $report = build_weekly_score_report($db, $weekId);
            break;
        case 'weekly_violations':
            $report = build_weekly_violations_report($db, $weekId);
            break;
        case 'journal':
            $report = build_weekly_journal_report($db, $weekId);
            break;
        default:
            $report = [];
    }

    echo json_encode([
        'success' => true,
        'week' => $week,
        'report' => $report
    ]);
}

function fetch_week_by_id(PDO $db, int $weekId): ?array
{
    $stmt = $db->prepare('SELECT id, ten_tuan, hoc_ky, ngay_bat_dau, ngay_ket_thuc FROM tuan_hoc WHERE id = ? LIMIT 1');
    $stmt->execute([$weekId]);
    $week = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$week) {
        return null;
    }

    return [
        'id' => (int)$week['id'],
        'ten_tuan' => $week['ten_tuan'],
        'hoc_ky' => isset($week['hoc_ky']) ? (int)$week['hoc_ky'] : null,
        'ngay_bat_dau' => $week['ngay_bat_dau'],
        'ngay_ket_thuc' => $week['ngay_ket_thuc']
    ];
}

function build_weekly_score_report(PDO $db, int $weekId): array
{
    $calculator = new thiduaCalculator($db);
    $raw = $calculator->calculateRawDataForWeek($weekId);
    $ranked = $calculator->rankWeeklyData($raw);

    $summary = [
        'total_classes' => count($ranked),
        'top_classes' => [],
        'kxtd_classes' => [],
        'highest_score' => null,
        'lowest_score' => null,
        'average_score' => null
    ];

    $sumScores = 0.0;
    $countRanked = 0;
    $highest = null;
    $lowest = null;

    $groups = [];

    foreach ($ranked as $row) {
        $className = $row['lop'] ?? '';
        $grade = substr($className, 0, 2) ?: 'Khác';

        if (!isset($groups[$grade])) {
            $groups[$grade] = [
                'grade' => $grade,
                'label' => 'Khối ' . $grade,
                'classes' => []
            ];
        }

        $totalScore = (float)($row['tong_diem'] ?? 0);
        $isKxtd = (bool)($row['kxtd'] ?? false);
        $rankValue = $row['xep_hang'] ?? null;

        $classEntry = [
            'class' => $className,
            'lop' => $className,
            'rank' => $rankValue,
            'kxtd' => $isKxtd,
            'total_score' => round($totalScore, 2),
            'lesson_tot' => (int)($row['so_tiet_tot'] ?? 0),
            'lesson_tb' => (int)($row['so_tiet_tb'] ?? 0),
            'components' => [
                'good' => round((float)($row['diem_tiet_tot_thanh_phan'] ?? 0), 2),
                'avg' => round((float)($row['diem_tiet_tb_thanh_phan'] ?? 0), 2),
                'sdb' => round((float)($row['diem_sdb_thanh_phan'] ?? 0), 2),
                'bonus' => round((float)($row['diem_cong_tru'] ?? 0), 2),
                'discipline' => round((float)($row['diem_noi_quy'] ?? 0), 2),
                'absence' => round((float)($row['tru_vang_thanh_phan'] ?? 0), 2)
            ],
            'absence' => [
                'excused' => (int)($row['vang_p'] ?? 0),
                'unexcused' => (int)($row['vang_kp'] ?? 0)
            ]
        ];

        $groups[$grade]['classes'][] = $classEntry;

        if ($isKxtd) {
            $summary['kxtd_classes'][] = $className;
            continue;
        }

        if ($rankValue === 1 || $rankValue === '1' || (is_numeric($rankValue) && (int)$rankValue === 1)) {
            $summary['top_classes'][] = $className;
        }

        $sumScores += $totalScore;
        $countRanked++;

        if ($highest === null || $totalScore > $highest) {
            $highest = $totalScore;
        }
        if ($lowest === null || $totalScore < $lowest) {
            $lowest = $totalScore;
        }
    }

    foreach ($groups as &$group) {
        usort($group['classes'], static function (array $a, array $b): int {
            if ($a['kxtd'] && !$b['kxtd']) {
                return 1;
            }
            if (!$a['kxtd'] && $b['kxtd']) {
                return -1;
            }
            $rankA = is_numeric($a['rank']) ? (int)$a['rank'] : PHP_INT_MAX;
            $rankB = is_numeric($b['rank']) ? (int)$b['rank'] : PHP_INT_MAX;
            return $rankA <=> $rankB;
        });
    }
    unset($group);

    ksort($groups);

    $summary['highest_score'] = $highest !== null ? round($highest, 2) : null;
    $summary['lowest_score'] = $lowest !== null ? round($lowest, 2) : null;
    $summary['average_score'] = $countRanked > 0 ? round($sumScores / $countRanked, 2) : null;

    return [
        'summary' => $summary,
        'groups' => array_values($groups)
    ];
}

function build_weekly_violations_report(PDO $db, int $weekId): array
{
    $sql = "SELECT vphs.id, vphs.ngay_vi_pham, vphs.ghi_chu, vphs.hoc_sinh_id, chvp.ten_vi_pham, chvp.diem_tru, chvp.nhom_vi_pham, hs.ma_hoc_sinh, hs.ho_dem, hs.ten, lh.ten_lop FROM vi_pham_hoc_sinh vphs JOIN quatrinh_hoc_tap qt ON vphs.hoc_sinh_id = qt.id JOIN hoc_sinh hs ON hs.ma_hoc_sinh = qt.ma_hoc_sinh AND hs.nam_hoc_id = qt.nam_hoc_id JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id LEFT JOIN cau_hinh_vi_pham chvp ON vphs.vi_pham_id = chvp.id WHERE vphs.tuan_hoc_id = ? ORDER BY vphs.ngay_vi_pham DESC, chvp.diem_tru DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute([$weekId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $records = [];
    $classStats = [];
    $groupStats = [];
    $studentIds = [];
    $totalPoints = 0.0;

    foreach ($rows as $row) {
        $points = (float)($row['diem_tru'] ?? 0);
        $totalPoints += $points;

        $fullName = trim(($row['ho_dem'] ?? '') . ' ' . ($row['ten'] ?? ''));
        $records[] = [
            'id' => (int)$row['id'],
            'date' => $row['ngay_vi_pham'],
            'class' => $row['ten_lop'],
            'student' => $fullName,
            'student_code' => $row['ma_hoc_sinh'],
            'violation' => $row['ten_vi_pham'],
            'group' => $row['nhom_vi_pham'],
            'points' => round($points, 2),
            'note' => $row['ghi_chu'] ?? null
        ];

        $classKey = $row['ten_lop'] ?? 'Không rõ';
        if (!isset($classStats[$classKey])) {
            $classStats[$classKey] = ['class' => $classKey, 'count' => 0, 'points' => 0.0];
        }
        $classStats[$classKey]['count']++;
        $classStats[$classKey]['points'] += $points;

        $groupKey = $row['nhom_vi_pham'] ?? 'Không phân loại';
        if (!isset($groupStats[$groupKey])) {
            $groupStats[$groupKey] = ['group' => $groupKey, 'count' => 0, 'points' => 0.0];
        }
        $groupStats[$groupKey]['count']++;
        $groupStats[$groupKey]['points'] += $points;

        if (!empty($row['hoc_sinh_id'])) {
            $studentIds[(int)$row['hoc_sinh_id']] = true;
        }
    }

    usort($records, static function (array $a, array $b): int {
        if ($a['date'] === $b['date']) {
            return strcmp($a['class'] ?? '', $b['class'] ?? '');
        }
        return strcmp($b['date'] ?? '', $a['date'] ?? '');
    });

    $classStats = array_values($classStats);
    usort($classStats, static function (array $a, array $b): int {
        return $b['count'] <=> $a['count'] ?: $b['points'] <=> $a['points'];
    });

    $groupStats = array_values($groupStats);
    usort($groupStats, static function (array $a, array $b): int {
        return $b['count'] <=> $a['count'] ?: $b['points'] <=> $a['points'];
    });

    return [
        'summary' => [
            'total_records' => count($records),
            'students_impacted' => count($studentIds),
            'total_points' => round($totalPoints, 2)
        ],
        'by_class' => array_map(static function (array $item): array {
            return [
                'class' => $item['class'],
                'count' => (int)$item['count'],
                'points' => round($item['points'], 2)
            ];
        }, $classStats),
        'by_group' => array_map(static function (array $item): array {
            return [
                'group' => $item['group'],
                'count' => (int)$item['count'],
                'points' => round($item['points'], 2)
            ];
        }, $groupStats),
        'records' => $records
    ];
}

function build_weekly_journal_report(PDO $db, int $weekId): array
{
    $summary = get_journal_summary($db, $weekId);

    $sql = 'SELECT snk.trang_thai, snk.ngay_gui, snk.ngay_duyet, snk.ghi_chu_admin, lh.ten_lop, hs.ho_dem, hs.ten FROM so_nhat_ky_online snk JOIN lop_hoc lh ON snk.lop_hoc_id = lh.id JOIN hoc_sinh hs ON snk.nguoi_nhap_id = hs.id WHERE snk.tuan_hoc_id = ? ORDER BY lh.ten_lop';
    $stmt = $db->prepare($sql);
    $stmt->execute([$weekId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $labelMap = [
        'approved' => 'Đã duyệt',
        'pending' => 'Chờ duyệt',
        'reverted' => 'Bị trả về',
        'draft' => 'Nháp',
        'missing' => 'Chưa gửi'
    ];

    $details = [];
    foreach ($rows as $row) {
        $statusKey = resolve_journal_status_key($row['trang_thai'] ?? '', $row['ghi_chu_admin'] ?? null);
        $details[] = [
            'class' => $row['ten_lop'],
            'status' => $statusKey,
            'status_label' => $labelMap[$statusKey] ?? 'Chờ duyệt',
            'operator' => trim(($row['ho_dem'] ?? '') . ' ' . ($row['ten'] ?? '')),
            'submitted_at' => $row['ngay_gui'],
            'approved_at' => $row['ngay_duyet'],
            'admin_note' => $row['ghi_chu_admin'] ?? null
        ];
    }

    return [
        'summary' => $summary,
        'details' => $details
    ];
}

function resolve_journal_status_key(string $status, ?string $adminNote): string
{
    $normalized = strtolower($status);
    if ($normalized === 'da_duyet') {
        return 'approved';
    }
    if ($normalized === 'da_gui') {
        return $adminNote ? 'reverted' : 'pending';
    }
    if ($normalized === 'nhap') {
        return 'draft';
    }
    return 'pending';
}

function resolve_student_by_code(PDO $db, string $rawCode): ?array
{
    $code = trim($rawCode);
    if ($code === '') {
        return null;
    }

    $candidates = [];
    $decoded = json_decode($code, true);
    if (is_array($decoded)) {
        if (!empty($decoded['student_id'])) {
            $candidates[] = ['id', (int)$decoded['student_id']];
        }
        if (!empty($decoded['ma_hoc_sinh'])) {
            $candidates[] = ['ma_hoc_sinh', trim((string)$decoded['ma_hoc_sinh'])];
        }
        if (!empty($decoded['cccd'])) {
            $candidates[] = ['ma_hoc_sinh', trim((string)$decoded['cccd'])];
        }
    }

    if (!$candidates) {
        $normalized = preg_replace('/[^0-9A-Za-z]/', '', $code);
        if ($normalized !== '') {
            $candidates[] = ['ma_hoc_sinh', $normalized];
        }
    }

    foreach ($candidates as [$key, $value]) {
        if ($key === 'id') {
            $student = find_student($db, ['id' => (int)$value]);
        } else {
            $student = find_student($db, ['ma_hoc_sinh' => $value]);
        }
        if ($student) {
            return $student;
        }
    }

    return null;
}

function normalize_class_label(string $input): string
{
    $value = strtoupper(preg_replace('/\s+/', '', $input));
    if ($value === '') {
        return $value;
    }

    if (preg_match('/^([ABC])(\d{1,2})$/', $value, $matches)) {
        $map = ['A' => '10', 'B' => '11', 'C' => '12'];
        return $map[$matches[1]] . 'A' . $matches[2];
    }

    return $value;
}

function find_class_by_label(PDO $db, string $classLabel): ?array
{
    $stmt = $db->prepare('SELECT id, ten_lop FROM lop_hoc WHERE ten_lop = ? LIMIT 1');
    $stmt->execute([$classLabel]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function search_students_by_name_in_class(PDO $db, int $classId, string $name): array
{
    $normalized = trim(preg_replace('/\s+/', ' ', $name));
    if ($normalized === '') {
        return [];
    }

    $like = '%' . str_replace(' ', '%', $normalized) . '%';
    $sql = "SELECT hs.id, hs.ma_hoc_sinh, TRIM(CONCAT(COALESCE(hs.ho_dem, ''), ' ', COALESCE(hs.ten, ''))) AS ho_ten, hs.trang_thai_hoc_tap FROM hoc_sinh hs WHERE hs.lop_hoc_id = ? AND CONCAT(TRIM(COALESCE(hs.ho_dem, '')), ' ', TRIM(COALESCE(hs.ten, ''))) LIKE ? ORDER BY hs.ten LIMIT 5";
    $stmt = $db->prepare($sql);
    $stmt->execute([$classId, $like]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function find_student(PDO $db, array $queryBy)
{
    if (isset($queryBy['id'])) {
        $stmt = $db->prepare('SELECT hs.*, lh.ten_lop, lh.gvcn_ten FROM hoc_sinh hs JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id WHERE hs.id = ?');
        $stmt->execute([$queryBy['id']]);
    } else {
        $stmt = $db->prepare('SELECT hs.*, lh.ten_lop, lh.gvcn_ten FROM hoc_sinh hs JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id WHERE hs.ma_hoc_sinh = ?');
        $stmt->execute([$queryBy['ma_hoc_sinh']]);
    }
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($student) {
        $student['anh_the_url'] = !empty($student['anh_the']) ? ('/thidua/public/assets/anh_the/' . $student['anh_the']) : null;
    }

    return $student;
}

function get_recent_violations(PDO $db, int $studentId): array
{
    $stmt = $db->prepare('SELECT vp.id, vp.ngay_vi_pham, chvp.ten_vi_pham, chvp.diem_tru, vp.ghi_chu FROM vi_pham_hoc_sinh vp JOIN cau_hinh_vi_pham chvp ON vp.vi_pham_id = chvp.id WHERE vp.hoc_sinh_id = ? ORDER BY vp.ngay_vi_pham DESC LIMIT 20');
    $stmt->execute([$studentId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_recent_commendations(PDO $db, int $studentId, int $classId): array
{
    $stmt = $db->prepare("SELECT kt.id, kt.loai, kt.ten_khen_thuong, kt.cap_khen_thuong, kt.ngay_khen_thuong, kt.ghi_chu, COALESCE(hs.ten, kt.ten_tap_the) AS doi_tuong FROM khen_thuong kt LEFT JOIN quatrinh_hoc_tap qt ON kt.hoc_sinh_id = qt.id LEFT JOIN ho_so_hoc_sinh hs ON qt.ma_hoc_sinh = hs.ma_hoc_sinh WHERE (qt.id = (SELECT id FROM quatrinh_hoc_tap WHERE ma_hoc_sinh = (SELECT ma_hoc_sinh FROM ho_so_hoc_sinh WHERE id = :student) LIMIT 1) OR (kt.loai = 'tap_the' AND kt.lop_hoc_id = :class)) ORDER BY kt.ngay_khen_thuong DESC LIMIT 15");
    $stmt->execute([':student' => $studentId, ':class' => $classId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_recent_exam_results(PDO $db, int $studentId): array
{
    $sql = "SELECT kt.ten_ky_thi, kt.ngay_ket_thuc, ktdt.* FROM ky_thi_hoc_sinh kths JOIN ky_thi kt ON kths.ky_thi_id = kt.id LEFT JOIN ky_thi_diem_thi ktdt ON kths.id = ktdt.ky_thi_hoc_sinh_id WHERE kths.hoc_sinh_id = ? ORDER BY kt.ngay_ket_thuc DESC, kt.id DESC LIMIT 5";
    $stmt = $db->prepare($sql);
    $stmt->execute([$studentId]);
    $results = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $item = [
            'ten_ky_thi' => $row['ten_ky_thi'],
            'ngay_ket_thuc' => $row['ngay_ket_thuc'],
            'diem' => []
        ];
        foreach ($row as $key => $value) {
            if (strpos($key, 'diem_') === 0 && $value !== null) {
                $item['diem'][$key] = $value;
            }
        }

        $results[] = $item;
    }
    return $results;
}

function handle_submit_violation(): void
{
    $payload = json_decode(file_get_contents('php://input'), true) ?? [];
    $maHocSinh = trim($payload['ma_hoc_sinh'] ?? '');
    $viPhamId = (int)($payload['vi_pham_id'] ?? 0);
    $ghiChu = trim($payload['ghi_chu'] ?? '');
    $ngayViPham = $payload['ngay_vi_pham'] ?? date('Y-m-d');
    $weekId = (int)($payload['tuan_hoc_id'] ?? 0);

    if ($maHocSinh === '' || !$viPhamId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Thiếu thông tin bắt buộc.']);
        return;
    }

    $db = get_db();
    if (!$weekId) {
        $weekId = (int)$db->query('SELECT id FROM tuan_hoc ORDER BY ngay_bat_dau DESC LIMIT 1')->fetchColumn();
    }

    $student = find_student($db, ['ma_hoc_sinh' => $maHocSinh]);
    if (!$student) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy học sinh.']);
        return;
    }

    $stmt = $db->prepare('INSERT INTO vi_pham_hoc_sinh (tuan_hoc_id, hoc_sinh_id, vi_pham_id, ngay_vi_pham, nguoi_nhap_id, ghi_chu, raw_ho_ten, raw_ten_lop, thoi_gian_nhap, nguoi_nhap_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        $weekId,
        (int)$student['id'],
        $viPhamId,
        $ngayViPham,
        $_SESSION['user_id'],
        $ghiChu,
        $student['ho_dem'] . ' ' . $student['ten'],
        $student['ten_lop'],
        date('Y-m-d H:i:s'),
        'admin_pwa'
    ]);

    echo json_encode(['success' => true, 'message' => 'Đã ghi nhận vi phạm.']);
}

function handle_keepalive(): void
{
    $_SESSION['last_activity'] = time();
    update_activity_log();
    refresh_session_cookie();
    echo json_encode(['success' => true]);
}

function handle_logout(): void
{
    clear_session_cookie();
    session_unset();
    session_destroy();
    echo json_encode(['success' => true]);
}
