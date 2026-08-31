<?php
// File: src/lib/exam_shift_manager.php

/**
 * Đảm bảo schema bảng Ca thi (ky_thi_ca_thi)
 */
function ensure_exam_shift_schema(PDO $db): void
{
    static $initialized = false;
    if ($initialized) return;
    $initialized = true;

    $db->exec(
        "CREATE TABLE IF NOT EXISTS ky_thi_ca_thi (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ky_thi_id INT NOT NULL,
            ten_ca VARCHAR(150) NOT NULL,
            ngay_thi DATE NULL,
            gio_thi TIME NULL,
            so_luot_thi INT NOT NULL DEFAULT 1,
            danh_sach_mon JSON NOT NULL,
            thu_tu INT NOT NULL DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_ktct_kt (ky_thi_id),
            INDEX idx_ktct_tt (ky_thi_id, thu_tu)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci"
    );
}

/**
 * Lấy danh sách ca thi của một kỳ thi
 */
function get_exam_shifts(PDO $db, int $ky_thi_id): array
{
    ensure_exam_shift_schema($db);
    $stmt = $db->prepare("SELECT * FROM ky_thi_ca_thi WHERE ky_thi_id = ? ORDER BY thu_tu ASC, id ASC");
    $stmt->execute([$ky_thi_id]);
    $shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($shifts as &$s) {
        $raw = $s['danh_sach_mon'] ?? '[]';
        $decoded = is_string($raw) ? json_decode($raw, true) : $raw;
        $s['mon_hoc_list'] = is_array($decoded) ? $decoded : [];
    }
    unset($s);

    return $shifts;
}

/**
 * Tạo danh sách ca thi mặc định cho kỳ thi mới
 * (Ca 1: Toán, Ca 2: Ngữ văn, Ca 3: Môn Tự chọn KHTN/KHXH - 2 lượt)
 */
function create_default_exam_shifts(PDO $db, int $ky_thi_id): void
{
    ensure_exam_shift_schema($db);
    $existing = get_exam_shifts($db, $ky_thi_id);
    if (!empty($existing)) {
        return;
    }

    $default_shifts = [
        [
            'ten_ca' => 'Ca 1: Môn Toán',
            'so_luot_thi' => 1,
            'danh_sach_mon' => json_encode(['toan']),
            'thu_tu' => 1
        ],
        [
            'ten_ca' => 'Ca 2: Môn Ngữ Văn',
            'so_luot_thi' => 1,
            'danh_sach_mon' => json_encode(['ngu_van']),
            'thu_tu' => 2
        ],
        [
            'ten_ca' => 'Ca 3: Các Môn Tự Chọn (KHTN / KHXH)',
            'so_luot_thi' => 2,
            'danh_sach_mon' => json_encode(['vat_ly', 'hoa_hoc', 'sinh_hoc', 'lich_su', 'dia_ly', 'gdcd', 'tin_hoc', 'cong_nghe', 'tieng_anh']),
            'thu_tu' => 3
        ]
    ];

    $stmt = $db->prepare("
        INSERT INTO ky_thi_ca_thi (ky_thi_id, ten_ca, so_luot_thi, danh_sach_mon, thu_tu)
        VALUES (?, ?, ?, ?, ?)
    ");

    foreach ($default_shifts as $ds) {
        $stmt->execute([$ky_thi_id, $ds['ten_ca'], $ds['so_luot_thi'], $ds['danh_sach_mon'], $ds['thu_tu']]);
    }
}
