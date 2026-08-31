<?php
$page_title = 'Quản Lý Đăng Ký Trực';
require_once __DIR__ . '/partials/admin_header.php';

// Giả định các biến đã được nạp
$danh_sach_dang_ky = $danh_sach_dang_ky ?? [];
?>

<div class="w-full px-2 lg:px-6 mt-4">
    <div class="bg-white rounded shadow border border-[#224397]/25 mb-6 p-0 overflow-hidden">
        <div class="px-4 py-3 border-b border-[#224397]/20 bg-[#224397]/5 flex flex-wrap justify-between items-center">
            <h3 class="mb-0 text-[15px] font-bold text-[#224397] uppercase flex items-center">
                DANH SÁCH ĐĂNG KÝ TRỰC
            </h3>
            
            <div class="flex items-center gap-1.5 flex-wrap mt-2 sm:mt-0">
                <a href="/thidua/admin/quan-ly-ma-ctv" class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-key-fill" viewBox="0 0 16 16"><path d="M3.5 11.5a3.5 3.5 0 1 1 3.163-5H14L15.5 8 14 9.5l-1-1-1 1-1-1-1 1-1-1-1 1H6.663a3.5 3.5 0 0 1-3.163 2M2.5 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/></svg>
                    Quản lý mã kích hoạt
                </a>
                <a href="/thidua/xuat-ds-truc-tuan" class="px-2 py-1 bg-[#107c41] border border-[#107c41] rounded text-white hover:bg-[#185c37] transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-excel-fill" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M5.884 6.68 8 9.219l2.116-2.54a.5.5 0 1 1 .768.641L8.651 10l2.233 2.68a.5.5 0 0 1-.768.64L8 10.781l-2.116 2.54a.5.5 0 0 1-.768-.641L7.349 10 5.116 7.32a.5.5 0 1 1 .768-.64"/></svg>
                    Xuất Lịch Sử
                </a>
            </div>
        </div>

        <div class="px-4 pb-4 pt-3">
            <?php if (empty($danh_sach_dang_ky)): ?>
                <div class="p-8 text-center text-slate-500 italic">Chưa có lớp nào gửi danh sách đăng ký trực.</div>
            <?php else: ?>
                <div class="overflow-x-auto w-full border border-slate-200 rounded">
                    <table class="w-full text-left border-collapse" id="dangKyTrucTable">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-3 py-2 text-[12px] font-semibold text-slate-700">STT</th>
                                <th class="px-3 py-2 text-[12px] font-semibold text-slate-700">Tuần</th>
                                <th class="px-3 py-2 text-[12px] font-semibold text-slate-700">Lớp</th>
                                <th class="px-3 py-2 text-[12px] font-semibold text-slate-700">Người Gửi</th>
                                <th class="px-3 py-2 text-[12px] font-semibold text-slate-700">Thời Gian Gửi</th>
                                <th class="px-3 py-2 text-[12px] font-semibold text-slate-700 text-center">Trạng Thái</th>
                                <th class="px-3 py-2 text-[12px] font-semibold text-slate-700 text-center">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $stt = 1; foreach ($danh_sach_dang_ky as $item): ?>
                                <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                    <td class="px-3 py-2 text-[13px] font-medium text-[#224397]"><?php echo $stt++; ?></td>
                                    <td class="px-3 py-2 text-[13px] text-slate-700"><?php echo htmlspecialchars($item['ten_tuan']); ?></td>
                                    <td class="px-3 py-2 text-[13px] text-slate-700 font-bold"><?php echo htmlspecialchars($item['ten_lop']); ?></td>
                                    <td class="px-3 py-2 text-[13px] text-slate-700"><?php echo htmlspecialchars($item['ten_nguoi_gui']); ?></td>
                                    <td class="px-3 py-2 text-[13px] text-slate-600"><?php echo date('d/m/Y H:i', strtotime($item['thoi_gian_gui'])); ?></td>
                                    <td class="px-3 py-2 text-[13px] text-center">
                                        <?php if (isset($item['trang_thai_luu_tru']) && $item['trang_thai_luu_tru'] == 1 && $item['trang_thai'] !== 'Hoàn thành'): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 uppercase">Đã Xóa</span>
                                        <?php elseif ($item['trang_thai'] === 'Hoàn thành'): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 uppercase">Hoàn thành</span>
                                        <?php elseif ($item['trang_thai'] === 'Chờ duyệt'): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 uppercase">Chờ duyệt</span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-800 uppercase">Đã duyệt</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-3 py-2 text-[13px] text-center">
                                        <button class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium inline-flex items-center gap-1 text-[11px] shadow-sm view-details-btn" data-id="<?php echo $item['id']; ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-eye-fill" viewBox="0 0 16 16"><path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/><path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/></svg>
                                            Chi tiết
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Đảm bảo SweetAlert luôn nằm trên modal */
.swal2-container { z-index: 10010 !important; }
/* Đảm bảo modal luôn căn giữa tuyệt đối trong trường hợp lỗi Tailwind */
#detailsModal:not(.hidden), #grantPermissionModal:not(.hidden) {
    display: flex !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    width: 100% !important;
    height: 100% !important;
    align-items: center !important;
    justify-content: center !important;
}
</style>

<!-- Details Modal -->
<div class="fixed inset-0 z-[10005] hidden items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0" id="detailsModal" onclick="closeModal('detailsModal')">
    <div class="bg-white rounded shadow-2xl w-[700px] max-w-[90%] flex flex-col overflow-hidden border border-slate-300 transform transition-all duration-300 scale-95 translate-y-4 opacity-0 modal-content-box" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="bg-slate-50 border-b border-[#224397]/25 px-5 py-4 flex justify-between items-center shrink-0">
            <h5 class="text-[#224397] font-bold flex items-center gap-2 text-[15px] uppercase m-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-text-fill text-[#FAB723]" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M5 4h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1 0-1m-.5 2.5A.5.5 0 0 1 5 6h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5M5 8h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1 0-1m0 2h3a.5.5 0 0 1 0 1H5a.5.5 0 0 1 0-1"/></svg>
                Chi Tiết Đăng Ký
            </h5>
            <button type="button" class="text-slate-400 hover:text-red-500 p-1" onclick="closeModal('detailsModal')">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16"><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.646a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg>
            </button>
        </div>
        <!-- Body -->
        <div class="p-6 overflow-y-auto max-h-[60vh] bg-white" id="detailsModalBody">
            <!-- Nội dung load qua AJAX -->
        </div>
        <!-- Footer -->
        <div class="bg-slate-50 border-t border-[#224397]/25 px-5 py-3 flex justify-between items-center shrink-0" id="detailsModalActions" style="display:none;">
            <button type="button" class="px-3 py-1.5 bg-red-500 border border-transparent rounded text-white hover:bg-red-600 transition-colors font-medium flex items-center gap-1 text-[12px] shadow-sm" id="delete-duty-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16"><path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5M8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5m3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0"/></svg>
                Xóa Đăng Ký
            </button>
            <div class="flex gap-2">
                <button type="button" class="px-3 py-1.5 bg-slate-500 border border-transparent rounded text-white hover:bg-slate-600 transition-colors font-medium flex items-center gap-1 text-[12px] shadow-sm" onclick="closeModal('detailsModal')">Đóng</button>
                <button type="button" class="px-3 py-1.5 bg-green-600 border border-transparent rounded text-white hover:bg-green-700 transition-colors font-medium flex items-center gap-1 text-[12px] shadow-sm" id="approve-duty-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
                    Duyệt và Cấp quyền
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Grant Permission Modal -->
<div class="fixed inset-0 z-[10005] hidden items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0" id="grantPermissionModal" onclick="closeModal('grantPermissionModal')">
    <div class="bg-white rounded shadow-2xl w-[500px] max-w-[90%] flex flex-col overflow-hidden border border-slate-300 transform transition-all duration-300 scale-95 translate-y-4 opacity-0 modal-content-box" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="bg-slate-50 border-b border-[#224397]/25 px-5 py-4 flex justify-between items-center shrink-0">
            <h5 class="text-[#224397] font-bold flex items-center gap-2 text-[15px] uppercase m-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-shield-plus text-[#FAB723]" viewBox="0 0 16 16"><path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56"/> <path d="M8 4.5a.5.5 0 0 1 .5.5v1.5H10a.5.5 0 0 1 0 1H8.5V9a.5.5 0 0 1-1 0V7.5H6a.5.5 0 0 1 0-1h1.5V5a.5.5 0 0 1 .5-.5"/></svg>
                Tùy Chọn Cấp Quyền
            </h5>
            <button type="button" class="text-slate-400 hover:text-red-500 p-1" onclick="closeModal('grantPermissionModal')">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16"><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.646a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg>
            </button>
        </div>
        <!-- Body -->
        <div class="p-6 bg-white space-y-4">
            <p class="text-[13px] text-slate-600 mb-4">Chọn các quyền bạn muốn cấp cho tất cả học sinh trong danh sách trực này. Các quyền học sinh đã có sẽ được giữ nguyên.</p>
            <form id="grantPermissionForm" class="space-y-3">
                <div class="flex items-center">
                    <input class="w-4 h-4 rounded border-slate-300 text-[#224397] focus:ring-[#224397] cursor-pointer" type="checkbox" name="permissions[]" value="nhap_vi_pham" id="perm_nhap_vi_pham" checked>
                    <label class="ml-2 block text-[13px] text-slate-800 cursor-pointer font-medium" for="perm_nhap_vi_pham">Quyền Nhập Vi phạm (Mặc định)</label>
                </div>
          
                <div class="flex items-center">
                    <input class="w-4 h-4 rounded border-slate-300 text-[#224397] focus:ring-[#224397] cursor-pointer" type="checkbox" name="permissions[]" value="dang_ky_truc" id="perm_dang_ky_truc">
                    <label class="ml-2 block text-[13px] text-slate-800 cursor-pointer font-medium" for="perm_dang_ky_truc">Quyền Đăng ký Trực</label>
                </div>
                <hr class="border-slate-200 my-4">
                <p class="text-slate-500 text-[12px] italic mb-0">Nếu không chọn quyền nào, hệ thống sẽ chỉ duyệt danh sách mà không cấp thêm quyền.</p>
            </form>
        </div>
        <!-- Footer -->
        <div class="bg-slate-50 border-t border-[#224397]/25 px-5 py-3 flex justify-end gap-2 shrink-0">
            <button type="button" class="px-3 py-1.5 bg-slate-500 border border-transparent rounded text-white hover:bg-slate-600 transition-colors font-medium flex items-center gap-1 text-[12px] shadow-sm" onclick="closeModal('grantPermissionModal')">Hủy</button>
            <button type="submit" form="grantPermissionForm" class="px-3 py-1.5 bg-[#224397] border border-transparent rounded text-white hover:bg-[#FAB723] transition-colors font-medium flex items-center gap-1 text-[12px] shadow-sm">
                Xác Nhận Duyệt & Cấp Quyền
            </button>
        </div>
    </div>
</div>

<script>
// Modal core logic - đồng bộ toàn hệ thống
function openModal(id) {
    try {
        const modal = document.getElementById(id);
        if (!modal) return;
        const content = modal.querySelector('.modal-content-box');
        
        // Force inline styles to bypass any Tailwind missing classes
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
        modal.style.alignItems = 'center';
        modal.style.justifyContent = 'center';
        modal.style.zIndex = '10005';
        void modal.offsetWidth; // force reflow
        
        modal.style.opacity = '1';
        modal.classList.remove('opacity-0');
        
        if (content) {
            content.style.transform = 'scale(1) translateY(0)';
            content.style.opacity = '1';
            content.classList.remove('scale-95', 'translate-y-4', 'opacity-0');
        }
    } catch(e) {
        console.error('Lỗi hiển thị modal:', e);
    }
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    const content = modal.querySelector('.modal-content-box');
    
    modal.style.opacity = '0';
    modal.classList.add('opacity-0');
    
    if (content) {
        content.style.transform = 'scale(0.95) translateY(1rem)';
        content.style.opacity = '0';
        content.classList.add('scale-95', 'translate-y-4', 'opacity-0');
    }
    
    setTimeout(() => {
        modal.style.display = 'none';
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }, 300);
}

document.addEventListener('DOMContentLoaded', function() {
    let currentDutyId = null;

    document.querySelectorAll('.view-details-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            currentDutyId = this.dataset.id;
            openModal('detailsModal');
            document.getElementById('detailsModalBody').innerHTML = '<div class="text-center py-10"><span class="spinner-border spinner-border-sm text-[#224397]"></span> Đang tải...</div>';
            document.getElementById('detailsModalActions').style.display = 'none';

            fetch(`/thidua/admin/dang-ky-truc/chi-tiet?id=${currentDutyId}`)
                .then(r => r.json())
                .then(res => {
                    if (res.success) { // Fixed mojibake success field!
                        let html = `
                            <table class="w-full text-left border-collapse border border-slate-200">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-200">
                                        <th class="px-3 py-2 text-[12px] font-semibold text-slate-700">Học Sinh</th>
                                        <th class="px-3 py-2 text-[12px] font-semibold text-slate-700">Thứ Trong Tuần</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;
                        res.data.forEach(item => {
                            html += `
                                <tr class="border-b border-slate-100 hover:bg-slate-50">
                                    <td class="px-3 py-2 text-[13px] font-medium text-[#224397]">${escapeHtml(item.ho_dem)} ${escapeHtml(item.ten)}</td>
                                    <td class="px-3 py-2 text-[13px] text-slate-600">${formatThu(item.thu_trong_tuan)}</td>
                                </tr>
                            `;
                        });
                        html += `</tbody></table>`;
                        document.getElementById('detailsModalBody').innerHTML = html;
                        document.getElementById('detailsModalActions').style.display = 'flex';
                    } else {
                        document.getElementById('detailsModalBody').innerHTML = `<div class="text-center py-10 text-red-500">${res.message || 'Lỗi tải dữ liệu'}</div>`;
                    }
                })
                .catch(e => {
                    document.getElementById('detailsModalBody').innerHTML = '<div class="text-center py-10 text-red-500">Lỗi kết nối.</div>';
                });
        });
    });

    document.getElementById('approve-duty-btn').addEventListener('click', function() {
        closeModal('detailsModal');
        setTimeout(() => {
            openModal('grantPermissionModal');
        }, 300);
    });

    document.getElementById('grantPermissionForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('id', currentDutyId);
        
        let submitBtn = document.querySelector('button[form="grantPermissionForm"]');
        let prevText = '';
        if (submitBtn) {
            prevText = submitBtn.innerHTML;
            submitBtn.innerHTML = 'Đang xử lý...';
            submitBtn.disabled = true;
        }

        fetch('/thidua/admin/dang-ky-truc/duyet', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) { // Fixed mojibake success field!
                if(typeof showToast==='function') showToast('success', 'Duyệt thành công!');
                else alert('Duyệt thành công!');
                setTimeout(() => location.reload(), 1000);
            } else {
                if(typeof showToast==='function') showToast('error', res.message || 'Lỗi duyệt');
                else alert(res.message || 'Lỗi duyệt');
                if (submitBtn) {
                    submitBtn.innerHTML = prevText;
                    submitBtn.disabled = false;
                }
            }
        })
        .catch(e => {
            if(typeof showToast==='function') showToast('error', 'Lỗi kết nối');
            else alert('Lỗi kết nối');
            if (submitBtn) {
                submitBtn.innerHTML = prevText;
                submitBtn.disabled = false;
            }
        });
    });

    document.getElementById('delete-duty-btn').addEventListener('click', async function() {
        if (typeof AppSwal !== 'undefined') {
            const result = await AppSwal.fire({
                title: 'Xác nhận xóa',
                html: 'Bạn có chắc chắn muốn xóa danh sách đăng ký này?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#224397',
                confirmButtonText: 'Xóa',
                cancelButtonText: 'Hủy'
            });
            if (!result.isConfirmed) return;
        } else {
            if (!confirm('Bạn có chắc chắn muốn xóa danh sách đăng ký này?')) return;
        }

        const formData = new FormData();
        formData.append('id', currentDutyId);
        fetch('/thidua/admin/dang-ky-truc/xoa', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) { // Fixed mojibake success field!
                if(typeof showToast==='function') showToast('success', 'Xóa thành công!');
                else alert('Xóa thành công!');
                setTimeout(() => location.reload(), 1000);
            } else {
                if(typeof showToast==='function') showToast('error', res.message || 'Lỗi xóa');
                else alert(res.message || 'Lỗi xóa');
            }
        })
        .catch(e => {
            if(typeof showToast==='function') showToast('error', 'Lỗi kết nối');
            else alert('Lỗi kết nối');
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

    function formatThu(thuStr) {
        if (!thuStr && thuStr !== 0 && thuStr !== '0') return '';
        return thuStr.toString().split(',').map(t => {
            const index = parseInt(t.trim(), 10);
            if (isNaN(index)) return t.trim();
            let label = '';
            if (index === 6) label = 'Chủ Nhật';
            else label = 'Thứ ' + (index + 2);
            return `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mr-1 mb-1 shadow-sm">${label}</span>`;
        }).join('');
    }
});
</script>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>