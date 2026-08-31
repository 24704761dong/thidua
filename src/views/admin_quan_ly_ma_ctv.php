<?php
$page_title = "Quản lý Mã Kích hoạt CTV";
require_once __DIR__ . '/partials/admin_header.php';
?>

<div class="container-fluid p-4">
    <!-- Header Page -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-[#224397] flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-key-fill" viewBox="0 0 16 16"><path d="M3.5 11.5a3.5 3.5 0 1 1 3.163-5H14L15.5 8 14 9.5l-1-1-1 1-1-1-1 1-1-1-1 1H6.663a3.5 3.5 0 0 1-3.163 2M2.5 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/></svg>
                Quản lý Mã Kích hoạt CTV
            </h2>
            <p class="text-slate-500 text-sm mt-1">Tạo và quản lý các mã cấp quyền truy cập tính năng cho Cộng Tác Viên.</p>
        </div>
        <div class="mt-3 md:mt-0 flex flex-wrap gap-2">
            <button id="deleteSelectedBtn" class="px-4 py-2 bg-red-600 border border-transparent rounded text-white hover:bg-red-700 hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 font-medium flex items-center justify-center gap-2 text-sm shadow-sm disabled:opacity-50 disabled:pointer-events-none" disabled>
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16"><path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5M8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5m3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0"/></svg>
                <span class="text">Xóa mục đã chọn</span>
            </button>
            <a href="/thidua/admin/ctv?action=export_codes" class="px-4 py-2 bg-white border border-slate-300 rounded text-slate-700 hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 font-medium flex items-center justify-center gap-2 text-sm shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-excel-fill" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M5.884 6.68 8 9.219l2.116-2.54a.5.5 0 1 1 .768.641L8.651 10l2.233 2.68a.5.5 0 0 1-.768.64L8 10.781l-2.116 2.54a.5.5 0 0 1-.768-.641L7.349 10 5.116 7.32a.5.5 0 1 1 .768-.64"/></svg>
                Xuất Excel
            </a>
            <a href="/thidua/quan-ly-dang-ky-truc" class="px-4 py-2 bg-white border border-slate-300 rounded text-slate-700 hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 font-medium flex items-center justify-center gap-2 text-sm shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-calendar-check-fill" viewBox="0 0 16 16"><path d="M4 .5a.5.5 0 0 0-1 0V1H2a2 2 0 0 0-2 2v1h16V3a2 2 0 0 0-2-2h-1V.5a.5.5 0 0 0-1 0V1H4zM16 14V5H0v9a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2m-5.146-5.146-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L7.5 10.793l2.646-2.647a.5.5 0 0 1 .708.708"/></svg>
                Đăng ký trực
            </a>
            <button onclick="document.getElementById('createDailyCodeModal').classList.remove('hidden'); setTimeout(() => {document.getElementById('createDailyCodeModal').classList.remove('opacity-0'); document.getElementById('createDailyCodeModal').querySelector('.modal-content').classList.remove('opacity-0', 'scale-95', 'translate-y-4'); document.getElementById('createDailyCodeModal').querySelector('.modal-content').classList.add('opacity-100', 'scale-100', 'translate-y-0');}, 10);" class="px-4 py-2 bg-white border border-slate-300 rounded text-slate-700 hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 font-medium flex items-center justify-center gap-2 text-sm shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-calendar-day" viewBox="0 0 16 16"><path d="M4.684 11.523v-2.3h2.261v-.61H4.684V6.801h2.464v-.61H4v5.332zm3.296 0h.676V8.98c0-.554.227-1.007.953-1.007.125 0 .258.004.329.015v-.613a2 2 0 0 0-.254-.02c-.582 0-.891.32-1.012.567h-.02v-.504H7.98zm2.805-5.093c0 .238.192.425.43.425a.428.428 0 1 0 0-.855.426.426 0 0 0-.43.43m.094 5.093h.672V7.418h-.672z"/><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/></svg>
                Tạo mã theo ngày
            </button>
            <button onclick="document.getElementById('createCodeModal').classList.remove('hidden'); setTimeout(() => {document.getElementById('createCodeModal').classList.remove('opacity-0'); document.getElementById('createCodeModal').querySelector('.modal-content').classList.remove('opacity-0', 'scale-95', 'translate-y-4'); document.getElementById('createCodeModal').querySelector('.modal-content').classList.add('opacity-100', 'scale-100', 'translate-y-0');}, 10);" class="px-4 py-2 bg-[#224397] border border-transparent rounded text-white hover:bg-[#FAB723] hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 font-medium flex items-center justify-center gap-2 text-sm shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/></svg>
                Tạo mã tùy chỉnh
            </button>
        </div>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded shadow-sm border border-[#224397]/25 overflow-hidden mb-6">
        <div class="bg-slate-50 px-5 py-3 border-b border-[#224397]/25 font-semibold text-[#224397] flex flex-wrap justify-between items-center text-sm uppercase gap-3">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-list-ul" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm-3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/></svg>
                Danh sách mã đã tạo
            </div>
        </div>
        <div class="p-0">
            <div class="overflow-x-auto list-scrollbar w-full">
                <table class="w-full text-left text-sm text-slate-600" id="codesTable">
                    <thead class="bg-slate-50 border-b border-[#224397]/25 text-xs uppercase font-semibold text-slate-500 sticky top-0">
                        <tr>
                            <th class="p-3 w-12 text-center border-r border-[#224397]/20">
                                <input type="checkbox" class="w-4 h-4 rounded border-slate-300 text-[#224397] focus:ring-[#224397] cursor-pointer" id="selectAllCheckbox">
                            </th>
                            <th class="p-3">Mã</th>
                            <th class="p-3">Tên Đợt</th>
                            <th class="p-3">Đối Tượng</th>
                            <th class="p-3 text-center">Số Lượng</th>
                            <th class="p-3">Thời Gian Hiệu Lực</th>
                            <th class="p-3 text-center">Trạng Thái</th>
                            <th class="p-3 text-center">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody" class="divide-y divide-[#224397]/20">
                        <tr>
                            <td colspan="8" class="text-center py-10 text-slate-500">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang tải...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tạo Mã Tùy Chỉnh -->
<div id="createCodeModal" class="fixed inset-0 z-[10005] hidden flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0">
    <div class="modal-content bg-white rounded shadow-2xl border border-slate-300 w-full max-w-[600px] flex flex-col transform transition-all duration-300 scale-95 translate-y-4 opacity-0 max-h-[90vh]">
        <!-- Header -->
        <div class="bg-slate-50 border-b border-[#224397]/25 px-5 py-4 flex justify-between items-center shrink-0">
            <h5 class="text-[#224397] font-bold flex items-center gap-2 text-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/></svg>
                Tạo Mã Kích Hoạt Mới
            </h5>
            <button type="button" class="close-modal-btn text-slate-400 hover:text-red-500 transition-colors" data-modal="createCodeModal">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16"><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg>
            </button>
        </div>
        <!-- Body -->
        <div class="p-5 overflow-y-auto list-scrollbar">
            <form id="createCodeForm">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Tên đợt kích hoạt <span class="text-red-500">*</span></label>
                    <input type="text" name="ten_dot_kich_hoat" class="block w-full rounded border border-slate-300 text-sm p-2 shadow-sm focus:border-[#224397] focus:ring focus:ring-[#224397] focus:ring-opacity-20 outline-none transition-colors" placeholder="Ví dụ: Cấp mã khối 10 tháng 9" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Đối tượng áp dụng</label>
                    <select name="doi_tuong_ap_dung" class="block w-full rounded border border-slate-300 text-sm p-2 shadow-sm focus:border-[#224397] focus:ring focus:ring-[#224397] focus:ring-opacity-20 outline-none transition-colors">
                        <option value="all">Toàn trường</option>
                        <?php if (isset($ds_khoi)) : foreach ($ds_khoi as $khoi) : ?>
                            <option value="khoi_<?php echo htmlspecialchars($khoi['khoi']); ?>">Khối <?php echo htmlspecialchars($khoi['khoi']); ?></option>
                        <?php endforeach; endif; ?>
                        <?php if (isset($ds_lop)) : foreach ($ds_lop as $lop) : ?>
                            <option value="lop_<?php echo htmlspecialchars($lop['id']); ?>">Lớp <?php echo htmlspecialchars($lop['ten_lop']); ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Số lượng sử dụng tối đa <span class="text-red-500">*</span></label>
                    <input type="number" name="so_luong_toi_da" min="1" value="10" class="block w-full rounded border border-slate-300 text-sm p-2 shadow-sm focus:border-[#224397] focus:ring focus:ring-[#224397] focus:ring-opacity-20 outline-none transition-colors" required>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Bắt đầu từ (Tùy chọn)</label>
                        <input type="datetime-local" name="thoi_gian_bat_dau" class="block w-full rounded border border-slate-300 text-sm p-2 shadow-sm focus:border-[#224397] focus:ring focus:ring-[#224397] focus:ring-opacity-20 outline-none transition-colors">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Hết hạn vào (Tùy chọn)</label>
                        <input type="datetime-local" name="thoi_gian_het_han" class="block w-full rounded border border-slate-300 text-sm p-2 shadow-sm focus:border-[#224397] focus:ring focus:ring-[#224397] focus:ring-opacity-20 outline-none transition-colors">
                    </div>
                </div>
            </form>
        </div>
        <!-- Footer -->
        <div class="bg-slate-50 border-t border-[#224397]/25 px-5 py-3 flex justify-end gap-2 shrink-0">
            <button type="button" class="close-modal-btn px-4 py-2 bg-white border border-slate-300 rounded text-slate-700 hover:bg-slate-100 font-medium transition-all duration-300" data-modal="createCodeModal">
                Hủy
            </button>
            <button type="button" id="saveCodeBtn" class="px-4 py-2 bg-[#224397] text-white rounded hover:bg-[#FAB723] font-medium shadow-sm hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-save" viewBox="0 0 16 16"><path d="M2 1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H9.5a1 1 0 0 0-1 1v7.293l2.646-2.647a.5.5 0 0 1 .708.708l-3.5 3.5a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L7.5 9.293V2a2 2 0 0 1 2-2H14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h2.5a.5.5 0 0 1 0 1z"/></svg>
                Tạo mã
            </button>
        </div>
    </div>
</div>

<!-- Modal Tạo Mã Theo Ngày -->
<div id="createDailyCodeModal" class="fixed inset-0 z-[10005] hidden flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0">
    <div class="modal-content bg-white rounded shadow-2xl border border-slate-300 w-full max-w-[600px] flex flex-col transform transition-all duration-300 scale-95 translate-y-4 opacity-0 max-h-[90vh]">
        <!-- Header -->
        <div class="bg-slate-50 border-b border-[#224397]/25 px-5 py-4 flex justify-between items-center shrink-0">
            <h5 class="text-[#224397] font-bold flex items-center gap-2 text-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-calendar-day" viewBox="0 0 16 16"><path d="M4.684 11.523v-2.3h2.261v-.61H4.684V6.801h2.464v-.61H4v5.332zm3.296 0h.676V8.98c0-.554.227-1.007.953-1.007.125 0 .258.004.329.015v-.613a2 2 0 0 0-.254-.02c-.582 0-.891.32-1.012.567h-.02v-.504H7.98zm2.805-5.093c0 .238.192.425.43.425a.428.428 0 1 0 0-.855.426.426 0 0 0-.43.43m.094 5.093h.672V7.418h-.672z"/><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/></svg>
                Tạo mã kích hoạt theo ngày
            </h5>
            <button type="button" class="close-modal-btn text-slate-400 hover:text-red-500 transition-colors" data-modal="createDailyCodeModal">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16"><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg>
            </button>
        </div>
        <!-- Body -->
        <div class="p-5 overflow-y-auto list-scrollbar">
            <form id="createDailyCodeForm">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Chọn tuần học <span class="text-red-500">*</span></label>
                    <select name="tuan_id" class="block w-full rounded border border-slate-300 text-sm p-2 shadow-sm focus:border-[#224397] focus:ring focus:ring-[#224397] focus:ring-opacity-20 outline-none transition-colors" required>
                        <option value="">-- Vui lòng chọn tuần --</option>
                        <?php if (isset($ds_tuan)) : foreach ($ds_tuan as $tuan) : ?>
                            <option value="<?php echo $tuan['id']; ?>"><?php echo htmlspecialchars($tuan['ten_tuan']); ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Đối tượng áp dụng</label>
                    <select name="doi_tuong_ap_dung" class="block w-full rounded border border-slate-300 text-sm p-2 shadow-sm focus:border-[#224397] focus:ring focus:ring-[#224397] focus:ring-opacity-20 outline-none transition-colors" required>
                        <option value="all">Toàn trường</option>
                        <?php if (isset($ds_khoi)) : foreach ($ds_khoi as $khoi) : ?>
                            <option value="khoi_<?php echo htmlspecialchars($khoi['khoi']); ?>">Khối <?php echo htmlspecialchars($khoi['khoi']); ?></option>
                        <?php endforeach; endif; ?>
                        <?php if (isset($ds_lop)) : foreach ($ds_lop as $lop) : ?>
                            <option value="lop_<?php echo htmlspecialchars($lop['id']); ?>">Lớp <?php echo htmlspecialchars($lop['ten_lop']); ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Số lượng sử dụng tối đa cho MỖI NGÀY <span class="text-red-500">*</span></label>
                    <input type="number" name="so_luong_toi_da" min="1" value="5" class="block w-full rounded border border-slate-300 text-sm p-2 shadow-sm focus:border-[#224397] focus:ring focus:ring-[#224397] focus:ring-opacity-20 outline-none transition-colors" required>
                </div>
            </form>
        </div>
        <!-- Footer -->
        <div class="bg-slate-50 border-t border-[#224397]/25 px-5 py-3 flex justify-end gap-2 shrink-0">
            <button type="button" class="close-modal-btn px-4 py-2 bg-white border border-slate-300 rounded text-slate-700 hover:bg-slate-100 font-medium transition-all duration-300" data-modal="createDailyCodeModal">
                Hủy
            </button>
            <button type="button" id="saveDailyCodeBtn" class="px-4 py-2 bg-[#224397] text-white rounded hover:bg-[#FAB723] font-medium shadow-sm hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-lightning-charge" viewBox="0 0 16 16"><path d="M11.251.068a.5.5 0 0 1 .227.58L9.677 6.5H13a.5.5 0 0 1 .364.843l-8 8.5a.5.5 0 0 1-.842-.49L6.323 9.5H3a.5.5 0 0 1-.364-.843l8-8.5a.5.5 0 0 1 .615-.09zM4.157 8.5H7a.5.5 0 0 1 .478.647L6.11 13.59l5.732-6.09H9a.5.5 0 0 1-.478-.647L9.89 2.41 4.157 8.5z"/></svg>
                Tạo mã
            </button>
        </div>
    </div>
</div>

<!-- Modal Xem HS Đã Dùng Mã -->
<div id="viewUsersModal" class="fixed inset-0 z-[10005] hidden flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0">
    <div class="modal-content bg-white rounded shadow-2xl border border-slate-300 w-full max-w-[800px] flex flex-col transform transition-all duration-300 scale-95 translate-y-4 opacity-0 max-h-[90vh]">
        <!-- Header -->
        <div class="bg-slate-50 border-b border-[#224397]/25 px-5 py-4 flex justify-between items-center shrink-0">
            <h5 class="text-[#224397] font-bold flex items-center gap-2 text-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-people" viewBox="0 0 16 16"><path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1zm-7.978-1L7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002-.014.002zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0M6.936 9.28a6 6 0 0 0-1.23-.247A7 7 0 0 0 5 9c-4 0-5 3-5 4q0 1 1 1h4.216A2.24 2.24 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816M4.92 10A5.5 5.5 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0m3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4"/></svg>
                Danh sách học sinh dùng mã: <span id="modalCode"></span>
            </h5>
            <button type="button" class="close-modal-btn text-slate-400 hover:text-red-500 transition-colors" data-modal="viewUsersModal">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16"><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg>
            </button>
        </div>
        <!-- Body -->
        <div class="p-0 overflow-y-auto list-scrollbar">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-[#224397]/25 text-xs uppercase font-semibold text-slate-500 sticky top-0">
                    <tr>
                        <th class="p-3 border-r border-[#224397]/20">Mã HS</th>
                        <th class="p-3">Họ và Tên</th>
                        <th class="p-3">Lớp</th>
                        <th class="p-3">Ngày Kích Hoạt</th>
                    </tr>
                </thead>
                <tbody id="userListBody" class="divide-y divide-[#224397]/20">
                    <tr><td colspan="4" class="text-center py-6 text-slate-500">Đang tải...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>

<script>
window.classesMap = {
<?php if (isset($ds_lop)) : foreach ($ds_lop as $lop) : ?>
    'lop_<?php echo htmlspecialchars($lop['id']); ?>': 'Lớp <?php echo htmlspecialchars($lop['ten_lop']); ?>',
<?php endforeach; endif; ?>
};
document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.getElementById('tableBody');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
    
    // JS Helper to open/close modals properly
    function openLocalModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.querySelector('.modal-content').classList.remove('opacity-0', 'scale-95', 'translate-y-4');
            modal.querySelector('.modal-content').classList.add('opacity-100', 'scale-100', 'translate-y-0');
        }, 10);
    }
    function closeLocalModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.add('opacity-0');
        modal.querySelector('.modal-content').classList.remove('opacity-100', 'scale-100', 'translate-y-0');
        modal.querySelector('.modal-content').classList.add('opacity-0', 'scale-95', 'translate-y-4');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    document.querySelectorAll('.close-modal-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            closeLocalModal(this.dataset.modal);
        });
    });

    function escapeHtml(text) {
        if (!text) return '';
        return String(text).replace(/[&<>"'`=\/]/g, function (s) {
            return {
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
                '/': '&#x2F;', '`': '&#x60;', '=': '&#x3D;'
            }[s];
        });
    }

    async function loadCodes() {
        tableBody.innerHTML = '<tr><td colspan="8" class="text-center py-10"><span class="spinner-border spinner-border-sm text-[#224397]"></span> Đang tải dữ liệu...</td></tr>';
        try {
            const r = await fetch('/thidua/admin/ctv?action=api_get_codes', {credentials: 'same-origin'});
            const j = await r.json();
            if (j.success && Array.isArray(j.data) && j.data.length > 0) {
                renderTable(j.data);
            } else {
                tableBody.innerHTML = '<tr><td colspan="8" class="text-center py-10 text-slate-500 italic">Không có mã kích hoạt nào trong hệ thống.</td></tr>';
            }
        } catch (e) {
            tableBody.innerHTML = '<tr><td colspan="8" class="text-center py-10 text-red-500">Lỗi khi tải dữ liệu!</td></tr>';
        }
    }

    function renderTable(codes) {
        tableBody.innerHTML = codes.map(code => {
            let statusHtml = '';
            if (code.trang_thai === 'active') statusHtml = '<span class="px-2.5 py-1 rounded-full bg-green-100 text-green-700 text-[11px] font-semibold">Đang hoạt động</span>';
            else if (code.trang_thai === 'expired') statusHtml = '<span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 text-[11px] font-semibold">Đã hết hạn</span>';
            else if (code.trang_thai === 'pending') statusHtml = '<span class="px-2.5 py-1 rounded-full bg-blue-100 text-blue-700 text-[11px] font-semibold">Chờ kích hoạt</span>';
            else if (code.trang_thai === 'disabled') statusHtml = '<span class="px-2.5 py-1 rounded-full bg-red-100 text-red-700 text-[11px] font-semibold">Vô hiệu hóa</span>';
            
            let doiTuongText = code.doi_tuong_ap_dung;
            if (doiTuongText === 'all') doiTuongText = 'Toàn trường';
            else if (doiTuongText.startsWith('khoi_')) doiTuongText = 'Khối ' + doiTuongText.replace('khoi_', '');
            else if (window.classesMap && window.classesMap[doiTuongText]) doiTuongText = window.classesMap[doiTuongText];
            else if (doiTuongText.startsWith('lop_')) doiTuongText = 'Lớp ' + doiTuongText.replace('lop_', '');
            
            let timeInfo = '';
            if (code.thoi_gian_bat_dau && code.thoi_gian_het_han) {
                timeInfo = `<div class="text-xs text-slate-500">Từ: ${new Date(code.thoi_gian_bat_dau).toLocaleString('vi-VN')}</div>
                            <div class="text-xs text-slate-500 mt-1">Đến: ${new Date(code.thoi_gian_het_han).toLocaleString('vi-VN')}</div>`;
            } else if (code.thoi_gian_bat_dau) {
                timeInfo = `<div class="text-xs text-slate-500">Từ: ${new Date(code.thoi_gian_bat_dau).toLocaleString('vi-VN')}</div>`;
            } else if (code.thoi_gian_het_han) {
                timeInfo = `<div class="text-xs text-slate-500">Đến: ${new Date(code.thoi_gian_het_han).toLocaleString('vi-VN')}</div>`;
            } else {
                timeInfo = '<span class="text-slate-400 italic text-xs">Không giới hạn</span>';
            }

            let usageColor = (code.so_luong_da_dung >= code.so_luong_toi_da) ? 'text-red-500 font-bold' : 'text-[#224397]';
            
            const btnActionClass = (code.trang_thai === 'active' || code.trang_thai === 'pending') 
                                    ? 'bg-red-50 text-red-600 hover:bg-red-600 hover:text-white border border-red-200' 
                                    : 'bg-green-50 text-green-600 hover:bg-green-600 hover:text-white border border-green-200';
            const btnActionIcon = (code.trang_thai === 'active' || code.trang_thai === 'pending')
                                    ? '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-stop-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="M5 6.5A1.5 1.5 0 0 1 6.5 5h3A1.5 1.5 0 0 1 11 6.5v3A1.5 1.5 0 0 1 9.5 11h-3A1.5 1.5 0 0 1 5 9.5z"/></svg>'
                                    : '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-play-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="M6.271 5.055a.5.5 0 0 1 .52.038l3.5 2.5a.5.5 0 0 1 0 .814l-3.5 2.5A.5.5 0 0 1 6 10.5v-5a.5.5 0 0 1 .271-.445"/></svg>';
            const btnActionTitle = (code.trang_thai === 'active' || code.trang_thai === 'pending') ? 'Ngừng hoạt động' : 'Kích hoạt lại';

            return `
            <tr class="hover:bg-slate-50 transition">
                <td class="p-3 text-center border-r border-[#224397]/20">
                    <input type="checkbox" class="w-4 h-4 rounded border-slate-300 text-[#224397] focus:ring-[#224397] cursor-pointer row-checkbox" data-id="${code.id}">
                </td>
                <td class="p-3 font-bold text-slate-800 tracking-wider font-mono">${escapeHtml(code.ma_kich_hoat)}</td>
                <td class="p-3 font-medium text-[#224397]">${escapeHtml(code.ten_dot_kich_hoat)}</td>
                <td class="p-3 text-slate-600">${escapeHtml(doiTuongText)}</td>
                <td class="p-3 text-center ${usageColor}">${code.so_luong_da_dung} / ${code.so_luong_toi_da}</td>
                <td class="p-3">${timeInfo}</td>
                <td class="p-3 text-center">${statusHtml}</td>
                <td class="p-3 text-center">
                    <div class="flex justify-center gap-1.5">
                        <button class="px-2 py-1 text-xs font-medium bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white border border-blue-200 rounded shadow-sm hover:-translate-y-1 hover:scale-110 transition-all duration-300 view-users-btn flex items-center justify-center gap-1" data-id="${code.id}" data-code="${escapeHtml(code.ma_kich_hoat)}" title="Xem danh sách dùng mã">
                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/></svg>
                        </button>
                        <button class="px-2 py-1 text-xs font-medium rounded shadow-sm hover:-translate-y-1 hover:scale-110 transition-all duration-300 toggle-status-btn flex items-center justify-center gap-1 ${btnActionClass}" data-id="${code.id}" data-action="${code.trang_thai}" title="${btnActionTitle}">
                            ${btnActionIcon}
                        </button>
                    </div>
                </td>
            </tr>`;
        }).join('');
        updateDeleteButtonState();
    }

    function updateDeleteButtonState() {
        const n = tableBody.querySelectorAll('.row-checkbox:checked').length;
        deleteSelectedBtn.disabled = n === 0;
        deleteSelectedBtn.querySelector('.text').textContent = n ? `Xóa (${n}) mục đã chọn` : 'Xóa mục đã chọn';
    }

    selectAllCheckbox.addEventListener('change', function() {
        tableBody.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = this.checked);
        updateDeleteButtonState();
    });

    tableBody.addEventListener('change', function(e) {
        if (e.target.classList.contains('row-checkbox')) {
            updateDeleteButtonState();
            const total = tableBody.querySelectorAll('.row-checkbox').length;
            const checked = tableBody.querySelectorAll('.row-checkbox:checked').length;
            selectAllCheckbox.checked = (total > 0 && total === checked);
        }
    });

    // Delete Bulk
    deleteSelectedBtn.addEventListener('click', async function() {
        const ids = [...tableBody.querySelectorAll('.row-checkbox:checked')].map(cb => cb.dataset.id);
        if (!ids.length) return;
        if (!confirm(`Xóa ${ids.length} mã đã chọn? Thao tác này không thể hoàn tác!`)) return;
        
        const prevHtml = this.innerHTML;
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang xóa...';
        
        try {
            const r = await fetch('/thidua/admin/ctv?action=api_delete_codes', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                credentials: 'same-origin',
                body: JSON.stringify({ids})
            });
            const j = await r.json();
            if (j.success) {
                if(typeof showToast==='function') showToast('success', 'Đã xóa các mã đã chọn.');
                else alert('Đã xóa thành công!');
                selectAllCheckbox.checked = false;
                loadCodes();
            } else {
                if(typeof showToast==='function') showToast('error', j.message || 'Không thể xóa.');
                else alert('Lỗi: ' + (j.message || 'Không thể xóa.'));
            }
        } catch (e) {
            if(typeof showToast==='function') showToast('error', 'Lỗi mạng hoặc server.');
            else alert('Lỗi mạng hoặc server.');
        } finally {
            this.disabled = false;
            this.innerHTML = prevHtml;
        }
    });

    // Save Custom Code
    document.getElementById('saveCodeBtn').addEventListener('click', async function() {
        const form = document.getElementById('createCodeForm');
        const data = Object.fromEntries(new FormData(form).entries());
        
        if (!data.ten_dot_kich_hoat || !data.so_luong_toi_da) {
            if(typeof showToast==='function') showToast('warning', 'Vui lòng nhập Tên đợt và Số lượng.');
            else alert('Vui lòng nhập Tên đợt và Số lượng.');
            return;
        }
        if (data.thoi_gian_bat_dau && data.thoi_gian_het_han && new Date(data.thoi_gian_bat_dau) > new Date(data.thoi_gian_het_han)) {
            if(typeof showToast==='function') showToast('warning', 'Thời gian bắt đầu phải trước thời gian hết hạn.');
            else alert('Thời gian bắt đầu phải trước thời gian hết hạn.');
            return;
        }

        const prevHtml = this.innerHTML;
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang lưu...';
        
        try {
            const r = await fetch('/thidua/admin/ctv?action=api_create_code', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                credentials: 'same-origin',
                body: JSON.stringify(data)
            });
            const j = await r.json();
            if (j.success) {
                closeLocalModal('createCodeModal');
                form.reset();
                if(typeof showToast==='function') showToast('success', j.message || 'Đã tạo mã thành công.');
                else alert('Tạo mã thành công!');
                loadCodes();
            } else {
                if(typeof showToast==='function') showToast('error', j.message || 'Không thể tạo mã.');
                else alert('Lỗi: ' + (j.message || 'Không thể tạo mã.'));
            }
        } catch (e) {
            if(typeof showToast==='function') showToast('error', 'Lỗi mạng hoặc server.');
            else alert('Lỗi mạng hoặc server.');
        } finally {
            this.disabled = false;
            this.innerHTML = prevHtml;
        }
    });

    // Save Daily Codes
    document.getElementById('saveDailyCodeBtn').addEventListener('click', async function() {
        const form = document.getElementById('createDailyCodeForm');
        const data = Object.fromEntries(new FormData(form).entries());
        
        if (!data.tuan_id) {
            if(typeof showToast==='function') showToast('warning', 'Vui lòng chọn tuần học.');
            else alert('Vui lòng chọn tuần học.');
            return;
        }

        const prevHtml = this.innerHTML;
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang tạo...';
        
        try {
            const r = await fetch('/thidua/admin/ctv?action=api_create_daily_codes', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                credentials: 'same-origin',
                body: JSON.stringify(data)
            });
            const j = await r.json();
            if (j.success) {
                closeLocalModal('createDailyCodeModal');
                form.reset();
                if(typeof showToast==='function') showToast('success', j.message || 'Đã tạo mã theo ngày thành công.');
                else alert('Tạo mã theo ngày thành công!');
                loadCodes();
            } else {
                if(typeof showToast==='function') showToast('error', j.message || 'Không thể tạo mã.');
                else alert('Lỗi: ' + (j.message || 'Không thể tạo mã.'));
            }
        } catch (e) {
            if(typeof showToast==='function') showToast('error', 'Lỗi mạng hoặc server.');
            else alert('Lỗi mạng hoặc server.');
        } finally {
            this.disabled = false;
            this.innerHTML = prevHtml;
        }
    });

    // Actions in table (View Users & Toggle)
    tableBody.addEventListener('click', async function(e) {
        const btnView = e.target.closest('.view-users-btn');
        const btnToggle = e.target.closest('.toggle-status-btn');
        
        if (btnView) {
            const id = btnView.dataset.id;
            const code = btnView.dataset.code;
            document.getElementById('modalCode').textContent = code;
            
            const userBody = document.getElementById('userListBody');
            userBody.innerHTML = '<tr><td colspan="4" class="text-center py-10"><span class="spinner-border spinner-border-sm text-[#224397]"></span> Đang tải...</td></tr>';
            openLocalModal('viewUsersModal');
            
            try {
                const r = await fetch(`/thidua/admin/ctv?action=api_get_code_users&id=${encodeURIComponent(id)}`, {credentials: 'same-origin'});
                const j = await r.json();
                if (j.success && Array.isArray(j.data) && j.data.length > 0) {
                    userBody.innerHTML = j.data.map(u => `
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-3 border-r border-[#224397]/20 font-medium">${escapeHtml(u.ma_hoc_sinh)}</td>
                            <td class="p-3 text-slate-800">${escapeHtml((u.ho_dem || '') + ' ' + (u.ten || ''))}</td>
                            <td class="p-3">${escapeHtml(u.ten_lop || '')}</td>
                            <td class="p-3 text-slate-500">${u.ngay_kich_hoat ? new Date(u.ngay_kich_hoat).toLocaleString('vi-VN') : ''}</td>
                        </tr>
                    `).join('');
                } else {
                    userBody.innerHTML = '<tr><td colspan="4" class="text-center py-10 text-slate-500 italic">Chưa có học sinh nào sử dụng mã này.</td></tr>';
                }
            } catch (_) {
                userBody.innerHTML = '<tr><td colspan="4" class="text-center py-10 text-red-500">Lỗi khi tải danh sách.</td></tr>';
            }
        }
        
        if (btnToggle) {
            const id = btnToggle.dataset.id;
            const action = btnToggle.dataset.action;
            const isActivating = (action !== 'active' && action !== 'pending');
            const msg = isActivating 
                        ? 'Bạn có muốn KÍCH HOẠT LẠI mã này không?' 
                        : 'Bạn có chắc muốn NGỪNG HOẠT ĐỘNG mã này?<br><small class="text-red-500 font-bold">Lưu ý: Tất cả quyền CTV của Học sinh đã dùng mã này sẽ bị THU HỒI.</small>';
            
            const result = await AppSwal.fire({
                title: 'Xác nhận',
                html: msg,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#224397',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Đồng ý',
                cancelButtonText: 'Hủy'
            });
            if (!result.isConfirmed) return;
            
            try {
                const r = await fetch('/thidua/admin/ctv?action=api_toggle_code', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    credentials: 'same-origin',
                    body: JSON.stringify({id})
                });
                const j = await r.json();
                if (j.success) {
                    if(typeof showToast==='function') showToast('success', j.message || 'Đã cập nhật trạng thái.');
                    else alert('Đã cập nhật trạng thái.');
                    loadCodes();
                } else {
                    if(typeof showToast==='function') showToast('error', j.message || 'Cập nhật trạng thái thất bại.');
                    else alert('Lỗi: ' + (j.message || 'Cập nhật trạng thái thất bại.'));
                }
            } catch (_) {
                if(typeof showToast==='function') showToast('error', 'Lỗi mạng hoặc server.');
                else alert('Lỗi mạng hoặc server.');
            }
        }
    });

    // Initial load
    loadCodes();
});
</script>
