<?php
// File: src/controllers/api_admin_email_hoc_sinh.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/microsoft.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'] ?? '', ['admin', 'user'])) {
    if (isset($_GET['action']) && $_GET['action'] == 'export') {
        die('Unauthorized');
    }
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$db = get_db_connection();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Xóa các hàm getMsGraphToken, removeAccentsAndLowercase, getFirstName, generateRandomPassword vì đã chuyển qua microsoft.php

// Logic load list
if ($action === 'load_list') {
    $sql = "SELECT hs.id as hs_id, CONCAT(hs.ho_dem, ' ', hs.ten) AS ho_ten, hs.ngay_sinh, hs.ma_hoc_sinh AS so_cccd, hs.nien_khoa, 
            lh.ten_lop as lop, SUBSTR(lh.ten_lop, 1, 2) as khoi, e.email as email_ca_nhan, e.trang_thai
            FROM hoc_sinh hs
            LEFT JOIN email_hoc_sinh e ON hs.id = e.hoc_sinh_id
            LEFT JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id
            ORDER BY 
                CAST(SUBSTR(lh.ten_lop, 1, 2) AS UNSIGNED) ASC,
                SUBSTR(lh.ten_lop, 3, 1) ASC,
                CAST(SUBSTR(lh.ten_lop, 4) AS UNSIGNED) ASC,
                hs.ten ASC, 
                hs.ho_dem ASC";
            
    $stmt = $db->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = [];
    $stt = 1;
    foreach ($data as $row) {
        $row['stt'] = $stt++;
        if (!$row['trang_thai']) $row['trang_thai'] = null; // chưa đk
        // Format ngay_sinh as dd/mm/yyyy
        if (!empty($row['ngay_sinh'])) {
            $ts = strtotime($row['ngay_sinh']);
            if ($ts !== false) {
                $row['ngay_sinh'] = date('d/m/Y', $ts);
            }
        } else {
            $row['ngay_sinh'] = '';
        }
        $result[] = $row;
    }

    echo json_encode([
        "data" => $result
    ]);
    exit;
}

if ($action === 'toggle_setting') {
    $status = (isset($_POST['status']) && ((string)$_POST['status'] === '1' || $_POST['status'] === 1 || $_POST['status'] === true)) ? '1' : '0';
    $stmt = $db->prepare("
        INSERT INTO he_thong_cai_dat (setting_key, setting_value, nam_hoc_id) 
        VALUES ('allow_email_request', ?, 0) 
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
    ");
    $stmt->execute([$status]);
    
    // Cập nhật tất cả các bản ghi allow_email_request nếu có nhiều nam_hoc_id
    $db->prepare("UPDATE he_thong_cai_dat SET setting_value = ? WHERE setting_key = 'allow_email_request'")->execute([$status]);

    echo json_encode(['success' => true, 'status' => $status]);
    exit;
}

if ($action === 'cap_mail') {
    $hs_id = $_POST['id'] ?? 0;
    
    $stmt = $db->prepare("SELECT ho_dem, ten, ma_hoc_sinh as so_cccd, nien_khoa, anh_the FROM hoc_sinh WHERE id = ?");
    $stmt->execute([$hs_id]);
    $hs = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($hs) {
        $hs['ho_ten'] = trim($hs['ho_dem'] . ' ' . $hs['ten']);
    }

    if (!$hs) {
        echo json_encode(['success' => false, 'message' => 'Học sinh không tồn tại']);
        exit;
    }

    $nien_khoa = str_replace(' ', '', $hs['nien_khoa']); // vd: 2024-2027
    $nk_parts = explode('-', $nien_khoa);
    $prefix = (isset($nk_parts[0]) && isset($nk_parts[1])) ? substr($nk_parts[0], -2) . substr($nk_parts[1], -2) : '9999';
    $ten = getFirstName($hs['ho_ten']);
    
    $userPrincipalName = $prefix . "." . $hs['so_cccd'] . "." . $ten . "@" . MS_DOMAIN;
    $password = generateRandomPassword();

    $token = getMsGraphToken();
    if (!$token) {
        echo json_encode(['success' => false, 'message' => 'Không thể lấy token từ Microsoft']);
        exit;
    }

    // Call Graph API create user
    $userData = [
        'accountEnabled' => true,
        'displayName' => $hs['ho_ten'],
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
        $msUserId = $resData['id']; // Lấy ID của user vừa tạo

        // Gán license "Office 365 A1 for students" (SKU ID: 314c4481-f395-4525-be8b-2ec4bb1e9d91)
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

        // Cập nhật CSDL
        $db->prepare("INSERT INTO email_hoc_sinh (hoc_sinh_id, email, mat_khau_tam, trang_thai, thoi_gian_cap) 
                      VALUES (?, ?, ?, 'da_cap', NOW()) 
                      ON DUPLICATE KEY UPDATE email=?, mat_khau_tam=?, trang_thai='da_cap', thoi_gian_cap=NOW(), error_message=NULL")
           ->execute([$hs_id, $userPrincipalName, $password, $userPrincipalName, $password]);
        
        echo json_encode(['success' => true, 'message' => 'Tạo thành công và đã gán License: ' . $userPrincipalName]);
    } else {
        $err = $resData['error']['message'] ?? 'Lỗi không xác định từ Microsoft';
        
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

                $db->prepare("INSERT INTO email_hoc_sinh (hoc_sinh_id, email, mat_khau_tam, trang_thai, thoi_gian_cap) 
                              VALUES (?, ?, 'BS@2026!Edu', 'da_cap', NOW()) 
                              ON DUPLICATE KEY UPDATE email = VALUES(email), trang_thai = 'da_cap', thoi_gian_cap = NOW(), error_message = NULL")
                   ->execute([$hs_id, $userPrincipalName]);

                echo json_encode(['success' => true, 'message' => 'Tài khoản đã tồn tại trên MS 365, đã liên kết thành công: ' . $userPrincipalName]);
                exit;
            }
        }

        $db->prepare("UPDATE email_hoc_sinh SET error_message = ? WHERE hoc_sinh_id = ?")->execute([$err, $hs_id]);
        echo json_encode(['success' => false, 'message' => $err]);
    }
    exit;
}

if ($action === 'reset_pass') {
    $hs_id = $_POST['id'] ?? 0;
    
    $stmt = $db->prepare("SELECT e.email, hs.email as personal_email, hs.ho_dem, hs.ten FROM email_hoc_sinh e JOIN hoc_sinh hs ON e.hoc_sinh_id = hs.id WHERE e.hoc_sinh_id = ?");
    $stmt->execute([$hs_id]);
    $hs_data = $stmt->fetch(PDO::FETCH_ASSOC);
    $email = $hs_data['email'] ?? null;

    if (!$email) {
        echo json_encode(['success' => false, 'message' => 'Email chưa được cấp']);
        exit;
    }

    $password = generateRandomPassword();
    $token = getMsGraphToken();
    if (!$token) {
        echo json_encode(['success' => false, 'message' => 'Lỗi kết nối Microsoft']);
        exit;
    }

    $userData = [
        'passwordProfile' => [
            'forceChangePasswordNextSignIn' => true,
            'password' => $password
        ]
    ];

    $ch = curl_init('https://graph.microsoft.com/v1.0/users/' . urlencode($email));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode == 204) {
        $db->prepare("UPDATE email_hoc_sinh SET mat_khau_tam = ? WHERE hoc_sinh_id = ?")->execute([$password, $hs_id]);
        
        $ho_ten = trim($hs_data['ho_dem'] . ' ' . $hs_data['ten']);
        $tieu_de = "Mật khẩu Email Edu đã được đặt lại";
        $noi_dung = "Tài khoản $email đã được cấp lại mật khẩu mới là: $password. Vui lòng đăng nhập tại https://outlook.cloud.microsoft để đổi mật khẩu.";
        create_student_notification($db, $hs_id, $tieu_de, $noi_dung, 'he_thong');
           
        if (!empty($hs_data['personal_email'])) {
            if (file_exists(__DIR__ . '/../lib/helpers.php')) {
                require_once __DIR__ . '/../lib/helpers.php';
                if (function_exists('send_email_via_api_batch')) {
                    $body = "<p>Chào <b>$ho_ten</b>,</p><p>Tài khoản Microsoft 365 của bạn ($email) đã được admin đặt lại mật khẩu thành công.</p><p><b>Mật khẩu mới:</b> $password</p>";
                    send_email_via_api_batch([['to' => $hs_data['personal_email'], 'subject' => $tieu_de, 'html' => $body]]);
                }
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Khôi phục thành công']);
    } else {
        $resData = json_decode($response, true);
        $errMsg = $resData['error']['message'] ?? 'Lỗi reset pass';
        if (strpos($errMsg, 'Insufficient privileges') !== false) {
            $errMsg = 'Ứng dụng Azure chưa được gán vai trò "User Administrator" (Quản trị viên người dùng) trong Microsoft Entra ID để đổi mật khẩu qua API.';
        }
        echo json_encode(['success' => false, 'message' => $errMsg]);
    }
    exit;
}

if ($action === 'khoa_mail') {
    $hs_id = $_POST['id'] ?? 0;
    
    $stmt = $db->prepare("SELECT e.email, hs.email as personal_email, hs.ho_dem, hs.ten FROM email_hoc_sinh e JOIN hoc_sinh hs ON e.hoc_sinh_id = hs.id WHERE e.hoc_sinh_id = ?");
    $stmt->execute([$hs_id]);
    $hs_data = $stmt->fetch(PDO::FETCH_ASSOC);
    $email = $hs_data['email'] ?? null;

    $token = getMsGraphToken();
    $userData = ['accountEnabled' => false];

    $ch = curl_init('https://graph.microsoft.com/v1.0/users/' . urlencode($email));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token, 'Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode == 204) {
        $db->prepare("UPDATE email_hoc_sinh SET trang_thai = 'da_khoa' WHERE hoc_sinh_id = ?")->execute([$hs_id]);
        
        $ho_ten = trim($hs_data['ho_dem'] . ' ' . $hs_data['ten']);
        $tieu_de = "Tài khoản Email Edu đã bị khóa";
        $noi_dung = "Tài khoản Microsoft 365 của bạn ($email) đã bị khóa bởi quản trị viên. Vui lòng liên hệ Admin nếu có sai sót.";
        create_student_notification($db, $hs_id, $tieu_de, $noi_dung, 'he_thong');
           
        if (!empty($hs_data['personal_email'])) {
            if (file_exists(__DIR__ . '/../lib/helpers.php')) {
                require_once __DIR__ . '/../lib/helpers.php';
                if (function_exists('send_email_via_api_batch')) {
                    $body = "<p>Chào <b>$ho_ten</b>,</p><p>Tài khoản Microsoft 365 của bạn ($email) đã bị khóa.</p>";
                    send_email_via_api_batch([['to' => $hs_data['personal_email'], 'subject' => $tieu_de, 'html' => $body]]);
                }
            }
        }
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi khóa mail']);
    }
    exit;
}

if ($action === 'mo_khoa_mail') {
    $hs_id = $_POST['id'] ?? 0;
    
    $stmt = $db->prepare("SELECT e.email, hs.email as personal_email, hs.ho_dem, hs.ten FROM email_hoc_sinh e JOIN hoc_sinh hs ON e.hoc_sinh_id = hs.id WHERE e.hoc_sinh_id = ?");
    $stmt->execute([$hs_id]);
    $hs_data = $stmt->fetch(PDO::FETCH_ASSOC);
    $email = $hs_data['email'] ?? null;

    $token = getMsGraphToken();
    $userData = ['accountEnabled' => true];

    $ch = curl_init('https://graph.microsoft.com/v1.0/users/' . urlencode($email));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token, 'Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode == 204) {
        $db->prepare("UPDATE email_hoc_sinh SET trang_thai = 'da_cap' WHERE hoc_sinh_id = ?")->execute([$hs_id]);
        
        $ho_ten = trim($hs_data['ho_dem'] . ' ' . $hs_data['ten']);
        $tieu_de = "Tài khoản Email Edu đã được mở khóa";
        $noi_dung = "Tài khoản Microsoft 365 của bạn ($email) đã được mở khóa. Bạn có thể đăng nhập bình thường.";
        create_student_notification($db, $hs_id, $tieu_de, $noi_dung, 'he_thong');
           
        if (!empty($hs_data['personal_email'])) {
            if (file_exists(__DIR__ . '/../lib/helpers.php')) {
                require_once __DIR__ . '/../lib/helpers.php';
                if (function_exists('send_email_via_api_batch')) {
                    $body = "<p>Chào <b>$ho_ten</b>,</p><p>Tài khoản Microsoft 365 của bạn ($email) đã được quản trị viên mở khóa.</p>";
                    send_email_via_api_batch([['to' => $hs_data['personal_email'], 'subject' => $tieu_de, 'html' => $body]]);
                }
            }
        }
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi mở khóa mail']);
    }
    exit;
}

if ($action === 'xoa_mail') {
    $hs_id = $_POST['id'] ?? 0;
    
    $stmt = $db->prepare("SELECT e.email, hs.email as personal_email, hs.ho_dem, hs.ten FROM email_hoc_sinh e JOIN hoc_sinh hs ON e.hoc_sinh_id = hs.id WHERE e.hoc_sinh_id = ?");
    $stmt->execute([$hs_id]);
    $hs_data = $stmt->fetch(PDO::FETCH_ASSOC);
    $email = $hs_data['email'] ?? null;

    if ($email) {
        $token = getMsGraphToken();
        $ch = curl_init('https://graph.microsoft.com/v1.0/users/' . urlencode($email));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }
    $db->prepare("DELETE FROM email_hoc_sinh WHERE hoc_sinh_id = ?")->execute([$hs_id]);
    
    if ($hs_data) {
        $ho_ten = trim($hs_data['ho_dem'] . ' ' . $hs_data['ten']);
        $tieu_de = "Tài khoản Email Edu đã bị xóa";
        $noi_dung = "Tài khoản Microsoft 365 của bạn ($email) đã bị thu hồi/xóa khỏi hệ thống.";
        create_student_notification($db, $hs_id, $tieu_de, $noi_dung, 'he_thong');
           
        if (!empty($hs_data['personal_email'])) {
            if (file_exists(__DIR__ . '/../lib/helpers.php')) {
                require_once __DIR__ . '/../lib/helpers.php';
                if (function_exists('send_email_via_api_batch')) {
                    $body = "<p>Chào <b>$ho_ten</b>,</p><p>Tài khoản Microsoft 365 của bạn ($email) đã bị quản trị viên thu hồi/xóa vĩnh viễn khỏi hệ thống.</p>";
                    send_email_via_api_batch([['to' => $hs_data['personal_email'], 'subject' => $tieu_de, 'html' => $body]]);
                }
            }
        }
    }

    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'cap_mail_hang_loat') {
    // Tắt timeout PHP để chạy lâu
    set_time_limit(0);
    
    $stmt = $db->query("SELECT hoc_sinh_id, email FROM email_hoc_sinh WHERE trang_thai = 'cho_duyet'");
    $pending_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($pending_list)) {
        echo json_encode(['success' => true, 'message' => 'Không có học sinh nào đang chờ duyệt.']);
        exit;
    }

    $token = getMsGraphToken();
    if (!$token) {
        echo json_encode(['success' => false, 'message' => 'Không thể lấy token từ Microsoft để cấp hàng loạt.']);
        exit;
    }

    $success_count = 0;
    $error_count = 0;

    foreach ($pending_list as $row) {
        $hs_id = $row['hoc_sinh_id'];
        
        $stmt_hs = $db->prepare("SELECT ho_dem, ten, ma_hoc_sinh as so_cccd, nien_khoa, anh_the FROM hoc_sinh WHERE id = ?");
        $stmt_hs->execute([$hs_id]);
        $hs = $stmt_hs->fetch(PDO::FETCH_ASSOC);
        if ($hs) {
            $hs['ho_ten'] = trim($hs['ho_dem'] . ' ' . $hs['ten']);
        }
        
        if (!$hs) {
            $error_count++;
            continue;
        }

        $nien_khoa = str_replace(' ', '', $hs['nien_khoa']);
        $nk_parts = explode('-', $nien_khoa);
        $prefix = (isset($nk_parts[0]) && isset($nk_parts[1])) ? substr($nk_parts[0], -2) . substr($nk_parts[1], -2) : '9999';
        $ten = getFirstName($hs['ho_ten']);
        
        $userPrincipalName = $prefix . "." . $hs['so_cccd'] . "." . $ten . "@" . MS_DOMAIN;
        $password = generateRandomPassword();

        // Data for Microsoft
        $userData = [
            'accountEnabled' => true,
            'displayName' => $hs['ho_ten'],
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

        if ($httpcode == 201 || $httpcode == 200) {
            $resData = json_decode($response, true);
            $msUserId = $resData['id']; // Lấy ID của user vừa tạo
            
            // Gán license "Office 365 A1 for students" (SKU ID: 314c4481-f395-4525-be8b-2ec4bb1e9d91)
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

            $db->prepare("UPDATE email_hoc_sinh SET email=?, mat_khau_tam=?, trang_thai='da_cap', thoi_gian_cap=NOW(), error_message=NULL WHERE hoc_sinh_id=?")
               ->execute([$userPrincipalName, $password, $hs_id]);
            $success_count++;
        } else {
            $resData = json_decode($response, true);
            $err = $resData['error']['message'] ?? 'Lỗi tạo TK MS';
            $db->prepare("UPDATE email_hoc_sinh SET error_message = ? WHERE hoc_sinh_id = ?")->execute([$err, $hs_id]);
            $error_count++;
        }
    }

    echo json_encode(['success' => true, 'message' => "Đã cấp thành công $success_count email. Lỗi: $error_count."]);
    exit;
}

if ($action === 'export') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="DanhSachEmailHocSinh.xls"');
    echo "<table border='1'>";
    echo "<tr><th>STT</th><th>Họ và tên</th><th>CCCD</th><th>Niên khóa</th><th>Lớp</th><th>Email Microsoft</th><th>Mật khẩu tạm</th><th>Trạng thái</th></tr>";
    
    $sys_year = $db->query("SELECT get_current_nam_hoc_id_mysql()")->fetchColumn();
    $sql = "SELECT CONCAT(hs.ho_dem, ' ', hs.ten) AS ho_ten, hs.ma_hoc_sinh, hs.nien_khoa, lh.ten_lop, 
            e.email, e.mat_khau_tam, e.trang_thai 
            FROM email_hoc_sinh e 
            JOIN hoc_sinh hs ON e.hoc_sinh_id = hs.id 
            LEFT JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id";
    $stmt = $db->query($sql);
    $stt = 1;
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . $stt++ . "</td>";
        echo "<td>" . $row['ho_ten'] . "</td>";
        echo "<td>" . $row['so_cccd'] . "</td>";
        echo "<td>" . $row['nien_khoa'] . "</td>";
        echo "<td>" . $row['ten_lop'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "<td>" . $row['mat_khau_tam'] . "</td>";
        echo "<td>" . $row['trang_thai'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    exit;
}

echo json_encode(['success' => false, 'message' => 'Action không tồn tại']);
