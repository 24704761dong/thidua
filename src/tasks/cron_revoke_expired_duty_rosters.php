<?php
// File: src/tasks/cron_revoke_expired_duty_rosters.php

require_once __DIR__ . '/../../config/bootstrap.php';

$log_content = "--------------------------------------------------\n";
$log_content .= "Bắt đầu chạy tác vụ thu hồi quyền trực: " . date('Y-m-d H:i:s') . "\n";

try {
    $db = get_db_connection();
    
    // Tìm các lịch trực đã duyệt, chưa lưu trữ và thuộc về tuần học đã kết thúc
    $stmt = $db->prepare("
        SELECT dkt.id, dkt.quyen_da_cap 
        FROM dang_ky_truc_tuan dkt
        JOIN raw_tuan_hoc th ON dkt.tuan_hoc_id = th.id
        WHERE dkt.trang_thai IN ('Đã duyệt', 'Da duyet') 
          AND dkt.trang_thai_luu_tru = 0 
          AND th.ngay_ket_thuc < CURDATE()
    ");
    $stmt->execute();
    $expired_rosters = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $completed_count = 0;
    $revoked_students_count = 0;

    if (!empty($expired_rosters)) {
        $log_content .= " - Tìm thấy " . count($expired_rosters) . " lịch trực đã hết hạn (qua tuần).\n";
        
        $stmt_chi_tiet = $db->prepare("SELECT DISTINCT hoc_sinh_id FROM dang_ky_truc_chi_tiet WHERE dang_ky_truc_tuan_id = ?");
        $stmt_get_perm = $db->prepare("SELECT quyen_truy_cap FROM hoc_sinh WHERE id = ?");
        $stmt_update_perm = $db->prepare("UPDATE hoc_sinh SET quyen_truy_cap = ? WHERE id = ?");
        
        // Cập nhật trạng thái thành Hoàn thành và đưa vào lưu trữ
        $stmt_update_roster = $db->prepare("UPDATE dang_ky_truc_tuan SET trang_thai = 'Hoàn thành', trang_thai_luu_tru = 1 WHERE id = ?");

        foreach ($expired_rosters as $roster) {
            $db->beginTransaction();
            try {
                $id = $roster['id'];
                $quyen_da_cap = json_decode($roster['quyen_da_cap'] ?: '[]', true);
                
                if (!empty($quyen_da_cap) && is_array($quyen_da_cap)) {
                    $stmt_chi_tiet->execute([$id]);
                    $student_ids = $stmt_chi_tiet->fetchAll(PDO::FETCH_COLUMN);

                    if (!empty($student_ids)) {
                        foreach ($student_ids as $hs_id) {
                            $stmt_get_perm->execute([$hs_id]);
                            $current_permissions = json_decode($stmt_get_perm->fetchColumn() ?: '{}', true);
                            
                            $changed = false;
                            foreach ($quyen_da_cap as $perm) {
                                if (isset($current_permissions[$perm])) {
                                    unset($current_permissions[$perm]);
                                    $changed = true;
                                }
                            }
                            
                            if ($changed) {
                                $stmt_update_perm->execute([json_encode($current_permissions), $hs_id]);
                                $revoked_students_count++;
                            }
                        }
                    }
                }
                
                $stmt_update_roster->execute([$id]);
                $db->commit();
                $completed_count++;
            } catch (Exception $e) {
                if ($db->inTransaction()) $db->rollBack();
                $log_content .= "   -> **LỖI** xử lý ID {$roster['id']}: " . $e->getMessage() . "\n";
            }
        }
    } else {
        $log_content .= " - Không có lịch trực nào hết hạn.\n";
    }

    $final_message = "Thu hồi lịch trực: {$completed_count} lịch hoàn thành, {$revoked_students_count} học sinh bị thu hồi quyền.";
    $log_content .= $final_message . "\n";
    
} catch (Exception $e) {
    $final_message = "Lỗi nghiêm trọng thu hồi lịch trực: " . $e->getMessage();
    $log_content .= "!!! LỖI: " . $e->getMessage() . "\n";
}

return $final_message;
