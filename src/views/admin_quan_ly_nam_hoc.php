<?php
require_once __DIR__ . '/partials/admin_header.php';
$db = get_db_connection();
$stmt = $db->query("SELECT * FROM nam_hoc ORDER BY is_default DESC, id DESC");
$nam_hocs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="p-6">
    <div class="max-w-6xl mx-auto">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <h3 class="text-2xl font-bold text-[#224397] flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-calendar-range" viewBox="0 0 16 16"><path d="M9 7a1 1 0 0 1 1-1h5v2h-5a1 1 0 0 1-1-1M1 9h4a1 1 0 0 1 0 2H1z"/>   <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/></svg> Quản Lý Năm Học
            </h3>
            <button onclick="openNamHocModal('add')" class="px-4 py-2 bg-white border border-blue-200 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 font-medium flex items-center gap-2 text-sm shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-plus-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>   <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/></svg> Thêm năm học
            </button>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 mb-6">
            <div class="p-0 overflow-x-auto">
                <table class="w-full text-left text-[13px] text-slate-600">
                    <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-6 font-semibold uppercase tracking-wider">ID</th>
                            <th class="py-3 px-4 font-semibold uppercase tracking-wider">Tên Năm Học</th>
                            <th class="py-3 px-4 font-semibold uppercase tracking-wider">Ngày Bắt Đầu</th>
                            <th class="py-3 px-4 font-semibold uppercase tracking-wider">Ngày Kết Thúc</th>
                            <th class="py-3 px-6 font-semibold uppercase tracking-wider text-right">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($nam_hocs as $nh): ?>
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-3 px-6 font-medium text-slate-500"><?php echo $nh['id']; ?></td>
                            <td class="py-3 px-4 font-bold text-[#224397] text-sm"><?php echo htmlspecialchars($nh['ten_nam_hoc']); ?></td>
                            <td class="py-3 px-4"><?php echo $nh['ngay_bat_dau'] ? date('d/m/Y', strtotime($nh['ngay_bat_dau'])) : '<span class="text-slate-400 italic">Chưa đặt</span>'; ?></td>
                            <td class="py-3 px-4"><?php echo $nh['ngay_ket_thuc'] ? date('d/m/Y', strtotime($nh['ngay_ket_thuc'])) : '<span class="text-slate-400 italic">Chưa đặt</span>'; ?></td>
                            <td class="py-3 px-6 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button onclick="try { openNamHocModal('edit', this.dataset.item) } catch(e) { alert('Lỗi: ' + e.message); }" data-item="<?php echo htmlspecialchars(json_encode($nh), ENT_QUOTES, 'UTF-8'); ?>" class="px-2.5 py-1.5 text-xs font-medium bg-white text-[#224397] border border-[#224397]/20 hover:bg-blue-50 rounded shadow-sm hover:-translate-y-1 hover:scale-110 transition-all duration-300 flex items-center gap-1" title="Sửa">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16"><path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/></svg>
                                    </button>
                                    <button onclick="try { deleteNamHoc(<?php echo $nh['id']; ?>) } catch(e) { alert('Lỗi: ' + e.message); }" class="px-2.5 py-1.5 text-xs font-medium bg-white text-red-600 border border-red-200 hover:bg-red-50 rounded shadow-sm hover:-translate-y-1 hover:scale-110 transition-all duration-300 flex items-center gap-1" title="Xóa">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>   <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($nam_hocs)): ?>
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500 bg-white">
                                <div class="flex flex-col items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-inbox text-3xl mb-2 block text-slate-300 mx-auto" viewBox="0 0 16 16"><path d="M4.98 4a.5.5 0 0 0-.39.188L1.54 8H6a.5.5 0 0 1 .5.5 1.5 1.5 0 1 0 3 0A.5.5 0 0 1 10 8h4.46l-3.05-3.812A.5.5 0 0 0 11.02 4zm9.954 5H10.45a2.5 2.5 0 0 1-4.9 0H1.066l.32 2.562a.5.5 0 0 0 .497.438h12.234a.5.5 0 0 0 .496-.438zM3.809 3.563A1.5 1.5 0 0 1 4.981 3h6.038a1.5 1.5 0 0 1 1.172.563l3.7 4.625a.5.5 0 0 1 .105.374l-.39 3.124A1.5 1.5 0 0 1 14.117 13H1.883a1.5 1.5 0 0 1-1.489-1.314l-.39-3.124a.5.5 0 0 1 .106-.374z"/></svg>
                                    Chưa có dữ liệu năm học.
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- Custom Tailwind Modal -->
<div id="namHocModal" class="fixed inset-0 z-[10005] hidden items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0" onclick="closeNamHocModal()">
    <div class="bg-white rounded-xl shadow-2xl w-[500px] max-w-[90%] flex flex-col overflow-hidden border border-slate-300 transform transition-all duration-300 scale-95 translate-y-4 opacity-0 modal-content-box" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h5 class="text-lg font-bold text-[#224397] flex items-center gap-2" id="modalTitle">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-journal-plus text-[#FAB723]" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 5.5a.5.5 0 0 1 .5.5v1.5H10a.5.5 0 0 1 0 1H8.5V10a.5.5 0 0 1-1 0V8.5H6a.5.5 0 0 1 0-1h1.5V6a.5.5 0 0 1 .5-.5"/>   <path d="M3 0h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-1h1v1a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1H1V2a2 2 0 0 1 2-2"/>   <path d="M1 5v-.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0V8h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0v.5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1z"/></svg> Thêm Năm Học
            </h5>
            <button type="button" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition" onclick="closeNamHocModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg>
            </button>
        </div>
        
        <form id="namHocForm">
            <div class="px-6 py-5 space-y-4">
                <input type="hidden" name="action" id="action" value="add">
                <input type="hidden" name="id" id="nh_id">
                
                <div>
                    <label class="block text-[13px] font-semibold text-slate-700 mb-1">Tên Năm Học <span class="text-red-500">*</span></label>
                    <input type="text" class="w-full text-[13px] px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-[#224397] focus:ring-1 focus:ring-[#224397] transition-colors" name="ten_nam_hoc" id="ten_nam_hoc" required placeholder="Ví dụ: 2024-2025">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[13px] font-semibold text-slate-700 mb-1">Ngày Bắt Đầu</label>
                        <input type="date" class="w-full text-[13px] px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-[#224397] focus:ring-1 focus:ring-[#224397] transition-colors" name="ngay_bat_dau" id="ngay_bat_dau">
                    </div>
                    <div>
                        <label class="block text-[13px] font-semibold text-slate-700 mb-1">Ngày Kết Thúc</label>
                        <input type="date" class="w-full text-[13px] px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-[#224397] focus:ring-1 focus:ring-[#224397] transition-colors" name="ngay_ket_thuc" id="ngay_ket_thuc">
                    </div>
                </div>
            </div>
            
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-2">
                <button type="button" class="px-4 py-2 text-[13px] font-medium text-gray-600 bg-white border border-gray-300 rounded shadow-sm hover:bg-gray-50 transition" onclick="closeNamHocModal()">Hủy</button>
                <button type="submit" class="px-4 py-2 text-[13px] font-bold text-slate-900 bg-[#FAB723] border border-[#FAB723] rounded shadow-sm hover:bg-[#e5a61d] transition flex items-center justify-center gap-1.5"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-floppy" viewBox="0 0 16 16"><path d="M11 2H9v3h2z"/>   <path d="M1.5 0h11.586a1.5 1.5 0 0 1 1.06.44l1.415 1.414A1.5 1.5 0 0 1 16 2.914V14.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 14.5v-13A1.5 1.5 0 0 1 1.5 0M1 1.5v13a.5.5 0 0 0 .5.5H2v-4.5A1.5 1.5 0 0 1 3.5 9h9a1.5 1.5 0 0 1 1.5 1.5V15h.5a.5.5 0 0 0 .5-.5V2.914a.5.5 0 0 0-.146-.353l-1.415-1.415A.5.5 0 0 0 13.086 1H13v4.5A1.5 1.5 0 0 1 11.5 7h-7A1.5 1.5 0 0 1 3 5.5V1H1.5a.5.5 0 0 0-.5.5m3 4a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5V1H4zM3 15h10v-4.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5z"/></svg> Lưu lại</button>
            </div>
        </form>
    </div>
</div>

<script>
function openNamHocModal(action, data = null) {
    try {
        if (typeof data === 'string') {
            data = JSON.parse(data);
        }
        document.getElementById('action').value = action;
        const titleObj = document.getElementById('modalTitle');
        
        if(action === 'add') {
            titleObj.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-journal-plus text-[#FAB723]" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 5.5a.5.5 0 0 1 .5.5v1.5H10a.5.5 0 0 1 0 1H8.5V10a.5.5 0 0 1-1 0V8.5H6a.5.5 0 0 1 0-1h1.5V6a.5.5 0 0 1 .5-.5"/>   <path d="M3 0h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-1h1v1a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1H1V2a2 2 0 0 1 2-2"/>   <path d="M1 5v-.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0V8h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0v.5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1z"/></svg> Thêm Năm Học';
            document.getElementById('namHocForm').reset();
            document.getElementById('nh_id').value = '';
        } else {
            titleObj.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-pencil-square text-[#FAB723]" viewBox="0 0 16 16"><path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>   <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/></svg> Cập Nhật Năm Học';
            document.getElementById('nh_id').value = data.id;
            document.getElementById('ten_nam_hoc').value = data.ten_nam_hoc;
            document.getElementById('ngay_bat_dau').value = data.ngay_bat_dau || '';
            document.getElementById('ngay_ket_thuc').value = data.ngay_ket_thuc || '';
        }
        
        const modal = document.getElementById('namHocModal');
        const content = modal.querySelector('.modal-content-box');
        
        // Force inline styles to bypass any Tailwind missing classes
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
        modal.style.zIndex = '10005';
        void modal.offsetWidth; // Force reflow
        
        modal.style.opacity = '1';
        modal.classList.remove('opacity-0');
        
        content.style.transform = 'scale(1) translateY(0)';
        content.style.opacity = '1';
        content.classList.remove('scale-95', 'translate-y-4', 'opacity-0');
    } catch(e) {
        alert('Lỗi hiển thị modal: ' + e.message);
        console.error(e);
    }
}

function closeNamHocModal() {
    const modal = document.getElementById('namHocModal');
    const content = modal.querySelector('.modal-content-box');
    
    modal.style.opacity = '0';
    content.style.transform = 'scale(0.95) translateY(1rem)';
    content.style.opacity = '0';
    
    setTimeout(() => {
        modal.style.display = 'none';
        modal.classList.add('hidden');
    }, 300);
}

document.getElementById('namHocForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    fetch('/thidua/api/nam-hoc-crud', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    }).then(res => res.json()).then(res => {
        if(res.success) {
            closeNamHocModal();
            setTimeout(() => {
                AppSwal.fire('Thành công!', 'Lưu năm học thành công.', 'success').then(() => window.location.reload());
            }, 300);
        } else {
            AppSwal.fire('Lỗi!', res.message || 'Có lỗi xảy ra', 'error');
        }
    });
});

function deleteNamHoc(id) {
    AppSwal.fire({
        title: 'Cảnh Báo Xóa!',
        text: 'Bạn có chắc chắn muốn xóa năm học này? Dữ liệu liên quan có thể bị mất và không thể khôi phục!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Xác nhận Xóa',
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
            fetch('/thidua/api/nam-hoc-crud', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete', id: id })
            }).then(res => res.json()).then(res => {
                if(res.success) window.location.reload();
                else AppSwal.fire('Lỗi!', res.message, 'error');
            });
        }
    });
}

function setDefault(id, name) {
    AppSwal.fire({
        title: 'Xác nhận!',
        text: 'Chuyển năm học này thành mặc định (Năm hiện tại của hệ thống)?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Đồng ý',
        cancelButtonText: 'Hủy',
        customClass: {
            popup: 'bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.15)] border border-slate-200 py-6 px-6',
            title: 'text-[#224397] font-bold text-xl mt-0',
            htmlContainer: 'text-slate-600 font-medium text-[14.5px] mt-2 mb-2',
            actions: 'flex justify-center gap-3 w-full mt-6',
            confirmButton: 'bg-[#224397] text-white rounded-lg px-6 py-2 font-medium shadow-sm hover:bg-[#FAB723] hover:text-slate-900 hover:scale-110 hover:shadow-md transition-all duration-300 outline-none',
            cancelButton: 'bg-white text-slate-600 rounded-lg px-6 py-2 font-medium shadow-sm border border-slate-300 hover:bg-slate-50 transition-all duration-300 outline-none',
            icon: 'scale-[0.85] my-2 text-[#224397]'
        },
        buttonsStyling: false
    }).then((result) => {
        if(result.isConfirmed) {
            fetch('/thidua/api/nam-hoc-crud', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'set_default', id: id })
            }).then(res => res.json()).then(res => {
                if(res.success) {
                    if (window.top !== window.self) {
                        window.top.postMessage({ action: 'nam_hoc_changed', id: id, new_name: name }, '*');
                    }
                    window.location.reload();
                }
                else AppSwal.fire('Lỗi!', res.message, 'error');
            });
        }
    });
}
</script>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>
