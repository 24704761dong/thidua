<?php
// File: src/views/admin_quan_ly_email_hoc_sinh.php
require_once __DIR__ . '/../../config/database.php';
$db = get_db_connection();

$page_title = "Quản Lý Email Học Sinh";
require 'partials/admin_header.php';

// Lấy cài đặt hệ thống
$stmt_allow = $db->query("SELECT setting_value FROM he_thong_cai_dat WHERE setting_key = 'allow_email_request' ORDER BY nam_hoc_id DESC LIMIT 1");
$allow_request = $stmt_allow ? $stmt_allow->fetchColumn() : null;
$is_allowed = ($allow_request === '1');

// Lấy danh sách niên khóa
$stmt_nk = $db->query("SELECT DISTINCT nien_khoa FROM hoc_sinh WHERE nien_khoa IS NOT NULL AND nien_khoa != '' ORDER BY nien_khoa DESC");
$danh_sach_nien_khoa = $stmt_nk->fetchAll(PDO::FETCH_COLUMN);

// Lấy danh sách lớp (theo năm học hiện tại)
$current_nam_hoc = $_SESSION['current_nam_hoc_id'] ?? 1;
$stmt_lop = $db->prepare("SELECT id, ten_lop FROM lop_hoc WHERE nam_hoc_id = ? ORDER BY CAST(SUBSTR(ten_lop, 1, 2) AS UNSIGNED) ASC, SUBSTR(ten_lop, 3, 1) ASC, CAST(SUBSTR(ten_lop, 4) AS UNSIGNED) ASC");
$stmt_lop->execute([$current_nam_hoc]);
$danh_sach_lop = $stmt_lop->fetchAll(PDO::FETCH_ASSOC);

// Trích xuất danh sách khối từ lớp
$danh_sach_khoi = [];
foreach ($danh_sach_lop as $lop) {
    $khoi = substr($lop['ten_lop'], 0, 2);
    if (!in_array($khoi, $danh_sach_khoi)) {
        $danh_sach_khoi[] = $khoi;
    }
}
?>
<link rel="stylesheet" href="/thidua/public/assets/libs/dataTables.tailwindcss.min.css">
<style>
    /* ----- Bảng màu và biến CSS hiện đại ----- */
    :root {
        --primary-blue: #00a8e8;
        --primary-green: #97c93c;
        --dark-blue: #2c3e50;
        --text-primary: #1d2d35;
        --text-secondary: #5a6a72;
        --bg-light: #f4f7f9;
        --card-border: #e9ecef;
    }
    
    body {
        background-color: var(--bg-light);
    }

    /* ----- Thiết kế bảng chính ----- */
    #emailTable {
        border: 1px solid rgba(34, 67, 151, 0.25);
        border-collapse: collapse;
        width: 100%;
    }
    #emailTable thead th {
        background-color: rgba(34, 67, 151, 0.08);
        color: #224397;
        font-weight: 800;
        text-transform: uppercase;
        font-size: 0.88rem;
        text-align: center;
        padding: 0.75rem 1rem;
        border: 1px solid rgba(34, 67, 151, 0.25);
    }
    #emailTable td {
        padding: 0.75rem 1rem;
        border: 1px solid rgba(34, 67, 151, 0.25);
        vertical-align: middle;
        font-size: 0.85rem;
        font-weight: 600;
        color: #1e293b;
    }
    #emailTable tbody tr:hover {
        background-color: rgba(34, 67, 151, 0.05) !important;
    }

    /* Tùy chỉnh thanh cuộn DataTables */
    body::-webkit-scrollbar, html::-webkit-scrollbar { display: block !important; width: 8px; height: 8px; }
    body::-webkit-scrollbar-thumb, html::-webkit-scrollbar-thumb { background: rgba(34, 67, 151, 0.3); border-radius: 4px; }
    body::-webkit-scrollbar-track, html::-webkit-scrollbar-track { background: transparent; }

    /* CSS cho datatables */
    .dataTables_wrapper .dataTables_paginate .paginate_button.current, .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #224397 !important;
        color: white !important;
        border: 1px solid #224397 !important;
    }
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        padding: 4px 8px;
        margin-left: 8px;
    }

    /* Filter select styling */
    .filter-select {
        border: 1px solid rgba(34, 67, 151, 0.25);
        border-radius: 4px;
        padding: 4px 8px;
        font-size: 12px;
        font-weight: 600;
        color: #224397;
        background: white;
        outline: none;
        cursor: pointer;
        min-width: 100px;
    }
    .filter-select:focus {
        border-color: #224397;
        box-shadow: 0 0 0 2px rgba(34, 67, 151, 0.15);
    }
    .filter-label {
        font-size: 11.5px;
        font-weight: 700;
        color: #224397;
        white-space: nowrap;
    }
</style>

<div class="w-full px-2 lg:px-6 mt-4">
    <div class="flex flex-row items-end justify-between gap-2 mb-4">
        
        <div class="flex items-center gap-4">
            <div class="flex items-center h-[28px]">
                <span class="mr-2 text-[13.5px] font-bold text-[#224397]">Cho phép HS đăng ký (Zalo):</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="toggleEmailRequest" class="sr-only peer" <?= $is_allowed ? 'checked' : '' ?>>
                    <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#224397]"></div>
                </label>
            </div>
        </div>

        <div class="flex items-center gap-1.5">
            <button class="btn-action-bulk px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm whitespace-nowrap" data-action="cap_mail_hang_loat">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-envelope-plus-fill" viewBox="0 0 16 16"><path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414zM0 4.697v7.104l5.803-3.558zM6.761 8.83l-6.57 4.026A2 2 0 0 0 2 14h6.256A4.5 4.5 0 0 1 8 12.5a4.49 4.49 0 0 1 1.606-3.446l-.367-.225L8 9.586zM16 4.697v4.974A4.5 4.5 0 0 0 12.5 8a4.5 4.5 0 0 0-1.965.45l-.338-.207z"/><path d="M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0m-3.5-2a.5.5 0 0 0-.5.5v1h-1a.5.5 0 0 0 0 1h1v1a.5.5 0 0 0 1 0v-1h1a.5.5 0 0 0 0-1h-1v-1a.5.5 0 0 0-.5-.5"/></svg> Cấp Mail Hàng Loạt
            </button>
            <button id="btnExport" class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-green-600 hover:text-white hover:border-green-600 transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm whitespace-nowrap">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-excel-fill" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M5.884 6.68 8 9.219l2.116-2.54a.5.5 0 1 1 .768.641L8.651 10l2.233 2.68a.5.5 0 0 1-.768.64L8 10.781l-2.116 2.54a.5.5 0 0 1-.768-.641L7.349 10 5.116 7.32a.5.5 0 1 1 .768-.64"/></svg> Xuất Excel
            </button>
        </div>
    </div>

    <div class="bg-white rounded shadow border border-[#224397]/25 mb-6 p-0 overflow-hidden">
        <div class="px-4 py-3 border-b border-[#224397]/20 bg-[#224397]/5 flex items-center justify-between">
            <h3 class="mb-0 text-[15px] font-bold text-[#224397] uppercase flex items-center"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-envelope-at-fill mr-2" viewBox="0 0 16 16"><path d="M2 2A2 2 0 0 0 .05 3.555L8 8.414l7.95-4.859A2 2 0 0 0 14 2zm-2 9.8V4.698l5.803 3.546zm6.761-2.97-6.57 4.026A2 2 0 0 0 2 14h6.256A4.5 4.5 0 0 1 8 12.5a4.49 4.49 0 0 1 1.606-3.446l-.367-.225L8 9.586zM16 9.671V4.697l-5.803 3.546.338.208A4.5 4.5 0 0 1 12.5 8c1.414 0 2.675.652 3.5 1.671"/><path d="M15.834 12.244c0 1.168-.577 2.025-1.587 2.025-.503 0-1.002-.228-1.12-.648h-.043c-.118.416-.543.643-1.015.643-.77 0-1.259-.544-1.259-1.434v-5.29h1.161v4.391c0 .486.273.743.702.743.339 0 .54-.224.54-.743v-4.391h1.161v5.29c0 .762.43 1.157 1.02 1.157.636 0 1.055-.453 1.055-1.295V10.15c0-1.203-.78-1.89-2.016-1.89-1.232 0-2.023.702-2.023 1.905v.852h1.162v-.824c0-.625.405-1.015.856-1.015.526 0 .852.387.852 1.004zM13 12.18c0-.625-.401-1.004-.84-1.004-.44 0-.828.375-.828 1.011v.172c0 .636.388 1.004.828 1.004.444 0 .84-.391.84-1.007z"/></svg> DANH SÁCH EMAIL HỌC SINH</h3>
            <div class="text-[12px] text-slate-500 font-medium">
                Định dạng mail: <span class="text-[#224397] font-bold">[niên_khóa].[cccd].[tên]@c3binhson.edu.vn</span>
            </div>
        </div>

        <!-- Bộ lọc -->
        <div class="px-4 py-3 border-b border-[#224397]/10 bg-slate-50/50 flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-1.5">
                <span class="filter-label">Niên khóa:</span>
                <select id="filterNienKhoa" class="filter-select">
                    <option value="">Tất cả</option>
                    <?php foreach ($danh_sach_nien_khoa as $nk): ?>
                        <option value="<?= htmlspecialchars($nk) ?>"><?= htmlspecialchars($nk) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="filter-label">Khối:</span>
                <select id="filterKhoi" class="filter-select">
                    <option value="">Tất cả</option>
                    <?php foreach ($danh_sach_khoi as $khoi): ?>
                        <option value="<?= htmlspecialchars($khoi) ?>">Khối <?= htmlspecialchars($khoi) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="filter-label">Lớp:</span>
                <select id="filterLop" class="filter-select">
                    <option value="">Tất cả</option>
                    <?php foreach ($danh_sach_lop as $lop): ?>
                        <option value="<?= htmlspecialchars($lop['ten_lop']) ?>"><?= htmlspecialchars($lop['ten_lop']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="filter-label">Trạng thái:</span>
                <select id="filterTrangThai" class="filter-select">
                    <option value="">Tất cả</option>
                    <option value="chua_dk">Chưa ĐK</option>
                    <option value="cho_duyet">Chờ duyệt</option>
                    <option value="da_cap">Đã cấp</option>
                    <option value="da_khoa">Đã khóa</option>
                </select>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="filter-label">Tìm kiếm:</span>
                <input type="text" id="filterSearch" class="filter-select" style="min-width:180px" placeholder="Họ tên, CCCD, email...">
            </div>
            <div class="flex items-center ml-auto">
                <span id="recordCount" class="text-[11.5px] font-bold text-slate-500"></span>
            </div>
        </div>
        
        <div class="w-full">
            <div class="overflow-x-auto w-full p-4">
                <table id="emailTable" class="w-full text-sm text-left text-gray-500 stripe hover" style="width:100%">
                    <thead>
                        <tr>
                            <th class="w-12">STT</th>
                            <th>Họ và tên</th>
                            <th>CCCD</th>
                            <th>Niên khóa</th>
                            <th>Lớp HT</th>
                            <th>Email Cá Nhân</th>
                            <th>Trạng Thái</th>
                            <th>Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Load by AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal chi tiết -->
<div id="actionModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white rounded shadow-lg w-full max-w-md p-0 overflow-hidden transform transition-all border border-[#224397]/25">
        <div class="px-4 py-3 bg-[#224397] text-white flex justify-between items-center">
            <h3 class="mb-0 text-[15px] font-bold" id="modalTitle">Xác nhận</h3>
        </div>
        <div class="p-6">
            <p class="text-slate-700 text-sm mb-6" id="modalMessage">Bạn có chắc chắn muốn thực hiện hành động này?</p>
            <div class="flex justify-end gap-2">
                <button id="btnCancel" class="px-3 py-1.5 text-slate-700 bg-slate-100 border border-slate-300 hover:bg-slate-200 rounded transition font-medium text-[13px]">Hủy</button>
                <button id="btnConfirm" class="px-3 py-1.5 text-white bg-[#224397] hover:bg-[#1a3478] rounded transition shadow-sm font-medium text-[13px]">Xác nhận</button>
            </div>
        </div>
    </div>
</div>

<script src="/thidua/public/assets/libs/jquery-3.7.0.min.js"></script>
<script src="/thidua/public/assets/libs/jquery.dataTables.min.js"></script>
<script src="/thidua/public/assets/libs/dataTables.tailwindcss.min.js"></script>
<script>
$(document).ready(function() {
    // Lưu trữ toàn bộ dữ liệu gốc và danh sách lớp từ PHP
    let allData = [];
    const allLop = <?= json_encode(array_column($danh_sach_lop, 'ten_lop')) ?>;

    const table = $('#emailTable').DataTable({
        ajax: {
            url: '/thidua/api/admin/email-hoc-sinh',
            type: 'POST',
            data: { action: 'load_list' },
            dataSrc: function(json) {
                allData = json.data || [];
                updateRecordCount(allData.length);
                return allData;
            }
        },
        columns: [
            { data: 'stt', defaultContent: '', className: 'text-center' },
            { 
                data: 'ho_ten',
                defaultContent: '',
                render: function(data, type, row) {
                    return `
                        <div class="font-bold text-[#224397]">${data || ''}</div>
                        <div class="text-xs text-slate-500">${row.ngay_sinh || ''}</div>
                    `;
                }
            },
            { data: 'so_cccd', defaultContent: '', className: 'text-center' },
            { data: 'nien_khoa', defaultContent: '', className: 'text-center' },
            { data: 'lop', defaultContent: '', className: 'text-center' },
            { data: 'email_ca_nhan', defaultContent: '' },
            { 
                data: 'trang_thai',
                defaultContent: '',
                className: 'text-center',
                render: function(data) {
                    if (data === 'cho_duyet') return '<span class="px-2 py-1 rounded bg-amber-100 text-amber-700 text-[11px] font-bold border border-amber-200">Chờ duyệt</span>';
                    if (data === 'da_cap') return '<span class="px-2 py-1 rounded bg-green-100 text-green-700 text-[11px] font-bold border border-green-200">Đã cấp</span>';
                    if (data === 'da_khoa') return '<span class="px-2 py-1 rounded bg-rose-100 text-rose-700 text-[11px] font-bold border border-rose-200">Đã khóa</span>';
                    return '<span class="px-2 py-1 rounded bg-slate-100 text-slate-500 text-[11px] font-bold border border-slate-200">Chưa ĐK</span>';
                }
            },
            { 
                data: null,
                defaultContent: '',
                className: 'text-center',
                render: function(data, type, row) {
                    let btns = `<div class="flex gap-1.5 justify-center">`;
                    if (row.trang_thai === 'cho_duyet' || row.trang_thai === null) {
                        btns += `<button class="btn-action w-7 h-7 flex items-center justify-center rounded bg-blue-50 text-[#224397] border border-blue-200 hover:bg-[#224397] hover:text-white transition-colors" data-id="${row.hs_id}" data-action="cap_mail" title="Cấp mail"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-envelope-plus-fill" viewBox="0 0 16 16"><path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414zM0 4.697v7.104l5.803-3.558zM6.761 8.83l-6.57 4.026A2 2 0 0 0 2 14h6.256A4.5 4.5 0 0 1 8 12.5a4.49 4.49 0 0 1 1.606-3.446l-.367-.225L8 9.586zM16 4.697v4.974A4.5 4.5 0 0 0 12.5 8a4.5 4.5 0 0 0-1.965.45l-.338-.207z"/><path d="M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0m-3.5-2a.5.5 0 0 0-.5.5v1h-1a.5.5 0 0 0 0 1h1v1a.5.5 0 0 0 1 0v-1h1a.5.5 0 0 0 0-1h-1v-1a.5.5 0 0 0-.5-.5"/></svg></button>`;
                    }
                    if (row.trang_thai === 'da_cap') {
                        btns += `<button class="btn-action w-7 h-7 flex items-center justify-center rounded bg-amber-50 text-amber-600 border border-amber-200 hover:bg-amber-600 hover:text-white transition-colors" data-id="${row.hs_id}" data-action="reset_pass" title="Khôi phục mật khẩu"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-key-fill" viewBox="0 0 16 16"><path d="M3.5 11.5a3.5 3.5 0 1 1 3.163-5H14L15.5 8 14 9.5l-1-1-1 1-1-1-1 1-1-1-1 1H6.663a3.5 3.5 0 0 1-3.163 2M2.5 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/></svg></button>`;
                        btns += `<button class="btn-action w-7 h-7 flex items-center justify-center rounded bg-rose-50 text-rose-600 border border-rose-200 hover:bg-rose-600 hover:text-white transition-colors" data-id="${row.hs_id}" data-action="khoa_mail" title="Khóa mail"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-lock-fill" viewBox="0 0 16 16"><path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2m3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2"/></svg></button>`;
                    }
                    if (row.trang_thai === 'da_khoa') {
                        btns += `<button class="btn-action w-7 h-7 flex items-center justify-center rounded bg-emerald-50 text-emerald-600 border border-emerald-200 hover:bg-emerald-600 hover:text-white transition-colors" data-id="${row.hs_id}" data-action="mo_khoa_mail" title="Mở khóa mail"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-unlock-fill" viewBox="0 0 16 16"><path d="M11 1a2 2 0 0 0-2 2v4a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h5V3a3 3 0 0 1 6 0v4a.5.5 0 0 1-1 0V3a2 2 0 0 0-2-2z"/></svg></button>`;
                    }
                    if (row.trang_thai !== null) {
                        btns += `<button class="btn-action w-7 h-7 flex items-center justify-center rounded bg-red-50 text-red-600 border border-red-200 hover:bg-red-600 hover:text-white transition-colors" data-id="${row.hs_id}" data-action="xoa_mail" title="Xóa dữ liệu"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16"><path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5M8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5m3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0"/></svg></button>`;
                    }
                    btns += `</div>`;
                    return btns;
                },
                orderable: false
            }
        ],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json' },
        paging: false,
        info: false,
        lengthChange: false,
        searching: false,
        ordering: false
    });

    function updateRecordCount(count) {
        $('#recordCount').text('Hiển thị: ' + count + ' / ' + allData.length + ' học sinh');
    }

    // ======== BỘ LỌC CLIENT-SIDE ========
    function applyFilters() {
        const nienKhoa = $('#filterNienKhoa').val();
        const khoi = $('#filterKhoi').val();
        const lop = $('#filterLop').val();
        const trangThai = $('#filterTrangThai').val();
        const search = $('#filterSearch').val().toLowerCase().trim();

        const filtered = allData.filter(function(row) {
            // Lọc niên khóa
            if (nienKhoa && row.nien_khoa !== nienKhoa) return false;
            // Lọc khối
            if (khoi && row.khoi !== khoi) return false;
            // Lọc lớp
            if (lop && row.lop !== lop) return false;
            // Lọc trạng thái
            if (trangThai) {
                if (trangThai === 'chua_dk' && row.trang_thai !== null) return false;
                if (trangThai !== 'chua_dk' && row.trang_thai !== trangThai) return false;
            }
            // Tìm kiếm
            if (search) {
                const hoTen = (row.ho_ten || '').toLowerCase();
                const cccd = (row.so_cccd || '').toLowerCase();
                const email = (row.email_ca_nhan || '').toLowerCase();
                if (!hoTen.includes(search) && !cccd.includes(search) && !email.includes(search)) return false;
            }
            return true;
        });

        // Cập nhật STT theo bộ lọc
        filtered.forEach(function(row, idx) {
            row.stt = idx + 1;
        });

        table.clear().rows.add(filtered).draw();
        updateRecordCount(filtered.length);
    }

    // Khi thay đổi bộ lọc khối → cập nhật danh sách lớp tương ứng
    $('#filterKhoi').on('change', function() {
        const selectedKhoi = $(this).val();
        const $lopSelect = $('#filterLop');
        $lopSelect.html('<option value="">Tất cả</option>');
        
        const filteredLop = selectedKhoi 
            ? allLop.filter(l => l.substring(0, 2) === selectedKhoi)
            : allLop;
        
        filteredLop.forEach(function(lop) {
            $lopSelect.append(`<option value="${lop}">${lop}</option>`);
        });
        
        applyFilters();
    });

    $('#filterNienKhoa, #filterLop, #filterTrangThai').on('change', applyFilters);
    
    let searchTimeout;
    $('#filterSearch').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(applyFilters, 300);
    });

    // Bật tắt yêu cầu
    $('#toggleEmailRequest').on('change', function() {
        const checkbox = this;
        const status = checkbox.checked ? 1 : 0;
        $.ajax({
            url: '/thidua/api/admin/email-hoc-sinh',
            type: 'POST',
            data: { action: 'toggle_setting', status: status },
            dataType: 'json',
            success: function(res) {
                if (res && res.success) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2500,
                        icon: 'success',
                        title: status ? 'Đã bật cho phép HS đăng ký' : 'Đã tắt cho phép HS đăng ký'
                    });
                } else {
                    checkbox.checked = !checkbox.checked;
                    Swal.fire({toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, icon: 'error', title: (res && res.message) ? res.message : 'Lỗi cập nhật'});
                }
            },
            error: function() {
                checkbox.checked = !checkbox.checked;
                Swal.fire({toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, icon: 'error', title: 'Lỗi kết nối máy chủ'});
            }
        });
    });

    // Hành động hàng loạt
    $('.btn-action-bulk').click(function() {
        const action = $(this).data('action');
        let msg = 'Hệ thống sẽ thực hiện cấp mail cho TẤT CẢ học sinh đang Chờ duyệt. Tiếp tục?';
        showModal('Cấp mail hàng loạt', msg, function() {
            Swal.fire({ title: 'Đang xử lý...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() }});
            $.post('/thidua/api/admin/email-hoc-sinh', { action: action }, function(res) {
                if(res.success) Swal.fire('Thông báo', res.message, 'info');
                else Swal.fire('Lỗi', res.message, 'error');
            });
        });
    });

    // Hành động đơn
    $('#emailTable').on('click', '.btn-action', function() {
        const id = $(this).data('id');
        const action = $(this).data('action');
        let msg = '';
        if (action === 'cap_mail') msg = 'Hệ thống sẽ kết nối với Microsoft 365 để tạo tài khoản. Tiếp tục?';
        else if (action === 'reset_pass') msg = 'Bạn muốn khôi phục mật khẩu ngẫu nhiên cho tài khoản này?';
        else if (action === 'khoa_mail') msg = 'Bạn muốn khóa (block sign-in) tài khoản Microsoft này?';
        else if (action === 'mo_khoa_mail') msg = 'Bạn muốn mở khóa tài khoản Microsoft này?';
        else if (action === 'xoa_mail') msg = 'Bạn muốn xóa dữ liệu liên kết? Tài khoản trên Microsoft 365 cũng sẽ bị xóa vĩnh viễn!';
        
        showModal('Xác nhận thao tác', msg, function() {
            Swal.fire({ title: 'Đang xử lý...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() }});
            $.post('/thidua/api/admin/email-hoc-sinh', { action: action, id: id }, function(res) {
                if(res.success) {
                    Swal.fire('Thành công', res.message || 'Thao tác thành công', 'success');
                    table.ajax.reload(null, false);
                } else {
                    Swal.fire('Lỗi', res.message || 'Có lỗi xảy ra', 'error');
                }
            }).fail(function() {
                Swal.fire('Lỗi', 'Không thể kết nối máy chủ', 'error');
            });
        });
    });

    // Modal
    let modalCallback = null;
    function showModal(title, message, callback) {
        $('#modalTitle').text(title);
        $('#modalMessage').text(message);
        $('#actionModal').removeClass('hidden');
        modalCallback = callback;
    }
    $('#btnCancel').click(() => $('#actionModal').addClass('hidden'));
    $('#btnConfirm').click(() => {
        $('#actionModal').addClass('hidden');
        if(modalCallback) modalCallback();
    });

    // Xuất excel
    $('#btnExport').click(function() {
        window.location.href = '/thidua/api/admin/email-hoc-sinh?action=export';
    });
});
</script>

<?php require 'partials/admin_footer.php'; ?>
