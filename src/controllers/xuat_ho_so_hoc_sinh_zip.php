<?php
// File: src/controllers/xuat_ho_so_hoc_sinh_zip.php
set_time_limit(0);
ini_set('memory_limit', '1024M');

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../vendor/autoload.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}

// GIẢI PHÓNG KHÓA SESSION ĐỂ TRÁNH LAG HỆ THỐNG
$session_id = session_id();
session_write_close();

require_once __DIR__ . '/../../config/database.php';
$db = get_db_connection();
$current_nam_hoc = $_SESSION['current_nam_hoc_id'] ?? 1;

$lop_id = $_GET['lop_id'] ?? 'all';
$admin_name = $_SESSION['user_ten'] ?? 'Quản trị viên';

function to_unsigned_string($str) {
    $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", "a", $str);
    $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", "e", $str);
    $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", "i", $str);
    $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", "o", $str);
    $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", "u", $str);
    $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", "y", $str);
    $str = preg_replace("/(đ)/", "d", $str);
    $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", "A", $str);
    $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", "E", $str);
    $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", "I", $str);
    $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", "O", $str);
    $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", "U", $str);
    $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", "Y", $str);
    $str = preg_replace("/(Đ)/", "D", $str);
    $str = preg_replace('/[^A-Za-z0-9\-_.]/', '_', $str);
    $str = preg_replace('/_+/', '_', $str);
    return $str;
}

try {
    $sql = "SELECT hs.*, lh.ten_lop, lh.gvcn_ten FROM hoc_sinh hs LEFT JOIN lop_hoc lh ON hs.lop_hoc_id = lh.id WHERE hs.nam_hoc_id = ?";
    $params = [$current_nam_hoc];
    if ($lop_id !== 'all') {
        $sql .= " AND hs.lop_hoc_id = ?";
        $params[] = $lop_id;
    }
    $sql .= " ORDER BY lh.ten_lop, hs.ten, hs.ho_dem";
    
    $stmt_hs = $db->prepare($sql);
    $stmt_hs->execute($params);
    $danh_sach_hs = $stmt_hs->fetchAll(PDO::FETCH_ASSOC);

    if (empty($danh_sach_hs)) {
        die('Không có dữ liệu học sinh để xuất.');
    }

    $zip_filename = 'HoSoHocSinh_' . ($lop_id !== 'all' ? $danh_sach_hs[0]['ten_lop'] : 'TatCa') . '_' . date('Ymd_His') . '.zip';
    $zip_filepath = sys_get_temp_dir() . '/' . $zip_filename;
    $zip = new ZipArchive();
    if ($zip->open($zip_filepath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        die('Lỗi: Không thể tạo file nén ZIP.');
    }

    $temp_files = [];

    foreach ($danh_sach_hs as $hoc_sinh) {
        $hoc_sinh_id = $hoc_sinh['id'];
        
        $stmt_kt = $db->prepare("SELECT kt.ngay_khen_thuong, kt.ten_khen_thuong, kt.cap_khen_thuong, kt.so_quyet_dinh FROM khen_thuong kt WHERE (kt.hoc_sinh_id = ? OR kt.hoc_sinh_id IN (SELECT id FROM quatrinh_hoc_tap WHERE ma_hoc_sinh = ?)) AND kt.loai = 'ca_nhan' ORDER BY kt.ngay_khen_thuong DESC");
        $stmt_kt->execute([$hoc_sinh_id, $hoc_sinh['ma_hoc_sinh']]);
        $lich_su_khen_thuong = $stmt_kt->fetchAll(PDO::FETCH_ASSOC);
    
        $stmt_vp = $db->prepare("
            SELECT vphs.ngay_vi_pham, (CONCAT(hs.ho_dem, ' ', hs.ten)) as ho_ten, chvp.ten_vi_pham, vphs.ghi_chu 
            FROM vi_pham_hoc_sinh vphs 
            JOIN cau_hinh_vi_pham chvp ON vphs.vi_pham_id = chvp.id 
            JOIN quatrinh_hoc_tap qt ON vphs.hoc_sinh_id = qt.id JOIN hoc_sinh hs ON hs.ma_hoc_sinh = qt.ma_hoc_sinh AND hs.nam_hoc_id = qt.nam_hoc_id
            WHERE hs.id = ? ORDER BY vphs.ngay_vi_pham DESC
        ");
        $stmt_vp->execute([$hoc_sinh_id]);
        $lich_su_vi_pham = $stmt_vp->fetchAll(PDO::FETCH_ASSOC);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Times New Roman')->setSize(11);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('HoSoTongHop');
        
        $sheet->mergeCells('A1:C1')->setCellValue('A1', 'TRƯỜNG THPT BÌNH SƠN');
        $sheet->mergeCells('A2:C2')->setCellValue('A2', 'HỆ THỐNG ĐÁNH GIÁ THI ĐUA');
        $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2')->getFont()->setBold(true);
    
        $sheet->mergeCells('A4:E4')->setCellValue('A4', 'HỒ SƠ HỌC SINH TỔNG HỢP');
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(14);
        
        $current_row = 6;
        $sheet->mergeCells("A{$current_row}:E{$current_row}")->setCellValue("A{$current_row}", 'I. THÔNG TIN CÁ NHÂN');
        $sheet->getStyle("A{$current_row}")->getFont()->setBold(true)->setSize(12);
        $current_row++;
        
        $temp_avatar = null;
        if (!empty($hoc_sinh['anh_the']) || !empty($hoc_sinh['anh_the_cloud_key'])) {
            $imagePath = null;
            $driver = $hoc_sinh['anh_the_driver'] ?? 'local';
            if ($driver === 'local' || empty($driver)) {
                $localCandidate = __DIR__ . '/../../public/assets/anh_the/' . ltrim($hoc_sinh['anh_the'], '/');
                if (file_exists($localCandidate)) { $imagePath = $localCandidate; }
            } else {
                $key = $hoc_sinh['anh_the_cloud_key'] ?? $hoc_sinh['anh_the'];
                $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . "/thidua/api/get-presigned-url?key=" . urlencode($key) . "&driver=" . urlencode($driver) . "&inline=1";
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                if ($session_id) { curl_setopt($ch, CURLOPT_COOKIE, session_name() . "=" . $session_id); }
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $imgData = curl_exec($ch);
                $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                if ($httpcode == 200 && $imgData) {
                    $temp_avatar = sys_get_temp_dir() . '/' . uniqid('avatar_') . '.jpg';
                    file_put_contents($temp_avatar, $imgData);
                    $imagePath = $temp_avatar;
                }
            }
            if ($imagePath && file_exists($imagePath)) {
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setName('AnhThe');
                $drawing->setDescription('Ảnh thẻ học sinh');
                $drawing->setPath($imagePath);
                $drawing->setCoordinates('D' . $current_row); 
                $drawing->setHeight(160); 
                $drawing->setOffsetX(10);
                $drawing->setOffsetY(5);
                $drawing->setWorksheet($sheet);
                $sheet->mergeCells("D{$current_row}:E" . ($current_row + 4));
            }
        }
        
        $dia_chi_parts = [];
        if (!empty($hoc_sinh['dia_chi_chi_tiet'])) $dia_chi_parts[] = $hoc_sinh['dia_chi_chi_tiet'];
        if (!empty($hoc_sinh['ap_khupho'])) $dia_chi_parts[] = $hoc_sinh['ap_khupho'];
        if (!empty($hoc_sinh['xa_phuong'])) $dia_chi_parts[] = $hoc_sinh['xa_phuong'];
        if (!empty($hoc_sinh['tinh_thanhpho'])) $dia_chi_parts[] = $hoc_sinh['tinh_thanhpho'];
        $dia_chi_full = !empty($dia_chi_parts) ? implode(', ', $dia_chi_parts) : 'Chưa có';

        $info_labels = ['Họ và tên:', 'Số CCCD:', 'Lớp:', 'Niên khóa:', 'GVCN:', 'Ngày sinh:', 'Địa chỉ:', 'Trạng thái:'];
        $info_values = [
            $hoc_sinh['ho_dem'] . ' ' . $hoc_sinh['ten'],
            $hoc_sinh['ma_hoc_sinh'],
            $hoc_sinh['ten_lop'],
            $hoc_sinh['nien_khoa'] ?? 'Chưa cập nhật',
            $hoc_sinh['gvcn_ten'] ?? 'Chưa có',
            (function($date_str) {
                $date = DateTime::createFromFormat('Y-m-d', $date_str) ?: DateTime::createFromFormat('d/m/Y', $date_str);
                return $date ? $date->format('d/m/Y') : 'Không hợp lệ';
            })($hoc_sinh['ngay_sinh']),
            $dia_chi_full,
            (($hoc_sinh['trang_thai_hoc_tap'] ?? 'dang_hoc') === 'nghi_hoc' ? 'Đã nghỉ học' : (($hoc_sinh['trang_thai_hoc_tap'] ?? 'dang_hoc') === 'da_tot_nghiep' ? 'Đã tốt nghiệp' : 'Đang học'))
        ];
    
        foreach ($info_labels as $index => $label) {
            $sheet->setCellValue("A{$current_row}", $label);
            $sheet->mergeCells("B{$current_row}:C{$current_row}")->setCellValue("B{$current_row}", $info_values[$index]);
            $current_row++;
        }
        $sheet->getStyle("A".($current_row - count($info_labels)).":A".($current_row-1))->getFont()->setBold(true);
        $sheet->getStyle("B".($current_row - count($info_labels)).":C".($current_row-1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $current_row++;
    
        $sheet->mergeCells("A{$current_row}:E{$current_row}")->setCellValue("A{$current_row}", 'II. LỊCH SỬ KHEN THƯỞNG');
        $sheet->getStyle("A{$current_row}")->getFont()->setBold(true)->setSize(12);
        $current_row++;
        if (!empty($lich_su_khen_thuong)) {
            $header_kt = ['STT', 'Ngày KT', 'Tên KT', 'Cấp KT', 'Số QĐ'];
            $sheet->fromArray($header_kt, NULL, "A{$current_row}");
            $header_range = "A{$current_row}:E{$current_row}";
            $sheet->getStyle($header_range)->getFont()->setBold(true);
            $sheet->getStyle($header_range)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $start_data_row = ++$current_row;
            foreach($lich_su_khen_thuong as $index => $item) {
                $sheet->fromArray([$index + 1, date('d/m/Y', strtotime($item['ngay_khen_thuong'])), $item['cap_khen_thuong'], $item['ten_khen_thuong'], $item['so_quyet_dinh']], NULL, "A{$current_row}");
                $current_row++;
            }
            $table_range = "A".($start_data_row-1).":E".($current_row-1);
            $sheet->getStyle($table_range)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        } else {
            $sheet->setCellValue("A{$current_row}", 'Chưa có khen thưởng nào.');
            $current_row++;
        }
        $current_row++;
    
        $sheet->mergeCells("A{$current_row}:E{$current_row}")->setCellValue("A{$current_row}", 'III. LỊCH SỬ VI PHẠM');
        $sheet->getStyle("A{$current_row}")->getFont()->setBold(true)->setSize(12);
        $current_row++;
        if (!empty($lich_su_vi_pham)) {
            $header_vp = ['STT', 'Ngày VP', 'Họ và tên', 'Tên Lỗi', 'Ghi Chú'];
            $sheet->fromArray($header_vp, NULL, "A{$current_row}");
            $header_range = "A{$current_row}:E{$current_row}"; 
            $sheet->getStyle($header_range)->getFont()->setBold(true);
            $sheet->getStyle($header_range)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $start_data_row = ++$current_row;
            foreach($lich_su_vi_pham as $index => $item) {
                $sheet->fromArray([$index + 1, date('d/m/Y', strtotime($item['ngay_vi_pham'])), $item['ho_ten'], $item['ten_vi_pham'], $item['ghi_chu']], NULL, "A{$current_row}");
                $current_row++;
            }
            $table_range = "A".($start_data_row-1).":E".($current_row-1); 
            $sheet->getStyle($table_range)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        } else {
            $sheet->setCellValue("A{$current_row}", 'Không có vi phạm nào.');
            $current_row++;
        }
        
        $current_row += 2;
        $sheet->mergeCells("D{$current_row}:E{$current_row}")->setCellValue('D'.$current_row, 'Đồng Nai, ngày '.date('d').' tháng '.date('m').' năm '.date('Y'));
        $sheet->getStyle("D{$current_row}")->getFont()->setItalic(true);
        $sheet->getStyle("D{$current_row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $current_row++;
        $sheet->mergeCells("D{$current_row}:E{$current_row}")->setCellValue('D'.$current_row, 'NGƯỜI LẬP BẢNG');
        $sheet->getStyle("D{$current_row}")->getFont()->setBold(true);
        $sheet->getStyle("D{$current_row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $current_row += 5;
        $sheet->mergeCells("D{$current_row}:E{$current_row}")->setCellValue('D'.$current_row, $admin_name);
        $sheet->getStyle("D{$current_row}")->getFont()->setBold(true);
        $sheet->getStyle("D{$current_row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    
        $sheet->getColumnDimension('A')->setWidth(15); 
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(30);

        $temp_excel = sys_get_temp_dir() . '/' . uniqid('hs_') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($temp_excel);
        
        $ten_lop_safe = to_unsigned_string($hoc_sinh['ten_lop']);
        $ho_ten_safe = to_unsigned_string($hoc_sinh['ho_dem'] . ' ' . $hoc_sinh['ten']);
        $ma_hs_safe = to_unsigned_string($hoc_sinh['ma_hoc_sinh']);
        
        $file_name_in_zip = $ten_lop_safe . '/' . $ho_ten_safe . '_' . $ma_hs_safe . '.xlsx';
        
        $zip->addFile($temp_excel, $file_name_in_zip);
        
        $temp_files[] = $temp_excel;
        if ($temp_avatar) { $temp_files[] = $temp_avatar; }
    }

    $zip->close();
    
    foreach ($temp_files as $f) {
        @unlink($f);
    }

    if (ob_get_length()) ob_clean();
    header('Content-Type: application/zip');
    header('Content-disposition: attachment; filename="' . $zip_filename . '"');
    header('Content-Length: ' . filesize($zip_filepath));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    readfile($zip_filepath);
    @unlink($zip_filepath);
    exit();

} catch (Exception $e) {
    die("Đã xảy ra lỗi: " . $e->getMessage());
}
