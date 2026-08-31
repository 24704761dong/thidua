<?php
require_once __DIR__ . '/../lib/zalo_auth_middleware.php';

zalo_api_cors_headers('GET, OPTIONS');
zalo_handle_options();

// File: src/controllers/api_zalo_get_surveys.php
$payload = zalo_authenticate_request();
$student_id = $payload['student_id'];
try {
    $db = get_db_connection();

    // 1. TỰ ĐỘNG TẠO BẢNG (AUTO-MIGRATION)
    $db->exec("
        CREATE TABLE IF NOT EXISTS khao_sat (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tieu_de VARCHAR(255) NOT NULL,
            mo_ta TEXT,
            loai_khao_sat VARCHAR(50) DEFAULT 'tu_nguyen',
            han_nop VARCHAR(100),
            banner_url VARCHAR(500),
            nam_hoc_id INT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            status VARCHAR(50) DEFAULT 'active'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    try {
        $db->exec("ALTER TABLE khao_sat ADD COLUMN banner_url VARCHAR(500) NULL");
    } catch (Exception $e) {}

    try {
        $db->exec("ALTER TABLE khao_sat ADD COLUMN style TEXT NULL");
    } catch (Exception $e) {}

    $db->exec("
        CREATE TABLE IF NOT EXISTS khao_sat_cau_hoi (
            id INT AUTO_INCREMENT PRIMARY KEY,
            khao_sat_id INT NOT NULL,
            tieu_de TEXT NOT NULL,
            mo_ta TEXT,
            loai_cau_hoi VARCHAR(50) NOT NULL,
            bat_buoc TINYINT(1) DEFAULT 0,
            tuy_chon TEXT,
            thu_tu INT DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    $db->exec("
        CREATE TABLE IF NOT EXISTS khao_sat_bai_lam (
            id INT AUTO_INCREMENT PRIMARY KEY,
            khao_sat_id INT NOT NULL,
            hoc_sinh_id INT NOT NULL,
            nam_hoc_id INT,
            ngay_nop DATETIME DEFAULT CURRENT_TIMESTAMP,
            status VARCHAR(50) DEFAULT 'completed'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    $db->exec("
        CREATE TABLE IF NOT EXISTS khao_sat_ket_qua (
            id INT AUTO_INCREMENT PRIMARY KEY,
            bai_lam_id INT NOT NULL,
            cau_hoi_id INT NOT NULL,
            gia_tri TEXT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 2. TỰ ĐỘNG SEED DỮ LIỆU MẪU ĐẦY ĐỦ CÁC LOẠI CÂU HỎI (GOOGLE FORMS STYLE)
    $stmt_check = $db->query("SELECT COUNT(*) FROM khao_sat");
    if ($stmt_check->fetchColumn() == 0) {
        // Survey 1
        $stmt_s1 = $db->prepare("INSERT INTO khao_sat (tieu_de, mo_ta, loai_khao_sat, han_nop, banner_url, status) VALUES (?, ?, ?, ?, ?, 'active')");
        $stmt_s1->execute([
            'Khảo sát ý kiến về việc tổ chức hoạt động Ngoại khóa Học kỳ I (2025-2026)',
            'Khảo sát nguyện vọng và ý kiến đóng góp của học sinh về địa điểm và nội dung tổ chức hoạt động ngoại khóa.',
            'bat_buoc',
            '15/11/2025',
            'https://images.unsplash.com/photo-1506744038136-46273834b3fb?w=1600&auto=format&fit=crop&q=80'
        ]);
        $s1_id = $db->lastInsertId();

        $questions_s1 = [
            ['tieu_de' => 'Phần 1: Thông tin chung & Địa điểm', 'mo_ta' => 'Vui lòng cung cấp thông tin cá nhân và lựa chọn địa điểm yêu thích.', 'loai_cau_hoi' => 'section_header', 'bat_buoc' => 0, 'tuy_chon' => json_encode([])],
            ['tieu_de' => 'Họ và tên đầy đủ của bạn', 'mo_ta' => 'Nhập chính xác họ và tên', 'loai_cau_hoi' => 'short_text', 'bat_buoc' => 1, 'tuy_chon' => json_encode([])],
            ['tieu_de' => 'Khối lớp của bạn là gì?', 'mo_ta' => 'Chọn khối lớp hiện tại', 'loai_cau_hoi' => 'dropdown', 'bat_buoc' => 1, 'tuy_chon' => json_encode(['options' => ['Khối 10', 'Khối 11', 'Khối 12']])],
            ['tieu_de' => 'Bạn mong muốn tổ chức hoạt động ngoại khóa ở đâu?', 'mo_ta' => 'Chọn 1 địa điểm hoặc điền địa điểm khác', 'loai_cau_hoi' => 'radio', 'bat_buoc' => 1, 'tuy_chon' => json_encode(['options' => ['Khu du lịch sinh thái', 'Bảo tàng / Di tích lịch sử', 'Cắm trại dã ngoại'], 'has_other' => true])],
            ['tieu_de' => 'Upload ảnh đề xuất địa điểm của bạn (nếu có)', 'mo_ta' => 'Tải lên hình ảnh tham khảo địa điểm bạn muốn (hỗ trợ JPG, PNG)', 'loai_cau_hoi' => 'file_upload', 'bat_buoc' => 0, 'tuy_chon' => json_encode([])],
            
            ['tieu_de' => 'Phần 2: Nội dung hoạt động & Đánh giá', 'mo_ta' => 'Lựa chọn các hoạt động cụ thể và mốc thời gian đề xuất.', 'loai_cau_hoi' => 'section_header', 'bat_buoc' => 0, 'tuy_chon' => json_encode([])],
            ['tieu_de' => 'Những hoạt động nào bạn muốn tham gia?', 'mo_ta' => 'Có thể chọn nhiều mục hoặc thêm mục khác', 'loai_cau_hoi' => 'checkbox', 'bat_buoc' => 1, 'tuy_chon' => json_encode(['options' => ['Teambuilding', 'Thi văn nghệ', 'Trò chơi dân gian', 'Cắm trại ca đêm'], 'has_other' => true])],
            ['tieu_de' => 'Mức độ hào hứng của bạn với hoạt động ngoại khóa này', 'mo_ta' => 'Thang điểm từ 1 (Không hào hứng) đến 10 (Rất hào hứng)', 'loai_cau_hoi' => 'linear_scale', 'bat_buoc' => 1, 'tuy_chon' => json_encode(['scale_min' => 1, 'scale_max' => 10, 'label_min' => 'Không hào hứng', 'label_max' => 'Rất hào hứng'])],
            ['tieu_de' => 'Đánh giá chất lượng tổ chức ngoại khóa năm ngoái', 'mo_ta' => 'Chọn số sao đánh giá', 'loai_cau_hoi' => 'star_rating', 'bat_buoc' => 1, 'tuy_chon' => json_encode([])],
            
            ['tieu_de' => 'Phần 3: Đánh giá chi tiết & Thời gian', 'mo_ta' => 'Đánh giá các yếu tố và đề xuất mốc thời gian cụ thể.', 'loai_cau_hoi' => 'section_header', 'bat_buoc' => 0, 'tuy_chon' => json_encode([])],
            ['tieu_de' => 'Đánh giá mức độ quan trọng của các yếu tố sau:', 'mo_ta' => 'Chọn 1 mức độ cho mỗi hàng', 'loai_cau_hoi' => 'grid_radio', 'bat_buoc' => 1, 'tuy_chon' => json_encode(['grid_rows' => ['Chi phí hợp lý', 'Địa điểm đẹp', 'An toàn', 'Thức ăn ngon'], 'grid_cols' => ['Rất quan trọng', 'Bình thường', 'Không quan trọng']])],
            ['tieu_de' => 'Thời gian rảnh của bạn trong các tuần tới:', 'mo_ta' => 'Có thể chọn nhiều mốc thời gian', 'loai_cau_hoi' => 'grid_checkbox', 'bat_buoc' => 0, 'tuy_chon' => json_encode(['grid_rows' => ['Sáng Thứ 7', 'Chiều Thứ 7', 'Sáng Chủ nhật'], 'grid_cols' => ['Tuần 1', 'Tuần 2', 'Tuần 3']])],
            ['tieu_de' => 'Ngày khởi hành đề xuất', 'mo_ta' => 'Chọn ngày mong muốn', 'loai_cau_hoi' => 'date', 'bat_buoc' => 1, 'tuy_chon' => json_encode([])],
            ['tieu_de' => 'Giờ xuất phát đề xuất', 'mo_ta' => 'Chọn giờ khởi hành', 'loai_cau_hoi' => 'time', 'bat_buoc' => 1, 'tuy_chon' => json_encode([])],
            ['tieu_de' => 'Ý kiến đóng góp khác của bạn (nếu có)', 'mo_ta' => 'Nhập chi tiết các góp ý khác', 'loai_cau_hoi' => 'long_text', 'bat_buoc' => 0, 'tuy_chon' => json_encode([])],
        ];

        foreach ($questions_s1 as $idx => $q) {
            $stmt_q = $db->prepare("INSERT INTO khao_sat_cau_hoi (khao_sat_id, tieu_de, mo_ta, loai_cau_hoi, bat_buoc, tuy_chon, thu_tu) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt_q->execute([$s1_id, $q['tieu_de'], $q['mo_ta'], $q['loai_cau_hoi'], $q['bat_buoc'], $q['tuy_chon'], $idx + 1]);
        }

        // Survey 2
        $stmt_s2 = $db->prepare("INSERT INTO khao_sat (tieu_de, mo_ta, loai_khao_sat, han_nop, banner_url, status) VALUES (?, ?, ?, ?, ?, 'active')");
        $stmt_s2->execute([
            'Đánh giá chất lượng cơ sở vật chất và Dịch vụ bán trú',
            'Phiếu khảo sát nhằm nâng cao chất lượng bữa ăn trưa và hệ thống điều hòa tại các phòng học bán trú.',
            'tu_nguyen',
            '20/11/2025',
            'https://images.unsplash.com/photo-1577153052146-24987747a8f3?w=1600&auto=format&fit=crop&q=80'
        ]);
        $s2_id = $db->lastInsertId();

        $questions_s2 = [
            ['tieu_de' => 'Đánh giá chất lượng bữa ăn trưa', 'mo_ta' => 'Chọn mức độ hài lòng', 'loai_cau_hoi' => 'star_rating', 'bat_buoc' => 1, 'tuy_chon' => json_encode([])],
            ['tieu_de' => 'Món ăn nào bạn muốn bổ sung vào thực đơn?', 'mo_ta' => 'Ghi tên món ăn', 'loai_cau_hoi' => 'short_text', 'bat_buoc' => 0, 'tuy_chon' => json_encode([])],
            ['tieu_de' => 'Ý kiến về hệ thống điều hòa và phòng ngủ', 'mo_ta' => 'Chi tiết phản hồi', 'loai_cau_hoi' => 'long_text', 'bat_buoc' => 0, 'tuy_chon' => json_encode([])],
        ];
        foreach ($questions_s2 as $idx => $q) {
            $stmt_q = $db->prepare("INSERT INTO khao_sat_cau_hoi (khao_sat_id, tieu_de, mo_ta, loai_cau_hoi, bat_buoc, tuy_chon, thu_tu) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt_q->execute([$s2_id, $q['tieu_de'], $q['mo_ta'], $q['loai_cau_hoi'], $q['bat_buoc'], $q['tuy_chon'], $idx + 1]);
        }
    }

    // 3. TRUY VẤN DANH SÁCH BÀI KHẢO SÁT VÀ TÌM KIẾM TRẠNG THÁI BÀI LÀM CỦA HỌC SINH
    $stmt_surveys = $db->query("SELECT * FROM khao_sat WHERE status = 'active' ORDER BY created_at DESC");
    $all_surveys = $stmt_surveys->fetchAll(PDO::FETCH_ASSOC);

    $stmt_completed = $db->prepare("SELECT khao_sat_id, ngay_nop FROM khao_sat_bai_lam WHERE hoc_sinh_id = ?");
    $stmt_completed->execute([$student_id]);
    $completed_map = [];
    while ($row = $stmt_completed->fetch(PDO::FETCH_ASSOC)) {
        $completed_map[$row['khao_sat_id']] = $row['ngay_nop'];
    }

    $pending = [];
    $completed = [];

    foreach ($all_surveys as $s) {
        $item = [
            'id' => (string)$s['id'],
            'title' => $s['tieu_de'],
            'badge' => $s['loai_khao_sat'] === 'bat_buoc' ? 'Bắt buộc' : 'Tự nguyện',
            'badgeType' => $s['loai_khao_sat'] === 'bat_buoc' ? 'required' : 'optional',
            'dueDate' => !empty($s['han_nop']) ? date('H:i - d/m/Y', strtotime(str_replace('/', '-', $s['han_nop']))) : 'Không giới hạn',
            'banner_url' => $s['banner_url'] ?? '',
            'description' => $s['mo_ta']
        ];
        
        $is_expired = false;
        if (!empty($s['han_nop'])) {
            $expire_time = strtotime(str_replace('/', '-', $s['han_nop']));
            if ($expire_time !== false && time() > $expire_time) {
                $is_expired = true;
                $item['isExpired'] = true;
            }
        }

        if (isset($completed_map[$s['id']])) {
            $item['completed'] = true;
            $item['badge'] = 'Đã hoàn thành';
            $item['badgeType'] = 'completed';
            $item['submittedAt'] = date('d/m/Y H:i', strtotime($completed_map[$s['id']]));
            $completed[] = $item;
        } else {
            $item['completed'] = false;
            if ($is_expired) {
                $item['badge'] = 'Đã hết hạn';
                $item['badgeType'] = 'expired';
            }
            $pending[] = $item;
        }
    }

    echo json_encode([
        'success' => true,
        'pending' => $pending,
        'completed' => $completed
    ]);

} catch (Exception $e) {
    http_response_code(500);
    zalo_api_error('Lỗi hệ thống, vui lòng thử lại sau.', 500, $e);
}
