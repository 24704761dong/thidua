<?php
// File: src/views/quan_ly_cau_hinh_vi_pham.php
$page_title = 'NỘI DUNG VI PHẠM';
require_once __DIR__ . '/partials/admin_header.php';

// Tạo CSRF token nếu chưa có
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];
?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-card-checklist text-[#224397]" viewBox="0 0 16 16"><path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2z"/>   <path d="M7 5.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0M7 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0"/></svg>
                CẤU HÌNH DANH SÁCH NỘI QUY
            </h1>
            <p class="text-sm text-slate-500 mt-1">Quản lý danh mục và điểm trừ các lỗi vi phạm</p>
        </div>
        <div class="flex items-center gap-2">

            <div class="relative" id="excelDropdownWrapper">
                <button type="button" class="px-2 py-1 bg-emerald-600 text-white rounded hover:bg-emerald-700 transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm whitespace-nowrap" onclick="toggleExcelDropdown(event)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-excel-fill" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M5.884 6.68 8 9.219l2.116-2.54a.5.5 0 1 1 .768.641L8.651 10l2.233 2.68a.5.5 0 0 1-.768.64L8 10.781l-2.116 2.54a.5.5 0 0 1-.768-.641L7.349 10 5.116 7.32a.5.5 0 1 1 .768-.64"/></svg> Excel <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-chevron-down text-[10px] ml-0.5" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708"/></svg>
                </button>
                <ul id="excelDropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-slate-100 py-1 z-50 transition-all duration-200 opacity-0 scale-95 origin-top-right">
                    <li><a class="flex items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-[#224397] transition-colors" href="#" onclick="openModal('importViolationModal')"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-upload mr-2" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>   <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708z"/></svg>Import Vi Phạm</a></li>
                    <li><a class="flex items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-[#224397] transition-colors" href="/thidua/tai-mau-cau-hinh-vi-pham"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-download mr-2" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>   <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/></svg>Tải File Mẫu</a></li>
                    <li><hr class="border-slate-100 my-1"></li>
                    <li><a class="flex items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-emerald-700 transition-colors" href="/thidua/xuat-cau-hinh-vi-pham"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-spreadsheet mr-2" viewBox="0 0 16 16"><path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V9H3V2a1 1 0 0 1 1-1h5.5zM3 12v-2h2v2zm0 1h2v2H4a1 1 0 0 1-1-1zm3 2v-2h3v2zm4 0v-2h3v1a1 1 0 0 1-1 1zm3-3h-3v-2h3zm-7 0v-2h3v2z"/></svg>Xuất Dữ Liệu</a></li>
                </ul>
            </div>

            <button type="button" class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm whitespace-nowrap" onclick="openModal('addViolationModal')">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-plus-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3z"/></svg> Thêm Vi Phạm
            </button>
        </div>
    </div>

    <!-- Danh sách vi phạm -->
    <div class="bg-white rounded-xl shadow-sm border border-[#224397]/[45%] overflow-hidden border-[#224397]/25">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse border border-slate-300 relative">
                <thead class="bg-slate-100 shadow-sm bg-slate-50 border-b border-[#224397]/25 text-xs uppercase font-semibold text-slate-500 sticky top-0">
                    <tr class="bg-slate-100 border-b border-slate-300 text-xs uppercase tracking-wider text-slate-700 font-semibold">
                        <th class="px-6 py-4 w-16 text-center border-r border-slate-300">STT</th>
                        <th class="px-6 py-4 text-center border-r border-slate-300 whitespace-nowrap">Nhóm</th>
                        <th class="px-6 py-4 border-r border-slate-300">Tên Nhóm Vi Phạm</th>
                        <th class="px-6 py-4 w-32 text-center border-r border-slate-300">Điểm Trừ</th>
                        <th class="px-6 py-4 w-32 text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-300 text-sm text-slate-700">
                    <?php if (empty($danh_sach_vi_pham)) : ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500 bg-slate-50/50">
                                <div class="flex flex-col items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="3em" height="3em" fill="currentColor" class="bi bi-inbox text-slate-300 mb-2" viewBox="0 0 16 16"><path d="M4.98 4a.5.5 0 0 0-.39.188L1.54 8H6a.5.5 0 0 1 .5.5 1.5 1.5 0 1 0 3 0A.5.5 0 0 1 10 8h4.46l-3.05-3.812A.5.5 0 0 0 11.02 4zm9.954 5H10.45a2.5 2.5 0 0 1-4.9 0H1.066l.32 2.562a.5.5 0 0 0 .497.438h12.234a.5.5 0 0 0 .496-.438zM3.809 3.563A1.5 1.5 0 0 1 4.981 3h6.038a1.5 1.5 0 0 1 1.172.563l3.7 4.625a.5.5 0 0 1 .105.374l-.39 3.124A1.5 1.5 0 0 1 14.117 13H1.883a1.5 1.5 0 0 1-1.489-1.314l-.39-3.124a.5.5 0 0 1 .106-.374z"/></svg>
                                    <span class="text-sm font-medium text-slate-500">Chưa có cấu hình vi phạm nào</span>
                                </div>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($danh_sach_vi_pham as $index => $vp) : ?>
                            <tr class="hover:bg-slate-50 transition-colors transition" data-id="<?php echo (int)$vp['id']; ?>" data-nhom="<?php echo htmlspecialchars($vp['nhom_vi_pham']); ?>" data-ten="<?php echo htmlspecialchars($vp['ten_vi_pham']); ?>" data-diem="<?php echo htmlspecialchars($vp['diem_tru']); ?>">
                                <td class="px-6 py-4 text-center text-slate-600 border-r border-slate-300"><?php echo $index + 1; ?></td>
                                <td class="px-6 py-4 text-center border-r border-slate-300">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-[#224397] border border-blue-200 whitespace-nowrap">
                                        <?php echo htmlspecialchars($vp['nhom_vi_pham']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-medium text-slate-800 border-r border-slate-300">
                                    <?php echo htmlspecialchars($vp['ten_vi_pham']); ?>
                                </td>
                                <td class="px-6 py-4 text-center border-r border-slate-300">
                                    <span class="font-bold text-red-600 bg-red-50 px-2.5 py-1 rounded border border-red-200">
                                        <?php echo htmlspecialchars($vp['diem_tru']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button class="edit-btn p-1.5 text-slate-400 hover:text-[#224397] hover:bg-blue-50 rounded-lg transition-all hover:scale-110 hover:-translate-y-1 hover:translate-x-1 hover:scale-[1.02] duration-300" title="Sửa">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-pencil-square text-lg" viewBox="0 0 16 16"><path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>   <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/></svg>
                                        </button>
                                        <button class="delete-btn p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all hover:scale-110 hover:-translate-y-1 hover:translate-x-1 hover:scale-[1.02] duration-300" title="Xóa">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-trash3-fill text-lg" viewBox="0 0 16 16"><path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-[#224397]/25 bg-slate-50 text-sm text-slate-500 font-medium flex justify-between items-center rounded-b-xl">
            <span>Danh sách cấu hình điểm trừ</span>
            <span>Tổng cộng: <strong class="text-[#224397] text-base"><?php echo count($danh_sach_vi_pham ?? []); ?></strong> vi phạm</span>
        </div>
    </div>
</div>

<!-- Modal Thêm Vi Phạm -->
<div id="addViolationModal" class="hidden fixed inset-0 z-[10005] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0" onclick="closeModal('addViolationModal')">
    <div class="modal-content bg-white rounded-2xl w-[500px] max-w-[90%] m-auto flex flex-col overflow-hidden border border-slate-300 transform transition-all duration-300 scale-95 translate-y-4 opacity-0 shadow-2xl border-[#224397]/25" onclick="event.stopPropagation()">
        <form id="addViolationForm" action="/thidua/them-cau-hinh-vi-pham" method="POST">
            <div class="bg-slate-50 px-6 py-4 border-b border-[#224397]/25 flex justify-between items-center font-semibold text-[#224397]">
                <h5 class="text-lg font-bold text-slate-800 flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-plus-circle-fill text-[#224397]" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3z"/></svg>Thêm Vi Phạm Mới</h5>
                <button type="button" class="text-slate-400 hover:text-red-500 transition-colors hover:translate-x-1 hover:scale-[1.02] transition-all duration-300" onclick="closeModal('addViolationModal')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg text-xl" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-5 bg-white">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nhóm vi phạm</label>
                    <input type="text" class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-[#224397] focus:ring-1 focus:ring-[#224397] focus:outline-none shadow-sm transition-colors text-slate-700" id="nhom_vi_pham" name="nhom_vi_pham" list="nhom-list" placeholder="Ví dụ: Nề nếp, Học tập...">
                    <datalist id="nhom-list">
                        <?php foreach (($danh_sach_nhom ?? []) as $nhom) : ?>
                            <option value="<?php echo htmlspecialchars($nhom); ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tên Nhóm Vi Phạm <span class="text-red-500">*</span></label>
                    <input type="text" class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-[#224397] focus:ring-1 focus:ring-[#224397] focus:outline-none shadow-sm transition-colors text-slate-700" id="ten_vi_pham" name="ten_vi_pham" required>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Điểm trừ <span class="text-red-500">*</span></label>
                    <input type="number" step="0.5" min="0" class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-[#224397] focus:ring-1 focus:ring-[#224397] focus:outline-none shadow-sm transition-colors text-slate-700" id="diem_tru" name="diem_tru" required>
                </div>
            </div>
            <div class="px-6 py-4 bg-slate-50 border-t border-[#224397]/25 flex justify-end gap-3">
                <button type="button" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors shadow-sm text-sm font-medium hover:translate-x-1 hover:scale-[1.02] transition-all duration-300" onclick="closeModal('addViolationModal')">Hủy</button>
                <button type="submit" class="px-4 py-2 bg-[#224397] text-white rounded-lg hover:bg-[#1a367d] transition-colors shadow-sm text-sm font-medium hover:translate-x-1 hover:scale-[1.02] transition-all duration-300">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Sửa Vi Phạm -->
<div id="editViolationModal" class="hidden fixed inset-0 z-[10005] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0" onclick="closeModal('editViolationModal')">
    <div class="modal-content bg-white rounded-2xl w-[500px] max-w-[90%] m-auto flex flex-col overflow-hidden border border-slate-300 transform transition-all duration-300 scale-95 translate-y-4 opacity-0 shadow-2xl border-[#224397]/25" onclick="event.stopPropagation()">
        <form id="editViolationForm">
            <div class="bg-slate-50 px-6 py-4 border-b border-[#224397]/25 flex justify-between items-center font-semibold text-[#224397]">
                <h5 class="text-lg font-bold text-slate-800 flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-pencil-square text-[#224397]" viewBox="0 0 16 16"><path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>   <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/></svg>Chỉnh Sửa Vi Phạm</h5>
                <button type="button" class="text-slate-400 hover:text-red-500 transition-colors hover:translate-x-1 hover:scale-[1.02] transition-all duration-300" onclick="closeModal('editViolationModal')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg text-xl" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-5 bg-white">
                <input type="hidden" id="edit_id" name="id">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nhóm vi phạm</label>
                    <input type="text" class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-[#224397] focus:ring-1 focus:ring-[#224397] focus:outline-none shadow-sm transition-colors text-slate-700" id="edit_nhom_vi_pham" name="nhom_vi_pham" list="nhom-list">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tên Nhóm Vi Phạm <span class="text-red-500">*</span></label>
                    <input type="text" class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-[#224397] focus:ring-1 focus:ring-[#224397] focus:outline-none shadow-sm transition-colors text-slate-700" id="edit_ten_vi_pham" name="ten_vi_pham" required>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Điểm trừ <span class="text-red-500">*</span></label>
                    <input type="number" step="0.5" min="0" class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-[#224397] focus:ring-1 focus:ring-[#224397] focus:outline-none shadow-sm transition-colors text-slate-700" id="edit_diem_tru" name="diem_tru" required>
                </div>
            </div>
            <div class="px-6 py-4 bg-slate-50 border-t border-[#224397]/25 flex justify-end gap-3">
                <button type="button" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors shadow-sm text-sm font-medium hover:translate-x-1 hover:scale-[1.02] transition-all duration-300" onclick="closeModal('editViolationModal')">Hủy</button>
                <button type="submit" class="px-4 py-2 bg-[#224397] text-white rounded-lg hover:bg-[#1a367d] transition-colors shadow-sm text-sm font-medium hover:translate-x-1 hover:scale-[1.02] transition-all duration-300">Lập tức Cập nhật</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Import -->
<div id="importViolationModal" class="hidden fixed inset-0 z-[10005] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0" onclick="closeModal('importViolationModal')">
    <div class="modal-content bg-white rounded-2xl w-[500px] max-w-[90%] m-auto flex flex-col overflow-hidden border border-slate-300 transform transition-all duration-300 scale-95 translate-y-4 opacity-0 shadow-2xl border-[#224397]/25" onclick="event.stopPropagation()">
        <form action="/thidua/import-cau-hinh-vi-pham<?php echo isset($_GET['iframe']) ? '?iframe=1' : ''; ?>" method="POST" enctype="multipart/form-data" id="import-form">
            <div class="bg-slate-50 px-6 py-4 border-b border-[#224397]/25 flex justify-between items-center font-semibold text-[#224397]">
                <h5 class="text-lg font-bold text-slate-800 flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-arrow-up-fill text-emerald-600" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M6.354 9.854a.5.5 0 0 1-.708-.708l2-2a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1-.708.708L8.5 8.707V12.5a.5.5 0 0 1-1 0V8.707z"/></svg>Import từ Excel</h5>
                <button type="button" class="text-slate-400 hover:text-red-500 transition-colors hover:translate-x-1 hover:scale-[1.02] transition-all duration-300" onclick="closeModal('importViolationModal')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg text-xl" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg>
                </button>
            </div>
            <div class="p-6 bg-white space-y-5">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <div class="bg-blue-50 text-[#224397] p-3.5 rounded-xl text-sm border border-blue-200 flex items-start gap-2.5 leading-relaxed">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-info-circle-fill shrink-0 mt-0.5" viewBox="0 0 16 16"><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2"/></svg>
                    <div>
                        Vui lòng tải lên file Excel (.xlsx) với các cột theo thứ tự: <strong>Nhom, TenViPham, DiemTru</strong>.
                    </div>
                </div>
                <div>
                    <label class="block mb-2 text-sm font-semibold text-slate-700">Chọn file Excel</label>
                    <input class="block w-full text-sm text-slate-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-slate-300 rounded-lg cursor-pointer bg-white focus:outline-none transition-colors" type="file" name="excelFile" accept=".xlsx" required>
                </div>
            </div>
            <div class="px-6 py-4 bg-slate-50 border-t border-[#224397]/25 flex justify-end gap-3">
                <button type="button" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors shadow-sm text-sm font-medium hover:translate-x-1 hover:scale-[1.02] transition-all duration-300" onclick="closeModal('importViolationModal')">Hủy</button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors shadow-sm text-sm font-medium hover:translate-x-1 hover:scale-[1.02] transition-all duration-300">Tải lên & Xem trước</button>
            </div>
        </form>
    </div>
</div>

<!-- Overlay loading -->
<div id="loading-overlay" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[9999] hidden items-center justify-center">
    <div class="bg-white p-6 rounded-2xl shadow-2xl flex flex-col items-center gap-4 border border-[#224397]/25 overflow-hidden">
        <div class="w-12 h-12 border-4 border-slate-200 border-t-[#224397] rounded-full animate-spin"></div>
        <p class="text-slate-600 font-medium">Đang xử lý, vui lòng đợi...</p>
    </div>
</div>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>

<style>
/* Đã gỡ bỏ toàn bộ CSS Bootstrap thừa */
</style>

<script>
// Logic Menu Xổ Xuống (Dropdown) bằng Vanilla JS
function toggleExcelDropdown(e) {
    if(e) e.stopPropagation();
    const menu = document.getElementById('excelDropdown');
    if (menu.classList.contains('hidden')) {
        menu.classList.remove('hidden');
        setTimeout(() => menu.classList.remove('opacity-0', 'scale-95'), 10);
    } else {
        menu.classList.add('opacity-0', 'scale-95');
        setTimeout(() => menu.classList.add('hidden'), 200);
    }
}
document.addEventListener('click', function(e) {
    const wrapper = document.getElementById('excelDropdownWrapper');
    const menu = document.getElementById('excelDropdown');
    if (wrapper && !wrapper.contains(e.target) && menu && !menu.classList.contains('hidden')) {
        menu.classList.add('opacity-0', 'scale-95');
        setTimeout(() => menu.classList.add('hidden'), 200);
    }
});

// Modal Animation Utility bằng Vanilla JS
function openModal(id) {
    // Tự động đóng Excel dropdown nếu đang mở
    const menu = document.getElementById('excelDropdown');
    if (menu && !menu.classList.contains('hidden')) {
        menu.classList.add('opacity-0', 'scale-95');
        setTimeout(() => menu.classList.add('hidden'), 200);
    }

    const modal = document.getElementById(id);
    if (!modal) return;
    const content = modal.querySelector('.modal-content, .bg-white.rounded-2xl') || modal.firstElementChild;
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
    void modal.offsetWidth; // Force reflow
    modal.classList.remove('opacity-0');
    if(content) {
        content.classList.remove('scale-95', 'translate-y-4', 'opacity-0');
        content.classList.add('scale-100', 'translate-y-0', 'opacity-100');
    }
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    const content = modal.querySelector('.modal-content, .bg-white.rounded-2xl') || modal.firstElementChild;
    modal.classList.add('opacity-0');
    if(content) {
        content.classList.remove('scale-100', 'translate-y-0', 'opacity-100');
        content.classList.add('scale-95', 'translate-y-4', 'opacity-0');
    }
    setTimeout(() => {
        modal.style.display = 'none';
        modal.classList.add('hidden');
    }, 300);
}

document.addEventListener('DOMContentLoaded', () => {
  const overlay = document.getElementById('loading-overlay');


  async function reloadTable() {
      try {
          const html = await (await fetch(window.location.href)).text();
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');
          const newTbody = doc.querySelector('table tbody');
          if (newTbody) {
              document.querySelector('table tbody').innerHTML = newTbody.innerHTML;
          }
      } catch (err) {
          console.error('Không thể cập nhật bảng:', err);
          location.reload();
      }
  }

  // Import Excel
  const importForm = document.getElementById('import-form');
  if (importForm) {
    importForm.addEventListener('submit', (e) => {
      const fileInput = importForm.querySelector('input[type="file"]');
      if (!fileInput.files.length) {
        e.preventDefault();
        showToast('error', 'Vui lòng chọn một file Excel để tải lên.');
        return;
      }
      const okTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
      if (!okTypes.includes(fileInput.files[0].type) && !fileInput.files[0].name.endsWith('.xlsx')) {
        e.preventDefault();
        showToast('error', 'Chỉ chấp nhận tệp .xlsx');
        return;
      }
      overlay.classList.remove('hidden');
      overlay.classList.add('flex');
      importForm.querySelector('button[type="submit"]').disabled = true;
    });
  }

  // Mở modal sửa
  const editForm = document.getElementById('editViolationForm');

  document.addEventListener('click', (e) => {
    const editBtn = e.target.closest('.edit-btn');
    if (!editBtn) return;
    const row = editBtn.closest('tr');
    
    document.getElementById('edit_id').value = row.dataset.id || '';
    document.getElementById('edit_nhom_vi_pham').value = row.dataset.nhom || '';
    document.getElementById('edit_ten_vi_pham').value = row.dataset.ten || '';
    document.getElementById('edit_diem_tru').value = row.dataset.diem || '';
    
    openModal('editViolationModal');
    setTimeout(() => document.getElementById('edit_ten_vi_pham').focus(), 200);
  });

  // Gửi form sửa (AJAX)
  editForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const submitBtn = editForm.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    overlay.classList.remove('hidden');
    overlay.classList.add('flex');

    const formData = new FormData(editForm);
    const data = Object.fromEntries(formData.entries());

    try {
      const res = await fetch('/thidua/sua-cau-hinh-vi-pham', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
        credentials: 'same-origin'
      });
      const result = await res.json();
      
      if (result.success) {
          closeModal('editViolationModal');
          overlay.classList.add('hidden');
          overlay.classList.remove('flex');
          showToast('success', result.message || 'Cập nhật thành công!');
          await reloadTable();
          submitBtn.disabled = false;
      } else {
          showToast('error', result.message || 'Lỗi khi sửa vi phạm.');
          overlay.classList.add('hidden');
          overlay.classList.remove('flex');
          submitBtn.disabled = false;
      }
    } catch (err) {
      showToast('error', 'Lỗi kết nối: ' + err);
      overlay.classList.add('hidden');
      overlay.classList.remove('flex');
      submitBtn.disabled = false;
    }
  });

  // Xóa vi phạm (AJAX)
  document.addEventListener('click', async (e) => {
    const deleteBtn = e.target.closest('.delete-btn');
    if (!deleteBtn) return;
    
    const row = deleteBtn.closest('tr');
    const id = row.dataset.id;
    const ten = row.dataset.ten || 'mục này';

    AppSwal.fire({
        title: 'Cảnh Báo!',
        text: `Bạn có chắc chắn muốn xóa vi phạm "${ten}" không? Hành động này không thể hoàn tác.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Xóa',
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
    }).then(async (result) => {
        if(result.isConfirmed) {
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
            
            try {
              const res = await fetch('/thidua/xoa-cau-hinh-vi-pham', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, csrf_token: '<?= htmlspecialchars($csrf) ?>' }),
        credentials: 'same-origin'
      });
      const result = await res.json();
      
      if (result.success) {
          overlay.classList.add('hidden');
          overlay.classList.remove('flex');
          showToast('success', result.message || 'Đã xóa thành công!');
          await reloadTable();
      } else {
          showToast('error', result.message || 'Lỗi khi xóa vi phạm.');
          overlay.classList.add('hidden');
          overlay.classList.remove('flex');
      }
    } catch (err) {
      showToast('error', 'Lỗi kết nối: ' + err);
      overlay.classList.add('hidden');
      overlay.classList.remove('flex');
    }
        }
    });
  });

  // Nút submit thêm vi phạm (AJAX)
  const addForm = document.getElementById('addViolationForm');
  addForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = addForm.querySelector('button[type="submit"]');
    btn.disabled = true;
    overlay.classList.remove('hidden');
    overlay.classList.add('flex');

    const formData = new FormData(addForm);
    const data = Object.fromEntries(formData.entries());

    try {
      const res = await fetch('/thidua/them-cau-hinh-vi-pham', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
        credentials: 'same-origin'
      });
      const result = await res.json();
      
      if (result.success) {
          closeModal('addViolationModal');
          addForm.reset();
          overlay.classList.add('hidden');
          overlay.classList.remove('flex');
          showToast('success', result.message || 'Thêm vi phạm thành công!');
          await reloadTable();
          btn.disabled = false;
      } else {
          showToast('error', result.message || 'Lỗi khi thêm vi phạm.');
          overlay.classList.add('hidden');
          overlay.classList.remove('flex');
          btn.disabled = false;
      }
    } catch (err) {
      showToast('error', 'Lỗi kết nối: ' + err);
      overlay.classList.add('hidden');
      overlay.classList.remove('flex');
      btn.disabled = false;
    }
  });
});
</script>
