<?php
/**
 * Nạp layout chung của cổng nhà trường (header/footer/Zalo chat)
 * mà không chạy side-effect bootstrap đầy đủ (redirect, visitor stats...).
 */
if (!defined('THIDUA_SCHOOL_CHROME_LOADED')) {
    define('THIDUA_SCHOOL_CHROME_LOADED', true);

    require_once __DIR__ . '/../../../../app/lib/Env.php';
    Env::load(__DIR__ . '/../../../../.env');

    $composerAutoload = __DIR__ . '/../../../../vendor/autoload.php';
    if (is_file($composerAutoload)) {
        require_once $composerAutoload;
    }

    $schoolChromeLibs = [
        'Database.php',
        'SeoService.php',
        'PostRepository.php',
        'DocumentRepository.php',
        'GoogleUserRepository.php',
        'VisitorStatsRepository.php',
        'SectionRepository.php',
        'PageRepository.php',
        'ContentLayoutRepository.php',
        'AdmissionRepository.php',
        'WebPushService.php',
        'SessionSecurity.php',
        'Csrf.php',
        'GoogleAuth.php',
    ];
    foreach ($schoolChromeLibs as $libFile) {
        require_once __DIR__ . '/../../../../app/lib/' . $libFile;
    }

    GoogleAuth::restoreFromCookie();
}
