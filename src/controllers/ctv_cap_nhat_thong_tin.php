<?php
// File: src/controllers/ctv_cap_nhat_thong_tin.php (Đã nâng cấp)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

if (!isset($_SESSION['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'Lỗi xác thực.']);
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$student_id = $_SESSION['student_id'];
$data = json_decode(file_get_contents('php://input'), true);

try {
    $db = get_db_connection();

    // Lấy các cài đặt của admin
    $stmt_settings = $db->query("SELECT setting_key, setting_value FROM he_thong_cai_dat");
    $settings_raw = $stmt_settings->fetchAll(PDO::FETCH_KEY_PAIR);
    $can_edit_chuc_vu = ($settings_raw['student_can_edit_chuc_vu'] ?? 'off') === 'on';
    $can_edit_sdt = ($settings_raw['student_can_edit_sdt'] ?? 'off') === 'on';

    $stmt_get = $db->prepare("SELECT ma_hoc_sinh, nam_hoc_id FROM hoc_sinh WHERE id = ?");
    $stmt_get->execute([$student_id]);
    $hs_info = $stmt_get->fetch(PDO::FETCH_ASSOC);

    if ($hs_info) {
        $db->beginTransaction();
        
        $sql_hoso_parts = [];
        $params_hoso = [];

        if ($can_edit_sdt && isset($data['sdt'])) {
            $sql_hoso_parts[] = "sdt = ?";
            $params_hoso[] = trim($data['sdt']);
        }

        $nhan_thong_bao_value = isset($data['nhan_thong_bao']) ? 1 : 0;
        $sql_hoso_parts[] = "nhan_thong_bao_vi_pham = ?";
        $params_hoso[] = $nhan_thong_bao_value;

        if (!empty($sql_hoso_parts)) {
            $sql_hoso = "UPDATE ho_so_hoc_sinh SET " . implode(', ', $sql_hoso_parts) . " WHERE ma_hoc_sinh = ?";
            $params_hoso[] = $hs_info['ma_hoc_sinh'];
            $db->prepare($sql_hoso)->execute($params_hoso);
        }

        if ($can_edit_chuc_vu && isset($data['chuc_vu'])) {
            $sql_qt = "UPDATE quatrinh_hoc_tap SET chuc_vu = ? WHERE ma_hoc_sinh = ? AND nam_hoc_id = ?";
            $db->prepare($sql_qt)->execute([trim($data['chuc_vu']), $hs_info['ma_hoc_sinh'], $hs_info['nam_hoc_id']]);
        }

        $db->commit();
    }

    echo json_encode(['success' => true, 'message' => 'Cập nhật thông tin thành công!']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
}