<?php
/**
 * Quản lý năm học – cách ly dữ liệu theo nam_hoc_id.
 * Bảng users không bị cách ly; tài khoản admin có thể chuyển năm làm việc qua session.
 */

const NAM_HOC_SCHEMA_VERSION = 1;

/**
 * Khởi tạo schema và năm học mặc định (chạy một lần mỗi request, idempotent).
 */
function ensure_nam_hoc_schema(PDO $db): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    $db->exec("
        CREATE TABLE IF NOT EXISTS nam_hoc (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            ten_nam_hoc VARCHAR(80) NOT NULL,
            ma_nam VARCHAR(30) NOT NULL,
            ngay_bat_dau DATE NULL,
            ngay_ket_thuc DATE NULL,
            is_mac_dinh TINYINT(1) NOT NULL DEFAULT 0,
            trang_thai VARCHAR(20) NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_ma_nam (ma_nam)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci
    ");

    $tables = [
        'tuan_hoc',
        'lop_hoc',
        'hoc_sinh',
        'khen_thuong',
        'ky_thi',
        'cau_hinh_vi_pham',
        'he_thong_cai_dat',
    ];

    foreach ($tables as $table) {
        if (!nam_hoc_table_exists($db, $table)) {
            continue;
        }
        if (!nam_hoc_column_exists($db, $table, 'nam_hoc_id')) {
            $db->exec("ALTER TABLE `{$table}` ADD COLUMN nam_hoc_id INT UNSIGNED NULL DEFAULT NULL");
            try {
                $db->exec("ALTER TABLE `{$table}` ADD INDEX idx_{$table}_nam_hoc (nam_hoc_id)");
            } catch (PDOException $e) {
                // index có thể đã tồn tại
            }
        }
    }

    $count = (int) $db->query('SELECT COUNT(*) FROM nam_hoc')->fetchColumn();
    if ($count === 0) {
        $db->exec("
            INSERT INTO nam_hoc (ten_nam_hoc, ma_nam, is_mac_dinh, trang_thai)
            VALUES ('Năm học 2025 – 2026', '2025-2026', 1, 'active')
        ");
    }

    $defaultId = (int) $db->query('SELECT id FROM nam_hoc WHERE is_mac_dinh = 1 ORDER BY id ASC LIMIT 1')->fetchColumn();
    if ($defaultId <= 0) {
        $defaultId = (int) $db->query('SELECT id FROM nam_hoc ORDER BY id ASC LIMIT 1')->fetchColumn();
        if ($defaultId > 0) {
            $db->exec("UPDATE nam_hoc SET is_mac_dinh = 0");
            $stmt = $db->prepare('UPDATE nam_hoc SET is_mac_dinh = 1 WHERE id = ?');
            $stmt->execute([$defaultId]);
        }
    }

    if ($defaultId > 0) {
        foreach ($tables as $table) {
            if (!nam_hoc_table_exists($db, $table) || !nam_hoc_column_exists($db, $table, 'nam_hoc_id')) {
                continue;
            }
            $db->prepare("UPDATE IGNORE `{$table}` SET nam_hoc_id = ? WHERE nam_hoc_id IS NULL OR nam_hoc_id = 0")
                ->execute([$defaultId]);
        }
    }

    $db->prepare("INSERT IGNORE INTO he_thong_cai_dat (setting_key, setting_value, nam_hoc_id) VALUES ('nam_hoc_schema_version', ?, NULL)")
        ->execute([(string) NAM_HOC_SCHEMA_VERSION]);
}

function nam_hoc_table_exists(PDO $db, string $table): bool
{
        // Tránh dùng prepared statement với SHOW ... (MariaDB/MySQL có thể không hỗ trợ placeholder '?').
        $stmt = $db->prepare(
                'SELECT 1
                 FROM information_schema.tables
                 WHERE table_schema = DATABASE()
                     AND table_name = ?
                 LIMIT 1'
        );
        $stmt->execute([$table]);
        return (bool) $stmt->fetchColumn();
}

function nam_hoc_column_exists(PDO $db, string $table, string $column): bool
{
        // Tránh dùng SHOW COLUMNS với placeholder '?'; dùng information_schema để bind an toàn.
        $stmt = $db->prepare(
                'SELECT 1
                 FROM information_schema.columns
                 WHERE table_schema = DATABASE()
                     AND table_name = ?
                     AND column_name = ?
                 LIMIT 1'
        );
        $stmt->execute([$table, $column]);
        return (bool) $stmt->fetchColumn();
}

function init_nam_hoc_context(PDO $db): void
{
    ensure_nam_hoc_schema($db);

    if (!isset($_SESSION['working_nam_hoc_id'])) {
        $_SESSION['working_nam_hoc_id'] = get_default_nam_hoc_id($db);
    }

    $workingId = (int) $_SESSION['working_nam_hoc_id'];
    if ($workingId <= 0 || !get_nam_hoc_by_id($db, $workingId)) {
        $_SESSION['working_nam_hoc_id'] = get_default_nam_hoc_id($db);
    }
}

function can_switch_nam_hoc(): bool
{
    return true; // Bất kỳ ai cũng có thể chuyển đổi năm học để tra cứu
}

function can_manage_nam_hoc(): bool
{
    return isset($_SESSION['user_id']) && ($_SESSION['user_vai_tro'] ?? '') === 'admin';
}

/**
 * Năm học đang dùng để truy vấn / nhập liệu.
 */
function current_nam_hoc_id(): int
{
    if (can_switch_nam_hoc() && !empty($_SESSION['working_nam_hoc_id'])) {
        return (int) $_SESSION['working_nam_hoc_id'];
    }

    if (!empty($_SESSION['working_nam_hoc_id'])) {
        return (int) $_SESSION['working_nam_hoc_id'];
    }

    try {
        $db = get_db_connection();
        ensure_nam_hoc_schema($db);
        return get_default_nam_hoc_id($db);
    } catch (Throwable $e) {
        return 1;
    }
}

/** Năm học mặc định cho cổng tra cứu công khai. */
function portal_nam_hoc_id(): int
{
    try {
        $db = get_db_connection();
        ensure_nam_hoc_schema($db);
        return get_default_nam_hoc_id($db);
    } catch (Throwable $e) {
        return 1;
    }
}

function get_default_nam_hoc_id(PDO $db): int
{
    ensure_nam_hoc_schema($db);
    $id = (int) $db->query('SELECT id FROM nam_hoc WHERE is_mac_dinh = 1 ORDER BY id ASC LIMIT 1')->fetchColumn();
    if ($id > 0) {
        return $id;
    }
    return (int) $db->query('SELECT id FROM nam_hoc ORDER BY id ASC LIMIT 1')->fetchColumn() ?: 1;
}

function get_all_nam_hoc(PDO $db): array
{
    ensure_nam_hoc_schema($db);
    return $db->query('SELECT * FROM nam_hoc ORDER BY ngay_bat_dau DESC, id DESC')->fetchAll(PDO::FETCH_ASSOC);
}

function get_nam_hoc_by_id(PDO $db, int $id): ?array
{
    $stmt = $db->prepare('SELECT * FROM nam_hoc WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function current_nam_hoc(PDO $db): array
{
    $row = get_nam_hoc_by_id($db, current_nam_hoc_id());
    return $row ?: ['id' => 1, 'ten_nam_hoc' => 'Năm học', 'ma_nam' => ''];
}

function format_nam_hoc_label(?array $row): string
{
    if (!$row) {
        return '';
    }
    return trim($row['ten_nam_hoc'] ?? $row['ma_nam'] ?? '');
}

function set_working_nam_hoc_id(int $id): bool
{
    if (!can_switch_nam_hoc()) {
        return false;
    }
    $db = get_db_connection();
    if (!get_nam_hoc_by_id($db, $id)) {
        return false;
    }
    $_SESSION['working_nam_hoc_id'] = $id;
    return true;
}

function set_default_nam_hoc_id(PDO $db, int $id): void
{
    if (!get_nam_hoc_by_id($db, $id)) {
        throw new InvalidArgumentException('Năm học không tồn tại.');
    }
    $db->exec('UPDATE nam_hoc SET is_mac_dinh = 0');
    $stmt = $db->prepare('UPDATE nam_hoc SET is_mac_dinh = 1 WHERE id = ?');
    $stmt->execute([$id]);
    if (can_switch_nam_hoc()) {
        $_SESSION['working_nam_hoc_id'] = $id;
    }
}

/**
 * Chuẩn hóa mã năm từ chuỗi nhập (vd: "2026-2027", "2026 – 2027").
 */
function normalize_ma_nam(string $input): string
{
    $s = preg_replace('/[^\d\-–—]/u', '', $input) ?? '';
    $s = str_replace(['–', '—'], '-', $s);
    $s = preg_replace('/-+/', '-', $s);
    return trim($s, '-');
}

/**
 * Tạo năm học mới – dữ liệu trống; sao chép cấu hình vi phạm từ năm mẫu (nếu có).
 */
function create_nam_hoc(PDO $db, string $tenNamHoc, string $maNam, bool $setAsDefault = false): int
{
    ensure_nam_hoc_schema($db);
    $maNam = normalize_ma_nam($maNam);
    if ($maNam === '') {
        throw new InvalidArgumentException('Mã năm học không hợp lệ.');
    }

    $tenNamHoc = trim($tenNamHoc);
    if ($tenNamHoc === '') {
        $tenNamHoc = 'Năm học ' . str_replace('-', ' – ', $maNam);
    }

    $stmtCheck = $db->prepare('SELECT id FROM nam_hoc WHERE ma_nam = ?');
    $stmtCheck->execute([$maNam]);
    if ($stmtCheck->fetchColumn()) {
        throw new InvalidArgumentException('Mã năm học đã tồn tại.');
    }

    $db->beginTransaction();
    try {
        $stmt = $db->prepare('INSERT INTO nam_hoc (ten_nam_hoc, ma_nam, is_mac_dinh, trang_thai) VALUES (?, ?, 0, ?)');
        $stmt->execute([$tenNamHoc, $maNam, 'active']);
        $newId = (int) $db->lastInsertId();

        seed_cau_hinh_vi_pham_for_year($db, $newId);

        if ($setAsDefault) {
            $db->exec('UPDATE nam_hoc SET is_mac_dinh = 0');
            $db->prepare('UPDATE nam_hoc SET is_mac_dinh = 1 WHERE id = ?')->execute([$newId]);
        }

        $db->commit();
        return $newId;
    } catch (Throwable $e) {
        $db->rollBack();
        throw $e;
    }
}

function seed_cau_hinh_vi_pham_for_year(PDO $db, int $namHocId): void
{
    if (!nam_hoc_table_exists($db, 'cau_hinh_vi_pham')) {
        return;
    }

    $templateId = get_default_nam_hoc_id($db);
    if ($templateId === $namHocId) {
        $stmt = $db->query('SELECT id FROM nam_hoc WHERE id != ' . (int) $namHocId . ' ORDER BY id ASC LIMIT 1');
        $templateId = (int) $stmt->fetchColumn();
    }

    if ($templateId <= 0) {
        return;
    }

    $cols = [];
    $stmtCols = $db->query('SHOW COLUMNS FROM cau_hinh_vi_pham');
    while ($col = $stmtCols->fetch(PDO::FETCH_ASSOC)) {
        $name = $col['Field'] ?? '';
        if (!in_array($name, ['id', 'nam_hoc_id'], true)) {
            $cols[] = $name;
        }
    }
    if (empty($cols)) {
        return;
    }

    $colList = implode(', ', array_map(static fn ($c) => "`{$c}`", $cols));
    $sql = "INSERT INTO cau_hinh_vi_pham (nam_hoc_id, {$colList})
            SELECT ?, {$colList} FROM cau_hinh_vi_pham WHERE nam_hoc_id = ?";
    $db->prepare($sql)->execute([$namHocId, $templateId]);
}

/**
 * Thêm điều kiện WHERE nam_hoc_id cho truy vấn.
 *
 * @return array{0: string, 1: array} [sql_fragment, params]
 */
function nam_hoc_sql_filter(string $column = 'nam_hoc_id', ?int $namHocId = null): array
{
    $id = $namHocId ?? current_nam_hoc_id();
    return [" AND {$column} = ?", [$id]];
}

function append_nam_hoc_where(array &$whereClauses, array &$params, string $column = 'nam_hoc_id', ?int $namHocId = null): void
{
    [$frag, $bind] = nam_hoc_sql_filter($column, $namHocId);
    $whereClauses[] = ltrim($frag, ' AND ');
    foreach ($bind as $p) {
        $params[] = $p;
    }
}
