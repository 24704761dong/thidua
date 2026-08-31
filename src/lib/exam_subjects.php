<?php

/**
 * Danh muc mon thi va cot Excel tuong ung cho chuc nang dang ky to hop mon.
 * Cot F-G la nhom mon chinh, H-V la nhom mon tu chon.
 */
function exam_subject_catalog()
{
    return [
        'toan' => ['label' => 'Toán', 'mandatory' => true, 'excel_column' => 'F'],
        'van' => ['label' => 'Văn', 'mandatory' => true, 'excel_column' => 'G'],
        'vat_ly' => ['label' => 'Vật lý', 'mandatory' => false, 'excel_column' => 'H'],
        'hoa_hoc' => ['label' => 'Hóa học', 'mandatory' => false, 'excel_column' => 'I'],
        'sinh_hoc' => ['label' => 'Sinh học', 'mandatory' => false, 'excel_column' => 'J'],
        'lich_su' => ['label' => 'Lịch sử', 'mandatory' => false, 'excel_column' => 'K'],
        'dia_ly' => ['label' => 'Địa lý', 'mandatory' => false, 'excel_column' => 'L'],
        'gdktpl' => ['label' => 'Giáo dục kinh tế và pháp luật', 'mandatory' => false, 'excel_column' => 'M'],
        'tin_hoc' => ['label' => 'Tin học', 'mandatory' => false, 'excel_column' => 'N'],
        'cong_nghe' => ['label' => 'Công nghệ', 'mandatory' => false, 'excel_column' => 'O'],
        'tieng_anh' => ['label' => 'Tiếng Anh', 'mandatory' => false, 'excel_column' => 'P'],
        'tieng_nga' => ['label' => 'Tiếng Nga', 'mandatory' => false, 'excel_column' => 'Q'],
        'tieng_phap' => ['label' => 'Tiếng Pháp', 'mandatory' => false, 'excel_column' => 'R'],
        'tieng_trung_quoc' => ['label' => 'Tiếng Trung Quốc', 'mandatory' => false, 'excel_column' => 'S'],
        'tieng_duc' => ['label' => 'Tiếng Đức', 'mandatory' => false, 'excel_column' => 'T'],
        'tieng_nhat' => ['label' => 'Tiếng Nhật', 'mandatory' => false, 'excel_column' => 'U'],
        'tieng_han' => ['label' => 'Tiếng Hàn', 'mandatory' => false, 'excel_column' => 'V'],
    ];
}

/**
 * Moi hoc sinh duoc chon toi da 2 mon tu chon.
 */
function exam_subject_max_optional()
{
    return 2;
}

function exam_subject_column_map()
{
    $map = [];
    foreach (exam_subject_catalog() as $code => $meta) {
        $map[$code] = $meta['excel_column'];
    }
    return $map;
}

function exam_subject_template_headers()
{
    return [
        'A' => 'ma_hoc_sinh (CCCD)',
        'B' => 'ho_dem',
        'C' => 'ten',
        'D' => 'ten_lop',
        'E' => 'ngay_sinh (YYYY-MM-DD)',
        'F' => 'Toán',
        'G' => 'Văn',
        'H' => 'Vật lý',
        'I' => 'Hóa học',
        'J' => 'Sinh học',
        'K' => 'Lịch sử',
        'L' => 'Địa lý',
        'M' => 'Giáo dục kinh tế và pháp luật',
        'N' => 'Tin học',
        'O' => 'Công nghệ',
        'P' => 'Tiếng Anh',
        'Q' => 'Tiếng Nga',
        'R' => 'Tiếng Pháp',
        'S' => 'Tiếng Trung Quốc',
        'T' => 'Tiếng Đức',
        'U' => 'Tiếng Nhật',
        'V' => 'Tiếng Hàn',
    ];
}

function exam_required_subject_codes()
{
    $codes = [];
    foreach (exam_subject_catalog() as $code => $meta) {
        if (!empty($meta['mandatory'])) {
            $codes[] = $code;
        }
    }
    return $codes;
}

function exam_optional_subject_codes()
{
    $codes = [];
    foreach (exam_subject_catalog() as $code => $meta) {
        if (empty($meta['mandatory'])) {
            $codes[] = $code;
        }
    }
    return $codes;
}

function exam_subject_label($subjectCode)
{
    $catalog = exam_subject_catalog();
    return $catalog[$subjectCode]['label'] ?? $subjectCode;
}

/**
 * Chuan hoa danh sach ma mon theo thu tu dinh nghia trong catalog.
 */
function exam_normalize_subject_codes($subjectCodes)
{
    $subjectCodes = is_array($subjectCodes) ? $subjectCodes : [];
    $catalog = exam_subject_catalog();
    $valid = [];

    foreach ($subjectCodes as $code) {
        if (!is_string($code)) {
            continue;
        }
        $normalized = strtolower(trim($code));
        if ($normalized !== '' && isset($catalog[$normalized])) {
            $valid[$normalized] = true;
        }
    }

    $ordered = [];
    foreach (array_keys($catalog) as $catalogCode) {
        if (isset($valid[$catalogCode])) {
            $ordered[] = $catalogCode;
        }
    }

    return $ordered;
}

function exam_default_subject_registration()
{
    return [];
}

/**
 * Decode chuoi JSON dang ky mon. Neu loi se fallback ve danh sach rong.
 */
function exam_decode_subject_registration($rawRegistration)
{
    if (!is_string($rawRegistration) || trim($rawRegistration) === '') {
        return exam_default_subject_registration();
    }

    $decoded = json_decode($rawRegistration, true);
    if (is_array($decoded)) {
        if (isset($decoded['subjects']) && is_array($decoded['subjects'])) {
            $decoded = $decoded['subjects'];
        } elseif (isset($decoded['all']) && is_array($decoded['all'])) {
            $decoded = $decoded['all'];
        }

        $codes = [];
        foreach ($decoded as $item) {
            if (is_string($item)) {
                $codes[] = $item;
            }
        }

        return exam_normalize_subject_codes($codes);
    }

    // Ho tro fallback cho du lieu dang csv/phan cach boi dau phay.
    $parts = preg_split('/[,;|]/', $rawRegistration) ?: [];
    return exam_normalize_subject_codes($parts);
}

function exam_ensure_required_subjects($subjectCodes)
{
    // Giu ten ham de tuong thich nguoc, nhung khong tu dong chen mon nao.
    return exam_normalize_subject_codes($subjectCodes);
}

function exam_extract_optional_subjects($subjectCodes)
{
    $subjectCodes = exam_normalize_subject_codes($subjectCodes);
    $optionalCodes = exam_optional_subject_codes();

    $optional = [];
    foreach ($subjectCodes as $code) {
        if (in_array($code, $optionalCodes, true)) {
            $optional[] = $code;
        }
    }

    return $optional;
}

function exam_validate_optional_subject_selection($optionalSubjectCodes)
{
    $optional = exam_extract_optional_subjects($optionalSubjectCodes);
    if (count($optional) > exam_subject_max_optional()) {
        return 'Mỗi học sinh chỉ được đăng ký tối đa 2 môn tự chọn.';
    }

    return null;
}

function exam_build_registration_from_optional($optionalSubjectCodes)
{
    $optional = exam_extract_optional_subjects($optionalSubjectCodes);
    $optional = array_slice($optional, 0, exam_subject_max_optional());
    return exam_normalize_subject_codes($optional);
}

function exam_encode_subject_registration($subjectCodes)
{
    $normalized = exam_ensure_required_subjects($subjectCodes);
    return json_encode($normalized, JSON_UNESCAPED_UNICODE);
}

function exam_subject_display_labels($subjectCodes)
{
    $labels = [];
    foreach (exam_ensure_required_subjects($subjectCodes) as $code) {
        $labels[] = exam_subject_label($code);
    }
    return $labels;
}

function exam_subject_display_labels_from_raw($rawRegistration)
{
    return exam_subject_display_labels(exam_decode_subject_registration($rawRegistration));
}

/**
 * Nhan biet o co duoc danh dau dang ky mon hay khong (x, 1, true, ...).
 */
function exam_is_marked_cell($value)
{
    if ($value === null) {
        return false;
    }

    $normalized = strtolower(trim((string)$value));
    return in_array($normalized, ['x', '1', 'true', 'yes', 'co', 'có', '✓', '✔'], true);
}

function exam_db_column_exists(PDO $db, $tableName, $columnName)
{
    // SHOW COLUMNS khong ho tro placeholder on dinh tren mot so ban MariaDB.
    if (!preg_match('/^[A-Za-z0-9_]+$/', (string)$tableName) || !preg_match('/^[A-Za-z0-9_]+$/', (string)$columnName)) {
        return false;
    }

    $tableSql = '`' . str_replace('`', '``', (string)$tableName) . '`';
    $columnSql = $db->quote((string)$columnName);
    $stmt = $db->query("SHOW COLUMNS FROM {$tableSql} LIKE {$columnSql}");

    return $stmt ? (bool)$stmt->fetch() : false;
}

/**
 * Tu dong bo sung cot luu dang ky mon thi neu CSDL chua co.
 */
function ensure_exam_subject_registration_schema(PDO $db)
{
    if (!exam_db_column_exists($db, 'ky_thi_hoc_sinh', 'dang_ky_mon_thi')) {
        $db->exec("ALTER TABLE ky_thi_hoc_sinh ADD COLUMN dang_ky_mon_thi TEXT NULL AFTER ghi_chu");
    }
}
