<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$db = get_db_connection();
$current_nam_hoc_id = get_current_nam_hoc_id();
$data = json_decode(file_get_contents('php://input'), true);

$action = $data['action'] ?? '';

try {
    if ($action === 'get_classes') {
        $nhId = (int)($data['nam_hoc_id'] ?? 0);
        $stmt = $db->prepare("SELECT id, ten_lop FROM raw_lop_hoc WHERE nam_hoc_id = ? ORDER BY ten_lop ASC");
        $stmt->execute([$nhId]);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);

    } elseif ($action === 'get_students') {
        $lopId = (int)($data['lop_hoc_id'] ?? 0);
        $nhId = (int)($data['nam_hoc_id'] ?? 0);
        
        $stmt = $db->prepare("SELECT hs.id, hs.ma_hoc_sinh, hs.ho_dem, hs.ten, hs.trang_thai_hoc_tap 
                              FROM ho_so_hoc_sinh hs
                              JOIN quatrinh_hoc_tap qt ON hs.ma_hoc_sinh = qt.ma_hoc_sinh
                              WHERE qt.lop_hoc_id = ? AND qt.nam_hoc_id = ? 
                              ORDER BY hs.ten ASC, hs.ho_dem ASC");
        $stmt->execute([$lopId, $nhId]);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);

    } elseif ($action === 'promote_students') {
        $student_ids = $data['student_ids'] ?? [];
        $new_lop_hoc_id = (int)($data['new_lop_hoc_id'] ?? 0);

        if (empty($student_ids) || !$new_lop_hoc_id) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            exit;
        }

        // Bắt đầu transaction
        $db->beginTransaction();

        foreach ($student_ids as $hs_id) {
            // Kiểm tra học sinh cũ (ho_so_hoc_sinh)
            $stmt = $db->prepare("SELECT * FROM ho_so_hoc_sinh WHERE id = ?");
            $stmt->execute([$hs_id]);
            $hs = $stmt->fetch();

            if ($hs && ($hs['trang_thai_hoc_tap'] ?? 'dang_hoc') === 'dang_hoc') {
                // Kiểm tra xem đã tồn tại học sinh này ở năm học mới chưa trong quatrinh_hoc_tap
                $checkStmt = $db->prepare("SELECT id FROM quatrinh_hoc_tap WHERE ma_hoc_sinh = ? AND nam_hoc_id = ?");
                $checkStmt->execute([$hs['ma_hoc_sinh'], $current_nam_hoc_id]);
                if ($checkStmt->fetchColumn()) {
                    continue; // Đã tồn tại trong năm mới thì bỏ qua
                }

                // Chèn quá trình học tập sang năm mới
                $insertStmt = $db->prepare("
                    INSERT INTO quatrinh_hoc_tap (
                        ma_hoc_sinh, nam_hoc_id, lop_hoc_id, chuc_vu
                    ) VALUES (?, ?, ?, 'Học sinh')
                ");
                $insertStmt->execute([
                    $hs['ma_hoc_sinh'], $current_nam_hoc_id, $new_lop_hoc_id
                ]);
            }
        }

        $db->commit();
        echo json_encode(['success' => true]);

    } elseif ($action === 'create_class') {
        $ten_lop = trim($data['ten_lop'] ?? '');
        if (empty($ten_lop)) {
            echo json_encode(['success' => false, 'message' => 'Tên lớp không được để trống']);
            exit;
        }

        // Kiểm tra lớp đã tồn tại chưa
        $stmtCheck = $db->prepare("SELECT id FROM raw_lop_hoc WHERE ten_lop = ? AND nam_hoc_id = ?");
        $stmtCheck->execute([$ten_lop, $current_nam_hoc_id]);
        if ($stmtCheck->fetchColumn()) {
            echo json_encode(['success' => false, 'message' => 'Lớp này đã tồn tại trong năm học hiện tại']);
            exit;
        }

        // Tạo lớp mới
        $stmtInsert = $db->prepare("INSERT INTO raw_lop_hoc (ten_lop, nam_hoc_id) VALUES (?, ?)");
        $stmtInsert->execute([$ten_lop, $current_nam_hoc_id]);
        $new_id = $db->lastInsertId();

        echo json_encode(['success' => true, 'data' => ['id' => $new_id, 'ten_lop' => $ten_lop]]);

    } else {
        echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
    }
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
