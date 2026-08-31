<?php
// File: src/views/admin_exam_participants.php
$page_title = 'Danh Sách Thí Sinh: ' . htmlspecialchars($ky_thi_info['ten_ky_thi'] ?? '');
require_once __DIR__ . '/partials/admin_header.php';
?>

<style>
    body, body > div.w-full.min-h-screen.bg-slate-50 {
        background: linear-gradient(to bottom right, #f8fafc, #E4F6FD) !important;
    }
    body::-webkit-scrollbar, html::-webkit-scrollbar { display: none; }

    /* Toast Notification styles */
    #toast-container {
        position: fixed; bottom: 1.5rem; right: 1.5rem;
        z-index: 10000; display: flex; flex-direction: column; gap: 0.5rem;
    }
    .toast-item {
        padding: 0.75rem 1.25rem;
        border-radius: 10px;
        font-size: 0.86rem;
        font-weight: 600;
        display: flex; align-items: center; gap: 0.6rem;
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        animation: toastIn 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        max-width: 380px;
        border: 1px solid;
    }
    .toast-success { background: #f0fdf4; color: #166534; border-color: #86efac; }
    .toast-error { background: #fef2f2; color: #991b1b; border-color: #fca5a5; }
    .toast-warning { background: #fffbeb; color: #92400e; border-color: #fcd34d; }
    .toast-info { background: #eff6ff; color: #1e40af; border-color: #93c5fd; }

    @keyframes toastIn { from { opacity:0; transform: translateX(50px); } to { opacity:1; transform: translateX(0); } }
    @keyframes toastOut { to { opacity:0; transform: translateX(50px); } }
</style>

<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 py-6 min-h-screen">
    
    <!-- Top Breadcrumb & Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 pb-4 border-b border-[#224397]/25 gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-1.5">
                <a href="/thidua/admin/exam-list?iframe=1" class="hover:text-[#224397] transition flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-chevron-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0"/></svg>
                    Quản Lý Kỳ Thi
                </a>
                <span>/</span>
                <span class="text-[#224397] font-semibold">Danh Sách Thí Sinh</span>
            </div>
            <h1 class="text-xl font-bold text-[#224397] flex items-center gap-2.5 uppercase tracking-wide m-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-people-fill text-[#FAB723]" viewBox="0 0 16 16"><path d="M7 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/></svg>
                DANH SÁCH THÍ SINH: <?= htmlspecialchars($ky_thi_info['ten_ky_thi'] ?? '') ?>
            </h1>
        </div>
        
        <div class="flex items-center gap-2.5">
            <a href="/thidua/admin/exam-rooms?id=<?= $ky_thi_id ?>&iframe=1" class="px-3.5 py-2 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg hover:bg-emerald-600 hover:text-white transition-all text-xs font-semibold flex items-center gap-1.5 shadow-sm hover:scale-105">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-door-open-fill" viewBox="0 0 16 16"><path d="M1.5 15a.5.5 0 0 0 0 1h13a.5.5 0 0 0 0-1H13V2.5A1.5 1.5 0 0 0 11.5 1H11V.5a.5.5 0 0 0-.57-.495l-7 1A.5.5 0 0 0 3 1.5V15zM11 2h.5a.5.5 0 0 1 .5.5V15h-1zm-2.5 8c-.276 0-.5-.448-.5-1s.224-1 .5-1 .5.448.5 1-.224 1-.5 1"/></svg>
                Chia Phòng Thi
            </a>
        </div>
    </div>

    <!-- Filter & Actions Card -->
    <div class="bg-white rounded-xl shadow-sm border border-[#224397]/25 p-4 mb-6">
        <div class="flex flex-col lg:flex-row justify-between items-stretch lg:items-center gap-4">
            
            <!-- Left: Filter & Search Controls -->
            <div class="flex flex-wrap items-center gap-3">
                
                <!-- Filter Type -->
                <div class="flex items-center gap-1.5">
                    <label class="text-xs font-semibold text-slate-500 whitespace-nowrap">Lọc:</label>
                    <select id="filterType" class="px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium text-slate-700 focus:outline-none focus:border-[#224397]">
                        <option value="all">Toàn trường</option>
                        <option value="grade">Theo Khối</option>
                        <option value="class">Theo Lớp</option>
                    </select>
                </div>

                <!-- Grade Filter (Hidden by default) -->
                <div id="filterGradeDiv" class="hidden items-center gap-1.5">
                    <select id="filterGrade" class="px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium text-slate-700 focus:outline-none focus:border-[#224397]">
                        <option value="10">Khối 10</option>
                        <option value="11">Khối 11</option>
                        <option value="12">Khối 12</option>
                    </select>
                </div>

                <!-- Class Filter (Hidden by default) -->
                <div id="filterClassDiv" class="hidden items-center gap-1.5">
                    <select id="filterClass" class="px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium text-slate-700 focus:outline-none focus:border-[#224397]">
                        <option value="">-- Chọn Lớp --</option>
                        <?php foreach ($ds_lop_hoc as $lop): ?>
                            <option value="<?= htmlspecialchars($lop['ten_lop']) ?>"><?= htmlspecialchars($lop['ten_lop']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Sort Type -->
                <div class="flex items-center gap-1.5">
                    <label class="text-xs font-semibold text-slate-500 whitespace-nowrap">Sắp xếp:</label>
                    <select id="sortType" class="px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium text-slate-700 focus:outline-none focus:border-[#224397]">
                        <option value="stt">Mặc định</option>
                        <option value="class">Theo Lớp & Tên</option>
                        <option value="name">Theo Tên (A-Z)</option>
                        <option value="sbd">Theo SBD</option>
                    </select>
                </div>

                <!-- Search Input -->
                <div class="relative min-w-[200px]">
                    <input type="text" id="searchInput" placeholder="Tìm tên, mã, SBD..." class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-300 rounded-lg text-xs text-slate-700 focus:outline-none focus:border-[#224397]">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" class="bi bi-search absolute left-2.5 top-2 text-slate-400" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/></svg>
                </div>
            </div>

            <!-- Right: Action Buttons & Dropdowns (UI_SYNC_STANDARDS.md) -->
            <div class="flex items-center gap-2 flex-wrap">
                
                <!-- Primary Action: Thêm Học Sinh -->
                <button type="button" class="px-3 py-1.5 bg-[#224397] text-white rounded-lg hover:bg-[#FAB723] hover:text-white font-medium flex items-center gap-1.5 text-xs shadow-sm hover:scale-105 transition-all duration-200" onclick="openModal('addStudentsModal')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-person-plus-fill" viewBox="0 0 16 16"><path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/><path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5"/></svg>
                    Thêm Học Sinh
                </button>

                <!-- Dropdown: Nhập / Xuất Excel (UI_SYNC_STANDARDS.md Section 1) -->
                <div class="relative inline-block text-left group">
                    <button type="button" class="px-3 py-1.5 bg-white border border-[#224397]/30 rounded-lg text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium flex items-center gap-1 text-xs shadow-sm whitespace-nowrap">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-file-earmark-spreadsheet-fill text-emerald-600 group-hover:text-white" viewBox="0 0 16 16"><path d="M6 12v-2h3v2z"/><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M3 9h10v1h-3v2h3v1h-3v2H9v-2H6v2H5v-2H3v-1h2v-2H3z"/></svg> 
                        Nhập / Xuất Excel 
                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="currentColor" class="bi bi-chevron-down text-[9px]" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708"/></svg>
                    </button>
                    
                    <ul class="absolute right-0 mt-1 w-56 bg-white rounded-lg shadow-xl border border-slate-200 focus:outline-none opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-[100] transform origin-top-right scale-95 group-hover:scale-100 py-1.5 text-left">
                        <li class="px-3 py-1 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tải Mẫu Excel</li>
                        <li><a class="flex items-center gap-2 px-3.5 py-1.5 text-xs text-slate-700 hover:bg-blue-50 hover:text-[#224397]" href="/thidua/admin/exam-export-template?id=<?= $ky_thi_id ?>&type=moet"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" class="bi bi-download text-blue-600" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/><path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/></svg> Mẫu Mã MOET</a></li>
                        <li><a class="flex items-center gap-2 px-3.5 py-1.5 text-xs text-slate-700 hover:bg-blue-50 hover:text-[#224397]" href="/thidua/admin/exam-export-template?id=<?= $ky_thi_id ?>&type=sbd"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" class="bi bi-download text-blue-600" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/><path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/></svg> Mẫu Số Báo Danh (SBD)</a></li>
                        <li><a class="flex items-center gap-2 px-3.5 py-1.5 text-xs text-slate-700 hover:bg-blue-50 hover:text-[#224397]" href="/thidua/admin/exam-export-template?id=<?= $ky_thi_id ?>&type=subjects"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" class="bi bi-download text-blue-600" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/><path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/></svg> Mẫu Tổ Hợp Môn Thi</a></li>
                        
                        <li><hr class="border-t border-slate-100 my-1"></li>
                        <li class="px-3 py-1 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Nhập Dữ Liệu</li>
                        <li><button type="button" class="w-full flex items-center gap-2 px-3.5 py-1.5 text-xs text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 text-left" onclick="openImportModal('moet')"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" class="bi bi-upload text-emerald-600" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/><path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708z"/></svg> Nhập Mã MOET</button></li>
                        <li><button type="button" class="w-full flex items-center gap-2 px-3.5 py-1.5 text-xs text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 text-left" onclick="openImportModal('sbd')"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" class="bi bi-upload text-emerald-600" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/><path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708z"/></svg> Nhập Số Báo Danh</button></li>
                        <li><button type="button" class="w-full flex items-center gap-2 px-3.5 py-1.5 text-xs text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 text-left" onclick="openImportModal('subjects')"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" class="bi bi-upload text-emerald-600" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/><path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708z"/></svg> Nhập Tổ Hợp Môn Thi</button></li>
                    </ul>
                </div>

                <!-- Dropdown: Tác vụ khác -->
                <div class="relative inline-block text-left group">
                    <button type="button" class="px-3 py-1.5 bg-white border border-[#224397]/30 rounded-lg text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium flex items-center gap-1 text-xs shadow-sm whitespace-nowrap">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-gear-fill" viewBox="0 0 16 16"><path d="M9.405 1.05c-.413-1.4-2.397-1.4-2.81 0l-.1.34a1.464 1.464 0 0 1-2.105.872l-.31-.17c-1.283-.698-2.686.705-1.987 1.987l.169.311c.446.82.023 1.841-.872 2.105l-.34.1c-1.4.413-1.4 2.397 0 2.81l.34.1a1.464 1.464 0 0 1 .872 2.105l-.17.31c-.698 1.283.705 2.686 1.987 1.987l.311-.169a1.464 1.464 0 0 1 2.105.872l.1.34c.413 1.4 2.397 1.4 2.81 0l.1-.34a1.464 1.464 0 0 1 2.105-.872l.31.17c1.283.698 2.686-.705 1.987-1.987l-.169-.311a1.464 1.464 0 0 1 .872-2.105l.34-.1c1.4-.413 1.4-2.397 0-2.81l-.34-.1a1.464 1.464 0 0 1-.872-2.105l.17-.31c.698-1.283-.705-2.686-1.987-1.987l-.311.169a1.464 1.464 0 0 1-2.105-.872zM8 10.93a2.929 2.929 0 1 1 0-5.86 2.929 2.929 0 0 1 0 5.858z"/></svg> 
                        Tác Vụ Khác 
                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="currentColor" class="bi bi-chevron-down text-[9px]" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708"/></svg>
                    </button>
                    
                    <ul class="absolute right-0 mt-1 w-52 bg-white rounded-lg shadow-xl border border-slate-200 focus:outline-none opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-[100] transform origin-top-right scale-95 group-hover:scale-100 py-1.5 text-left">
                        <li>
                            <button type="button" class="w-full flex items-center gap-2 px-3.5 py-2 text-xs text-slate-700 hover:bg-blue-50 hover:text-[#224397] transition text-left" onclick="openModal('generateSbdModal')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" class="bi bi-magic text-blue-600" viewBox="0 0 16 16"><path d="M9.5 2.672a.5.5 0 0 1 .707 0l2.122 2.122a.5.5 0 0 1 0 .707l-2.122 2.122a.5.5 0 0 1-.707 0L7.378 5.5a.5.5 0 0 1 0-.707zm-4.243 4.243a.5.5 0 0 1 .707 0l2.122 2.122a.5.5 0 0 1 0 .707l-2.122 2.122a.5.5 0 0 1-.707 0L3.136 9.74a.5.5 0 0 1 0-.707z"/><path d="M14.046 3.207a1.5 1.5 0 0 0-2.121 0L1.464 13.668a1.5 1.5 0 0 0 2.122 2.121L14.046 5.328a1.5 1.5 0 0 0 0-2.121m-1.414 1.414L2.879 14.375a.5.5 0 0 1-.707-.707l9.753-9.754a.5.5 0 0 1 .707.707"/></svg>
                                Tạo SBD Tự Động
                            </button>
                        </li>
                        <li><hr class="border-t border-slate-100 my-1"></li>
                        <li>
                            <button type="button" class="w-full flex items-center gap-2 px-3.5 py-2 text-xs text-red-600 hover:bg-red-50 transition text-left" onclick="deleteAllStudents()">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" class="bi bi-trash3-fill text-red-600" viewBox="0 0 16 16"><path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5"/></svg>
                                Xóa Toàn Bộ Danh Sách
                            </button>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="bg-white rounded-xl shadow-sm border border-[#224397]/25 mb-6">
        <div class="bg-slate-50 px-5 py-3.5 border-b border-[#224397]/25 font-semibold text-[#224397] flex items-center justify-between text-sm uppercase rounded-t-xl">
            <span class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="currentColor" class="bi bi-card-list text-[#FAB723]" viewBox="0 0 16 16"><path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2z"/><path fill-rule="evenodd" d="M5 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 5 8m0-2.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m0 5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-1-5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0M4 8a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0m0 2.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0"/></svg>
                DANH SÁCH THÍ SINH
            </span>
            <span class="text-xs font-normal text-slate-500 normal-case">
                Tổng số hiển thị: <strong id="studentCountBadge" class="text-[#224397] font-bold"><?= count($ds_hoc_sinh ?? []) ?></strong> thí sinh
            </span>
        </div>

        <div class="overflow-x-auto w-full">
            <table class="w-full text-left text-[13px] text-slate-600 border-collapse">
                <thead>
                    <tr class="bg-slate-100/80 text-[#224397] uppercase text-[11.5px] font-bold tracking-wide border-b border-[#224397]/25">
                        <th class="p-3 border-r border-slate-200 text-center w-12">STT</th>
                        <th class="p-3 border-r border-slate-200 text-center w-28">Mã MOET</th>
                        <th class="p-3 border-r border-slate-200 text-center w-24">Mã HS</th>
                        <th class="p-3 border-r border-slate-200 text-center w-28">Số Báo Danh</th>
                        <th class="p-3 border-r border-slate-200 min-w-[160px]">Họ Và Tên</th>
                        <th class="p-3 border-r border-slate-200 text-center w-20">Lớp</th>
                        <th class="p-3 border-r border-slate-200 text-center w-24">Ngày Sinh</th>
                        <th class="p-3 border-r border-slate-200 text-center w-20">Giới Tính</th>
                        <th class="p-3 border-r border-slate-200 text-center w-28">Phòng Thi</th>
                        <th class="p-3 border-r border-slate-200 min-w-[180px]">Tổ Hợp Môn Thi</th>
                        <th class="p-3 border-r border-slate-200 min-w-[120px]">Ghi Chú</th>
                        <th class="p-3 text-center w-14">Xóa</th>
                    </tr>
                </thead>
                <tbody id="studentListBody">
                    <?php if (empty($ds_hoc_sinh)): ?>
                        <tr id="emptyStudentRow">
                            <td colspan="12" class="text-center text-slate-400 p-12 italic">
                                <svg xmlns="http://www.w3.org/2000/svg" width="2.5em" height="2.5em" fill="currentColor" class="bi bi-people mx-auto mb-2 text-slate-300" viewBox="0 0 16 16"><path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4-1 1-1 1zm-7.978-1L7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002-.014.002zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0M6.936 9.28a6 6 0 0 0-1.23-.247A7 7 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/></svg>
                                Chưa có thí sinh nào trong kỳ thi này. Bấm <strong>"Thêm Học Sinh"</strong> để bắt đầu.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ds_hoc_sinh as $index => $hs): 
                            $grade = substr($hs['ten_lop'], 0, 2);
                            $fullName = trim(($hs['ho_dem'] ?? '') . ' ' . ($hs['ten'] ?? ''));
                        ?>
                            <tr id="student-row-<?= $hs['kths_id'] ?>" 
                                class="hover:bg-blue-50/40 transition-colors duration-150 border-b border-slate-200"
                                data-stt="<?= $index + 1 ?>"
                                data-grade="<?= htmlspecialchars($grade) ?>"
                                data-class="<?= htmlspecialchars($hs['ten_lop']) ?>"
                                data-name="<?= htmlspecialchars($hs['ten'] . ' ' . $hs['ho_dem']) ?>"
                                data-fullname="<?= htmlspecialchars(mb_strtolower($fullName)) ?>"
                                data-mahocsinh="<?= htmlspecialchars(mb_strtolower($hs['ma_hoc_sinh'] ?? '')) ?>"
                                data-sbd="<?= htmlspecialchars($hs['so_bao_danh'] ?? '') ?>">
                                
                                <td class="p-3 border-r border-slate-200 text-center font-medium text-slate-500 stt-cell"><?= $index + 1 ?></td>
                                <td class="p-3 border-r border-slate-200 text-center font-mono text-xs"><?= htmlspecialchars($hs['ma_moet'] ?: '---') ?></td>
                                <td class="p-3 border-r border-slate-200 text-center font-mono text-xs font-semibold text-slate-700"><?= htmlspecialchars($hs['ma_hoc_sinh']) ?></td>
                                <td class="p-3 border-r border-slate-200 text-center font-mono font-bold text-[#224397]"><?= htmlspecialchars($hs['so_bao_danh'] ?: '---') ?></td>
                                <td class="p-3 border-r border-slate-200 font-bold text-slate-800"><?= htmlspecialchars($fullName) ?></td>
                                <td class="p-3 border-r border-slate-200 text-center font-semibold text-slate-700"><?= htmlspecialchars($hs['ten_lop']) ?></td>
                                <td class="p-3 border-r border-slate-200 text-center text-slate-600"><?= $hs['ngay_sinh'] ? date('d/m/Y', strtotime($hs['ngay_sinh'])) : '---' ?></td>
                                <td class="p-3 border-r border-slate-200 text-center"><?= htmlspecialchars($hs['gioi_tinh'] ?? '---') ?></td>
                                <td class="p-3 border-r border-slate-200 text-center">
                                    <?php if (!empty($hs['ten_phong'])): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200"><?= htmlspecialchars($hs['ten_phong']) ?></span>
                                    <?php else: ?>
                                        <span class="text-slate-400 italic text-xs">Chưa chia</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-3 border-r border-slate-200 text-xs">
                                    <?php if (!empty($hs['dang_ky_mon_labels'])): ?>
                                        <div class="flex flex-wrap gap-1">
                                            <?php foreach ($hs['dang_ky_mon_labels'] as $label): ?>
                                                <span class="px-1.5 py-0.5 bg-blue-50 text-[#224397] border border-blue-100 rounded text-[11px] font-medium"><?= htmlspecialchars($label) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-slate-400 italic">Mặc định</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-3 border-r border-slate-200 text-xs text-slate-500"><?= htmlspecialchars($hs['ghi_chu'] ?? '') ?></td>
                                <td class="p-3 text-center">
                                    <button type="button" class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded transition" onclick="removeStudent(<?= $hs['kths_id'] ?>, '<?= htmlspecialchars(addslashes($fullName)) ?>')" title="Xóa khỏi kỳ thi">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16"><path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5"/></svg>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- =================================================================== -->
<!-- MODAL: THÊM HỌC SINH VÀO KỲ THI (UI_SYNC_STANDARDS.md Section 2) -->
<!-- =================================================================== -->
<div id="addStudentsModal" class="fixed inset-0 z-[10005] hidden items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0" onclick="closeModal('addStudentsModal')">
    <div class="bg-white rounded-xl shadow-2xl w-[500px] max-w-[92%] flex flex-col overflow-hidden border border-slate-300 transform transition-all duration-300 scale-95 translate-y-4 opacity-0 modal-content-box" onclick="event.stopPropagation()">
        
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-slate-50">
            <h5 class="text-lg font-bold text-[#224397] flex items-center gap-2 m-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="currentColor" class="bi bi-person-plus-fill text-[#FAB723]" viewBox="0 0 16 16"><path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/><path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5"/></svg>
                Thêm Thí Sinh Vào Kỳ Thi
            </h5>
            <button type="button" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition" onclick="closeModal('addStudentsModal')">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg>
            </button>
        </div>
        
        <!-- Body -->
        <div class="px-6 py-5 space-y-4 text-sm text-slate-700">
            <p class="text-xs text-slate-600 bg-amber-50 p-2.5 rounded-lg border border-amber-200 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle-fill text-amber-600 shrink-0" viewBox="0 0 16 16"><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2"/></svg>
                <span>Hệ thống <strong>chỉ thêm học sinh đang học tập</strong> trong năm học của kỳ thi (tự động loại trừ học sinh đã nghỉ học hoặc đã tốt nghiệp).</span>
            </p>
            
            <div>
                <label for="addModalType" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Chọn Đối Tượng Thêm <span class="text-red-500">*</span></label>
                <select id="addModalType" class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:border-[#224397] focus:ring-1 focus:ring-[#224397] focus:outline-none shadow-sm text-slate-800 text-sm font-medium">
                    <option value="all">Toàn bộ học sinh đang học (Toàn trường)</option>
                    <option value="grade">Theo Khối</option>
                    <option value="class">Theo từng Lớp cụ thể</option>
                </select>
            </div>

            <!-- Grade Choice -->
            <div id="addModalGradeChoice" class="hidden">
                <label for="addModalGradeSelect" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Chọn Khối <span class="text-red-500">*</span></label>
                <select id="addModalGradeSelect" class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:border-[#224397] focus:ring-1 focus:ring-[#224397] focus:outline-none shadow-sm text-slate-800 text-sm">
                    <option value="10">Khối 10</option>
                    <option value="11">Khối 11</option>
                    <option value="12">Khối 12</option>
                </select>
            </div>

            <!-- Class Choice -->
            <div id="addModalClassChoice" class="hidden">
                <label for="addModalClassSelect" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Chọn Lớp Học <span class="text-red-500">*</span></label>
                <select id="addModalClassSelect" class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:border-[#224397] focus:ring-1 focus:ring-[#224397] focus:outline-none shadow-sm text-slate-800 text-sm">
                    <option value="">-- Chọn Lớp --</option>
                    <?php foreach ($ds_lop_hoc as $lop): ?>
                        <option value="<?= $lop['id'] ?>"><?= htmlspecialchars($lop['ten_lop']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-2">
            <button type="button" class="px-4 py-2 text-[13px] font-medium text-gray-600 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 transition" onclick="closeModal('addStudentsModal')">Đóng</button>
            <button type="button" class="px-4 py-2 text-[13px] font-bold text-slate-900 bg-[#FAB723] border border-[#FAB723] rounded-lg shadow-sm hover:bg-[#e5a61d] transition flex items-center gap-1.5" id="btnConfirmAddStudents" onclick="confirmAddStudents()">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-check-lg" viewBox="0 0 16 16"><path d="M12.736 3.97a.75.75 0 0 1 1.02 1.084l-7.25 7.5a.75.75 0 0 1-1.08 0L2.21 8.804a.75.75 0 0 1 1.08-1.084l2.74 2.74z"/></svg>
                <span>Xác Nhận Thêm</span>
            </button>
        </div>
    </div>
</div>

<!-- =================================================================== -->
<!-- MODAL: NHẬP DỮ LIỆU TỪ EXCEL (Import Modal) -->
<!-- =================================================================== -->
<div id="importExcelModal" class="fixed inset-0 z-[10005] hidden items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0" onclick="closeModal('importExcelModal')">
    <div class="bg-white rounded-xl shadow-2xl w-[520px] max-w-[92%] flex flex-col overflow-hidden border border-slate-300 transform transition-all duration-300 scale-95 translate-y-4 opacity-0 modal-content-box" onclick="event.stopPropagation()">
        
        <form id="importForm" onsubmit="event.preventDefault(); submitImportExcel();">
            <input type="hidden" id="importTypeHidden" name="import_type" value="moet">
            
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-slate-50">
                <h5 class="text-lg font-bold text-[#224397] flex items-center gap-2 m-0" id="importModalTitle">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="currentColor" class="bi bi-file-earmark-arrow-up-fill text-emerald-600" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M6.354 9.854a.5.5 0 0 1-.708-.708l2-2a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1-.708.708L8.5 8.707V12.5a.5.5 0 0 1-1 0V8.707z"/></svg>
                    Nhập Dữ Liệu Từ Excel
                </h5>
                <button type="button" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition" onclick="closeModal('importExcelModal')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg>
                </button>
            </div>
            
            <!-- Body -->
            <div class="px-6 py-5 space-y-4 text-sm text-slate-700">
                <div class="text-xs text-slate-600 bg-blue-50 p-3 rounded-lg border border-blue-200" id="importHelpText">
                    Vui lòng tải file mẫu Excel tương ứng, điền thông tin và tải lên đây.
                </div>
                
                <div>
                    <label for="excelFile" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Chọn Tệp Excel (.xlsx, .xls) <span class="text-red-500">*</span></label>
                    <input type="file" id="excelFile" name="importFile" accept=".xlsx, .xls" class="w-full text-xs text-slate-600 file:mr-3 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[#224397] file:text-white hover:file:bg-[#FAB723] hover:file:text-white file:cursor-pointer border border-slate-300 rounded-lg p-1.5 bg-slate-50" required>
                </div>
                
                <!-- Progress & Result -->
                <div id="importProgress" class="hidden text-center py-2">
                    <div class="inline-block w-6 h-6 border-2 border-[#224397] border-t-transparent rounded-full animate-spin"></div>
                    <p class="text-xs text-slate-500 font-medium mt-1">Đang xử lý dữ liệu Excel...</p>
                </div>
                <div id="importResult" class="text-xs"></div>
            </div>
            
            <!-- Footer -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-2">
                <button type="button" class="px-4 py-2 text-[13px] font-medium text-gray-600 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 transition" onclick="closeModal('importExcelModal')">Đóng</button>
                <button type="submit" class="px-4 py-2 text-[13px] font-bold text-slate-900 bg-[#FAB723] border border-[#FAB723] rounded-lg shadow-sm hover:bg-[#e5a61d] transition flex items-center gap-1.5" id="btnSubmitImport">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-upload" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/><path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708z"/></svg>
                    <span>Tiến Hành Nhập</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- =================================================================== -->
<!-- MODAL: TẠO SỐ BÁO DANH TỰ ĐỘNG (Generate SBD Modal) -->
<!-- =================================================================== -->
<div id="generateSbdModal" class="fixed inset-0 z-[10005] hidden items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0" onclick="closeModal('generateSbdModal')">
    <div class="bg-white rounded-xl shadow-2xl w-[500px] max-w-[92%] flex flex-col overflow-hidden border border-slate-300 transform transition-all duration-300 scale-95 translate-y-4 opacity-0 modal-content-box" onclick="event.stopPropagation()">
        
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-slate-50">
            <h5 class="text-lg font-bold text-[#224397] flex items-center gap-2 m-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="currentColor" class="bi bi-magic text-[#FAB723]" viewBox="0 0 16 16"><path d="M9.5 2.672a.5.5 0 0 1 .707 0l2.122 2.122a.5.5 0 0 1 0 .707l-2.122 2.122a.5.5 0 0 1-.707 0L7.378 5.5a.5.5 0 0 1 0-.707zm-4.243 4.243a.5.5 0 0 1 .707 0l2.122 2.122a.5.5 0 0 1 0 .707l-2.122 2.122a.5.5 0 0 1-.707 0L3.136 9.74a.5.5 0 0 1 0-.707z"/><path d="M14.046 3.207a1.5 1.5 0 0 0-2.121 0L1.464 13.668a1.5 1.5 0 0 0 2.122 2.121L14.046 5.328a1.5 1.5 0 0 0 0-2.121m-1.414 1.414L2.879 14.375a.5.5 0 0 1-.707-.707l9.753-9.754a.5.5 0 0 1 .707.707"/></svg>
                Tạo Số Báo Danh (SBD) Tự Động
            </h5>
            <button type="button" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition" onclick="closeModal('generateSbdModal')">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg>
            </button>
        </div>
        
        <!-- Body -->
        <div class="px-6 py-5 space-y-4 text-sm text-slate-700">
            <div class="text-xs text-slate-600 bg-amber-50 p-3 rounded-lg border border-amber-200">
                Quy tắc sinh SBD: <strong>48 + [Mã Khối 10/11/12] + [Số thứ tự 001, 002...]</strong>.
                <br>Ví dụ: Thí sinh khối 10 đầu tiên sẽ có SBD là <strong>4810001</strong>.
            </div>
            
            <div>
                <label for="sbdSortMethod" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Quy Tắc Sắp Xếp Để Đánh Số <span class="text-red-500">*</span></label>
                <select id="sbdSortMethod" class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:border-[#224397] focus:ring-1 focus:ring-[#224397] focus:outline-none shadow-sm text-slate-800 text-sm font-medium">
                    <option value="by_class">Sắp xếp theo từng Lớp (Lớp 10A1 ➔ 10A2...), trong lớp xếp theo Tên A-Z</option>
                    <option value="by_grade_name">Sắp xếp theo Tên A-Z toàn bộ khối (Trộn lẫn các lớp trong khối)</option>
                </select>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-2">
            <button type="button" class="px-4 py-2 text-[13px] font-medium text-gray-600 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 transition" onclick="closeModal('generateSbdModal')">Hủy</button>
            <button type="button" class="px-4 py-2 text-[13px] font-bold text-slate-900 bg-[#FAB723] border border-[#FAB723] rounded-lg shadow-sm hover:bg-[#e5a61d] transition flex items-center gap-1.5" id="btnConfirmGenerateSbd" onclick="confirmGenerateSbd()">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-play-fill" viewBox="0 0 16 16"><path d="m11.596 8.697-6.363 3.692c-.54.313-1.233-.066-1.233-.697V4.308c0-.63.692-1.01 1.233-.696l6.363 3.692a.802.802 0 0 1 0 1.393"/></svg>
                <span>Bắt Đầu Đánh Số</span>
            </button>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div id="toast-container"></div>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>

<script>
    const CURRENT_KY_THI_ID = <?= (int)$ky_thi_id ?>;

    // ===== MODAL ANIMATION HELPERS (UI_SYNC_STANDARDS.md) =====
    function openModal(id) {
        const modal = document.getElementById(id);
        const content = modal.querySelector('.modal-content-box');
        
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
        void modal.offsetWidth; // Force reflow
        
        modal.style.opacity = '1';
        modal.classList.remove('opacity-0');
        if (content) {
            content.style.transform = 'scale(1) translateY(0)';
            content.style.opacity = '1';
            content.classList.remove('scale-95', 'translate-y-4', 'opacity-0');
        }
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        const content = modal.querySelector('.modal-content-box');
        
        modal.style.opacity = '0';
        if (content) {
            content.style.transform = 'scale(0.95) translateY(1rem)';
            content.style.opacity = '0';
        }
        
        setTimeout(() => {
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }, 300);
    }

    // ===== TOAST NOTIFICATION HELPER =====
    function showToast(message, type = 'success', duration = 3000) {
        const container = document.getElementById('toast-container');
        if (!container) return;
        
        const toast = document.createElement('div');
        toast.className = `toast-item toast-${type}`;
        
        let icon = '';
        if (type === 'success') icon = `<svg width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill shrink-0" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>`;
        else if (type === 'error') icon = `<svg width="16" height="16" fill="currentColor" class="bi bi-exclamation-circle-fill shrink-0" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4m.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2"/></svg>`;
        else icon = `<svg width="16" height="16" fill="currentColor" class="bi bi-info-circle-fill shrink-0" viewBox="0 0 16 16"><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2"/></svg>`;
        
        toast.innerHTML = `${icon} <span>${message}</span>`;
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'toastOut 0.35s forwards';
            setTimeout(() => toast.remove(), 350);
        }, duration);
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // ===== BỘ LỌC VÀ TÌM KIẾM =====
    document.addEventListener('DOMContentLoaded', function() {
        const filterType = document.getElementById('filterType');
        const filterGradeDiv = document.getElementById('filterGradeDiv');
        const filterClassDiv = document.getElementById('filterClassDiv');
        const filterGrade = document.getElementById('filterGrade');
        const filterClass = document.getElementById('filterClass');
        const sortType = document.getElementById('sortType');
        const searchInput = document.getElementById('searchInput');

        // Modal Thêm Học Sinh Switch Types
        const addModalType = document.getElementById('addModalType');
        const addModalGradeChoice = document.getElementById('addModalGradeChoice');
        const addModalClassChoice = document.getElementById('addModalClassChoice');

        if (addModalType) {
            addModalType.addEventListener('change', function() {
                addModalGradeChoice.classList.toggle('hidden', this.value !== 'grade');
                addModalClassChoice.classList.toggle('hidden', this.value !== 'class');
            });
        }

        // Filter Controls
        filterType.addEventListener('change', function() {
            const val = this.value;
            filterGradeDiv.classList.toggle('hidden', val !== 'grade');
            filterGradeDiv.classList.toggle('flex', val === 'grade');
            filterClassDiv.classList.toggle('hidden', val !== 'class');
            filterClassDiv.classList.toggle('flex', val === 'class');
            applyFiltersAndSort();
        });

        filterGrade.addEventListener('change', applyFiltersAndSort);
        filterClass.addEventListener('change', applyFiltersAndSort);
        sortType.addEventListener('change', applyFiltersAndSort);
        searchInput.addEventListener('input', applyFiltersAndSort);

        applyFiltersAndSort();
    });

    function naturalSort(a, b) {
        const re = /(\d+)|(\D+)/g;
        const aParts = String(a).match(re) || [];
        const bParts = String(b).match(re) || [];

        for (let i = 0, len = Math.max(aParts.length, bParts.length); i < len; i++) {
            const aPart = aParts[i] || '';
            const bPart = bParts[i] || '';

            if (!isNaN(aPart) && !isNaN(bPart)) {
                const aNum = parseInt(aPart, 10);
                const bNum = parseInt(bPart, 10);
                if (aNum !== bNum) return aNum - bNum;
            } else {
                if (aPart !== bPart) return aPart.localeCompare(bPart, 'vi');
            }
        }
        return 0;
    }

    function applyFiltersAndSort() {
        const studentListBody = document.getElementById('studentListBody');
        const allRows = Array.from(studentListBody.querySelectorAll('tr[id^="student-row-"]'));
        const emptyRow = document.getElementById('emptyStudentRow');

        const filterTypeVal = document.getElementById('filterType').value;
        const filterGradeVal = document.getElementById('filterGrade').value;
        const filterClassVal = document.getElementById('filterClass').value;
        const sortTypeVal = document.getElementById('sortType').value;
        const searchKeyword = document.getElementById('searchInput').value.trim().toLowerCase();

        let visibleRows = [];

        // Lọc
        allRows.forEach(row => {
            let isVisible = true;

            if (filterTypeVal === 'grade') {
                if (row.dataset.grade !== filterGradeVal) isVisible = false;
            } else if (filterTypeVal === 'class') {
                if (filterClassVal && row.dataset.class !== filterClassVal) isVisible = false;
            }

            if (isVisible && searchKeyword) {
                const matchName = row.dataset.fullname?.includes(searchKeyword);
                const matchClass = row.dataset.class?.toLowerCase().includes(searchKeyword);
                const matchMa = row.dataset.mahocsinh?.includes(searchKeyword);
                const matchSbd = row.dataset.sbd?.toLowerCase().includes(searchKeyword);
                if (!matchName && !matchClass && !matchMa && !matchSbd) {
                    isVisible = false;
                }
            }

            row.style.display = isVisible ? '' : 'none';
            if (isVisible) visibleRows.push(row);
        });

        // Sắp xếp
        visibleRows.sort((a, b) => {
            let valA, valB;
            switch (sortTypeVal) {
                case 'stt':
                    return parseInt(a.dataset.stt, 10) - parseInt(b.dataset.stt, 10);
                case 'class':
                    const classCmp = naturalSort(a.dataset.class, b.dataset.class);
                    if (classCmp !== 0) return classCmp;
                    return (a.dataset.name || '').localeCompare(b.dataset.name || '', 'vi');
                case 'name':
                    return (a.dataset.name || '').localeCompare(b.dataset.name || '', 'vi');
                case 'sbd':
                    valA = a.dataset.sbd || '0';
                    valB = b.dataset.sbd || '0';
                    return valA.localeCompare(valB, undefined, { numeric: true });
                default:
                    return 0;
            }
        });

        // Cập nhật lại DOM
        visibleRows.forEach((row, index) => {
            const sttCell = row.querySelector('.stt-cell');
            if (sttCell) sttCell.textContent = index + 1;
            studentListBody.appendChild(row);
        });

        const badge = document.getElementById('studentCountBadge');
        if (badge) badge.textContent = visibleRows.length;

        if (emptyRow) {
            if (visibleRows.length === 0 && allRows.length > 0) {
                emptyRow.style.display = '';
                emptyRow.querySelector('td').textContent = 'Không tìm thấy thí sinh nào khớp với bộ lọc / từ khóa tìm kiếm.';
                studentListBody.appendChild(emptyRow);
            } else if (allRows.length === 0) {
                emptyRow.style.display = '';
                emptyRow.querySelector('td').innerHTML = 'Chưa có thí sinh nào trong kỳ thi này. Bấm <strong>"Thêm Học Sinh"</strong> để bắt đầu.';
                studentListBody.appendChild(emptyRow);
            } else {
                emptyRow.style.display = 'none';
            }
        }
    }

    // ===== THAO TÁC XÓA 1 HỌC SINH (SWEETALERT2) =====
    async function removeStudent(kthsId, studentName) {
        AppSwal.fire({
            title: 'Xóa Thí Sinh?',
            text: `Bạn có chắc chắn muốn xóa thí sinh "${studentName}" khỏi kỳ thi này?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Xác nhận xóa',
            cancelButtonText: 'Hủy',
            customClass: {
                popup: 'bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.15)] border border-slate-200 py-6 px-6',
                title: 'text-red-600 font-bold text-xl mt-0',
                htmlContainer: 'text-slate-600 font-medium text-[14.5px] mt-2 mb-2',
                actions: 'flex justify-center gap-3 w-full mt-6',
                confirmButton: 'bg-red-600 text-white rounded-lg px-6 py-2 font-medium shadow-sm hover:bg-red-700 transition outline-none',
                cancelButton: 'bg-white text-slate-600 rounded-lg px-6 py-2 font-medium shadow-sm border border-slate-300 hover:bg-slate-50 transition outline-none',
                icon: 'scale-[0.85] my-2'
            },
            buttonsStyling: false
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const response = await fetch('/thidua/api/exam-manage-students', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'remove', kths_id: kthsId, ky_thi_id: CURRENT_KY_THI_ID })
                    });
                    const res = await response.json();
                    if (res.success) {
                        const row = document.getElementById(`student-row-${kthsId}`);
                        if (row) row.remove();
                        showToast(res.message || 'Đã xóa thí sinh thành công', 'success');
                        applyFiltersAndSort();
                    } else {
                        showToast(res.message || 'Lỗi khi xóa thí sinh', 'error');
                    }
                } catch (e) {
                    showToast('Lỗi kết nối máy chủ.', 'error');
                }
            }
        });
    }

    // ===== THAO TÁC XÓA TOÀN BỘ DANH SÁCH =====
    async function deleteAllStudents() {
        AppSwal.fire({
            title: 'Xóa Toàn Bộ Danh Sách?',
            text: 'Bạn có chắc chắn muốn XÓA TOÀN BỘ học sinh trong kỳ thi này không? Hành động này không thể hoàn tác!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Đồng ý xóa sạch',
            cancelButtonText: 'Hủy',
            customClass: {
                popup: 'bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.15)] border border-slate-200 py-6 px-6',
                title: 'text-red-600 font-bold text-xl mt-0',
                htmlContainer: 'text-slate-600 font-medium text-[14.5px] mt-2 mb-2',
                actions: 'flex justify-center gap-3 w-full mt-6',
                confirmButton: 'bg-red-600 text-white rounded-lg px-6 py-2 font-medium shadow-sm hover:bg-red-700 transition outline-none',
                cancelButton: 'bg-white text-slate-600 rounded-lg px-6 py-2 font-medium shadow-sm border border-slate-300 hover:bg-slate-50 transition outline-none',
                icon: 'scale-[0.85] my-2'
            },
            buttonsStyling: false
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const response = await fetch('/thidua/api/exam-manage-students', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'remove_all', ky_thi_id: CURRENT_KY_THI_ID })
                    });
                    const res = await response.json();
                    if (res.success) {
                        showToast(res.message || 'Đã xóa toàn bộ thí sinh.', 'success');
                        setTimeout(() => location.reload(), 800);
                    } else {
                        showToast(res.message || 'Lỗi khi xóa.', 'error');
                    }
                } catch (e) {
                    showToast('Lỗi kết nối máy chủ.', 'error');
                }
            }
        });
    }

    // ===== THÊM HỌC SINH VÀO KỲ THI =====
    async function confirmAddStudents() {
        const type = document.getElementById('addModalType').value;
        let value = null;

        if (type === 'grade') {
            value = document.getElementById('addModalGradeSelect').value;
        } else if (type === 'class') {
            value = document.getElementById('addModalClassSelect').value;
            if (!value) {
                showToast('Vui lòng chọn một lớp học.', 'warning');
                return;
            }
        }

        const btn = document.getElementById('btnConfirmAddStudents');
        btn.disabled = true;
        btn.innerHTML = '<span class="inline-block w-4 h-4 border-2 border-slate-900 border-t-transparent rounded-full animate-spin"></span> <span>Đang thêm...</span>';

        try {
            const response = await fetch('/thidua/api/exam-manage-students', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'add',
                    ky_thi_id: CURRENT_KY_THI_ID,
                    add_type: type,
                    value: value
                })
            });

            const res = await response.json();
            if (res.success) {
                closeModal('addStudentsModal');
                showToast(res.message || 'Thêm học sinh thành công!', 'success');
                setTimeout(() => location.reload(), 800);
            } else {
                showToast(res.message || 'Lỗi khi thêm học sinh.', 'error');
            }
        } catch (e) {
            showToast('Lỗi kết nối máy chủ.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-check-lg" viewBox="0 0 16 16"><path d="M12.736 3.97a.75.75 0 0 1 1.02 1.084l-7.25 7.5a.75.75 0 0 1-1.08 0L2.21 8.804a.75.75 0 0 1 1.08-1.084l2.74 2.74z"/></svg>
                <span>Xác Nhận Thêm</span>
            `;
        }
    }

    // ===== TẠO SỐ BÁO DANH TỰ ĐỘNG =====
    async function confirmGenerateSbd() {
        const sortMethod = document.getElementById('sbdSortMethod').value;
        const btn = document.getElementById('btnConfirmGenerateSbd');
        btn.disabled = true;
        btn.innerHTML = '<span class="inline-block w-4 h-4 border-2 border-slate-900 border-t-transparent rounded-full animate-spin"></span> <span>Đang đánh số...</span>';

        try {
            const response = await fetch('/thidua/api/exam-generate-sbd', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    ky_thi_id: CURRENT_KY_THI_ID,
                    sort_method: sortMethod
                })
            });

            const res = await response.json();
            if (res.success) {
                closeModal('generateSbdModal');
                showToast(res.message || 'Đã sinh Số Báo Danh tự động thành công!', 'success');
                setTimeout(() => location.reload(), 900);
            } else {
                showToast(res.message || 'Lỗi khi sinh SBD.', 'error');
            }
        } catch (e) {
            showToast('Lỗi kết nối máy chủ.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-play-fill" viewBox="0 0 16 16"><path d="m11.596 8.697-6.363 3.692c-.54.313-1.233-.066-1.233-.697V4.308c0-.63.692-1.01 1.233-.696l6.363 3.692a.802.802 0 0 1 0 1.393"/></svg>
                <span>Bắt Đầu Đánh Số</span>
            `;
        }
    }

    // ===== NHẬP EXCEL (IMPORT MODAL) =====
    function openImportModal(type) {
        document.getElementById('importTypeHidden').value = type;
        const fileInput = document.getElementById('excelFile');
        fileInput.value = '';
        document.getElementById('importResult').innerHTML = '';
        document.getElementById('importProgress').classList.add('hidden');

        const titleMap = {
            'moet': 'Nhập Mã MOET Từ Excel',
            'sbd': 'Nhập Số Báo Danh (SBD) Từ Excel',
            'subjects': 'Nhập Tổ Hợp Môn Thi Từ Excel'
        };
        const helpMap = {
            'moet': 'Cập nhật Mã Định Danh Bộ Giáo Dục (Mã MOET) cho học sinh theo file mẫu.',
            'sbd': 'Cập nhật danh sách Số Báo Danh (SBD) đã được chuẩn bị sẵn từ file Excel.',
            'subjects': 'Cập nhật các môn thi đăng ký / tổ hợp môn tự chọn cho thí sinh theo file mẫu.'
        };

        document.getElementById('importModalTitle').innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="currentColor" class="bi bi-file-earmark-arrow-up-fill text-emerald-600" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M6.354 9.854a.5.5 0 0 1-.708-.708l2-2a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1-.708.708L8.5 8.707V12.5a.5.5 0 0 1-1 0V8.707z"/></svg>
            ${titleMap[type] || 'Nhập Dữ Liệu Excel'}
        `;
        document.getElementById('importHelpText').textContent = helpMap[type] || '';

        openModal('importExcelModal');
    }

    async function submitImportExcel() {
        const fileInput = document.getElementById('excelFile');
        if (!fileInput.files || fileInput.files.length === 0) {
            showToast('Vui lòng chọn file Excel.', 'warning');
            return;
        }

        const formData = new FormData(document.getElementById('importForm'));
        formData.append('ky_thi_id', CURRENT_KY_THI_ID);

        const btn = document.getElementById('btnSubmitImport');
        const progressDiv = document.getElementById('importProgress');
        const resultDiv = document.getElementById('importResult');

        btn.disabled = true;
        progressDiv.classList.remove('hidden');
        resultDiv.innerHTML = '';

        try {
            const response = await fetch('/thidua/api/exam-import-data', {
                method: 'POST',
                body: formData
            });

            const text = await response.text();
            let res;
            try {
                res = JSON.parse(text);
            } catch (err) {
                console.error('Raw server response:', text);
                const detail = text ? text.substring(0, 200) : `Không có phản hồi (Mã HTTP: ${response.status} ${response.statusText})`;
                throw new Error(detail);
            }

            if (response.ok && res.success) {
                let html = `<div class="p-3 my-2 rounded-lg bg-green-50 text-green-800 border border-green-200 font-semibold">${escapeHtml(res.message || 'Nhập thành công!')}</div>`;
                
                const errors = Array.isArray(res.errors) ? res.errors : [];
                if (errors.length > 0) {
                    const topErrors = errors.slice(0, 5).map(e => `<li>${escapeHtml(e)}</li>`).join('');
                    const moreText = errors.length > 5 ? `<div class="text-[11px] text-amber-700 mt-1">... và ${errors.length - 5} lỗi khác.</div>` : '';
                    html += `<div class="p-3 my-2 rounded-lg bg-amber-50 text-amber-800 border border-amber-200"><strong class="font-bold">Các dòng chưa import:</strong><ul class="list-disc pl-4 mt-1 space-y-0.5 text-xs">${topErrors}</ul>${moreText}</div>`;
                }

                resultDiv.innerHTML = html;

                if (errors.length === 0) {
                    showToast('Nhập dữ liệu thành công!', 'success');
                    setTimeout(() => {
                        closeModal('importExcelModal');
                        location.reload();
                    }, 1200);
                } else {
                    btn.disabled = false;
                }
            } else {
                throw new Error(res.message || 'Không thể xử lý file Excel.');
            }
        } catch (e) {
            resultDiv.innerHTML = `<div class="p-3 my-2 rounded-lg bg-red-50 text-red-800 border border-red-200 font-medium">Lỗi: ${escapeHtml(e.message)}</div>`;
            btn.disabled = false;
        } finally {
            progressDiv.classList.add('hidden');
        }
    }
</script>
