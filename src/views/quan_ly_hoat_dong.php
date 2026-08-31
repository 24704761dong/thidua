<?php
// File: src/views/quan_ly_hoat_dong.php
require_once __DIR__ . '/partials/admin_header.php';
?>

<style>
/* Custom scrollbar with arrows matching the school theme */
.list-scrollbar::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}
.list-scrollbar::-webkit-scrollbar-track {
    background: #eef2ff;
    border-left: 1px solid #e2e8f0;
}
.list-scrollbar::-webkit-scrollbar-thumb {
    background: #224397;
    border-radius: 4px;
    border: 1px solid #eef2ff;
}
.list-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #FAB723;
}
.list-scrollbar::-webkit-scrollbar-button:single-button {
    background-color: #eef2ff;
    display: block;
    height: 10px;
    width: 8px;
}
/* Up arrow */
.list-scrollbar::-webkit-scrollbar-button:single-button:vertical:decrement {
    background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='100' height='100' fill='rgb(34, 67, 151)'><polygon points='50,20 10,80 90,80'/></svg>");
    background-size: 6px;
    background-position: center 3px;
    background-repeat: no-repeat;
}
.list-scrollbar::-webkit-scrollbar-button:single-button:vertical:decrement:hover {
    background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='100' height='100' fill='rgb(250, 183, 35)'><polygon points='50,20 10,80 90,80'/></svg>");
}
/* Down arrow */
.list-scrollbar::-webkit-scrollbar-button:single-button:vertical:increment {
    background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='100' height='100' fill='rgb(34, 67, 151)'><polygon points='10,20 90,20 50,80'/></svg>");
    background-size: 6px;
    background-position: center 2px;
    background-repeat: no-repeat;
}
.list-scrollbar::-webkit-scrollbar-button:single-button:vertical:increment:hover {
    background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='100' height='100' fill='rgb(250, 183, 35)'><polygon points='10,20 90,20 50,80'/></svg>");
}
</style>

<!-- Page Content -->
<div class="flex-1 overflow-y-auto bg-transparent p-6 min-h-screen">
    <div class="max-w-7xl mx-auto">
        
        <div class="flex justify-between items-center mb-6 border-b border-[#224397]/25 pb-3">
            <h3 class="text-xl font-bold text-[#224397] flex items-center gap-2 uppercase">
                <i class="fa-solid fa-person-running"></i> Quản Lý Hoạt Động & Điểm Rèn Luyện
            </h3>
            <button onclick="openActivityModal()" class="px-4 py-2 bg-[#224397] text-white rounded shadow-sm hover:bg-[#FAB723] hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 font-medium flex items-center gap-2 text-sm">
                <i class="fa-solid fa-plus"></i> <span class="hidden md:inline">Thêm Hoạt Động Mới</span><span class="inline md:hidden">Thêm Mới</span>
            </button>
        </div>

        <div class="bg-white rounded shadow-sm border border-[#224397]/25 overflow-hidden">
            <div class="bg-slate-50 px-5 py-3 border-b border-[#224397]/25 font-semibold text-[#224397] flex items-center gap-2 text-sm uppercase">
                <i class="fa-solid fa-list-ul"></i> Danh Sách Hoạt Động
            </div>
            <div class="overflow-x-auto list-scrollbar max-h-[75vh]">
                <table id="activitiesTable" class="w-full text-left text-sm text-slate-600 border-collapse relative">
                    <thead class="bg-slate-50 border-b border-[#224397]/25 text-xs uppercase font-semibold text-slate-500 sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-3 text-center w-12">ID</th>
                            <th class="px-4 py-3 min-w-[300px]">Tên hoạt động</th>
                            <th class="px-4 py-3 text-center w-24">Điểm TL</th>
                            <th class="px-4 py-3 text-center w-28">Đăng ký</th>
                            <th class="px-4 py-3 w-40">Thời hạn ĐK</th>
                            <th class="px-4 py-3 text-center w-32">Hiển thị App</th>
                            <th class="px-4 py-3 text-center w-32">Trạng thái</th>
                            <th class="px-4 py-3 text-center w-36">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="activitiesBody" class="divide-y divide-[#224397]/20">
                        <!-- Dữ liệu load qua AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Thêm/Sửa -->
<div id="activityModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[100] hidden items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50">
            <h3 class="text-lg font-bold text-slate-800" id="modalTitle">Thêm hoạt động mới</h3>
            <button onclick="closeActivityModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>
        
        <div class="p-6 overflow-y-auto flex-1">
            <form id="activityForm" class="space-y-5">
                <input type="hidden" id="hoat_dong_id" name="id" value="">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tên hoạt động <span class="text-red-500">*</span></label>
                        <input type="text" id="ten_hoat_dong" name="ten_hoat_dong" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#224397] focus:border-[#224397] outline-none transition-all">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Mô tả ngắn</label>
                        <textarea id="mo_ta_ngan" name="mo_ta_ngan" rows="3" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#224397] focus:border-[#224397] outline-none transition-all" placeholder="Thời gian, địa điểm, nội dung..."></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Điểm tích luỹ (±) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.1" id="diem_tich_luy" name="diem_tich_luy" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#224397] focus:border-[#224397] outline-none transition-all">
                        <p class="text-xs text-slate-500 mt-1">VD: 2.0 (cộng điểm) hoặc -1.0 (trừ điểm)</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Giới hạn số người đăng ký</label>
                        <input type="number" id="so_luong_dang_ky" name="so_luong_dang_ky" value="0" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#224397] focus:border-[#224397] outline-none transition-all">
                        <p class="text-xs text-slate-500 mt-1">Nhập 0 để không giới hạn</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Thời gian Bắt đầu ĐK</label>
                        <input type="datetime-local" id="thoi_gian_bd_dang_ky" name="thoi_gian_bd_dang_ky" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#224397] focus:border-[#224397] outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Thời gian Kết thúc ĐK</label>
                        <input type="datetime-local" id="thoi_gian_kt_dang_ky" name="thoi_gian_kt_dang_ky" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#224397] focus:border-[#224397] outline-none transition-all">
                    </div>

                    <div class="md:col-span-2 relative">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Đối tượng áp dụng</label>
                        <input type="hidden" id="doi_tuong" name="doi_tuong" value="Tất cả">
                        
                        <button type="button" id="doiTuongBtn" onclick="document.getElementById('doiTuongDropdown').classList.toggle('hidden')" class="w-full px-3 py-2 border border-slate-300 rounded-lg flex justify-between items-center bg-white text-left text-slate-700 focus:ring-2 focus:ring-[#224397] outline-none transition-all">
                            <span id="doiTuongText" class="truncate font-medium text-sm">Tất cả học sinh</span>
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        
                        <div id="doiTuongDropdown" class="hidden absolute z-[100] w-full mt-1 bg-white border border-slate-200 rounded-lg shadow-xl max-h-[250px] overflow-y-auto list-scrollbar">
                            <div class="p-2 space-y-1">
                                <!-- Common options -->
                                <label class="flex items-center gap-2 p-2 hover:bg-slate-50 rounded cursor-pointer">
                                    <input type="checkbox" value="Tất cả" class="doi-tuong-cb w-4 h-4 text-[#224397] rounded border-slate-300 focus:ring-[#224397]" onchange="updateDoiTuong(this)">
                                    <span class="text-sm text-slate-700 font-medium">Tất cả học sinh</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 hover:bg-slate-50 rounded cursor-pointer">
                                    <input type="checkbox" value="Khối 10" class="doi-tuong-cb w-4 h-4 text-[#224397] rounded border-slate-300 focus:ring-[#224397]" onchange="updateDoiTuong(this)">
                                    <span class="text-sm text-slate-700 font-medium">Toàn Khối 10</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 hover:bg-slate-50 rounded cursor-pointer">
                                    <input type="checkbox" value="Khối 11" class="doi-tuong-cb w-4 h-4 text-[#224397] rounded border-slate-300 focus:ring-[#224397]" onchange="updateDoiTuong(this)">
                                    <span class="text-sm text-slate-700 font-medium">Toàn Khối 11</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 hover:bg-slate-50 rounded cursor-pointer">
                                    <input type="checkbox" value="Khối 12" class="doi-tuong-cb w-4 h-4 text-[#224397] rounded border-slate-300 focus:ring-[#224397]" onchange="updateDoiTuong(this)">
                                    <span class="text-sm text-slate-700 font-medium">Toàn Khối 12</span>
                                </label>
                                
                                <div class="border-t border-slate-200 my-1"></div>
                                <div class="px-2 py-1 text-xs font-bold text-slate-400 uppercase tracking-wider">Từng lớp cụ thể</div>
                                
                                <div class="grid grid-cols-2 gap-1 px-1">
                                    <?php foreach ($danh_sach_lop ?? [] as $lop): ?>
                                        <label class="flex items-center gap-2 p-1.5 hover:bg-slate-50 rounded cursor-pointer">
                                            <input type="checkbox" value="<?php echo htmlspecialchars($lop['ten_lop']); ?>" class="doi-tuong-cb w-4 h-4 text-[#224397] rounded border-slate-300 focus:ring-[#224397]" onchange="updateDoiTuong(this)">
                                            <span class="text-sm text-slate-700"><?php echo htmlspecialchars($lop['ten_lop']); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2 flex items-center gap-6 p-3 bg-slate-50 rounded-lg border border-slate-200">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="show_tren_app" name="show_tren_app" value="1" checked class="w-4 h-4 text-[#224397] rounded border-slate-300 focus:ring-[#224397]">
                            <span class="text-sm font-medium text-slate-700">Hiển thị trên Zalo Mini App</span>
                        </label>
                        
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="trang_thai" name="trang_thai" value="1" checked class="w-4 h-4 text-[#224397] rounded border-slate-300 focus:ring-[#224397]">
                            <span class="text-sm font-medium text-slate-700">Trạng thái Hoạt động</span>
                        </label>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
            <button onclick="closeActivityModal()" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors text-sm font-medium">Huỷ bỏ</button>
            <button onclick="saveActivity()" class="px-4 py-2 bg-[#224397] text-white rounded-lg hover:bg-blue-700 transition-colors shadow-sm text-sm font-medium flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-floppy-fill" viewBox="0 0 16 16"><path d="M0 1.5A1.5 1.5 0 0 1 1.5 0H3v5.5A1.5 1.5 0 0 0 4.5 7h7A1.5 1.5 0 0 0 13 5.5V0h.086a1.5 1.5 0 0 1 1.06.44l1.415 1.414A1.5 1.5 0 0 1 16 2.914V14.5a1.5 1.5 0 0 1-1.5 1.5H14v-5.5A1.5 1.5 0 0 0 12.5 9h-9A1.5 1.5 0 0 0 2 10.5V16h-.5A1.5 1.5 0 0 1 0 14.5v-13Z"/><path d="M3 16h10v-5.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5V16Zm9-16H4v5.5a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5V0ZM9 1h2v4H9V1Z"/></svg> Lưu lại
            </button>
        </div>
    </div>
</div>

<script src="/thidua/public/assets/libs/jquery-3.7.0.min.js"></script>

<script>
    $(document).ready(function() {
        loadActivities();
    });

    function loadActivities() {
        $.ajax({
            url: '/thidua/api/hoat-dong-crud',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ action: 'list' }),
            success: function(res) {
                if (res.success) {
                    let html = '';
                    if (!res.data || res.data.length === 0) {
                        html = `<tr><td colspan="8" class="px-5 py-8 text-center text-slate-500">Chưa có hoạt động nào.</td></tr>`;
                    } else {
                        res.data.forEach(row => {
                            const val = parseFloat(row.diem_tich_luy);
                            let diemHtml = `<span class="text-slate-600 font-bold">${val}</span>`;
                            if (val > 0) diemHtml = `<span class="text-green-600 font-bold">+${val}</span>`;
                            if (val < 0) diemHtml = `<span class="text-red-600 font-bold">${val}</span>`;

                            const count = row.dang_ky_count || 0;
                            const max = row.so_luong_dang_ky == 0 ? '∞' : row.so_luong_dang_ky;
                            
                            let timeHtml = '<span class="text-slate-400 text-xs font-medium">Không giới hạn</span>';
                            if (row.thoi_gian_bd_dang_ky && row.thoi_gian_kt_dang_ky) {
                                const startStr = row.thoi_gian_bd_dang_ky.replace(' ', 'T');
                                const endStr = row.thoi_gian_kt_dang_ky.replace(' ', 'T');
                                const start = new Date(startStr).toLocaleString('vi-VN', {day:'2-digit', month:'2-digit', hour:'2-digit', minute:'2-digit'});
                                const end = new Date(endStr).toLocaleString('vi-VN', {day:'2-digit', month:'2-digit', hour:'2-digit', minute:'2-digit'});
                                timeHtml = `<div class="text-xs font-medium">${start}<br><span class="text-slate-400">đến</span><br>${end}</div>`;
                            }

                            const appHtml = row.show_tren_app == 1
                                ? `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill text-green-500 mx-auto" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>` 
                                : `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-circle-fill text-slate-300 mx-auto" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/></svg>`;
                            
                            const statusHtml = row.trang_thai == 1
                                ? `<span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-md font-medium">Hoạt động</span>` 
                                : `<span class="px-2 py-1 bg-slate-100 text-slate-600 text-xs rounded-md font-medium">Đã khoá</span>`;

                            const descHtml = row.mo_ta_ngan ? `<div class="text-xs text-slate-500 mt-1 truncate max-w-xs" title="${row.mo_ta_ngan.replace(/"/g, '&quot;')}">${row.mo_ta_ngan}</div>` : '';

                            html += `
                                <tr class="hover:bg-slate-50 transition border-b border-[#224397]/10 last:border-b-0">
                                    <td class="px-4 py-4 text-center font-medium">${row.id}</td>
                                    <td class="px-4 py-4">
                                        <div class="font-semibold text-slate-800 text-sm">${row.ten_hoat_dong}</div>
                                        ${descHtml}
                                    </td>
                                    <td class="px-4 py-4 text-center">${diemHtml}</td>
                                    <td class="px-4 py-4 text-center">
                                        <span class="px-2 py-1 bg-blue-50 text-[#224397] border border-blue-200 text-xs rounded-md font-medium">${count} / ${max}</span>
                                    </td>
                                    <td class="px-4 py-4 text-center">${timeHtml}</td>
                                    <td class="px-4 py-4 text-center">${appHtml}</td>
                                    <td class="px-4 py-4 text-center">${statusHtml}</td>
                                    <td class="px-4 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="/thidua/admin/hoat-dong-diem-danh?id=${row.id}" class="px-3 py-1.5 bg-white border border-slate-300 rounded shadow-sm text-sm font-medium text-slate-700 hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 flex items-center justify-center" title="Điểm danh & Quét QR">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-qr-code" viewBox="0 0 16 16"><path d="M2 2h2v2H2V2Z"/><path d="M6 0v6H0V0h6ZM5 1H1v4h4V1ZM4 12H2v2h2v-2Z"/><path d="M6 10v6H0v-6h6Zm-5 1v4h4v-4H1Zm11-9h2v2h-2V2Z"/><path d="M10 0v6h6V0h-6Zm5 1v4h-4V1h4ZM8 1V0h1v2H8v2H7V1h1Zm0 5V4h1v2H8ZM6 8V7h1V6h1v2h1V7h5v1h-4v1H7V8H6Zm0 0v1H2V8H1v1H0V7h3v1h3Zm10 1h-1V7h1v2Zm-1 0h-1v2h2v-1h-1V9Zm-4 0h2v1h-1v1h-1V9Zm2 3v-1h-1v1h-1v1H9v1h3v-2h1Zm0 0h3v1h-2v1h-1v-2Zm-4-1v1h1v-2H7v1h2Z"/><path d="M7 12h1v3h4v1H7v-4Zm9 2v2h-3v-1h2v-1h1Z"/></svg>
                                            </a>
                                            <button onclick="editActivity(${row.id})" class="px-3 py-1.5 bg-white border border-slate-300 rounded shadow-sm text-sm font-medium text-slate-700 hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 flex items-center justify-center" title="Chỉnh sửa">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-pencil-fill" viewBox="0 0 16 16"><path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708l-3-3zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207l6.5-6.5zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.499.499 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11l.178-.178z"/></svg>
                                            </button>
                                            <button onclick="deleteActivity(${row.id})" class="px-3 py-1.5 bg-white border border-red-200 rounded shadow-sm text-sm font-medium text-red-600 hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 flex items-center justify-center" title="Xoá">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16"><path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5M8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5m3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });
                    }
                    $('#activitiesBody').html(html);
                } else {
                    $('#activitiesBody').html(`<tr><td colspan="8" class="px-5 py-8 text-center text-red-500">Lỗi: ${res.message}</td></tr>`);
                }
            },
            error: function() {
                $('#activitiesBody').html(`<tr><td colspan="8" class="px-5 py-8 text-center text-red-500">Lỗi kết nối máy chủ!</td></tr>`);
            }
        });
    }

    function openActivityModal() {
        $('#activityForm')[0].reset();
        $('#hoat_dong_id').val('');
        $('#modalTitle').text('Thêm hoạt động mới');
        
        $('#doi_tuong').val('Tất cả');
        $('#doiTuongText').text('Tất cả học sinh');
        $('.doi-tuong-cb').prop('checked', false);
        $('.doi-tuong-cb[value="Tất cả"]').prop('checked', true);

        $('#activityModal').removeClass('hidden').addClass('flex');
    }

    function closeActivityModal() {
        $('#activityModal').removeClass('flex').addClass('hidden');
    }

    function editActivity(id) {
        AppSwal.showLoading();
        $.ajax({
            url: '/thidua/api/hoat-dong-crud',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ action: 'get', id: id }),
            success: function(res) {
                AppSwal.close();
                if(res.success) {
                    const data = res.data;
                    $('#hoat_dong_id').val(data.id);
                    $('#ten_hoat_dong').val(data.ten_hoat_dong);
                    $('#mo_ta_ngan').val(data.mo_ta_ngan);
                    $('#diem_tich_luy').val(data.diem_tich_luy);
                    $('#so_luong_dang_ky').val(data.so_luong_dang_ky);
                    
                    const dt = data.doi_tuong || 'Tất cả';
                    $('#doi_tuong').val(dt);
                    $('#doiTuongText').text(dt);
                    $('.doi-tuong-cb').prop('checked', false);
                    const arr = dt.split(',').map(s => s.trim());
                    arr.forEach(val => {
                        $(`.doi-tuong-cb[value="${val}"]`).prop('checked', true);
                    });
                    
                    if(data.thoi_gian_bd_dang_ky) $('#thoi_gian_bd_dang_ky').val(data.thoi_gian_bd_dang_ky.replace(' ', 'T'));
                    if(data.thoi_gian_kt_dang_ky) $('#thoi_gian_kt_dang_ky').val(data.thoi_gian_kt_dang_ky.replace(' ', 'T'));

                    $('#show_tren_app').prop('checked', data.show_tren_app == 1);
                    $('#trang_thai').prop('checked', data.trang_thai == 1);

                    $('#modalTitle').text('Chỉnh sửa hoạt động');
                    $('#activityModal').removeClass('hidden').addClass('flex');
                } else {
                    showToast('error', res.message || 'Lỗi lấy dữ liệu');
                }
            }
        });
    }

    function saveActivity() {
        if (!$('#ten_hoat_dong').val().trim()) {
            showToast('warning', 'Vui lòng nhập tên hoạt động');
            return;
        }

        const data = {
            action: $('#hoat_dong_id').val() ? 'edit' : 'add',
            id: $('#hoat_dong_id').val(),
            ten_hoat_dong: $('#ten_hoat_dong').val(),
            mo_ta_ngan: $('#mo_ta_ngan').val(),
            diem_tich_luy: $('#diem_tich_luy').val(),
            so_luong_dang_ky: $('#so_luong_dang_ky').val(),
            doi_tuong: $('#doi_tuong').val(),
            thoi_gian_bd_dang_ky: $('#thoi_gian_bd_dang_ky').val() || null,
            thoi_gian_kt_dang_ky: $('#thoi_gian_kt_dang_ky').val() || null,
            show_tren_app: $('#show_tren_app').is(':checked') ? 1 : 0,
            trang_thai: $('#trang_thai').is(':checked') ? 1 : 0
        };

        $.ajax({
            url: '/thidua/api/hoat-dong-crud',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(res) {
                if(res.success) {
                    showToast('success', res.message || 'Lưu thành công!');
                    closeActivityModal();
                    loadActivities();
                } else {
                    showToast('error', res.message || 'Lỗi lưu dữ liệu');
                }
            }
        });
    }

    function deleteActivity(id) {
        AppSwal.fire({
            title: 'Xác nhận xoá',
            text: "Bạn có chắc chắn muốn xoá hoạt động này? Mọi dữ liệu đăng ký và điểm danh của hoạt động này sẽ bị xoá vĩnh viễn.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Đồng ý xoá',
            cancelButtonText: 'Huỷ'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/thidua/api/hoat-dong-crud',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ action: 'delete', id: id }),
                    success: function(res) {
                        if(res.success) {
                            showToast('success', 'Đã xoá thành công');
                            loadActivities();
                        } else {
                            showToast('error', res.message || 'Không thể xoá');
                        }
                    }
                });
            }
        });
    }

    function updateDoiTuong(clickedCb) {
        const val = clickedCb.value;
        const isChecked = clickedCb.checked;
        
        if (val === 'Tất cả' && isChecked) {
            $('.doi-tuong-cb').not(clickedCb).prop('checked', false);
        } else if (isChecked && val !== 'Tất cả') {
            $('.doi-tuong-cb[value="Tất cả"]').prop('checked', false);
        }

        const selected = [];
        $('.doi-tuong-cb:checked').each(function() {
            selected.push($(this).val());
        });

        if (selected.length === 0) {
            $('.doi-tuong-cb[value="Tất cả"]').prop('checked', true);
            selected.push('Tất cả');
        }

        $('#doi_tuong').val(selected.join(', '));
        const text = selected.join(', ');
        $('#doiTuongText').text(text.length > 40 ? text.substring(0, 40) + '...' : text);
    }

    $(document).on('click', function(e) {
        if (!$(e.target).closest('.md\\:col-span-2.relative').length) {
            $('#doiTuongDropdown').addClass('hidden');
        }
    });

</script>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>

