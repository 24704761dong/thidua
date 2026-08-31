<?php

function verify_recaptcha(string $response, string $secretKey, ?string $remoteIp = null): bool
{
    $response = trim($response);
    if ($response === '' || $secretKey === '') {
        return false;
    }

    $payload = [
        'secret' => $secretKey,
        'response' => $response,
    ];
    if ($remoteIp !== null && $remoteIp !== '') {
        $payload['remoteip'] = $remoteIp;
    }

    $verifyResponse = false;
    if (function_exists('curl_init')) {
        $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $verifyResponse = curl_exec($ch);
        curl_close($ch);
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query($payload),
                'timeout' => 10,
            ],
        ]);
        $verifyResponse = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
    }

    if ($verifyResponse === false || $verifyResponse === '') {
        return false;
    }

    $data = json_decode((string) $verifyResponse, true);
    return is_array($data) && !empty($data['success']);
}
