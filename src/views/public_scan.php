<?php
// File: src/views/public_scan.php

require_once __DIR__ . '/partials/school_chrome.php';

$tracuuCanonicalUrl = SeoService::getBaseUrl() . '/thidua/public-scan?token=' . urlencode($token);
$pageTitle = 'Quét Điểm Danh - ' . ($hoat_dong['ten_hoat_dong'] ?? 'THPT Bình Sơn');
$pageDescription = 'Hệ thống quét mã điểm danh công khai hoạt động phong trào - Trường THPT Bình Sơn.';
$pageKeywords = 'quét điểm danh, hoạt động phong trào, THPT Bình Sơn, tra cứu thi đua';
$pageCanonicalUrl = $tracuuCanonicalUrl;
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

<script src="/thidua/public/assets/libs/jquery-3.7.0.min.js"></script>
<script src="/thidua/public/assets/libs/sweetalert2.min.js"></script>
<script src="/thidua/public/assets/libs/html5-qrcode.min.js"></script>

<body class="bg-gray-50 text-slate-800 antialiased">
<style>
    .thidua-portal-head { position: relative; border-bottom: 1px solid #f1f5f9; background: linear-gradient(180deg, rgba(239,246,255,.55), #fff); padding: 0.75rem 1rem 1rem; }
    @media (min-width: 640px) { .thidua-portal-head { padding: 1rem 1.5rem 1.25rem; } }
    .thidua-portal-brand { display: flex; flex-direction: column; align-items: center; text-align: center; gap: 0.75rem; }
    .thidua-portal-logo { display: block; height: 7.6rem; width: auto; max-width: min(100%, 13.6rem); object-fit: contain; object-position: center bottom; margin: 0; line-height: 0; }
    @media (min-width: 640px) { .thidua-portal-logo { height: 8.8rem; max-width: 15.2rem; } }
    .thidua-portal-title { margin: 0; font-size: 1rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: #1e3a8a; line-height: 1.3; }
    @media (min-width: 640px) { .thidua-portal-title { font-size: 1.125rem; } }
    @media (min-width: 768px) { .thidua-portal-title { font-size: 1.25rem; } }
    .thidua-portal-year { margin: 0; font-size: 0.875rem; font-weight: 700; color: #2563eb; line-height: 1.3; }
    
    .thidua-lookup-form { display: flex; flex-direction: column; align-items: center; gap: 0.875rem; width: 100%; max-width: 22rem; margin-inline: auto; }
    .thidua-lookup-field { width: 100%; }
    .thidua-lookup-label { display: block; margin-bottom: 0.375rem; font-size: 0.9375rem; font-weight: 700; color: #334155; text-align: center; }
    .thidua-lookup-input { display: block; width: 100%; height: 2.75rem; border-radius: 0.5rem; border: 1px solid #e2e8f0; background: #fff; padding: 0 0.875rem; font-size: 1rem; font-weight: 600; color: #0f172a; outline: none; text-align: center; }
    .thidua-lookup-input:focus { border-color: #60a5fa; box-shadow: 0 0 0 3px rgba(59,130,246,.15); }
    .thidua-lookup-input::placeholder { font-weight: 500; color: #94a3b8; }
    
    .thidua-btn-search { display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem; height: 2.75rem; padding: 0 1.5rem; font-size: 0.875rem; font-weight: 800; border: none; border-radius: 0.5rem; background: #f97316; color: #fff; cursor: pointer; transition: background .2s ease, transform .2s ease; }
    .thidua-btn-search:hover { background: #ea580c; transform: translateX(2px); }
    .thidua-btn-search:disabled { opacity: .65; cursor: not-allowed; transform: none; }
    
    .thidua-login-link { position: absolute; right: 1rem; top: 1rem; display: inline-flex; align-items: center; gap: 0.35rem; border: 0; background: transparent; padding: 0.25rem 0; font-size: 0.875rem; font-weight: 800; color: #1e40af; cursor: pointer; transition: color .2s ease, transform .2s ease; text-decoration: none; }
    .thidua-login-link:hover { color: #f97316; transform: translateX(4px); }
    @media (min-width: 640px) { .thidua-login-link { right: 1.5rem; top: 1.5rem; font-size: 0.9375rem; } }

    /* Custom Scanner Styles */
    #reader { width: 100% !important; border: none !important; }
    #reader video { object-fit: cover !important; border-radius: 0.75rem !important; width: 100% !important; max-height: 380px !important; }
    #reader__scan_region { background: #0f172a !important; border-radius: 0.75rem !important; overflow: hidden !important; }
    #reader__dashboard_section_csr { padding: 0.75rem 0 !important; text-align: center !important; }
    #reader__dashboard_section_csr button {
        background-color: #1e3a8a !important;
        color: #fff !important;
        border: none !important;
        padding: 0.45rem 1rem !important;
        border-radius: 0.5rem !important;
        font-weight: 700 !important;
        font-size: 0.8125rem !important;
        margin: 0.25rem !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
    }
    #reader__dashboard_section_csr button:hover {
        background-color: #f97316 !important;
    }
    #reader__dashboard_section_csr select {
        border: 1px solid #cbd5e1 !important;
        border-radius: 0.5rem !important;
        padding: 0.35rem 0.75rem !important;
        font-size: 0.8125rem !important;
        margin: 0.25rem !important;
        outline: none !important;
        font-weight: 600 !important;
    }
    #reader__header_message { display: none !important; } /* Hide raw html5-qrcode error banner */
    #reader a, #reader img[alt="Info icon"], #reader img[src*="info"], #reader a[href*="scanApp"], #reader a[href*="github"] { display: none !important; }

    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-8px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-down { animation: fadeInDown 0.25s ease-out forwards; }
</style>

<?php require __DIR__ . '/../../../app/views/partials/header.php'; ?>

<?php
$contentSectionTitle = '';
$contentSectionDescription = '';
$breadcrumbItems = [
    ['label' => 'Đánh giá thi đua', 'href' => '/thidua/tracuu'],
    ['label' => 'Quét điểm danh', 'href' => ''],
];
require __DIR__ . '/../../../app/views/partials/content_layout_open.php';
?>

  <article class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-md">
    <!-- Portal Header identical to Tracuu -->
    <div class="thidua-portal-head">
      <a href="/thidua/tracuu" class="thidua-login-link" title="Về trang tra cứu">
        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/></svg>
        <span>Tra cứu</span>
      </a>
      <div class="thidua-portal-brand mx-auto max-w-2xl">
        <img src="<?= htmlspecialchars($logo_path) ?>" alt="<?= htmlspecialchars($school_name) ?>" class="thidua-portal-logo" width="304" height="304">
        <h1 class="thidua-portal-title"><?= htmlspecialchars($portalTitle) ?></h1>
        <p class="thidua-portal-year"><?= htmlspecialchars($hoat_dong['ten_hoat_dong'] ?? 'HỆ THỐNG QUÉT MÃ CÔNG KHAI') ?></p>
      </div>
    </div>

    <div class="p-6 sm:p-5">
      <!-- Card thông tin hoạt động -->
      <div class="mb-6 rounded-xl bg-slate-50 border border-slate-200/80 p-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-center">
          <div class="p-2.5 bg-white rounded-lg border border-slate-200/80 shadow-2xs">
            <span class="text-xs text-slate-400 block font-semibold uppercase">Điểm tích lũy</span>
            <span class="font-extrabold text-base text-emerald-600">+<?= (int)($hoat_dong['diem_tich_luy'] ?? 0) ?>đ</span>
          </div>
          <div class="p-2.5 bg-white rounded-lg border border-slate-200/80 shadow-2xs">
            <span class="text-xs text-slate-400 block font-semibold uppercase">Đối tượng</span>
            <span class="font-bold text-slate-700 text-sm truncate block mt-0.5"><?= htmlspecialchars($hoat_dong['doi_tuong'] ?? 'Tất cả') ?></span>
          </div>
          <div class="p-2.5 bg-white rounded-lg border border-slate-200/80 shadow-2xs">
            <span class="text-xs text-slate-400 block font-semibold uppercase">Đã đăng ký</span>
            <span class="font-extrabold text-base text-blue-700">
              <?php
              $stmtCount = $db->prepare("SELECT COUNT(*) FROM hoat_dong_dang_ky WHERE hoat_dong_id = ?");
              $stmtCount->execute([$hoat_dong['id']]);
              echo (int)$stmtCount->fetchColumn();
              ?>
              <?= !empty($hoat_dong['so_luong_dang_ky']) ? ' / ' . $hoat_dong['so_luong_dang_ky'] : '' ?>
            </span>
          </div>
        </div>
      </div>

      <!-- Toast thông báo kết quả quét -->
      <div id="toastContainer" class="max-w-xl mx-auto mb-4"></div>

      <!-- Khu vực Quét QR & Nhập CCCD -->
      <div class="max-w-xl mx-auto">
        <!-- Tab chuyển chế độ -->
        <div class="flex items-center justify-center gap-1.5 mb-4 bg-slate-100 p-1 rounded-xl border border-slate-200/80">
          <button type="button" id="tabCameraBtn" onclick="switchScanTab('camera')" class="flex-1 py-2.5 px-3 text-xs sm:text-sm font-bold rounded-lg transition-all shadow-2xs bg-white text-primary-800">
            Quét bằng Camera
          </button>
          <button type="button" id="tabFileBtn" onclick="switchScanTab('file')" class="flex-1 py-2.5 px-3 text-xs sm:text-sm font-semibold rounded-lg transition-all text-slate-600 hover:text-slate-900">
            Tải ảnh mã QR
          </button>
          <button type="button" id="tabManualBtn" onclick="switchScanTab('manual')" class="flex-1 py-2.5 px-3 text-xs sm:text-sm font-semibold rounded-lg transition-all text-slate-600 hover:text-slate-900">
            Nhập CCCD
          </button>
        </div>

        <!-- Panel 1: Camera Scanner -->
        <div id="panelCamera" class="scan-panel">
          <div class="rounded-xl border border-slate-200 overflow-hidden bg-slate-900 shadow-inner relative">
            <div id="reader" class="w-full"></div>
          </div>
          <p class="text-center text-xs text-slate-400 mt-2">Đưa mã QR trên thẻ học sinh hoặc CCCD vào trước camera</p>
        </div>

        <!-- Panel 2: File Upload Scanner -->
        <div id="panelFile" class="scan-panel hidden">
          <div id="dropZone" onclick="document.getElementById('fileQrInput').click()" class="border-2 border-dashed border-slate-300 hover:border-primary-600 rounded-xl p-8 text-center cursor-pointer transition-colors bg-slate-50 hover:bg-primary-50/20 flex flex-col items-center justify-center">
            <svg class="w-12 h-12 text-slate-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <span class="font-bold text-slate-700 text-sm mb-1" id="fileSelectedLabel">Chọn ảnh mã QR từ thiết bị</span>
            <span class="text-xs text-slate-400">hoặc kéo thả file ảnh (PNG, JPG, JPEG) vào đây</span>
            <input type="file" id="fileQrInput" accept="image/*" class="hidden">
          </div>
          <div id="fileScanStatus" class="mt-2 text-center text-xs text-slate-500 hidden"></div>
          <div id="reader-file-dummy" class="hidden"></div>
        </div>

        <!-- Panel 3: Manual CCCD Form -->
        <div id="panelManual" class="scan-panel hidden">
          <div class="thidua-lookup-form my-4">
            <div class="thidua-lookup-field">
              <label class="thidua-lookup-label" for="manual_cccd_input">Mã định danh / CCCD học sinh</label>
              <input type="text" class="thidua-lookup-input" id="manual_cccd_input" placeholder="Nhập 12 số CCCD / Mã HS" autocomplete="off">
            </div>
            <button class="thidua-btn-search w-full" type="button" onclick="submitManualScan()">
              <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check2-circle" viewBox="0 0 16 16"><path d="M2.5 8a5.5 5.5 0 0 1 8.25-4.764.5.5 0 0 0 .5-.866A6.5 6.5 0 1 0 14.5 8a.5.5 0 0 0-1 0 5.5 5.5 0 1 1-11 0"/><path d="M15.354 3.354a.5.5 0 0 0-.708-.708L8 9.293 5.354 6.646a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0z"/></svg>
              Gửi điểm danh
            </button>
          </div>
        </div>

        <!-- Nhập CCCD nhanh phía dưới Camera -->
        <div id="quickManualSection" class="mt-5 pt-4 border-t border-slate-100">
          <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Hoặc nhập mã CCCD thủ công</label>
          <div class="flex gap-2">
            <input type="text" id="qr_input" class="flex-1 h-11 px-4 border border-slate-200 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-100 outline-none text-sm font-semibold text-slate-800" placeholder="Nhập mã CCCD / Mã học sinh...">
            <button type="button" onclick="submitQrScan()" class="thidua-btn-search h-11 px-6 rounded-lg font-bold text-sm">
              GỬI
            </button>
          </div>
        </div>

        <!-- Lịch sử điểm danh gần đây -->
        <div class="mt-6 pt-4 border-t border-slate-100">
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
              <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              Vừa điểm danh (<span id="recentCount">0</span>)
            </span>
            <button type="button" onclick="$('#recentList').empty(); $('#recentCount').text('0');" class="text-[11px] text-slate-400 hover:text-red-500 transition-colors">Xóa lịch sử</button>
          </div>
          <div id="recentList" class="space-y-2 max-h-48 overflow-y-auto pr-1">
            <div id="emptyRecentText" class="text-center py-4 text-xs text-slate-400 italic">Chưa có lượt điểm danh nào trong phiên làm việc này.</div>
          </div>
        </div>
      </div>

      <!-- Footer chia sẻ & thống kê đồng bộ Tracuu -->
      <div class="mt-8 pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-4 text-xs text-slate-500">
        <div class="flex items-center gap-3">
          <span>Chia sẻ:</span>
          <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($tracuuCanonicalUrl) ?>" target="_blank" rel="noopener noreferrer" class="text-slate-400 hover:text-blue-600 transition-colors" title="Chia sẻ lên Facebook">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
          </a>
          <button type="button" onclick="copyScanLink()" class="text-slate-400 hover:text-slate-700 transition-colors" title="Sao chép liên kết">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
          </button>
        </div>
        <div class="flex items-center gap-1.5 text-slate-400">
          <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
          <span>Hệ thống điểm danh trực tuyến</span>
        </div>
      </div>
    </div>
  </article>

<?php
require __DIR__ . '/../../../app/views/partials/content_layout_close.php';
?>

<!-- Logic JS Quét mã và xử lý giao diện -->
<script>
    const TOKEN = '<?= htmlspecialchars($token) ?>';
    const API_URL = '/thidua/api/public-scan';
    
    let html5QrcodeScanner = null;
    let isProcessingScan = false;
    let audioCtx = null;
    let recentScanCounter = 0;

    // Chuyển Tab quét
    function switchScanTab(tab) {
        $('.scan-panel').addClass('hidden');
        $('#tabCameraBtn, #tabFileBtn, #tabManualBtn')
            .removeClass('bg-white text-primary-800 shadow-2xs font-bold')
            .addClass('text-slate-600 font-semibold');

        if (tab === 'camera') {
            $('#panelCamera').removeClass('hidden');
            $('#tabCameraBtn').addClass('bg-white text-primary-800 shadow-2xs font-bold').removeClass('text-slate-600 font-semibold');
            $('#quickManualSection').removeClass('hidden');
            if (html5QrcodeScanner) {
                try { html5QrcodeScanner.resume(); } catch(e) {}
            }
        } else if (tab === 'file') {
            $('#panelFile').removeClass('hidden');
            $('#tabFileBtn').addClass('bg-white text-primary-800 shadow-2xs font-bold').removeClass('text-slate-600 font-semibold');
            $('#quickManualSection').addClass('hidden');
            if (html5QrcodeScanner) {
                try { html5QrcodeScanner.pause(true); } catch(e) {}
            }
        } else if (tab === 'manual') {
            $('#panelManual').removeClass('hidden');
            $('#tabManualBtn').addClass('bg-white text-primary-800 shadow-2xs font-bold').removeClass('text-slate-600 font-semibold');
            $('#quickManualSection').addClass('hidden');
            $('#manual_cccd_input').focus();
            if (html5QrcodeScanner) {
                try { html5QrcodeScanner.pause(true); } catch(e) {}
            }
        }
    }

    // Âm thanh thông báo
    function playBeep(type) {
        try {
            if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            if (audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
            if (type === 'success') {
                const osc = audioCtx.createOscillator();
                const gainNode = audioCtx.createGain();
                osc.connect(gainNode);
                gainNode.connect(audioCtx.destination);
                osc.type = 'sine';
                osc.frequency.setValueAtTime(880, audioCtx.currentTime);
                gainNode.gain.setValueAtTime(0, audioCtx.currentTime);
                gainNode.gain.linearRampToValueAtTime(0.4, audioCtx.currentTime + 0.02);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.18);
                osc.start(audioCtx.currentTime);
                osc.stop(audioCtx.currentTime + 0.18);
            } else if (type === 'error') {
                const playTone = (time) => {
                    const osc = audioCtx.createOscillator();
                    const gainNode = audioCtx.createGain();
                    osc.connect(gainNode);
                    gainNode.connect(audioCtx.destination);
                    osc.type = 'square';
                    osc.frequency.setValueAtTime(240, time);
                    gainNode.gain.setValueAtTime(0, time);
                    gainNode.gain.linearRampToValueAtTime(0.25, time + 0.02);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, time + 0.14);
                    osc.start(time);
                    osc.stop(time + 0.14);
                };
                playTone(audioCtx.currentTime);
                playTone(audioCtx.currentTime + 0.18);
            }
        } catch (e) {
            console.warn("Audio error:", e);
        }
    }

    // Hiển thị thông báo Toast
    function showMessage(type, title, msg) {
        const isError = type === 'error';
        const isWarning = type === 'warning';
        let bgColor = 'bg-emerald-50 border-emerald-200 text-emerald-800';
        let iconSvg = `<svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>`;

        if (isError) {
            bgColor = 'bg-red-50 border-red-200 text-red-800';
            iconSvg = `<svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="currentColor" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/></svg>`;
        } else if (isWarning) {
            bgColor = 'bg-amber-50 border-amber-200 text-amber-800';
            iconSvg = `<svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="currentColor" viewBox="0 0 16 16"><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/></svg>`;
        }
        
        const html = `
            <div class="${bgColor} border rounded-xl p-4 flex items-start gap-3 shadow-sm animate-fade-in-down">
                <div class="mt-0.5">${iconSvg}</div>
                <div class="flex-1 min-w-0">
                    <h4 class="font-bold text-sm leading-tight">${title}</h4>
                    <p class="text-xs opacity-90 mt-1 leading-normal break-words">${msg}</p>
                </div>
            </div>
        `;
        
        $('#toastContainer').html(html);
        
        setTimeout(() => {
            $('#toastContainer').empty();
        }, 4500);
    }

    // Thêm vào danh sách vừa điểm danh
    function addRecentScan(message, isSuccess) {
        $('#emptyRecentText').remove();
        recentScanCounter++;
        $('#recentCount').text(recentScanCounter);

        const timeStr = new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        const badgeColor = isSuccess ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-amber-100 text-amber-700 border-amber-200';
        
        const itemHtml = `
            <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-50 border border-slate-200/70 text-xs">
                <div class="flex items-center gap-2 truncate flex-1 mr-2">
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold border ${badgeColor}">
                        ${isSuccess ? 'Thành công' : 'Đã điểm danh'}
                    </span>
                    <span class="font-medium text-slate-700 truncate">${message}</span>
                </div>
                <span class="text-[11px] text-slate-400 whitespace-nowrap">${timeStr}</span>
            </div>
        `;
        $('#recentList').prepend(itemHtml);
    }

    // Xử lý quét mã QR / CCCD
    function onScanSuccess(decodedText) {
        if (isProcessingScan) return;
        isProcessingScan = true;
        
        if (html5QrcodeScanner) {
            try { html5QrcodeScanner.pause(true); } catch(e) {}
        }
        
        $('#qr_input').val(decodedText);
        
        $.ajax({
            url: API_URL, 
            type: 'POST', 
            contentType: 'application/json',
            data: JSON.stringify({ action: 'scan_qr', token: TOKEN, cccd: decodedText }),
            success: function(res) {
                if (res.success) {
                    playBeep('success');
                    showMessage('success', 'Điểm danh thành công!', res.message);
                    addRecentScan(res.message, true);
                    
                    setTimeout(() => {
                        isProcessingScan = false;
                        $('#qr_input').val('');
                        $('#manual_cccd_input').val('');
                        if (html5QrcodeScanner) {
                            try { html5QrcodeScanner.resume(); } catch(e) {}
                        }
                    }, 1500);
                } 
                else if (res.error_type === 'not_registered') {
                    playBeep('error');
                    Swal.fire({
                        title: 'Học sinh chưa đăng ký!',
                        text: res.message,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Đồng ý thêm vào',
                        cancelButtonText: 'Bỏ qua',
                        confirmButtonColor: '#1e3a8a',
                        cancelButtonColor: '#94a3b8',
                        customClass: {
                            popup: 'rounded-2xl shadow-2xl border border-slate-100',
                            title: 'text-lg font-bold text-slate-800',
                            confirmButton: 'rounded-xl px-5 py-2.5 font-bold',
                            cancelButton: 'rounded-xl px-4 py-2.5 font-semibold'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: API_URL, 
                                type: 'POST', 
                                contentType: 'application/json',
                                data: JSON.stringify({ action: 'add_and_scan_qr', token: TOKEN, cccd: decodedText }),
                                success: function(addRes) {
                                    if (addRes.success) {
                                        playBeep('success');
                                        showMessage('success', 'Thành công!', addRes.message);
                                        addRecentScan(addRes.message, true);
                                    } else {
                                        playBeep('error');
                                        showMessage('error', 'Lỗi thêm học sinh', addRes.message);
                                    }
                                },
                                complete: function() {
                                    setTimeout(() => {
                                        isProcessingScan = false;
                                        $('#qr_input').val('');
                                        $('#manual_cccd_input').val('');
                                        if (html5QrcodeScanner) {
                                            try { html5QrcodeScanner.resume(); } catch(e) {}
                                        }
                                    }, 1000);
                                }
                            });
                        } else {
                            isProcessingScan = false;
                            $('#qr_input').val('');
                            $('#manual_cccd_input').val('');
                            if (html5QrcodeScanner) {
                                try { html5QrcodeScanner.resume(); } catch(e) {}
                            }
                        }
                    });
                }
                else {
                    playBeep('error');
                    showMessage('warning', 'Thông báo', res.message);
                    if (res.error_type === 'already_scanned') {
                        addRecentScan(res.message, false);
                    }
                    setTimeout(() => {
                        isProcessingScan = false;
                        $('#qr_input').val('');
                        $('#manual_cccd_input').val('');
                        if (html5QrcodeScanner) {
                            try { html5QrcodeScanner.resume(); } catch(e) {}
                        }
                    }, 2000);
                }
            },
            error: function() {
                playBeep('error');
                showMessage('error', 'Lỗi kết nối', 'Không thể kết nối đến máy chủ. Vui lòng thử lại sau.');
                setTimeout(() => {
                    isProcessingScan = false;
                    if (html5QrcodeScanner) {
                        try { html5QrcodeScanner.resume(); } catch(e) {}
                    }
                }, 2000);
            }
        });
    }

    function submitQrScan() {
        const val = $('#qr_input').val().trim();
        if (val) {
            onScanSuccess(val);
        }
    }

    function submitManualScan() {
        const val = $('#manual_cccd_input').val().trim();
        if (!val) {
            showMessage('warning', 'Vui lòng nhập CCCD', 'Bạn chưa nhập mã số định danh hoặc CCCD.');
            $('#manual_cccd_input').focus();
            return;
        }
        onScanSuccess(val);
    }

    function copyScanLink() {
        navigator.clipboard.writeText(window.location.href).then(() => {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Đã sao chép liên kết quét mã!',
                showConfirmButton: false,
                timer: 2000
            });
        });
    }

    // Khởi tạo html5-qrcode
    $(document).ready(function() {
        document.body.addEventListener('click', function() {
            if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        }, { once: true });

        // Khởi tạo camera scanner
        try {
            html5QrcodeScanner = new Html5QrcodeScanner(
                "reader",
                { 
                    fps: 10, 
                    qrbox: function(viewfinderWidth, viewfinderHeight) {
                        var minEdgePercentage = 0.7;
                        var minEdgeSize = Math.min(viewfinderWidth, viewfinderHeight);
                        var qrboxSize = Math.floor(minEdgeSize * minEdgePercentage);
                        return {
                            width: Math.min(qrboxSize, 280),
                            height: Math.min(qrboxSize, 280)
                        };
                    },
                    aspectRatio: 1.0,
                    showTorchButtonIfSupported: true,
                    rememberLastUsedCamera: true
                },
                false
            );
            html5QrcodeScanner.render(
                function(decodedText, decodedResult) {
                    onScanSuccess(decodedText);
                },
                function(errorMessage) {
                    // Do not print anything on regular frame failure
                }
            );
        } catch (e) {
            console.error("Camera init error:", e);
        }

        // Xử lý quét bằng File ảnh
        const fileInput = document.getElementById('fileQrInput');
        fileInput.addEventListener('change', function(e) {
            if (!e.target.files || e.target.files.length === 0) return;
            const file = e.target.files[0];
            $('#fileSelectedLabel').text(file.name);
            $('#fileScanStatus').removeClass('hidden text-red-500 text-emerald-600').addClass('text-slate-500').text('Đang nhận diện mã QR trong ảnh...');

            const html5QrCode = new Html5Qrcode("reader-file-dummy");
            html5QrCode.scanFile(file, true)
                .then(decodedText => {
                    $('#fileScanStatus').addClass('text-emerald-600').text('Đã nhận diện thành công: ' + decodedText);
                    onScanSuccess(decodedText);
                })
                .catch(err => {
                    playBeep('error');
                    let safeErrMsg = 'Không nhận diện được mã QR hợp lệ trong ảnh.';
                    if (typeof err === 'string') safeErrMsg = err;
                    else if (err && err.message) safeErrMsg = err.message;
                    $('#fileScanStatus').addClass('text-red-500').text(safeErrMsg);
                    showMessage('error', 'Không đọc được mã', safeErrMsg + ' Vui lòng chọn ảnh rõ nét hơn hoặc nhập CCCD.');
                })
                .finally(() => {
                    html5QrCode.clear();
                    fileInput.value = '';
                });
        });

        // Kéo thả ảnh vào Dropzone
        const dropZone = document.getElementById('dropZone');
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.add('border-primary-600', 'bg-primary-50/30');
            }, false);
        });
        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.remove('border-primary-600', 'bg-primary-50/30');
            }, false);
        });
        dropZone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files && files.length > 0) {
                fileInput.files = files;
                const event = new Event('change');
                fileInput.dispatchEvent(event);
            }
        });

        // Xử lý nhấn Enter
        $('#qr_input').on('keypress', function(e) {
            if (e.which === 13) submitQrScan();
        });
        $('#manual_cccd_input').on('keypress', function(e) {
            if (e.which === 13) submitManualScan();
        });
    });
</script>

<?php require __DIR__ . '/../../../app/views/partials/footer.php'; ?>
