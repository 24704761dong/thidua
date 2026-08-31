<?php
// File: src/views/partials/admin_header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../lib/tracking.php';
require_once __DIR__ . '/../../lib/helpers.php';
update_activity_log();

// Lấy danh sách Năm Học
try {
    $db_nh = get_db_connection();
    $stmt_nh_list = $db_nh->query("SELECT * FROM nam_hoc ORDER BY ten_nam_hoc DESC");
    $nam_hoc_list = $stmt_nh_list->fetchAll();
    
    $nam_hoc_current = null;
    $nam_hoc_id_session = $_SESSION['nam_hoc_id'] ?? null;
    foreach ($nam_hoc_list as $nh) {
        if ($nam_hoc_id_session && $nh['id'] == $nam_hoc_id_session) {
            $nam_hoc_current = $nh;
        } elseif (!$nam_hoc_id_session && $nh['is_mac_dinh'] == 1) {
            $nam_hoc_current = $nh;
        }
    }
    if (!$nam_hoc_current && !empty($nam_hoc_list)) {
        $nam_hoc_current = $nam_hoc_list[0];
    }
} catch (Exception $e) {
    $nam_hoc_list = [];
    $nam_hoc_current = null;
}

try {
    $db_check = get_db_connection();
    $stmt_user_settings = $db_check->prepare("SELECT auto_logout_enabled, hinh_nen_desktop, vi_tri_icons FROM users WHERE id = ?");
    $stmt_user_settings->execute([$_SESSION['user_id'] ?? 0]);
    $user_settings = $stmt_user_settings->fetch();
    $user_logout_enabled = $user_settings ? $user_settings['auto_logout_enabled'] : 0;
    $desktop_bg = ($user_settings && !empty($user_settings['hinh_nen_desktop'])) ? $user_settings['hinh_nen_desktop'] : '/thidua/public/assets/img/desktop_bg.jpg';
    $vi_tri_icons = ($user_settings && !empty($user_settings['vi_tri_icons'])) ? $user_settings['vi_tri_icons'] : '{}';

    $stmt_check = $db_check->query("SELECT setting_value FROM he_thong_cai_dat WHERE setting_key = 'auto_logout_duration'");
    $auto_logout_duration = (int)($stmt_check->fetchColumn() ?: 1800);
    if ($auto_logout_duration < 300) {
        $auto_logout_duration = 1800; // Đảm bảo tối thiểu 30 phút, tránh bị = 0 gây văng ngay lập tức
    }

    if ($user_logout_enabled && isset($_SESSION['user_id'])) {
        if (!isset($_SESSION['last_activity'])) {
            $_SESSION['last_activity'] = time();
        } elseif (time() - $_SESSION['last_activity'] > $auto_logout_duration) {
            session_unset();
            session_destroy();
            
            // Xóa cookie remember_me để không bị auto-login lại ngay lập tức
            setcookie('remember_me', '', time() - 3600, '/');
            setcookie('student_remember_me', '', time() - 3600, '/');
            
            // Chuyển hướng toàn bộ trang (window.top) ra ngoài trang tra cứu thay vì kẹt trong iframe
            echo "<script>window.top.location.href = '/thidua/tracuu?reason=inactive';</script>";
            exit();
        }
        $_SESSION['last_activity'] = time();
    }
} catch(Exception $e) {
    error_log("Auto-logout check failed: " . $e->getMessage());
}

$permission_config = require __DIR__ . '/../../../config/permissions.php';
$user_role = $_SESSION['user_vai_tro'] ?? 'user';
$user_permissions = $_SESSION['user_permissions'] ?? [];

$current_username = $_SESSION['user_ten_dang_nhap'] ?? '';
if (empty($current_username) && isset($_SESSION['user_id'])) {
    try {
        require_once __DIR__ . '/../../../config/database.php';
        $db_u = get_db_connection();
        $stmt_u = $db_u->prepare("SELECT ten_dang_nhap FROM users WHERE id = ?");
        $stmt_u->execute([$_SESSION['user_id']]);
        $current_username = $stmt_u->fetchColumn();
        $_SESSION['user_ten_dang_nhap'] = $current_username;
    } catch (\Throwable $e) {}
}
$is_super_admin = (strtolower((string)$current_username) === 'admin');

$start_menu_items = [];
foreach ($permission_config as $group) {
    foreach ($group['permissions'] as $key => $perm) {
        if (isset($perm['route']) && $perm['route'] !== '#') {
            if ($key === 'quan_ly_tai_khoan_admin' && !$is_super_admin) {
                continue;
            }
            $start_menu_items[$key] = $perm;
        }
    }
}

$pageTitle = isset($page_title) ? $page_title . ' - Hệ thống Đánh Giá Thi Đua' : 'Admin - Hệ thống Đánh Giá Thi Đua';

// === LOGIC IFRAME SPA WINDOW MANAGER ===
$is_iframe = isset($_GET['iframe']) || (isset($_SERVER['HTTP_SEC_FETCH_DEST']) && $_SERVER['HTTP_SEC_FETCH_DEST'] === 'iframe');
$current_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$is_dashboard = (strpos($current_uri, '/admin/dashboard') !== false || $current_uri === '/thidua/admin/' || $current_uri === '/thidua/admin' || strpos($current_uri, 'index.php') !== false);

// Nếu truy cập thẳng trang con (không qua iframe), redirect về trang chủ và mở window
if (!$is_iframe && !$is_dashboard && strpos($current_uri, '/api/') === false) {
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
    $_SESSION['launch_app'] = $_SERVER['REQUEST_URI'];
    header("Location: /thidua/admin");
    exit();
}

?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="icon" type="image/x-icon" href="/thidua/public/assets/img/favicon.ico">
    <script>
    window.addEventListener('error', function(e) {
        if (e.message === 'Script error.' || !e.message) return;
        console.error('Lỗi JS:', e.message, 'tại dòng', e.lineno, e.filename);
        if (typeof showToast === 'function') {
            showToast('error', 'Lỗi JS: ' + e.message);
        }
    });
    </script>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <!-- FontAwesome & Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- SweetAlert2 -->
    <script src="/thidua/public/assets/libs/sweetalert2.min.js"></script>
    <script>
        window.AppSwal = Swal.mixin({
            width: '400px',
            customClass: {
                popup: 'bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.15)] border border-slate-200 py-6 px-6',
                title: 'text-[#224397] font-bold text-xl mt-0',
                htmlContainer: 'text-slate-600 font-medium text-[14.5px] mt-2 mb-2',
                actions: 'flex justify-center gap-3 w-full mt-6',
                confirmButton: 'bg-[#224397] text-white rounded-lg px-6 py-2 font-medium shadow-sm hover:bg-[#FAB723] hover:text-slate-900 hover:scale-110 hover:shadow-md transition-all duration-300 outline-none',
                cancelButton: 'bg-white text-slate-600 rounded-lg px-6 py-2 font-medium shadow-sm border border-slate-300 hover:bg-slate-50 transition-all duration-200 outline-none',
                icon: 'scale-[0.85] my-2'
            },
            buttonsStyling: false
        });
    </script>

    <!-- AUTO-LOGOUT INACTIVITY TRACKER -->
    <script>
    (function() {
        window.AUTO_LOGOUT_ENABLED = <?= $user_logout_enabled ? 'true' : 'false' ?>;
        window.AUTO_LOGOUT_DURATION = <?= (int)$auto_logout_duration ?>;

        if (window.self === window.top) {
            let lastActiveTime = Date.now();
            let logoutTimer = null;
            let isSessionExpired = false;

            function doAutoLogout() {
                if (!window.AUTO_LOGOUT_ENABLED || window.AUTO_LOGOUT_DURATION <= 0) return;
                if (isSessionExpired) return;
                isSessionExpired = true;

                if (logoutTimer) clearTimeout(logoutTimer);

                // Hủy session ở backend ngầm
                fetch('/thidua/dang-xuat?ajax=1').catch(function(){});

                // Hiển thị modal thông báo hết hạn phiên chuyên nghiệp
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Phiên làm việc đã hết hạn!',
                        text: 'Phiên làm việc của bạn đã hết hạn do không có hoạt động. Vui lòng đăng nhập lại.',
                        icon: 'warning',
                        confirmButtonText: 'Đăng Nhập Lại',
                        confirmButtonColor: '#224397',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        allowEnterKey: true,
                        customClass: {
                            popup: 'bg-white rounded-2xl shadow-2xl p-6 border border-slate-200 z-[999999]',
                            title: 'text-[#224397] font-bold text-xl',
                            htmlContainer: 'text-slate-600 font-medium text-[15px] mt-2 mb-4',
                            confirmButton: 'bg-[#224397] text-white px-6 py-2.5 rounded-lg font-semibold hover:bg-[#FAB723] hover:text-slate-900 transition-all cursor-pointer'
                        }
                    }).then(function() {
                        window.top.location.href = '/thidua/tracuu?show_login=1&reason=inactive';
                    });
                } else {
                    alert('Phiên làm việc đã hết hạn do không có hoạt động. Vui lòng đăng nhập lại.');
                    window.top.location.href = '/thidua/tracuu?show_login=1&reason=inactive';
                }
            }

            function resetTimer() {
                if (!window.AUTO_LOGOUT_ENABLED || window.AUTO_LOGOUT_DURATION <= 0) {
                    if (logoutTimer) clearTimeout(logoutTimer);
                    return;
                }
                if (isSessionExpired) return;
                lastActiveTime = Date.now();
                if (logoutTimer) clearTimeout(logoutTimer);
                logoutTimer = setTimeout(doAutoLogout, window.AUTO_LOGOUT_DURATION * 1000);
            }

            function cancelTimer() {
                if (logoutTimer) clearTimeout(logoutTimer);
            }

            window.resetInactivityTimer = resetTimer;
            window.cancelInactivityTimer = cancelTimer;

            const userEvents = ['mousedown', 'mousemove', 'keydown', 'scroll', 'touchstart', 'click'];
            userEvents.forEach(function(evt) {
                window.addEventListener(evt, resetTimer, { passive: true });
            });

            if (window.AUTO_LOGOUT_ENABLED && window.AUTO_LOGOUT_DURATION > 0) {
                resetTimer();
            }

            setInterval(function() {
                if (window.AUTO_LOGOUT_ENABLED && window.AUTO_LOGOUT_DURATION > 0 && !isSessionExpired) {
                    if (Date.now() - lastActiveTime >= window.AUTO_LOGOUT_DURATION * 1000) {
                        doAutoLogout();
                    }
                }
            }, 1000);
        } else {
            const userEvents = ['mousedown', 'mousemove', 'keydown', 'scroll', 'touchstart', 'click'];
            userEvents.forEach(function(evt) {
                window.addEventListener(evt, function() {
                    try {
                        if (window.top && typeof window.top.resetInactivityTimer === 'function') {
                            window.top.resetInactivityTimer();
                        }
                    } catch(e) {}
                }, { passive: true });
            });
        }
    })();
    </script>

    <!-- IFRAME CORRECTION LOGIC -->
    <script>
    (function() {
        if (window.top !== window.self && window.location.href.indexOf('/thidua/tracuu') !== -1) {
            window.top.location.href = window.location.href;
            return;
        }

        var isSelfIframe = (window.self !== window.top);
        var phpIsIframe = <?php echo $is_iframe ? 'true' : 'false'; ?>;
        
        if (isSelfIframe && !phpIsIframe) {
            var url = new URL(window.location.href);
            url.searchParams.set('iframe', '1');
            window.location.replace(url.toString());
        } else if (!isSelfIframe && phpIsIframe) {
            var url = new URL(window.location.href);
            url.searchParams.delete('iframe');
            window.location.replace(url.toString());
        }

        if (phpIsIframe) {
            document.addEventListener('click', function(e) {
                var target = e.target.closest('a');
                if (target && target.href && !target.getAttribute('href').startsWith('javascript:') && !target.getAttribute('href').startsWith('#') && !target.hasAttribute('target')) {
                    try {
                        var url = new URL(target.href);
                        if (url.origin === window.location.origin && !url.searchParams.has('iframe')) {
                            url.searchParams.set('iframe', '1');
                            target.href = url.toString();
                        }
                    } catch(err) {}
                }
            });
        }
    })();
    </script>

    <style>
    <?php if (!$is_iframe): ?>
        body { 
            background: #004d9c url('<?php echo htmlspecialchars($desktop_bg ?? '/thidua/public/assets/img/desktop_bg.jpg'); ?>') center/cover no-repeat fixed; 
            color: #1f2937;
            overflow: hidden;
            font-family: "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            margin: 0; padding: 0;
        }
        .os-desktop {
            position: absolute; inset: 0; display: flex; flex-direction: column; overflow: hidden;
        }
        .os-desktop-icons {
            position: absolute; top: 0; left: 0; bottom: 40px; right: 0; padding: 10px;
        }
        .desktop-icon-item {
            width: 80px; display: flex; flex-direction: column; align-items: center; padding: 6px;
            border-radius: 4px; border: 1px solid transparent; text-align: center; cursor: pointer; text-decoration: none;
            position: absolute; user-select: none;
        }
        .desktop-icon-item img {
            width: 48px; height: 48px; margin: 0 auto 4px auto; object-fit: contain;
        }
        .desktop-icon-item span {
            color: white; font-size: 11px; 
            line-height: 1.2; word-break: break-word; transition: color 0.3s;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.8);
        }
        body.light-bg .desktop-icon-item span {
            color: black; 
            font-weight: 600;
            text-shadow: 1px 1px 3px rgba(255,255,255,0.8);
        }
        .os-desktop {
            position: absolute; inset: 0; display: flex; flex-direction: column; overflow: hidden;
        }
        .os-desktop-icons {
            position: absolute; top: 0; left: 0; bottom: 40px; right: 0; padding: 10px;
        }
        .desktop-icon-item {
            width: 80px; display: flex; flex-direction: column; align-items: center; padding: 6px;
            border-radius: 4px; border: 1px solid transparent; text-align: center; cursor: pointer; text-decoration: none;
            position: absolute; user-select: none;
        }
        .desktop-icon-item img {
            width: 48px; height: 48px; margin: 0 auto 4px auto; object-fit: contain;
        }
        .desktop-icon-item span {
            color: white; font-size: 11px; 
            line-height: 1.2; word-break: break-word; transition: color 0.3s;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.8);
        }
        body.light-bg .desktop-icon-item span {
            color: black; 
            font-weight: 600;
            text-shadow: 1px 1px 3px rgba(255,255,255,0.8);
        }
        
        /* Cửa sổ Windows OS */
        .os-window {
            position: absolute;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
            display: flex; flex-direction: column;
            border: 1px solid #cbd5e1;
            overflow: hidden;
            transition: transform 0.2s, opacity 0.2s;
            min-width: 300px;
            min-height: 200px;
        }
        .os-window.minimized { display: none !important; }
        .os-window.maximized { top: 0 !important; left: 0 !important; right: 0 !important; bottom: 40px !important; border-radius: 0; width: auto !important; height: auto !important; }
        
        /* Desktop Widgets */
        .desktop-widget {
            position: absolute;
            background: rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 12px;
            cursor: move;
            user-select: none;
            transition: box-shadow 0.3s ease, transform 0.3s ease, opacity 0.2s, background-color 0.3s ease, border-color 0.3s ease;
            width: 250px;
        }
        .desktop-widget:hover {
            box-shadow: 0 10px 30px rgba(250,183,35,0.2);
            transform: translateY(-4px);
            background: rgba(250, 183, 35, 0.1);
            border-color: #FAB723;
        }
        .desktop-widget.hidden-widget {
            display: none !important;
        }
        .widget-title {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .widget-value {
            font-size: 20px;
            font-weight: 800;
            color: #1e293b;
        }
        .widget-icon {
            width: 32px; height: 32px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px;
        }
        
        @keyframes windowOpen {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .os-window-header {
            height: 32px; background: #ffffff; border-bottom: 1px solid #cbd5e1;
            display: flex; justify-content: space-between; align-items: center;
            cursor: move; user-select: none; border-top-left-radius: 8px; border-top-right-radius: 8px;
        }
        .os-window-title {
            padding-left: 12px; font-size: 12px; font-weight: 600; color: #334155; display: flex; align-items: center; gap: 8px; pointer-events: none;
        }
        .os-window-controls {
            display: flex; height: 100%;
        }
        .os-win-btn {
            width: 32px; height: 100%; display: flex; justify-content: center; align-items: center; border: none; background: transparent; color: #475569; font-size: 12px; cursor: pointer; transition: background 0.1s;
        }
        .os-win-btn:hover { background: #e2e8f0; }
        .os-win-btn.close:hover { background: #e81123; color: white; }
        
        /* Custom Resize Handles */
        .resizer { position: absolute; z-index: 50; }
        .resizer-r { right: 0; top: 0; width: 6px; height: 100%; cursor: e-resize; }
        .resizer-l { left: 0; top: 0; width: 6px; height: 100%; cursor: w-resize; }
        .resizer-b { bottom: 0; left: 0; width: 100%; height: 6px; cursor: s-resize; }
        .resizer-br { bottom: 0; right: 0; width: 16px; height: 16px; cursor: se-resize; z-index: 60; }
        .resizer-bl { bottom: 0; left: 0; width: 16px; height: 16px; cursor: sw-resize; z-index: 60; }
        
        .window-content {
            flex: 1; overflow: hidden; background: #f1f5f9; position: relative; display: flex; flex-direction: column;
        }
        
        .app-iframe { width: 100%; height: 100%; border: none; }
        .os-window.maximized { top: 0 !important; left: 0 !important; right: 0 !important; bottom: 40px !important; border-radius: 0; width: auto !important; height: auto !important; }
        .os-window.minimized { display: none !important; }

        /* Taskbar Windows */
        .taskbar {
            position: absolute; bottom: 0; left: 0; right: 0; height: 40px;
            background: rgba(255, 255, 255, 0.98); border-top: 1px solid rgba(0,0,0,0.1); box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
            display: flex; justify-content: space-between; align-items: center; padding: 0 12px; z-index: 9999;
        }
        .taskbar-left { display: flex; align-items: center; height: 100%; gap: 4px; }
        .taskbar-right { display: flex; align-items: center; height: 100%; gap: 12px; }
        .taskbar-btn {
            height: 36px; min-width: 36px; border-radius: 4px; border: none; background: transparent; color: #334155; display: flex; justify-content: center; align-items: center; cursor: pointer; padding: 0 8px; transition: background 0.1s; position: relative;
        }
        .taskbar-btn:hover { background: rgba(0,0,0,0.05); }
        .taskbar-btn.active { background: rgba(0,0,0,0.1); border-bottom: 2px solid #00a8e8; }
        .taskbar-btn img { width: 20px; height: 20px; object-fit: contain; }
        
        /* Start Menu */
        .start-menu {
            position: absolute; bottom: 40px; left: 0; width: 320px; background: rgba(255,255,255,0.95); backdrop-filter: blur(20px); border-radius: 0 4px 0 0; box-shadow: 0 10px 30px rgba(0,0,0,0.3); border: 1px solid rgba(0,0,0,0.1); border-bottom: none; border-left: none; display: flex; flex-direction: column; transform-origin: bottom left; transition: transform 0.2s, opacity 0.2s;
            opacity: 0; pointer-events: none; transform: translateY(10px); z-index: 10000;
        }
        .start-menu.show { opacity: 1; pointer-events: auto; transform: translateY(0); }
        .start-menu-footer { background: #f1f5f9; padding: 12px; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #e2e8f0; }

        .iframe-blocker { position: absolute; inset: 0; z-index: 10; display: none; }
        .is-dragging .iframe-blocker { display: block; }
    <?php else: ?>
        body, body > div.w-full.min-h-screen.bg-slate-50 { 
            background: linear-gradient(to bottom right, #f8fafc, #E4F6FD) !important; 
            color: #1f2937;
            font-family: "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            overflow-x: hidden;
            overflow-y: auto;
        }
        /* Ẩn thanh cuộn dc lớn bên phải */
        body::-webkit-scrollbar, html::-webkit-scrollbar { display: none; }
        body, html { -ms-overflow-style: none; scrollbar-width: none; }
    <?php endif; ?>
    </style>
    </head>
<body class="bg-gray-50/50">

<?php if (!$is_iframe): ?>
    <!-- Startup & Year Switch Loading Screen -->
    <div id="startupLoadingScreen" class="fixed inset-0 bg-[#E4F6FD] z-[999999] flex flex-col items-center justify-center transition-opacity duration-500 opacity-100">
        <div class="relative mb-6 mt-[-80px]">
            <!-- Glow effect -->
            <div class="absolute inset-0 bg-white rounded-full blur-3xl opacity-80 scale-[1.5]"></div>
            <img src="/thidua/public/assets/img/22logoapp.png" class="relative h-[80px] w-auto object-contain drop-shadow-xl z-10" alt="Logo" onerror="this.src='/thidua/public/assets/img/logoapp.png'" />
        </div>
        
        <div class="flex gap-2.5 mb-5 z-10">
            <div class="w-3 h-3 bg-[#A7B8E6] rounded-full animate-bounce shadow-sm shadow-[#A7B8E6]/50" style="animation-delay: 0s"></div>
            <div class="w-3 h-3 bg-[#8A9ED1] rounded-full animate-bounce shadow-sm shadow-[#8A9ED1]/50" style="animation-delay: 0.1s"></div>
            <div class="w-3 h-3 bg-[#627EC1] rounded-full animate-bounce shadow-sm shadow-[#627EC1]/50" style="animation-delay: 0.2s"></div>
            <div class="w-3 h-3 bg-[#3F5BB0] rounded-full animate-bounce shadow-sm shadow-[#3F5BB0]/50" style="animation-delay: 0.3s"></div>
            <div class="w-3 h-3 bg-[#224397] rounded-full animate-bounce shadow-sm shadow-[#224397]/50" style="animation-delay: 0.4s"></div>
        </div>
        
        <div class="z-10 text-center px-4">
            <h2 id="loadingScreenText" class="text-xl md:text-[22px] font-semibold text-[#224397] tracking-wide">
                <?= isset($_GET['switch']) ? 'Hệ thống đang khởi tạo sever...' : 'Đang khởi tạo dữ liệu năm học ' . htmlspecialchars($nam_hoc_current['ten_nam_hoc'] ?? 'Chưa đặt') . '...' ?>
            </h2>
        </div>
    </div>

    <script>
        // Hide loader on initial boot
        window.addEventListener('load', () => {
            setTimeout(() => {
                const loader = document.getElementById('startupLoadingScreen');
                if (loader && !loader.dataset.isSwitching) {
                    loader.classList.remove('opacity-100');
                    loader.classList.add('opacity-0');
                    setTimeout(() => loader.classList.add('hidden'), 500);
                }
            }, 1500); // 1.5s delay for boot
        });

        window.triggerYearSwitchLoading = function(newYearName) {
            const loader = document.getElementById('startupLoadingScreen');
            if (!loader) return window.location.href = '/thidua/admin?switch=1';
            
            loader.dataset.isSwitching = "true";
            let txt = 'Đang đổi server năm học';
            if (newYearName) txt += ' ' + newYearName;
            txt += '...';
            document.getElementById('loadingScreenText').innerText = txt;
            
            loader.classList.remove('hidden');
            setTimeout(() => {
                loader.classList.remove('opacity-0');
                loader.classList.add('opacity-100');
            }, 10);
            
            // Dramatic delay before redirecting
            setTimeout(() => {
                window.location.href = '/thidua/admin?switch=1';
            }, 1500);
        };
    </script>
<script>
    window.USER_ICON_POSITIONS = <?php echo $vi_tri_icons; ?>;

    window.updateIconColors = function(imageUrl) {
        if (!imageUrl) return;
        if(imageUrl.startsWith('url(')) {
            imageUrl = imageUrl.slice(4, -1).replace(/['"]/g, '');
        }
        
        try {
            const cache = JSON.parse(localStorage.getItem('desktop_bg_brightness') || '{}');
            if (cache.url === imageUrl) {
                if (cache.isLight) document.body.classList.add('light-bg');
                else document.body.classList.remove('light-bg');
                return; 
            }
        } catch(e) {}

        const img = new Image();
        img.crossOrigin = "Anonymous";
        img.onload = function() {
            const canvas = document.createElement('canvas');
            const size = 50; 
            canvas.width = size;
            canvas.height = size;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, size, size);
            
            try {
                const imageData = ctx.getImageData(0, 0, size, size);
                const data = imageData.data;
                let colorSum = 0;
                let pixelCount = 0;
                
                for (let i = 0; i < data.length; i += 4) {
                    const r = data[i]; const g = data[i + 1]; const b = data[i + 2];
                    const brightness = (r * 299 + g * 587 + b * 114) / 1000;
                    colorSum += brightness; pixelCount++;
                }
                
                const avgBrightness = colorSum / pixelCount;
                const isLight = avgBrightness > 140;
                if (isLight) document.body.classList.add('light-bg');
                else document.body.classList.remove('light-bg');
                
                try {
                    localStorage.setItem('desktop_bg_brightness', JSON.stringify({ url: imageUrl, isLight: isLight }));
                } catch(e) {}
                
            } catch (e) { console.warn('Lỗi CORS khi tính độ sáng ảnh:', e); }
        };
        img.src = imageUrl;
    };
    
    document.addEventListener('DOMContentLoaded', () => {
        window.updateIconColors('<?php echo htmlspecialchars($desktop_bg ?? '/thidua/public/assets/img/desktop_bg.jpg'); ?>');
    });
</script>

<div class="os-desktop" id="osDesktopBg">
    <!-- Electron Window Drag Area (for draggable desktop) -->
    <div id="electronDragBar" class="fixed top-0 left-0 right-[140px] h-[35px] z-[8] select-none" style="-webkit-app-region: drag;"></div>
    <!-- Desktop Icons -->
    <div class="os-desktop-icons">
        <?php 
        $icon_map = [
            'quan_ly_hoc_sinh' => 'quanlylophoc.svg', 'quan_ly_ky_thi' => 'test.svg', 'nhap_vi_pham' => 'nenep.svg',
            'nhap_diem_thi_dua' => 'sodiem.svg', 'cai_dat_he_thong' => 'caidat.svg', 'bao_cao_thong_ke' => 'thongkebaocao.svg',
            'lich_sinh_nhat' => 'sinhnhat.svg', 'trung_tam_duyet' => 'duyet.svg', 'quan_ly_khen_thuong' => 'quanlykhenthuong.svg',
            'nhat_ky_he_thong' => 'nhatky.svg', 'nhat_ky_email' => 'sms_sender.svg', 'quan_ly_the_hoc_sinh' => 'thehocsinh.svg',
            'duyet_so_nhat_ky' => 'sodaubai.svg', 'quan_ly_nam_hoc' => 'quanlynamhoc.svg',
            'xu_ly_tre_hoc' => 'trehoc.svg', 'diem_danh_nang_cao' => 'diemdanh.svg', 'quan_ly_dang_ky_truc' => 'dangky.svg',
            'xem_minh_chung' => 'minhchung.svg', 'quan_ly_diem_thi' => 'quanlydiemthi.svg', 'quan_ly_phuc_khao' => 'phuckhao.svg',
            'quan_ly_ma_ctv' => 'qrcode.svg', 'quan_ly_thong_bao' => 'thongbao.svg', 'cau_hinh_vi_pham' => 'cauhinhvipham.svg',
            'cau_hinh_bao_cao' => 'cauhinhbaocao.svg', 'hoc_sinh_luu_tru' => 'hocsinhluutru.svg',
            'cau_hinh_tra_cuu_diem_thi' => 'cauhinhtracuu.svg', 'quan_ly_tai_khoan_ca_nhan' => 'taikhoancanhan.svg', 'quan_ly_tai_khoan_admin' => 'taikhoanadmin.svg',
            'quan_ly_giao_vien' => 'quanlygiaovien.svg', 'ky_pdf_dien_tu' => 'kypdf.svg', 'quan_ly_zalo_mini' => 'zalo.svg',
            'quan_ly_hoat_dong' => 'hoatdong.svg', 'quan_ly_email_hoc_sinh' => 'email_hs.svg',
            'duyet_vang_hoc' => 'duyet.svg'
        ];
        foreach ($start_menu_items as $key => $item): 
            if ($key === 'quan_ly_tai_khoan_admin' && !$is_super_admin) continue;
            if ($user_role === 'admin' || in_array('all', $user_permissions) || in_array($key, $user_permissions)): 
                $img_file = $icon_map[$key] ?? 'logoapp.svg';
        ?>
        <div class="desktop-icon-item" ondblclick="openApp('<?php echo $key; ?>', '<?php echo htmlspecialchars($item['label'] ?? $item['name'] ?? 'App'); ?>', '<?php echo htmlspecialchars($item['route']); ?>', '/thidua/public/assets/img/icons/<?= $img_file ?>')">
            <img src="/thidua/public/assets/img/icons/<?= $img_file ?>" onerror="this.src='/thidua/public/assets/img/22logoapp.png'" alt="">
            <span><?php echo htmlspecialchars($item['label'] ?? $item['name'] ?? 'App'); ?></span>
        </div>
        <?php endif; endforeach; ?>
    </div>
    
    <?php if ($user_role === 'admin'): ?>
    <!-- Desktop Widgets Container -->
    <div id="desktopWidgetsContainer" class="absolute inset-0 z-[5] pointer-events-none">
        <style>.desktop-widget { pointer-events: auto; display: none; }</style>
        
        <div class="desktop-widget" id="widget_students" data-name="Tổng số Hc sinh">
            <div class="widget-title"><span>Tổng số Hc sinh</span> <i class="fa-solid fa-users text-blue-500"></i></div>
            <div class="widget-value text-blue-700" id="stat-total-students">...</div>
        </div>
        
        <div class="desktop-widget" id="widget_teachers" data-name="Tổng số GVCN">
            <div class="widget-title"><span>Tổng số GVCN</span> <i class="fa-solid fa-chalkboard-user text-green-500"></i></div>
            <div class="widget-value text-green-700" id="stat-total-teachers">...</div>
        </div>
        
        <div class="desktop-widget" id="widget_ctv" data-name="Tổng số CTV">
            <div class="widget-title"><span>Tổng số CTV</span> <i class="fa-solid fa-user-shield text-purple-500"></i></div>
            <div class="widget-value text-purple-700" id="stat-total-ctv">...</div>
        </div>

        <div class="desktop-widget" id="widget_students_email" data-name="HS có Gmail">
            <div class="widget-title"><span>HS có Gmail</span> <i class="fa-solid fa-envelope text-red-400"></i></div>
            <div class="widget-value text-red-600" id="stat-students-email">...</div>
        </div>

        <div class="desktop-widget" id="widget_students_mail" data-name="HS nhận TB Mail">
            <div class="widget-title"><span>HS nhận TB Mail</span> <i class="fa-solid fa-paper-plane text-teal-500"></i></div>
            <div class="widget-value text-teal-700" id="stat-students-mail">...</div>
        </div>

        <div class="desktop-widget" id="widget_total_visits" data-name="Tổng lượt truy cập">
            <div class="widget-title"><span>Tổng lượt truy cập</span> <i class="fa-solid fa-globe text-indigo-500"></i></div>
            <div class="widget-value text-indigo-700" id="stat-total-visits">...</div>
        </div>

        <div class="desktop-widget" id="widget_total_lookups" data-name="Tổng lượt tra cứu">
            <div class="widget-title"><span>Tổng lượt tra cứu</span> <i class="fa-solid fa-magnifying-glass text-cyan-500"></i></div>
            <div class="widget-value text-cyan-700" id="stat-total-lookups">...</div>
        </div>

        <div class="desktop-widget" id="widget_active_now" data-name="Đang truy cập">
            <div class="widget-title"><span>Đang truy cập</span> <i class="fa-solid fa-user-clock text-emerald-500"></i></div>
            <div class="widget-value text-emerald-700" id="stat-active-now">...</div>
        </div>
        
        <div class="desktop-widget" id="widget_pending" data-name="Yêu cầu Chờ Duyệt">
            <div class="widget-title"><span>Yêu cầu Chờ Duyệt</span> <i class="fa-solid fa-clock-rotate-left text-orange-500"></i></div>
            <div class="widget-value text-orange-700" id="stat-pending">...</div>
        </div>

        <div class="desktop-widget" id="widget_top_last_week" data-name="Hạng Nhất Tuần Trước" style="min-height: 80px;">
            <div class="widget-title"><span>Hạng Nhất Tuần Trước</span> <i class="fa-solid fa-medal text-yellow-500"></i></div>
            <div class="text-sm font-bold text-yellow-600 mt-2" id="stat-top-last-week">...</div>
        </div>

        <div class="desktop-widget" id="widget_bottom_last_week" data-name="Hạng Chót/KXT Tuần Trước" style="min-height: 80px;">
            <div class="widget-title"><span>Hạng Chót/KXT Tuần Trước</span> <i class="fa-solid fa-arrow-trend-down text-red-500"></i></div>
            <div class="text-sm font-bold text-red-600 mt-2" id="stat-bottom-last-week">...</div>
        </div>

        <div class="desktop-widget" id="widget_violations_last_week" data-name="Vi phạm Tuần Trước">
            <div class="widget-title"><span>Vi phạm Tuần Trước</span> <i class="fa-solid fa-circle-exclamation text-rose-500"></i></div>
            <div class="widget-value text-rose-700" id="stat-violations-last-week">...</div>
        </div>
        
        <div class="desktop-widget" id="widget_violations" data-name="Tổng vi phạm (Tuần hiện tại)">
            <div class="widget-title"><span>Tổng vi phạm (Tuần hiện tại)</span> <i class="fa-solid fa-triangle-exclamation text-red-500"></i></div>
            <div class="widget-value text-red-700" id="stat-current-violations">...</div>
        </div>

        <div class="desktop-widget" id="widget_hosting_disk" data-name="Ổ lưu trữ hosting">
            <div class="widget-title"><span>Ổ lưu trữ hosting</span> <i class="fa-solid fa-hard-drive text-slate-500"></i></div>
            <div class="text-sm text-slate-700 mt-2 font-medium" id="stat-hosting-disk">...</div>
        </div>

        <div class="desktop-widget" id="widget_cloud_disk" data-name="Cloud (R2)">
            <div class="widget-title"><span>Cloud (R2)</span> <i class="fa-solid fa-cloud text-sky-500"></i></div>
            <div class="text-sm text-sky-700 mt-2 font-medium" id="stat-cloud-disk">...</div>
        </div>

        <div class="desktop-widget" id="widget_onedrive_disk" data-name="Cloud (OneDrive)">
            <div class="widget-title"><span>Cloud (OneDrive)</span> <i class="fa-brands fa-microsoft text-blue-600"></i></div>
            <div class="text-sm text-blue-700 mt-2 font-medium" id="stat-onedrive-disk">...</div>
        </div>
        
        <div class="desktop-widget" id="widget_birthdays" data-name="Sinh Nhật Sắp Tới" style="min-height: 150px; max-height: 300px; overflow-y: auto;">
            <div class="widget-title"><span>Sinh Nhật Sắp Tới</span> <i class="fa-solid fa-cake-candles text-pink-500"></i></div>
            <div id="stat-birthdays" class="mt-2 text-sm text-gray-700 flex flex-col gap-1">
                <div class="text-center py-2 text-gray-400 text-xs">Đang tải...</div>
            </div>
        </div>


        
    </div>
    
    <!-- JS Tải dữ liệu cho Widgets -->
    <script src="/thidua/public/assets/libs/chart.min.js"></script>
    <script>
    window.refreshWidgets = function() {
        const fetchUrl = '/thidua/api/get-dashboard-stats';
        
        // Cập nhật giao diện loading...
        document.querySelectorAll('.widget-value').forEach(el => {
            if(!el.innerHTML.includes('<canvas')) {
                el.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-gray-300"></i>';
            }
        });

        fetch(fetchUrl)
            .then(res => res.json())
            .then(data => {
                if(document.getElementById('stat-total-students')) document.getElementById('stat-total-students').textContent = data.total_students || 0;
                if(document.getElementById('stat-total-teachers')) document.getElementById('stat-total-teachers').textContent = data.total_teachers || 0;
                if(document.getElementById('stat-total-ctv')) document.getElementById('stat-total-ctv').textContent = data.total_ctv || 0;
                if(document.getElementById('stat-students-email')) document.getElementById('stat-students-email').textContent = data.students_with_email || 0;
                if(document.getElementById('stat-students-mail')) document.getElementById('stat-students-mail').textContent = data.students_receiving_mail || 0;
                if(document.getElementById('stat-total-visits')) document.getElementById('stat-total-visits').textContent = data.total_visits || 0;
                if(document.getElementById('stat-total-lookups')) document.getElementById('stat-total-lookups').textContent = data.total_lookups || 0;
                if(document.getElementById('stat-active-now')) document.getElementById('stat-active-now').textContent = data.active_now || 0;
                if(document.getElementById('stat-pending')) document.getElementById('stat-pending').textContent = data.pending_requests || 0;
                if(document.getElementById('stat-current-violations')) document.getElementById('stat-current-violations').textContent = data.current_violations || 0;
                if(document.getElementById('stat-violations-last-week')) document.getElementById('stat-violations-last-week').textContent = data.previous_week_violations || 0;


                if(document.getElementById('stat-top-last-week')) {
                    if (data.top_class_last_week && data.top_class_last_week.length > 0) {
                        document.getElementById('stat-top-last-week').innerHTML = data.top_class_last_week.map(c => `<div class="truncate">${c.lop} (${c.tong_diem}đ)</div>`).join('');
                    } else document.getElementById('stat-top-last-week').textContent = 'Chưa có';
                }

                if(document.getElementById('stat-bottom-last-week')) {
                    if (data.bottom_class_last_week && data.bottom_class_last_week.length > 0) {
                        document.getElementById('stat-bottom-last-week').innerHTML = data.bottom_class_last_week.join('<br>');
                    } else document.getElementById('stat-bottom-last-week').textContent = 'Chưa có';
                }

                if(document.getElementById('stat-hosting-disk') && data.disk) {
                    const gb = (data.disk.used_bytes / 1073741824).toFixed(2);
                    const total_gb = (data.disk.total_bytes / 1073741824).toFixed(2);
                    document.getElementById('stat-hosting-disk').innerHTML = `${gb} GB / ${total_gb} GB<br><div class="w-full bg-gray-200 rounded-full h-2 mt-1"><div class="bg-slate-500 h-2 rounded-full" style="width: ${data.disk.used_percent}%"></div></div>`;
                }

                if(document.getElementById('stat-cloud-disk') && data.r2) {
                    const mb = (data.r2.total_bytes / 1048576).toFixed(2);
                    document.getElementById('stat-cloud-disk').innerHTML = `${mb} MB<br><span class="text-xs text-sky-500 font-normal">${data.r2.object_count} files</span>`;
                }

                if(document.getElementById('stat-onedrive-disk') && data.onedrive) {
                    const gb = (data.onedrive.used_bytes / 1073741824).toFixed(4);
                    const total_gb = (data.onedrive.total_bytes / 1073741824).toFixed(2);
                    document.getElementById('stat-onedrive-disk').innerHTML = `${gb} GB / ${total_gb} GB<br><div class="w-full bg-blue-200 rounded-full h-2 mt-1"><div class="bg-blue-600 h-2 rounded-full" style="width: ${data.onedrive.used_percent}%"></div></div>`;
                }

                const bList = document.getElementById('stat-birthdays');
                if (bList && data.upcoming_birthdays && data.upcoming_birthdays.length > 0) {
                    bList.innerHTML = data.upcoming_birthdays.map(b => `
                        <div class="flex items-center justify-between py-1 border-b border-gray-100 last:border-0">
                            <span class="font-medium text-gray-800 truncate pr-2">${b.ho_ten}</span>
                            <span class="text-xs text-pink-600 bg-pink-50 px-2 py-0.5 rounded-md shrink-0">${b.ngay_sinh_formatted}</span>
                        </div>
                    `).join('');
                } else if (bList) {
                    bList.innerHTML = '<div class="text-center py-2 text-gray-400 text-xs">Không có sinh nhật nào.</div>';
                }


            })
            .catch(err => {
                console.error("Lỗi tải widgets:", err);
                document.querySelectorAll('.widget-value').forEach(el => {
                    if(!el.innerHTML.includes('<canvas')) {
                        el.innerHTML = '<span class="text-red-500 text-sm">Lỗi tải dữ liệu</span>';
                    }
                });
            });
    };

    document.addEventListener('DOMContentLoaded', function() {
        window.refreshWidgets();
    });
    </script>
    <?php endif; ?>
    
    <!-- Window Container -->
    <div id="windowContainer"></div>
    
    <!-- Flash Messages on Desktop -->
    <div class="absolute top-10 left-1/2 transform -translate-x-1/2 z-[9999] w-full max-w-xl px-4 pointer-events-none">
        <div class="pointer-events-auto">
            <?php require_once __DIR__ . '/flash_messages.php'; ?>
        </div>
    </div>

<?php else: ?>
    <!-- NỘI DUNG IFRAME SPA -->
    <div class="w-full min-h-screen bg-gradient-to-br from-slate-50 to-[#E4F6FD] flex flex-col">
        <div class="p-4 lg:p-6 flex-1">
            <?php require_once __DIR__ . '/flash_messages.php'; ?>
<?php endif; ?>
