<?php
// File: src/lib/maintenance.php

if (!defined('MAINTENANCE_BOOTSTRAPPED')) {
    define('MAINTENANCE_BOOTSTRAPPED', true);

    if (PHP_SAPI === 'cli') return; // Bỏ qua CLI

    // Nạp dotenv (nếu có)
    if (!class_exists('Dotenv\Dotenv') && file_exists(__DIR__ . '/../../vendor/autoload.php')) {
        require_once __DIR__ . '/../../vendor/autoload.php';
    }
    if (class_exists('Dotenv\Dotenv')) {
        $root = realpath(__DIR__ . '/../../');
        if ($root && file_exists($root.'/.env')) {
            Dotenv\Dotenv::createImmutable($root)->safeLoad();
        }
    }

    // Đọc biến môi trường
    $enabled   = filter_var(getenv('APP_MAINTENANCE') ?: ($_ENV['APP_MAINTENANCE'] ?? false), FILTER_VALIDATE_BOOLEAN);
    $allowed   = array_filter(array_map('trim', explode(',', (string)(getenv('MAINTENANCE_ALLOWED_IPS') ?: ($_ENV['MAINTENANCE_ALLOWED_IPS'] ?? '')))));
    $retry     = (int)(getenv('RETRY_AFTER_SECONDS') ?: ($_ENV['RETRY_AFTER_SECONDS'] ?? 3600));
    $untilText = getenv('MAINTENANCE_UNTIL') ?: ($_ENV['MAINTENANCE_UNTIL'] ?? '');

    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $ip  = $_SERVER['REMOTE_ADDR'] ?? '';

    // Bỏ qua asset tĩnh & chính trang maintenance
    $isStatic = (bool)preg_match('~\.(?:css|js|png|jpe?g|gif|webp|svg|ico|woff2?|ttf|eot|map)$~i', $uri);
    $isMaintenancePage = (stripos($uri, '/thidua/maintenance.php') === 0 || preg_match('~/maintenance\.php$~i', $uri));

    if ($enabled && !$isStatic && !$isMaintenancePage && !in_array($ip, $allowed, true)) {
        // Xoá mọi buffer để tránh "headers already sent"
        while (ob_get_level() > 0) { @ob_end_clean(); }

        // 503 + Retry-After
        http_response_code(503);
        if ($retry > 0 && !headers_sent()) header('Retry-After: '.$retry);

        // Biến dùng trong maintenance view
        $GLOBALS['_MAINTENANCE_UNTIL'] = $untilText;

        // Tìm maintenance.php
        $candidates = [
            $_SERVER['DOCUMENT_ROOT'] . '/thidua/maintenance.php',
            $_SERVER['DOCUMENT_ROOT'] . '/maintenance.php',
            realpath(__DIR__ . '/../../public/maintenance.php'),
            realpath(__DIR__ . '/../../maintenance.php'),
        ];
        $maintenanceFile = null;
        foreach ($candidates as $c) { if ($c && is_file($c)) { $maintenanceFile = $c; break; } }

        if ($maintenanceFile) { include $maintenanceFile; exit; }

        // Fallback tối giản (UI đẹp, chỉ 3 icon)
        renderMaintenanceFallbackUI($untilText);
        exit;
    }
}

/**
 * Fallback UI đẹp & tối giản (không nút, chỉ 3 icon Zalo/Facebook/Gmail)
 */
function renderMaintenanceFallbackUI(string $untilText=''): void {
    if (!headers_sent()) header('Content-Type: text/html; charset=UTF-8');

    // Giá trị mặc định an toàn (có thể đổi cho hợp branding)
    $page_title  = 'Hệ thống đang bảo trì';
    $school_name = 'BINH SON HIGH SCHOOL';
    $system_name = 'Hệ thống Đánh Giá Thi Đua';
    $logo_path   = '/thidua/public/assets/img/22logoapp.png';

    ?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($page_title) ?></title>
  <link rel="icon" type="image/x-icon" href="/thidua/public/assets/img/favicon.ico" />
  <style>
    :root{--primary-blue:#00a8e8;--dark-blue:#2c3e50;--text-primary:#1d2d35;--text-secondary:#5a6a72;--bg-light:#f4f7f9;--card-border:#e9ecef;}
    body{min-height:100vh;margin:0;display:grid;place-items:center;font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;background:
      radial-gradient(circle at top left, rgba(0,168,232,.15), transparent 60%),
      radial-gradient(circle at bottom right, rgba(151,201,60,.15), transparent 60%),
      var(--bg-light);}
    .note-container{width:min(600px,90vw);padding:clamp(2rem,5vw,3rem);border-radius:16px;background:#fff;box-shadow:0 10px 30px rgba(0,0,0,.07);text-align:center;border:1px solid var(--card-border);}
    .brand-logo img{height:70px;margin-bottom:1rem;}
    .brand-text h1{margin:0;font-weight:700;font-size:1.5rem;color:var(--text-primary);}
    .brand-text h2{margin:.25rem 0 1.5rem 0;font-size:1rem;font-weight:500;color:var(--text-secondary);text-transform:uppercase;letter-spacing:1px;}
    .big-icon{font-size:clamp(3.5rem,12vw,5rem);color:var(--primary-blue);margin-bottom:.75rem;line-height:1;}
    .heading{font-size:1.75rem;font-weight:700;color:var(--text-primary);margin-bottom:.6rem;}
    .message{color:var(--text-secondary);margin-bottom:1.25rem;max-width:460px;margin:auto;}
    .until{display:inline-block;padding:.35rem .7rem;border:1px dashed var(--card-border);border-radius:8px;color:var(--dark-blue);background:#f9fbff;margin-bottom:1.5rem;}
    .social-links{margin-top:1.2rem;}
    .social-links a{display:inline-flex;align-items:center;justify-content:center;width:54px;height:54px;margin:0 .35rem;border-radius:50%;font-size:1.35rem;color:#fff;transition:.2s;}
    .social-zalo{background:#0068ff;}.social-facebook{background:#1877f2;}.social-gmail{background:#ea4335;}
    .social-links a:hover{transform:translateY(-2px);opacity:.9;}
    .foot{margin-top:1.25rem;font-size:.9rem;color:var(--text-secondary);}
  </style>
    </head>
<body>
  <main class="note-container">
    <div class="brand-logo"><img src="<?= htmlspecialchars($logo_path) ?>" alt="Logo <?= htmlspecialchars($school_name) ?>"></div>
    <div class="brand-text">
      <h1><?= htmlspecialchars($school_name) ?></h1>
      <h2><?= htmlspecialchars($system_name) ?></h2>
    </div>

    <div>
      <div class="big-icon"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-tools" viewBox="0 0 16 16"><path d="M1 0 0 1l2.2 3.081a1 1 0 0 0 .815.419h.07a1 1 0 0 1 .708.293l2.675 2.675-2.617 2.654A3.003 3.003 0 0 0 0 13a3 3 0 1 0 5.878-.851l2.654-2.617.968.968-.305.914a1 1 0 0 0 .242 1.023l3.27 3.27a.997.997 0 0 0 1.414 0l1.586-1.586a.997.997 0 0 0 0-1.414l-3.27-3.27a1 1 0 0 0-1.023-.242L10.5 9.5l-.96-.96 2.68-2.643A3.005 3.005 0 0 0 16 3q0-.405-.102-.777l-2.14 2.141L12 4l-.364-1.757L13.777.102a3 3 0 0 0-3.675 3.68L7.462 6.46 4.793 3.793a1 1 0 0 1-.293-.707v-.071a1 1 0 0 0-.419-.814zm9.646 10.646a.5.5 0 0 1 .708 0l2.914 2.915a.5.5 0 0 1-.707.707l-2.915-2.914a.5.5 0 0 1 0-.708M3 11l.471.242.529.026.287.445.445.287.026.529L5 13l-.242.471-.026.529-.445.287-.287.445-.529.026L3 15l-.471-.242L2 14.732l-.287-.445L1.268 14l-.026-.529L1 13l.242-.471.026-.529.445-.287.287-.445.529-.026z"/></svg></div>
      <div class="heading">Hệ thống đang bảo trì</div>
      <p class="message">Rất tiếc vì sự bất tiện. Chúng tôi đang tiến hành bảo trì để nâng cấp hiệu năng và độ ổn định. Vui lòng quay lại sau.</p>
      <?php if (!empty($untilText)): ?>
        <div class="until">Dự kiến hoàn tất: <strong><?= htmlspecialchars($untilText) ?></strong></div>
      <?php endif; ?>

      <!-- 3 icon liên hệ -->
      <div class="social-links">
        <a class="social-zalo" href="http://zaloapp.com/qr/p/n0zxl1acmd61" target="_blank" title="Zalo"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-chat-dots-fill" viewBox="0 0 16 16"><path d="M16 8c0 3.866-3.582 7-8 7a9 9 0 0 1-2.347-.306c-.584.296-1.925.864-4.181 1.234-.2.032-.352-.176-.273-.362.354-.836.674-1.95.77-2.966C.744 11.37 0 9.76 0 8c0-3.866 3.582-7 8-7s8 3.134 8 7M5 8a1 1 0 1 0-2 0 1 1 0 0 0 2 0m4 0a1 1 0 1 0-2 0 1 1 0 0 0 2 0m3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/></svg></a>
        <a class="social-facebook" href="https://www.facebook.com/phamvanthanhdong" target="_blank" title="Facebook"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-facebook" viewBox="0 0 16 16"><path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951"/></svg></a>
        <a class="social-gmail" href="mailto:quanlydanhgia.c3binhson@gmail.com" title="Gmail"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-envelope-fill" viewBox="0 0 16 16"><path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414zM0 4.697v7.104l5.803-3.558zM6.761 8.83l-6.57 4.027A2 2 0 0 0 2 14h12a2 2 0 0 0 1.808-1.144l-6.57-4.027L8 9.586zm3.436-.586L16 11.801V4.697z"/></svg></a>
      </div>

      <div class="foot">Mã trạng thái: 503 Service Unavailable</div>
    </div>
  </main>
</body>
</html>
<?php
}
