<?php
// File: src/tasks/cron_update_ctv_codes_status.php
// PHIÊN BẢN NÂNG CẤP: Tự động thu hồi quyền học sinh trước khi ngừng hoạt động mã CTV.

require_once __DIR__ . '/../../config/bootstrap.php';

$log_content = "--------------------------------------------------\n";
$log_content .= "Bắt đầu chạy tác vụ lúc: " . date('Y-m-d H:i:s') . "\n";
$final_message = '';

try {
    $db = get_db_connection();
    $now = date('Y-m-d H:i:s');
    $log_content .= "Thời gian hệ thống (Asia/Ho_Chi_Minh): $now\n";

    // ===================================================================
    // 1️⃣ KÍCH HOẠT MÃ TỪ pending -> active
    // ===================================================================
    $sql_activate = "UPDATE ma_kich_hoat_ctv 
                     SET trang_thai = 'active' 
                     WHERE trang_thai = 'pending' AND :now >= thoi_gian_bat_dau";
    $stmt_activate = $db->prepare($sql_activate);
    $stmt_activate->bindValue(':now', $now);
    $stmt_activate->execute();
    $activated_count = $stmt_activate->rowCount();
    $log_content .= " - Đã kích hoạt (pending -> active): {$activated_count} mã.\n";

    // ===================================================================
    // 2️⃣ XỬ LÝ THU HỒI QUYỀN + VÔ HIỆU HÓA MÃ HẾT HẠN
    // ===================================================================
    $stmt_find_expired = $db->prepare("
        SELECT id FROM ma_kich_hoat_ctv
        WHERE trang_thai = 'active' 
          AND thoi_gian_het_han IS NOT NULL
          AND :now > thoi_gian_het_han
    ");
    $stmt_find_expired->bindValue(':now', $now);
    $stmt_find_expired->execute();
    $ma_het_han_ids = $stmt_find_expired->fetchAll(PDO::FETCH_COLUMN);

    $deactivated_count = 0;
    $failed_count = 0;

    if (!empty($ma_het_han_ids)) {
        $log_content .= " - Tìm thấy " . count($ma_het_han_ids) . " mã hết hạn cần xử lý.\n";

        $stmt_update_status = $db->prepare("UPDATE ma_kich_hoat_ctv SET trang_thai = 'inactive' WHERE id = ?");
        $stmt_users = $db->prepare("SELECT hoc_sinh_id FROM lich_su_su_dung_ma_ctv WHERE ma_ctv_id = ?");
        $stmt_get_perm = $db->prepare("SELECT quyen_truy_cap FROM ho_so_hoc_sinh WHERE id = ?");
        $stmt_update_perm = $db->prepare("UPDATE ho_so_hoc_sinh SET quyen_truy_cap = ? WHERE id = ?");

        foreach ($ma_het_han_ids as $ma_id) {
            try {
                $db->beginTransaction();

                // --- Lấy danh sách học sinh đã dùng mã ---
                $stmt_users->execute([$ma_id]);
                $student_ids = $stmt_users->fetchAll(PDO::FETCH_COLUMN);
                $revoked_count = 0;

                if (!empty($student_ids)) {
                    foreach ($student_ids as $student_id) {
                        $stmt_get_perm->execute([$student_id]);
                        $permissions_json = $stmt_get_perm->fetchColumn();
                        $permissions = json_decode($permissions_json ?: '{}', true) ?: [];

                        if (isset($permissions['nhap_vi_pham']) && $permissions['nhap_vi_pham'] === true) {
                            unset($permissions['nhap_vi_pham']);
                            $new_permissions_json = json_encode($permissions);
                            $stmt_update_perm->execute([$new_permissions_json, $student_id]);
                            $revoked_count++;
                        }
                    }
                }

                // --- Cập nhật trạng thái mã ---
                $stmt_update_status->execute([$ma_id]);
                $db->commit();

                $deactivated_count++;
                $log_content .= "   -> Mã ID {$ma_id}: Đã thu hồi quyền của {$revoked_count} học sinh và ngừng hoạt động.\n";
            } catch (Exception $inner) {
                if ($db->inTransaction()) $db->rollBack();
                $failed_count++;
                $log_content .= "   -> **LỖI** khi xử lý mã ID {$ma_id}: " . $inner->getMessage() . "\n";
            }
        }
    } else {
        $log_content .= " - Không có mã nào hết hạn cần xử lý.\n";
    }

    // ===================================================================
    // 3️⃣ KẾT THÚC
    // ===================================================================
    $final_message = "Tác vụ hoàn tất. Kích hoạt: {$activated_count}, Vô hiệu hóa & Thu hồi quyền: {$deactivated_count}, Lỗi: {$failed_count}.";
    $log_content .= $final_message . "\n";
    $log_content .= "KẾT THÚC TÁC VỤ THÀNH CÔNG.\n";

} catch (Exception $e) {
    $final_message = "Lỗi nghiêm trọng: " . $e->getMessage();
    $log_content .= "!!! LỖI: " . $e->getMessage() . "\n";
}

// ===================================================================
// GHI Nhật kỳ
// ===================================================================
// $log_file = __DIR__ . '/../../logs/cron_ctv_codes.log';
// if (!is_dir(dirname($log_file))) {
//     mkdir(dirname($log_file), 0755, true);
// }
// file_put_contents($log_file, $log_content, FILE_APPEND);

// Trả về kết quả cho hệ thống cron hoặc dashboard
return $final_message;
