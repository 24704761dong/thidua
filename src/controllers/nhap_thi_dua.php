<?php
// File: src/controllers/nhap_thi_dua.php (Đã gom các tính năng hiển thị, tải mẫu và import)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

global $route;
$db = get_db_connection();

// --- 1. Xử lý Tải File Mẫu ---
if ($route === '/nhap-diem-thi-dua/tai-mau') {
    try {
        $tuan_id = $_GET['tuan_id'] ?? null;
        $nam_hoc_id_cua_tuan = $_SESSION['working_nam_hoc_id'] ?? 1;
        if ($tuan_id) {
            $stmt_tuan = $db->prepare("SELECT nam_hoc_id FROM tuan_hoc WHERE id = ?");
            $stmt_tuan->execute([$tuan_id]);
            $tuan_hoc = $stmt_tuan->fetch();
            if ($tuan_hoc && $tuan_hoc['nam_hoc_id']) {
                $nam_hoc_id_cua_tuan = $tuan_hoc['nam_hoc_id'];
            }
        }

        $sql_lop = "SELECT ten_lop FROM lop_hoc WHERE nam_hoc_id = ? ORDER BY CAST(SUBSTR(ten_lop, 1, 2) AS INTEGER) ASC, SUBSTR(ten_lop, 3, 1) ASC, CAST(SUBSTR(ten_lop, 4) AS INTEGER) ASC";
        $stmt_lop = $db->prepare($sql_lop);
        $stmt_lop->execute([$nam_hoc_id_cua_tuan]);
        $danh_sach_lop = $stmt_lop->fetchAll(PDO::FETCH_COLUMN);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('MauNhapDiemthidua');

        $header = ['Lớp', 'so_tiet_tot', 'so_tiet_tb', 'sdb_tt', 'sdb_ck', 'sdb_nk', 'nhat_ky', 'diem_cong_tru'];
        $sheet->fromArray($header, NULL, 'A1');
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->fromArray(array_map(fn($lop) => [$lop], $danh_sach_lop), NULL, 'A2');
        foreach (range('A', 'H') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        
        $filename = "Mau_Nhap_Diem_Thi_Dua_" . date('Ymd') . ".xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        ob_clean();
        flush();
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    } catch (Exception $e) {
        die("Lỗi khi tạo file mẫu: " . $e->getMessage());
    }
}

// --- 2. Xử lý Import Excel ---
if ($route === '/nhap-diem-thi-dua/import') {
    $tuan_id = $_POST['tuan_id'] ?? null;
    $redirect_url = '/thidua/nhap-diem-thi-dua?tuan_id=' . ($tuan_id ?: '') . '&iframe=1';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$tuan_id || !isset($_FILES['excelFile'])) {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Yêu cầu không hợp lệ.'];
        header('Location: ' . $redirect_url);
        exit();
    }

    try {
        $file = $_FILES['excelFile'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Lỗi trong quá trình tải file lên.');
        }

        $stmt_tuan = $db->prepare("SELECT nam_hoc_id FROM tuan_hoc WHERE id = ?");
        $stmt_tuan->execute([$tuan_id]);
        $tuan_hoc = $stmt_tuan->fetch();
        $nam_hoc_id_cua_tuan = ($tuan_hoc && $tuan_hoc['nam_hoc_id']) ? $tuan_hoc['nam_hoc_id'] : ($_SESSION['working_nam_hoc_id'] ?? 1);

        $stmt_lop = $db->prepare("SELECT ten_lop, id FROM lop_hoc WHERE nam_hoc_id = ?");
        $stmt_lop->execute([$nam_hoc_id_cua_tuan]);
        $ds_lop_hoc_map = $stmt_lop->fetchAll(PDO::FETCH_KEY_PAIR);

        $spreadsheet = IOFactory::load($file['tmp_name']);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();
        array_shift($data); // Bỏ qua dòng tiêu đề

        $db->beginTransaction();
        
        $sql = "
            INSERT INTO thi_dua_tuan (tuan_hoc_id, lop_hoc_id, nguoi_nhap_id, last_updated, so_tiet_tot, so_tiet_tb, sdb_tt, sdb_ck, sdb_nk, nhat_ky, diem_cong_tru)
            VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                so_tiet_tot = VALUES(so_tiet_tot),
                so_tiet_tb = VALUES(so_tiet_tb),
                sdb_tt = VALUES(sdb_tt),
                sdb_ck = VALUES(sdb_ck),
                sdb_nk = VALUES(sdb_nk),
                nhat_ky = VALUES(nhat_ky),
                diem_cong_tru = VALUES(diem_cong_tru),
                nguoi_nhap_id = VALUES(nguoi_nhap_id),
                last_updated = NOW();
        ";
        $stmt = $db->prepare($sql);
        $user_id = $_SESSION['user_id'];
        $updated_count = 0;

        foreach ($data as $row) {
            $ten_lop = trim($row[0] ?? '');
            if (empty($ten_lop) || !isset($ds_lop_hoc_map[$ten_lop])) {
                continue;
            }
            $lop_id = $ds_lop_hoc_map[$ten_lop];
            
            $stmt->execute([
                $tuan_id,
                $lop_id,
                $user_id,
                (int)($row[1] ?? 0),
                (int)($row[2] ?? 0),
                strtoupper(trim($row[3] ?? '')) === 'X' ? 1 : 0,
                strtoupper(trim($row[4] ?? '')) === 'X' ? 1 : 0,
                strtoupper(trim($row[5] ?? '')) === 'X' ? 1 : 0,
                strtoupper(trim($row[6] ?? '')) === 'X' ? 1 : 0,
                (float)($row[7] ?? 0)
            ]);
            $updated_count++;
        }

        $db->commit();
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => "Import thành công! Đã cập nhật dữ liệu cho {$updated_count} lớp."];

    } catch (Throwable $e) {
        if (isset($db) && $db->inTransaction()) {
            $db->rollBack();
        }
        die('<div style="background:#fff;padding:20px;color:red;">Lỗi nghiêm trọng: ' . $e->getMessage() . '<br>' . nl2br($e->getTraceAsString()) . '</div>');
    }

    header('Location: ' . $redirect_url);
    exit();
}

// --- 3. Xử lý Hiển thị Giao diện Nhập Điểm (Mặc định) ---
$tuan_id = $_GET['tuan_id'] ?? null;
if (!$tuan_id) {
    header('Location: /thidua/admin/tuan-hoc?action=select_thidua&iframe=1');
    exit();
}

$stmt_tuan = $db->prepare("SELECT * FROM tuan_hoc WHERE id = ?");
$stmt_tuan->execute([$tuan_id]);
$tuan_hoc = $stmt_tuan->fetch();

if (!$tuan_hoc) {
    header('Location: /thidua/admin/tuan-hoc?action=select_thidua&iframe=1');
    exit();
}

$nam_hoc_id_cua_tuan = $tuan_hoc['nam_hoc_id'] ?: ($_SESSION['working_nam_hoc_id'] ?? 1);

// Lấy danh sách lớp và dữ liệu thi đua đã có của tuần này
$sql = "
    SELECT 
        lh.id as lop_hoc_id, 
        lh.ten_lop,
        tdt.so_tiet_tot,
        tdt.so_tiet_tb,
        tdt.sdb_tt,
        tdt.sdb_ck,
        tdt.sdb_nk,
        tdt.nhat_ky,
        tdt.diem_cong_tru
    FROM lop_hoc lh
    LEFT JOIN thi_dua_tuan tdt ON lh.id = tdt.lop_hoc_id AND tdt.tuan_hoc_id = ?
    WHERE lh.nam_hoc_id = ?
    ORDER BY 
        CAST(SUBSTR(lh.ten_lop, 1, 2) AS INTEGER) ASC, 
        SUBSTR(lh.ten_lop, 3, 1) ASC,
        CAST(SUBSTR(lh.ten_lop, 4) AS INTEGER) ASC
";
$stmt_data = $db->prepare($sql);
$stmt_data->execute([$tuan_id, $nam_hoc_id_cua_tuan]);
$danh_sach_lop = $stmt_data->fetchAll();

require_once __DIR__ . '/../views/nhap_thi_dua.php';