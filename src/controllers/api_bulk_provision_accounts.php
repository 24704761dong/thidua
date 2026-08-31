<?php // FILE: src/controllers/api_bulk_provision_accounts.php (PHIÊN BẢN NÂNG CẤP V3 - Progress, commit từng bản ghi)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? null; // 'discover' | 'process' | null (legacy)
    $scope = $data['scope'] ?? null;
    $lop_id = $data['lop_id'] ?? null;

    try {
        $db = get_db_connection();

        // Helper: tạo mật khẩu từ ngày sinh (hỗ trợ 'dd/mm/yyyy' hoặc 'yyyy-mm-dd')
        $buildPassword = function ($dob) {
            if (!$dob) return null;
            $dob = trim($dob);
            if ($dob === '') return null;
            if (strpos($dob, '/') !== false) {
                // dd/mm/yyyy -> ddmmyyyy
                return str_replace('/', '', $dob);
            }
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
                // yyyy-mm-dd -> ddmmyyyy
                [$y, $m, $d] = explode('-', $dob);
                return $d.$m.$y;
            }
            // Fallback: giữ nguyên số nếu trông giống ddmmyyyy
            if (preg_match('/^\d{8}$/', $dob)) return $dob;
            return null;
        };

        // Xây dựng WHERE chung theo scope
        $sql_where = "FROM hoc_sinh WHERE trang_thai_tai_khoan = 'Chưa cấp TK' AND ngay_sinh IS NOT NULL AND ngay_sinh != ''";
        $params = [];
        if ($scope === 'class' && !empty($lop_id)) {
            $sql_where .= " AND lop_hoc_id = ?";
            $params[] = $lop_id;
        } elseif ($scope !== 'all') {
            // Cho phép legacy mode không truyền scope (giữ tương thích)
            if ($scope !== null) {
                throw new Exception('Lựa chọn không hợp lệ.');
            }
        }

        // Nhánh 1: Khám phá danh sách ID cần cấp (discover)
        if ($action === 'discover') {
            $stmt_count = $db->prepare("SELECT COUNT(id) " . $sql_where);
            $stmt_count->execute($params);
            $total = (int)$stmt_count->fetchColumn();

            if ($total === 0) {
                echo json_encode(['success' => true, 'total' => 0, 'ids' => []]);
                return;
            }

            $stmt_ids = $db->prepare("SELECT id " . $sql_where);
            $stmt_ids->execute($params);
            $ids = array_map(function ($row) { return (int)$row['id']; }, $stmt_ids->fetchAll());

            echo json_encode(['success' => true, 'total' => $total, 'ids' => $ids]);
            return;
        }

        // Nhánh 2: Xử lý theo batches, commit từng bản ghi (process)
        if ($action === 'process') {
            $ids = $data['ids'] ?? [];
            $offset = max(0, (int)($data['offset'] ?? 0));
            $limit = max(1, min(200, (int)($data['limit'] ?? 50)));

            if (!is_array($ids) || empty($ids)) {
                throw new Exception('Thiếu danh sách học sinh (ids).');
            }

            $slice = array_slice($ids, $offset, $limit);
            $processed = 0; $updated = 0; $skipped = 0; $errors = [];

            $stmt_get = $db->prepare("SELECT id, ngay_sinh FROM hoc_sinh WHERE id = ?");
            $stmt_update = $db->prepare("UPDATE hoc_sinh SET mat_khau_hash = ?, trang_thai_tai_khoan = 'Đã cấp TK' WHERE id = ?");

            foreach ($slice as $sid) {
                try {
                    $stmt_get->execute([$sid]);
                    $row = $stmt_get->fetch();
                    if (!$row) { $skipped++; continue; }
                    $pwd = $buildPassword($row['ngay_sinh'] ?? '');
                    if (!$pwd) { $skipped++; continue; }
                    $hash = password_hash($pwd, PASSWORD_DEFAULT);
                    $stmt_update->execute([$hash, $sid]);
                    $updated++; $processed++;
                } catch (Exception $ie) {
                    $errors[] = ['id' => $sid, 'error' => $ie->getMessage()];
                    $processed++;
                }
            }

            echo json_encode([
                'success' => true,
                'processed' => $processed,
                'updated' => $updated,
                'skipped' => $skipped,
                'nextOffset' => $offset + count($slice),
                'limit' => $limit,
                'errors' => $errors,
            ]);
            return;
        }

        // Legacy: giữ lại hành vi cũ nếu không truyền action (xử lý một lần)
        $count = 0;
        $stmt_count = $db->prepare("SELECT COUNT(id) " . $sql_where);
        $stmt_count->execute($params);
        $total_to_provision = (int)$stmt_count->fetchColumn();
        if ($total_to_provision === 0) {
            throw new Exception('Không có học sinh nào phù hợp để cấp tài khoản (chưa có tài khoản hoặc thiếu ngày sinh).');
        }
        $stmt_select_data = $db->prepare("SELECT id, ngay_sinh " . $sql_where);
        $stmt_select_data->execute($params);
        $students = $stmt_select_data->fetchAll();

        $stmt_update = $db->prepare("UPDATE hoc_sinh SET mat_khau_hash = ?, trang_thai_tai_khoan = 'Đã cấp TK' WHERE id = ?");
        foreach ($students as $row) {
            $pwd = $buildPassword($row['ngay_sinh'] ?? '');
            if (!$pwd) continue;
            $hash = password_hash($pwd, PASSWORD_DEFAULT);
            $stmt_update->execute([$hash, $row['id']]);
            $count++;
        }

        $response = [
            'success' => true,
            'message' => "Đã cấp tài khoản thành công cho {$count}/{$total_to_provision} học sinh."
        ];
    } catch (Exception $e) {
        $response['message'] = 'Lỗi: ' . $e->getMessage();
    }
}

echo json_encode($response);
?>