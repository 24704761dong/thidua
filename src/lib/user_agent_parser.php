<?php
// File: src/lib/user_agent_parser.php (Bản nâng cấp chi tiết v2)

/**
 * Trích xuất phiên bản theo khóa định danh (vd: Chrome/xx, Android xx).
 */
function ua_version_of(string $ua, array $keys, string $pattern = null): ?string {
    // Nếu truyền pattern tùy biến, ưu tiên dùng
    if ($pattern) {
        if (preg_match($pattern, $ua, $m)) {
            return trim($m[1] ?? ($m[0] ?? ''));
        }
        return null;
    }
    // Mặc định dạng: Key/Version hoặc Key Version
    foreach ($keys as $k) {
        // Key/1.2.3 hoặc Key/1
        if (preg_match('~' . preg_quote($k, '~') . '[/ ]([0-9A-Za-z._-]+)~i', $ua, $m)) {
            return $m[1];
        }
    }
    return null;
}

/**
 * Phân loại thiết bị (desktop/mobile/tablet/TV/console/bot) và trích xuất model (Android/iOS).
 */
function ua_device_info(string $ua): array {
    $is_bot = preg_match('~bot|spider|crawler|facebookexternalhit|slurp|bingpreview|duckduckbot|yandex|google-structured-data-testing-tool~i', $ua) ? true : false;

    // Mặc định
    $type = 'PC / Laptop';
    if ($is_bot) {
        $type = 'Bot / Crawler';
    } elseif (preg_match('~smart[- ]?tv|hbbtv|appletv|googletv|tizen|webos~i', $ua)) {
        $type = 'Smart TV';
    } elseif (preg_match('~playstation|xbox|nintendo|switch~i', $ua)) {
        $type = 'Console';
    } elseif (preg_match('~tablet|ipad|sm\-t|kindle|silk/|tab|mi pad|lenovo tab~i', $ua)) {
        $type = 'Tablet';
    } elseif (preg_match('~mobile|iphone|ipod|android(?!.*(tablet|xoom|nexus 7|nexus 10))~i', $ua)) {
        $type = 'Smartphone';
    }

    // Model thiết bị (Android: chuỗi model sau Android xx; iOS: iPhone/ iPad)
    $model = null;
    if (preg_match('~Android\s*[0-9\.]*;\s*([^)]+)\)~i', $ua, $m)) {
        // Thường dạng: Android 13; SM-G991B) → lấy SM-G991B
        // hoặc Android 12; Pixel 7 Pro Build/...
        $candidate = trim($m[1]);
        // Cắt bỏ "Build/..." nếu có
        $candidate = preg_replace('~Build/.*$~i', '', $candidate);
        // Loại bỏ phần ";"
        $candidate = preg_replace('~;.*$~', '', $candidate);
        // Làm gọn khoảng trắng
        $candidate = preg_replace('~\s+~'|| ' ' || $candidate);
        if ($candidate !== '') $model = $candidate;
    } elseif (preg_match('~\((iPhone|iPad|iPod)[^;)]*;~i', $ua, $m)) {
        $model = $m[1];
    }

    return [$type, $model, $is_bot];
}

/**
 * Xác định hệ điều hành + phiên bản.
 */
function ua_os_info(string $ua): array {
    $os = 'Không rõ';
    $os_version = null;

    // Windows
    if (preg_match('~Windows NT ([0-9.]+)~i', $ua, $m)) {
        $map = [
            '10.0' => 'Windows 10/11',
            '6.3'  => 'Windows 8.1',
            '6.2'  => 'Windows 8',
            '6.1'  => 'Windows 7',
            '6.0'  => 'Windows Vista',
            '5.1'  => 'Windows XP',
        ];
        $nt = $m[1];
        $os = $map[$nt] ?? 'Windows';
        $os_version = $nt;
    }
    // iOS (iPhone/iPad)
    elseif (preg_match('~CPU (?:iPhone )?OS ([0-9_]+)~i', $ua, $m)) {
        $os = 'iOS';
        $os_version = str_replace('_', '.', $m[1]);
    }
    // macOS
    elseif (preg_match('~Mac OS X ([0-9_\.]+)~i', $ua, $m)) {
        $os = 'macOS';
        $os_version = str_replace('_', '.', $m[1]);
    }
    // Android
    elseif (preg_match('~Android ([0-9.]+)~i', $ua, $m)) {
        $os = 'Android';
        $os_version = $m[1];
    }
    // Linux distros (nếu có chuỗi)
    elseif (preg_match('~Ubuntu|Fedora|Debian|Arch|CentOS|Red Hat|SUSE~i', $ua, $m)) {
        $os = ucfirst(strtolower($m[0]));
    }
    // Linux chung
    elseif (preg_match('~Linux~i', $ua)) {
        $os = 'Linux';
    }

    return [$os, $os_version];
}

/**
 * Nhân render & kiến trúc CPU.
 */
function ua_engine_arch(string $ua): array {
    $engine = 'Không rõ';
    if (preg_match('~AppleWebKit~i', $ua)) $engine = 'WebKit/Blink';
    if (preg_match('~Gecko~i', $ua) && !preg_match('~like Gecko~i', $ua)) $engine = 'Gecko';
    if (preg_match('~Trident|MSIE~i', $ua)) $engine = 'Trident';
    if (preg_match('~EdgeHTML~i', $ua)) $engine = 'EdgeHTML';

    $arch = null;
    if (preg_match('~arm64|aarch64~i', $ua)) $arch = 'ARM64';
    elseif (preg_match('~x86_64|win64|x64|amd64~i', $ua)) $arch = 'x64';
    elseif (preg_match('~i686|i386|x86~i', $ua)) $arch = 'x86';

    return [$engine, $arch];
}

/**
 * Ứng dụng đặc thù & trình duyệt + phiên bản.
 */
function ua_app_browser(string $ua): array {
    $app = null;
    $app_version = null;

    // Ứng dụng phổ biến
    if (preg_match('~Zalo(?:PC)?~i', $ua)) {
        $app = 'Zalo';
        $app_version = ua_version_of($ua, ['Zalo', 'ZaloPC']);
    } elseif (preg_match('~FBAV~i', $ua)) {
        $app = 'Facebook App';
        $app_version = ua_version_of($ua, ['FBAV']);
    } elseif (preg_match('~Messenger~i', $ua)) {
        $app = 'Messenger App';
        $app_version = ua_version_of($ua, ['Messenger']);
    } elseif (preg_match('~Telegram~i', $ua)) {
        $app = 'Telegram';
        $app_version = ua_version_of($ua, ['Telegram']);
    } elseif (preg_match('~Electron~i', $ua)) {
        $app = 'Electron App';
        $app_version = ua_version_of($ua, ['Electron']);
    }

    // Trình duyệt + phiên bản
    $browser = 'Không rõ';
    $browser_version = null;

    if (preg_match('~Brave~i', $ua)) {
        $browser = 'Brave';
        // Brave thường ẩn version, fallback Chrome
        $browser_version = ua_version_of($ua, ['Brave']) ?? ua_version_of($ua, ['Chrome', 'CriOS']);
    } elseif (preg_match('~Vivaldi~i', $ua)) {
        $browser = 'Vivaldi';
        $browser_version = ua_version_of($ua, ['Vivaldi']);
    } elseif (preg_match('~OPR/|Opera~i', $ua)) {
        $browser = 'Opera';
        $browser_version = ua_version_of($ua, ['OPR', 'Opera']);
    } elseif (preg_match('~(Edg|Edge)/~i', $ua)) {
        $browser = 'Edge';
        $browser_version = ua_version_of($ua, ['Edg', 'Edge']);
    } elseif (preg_match('~SamsungBrowser~i', $ua)) {
        $browser = 'Samsung Browser';
        $browser_version = ua_version_of($ua, ['SamsungBrowser']);
    } elseif (preg_match('~Firefox|FxiOS~i', $ua)) {
        $browser = 'Firefox';
        $browser_version = ua_version_of($ua, ['Firefox', 'FxiOS']);
    } elseif (preg_match('~Chrome|CriOS~i', $ua) && !preg_match('~OPR/|Edg/|SamsungBrowser~i', $ua)) {
        $browser = 'Chrome';
        $browser_version = ua_version_of($ua, ['Chrome', 'CriOS']);
    } elseif (preg_match('~Safari~i', $ua) && !preg_match('~Chrome|CriOS|OPR/|Edg/|SamsungBrowser~i', $ua)) {
        $browser = 'Safari';
        // Safari version thường nằm ở Version/x.y
        $browser_version = ua_version_of($ua, ['Version']) ?? ua_version_of($ua, ['Safari']);
    } elseif (preg_match('~MSIE|Trident~i', $ua)) {
        $browser = 'Internet Explorer';
        $browser_version = ua_version_of($ua, ['MSIE']) ?? ua_version_of($ua, ['rv']); // IE11 sử dụng rv:11.0
    }

    return [$app, $app_version, $browser, $browser_version];
}

/**
 * Hàm chính: parse_user_agent
 * Trả về mảng thông tin chi tiết, dễ đọc.
 */
function parse_user_agent(string $user_agent): array {
    $ua = trim($user_agent);

    // OS & version
    [$os, $os_version] = ua_os_info($ua);

    // Device
    [$device, $device_model, $is_bot] = ua_device_info($ua);

    // Engine & CPU
    [$engine, $arch] = ua_engine_arch($ua);

    // App & Browser
    [$app, $app_version, $browser, $browser_version] = ua_app_browser($ua);

    // Xâu mô tả đầy đủ
    $parts = [];
    if ($is_bot) $parts[] = 'Bot/Crawler';
    if ($app) $parts[] = $app . ($app_version ? " $app_version" : '');
    $parts[] = $browser . ($browser_version ? " $browser_version" : '');
    $os_disp = $os . ($os_version ? " $os_version" : '');
    $parts[] = "trên $os_disp";
    $parts[] = "($device" . ($device_model ? ": $device_model" : "") . ")";
    if ($arch) $parts[] = "CPU $arch";
    if ($engine !== 'Không rõ') $parts[] = "Engine $engine";

    $full = implode(' · ', array_filter($parts));

    return [
        'browser'          => $browser,
        'browser_version'  => $browser_version,
        'app'              => $app,
        'app_version'      => $app_version,
        'os'               => $os,
        'os_version'       => $os_version,
        'device'           => $device,
        'device_model'     => $device_model,
        'is_bot'           => $is_bot,
        'cpu_arch'         => $arch,
        'engine'           => $engine,
        'full_string'      => $full !== '' ? $full : 'Không xác định',
        'raw'              => $ua,
    ];
}

/* =========================
   Ví dụ sử dụng (xóa khi deploy):
   echo '<pre>';
   print_r(parse_user_agent($_SERVER['HTTP_USER_AGENT'] ?? ''));
   echo '</pre>';
   ========================= */
