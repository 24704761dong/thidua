<?php
// File: src/lib/hoc_sinh_db.php (Đã sửa lỗi, lấy đủ các cột + phân trang)

/**
 * Lấy danh sách học sinh có áp dụng bộ lọc.
 * @param PDO $db Đối tượng kết nối CSDL.
 * @param array $filters Mảng chứa các điều kiện lọc.
 * @return array Danh sách học sinh đã được lọc.
 */
function get_all_hoc_sinh(PDO $db, $filters = [], $pagination = null) {
    $current_nam_hoc_id = (int)($_SESSION['nam_hoc_id'] ?? 0);
    if ($current_nam_hoc_id <= 0) {
        $stmtDef = $db->query("SELECT id FROM nam_hoc WHERE is_mac_dinh = 1 LIMIT 1");
        $current_nam_hoc_id = (int)($stmtDef ? $stmtDef->fetchColumn() : 1);
    }
    $stmt_next_nam = $db->prepare("
        SELECT id, ten_nam_hoc 
        FROM nam_hoc 
        WHERE ngay_bat_dau > (SELECT ngay_bat_dau FROM nam_hoc WHERE id = ?) 
           OR (ngay_bat_dau = (SELECT ngay_bat_dau FROM nam_hoc WHERE id = ?) AND id > ?)
        ORDER BY ngay_bat_dau ASC, id ASC 
        LIMIT 1
    ");
    $stmt_next_nam->execute([$current_nam_hoc_id, $current_nam_hoc_id, $current_nam_hoc_id]);
    $next_nam_hoc = $stmt_next_nam->fetch(PDO::FETCH_ASSOC);
    $next_nam_hoc_id = $next_nam_hoc ? (int)$next_nam_hoc['id'] : 0;
    $next_nam_hoc_name = $next_nam_hoc ? $next_nam_hoc['ten_nam_hoc'] : '';

    $next_select = "";
    $next_join = "";
    if ($next_nam_hoc_id > 0) {
        $next_select = ", lh_next.ten_lop AS next_ten_lop, " . $db->quote($next_nam_hoc_name) . " AS next_ten_nam_hoc";
        $next_join = "
            LEFT JOIN quatrinh_hoc_tap qt_next ON hs.ma_hoc_sinh = qt_next.ma_hoc_sinh AND qt_next.nam_hoc_id = $next_nam_hoc_id
            LEFT JOIN raw_lop_hoc lh_next ON qt_next.lop_hoc_id = lh_next.id
        ";
    }

    $sql = "
        SELECT 
            hs.id, hs.ma_hoc_sinh, hs.ho_dem, hs.ten, hs.ngay_sinh, hs.gioi_tinh, hs.chuc_vu, hs.sdt, hs.email,
            hs.google_id, hs.verified_email, hs.two_fa_enabled,
            hs.trang_thai_tai_khoan, hs.quyen_truy_cap, hs.nhan_thong_bao_vi_pham,
            hs.anh_the, hs.anh_the_driver, hs.anh_the_cloud_key, hs.ma_moet, hs.nien_khoa, hs.trang_thai_hoc_tap,
            hs.tinh_thanhpho, hs.xa_phuong, hs.ap_khupho, hs.dia_chi_chi_tiet,
            lh.ten_lop,
            hs.lop_hoc_id,
            lh.gvcn_ten,
            hs.ghi_chu
            $next_select
        FROM hoc_sinh hs
        JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id
        $next_join
    ";

    $where_clauses = [];
    $params = [];
    
    // Removed hs.trang_thai_hoc_tap = 'dang_hoc' filter per user request so they appear with strikethrough
    // Allow graduated students to appear in the list as well per user request
    
    if (!empty($filters['khoi']) && $filters['khoi'] !== 'all') {
        $where_clauses[] = "SUBSTR(lh.ten_lop, 1, 2) = ?";
        $params[] = $filters['khoi'];
    }

    if (!empty($filters['lop_id']) && $filters['lop_id'] !== 'all') {
        $where_clauses[] = "hs.lop_hoc_id = ?";
        $params[] = $filters['lop_id'];
    }

    if (!empty($filters['chuc_vu']) && $filters['chuc_vu'] !== 'all') {
        $where_clauses[] = "hs.chuc_vu = ?";
        $params[] = $filters['chuc_vu'];
    }

    $keyword = trim($filters['keyword'] ?? '');
    if ($keyword !== '') {
        $where_clauses[] = "(hs.ma_hoc_sinh LIKE ? OR CONCAT(TRIM(hs.ho_dem), ' ', TRIM(hs.ten)) LIKE ? )";
        $like = '%' . $keyword . '%';
        $params[] = $like;
        $params[] = $like;
    }

    if (!empty($filters['has_permission'])) {
        // Chỉ lọc những học sinh có ÍT NHẤT MỘT quyền đang bật (true)
        // Lưu ý: cột quyen_truy_cap đang lưu JSON (TEXT). MySQL vẫn hỗ trợ JSON_EXTRACT trên chuỗi JSON hợp lệ.
        // Tránh trường hợp JSON tồn tại nhưng tất cả đều false như {"nhap_vi_pham":false,...}
        $where_clauses[] = "(
            JSON_EXTRACT(hs.quyen_truy_cap, '$.nhap_vi_pham') = true
            OR JSON_EXTRACT(hs.quyen_truy_cap, '$.dang_ky_truc') = true
            OR JSON_EXTRACT(hs.quyen_truy_cap, '$.so_nhat_ky_online') = true
        )";
    }

    if (!empty($filters['has_account'])) {
        $where_clauses[] = "hs.trang_thai_tai_khoan != 'Chưa cấp TK'";
    }

    if (!empty($where_clauses)) {
        $sql .= " WHERE " . implode(" AND ", $where_clauses);
    }

    $sql .= "
        ORDER BY 
            CAST(SUBSTR(lh.ten_lop, 1, 2) AS INTEGER) ASC,
            SUBSTR(lh.ten_lop, 3, 1) ASC,
            CAST(SUBSTR(lh.ten_lop, 4) AS INTEGER) ASC,
            hs.ten  ASC, 
            hs.ho_dem  ASC
    ";

    // Áp dụng phân trang nếu có
    $limit = null; $offset = null;
    if (is_array($pagination)) {
        $limit = isset($pagination['limit']) ? (int)$pagination['limit'] : null;
        $offset = isset($pagination['offset']) ? (int)$pagination['offset'] : null;
        if ($limit !== null) {
            // MySQL hỗ trợ cả "LIMIT offset, count" hoặc "LIMIT count OFFSET offset"
            $sql .= " LIMIT " . (int)$limit;
            if ($offset !== null && $offset >= 0) {
                $sql .= " OFFSET " . (int)$offset;
            }
        }
    }
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Đếm tổng số học sinh theo cùng điều kiện lọc (phục vụ phân trang)
 */
function count_hoc_sinh(PDO $db, $filters = []) {
    $sql = "SELECT COUNT(*) FROM hoc_sinh hs JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id";
    $where_clauses = [];
    $params = [];
    
    // Removed hs.trang_thai_hoc_tap = 'dang_hoc' filter per user request so they appear with strikethrough
    // Allow graduated students to appear in the list as well per user request
    
    if (!empty($filters['khoi']) && $filters['khoi'] !== 'all') {
        $where_clauses[] = "SUBSTR(lh.ten_lop, 1, 2) = ?";
        $params[] = $filters['khoi'];
    }
    if (!empty($filters['lop_id']) && $filters['lop_id'] !== 'all') {
        $where_clauses[] = "hs.lop_hoc_id = ?";
        $params[] = $filters['lop_id'];
    }
    if (!empty($filters['chuc_vu']) && $filters['chuc_vu'] !== 'all') {
        $where_clauses[] = "hs.chuc_vu = ?";
        $params[] = $filters['chuc_vu'];
    }

    $keyword = trim($filters['keyword'] ?? '');
    if ($keyword !== '') {
        $where_clauses[] = "(hs.ma_hoc_sinh LIKE ? OR CONCAT(TRIM(hs.ho_dem), ' ', TRIM(hs.ten)) LIKE ? )";
        $like = '%' . $keyword . '%';
        $params[] = $like;
        $params[] = $like;
    }
    if (!empty($filters['has_permission'])) {
        $where_clauses[] = "(
            JSON_EXTRACT(hs.quyen_truy_cap, '$.nhap_vi_pham') = true
            OR JSON_EXTRACT(hs.quyen_truy_cap, '$.dang_ky_truc') = true
            OR JSON_EXTRACT(hs.quyen_truy_cap, '$.so_nhat_ky_online') = true
        )";
    }
    if (!empty($filters['has_account'])) {
        $where_clauses[] = "hs.trang_thai_tai_khoan != 'Chưa cấp TK'";
    }
    if (!empty($where_clauses)) {
        $sql .= " WHERE " . implode(" AND ", $where_clauses);
    }
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
}


function get_all_students_with_details($khoi = null, $lop_id = null) {
    $db = get_db_connection();
    // Câu lệnh SQL này được thiết kế để lấy thông tin chi tiết của học sinh
    // kết hợp với thông tin từ bảng lớp học (lop_hoc).
    $sql = "
        SELECT 
            hs.id,
            hs.ma_hoc_sinh, -- Sử dụng mã học sinh thay cho cccd để định danh
            hs.ho_dem,
            hs.ten,
            hs.ngay_sinh,
            hs.gioi_tinh,
            hs.chuc_vu,
            hs.anh_the,
            hs.ma_moet,
            lh.ten_lop,
            lh.giao_vien_chu_nhiem
        FROM hoc_sinh hs
        LEFT JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id
        WHERE hs.trang_thai_hoc_tap = 'dang_hoc'";

    // Thêm điều kiện lọc nếu người dùng chọn khối hoặc lớp
    if ($khoi) {
        $sql .= " AND lh.khoi = :khoi";
    }
    if ($lop_id) {
        $sql .= " AND lh.id = :lop_id";
    }
    // Sắp xếp danh sách theo thứ tự lớp, sau đó theo tên học sinh
    $sql .= " ORDER BY lh.thu_tu, hs.ten, hs.ho_dem";

    $stmt = $db->prepare($sql);

    // Gán giá trị cho các tham số lọc một cách an toàn
    if ($khoi) {
        $stmt->bindValue(':khoi', $khoi, PDO::PARAM_INT);
    }
    if ($lop_id) {
        $stmt->bindValue(':lop_id', $lop_id, PDO::PARAM_INT);
    }

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
/**
 * Lấy thông tin chi tiết của một nhóm học sinh dựa trên danh sách ID của họ.
 * Hàm này được tối ưu cho việc lấy dữ liệu để in thẻ hàng loạt.
 *
 * @param PDO $db Đối tượng kết nối CSDL.
 * @param array $ids Mảng chứa các ID (số nguyên) của học sinh cần lấy thông tin.
 * @return array Trả về một mảng chứa thông tin chi tiết của các học sinh tìm thấy.
 */
function get_hoc_sinh_by_ids(PDO $db, array $ids) {
    // Nếu danh sách ID rỗng, trả về một mảng rỗng ngay lập tức để tránh lỗi
    if (empty($ids)) {
        return [];
    }
    
    // Tạo ra một chuỗi các dấu chấm hỏi (?) tương ứng với số lượng ID
    // Ví dụ: nếu $ids = [1, 2, 3], chuỗi này sẽ là "?,?,?"
    // Đây là cách an toàn nhất để chống lại lỗi SQL Injection khi dùng mệnh đề IN.
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    
    // Câu lệnh SQL này lấy tất cả các cột cần thiết từ bảng hoc_sinh (hs)
    // và join với bảng lop_hoc (lh) để lấy tên lớp.
    $sql = "
        SELECT 
            hs.*, 
            lh.ten_lop
        FROM hoc_sinh hs
        JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id
        WHERE hs.id IN ($placeholders)
    ";
    try {
        // Chuẩn bị câu lệnh SQL
        $stmt = $db->prepare($sql);
        
        // Thực thi câu lệnh, truyền mảng ID vào để lấp đầy các dấu chấm hỏi
        $stmt->execute($ids);
        
        // Lấy tất cả các kết quả và trả về
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        // Ghi lại lỗi ra log của server để quản trị viên có thể xem
        error_log('Lỗi CSDL trong hàm get_hoc_sinh_by_ids: ' . $e->getMessage());
        // Trả về mảng rỗng nếu có lỗi để chương trình không bị dừng đột ngột
        return [];
    }
}



?>