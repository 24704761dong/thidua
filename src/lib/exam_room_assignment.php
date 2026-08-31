<?php
// File: src/lib/exam_room_assignment.php

function ensure_exam_room_assignment_schema(PDO $db): void
{
    static $initialized = false;
    if ($initialized) return;
    $initialized = true;

    $db->exec(
        "CREATE TABLE IF NOT EXISTS ky_thi_xep_phong (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ky_thi_id INT NOT NULL,
            ca_thi_id INT NULL,
            ca_thi INT NOT NULL DEFAULT 1,
            phong_thi_id INT NOT NULL,
            ky_thi_hoc_sinh_id INT NOT NULL,
            mon_thi VARCHAR(50) NOT NULL,
            luot_thi INT NOT NULL DEFAULT 1,
            seat_no INT NOT NULL DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_ktxp_kt (ky_thi_id),
            INDEX idx_ktxp_ca_id (ca_thi_id),
            INDEX idx_ktxp_phong (phong_thi_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci"
    );

    // Kiểm tra và thêm cột ca_thi_id nếu bảng đã tồn tại từ trước
    try {
        $cols = $db->query("SHOW COLUMNS FROM ky_thi_xep_phong LIKE 'ca_thi_id'")->fetchAll();
        if (empty($cols)) {
            $db->exec("ALTER TABLE ky_thi_xep_phong ADD COLUMN ca_thi_id INT NULL AFTER ky_thi_id");
            $db->exec("CREATE INDEX idx_ktxp_ca_id ON ky_thi_xep_phong (ca_thi_id)");
        }
    } catch (Exception $e) {
        // bỏ qua nếu đã tồn tại
    }

    // Xóa Unique Key cũ (nếu có) gây cản trở 1 học sinh thi 2 lượt trong cùng 1 ca
    try {
        $db->exec("ALTER TABLE ky_thi_xep_phong DROP INDEX uniq_ca_hs");
    } catch (Exception $e) {
        // bỏ qua nếu không có
    }
}

function exam_room_assignment_clear(PDO $db, $ky_thi_id, $ca_thi_id = null)
{
    if ($ca_thi_id !== null) {
        $stmt = $db->prepare("DELETE FROM ky_thi_xep_phong WHERE ky_thi_id = ? AND ca_thi_id = ?");
        $stmt->execute([$ky_thi_id, $ca_thi_id]);
    } else {
        $stmt = $db->prepare("DELETE FROM ky_thi_xep_phong WHERE ky_thi_id = ?");
        $stmt->execute([$ky_thi_id]);
    }
}
