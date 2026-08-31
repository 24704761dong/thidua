<?php
// === BẮT ĐẦU SỬA LỖI TRANG TRẮNG ===
// Bắt đầu bộ đệm để "bắt" mọi lỗi có thể xảy ra
ob_start(); 

// Tắt 'mbstring.func_overload' (nguyên nhân gây lỗi cho thư viện QR Code)
if (function_exists('mb_internal_encoding')) {
    ini_set('mbstring.func_overload', 0);
}
// === KẾT THÚC SỬA LỖI ===


// File: src/controllers/in_the_hoc_sinh_controller.php (Đã nâng cấp logic ngắt trang cho 8 thẻ/trang + in HOA)

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/hoc_sinh_db.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

// Lấy danh sách ID học sinh và ID mẫu thẻ từ form đã gửi lên
// Ưu tiên nhận từ selected_ids (JSON/CSV) để tránh vượt quá max_input_vars
$student_ids = [];
$mau_the_id = $_POST['mau_the_id'] ?? null;

if (!empty($_POST['selected_ids'])) {
    $raw = $_POST['selected_ids'];
    if (is_string($raw)) {
        $decoded = null;
        // Nếu là JSON mảng
        if (strlen($raw) > 0 && $raw[0] === '[') {
            $decoded = json_decode($raw, true);
        }
        // Nếu JSON hỏng hoặc không phải JSON, thử CSV
        if (!is_array($decoded)) {
            $decoded = array_filter(array_map('trim', explode(',', $raw)), fn($x) => $x !== '');
        }
        if (is_array($decoded)) {
            $student_ids = $decoded;
        }
    }
}

// Fallback: nếu selected_ids không có, nhận từ mảng checkbox cũ (student_ids[])
if (empty($student_ids) && !empty($_POST['student_ids']) && is_array($_POST['student_ids'])) {
    $student_ids = $_POST['student_ids'];
}

// Chuẩn hóa: ép kiểu số nguyên, loại bỏ phần tử rỗng, unique
$student_ids = array_values(array_unique(array_filter(array_map(function ($v) {
    // Cho phép chuỗi số, ép về int dương
    $n = (int)preg_replace('/[^0-9]/', '', (string)$v);
    return $n > 0 ? $n : null;
}, $student_ids))));

if (empty($student_ids)) {
    die('Vui lòng chọn ít nhất một học sinh để in thẻ.');
}

$db = get_db_connection();

// Lấy đúng mẫu thẻ mà người dùng đã chọn
try {
    if ($mau_the_id) {
        $stmt = $db->prepare("SELECT * FROM mau_the_hoc_sinh WHERE id = ?");
        $stmt->execute([$mau_the_id]);
    } else {
        $stmt = $db->prepare("SELECT * FROM mau_the_hoc_sinh WHERE is_default = 1 LIMIT 1");
        $stmt->execute();
    }
    
    $mau_the_record = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$mau_the_record) {
        die('Lỗi: Không tìm thấy mẫu thẻ hợp lệ. Vui lòng vào trang Thiết Kế và đặt một mẫu làm mặc định.');
    }

    $mau_the = json_decode($mau_the_record['cau_hinh_json'], true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($mau_the)) {
        $error_detail = json_last_error_msg();
        log_to_file('Card template JSON decode failed in in_the_hoc_sinh_controller.php: ' . $error_detail);
        $mau_the = get_default_card_template();
        $mau_the['__fallback_used'] = true;
    }

    if (empty($mau_the['background'])) {
        $mau_the['background'] = '/thidua/public/assets/phoi_the_mac_dinh.png';
    }

    if (!isset($mau_the['elements']) || !is_array($mau_the['elements'])) {
        $mau_the['elements'] = [];
    }

} catch (PDOException $e) {
    die('Lỗi cơ sở dữ liệu khi lấy mẫu thẻ: ' . $e->getMessage());
}

// Lấy thông tin của các học sinh đã chọn
$danh_sach_hoc_sinh_can_in = get_hoc_sinh_by_ids($db, $student_ids);

// --- BẮT ĐẦU NÂNG CẤP LOGIC TẠO HTML VÀ NGẮT TRANG ---
$cards_per_page = 8; // Đặt số lượng thẻ mỗi trang là 8
$cards_html = '';
$card_count = 0;
$total_cards = count($danh_sach_hoc_sinh_can_in);

foreach ($danh_sach_hoc_sinh_can_in as $hs) {
    // Bắt đầu một trang A4 mới (một container mới) cho thẻ đầu tiên và sau mỗi 8 thẻ
    if ($card_count % $cards_per_page === 0) {
        $cards_html .= '<div class="card-container">';
    }

    $card_content = '';
    if (isset($mau_the['elements']) && is_array($mau_the['elements'])) {
        foreach ($mau_the['elements'] as $id => $el) {
            $style = "left: " . ($el['x'] ?? 0) . "px; top: " . ($el['y'] ?? 0) . "px;";
            $content = '';

            $isTextElement = ($el['type'] === 'custom-text' || in_array($el['type'], ['ho_ten', 'ma_hoc_sinh', 'lop', 'ngay_sinh', 'nien_khoa']));
            
            if ($isTextElement) {
    // Giữ nguyên size mặc định từ JSON
    $fontSize = $el['fontSize'] ?? 12;

    // === LOGIC CỠ CHỮ ĐỘNG CHO HỌ TÊN ===
    if ($el['type'] === 'ho_ten' && !empty($el['dynamicSize']) && !empty($el['sizeRules'])) {
        $ten_day_du = trim($hs['ho_dem'] . ' ' . $hs['ten']);
        $name_length = mb_strlen($ten_day_du, 'UTF-8');

        usort($el['sizeRules'], function($a, $b) {
            return $a['maxChars'] <=> $b['maxChars'];
        });

        $fontSizeApplied = false;
        foreach ($el['sizeRules'] as $rule) {
            if ($name_length <= $rule['maxChars']) {
                $fontSize = $rule['fontSize'];
                $fontSizeApplied = true;
                break;
            }
        }
        if (!$fontSizeApplied && !empty($el['sizeRules'])) {
            $fontSize = end($el['sizeRules'])['fontSize'];
        }
    }
    // === KẾT THÚC LOGIC CỠ CHỮ ĐỘNG ===

    // Áp dụng style như cũ
    $style .= "font-size: " . $fontSize . "px; color: " . ($el['color'] ?? '#000000') . "; font-family: " . ($el['fontFamily'] ?? 'Arial, sans-serif') . ";";
    if ($el['isBold'] ?? false) $style .= 'font-weight: bold;';
    if ($el['isItalic'] ?? false) $style .= 'font-style: italic;';
}


            // Xử lý logic in hoa & ngày sinh
            switch ($el['type']) {
                case 'ho_ten': 
                    $content = mb_strtoupper($hs['ho_dem'] . ' ' . $hs['ten'], 'UTF-8'); 
                    break;
                case 'lop': 
                    $content = mb_strtoupper($hs['ten_lop'], 'UTF-8'); 
                    break;
                case 'nien_khoa': 
                    $content = mb_strtoupper($hs['nien_khoa'] ?? '', 'UTF-8'); 
                    break;
                case 'ngay_sinh':
                    $ngay_sinh_str = trim($hs['ngay_sinh'] ?? '');
                    if (!empty($ngay_sinh_str)) {
                        $date_obj = DateTime::createFromFormat('d/m/Y', $ngay_sinh_str) ?: DateTime::createFromFormat('Y-m-d', $ngay_sinh_str);
                        if ($date_obj) {
                            $content = $date_obj->format('d/m/Y');
                        }
                    }
                    break;
                case 'ma_hoc_sinh': 
                    $content = mb_strtoupper($hs['ma_hoc_sinh'], 'UTF-8'); 
                    break;
                case 'custom-text': 
                    $content = mb_strtoupper($el['text'] ?? '', 'UTF-8'); 
                    break;
            }

            if ($el['type'] === 'anh_the') {
                $anh_the_url = !empty($hs['anh_the']) 
                    ? '/thidua/public/assets/anh_the/' . htmlspecialchars($hs['anh_the']) 
                    : '/thidua/public/assets/avatar_mac_dinh.png';
                $card_content .= "<div class='card-element element-image' style='{$style} width: " . ($el['width'] ?? 85) . "px; height: " . ($el['height'] ?? 113) . "px; background-image: url(\"{$anh_the_url}\");'></div>";
            } elseif ($el['type'] === 'qr_code') {
                $qr_result = Builder::create()->writer(new PngWriter())->data($hs['ma_hoc_sinh'])->build();
                $qr_base64 = $qr_result->getDataUri();
                $card_content .= "<div class='card-element element-qr' style='{$style} width: " . ($el['width'] ?? 60) . "px; height: " . ($el['height'] ?? 60) . "px;'><img src='{$qr_base64}' width='100%' height='100%'></div>";
            // BẮT ĐẦU MÃ MỚI
} elseif ($isTextElement) {
    // Bổ sung style cho chiều rộng và căn lề
    $final_style = $style;
    if (!empty($el['width'])) {
        $final_style .= " width: " . $el['width'] . "px;";
    }
    if (!empty($el['textAlign'])) {
        $final_style .= " text-align: " . $el['textAlign'] . ";";
    }
    $card_content .= "<div class='card-element' style='{$final_style}'>" . htmlspecialchars($content) . "</div>";
}
// KẾT THÚC MÃ MỚI
        }
    }
    
    $cards_html .= "<div class='student-card'>" . $card_content . "</div>";
    $card_count++;

    // Đóng container lại sau mỗi 8 thẻ, hoặc khi đã đến thẻ cuối cùng
    if ($card_count % $cards_per_page === 0 || $card_count === $total_cards) {
        $cards_html .= '</div>';
    }
}
// --- KẾT THÚC NÂNG CẤP ---

require_once __DIR__ . '/../views/in_the_hoc_sinh_layout_a4.php';

// === THÊM DÒNG NÀY Ở CUỐI CÙNG ===
ob_end_flush(); 
?>