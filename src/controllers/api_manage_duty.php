<?php
// File: src/controllers/api_manage_duty.php (PHIÊN BẢN HOÀN CHỈNH)
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    http_response_code(403);
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../lib/helpers.php'; // Đảm bảo bạn đã có file này

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? null;
$id = $data['id'] ?? null;

if (!$action || !$id) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin.']);
    exit();
}

$db = get_db_connection();

try {
    $db->beginTransaction();

    // Lấy danh sách học sinh trong ca trực này (luôn cần cho cả 2 hành động)
    $stmt_students = $db->prepare("SELECT hoc_sinh_id FROM dang_ky_truc_chi_tiet WHERE dang_ky_truc_tuan_id = ?");
    $stmt_students->execute([$id]);
    $student_ids = $stmt_students->fetchAll(PDO::FETCH_COLUMN);

    if ($action === 'approve_and_grant') {
        $permissions_to_grant = $data['permissions'] ?? [];
        
        if (!empty($student_ids) && !empty($permissions_to_grant)) {
            $stmt_get = $db->prepare("SELECT quyen_truy_cap FROM hoc_sinh WHERE id = ?");
            $stmt_update = $db->prepare("UPDATE hoc_sinh SET quyen_truy_cap = ? WHERE id = ?");

            foreach ($student_ids as $student_id) {
                $stmt_get->execute([$student_id]);
                $current_permissions = json_decode($stmt_get->fetchColumn() ?: '{}', true);
                
                // Thêm các quyền mới vào
                foreach ($permissions_to_grant as $perm) {
                    $current_permissions[$perm] = true;
                }
                
                $stmt_update->execute([json_encode($current_permissions), $student_id]);
            }
        }
        
        // Cập nhật trạng thái và lưu lại danh sách quyền đã cấp (dưới dạng JSON)
        $quyen_da_cap_json = empty($permissions_to_grant) ? NULL : json_encode($permissions_to_grant);
        $stmt = $db->prepare("UPDATE dang_ky_truc_tuan SET trang_thai = 'Đã duyệt', quyen_da_cap = ? WHERE id = ?");
        $stmt->execute([$quyen_da_cap_json, $id]);
        
        $message = 'Đã duyệt danh sách thành công!';
        if (!empty($permissions_to_grant)) {
            $message .= ' Đã cấp ' . count($permissions_to_grant) . ' quyền cho ' . count($student_ids) . ' học sinh.';
        }
        $response = ['success' => true, 'message' => $message];

    } elseif ($action === 'delete') {
        // === LOGIC XÓA (CHUYỂN SANG LƯU TRỮ) ===

        // 1. Lấy danh sách quyền đã cấp bởi chính lần đăng ký này để thu hồi
        $stmt_get_perm = $db->prepare("SELECT quyen_da_cap FROM dang_ky_truc_tuan WHERE id = ?");
        $stmt_get_perm->execute([$id]);
        $permissions_json_to_revoke = $stmt_get_perm->fetchColumn();
        $permissions_to_revoke = json_decode($permissions_json_to_revoke ?: '[]', true);

        // 2. Thu hồi các quyền đã cấp (nếu có)
        if (!empty($permissions_to_revoke) && !empty($student_ids)) {
            $stmt_get = $db->prepare("SELECT quyen_truy_cap FROM hoc_sinh WHERE id = ?");
            $stmt_update = $db->prepare("UPDATE hoc_sinh SET quyen_truy_cap = ? WHERE id = ?");

            foreach ($student_ids as $student_id) {
                $stmt_get->execute([$student_id]);
                $current_permissions = json_decode($stmt_get->fetchColumn() ?: '{}', true);
                
                // Chỉ xóa những quyền đã được cấp bởi lần đăng ký này
                foreach($permissions_to_revoke as $perm) {
                    if(isset($current_permissions[$perm])) {
                        unset($current_permissions[$perm]);
                    }
                }
                
                $new_permissions_json = empty($current_permissions) ? NULL : json_encode($current_permissions);
                $stmt_update->execute([$new_permissions_json, $student_id]);
            }
        }
        
        // 3. THAY VÌ XÓA, CẬP NHẬT TRẠNG THÁI SANG "LƯU TRỮ"
        $stmt_archive = $db->prepare("UPDATE dang_ky_truc_tuan SET trang_thai_luu_tru = 1 WHERE id = ?");
        $stmt_archive->execute([$id]);
        
        $response = ['success' => true, 'message' => 'Đã chuyển đăng ký vào kho lưu trữ và thu hồi quyền thành công.'];

    } else {
        $response = ['success' => false, 'message' => 'Hành động không hợp lệ.'];
    }
    
    $db->commit();

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    // Ghi log lỗi để dễ debug
    log_to_file(['API' => 'api_manage_duty', 'Exception' => $e->getMessage(), 'Data' => $data]);
    $response = ['success' => false, 'message' => 'Lỗi Server: ' . $e->getMessage()];
    http_response_code(500);
}

echo json_encode($response);