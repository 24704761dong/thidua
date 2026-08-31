<?php
// 1. KHỞI TẠO SESSION (GIỮ NGUYÊN)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. HEADER ẢNH (GIỮ NGUYÊN)
header('Content-Type: image/png');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

// 3. CẤU HÌNH KÍCH THƯỚC & ĐƯỜNG DẪN
$W = 160; // Kích thước chuẩn
$H = 55;
$LEN = 5;

// Đường dẫn font chữ (Giữ nguyên hoặc trỏ về font đẹp hơn nếu có)
$FONT_MAIN = __DIR__ . '/../../../public/assets/fonts/arial.ttf'; 
// Bỏ nền Tết, dùng nền sáng trung tính
$BG_IMAGE  = null;

$FONT_FILE = is_file($FONT_MAIN) ? $FONT_MAIN : null;
$HAS_TTF   = !empty($FONT_FILE);

// 4. SINH MÃ CAPTCHA (GIỮ NGUYÊN LOGIC CŨ)
// Loại bỏ số 0, 1 để tránh nhầm lẫn nếu thích, hoặc giữ nguyên
$CHARSET = '0123456789'; 
$captcha_text = '';
for ($i = 0; $i < $LEN; $i++) {
    $captcha_text .= $CHARSET[random_int(0, strlen($CHARSET) - 1)];
}

// QUAN TRỌNG: Giữ nguyên tên key session này để file kiểm tra không bị lỗi
$_SESSION['captcha_text']    = $captcha_text;
$_SESSION['captcha_expires'] = time() + 180;

// 5. TẠO ẢNH & NỀN SÁNG
$im = imagecreatetruecolor($W, $H);
$bg_light = imagecolorallocate($im, 245, 247, 249); // #f5f7f9
$bg_acc   = imagecolorallocate($im, 226, 232, 240); // viền nhẹ
imagefill($im, 0, 0, $bg_light);
// viền mỏng phía trên để dịu mắt
imagefilledrectangle($im, 0, 0, $W, 4, $bg_acc);

// 6. MÀU SẮC CHỮ (THEME MỚI)
$text_color = imagecolorallocate($im, 37, 99, 235);      // xanh chủ đạo
$shadow_color = imagecolorallocate($im, 226, 232, 240);  // bóng sáng

// 7. VẼ CHỮ LÊN ẢNH
$x = 20; // Vị trí bắt đầu x
$baseY = (int)($H * 0.7); // Vị trí dòng kẻ chữ

for ($i = 0; $i < strlen($captcha_text); $i++) {
    $angle = random_int(-10, 10); // Góc xoay nhẹ
    $size  = 22; // Cỡ chữ to hơn chút

    if ($HAS_TTF) {
        // Vẽ bóng nhẹ trước (lệch 2px)
        imagettftext($im, $size, $angle, $x + 2, $baseY + 2, $shadow_color, $FONT_FILE, $captcha_text[$i]);
        // Vẽ chữ xanh đậm
        imagettftext($im, $size, $angle, $x, $baseY, $text_color, $FONT_FILE, $captcha_text[$i]);
    } else {
        // Fallback nếu không có font (dùng font hệ thống số 5)
        imagestring($im, 5, $x + 1, $baseY - 20 + 1, $captcha_text[$i], $shadow_color);
        imagestring($im, 5, $x, $baseY - 20, $captcha_text[$i], $text_color);
    }
    $x += 25; // Khoảng cách giữa các chữ
}

// 8. THÊM NHIỄU NHẸ (XÁM TRUNG TÍNH)
for ($i = 0; $i < 30; $i++) {
    $noise_color = imagecolorallocatealpha($im, 148, 163, 184, 90);
    imagefilledellipse($im, random_int(0, $W), random_int(0, $H), 2, 2, $noise_color);
}

// 9. XUẤT ẢNH
imagepng($im);
imagedestroy($im);
exit;
?>