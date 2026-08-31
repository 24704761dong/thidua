<?php
// File: src/controllers/public_scan_controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';
$db = get_db_connection();

$token = $_GET['token'] ?? '';

if (empty($token)) {
    die("<h3>Lỗi: Không tìm thấy đường dẫn quét điểm danh.</h3>");
}

$stmt = $db->prepare("SELECT * FROM hoat_dong WHERE scan_token = ?");
$stmt->execute([$token]);
$hoat_dong = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$hoat_dong) {
    die("<h3>Lỗi: Đường dẫn không tồn tại hoặc đã bị hủy.</h3>");
}

$auth_key = 'public_scan_' . $token;

// Xử lý submit password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['scan_password_submit'])) {
    $input_pwd = $_POST['scan_password_submit'] ?? '';
    if (password_verify($input_pwd, $hoat_dong['scan_password'])) {
        $_SESSION[$auth_key] = true;
        header("Location: /thidua/public-scan?token=" . $token);
        exit;
    } else {
        $error_msg = "Mật khẩu không đúng!";
    }
}

$requires_password = !empty($hoat_dong['scan_password']);
$is_authenticated = isset($_SESSION[$auth_key]) && $_SESSION[$auth_key] === true;

if ($requires_password && !$is_authenticated) {
    // Hiển thị form nhập mật khẩu với giao diện đồng bộ
    require_once __DIR__ . '/../views/partials/school_chrome.php';
    $pageTitle = 'Bảo Mật Điểm Danh - ' . ($hoat_dong['ten_hoat_dong'] ?? 'THPT Bình Sơn');
    $pageDescription = 'Xác thực mật khẩu quét điểm danh hoạt động';
    $pageCanonicalUrl = SeoService::getBaseUrl() . '/thidua/public-scan?token=' . urlencode($token);
    $pageOgType = 'website';
    $active = '';
    $contentLayoutConfig = ContentLayoutRepository::getConfig();
    $portalTitle = 'HỆ THỐNG ĐIỂM DANH HOẠT ĐỘNG';
    $logo_path = '/thidua/public/assets/img/22logoapp.png';
    $school_name = 'TRƯỜNG THPT BÌNH SƠN';
    $school_year = $hoat_dong['ten_hoat_dong'] ?? 'HỆ THỐNG QUÉT MÃ CÔNG KHAI';

    $breadcrumbItems = [
        ['label' => 'Đánh giá thi đua', 'href' => '/thidua/tracuu'],
        ['label' => 'Quét điểm danh', 'href' => ''],
    ];

    require __DIR__ . '/../../../app/views/partials/head.php';
    ?>
    <body class="bg-gray-50 text-slate-800 antialiased">
    <style>
        .thidua-portal-head { position: relative; border-bottom: 1px solid #f1f5f9; background: linear-gradient(180deg, rgba(239,246,255,.55), #fff); padding: 1rem 1.5rem; }
        .thidua-portal-brand { display: flex; flex-direction: column; align-items: center; text-align: center; gap: 0.75rem; }
        .thidua-portal-logo { display: block; height: 8rem; width: auto; max-width: min(100%, 14rem); object-fit: contain; object-position: center bottom; margin: 0; line-height: 0; }
        .thidua-portal-title { margin: 0; font-size: 1.125rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: #1e3a8a; line-height: 1.3; }
        .thidua-portal-year { margin: 0; font-size: 0.875rem; font-weight: 700; color: #2563eb; line-height: 1.3; }
        .thidua-lookup-form { display: flex; flex-direction: column; align-items: center; gap: 0.875rem; width: 100%; max-width: 22rem; margin-inline: auto; }
        .thidua-lookup-field { width: 100%; }
        .thidua-lookup-label { display: block; margin-bottom: 0.375rem; font-size: 0.9375rem; font-weight: 700; color: #334155; text-align: center; }
        .thidua-lookup-input { display: block; width: 100%; height: 2.75rem; border-radius: 0.5rem; border: 1px solid #e2e8f0; background: #fff; padding: 0 0.875rem; font-size: 1rem; font-weight: 600; color: #0f172a; outline: none; text-align: center; }
        .thidua-lookup-input:focus { border-color: #60a5fa; box-shadow: 0 0 0 3px rgba(59,130,246,.15); }
        .thidua-btn-search { display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem; height: 2.75rem; padding: 0 1.5rem; font-size: 0.875rem; font-weight: 800; border: none; border-radius: 0.5rem; background: #f97316; color: #fff; cursor: pointer; transition: background .2s ease, transform .2s ease; }
        .thidua-btn-search:hover { background: #ea580c; transform: translateX(2px); }
    </style>
    <?php require __DIR__ . '/../../../app/views/partials/header.php'; ?>
    <?php
    $contentSectionTitle = '';
    $contentSectionDescription = '';
    require __DIR__ . '/../../../app/views/partials/content_layout_open.php';
    ?>
      <article class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-md">
        <div class="thidua-portal-head">
          <div class="thidua-portal-brand mx-auto max-w-2xl">
            <img src="<?= htmlspecialchars($logo_path) ?>" alt="<?= htmlspecialchars($school_name) ?>" class="thidua-portal-logo" width="304" height="304">
            <h1 class="thidua-portal-title"><?= htmlspecialchars($portalTitle) ?></h1>
            <p class="thidua-portal-year"><?= htmlspecialchars($hoat_dong['ten_hoat_dong'] ?? 'HỆ THỐNG QUÉT MÃ CÔNG KHAI') ?></p>
          </div>
        </div>
        <div class="p-8 sm:p-6">
          <div class="max-w-md mx-auto text-center">
            <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-blue-50 text-primary-800 flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
              </svg>
            </div>
            <h2 class="text-xl font-bold text-slate-800 mb-1">Yêu cầu mật khẩu</h2>
            <p class="text-slate-500 text-sm mb-6">Liên kết điểm danh này đã được cài đặt mật khẩu bảo vệ.</p>

            <?php if (isset($error_msg)): ?>
              <div class="bg-red-50 text-red-600 p-3 rounded-lg text-sm font-semibold mb-4 border border-red-200">
                <?= htmlspecialchars($error_msg) ?>
              </div>
            <?php endif; ?>

            <form method="POST" class="thidua-lookup-form">
              <div class="thidua-lookup-field">
                <input type="password" name="scan_password_submit" placeholder="Nhập mật khẩu truy cập" required class="thidua-lookup-input">
              </div>
              <button type="submit" class="thidua-btn-search w-full">Truy cập bộ quét</button>
            </form>
          </div>
        </div>
      </article>
    <?php
    require __DIR__ . '/../../../app/views/partials/content_layout_close.php';
    require __DIR__ . '/../../../app/views/partials/footer.php';
    exit;
}

// Gọi giao diện quét mã
require_once __DIR__ . '/../views/public_scan.php';
