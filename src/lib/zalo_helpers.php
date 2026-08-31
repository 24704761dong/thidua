<?php
// File: src/lib/zalo_helpers.php

/**
 * Láº¥y Access Token cá»§a Zalo OA. 
 * Æ¯u tiÃªn láº¥y tá»« CSDL (báº£ng settings), náº¿u khÃ´ng cÃ³ thÃ¬ láº¥y tá»« .env
 */
function get_zalo_oa_access_token() {
    try {
        $db = get_db_connection();
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'zalo_oa_access_token'");
        $stmt->execute();
        $token = $stmt->fetchColumn();
        if (!empty($token)) return $token;
    } catch (\Throwable $e) {
        try {
            $stmt2 = $db->prepare("SELECT setting_value FROM he_thong_cai_dat WHERE setting_key = 'zalo_oa_access_token'");
            $stmt2->execute();
            $token2 = $stmt2->fetchColumn();
            if (!empty($token2)) return $token2;
        } catch (\Throwable $e2) {}
    }

    return $_ENV['ZALO_OA_ACCESS_TOKEN'] ?? '';
}

/**
 * Láº¥y Refresh Token cá»§a Zalo OA
 */
function get_zalo_oa_refresh_token() {
    try {
        $db = get_db_connection();
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'zalo_oa_refresh_token'");
        $stmt->execute();
        $token = $stmt->fetchColumn();
        if (!empty($token)) return $token;
    } catch (\Throwable $e) {
        try {
            $stmt2 = $db->prepare("SELECT setting_value FROM he_thong_cai_dat WHERE setting_key = 'zalo_oa_refresh_token'");
            $stmt2->execute();
            $token2 = $stmt2->fetchColumn();
            if (!empty($token2)) return $token2;
        } catch (\Throwable $e2) {}
    }

    return $_ENV['ZALO_OA_REFRESH_TOKEN'] ?? '';
}

/**
 * LÆ°u token má»›i vÃ o CSDL
 */
function update_zalo_oa_tokens($access_token, $refresh_token) {
    try {
        $db = get_db_connection();
        $stmt = $db->prepare("SELECT id FROM settings WHERE setting_key = 'zalo_oa_access_token'");
        $stmt->execute();
        if ($stmt->fetch()) {
            $db->prepare("UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = 'zalo_oa_access_token'")->execute([$access_token]);
        } else {
            $db->prepare("INSERT INTO settings (setting_key, setting_value, group_name, updated_at) VALUES ('zalo_oa_access_token', ?, 'zalo', NOW())")->execute([$access_token]);
        }
        $stmt = $db->prepare("SELECT id FROM settings WHERE setting_key = 'zalo_oa_refresh_token'");
        $stmt->execute();
        if ($stmt->fetch()) {
            $db->prepare("UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = 'zalo_oa_refresh_token'")->execute([$refresh_token]);
        } else {
            $db->prepare("INSERT INTO settings (setting_key, setting_value, group_name, updated_at) VALUES ('zalo_oa_refresh_token', ?, 'zalo', NOW())")->execute([$refresh_token]);
        }
    } catch (\Throwable $e) {
        try {
            $stmt = $db->prepare("SELECT setting_key FROM he_thong_cai_dat WHERE setting_key = 'zalo_oa_access_token'");
            $stmt->execute();
            if ($stmt->fetch()) {
                $db->prepare("UPDATE he_thong_cai_dat SET setting_value = ? WHERE setting_key = 'zalo_oa_access_token'")->execute([$access_token]);
            } else {
                $db->prepare("INSERT INTO he_thong_cai_dat (setting_key, setting_value, nam_hoc_id) VALUES ('zalo_oa_access_token', ?, 0)")->execute([$access_token]);
            }
            $stmt = $db->prepare("SELECT setting_key FROM he_thong_cai_dat WHERE setting_key = 'zalo_oa_refresh_token'");
            $stmt->execute();
            if ($stmt->fetch()) {
                $db->prepare("UPDATE he_thong_cai_dat SET setting_value = ? WHERE setting_key = 'zalo_oa_refresh_token'")->execute([$refresh_token]);
            } else {
                $db->prepare("INSERT INTO he_thong_cai_dat (setting_key, setting_value, nam_hoc_id) VALUES ('zalo_oa_refresh_token', ?, 0)")->execute([$refresh_token]);
            }
        } catch (\Throwable $e2) {}
    }
}

/**
 * LÃ m má»›i Access Token thÃ´ng qua Refresh Token
 */
function refresh_zalo_oa_token() {
    $refresh_token = get_zalo_oa_refresh_token();
    $app_id = $_ENV['ZALO_APP_ID'] ?? '';
    $secret_key = $_ENV['ZALO_APP_SECRET'] ?? '';

    if (empty($refresh_token) || empty($app_id) || empty($secret_key)) {
        $missing = [];
        if (empty($refresh_token)) $missing[] = 'Refresh Token';
        if (empty($app_id)) $missing[] = 'App ID';
        if (empty($secret_key)) $missing[] = 'Secret Key';
        $msg = "Zalo ZNS: Thiáº¿u " . implode(', ', $missing);
        error_log($msg);
        if (function_exists('log_to_file')) { log_to_file("[ZALO REFRESH] " . $msg); }
        return false;
    }

    if (function_exists('log_to_file')) { log_to_file("[ZALO REFRESH] Äang refresh token vá»›i App ID: {$app_id}"); }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://oauth.zaloapp.com/v4/oa/access_token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'secret_key: ' . $secret_key
    ]);
    
    $data = http_build_query([
        'refresh_token' => $refresh_token,
        'app_id' => $app_id,
        'grant_type' => 'refresh_token'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        $msg = "Zalo ZNS Token Refresh cURL Error: " . $err;
        error_log($msg);
        if (function_exists('log_to_file')) { log_to_file("[ZALO REFRESH] " . $msg); }
        return false;
    }

    if (function_exists('log_to_file')) { log_to_file("[ZALO REFRESH] HTTP {$http_code} - Response: " . substr($response, 0, 500)); }

    $result = json_decode($response, true);
    if (isset($result['access_token']) && isset($result['refresh_token'])) {
        update_zalo_oa_tokens($result['access_token'], $result['refresh_token']);
        if (function_exists('log_to_file')) { log_to_file("[ZALO REFRESH] Refresh thÃ nh cÃ´ng! Token má»›i Ä‘Ã£ Ä‘Æ°á»£c lÆ°u."); }
        return $result['access_token'];
    }

    $msg = "Zalo ZNS Token Refresh Failed: " . $response;
    error_log($msg);
    if (function_exists('log_to_file')) { log_to_file("[ZALO REFRESH] " . $msg); }
    return false;
}

/**
 * Chuáº©n hÃ³a sá»‘ Ä‘iá»‡n thoáº¡i cho Zalo (chuyá»ƒn 0 thÃ nh 84)
 */
function format_phone_for_zalo($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (strpos($phone, '0') === 0) {
        $phone = '84' . substr($phone, 1);
    }
    return $phone;
}

/**
 * Gá»­i tin nháº¯n ZNS Cáº£nh bÃ¡o Ä‘Äƒng nháº­p
 */
function send_zalo_login_alert($phone_number, $user_name, $account_name, $time, $ip_address, $browser, $vi_tri_ip = 'KhÃ´ng xÃ¡c Ä‘á»‹nh', $vi_tri_gps = null) {
    // Append location to browser string since Zalo template might not have location fields
    $thiet_bi_full = $browser . ' | Vá»‹ trÃ­: ' . $vi_tri_ip;
    if ($vi_tri_gps) {
        $thiet_bi_full .= ' | Báº£n Ä‘á»“: ' . $vi_tri_gps;
    }
    // ID Máº«u ZNS Ä‘ang chá» duyá»‡t (Báº¡n sáº½ Ä‘iá»n vÃ o .env sau khi cÃ³)
    $template_id = $_ENV['ZALO_ZNS_TEMPLATE_ID'] ?? ''; 
    if (empty($template_id)) {
        if (function_exists('log_to_file')) { log_to_file("[ZALO ZNS] Bá» qua - ChÆ°a cáº¥u hÃ¬nh ZALO_ZNS_TEMPLATE_ID trong .env"); }
        return false; 
    }

    if (empty($phone_number)) {
        if (function_exists('log_to_file')) { log_to_file("[ZALO ZNS] Bá» qua - KhÃ´ng cÃ³ sá»‘ Ä‘iá»‡n thoáº¡i ngÆ°á»i nháº­n"); }
        return false;
    }

    $access_token = get_zalo_oa_access_token();
    if (empty($access_token)) {
        if (function_exists('log_to_file')) { log_to_file("[ZALO ZNS] KhÃ´ng cÃ³ Access Token. Thá»­ refresh..."); }
        // Thá»­ refresh token ngay láº­p tá»©c náº¿u khÃ´ng cÃ³ access token
        $access_token = refresh_zalo_oa_token();
        if (empty($access_token)) {
            if (function_exists('log_to_file')) { log_to_file("[ZALO ZNS] Refresh token tháº¥t báº¡i. KhÃ´ng thá»ƒ gá»­i ZNS."); }
            return false;
        }
    }

    $zalo_phone = format_phone_for_zalo($phone_number);
    if (function_exists('log_to_file')) { log_to_file("[ZALO ZNS] Gá»­i Ä‘áº¿n {$zalo_phone}, Template: {$template_id}"); }

    $payload = [
        "phone" => $zalo_phone,
        "template_id" => $template_id,
        "template_data" => [
            "ten_quan_tri" => substr($user_name, 0, 30),
            "ten_tai_khoan" => substr($account_name, 0, 30),
            "thoi_gian" => $time,
            "ip_address" => substr($ip_address, 0, 30),
            "thiet_bi" => substr($thiet_bi_full, 0, 200)
        ]
    ];

    $secret_key = $_ENV['ZALO_APP_SECRET'] ?? '';
    $appsecret_proof = hash_hmac('sha256', $access_token, $secret_key);

    $ch = curl_init('https://business.openapi.zalo.me/message/template');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'access_token: ' . $access_token,
        'appsecret_proof: ' . $appsecret_proof
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);

    // Náº¿u lá»—i do Token háº¿t háº¡n (-216) hoáº·c khÃ´ng há»£p lá»‡ (-124, -1241), thá»­ refresh token vÃ  gá»­i láº¡i 1 láº§n
    if (isset($result['error']) && in_array($result['error'], [-216, -124, -1241])) {
        if (function_exists('log_to_file')) { log_to_file("[ZALO ZNS] Token lá»—i (error: {$result['error']}). Ä ang tá»± refresh token..."); }
        $new_token = refresh_zalo_oa_token();
        if ($new_token) {
            if (function_exists('log_to_file')) { log_to_file("[ZALO ZNS] Refresh thÃ nh cÃ´ng. Gá»­i láº¡i ZNS..."); }
            $new_appsecret_proof = hash_hmac('sha256', $new_token, $secret_key);
            $ch = curl_init('https://business.openapi.zalo.me/message/template');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'access_token: ' . $new_token,
                'appsecret_proof: ' . $new_appsecret_proof
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $response = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($response, true);
        } else {
            if (function_exists('log_to_file')) { log_to_file("[ZALO ZNS] Refresh token tháº¥t báº¡i. KhÃ´ng thá»ƒ gá»­i láº¡i."); }
        }
    }

    if (isset($result['error']) && $result['error'] != 0) {
        error_log("Zalo ZNS Send Error to {$zalo_phone}: " . $response);
        if (function_exists('log_to_file')) { log_to_file("[ZALO ZNS] Lá»—i gá»­i Ä‘áº¿n {$zalo_phone}: " . $response); }
        return false;
    }

    if (function_exists('log_to_file')) { log_to_file("[ZALO ZNS] Gá»­i thÃ nh cÃ´ng Ä‘áº¿n {$zalo_phone}!"); }
    return true;
}

/**
 * Gửi thông báo đẩy (Push Notification) qua hệ thống Zalo Mini App (Miễn phí)
 * Chú ý: Đòi hỏi thiết lập ZALO_MINIAPP_NOTIF_TEMPLATE_ID trong file .env
 */
function send_zalo_push_notification($zalo_id, $message_text, $title = 'Thông báo') {
    if (empty($zalo_id)) return false;

    $app_id = $_ENV['ZALO_APP_ID'] ?? '';
    $app_secret = $_ENV['ZALO_APP_SECRET'] ?? '';
    $template_id = $_ENV['ZALO_MINIAPP_NOTIF_TEMPLATE_ID'] ?? '';

    if (empty($app_id) || empty($app_secret) || empty($template_id)) {
        if (function_exists("log_to_file")) { 
            log_to_file("[ZALO PUSH] Bỏ qua - Thiếu cấu hình ZALO_APP_ID, ZALO_APP_SECRET hoặc ZALO_MINIAPP_NOTIF_TEMPLATE_ID trong .env"); 
        }
        return false;
    }

    // Payload chuẩn gửi thông báo Mini App Zalo
    $payload = [
        "templateId" => $template_id,
        "templateData" => [
            "title" => $title,
            "content" => $message_text
        ],
        "to" => $zalo_id
    ];

    $ch = curl_init("https://openapi.mini.zalo.me/notification/template");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "X-Zalo-MiniApp-Id: " . $app_id,
        "X-Zalo-MiniApp-Secret: " . $app_secret
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);
    
    if (isset($result["error"]) && $result["error"] != 0) {
        if (function_exists("log_to_file")) { log_to_file("[ZALO OA] L×i gíi ¿n {$zalo_id}: " . $response); }
        return false;
    }

    return true;
}



