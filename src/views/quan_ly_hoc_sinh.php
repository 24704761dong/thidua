<?php
$page_title = 'Quản Lý Học Sinh & CTV';
require_once __DIR__ . '/partials/admin_header.php';

$filter_has_permission = isset($_GET['has_permission']) && $_GET['has_permission'] === '1';
?>


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
    #studentTable {
        border: 1px solid rgba(34, 67, 151, 0.25);
        border-collapse: collapse;
        width: 100%;
    }
    #studentTable thead th {
        background-color: rgba(34, 67, 151, 0.08);
        color: #224397;
        font-weight: 800; /* Tiêu đề bảng in đậm hơn */
        text-transform: uppercase;
        font-size: 0.88rem; /* To hơn dữ liệu bảng 1 xíu */
        text-align: center;
        padding: 0.75rem 1rem;
        border: 1px solid rgba(34, 67, 151, 0.25);
    }
    #studentTable td {
        padding: 0.75rem 1rem;
        border: 1px solid rgba(34, 67, 151, 0.25); /* Các đường kẻ đều nhau */
        vertical-align: middle;
        font-size: 0.85rem;
        font-weight: 600; /* Chữ đậm lên dễ nhìn */
        color: #1e293b;
    }
    #studentTable tbody tr:hover {
        background-color: rgba(34, 67, 151, 0.05) !important;
    }

    /* ----- Chức năng ẩn/hiện cột xóa ----- */
    .delete-col { display: none; }
    #studentTable.deletion-mode .delete-col { display: table-cell; }
    
    .btn-permission { min-width: 36px; }

    /* ----- Ép hiện thanh cuộn cho trang danh sách dài ----- */
    body::-webkit-scrollbar, html::-webkit-scrollbar { display: block !important; width: 8px; height: 8px; }
    body::-webkit-scrollbar-thumb, html::-webkit-scrollbar-thumb { background: rgba(34, 67, 151, 0.3); border-radius: 4px; }
    body::-webkit-scrollbar-track, html::-webkit-scrollbar-track { background: transparent; }

    /* Kẻ ngang full hàng cho HS nghỉ học */
    .row-strike-through td {
        position: relative;
    }
    .row-strike-through td::after {
        content: "";
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        border-top: 1.5px solid #94a3b8; /* Slate-400 */
        z-index: 10;
        pointer-events: none;
    }
</style>

<div class="w-full px-2 lg:px-6">
    <!-- Nút quay về màn hình chính đã được loại bỏ -->
    
    <!-- Filter và Buttons trên 1 hàng -->
    <div class="flex flex-row items-end justify-between gap-2 mb-4">
        
        <!-- Filter Form (Bên trái) -->
        <form id="filterForm" action="/thidua/admin/hoc-sinh" method="GET" class="flex flex-row items-end gap-2 m-0">
            <input type="hidden" name="page" id="page" value="1">
            <?php if (isset($_GET['iframe'])): ?>
                <input type="hidden" name="iframe" value="1">
            <?php endif; ?>
            
            <div>
                <!-- Cụm tìm kiếm, lọc có tiêu đề bự hơn xíu -->
                <label for="keyword" class="block text-[13.5px] font-bold text-[#224397] mb-0.5">Tên / Mã HS</label>
                <input type="text" id="keyword" name="keyword" class="block w-36 rounded border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-2 py-1 text-[13px]" placeholder="Nhập tên..." value="<?php echo htmlspecialchars($filter_keyword ?? ''); ?>">
            </div>
            <div>
                <label for="khoi" class="block text-[13.5px] font-bold text-[#224397] mb-0.5">Khối</label>
                <select id="khoi" name="khoi" class="block w-20 rounded border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-2 py-1 text-[13px]">
                    <option value="all" <?php if ($filter_khoi === 'all') echo 'selected'; ?>>Tất cả</option>
                    <option value="10" <?php if ($filter_khoi === '10') echo 'selected'; ?>>Khối 10</option>
                    <option value="11" <?php if ($filter_khoi === '11') echo 'selected'; ?>>Khối 11</option>
                    <option value="12" <?php if ($filter_khoi === '12') echo 'selected'; ?>>Khối 12</option>
                </select>
            </div>
            <div>
                <label for="lop_id" class="block text-[13.5px] font-bold text-[#224397] mb-0.5">Lớp</label>
                <select id="lop_id" name="lop_id" class="block w-24 rounded border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-2 py-1 text-[13px]">
                    <option value="all">Tất cả</option>
                    <?php foreach ($danh_sach_lop as $lop) : ?>
                        <option value="<?php echo $lop['id']; ?>" data-khoi="<?php echo substr($lop['ten_lop'], 0, 2); ?>" <?php if ($filter_lop_id == $lop['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($lop['ten_lop']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="chuc_vu" class="block text-[13.5px] font-bold text-[#224397] mb-0.5">Chức vụ</label>
                <select id="chuc_vu" name="chuc_vu" class="block w-24 rounded border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-2 py-1 text-[13px]">
                    <option value="all" <?php if ($filter_chuc_vu === 'all') echo 'selected'; ?>>Tất cả</option>
                    <?php foreach ($danh_sach_chuc_vu as $chuc_vu_item) : ?>
                        <option value="<?php echo htmlspecialchars($chuc_vu_item); ?>" <?php if ($filter_chuc_vu === $chuc_vu_item) echo 'selected'; ?>><?php echo htmlspecialchars($chuc_vu_item); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex items-center h-[28px] ml-1">
                <input class="rounded border-slate-300 text-blue-500 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200" type="checkbox" name="has_permission" value="1" id="has_permission_filter" <?php if ($filter_has_permission) echo 'checked'; ?>>
                <label class="ml-1.5 block text-[13.5px] font-bold text-[#224397]" for="has_permission_filter">Chỉ CTV</label>
            </div>
            <div class="ml-1">
                <label for="page_size" class="block text-[13.5px] font-bold text-[#224397] mb-0.5">Hiển thị</label>
                <select id="page_size" name="page_size" class="block w-20 rounded border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-2 py-1 text-[13px]">
                    <?php $ps = $pagination['page_size'] ?? 100; foreach([50,100,200,300,500] as $opt){ $sel = ($ps==$opt)?'selected':''; echo "<option value=\"$opt\" $sel>$opt</option>"; } ?>
                </select>
            </div>
        </form>

        <!-- Action Buttons (Bên phải, nhỏ lại) -->
        <div class="flex items-center gap-1.5" id="default-toolbar">
            
            <a href="/thidua/admin/ctv?action=manage_codes" class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm whitespace-nowrap"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-key-fill" viewBox="0 0 16 16"><path d="M3.5 11.5a3.5 3.5 0 1 1 3.163-5H14L15.5 8 14 9.5l-1-1-1 1-1-1-1 1-1-1-1 1H6.663a3.5 3.5 0 0 1-3.163 2M2.5 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/></svg> MÃ CẤP QUYỀN</a>
            <a href="/thidua/admin/ctv" class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm whitespace-nowrap"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-people-fill" viewBox="0 0 16 16"><path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/></svg> QL CTV</a>
            <!-- Tác Vụ Dropdown -->
            <div class="relative inline-block text-left group z-50">
                <button type="button" class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm whitespace-nowrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-lightning-charge-fill" viewBox="0 0 16 16"><path d="M11.251.068a.5.5 0 0 1 .227.58L9.677 6.5H13a.5.5 0 0 1 .364.843l-8 8.5a.5.5 0 0 1-.842-.49L6.323 9.5H3a.5.5 0 0 1-.364-.843l8-8.5a.5.5 0 0 1 .615-.09z"/></svg> Tác vụ <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-chevron-down text-[9px]" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708"/></svg>
                </button>
                <ul class="absolute right-0 mt-1 w-40 bg-white rounded shadow-lg border border-slate-100 focus:outline-none opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-[100] transform origin-top-right scale-95 group-hover:scale-100 py-1">
                    <li><a class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] text-slate-700 hover:bg-blue-50 hover:text-[#224397]" href="#" id="quick-grant-btn"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-shield-plus" viewBox="0 0 16 16"><path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56"/>   <path d="M8 4.5a.5.5 0 0 1 .5.5v1.5H10a.5.5 0 0 1 0 1H8.5V9a.5.5 0 0 1-1 0V7.5H6a.5.5 0 0 1 0-1h1.5V5a.5.5 0 0 1 .5-.5"/></svg>Cấp quyền</a></li>
                    <li><a class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] text-slate-700 hover:bg-blue-50 hover:text-[#224397]" href="#" id="quick-revoke-btn"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-shield-slash-fill" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.093 3.093c-.465 4.275.885 7.46 2.513 9.589a11.8 11.8 0 0 0 2.517 2.453c.386.273.744.482 1.048.625.28.132.581.24.829.24s.548-.108.829-.24a7 7 0 0 0 1.048-.625 11.3 11.3 0 0 0 1.733-1.525zm12.215 8.215L3.128 1.128A61 61 0 0 1 5.073.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.483 3.626-.332 6.491-1.551 8.616m.338 3.046-13-13 .708-.708 13 13z"/></svg>Thu hồi quyền</a></li>
                    <li><hr class="border-t border-slate-100 my-1"></li>
                    <li><a class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] text-slate-700 hover:bg-blue-50 hover:text-[#224397]" href="#" id="addStudentBtn"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-plus-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3z"/></svg>Thêm HS</a></li>
                    <li><a class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] text-blue-600 hover:bg-blue-50" href="/thidua/admin/nhan-hoc-sinh"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-box-arrow-in-right" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M6 3.5a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 0-1 0v2A1.5 1.5 0 0 0 6.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-8A1.5 1.5 0 0 0 5 3.5v2a.5.5 0 0 0 1 0z"/>   <path fill-rule="evenodd" d="M11.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H1.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z"/></svg>Nhận HS Lên Lớp</a></li>
                    <li><a class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] text-slate-700 hover:bg-blue-50 hover:text-[#224397]" href="#" id="editModeBtn"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16"><path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>   <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/></svg>Sửa thông tin</a></li>
                    <li><a class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] text-green-600 hover:bg-green-50" href="#" id="graduate-mode-btn"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-mortarboard-fill" viewBox="0 0 16 16"><path d="M8.211 2.047a.5.5 0 0 0-.422 0l-7.5 3.5a.5.5 0 0 0 .025.917l7.5 3a.5.5 0 0 0 .372 0l7.5-3a.5.5 0 0 0 .025-.917l-7.5-3.5Z"/><path d="M4.176 9.032a.5.5 0 0 0-.656.327l-.5 1.7a.5.5 0 0 0 .294.605l4.5 1.8a.5.5 0 0 0 .372 0l4.5-1.8a.5.5 0 0 0 .294-.605l-.5-1.7a.5.5 0 0 0-.656-.327L8 10.466 4.176 9.032Z"/></svg>Nhận HS tốt nghiệp</a></li>
                    <li><a class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] text-red-600 hover:bg-red-50" href="#" id="delete-mode-btn"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-person-x-fill" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m6.146-2.854a.5.5 0 0 1 .708 0L14 6.293l1.146-1.147a.5.5 0 0 1 .708.708L14.707 7l1.147 1.146a.5.5 0 0 1-.708.708L14 7.707l-1.146 1.147a.5.5 0 0 1-.708-.708L13.293 7l-1.147-1.146a.5.5 0 0 1 0-.708"/></svg>Cho nghỉ học</a></li>
                    <li><a class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] text-slate-700 hover:bg-blue-50 hover:text-[#224397]" href="/thidua/admin/hoc-sinh-luu-tru"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-archive-fill" viewBox="0 0 16 16"><path d="M12.643 15C13.979 15 15 13.845 15 12.5V5H1v7.5C1 13.845 2.021 15 3.357 15zM5.5 7h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1 0-1M.8 1a.8.8 0 0 0-.8.8V3a.8.8 0 0 0 .8.8h14.4A.8.8 0 0 0 16 3V1.8a.8.8 0 0 0-.8-.8z"/></svg>HS đã nghỉ</a></li>
                    <li><a class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] text-slate-700 hover:bg-blue-50 hover:text-[#224397]" href="/thidua/admin/hoc-sinh-tot-nghiep"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-award-fill" viewBox="0 0 16 16"><path d="m8 0 1.669.864 1.858.282.842 1.68 1.337 1.32L13.4 6l.305 1.854-1.337 1.32-.842 1.68-1.858.282L8 12l-1.669-.864-1.858-.282-.842-1.68-1.337-1.32L2.6 6l-.305-1.854 1.337-1.32.842-1.68 1.858-.282L8 0z"/><path d="M4 11.794V16l4-1 4 1v-4.206l-2.018.306L8 13.126 6.018 12.1 4 11.794z"/></svg>HS đã tốt nghiệp</a></li>
                </ul>
            </div>

            <!-- Nhập Xuất Dropdown -->
            <div class="relative inline-block text-left group z-50">
                <button type="button" class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm whitespace-nowrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-arrow-down-up" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M11.5 15a.5.5 0 0 0 .5-.5V2.707l3.146 3.147a.5.5 0 0 0 .708-.708l-4-4a.5.5 0 0 0-.708 0l-4 4a.5.5 0 1 0 .708.708L11 2.707V14.5a.5.5 0 0 0 .5.5m-7-14a.5.5 0 0 1 .5.5v11.793l3.146-3.147a.5.5 0 0 1 .708.708l-4 4a.5.5 0 0 1-.708 0l-4-4a.5.5 0 0 1 .708-.708L4 13.293V1.5a.5.5 0 0 1 .5-.5"/></svg> Nhập/Xuất <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-chevron-down text-[9px]" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708"/></svg>
                </button>
                <ul class="absolute right-0 mt-1 w-36 bg-white rounded shadow-lg border border-slate-100 focus:outline-none opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-[100] transform origin-top-right scale-95 group-hover:scale-100 py-1">
                    <li><a class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] text-slate-700 hover:bg-blue-50 hover:text-[#224397] cursor-pointer" onclick="openModal('importModal')"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-upload" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>   <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708z"/></svg>Import</a></li>
                    <li><a class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] text-slate-700 hover:bg-blue-50 hover:text-[#224397]" href="/thidua/tai-file-mau-hoc-sinh"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>   <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/></svg>File mẫu</a></li>
                    <li><hr class="border-t border-slate-100 my-1"></li>
                    <li><a class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] text-slate-700 hover:bg-blue-50 hover:text-[#224397]" href="#" id="exportExcelBtn"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-excel-fill" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M5.884 6.68 8 9.219l2.116-2.54a.5.5 0 1 1 .768.641L8.651 10l2.233 2.68a.5.5 0 0 1-.768.64L8 10.781l-2.116 2.54a.5.5 0 0 1-.768-.641L7.349 10 5.116 7.32a.5.5 0 1 1 .768-.64"/></svg>Xuất DS</a></li>
                    <li><a class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] text-slate-700 hover:bg-blue-50 hover:text-[#224397]" href="#" id="export-ctv-btn"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-person-vcard-fill" viewBox="0 0 16 16"><path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm9 1.5a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 0-1h-4a.5.5 0 0 0-.5.5M9 8a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 0-1h-4A.5.5 0 0 0 9 8m1 2.5a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 0-1h-3a.5.5 0 0 0-.5.5m-1 2C9 10.567 7.21 9 5 9c-2.086 0-3.8 1.398-3.984 3.181A1 1 0 0 0 2 13h6.96q.04-.245.04-.5M7 6a2 2 0 1 0-4 0 2 2 0 0 0 4 0"/></svg>Xuất DS CTV</a></li>
                    <li><hr class="border-t border-slate-100 my-1"></li>
                    <li><a class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] text-slate-700 hover:bg-blue-50 hover:text-[#224397]" href="#" id="exportZipBtn"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-zip-fill" viewBox="0 0 16 16"><path d="M8.5 9.438V8.5h-1v.938a1 1 0 0 1-.03.243l-.4 1.598.93.62.93-.62-.4-1.598a1 1 0 0 1-.03-.243zM7.5 3h-1v1h1zm1 1h-1v1h1zm-1 1h-1v1h1zm1 1h-1v1h1z"/><path d="M4 0h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2m2.5 8.5v.938l-.4 1.599a1 1 0 0 0 .416 1.074l.93.62a1 1 0 0 0 1.108 0l.93-.62a1 1 0 0 0 .415-1.074l-.4-1.599V8.5a1 1 0 0 0-1-1h-1a1 1 0 0 0-1 1"/></svg>Xuất Hồ Sơ ZIP</a></li>
                </ul>
            </div>
        </div>

        <div id="delete-toolbar" class="flex items-center gap-1.5 hidden">
            <button class="px-2 py-1 bg-red-500 border border-transparent rounded text-white hover:bg-red-600 transition-colors font-medium flex items-center gap-1 text-[11px] shadow-sm" id="confirm-delete-btn"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg> Xác nhận</button>
            <button class="px-2 py-1 bg-slate-500 border border-transparent rounded text-white hover:bg-slate-600 transition-colors font-medium flex items-center gap-1 text-[11px] shadow-sm" id="cancel-delete-btn"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>   <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/></svg> Hủy</button>
        </div>
        <div id="graduate-toolbar" class="flex items-center gap-1.5 hidden">
            <button class="px-2 py-1 bg-green-600 border border-transparent rounded text-white hover:bg-green-700 transition-colors font-medium flex items-center gap-1 text-[11px] shadow-sm" id="confirm-graduate-btn"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg> Xác nhận tốt nghiệp</button>
            <button class="px-2 py-1 bg-slate-500 border border-transparent rounded text-white hover:bg-slate-600 transition-colors font-medium flex items-center gap-1 text-[11px] shadow-sm" id="cancel-graduate-btn"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>   <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/></svg> Hủy</button>
        </div>
    </div>

    <div class="bg-white rounded shadow border border-[#224397]/25 mb-6 p-0 overflow-hidden">
        <div class="px-4 py-3 border-b border-[#224397]/20 bg-[#224397]/5 flex items-center">
            <h3 class="mb-0 text-[15px] font-bold text-[#224397] uppercase flex items-center"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-person-badge-fill mr-2" viewBox="0 0 16 16"><path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2zm4.5 0a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1zM8 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6m5 2.755C12.146 12.825 10.623 12 8 12s-4.146.826-5 1.755V14a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1z"/></svg>DANH SÁCH HỌC SINH</h3>
        </div>
        <div class="px-4 pb-4 pt-3">

        <div id="editModeAlert" class="p-3 mb-4 rounded border bg-amber-50 text-amber-800 border-amber-200 hidden text-sm" role="alert">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-info-circle-fill shrink-0" viewBox="0 0 16 16"><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2"/></svg>
                <div><strong>Chế độ Sửa:</strong> Nhấp vào biểu tượng bút chì để sửa thông tin học sinh.</div>
            </div>
        </div>
        <div id="deleteModeAlert" class="p-3 mb-4 rounded border bg-red-50 text-red-800 border-red-200 hidden text-sm" role="alert">
            <div class="flex items-start gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-exclamation-triangle-fill shrink-0 mt-0.5" viewBox="0 0 16 16"><path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/></svg>
                <div><strong>Chế độ Chuyển Trạng Thái:</strong> Chọn các học sinh cần chuyển sang trạng thái <strong>Nghỉ học</strong>. Những học sinh này sẽ bị đưa vào danh sách lưu trữ và không thể đăng nhập.</div>
            </div>
            <div class="mt-3 flex gap-3">
                <input type="date" id="bulk-ngay-nghi-hoc" class="rounded border-red-300 text-sm p-1.5 w-40" required value="<?php echo date('Y-m-d'); ?>" title="Ngày nghỉ học">
                <input type="text" id="bulk-ly-do-nghi" class="rounded border-red-300 text-sm p-1.5 flex-1" placeholder="Lý do (VD: Chuyển trường, Nghỉ học luôn...)" required>
            </div>
        </div>
        <div id="graduateModeAlert" class="p-3 mb-4 rounded border bg-green-50 text-green-800 border-green-200 hidden text-sm" role="alert">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-mortarboard-fill shrink-0" viewBox="0 0 16 16"><path d="M8.211 2.047a.5.5 0 0 0-.422 0l-7.5 3.5a.5.5 0 0 0 .025.917l7.5 3a.5.5 0 0 0 .372 0l7.5-3a.5.5 0 0 0 .025-.917l-7.5-3.5Z"/><path d="M4.176 9.032a.5.5 0 0 0-.656.327l-.5 1.7a.5.5 0 0 0 .294.605l4.5 1.8a.5.5 0 0 0 .372 0l4.5-1.8a.5.5 0 0 0 .294-.605l-.5-1.7a.5.5 0 0 0-.656-.327L8 10.466 4.176 9.032Z"/></svg>
                <div><strong>Chế độ Tốt Nghiệp:</strong> Chọn các học sinh cần chuyển sang trạng thái <strong>Đã tốt nghiệp</strong> và nhập Năm tốt nghiệp bên dưới.</div>
            </div>
            <div class="mt-3 flex items-center gap-3">
                <label for="bulk-nam-tot-nghiep" class="font-bold text-sm text-green-800">Năm tốt nghiệp:</label>
                <input type="number" id="bulk-nam-tot-nghiep" class="rounded border-green-300 text-sm p-1.5 w-32" required value="<?php echo date('Y'); ?>" title="Năm tốt nghiệp">
            </div>
        </div>

        <div class="w-full">
            <div class="overflow-x-auto w-full">
                <table id="studentTable">
                    <thead>
                        <tr>
                            <th class="delete-col w-12"><input type="checkbox" id="select-all-delete" class="rounded border-slate-300 text-primary-600 shadow-sm focus:border-primary-300"></th>
                            <th class="w-12 text-center">STT</th>
                            <th class="text-center">Niên khóa</th>
                            <th class="text-center">Số CCCD</th>
                            <th class="text-left">Họ và Tên</th>
                            <th class="text-center">Lớp</th>
                            <th class="text-center">Ngày sinh</th>
                            <th class="text-center">Chức Vụ</th>
                            <th class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($danh_sach_hoc_sinh)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-8 text-slate-500 font-medium">Không tìm thấy học sinh nào phù hợp với điều kiện lọc.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($danh_sach_hoc_sinh as $index => $hs) : ?>
                                <tr data-id="<?php echo $hs['id']; ?>" class="hover:bg-slate-50 transition-colors <?php echo (($hs['trang_thai_hoc_tap'] ?? '') === 'nghi_hoc') ? 'row-strike-through text-slate-400 bg-slate-50' : ''; ?>" <?php echo (($hs['trang_thai_hoc_tap'] ?? '') === 'nghi_hoc') ? 'title="Học sinh đã nghỉ học"' : ''; ?>>
                                    <td class="delete-col text-center"><input type="checkbox" class="rounded border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 delete-checkbox" value="<?php echo $hs['id']; ?>"></td>
                                    <td class="text-center"><?php echo ($pagination['offset'] ?? 0) + $index + 1; ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($hs['nien_khoa'] ?? ''); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($hs['ma_hoc_sinh']); ?></td>
                                    <td class="text-left">
                                        <div class="flex items-center gap-1.5">
                                            <span><?php echo htmlspecialchars($hs['ho_dem'] . ' ' . $hs['ten']); ?></span>
                                            <?php if (($hs['trang_thai_hoc_tap'] ?? '') === 'da_tot_nghiep'): ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill text-green-600 shrink-0" viewBox="0 0 16 16" title="Học sinh đã tốt nghiệp">
                                                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                                </svg>
                                            <?php elseif (!empty($hs['next_ten_lop'])): ?>
                                                <?php 
                                                    $curGrade = (int)preg_replace('/[^0-9]/', '', $hs['ten_lop'] ?? '');
                                                    $nextGrade = (int)preg_replace('/[^0-9]/', '', $hs['next_ten_lop'] ?? '');
                                                ?>
                                                <?php if ($nextGrade > $curGrade): ?>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-up-circle-fill text-emerald-600 shrink-0" viewBox="0 0 16 16" title="Đã lên lớp <?php echo htmlspecialchars($hs['next_ten_lop']); ?> (Năm học <?php echo htmlspecialchars($hs['next_ten_nam_hoc'] ?? ''); ?>)">
                                                        <path d="M16 8A8 8 0 1 0 0 8a8 8 0 0 0 16 0m-7.5 3.5a.5.5 0 0 1-1 0V5.707L5.354 7.854a.5.5 0 1 1-.708-.708l3-3a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 5.707z"/>
                                                    </svg>
                                                <?php else: ?>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-dash-circle-fill text-amber-500 shrink-0" viewBox="0 0 16 16" title="Ở lại lớp <?php echo htmlspecialchars($hs['next_ten_lop']); ?> (Năm học <?php echo htmlspecialchars($hs['next_ten_nam_hoc'] ?? ''); ?>)">
                                                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M4.5 7.5a.5.5 0 0 0 0 1h7a.5.5 0 0 0 0-1z"/>
                                                    </svg>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="text-center"><?php echo htmlspecialchars($hs['ten_lop']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($hs['ngay_sinh'] ?? ''); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($hs['chuc_vu'] ?? ''); ?></td>
                                    <td class="text-center">
                                        <?php 
                                            $permissions = json_decode($hs['quyen_truy_cap'] ?? '{}', true); 
                                            $btnVP = !empty($permissions['nhap_vi_pham']) ? 'bg-green-600 text-white border-green-600 hover:bg-green-700' : 'bg-white text-slate-500 border-slate-300 hover:bg-slate-50';
                                            $btnT = !empty($permissions['dang_ky_truc']) ? 'bg-green-600 text-white border-green-600 hover:bg-green-700' : 'bg-white text-slate-500 border-slate-300 hover:bg-slate-50';
                                            $btnNK = !empty($permissions['so_nhat_ky_online']) ? 'bg-green-600 text-white border-green-600 hover:bg-green-700' : 'bg-white text-slate-500 border-slate-300 hover:bg-slate-50';
                                        ?>
                                        <div class="flex items-center justify-center gap-1" role="group">
                                            <button type="button" class="px-2 py-1 text-[11px] font-bold border rounded shadow-sm transition-all btn-permission <?php echo $btnVP; ?>" data-student-id="<?php echo $hs['id']; ?>" data-permission="nhap_vi_pham" title="Nhập Vi phạm">VP</button>
                                            <button type="button" class="px-2 py-1 text-[11px] font-bold border rounded shadow-sm transition-all btn-permission <?php echo $btnT; ?>" data-student-id="<?php echo $hs['id']; ?>" data-permission="dang_ky_truc" title="Đăng ký Trực">T</button>
                                            <button type="button" class="px-2 py-1 text-[11px] font-bold border rounded shadow-sm transition-all btn-permission <?php echo $btnNK; ?>" data-student-id="<?php echo $hs['id']; ?>" data-permission="so_nhat_ky_online" title="Sổ nhật kỳ Online">NK</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>

<div class="fixed inset-0 z-[10005] hidden flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0 transform transition-all duration-300 scale-95 translate-y-4 opacity-0 w-full" data-dialog-content="true">
        <div class="modal-content relative bg-white rounded shadow-2xl border border-slate-300 w-full max-w-[600px] text-left overflow-hidden flex flex-col max-h-[95vh]">
            <div class="bg-slate-50 border-b border-[#224397]/25 px-5 py-4 flex justify-between items-center shrink-0">
                <h5 class="text-[#224397] font-bold flex items-center gap-2 text-lg"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-upload mr-2" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/><path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708z"/></svg>Import Dữ Liệu Học Sinh</h5>
                <button type="button" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded transition" onclick="closeModal(this.closest('.fixed.inset-0').id)"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg></button>
            </div>
            <div class="p-6 overflow-y-auto list-scrollbar bg-white flex-1 space-y-4">
                <form id="importForm" action="/thidua/admin/hoc-sinh?action=import_process<?= isset($_GET['iframe']) ? '&iframe=1' : '' ?>" method="POST" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Chọn file Excel (.xlsx, .xls) <span class="text-red-600">*</span></label>
                        <input type="file" name="excelFile" accept=".xlsx, .xls" class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-[#224397]/50 focus:border-[#224397] outline-none transition text-sm" required>
                    </div>
                    <div class="p-3 bg-blue-50 border border-blue-200 rounded text-sm text-blue-800">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-info-circle-fill inline mr-1" viewBox="0 0 16 16"><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2"/></svg> Bạn có thể tải <a href="/thidua/tai-file-mau-hoc-sinh" class="font-bold underline text-[#224397]">file mẫu tại đây</a> để đảm bảo định dạng dữ liệu chính xác trước khi tải lên.
                    </div>
                </form>
            </div>
            <div class="bg-slate-50 border-t border-[#224397]/25 px-5 py-3 flex justify-end gap-2 shrink-0">
                <button type="button" class="px-4 py-2 bg-white border border-slate-300 rounded text-slate-700 hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] font-medium transition-all duration-300 text-sm" onclick="closeModal(this.closest('.fixed.inset-0').id)">Hủy</button>
                <button type="submit" form="importForm" class="px-4 py-2 bg-[#224397] text-white rounded hover:bg-[#FAB723] font-medium shadow-sm hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 flex items-center gap-2 text-sm"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-arrow-right-circle" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H4.5z"/></svg> Xem Trước</button>
            </div>
        </div>
    </div>
</div>

<div class="fixed inset-0 z-[10005] hidden flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0" id="studentModal" tabindex="-1" aria-labelledby="studentModalLabel" aria-hidden="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0 transform transition-all duration-300 scale-95 translate-y-4 opacity-0 w-full" data-dialog-content="true">
        <div class="modal-content relative bg-white rounded shadow-2xl border border-slate-300 w-full max-w-[800px] text-left overflow-hidden flex flex-col max-h-[95vh]">
            <div class="bg-slate-50 border-b border-[#224397]/25 px-5 py-4 flex justify-between items-center shrink-0">
                <h5 class="text-[#224397] font-bold flex items-center gap-2 text-lg" id="studentModalLabel">Thêm Mới Học Sinh</h5>
                <button type="button" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded transition" onclick="closeModal(this.closest('.fixed.inset-0').id)"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg></button>
            </div>
            <div class="p-6 overflow-y-auto list-scrollbar bg-white flex-1 space-y-4">
                <form id="studentForm" action="/thidua/admin/hoc-sinh?action=add<?= isset($_GET['iframe']) ? '&iframe=1' : '' ?>" method="POST" class="flex flex-col h-full overflow-hidden">
                    <input type="hidden" id="student_id" name="student_id">
                    <div class="flex flex-wrap -mx-3">
                        <div class="w-full md:w-1/3 px-6 mb-6"><label for="ma_hoc_sinh" class="block text-sm font-medium text-slate-700 mb-1">Số CCCD <span class="text-red-600">*</span></label><input type="text" class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-[#224397]/50 focus:border-[#224397] outline-none transition text-sm" id="ma_hoc_sinh" name="ma_hoc_sinh" required></div>
                        <div class="w-full md:w-1/3 px-6 mb-6"><label for="ho_ten" class="block text-sm font-medium text-slate-700 mb-1">Họ và Tên <span class="text-red-600">*</span></label><input type="text" class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-[#224397]/50 focus:border-[#224397] outline-none transition text-sm" id="ho_ten" name="ho_ten" required></div>
                        <div class="w-full md:w-1/3 px-6 mb-6"><label for="nien_khoa" class="block text-sm font-medium text-slate-700 mb-1">Niên Khóa</label><input type="text" class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-[#224397]/50 focus:border-[#224397] outline-none transition text-sm" id="nien_khoa" name="nien_khoa" placeholder="VD: 2023-2026"></div>
                    </div>
                    <div class="flex flex-wrap -mx-3">
                        <div class="w-full md:w-1/2 px-6 mb-6">
                            <label for="ten_lop" class="block text-sm font-medium text-slate-700 mb-1">Lớp <span class="text-red-600">*</span></label>
                            <input class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-[#224397]/50 focus:border-[#224397] outline-none transition text-sm" list="datalistOptionsLop" id="ten_lop" name="ten_lop" placeholder="Nhập để tìm hoặc tạo mới..." required>
                            <datalist id="datalistOptionsLop">
                                <?php foreach ($danh_sach_lop as $lop) : ?>
                                    <option value="<?php echo htmlspecialchars($lop['ten_lop']); ?>">
                                    <?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="w-full md:w-1/2 px-6 mb-6"><label for="ngay_sinh" class="block text-sm font-medium text-slate-700 mb-1">Ngày sinh <span class="text-red-600">*</span></label><input type="text" class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-[#224397]/50 focus:border-[#224397] outline-none transition text-sm" id="ngay_sinh" name="ngay_sinh" placeholder="dd/mm/yyyy" required></div>
                    </div>
                    <div class="flex flex-wrap -mx-3">
                        <div class="w-full md:w-1/2 px-6 mb-6"><label class="block text-sm font-medium text-slate-700 mb-1">Giới tính <span class="text-red-600">*</span></label>
                            <div>
                                <div class="flex items-center flex align-items-center-inline"><input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="radio" name="gioi_tinh" id="gioi_tinh_nam" value="Nam" required><label class="ml-2 block text-sm text-slate-900" for="gioi_tinh_nam">Nam</label></div>
                                <div class="flex items-center flex align-items-center-inline"><input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="radio" name="gioi_tinh" id="gioi_tinh_nu" value="Nữ"><label class="ml-2 block text-sm text-slate-900" for="gioi_tinh_nu">Nữ</label></div>
                            </div>
                        </div>
                        <div class="w-full md:w-1/2 px-6 mb-6"><label for="modal_chuc_vu" class="block text-sm font-medium text-slate-700 mb-1">Chức vụ</label><select class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-[#224397]/50 focus:border-[#224397] outline-none transition text-sm" id="modal_chuc_vu" name="chuc_vu">
                                <option value="">-- Không có --</option>
                                <option value="Học sinh">Học sinh</option>
                                <option value="Lớp trưởng">Lớp trưởng</option>
                                <option value="Bí thư">Bí thư</option>
                                <option value="Lớp phó">Lớp phó</option>
                            </select></div>
                    </div>
                    <div class="flex flex-wrap -mx-3">
                        <div class="w-full md:w-1/4 px-6 mb-6">
                            <label for="tinh_thanhpho" class="block text-sm font-medium text-slate-700 mb-1">Tỉnh/Thành phố</label>
                            <input type="text" class="w-full px-4 py-2 border border-slate-300 rounded bg-slate-100 text-slate-500 text-sm" id="tinh_thanhpho" name="tinh_thanhpho" value="Thành phố Đồng Nai" readonly>
                        </div>
                        <div class="w-full md:w-1/4 px-6 mb-6">
                            <label for="xa_phuong" class="block text-sm font-medium text-slate-700 mb-1">Xã/Phường <span class="text-red-600">*</span></label>
                            <select class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-[#224397]/50 focus:border-[#224397] outline-none transition text-sm" id="xa_phuong" name="xa_phuong" required>
                                <option value="">-- Chọn --</option>
                            </select>
                        </div>
                        <div class="w-full md:w-1/4 px-6 mb-6">
                            <label for="ap_khupho" class="block text-sm font-medium text-slate-700 mb-1">Ấp/Khu phố <span class="text-red-600">*</span></label>
                            <select class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-[#224397]/50 focus:border-[#224397] outline-none transition text-sm" id="ap_khupho" name="ap_khupho" required>
                                <option value="">-- Chọn --</option>
                            </select>
                        </div>
                        <div class="w-full md:w-1/4 px-6 mb-6">
                            <label for="dia_chi_chi_tiet" class="block text-sm font-medium text-slate-700 mb-1">Địa chỉ chi tiết</label>
                            <input type="text" class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-[#224397]/50 focus:border-[#224397] outline-none transition text-sm" id="dia_chi_chi_tiet" name="dia_chi_chi_tiet" placeholder="Số nhà, hẻm...">
                        </div>
                    </div>
                    <div class="flex flex-wrap -mx-3">
                        <div class="w-full md:w-1/2 px-6 mb-6"><label for="email" class="block text-sm font-medium text-slate-700 mb-1">Gmail</label><input type="email" class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-[#224397]/50 focus:border-[#224397] outline-none transition text-sm" id="email" name="email"></div>
                        <div class="w-full md:w-1/2 px-6 mb-6"><label for="sdt" class="block text-sm font-medium text-slate-700 mb-1">Số điện thoại</label><input type="tel" class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-[#224397]/50 focus:border-[#224397] outline-none transition text-sm" id="sdt" name="sdt"></div>
                    </div>
                </form>
            </div>
            <div class="bg-slate-50 border-t border-[#224397]/25 px-5 py-3 flex justify-end gap-2 shrink-0">
                <button type="button" class="px-4 py-2 bg-white border border-slate-300 rounded text-slate-700 hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] font-medium transition-all duration-300 text-sm" onclick="closeModal(this.closest('.fixed.inset-0').id)">Đóng</button>
                <button type="submit" form="studentForm" class="px-4 py-2 bg-[#224397] text-white rounded hover:bg-[#FAB723] font-medium shadow-sm hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 flex items-center gap-2 text-sm"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-save" viewBox="0 0 16 16"><path d="M2 1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H9.5a1 1 0 0 0-1 1v7.293l2.646-2.647a.5.5 0 0 1 .708.708l-3.5 3.5a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L7.5 9.293V2a2 2 0 0 1 2-2H14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h2.5a.5.5 0 0 1 0 1z"/></svg> Lưu Thay Đổi</button>
            </div>
        </div>
    </div>
</div>
<div class="fixed inset-0 z-[10005] hidden flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0" id="quickGrantModal" tabindex="-1" aria-labelledby="quickGrantModalLabel" aria-hidden="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0 transform transition-all duration-300 scale-95 translate-y-4 opacity-0 w-full" data-dialog-content="true">
        <div class="modal-content relative bg-white rounded shadow-2xl border border-slate-300 w-full max-w-[600px] text-left overflow-hidden flex flex-col max-h-[95vh]">
            <div class="bg-slate-50 border-b border-[#224397]/25 px-5 py-4 flex justify-between items-center shrink-0">
                <h5 class="text-[#224397] font-bold flex items-center gap-2 text-lg" id="quickGrantModalLabel"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-shield-plus mr-2" viewBox="0 0 16 16"><path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56"/>   <path d="M8 4.5a.5.5 0 0 1 .5.5v1.5H10a.5.5 0 0 1 0 1H8.5V9a.5.5 0 0 1-1 0V7.5H6a.5.5 0 0 1 0-1h1.5V5a.5.5 0 0 1 .5-.5"/></svg>Cấp Quyền Nhanh Cho Cộng Tác Viên</h5>
                <button type="button" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded transition" onclick="closeModal(this.closest('.fixed.inset-0').id)"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg></button>
            </div>
            <div class="p-6 overflow-y-auto list-scrollbar bg-white flex-1 space-y-4">
                <form id="quickGrantForm">
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 mb-1 font-bold">1. Chọn đối tượng áp dụng:</label>
                        <select class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-[#224397]/50 focus:border-[#224397] outline-none transition text-sm" id="target-type-select" name="target_type">
                            <option value="chuc_vu">Theo Chức vụ</option>
                            <option value="lop">Theo Lớp</option>
                            <option value="hoc_sinh">Theo Học sinh cụ thể</option>
                        </select>
                    </div>

                    <div id="target-value-container" class="mt-3 p-4 bg-slate-50 border border-[#224397]/10 rounded shadow-inner mb-6">
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 mb-1 font-bold">2. Chọn các quyền để cấp:</label>
                        <div class="flex items-center"><input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="checkbox" value="1" name="permissions[nhap_vi_pham]" id="grant_nhap_vi_pham"><label class="ml-2 block text-sm text-slate-900" for="grant_nhap_vi_pham">Quyền Nhập Vi phạm</label></div>
           
                        <div class="flex items-center"><input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="checkbox" value="1" name="permissions[dang_ky_truc]" id="grant_dang_ky_truc"><label class="ml-2 block text-sm text-slate-900" for="grant_dang_ky_truc">Quyền Đăng ký Trực</label></div>
                        <div class="flex items-center">
  <input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="checkbox" value="1" name="permissions[so_nhat_ky_online]" id="grant_so_nhat_ky_online">
  <label class="ml-2 block text-sm text-slate-900" for="grant_so_nhat_ky_online">Quyền Sổ nhật kỳ Online</label>
</div>

                    </div>
                </form>
            </div>
            <div class="bg-slate-50 border-t border-[#224397]/25 px-5 py-3 flex justify-end gap-2 shrink-0">
                <button type="button" class="px-4 py-2 bg-white border border-slate-300 rounded text-slate-700 hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] font-medium transition-all duration-300 text-sm" onclick="closeModal(this.closest('.fixed.inset-0').id)">Đóng</button>
                <button type="submit" form="quickGrantForm" class="px-4 py-2 bg-[#224397] text-white rounded hover:bg-[#FAB723] font-medium shadow-sm hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 flex items-center gap-2 text-sm"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/></svg> Áp Dụng Quyền</button>
            </div>
        </div>
    </div>
</div>

<div class="fixed inset-0 z-[10005] hidden flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0" id="quickRevokeModal" tabindex="-1" aria-hidden="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0 transform transition-all duration-300 scale-95 translate-y-4 opacity-0 w-full" data-dialog-content="true">
        <div class="modal-content relative bg-white rounded shadow-2xl border border-slate-300 w-full max-w-[600px] text-left overflow-hidden flex flex-col max-h-[95vh]">
            <div class="bg-slate-50 border-b border-[#224397]/25 px-5 py-4 flex justify-between items-center shrink-0">
                <h5 class="text-[#224397] font-bold flex items-center gap-2 text-lg"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-shield-slash-fill mr-2" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.093 3.093c-.465 4.275.885 7.46 2.513 9.589a11.8 11.8 0 0 0 2.517 2.453c.386.273.744.482 1.048.625.28.132.581.24.829.24s.548-.108.829-.24a7 7 0 0 0 1.048-.625 11.3 11.3 0 0 0 1.733-1.525zm12.215 8.215L3.128 1.128A61 61 0 0 1 5.073.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.483 3.626-.332 6.491-1.551 8.616m.338 3.046-13-13 .708-.708 13 13z"/></svg>Thu Hồi Quyền Hàng Loạt</h5>
                <button type="button" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded transition" onclick="closeModal(this.closest('.fixed.inset-0').id)"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg></button>
            </div>
            <div class="p-6 overflow-y-auto list-scrollbar bg-white flex-1 space-y-4">
                <form id="quickRevokeForm">
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 mb-1 font-bold">1. Chọn đối tượng bị thu hồi:</label>
                        <select class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-[#224397]/50 focus:border-[#224397] outline-none transition text-sm" name="target_type">
                            <option value="all">Toàn bộ học sinh</option>
                            <option value="lop">Theo Lớp</option>
                            <option value="chuc_vu">Theo Chức vụ</option>
                        </select>
                    </div>
                    <div class="mt-3 p-4 bg-slate-50 border border-[#224397]/10 rounded shadow-inner mb-6" id="revoke-target-value-container" style="display: none;">
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 mb-1 font-bold">2. Chọn hành động thu hồi:</label>
                        <select class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-[#224397]/50 focus:border-[#224397] outline-none transition text-sm" name="revoke_action">
                            <option value="all">Thu hồi toàn bộ</option>
                            <option value="nhap_vi_pham">Chỉ thu hồi quyền nhập VP</option>
        
                            <option value="dang_ky_truc">Chỉ thu hồi quyền đăng ký trực</option>
                            <option value="so_nhat_ky_online">Chỉ thu hồi quyền Sổ nhật kỳ Online</option>

                        </select>
                    </div>
                </form>
            </div>
            <div class="bg-slate-50 border-t border-[#224397]/25 px-5 py-3 flex justify-end gap-2 shrink-0">
                <button type="button" class="px-4 py-2 bg-white border border-slate-300 rounded text-slate-700 hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] font-medium transition-all duration-300 text-sm" onclick="closeModal(this.closest('.fixed.inset-0').id)">Hủy</button>
                <button type="submit" form="quickRevokeForm" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 font-medium shadow-sm hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 flex items-center gap-2 text-sm"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-exclamation-triangle" viewBox="0 0 16 16"><path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z"/><path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z"/></svg> Xác Nhận Thu Hồi</button>
            </div>
        </div>
    </div>
</div>

<div class="fixed inset-0 z-[10005] hidden flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0" id="confirmRevokeModal" tabindex="-1" aria-hidden="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0 transform transition-all duration-300 scale-95 translate-y-4 opacity-0 w-full" data-dialog-content="true">
        <div class="modal-content relative bg-white rounded shadow-2xl border border-slate-300 w-full max-w-[400px] text-left overflow-hidden flex flex-col max-h-[95vh]">
            <div class="p-6 overflow-y-auto list-scrollbar bg-white flex-1 space-y-4 text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-2 mt-4">
                    <svg class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-lg leading-6 font-bold text-slate-900">Cảnh Báo Thu Hồi</h3>
                <div class="mt-2">
                    <p class="text-sm text-slate-500">Bạn có chắc chắn muốn thực hiện hành động thu hồi quyền này không? Hành động này không thể hoàn tác.</p>
                </div>
            </div>
            <div class="bg-slate-50 border-t border-[#224397]/25 px-5 py-3 flex justify-center gap-2 shrink-0">
                <button type="button" class="px-4 py-2 w-full bg-white border border-slate-300 rounded text-slate-700 hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition font-medium shadow-sm text-sm" onclick="closeModal('confirmRevokeModal'); openModal('quickRevokeModal');">Hủy / Quay lại</button>
                <button type="button" class="px-4 py-2 w-full bg-red-600 text-white rounded hover:bg-red-700 transition font-medium shadow-sm text-sm" id="confirm-revoke-btn">Xác Nhận Thu Hồi</button>
            </div>
        </div>
    </div>
</div>

<div class="fixed inset-0 z-[10005] hidden flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0" id="exportCtvOptionsModal" tabindex="-1" aria-labelledby="exportCtvOptionsModalLabel" aria-hidden="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0 transform transition-all duration-300 scale-95 translate-y-4 opacity-0 w-full" data-dialog-content="true">
        <div class="modal-content relative bg-white rounded shadow-2xl border border-slate-300 w-full max-w-[600px] text-left overflow-hidden flex flex-col max-h-[95vh]">
            <div class="bg-slate-50 border-b border-[#224397]/25 px-5 py-4 flex justify-between items-center shrink-0">
                <h5 class="text-[#224397] font-bold flex items-center gap-2 text-lg" id="exportCtvOptionsModalLabel"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-columns-gap mr-2" viewBox="0 0 16 16"><path d="M6 1v3H1V1zM1 0a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h5a1 1 0 0 0 1-1V1a1 1 0 0 0-1-1zm14 12v3h-5v-3zm-5-1a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h5a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1zM6 8v7H1V8zM1 7a1 1 0 0 0-1 1v7a1 1 0 0 0 1 1h5a1 1 0 0 0 1-1V8a1 1 0 0 0-1-1zm14-6v7h-5V1zm-5-1a1 1 0 0 0-1 1v7a1 1 0 0 0 1 1h5a1 1 0 0 0 1-1V1a1 1 0 0 0-1-1z"/></svg>Tùy Chọn Cột Xuất DS CTV</h5>
                <button type="button" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded transition" onclick="closeModal(this.closest('.fixed.inset-0').id)"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg></button>
            </div>
            <form id="exportCtvOptionsForm">
                <div class="p-6 overflow-y-auto list-scrollbar bg-white flex-1 space-y-4">
                    <p>Chọn phạm vi dữ liệu và các cột bạn muốn xuất. Mặc định đã chọn tất cả.</p>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 mb-1 font-bold">Phạm vi xuất:</label>
                        <select name="export_scope" class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-[#224397]/50 focus:border-[#224397] outline-none transition text-sm">
                            <option value="all">Toàn bộ học sinh</option>
                            <option value="has_account">Chỉ những HS đã được cấp tài khoản</option>
                            <option value="has_permission" selected>Chỉ những HS đã được cấp quyền (CTV)</option>
                        </select>
                    </div>

                    <label class="block text-sm font-medium text-slate-700 mb-1 font-bold">Các cột cần xuất:</label>
                    <div class="flex flex-wrap -mx-3">
                        <div class="w-1/2 px-6">
                            <div class="flex items-center"><input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="checkbox" name="columns[]" value="khoi" id="ctv_col_khoi_2" checked><label class="ml-2 block text-sm text-slate-900" for="ctv_col_khoi_2">Khối</label></div>
                            <div class="flex items-center"><input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="checkbox" name="columns[]" value="lop" id="ctv_col_lop_2" checked><label class="ml-2 block text-sm text-slate-900" for="ctv_col_lop_2">Lớp</label></div>
                            <div class="flex items-center"><input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="checkbox" name="columns[]" value="nien_khoa" id="ctv_col_nien_khoa_2" checked><label class="ml-2 block text-sm text-slate-900" for="ctv_col_nien_khoa_2">Niên khóa</label></div>
                            <div class="flex items-center"><input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="checkbox" name="columns[]" value="ma_hs" id="ctv_col_ma_hs_2" checked><label class="ml-2 block text-sm text-slate-900" for="ctv_col_ma_hs_2">Số CCCD</label></div>
                            <div class="flex items-center"><input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="checkbox" name="columns[]" value="ho_ten" id="ctv_col_ho_ten_2" checked><label class="ml-2 block text-sm text-slate-900" for="ctv_col_ho_ten_2">Họ và Tên</label></div>
                            <div class="flex items-center"><input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="checkbox" name="columns[]" value="ngay_sinh" id="ctv_col_ngay_sinh_2" checked><label class="ml-2 block text-sm text-slate-900" for="ctv_col_ngay_sinh_2">Ngày sinh</label></div>
                            <div class="flex items-center"><input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="checkbox" name="columns[]" value="gioi_tinh" id="ctv_col_gioi_tinh_2" checked><label class="ml-2 block text-sm text-slate-900" for="ctv_col_gioi_tinh_2">Giới tính</label></div>
                            <div class="flex items-center"><input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="checkbox" name="columns[]" value="chuc_vu" id="ctv_col_chuc_vu_2" checked><label class="ml-2 block text-sm text-slate-900" for="ctv_col_chuc_vu_2">Chức vụ</label></div>
                            <div class="flex items-center"><input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="checkbox" name="columns[]" value="sdt" id="ctv_col_sdt_2" checked><label class="ml-2 block text-sm text-slate-900" for="ctv_col_sdt_2">SĐT</label></div>
                        </div>
                        <div class="w-1/2 px-6">
                            <div class="flex items-center"><input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="checkbox" name="columns[]" value="gmail" id="ctv_col_gmail_2" checked><label class="ml-2 block text-sm text-slate-900" for="ctv_col_gmail_2">Gmail</label></div>
                            <div class="flex items-center"><input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="checkbox" name="columns[]" value="trang_thai_tk" id="ctv_col_trang_thai_tk_2" checked><label class="ml-2 block text-sm text-slate-900" for="ctv_col_trang_thai_tk_2">Trạng thái TK</label></div>
                            <div class="flex items-center"><input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="checkbox" name="columns[]" value="quyen_vp" id="ctv_col_quyen_vp_2" checked><label class="ml-2 block text-sm text-slate-900" for="ctv_col_quyen_vp_2">Quyền Nhập VP</label></div>

                            <div class="flex items-center"><input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="checkbox" name="columns[]" value="quyen_truc" id="ctv_col_quyen_truc_2" checked><label class="ml-2 block text-sm text-slate-900" for="ctv_col_quyen_truc_2">Quyền Trực</label></div>
                            <div class="flex items-center">
  <input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="checkbox" name="columns[]" value="quyen_nhat_ky" id="ctv_col_quyen_nhat_ky_2" checked>
  <label class="ml-2 block text-sm text-slate-900" for="ctv_col_quyen_nhat_ky_2">Quyền Sổ nhật kỳ Online</label>
</div>

                            <div class="flex items-center"><input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="checkbox" name="columns[]" value="dia_chi" id="ctv_col_dia_chi_2" checked><label class="ml-2 block text-sm text-slate-900" for="ctv_col_dia_chi_2">Địa chỉ</label></div>
                            <div class="flex items-center"><input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="checkbox" name="columns[]" value="ghi_chu" id="ctv_col_ghi_chu_2" checked><label class="ml-2 block text-sm text-slate-900" for="ctv_col_ghi_chu_2">Ghi chú</label></div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 border-t border-[#224397]/25 px-5 py-3 flex justify-end gap-2 shrink-0">
                    <button type="button" class="px-4 py-2 bg-white border border-slate-300 rounded text-slate-700 hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] font-medium transition-all duration-300 text-sm" onclick="closeModal(this.closest('.fixed.inset-0').id)">Hủy</button>
                    <button type="submit" form="exportCtvOptionsForm" class="px-4 py-2 bg-[#224397] text-white rounded hover:bg-[#FAB723] font-medium shadow-sm hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 flex items-center gap-2 text-sm"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-excel-fill" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M5.884 6.68 8 9.219l2.116-2.54a.5.5 0 1 1 .768.641L8.651 10l2.233 2.68a.5.5 0 0 1-.768.64L8 10.781l-2.116 2.54a.5.5 0 0 1-.768-.641L7.349 10 5.116 7.32a.5.5 0 1 1 .768-.64"/></svg> Xác Nhận Xuất File</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="fixed inset-0 z-[10005] hidden flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0" id="exportOptionsModal" tabindex="-1" aria-labelledby="exportOptionsModalLabel" aria-hidden="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0 transform transition-all duration-300 scale-95 translate-y-4 opacity-0 w-full" data-dialog-content="true">
        <div class="modal-content relative bg-white rounded shadow-2xl border border-slate-300 w-full max-w-[600px] text-left overflow-hidden flex flex-col max-h-[95vh]">
            <div class="bg-slate-50 border-b border-[#224397]/25 px-5 py-4 flex justify-between items-center shrink-0">
                <h5 class="text-[#224397] font-bold flex items-center gap-2 text-lg" id="exportOptionsModalLabel"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-columns-gap mr-2" viewBox="0 0 16 16"><path d="M6 1v3H1V1zM1 0a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h5a1 1 0 0 0 1-1V1a1 1 0 0 0-1-1zm14 12v3h-5v-3zm-5-1a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h5a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1zM6 8v7H1V8zM1 7a1 1 0 0 0-1 1v7a1 1 0 0 0 1 1h5a1 1 0 0 0 1-1V8a1 1 0 0 0-1-1zm14-6v7h-5V1zm-5-1a1 1 0 0 0-1 1v7a1 1 0 0 0 1 1h5a1 1 0 0 0 1-1V1a1 1 0 0 0-1-1z"/></svg>Tùy Chọn Cột Xuất Ra Excel</h5>
                <button type="button" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded transition" onclick="closeModal(this.closest('.fixed.inset-0').id)"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg></button>
            </div>
            <div class="p-6 overflow-y-auto list-scrollbar bg-white flex-1 space-y-4">
                <p>Vui lòng chọn các trường thông tin bạn muốn xuất ra file Excel.</p>
                <form id="exportOptionsForm">
                    <div class="flex flex-wrap -mx-3">
                        <div class="w-1/2 px-6">
                            <div class="flex items-center"><input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="checkbox" name="columns[]" value="khoi" id="col_khoi" checked><label class="ml-2 block text-sm text-slate-900" for="col_khoi">Khối</label></div>
                            <div class="flex items-center"><input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="checkbox" name="columns[]" value="lop" id="col_lop" checked><label class="ml-2 block text-sm text-slate-900" for="col_lop">Lớp</label></div>
                            <div class="flex items-center"><input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="checkbox" name="columns[]" value="nien_khoa" id="col_nien_khoa" checked><label class="ml-2 block text-sm text-slate-900" for="col_nien_khoa">Niên khóa</label></div>
                            <div class="flex items-center"><input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="checkbox" name="columns[]" value="ma_hs" id="col_ma_hs" checked><label class="ml-2 block text-sm text-slate-900" for="col_ma_hs">Số CCCD</label></div>
                            <div class="flex items-center"><input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="checkbox" name="columns[]" value="ho_ten" id="col_ho_ten" checked><label class="ml-2 block text-sm text-slate-900" for="col_ho_ten">Họ và Tên</label></div>
                            <div class="flex items-center"><input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="checkbox" name="columns[]" value="ngay_sinh" id="col_ngay_sinh" checked><label class="ml-2 block text-sm text-slate-900" for="col_ngay_sinh">Ngày sinh</label></div>
                        </div>
                        <div class="w-1/2 px-6">
                            <div class="flex items-center"><input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="checkbox" name="columns[]" value="gioi_tinh" id="col_gioi_tinh" checked><label class="ml-2 block text-sm text-slate-900" for="col_gioi_tinh">Giới tính</label></div>
                            <div class="flex items-center"><input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="checkbox" name="columns[]" value="chuc_vu" id="col_chuc_vu" checked><label class="ml-2 block text-sm text-slate-900" for="col_chuc_vu">Chức vụ</label></div>
                            <div class="flex items-center"><input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="checkbox" name="columns[]" value="sdt" id="col_sdt" checked><label class="ml-2 block text-sm text-slate-900" for="col_sdt">Số điện thoại</label></div>
                            <div class="flex items-center"><input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="checkbox" name="columns[]" value="gmail" id="col_gmail" checked><label class="ml-2 block text-sm text-slate-900" for="col_gmail">Gmail</label></div>
                            <div class="flex items-center"><input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="checkbox" name="columns[]" value="dia_chi" id="col_dia_chi" checked><label class="ml-2 block text-sm text-slate-900" for="col_dia_chi">Địa chỉ</label></div>
                            <div class="flex items-center"><input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="checkbox" name="columns[]" value="ghi_chu" id="col_ghi_chu"><label class="ml-2 block text-sm text-slate-900" for="col_ghi_chu">Ghi chú</label></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-slate-50 border-t border-[#224397]/25 px-5 py-3 flex justify-end gap-2 shrink-0">
                <button type="button" class="px-4 py-2 bg-white border border-slate-300 rounded text-slate-700 hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] font-medium transition-all duration-300 text-sm" onclick="closeModal(this.closest('.fixed.inset-0').id)">Hủy</button>
                <button type="submit" form="exportOptionsForm" class="px-4 py-2 bg-[#224397] text-white rounded hover:bg-[#FAB723] font-medium shadow-sm hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 flex items-center gap-2 text-sm"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-excel-fill" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M5.884 6.68 8 9.219l2.116-2.54a.5.5 0 1 1 .768.641L8.651 10l2.233 2.68a.5.5 0 0 1-.768.64L8 10.781l-2.116 2.54a.5.5 0 0 1-.768-.641L7.349 10 5.116 7.32a.5.5 0 1 1 .768-.64"/></svg> Xác Nhận Xuất File</button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>

<script>
// Modal Animation Utility
function openModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    const content = modal.querySelector('[data-dialog-content="true"]') || modal.querySelector('.modal-content') || modal.firstElementChild;
    modal.classList.remove('hidden');
    void modal.offsetWidth;
    modal.classList.remove('opacity-0');
    if (content) content.classList.remove('scale-95', 'translate-y-4', 'opacity-0');
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    const content = modal.querySelector('[data-dialog-content="true"]') || modal.querySelector('.modal-content') || modal.firstElementChild;
    modal.classList.add('opacity-0');
    if (content) content.classList.add('scale-95', 'translate-y-4', 'opacity-0');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

document.addEventListener('DOMContentLoaded', function() {
    const studentModal = document.getElementById('studentModal');
    const quickGrantModal = document.getElementById('quickGrantModal');
    const quickRevokeModal = document.getElementById('quickRevokeModal');
    const exportCtvOptionsModal = document.getElementById('exportCtvOptionsModal');
    const exportOptionsModal = document.getElementById('exportOptionsModal');
    const studentTable = document.getElementById('studentTable');
    const defaultToolbar = document.getElementById('default-toolbar');
    const deleteToolbar = document.getElementById('delete-toolbar');
    const editModeAlert = document.getElementById('editModeAlert');
    let isEditMode = false;
    const khoiSelect = document.getElementById('khoi');
    const lopSelect = document.getElementById('lop_id');
    const originalLopOptions = Array.from(lopSelect.options);
    function filterLopTheoKhoi() {
        const selectedKhoi = khoiSelect.value;
        const selectedLopId = lopSelect.value;
        lopSelect.innerHTML = '';
        originalLopOptions.forEach(option => {
            if (option.value === 'all' || selectedKhoi === 'all' || option.dataset.khoi === selectedKhoi) {
                lopSelect.appendChild(option.cloneNode(true));
            }
        });
        lopSelect.value = selectedLopId;
        if (lopSelect.selectedIndex === -1) {
            lopSelect.value = 'all';
        }
    }
    filterLopTheoKhoi();
    
    studentTable.addEventListener('click', function(e) {
        if (!e.target.classList.contains('btn btn-sm-permission')) return;
        const button = e.target;
        const studentId = button.closest('tr').dataset.id;
        const permission = button.dataset.permission;
        button.disabled = true;
        fetch('/thidua/admin/ctv?action=api_toggle_permission', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    student_id: studentId,
                    permission_type: permission
                })
            }).then(res => res.json())
            .then(data => {
                if (data.success) {
                    const isChecked = !button.classList.contains('bg-primary-600 hover:bg-primary-700 text-white shadow-sm border-transparent') && !button.classList.contains('btn-info') && !button.classList.contains('btn-warning');
                    button.classList.toggle('bg-primary-600 hover:bg-primary-700 text-white shadow-sm border-transparent', permission === 'nhap_vi_pham' && isChecked);
                    button.classList.toggle('bg-transparent hover:bg-primary-600 text-primary-600 hover:text-white border border-primary-600', permission === 'nhap_vi_pham' && !isChecked);
                    button.classList.toggle('btn-info', permission === 'nhap_diem_danh' && isChecked);
                    button.classList.toggle('btn btn-sm-outline-info', permission === 'nhap_diem_danh' && !isChecked);
                    button.classList.toggle('btn-warning', permission === 'dang_ky_truc' && isChecked);
                    button.classList.toggle('btn btn-sm-outline-warning', permission === 'dang_ky_truc' && !isChecked);
                    button.classList.toggle('btn-success', permission === 'so_nhat_ky_online' && isChecked);
button.classList.toggle('btn btn-sm-outline-success', permission === 'so_nhat_ky_online' && !isChecked);

                } else {
                    showToast('error', data.message);
                }
            }).finally(() => {
                button.disabled = false;
            });
    });
    document.getElementById('editModeBtn').addEventListener('click', function(e) {
        e.preventDefault();
        isEditMode = !isEditMode;
        studentTable.classList.toggle('-pointer', isEditMode);
        editModeAlert.classList.toggle('hidden', !isEditMode);
        this.classList.toggle('active');
    });
    studentTable.addEventListener('click', function(event) {
        if (!isEditMode || event.target.closest('.btn btn-sm-permission') || event.target.closest('.delete-col')) return;
        const row = event.target.closest('tr');
        if (!row || !row.dataset.id) return;
        const studentId = row.dataset.id;
        fetch(`/thidua/api/get-hoc-sinh-details?id=${studentId}`)
            .then(response => response.json())
            .then(data => {
                const studentForm = document.getElementById('studentForm');
                document.getElementById('studentModalLabel').textContent = 'Chỉnh Sửa Thông Tin Học Sinh';
                studentForm.action = '/thidua/admin/hoc-sinh?action=edit<?= isset($_GET['iframe']) ? '&iframe=1' : '' ?>';
                studentForm.reset();
                document.getElementById('student_id').value = data.id;
                document.getElementById('ma_hoc_sinh').value = data.ma_hoc_sinh;
                document.getElementById('ho_ten').value = (data.ho_dem + ' ' + data.ten).trim();
                document.getElementById('ten_lop').value = data.ten_lop;
                document.getElementById('ngay_sinh').value = data.ngay_sinh || '';
                document.getElementById('modal_chuc_vu').value = data.chuc_vu || '';
                document.getElementById('nien_khoa').value = data.nien_khoa || '';
                document.getElementById('sdt').value = data.sdt || '';
                document.getElementById('email').value = data.email || '';
                document.getElementById('tinh_thanhpho').value = data.tinh_thanhpho || 'Thành phố Đồng Nai';
                
                // Set Xã/Phường và Ấp/Khu phố
                const xaSelect = document.getElementById('xa_phuong');
                xaSelect.value = data.xa_phuong || '';
                // Trigger change to populate Ấp
                xaSelect.dispatchEvent(new Event('change'));
                
                // Đợi 1 chút để change event xử lý xong options rồi gán giá trị ấp
                setTimeout(() => {
                    document.getElementById('ap_khupho').value = data.ap_khupho || '';
                }, 10);
                
                document.getElementById('dia_chi_chi_tiet').value = data.dia_chi_chi_tiet || '';

                if (data.gioi_tinh === 'Nam') {
                    document.getElementById('gioi_tinh_nam').checked = true;
                } else if (data.gioi_tinh === 'Nữ') {
                    document.getElementById('gioi_tinh_nu').checked = true;
                }
                openModal('studentModal');
            });
    });
    const deleteModeBtn = document.getElementById('delete-mode-btn');
    const cancelDeleteBtn = document.getElementById('cancel-delete-btn');
    const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
    const selectAllCheckbox = document.getElementById('select-all-delete');
    const deleteModeAlert = document.getElementById('deleteModeAlert');
    const graduateToolbar = document.getElementById('graduate-toolbar');
    const graduateModeAlert = document.getElementById('graduateModeAlert');

    function toggleDeleteMode(active) {
        studentTable.classList.toggle('deletion-mode', active);
        defaultToolbar.classList.toggle('hidden', active);
        deleteToolbar.classList.toggle('hidden', !active);
        deleteModeAlert.classList.toggle('hidden', !active);
        if (!active) {
            selectAllCheckbox.checked = false;
            document.querySelectorAll('.delete-checkbox').forEach(cb => cb.checked = false);
        }
    }
    deleteModeBtn.addEventListener('click', (e) => { e.preventDefault(); toggleDeleteMode(true); });
    cancelDeleteBtn.addEventListener('click', () => toggleDeleteMode(false));
    
    const graduateModeBtn = document.getElementById('graduate-mode-btn');
    const cancelGraduateBtn = document.getElementById('cancel-graduate-btn');
    const confirmGraduateBtn = document.getElementById('confirm-graduate-btn');
    
    function toggleGraduateMode(active) {
        studentTable.classList.toggle('deletion-mode', active);
        defaultToolbar.classList.toggle('hidden', active);
        graduateToolbar.classList.toggle('hidden', !active);
        graduateModeAlert.classList.toggle('hidden', !active);
        if (!active) {
            selectAllCheckbox.checked = false;
            document.querySelectorAll('.delete-checkbox').forEach(cb => cb.checked = false);
        }
    }
    graduateModeBtn.addEventListener('click', (e) => { e.preventDefault(); toggleGraduateMode(true); });
    cancelGraduateBtn.addEventListener('click', () => toggleGraduateMode(false));
    
    confirmGraduateBtn.addEventListener('click', async function() {
        const idsToGraduate = Array.from(document.querySelectorAll('.delete-checkbox:checked')).map(cb => cb.value);
        if (idsToGraduate.length === 0) {
            showToast('error', 'Vui lòng chọn ít nhất một học sinh.');
            return;
        }
        const namTotNghiep = document.getElementById('bulk-nam-tot-nghiep').value;
        if (!namTotNghiep) {
            showToast('error', 'Vui lòng nhập Năm tốt nghiệp.');
            return;
        }
        
        this.disabled = true;
        this.innerHTML = '<span class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span> Đang xử lý...';
        
        try {
            const response = await fetch('/thidua/admin/hoc-sinh?action=api_graduate', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    ids: idsToGraduate,
                    nam_tot_nghiep: namTotNghiep
                })
            });
            const data = await response.json();
            showToast(data.success ? 'success' : 'error', data.message);
            if (data.success) {
                setTimeout(() => window.location.reload(), 1000);
            }
        } catch (error) {
            showToast('error', 'Lỗi kết nối mạng: ' + error.message);
        } finally {
            this.disabled = false;
            this.innerHTML = 'Xác nhận tốt nghiệp';
        }
    });

    selectAllCheckbox.addEventListener('change', function() {
        document.querySelectorAll('.delete-checkbox').forEach(cb => cb.checked = this.checked);
    });
    confirmDeleteBtn.addEventListener('click', async function() {
        const idsToArchive = Array.from(document.querySelectorAll('.delete-checkbox:checked')).map(cb => cb.value);
        
        if (idsToArchive.length === 0) {
            showToast('error', 'Vui lòng chọn ít nhất một học sinh.');
            return;
        }

        const ngayNghiHoc = document.getElementById('bulk-ngay-nghi-hoc').value;
        const lyDo = document.getElementById('bulk-ly-do-nghi').value.trim();

        if (!ngayNghiHoc || !lyDo) {
            showToast('error', 'Vui lòng nhập Ngày nghỉ học và Lý do nghỉ học ở khung bên trên.');
            if (!lyDo) document.getElementById('bulk-ly-do-nghi').focus();
            return;
        }

        this.disabled = true;
        const originalHtml = this.innerHTML;
        this.innerHTML = '<span class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span> Đang xử lý...';

        try {
            const response = await fetch('/thidua/admin/hoc-sinh?action=api_delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    ids: idsToArchive,
                    ngay_nghi_hoc: ngayNghiHoc,
                    ly_do_nghi_hoc: lyDo
                })
            });
            const data = await response.json();
            showToast(data.success ? 'success' : 'error', data.message);
            if (data.success) {
                setTimeout(() => window.location.reload(), 1000);
            }
        } catch (error) {
            showToast('error', 'Lỗi kết nối mạng: ' + error.message);
        } finally {
            this.disabled = false;
            this.innerHTML = originalHtml;
        }
    });
    document.getElementById('addStudentBtn').addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('studentModalLabel').textContent = 'Thêm Mới Học Sinh';
        const studentForm = document.getElementById('studentForm');
        studentForm.action = '/thidua/admin/hoc-sinh?action=add<?= isset($_GET['iframe']) ? '&iframe=1' : '' ?>';
        studentForm.reset();
        document.getElementById('student_id').value = '';
        openModal('studentModal');
    });
    const quickGrantBtn = document.getElementById('quick-grant-btn');
    const quickGrantForm = document.getElementById('quickGrantForm');
    const targetTypeSelect = document.getElementById('target-type-select');
    const targetValueContainer = document.getElementById('target-value-container');
    quickGrantBtn.addEventListener('click', (e) => { e.preventDefault(); openModal('quickGrantModal'); });
    const optionsHtml = {
        chuc_vu: `<?php echo '<label class="block text-sm font-medium text-slate-700 mb-1">Chọn chức vụ cụ thể:</label><select class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-[#224397]/50 focus:border-[#224397] outline-none transition text-sm" name="target_value"><option value="">-- Chọn chức vụ --</option>'; foreach($danh_sach_chuc_vu as $cv) { echo "<option value=\'".htmlspecialchars($cv)."\'>".htmlspecialchars($cv)."</option>"; } echo '</select>'; ?>`,
        lop: `<?php echo '<label class="block text-sm font-medium text-slate-700 mb-1">Chọn lớp cụ thể:</label><select class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-[#224397]/50 focus:border-[#224397] outline-none transition text-sm" name="target_value"><option value="">-- Chọn lớp --</option>'; foreach($danh_sach_lop as $l) { echo "<option value=\'".$l['id']."\'>".htmlspecialchars($l['ten_lop'])."</option>"; } echo '</select>'; ?>`,
        hoc_sinh: `
            <div class="mb-3">
                <label class="block text-sm font-medium text-slate-700 mb-1">Lọc theo lớp:</label>
                <select class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-[#224397]/50 focus:border-[#224397] outline-none transition text-sm" id="grant-student-class-filter">
                    <option value="all">Tất cả các lớp</option>
                    <?php foreach($danh_sach_lop as $l) { echo "<option value=\'".$l['id']."\'>".htmlspecialchars($l['ten_lop'])."</option>"; } ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Chọn học sinh (tích vào ô vuông):</label>
                <div class="max-h-48 overflow-y-auto list-scrollbar bg-white border border-slate-200 rounded p-2" id="grant-student-list">
                    <?php foreach($danh_sach_hoc_sinh as $index => $hs) { 
                        $hs_id = "student-check-" . $index;
                        echo "<div class=\'flex items-center mb-1.5 student-checkbox-item\' data-lop-id=\'".$hs['lop_hoc_id']."\'>";
                        echo "<input class=\'rounded border-slate-300 text-[#224397] shadow-sm focus:border-[#224397] focus:ring focus:ring-[#224397]/50\' type=\'checkbox\' name=\'target_value[]\' value=\'".$hs['id']."\' id=\'".$hs_id."\'>";
                        echo "<label class=\'ml-2 text-sm text-slate-700 cursor-pointer flex-1\' for=\'".$hs_id."\'>".htmlspecialchars($hs['ten_lop'] . ' - ' . $hs['ho_dem'] . ' ' . $hs['ten'])."</label>";
                        echo "</div>";
                    } ?>
                </div>
            </div>`
    };
    function renderTargetValueInput() {
        targetValueContainer.innerHTML = optionsHtml[targetTypeSelect.value] || '';
        if (targetTypeSelect.value === 'hoc_sinh') {
            const classFilter = document.getElementById('grant-student-class-filter');
            classFilter.addEventListener('change', function() {
                const selectedLopId = this.value;
                document.querySelectorAll('.student-checkbox-item').forEach(item => {
                    item.style.display = (selectedLopId === 'all' || item.dataset.lopId === selectedLopId) ? 'd-block' : 'none';
                });
            });
        }
    }
    targetTypeSelect.addEventListener('change', renderTargetValueInput);
    renderTargetValueInput();
    quickGrantForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const data = {
            target_type: formData.get('target_type'),
            target_value: formData.get('target_type') === 'hoc_sinh' ? formData.getAll('target_value[]') : formData.get('target_value'),
            permissions: {
                nhap_vi_pham: formData.has('permissions[nhap_vi_pham]'),
                dang_ky_truc: formData.has('permissions[dang_ky_truc]'),
                   so_nhat_ky_online: formData.has('permissions[so_nhat_ky_online]'),
                
            }
        };
        if (data.target_type !== 'all' && (!data.target_value || (Array.isArray(data.target_value) && data.target_value.length === 0))) {
            showToast('warning', 'Vui lòng chọn đối tượng áp dụng.');
            return;
        }
        fetch('/thidua/admin/ctv?action=api_bulk_grant_permissions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            }).then(res => res.json())
            .then(result => {
                showToast(result.success ? 'success' : 'error', result.message);
                if (result.success) window.location.reload();
            });
    });
    // --- LOGIC CHO MODAL KẾT CHUYỂN NĂM HỌC ---

    const quickRevokeBtn = document.getElementById('quick-revoke-btn');
    const quickRevokeForm = document.getElementById('quickRevokeForm');
    const revokeTargetTypeSelect = quickRevokeForm.querySelector('select[name="target_type"]');
    const revokeTargetValueContainer = document.getElementById('revoke-target-value-container');
    quickRevokeBtn.addEventListener('click', (e) => { e.preventDefault(); openModal('quickRevokeModal'); });
    const revokeOptionsHtml = {
        chuc_vu: `<?php echo '<label class="block text-sm font-medium text-slate-700 mb-1">Chọn chức vụ:</label><select class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-[#224397]/50 focus:border-[#224397] outline-none transition text-sm" name="target_value"><option value="">-- Chọn chức vụ --</option>'; foreach($danh_sach_chuc_vu as $cv) { echo "<option value=\'".htmlspecialchars($cv)."\'>".htmlspecialchars($cv)."</option>"; } echo '</select>'; ?>`,
        lop: `<?php echo '<label class="block text-sm font-medium text-slate-700 mb-1">Chọn lớp:</label><select class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-[#224397]/50 focus:border-[#224397] outline-none transition text-sm" name="target_value"><option value="">-- Chọn lớp --</option>'; foreach($danh_sach_lop as $l) { echo "<option value=\'".$l['id']."\'>".htmlspecialchars($l['ten_lop'])."</option>"; } echo '</select>'; ?>`
    };
    revokeTargetTypeSelect.addEventListener('change', function() {
        if (this.value === 'lop' || this.value === 'chuc_vu') {
            revokeTargetValueContainer.innerHTML = revokeOptionsHtml[this.value];
            revokeTargetValueContainer.style.display = 'block';
        } else {
            revokeTargetValueContainer.style.display = 'none';
        }
    });
    let pendingRevokeData = null;
    quickRevokeForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const targetType = formData.get('target_type');
        let targetValue = targetType === 'hoc_sinh' ? formData.getAll('target_value[]') : formData.get('target_value');
        
        if (targetType !== 'all' && (!targetValue || (Array.isArray(targetValue) && targetValue.length === 0))) {
            showToast('warning', 'Vui lòng chọn đối tượng bị thu hồi.');
            return;
        }

        pendingRevokeData = {
            target_type: targetType,
            target_value: targetValue,
            revoke_action: formData.get('revoke_action')
        };
        closeModal('quickRevokeModal');
        openModal('confirmRevokeModal');
    });

    document.getElementById('confirm-revoke-btn').addEventListener('click', function() {
        if (!pendingRevokeData) return;
        closeModal('confirmRevokeModal');
        fetch('/thidua/admin/ctv?action=api_bulk_revoke_permissions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(pendingRevokeData)
        }).then(res => res.json())
        .then(result => {
            showToast(result.success ? 'success' : 'error', result.message);
            if (result.success) window.location.reload();
        });
    });
    const exportExcelBtn = document.getElementById('exportExcelBtn');
    const exportOptionsForm = document.getElementById('exportOptionsForm');
    exportExcelBtn.addEventListener('click', function(e) {
        e.preventDefault();
        openModal('exportOptionsModal');
    });
    exportOptionsForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const filterParams = new URLSearchParams(new FormData(document.getElementById('filterForm')));
        const selectedColumns = new FormData(this);
        const columnsQuery = new URLSearchParams(selectedColumns).toString();
        if (!columnsQuery) {
            showToast('warning', 'Bạn phải chọn ít nhất một cột để xuất!');
            return;
        }
        window.location.href = `/thidua/admin/hoc-sinh?action=export_excel&${filterParams.toString()}&${columnsQuery}`;
        closeModal('exportOptionsModal');
    });
    const exportCtvBtn = document.getElementById('export-ctv-btn');
    const exportCtvOptionsForm = document.getElementById('exportCtvOptionsForm');
    exportCtvBtn.addEventListener('click', (e) => {
        e.preventDefault();
        openModal('exportCtvOptionsModal');
    });
    const exportZipBtn = document.getElementById('exportZipBtn');
    exportZipBtn.addEventListener('click', function(e) {
        e.preventDefault();
        const lop_id = document.getElementById('lop_id').value;
        showToast('info', 'Đang tạo file ZIP, vui lòng đợi...');
        window.location.href = `/thidua/src/controllers/xuat_ho_so_hoc_sinh_zip.php?lop_id=${lop_id}`;
    });
    exportCtvOptionsForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const pageFilters = new URLSearchParams(new FormData(document.getElementById('filterForm'))).toString();
        const modalOptions = new URLSearchParams(new FormData(this)).toString();
        if (!modalOptions.includes('columns')) {
            showToast('warning', 'Bạn phải chọn ít nhất một cột để xuất!');
            return;
        }
        window.location.href = `/thidua/admin/ctv?action=export_accounts&${pageFilters}&${modalOptions}`;
        closeModal('exportCtvOptionsModal');
    });

    // Toggle permission cá nhân
    document.addEventListener('click', function(e) {
        if(e.target.closest('.btn-permission')) {
            const btn = e.target.closest('.btn-permission');
            e.preventDefault();
            const studentId = btn.dataset.studentId;
            const permission = btn.dataset.permission;
            const isCurrentlyGranted = btn.classList.contains('bg-green-600');
            const newStatus = !isCurrentlyGranted;
            
            fetch('/thidua/admin/ctv?action=api_toggle_permission', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    student_id: studentId,
                    permission: permission,
                    status: newStatus
                })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    if(newStatus) {
                        btn.classList.remove('bg-white', 'text-slate-500', 'border-slate-300', 'hover:bg-slate-50');
                        btn.classList.add('bg-green-600', 'text-white', 'border-green-600', 'hover:bg-green-700');
                    } else {
                        btn.classList.remove('bg-green-600', 'text-white', 'border-green-600', 'hover:bg-green-700');
                        btn.classList.add('bg-white', 'text-slate-500', 'border-slate-300', 'hover:bg-slate-50');
                    }
                } else {
                    showToast(data.success ? 'success' : 'error', data.message);
                }
            })
            .catch(err => {
                console.error(err);
                showToast('error', 'Có lỗi xảy ra khi cập nhật quyền.');
            });
        }
    });
});
</script>


<?php
// --- Pagination footer ---
if (isset($pagination) && ($pagination['total_pages'] ?? 1) > 1):
        $qs = $_GET; unset($qs['page']); $base = '/thidua/admin/hoc-sinh?'.http_build_query($qs);
        $cur = $pagination['page']; $totalPages = $pagination['total_pages'];
?>
<nav class="mt-6 mb-8 flex flex-col items-center gap-3" aria-label="Phân trang học sinh">
    <ul class="flex flex-wrap items-center justify-center gap-1">
        <li>
            <a class="px-3 py-1.5 text-sm font-medium rounded-md border <?php echo $cur<=1 ? 'border-slate-200 text-slate-400 bg-slate-50 cursor-not-allowed pointer-events-none' : 'border-[#224397]/25 text-[#224397] bg-white hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all'; ?>" href="<?php echo $cur<=1 ? '#' : $base.'&page='.max(1,$cur-1); ?>">«</a>
        </li>
        <?php
            $start = max(1, $cur-2); $end = min($totalPages, $cur+2);
            for ($p=$start; $p<=$end; $p++):
        ?>
            <li>
                <a class="px-3 py-1.5 text-sm font-medium rounded-md border <?php echo $p==$cur ? 'border-[#224397] bg-[#224397] text-white' : 'border-[#224397]/25 text-[#224397] bg-white hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all'; ?>" href="<?php echo $base.'&page='.$p; ?>"><?php echo $p; ?></a>
            </li>
        <?php endfor; ?>
        <li>
            <a class="px-3 py-1.5 text-sm font-medium rounded-md border <?php echo $cur>=$totalPages ? 'border-slate-200 text-slate-400 bg-slate-50 cursor-not-allowed pointer-events-none' : 'border-[#224397]/25 text-[#224397] bg-white hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all'; ?>" href="<?php echo $cur>=$totalPages ? '#' : $base.'&page='.min($totalPages,$cur+1); ?>">»</a>
        </li>
    </ul>
    <p class="text-center text-[#224397] text-sm font-medium">
        Tổng: <?php echo number_format($pagination['total']); ?> học sinh · Trang <?php echo $cur; ?>/<?php echo $totalPages; ?>
    </p>
</nav>
<?php endif; ?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const filterForm = document.getElementById("filterForm");
    if (filterForm) {
        // Prevent default submit
        filterForm.addEventListener("submit", function(e) {
            e.preventDefault();
            performAjaxFilter();
        });

        // Trigger ajax on change
        filterForm.querySelectorAll("select, input[type='checkbox']").forEach(el => {
            el.addEventListener("change", () => {
                document.getElementById('page').value = '1';
                performAjaxFilter();
            });
        });
        
        // Auto submit on typing in keyword with debounce
        let typingTimer;
        const keywordInput = document.getElementById('keyword');
        if (keywordInput) {
            keywordInput.addEventListener('input', function() {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(() => {
                    document.getElementById('page').value = '1';
                    performAjaxFilter();
                }, 400); // Giam do tre de phan hoi nhanh hon
            });
        }
    }

    function performAjaxFilter() {
        if (!filterForm) return;
        const formData = new FormData(filterForm);
        const searchParams = new URLSearchParams(formData);
        
        const tbody = document.querySelector('#studentTable tbody');

        fetch('/thidua/admin/hoc-sinh?' + searchParams.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Thay the table body
            const newTbody = doc.querySelector('#studentTable tbody');
            if (newTbody && tbody) {
                tbody.innerHTML = newTbody.innerHTML;
            }

            // Thay the phan trang
            const oldNav = document.querySelector('nav.mt-6.mb-8');
            const newNav = doc.querySelector('nav.mt-6.mb-8');
            
            if (oldNav && newNav) {
                oldNav.outerHTML = newNav.outerHTML;
            } else if (!oldNav && newNav) {
                const tableContainer = document.querySelector('.overflow-x-auto').parentElement;
                tableContainer.insertAdjacentHTML('beforeend', newNav.outerHTML);
            } else if (oldNav && !newNav) {
                oldNav.remove();
            }

            attachPaginationListeners();
        })
        .catch(error => {
            console.error('Loi khi loc du lieu:', error);
        });
        
        // Update URL
        window.history.replaceState({}, '', '?' + searchParams.toString());
    }

    function attachPaginationListeners() {
        const nav = document.querySelector('nav.mt-6.mb-8');
        if (nav) {
            nav.querySelectorAll('a').forEach(a => {
                a.addEventListener('click', function(e) {
                    if (this.getAttribute('href') !== '#') {
                        e.preventDefault();
                        const url = new URL(this.href, window.location.origin);
                        const page = url.searchParams.get('page');
                        if (page) {
                            document.getElementById('page').value = page;
                            performAjaxFilter();
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        }
                    }
                });
            });
        }
    }
    
    attachPaginationListeners();

    // === Logic xử lý địa chỉ 3 cấp ===
    const diaChiRaw = <?php echo json_encode($settings['dia_chi_options'] ?? ''); ?>;
    const diaChiMap = {};
    if (diaChiRaw) {
        diaChiRaw.split('\n').forEach(line => {
            const parts = line.split(':');
            if (parts.length >= 2) {
                const xa = parts[0].trim();
                const aps = parts[1].split(',').map(a => a.trim()).filter(a => a);
                if (xa) diaChiMap[xa] = aps;
            }
        });
    }

    const xaPhuongSelect = document.getElementById('xa_phuong');
    const apKhuPhoSelect = document.getElementById('ap_khupho');

    if (xaPhuongSelect && apKhuPhoSelect) {
        Object.keys(diaChiMap).forEach(xa => {
            const opt = document.createElement('option');
            opt.value = xa;
            opt.textContent = xa;
            xaPhuongSelect.appendChild(opt);
        });

        xaPhuongSelect.addEventListener('change', function() {
            const selectedXa = this.value;
            apKhuPhoSelect.innerHTML = '<option value="">-- Chọn --</option>';
            if (selectedXa && diaChiMap[selectedXa]) {
                diaChiMap[selectedXa].forEach(ap => {
                    const opt = document.createElement('option');
                    opt.value = ap;
                    opt.textContent = ap;
                    apKhuPhoSelect.appendChild(opt);
                });
            }
        });
    }
});
</script>
