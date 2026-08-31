<?php
// File: src/views/xem_diem_thi.php
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
        max-width: 420px;
        border: 1px solid;
    }
    .toast-success { background: #f0fdf4; color: #166534; border-color: #86efac; }
    .toast-error { background: #fef2f2; color: #991b1b; border-color: #fca5a5; }
    .toast-warning { background: #fffbeb; color: #92400e; border-color: #fcd34d; }
    .toast-info { background: #eff6ff; color: #1e40af; border-color: #93c5fd; }

    @keyframes toastIn { from { opacity:0; transform: translateX(50px); } to { opacity:1; transform: translateX(0); } }
    @keyframes toastOut { to { opacity:0; transform: translateX(50px); } }

    .score-input {
        width: 100%;
        min-width: 48px;
        max-width: 60px;
        padding: 4px 2px;
        text-align: center;
        font-size: 12.5px;
        font-weight: 600;
        border: 1px solid transparent;
        background: transparent;
        border-radius: 4px;
        transition: all 0.15s;
    }
    .score-input:hover {
        background: #ffffff;
        border-color: #cbd5e1;
    }
    .score-input:focus {
        background: #ffffff;
        border-color: #224397;
        outline: none;
        box-shadow: 0 0 0 2px rgba(34, 67, 151, 0.15);
    }
    .score-input.changed {
        background: #fef3c7 !important;
        border-color: #f59e0b !important;
        color: #92400e !important;
    }
</style>

<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 py-6 min-h-screen">
    
    <!-- Top Breadcrumb & Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 pb-4 border-b border-[#224397]/25 gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-1.5">
                <a href="/thidua/admin/exam-list" class="hover:text-[#224397] transition flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-chevron-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0"/></svg>
                    Quản Lý Kỳ Thi
                </a>
                <span>/</span>
                <span class="text-[#224397] font-semibold">Bảng Điểm Thi</span>
            </div>
            <h1 class="text-xl font-bold text-[#224397] flex items-center gap-2.5 uppercase tracking-wide m-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-clipboard2-data-fill text-[#FAB723]" viewBox="0 0 16 16"><path d="M10 .5a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5.5.5 0 0 1-.5.5.5.5 0 0 0-.5.5V2a.5.5 0 0 0 .5.5h5A.5.5 0 0 0 11 2v-.5a.5.5 0 0 0-.5-.5.5.5 0 0 1-.5-.5"/><path d="M4.085 1H3.5A1.5 1.5 0 0 0 2 2.5v12A1.5 1.5 0 0 0 3.5 16h9a1.5 1.5 0 0 0 1.5-1.5v-12A1.5 1.5 0 0 0 12.5 1h-.585q.084.236.085.5V2a1.5 1.5 0 0 1-1.5 1.5h-5A1.5 1.5 0 0 1 4 2v-.5q.001-.264.085-.5M10 7a1 1 0 1 1 2 0v5a1 1 0 1 1-2 0zm-6 4a1 1 0 1 1 2 0v1a1 1 0 1 1-2 0zm4-3a1 1 0 0 1 1 1v3a1 1 0 1 1-2 0V9a1 1 0 0 1 1-1"/></svg>
                QUẢN LÝ ĐIỂM THI: <?= htmlspecialchars($ky_thi_info['ten_ky_thi'] ?? 'Chưa chọn kỳ thi') ?>
            </h1>
        </div>
        
        <div class="flex items-center gap-2.5 flex-wrap">
            <!-- Exam Selector Dropdown -->
            <?php if (!empty($all_exams)): ?>
                <div class="flex items-center gap-1.5">
                    <span class="text-xs text-slate-500 font-medium">Kỳ thi:</span>
                    <select onchange="location.href='/thidua/admin/quan-ly-diem-thi?id=' + this.value" class="px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-bold text-[#224397] focus:outline-none focus:border-[#224397] shadow-sm">
                        <?php foreach ($all_exams as $ex): ?>
                            <option value="<?= $ex['id'] ?>" <?= ((int)$ex['id'] === (int)$ky_thi_id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ex['ten_ky_thi']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <!-- Import / Export Dropdown (UI_SYNC_STANDARDS.md) -->
            <?php if ($ky_thi_info): ?>
                <div class="relative inline-block text-left group z-50">
                    <button type="button" class="px-3.5 py-2 bg-white border border-[#224397]/25 rounded-lg text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all font-medium flex items-center gap-1.5 text-xs shadow-sm whitespace-nowrap">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-file-earmark-spreadsheet-fill text-emerald-600 group-hover:text-white transition" viewBox="0 0 16 16"><path d="M6 12v-2h3v2z"/><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M3 9h10v5a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1zm1 1v2h1v-2zm0 3v1h1v-1zm2 1h3v-1H6zm4 0h1v-1h-1zm1-2h-1v-2h1z"/></svg>
                        Nhập / Xuất Điểm
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-chevron-down" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708"/></svg>
                    </button>
                    <ul class="absolute right-0 mt-1 w-52 bg-white rounded-lg shadow-xl border border-slate-200 focus:outline-none opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-[100] transform origin-top-right scale-95 group-hover:scale-100 py-1 text-xs">
                        <li>
                            <a class="flex items-center gap-2 px-3 py-2 text-slate-700 hover:bg-blue-50 hover:text-[#224397] transition" href="/thidua/admin/xuat-mau-diem-thi?id=<?= $ky_thi_id ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-download text-blue-600" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/><path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/></svg>
                                <span>Tải Mẫu Nhập Điểm (.xlsx)</span>
                            </a>
                        </li>
                        <li>
                            <a class="flex items-center gap-2 px-3 py-2 text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 transition cursor-pointer" onclick="openImportScoreModal()">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-upload text-emerald-600" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/><path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708z"/></svg>
                                <span>Nhập Điểm Từ Excel</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <button type="button" id="btnSaveScores" class="px-3.5 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-medium flex items-center gap-1.5 text-xs shadow-sm hover:scale-105 transition-all" onclick="saveAllChangedScores()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-floppy2-fill" viewBox="0 0 16 16"><path d="M12 2h-2v3h2z"/><path d="M1.5 0A1.5 1.5 0 0 0 0 1.5v13A1.5 1.5 0 0 0 1.5 16h13a1.5 1.5 0 0 0 1.5-1.5V4.707A1.5 1.5 0 0 0 15.293 3.5L12.5.707A1.5 1.5 0 0 0 11.293 0zm11 1a.5.5 0 0 1 .354.146l2.793 2.793a.5.5 0 0 1 .146.354V14.5a.5.5 0 0 1-.5.5H2a.5.5 0 0 1-.5-.5v-13a.5.5 0 0 1 .5-.5H4v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1z"/></svg>
                    <span>Lưu Điểm Chỉnh Sửa</span>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$ky_thi_info): ?>
        <div class="bg-white rounded-xl shadow-sm border border-[#224397]/25 p-12 text-center">
            <div class="w-16 h-16 bg-blue-50 text-[#224397] rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-bold">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-calendar-x" viewBox="0 0 16 16"><path d="M6.146 7.146a.5.5 0 0 1 .708 0L8 8.293l1.146-1.147a.5.5 0 1 1 .708.708L8.707 9l1.147 1.146a.5.5 0 0 1-.708.708L8 9.707l-1.146 1.147a.5.5 0 0 1-.708-.708L7.293 9 6.146 7.854a.5.5 0 0 1 0-.708"/><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/></svg>
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-2">Chưa có kỳ thi nào trong năm học này</h3>
            <p class="text-xs text-slate-500 mb-4">Vui lòng tạo kỳ thi trước trong mục Quản Lý Kỳ Thi để tiến hành nhập điểm.</p>
            <a href="/thidua/admin/exam-list" class="inline-flex items-center gap-2 px-4 py-2 bg-[#224397] text-white rounded-lg text-xs font-bold shadow-sm hover:bg-[#FAB723] transition">
                Tới Quản Lý Kỳ Thi
            </a>
        </div>
    <?php else: ?>

        <!-- Filter & Search Bar (UI_SYNC_STANDARDS.md) -->
        <div class="bg-white rounded-xl shadow-sm border border-[#224397]/25 p-4 mb-6">
            <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
                
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-bold text-slate-600">Lọc theo:</span>
                    
                    <select id="filterClass" class="px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-slate-700 font-semibold focus:outline-none focus:border-[#224397]">
                        <option value="">-- Tất cả các lớp --</option>
                        <?php foreach ($ds_lop_hoc as $lh): ?>
                            <option value="<?= htmlspecialchars($lh['ten_lop']) ?>"><?= htmlspecialchars($lh['ten_lop']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select id="sortType" class="px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-slate-700 font-semibold focus:outline-none focus:border-[#224397]">
                        <option value="default">Sắp xếp: Mặc định (Theo lớp + Tên)</option>
                        <option value="name_asc">Tên học sinh (A - Z)</option>
                        <option value="sbd_asc">Số báo danh (Tăng dần)</option>
                        <option value="dtb_desc">Điểm trung bình (Cao ➔ Thấp)</option>
                    </select>
                </div>

                <div class="relative w-full sm:w-72">
                    <input type="text" id="searchInput" placeholder="Tìm tên, mã HS, SBD..." class="w-full pl-9 pr-3 py-2 rounded-lg border border-slate-300 focus:border-[#224397] focus:outline-none text-slate-700 text-xs font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-search absolute left-3 top-2.5 text-slate-400" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/></svg>
                </div>

            </div>
        </div>

        <!-- Score Table Card -->
        <div class="bg-white rounded-xl shadow-sm border border-[#224397]/25 overflow-hidden">
            <div class="bg-slate-50 px-5 py-3.5 border-b border-[#224397]/25 font-semibold text-[#224397] flex items-center justify-between text-sm uppercase">
                <span class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="currentColor" class="bi bi-table text-[#FAB723]" viewBox="0 0 16 16"><path d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm15 2h-4v3h4zm0 4h-4v3h4zm0 4h-4v3h3a1 1 0 0 0 1-1zm-5 3v-3H6v3zm-5 0v-3H1v2a1 1 0 0 0 1 1zm-4-4h4V8H1zm0-4h4V4H1zm5-3v3h4V1zM5 1v3H1V2a1 1 0 0 1 1-1z"/></svg>
                    DANH SÁCH ĐIỂM THI THÍ SINH
                </span>
                <span class="text-xs font-normal text-slate-500 normal-case">
                    Tổng số: <strong id="totalDisplayedStudents" class="text-[#224397]"><?= count($ds_diem_thi) ?></strong> thí sinh
                </span>
            </div>

            <div class="overflow-x-auto w-full">
                <table class="w-full text-left text-[12.5px] text-slate-600 border-collapse">
                    <thead>
                        <tr class="bg-slate-100/80 text-[#224397] uppercase text-[11px] font-bold tracking-wide border-b border-[#224397]/25 whitespace-nowrap">
                            <th class="p-2.5 border-r border-slate-200 text-center w-10">STT</th>
                            <th class="p-2.5 border-r border-slate-200 text-center w-20">SBD</th>
                            <th class="p-2.5 border-r border-slate-200 min-w-[140px]">Họ và Tên</th>
                            <th class="p-2.5 border-r border-slate-200 text-center w-16">Lớp</th>
                            
                            <!-- Subject Score Headers -->
                            <th class="p-2 border-r border-slate-200 text-center w-14 text-blue-800">Toán</th>
                            <th class="p-2 border-r border-slate-200 text-center w-14 text-blue-800">Văn</th>
                            <th class="p-2 border-r border-slate-200 text-center w-14 text-slate-700">Lý</th>
                            <th class="p-2 border-r border-slate-200 text-center w-14 text-slate-700">Hóa</th>
                            <th class="p-2 border-r border-slate-200 text-center w-14 text-slate-700">Sinh</th>
                            <th class="p-2 border-r border-slate-200 text-center w-14 text-slate-700">Sử</th>
                            <th class="p-2 border-r border-slate-200 text-center w-14 text-slate-700">Địa</th>
                            <th class="p-2 border-r border-slate-200 text-center w-14 text-slate-700">GDKTPL</th>
                            <th class="p-2 border-r border-slate-200 text-center w-14 text-slate-700">N.Ngữ</th>
                            <th class="p-2 border-r border-slate-200 text-center w-14 text-slate-700">CN-NN</th>
                            
                            <th class="p-2 border-r border-slate-200 text-center w-16 font-bold text-amber-700 bg-amber-50/50">ĐTB</th>
                            <th class="p-2 border-r border-slate-200 text-center w-16 font-bold text-purple-700 bg-purple-50/50">Điểm XT</th>
                            <th class="p-2 text-center w-20 font-bold text-emerald-700 bg-emerald-50/50">Kết Quả</th>
                        </tr>
                    </thead>
                    <tbody id="scoreTableBody">
                        <?php if (empty($ds_diem_thi)): ?>
                            <tr id="emptyRow">
                                <td colspan="17" class="text-center text-slate-400 p-8 italic">
                                    Chưa có thí sinh nào trong kỳ thi này.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($ds_diem_thi as $index => $row): 
                                $kths_id = (int)$row['ky_thi_hoc_sinh_id'];
                                $full_name = trim($row['ho_dem'] . ' ' . $row['ten']);
                            ?>
                                <tr id="row-<?= $kths_id ?>" class="hover:bg-blue-50/40 transition-colors duration-150 border-b border-slate-200"
                                    data-kths-id="<?= $kths_id ?>"
                                    data-name="<?= htmlspecialchars($full_name) ?>"
                                    data-sbd="<?= htmlspecialchars($row['so_bao_danh'] ?? '') ?>"
                                    data-ma-hs="<?= htmlspecialchars($row['ma_hoc_sinh'] ?? '') ?>"
                                    data-class="<?= htmlspecialchars($row['ten_lop'] ?? '') ?>"
                                    data-dtb="<?= (float)($row['dtb_mon'] ?? 0) ?>">
                                    
                                    <td class="p-2 border-r border-slate-200 text-center text-slate-400 text-[11px]"><?= $index + 1 ?></td>
                                    <td class="p-2 border-r border-slate-200 text-center font-bold text-[#224397]"><?= htmlspecialchars($row['so_bao_danh'] ?: '---') ?></td>
                                    <td class="p-2 border-r border-slate-200 font-semibold text-slate-800 whitespace-nowrap"><?= htmlspecialchars($full_name) ?></td>
                                    <td class="p-2 border-r border-slate-200 text-center font-medium text-slate-600"><?= htmlspecialchars($row['ten_lop']) ?></td>

                                    <!-- Score Input Cells -->
                                    <td class="p-1 border-r border-slate-200 text-center"><input type="text" class="score-input" data-col="diem_toan" value="<?= $row['diem_toan'] ?? '' ?>" onchange="markChanged(this, <?= $kths_id ?>)"></td>
                                    <td class="p-1 border-r border-slate-200 text-center"><input type="text" class="score-input" data-col="diem_van" value="<?= $row['diem_van'] ?? '' ?>" onchange="markChanged(this, <?= $kths_id ?>)"></td>
                                    <td class="p-1 border-r border-slate-200 text-center"><input type="text" class="score-input" data-col="diem_ly" value="<?= $row['diem_ly'] ?? '' ?>" onchange="markChanged(this, <?= $kths_id ?>)"></td>
                                    <td class="p-1 border-r border-slate-200 text-center"><input type="text" class="score-input" data-col="diem_hoa" value="<?= $row['diem_hoa'] ?? '' ?>" onchange="markChanged(this, <?= $kths_id ?>)"></td>
                                    <td class="p-1 border-r border-slate-200 text-center"><input type="text" class="score-input" data-col="diem_sinh" value="<?= $row['diem_sinh'] ?? '' ?>" onchange="markChanged(this, <?= $kths_id ?>)"></td>
                                    <td class="p-1 border-r border-slate-200 text-center"><input type="text" class="score-input" data-col="diem_su" value="<?= $row['diem_su'] ?? '' ?>" onchange="markChanged(this, <?= $kths_id ?>)"></td>
                                    <td class="p-1 border-r border-slate-200 text-center"><input type="text" class="score-input" data-col="diem_dia" value="<?= $row['diem_dia'] ?? '' ?>" onchange="markChanged(this, <?= $kths_id ?>)"></td>
                                    <td class="p-1 border-r border-slate-200 text-center"><input type="text" class="score-input" data-col="diem_gdktpl" value="<?= $row['diem_gdktpl'] ?? '' ?>" onchange="markChanged(this, <?= $kths_id ?>)"></td>
                                    <td class="p-1 border-r border-slate-200 text-center"><input type="text" class="score-input" data-col="diem_ngoai_ngu" value="<?= $row['diem_ngoai_ngu'] ?? '' ?>" onchange="markChanged(this, <?= $kths_id ?>)"></td>
                                    <td class="p-1 border-r border-slate-200 text-center"><input type="text" class="score-input" data-col="diem_cn_nn" value="<?= $row['diem_cn_nn'] ?? '' ?>" onchange="markChanged(this, <?= $kths_id ?>)"></td>

                                    <!-- Calculated Summary Columns -->
                                    <td class="p-1 border-r border-slate-200 text-center font-bold text-amber-700 bg-amber-50/20"><input type="text" class="score-input font-bold text-amber-800" data-col="dtb_mon" value="<?= $row['dtb_mon'] ?? '' ?>" onchange="markChanged(this, <?= $kths_id ?>)"></td>
                                    <td class="p-1 border-r border-slate-200 text-center font-bold text-purple-700 bg-purple-50/20"><input type="text" class="score-input font-bold text-purple-800" data-col="diem_xt_tn" value="<?= $row['diem_xt_tn'] ?? '' ?>" onchange="markChanged(this, <?= $kths_id ?>)"></td>
                                    <td class="p-1 text-center font-bold text-emerald-700 bg-emerald-50/20"><input type="text" class="score-input font-bold text-emerald-800 !max-w-[70px]" data-col="ket_qua" value="<?= $row['ket_qua'] ?? '' ?>" onchange="markChanged(this, <?= $kths_id ?>)"></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php endif; ?>

</div>

<!-- ========================================================================= -->
<!-- MODAL: NHẬP ĐIỂM THI TỪ EXCEL -->
<!-- ========================================================================= -->
<div id="importScoreModal" class="fixed inset-0 z-[10005] hidden items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0" onclick="closeModal('importScoreModal')">
    <div class="bg-white rounded-xl shadow-2xl w-[520px] max-w-[92%] flex flex-col overflow-hidden border border-slate-300 transform transition-all duration-300 scale-95 translate-y-4 opacity-0 modal-content-box" onclick="event.stopPropagation()">
        
        <form id="importScoreForm" onsubmit="event.preventDefault(); submitImportScore();">
            <input type="hidden" name="ky_thi_id" value="<?= (int)($ky_thi_id ?? 0) ?>">
            
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-slate-50">
                <h5 class="text-lg font-bold text-[#224397] flex items-center gap-2 m-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="currentColor" class="bi bi-file-earmark-arrow-up-fill text-emerald-600" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M6.354 9.854a.5.5 0 0 1-.708-.708l2-2a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1-.708.708L8.5 8.707V12.5a.5.5 0 0 1-1 0V8.707z"/></svg>
                    Nhập Điểm Thi Từ File Excel
                </h5>
                <button type="button" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition" onclick="closeModal('importScoreModal')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg>
                </button>
            </div>
            
            <div class="px-6 py-5 space-y-4 text-sm text-slate-700">
                <div class="text-xs text-slate-600 bg-blue-50 p-3 rounded-lg border border-blue-200 space-y-1">
                    <p><strong>Hướng dẫn:</strong> Tải file mẫu <a href="/thidua/admin/xuat-mau-diem-thi?id=<?= $ky_thi_id ?>" class="text-[#224397] font-bold underline">tại đây</a>, điền điểm thi tương ứng cho thí sinh rồi tải lên.</p>
                </div>
                
                <div>
                    <label for="scoreExcelFile" class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Chọn Tệp Excel (.xlsx, .xls) <span class="text-red-500">*</span></label>
                    <input type="file" id="scoreExcelFile" name="excel_file" accept=".xlsx, .xls" class="w-full text-xs text-slate-600 file:mr-3 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[#224397] file:text-white hover:file:bg-[#FAB723] hover:file:text-white file:cursor-pointer border border-slate-300 rounded-lg p-1.5 bg-slate-50" required>
                </div>
                
                <div id="importScoreProgress" class="hidden text-center py-2">
                    <div class="inline-block w-6 h-6 border-2 border-[#224397] border-t-transparent rounded-full animate-spin"></div>
                    <p class="text-xs text-slate-500 font-medium mt-1">Đang xử lý và cập nhật điểm...</p>
                </div>
                <div id="importScoreResult" class="text-xs"></div>
            </div>
            
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-2">
                <button type="button" class="px-4 py-2 text-[13px] font-medium text-gray-600 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 transition" onclick="closeModal('importScoreModal')">Đóng</button>
                <button type="submit" class="px-4 py-2 text-[13px] font-bold text-slate-900 bg-[#FAB723] border border-[#FAB723] rounded-lg shadow-sm hover:bg-[#e5a61d] transition flex items-center gap-1.5" id="btnSubmitImportScore">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-upload" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/><path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708z"/></svg>
                    <span>Tiến Hành Nhập Điểm</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Toast Container -->
<div id="toast-container"></div>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>

<script>
    const changedScores = {};

    function markChanged(input, kthsId) {
        input.classList.add('changed');
        const col = input.getAttribute('data-col');
        const val = input.value.trim();

        if (!changedScores[kthsId]) {
            changedScores[kthsId] = {};
        }
        changedScores[kthsId][col] = val;
    }

    async function saveAllChangedScores() {
        const studentIds = Object.keys(changedScores);
        if (studentIds.length === 0) {
            showToast('Không có điểm số nào thay đổi cần lưu.', 'info');
            return;
        }

        const btn = document.getElementById('btnSaveScores');
        btn.disabled = true;
        btn.innerHTML = '<span class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span> <span>Đang lưu...</span>';

        try {
            const res = await fetch('/thidua/api/luu-diem-thi-tay', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    ky_thi_id: <?= (int)($ky_thi_id ?? 0) ?>,
                    scores: changedScores
                })
            });
            const data = await res.json();
            if (data.success) {
                showToast(data.message || 'Lưu điểm thi thành công!', 'success');
                document.querySelectorAll('.score-input.changed').forEach(el => el.classList.remove('changed'));
                for (const k in changedScores) delete changedScores[k];
            } else {
                showToast(data.message || 'Lỗi khi lưu điểm.', 'error');
            }
        } catch (e) {
            showToast('Lỗi kết nối máy chủ.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-floppy2-fill" viewBox="0 0 16 16"><path d="M12 2h-2v3h2z"/><path d="M1.5 0A1.5 1.5 0 0 0 0 1.5v13A1.5 1.5 0 0 0 1.5 16h13a1.5 1.5 0 0 0 1.5-1.5V4.707A1.5 1.5 0 0 0 15.293 3.5L12.5.707A1.5 1.5 0 0 0 11.293 0zm11 1a.5.5 0 0 1 .354.146l2.793 2.793a.5.5 0 0 1 .146.354V14.5a.5.5 0 0 1-.5.5H2a.5.5 0 0 1-.5-.5v-13a.5.5 0 0 1 .5-.5H4v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1z"/></svg>
                <span>Lưu Điểm Chỉnh Sửa</span>
            `;
        }
    }

    // ===== MODAL ANIMATION HELPERS (UI_SYNC_STANDARDS.md) =====
    function openModal(id) {
        const modal = document.getElementById(id);
        const content = modal.querySelector('.modal-content-box');
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
        void modal.offsetWidth;
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

    function showToast(message, type = 'success', duration = 3000) {
        const container = document.getElementById('toast-container');
        if (!container) return;
        
        const toast = document.createElement('div');
        toast.className = `toast-item toast-${type}`;
        
        let icon = '';
        if (type === 'success') icon = `<svg width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill shrink-0" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>`;
        else if (type === 'error') icon = `<svg width="16" height="16" fill="currentColor" class="bi bi-exclamation-circle-fill shrink-0" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/></svg>`;
        else icon = `<svg width="16" height="16" fill="currentColor" class="bi bi-info-circle-fill shrink-0" viewBox="0 0 16 16"><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2"/></svg>`;
        
        toast.innerHTML = `${icon} <span>${message}</span>`;
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'toastOut 0.35s forwards';
            setTimeout(() => toast.remove(), 350);
        }, duration);
    }

    function openImportScoreModal() {
        document.getElementById('scoreExcelFile').value = '';
        document.getElementById('importScoreResult').innerHTML = '';
        document.getElementById('importScoreProgress').classList.add('hidden');
        openModal('importScoreModal');
    }

    async function submitImportScore() {
        const fileInput = document.getElementById('scoreExcelFile');
        if (!fileInput.files || fileInput.files.length === 0) {
            showToast('Vui lòng chọn file Excel.', 'warning');
            return;
        }

        const formData = new FormData(document.getElementById('importScoreForm'));
        const btn = document.getElementById('btnSubmitImportScore');
        const progressDiv = document.getElementById('importScoreProgress');
        const resultDiv = document.getElementById('importScoreResult');

        btn.disabled = true;
        progressDiv.classList.remove('hidden');
        resultDiv.innerHTML = '';

        try {
            const response = await fetch('/thidua/api/nhap-diem-thi-excel', {
                method: 'POST',
                body: formData
            });

            const text = await response.text();
            let res;
            try {
                res = JSON.parse(text);
            } catch (err) {
                console.error('Raw response:', text);
                throw new Error(text || `Lỗi máy chủ (HTTP ${response.status})`);
            }

            if (res.success) {
                resultDiv.innerHTML = `<div class="p-3 my-2 rounded-lg bg-green-50 text-green-800 border border-green-200 font-semibold">${escapeHtml(res.message || 'Nhập điểm thành công!')}</div>`;
                showToast('Nhập điểm thành công!', 'success');
                setTimeout(() => {
                    closeModal('importScoreModal');
                    location.reload();
                }, 1200);
            } else {
                throw new Error(res.message || 'Lỗi khi nhập điểm.');
            }
        } catch (e) {
            resultDiv.innerHTML = `<div class="p-3 my-2 rounded-lg bg-red-50 text-red-800 border border-red-200 font-medium">Lỗi: ${escapeHtml(e.message)}</div>`;
            btn.disabled = false;
        } finally {
            progressDiv.classList.add('hidden');
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }

    // ===== FILTER & SORT =====
    document.addEventListener('DOMContentLoaded', function() {
        const filterClass = document.getElementById('filterClass');
        const sortType = document.getElementById('sortType');
        const searchInput = document.getElementById('searchInput');

        if (filterClass) filterClass.addEventListener('change', applyFilters);
        if (sortType) sortType.addEventListener('change', applyFilters);
        if (searchInput) searchInput.addEventListener('input', applyFilters);
    });

    function applyFilters() {
        const classVal = document.getElementById('filterClass') ? document.getElementById('filterClass').value : '';
        const sortVal = document.getElementById('sortType') ? document.getElementById('sortType').value : 'default';
        const searchVal = document.getElementById('searchInput') ? document.getElementById('searchInput').value.trim().toLowerCase() : '';

        const tbody = document.getElementById('scoreTableBody');
        const rows = Array.from(tbody.querySelectorAll('tr[id^="row-"]'));
        let count = 0;

        rows.forEach(row => {
            const rClass = row.getAttribute('data-class') || '';
            const rName = (row.getAttribute('data-name') || '').toLowerCase();
            const rSbd = (row.getAttribute('data-sbd') || '').toLowerCase();
            const rMaHs = (row.getAttribute('data-ma-hs') || '').toLowerCase();

            let matchClass = !classVal || rClass === classVal;
            let matchSearch = !searchVal || rName.includes(searchVal) || rSbd.includes(searchVal) || rMaHs.includes(searchVal);

            if (matchClass && matchSearch) {
                row.style.display = '';
                count++;
            } else {
                row.style.display = 'none';
            }
        });

        // Sorting
        const visibleRows = rows.filter(r => r.style.display !== 'none');
        if (sortVal === 'name_asc') {
            visibleRows.sort((a, b) => (a.getAttribute('data-name') || '').localeCompare(b.getAttribute('data-name') || '', 'vi'));
        } else if (sortVal === 'sbd_asc') {
            visibleRows.sort((a, b) => (a.getAttribute('data-sbd') || '').localeCompare(b.getAttribute('data-sbd') || ''));
        } else if (sortVal === 'dtb_desc') {
            visibleRows.sort((a, b) => parseFloat(b.getAttribute('data-dtb') || 0) - parseFloat(a.getAttribute('data-dtb') || 0));
        }

        visibleRows.forEach(r => tbody.appendChild(r));

        const totalEl = document.getElementById('totalDisplayedStudents');
        if (totalEl) totalEl.textContent = count;
    }
</script>
