<?php
/**
 * card_image_renderer.php
 * Render thẻ học sinh thành ảnh PNG bằng thư viện GD.
 * 
 * Hàm chính: render_student_card_image($template, $student_data, $base_path)
 * Trả về: đường dẫn web của file ảnh PNG (có cache theo hash).
 */

// Cần Endroid QR Code (đã có qua composer)
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

/**
 * Bảng ánh xạ CSS font-family → file TTF trên Windows.
 */
function get_font_path($fontFamily, $isBold = false, $isItalic = false) {
    $fonts_dir = realpath(__DIR__ . '/../../vendor/mpdf/mpdf/ttfonts/') . DIRECTORY_SEPARATOR;
    
    $style = 'normal';
    if ($isBold && $isItalic) $style = 'bold-italic';
    elseif ($isBold) $style = 'bold';
    elseif ($isItalic) $style = 'italic';

    if ($style === 'bold') return $fonts_dir . 'DejaVuSans-Bold.ttf';
    if ($style === 'italic') return $fonts_dir . 'DejaVuSans-Oblique.ttf';
    if ($style === 'bold-italic') return $fonts_dir . 'DejaVuSans-BoldOblique.ttf';
    
    return $fonts_dir . 'DejaVuSans.ttf';
}

/**
 * Parse màu CSS hex (#rrggbb) thành array [r, g, b]
 */
function parse_hex_color($hex) {
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }
    return [
        hexdec(substr($hex, 0, 2)),
        hexdec(substr($hex, 2, 2)),
        hexdec(substr($hex, 4, 2)),
    ];
}

/**
 * Tính hash dùng làm "vân tay" của thẻ.
 * Hash thay đổi khi template hoặc dữ liệu HS thay đổi → ảnh sẽ được render lại.
 */
function compute_card_hash($template_json, $student) {
    $data = $template_json 
        . ($student['ho_dem'] ?? '') 
        . ($student['ten'] ?? '')
        . ($student['ten_lop'] ?? '')
        . ($student['ngay_sinh'] ?? '')
        . ($student['ma_hoc_sinh'] ?? '')
        . ($student['nien_khoa'] ?? '')
        . ($student['anh_the'] ?? '')
        . ($student['updated_at'] ?? '');
    return substr(md5($data), 0, 10);
}

/**
 * Load ảnh từ file (hỗ trợ PNG, JPG, GIF, WebP)
 */
function load_image_from_file($filepath) {
    if (!file_exists($filepath)) return false;
    
    $info = getimagesize($filepath);
    if (!$info) return false;
    
    switch ($info[2]) {
        case IMAGETYPE_PNG:  return imagecreatefrompng($filepath);
        case IMAGETYPE_JPEG: return imagecreatefromjpeg($filepath);
        case IMAGETYPE_GIF:  return imagecreatefromgif($filepath);
        case IMAGETYPE_WEBP: return imagecreatefromwebp($filepath);
        default: return false;
    }
}

/**
 * HÀM CHÍNH: Render thẻ học sinh thành ảnh PNG.
 * 
 * @param array  $template      Template đã parse từ JSON (chứa background, elements)
 * @param string $template_json Chuỗi JSON gốc (dùng tính hash)
 * @param array  $student       Dữ liệu học sinh từ DB
 * @param string $base_path     Đường dẫn gốc của project (e.g. E:\VPS\htdocs\thidua)
 * @return string|null          Đường dẫn web của file ảnh, hoặc null nếu lỗi
 */
function render_student_card_image($template, $template_json, $student, $base_path) {
    // --- 1. Tính Hash & Kiểm tra Cache ---
    $student_id = $student['id'] ?? 0;
    $hash = compute_card_hash($template_json, $student);
    $cache_dir = $base_path . '/public/assets/card_cache';
    $filename = "the_{$student_id}_{$hash}.png";
    $filepath = $cache_dir . '/' . $filename;
    $web_path = '/thidua/public/assets/card_cache/' . $filename;
    
    // Nếu file đã tồn tại → trả URL ngay (0 xử lý)
    if (file_exists($filepath)) {
        return $web_path;
    }
    
    // Tạo thư mục cache nếu chưa có
    if (!is_dir($cache_dir)) {
        mkdir($cache_dir, 0755, true);
    }
    
    // Xóa ảnh cache cũ của học sinh này
    $old_files = glob($cache_dir . "/the_{$student_id}_*.png");
    foreach ($old_files as $old) {
        @unlink($old);
    }
    
    // --- 2. Tạo Canvas 321x204 (85mm x 54mm chuẩn như trang in) ---
    // Để thẻ hiển thị giống hệt trang in, ta phải dùng chung kích thước 321x204 
    // vì người dùng đã căn chỉnh để in đẹp trên khổ này
    $canvas_w = 321;
    $canvas_h = 204;
    $canvas = imagecreatetruecolor($canvas_w, $canvas_h);
    
    // Bật anti-aliasing
    imageantialias($canvas, true);
    
    // Tô nền trắng mặc định
    $white = imagecolorallocate($canvas, 255, 255, 255);
    imagefilledrectangle($canvas, 0, 0, $canvas_w - 1, $canvas_h - 1, $white);
    
    // --- 3. Vẽ Background (Phôi thẻ) ---
    $bg_path = $template['background'] ?? '';
    if ($bg_path) {
        // Chuyển đường dẫn web → đường dẫn file thực
        $bg_file = $base_path . str_replace('/thidua/', '/', $bg_path);
        $bg_file = str_replace('/', DIRECTORY_SEPARATOR, $bg_file);
        
        $bg_img = load_image_from_file($bg_file);
        if ($bg_img) {
            // Scale background vừa khít canvas (background-size: 100% 100% giống trang in)
            imagecopyresampled(
                $canvas, $bg_img,
                0, 0, 0, 0,
                $canvas_w, $canvas_h,
                imagesx($bg_img), imagesy($bg_img)
            );
            imagedestroy($bg_img);
        }
    }
    
    // --- 4. Vẽ từng Element ---
    $elements = $template['elements'] ?? [];
    if (!is_array($elements)) $elements = [];
    
    foreach ($elements as $el) {
        // --- BẮT ĐẦU VẼ CÁC THÀNH PHẦN ---
        // Thêm offset để tinh chỉnh nhích lên/xuống/trái/phải
        $y_offset = 1; // Lên 1px (giảm từ 2 xuống 1 theo yêu cầu "nhích lên 0.5px nữa")
        $x_offset = 0.5; // Sang phải 0.5px
        
        $type = $el['type'] ?? '';
        
        // Điều chỉnh riêng cho văn bản tùy chỉnh (custom-text) xích xuống 1 xíu (khoảng 1px ~ 0.5px theo mắt nhìn)
        if (strpos($type, 'custom-text') === 0) {
            $y_offset += 1;
        }

        $x = (int)round(($el['x'] ?? 0) + $x_offset);
        $y = (int)round(($el['y'] ?? 0) + $y_offset);
        $w = (int)($el['width'] ?? 0);
        $h = (int)($el['height'] ?? 0);
        
        // --- 4a. Ảnh thẻ (Avatar) ---
        if ($type === 'anh_the') {
            $anh_file = '';
            if (!empty($student['anh_the'])) {
                $anh_file = $base_path . '/public/assets/anh_the/' . $student['anh_the'];
            }
            if (!$anh_file || !file_exists($anh_file)) {
                $anh_file = $base_path . '/public/assets/avatar_mac_dinh.png';
            }
            
            if (file_exists($anh_file)) {
                $avatar = load_image_from_file($anh_file);
                if ($avatar) {
                    $aw = $w ?: 85;
                    $ah = $h ?: 113;
                    imagecopyresampled(
                        $canvas, $avatar,
                        $x, $y, 0, 0,
                        $aw, $ah,
                        imagesx($avatar), imagesy($avatar)
                    );
                    imagedestroy($avatar);
                }
            }
            continue;
        }
        
        // --- 4b. QR Code ---
        if ($type === 'qr_code') {
            $qr_data = $student['ma_hoc_sinh'] ?? '';
            if ($qr_data) {
                try {
                    $qr_result = Builder::create()
                        ->writer(new PngWriter())
                        ->data($qr_data)
                        ->size($w ?: 60)
                        ->margin(0)
                        ->build();
                    
                    // Lưu QR tạm vào bộ nhớ
                    $qr_tmp = tempnam(sys_get_temp_dir(), 'qr_');
                    file_put_contents($qr_tmp, $qr_result->getString());
                    
                    $qr_img = imagecreatefrompng($qr_tmp);
                    if ($qr_img) {
                        $qw = $w ?: 60;
                        $qh = $h ?: 60;
                        imagecopyresampled(
                            $canvas, $qr_img,
                            $x, $y, 0, 0,
                            $qw, $qh,
                            imagesx($qr_img), imagesy($qr_img)
                        );
                        imagedestroy($qr_img);
                    }
                    @unlink($qr_tmp);
                } catch (Exception $e) {
                    // Bỏ qua nếu QR bị lỗi
                }
            }
            continue;
        }
        
        // --- 4c. Text Elements (ho_ten, lop, ngay_sinh, nien_khoa, ma_hoc_sinh, custom-text) ---
        $content = '';
        switch ($type) {
            case 'ho_ten':
                $content = mb_strtoupper(trim(($student['ho_dem'] ?? '') . ' ' . ($student['ten'] ?? '')), 'UTF-8');
                break;
            case 'lop':
                $content = mb_strtoupper($student['ten_lop'] ?? '', 'UTF-8');
                break;
            case 'nien_khoa':
                $content = mb_strtoupper($student['nien_khoa'] ?? '', 'UTF-8');
                break;
            case 'ngay_sinh':
                $ns = trim($student['ngay_sinh'] ?? '');
                if ($ns) {
                    $date_obj = DateTime::createFromFormat('Y-m-d', $ns) ?: DateTime::createFromFormat('d/m/Y', $ns);
                    $content = $date_obj ? $date_obj->format('d/m/Y') : $ns;
                }
                break;
            case 'ma_hoc_sinh':
                $content = mb_strtoupper($student['ma_hoc_sinh'] ?? '', 'UTF-8');
                break;
            case 'custom-text':
                $content = mb_strtoupper($el['text'] ?? '', 'UTF-8');
                break;
            default:
                continue 2; // Bỏ qua type không xác định
        }
        
        if ($content === '') continue;
        
        // Xử lý cỡ chữ
        $fontSize = (float)($el['fontSize'] ?? 12);
        $isBold = !empty($el['isBold']);
        $isItalic = !empty($el['isItalic']);
        $fontFamily = $el['fontFamily'] ?? 'Arial, sans-serif';
        $color = $el['color'] ?? '#000000';
        $textAlign = $el['textAlign'] ?? 'left';
        
        // Logic cỡ chữ động cho Họ tên
        if ($type === 'ho_ten' && !empty($el['dynamicSize']) && !empty($el['sizeRules']) && is_array($el['sizeRules'])) {
            $nameLen = mb_strlen($content, 'UTF-8');
            $rules = $el['sizeRules'];
            usort($rules, function($a, $b) { return ($a['maxChars'] ?? 0) <=> ($b['maxChars'] ?? 0); });
            $ruleApplied = false;
            foreach ($rules as $rule) {
                if ($nameLen <= ($rule['maxChars'] ?? 0)) {
                    $fontSize = (float)($rule['fontSize'] ?? $fontSize);
                    $ruleApplied = true;
                    break;
                }
            }
            if (!$ruleApplied && count($rules) > 0) {
                $fontSize = (float)(end($rules)['fontSize'] ?? $fontSize);
            }
        }
        
        // Lấy file font TTF
        $fontFile = get_font_path($fontFamily, $isBold, $isItalic);
        
        // Parse màu
        list($r, $g, $b) = parse_hex_color($color);
        $gdColor = imagecolorallocate($canvas, $r, $g, $b);
        
        // --- QUAN TRỌNG: SỬA LỖI SIZE CHỮ ---
        // GD imagettftext nhận kích thước theo Point (pt), trong khi CSS dùng Pixel (px)
        // 1 pt = 1.333 px -> point_size = pixel_size * 0.75
        $gdFontSize = $fontSize * 0.75;
        
        // Tính toán vị trí text
        // GD dùng baseline cho y, CSS dùng top → cần bù lên
        // LƯU Ý QUAN TRỌNG: Để tất cả các text cùng cỡ chữ có chung một đường baseline ngang nhau,
        // ta không tính ascent dựa trên nội dung text thực tế (vì "2025" sẽ thấp hơn "18/01/2006" do dấu "/").
        // Thay vào đó, ta dùng chuỗi tham chiếu chuẩn "Wg/" để lấy chiều cao ascent tối đa.
        $ref_bbox = imagettfbbox($gdFontSize, 0, $fontFile, "Wg/");
        $ascent = abs($ref_bbox[7]); // Khoảng cách từ baseline lên đỉnh chữ chuẩn
        
        $bbox = imagettfbbox($gdFontSize, 0, $fontFile, $content);
        $textWidth = abs($bbox[2] - $bbox[0]);
        
        $draw_x = $x;
        // Thêm một chút padding dọc mô phỏng line-height của trình duyệt (khoảng 10-15%)
        // Nhích xuống 0.5px theo yêu cầu (từ +1 lên +1.5)
        $draw_y = $y + $ascent + ($fontSize * 0.15) + 1.5; 
        
        // Căn lề
        if ($w > 0) {
            if ($textAlign === 'center') {
                $draw_x = $x + (int)(($w - $textWidth) / 2);
            } elseif ($textAlign === 'right') {
                $draw_x = $x + $w - $textWidth;
            }
        }
        
        // Vẽ text
        imagettftext($canvas, $gdFontSize, 0, $draw_x, $draw_y, $gdColor, $fontFile, $content);
    }
    
    // --- 5. Lưu file PNG ---
    imagepng($canvas, $filepath, 6); // Chất lượng nén 6/9 (cân bằng size/quality)
    imagedestroy($canvas);
    
    return file_exists($filepath) ? $web_path : null;
}
