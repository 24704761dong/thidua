<?php
$page_title = 'Quản Lý Giáo Viên';
require_once __DIR__ . '/partials/admin_header.php';
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
    
    body { background-color: var(--bg-light); }

    /* ----- Thiết kế bảng chính ----- */
    #teacherTable {
        border: 1px solid rgba(34, 67, 151, 0.25);
        border-collapse: collapse;
        width: 100%;
    }
    #teacherTable thead th {
        background-color: rgba(34, 67, 151, 0.08);
        color: #224397;
        font-weight: 800;
        text-transform: uppercase;
        font-size: 0.88rem;
        text-align: center;
        padding: 0.75rem 1rem;
        border: 1px solid rgba(34, 67, 151, 0.25);
    }
    #teacherTable td {
        padding: 0.75rem 1rem;
        border: 1px solid rgba(34, 67, 151, 0.25);
        vertical-align: middle;
        font-size: 0.85rem;
        font-weight: 600;
        color: #1e293b;
    }
    #teacherTable tbody tr:hover { background-color: rgba(34, 67, 151, 0.05) !important; }

    /* Custom Checkbox */
    .cb-teacher { cursor: pointer; width: 16px; height: 16px; }

    body::-webkit-scrollbar, html::-webkit-scrollbar { display: block !important; width: 8px; height: 8px; }
    body::-webkit-scrollbar-thumb, html::-webkit-scrollbar-thumb { background: rgba(34, 67, 151, 0.3); border-radius: 4px; }
    body::-webkit-scrollbar-track, html::-webkit-scrollbar-track { background: transparent; }

    /* Form inputs đậm hơn */
    .form-input-thick { border: 2px solid #cbd5e1; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); }
    .form-input-thick:focus { border-color: #3b82f6; ring: 2px; }
</style>

<div class="w-full px-2 lg:px-6">
    <div class="flex flex-row items-end justify-between gap-2 mb-4">
        <form id="filterForm" class="flex flex-row items-end gap-2 m-0">
            <div>
                <label for="keyword" class="block text-[13.5px] font-bold text-[#224397] mb-0.5">Tên / CCCD / SĐT</label>
                <input type="text" id="keyword" name="keyword" class="block w-64 rounded border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-2 py-1 text-[13px]" placeholder="Nhập từ khóa...">
            </div>
            <div class="ml-1">
                <label for="page_size" class="block text-[13.5px] font-bold text-[#224397] mb-0.5">Hiển thị</label>
                <select id="page_size" name="page_size" class="block w-20 rounded border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-2 py-1 text-[13px]">
                    <option value="50">50</option>
                    <option value="100" selected>100</option>
                    <option value="200">200</option>
                </select>
            </div>
        </form>

        <div class="flex items-center gap-1.5" id="default-toolbar">
            <button type="button" class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#224397] hover:text-white transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm whitespace-nowrap" onclick="openTeacherModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-plus-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3z"/></svg> THÊM GIÁO VIÊN
            </button>
            <button type="button" class="px-2 py-1 bg-white border border-emerald-500/30 rounded text-emerald-600 hover:bg-emerald-500 hover:text-white transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm whitespace-nowrap" onclick="openPhanLopModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-diagram-3-fill" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M6 3.5A1.5 1.5 0 0 1 7.5 2h1A1.5 1.5 0 0 1 10 3.5v1A1.5 1.5 0 0 1 8.5 6v1H14a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-1 0V8h-5v.5a.5.5 0 0 1-1 0V8h-5v.5a.5.5 0 0 1-1 0v-1A.5.5 0 0 1 2 7h5.5V6A1.5 1.5 0 0 1 6 4.5zm-6 8A1.5 1.5 0 0 1 1.5 10h1A1.5 1.5 0 0 1 4 11.5v1A1.5 1.5 0 0 1 2.5 14h-1A1.5 1.5 0 0 1 0 12.5zm6 0A1.5 1.5 0 0 1 7.5 10h1a1.5 1.5 0 0 1 1.5 1.5v1A1.5 1.5 0 0 1 8.5 14h-1A1.5 1.5 0 0 1 6 12.5zm6 0a1.5 1.5 0 0 1 1.5-1.5h1a1.5 1.5 0 0 1 1.5 1.5v1a1.5 1.5 0 0 1-1.5 1.5h-1a1.5 1.5 0 0 1-1.5-1.5z"/></svg> PHÂN LỚP
            </button>
            <button type="button" id="btnBulkAccount" class="px-2 py-1 bg-white border border-amber-500/30 rounded text-amber-600 hover:bg-amber-500 hover:text-white transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm whitespace-nowrap opacity-50 cursor-not-allowed" disabled onclick="bulkAccount()">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-person-fill-lock" viewBox="0 0 16 16"><path d="M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0m-9 8c0 1 1 1 1 1h5v-1a2 2 0 0 1 .01-.2 4.49 4.49 0 0 1 1.534-3.693Q8.844 9.002 8 9c-5 0-6 3-6 4m7 0a1 1 0 0 1 1-1v-1a2 2 0 1 1 4 0v1a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1zm3-3a1 1 0 0 0-1 1v1h2v-1a1 1 0 0 0-1-1"/></svg> CẤP TÀI KHOẢN
            </button>

            <!-- Menu Tác Vụ -->
            <div class="relative inline-block text-left group z-[100]">
                <button type="button" id="btnTaskMenu" class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm whitespace-nowrap opacity-50 cursor-not-allowed" disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-gear-fill" viewBox="0 0 16 16"><path d="M9.405 1.05c-.413-1.4-2.397-1.4-2.81 0l-.1.34a1.464 1.464 0 0 1-2.105.872l-.31-.17c-1.283-.698-2.686.705-1.987 1.987l.169.311c.446.82.023 1.841-.872 2.105l-.34.1c-1.4.413-1.4 2.397 0 2.81l.34.1a1.464 1.464 0 0 1 .872 2.105l-.17.31c-.698 1.283.705 2.686 1.987 1.987l.311-.169a1.464 1.464 0 0 1 2.105.872l.1.34c.413 1.4 2.397 1.4 2.81 0l.1-.34a1.464 1.464 0 0 1 2.105-.872l.31.17c1.283.698 2.686-.705 1.987-1.987l-.169-.311a1.464 1.464 0 0 1 .872-2.105l.34-.1c1.4-.413 1.4-2.397 0-2.81l-.34-.1a1.464 1.464 0 0 1-.872-2.105l.17-.31c.698-1.283-.705-2.686-1.987-1.987l-.311.169a1.464 1.464 0 0 1-2.105-.872zM8 10.93a2.929 2.929 0 1 1 0-5.86 2.929 2.929 0 0 1 0 5.858z"/></svg> MENU TÁC VỤ <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-chevron-down text-[9px]" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708"/></svg>
                </button>
                <ul class="absolute right-0 mt-1 w-48 bg-white rounded shadow-lg border border-slate-100 focus:outline-none opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-right scale-95 group-hover:scale-100 py-1 text-left">
                    <li><a class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] text-slate-700 hover:bg-blue-50 hover:text-[#224397] cursor-pointer" id="btnBulkEdit"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16"><path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>   <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/></svg>Sửa thông tin (1 dòng)</a></li>
                    <li><a class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] text-red-600 hover:bg-red-50 cursor-pointer" id="btnBulkDelete"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>   <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/></svg>Xóa (nhiều dòng)</a></li>
                    <li><a class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] text-amber-600 hover:bg-amber-50 cursor-pointer" id="btnBulkResetPass"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-key" viewBox="0 0 16 16"><path d="M0 8a4 4 0 0 1 7.465-2H14a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0L13 9.207l-.646.647a.5.5 0 0 1-.708 0L11 9.207l-.646.647a.5.5 0 0 1-.708 0L9 9.207l-.646.647A.5.5 0 0 1 8 10h-.535A4 4 0 0 1 0 8m4-3a3 3 0 1 0 2.712 4.285A.5.5 0 0 1 7.163 9h.63l.853-.854a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.793-.793-1-1h-6.63a.5.5 0 0 1-.451-.285A3 3 0 0 0 4 5"/>   <path d="M4 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/></svg>Khôi phục mật khẩu</a></li>
                </ul>
            </div>

            <!-- Nhập Xuất -->
            <div class="relative inline-block text-left group z-[90]">
                <button type="button" class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm whitespace-nowrap" id="importMenuBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-arrow-down-up" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M11.5 15a.5.5 0 0 0 .5-.5V2.707l3.146 3.147a.5.5 0 0 0 .708-.708l-4-4a.5.5 0 0 0-.708 0l-4 4a.5.5 0 1 0 .708.708L11 2.707V14.5a.5.5 0 0 0 .5.5m-7-14a.5.5 0 0 1 .5.5v11.793l3.146-3.147a.5.5 0 0 1 .708.708l-4 4a.5.5 0 0 1-.708 0l-4-4a.5.5 0 0 1 .708-.708L4 13.293V1.5a.5.5 0 0 1 .5-.5"/></svg> NHẬP/XUẤT <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-chevron-down text-[9px]" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708"/></svg>
                </button>
                <ul class="absolute right-0 mt-1 w-40 bg-white rounded shadow-lg border border-slate-100 focus:outline-none opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-right scale-95 group-hover:scale-100 py-1 text-left">
                    <li><a class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] text-slate-700 hover:bg-blue-50 hover:text-[#224397] cursor-pointer" onclick="exportExcel()"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-excel" viewBox="0 0 16 16"><path d="M5.884 6.68a.5.5 0 1 0-.768.64L7.349 10l-2.233 2.68a.5.5 0 0 0 .768.64L8 10.781l2.116 2.54a.5.5 0 0 0 .768-.641L8.651 10l2.233-2.68a.5.5 0 0 0-.768-.64L8 9.219l-2.116-2.54z"/>   <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/></svg>Xuất DS Excel</a></li>
                    <li><a class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] text-slate-700 hover:bg-blue-50 hover:text-[#224397] cursor-pointer" id="btnTriggerImport"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-upload" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>   <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708z"/></svg>Import Excel</a></li>
                    <li><a class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] text-slate-700 hover:bg-blue-50 hover:text-[#224397]" href="/thidua/admin/giao-vien?action=download_import_template"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>   <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/></svg>Tải Mẫu Import</a></li>
                </ul>
                <input type="file" id="importFile" accept=".xlsx, .xls" class="hidden">
            </div>
        </div>
    </div>

    <div class="bg-white rounded shadow border border-[#224397]/25 mb-6 p-0 overflow-hidden">
        <div class="px-4 py-3 border-b border-[#224397]/20 bg-[#224397]/5 flex items-center justify-between">
            <h3 class="mb-0 text-[15px] font-bold text-[#224397] uppercase flex items-center"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-person-badge-fill mr-2" viewBox="0 0 16 16"><path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2zm4.5 0a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1zM8 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6m5 2.755C12.146 12.825 10.623 12 8 12s-4.146.826-5 1.755V14a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1z"/></svg>DANH SÁCH GIÁO VIÊN</h3>
        </div>
        <div class="px-4 pb-4 pt-3">
        <div class="w-full">
            <div class="overflow-x-auto w-full">
                <table id="teacherTable">
                    <thead>
                        <tr>
                            <th class="w-8 text-center"><input type="checkbox" id="cbCheckAll" class="cb-teacher"></th>
                            <th class="w-10 text-center">STT</th>
                            <th class="w-12 text-center">Ảnh</th>
                            <th class="text-center w-32">Số CCCD</th>
                            <th class="text-left">Họ và Tên</th>
                            <th class="text-center w-28">Ngày sinh</th>
                            <th class="text-center w-28">SĐT</th>
                            <th class="text-left w-48">Email</th>
                            <th class="text-left">Các lớp đã chủ nhiệm</th>
                            <th class="text-center w-16 text-[10px]">Tài khoản</th>
                            <th class="text-left min-w-[120px]">Ghi chú</th>
                            <th class="text-center w-12"></th>
                        </tr>
                    </thead>
                    <tbody id="teacherTableBody">
                        <!-- Ajax Content -->
                    </tbody>
                </table>
            </div>
            <div class="mt-4 flex items-center justify-between">
                <div class="text-sm text-slate-500" id="paginationInfo"></div>
                <div id="paginationControls" class="flex gap-1"></div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Modal Thêm / Sửa -->
<div class="fixed inset-0 z-[10005] hidden items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0" id="teacherModal" onclick="closeTeacherModal()">
    <div class="bg-white rounded-xl shadow-2xl w-[600px] max-w-[90%] flex flex-col overflow-hidden border border-slate-300 transform transition-all duration-300 scale-95 translate-y-4 opacity-0 modal-content-box" id="teacherModalContent" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h5 class="text-lg font-bold text-[#224397] flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-person-badge-fill text-[#FAB723]" viewBox="0 0 16 16">
                  <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2zm4.5 0a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1zM8 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6m5 2.755C12.146 12.825 10.623 12 8 12s-4.146.826-5 1.755V14a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1z"/>
                </svg>
                <span id="teacherModalLabel">Thêm Giáo Viên</span>
            </h5>
            <button type="button" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition" onclick="closeTeacherModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg>
            </button>
        </div>
            <form id="teacherForm" class="flex-1 overflow-y-auto" enctype="multipart/form-data">
                <div class="p-6 space-y-4">
                    <input type="hidden" name="id" id="gv_id">
                    
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-16 h-16 rounded-full overflow-hidden border-2 border-slate-200 shrink-0">
                            <img id="gv_avatar_preview" src="/thidua/public/assets/img/anhthegoc.JPG" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Ảnh đại diện (Avatar)</label>
                            <input type="file" name="avatar" id="gv_avatar" accept="image/*" class="block w-full text-sm text-slate-500 file:mr-4 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>
                    </div>

                    <div class="flex flex-wrap -mx-3">
                        <div class="w-full md:w-1/2 px-3 mb-4">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Số CCCD</label>
                            <input type="text" name="cccd" id="gv_cccd" class="block w-full rounded form-input-thick px-3 py-2 text-sm">
                        </div>
                        <div class="w-full md:w-1/2 px-3 mb-4">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Số Điện Thoại</label>
                            <input type="text" name="sdt" id="gv_sdt" class="block w-full rounded form-input-thick px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Họ Tên <span class="text-red-500">*</span></label>
                        <input type="text" name="ho_ten" id="gv_ten" required class="block w-full rounded form-input-thick px-3 py-2 text-sm">
                    </div>
                    <div class="flex flex-wrap -mx-3">
                        <div class="w-full md:w-1/2 px-3 mb-4">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Ngày Sinh (dd/mm/yyyy)</label>
                            <input type="text" name="ngay_sinh" id="gv_ngaysinh" class="block w-full rounded form-input-thick px-3 py-2 text-sm" placeholder="18/01/2006">
                        </div>
                        <div class="w-full md:w-1/2 px-3 mb-4">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                            <input type="email" name="email" id="gv_email" class="block w-full rounded form-input-thick px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Ghi Chú</label>
                        <textarea name="ghi_chu" id="gv_ghichu" class="block w-full rounded form-input-thick px-3 py-2 text-sm" rows="2"></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-2">
                    <button type="button" class="px-4 py-2 text-[13px] font-medium text-gray-600 bg-white border border-gray-300 rounded shadow-sm hover:bg-gray-50 transition" onclick="closeTeacherModal()">Hủy</button>
                    <button type="submit" class="px-4 py-2 text-[13px] font-bold text-slate-900 bg-[#FAB723] border border-[#FAB723] rounded shadow-sm hover:bg-[#e5a61d] transition">Xác Nhận</button>
                </div>
            </form>
    </div>
</div>

<!-- Modal Phân Lớp -->
<div class="fixed inset-0 z-[10005] hidden opacity-0 transition-opacity duration-300" id="phanLopModal" tabindex="-1">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0 transform transition-all duration-300 scale-95 translate-y-4 opacity-0 bg-black/40 backdrop-blur-sm" id="phanLopModalContent" style="margin:0; padding:0; height:100vh; display:flex; align-items:center; justify-content:center;">
        <div class="relative bg-white rounded-2xl shadow-2xl border border-[#224397]/25 w-full max-w-2xl text-left overflow-hidden flex flex-col mx-auto max-h-[85vh]">
            <div class="flex items-center justify-between p-4 border-b rounded-t-xl bg-[#224397]/5">
                <h5 class="text-base font-bold text-[#224397]" id="phanLopModalLabel"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-diagram-3-fill mr-2" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M6 3.5A1.5 1.5 0 0 1 7.5 2h1A1.5 1.5 0 0 1 10 3.5v1A1.5 1.5 0 0 1 8.5 6v1H14a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-1 0V8h-5v.5a.5.5 0 0 1-1 0V8h-5v.5a.5.5 0 0 1-1 0v-1A.5.5 0 0 1 2 7h5.5V6A1.5 1.5 0 0 1 6 4.5zm-6 8A1.5 1.5 0 0 1 1.5 10h1A1.5 1.5 0 0 1 4 11.5v1A1.5 1.5 0 0 1 2.5 14h-1A1.5 1.5 0 0 1 0 12.5zm6 0A1.5 1.5 0 0 1 7.5 10h1a1.5 1.5 0 0 1 1.5 1.5v1A1.5 1.5 0 0 1 8.5 14h-1A1.5 1.5 0 0 1 6 12.5zm6 0a1.5 1.5 0 0 1 1.5-1.5h1a1.5 1.5 0 0 1 1.5 1.5v1a1.5 1.5 0 0 1-1.5 1.5h-1a1.5 1.5 0 0 1-1.5-1.5z"/></svg>Phân công Giáo viên Chủ nhiệm (Năm học hiện tại)</h5>
                <button type="button" class="text-slate-400 hover:text-[#224397] p-2" onclick="closePhanLopModal()"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg></button>
            </div>
            <div class="flex-1 overflow-y-auto p-4 bg-slate-50">
                <div id="phanLopLoading" class="text-center py-8"><div class="spinner-border text-[#224397]"></div></div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="phanLopContainer" style="display:none;">
                    <!-- Render classes here -->
                </div>
            </div>
            <div class="flex items-center justify-end p-4 border-t space-x-2 rounded-b-xl bg-white">
                <button type="button" class="px-4 py-2 bg-slate-600 rounded text-white text-sm font-medium inline-flex items-center justify-center" onclick="closePhanLopModal()">Đóng</button>
                <button type="button" class="px-4 py-2 bg-[#224397] rounded text-white text-sm font-medium inline-flex items-center justify-center" onclick="savePhanLop()"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-save mr-2" viewBox="0 0 16 16"><path d="M2 1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H9.5a1 1 0 0 0-1 1v7.293l2.646-2.647a.5.5 0 0 1 .708.708l-3.5 3.5a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L7.5 9.293V2a2 2 0 0 1 2-2H14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h2.5a.5.5 0 0 1 0 1z"/></svg>Lưu Phân Lớp</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Profile -->
<div class="fixed inset-0 z-[10005] hidden opacity-0 transition-opacity duration-300" id="profileModal" tabindex="-1">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0 transform transition-all duration-300 scale-95 translate-y-4 opacity-0 bg-black/40 backdrop-blur-sm" id="profileModalContent" style="margin:0; padding:0; height:100vh; display:flex; align-items:center; justify-content:center;">
        <div class="relative bg-white rounded-2xl shadow-2xl border border-[#224397]/20 w-full max-w-md text-left overflow-hidden flex flex-col mx-auto p-8">
            <div class="absolute top-4 right-4 z-10">
                <button type="button" class="text-slate-400 hover:text-red-500 bg-slate-100 hover:bg-red-50 rounded-full w-8 h-8 flex items-center justify-center transition-colors" onclick="closeProfileModal()"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg></button>
            </div>
            
            <div class="flex flex-col items-center mb-6 mt-2">
                <div class="relative mb-4">
                    <div class="w-28 h-28 rounded-full overflow-hidden border-4 border-slate-100 shadow-lg ring-4 ring-white">
                        <img id="prof_avatar" src="" class="w-full h-full object-cover">
                    </div>
                    <span class="absolute bottom-1 right-1 w-6 h-6 bg-emerald-500 border-2 border-white rounded-full shadow" title="Đang hoạt động"></span>
                </div>
                <h2 class="text-2xl font-black text-[#224397] tracking-tight" id="prof_name">Nguyễn Văn A</h2>
                <div class="flex items-center gap-2 mt-1.5">
                    <span class="px-2.5 py-0.5 bg-blue-50 text-blue-600 text-[11px] font-bold uppercase tracking-wider rounded-full border border-blue-100">Giáo Viên</span>
                    <span class="text-sm text-slate-500 font-medium" id="prof_cccd">CCCD: 0123456789</span>
                </div>
            </div>
            
            <div class="w-full h-px bg-gradient-to-r from-transparent via-slate-200 to-transparent mb-6"></div>
            
            <div class="space-y-4 text-sm w-full px-2">
                <div class="flex items-center gap-4 p-2.5 rounded-lg hover:bg-slate-50 transition-colors">
                    <div class="w-10 h-10 rounded-full bg-orange-50 flex items-center justify-center text-orange-500 shrink-0"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-calendar2-heart text-lg" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4 .5a.5.5 0 0 0-1 0V1H2a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2h-1V.5a.5.5 0 0 0-1 0V1H4zM1 3a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1zm2 .5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h10a.5.5 0 0 0 .5-.5V4a.5.5 0 0 0-.5-.5zm5 4.493c1.664-1.711 5.825 1.283 0 5.132-5.825-3.85-1.664-6.843 0-5.132"/></svg></div>
                    <div class="flex-1">
                        <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wider block mb-0.5">Ngày sinh</span>
                        <span class="font-bold text-slate-700" id="prof_dob"></span>
                    </div>
                </div>
                
                <div class="flex items-center gap-4 p-2.5 rounded-lg hover:bg-slate-50 transition-colors">
                    <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600 shrink-0"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-telephone text-lg" viewBox="0 0 16 16"><path d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.68.68 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.68.68 0 0 0-.122-.58zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"/></svg></div>
                    <div class="flex-1">
                        <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wider block mb-0.5">Điện thoại</span>
                        <span class="font-bold text-slate-700" id="prof_phone"></span>
                    </div>
                </div>
                
                <div class="flex items-center gap-4 p-2.5 rounded-lg hover:bg-slate-50 transition-colors">
                    <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-500 shrink-0"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-envelope-at text-lg" viewBox="0 0 16 16"><path d="M2 2a2 2 0 0 0-2 2v8.01A2 2 0 0 0 2 14h5.5a.5.5 0 0 0 0-1H2a1 1 0 0 1-.966-.741l5.64-3.471L8 9.583l7-4.2V8.5a.5.5 0 0 0 1 0V4a2 2 0 0 0-2-2zm3.708 6.208L1 11.105V5.383zM1 4.217V4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v.217l-7 4.2z"/>   <path d="M14.247 14.269c1.01 0 1.587-.857 1.587-2.025v-.21C15.834 10.43 14.64 9 12.52 9h-.035C10.42 9 9 10.36 9 12.432v.214C9 14.82 10.438 16 12.358 16h.044c.594 0 1.018-.074 1.237-.175v-.73c-.245.11-.673.18-1.18.18h-.044c-1.334 0-2.571-.788-2.571-2.655v-.157c0-1.657 1.058-2.724 2.64-2.724h.04c1.535 0 2.484 1.05 2.484 2.326v.118c0 .975-.324 1.39-.639 1.39-.232 0-.41-.148-.41-.42v-2.19h-.906v.569h-.03c-.084-.298-.368-.63-.954-.63-.778 0-1.259.555-1.259 1.4v.528c0 .892.49 1.434 1.26 1.434.471 0 .896-.227 1.014-.643h.043c.118.42.617.648 1.12.648m-2.453-1.588v-.227c0-.546.227-.791.573-.791.297 0 .572.192.572.708v.367c0 .573-.253.744-.564.744-.354 0-.581-.215-.581-.8Z"/></svg></div>
                    <div class="flex-1">
                        <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wider block mb-0.5">Email</span>
                        <span class="font-bold text-slate-700 truncate block max-w-[250px]" id="prof_email"></span>
                    </div>
                </div>
                
                <div class="flex items-center gap-4 p-2.5 rounded-lg hover:bg-slate-50 transition-colors">
                    <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-500 shrink-0"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-diagram-3 text-lg" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M6 3.5A1.5 1.5 0 0 1 7.5 2h1A1.5 1.5 0 0 1 10 3.5v1A1.5 1.5 0 0 1 8.5 6v1H14a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-1 0V8h-5v.5a.5.5 0 0 1-1 0V8h-5v.5a.5.5 0 0 1-1 0v-1A.5.5 0 0 1 2 7h5.5V6A1.5 1.5 0 0 1 6 4.5zM8.5 5a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5zM0 11.5A1.5 1.5 0 0 1 1.5 10h1A1.5 1.5 0 0 1 4 11.5v1A1.5 1.5 0 0 1 2.5 14h-1A1.5 1.5 0 0 1 0 12.5zm1.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm4.5.5A1.5 1.5 0 0 1 7.5 10h1a1.5 1.5 0 0 1 1.5 1.5v1A1.5 1.5 0 0 1 8.5 14h-1A1.5 1.5 0 0 1 6 12.5zm1.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm4.5.5a1.5 1.5 0 0 1 1.5-1.5h1a1.5 1.5 0 0 1 1.5 1.5v1a1.5 1.5 0 0 1-1.5 1.5h-1a1.5 1.5 0 0 1-1.5-1.5zm1.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5z"/></svg></div>
                    <div class="flex-1">
                        <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wider block mb-1">Các lớp đã chủ nhiệm</span>
                        <div class="font-medium text-slate-700 flex flex-wrap gap-1" id="prof_classes"></div>
                    </div>
                </div>
                
                <div class="flex items-start gap-4 p-2.5 rounded-lg bg-slate-50 border border-slate-100 mt-2">
                    <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-slate-500 shrink-0 shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-card-text text-lg" viewBox="0 0 16 16"><path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2z"/>   <path d="M3 5.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5M3 8a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9A.5.5 0 0 1 3 8m0 2.5a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5"/></svg></div>
                    <div class="flex-1 pt-1">
                        <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wider block mb-1">Ghi chú</span>
                        <span class="font-medium text-slate-600 italic text-xs leading-relaxed" id="prof_note"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Hàm hiển thị Toast Notifications mượt mà
if(typeof window.showToast !== 'function') {
    window.showToast = function(type, message) {
        const toastId = 'toast-' + Math.random().toString(36).substr(2, 9);
        let bgColor = 'bg-emerald-50';
        let textColor = 'text-emerald-800';
        let borderColor = 'border-emerald-200';
        let icon = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check-circle-fill text-lg text-emerald-500" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>';

        if (type === 'danger' || type === 'error') {
            bgColor = 'bg-red-50'; textColor = 'text-red-800'; borderColor = 'border-red-200';
            icon = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-exclamation-triangle-fill text-lg text-red-500" viewBox="0 0 16 16"><path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/></svg>';
        } else if (type === 'warning') {
            bgColor = 'bg-amber-50'; textColor = 'text-amber-800'; borderColor = 'border-amber-200';
            icon = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-exclamation-circle-fill text-lg text-amber-500" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4m.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2"/></svg>';
        }

        const toastHTML = `
        <div id="${toastId}" class="fixed top-6 right-6 z-[10010] flex items-center p-4 ${textColor} rounded-lg ${bgColor} border ${borderColor} shadow-lg transform transition-all duration-500 opacity-0 translate-x-full" style="width: max-content; max-width: 400px;" role="alert">
            <div class="mr-3">${icon}</div>
            <div class="text-sm font-medium pr-4">${message}</div>
            <button type="button" class="ml-3 -mx-1.5 -my-1.5 bg-transparent text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-white/50 inline-flex h-8 w-8 items-center justify-center transition-colors" aria-label="Close" onclick="closeToast('${toastId}')">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg text-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg>
            </button>
        </div>
        `;

        document.body.insertAdjacentHTML('beforeend', toastHTML);
        const toastEl = document.getElementById(toastId);
        
        requestAnimationFrame(() => {
            toastEl.classList.remove('opacity-0', 'translate-x-full');
            toastEl.classList.add('opacity-100', 'translate-x-0');
        });

        setTimeout(() => {
            if(typeof closeToast === 'function') closeToast(toastId);
        }, 5000);
    };

    window.closeToast = function(id) {
        const el = document.getElementById(id);
        if (el) {
            el.classList.replace("opacity-100", "opacity-0");
            el.classList.replace("translate-x-0", "translate-x-full");
            setTimeout(() => { if (el && el.parentNode) el.parentNode.removeChild(el); }, 500);
        }
    };
}
let currentData = [];

function openTeacherModal(gv = null) {
    const modal = document.getElementById('teacherModal');
    const content = document.getElementById('teacherModalContent');
    const form = document.getElementById('teacherForm');
    
    if (gv) {
        document.getElementById('teacherModalLabel').textContent = 'Sửa Thông Tin Giáo Viên';
        document.getElementById('gv_id').value = gv.id;
        document.getElementById('gv_cccd').value = gv.cccd || '';
        document.getElementById('gv_sdt').value = gv.sdt || '';
        document.getElementById('gv_ten').value = gv.ho_ten || '';
        document.getElementById('gv_ngaysinh').value = gv.ngay_sinh || '';
        document.getElementById('gv_email').value = gv.email || '';
        document.getElementById('gv_ghichu').value = gv.ghi_chu || '';
        document.getElementById('gv_avatar_preview').src = gv.final_avatar || '/thidua/public/assets/img/anhthegoc.JPG';
    } else {
        document.getElementById('teacherModalLabel').textContent = 'Thêm Giáo Viên Mới';
        form.reset();
        document.getElementById('gv_id').value = '';
        document.getElementById('gv_avatar_preview').src = '/thidua/public/assets/img/anhthegoc.JPG';
    }

    modal.classList.remove('hidden');
    modal.style.display = 'flex';
    void modal.offsetWidth; 
    modal.classList.remove('opacity-0');
    content.classList.remove('scale-95', 'translate-y-4', 'opacity-0');
}

function closeTeacherModal() {
    const modal = document.getElementById('teacherModal');
    const content = document.getElementById('teacherModalContent');
    modal.classList.add('opacity-0');
    content.classList.add('scale-95', 'translate-y-4', 'opacity-0');
    setTimeout(() => {
        modal.style.display = 'none';
        modal.classList.add('hidden');
    }, 300);
}

function openProfileModal(idx) {
    const gv = currentData[idx];
    if(!gv) return;
    document.getElementById('prof_name').textContent = gv.ho_ten;
    document.getElementById('prof_cccd').textContent = 'CCCD: ' + (gv.cccd || '---');
    document.getElementById('prof_dob').textContent = gv.ngay_sinh || '---';
    document.getElementById('prof_phone').textContent = gv.sdt || '---';
    document.getElementById('prof_email').textContent = gv.email || '---';
    document.getElementById('prof_note').textContent = gv.ghi_chu || '---';
    
    let classesHtml = '';
    if (gv.cac_lop_chu_nhiem) {
        const clsArr = gv.cac_lop_chu_nhiem.split(', ');
        classesHtml = clsArr.map(c => `<span class="inline-block px-2 py-0.5 bg-[#224397]/10 text-[#224397] rounded text-xs mr-1 mb-1 font-bold">${c}</span>`).join('');
    } else {
        classesHtml = '<span class="text-slate-400 italic">Chưa phân công</span>';
    }
    document.getElementById('prof_classes').innerHTML = classesHtml;
    document.getElementById('prof_avatar').src = gv.final_avatar || '/thidua/public/assets/img/anhthegoc.JPG';

    const m = document.getElementById('profileModal');
    const c = document.getElementById('profileModalContent');
    m.classList.remove('hidden'); void m.offsetWidth;
    m.classList.remove('opacity-0'); c.classList.remove('scale-95', 'translate-y-4', 'opacity-0');
}
function closeProfileModal() {
    const m = document.getElementById('profileModal');
    const c = document.getElementById('profileModalContent');
    m.classList.add('opacity-0'); c.classList.add('scale-95', 'translate-y-4', 'opacity-0');
    setTimeout(() => m.classList.add('hidden'), 300);
}

function openPhanLopModal() {
    const m = document.getElementById('phanLopModal');
    const c = document.getElementById('phanLopModalContent');
    const container = document.getElementById('phanLopContainer');
    const loading = document.getElementById('phanLopLoading');
    
    m.classList.remove('hidden'); void m.offsetWidth;
    m.classList.remove('opacity-0'); c.classList.remove('scale-95', 'translate-y-4', 'opacity-0');
    
    container.style.display = 'none';
    loading.style.display = 'block';

    fetch('/thidua/admin/giao-vien?action=api_phan_lop_data')
    .then(r=>r.json()).then(d => {
        if(d.success) {
            let html = '';
            d.classes.forEach(cls => {
                let options = `<option value="">-- Bỏ trống --</option>`;
                d.teachers.forEach(t => {
                    const sel = (cls.giao_vien_id == t.id) ? 'selected' : '';
                    options += `<option value="${t.id}" ${sel}>${t.ho_ten}</option>`;
                });
                html += `
                <div class="bg-white p-3 rounded shadow-sm border border-slate-200">
                    <label class="block text-[13px] font-bold text-[#224397] mb-1">Lớp ${cls.ten_lop}</label>
                    <select class="pl-select block w-full rounded border-slate-300 text-sm focus:border-blue-500 py-1" data-lop="${cls.id}">
                        ${options}
                    </select>
                </div>`;
            });
            container.innerHTML = html;
            loading.style.display = 'none';
            container.style.display = 'grid';
        }
    });
}

function closePhanLopModal() {
    const m = document.getElementById('phanLopModal');
    const c = document.getElementById('phanLopModalContent');
    m.classList.add('opacity-0'); c.classList.add('scale-95', 'translate-y-4', 'opacity-0');
    setTimeout(() => m.classList.add('hidden'), 300);
}

function savePhanLop() {
    const selects = document.querySelectorAll('.pl-select');
    let assignments = [];
    selects.forEach(s => assignments.push({lop_id: s.dataset.lop, giao_vien_id: s.value}));
    
    fetch('/thidua/admin/giao-vien?action=api_save_phan_lop', {
        method: 'POST', headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({assignments: assignments})
    }).then(r=>r.json()).then(d=>{
        if(d.success) {
            closePhanLopModal();
            loadData(window.lastPage || 1);
        } else {
            showToast('error', d.message);
        }
    });
}

// Avatar preview
document.getElementById('gv_avatar').addEventListener('change', function(e){
    if(this.files && this.files[0]){
        let r = new FileReader();
        r.onload = function(ev) { document.getElementById('gv_avatar_preview').src = ev.target.result; }
        r.readAsDataURL(this.files[0]);
    }
});

let currentPage = 1;
const filterForm = document.getElementById('filterForm');
const teacherTableBody = document.getElementById('teacherTableBody');
const paginationInfo = document.getElementById('paginationInfo');
const paginationControls = document.getElementById('paginationControls');

function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return unsafe.toString().replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

function getCheckedIds() {
    const cbs = document.querySelectorAll('.cb-teacher-item:checked');
    return Array.from(cbs).map(cb => cb.value);
}

function updateToolbarState() {
    const count = document.querySelectorAll('.cb-teacher-item:checked').length;
    const btnAccount = document.getElementById('btnBulkAccount');
    const btnTask = document.getElementById('btnTaskMenu');
    
    if (count > 0) {
        btnAccount.disabled = false; btnAccount.classList.remove('opacity-50', 'cursor-not-allowed');
        btnTask.disabled = false; btnTask.classList.remove('opacity-50', 'cursor-not-allowed');
    } else {
        btnAccount.disabled = true; btnAccount.classList.add('opacity-50', 'cursor-not-allowed');
        btnTask.disabled = true; btnTask.classList.add('opacity-50', 'cursor-not-allowed');
    }
}

function loadData(page = 1) {
    window.lastPage = page;
    const params = new URLSearchParams(new FormData(filterForm));
    params.append('page', page);
    params.append('action', 'index');
    params.append('_t', new Date().getTime());
    
    teacherTableBody.innerHTML = '<tr><td colspan="11" class="text-center py-8"><div class="spinner-border text-[#224397] spinner-border-sm"></div> Đang tải...</td></tr>';
    document.getElementById('cbCheckAll').checked = false;
    updateToolbarState();

    fetch('/thidua/admin/giao-vien?' + params.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Cache-Control': 'no-cache' } })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            currentData = res.data;
            renderTable(res.data, res.pagination);
        }
    });
}

function renderTable(data, pagination) {
    teacherTableBody.innerHTML = '';
    if (data.length === 0) {
        teacherTableBody.innerHTML = '<tr><td colspan="11" class="text-center py-8 text-slate-500 font-medium">Không tìm thấy giáo viên nào.</td></tr>';
        return;
    }

    const offset = (pagination.current_page - 1) * parseInt(document.getElementById('page_size').value || 100);
    
    data.forEach((gv, index) => {
        const tr = document.createElement('tr');
        
        const avatarSrc = gv.final_avatar ? gv.final_avatar : '/thidua/public/assets/img/anhthegoc.JPG';
        let classesStr = gv.cac_lop_chu_nhiem || '';
        let classesDisplay = classesStr;
        if(classesStr.length > 50) {
            classesDisplay = `<span title="${escapeHtml(classesStr)}">${escapeHtml(classesStr.substring(0, 50))}...</span>`;
        } else {
            classesDisplay = escapeHtml(classesStr);
        }

        let accHtml = '';
        if(gv.account_id) {
            const isLocked = gv.trang_thai_tai_khoan === 'Đã khóa';
            accHtml = `<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check-lg text-2xl font-bold ${isLocked ? 'text-red-500' : 'text-green-500'} mx-auto" viewBox="0 0 16 16" title="${isLocked ? 'Đã khóa' : 'Đã cấp'}"><path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425z"/></svg>`;
        } else {
            accHtml = `<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg text-lg text-slate-300 mx-auto" viewBox="0 0 16 16" title="Chưa có"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg>`;
        }

        tr.innerHTML = `
            <td class="text-center"><input type="checkbox" class="cb-teacher cb-teacher-item" value="${gv.id}"></td>
            <td class="text-center">${offset + index + 1}</td>
            <td class="p-0 text-center align-middle" style="padding: 0 !important; width: 60px; min-width: 60px;"><img src="${escapeHtml(avatarSrc)}" class="w-full h-[80px] object-cover block" onerror="this.src='/thidua/public/assets/img/anhthegoc.JPG'"></td>
            <td class="text-center">${escapeHtml(gv.cccd)}</td>
            <td class="text-left font-bold text-[#224397]">${escapeHtml(gv.ho_ten)}</td>
            <td class="text-center text-slate-600">${escapeHtml(gv.ngay_sinh)}</td>
            <td class="text-center">${escapeHtml(gv.sdt)}</td>
            <td class="text-left text-slate-600">${escapeHtml(gv.email)}</td>
            <td class="text-left font-medium text-[12px] text-slate-700">${classesDisplay}</td>
            <td class="text-center">${accHtml}</td>
            <td class="text-left text-[12px] text-slate-600 italic break-words max-w-[150px]" title="${gv.ghi_chu ? escapeHtml(gv.ghi_chu) : ''}">${gv.ghi_chu ? escapeHtml(gv.ghi_chu.length > 40 ? gv.ghi_chu.substring(0, 40) + '...' : gv.ghi_chu) : ''}</td>
            <td class="text-center">
                <button type="button" class="text-[#224397] hover:text-[#FAB723] transition p-1" onclick="openProfileModal(${index})" title="Xem hồ sơ chi tiết"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-eye-fill" viewBox="0 0 16 16"><path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>   <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/></svg></button>
            </td>
        `;
        teacherTableBody.appendChild(tr);
    });

    // Checkbox logic
    const cbCheckAll = document.getElementById('cbCheckAll');
    cbCheckAll.checked = false;
    cbCheckAll.indeterminate = false;
    const itemCbs = document.querySelectorAll('.cb-teacher-item');
    
    cbCheckAll.onchange = function() {
        this.indeterminate = false;
        const chk = this.checked;
        itemCbs.forEach(cb => cb.checked = chk);
        updateToolbarState();
    };
    
    itemCbs.forEach(cb => cb.onchange = function() {
        const total = itemCbs.length;
        const checkedCount = document.querySelectorAll('.cb-teacher-item:checked').length;
        if (checkedCount === 0) {
            cbCheckAll.checked = false;
            cbCheckAll.indeterminate = false;
        } else if (checkedCount === total) {
            cbCheckAll.checked = true;
            cbCheckAll.indeterminate = false;
        } else {
            cbCheckAll.checked = false;
            cbCheckAll.indeterminate = true;
        }
        updateToolbarState();
    });

    currentPage = pagination.current_page;
    paginationInfo.innerHTML = `Trang ${pagination.current_page} / ${pagination.total_pages} (${pagination.total_records} bản ghi)`;
    let paginationHtml = '';
    if (pagination.total_pages > 1) {
        if (pagination.current_page > 1) paginationHtml += `<button type="button" class="px-2.5 py-1 border border-[#224397]/25 rounded text-[#224397] hover:bg-slate-100 text-sm" onclick="changePage(${pagination.current_page - 1})"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-chevron-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0"/></svg></button>`;
        paginationHtml += `<button type="button" class="px-2.5 py-1 border border-[#224397] rounded bg-[#224397] text-white text-sm">${pagination.current_page}</button>`;
        if (pagination.current_page < pagination.total_pages) paginationHtml += `<button type="button" class="px-2.5 py-1 border border-[#224397]/25 rounded text-[#224397] hover:bg-slate-100 text-sm" onclick="changePage(${pagination.current_page + 1})"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-chevron-right" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708"/></svg></button>`;
    }
    paginationControls.innerHTML = paginationHtml;
}

window.changePage = function(page) { loadData(page); };
filterForm.addEventListener('input', () => { clearTimeout(window.searchTimeout); window.searchTimeout = setTimeout(() => loadData(1), 300); });
filterForm.addEventListener('change', () => loadData(1));
filterForm.addEventListener('submit', (e) => { e.preventDefault(); loadData(1); });

document.getElementById('teacherForm').addEventListener('submit', function(e) {
    e.preventDefault();
    fetch('/thidua/admin/giao-vien?action=api_save', { method: 'POST', body: new FormData(this) })
    .then(r => r.json()).then(d => { if (d.success) { closeTeacherModal(); loadData(currentPage); showToast('success', 'Lưu giáo viên thành công!'); } else showToast('error', d.message); });
});

window.bulkAccount = function() {
    const ids = getCheckedIds();
    if(ids.length === 0) return;
    AppSwal.fire({
        title: 'Cấp Tài Khoản',
        text: `Cấp tài khoản cho ${ids.length} giáo viên đã chọn?`,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Xác nhận',
        cancelButtonText: 'Hủy',
        customClass: {
            popup: 'bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.15)] border border-slate-200 py-6 px-6',
            title: 'text-[#224397] font-bold text-xl mt-0',
            htmlContainer: 'text-slate-600 font-medium text-[14.5px] mt-2 mb-2',
            actions: 'flex justify-center gap-3 w-full mt-6',
            confirmButton: 'bg-[#224397] text-white rounded-lg px-6 py-2 font-medium shadow-sm hover:bg-[#1a3475] hover:scale-110 hover:shadow-md transition-all duration-300 outline-none',
            cancelButton: 'bg-white text-slate-600 rounded-lg px-6 py-2 font-medium shadow-sm border border-slate-300 hover:bg-slate-50 transition-all duration-300 outline-none',
            icon: 'scale-[0.85] my-2'
        },
        buttonsStyling: false
    }).then((result) => {
        if(result.isConfirmed) {
            const loadingDiv = document.createElement('div');
        loadingDiv.innerHTML = `
            <div style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.8); backdrop-filter:blur(4px); z-index:99999; display:flex; flex-direction:column; align-items:center; justify-content:center; color:#224397; font-family:inherit;">
                <div style="border:4px solid #e2e8f0; border-top:4px solid #224397; border-radius:50%; width:50px; height:50px; animation:spin 1s linear infinite; margin-bottom:20px;"></div>
                <h3 style="margin:0; font-size:20px; font-weight:bold;">Hệ thống đang xử lý...</h3>
                <p style="margin-top:8px; font-size:15px; color:#475569;">Đang cấp tài khoản cho ${ids.length} giáo viên. Vui lòng chờ trong giây lát!</p>
                <style>@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style>
            </div>
        `;
        document.body.appendChild(loadingDiv);

        fetch('/thidua/admin/giao-vien?action=api_bulk_create_account', { method: 'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ids: ids}) })
        .then(r=>r.json()).then(d=>{ 
            document.body.removeChild(loadingDiv);
            showToast(d.success ? 'success' : 'error', d.message); 
            loadData(currentPage); 
        }).catch(err => {
            document.body.removeChild(loadingDiv);
            showToast('error', 'Có lỗi xảy ra kết nối mạng!');
        });
        }
    });
};

document.getElementById('btnBulkEdit').addEventListener('click', function(){
    const ids = getCheckedIds();
    if(ids.length !== 1) { showToast('warning', 'Vui lòng chỉ chọn đúng 1 giáo viên để sửa.'); return; }
    const gv = currentData.find(g => g.id == ids[0]);
    if(gv) openTeacherModal(gv);
});

document.getElementById('btnBulkDelete').addEventListener('click', function(){
    const ids = getCheckedIds();
    if(ids.length === 0) return;
    AppSwal.fire({
        title: 'Cảnh Báo!',
        text: `Chắc chắn muốn XÓA ${ids.length} giáo viên đã chọn? Hành động này không thể hoàn tác.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Xóa ngay',
        cancelButtonText: 'Hủy',
        customClass: {
            popup: 'bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.15)] border border-slate-200 py-6 px-6',
            title: 'text-red-600 font-bold text-xl mt-0',
            htmlContainer: 'text-slate-600 font-medium text-[14.5px] mt-2 mb-2',
            actions: 'flex justify-center gap-3 w-full mt-6',
            confirmButton: 'bg-red-600 text-white rounded-lg px-6 py-2 font-medium shadow-sm hover:bg-red-700 hover:scale-110 hover:shadow-md transition-all duration-300 outline-none',
            cancelButton: 'bg-white text-slate-600 rounded-lg px-6 py-2 font-medium shadow-sm border border-slate-300 hover:bg-slate-50 transition-all duration-300 outline-none',
            icon: 'scale-[0.85] my-2'
        },
        buttonsStyling: false
    }).then((result) => {
        if(result.isConfirmed) {
            fetch('/thidua/admin/giao-vien?action=api_bulk_delete', { method: 'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ids: ids}) })
            .then(r=>r.json()).then(d=>{ if(d.success) { loadData(currentPage); showToast('success', 'Đã xóa thành công!'); } else showToast('error', d.message); });
        }
    });
});

document.getElementById('btnBulkResetPass').addEventListener('click', function(){
    const ids = getCheckedIds();
    if(ids.length === 0) return;
    AppSwal.fire({
        title: 'Khôi Phục Mật Khẩu',
        text: `Khôi phục mật khẩu mặc định (Ngày sinh) cho ${ids.length} giáo viên đã chọn?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Khôi phục',
        cancelButtonText: 'Hủy',
        customClass: {
            popup: 'bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.15)] border border-slate-200 py-6 px-6',
            title: 'text-[#FAB723] font-bold text-xl mt-0',
            htmlContainer: 'text-slate-600 font-medium text-[14.5px] mt-2 mb-2',
            actions: 'flex justify-center gap-3 w-full mt-6',
            confirmButton: 'bg-[#FAB723] text-white rounded-lg px-6 py-2 font-medium shadow-sm hover:bg-[#e5a61d] hover:scale-110 hover:shadow-md transition-all duration-300 outline-none',
            cancelButton: 'bg-white text-slate-600 rounded-lg px-6 py-2 font-medium shadow-sm border border-slate-300 hover:bg-slate-50 transition-all duration-300 outline-none',
            icon: 'scale-[0.85] my-2'
        },
        buttonsStyling: false
    }).then((result) => {
        if(result.isConfirmed) {
            fetch('/thidua/admin/giao-vien?action=api_bulk_reset_password', { method: 'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ids: ids}) })
            .then(r=>r.json()).then(d=>{ showToast(d.success ? 'success' : 'error', d.message); });
        }
    });
});

window.exportExcel = function() {
    const keyword = document.getElementById('keyword').value;
    window.location.href = `/thidua/admin/giao-vien?action=export_excel&keyword=${encodeURIComponent(keyword)}`;
};

const importFile = document.getElementById('importFile');
document.getElementById('btnTriggerImport').addEventListener('click', () => importFile.click());
importFile.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if(!file) return;
    const fd = new FormData(); fd.append('file', file);
    const btn = document.getElementById('importMenuBtn');
    const oldHtml = btn.innerHTML;
    btn.innerHTML = '<div class="spinner-border spinner-border-sm mr-2" style="width:12px; height:12px;"></div> Đang xử lý...';
    btn.disabled = true;

    fetch('/thidua/admin/giao-vien?action=import', { method: 'POST', body: fd })
    .then(r => r.json()).then(d => {
        btn.innerHTML = oldHtml; btn.disabled = false; importFile.value = '';
        showToast(d.success ? 'success' : 'error', d.message); if (d.success) loadData(currentPage);
    }).catch(() => { btn.innerHTML = oldHtml; btn.disabled = false; importFile.value = ''; showToast('error', 'Lỗi tải file.'); });
});

loadData(1);
</script>
<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>
