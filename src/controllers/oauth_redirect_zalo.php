<?php
// File: src/controllers/oauth_redirect_zalo.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->safeLoad();

$app_id = $_ENV['ZALO_APP_ID'] ?? '';
$callback_url = $_ENV['ZALO_CALLBACK_URL'] ?? 'http://localhost/thidua/oauth-callback-zalo';

// Generate state
$state = bin2hex(random_bytes(16));
$_SESSION['zalo_oauth_state'] = $state;

// Xây dựng Code Verifier và Code Challenge (PKCE cho Zalo v4)
$code_verifier = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
$_SESSION['zalo_code_verifier'] = $code_verifier;
$code_challenge = rtrim(strtr(base64_encode(hash('sha256', $code_verifier, true)), '+/', '-_'), '=');

$url = "https://oauth.zaloapp.com/v4/permission?app_id=" . urlencode($app_id) .
       "&redirect_uri=" . urlencode($callback_url) .
       "&state=" . urlencode($state) .
       "&code_challenge=" . urlencode($code_challenge) .
       "&code_challenge_method=S256" .
       "&prompt=login";

header("Location: $url");
exit;
