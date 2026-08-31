<?php
// File: src/controllers/api_set_student_status.php (File mới)
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $data = json_decode(file_get_contents('php://input'), true);
    $student_id = $data['student_id'] ?? null;
    $new_status = $data['new_status'] ?? null; // Sẽ là 'dang_hoc' (để khôi phục) hoặc 'nghi_hoc' (để lưu trữ)

    if ($student_id && in_array($new_status, ['dang_hoc', 'nghi_hoc'])) {
        try {
            $db = get_db_connection();
            
            if ($new_status === 'nghi_hoc') {
                // CHO NGHỈ HỌC: Cập nhật trạng thái VÀ thu hồi tất cả quyền CTV
                $sql = "UPDATE hoc_sinh 
                        SET trang_thai_hoc_tap = 'nghi_hoc', 
                            quyen_truy_cap = NULL 
                        WHERE id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$student_id]);
                $message = 'Đã chuyển học sinh sang trạng thái Nghỉ Học và thu hồi quyền CTV.';

            } else {
                // KHÔI PHỤC ('dang_hoc'): Cập nhật trạng thái VÀ xóa lý do nghỉ
                $sql = "UPDATE hoc_sinh 
                        SET trang_thai_hoc_tap = 'dang_hoc',
                            ngay_nghi_hoc = NULL,
                            ly_do_nghi_hoc = NULL
                        WHERE id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$student_id]);
                $message = 'Đã khôi phục học sinh, họ sẽ xuất hiện lại trong các danh sách.';
            }

            if ($stmt->rowCount() > 0) {
                $response = ['success' => true, 'message' => $message];
            } else {
                $response['message'] = 'Không có gì thay đổi.';
            }
        } catch (PDOException $e) {
            $response['message'] = 'Lỗi CSDL: ' . $e->getMessage();
        }
    }
}
echo json_encode($response);
?>