<?php
// File: src/controllers/xuat_ds_truc_tuan.php (Nâng cấp để xuất cả lịch sử đã xóa)

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

try {
    $db = get_db_connection();

    // Lấy TOÀN BỘ lịch sử, bao gồm cả các bản ghi đã được "xóa" (lưu trữ)
    $sql = "
        SELECT 
            th.ten_tuan,
            lh.ten_lop,
            CONCAT(nguoi_gui.ho_dem, ' ', nguoi_gui.ten) as ten_nguoi_gui,
            dkt.thoi_gian_gui,
            dtd.ngay_trong_tuan,
            CONCAT(hs.ho_dem, ' ', hs.ten) as ten_hoc_sinh_truc,
            dkt.trang_thai,
            dkt.trang_thai_luu_tru -- Thêm cột mới
        FROM dang_ky_truc_chi_tiet dtd
        JOIN dang_ky_truc_tuan dkt ON dtd.dang_ky_truc_tuan_id = dkt.id
        JOIN hoc_sinh hs ON dtd.hoc_sinh_id = hs.id
        JOIN hoc_sinh nguoi_gui ON dkt.nguoi_gui_id = nguoi_gui.id
        JOIN lop_hoc lh ON dkt.lop_hoc_id = lh.id
        JOIN tuan_hoc th ON dkt.tuan_hoc_id = th.id
        ORDER BY th.ngay_bat_dau DESC, lh.ten_lop, dtd.ngay_trong_tuan, ten_hoc_sinh_truc
    ";
    $stmt = $db->query($sql);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($results)) {
        echo "<script>alert('Không có dữ liệu đăng ký trực nào trong hệ thống để xuất file.'); history.back();</script>";
        exit();
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('LichSu_DangKyTruc');

    // Thêm cột mới vào header
    $header = ['STT', 'Tuần', 'Lớp', 'Người Gửi DS', 'Thời Gian Gửi', 'Ngày Trực', 'Học Sinh Trực', 'Trạng Thái Duyệt', 'Trạng Thái Lưu Trữ'];
    $sheet->fromArray($header, NULL, 'A1');
    $sheet->getStyle('A1:I1')->getFont()->setBold(true);

    // Ghi dữ liệu
    $rowIndex = 2;
    $stt = 1;
    $days_map = ['Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy', 'Chủ Nhật'];

    foreach ($results as $row) {
        $sheet->setCellValue('A' . $rowIndex, $stt++);
        $sheet->setCellValue('B' . $rowIndex, $row['ten_tuan']);
        $sheet->setCellValue('C' . $rowIndex, $row['ten_lop']);
        $sheet->setCellValue('D' . $rowIndex, $row['ten_nguoi_gui']);
        $sheet->setCellValue('E' . $rowIndex, date('d/m/Y H:i', strtotime($row['thoi_gian_gui'])));
        $sheet->setCellValue('F' . $rowIndex, $days_map[$row['ngay_trong_tuan']]);
        $sheet->setCellValue('G' . $rowIndex, $row['ten_hoc_sinh_truc']);
        $sheet->setCellValue('H' . $rowIndex, $row['trang_thai']);
        // Ghi trạng thái lưu trữ
        $sheet->setCellValue('I' . $rowIndex, $row['trang_thai_luu_tru'] == 1 ? 'Đã xóa' : 'Hoạt động');
        $rowIndex++;
    }

    // Tự động chỉnh độ rộng cột
    foreach (range('A', 'I') as $columnID) {
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }
    
    // Xuất file
    $filename = "LichSu_ToanBo_DangKyTruc_" . date('Ymd') . ".xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    // Xóa tất cả các output buffer trước khi xuất file để tránh file Excel bị lỗi (corrupted) do các warning/notice
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();

} catch (Exception $e) {
    die("Lỗi khi tạo file Excel: " . $e->getMessage());
}