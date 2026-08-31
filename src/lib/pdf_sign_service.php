<?php

use setasign\Fpdi\Tcpdf\Fpdi as TcpdfFpdi;

/**
 * Kiem tra quyen ky PDF dien tu cua tai khoan dang nhap.
 */
function can_current_user_sign_pdf(): bool
{
    $role = $_SESSION['user_vai_tro'] ?? '';
    $permissions = $_SESSION['user_permissions'] ?? [];

    if ($role === 'admin') {
        return true;
    }

    if ($role !== 'user' || !is_array($permissions)) {
        return false;
    }

    return in_array('all', $permissions, true) || in_array('ky_pdf_dien_tu', $permissions, true);
}

/**
 * Tao bang luu lich su ky PDF neu chua co.
 */
function ensure_pdf_sign_schema(PDO $db): void
{
    $db->exec(
        "CREATE TABLE IF NOT EXISTS pdf_sign_history (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            original_file_name VARCHAR(255) NOT NULL,
            source_file_path VARCHAR(500) NOT NULL,
            signed_file_path VARCHAR(500) NOT NULL,
            source_file_sha256 CHAR(64) NOT NULL,
            signed_file_sha256 CHAR(64) NOT NULL,
            signature_type ENUM('image','text') NOT NULL DEFAULT 'image',
            signature_label VARCHAR(255) NULL,
            signer_user_id INT NOT NULL,
            signer_name VARCHAR(255) NOT NULL,
            signer_role VARCHAR(50) NOT NULL,
            signer_ip VARCHAR(64) NULL,
            signed_at DATETIME NOT NULL,
            page_number INT NOT NULL,
            pos_x_percent DECIMAL(8,4) NOT NULL,
            pos_y_percent DECIMAL(8,4) NOT NULL,
            box_w_percent DECIMAL(8,4) NOT NULL,
            box_h_percent DECIMAL(8,4) NOT NULL,
            ca_serial VARCHAR(255) NULL,
            ca_fingerprint VARCHAR(128) NULL,
            ca_signature_base64 LONGTEXT NULL,
            verification_payload LONGTEXT NULL,
            INDEX idx_pdf_sign_signed_at (signed_at),
            INDEX idx_pdf_sign_user (signer_user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci"
    );
}

/**
 * Dam bao cac thu muc luu file cho module ky PDF.
 */
function ensure_pdf_sign_directories(): array
{
    $basePublic = realpath(__DIR__ . '/../../public');
    if ($basePublic === false) {
        throw new RuntimeException('Khong tim thay thu muc public.');
    }

    $sourceDir = $basePublic . '/uploads/pdf_sign_source';
    $signedDir = $basePublic . '/uploads/signed_pdfs';
    $tempDir = $basePublic . '/uploads/pdf_sign_temp';
    $keyDir = realpath(__DIR__ . '/../../') . '/key';

    foreach ([$sourceDir, $signedDir, $tempDir, $keyDir] as $dir) {
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException('Khong the tao thu muc: ' . $dir);
        }
    }

    return [
        'source_dir' => $sourceDir,
        'signed_dir' => $signedDir,
        'temp_dir' => $tempDir,
        'key_dir' => $keyDir,
    ];
}

/**
 * Gom cac thong bao loi tu OpenSSL de debug.
 */
function collect_openssl_errors(): string
{
    $errors = [];
    while ($msg = openssl_error_string()) {
        $errors[] = $msg;
    }
    return empty($errors) ? 'unknown_openssl_error' : implode(' | ', $errors);
}

/**
 * Tim file openssl.cnf phu hop tren nhieu moi truong, nhat la Windows.
 */
function find_openssl_config_path(): ?string
{
    $candidates = [];

    $envPath = getenv('OPENSSL_CONF');
    if (is_string($envPath) && $envPath !== '') {
        $candidates[] = $envPath;
    }

    $loadedIni = php_ini_loaded_file();
    if (is_string($loadedIni) && $loadedIni !== '') {
        $iniDir = dirname($loadedIni);
        $candidates[] = $iniDir . DIRECTORY_SEPARATOR . 'openssl.cnf';
        $candidates[] = $iniDir . DIRECTORY_SEPARATOR . 'extras' . DIRECTORY_SEPARATOR . 'ssl' . DIRECTORY_SEPARATOR . 'openssl.cnf';
    }

    if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
        $candidates[] = 'C:\\xampp\\apache\\bin\\openssl.cnf';
        $candidates[] = 'C:\\xampp\\php\\extras\\ssl\\openssl.cnf';
        $candidates[] = 'C:\\php\\extras\\ssl\\openssl.cnf';
        $candidates[] = 'C:\\Program Files\\OpenSSL-Win64\\bin\\openssl.cfg';
        $candidates[] = 'C:\\Program Files\\OpenSSL-Win32\\bin\\openssl.cfg';
    }

    foreach ($candidates as $candidate) {
        if (is_string($candidate) && $candidate !== '' && is_file($candidate)) {
            return $candidate;
        }
    }

    return null;
}

/**
 * Tao private key voi nhieu cau hinh fallback de tang tuong thich.
 */
function create_private_key_with_fallback(array $baseConfig)
{
    $attempts = [];

    $attempts[] = $baseConfig + [
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
        'private_key_bits' => 2048,
        'digest_alg' => 'sha256',
    ];
    $attempts[] = $baseConfig + [
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
        'private_key_bits' => 1024,
        'digest_alg' => 'sha256',
    ];
    $attempts[] = [
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
        'private_key_bits' => 2048,
    ];
    $attempts[] = [
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
        'private_key_bits' => 1024,
    ];

    $lastError = 'unknown_openssl_error';
    foreach ($attempts as $config) {
        $key = openssl_pkey_new($config);
        if ($key !== false) {
            return $key;
        }
        $lastError = collect_openssl_errors();
    }

    throw new RuntimeException('Khong tao duoc private key cho CA. OpenSSL: ' . $lastError);
}

/**
 * Tao hoac nap private CA noi bo phuc vu ky du lieu xac thuc.
 */
function ensure_private_ca_material(array $dirs): array
{
    if (!extension_loaded('openssl')) {
        throw new RuntimeException('Server chua bat extension OpenSSL.');
    }

    $keyPath = $dirs['key_dir'] . '/pdf_sign_ca_private.pem';
    $certPath = $dirs['key_dir'] . '/pdf_sign_ca_cert.pem';

    if (!is_dir($dirs['key_dir']) || !is_writable($dirs['key_dir'])) {
        throw new RuntimeException('Thu muc key khong co quyen ghi: ' . $dirs['key_dir']);
    }

    if (!file_exists($keyPath) || !file_exists($certPath)) {
        $config = [];
        $opensslCnf = find_openssl_config_path();
        if ($opensslCnf !== null) {
            $config['config'] = $opensslCnf;
        }

        // Xoa cac loi cu trong queue truoc khi tao key/cert.
        collect_openssl_errors();

        $dn = [
            'countryName' => 'VN',
            'stateOrProvinceName' => 'Quang Ngai',
            'localityName' => 'Binh Son',
            'organizationName' => 'THPT Binh Son',
            'organizationalUnitName' => 'PDF Signing Internal CA',
            'commonName' => 'THPT Binh Son Internal PDF CA',
            'emailAddress' => 'admin@thptbinhson.local',
        ];

        $privateKey = create_private_key_with_fallback($config);

        $csr = openssl_csr_new($dn, $privateKey, $config);
        if ($csr === false) {
            throw new RuntimeException('Khong tao duoc CSR cho CA. OpenSSL: ' . collect_openssl_errors());
        }

        $x509 = openssl_csr_sign($csr, null, $privateKey, 3650, $config + ['digest_alg' => 'sha256']);
        if ($x509 === false) {
            throw new RuntimeException('Khong tao duoc chung chi CA. OpenSSL: ' . collect_openssl_errors());
        }

        $privateKeyPemOut = '';
        if (!openssl_pkey_export($privateKey, $privateKeyPemOut, null, $config)) {
            throw new RuntimeException('Khong export duoc private key CA. OpenSSL: ' . collect_openssl_errors());
        }

        $certPemOut = '';
        if (!openssl_x509_export($x509, $certPemOut, false)) {
            throw new RuntimeException('Khong export duoc cert CA. OpenSSL: ' . collect_openssl_errors());
        }

        if (file_put_contents($keyPath, $privateKeyPemOut, LOCK_EX) === false) {
            throw new RuntimeException('Khong luu duoc private key CA. Path: ' . $keyPath);
        }
        if (file_put_contents($certPath, $certPemOut, LOCK_EX) === false) {
            throw new RuntimeException('Khong luu duoc cert CA. Path: ' . $certPath);
        }

        @chmod($keyPath, 0600);
        @chmod($certPath, 0644);
    }

    $privateKeyPem = file_get_contents($keyPath);
    $certPem = file_get_contents($certPath);
    if ($privateKeyPem === false || $certPem === false) {
        throw new RuntimeException('Khong doc duoc du lieu CA.');
    }

    $certInfo = openssl_x509_parse($certPem);
    $fingerprint = openssl_x509_fingerprint($certPem, 'sha256');

    return [
        'private_key_pem' => $privateKeyPem,
        'cert_pem' => $certPem,
        'serial' => isset($certInfo['serialNumberHex']) ? (string) $certInfo['serialNumberHex'] : null,
        'fingerprint' => $fingerprint ?: null,
        'key_path' => $keyPath,
        'cert_path' => $certPath,
    ];
}

/**
 * Tao ten file an toan.
 */
function safe_pdf_sign_filename(string $name): string
{
    $name = preg_replace('/[^A-Za-z0-9._-]+/', '_', $name);
    $name = trim((string) $name, '._-');
    return $name !== '' ? $name : 'pdf_file';
}

/**
 * Chuyen data URL thanh file anh tam de chen vao PDF.
 */
function persist_signature_image_data(string $imageDataUrl, string $tempDir): string
{
    if (!preg_match('#^data:image/(png|jpeg|jpg);base64,#i', $imageDataUrl, $matches)) {
        throw new InvalidArgumentException('Dinh dang anh chu ky khong hop le.');
    }

    $ext = strtolower($matches[1]) === 'jpg' ? 'jpeg' : strtolower($matches[1]);
    $raw = substr($imageDataUrl, strpos($imageDataUrl, ',') + 1);
    $binary = base64_decode($raw, true);
    if ($binary === false) {
        throw new InvalidArgumentException('Khong giai ma duoc anh chu ky base64.');
    }

    $tmpFile = $tempDir . '/sig_' . uniqid('', true) . '.' . $ext;
    if (file_put_contents($tmpFile, $binary) === false) {
        throw new RuntimeException('Khong luu duoc anh chu ky tam.');
    }

    return $tmpFile;
}

/**
 * Dong dau chu ky len PDF tai vi tri tu do va tra ve duong dan file da ky.
 */
function stamp_pdf_signature(
    string $sourcePdf,
    string $signedPdf,
    int $pageNumber,
    float $xPercent,
    float $yPercent,
    float $wPercent,
    float $hPercent,
    string $signatureImagePath,
    string $auditText,
    string $signingCertPem,
    string $signingPrivateKeyPem,
    string $signerName,
    string $signReason = 'Ky PDF dien tu noi bo',
    string $signLocation = 'THPT Binh Son',
    string $contactInfo = 'admin@thptbinhson.local'
): void {
    $pdf = new TcpdfFpdi('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(0, 0, 0, true);
    $pdf->SetAutoPageBreak(false, 0);

    $pageCount = $pdf->setSourceFile($sourcePdf);

    if ($pageNumber < 1 || $pageNumber > $pageCount) {
        throw new InvalidArgumentException('Trang ky khong hop le.');
    }

    $pdf->setSignature(
        $signingCertPem,
        $signingPrivateKeyPem,
        '',
        '',
        2,
        [
            'Name' => $signerName,
            'Location' => $signLocation,
            'Reason' => $signReason,
            'ContactInfo' => $contactInfo,
        ]
    );

    for ($i = 1; $i <= $pageCount; $i++) {
        $tpl = $pdf->importPage($i);
        $size = $pdf->getTemplateSize($tpl);
        $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';

        $pdf->AddPage($orientation, [$size['width'], $size['height']]);
        $pdf->useTemplate($tpl);

        if ($i === $pageNumber) {
            $x = max(0.0, min(100.0, $xPercent)) * $size['width'] / 100;
            $y = max(0.0, min(100.0, $yPercent)) * $size['height'] / 100;
            $w = max(1.0, min(100.0, $wPercent)) * $size['width'] / 100;
            $h = max(1.0, min(100.0, $hPercent)) * $size['height'] / 100;

            $x = min($x, max(0.0, $size['width'] - $w));
            $y = min($y, max(0.0, $size['height'] - $h));

            $pdf->SetDrawColor(31, 111, 235);
            $pdf->SetLineWidth(0.4);
            $pdf->Rect($x, $y, $w, $h);

            $imgPadding = 2.0;
            $imgX = $x + $imgPadding;
            $imgY = $y + $imgPadding;
            $imgW = max(6.0, $w - ($imgPadding * 2));
            $imgH = max(6.0, $h - 9.0);

            $canDrawImage = $signatureImagePath !== ''
                && is_file($signatureImagePath)
                && @getimagesize($signatureImagePath) !== false;

            if ($canDrawImage) {
                $pdf->Image($signatureImagePath, $imgX, $imgY, $imgW, $imgH);
            }

            $pdf->SetXY($x + 1.5, $y + $h - 5.5);
            $pdf->SetFont('Helvetica', '', 7);
            $pdf->SetTextColor(40, 40, 40);
            $pdf->Cell($w - 3.0, 4.0, $auditText, 0, 0, 'C');

            // Dat signature widget de PDF viewer nhan dien chu ky so CMS/PKCS#7.
            $pdf->setSignatureAppearance($x, $y, $w, $h, $pageNumber);
        }
    }

    $pdf->Output($signedPdf, 'F');
}

/**
 * Lay lich su ky PDF gan nhat.
 */
function get_pdf_sign_history(PDO $db, int $limit = 100): array
{
    ensure_pdf_sign_schema($db);
    $limit = max(1, min(500, $limit));
    $stmt = $db->query("SELECT * FROM pdf_sign_history ORDER BY signed_at DESC, id DESC LIMIT {$limit}");
    return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
}
