<?php
echo '<script>if (window.top !== window.self) { window.top.location.href = window.self.location.href; }</script>';

require_once __DIR__ . '/../lib/tracking.php';
update_activity_log();
$page_title = 'CỔNG THÔNG TIN QUẢN LÝ ĐÁNH GIÁ';
$school_name = 'TRƯỜNG THPT BÌNH SƠN';
$school_year = $school_year_display ?? 'HỆ THỐNG TRA CỨU';
$logo_path = '/thidua/public/assets/img/22logoapp.png';

require_once __DIR__ . '/../../config/oauth_providers.php';

$google_login_url = '#';
$google_oauth_ready = false;

try {
    $google_provider = get_google_provider();
    $google_login_url = $google_provider->getAuthorizationUrl(['scope' => ['email', 'profile']]);
    $google_oauth_ready = true;
} catch (Throwable $oauthError) {
    if (function_exists('log_to_file')) {
        log_to_file('[GOOGLE OAUTH] Không thể khởi tạo provider: ' . $oauthError->getMessage());
    } else {
        error_log('[GOOGLE OAUTH] Init failed: ' . $oauthError->getMessage());
    }
}

try {
    $db = get_db_connection();
    
    $stmt_thong_bao = $db->query("SELECT tieu_de, noi_dung, loai_thong_bao FROM thong_bao_cong_khai WHERE trang_thai = 1 ORDER BY id DESC LIMIT 1");
    $public_notification = $stmt_thong_bao->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {

    $public_notification = null;
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$login_flash_message = null;
$show_login_modal = isset($_GET['show_login']) && $_GET['show_login'] === '1';

if (isset($_SESSION['flash_message'])) {
    $msg_text = is_array($_SESSION['flash_message']) ? $_SESSION['flash_message']['message'] : $_SESSION['flash_message'];
    // Nếu có tham số show_login hoặc thông báo liên quan đến đăng nhập/tài khoản/oauth
    if ($show_login_modal || stripos($msg_text, 'đăng nhập') !== false || stripos($msg_text, 'mật khẩu') !== false || stripos($msg_text, 'truy cập') !== false || stripos($msg_text, 'Google') !== false || stripos($msg_text, 'Zalo') !== false || stripos($msg_text, 'tài khoản') !== false || stripos($msg_text, 'OAuth') !== false) {
        $login_flash_message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        $show_login_modal = true;
    }
}
require_once __DIR__ . '/../lib/lookup_helpers.php';

$current_scope_label = label_tuan_hien_tai($danh_sach_tuan ?? [], $tuan_id ?? 'all', $school_year);
$found_person = !empty($student_info) || !empty($teacher_info);
$violation_summary = $_SESSION['violation_summary'] ?? [];
$has_error = strpos($search_info ?? '', 'Lỗi') !== false;
$filter_label = 'Tất cả';
if (!empty($tuan_id) && $tuan_id !== 'all' && !empty($danh_sach_tuan)) {
    foreach ($danh_sach_tuan as $t) {
        if ((string) $t['id'] === (string) $tuan_id) {
            $filter_label = tuan_label_ngan($t['ten_tuan']);
            $break;
        }
    }
}

require_once __DIR__ . '/partials/school_chrome.php';

$tracuuCanonicalUrl = SeoService::getBaseUrl() . '/thidua/tracuu';
$pageTitle = 'Hệ thống Đánh Giá Thi Đua - THPT Bình Sơn';
$pageDescription = 'Tra cứu vi phạm, thành tích khen thưởng và kết quả đánh giá thi đua dành cho học sinh và giáo viên Trường THPT Bình Sơn.';
$pageKeywords = 'tra cứu vi phạm, thi đua, khen thưởng, đánh giá thi đua, THPT Bình Sơn, quản lý đánh giá, học sinh, giáo viên, GVCN, mã tra cứu, Đồng Nai';
$pageCanonicalUrl = $tracuuCanonicalUrl;
$pageOgType = 'website';
$active = '';
$contentLayoutConfig = ContentLayoutRepository::getConfig();
$portalTitle = 'Hệ thống Đánh Giá Thi Đua';
$recaptchaConfig = require __DIR__ . '/../../config/recaptcha.php';
$recaptchaSiteKey = !empty($recaptchaConfig['enabled']) ? (string) ($recaptchaConfig['site_key'] ?? '') : '';
$pageOgImage = $logo_path;
$pageOgImageAlt = $pageTitle;
$pageOgImageWidth = 2244;
$pageOgImageHeight = 838;
$pageJsonLdSchemas = [
    [
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        'name' => $pageTitle,
        'description' => $pageDescription,
        'url' => $tracuuCanonicalUrl,
        'inLanguage' => 'vi-VN',
        'isPartOf' => [
            '@type' => 'WebSite',
            'name' => 'Trường THPT Bình Sơn',
            'url' => SeoService::getBaseUrl(),
        ],
        'publisher' => [
            '@type' => 'EducationalOrganization',
            'name' => 'Trường THPT Bình Sơn',
            'url' => SeoService::getBaseUrl(),
        ],
        'primaryImageOfPage' => [
            '@type' => 'ImageObject',
            'url' => SeoService::absoluteAssetUrl($logo_path),
        ],
    ],
];
if (method_exists(SeoService::class, 'getBreadcrumbSchema')) {
    $pageJsonLdSchemas[] = SeoService::getBreadcrumbSchema([
        ['name' => 'Trang chủ', 'url' => SeoService::getBaseUrl() . '/'],
        ['name' => 'Hệ thống Đánh Giá Thi Đua', 'url' => $tracuuCanonicalUrl],
    ]);
}
require __DIR__ . '/../../../app/views/partials/head.php';
?>
<script>
    // Nếu trang đăng nhập bị load bên trong iframe (do hết session trong popup/modal), 
    // tự động đẩy trang gốc (top window) ra ngoài trang đăng nhập.
    if (window.top !== window.self) {
        window.top.location.href = window.self.location.href;
    }
</script>
<body class="bg-gray-50 text-slate-800 antialiased">
<style>
    .thidua-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .thidua-table { width: 100%; font-size: 0.72rem; white-space: nowrap; border-collapse: collapse; }
    .thidua-table thead th { font-size: 0.62rem; text-transform: uppercase; background: #f8fafc; color: #64748b; font-weight: 700; border: 1px solid #e2e8f0; padding: 0.35rem 0.5rem; }
    .thidua-table td { vertical-align: middle; padding: 0.35rem 0.5rem; border: 1px solid #e2e8f0; }
    .thidua-result-title { text-align: center; font-weight: 800; text-transform: uppercase; margin-bottom: 1rem; font-size: 0.88rem; color: #1e293b; padding-bottom: 0.5rem; border-bottom: 1px solid #e2e8f0; }
    .thidua-summary-row { display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; font-size: 0.85rem; font-weight: 700; border-bottom: 1px solid #e2e8f0; }
    .thidua-portal-head { position: relative; border-bottom: 1px solid #f1f5f9; background: linear-gradient(180deg, rgba(239,246,255,.55), #fff); padding: 0.75rem 1rem 1rem; }
    @media (min-width: 640px) { .thidua-portal-head { padding: 1rem 1.5rem 1.25rem; } }
    .thidua-portal-brand { display: flex; flex-direction: column; align-items: center; text-align: center; gap: 0.75rem; }
    .thidua-portal-logo { display: d-block; height: 7.6rem; width: auto; max-width: min(100%, 13.6rem); object-fit: contain; object-position: center bottom; margin: 0; line-height: 0; }
    @media (min-width: 640px) { .thidua-portal-logo { height: 8.8rem; max-width: 15.2rem; } }
    .thidua-portal-title { margin: 0; font-size: 1rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: #1e3a8a; line-height: 1.3; }
    @media (min-width: 640px) { .thidua-portal-title { font-size: 1.125rem; } }
    @media (min-width: 768px) { .thidua-portal-title { font-size: 1.25rem; } }
    .thidua-portal-year { margin: 0; font-size: 0.8125rem; font-weight: 600; color: #64748b; line-height: 1.25; }
    .thidua-lookup-form { display: flex; flex-direction: column; align-items: center; gap: 0.875rem; width: 100%; max-width: 20rem; margin-inline: auto; }
    .thidua-lookup-field { width: 100%; }
    .thidua-lookup-label { display: d-block; margin-bottom: 0.375rem; font-size: 0.9375rem; font-weight: 700; color: #334155; text-align: center; }
    .thidua-lookup-input { display: d-block; width: 100%; height: 2.75rem; border-radius: 0.5rem; border: 1px solid #e2e8f0; background: #fff; padding: 0 0.875rem; font-size: 1rem; font-weight: 600; color: #0f172a; outline: none; text-align: center; }
    .thidua-lookup-input:focus { border-color: #60a5fa; box-shadow: 0 0 0 3px rgba(59,130,246,.15); }
    .thidua-lookup-input::placeholder { font-weight: 500; color: #94a3b8; }
    .thidua-recaptcha-wrap { display: flex; justify-content: center; width: 100%; overflow: hidden; }
    .thidua-recaptcha-wrap > div { transform-origin: center top; }
    #tracuuResultsDynamic { transition: opacity .15s ease; }
    #tracuuResultsDynamic.is-loading { opacity: 0.55; pointer-events: none; }
    .thidua-login-link { position: absolute; right: 1rem; top: 1rem; display: inline-flex; align-items: center; gap: 0.35rem; border: 0; background: transparent; padding: 0.25rem 0; font-size: 0.875rem; font-weight: 800; color: #1e40af; cursor: pointer; transition: color .2s ease, transform .2s ease; }
    .thidua-login-link:hover { color: #f97316; transform: translateX(4px); }
    @media (min-width: 640px) { .thidua-login-link { right: 1.5rem; top: 1.5rem; font-size: 0.9375rem; } }
    .thidua-bg-primary-600 hover:bg-primary-700 text-white shadow-sm border-transparent { display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem; height: 2.5rem; padding: 0 1.25rem; font-size: 0.875rem; font-weight: 800; border: none; border-radius: 0.5rem; background: #1d4ed8; color: #fff; cursor: pointer; transition: background .2s ease, transform .2s ease; }
    .thidua-bg-primary-600 hover:bg-primary-700 text-white shadow-sm border-transparent:hover { background: #f97316; transform: translateX(4px); }
    .thidua-bg-primary-600 hover:bg-primary-700 text-white shadow-sm border-transparent:disabled { opacity: .65; cursor: not-allowed; transform: none; }
    .thidua-bg-primary-600 hover:bg-primary-700 text-white shadow-sm border-transparent.w-100 { width: 100%; }
    .thidua-btn-search { display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem; height: 2.5rem; padding: 0 1.25rem; font-size: 0.875rem; font-weight: 800; border: none; border-radius: 0.5rem; background: #f97316; color: #fff; cursor: pointer; transition: background .2s ease, transform .2s ease; }
    .thidua-btn-search:hover { background: #ea580c; transform: translateX(4px); }
    .thidua-action-bar { margin: 0.5rem 0 1rem; border-radius: 0.75rem; border: 1px solid #e2e8f0; background: linear-gradient(180deg, #f8fafc, #fff); padding: 0.875rem 1rem; }
    .thidua-action-bar-inner { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 0.75rem 1rem; }
    .thidua-filter-group { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; }
    .thidua-filter-label { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: #475569; white-space: nowrap; }
    .thidua-select { min-width: 8.5rem; height: 2.375rem; border-radius: 0.5rem; border: 1px solid #cbd5e1; background: #fff; padding: 0 0.75rem; font-size: 0.8125rem; font-weight: 700; color: #1e293b; outline: none; transition: border-color .15s ease, box-shadow .15s ease; }
    .thidua-select:focus { border-color: #60a5fa; box-shadow: 0 0 0 3px rgba(59,130,246,.15); }
    .thidua-action-group { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 0.5rem; }
    .thidua-action-link { display: inline-flex; align-items: center; gap: 0.4rem; height: 2.375rem; border-radius: 0.5rem; border: 1px solid #bfdbfe; background: #fff; padding: 0 0.875rem; font-size: 0.8125rem; font-weight: 700; color: #1e40af; text-decoration: none; transition: border-color .15s ease, background .15s ease, color .15s ease, transform .15s ease; }
    .thidua-action-link:hover { border-color: #93c5fd; background: #eff6ff; color: #1d4ed8; transform: translateX(3px); }
    .thidua-action-link-accent { border-color: #fed7aa; color: #c2410c; }
    .thidua-action-link-accent:hover { border-color: #fdba74; background: #fff7ed; color: #ea580c; }
    @media (min-width: 768px) {
        .thidua-action-bar-inner { justify-content: space-between; }
        .thidua-action-group { margin-left: auto; }
    }
    /* Modal Base CSS */
    .modal-backdrop { display: none !important; }
    body.modal-open { overflow: hidden !important; padding-right: 0 !important; }
    .modal { position: fixed; inset: 0; z-index: 1055; display: none; overflow-x: hidden; overflow-y: auto; outline: 0; background-color: rgba(15, 23, 42, 0.5); backdrop-filter: blur(4px); }
    .modal.show { display: block; }
    .modal-dialog { position: relative; width: auto; margin: 0.5rem; pointer-events: none; display: flex; align-items: center; min-height: calc(100% - 1rem); }
    @media (min-width: 576px) { .modal-dialog { max-width: 500px; margin: 1.75rem auto; min-height: calc(100% - 3.5rem); } }
    .modal.fade .modal-dialog { transition: transform .3s ease-out, opacity .3s ease-out; transform: translateY(-20px); opacity: 0; }
    .modal.show .modal-dialog { transform: translateY(0); opacity: 1; }
    .modal-content { position: relative; display: flex; flex-direction: column; width: 100%; pointer-events: auto; background-color: #fff; border-radius: 0.5rem; outline: 0; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); overflow: hidden; }
    .thidua-modal .form-label { display: d-block; margin-bottom: 0.35rem; font-size: 0.875rem; font-weight: 600; color: #334155; }
    .thidua-modal .form-control { display: d-block; width: 100%; padding: 0.5rem 0.75rem; font-size: 0.875rem; border: 1px solid #cbd5e1; border-radius: 0.5rem; background: #fff; color: #0f172a; }
    .thidua-modal .input-group { display: flex; width: 100%; margin-bottom: 0.75rem; }
    .thidua-modal .d-flex align-items-center px-4 rounded-l-md border border-r-0 border-slate-300 bg-light text-muted sm:text-sm { display: flex; align-items: center; padding: 0 0.75rem; background: #f8fafc; border: 1px solid #cbd5e1; border-right: 0; border-radius: 0.5rem 0 0 0.5rem; color: #64748b; }
    .thidua-modal .input-group .form-control { border-radius: 0; d-flex: 1; margin-bottom: 0; }
    .thidua-modal .input-group .form-control:first-child { border-radius: 0.5rem 0 0 0.5rem; }
    .thidua-modal .input-group > .btn.btn-sm { border: 1px solid #cbd5e1; border-left: 0; border-radius: 0 0.5rem 0.5rem 0; background: #f8fafc; padding: 0 0.75rem; cursor: pointer; }
    .thidua-modal .btn.btn-sm { display: inline-flex; align-items: center; justify-content: center; padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 700; border-radius: 0.5rem; border: 1px solid transparent; cursor: pointer; }
    .thidua-modal .bg-primary-600 hover:bg-primary-700 text-white shadow-sm border-transparent { background: #1d4ed8; color: #fff; transition: background .2s ease, transform .2s ease; }
    .thidua-modal .bg-primary-600 hover:bg-primary-700 text-white shadow-sm border-transparent:hover { background: #f97316; transform: translateX(4px); }
    .thidua-modal .btn-secondary, .thidua-modal .btn-outline-secondary { background: #f1f5f9; color: #334155; border-color: #cbd5e1; }
    .thidua-modal .btn.btn-sm { background: #fff; color: #0f172a; border-color: #cbd5e1; }
    .thidua-modal .btn.btn-sm { background: #f8fafc; color: #334155; border-color: #e2e8f0; }
    .thidua-modal .btn-success { background: #16a34a; color: #fff; }
    .thidua-modal .btn-close { width: 1rem; height: 1rem; margin-left: auto; border: 0; background: transparent url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23000'%3e%3cpath d='M.293.293a1 1 0 0 1 1.414 0L8 6.586 14.293.293a1 1 0 1 1 1.414 1.414L9.414 8l6.293 6.293a1 1 0 0 1-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 0 1-1.414-1.414L6.586 8 .293 1.707a1 1 0 0 1 0-1.414z'/%3e%3c/svg%3e") center/1rem auto no-repeat; opacity: .55; cursor: pointer; }
    .thidua-modal .alert { padding: 0.75rem 1rem; border-radius: 0.5rem; margin-bottom: 1rem; font-size: 0.875rem; }
    .thidua-modal .alert-danger { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
    .thidua-modal .alert-success { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
    .thidua-modal .alert-warning { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
    .thidua-modal .d-flex align-items-center { display: flex; align-items: center; gap: 0.5rem; margin: 0.75rem 0; font-size: 0.875rem; }
    .thidua-modal .w-100 { width: 100%; }
    .thidua-modal .hidden { display: none !important; }
    .thidua-modal .spinner-border { display: d-inline-block; width: 1.25rem; height: 1.25rem; vertical-align: -0.125em; border: 0.2em solid currentColor; border-right-color: transparent; border-radius: 50%; animation: thidua-spin .75s linear infinite; }
    .thidua-modal .spinner-border-sm { width: 1rem; height: 1rem; border-width: 0.15em; }
    @keyframes thidua-spin { to { transform: rotate(360deg); } }
    .thidua-modal .invalid-feedback { display: none; margin-top: 0.25rem; font-size: 0.75rem; color: #dc2626; }
    .thidua-modal .is-invalid { border-color: #fca5a5 !important; }
    .thidua-modal .is-invalid ~ .invalid-feedback { display: d-block; }
    .thidua-modal .text-muted { color: #64748b; }
    .thidua-modal .text-primary { color: #1d4ed8; }
    .thidua-modal .fw-bold { font-weight: 700; }
    .thidua-modal .modal-title { margin: 0; font-size: 1rem; font-weight: 800; }
    .thidua-modal .justify-content-center { justify-content: center; }
    .thidua-modal .border-0 { border: 0 !important; }
    .thidua-modal .position-relative { position: relative; }
    .thidua-modal .z-1 { z-index: 1; }
    .thidua-modal .img-fluid { max-width: 100%; height: auto; }
    .thidua-modal .fs-5 { font-size: 1.125rem; }
    .thidua-modal .mb-4 { margin-bottom: 0.75rem; }
    .thidua-modal .my-4 { margin-top: 0.75rem; margin-bottom: 0.75rem; }
    .thidua-modal .mt-2 { margin-top: 0.5rem; }
    .thidua-modal .mt-4 { margin-top: 0.75rem; }
    .thidua-modal .p-4 { padding: 1rem; }
    .thidua-modal .p-5 { padding: 1.25rem; }
    .thidua-modal .sub { color: #475569; }
    .thidua-badge { display: inline-flex; min-width: 1.75rem; align-items: center; justify-content: center; border-radius: 9999px; padding: 0.125rem 0.5rem; font-size: 0.75rem; font-weight: 700; color: #fff; }
    .thidua-badge-danger { background: #dc2626; }
    .thidua-badge-dark { background: #334155; }
    .modal-celebrate .relative bg-white rounded-xl shadow-xl table-bordered d-flex d-flex-col { background: url('/thidua/public/assets/img/congratulation-bg1.png') center/cover no-repeat; border: none; overflow: hidden; }
    .modal-celebrate .relative bg-white rounded-xl shadow-xl table-bordered d-flex d-flex-col::before { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(255,255,255,.96), rgba(255,255,255,.88)); z-index: 0; }
    .modal-celebrate .p-4 space-y-4, .modal-celebrate .d-flex align-items-center justify-content-between p-4 border-b rounded-t-xl, .modal-celebrate .d-flex align-items-center justify-content-end p-4 border-t space-x-2 rounded-b-xl { position: relative; z-index: 1; }
    .modal-celebrate .title { color: #dc2626; font-weight: 800; }
</style>
<?php require __DIR__ . '/../../../app/views/partials/header.php'; ?>

<?php
$contentSectionTitle = '';
$contentSectionDescription = '';
$breadcrumbItems = [
    ['label' => 'Đánh giá thi đua', 'href' => '/thidua/tracuu'],
    ['label' => 'Tra cứu', 'href' => ''],
];
require __DIR__ . '/../../../app/views/partials/content_layout_open.php';
?>

  <article class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-md">
    <div class="thidua-portal-head">
      <button type="button" class="thidua-login-link" title="Đăng nhập quản trị" aria-label="Đăng nhập quản trị">
        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-box-arrow-in-right" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M6 3.5a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 0-1 0v2A1.5 1.5 0 0 0 6.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-8A1.5 1.5 0 0 0 5 3.5v2a.5.5 0 0 0 1 0z"/>   <path fill-rule="evenodd" d="M11.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H1.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z"/></svg>
        <span>Đăng nhập</span>
      </button>
      <div class="thidua-portal-brand mx-auto max-w-2xl">
        <img src="<?= htmlspecialchars($logo_path) ?>" alt="<?= htmlspecialchars($school_name) ?>" class="thidua-portal-logo" width="304" height="304">
        <h1 class="thidua-portal-title"><?= htmlspecialchars($portalTitle) ?></h1>
        <p class="thidua-portal-year"><?= htmlspecialchars($school_year) ?></p>
      </div>
    </div>

    <div class="p-6 sm:p-4">
      <div class="prose prose-slate mt-2 max-w-none prose-headings:fw-bold prose-p:leading-7 prose-li:leading-7">


        <div class="not-prose mx-auto mt-6">
          <form action="/thidua/tracuu" method="POST" id="lookupForm" novalidate>
            <input type="hidden" name="action" id="form_action" value="search">
            <input type="hidden" name="tuan_id" id="tuan_id_hidden" value="<?= htmlspecialchars($tuan_id ?? 'all') ?>">
            <div class="thidua-lookup-form">
              <div class="thidua-lookup-field">
                <label class="thidua-lookup-label" for="search_code_input">Mã tra cứu</label>
                <input type="text" class="thidua-lookup-input" name="search_code" id="search_code_input" placeholder="Nhập mã tra cứu" value="<?= htmlspecialchars($search_code ?? '') ?>" required autocomplete="off">
              </div>
              <?php if ($recaptchaSiteKey !== '') : ?>
              <div id="recaptcha-lookup" class="thidua-recaptcha-wrap"></div>
              <?php endif; ?>
              <button class="thidua-btn-search" type="submit">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/></svg>
                Tra cứu
              </button>
            </div>
          </form>
        </div>

<?php if (!empty($is_search_performed)) : ?>
        <div id="searchResults" class="not-prose mt-8 space-y-5">
        <?php if ($has_error) : ?>
            <div class="rounded-lg border border-red-200 bg-red-50 px-6 py-6 text-sm text-red-800"><?= htmlspecialchars($search_info ?? 'Đã có lỗi xảy ra.') ?></div>
        <?php elseif ($found_person) : ?>

            <?php if (!empty($student_info)) : ?>
    <div class="rounded-lg border border-slate-200 bg-slate-50 p-6 sm:p-5">
            <div class="thidua-result-title">Thông tin học sinh</div>
            <div class="grid gricols-1 gap-6 text-sm text-slate-700 md:gricols-2">
                <div>
                    <strong>Email:</strong> <?= htmlspecialchars($student_info['email'] ?? 'Vui lòng đăng nhập để xác minh email.') ?><br>
                    <strong>Họ và tên:</strong> <?= htmlspecialchars($student_info['ho_dem'] . ' ' . $student_info['ten']) ?> <?php if ($is_birthday) : ?>🎂<?php endif; ?><br>
                    <strong>Trạng thái:</strong>
                    <?php 
                        $status = $student_info['trang_thai_hoc_tap'] ?? 'dang_hoc';
                        if ($status === 'dang_hoc') {
                            echo '<span class="font-bold text-green-700">Đang học</span>';
                        } else {
                            echo '<span class="font-bold text-red-700">Đã nghỉ học</span>';
                        }
                    ?>
                    </div>
                <div>
                    <strong>Lớp:</strong> <?= htmlspecialchars($student_info['ten_lop']) ?><br>
                    <strong>GVCN:</strong> <?= htmlspecialchars($student_info['gvcn_ten'] ?? 'Chưa cập nhật') ?>
                </div>
            </div>
    </div>
<?php endif; ?>
            <?php if (!empty($teacher_info)) : ?>
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-6 sm:p-5">
                        <div class="thidua-result-title">Thông tin Giáo viên</div>
                        <div class="grid gricols-1 gap-6 text-sm text-slate-700 md:gricols-2">
                            <div><strong>Mã giáo viên:</strong> <?= htmlspecialchars($teacher_info['gvcn_ma']) ?><br><strong>Họ và tên:</strong> <?= htmlspecialchars($teacher_info['gvcn_ten']) ?> <?php if ($is_birthday) : ?>🎂<?php endif; ?><br><strong>Lớp chủ nhiệm:</strong> <?= htmlspecialchars($teacher_info['ten_lop']) ?> (Sĩ số: <?= (int) $teacher_info['si_so'] ?>)</div>
                            <div id="email-section" data-class-id="<?= (int) $teacher_info['id'] ?>"><strong>Email:</strong>
                                <div id="email-display-wrapper" class="mt-1 flex flex-wrap items-center gap-2"><input type="text" class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-sm" value="<?= htmlspecialchars($teacher_info['gvcn_email'] ?? 'Chưa có') ?>" readonly><button class="rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-light" id="change-email-btn">Sửa</button></div>
                                <div id="email-edit-wrapper" class="mt-2 hidden">
                                    <div class="flex flex-wrap gap-2"><input type="email" id="new-email-input" class="min-w-[180px] d-flex-1 rounded-lg border border-slate-200 px-2 py-1 text-sm" placeholder="nhap@gmail.com" value="<?= htmlspecialchars($teacher_info['gvcn_email'] ?? '') ?>"><button class="rounded-lg bg-slate-600 px-6 py-1 text-xs font-semibold text-white" id="send-otp-btn">Gửi OTP</button></div>
                                </div>
                                <div id="otp-wrapper" class="mt-2 hidden">
                                    <div class="flex flex-wrap gap-2"><input type="number" id="otp-input" class="min-w-[120px] rounded-lg border border-slate-200 px-2 py-1 text-sm" placeholder="Nhập mã OTP..."><button class="rounded-lg bg-green-600 px-6 py-1 text-xs font-semibold text-white" id="verify-otp-btn">Xác thực & Lưu</button></div>
                                    <div class="mt-1 text-xs text-slate-500" id="otp-message"></div>
                                </div>
                            </div>
                        </div>
                </div>
            <?php endif; ?>

<div class="thidua-action-bar">
    <div class="thidua-action-bar-inner">
        <div class="thidua-filter-group">
            <span class="thidua-filter-label" for="filterWeekSelect"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-funnel" viewBox="0 0 16 16"><path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5zm1 .5v1.308l4.372 4.858A.5.5 0 0 1 7 8.5v5.306l2-.666V8.5a.5.5 0 0 1 .128-.334L13.5 3.308V2z"/></svg> Lọc tuần</span>
            <select id="filterWeekSelect" class="thidua-select">
                <option value="all" data-label="Tất cả"<?= (empty($tuan_id) || $tuan_id === 'all') ? ' selected' : '' ?>>Tất cả</option>
                <?php if (!empty($danh_sach_tuan)) foreach ($danh_sach_tuan as $tuan) : $short = tuan_label_ngan($tuan['ten_tuan']); ?>
                    <option value="<?= htmlspecialchars($tuan['id']) ?>" data-label="<?= htmlspecialchars($short) ?>"<?= ((string) $tuan_id === (string) $tuan['id']) ? ' selected' : '' ?>><?= htmlspecialchars($short) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if (!empty($teacher_info)) : ?>
        <div class="thidua-action-group">
            <a href="https://c3binhson.edu.vn/thidua/bao-cao/cong-khai?tuan_id=1" class="thidua-action-link" target="_blank" rel="noopener noreferrer">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-bar-chart-line" viewBox="0 0 16 16"><path d="M11 2a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v12h.5a.5.5 0 0 1 0 1H.5a.5.5 0 0 1 0-1H1v-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3h1V7a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7h1zm1 12h2V2h-2zm-3 0V7H7v7zm-5 0v-3H2v3z"/></svg>
                <span>Báo cáo thi đua</span>
            </a>
            <a href="/thidua/admin/vi-pham?action=export_excel_lop&class_id=<?= (int) $teacher_info['id'] ?>" target="_blank" rel="noopener noreferrer" class="thidua-action-link thidua-action-link-accent">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-spreadsheet" viewBox="0 0 16 16"><path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V9H3V2a1 1 0 0 1 1-1h5.5zM3 12v-2h2v2zm0 1h2v2H4a1 1 0 0 1-1-1zm3 2v-2h3v2zm4 0v-2h3v1a1 1 0 0 1-1 1zm3-3h-3v-2h3zm-7 0v-2h3v2z"/></svg>
                <span>Tải DS vi phạm</span>
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>
            <div id="tracuuResultsDynamic">
            <?php require __DIR__ . '/partials/tracuu_filter_results.php'; ?>
            </div>

        <?php else : ?>
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-6 py-6 text-center text-sm font-semibold text-amber-900">Không tìm thấy thông tin <strong>"<?= htmlspecialchars($search_code ?? '') ?>"</strong>, vui lòng kiểm tra và nhập lại.</div>
        <?php endif; ?>
        </div>
<?php endif; ?>
      </div>

      <div class="mt-10 space-y-5 border-t border-slate-200 pt-6">
        <?php
        $shareUrl = (isset($_SERVER['REQUEST_SCHEME'], $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']))
            ? ($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'])
            : '';
        $shareTitle = (string) ($contentLayoutConfig['share_title'] ?? 'Chia sẻ');
        $viewCount = (int) ($tracuuViewCount ?? 0);
        require __DIR__ . '/../../../app/views/partials/content_share.php';
        require __DIR__ . '/../../../app/views/partials/content_quick_links.php';
        ?>
      </div>
    </div>
  </article>
<?php require __DIR__ . '/../../../app/views/partials/content_layout_close.php'; ?>

<div class="thidua-modal">
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="flex items-center justify-between p-4 sm:p-6 border-b border-slate-100 bg-slate-50">
                    <h5 class="text-lg sm:text-xl font-bold text-[#224397] flex items-center gap-2 uppercase tracking-wide" id="loginModalLabel">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-person-circle" viewBox="0 0 16 16"><path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/>   <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1"/></svg> CỔNG THÔNG TIN
                    </h5>
                    <button type="button" class="text-slate-400 hover:text-[#FAB723] transition-colors p-1" aria-label="Đóng" data-bs-dismiss="modal" onclick="if(window.thiduaCloseModal) thiduaCloseModal(document.getElementById('loginModal'));">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg text-base sm:text-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg>
                    </button>
                </div>
                <div class="p-5 sm:p-8">
                    <div id="login-alert-container">
                        <?php if ($login_flash_message) : ?>
                            <div class="p-3 sm:p-4 mb-4 sm:mb-6 rounded text-xs sm:text-sm alert-<?= htmlspecialchars($login_flash_message['type']) ?> bg-<?= $login_flash_message['type'] === 'danger' ? 'red' : ($login_flash_message['type'] === 'success' ? 'green' : 'blue') ?>-50 text-<?= $login_flash_message['type'] === 'danger' ? 'red' : ($login_flash_message['type'] === 'success' ? 'green' : 'blue') ?>-700 border border-<?= $login_flash_message['type'] === 'danger' ? 'red' : ($login_flash_message['type'] === 'success' ? 'green' : 'blue') ?>-200" role="alert">
                                <?= htmlspecialchars($login_flash_message['message']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <form id="loginForm" action="/thidua/dang-nhap-xu-ly" method="POST" novalidate class="space-y-4 sm:space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="ten_dang_nhap">Tên đăng nhập</label>
                            <div class="flex rounded shadow-sm group">
                                <span class="inline-flex items-center px-3 sm:px-4 rounded-l border border-r-0 border-slate-300 bg-slate-50 text-slate-500 group-focus-within:border-[#224397] group-focus-within:text-[#224397] transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-person text-sm sm:text-base" viewBox="0 0 16 16"><path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/></svg>
                                </span>
                                <input type="text" class="flex-1 block w-full px-3 py-2 sm:py-2.5 rounded-none rounded-r border border-slate-300 focus:outline-none focus:ring-1 focus:ring-[#224397] focus:border-[#224397] text-slate-900 transition-colors text-sm sm:text-base" id="ten_dang_nhap" name="ten_dang_nhap" placeholder="Nhập tên đăng nhập" required autocomplete="username">
                            </div>
                            <div class="invalid-feedback mt-1 text-xs sm:text-sm text-red-600 hidden">Vui lòng nhập tên đăng nhập.</div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="mat_khau">Mật khẩu</label>
                            <div class="flex rounded shadow-sm group">
                                <span class="inline-flex items-center px-3 sm:px-4 rounded-l border border-r-0 border-slate-300 bg-slate-50 text-slate-500 group-focus-within:border-[#224397] group-focus-within:text-[#224397] transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-lock text-sm sm:text-base" viewBox="0 0 16 16"><path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2m3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2M5 8h6a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1"/></svg>
                                </span>
                                <input type="password" class="flex-1 block w-full px-3 py-2 sm:py-2.5 rounded-none border border-r-0 border-slate-300 focus:outline-none focus:ring-1 focus:ring-[#224397] focus:border-[#224397] text-slate-900 transition-colors text-sm sm:text-base" id="mat_khau" name="mat_khau" placeholder="Nhập mật khẩu" required autocomplete="current-password" minlength="5">
                                <button type="button" class="inline-flex items-center px-3 sm:px-4 rounded-r border border-slate-300 bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition-colors focus:outline-none focus:ring-1 focus:ring-[#224397] focus:border-[#224397]" id="togglePassword" aria-label="Hiển thị mật khẩu">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-eye text-sm sm:text-base" viewBox="0 0 16 16"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>   <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/></svg>
                                </button>
                            </div>
                            <div class="invalid-feedback mt-1 text-xs sm:text-sm text-red-600 hidden">Mật khẩu tối thiểu 5 ký tự.</div>
                        </div>
                        <div class="flex items-center mt-2">
                            <input class="h-3.5 w-3.5 sm:h-4 sm:w-4 text-[#224397] focus:ring-[#224397] border-slate-300 rounded cursor-pointer transition-colors" type="checkbox" name="remember_me" id="remember_me" value="1">
                            <label class="ml-2 block text-xs sm:text-sm text-slate-700 font-medium cursor-pointer select-none" for="remember_me">Ghi nhớ đăng nhập</label>
                        </div>
                        
                        <?php if ($recaptchaSiteKey !== '') : ?>
                        <div id="recaptcha-login" class="flex justify-center mt-3 sm:mt-4 w-full overflow-hidden transform scale-90 sm:scale-100 origin-center"></div>
                        <?php endif; ?>
                        
                        <input type="hidden" name="gps_lat" id="gps_lat" value="">
                        <input type="hidden" name="gps_lon" id="gps_lon" value="">

                        
                        <button type="submit" class="w-full flex items-center justify-center gap-2 py-2 sm:py-2.5 px-4 mt-5 sm:mt-6 border border-transparent rounded shadow-sm text-sm sm:text-base font-bold text-white bg-[#224397] hover:bg-[#FAB723] hover:text-slate-900 hover:scale-[1.02] hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#224397] transition-all duration-300 ease-out" id="loginSubmit">
                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-box-arrow-in-right text-base sm:text-lg" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M6 3.5a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 0-1 0v2A1.5 1.5 0 0 0 6.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-8A1.5 1.5 0 0 0 5 3.5v2a.5.5 0 0 0 1 0z"/>   <path fill-rule="evenodd" d="M11.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H1.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z"/></svg> Đăng nhập
                            <span class="spinner-border spinner-border-sm hidden ml-2" role="status" aria-hidden="true"></span>
                        </button>
                    </form>
                    
                    <div class="mt-5 sm:mt-6 relative">
                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                            <div class="w-full border-t border-slate-200"></div>
                        </div>
                        <div class="relative flex justify-center text-xs sm:text-sm">
                            <span class="px-3 bg-white text-slate-500 font-medium">Hoặc đăng nhập bằng</span>
                        </div>
                    </div>
                    
                    <div class="mt-4 sm:mt-6 flex flex-col sm:flex-row gap-3">
                        <a href="<?= htmlspecialchars($google_login_url) ?>" class="w-full sm:w-1/2 flex items-center justify-center gap-2 py-2 sm:py-2.5 px-3 border border-slate-300 rounded shadow-sm bg-white text-sm font-bold text-slate-700 hover:bg-slate-50 hover:border-slate-400 transition-all duration-200 <?= $google_oauth_ready ? '' : ' opacity-60 cursor-not-allowed pointer-events-none' ?>">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" alt="Google" class="w-4 h-4">
                            Google
                        </a>
                        <a href="/thidua/oauth-redirect-zalo" class="w-full sm:w-1/2 flex items-center justify-center gap-2 py-2 sm:py-2.5 px-3 border border-transparent rounded shadow-sm bg-[#0068FF] text-white text-sm font-bold hover:bg-[#0054cc] transition-all duration-200">
                            <img src="/thidua/public/assets/img/icons/zalo.svg" alt="Zalo" class="w-5 h-5">
                            Zalo
                        </a>
                    </div>
                    <?php if (!$google_oauth_ready) : ?>
                        <p class="mt-2 text-center text-[10px] sm:text-xs text-red-500 font-medium">Tính năng Google chưa khả dụng. Vui lòng thử lại sau.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="twoFaModal" tabindex="-1" aria-labelledby="twoFaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered w-[400px] max-w-[95%]">
            <div class="modal-content relative bg-white rounded-2xl shadow-2xl border-0 overflow-hidden flex flex-col">
                <div class="flex items-center justify-between px-6 py-4 bg-slate-50 border-b border-slate-100">
                    <h5 class="text-lg font-bold text-[#224397] flex items-center gap-2" id="twoFaModalLabel">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-shield-lock-fill text-xl" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 0c-.69 0-1.843.265-2.928.56-1.11.3-2.229.655-2.887.87a1.54 1.54 0 0 0-1.044 1.262c-.596 4.477.787 7.795 2.465 9.99a11.8 11.8 0 0 0 2.517 2.453c.386.273.744.482 1.048.625.28.132.581.24.829.24s.548-.108.829-.24a7 7 0 0 0 1.048-.625 11.8 11.8 0 0 0 2.517-2.453c1.678-2.195 3.061-5.513 2.465-9.99a1.54 1.54 0 0 0-1.044-1.263 63 63 0 0 0-2.887-.87C9.843.266 8.69 0 8 0m0 5a1.5 1.5 0 0 1 .5 2.915l.385 1.99a.5.5 0 0 1-.491.595h-.788a.5.5 0 0 1-.49-.595l.384-1.99A1.5 1.5 0 0 1 8 5"/></svg> Xác thực 2 yếu tố
                    </h5>
                    <button type="button" class="text-slate-400 hover:text-slate-600 hover:bg-slate-200 p-2 rounded-lg transition" aria-label="Close"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg></button>
                </div>
                <div class="p-6">
                    <div id="twofa-alert-container"></div>
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4 text-[#224397]">
                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-phone-vibrate text-3xl" viewBox="0 0 16 16"><path d="M10 3a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1zM6 2a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2z"/>   <path d="M8 12a1 1 0 1 0 0-2 1 1 0 0 0 0 2M1.599 4.058a.5.5 0 0 1 .208.676A7 7 0 0 0 1 8c0 1.18.292 2.292.807 3.266a.5.5 0 0 1-.884.468A8 8 0 0 1 0 8c0-1.347.334-2.619.923-3.734a.5.5 0 0 1 .676-.208m12.802 0a.5.5 0 0 1 .676.208A8 8 0 0 1 16 8a8 8 0 0 1-.923 3.734.5.5 0 0 1-.884-.468A7 7 0 0 0 15 8c0-1.18-.292-2.292-.807-3.266a.5.5 0 0 1 .208-.676M3.057 5.534a.5.5 0 0 1 .284.648A5 5 0 0 0 3 8c0 .642.12 1.255.34 1.818a.5.5 0 1 1-.93.364A6 6 0 0 1 2 8c0-.769.145-1.505.41-2.182a.5.5 0 0 1 .647-.284m9.886 0a.5.5 0 0 1 .648.284C13.855 6.495 14 7.231 14 8s-.145 1.505-.41 2.182a.5.5 0 0 1-.93-.364C12.88 9.255 13 8.642 13 8s-.12-1.255-.34-1.818a.5.5 0 0 1 .283-.648"/></svg>
                        </div>
                        <p class="text-slate-600 text-sm">Vui lòng mở ứng dụng <strong class="text-slate-800">Google Authenticator</strong> hoặc <strong class="text-slate-800">Authy</strong> và nhập mã 6 số để hoàn tất đăng nhập.</p>
                    </div>
                    <div class="mb-2">
                        <label for="twoFaCodeInput" class="block text-sm font-semibold text-slate-700 mb-2 text-center">Mã xác nhận (6 số)</label>
                        <input type="text" class="block w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-[#224397] focus:ring-2 focus:ring-blue-100 outline-none transition text-center text-2xl tracking-[0.5em] font-mono text-slate-800 placeholder-slate-300" id="twoFaCodeInput" placeholder="••••••" inputmode="numeric" autocomplete="one-time-code" maxlength="6">
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" class="px-5 py-2.5 rounded-xl font-medium text-slate-600 hover:bg-slate-200 transition-colors">Hủy</button>
                    <button type="button" class="px-6 py-2.5 rounded-xl font-medium bg-[#224397] text-white hover:bg-[#1a3375] hover:-translate-y-0.5 hover:shadow-lg transition-all flex items-center gap-2" id="twoFaConfirmBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>   <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/></svg> Xác nhận
                        <span class="spinner-border spinner-border-sm hidden" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php if ($is_birthday) : ?> <?php if (!empty($student_info)) : ?> <div class="modal fade modal-celebrate" id="birthdayModal" tabindex="-1" aria-labelledby="birthdayModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content relative bg-white rounded-xl shadow-xl border border-slate-200 flex flex-col text-center">
                        <div class="cele-bg"></div>
                        <div class="p-6 space-y-4 p-8 position-relative">
                            <h1 class="title mb-6">🎉 Chúc Mừng Sinh Nhật!</h1>
                            <p class="sub">Trường THPT Bình Sơn gửi lời chúc mừng đến</p>
                            <h3 class="font-bold text-primary-600 mt-2"> <?= htmlspecialchars($student_info['ho_dem'] . ' ' . $student_info['ten']) ?> </h3>
                            <p class="sub mt-6">Chúc em một ngày rực rỡ và một năm học bứt phá!</p> <button type="button" class="btn bg-primary-600 hover:bg-primary-700 text-white shadow-sm border-transparent mt-6"> <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-hand-thumbs-up-fill mr-1" viewBox="0 0 16 16"><path d="M6.956 1.745C7.021.81 7.908.087 8.864.325l.261.066c.463.116.874.456 1.012.965.22.816.533 2.511.062 4.51a10 10 0 0 1 .443-.051c.713-.065 1.669-.072 2.516.21.518.173.994.681 1.2 1.273.184.532.16 1.162-.234 1.733q.086.18.138.363c.077.27.113.567.113.856s-.036.586-.113.856c-.039.135-.09.273-.16.404.169.387.107.819-.003 1.148a3.2 3.2 0 0 1-.488.901c.054.152.076.312.076.465 0 .305-.089.625-.253.912C13.1 15.522 12.437 16 11.5 16H8c-.605 0-1.07-.081-1.466-.218a4.8 4.8 0 0 1-.97-.484l-.048-.03c-.504-.307-.999-.609-2.068-.722C2.682 14.464 2 13.846 2 13V9c0-.85.685-1.432 1.357-1.615.849-.232 1.574-.787 2.132-1.41.56-.627.914-1.28 1.039-1.639.199-.575.356-1.539.428-2.59z"/></svg> Happy day! </button>
                        </div>
                    </div>
                </div>
            </div> <?php elseif (!empty($teacher_info)) : ?> <div class="modal fade modal-celebrate" id="teacherBirthdayModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content relative bg-white rounded-xl shadow-xl border border-slate-200 flex flex-col text-center">
                        <div class="cele-bg"></div>
                        <div class="p-6 space-y-4 p-8 position-relative">
                            <h1 class="title mb-6">🎂 Chúc Mừng Sinh Nhật!</h1>
                            <p class="sub">Tập thể Trường THPT Bình Sơn trân trọng chúc mừng Thầy/Cô</p>
                            <h3 class="font-bold text-primary-600 mt-2"> <?= htmlspecialchars($teacher_info['gvcn_ten']) ?> </h3>
                            <p class="sub mt-6">Kính chúc Thầy/Cô luôn mạnh khỏe, hạnh phúc và thành công.</p> <button type="button" class="btn bg-primary-600 hover:bg-primary-700 text-white shadow-sm border-transparent mt-6"> <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-heart-fill mr-1" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 1.314C12.438-3.248 23.534 4.735 8 15-7.534 4.736 3.562-3.248 8 1.314"/></svg> Happy day ! </button>
                        </div>
                    </div>
                </div>
            </div>     <?php endif; ?> <?php endif; ?>

    <?php if (!empty($public_notification)) : ?>
    <div class="modal fade modal-celebrate" id="publicNotificationModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content relative bg-white rounded-xl shadow-xl border border-slate-200 flex flex-col text-center">
                <div class="cele-bg"></div>
                <div class="flex items-center justify-between p-6 border-b rounded-t-xl border-0 justify-center position-relative z-1">
                    <h5 class="text-lg font-semibold text-slate-900 font-bold">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-megaphone-fill" viewBox="0 0 16 16"><path d="M13 2.5a1.5 1.5 0 0 1 3 0v11a1.5 1.5 0 0 1-3 0zm-1 .724c-2.067.95-4.539 1.481-7 1.656v6.237a25 25 0 0 1 1.088.085c2.053.204 4.038.668 5.912 1.56zm-8 7.841V4.934c-.68.027-1.399.043-2.008.053A2.02 2.02 0 0 0 0 7v2c0 1.106.896 1.996 1.994 2.009l.496.008a64 64 0 0 1 1.51.048m1.39 1.081q.428.032.85.078l.253 1.69a1 1 0 0 1-.983 1.187h-.548a1 1 0 0 1-.916-.599l-1.314-2.48a66 66 0 0 1 1.692.064q.491.026.966.06"/></svg>
                        <?= htmlspecialchars($public_notification['tieu_de']) ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="if(window.thiduaCloseModal) thiduaCloseModal(document.getElementById('publicNotificationModal'));"></button>
                </div>
                <div class="p-6 space-y-4 text-center position-relative z-1">
                    <?php if (!empty($public_notification['hinh_anh'])) : ?>
                        <img src="/thidua/public/assets/thong_bao/<?= htmlspecialchars($public_notification['hinh_anh']) ?>" alt="Hình ảnh thông báo" class="img-fluid mb-6">
                    <?php endif; ?>
                    <p class="mt-2 text-lg mb-0"><?= nl2br(htmlspecialchars($public_notification['noi_dung'])) ?></p>
                </div>
                <div class="flex items-center justify-end p-6 border-t space-x-2 rounded-b-xl border-0 justify-center position-relative z-1">
                    <button type="button" class="btn btn" data-bs-dismiss="modal" onclick="if(window.thiduaCloseModal) thiduaCloseModal(document.getElementById('publicNotificationModal'));">Thoát</button>
                    <?php if (!empty($public_notification['link_url'])) : ?>
                        <a href="<?= htmlspecialchars($public_notification['link_url']) ?>" target="_blank" class="btn bg-primary-600 hover:bg-primary-700 text-white shadow-sm border-transparent">
                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-link-45deg" viewBox="0 0 16 16"><path d="M4.715 6.542 3.343 7.914a3 3 0 1 0 4.243 4.243l1.828-1.829A3 3 0 0 0 8.586 5.5L8 6.086a1 1 0 0 0-.154.199 2 2 0 0 1 .861 3.337L6.88 11.45a2 2 0 1 1-2.83-2.83l.793-.792a4 4 0 0 1-.128-1.287z"/>   <path d="M6.586 4.672A3 3 0 0 0 7.414 9.5l.775-.776a2 2 0 0 1-.896-3.346L9.12 3.55a2 2 0 1 1 2.83 2.83l-.793.792c.112.42.155.855.128 1.287l1.372-1.372a3 3 0 1 0-4.243-4.243z"/></svg>
                            <?= htmlspecialchars($public_notification['link_text'] ?? 'Xem chi tiết') ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
    var thiduaRecaptchaSiteKey = <?= json_encode($recaptchaSiteKey, JSON_UNESCAPED_UNICODE) ?>;
    var thiduaLookupWidgetId = null;
    var thiduaLoginWidgetId = null;
    function thiduaOnRecaptchaLoad() {
        if (thiduaRecaptchaSiteKey && document.getElementById('recaptcha-lookup')) {
            thiduaLookupWidgetId = grecaptcha.render('recaptcha-lookup', { sitekey: thiduaRecaptchaSiteKey });
        }
        if (thiduaRecaptchaSiteKey && document.getElementById('recaptcha-login')) {
            thiduaLoginWidgetId = grecaptcha.render('recaptcha-login', { sitekey: thiduaRecaptchaSiteKey });
        }
    }
    function thiduaGetLookupRecaptcha() {
        if (typeof grecaptcha === 'undefined') return '';
        if (thiduaLookupWidgetId !== null) return grecaptcha.getResponse(thiduaLookupWidgetId) || '';
        return grecaptcha.getResponse() || '';
    }
    function thiduaGetLoginRecaptcha() {
        if (typeof grecaptcha === 'undefined') return '';
        if (thiduaLoginWidgetId !== null) return grecaptcha.getResponse(thiduaLoginWidgetId) || '';
        return grecaptcha.getResponse() || '';
    }
    function thiduaResetLoginRecaptcha() {
        if (typeof grecaptcha === 'undefined' || thiduaLoginWidgetId === null) return;
        grecaptcha.reset(thiduaLoginWidgetId);
    }
    function thiduaModalOptions() {
        return { backdrop: false, keyboard: true };
    }
</script>
<script src="https://www.google.com/recaptcha/api.js?onload=thiduaOnRecaptchaLoad&render=explicit" async defer></script>
<script>
        window.thiduaOpenModal = function(modal) {
            if(!modal) return;
            modal.style.display = 'block';
            setTimeout(() => {
                modal.classList.add('show');
                document.body.classList.add('modal-open');
                modal.dispatchEvent(new Event('show.bs.modal'));
                modal.dispatchEvent(new Event('shown.bs.modal'));
            }, 10);
        };
        window.thiduaCloseModal = function(modal) {
            if(!modal) return;
            modal.classList.remove('show');
            document.body.classList.remove('modal-open');
            setTimeout(() => {
                modal.style.display = 'none';
                modal.dispatchEvent(new Event('hide.bs.modal'));
                modal.dispatchEvent(new Event('hidden.bs.modal'));
            }, 300);
        };
        document.addEventListener('click', function(e) {
            const loginBtn = e.target.closest('.thidua-login-link');
            if (loginBtn) {
                e.preventDefault();
                e.stopPropagation();
                thiduaOpenModal(document.getElementById('loginModal'));
            }
            const dismissBtn = e.target.closest('[data-bs-dismiss="modal"]');
            if (dismissBtn) {
                thiduaCloseModal(dismissBtn.closest('.modal'));
            }
            if (e.target.classList.contains('modal')) {
                thiduaCloseModal(e.target);
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($login_flash_message || $show_login_modal) : ?>
                thiduaOpenModal(document.getElementById('loginModal'));
            <?php endif; ?>
            const lookupForm = document.getElementById('lookupForm');
            lookupForm?.addEventListener('submit', function(e) {
                const act = document.getElementById('form_action');
                if (act && act.value === 'filter') {
                    return;
                }
                if (thiduaRecaptchaSiteKey && !thiduaGetLookupRecaptcha()) {
                    e.preventDefault();
                    alert('Vui lòng xác nhận bạn không phải là người máy.');
                }
            });
            <?php if (!empty($is_search_performed)) : ?>
                document.getElementById('searchResults')?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            <?php endif; ?>
            const pwd = document.getElementById('mat_khau'),
                toggle = document.getElementById('togglePassword');
            toggle?.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const isText = pwd.type === 'text';
                pwd.type = isText ? 'password' : 'text';
                this.setAttribute('aria-pressed', String(!isText));
                this.innerHTML = isText ? '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>   <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/></svg>' : '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-eye-slash" viewBox="0 0 16 16"><path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7 7 0 0 0-2.79.588l.77.771A6 6 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755q-.247.248-.517.486z"/>   <path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829"/>   <path d="M3.35 5.47q-.27.24-.518.487A13 13 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7 7 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12z"/></svg>';
                pwd.focus();
            });
            (function() {
                const form = document.getElementById('lookupForm'),
                    week = document.getElementById('tuan_id_hidden'),
                    filterSelect = document.getElementById('filterWeekSelect'),
                    dynamic = document.getElementById('tracuuResultsDynamic'),
                    searchInput = document.getElementById('search_code_input');
                let filterBusy = false;
                filterSelect?.addEventListener('change', async () => {
                    if (filterBusy || !dynamic) return;
                    const searchCode = searchInput?.value?.trim();
                    if (!searchCode) return;
                    const tuanId = filterSelect.value;
                    const prevValue = week?.value ?? 'all';
                    filterBusy = true;
                    filterSelect.disabled = true;
                    dynamic.classList.add('is-loading');
                    try {
                        const fd = new FormData();
                        fd.append('action', 'filter');
                        fd.append('search_code', searchCode);
                        fd.append('tuan_id', tuanId);
                        const res = await fetch('/thidua/tracuu', {
                            method: 'POST',
                            body: fd,
                            credentials: 'same-origin',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        });
                        const html = await res.text();
                        if (!res.ok) {
                            throw new Error(html.replace(/<[^>]+>/g, '').trim() || 'Không thể lọc dữ liệu.');
                        }
                        dynamic.innerHTML = html;
                        if (week) week.value = tuanId;
                    } catch (err) {
                        filterSelect.value = prevValue;
                        alert(err.message || 'Lỗi khi lọc tuần. Vui lòng thử lại.');
                    } finally {
                        filterBusy = false;
                        filterSelect.disabled = false;
                        dynamic.classList.remove('is-loading');
                    }
                });
            })();
            const sec = document.getElementById('email-section');
            if (sec) {
                const id = sec.dataset.classId,
                    chg = document.getElementById('change-email-btn'),
                    send = document.getElementById('send-otp-btn'),
                    ver = document.getElementById('verify-otp-btn'),
                    vWrap = document.getElementById('email-display-wrapper'),
                    eWrap = document.getElementById('email-edit-wrapper'),
                    oWrap = document.getElementById('otp-wrapper'),
                    eInp = document.getElementById('new-email-input'),
                    oInp = document.getElementById('otp-input'),
                    msg = document.getElementById('otp-message');
                chg?.addEventListener('click', () => {
                    vWrap?.classList.add('hidden');
                    eWrap?.classList.remove('hidden');
                    eInp?.focus();
                });
                send?.addEventListener('click', () => {
                    const em = eInp?.value?.trim();
                    if (!em) {
                        alert('Vui lòng nhập email mới.');
                        return;
                    }
                    send.disabled = true;
                    send.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
                    msg.textContent = '';
                    msg.className = 'form-text';
                    fetch('/thidua/api/send-otp', {
                            method: 'POST',
                            body: new URLSearchParams({
                                email: em,
                                class_id: id
                            })
                        })
                        .then(r => r.json()).then(d => {
                            if (d.success) {
                                oWrap?.classList.remove('hidden');
                                msg.textContent = d.message || 'Đã gửi OTP.';
                                msg.className = 'mt-1 text-xs text-green-700';
                            } else {
                                throw new Error(d.message || 'Gửi OTP thất bại');
                            }
                        }).catch(e => {
                            msg.textContent = e.message;
                            msg.className = 'mt-1 text-xs text-red-700';
                        })
                        .finally(() => {
                            send.disabled = false;
                            send.textContent = 'Gửi OTP';
                        });
                });
                ver?.addEventListener('click', () => {
                    const o = oInp?.value?.trim();
                    if (!o) {
                        alert('Vui lòng nhập mã OTP.');
                        return;
                    }
                    ver.disabled = true;
                    ver.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
                    fetch('/thidua/api/verify-otp', {
                            method: 'POST',
                            body: new URLSearchParams({
                                otp: o,
                                class_id: id
                            })
                        })
                        .then(r => r.json()).then(d => {
                            if (d.success) {
                                alert(d.message || 'Xác thực thành công!');
                                location.reload();
                            } else {
                                throw new Error(d.message || 'Xác thực OTP thất bại');
                            }
                        }).catch(e => alert('Lỗi: ' + e.message))
                        .finally(() => {
                            ver.disabled = false;
                            ver.textContent = 'Xác thực & Lưu';
                        });
                });
            }
            const loginForm = document.getElementById('loginForm'),
                  rememberMeCheckbox = document.getElementById('remember_me'),
                  usernameInput = document.getElementById('ten_dang_nhap'),
                  passwordInput = document.getElementById('mat_khau'),
                  togglePasswordBtn = document.getElementById('togglePassword'),
                  loginSubmit = document.getElementById('loginSubmit');
                  
            // Tự động lấy GPS
            if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lon = position.coords.longitude;
                        document.getElementById('gps_lat').value = lat;
                        document.getElementById('gps_lon').value = lon;
                    },
                    function(error) {
                        console.warn('Không thể lấy vị trí GPS:', error.message);
                    },
                    { timeout: 5000, maximumAge: 60000 }
                );
            }
            const loginModal = document.getElementById('loginModal'),
                  loginAlertContainer = document.getElementById('login-alert-container'),
                  loginSubmitBtn = document.getElementById('loginSubmit'),
                twoFaModalEl = document.getElementById('twoFaModal'),
                twoFaCodeInput = document.getElementById('twoFaCodeInput'),
                twoFaConfirmBtn = document.getElementById('twoFaConfirmBtn'),
                twoFaAlertContainer = document.getElementById('twofa-alert-container');

            // Đã loại bỏ trình lắng nghe togglePassword bị trùng lặp ở đây để tránh xung đột

            const getLoginModalInstance = () => (loginModal ? { hide: () => thiduaCloseModal(loginModal) } : null);
            const getTwoFaModalInstance = () => (twoFaModalEl ? { hide: () => thiduaCloseModal(twoFaModalEl) } : null);

            const toggleButtonLoading = (buttonEl, isLoading) => {
                if (!buttonEl) return;
                const spinnerEl = buttonEl.querySelector('.spinner-border');
                buttonEl.disabled = isLoading;
                if (spinnerEl) spinnerEl.classList.toggle('hidden', !isLoading);
            };

            const renderAlert = (container, message = '', type = 'danger') => {
                if (!container) return;
                if (!message) {
                    container.innerHTML = '';
                    return;
                }
                const bgClass = type === 'danger' ? 'bg-red-100 text-red-800 border-red-300 font-bold' : (type === 'success' ? 'bg-green-100 text-green-800 border-green-300 font-bold' : 'bg-blue-100 text-blue-800 border-blue-300 font-bold');
                container.innerHTML = `<div class="p-4 mb-4 rounded-lg text-sm border ${bgClass} shadow-sm flex items-center gap-3" role="alert"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-red-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg> <span>${message}</span></div>`;
                
                if (typeof AppSwal !== 'undefined') {
                    AppSwal.fire({ icon: type === 'danger' ? 'error' : (type === 'success' ? 'success' : 'info'), title: type === 'danger' ? 'Đăng nhập thất bại' : 'Thông báo', text: message });
                } else if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: type === 'danger' ? 'error' : (type === 'success' ? 'success' : 'info'), title: type === 'danger' ? 'Đăng nhập thất bại' : 'Thông báo', text: message });
                } else {
                    alert(message);
                }
            };

            const openTwoFaModal = () => {
                if (!twoFaModalEl) return;
                renderAlert(twoFaAlertContainer);
                if (twoFaCodeInput) {
                    twoFaCodeInput.value = '';
                    twoFaCodeInput.focus();
                }
                thiduaOpenModal(twoFaModalEl);
            };

            const submitTwoFaCode = async () => {
                if (!twoFaConfirmBtn) return;
                const code = (twoFaCodeInput?.value || '').trim();
                if (!/^[0-9]{6}$/.test(code)) {
                    renderAlert(twoFaAlertContainer, 'Vui lòng nhập đúng 6 số từ ứng dụng của bạn.', 'warning');
                    twoFaCodeInput?.focus();
                    return;
                }
                renderAlert(twoFaAlertContainer);
                toggleButtonLoading(twoFaConfirmBtn, true);
                try {
                    const response = await fetch('/thidua/api/2fa-login', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ code }),
                        credentials: 'same-origin'
                    });
                    const data = await response.json();
                    if (!response.ok || !data.success) {
                        throw new Error(data?.message || 'Mã 2FA không chính xác. Vui lòng thử lại.');
                    }
                    const redirectUrl = data.redirect_url || '/thidua';
                    window.location.href = redirectUrl;
                } catch (error) {
                    console.error('Lỗi 2FA:', error);
                    renderAlert(twoFaAlertContainer, error.message || 'Không thể xác thực 2FA lúc này.', 'danger');
                } finally {
                    toggleButtonLoading(twoFaConfirmBtn, false);
                }
            };

            twoFaModalEl?.addEventListener('shown.bs.modal', () => {
                twoFaCodeInput?.focus();
            });
            twoFaModalEl?.addEventListener('hidden.bs.modal', () => {
                if (twoFaCodeInput) twoFaCodeInput.value = '';
                renderAlert(twoFaAlertContainer);
            });
            twoFaCodeInput?.addEventListener('input', () => {
                twoFaCodeInput.value = twoFaCodeInput.value.replace(/\D/g, '').slice(0, 6);
            });
            twoFaConfirmBtn?.addEventListener('click', submitTwoFaCode);
            twoFaCodeInput?.addEventListener('keydown', (evt) => {
                if (evt.key === 'Enter') {
                    evt.preventDefault();
                    submitTwoFaCode();
                }
            });

            loginForm?.addEventListener('submit', async function(event) {
                event.preventDefault();
                loginForm.classList.remove('was-validated');
                if (!loginForm.checkValidity()) {
                    loginForm.classList.add('was-validated');
                    loginForm.reportValidity();
                    return;
                }

                if (thiduaRecaptchaSiteKey && !thiduaGetLoginRecaptcha()) {
                    renderAlert(loginAlertContainer, 'Vui lòng xác nhận bạn không phải là người máy.', 'danger');
                    return;
                }

                if (rememberMeCheckbox?.checked) {
                    localStorage.setItem('savedUsername_thidua', usernameInput.value);
                    localStorage.setItem('savedPassword_thidua', passwordInput.value);
                } else {
                    localStorage.removeItem('savedUsername_thidua');
                    localStorage.removeItem('savedPassword_thidua');
                }

                renderAlert(loginAlertContainer);
                toggleButtonLoading(loginSubmitBtn, true);

                const formData = new FormData(loginForm);
                if (thiduaRecaptchaSiteKey) {
                    formData.set('g-recaptcha-response', thiduaGetLoginRecaptcha());
                }

                try {
                    const response = await fetch(loginForm.action, {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin',
                        headers: { 'Accept': 'application/json' }
                    });

                    if (response.redirected) {
                        window.location.href = response.url;
                        return;
                    }

                    const rawBody = await response.text();
                    let data = null;
                    try {
                        data = rawBody ? JSON.parse(rawBody) : null;
                    } catch (parseError) {
                        console.error('Không thể parse JSON từ phản hồi đăng nhập:', rawBody);
                        throw new Error('Máy chủ trả về dữ liệu không hợp lệ.');
                    }

                    if (!response.ok || !data || !data.success) {
                        const message = data?.message || 'Tên đăng nhập hoặc mật khẩu không đúng!';
                        renderAlert(loginAlertContainer, message, 'danger');
                        thiduaResetLoginRecaptcha();
                        return;
                    }

                    if (data.requires_2fa) {
                        getLoginModalInstance()?.hide();
                        openTwoFaModal();
                        return;
                    }

                    const redirectUrl = data.redirect_url || '/thidua';
                    window.location.href = redirectUrl;
                } catch (error) {
                    console.error('Lỗi đăng nhập:', error);
                    renderAlert(loginAlertContainer, error.message || 'Không thể kết nối tới máy chủ. Vui lòng thử lại.', 'danger');
                    thiduaResetLoginRecaptcha();
                } finally {
                    toggleButtonLoading(loginSubmitBtn, false);
                }
            });

            // Thử lấy vị trí GPS sớm để sẵn sàng khi submit
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const latInput = document.getElementById('gps_lat');
                        const lonInput = document.getElementById('gps_lon');
                        if (latInput) latInput.value = position.coords.latitude;
                        if (lonInput) lonInput.value = position.coords.longitude;
                    },
                    (error) => {
                        console.log('GPS Error:', error.message);
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
                );
            }

            loginModal?.addEventListener('show.bs.modal', function() {

                const savedUsername = localStorage.getItem('savedUsername_thidua');
                const savedPassword = localStorage.getItem('savedPassword_thidua');
                if (savedUsername && savedPassword) {
                    usernameInput.value = savedUsername;
                    passwordInput.value = savedPassword;
                    if (rememberMeCheckbox) rememberMeCheckbox.checked = true;
                }
            });
            <?php if ($is_birthday) : ?>
                <?php if (!empty($student_info)) : ?>
                    thiduaOpenModal(document.getElementById('birthdayModal'));
                <?php elseif (!empty($teacher_info)) : ?>
                    thiduaOpenModal(document.getElementById('teacherBirthdayModal'));
                <?php endif; ?>
            <?php endif; ?>

        });
    </script>
    <script>
        (function() {

            const isEditable = (el) => el.isContentEditable || el.matches('input,textarea,select');
            window.addEventListener('contextmenu', (e) => {
                if (!isEditable(e.target)) e.preventDefault();
            }, {
                capture: true
            });
            window.addEventListener('dragstart', (e) => e.preventDefault(), {
                capture: true
            });
            window.addEventListener('drop', (e) => e.preventDefault(), {
                capture: true
            });
            ['copy', 'cut', 'paste'].forEach(evt => {
                document.addEventListener(evt, (e) => {
                    if (!isEditable(e.target)) e.preventDefault();
                }, {
                    capture: true
                });
            });
            document.addEventListener('keydown', function(e) {
                const k = e.key || '';
                const ctrl = e.ctrlKey || e.metaKey;
                const shift = e.shiftKey;
                if (k === 'F12' || (e.keyCode && e.keyCode === 123)) return eat(e);
                if (ctrl && ['U', 'S', 'P'].includes(k.toUpperCase())) return eat(e);
                if (ctrl && shift && ['I', 'J', 'C'].includes(k.toUpperCase())) return eat(e);

                function eat(evt) {
                    evt.preventDefault();
                    evt.stopPropagation();
                    return false;
                }
            }, {
                capture: true
            });
 
        })();

    </script>

<?php if (empty($is_search_performed) && !empty($public_notification)) : ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        thiduaOpenModal(document.getElementById('publicNotificationModal'));
    });
</script>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('trigger_2fa')) {
            const twoFaModalEl = document.getElementById('twoFaModal');
            if (twoFaModalEl) {
                thiduaOpenModal(twoFaModalEl);
                // Xóa param trên URL cho sạch sẽ
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        }
    });
</script>

<script>
document.querySelectorAll('.content-share-tiktok').forEach(function (button) {
  button.addEventListener('click', async function () {
    var shareUrl = button.getAttribute('data-share-url') || window.location.href;
    var tiktokUrl = button.getAttribute('data-tiktok-url') || 'https://www.tiktok.com/';
    try {
      if (navigator.clipboard && navigator.clipboard.writeText) {
        await navigator.clipboard.writeText(shareUrl);
      }
    } catch (error) {}
    window.open(tiktokUrl, '_blank', 'noopener');
  });
});
document.querySelectorAll('.content-share-copy').forEach(function (button) {
  button.addEventListener('click', async function () {
    var shareUrl = button.getAttribute('data-share-url') || window.location.href;
    try {
      if (navigator.clipboard && navigator.clipboard.writeText) {
        await navigator.clipboard.writeText(shareUrl);
      }
    } catch (error) {}
  });
});
</script>

<?php require __DIR__ . '/../../../app/views/partials/footer.php'; ?>
