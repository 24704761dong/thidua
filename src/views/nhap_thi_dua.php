<?php
$page_title = 'Nhập Điểm Thi Đua - ' . htmlspecialchars($tuan_hoc['ten_tuan'] ?? '');
require_once __DIR__ . '/partials/admin_header.php';

// Giả định các biến đã được nạp từ controller
$tuan_id = $tuan_id ?? 0;
$danh_sach_lop = $danh_sach_lop ?? [];
?>

<div class="flex-1 overflow-y-auto bg-transparent p-6 min-h-screen">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 border-b border-[#224397]/25 pb-4 gap-4">
            <h3 class="text-xl font-bold text-[#224397] flex items-center gap-2 uppercase tracking-wide">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16"><path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>   <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/></svg> NHẬP ĐIỂM THI ĐUA: <?= htmlspecialchars($tuan_hoc['ten_tuan'] ?? '') ?>
            </h3>
            
            <div class="flex flex-wrap items-center gap-2">
                <button id="sync-journal-btn" class="px-2 py-1 bg-sky-500 text-white rounded hover:bg-sky-600 transition-all duration-200 font-medium flex items-center justify-center gap-1 text-[11px] shadow-sm whitespace-nowrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-arrow-repeat text-base" viewBox="0 0 16 16"><path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41m-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9"/>   <path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5 5 0 0 0 8 3M3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9z"/></svg> Đồng bộ Nhật kỳ
                </button>
                
                <div class="relative group">
                    <button type="button" class="px-2 py-1 bg-emerald-600 text-white rounded hover:bg-emerald-700 transition-all duration-200 font-medium flex items-center justify-center gap-1 text-[11px] shadow-sm whitespace-nowrap cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-excel-fill text-base" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M5.884 6.68 8 9.219l2.116-2.54a.5.5 0 1 1 .768.641L8.651 10l2.233 2.68a.5.5 0 0 1-.768.64L8 10.781l-2.116 2.54a.5.5 0 0 1-.768-.641L7.349 10 5.116 7.32a.5.5 0 1 1 .768-.64"/></svg> Excel <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-chevron-down text-[10px]" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708"/></svg>
                    </button>
                    <!-- Dropdown -->
                    <div class="absolute right-0 top-full mt-1 w-48 bg-white border border-[#224397]/[45%] rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50">
                        <ul class="py-1">
                            <li><button type="button" class="w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 flex items-center gap-2" onclick="openModal('importModal')"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-upload" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>   <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708z"/></svg> Import từ Excel</button></li>
                            <li><a href="/thidua/nhap-diem-thi-dua/tai-mau?tuan_id=<?= $tuan_id ?>" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>   <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/></svg> Tải File Mẫu</a></li>
                        </ul>
                    </div>
                </div>
                
               
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-[#224397]/[45%] mb-6 overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-[#224397]/25 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="flex gap-2 items-center w-full md:w-auto">
                    <?php
                    $khoi_list = [];
                    foreach ($danh_sach_lop as $lop) {
                        if (preg_match('/^(\d+)/', trim($lop['ten_lop']), $matches)) {
                            $khoi_list[$matches[1]] = $matches[1];
                        }
                    }
                    sort($khoi_list);
                    ?>
                    <div class="relative text-slate-500 flex-1 md:flex-none">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-funnel absolute left-3 top-1/2 -translate-y-1/2" viewBox="0 0 16 16"><path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5zm1 .5v1.308l4.372 4.858A.5.5 0 0 1 7 8.5v5.306l2-.666V8.5a.5.5 0 0 1 .128-.334L13.5 3.308V2z"/></svg>
                        <select id="filterGrade" class="w-full pl-8 pr-8 py-1.5 border border-slate-300 rounded focus:outline-none focus:border-[#224397] focus:ring-1 focus:ring-[#224397] text-[13px] text-slate-700 bg-white transition-colors cursor-pointer hover:bg-slate-50 appearance-none shadow-sm">
                            <option value="">Khối: Tất cả</option>
                            <?php foreach($khoi_list as $k): ?>
                                <option value="<?= $k ?>">Khối <?= $k ?></option>
                            <?php endforeach; ?>
                        </select>
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-chevron-down absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-[10px]" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708"/></svg>
                    </div>
                    <div class="relative text-slate-500 flex-1 md:flex-none">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/></svg>
                        <input type="text" id="filterClass" placeholder="Tìm lớp..." class="w-full md:w-40 pl-8 pr-3 py-1.5 border border-slate-300 rounded focus:outline-none focus:border-[#224397] focus:ring-1 focus:ring-[#224397] text-[13px] text-slate-700 transition-colors shadow-sm">
                    </div>
                </div>
                <div class="flex items-center justify-between w-full md:w-auto gap-4">
                    <span class="hidden lg:inline-flex items-center gap-1.5 text-[13px] text-slate-500 font-medium"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-info-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>   <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/></svg> Dùng phím mũi tên hoặc Tab/Enter để di chuyển.</span>
                    <span id="status-indicator" class="inline-flex items-center gap-1.5 text-[13px] font-semibold italic text-slate-400"></span>
                </div>
            </div>
            
            <div class="overflow-x-auto w-full">
                <table class="w-full text-center text-[13px] text-slate-700 border-collapse" id="emulationTable">
                    <thead>
                        <tr class="bg-slate-100 text-[#224397] uppercase text-[12px] font-bold tracking-wide">
                            <th rowspan="2" class="border border-slate-300 p-2 w-[5%]">STT</th>
                            <th rowspan="2" class="border border-slate-300 p-2 w-[12%]">Lớp</th>
                            <th colspan="2" class="border border-slate-300 p-2 bg-sky-100/50">Số tiết</th>
                            <th colspan="3" class="border border-slate-300 p-2 bg-amber-100/50">Sổ đầu bài</th>
                            <th rowspan="2" class="border border-slate-300 p-2 w-[8%]">Nhật kỳ</th>
                            <th rowspan="2" class="border border-slate-300 p-2 w-[10%]">Điểm +/- Khác</th>
                        </tr>
                        <tr class="bg-slate-50 text-slate-600 uppercase text-[11px] font-bold">
                            <th class="border border-slate-300 p-2 bg-sky-50/50 w-[9%]">Tiết Tốt</th>
                            <th class="border border-slate-300 p-2 bg-sky-50/50 w-[9%]">Tiết TB</th>
                            <th class="border border-slate-300 p-2 bg-amber-50/50 w-[7%]">TT</th>
                            <th class="border border-slate-300 p-2 bg-amber-50/50 w-[7%]">CK</th>
                            <th class="border border-slate-300 p-2 bg-amber-50/50 w-[7%]">NK</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($danh_sach_lop as $index => $lop): ?>
                        <tr class="hover:bg-transparent transition-colors duration-150" data-lop-id="<?= $lop['lop_hoc_id'] ?>">
                            <td class="border border-slate-300 p-1 font-medium text-slate-500"><?= $index + 1 ?></td>
                            <td class="border border-slate-300 p-1 font-bold text-[#224397]"><?= htmlspecialchars($lop['ten_lop']) ?></td>
                            
                            <td class="border border-slate-300 p-0 bg-sky-50/20"><input type="number" class="auto-save editable-cell w-full h-10 bg-transparent text-center focus:outline-none focus:bg-white focus:ring-2 focus:ring-[#224397] focus:relative focus:z-10 rounded-sm font-medium" data-field="so_tiet_tot" value="<?= htmlspecialchars($lop['so_tiet_tot'] ?? '') ?>"></td>
                            <td class="border border-slate-300 p-0 bg-sky-50/20"><input type="number" class="auto-save editable-cell w-full h-10 bg-transparent text-center focus:outline-none focus:bg-white focus:ring-2 focus:ring-[#224397] focus:relative focus:z-10 rounded-sm font-medium" data-field="so_tiet_tb" value="<?= htmlspecialchars($lop['so_tiet_tb'] ?? '') ?>"></td>
                            
                            <td class="border border-slate-300 p-0 bg-amber-50/20"><input type="text" class="auto-save editable-cell x-input w-full h-10 bg-transparent text-center focus:outline-none focus:bg-white focus:ring-2 focus:ring-[#224397] focus:relative focus:z-10 rounded-sm font-bold text-[#ea580c] uppercase cursor-pointer" data-field="sdb_tt" value="<?= ($lop['sdb_tt'] ?? 0) == 1 ? 'X' : '' ?>" title="Gõ X, Phím cách hoặc Click để đổi trạng thái"></td>
                            <td class="border border-slate-300 p-0 bg-amber-50/20"><input type="text" class="auto-save editable-cell x-input w-full h-10 bg-transparent text-center focus:outline-none focus:bg-white focus:ring-2 focus:ring-[#224397] focus:relative focus:z-10 rounded-sm font-bold text-[#ea580c] uppercase cursor-pointer" data-field="sdb_ck" value="<?= ($lop['sdb_ck'] ?? 0) == 1 ? 'X' : '' ?>" title="Gõ X, Phím cách hoặc Click để đổi trạng thái"></td>
                            <td class="border border-slate-300 p-0 bg-amber-50/20"><input type="text" class="auto-save editable-cell x-input w-full h-10 bg-transparent text-center focus:outline-none focus:bg-white focus:ring-2 focus:ring-[#224397] focus:relative focus:z-10 rounded-sm font-bold text-[#ea580c] uppercase cursor-pointer" data-field="sdb_nk" value="<?= ($lop['sdb_nk'] ?? 0) == 1 ? 'X' : '' ?>" title="Gõ X, Phím cách hoặc Click để đổi trạng thái"></td>
                            
                            <td class="border border-slate-300 p-0"><input type="text" class="auto-save editable-cell x-input w-full h-10 bg-transparent text-center focus:outline-none focus:bg-white focus:ring-2 focus:ring-[#224397] focus:relative focus:z-10 rounded-sm font-bold text-[#ea580c] uppercase cursor-pointer" data-field="nhat_ky" value="<?= ($lop['nhat_ky'] ?? 0) == 1 ? 'X' : '' ?>" title="Gõ X, Phím cách hoặc Click để đổi trạng thái"></td>
                            <td class="border border-slate-300 p-0"><input type="number" step="0.5" class="auto-save editable-cell w-full h-10 bg-transparent text-center focus:outline-none focus:bg-white focus:ring-2 focus:ring-[#224397] focus:relative focus:z-10 rounded-sm font-medium" data-field="diem_cong_tru" value="<?= htmlspecialchars($lop['diem_cong_tru'] ?? '') ?>"></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($danh_sach_lop)): ?>
                        <tr>
                            <td colspan="9" class="p-8 text-slate-400 italic">Chưa có danh sách lớp để nhập điểm thi đua.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<!-- Modal Xác nhận Đồng bộ -->
<div id="syncConfirmModal" class="fixed inset-0 z-[10006] hidden opacity-0 transition-opacity duration-300">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModal('syncConfirmModal')"></div>
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0 pointer-events-none">
        <div class="relative bg-white rounded-2xl shadow-2xl border border-[#224397]/[45%] flex flex-col w-full max-w-md pointer-events-auto transform transition-all duration-300 scale-95 translate-y-4 opacity-0">
            <div class="flex items-center justify-between p-5 border-b border-[#224397]/25">
                <h5 class="text-lg font-bold text-[#224397] flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-exclamation-triangle-fill text-amber-500" viewBox="0 0 16 16"><path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/></svg> Xác nhận đồng bộ</h5>
                <button type="button" class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 p-1.5 rounded-lg transition-colors" onclick="closeModal('syncConfirmModal')"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg></button>
            </div>
            <div class="p-6 text-left space-y-4">
                <p class="text-[14px] text-slate-700">Bạn có chắc muốn đồng bộ dữ liệu từ <strong>Sổ nhật kỳ Online</strong> đã được duyệt không?</p>
                <div class="bg-amber-50 border-l-4 border-amber-500 p-3 rounded">
                    <p class="text-[13px] text-amber-800"><span class="font-bold">LƯU Ý:</span> Thao tác này sẽ ghi đè lên các giá trị hiện tại.</p>
                </div>
            </div>
            <div class="flex items-center justify-end px-6 py-4 bg-slate-50 border-t border-[#224397]/25 gap-2 rounded-b-2xl">
                <button type="button" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-[13px] font-medium text-slate-600 bg-white border border-slate-300 rounded hover:bg-slate-50 transition-colors shadow-sm" onclick="closeModal('syncConfirmModal')">Hủy</button>
                <button type="button" id="confirmSyncBtn" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-[13px] font-bold text-white bg-sky-500 border border-sky-500 rounded shadow-sm hover:bg-sky-600 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-arrow-repeat text-base" viewBox="0 0 16 16"><path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41m-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9"/><path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5 5 0 0 0 8 3M3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9z"/></svg>
                    Đồng bộ ngay
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Import -->
<div id="importModal" class="fixed inset-0 z-[10005] hidden opacity-0 transition-opacity duration-300">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModal('importModal')"></div>
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0 pointer-events-none">
        <div class="relative bg-white rounded-2xl shadow-2xl border border-[#224397]/[45%] flex flex-col w-full max-w-md pointer-events-auto transform transition-all duration-300 scale-95 translate-y-4 opacity-0">
            <form action="/thidua/nhap-diem-thi-dua/import" method="POST" enctype="multipart/form-data">
                <div class="flex items-center justify-between p-5 border-b border-[#224397]/25">
                    <h5 class="text-lg font-bold text-[#224397] flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-excel" viewBox="0 0 16 16"><path d="M5.884 6.68a.5.5 0 1 0-.768.64L7.349 10l-2.233 2.68a.5.5 0 0 0 .768.64L8 10.781l2.116 2.54a.5.5 0 0 0 .768-.641L8.651 10l2.233-2.68a.5.5 0 0 0-.768-.64L8 9.219l-2.116-2.54z"/>   <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/></svg> Import Điểm Thi Đua</h5>
                    <button type="button" class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 p-1.5 rounded-lg transition-colors" onclick="closeModal('importModal')"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg></button>
                </div>
                <div class="p-6 text-left space-y-4">
                    <p class="text-[13px] text-slate-600">Chọn file Excel (.xlsx) đã điền sẵn dữ liệu để tải lên. Hệ thống sẽ tự động cập nhật.</p>
                    <input type="hidden" name="tuan_id" value="<?= $tuan_id ?>">
                    <div>
                        <label for="excelFile" class="block text-[13px] font-semibold text-slate-700 mb-1">Chọn file</label>
                        <input class="w-full text-[13px] px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-[#224397] transition-colors" type="file" name="excelFile" id="excelFile" accept=".xls,.xlsx" required>
                    </div>
                </div>
                <div class="flex items-center justify-end px-6 py-4 bg-slate-50 border-t border-[#224397]/25 gap-2 rounded-b-2xl">
                    <button type="button" class="px-4 py-2 text-[13px] font-medium text-slate-600 bg-white border border-slate-300 rounded hover:bg-slate-50 transition-colors shadow-sm" onclick="closeModal('importModal')">Hủy</button>
                    <button type="submit" class="px-4 py-2 text-[13px] font-bold text-slate-900 bg-[#FAB723] border border-[#FAB723] rounded shadow-sm hover:bg-[#e5a61d] transition flex items-center justify-center gap-1.5"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-upload" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>   <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708z"/></svg> Tải lên & Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Tắt mũi tên của input type number */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
input[type="number"] { -moz-appearance: textfield; }
</style>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>

<script>
// Hàm cho Modal Tailwind
function openModal(id) {
    const modal = document.getElementById(id);
    const content = modal.querySelector('.bg-white.rounded-2xl');
    modal.classList.remove('hidden');
    void modal.offsetWidth;
    modal.classList.remove('opacity-0');
    if(content) content.classList.remove('scale-95', 'translate-y-4', 'opacity-0');
}

function closeModal(id) {
    const modal = document.getElementById(id);
    const content = modal.querySelector('.bg-white.rounded-2xl');
    modal.classList.add('opacity-0');
    if(content) content.classList.add('scale-95', 'translate-y-4', 'opacity-0');
    setTimeout(() => modal.classList.add('hidden'), 300);
}

document.addEventListener('DOMContentLoaded', function() {
    const statusIndicator = document.getElementById('status-indicator');
    const tuanId = <?= json_encode($tuan_id) ?>;
    const emulationTable = document.getElementById('emulationTable');
    let saveTimeout;
    
    // Logic bộ lọc
    const filterGrade = document.getElementById('filterGrade');
    const filterClass = document.getElementById('filterClass');
    const tableRows = emulationTable ? emulationTable.querySelectorAll('tbody tr') : [];

    function applyFilters() {
        const gradeVal = filterGrade ? filterGrade.value : '';
        const classVal = filterClass ? filterClass.value.toLowerCase().trim() : '';
        
        let visibleIndex = 1;

        tableRows.forEach(row => {
            if (row.querySelector('td[colspan]')) return; // Bỏ qua dòng trống

            const classCell = row.querySelector('td:nth-child(2)');
            if (!classCell) return;
            const className = classCell.textContent.trim();
            const classLower = className.toLowerCase();

            let matchGrade = true;
            if (gradeVal !== '') {
                matchGrade = className.startsWith(gradeVal);
            }

            let matchClass = true;
            if (classVal !== '') {
                matchClass = classLower.includes(classVal);
            }

            if (matchGrade && matchClass) {
                row.style.display = '';
                // Cập nhật lại số thứ tự
                const sttCell = row.querySelector('td:first-child');
                if(sttCell) sttCell.textContent = visibleIndex++;
            } else {
                row.style.display = 'none';
            }
        });
    }

    if (filterGrade) filterGrade.addEventListener('change', applyFilters);
    if (filterClass) filterClass.addEventListener('input', applyFilters);
    
    // Nút đồng bộ
    const syncBtn = document.getElementById('sync-journal-btn');
    const confirmSyncBtn = document.getElementById('confirmSyncBtn');
    if (syncBtn) {
        syncBtn.addEventListener('click', function() {
            openModal('syncConfirmModal');
        });
    }

    if (confirmSyncBtn) {
        confirmSyncBtn.addEventListener('click', async function() {
            closeModal('syncConfirmModal');
            syncBtn.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm w-4 h-4 inline-block border-2 border-white border-t-transparent rounded-full animate-spin"></span> Đang đồng bộ...';
            statusIndicator.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-hourglass-split" viewBox="0 0 16 16"><path d="M2.5 15a.5.5 0 1 1 0-1h1v-1a4.5 4.5 0 0 1 2.557-4.06c.29-.139.443-.377.443-.59v-.7c0-.213-.154-.451-.443-.59A4.5 4.5 0 0 1 3.5 3V2h-1a.5.5 0 0 1 0-1h11a.5.5 0 0 1 0 1h-1v1a4.5 4.5 0 0 1-2.557 4.06c-.29.139-.443.377-.443.59v.7c0 .213.154.451.443.59A4.5 4.5 0 0 1 12.5 13v1h1a.5.5 0 0 1 0 1zm2-13v1c0 .537.12 1.045.337 1.5h6.326c.216-.455.337-.963.337-1.5V2zm3 6.35c0 .701-.478 1.236-1.011 1.492A3.5 3.5 0 0 0 4.5 13s.866-1.299 3-1.48zm1 0v3.17c2.134.181 3 1.48 3 1.48a3.5 3.5 0 0 0-1.989-3.158C8.978 9.586 8.5 9.052 8.5 8.351z"/></svg> Đang đồng bộ dữ liệu...';
            statusIndicator.className = 'inline-flex items-center gap-1.5 text-[13px] font-semibold italic text-sky-600';

            try {
                const response = await fetch('/thidua/api/dong-bo-nhat-ky', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ tuan_id: tuanId })
                });

                const result = await response.json();

                if (result.success) {
                    showToast('success', result.message);
                    setTimeout(() => window.location.reload(), 1000); 
                } else {
                    throw new Error(result.message || 'Đồng bộ thất bại.');
                }
            } catch (error) {
                showToast('error', 'Lỗi: ' + error.message);
                statusIndicator.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>   <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/></svg> Lỗi đồng bộ: ' + error.message;
                statusIndicator.className = 'inline-flex items-center gap-1.5 text-[13px] font-semibold italic text-red-500';
            } finally {
                this.disabled = false;
                this.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-arrow-repeat text-base" viewBox="0 0 16 16"><path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41m-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9"/>   <path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5 5 0 0 0 8 3M3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9z"/></svg> Đồng bộ Nhật kỳ';
            }
        });
    }

    // Auto save
    async function saveData(element) {
        clearTimeout(saveTimeout);
        statusIndicator.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-hourglass" viewBox="0 0 16 16"><path d="M2 1.5a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-1v1a4.5 4.5 0 0 1-2.557 4.06c-.29.139-.443.377-.443.59v.7c0 .213.154.451.443.59A4.5 4.5 0 0 1 12.5 13v1h1a.5.5 0 0 1 0 1h-11a.5.5 0 1 1 0-1h1v-1a4.5 4.5 0 0 1 2.557-4.06c.29-.139.443-.377.443-.59v-.7c0-.213-.154-.451-.443-.59A4.5 4.5 0 0 1 3.5 3V2h-1a.5.5 0 0 1-.5-.5m2.5.5v1a3.5 3.5 0 0 0 1.989 3.158c.533.256 1.011.791 1.011 1.491v.702c0 .7-.478 1.235-1.011 1.491A3.5 3.5 0 0 0 4.5 13v1h7v-1a3.5 3.5 0 0 0-1.989-3.158C8.978 9.586 8.5 9.052 8.5 8.351v-.702c0-.7.478-1.235 1.011-1.491A3.5 3.5 0 0 0 11.5 3V2z"/></svg> Đang lưu...';
        statusIndicator.className = 'inline-flex items-center gap-1.5 text-[13px] font-semibold italic text-slate-500';

        const row = element.closest('tr');
        const lopId = row.dataset.lopId;
        const field = element.dataset.field;
        let value = element.value;

        if (element.classList.contains('x-input')) {
            value = (element.value.trim().toUpperCase() === 'X') ? 1 : 0;
        }

        try {
            const response = await fetch('/thidua/api/luu-thi-dua', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ tuan_id: tuanId, lop_id: lopId, field: field, value: value })
            });
            const result = await response.json();
            if (!result.success) throw new Error(result.message);
            
            statusIndicator.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>   <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/></svg> Đã lưu thành công';
            statusIndicator.className = 'inline-flex items-center gap-1.5 text-[13px] font-semibold italic text-emerald-600';
            saveTimeout = setTimeout(() => { statusIndicator.innerHTML = ''; }, 2000);
        } catch (error) {
            statusIndicator.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-exclamation-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>   <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z"/></svg> Lỗi lưu: ' + error.message;
            statusIndicator.className = 'inline-flex items-center gap-1.5 text-[13px] font-semibold italic text-red-500';
        }
    }

    if (emulationTable) {
        // Tự động bôi đen nội dung ô khi focus để gõ đè nhanh
        emulationTable.addEventListener('focusin', function(e) {
            if (e.target.classList.contains('editable-cell')) {
                setTimeout(() => e.target.select(), 10);
            }
        });

        // Click để chuyển đổi nhanh giữa X và trống
        emulationTable.addEventListener('click', function(e) {
            const input = e.target;
            if (input.classList.contains('x-input')) {
                input.value = (input.value.trim().toUpperCase() === 'X') ? '' : 'X';
                saveData(input);
            }
        });

        emulationTable.addEventListener('blur', function(e) {
            if (e.target.classList.contains('auto-save')) saveData(e.target);
        }, true);

        emulationTable.addEventListener('input', function(e) {
            const input = e.target;
            if (input.classList.contains('x-input')) {
                const val = input.value.trim().toUpperCase();
                input.value = (val.includes('X') || val.includes('1') || val.includes('V') || val.includes('C') || val.includes('+')) ? 'X' : '';
                saveData(input);
            }
        });

        emulationTable.addEventListener('keydown', function(e) {
            const activeElement = document.activeElement;
            if (!activeElement.classList.contains('editable-cell')) return;

            const key = e.key;

            // Xử lý phím tắt cho ô x-input (Sổ đầu bài, Nhật ký)
            if (activeElement.classList.contains('x-input')) {
                if (key === ' ' || key === 'Spacebar') {
                    e.preventDefault();
                    activeElement.value = (activeElement.value.trim().toUpperCase() === 'X') ? '' : 'X';
                    saveData(activeElement);
                    return;
                }
                if (['x', 'X', '1', 'v', 'V', 'c', 'C', '+'].includes(key)) {
                    e.preventDefault();
                    activeElement.value = 'X';
                    saveData(activeElement);
                    return;
                }
                if (['0', 'Backspace', 'Delete'].includes(key)) {
                    e.preventDefault();
                    activeElement.value = '';
                    saveData(activeElement);
                    return;
                }
            }

            if (!['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'Tab', 'Enter'].includes(key)) return;
            
            e.preventDefault();
            
            const currentCell = activeElement.parentElement;
            const currentRow = currentCell.closest('tr');
            const cellsInRow = Array.from(currentRow.querySelectorAll('td'));
            const currentCellIndex = cellsInRow.indexOf(currentCell);
            const allRows = Array.from(emulationTable.querySelectorAll('tbody tr'));
            const currentRowIndex = allRows.indexOf(currentRow);
            
            let targetRowIndex = currentRowIndex;
            let targetCellIndex = currentCellIndex;

            if (key === 'ArrowUp') {
                targetRowIndex = Math.max(0, currentRowIndex - 1);
            } else if (key === 'ArrowDown' || key === 'Enter') {
                targetRowIndex = Math.min(allRows.length - 1, currentRowIndex + 1);
            } else if (key === 'ArrowLeft') {
                targetCellIndex = Math.max(2, currentCellIndex - 1);
            } else if (key === 'ArrowRight' || key === 'Tab') {
                targetCellIndex = Math.min(cellsInRow.length - 1, currentCellIndex + 1);
            }
            
            const targetRow = allRows[targetRowIndex];
            const targetCell = targetRow.querySelectorAll('td')[targetCellIndex];
            const targetInput = targetCell ? targetCell.querySelector('.editable-cell') : null;

            if (targetInput) {
                targetInput.focus();
                targetInput.select();
            }
        });
    }
});
</script>
