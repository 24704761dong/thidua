<?php
$page_title = 'Nhận Học Sinh Lên Lớp';
require_once __DIR__ . '/partials/admin_header.php';
?>
<div class="w-full px-2 lg:px-6 pt-4">
    <!-- Header Page -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-5">
        <div>
            <h2 class="text-xl font-bold text-[#224397] flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="currentColor" class="bi bi-person-lines-fill text-[#FAB723]" viewBox="0 0 16 16"><path d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5 6s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zM11 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5m.5 2.5a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1zm2 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1zm0 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1z"/></svg>
                NHẬN HỌC SINH LÊN LỚP
            </h2>
            <p class="text-slate-500 text-xs mt-0.5">Chuyển học sinh từ năm học cũ sang năm học mới một cách nhanh chóng và chính xác.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        <!-- Khung chọn dữ liệu (Cột bên trái - 4/12) -->
        <div class="lg:col-span-4 space-y-5">
            <!-- Bộ Lọc -->
            <div class="bg-white rounded shadow border border-[#224397]/25 overflow-hidden">
                <div class="px-4 py-3 border-b border-[#224397]/20 bg-[#224397]/5 flex items-center gap-2 text-[14px] font-bold text-[#224397] uppercase">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-filter" viewBox="0 0 16 16"><path d="M6 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5"/></svg>
                    BỘ LỌC LỚP CŨ
                </div>
                <div class="p-4 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-[#224397] mb-1">Từ Năm Học Cũ</label>
                        <select id="namHocCu" class="block w-full rounded border border-slate-300 text-xs p-2 shadow-sm focus:border-[#224397] focus:ring-1 focus:ring-[#224397] outline-none transition-colors">
                            <option value="">-- Chọn Năm Học Cũ --</option>
                            <?php foreach ($nam_hoc_cu_list as $nh): ?>
                            <option value="<?php echo $nh['id']; ?>"><?php echo htmlspecialchars($nh['ten_nam_hoc']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-[#224397] mb-1">Từ Lớp Cũ</label>
                        <select id="lopHocCu" class="block w-full rounded border border-slate-300 text-xs p-2 shadow-sm focus:border-[#224397] focus:ring-1 focus:ring-[#224397] outline-none transition-colors disabled:bg-slate-100 disabled:text-slate-400" disabled>
                            <option value="">-- Chọn Lớp --</option>
                        </select>
                    </div>

                    <button id="btnLoadHocSinh" class="w-full px-4 py-2 bg-white border border-slate-300 rounded text-slate-700 hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 font-bold flex items-center justify-center gap-2 text-xs shadow-sm disabled:opacity-50 disabled:pointer-events-none" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/></svg> 
                        Tải Danh Sách Học Sinh
                    </button>
                </div>
            </div>

            <!-- Khung chuyển lớp -->
            <div class="bg-white rounded shadow border border-emerald-300 overflow-hidden" id="boxChuyenLop" style="display: none;">
                <div class="px-4 py-3 border-b border-emerald-200 bg-emerald-50/80 font-bold text-emerald-800 flex items-center gap-2 text-[14px] uppercase">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="currentColor" class="bi bi-arrow-right-circle-fill text-emerald-600" viewBox="0 0 16 16"><path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0M4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5z"/></svg>
                    ĐƯA VÀO LỚP MỚI
                </div>
                <div class="p-4 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Vào Lớp (Năm hiện tại)</label>
                        <div class="flex w-full gap-2">
                            <select id="lopHocMoi" class="block w-full rounded border border-slate-300 text-xs p-2 shadow-sm focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 outline-none transition-colors">
                                <option value="">-- Chọn Lớp Mới --</option>
                                <?php foreach ($lop_hoc_moi_list as $lop): ?>
                                <option value="<?php echo $lop['id']; ?>"><?php echo htmlspecialchars($lop['ten_lop']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button class="px-3 py-2 bg-slate-700 border border-transparent rounded text-white hover:bg-slate-800 transition-all font-medium flex items-center justify-center shadow-sm text-xs" type="button" id="btnTaoLopMoi" title="Tạo lớp mới">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/></svg>
                            </button>
                        </div>
                    </div>
                    <button id="btnNhanHocSinh" class="w-full px-4 py-2 bg-[#224397] text-white rounded hover:bg-[#FAB723] font-bold shadow-sm transition-all duration-300 flex items-center justify-center gap-2 text-xs">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/></svg> 
                        Nhận Các Học Sinh Đã Chọn
                    </button>
                </div>
            </div>
        </div>

        <!-- Khung danh sách học sinh (Cột bên phải - 8/12) -->
        <div class="lg:col-span-8">
            <div class="bg-white rounded shadow border border-[#224397]/25 overflow-hidden mb-6">
                <div class="px-4 py-3 border-b border-[#224397]/20 bg-[#224397]/5 flex flex-wrap justify-between items-center text-sm uppercase gap-3">
                    <div class="flex items-center gap-2 text-[14px] font-bold text-[#224397]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-people-fill" viewBox="0 0 16 16"><path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4q0 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/></svg>
                        DANH SÁCH HỌC SINH LỚP CŨ
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" class="w-4 h-4 rounded border-slate-300 text-[#224397] focus:ring-[#224397] cursor-pointer" id="checkAll">
                        <label class="text-xs text-slate-700 font-bold cursor-pointer normal-case" for="checkAll">Chọn Tất Cả</label>
                    </div>
                </div>
                <div class="p-0">
                    <div class="overflow-x-auto list-scrollbar w-full">
                        <table class="w-full text-left text-sm text-slate-600" id="tableHocSinh">
                            <thead class="bg-slate-50 border-b border-[#224397]/25 text-xs uppercase font-semibold text-slate-500 sticky top-0">
                                <tr>
                                    <th class="p-3 w-12 text-center border-r border-[#224397]/20"></th>
                                    <th class="p-3 border-r border-[#224397]/20">Mã HS</th>
                                    <th class="p-3 border-r border-[#224397]/20">Họ Đệm</th>
                                    <th class="p-3 border-r border-[#224397]/20">Tên</th>
                                    <th class="p-3 text-center">Trạng Thái Cũ</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyHocSinh" class="divide-y divide-[#224397]/20">
                                <tr>
                                    <td colspan="5" class="text-center py-10 text-slate-500 italic">Vui lòng chọn năm học và lớp để tải danh sách.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal xác nhận hệ thống -->
<div id="promoteConfirmModal" class="fixed inset-0 z-[10005] hidden flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0">
    <div class="modal-content bg-white rounded shadow-2xl border border-slate-300 w-full max-w-[500px] flex flex-col transform transition-all duration-300 scale-95 translate-y-4 opacity-0">
        <!-- Header -->
        <div class="bg-slate-50 border-b border-[#224397]/25 px-5 py-4 flex justify-between items-center shrink-0">
            <h5 class="text-[#224397] font-bold flex items-center gap-2 text-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-question-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M5.255 5.786a.237.237 0 0 0 .241.247h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286zm1.557 5.763c0 .533.425.927 1.01.927.609 0 1.028-.394 1.028-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94z"/></svg>
                Xác Nhận Nhận Lên Lớp
            </h5>
            <button type="button" class="close-promote-modal text-slate-400 hover:text-red-500 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16"><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg>
            </button>
        </div>
        <!-- Body -->
        <div class="p-5">
            <p class="text-slate-700">Bạn sẽ đưa <strong id="promoteStudentCount" class="text-[#224397]">0</strong> học sinh được chọn vào lớp <strong id="promoteTargetClass" class="text-[#224397]"></strong>.</p>
            <p class="text-slate-500 mt-2 text-sm italic">Thao tác này sẽ cập nhật lớp học và niên khóa mới cho các học sinh này.</p>
        </div>
        <!-- Footer -->
        <div class="bg-slate-50 border-t border-[#224397]/25 px-5 py-3 flex justify-end gap-2 shrink-0">
            <button type="button" class="close-promote-modal px-4 py-2 bg-white border border-slate-300 rounded text-slate-700 hover:bg-slate-100 font-medium transition-all duration-300">
                Hủy
            </button>
            <button type="button" id="confirmPromoteBtn" class="px-4 py-2 bg-[#224397] text-white rounded hover:bg-[#FAB723] font-medium shadow-sm hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check2" viewBox="0 0 16 16"><path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/></svg>
                Đồng Ý
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const namHocCuSelect = document.getElementById('namHocCu');
    const lopHocCuSelect = document.getElementById('lopHocCu');
    const btnLoadHocSinh = document.getElementById('btnLoadHocSinh');
    const boxChuyenLop = document.getElementById('boxChuyenLop');
    const checkAll = document.getElementById('checkAll');
    const tbodyHocSinh = document.getElementById('tbodyHocSinh');
    
    // Modal Elements
    const promoteModal = document.getElementById('promoteConfirmModal');
    const modalContent = promoteModal ? promoteModal.querySelector('.modal-content') : null;
    const confirmPromoteBtn = document.getElementById('confirmPromoteBtn');
    const promoteStudentCountEl = document.getElementById('promoteStudentCount');
    const promoteTargetClassEl = document.getElementById('promoteTargetClass');
    
    let currentSelectedIds = [];
    let currentNewLopId = null;

    // Helper functions for Toast/Alert
    function showAlert(type, message) {
        if (typeof showToast === 'function') {
            showToast(type, message);
        } else {
            alert(message);
        }
    }

    // Modal functions
    function openPromoteModal() {
        promoteModal.classList.remove('hidden');
        setTimeout(() => {
            promoteModal.classList.remove('opacity-0');
            modalContent.classList.remove('opacity-0', 'scale-95', 'translate-y-4');
            modalContent.classList.add('opacity-100', 'scale-100', 'translate-y-0');
        }, 10);
    }

    function closePromoteModal() {
        promoteModal.classList.add('opacity-0');
        modalContent.classList.remove('opacity-100', 'scale-100', 'translate-y-0');
        modalContent.classList.add('opacity-0', 'scale-95', 'translate-y-4');
        setTimeout(() => {
            promoteModal.classList.add('hidden');
        }, 300);
    }

    if (promoteModal) {
        document.querySelectorAll('.close-promote-modal').forEach(btn => {
            btn.addEventListener('click', closePromoteModal);
        });
        promoteModal.addEventListener('click', function(e) {
            if (e.target === promoteModal) closePromoteModal();
        });
    }

    // Load class list when selecting old year
    namHocCuSelect.addEventListener('change', function() {
        const nhId = this.value;
        lopHocCuSelect.innerHTML = '<option value="">-- Chọn Lớp --</option>';
        if(!nhId) {
            lopHocCuSelect.disabled = true;
            btnLoadHocSinh.disabled = true;
            return;
        }

        fetch('/thidua/api/nhan-hoc-sinh', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'get_classes', nam_hoc_id: nhId })
        }).then(res => res.json()).then(res => {
            if(res.success && res.data.length > 0) {
                res.data.forEach(lop => {
                    const option = document.createElement('option');
                    option.value = lop.id;
                    option.textContent = lop.ten_lop;
                    lopHocCuSelect.appendChild(option);
                });
                lopHocCuSelect.disabled = false;
            } else {
                lopHocCuSelect.innerHTML = '<option value="">Không có lớp nào</option>';
            }
        });
    });

    lopHocCuSelect.addEventListener('change', function() {
        btnLoadHocSinh.disabled = !this.value;
    });

    // Load students
    btnLoadHocSinh.addEventListener('click', function() {
        const lopId = lopHocCuSelect.value;
        const nhId = namHocCuSelect.value;
        if(!lopId || !nhId) return;

        tbodyHocSinh.innerHTML = '<tr><td colspan="5" class="text-center py-10 text-slate-500"><span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span> Đang tải dữ liệu...</td></tr>';
        
        fetch('/thidua/api/nhan-hoc-sinh', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'get_students', lop_hoc_id: lopId, nam_hoc_id: nhId })
        }).then(res => res.json()).then(res => {
            if(res.success && res.data.length > 0) {
                tbodyHocSinh.innerHTML = '';
                res.data.forEach(hs => {
                    const tr = document.createElement('tr');
                    
                    // Format status accurately
                    let statusLabel = 'Đang học';
                    let statusClass = 'text-green-600';
                    let isSelectable = true;

                    if (hs.trang_thai_hoc_tap === 'nghi_hoc') {
                        statusLabel = 'Nghỉ học';
                        statusClass = 'text-red-500 font-semibold';
                        isSelectable = false;
                    } else if (hs.trang_thai_hoc_tap === 'da_tot_nghiep') {
                        statusLabel = 'Đã tốt nghiệp';
                        statusClass = 'text-indigo-600 font-semibold';
                        isSelectable = false;
                    }

                    const checkboxHtml = isSelectable 
                        ? `<input type="checkbox" class="w-4 h-4 rounded border-slate-300 text-[#224397] focus:ring-[#224397] cursor-pointer student-checkbox" value="${hs.id}">`
                        : `<input type="checkbox" class="w-4 h-4 rounded border-slate-200 text-slate-300 cursor-not-allowed" disabled title="Học sinh đã ${statusLabel.toLowerCase()}, không thể nhận lên lớp">`;

                    tr.className = isSelectable ? 'hover:bg-slate-50 transition cursor-pointer' : 'bg-slate-50/70 opacity-60 cursor-not-allowed';
                    if (!isSelectable) {
                        tr.title = `Học sinh này đã ${statusLabel.toLowerCase()}, không thể nhận lên lớp`;
                    }

                    tr.innerHTML = `
                        <td class="p-3 text-center border-r border-[#224397]/20">
                            ${checkboxHtml}
                        </td>
                        <td class="p-3 font-bold text-slate-700 ${!isSelectable ? 'text-slate-400' : ''}">${hs.ma_hoc_sinh}</td>
                        <td class="p-3 ${!isSelectable ? 'text-slate-400' : ''}">${hs.ho_dem}</td>
                        <td class="p-3 font-medium text-slate-800 ${!isSelectable ? 'text-slate-400' : ''}">${hs.ten}</td>
                        <td class="p-3 ${statusClass} font-medium">${statusLabel}</td>
                    `;

                    // Clicking on row checks the box only if selectable
                    if (isSelectable) {
                        tr.addEventListener('click', function(e) {
                            if(e.target.type !== 'checkbox') {
                                const cb = tr.querySelector('.student-checkbox');
                                if (cb) cb.checked = !cb.checked;
                            }
                        });
                    }

                    tbodyHocSinh.appendChild(tr);
                });
                boxChuyenLop.style.display = 'block';
                checkAll.checked = false;
            } else {
                tbodyHocSinh.innerHTML = '<tr><td colspan="5" class="text-center py-10 text-slate-500">Lớp này không có học sinh hoặc đã được chuyển đi hết.</td></tr>';
                boxChuyenLop.style.display = 'none';
            }
        });
    });

    checkAll.addEventListener('change', function() {
        document.querySelectorAll('.student-checkbox:not(:disabled)').forEach(cb => {
            cb.checked = this.checked;
        });
    });

    // Trigger Promote Process (Open Modal)
    document.getElementById('btnNhanHocSinh').addEventListener('click', function() {
        const lopMoiSelect = document.getElementById('lopHocMoi');
        currentNewLopId = lopMoiSelect.value;
        if(!currentNewLopId) {
            showAlert('error', 'Vui lòng chọn lớp học mới của năm hiện tại!');
            return;
        }

        currentSelectedIds = Array.from(document.querySelectorAll('.student-checkbox:checked')).map(cb => cb.value);
        if(currentSelectedIds.length === 0) {
            showAlert('warning', 'Vui lòng tick chọn ít nhất 1 học sinh!');
            return;
        }

        // Setup modal info
        promoteStudentCountEl.textContent = currentSelectedIds.length;
        promoteTargetClassEl.textContent = lopMoiSelect.options[lopMoiSelect.selectedIndex].text;
        
        openPromoteModal();
    });

    // Confirm Promote (in Modal)
    if (confirmPromoteBtn) {
        confirmPromoteBtn.addEventListener('click', function() {
            if(currentSelectedIds.length === 0 || !currentNewLopId) return;

            const originalBtnHtml = confirmPromoteBtn.innerHTML;
            confirmPromoteBtn.disabled = true;
            confirmPromoteBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý...';

            fetch('/thidua/api/nhan-hoc-sinh', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    action: 'promote_students', 
                    student_ids: currentSelectedIds, 
                    new_lop_hoc_id: currentNewLopId
                })
            }).then(res => res.json()).then(res => {
                if(res.success) {
                    closePromoteModal();
                    showAlert('success', 'Nhận học sinh thành công!');
                    btnLoadHocSinh.click(); // Reload danh sách cũ
                } else {
                    showAlert('error', 'Lỗi: ' + res.message);
                }
            }).catch(err => {
                showAlert('error', 'Lỗi mạng hoặc server!');
            }).finally(() => {
                confirmPromoteBtn.disabled = false;
                confirmPromoteBtn.innerHTML = originalBtnHtml;
            });
        });
    }

    // Tạo lớp mới
    const btnTaoLopMoi = document.getElementById('btnTaoLopMoi');
    if (btnTaoLopMoi) {
        btnTaoLopMoi.addEventListener('click', function() {
            const processCreate = (tenLop) => {
                fetch('/thidua/api/nhan-hoc-sinh', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        action: 'create_class', 
                        ten_lop: tenLop
                    })
                }).then(res => res.json()).then(res => {
                    if (res.success) {
                        const select = document.getElementById('lopHocMoi');
                        const option = document.createElement('option');
                        option.value = res.data.id;
                        option.textContent = res.data.ten_lop;
                        select.appendChild(option);
                        select.value = res.data.id;
                        showAlert('success', 'Tạo lớp thành công!');
                    } else {
                        showAlert('error', 'Lỗi: ' + res.message);
                    }
                }).catch(err => {
                    showAlert('error', 'Có lỗi xảy ra khi tạo lớp!');
                });
            };

            if (typeof AppSwal !== 'undefined') {
                AppSwal.fire({
                    title: 'Tạo Lớp Mới',
                    input: 'text',
                    inputPlaceholder: 'Nhập tên lớp (Ví dụ: 10A1)...',
                    showCancelButton: true,
                    confirmButtonText: 'Tạo Lớp',
                    cancelButtonText: 'Hủy',
                    inputValidator: (value) => {
                        if (!value || value.trim() === '') {
                            return 'Vui lòng nhập tên lớp!';
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed && result.value) {
                        processCreate(result.value.trim());
                    }
                });
            } else {
                const tenLop = prompt('Nhập tên lớp mới (Ví dụ: 10A1):');
                if (tenLop && tenLop.trim() !== '') {
                    processCreate(tenLop.trim());
                }
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>
