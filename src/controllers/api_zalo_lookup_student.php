<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('POST, GET, OPTIONS');
zalo_handle_options();

$response = ['success' => false, 'message' => 'Lỗi không xác định.'];

try {
    $db = get_db_connection();
    
    $payload = zalo_authenticate_request();
    $student_id = $payload['student_id'];
    $nam_hoc_header = zalo_get_nam_hoc_id();
    $nam_hoc_id = $nam_hoc_header;

    if (!$nam_hoc_id) {
        $stmt_hs = $db->prepare("
            SELECT (SELECT MAX(nam_hoc_id) FROM quatrinh_hoc_tap WHERE ma_hoc_sinh = hs.ma_hoc_sinh) as nam_hoc_id 
            FROM ho_so_hoc_sinh hs 
            WHERE hs.id = ?
        ");
        $stmt_hs->execute([$student_id]);
        $nam_hoc_id = $stmt_hs->fetchColumn();
    }

    if (!$nam_hoc_id) {
        throw new Exception("Không xác định được năm học.");
    }

    $ho_ten_raw = trim($_GET['ho_ten'] ?? '');
    $ten_lop_raw = trim($_GET['ten_lop'] ?? '');

    if (empty($ho_ten_raw) || empty($ten_lop_raw)) {
        throw new Exception('Thiếu thông tin họ tên hoặc lớp.');
    }

    // Tự động chuẩn hóa các định dạng
    $ten_lop_final = strtoupper(str_replace(' ', '', $ten_lop_raw)); // Xóa khoảng trắng và in hoa
    
    // Nếu nhập dạng a1, b2, c10 -> chuyển thành 10A1, 11A2, 12A10
    if (preg_match('/^([A-C])(\d+)$/', $ten_lop_final, $matches)) {
        $prefix = $matches[1];
        $number = $matches[2];
        $khoi_map = ['A' => '10', 'B' => '11', 'C' => '12'];
        $ten_lop_final = $khoi_map[$prefix] . 'A' . $number;
    }
    
    // Đảm bảo chữ cái trong tên lớp luôn là 'A' vì trường chỉ có lớp A (VD: 10B1 -> 10A1, 11C2 -> 11A2, 12b -> 12A)
    if (preg_match('/^(10|11|12)[A-Z](.*)$/', $ten_lop_final, $matches)) {
        $ten_lop_final = $matches[1] . 'A' . $matches[2];
    }

    // Tìm lớp trong CSDL
    $stmt_lop = $db->prepare("SELECT id, ten_lop FROM raw_lop_hoc WHERE ten_lop = ? AND nam_hoc_id = ?");
    $stmt_lop->execute([$ten_lop_final, $nam_hoc_id]);
    $lop = $stmt_lop->fetch();

    if (!$lop) {
        $response['message'] = "Lớp '" . htmlspecialchars($ten_lop_final) . "' không tồn tại.";
        echo json_encode($response);
        exit();
    }

    $ho_ten_search = trim($ho_ten_raw);
    $ho_ten_exact = preg_replace('/\s+/', ' ', $ho_ten_search);

    // Dùng LIKE để lấy tất cả ứng viên (SQL sẽ tự động tìm không phân biệt hoa thường và không phân biệt dấu)
    $sql_search_hs = "
    SELECT qt.id, ho_so.ma_hoc_sinh, ho_so.ho_dem, ho_so.ten, ho_so.trang_thai_hoc_tap 
    FROM quatrinh_hoc_tap qt
    JOIN ho_so_hoc_sinh ho_so ON qt.ma_hoc_sinh = ho_so.ma_hoc_sinh
    WHERE qt.lop_hoc_id = ? AND qt.nam_hoc_id = ? AND CONCAT(ho_so.ho_dem, ' ', ho_so.ten) LIKE ?
    ";
    $stmt_hs = $db->prepare($sql_search_hs);
    $stmt_hs->execute([$lop['id'], $nam_hoc_id, '%' . $ho_ten_exact . '%']);
    $students = $stmt_hs->fetchAll();

    // Thuật toán tính điểm (Scoring) để ưu tiên người khớp chính xác nhất
    if (count($students) > 1) {
        $best_matches = [];
        $max_score = -1;
        $search_mb = mb_strtolower($ho_ten_exact, 'UTF-8');

        foreach ($students as $s) {
            $full_name = trim($s['ho_dem'] . ' ' . $s['ten']);
            $ten = trim($s['ten']);
            
            $score = 0;
            $full_mb = mb_strtolower($full_name, 'UTF-8');
            $ten_mb = mb_strtolower($ten, 'UTF-8');

            if ($search_mb === $full_mb) {
                $score = 100; // Khớp chính xác hoàn toàn cả họ tên (có dấu)
            } elseif ($search_mb === $ten_mb) {
                $score = 90;  // Khớp chính xác tên chính (có dấu)
            } else {
                // Khớp phần cuối của tên (Vd: "Bảo Trân" khớp "Đỗ Bảo Trân")
                $len = mb_strlen($search_mb, 'UTF-8');
                if ($len > 0 && mb_substr($full_mb, -$len, null, 'UTF-8') === $search_mb) {
                    $score = 80;
                } elseif (mb_strpos($full_mb, $search_mb) !== false) {
                    $score = 70; // Tên có chứa cụm từ gõ vào (khớp chính xác dấu)
                } else {
                    $score = 50; // Chỉ khớp do SQL tìm được (khớp không dấu, ví dụ gõ "trân" ra "trần")
                }
            }

            if ($score > $max_score) {
                $max_score = $score;
                $best_matches = [$s];
            } elseif ($score === $max_score) {
                $best_matches[] = $s;
            }
        }
        $students = $best_matches;
    }

    if (count($students) === 1) {
        $student = $students[0];

        if ($student['trang_thai_hoc_tap'] === 'nghi_hoc') {
            $response['message'] = 'Học sinh này đã nghỉ học.';
            $response['success'] = false;
        } else {
            $response = [
                'success' => true,
                'student' => [
                    'id' => $student['id'],
                    'ma_hoc_sinh' => $student['ma_hoc_sinh'],
                    'ho_ten' => trim($student['ho_dem'] . ' ' . $student['ten']),
                    'lop_hoc_id' => $lop['id'],
                    'ten_lop' => $lop['ten_lop']
                ]
            ];
        }
    } elseif (count($students) > 1) {
        $ds_ten = [];
        foreach ($students as $s) {
            $ds_ten[] = trim($s['ho_dem'] . ' ' . $s['ten']) . " (" . $s['ma_hoc_sinh'] . ")";
        }
        $response['message'] = 'Tìm thấy nhiều HS: ' . implode(', ', $ds_ten);
        $response['success'] = false;
    } else {
        $response['message'] = "Không tìm thấy học sinh có tên chứa '" . htmlspecialchars($ho_ten_search) . "' trong lớp " . $lop['ten_lop'] . ".";
        $response['success'] = false;
    }

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
