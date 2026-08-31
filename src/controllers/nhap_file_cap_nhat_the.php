<?php
// File: src/controllers/nhap_file_cap_nhat_the.php (Đã đồng bộ Premium Tailwind UI & Iframe Preservation)

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    header('Location: /thidua/tracuu');
    exit();
}

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// --- PHẦN 3: XỬ LÝ KHI NGƯỜI DÙNG XÁC NHẬN IMPORT TỪ TRANG XEM TRƯỚC ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'confirm_import') {
    $preview_data = $_SESSION['import_preview_data'] ?? [];
    
    if (empty($preview_data)) {
        $_SESSION['error_message'] = "Không có dữ liệu để import. Vui lòng thử lại.";
        header('Location: /thidua/admin/the-hoc-sinh/danh-sach?iframe=1');
        exit();
    }

    $db = get_db_connection();
    $updated_count = 0;
    try {
        $db->beginTransaction();
        $update_stmt = $db->prepare("UPDATE hoc_sinh SET anh_the = ?, ma_moet = ? WHERE ma_hoc_sinh = ?");

        foreach ($preview_data as $row) {
            // Chỉ import những dòng đã xác nhận là HỢP LỆ
            if ($row['status'] === 'HỢP LỆ') {
                $anh_the_final = $row['anh_the'] === '' ? null : $row['anh_the'];
                $ma_moet_final = $row['ma_moet'] === '' ? null : $row['ma_moet'];
                $update_stmt->execute([$anh_the_final, $ma_moet_final, $row['ma_hoc_sinh']]);
                if ($update_stmt->rowCount() > 0) {
                    $updated_count++;
                }
            }
        }
        $db->commit();
        $_SESSION['success_message'] = "Đã cập nhật thành công thông tin cho {$updated_count} học sinh.";
    } catch (Exception $e) {
        if ($db->inTransaction()) $db->rollBack();
        $_SESSION['error_message'] = "Lỗi khi cập nhật CSDL: " . $e->getMessage();
    }
    
    // Xóa session sau khi hoàn tất
    unset($_SESSION['import_preview_data']);
    unset($_SESSION['import_log']);
    header('Location: /thidua/admin/the-hoc-sinh/danh-sach?iframe=1');
    exit();
}


// --- PHẦN 2: XỬ LÝ KHI NGƯỜI DÙNG UPLOAD FILE LÊN ĐẦU ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file']['tmp_name'];
    
    try {
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getSheet(0);
        $highestRow = $sheet->getHighestRow();

        $db = get_db_connection();
        // Lấy tất cả mã học sinh trong CSDL để đối chiếu
        $stmt_all_hs = $db->query("SELECT ma_hoc_sinh FROM hoc_sinh");
        $all_ma_hs_in_db = $stmt_all_hs->fetchAll(PDO::FETCH_COLUMN, 0);
        
        $preview_data = [];
        $log = ['total_rows' => 0, 'valid_rows' => 0, 'not_found_rows' => 0, 'empty_ma_hs_rows' => 0];

        for ($row_index = 2; $row_index <= $highestRow; $row_index++) {
            $log['total_rows']++;
            $row_data = [
                'ma_hoc_sinh' => trim((string)$sheet->getCell('A' . $row_index)->getFormattedValue()),
                'ho_ten'      => trim((string)$sheet->getCell('B' . $row_index)->getValue()),
                'anh_the'     => trim((string)$sheet->getCell('D' . $row_index)->getValue()),
                'ma_moet'     => trim((string)$sheet->getCell('E' . $row_index)->getValue()),
                'status'      => '',
                'message'     => ''
            ];

            if (empty($row_data['ma_hoc_sinh'])) {
                $row_data['status'] = 'LỖI';
                $row_data['message'] = 'Cột mã học sinh bị trống.';
                $log['empty_ma_hs_rows']++;
            } elseif (in_array($row_data['ma_hoc_sinh'], $all_ma_hs_in_db)) {
                $row_data['status'] = 'HỢP LỆ';
                $row_data['message'] = 'OK - Sẵn sàng để cập nhật.';
                $log['valid_rows']++;
            } else {
                $row_data['status'] = 'LỖI';
                $row_data['message'] = 'Không tìm thấy mã học sinh này trong CSDL.';
                $log['not_found_rows']++;
            }
            $preview_data[] = $row_data;
        }

        // Lưu dữ liệu xem trước và log vào session
        $_SESSION['import_preview_data'] = $preview_data;
        $_SESSION['import_log'] = $log;

        // Chuyển hướng đến trang xem trước kèm iframe=1
        header('Location: /thidua/admin/the-hoc-sinh/xem-truoc-import?iframe=1');
        exit();

    } catch (Exception $e) {
        $_SESSION['error_message'] = "Lỗi khi đọc file Excel: " . $e->getMessage();
        header('Location: /thidua/admin/the-hoc-sinh/nhap-file-cap-nhat?iframe=1');
        exit();
    }
}

// Xử lý khi người dùng nhấn Hủy bỏ
if(isset($_GET['action']) && $_GET['action'] === 'cancel') {
    unset($_SESSION['import_preview_data']);
    unset($_SESSION['import_log']);
    header('Location: /thidua/admin/the-hoc-sinh/danh-sach?iframe=1');
    exit();
}

// --- PHẦN 1: HIỂN THỊ GIAO DIỆN UPLOAD FILE BAN ĐẦU ---
$page_title = 'Nhập File Cập Nhật Thông Tin Thẻ';
require_once __DIR__ . '/../views/partials/admin_header.php';
?>
<style>
    body { background-color: #f4f7f9; }
</style>

<div class="w-full max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- HEADER -->
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8 border-b border-slate-200 pb-4">
        <h1 class="text-xl font-bold text-[#224397] uppercase flex items-center gap-2 m-0">
            <svg xmlns="http://www.w3.org/2000/svg" width="1.3em" height="1.3em" fill="currentColor" class="bi bi-upload text-[#FAB723]" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/><path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708z"/></svg>
            Nhập File Cập Nhật Thông Tin Thẻ
        </h1>
        <a href="/thidua/admin/the-hoc-sinh/danh-sach?iframe=1" class="px-4 py-2 bg-white border border-slate-300 rounded-xl text-slate-700 hover:bg-slate-50 hover:text-[#224397] transition-all duration-200 font-bold flex items-center gap-1.5 text-sm shadow-sm text-decoration-none">
            <svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/></svg> 
            Quay lại Danh sách
        </a>
    </div>

    <!-- CARD FORM -->
    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-8">
        <div class="flex items-start gap-4 mb-6 bg-indigo-50 border border-indigo-100 rounded-xl p-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="2em" height="2em" fill="currentColor" class="bi bi-info-circle-fill text-[#224397] flex-shrink-0 mt-0.5" viewBox="0 0 16 16"><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2"/></svg>
            <div>
                <h3 class="text-base font-bold text-[#224397] mb-1">Hướng dẫn nạp file Excel</h3>
                <p class="text-sm text-slate-600 mb-0 leading-relaxed">
                    Vui lòng sử dụng file mẫu đã được xuất từ hệ thống (hoặc có cấu trúc tương ứng) để đảm bảo đúng định dạng. Hệ thống sẽ dựa vào cột <strong>`ma_hoc_sinh`</strong> (Mã học sinh) để xác thực và cập nhật thông tin Ảnh thẻ & Mã MOET.
                </p>
            </div>
        </div>

        <form action="/thidua/admin/the-hoc-sinh/nhap-file-cap-nhat?iframe=1" method="POST" enctype="multipart/form-data" class="space-y-6 m-0">
            <div>
                <label for="excel_file" class="block text-sm font-bold text-slate-800 mb-2">Chọn file Excel (.xlsx):</label>
                <div class="relative border-2 border-dashed border-slate-300 hover:border-[#224397] rounded-xl p-6 bg-slate-50 text-center transition-all cursor-pointer group">
                    <input class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" type="file" id="excel_file" name="excel_file" accept=".xlsx" required onchange="updateFilename(this)">
                    <div id="uploadPlaceholder" class="space-y-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="2.5em" height="2.5em" fill="currentColor" class="bi bi-file-earmark-spreadsheet text-slate-400 group-hover:text-[#224397] mx-auto transition-colors" viewBox="0 0 16 16"><path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V9H3V2a1 1 0 0 1 1-1h5.5zM3 12v-2h2v2zm0 1h2v2H4a1 1 0 0 1-1-1zm3 2v-2h3v2zm4 0v-2h3v1a1 1 0 0 1-1 1zm3-3h-3v-2h3zm-7 0v-2h3v2z"/></svg>
                        <p class="text-sm font-medium text-slate-600 mb-0">Nhấn vào đây để chọn file hoặc kéo thả file vào khung này</p>
                        <p class="text-xs text-slate-400 mb-0">Định dạng hỗ trợ: .xlsx</p>
                    </div>
                    <div id="selectedFileInfo" class="hidden flex items-center justify-center gap-3 py-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="2em" height="2em" fill="currentColor" class="bi bi-file-earmark-excel-fill text-emerald-600 flex-shrink-0" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M5.884 6.68 8 9.219l2.116-2.54a.5.5 0 1 1 .768.641L8.651 10l2.233 2.68a.5.5 0 0 1-.768.64L8 10.781l-2.116 2.54a.5.5 0 0 1-.768-.641L7.349 10 5.116 7.32a.5.5 0 1 1 .768-.64z"/></svg>
                        <span id="fileNameDisplay" class="text-base font-bold text-slate-800"></span>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="submit" class="px-6 py-3 bg-[#224397] hover:bg-[#224397]/90 text-white rounded-xl font-bold text-sm shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-cloud-arrow-up-fill" viewBox="0 0 16 16"><path d="M8 2a5.53 5.53 0 0 0-3.594 1.342c-.766.66-1.321 1.52-1.464 2.383C1.266 6.095 0 7.555 0 9.318 0 11.366 1.708 13 3.781 13h8.906C14.502 13 16 11.57 16 9.773c0-1.636-1.242-2.969-2.834-3.194C12.923 3.999 10.69 2 8 2m2.354 5.146a.5.5 0 0 1-.708.708L8.5 6.707V10.5a.5.5 0 0 1-1 0V6.707L6.354 7.854a.5.5 0 1 1-.708-.708l2-2a.5.5 0 0 1 .708 0z"/></svg> 
                    Xem Trước Dữ Liệu Import
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function updateFilename(input) {
    const placeholder = document.getElementById('uploadPlaceholder');
    const info = document.getElementById('selectedFileInfo');
    const display = document.getElementById('fileNameDisplay');
    
    if (input.files && input.files[0]) {
        placeholder.classList.add('hidden');
        info.classList.remove('hidden');
        display.textContent = input.files[0].name;
    } else {
        placeholder.classList.remove('hidden');
        info.classList.add('hidden');
        display.textContent = '';
    }
}
</script>

<?php require_once __DIR__ . '/../views/partials/admin_footer.php'; ?>
