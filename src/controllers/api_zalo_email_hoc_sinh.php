<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('GET, POST, OPTIONS');
zalo_handle_options();

// File: src/controllers/api_zalo_email_hoc_sinh.php
require_once __DIR__ . '/../../config/microsoft.php';
$payload = zalo_authenticate_request();
$student_id = $payload['student_id'];

try {
    $db = get_db_connection();
    
    // Kiểm tra cài đặt
    $allow_request = $db->query("SELECT setting_value FROM he_thong_cai_dat WHERE setting_key = 'allow_email_request'")->fetchColumn();
    $is_allowed = ($allow_request === '1');

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $db->prepare("SELECT email, mat_khau_tam, trang_thai, error_message FROM email_hoc_sinh WHERE hoc_sinh_id = ?");
        $stmt->execute([$student_id]);
        $email_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($email_data) {
            echo json_encode([
                'success' => true,
                'allow_request' => $is_allowed,
                'data' => [
                    'trang_thai' => $email_data['trang_thai'],
                    'email' => $email_data['email'],
                    'mat_khau' => $email_data['mat_khau_tam'],
                    'error_message' => $email_data['error_message']
                ]
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'allow_request' => $is_allowed,
                'data' => null
            ]);
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!$is_allowed) {
            echo json_encode(['success' => false, 'message' => 'Chức năng đăng ký đang tạm khóa']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['action']) && $input['action'] === 'request') {
            // Kiểm tra xem đã đăng ký chưa
            $stmt = $db->prepare("SELECT id FROM email_hoc_sinh WHERE hoc_sinh_id = ?");
            $stmt->execute([$student_id]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Bạn đã gửi yêu cầu trước đó.']);
                exit;
            }

            // Lấy thông tin học sinh để kiểm tra
            $stmt = $db->prepare("SELECT email, sdt, anh_the, ho_dem, ten, ma_hoc_sinh, nien_khoa FROM hoc_sinh WHERE id = ?");
            $stmt->execute([$student_id]);
            $hs = $stmt->fetch(PDO::FETCH_ASSOC);

            // Kiểm tra điều kiện bắt buộc
            if (!$hs['email'] || trim($hs['email']) === '') {
                echo json_encode(['success' => false, 'message' => 'Vui lòng cập nhật Email cá nhân trong phần Thông tin trước khi đăng ký.']);
                exit;
            }
            if (!$hs['sdt'] || trim($hs['sdt']) === '') {
                echo json_encode(['success' => false, 'message' => 'Vui lòng cập nhật Số điện thoại trong phần Thông tin trước khi đăng ký.']);
                exit;
            }
            if (!$hs['anh_the'] || trim($hs['anh_the']) === '') {
                echo json_encode(['success' => false, 'message' => 'Vui lòng cập nhật Ảnh thẻ (avatar) trước khi đăng ký.']);
                exit;
            }

            $ho_ten = $hs ? trim($hs['ho_dem'] . ' ' . $hs['ten']) : "ID $student_id";

            // TẠO TÀI KHOẢN MICROSOFT 365 TRỰC TIẾP
            $token = getMsGraphToken();
            if (!$token) {
                echo json_encode(['success' => false, 'message' => 'Lỗi xác thực hệ thống Microsoft. Vui lòng liên hệ Admin.']);
                exit;
            }

            $nien_khoa = str_replace(' ', '', $hs['nien_khoa']);
            $nk_parts = explode('-', $nien_khoa);
            $prefix = (isset($nk_parts[0]) && isset($nk_parts[1])) ? substr($nk_parts[0], -2) . substr($nk_parts[1], -2) : '9999';
            $ten = getFirstName($ho_ten);
            
            $userPrincipalName = $prefix . "." . $hs['ma_hoc_sinh'] . "." . $ten . "@" . MS_DOMAIN;
            $password = generateRandomPassword();

            $userData = [
                'accountEnabled' => true,
                'displayName' => $ho_ten,
                'mailNickname' => explode('@', $userPrincipalName)[0],
                'userPrincipalName' => $userPrincipalName,
                'passwordProfile' => [
                    'forceChangePasswordNextSignIn' => true,
                    'password' => $password
                ],
                'usageLocation' => 'VN'
            ];

            $ch = curl_init('https://graph.microsoft.com/v1.0/users');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $resData = json_decode($response, true);
            
            if ($httpcode == 201 || $httpcode == 200) {
                $msUserId = $resData['id'];

                // Gán license "Office 365 A1 for students"
                $licenseData = [
                    'addLicenses' => [
                        [
                            'disabledPlans' => [],
                            'skuId' => '314c4481-f395-4525-be8b-2ec4bb1e9d91'
                        ]
                    ],
                    'removeLicenses' => []
                ];

                $chLic = curl_init("https://graph.microsoft.com/v1.0/users/$msUserId/assignLicense");
                curl_setopt($chLic, CURLOPT_HTTPHEADER, [
                    'Authorization: Bearer ' . $token,
                    'Content-Type: application/json'
                ]);
                curl_setopt($chLic, CURLOPT_POST, true);
                curl_setopt($chLic, CURLOPT_POSTFIELDS, json_encode($licenseData));
                curl_setopt($chLic, CURLOPT_RETURNTRANSFER, true);
                curl_exec($chLic);
                curl_close($chLic);

                // Tự động upload ảnh thẻ (avatar) lên Microsoft 365
                uploadMsUserAvatar($msUserId, $token, $hs['anh_the'] ?? '');

                // Lưu DB với trạng thái đã cấp
                $insert = $db->prepare("INSERT INTO email_hoc_sinh (hoc_sinh_id, email, mat_khau_tam, trang_thai, thoi_gian_cap) VALUES (?, ?, ?, 'da_cap', NOW())");
                $insert->execute([$student_id, $userPrincipalName, $password]);

                // Gửi thông báo cho học sinh (trên web/app)
                $tieu_de_hs = "Tài khoản Email của bạn đã được tạo";
                $noi_dung_hs = "Tài khoản Microsoft 365 của bạn là: $userPrincipalName. Mật khẩu: $password. Vui lòng đăng nhập tại https://outlook.cloud.microsoft/c3binhson.edu.vn để đổi mật khẩu.";
                create_student_notification($db, $student_id, $tieu_de_hs, $noi_dung_hs, 'he_thong');

                // Gửi thông báo về Email cá nhân (Gmail) của học sinh
                if (!empty($hs['email'])) {
                    if (file_exists(__DIR__ . '/../lib/helpers.php')) {
                        require_once __DIR__ . '/../lib/helpers.php';
                        if (function_exists('send_email_via_api_batch')) {
                            $subject = "Tài khoản Email Edu của bạn đã được cấp thành công";
                            $body = "<p>Chào <b>$ho_ten</b>,</p>
                                     <p>Tài khoản Microsoft 365 (@c3binhson.edu.vn) của bạn đã được khởi tạo thành công.</p>
                                     <p><b>Email:</b> $userPrincipalName</p>
                                     <p><b>Mật khẩu tạm:</b> $password</p>
                                     <p>Vui lòng truy cập <a href='https://outlook.cloud.microsoft/c3binhson.edu.vn'>https://outlook.cloud.microsoft/c3binhson.edu.vn</a> để đăng nhập và đổi mật khẩu.</p>";
                            send_email_via_api_batch([['to' => $hs['email'], 'subject' => $subject, 'html' => $body]]);
                        }
                    }
                }

                // Gửi thông báo cho Admin
                $noi_dung_admin = "Học sinh $ho_ten vừa gửi yêu cầu cấp email edu.";
                $db->prepare("INSERT INTO thong_bao (loai_thong_bao, id_lien_quan, noi_dung, thoi_gian, da_xem) VALUES ('cap_email_edu', ?, ?, NOW(), 0)")
                   ->execute([$student_id, $noi_dung_admin]);

                echo json_encode([
                    'success' => true,
                    'message' => 'Đăng ký thành công! Email của bạn đã được cấp.',
                    'data' => [
                        'trang_thai' => 'da_cap',
                        'email' => $userPrincipalName,
                        'mat_khau' => $password
                    ]
                ]);
            } else {
                $err = $resData['error']['message'] ?? 'Lỗi không xác định từ Microsoft';
                
                // Nếu tài khoản đã tồn tại trên Microsoft 365 -> tự động đồng bộ liên kết
                if (stripos($err, 'already exists') !== false || stripos($err, 'userPrincipalName') !== false) {
                    $chEx = curl_init('https://graph.microsoft.com/v1.0/users/' . urlencode($userPrincipalName) . '?$select=id,displayName');
                    curl_setopt($chEx, CURLOPT_HTTPHEADER, [
                        'Authorization: Bearer ' . $token,
                        'Content-Type: application/json'
                    ]);
                    curl_setopt($chEx, CURLOPT_RETURNTRANSFER, true);
                    $exRes = curl_exec($chEx);
                    $exCode = curl_getinfo($chEx, CURLINFO_HTTP_CODE);
                    curl_close($chEx);

                    if ($exCode == 200) {
                        $exData = json_decode($exRes, true);
                        $msUserId = $exData['id'] ?? null;

                        if ($msUserId) {
                            $licenseData = [
                                'addLicenses' => [['disabledPlans' => [], 'skuId' => '314c4481-f395-4525-be8b-2ec4bb1e9d91']],
                                'removeLicenses' => []
                            ];
                            $chLic = curl_init("https://graph.microsoft.com/v1.0/users/$msUserId/assignLicense");
                            curl_setopt($chLic, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token, 'Content-Type: application/json']);
                            curl_setopt($chLic, CURLOPT_POST, true);
                            curl_setopt($chLic, CURLOPT_POSTFIELDS, json_encode($licenseData));
                            curl_setopt($chLic, CURLOPT_RETURNTRANSFER, true);
                            curl_exec($chLic);
                            curl_close($chLic);

                            uploadMsUserAvatar($msUserId, $token, $hs['anh_the'] ?? '');
                        }

                        // Lưu DB
                        $insert = $db->prepare("INSERT INTO email_hoc_sinh (hoc_sinh_id, email, mat_khau_tam, trang_thai, thoi_gian_cap) 
                                                VALUES (?, ?, 'BS@2026!Edu', 'da_cap', NOW()) 
                                                ON DUPLICATE KEY UPDATE email = VALUES(email), trang_thai = 'da_cap', thoi_gian_cap = NOW(), error_message = NULL");
                        $insert->execute([$student_id, $userPrincipalName]);

                        echo json_encode([
                            'success' => true,
                            'message' => 'Tài khoản Email Edu của bạn đã tồn tại và được liên kết thành công!',
                            'data' => [
                                'trang_thai' => 'da_cap',
                                'email' => $userPrincipalName,
                                'mat_khau' => 'Tài khoản đã được khởi tạo trước đó. Nếu quên mật khẩu, vui lòng liên hệ Admin.'
                            ]
                        ]);
                        exit;
                    }
                }

                echo json_encode(['success' => false, 'message' => "Lỗi tạo tài khoản: $err"]);
            }
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
            exit;
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    zalo_api_error('Lỗi hệ thống, vui lòng thử lại sau.', 500, $e);
}
