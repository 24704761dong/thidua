<?php
// File: src/controllers/send_birthday_wishes.php
// Gửi mail chúc mừng sinh nhật — ghi lịch sử vào bảng nhat_ky_sinh_nhat

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// === KHỞI TẠO & HIỂN THỊ LỖI ===
require_once __DIR__ . '/../../config/bootstrap.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

set_time_limit(0);
if (PHP_SAPI !== 'cli') {
    echo "<pre>";
}

// === NẠP THƯ VIỆN & CẤU HÌNH ===
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';
// (Tuỳ chọn) Nếu bạn muốn dùng các helper khác: require_once __DIR__ . '/../lib/helpers.php';
date_default_timezone_set('Asia/Ho_Chi_Minh');

/** Helper nhỏ: escape HTML an toàn */
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

/** Helper: chuyển chuỗi ngày sinh về định dạng dd/mm (hỗ trợ dd/mm/YYYY và YYYY-mm-dd) */
function birthday_to_ddmm(?string $s): ?string {
    $s = trim((string)$s);
    if ($s === '') return null;
    if (strpos($s, '/') !== false) {
        $p = explode('/', $s);
        if (count($p) === 3) return sprintf('%02d/%02d', (int)$p[0], (int)$p[1]);
    } elseif (strpos($s, '-') !== false) {
        $p = explode('-', $s);
        if (count($p) === 3) return sprintf('%02d/%02d', (int)$p[2], (int)$p[1]);
    }
    return null;
}

/**
 * Email chúc mừng sinh nhật – header tách riêng HS/GVCN
 * @param string $recipient_name
 * @param 'hoc_sinh'|'giao_vien' $type
 * @return string HTML email
 */
function generate_beautiful_birthday_email(string $recipient_name, string $type): string {
    $name = htmlspecialchars($recipient_name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $email_id = date('YmdHis');
    $logo_url = "https://c3binhson.edu.vn/thidua/public/assets/img/22logoapp.png";

    if ($type === 'hoc_sinh') {
        // ======= HỌC SINH =======
        $ribbon_text   = "🎉 Chúc Mừng Sinh Nhật 🎂";
        $hero_title    = "Chúc mừng sinh nhật em 🎈";
        $hero_subtitle = "Cảm ơn em đã là một phần của <strong>Trường THPT Bình Sơn</strong>.";
        $salute        = "Thân gửi em <strong>{$name}</strong>,";
        $lead          = "Nhân dịp sinh nhật, Ban Quản Trị QLĐG xin gửi đến em những lời chúc tốt đẹp nhất.";
        $wish          = "Chúc em một ngày sinh nhật thật <strong>vui vẻ</strong>, <strong>hạnh phúc</strong> bên gia đình và bạn bè, và một tuổi mới <strong>học giỏi – năng động – nhiều trải nghiệm đáng nhớ</strong>!";
        $accent_color  = "#0ea5e9"; // xanh sáng tươi
    } else {
        // ======= GIÁO VIÊN / GVCN =======
        $ribbon_text   = "🎂 Kính chúc mừng sinh nhật Quý Thầy/Cô 🎉";
        $hero_title    = "Trân trọng tri ân sự đồng hành của <strong>Thầy/Cô</strong> cùng <strong>Trường THPT Bình Sơn</strong>.";
        $hero_subtitle = "Mến chúc ngày sinh nhật thật nhiều niềm vui và ý nghĩa!";
        $salute        = "Kính gửi Quý Thầy/Cô <strong>{$name}</strong>,";
        $lead          = "Nhân dịp sinh nhật, tập thể Trường THPT Bình Sơn xin gửi đến Quý Thầy/Cô những lời chúc mừng chân thành nhất.";
        $wish          = "Kính chúc Quý Thầy/Cô luôn <strong>dồi dào sức khỏe</strong>, <strong>hạnh phúc</strong> và gặt hái thêm nhiều thành công trong sự nghiệp trồng người.";
        $accent_color  = "#16a34a"; // xanh lá nhẹ, trang nhã
    }

    return <<<HTML
    <div style="font-family: Inter, Arial, sans-serif; max-width: 640px; margin: auto; background: #f4f7f9; padding: 28px 20px; border-radius: 12px; border: 1px solid #e5e7eb;">
        <!-- Header -->
        <div style="text-align:center; margin-bottom: 24px;">
            <img src="{$logo_url}" alt="Logo THPT Bình Sơn" style="height: 60px; max-width: 180px; object-fit: contain; margin-bottom: 12px;">
            <div style="display:inline-block; background: #fff; color: {$accent_color}; border: 1px solid #e5e7eb; border-radius: 999px; padding: 8px 16px; font-weight: 600; margin-bottom: 10px;">
                {$ribbon_text}
            </div>
            <h2 style="color: #111827; margin: 8px 0 4px; font-size: 22px;">{$hero_title}</h2>
            <p style="font-size: 0.95rem; color: #4b5563; margin: 0;">{$hero_subtitle}</p>
        </div>

        <!-- Nội dung chính -->
        <div style="background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px;">
            <p style="margin: 0 0 10px; font-size: 1rem; color: #111827;">{$salute}</p>
            <p style="margin: 0 0 14px; font-size: 1rem; color: #374151;">{$lead}</p>

            <p style="margin: 10px 0; font-size: 1.05rem; color: #111827; line-height: 1.7;">
                <span style="font-size:1.25rem; opacity:.7; margin-right:6px;">❝</span>
                {$wish}
                <span style="font-size:1.25rem; opacity:.7; margin-left:6px;">❞</span>
            </p>

            <p style="text-align:center; margin:12px 0 14px; font-size:1.3rem;">🎊 🎈 🎉</p>

            <hr style="border:none; border-top:1px dashed #e5e7eb; margin:18px 0;">
            <p style="margin:0 0 8px; font-size:1rem; color:#374151;">
                Cảm ơn bạn đã là một phần của đại gia đình <strong>Trường THPT Bình Sơn</strong>! 🥳
            </p>
            <p style="margin:0; font-size:1rem; color:#374151;">
                Trân trọng,<br><strong>Hệ thống Đánh Giá Thi Đua</strong>
            </p>
        </div>

        <!-- Footer -->
        <p style="font-size: 0.85rem; color: #6b7280; margin-top: 24px; text-align: center;">
            Cần hỗ trợ kỹ thuật? Liên hệ ngay qua:<br>
            <strong>Liên hệ hỗ trợ:</strong>
            <a href="https://zalo.me/0362566146" style="color:#2563eb; text-decoration:none;">Zalo 036.256.6146</a>
        </p>

        <hr style="margin: 20px auto; border: none; border-top: 1px solid #e5e7eb; width: 80%;">
        <p style="text-align: center; font-size: 0.85rem; color: #9ca3af; line-height: 1.6;">
            Trân trọng,<br>
            <span style="font-weight: 600; color: #6b7280;">Hệ thống quản lý thi đua</span><br>
            <span style="font-style: italic; color:#6b7280;">Trường THPT Bình Sơn</span><br>
            <span style="font-size: 0.75rem; color: #cbd5e1;">Email ID: {$email_id}</span>
        </p>
    </div>
    HTML;
}



/** Ghi log vào bảng nhat_ky_sinh_nhat (đúng schema migration #34) */
if (!function_exists('log_birthday_wish')) {
    function log_birthday_wish(PDO $db, ?int $person_id, ?string $person_type, string $status, string $message, ?string $person_name = null, ?string $birthday_date = null): void {
        try {
            $sent_at = (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s');

            // Nếu thiếu tên/ngày sinh thì cố lấy từ DB cho đủ
            if ((!$person_name || !$birthday_date) && $person_id && $person_type) {
                if ($person_type === 'hoc_sinh') {
                    $stm = $db->prepare("SELECT TRIM(COALESCE(ho_dem,'')) AS hd, TRIM(COALESCE(ten,'')) AS t, TRIM(COALESCE(ngay_sinh,'')) AS ns FROM hoc_sinh WHERE id = ?");
                    $stm->execute([$person_id]);
                    if ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
                        $person_name   = $person_name   ?: trim(($row['hd'] ?? '') . ' ' . ($row['t'] ?? ''));
                        $birthday_date = $birthday_date ?: ($row['ns'] ?? '');
                    }
                } elseif ($person_type === 'giao_vien') {
                    $stm = $db->prepare("SELECT TRIM(COALESCE(gvcn_ten,'')) AS ten, TRIM(COALESCE(gvcn_ngay_sinh,'')) AS ns FROM lop_hoc WHERE id = ?");
                    $stm->execute([$person_id]);
                    if ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
                        $person_name   = $person_name   ?: ($row['ten'] ?? '');
                        $birthday_date = $birthday_date ?: ($row['ns'] ?? '');
                    }
                }
            }

            $ins = $db->prepare("
                INSERT INTO nhat_ky_sinh_nhat (person_id, person_type, person_name, birthday_date, status, sent_at, error_message)
                VALUES (:pid, :ptype, :pname, :bday, :st, :sent, :err)
            ");
            $ins->execute([
                ':pid'   => $person_id,
                ':ptype' => $person_type,
                ':pname' => $person_name ?? '',
                ':bday'  => $birthday_date ?? '',
                ':st'    => $status,
                ':sent'  => $sent_at,
                ':err'   => $message,
            ]);
        } catch (Throwable $e) {
            error_log('[nhat_ky_sinh_nhat]['.$status.'] pid='.$person_id.' type='.$person_type.' msg='.$message.' err='.$e->getMessage());
        }
    }
}

echo "=====================================================\n";
echo "=== TIEN TRINH GUI MAIL CHUC MUNG SINH NHAT ===\n";
echo "=====================================================\n";
echo "Thoi gian bat dau: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // Kết nối DB chính
    $db = get_db_connection();

    // Hôm nay (dd/mm)
    $today_ddmm = (new DateTime('today'))->format('d/m');
    echo "[INFO] Dang quet sinh nhat cho ngay: {$today_ddmm}\n";

    // Lấy danh sách có email (lọc ngày sinh trong PHP để hỗ trợ cả 2 định dạng)
    $sql = "
        SELECT 'hoc_sinh' AS doi_tuong, hs.id, TRIM(COALESCE(hs.ho_dem,'')) AS ho_dem, TRIM(COALESCE(hs.ten,'')) AS ten, hs.email, hs.ngay_sinh
        FROM hoc_sinh hs
        WHERE hs.email IS NOT NULL AND hs.email <> ''
        UNION ALL
        SELECT 'giao_vien' AS doi_tuong, lh.id, '' AS ho_dem, TRIM(COALESCE(lh.gvcn_ten,'')) AS ten, lh.gvcn_email AS email, lh.gvcn_ngay_sinh AS ngay_sinh
        FROM lop_hoc lh
        WHERE lh.gvcn_email IS NOT NULL AND lh.gvcn_email <> ''
    ";
    $rows = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    // Lọc đúng sinh nhật hôm nay
    $targets = [];
    foreach ($rows as $r) {
        $ddmm = birthday_to_ddmm($r['ngay_sinh'] ?? '');
        if ($ddmm === $today_ddmm) $targets[] = $r;
    }

    if (empty($targets)) {
        echo "[THONG BAO] Khong tim thay ai co sinh nhat hom nay ({$today_ddmm}).\n";
        log_birthday_wish($db, null, null, 'Không có sinh nhật', 'Hệ thống đã chạy nhưng không tìm thấy ai có sinh nhật hôm nay.');
        echo "\nKet thuc.\n";
        if (PHP_SAPI !== 'cli') echo "</pre>";
        exit();
    }

    echo "[THANH CONG] Da tim thay " . count($targets) . " nguoi co sinh nhat hom nay. Bat dau gui mail...\n\n";

    // Cấu hình mail (giống api_ctv_send_otp.php)
    $mail_config = require __DIR__ . '/../../config/mail_config.php';
    // Kỳ vọng: HOST, USERNAME, PASSWORD, PORT, SENDER_EMAIL, SENDER_NAME

    foreach ($targets as $person) {
        $full_name = trim(trim($person['ho_dem'] ?? '') . ' ' . trim($person['ten'] ?? ''));
        $email_to  = trim($person['email'] ?? '');
        $bday_raw  = trim($person['ngay_sinh'] ?? '');
        $doi_tuong = $person['doi_tuong'] ?? null;
        $pid       = isset($person['id']) ? (int)$person['id'] : null;

        echo "-----------------------------------------------------\n";
        echo "Dang chuan bi gui den [{$doi_tuong}]: {$full_name} ({$email_to})\n";

        if (!filter_var($email_to, FILTER_VALIDATE_EMAIL)) {
            echo "[BO QUA] Email khong hop le: {$email_to}\n";
            log_birthday_wish($db, $pid, $doi_tuong, 'Bỏ qua', "Email không hợp lệ: {$email_to}", $full_name, $bday_raw);
            continue;
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $mail_config['HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $mail_config['USERNAME'];
            $mail->Password   = $mail_config['PASSWORD'];
            $mail->SMTPSecure = $mail_config['USE_TLS'] ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = (int)$mail_config['PORT'];
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom($mail_config['SENDER_EMAIL'], $mail_config['SENDER_NAME']);
            $mail->addAddress($email_to, $full_name);
            $mail->isHTML(true);

            // === NỘI DUNG EMAIL ĐẸP (mới) ===
            if ($doi_tuong === 'hoc_sinh') {
                $mail->Subject = mb_encode_mimeheader("Chúc Mừng Sinh Nhật em " . $full_name, "UTF-8");
                $mail->Body    = generate_beautiful_birthday_email($full_name, 'hoc_sinh');
            } else {
                $mail->Subject = mb_encode_mimeheader("Chúc Mừng Sinh Nhật Thầy/Cô " . $full_name, "UTF-8");
                $mail->Body    = generate_beautiful_birthday_email($full_name, 'giao_vien');
            }

            // AltBody dự phòng (text thuần)
            $mail->AltBody = "Chúc mừng sinh nhật " . $full_name . "!\n"
                           . (($doi_tuong === 'hoc_sinh')
                                ? "Chúc em một ngày thật vui vẻ, hạnh phúc và một tuổi mới học giỏi, nhiều trải nghiệm hay.\n"
                                : "Kính chúc Thầy/Cô dồi dào sức khỏe, hạnh phúc và thêm nhiều thành công trong sự nghiệp trồng người.\n")
                           . "Trân trọng.";

            $mail->send();
            echo "[THANH CONG] Da gui mail thanh cong!\n";
            log_birthday_wish($db, $pid, $doi_tuong, 'Thành công', "Đã gửi mail chúc mừng sinh nhật đến {$email_to}", $full_name, $bday_raw);

        } catch (Exception $e) {
            echo "[THAT BAI] Co loi xay ra khi gui mail: " . $mail->ErrorInfo . "\n";
            log_birthday_wish($db, $pid, $doi_tuong, 'Thất bại', "Lỗi: " . $mail->ErrorInfo, $full_name, $bday_raw);
        }
    }

} catch (PDOException $e) {
    echo "[LOI CSDL] Khong the thuc thi truy van: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "[LOI HE THONG] Mot loi nghiem trong da xay ra: " . $e->getMessage() . "\n";
}

echo "-----------------------------------------------------\n";
echo "\nHoan thanh tien trinh luc: " . date('Y-m-d H:i:s') . "\n";
if (PHP_SAPI !== 'cli') {
    echo "</pre>";
}
