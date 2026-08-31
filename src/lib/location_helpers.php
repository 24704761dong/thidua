<?php
// File: src/lib/location_helpers.php

if (!function_exists('get_client_ip')) {
    /**
     * Lấy IP thực của client, vượt qua proxy (Cloudflare, Nginx, Ngrok...)
     * @return string
     */
    function get_client_ip() {
        $ip_keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip_list = explode(',', $_SERVER[$key]);
                return trim($ip_list[0]);
            }
        }
        return '127.0.0.1';
    }
}

if (!function_exists('get_ip_location')) {
    /**
     * Lấy vị trí từ địa chỉ IP qua ip-api.com
     * @param string $ip
     * @return string
     */
    function get_ip_location($ip) {
        if (empty($ip) || $ip === '127.0.0.1' || $ip === '::1') {
            return 'Biên Hòa, Việt Nam (Localhost)';
        }

        $url = "http://ip-api.com/json/{$ip}?fields=status,message,country,city,isp&lang=vi";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2); // Timeout 2s để không làm chậm login
        
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            $data = json_decode($response, true);
            if (isset($data['status']) && $data['status'] === 'success') {
                $city = $data['city'] ?? '';
                $country = $data['country'] ?? '';
                $isp = $data['isp'] ?? '';
                
                $parts = array_filter([$city, $country]);
                $location = implode(', ', $parts);
                
                if (!empty($isp)) {
                    $location .= " ($isp)";
                }
                
                return $location;
            }
        }
        return 'Không xác định';
    }
}

if (!function_exists('format_gps_location')) {
    /**
     * Format tạo độ GPS thành string Google Maps
     * @param string $lat
     * @param string $lon
     * @return string|null
     */
    function format_gps_location($lat, $lon) {
        if (empty($lat) || empty($lon)) {
            $client_ip = get_client_ip();
            if ($client_ip === '127.0.0.1' || $client_ip === '::1') {
                return "https://www.google.com/maps?q=10.9574,106.8427";
            }
            return null;
        }
        // Xóa các khoảng trắng thừa
        $lat = trim($lat);
        $lon = trim($lon);
        return "https://www.google.com/maps?q={$lat},{$lon}";
    }
}
