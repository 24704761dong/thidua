<?php
$page_title = "Xử lý Trễ học";
require_once __DIR__ . '/partials/admin_header.php';
?>

<style>
    body { background-color: #f4f7f9; }
    table thead th { background-color: rgba(34,67,151,0.08) !important; color: #224397 !important; font-weight: 800 !important; text-transform: uppercase; font-size: 0.82rem; padding: 0.65rem 0.85rem; border: 1px solid rgba(34,67,151,0.25) !important; }
    table td { padding: 0.65rem 0.85rem; border: 1px solid rgba(34,67,151,0.25) !important; vertical-align: middle; font-size: 0.83rem; font-weight: 600; color: #1e293b; }
    table tbody tr:hover { background-color: rgba(34,67,151,0.04) !important; }
    .btn-primary, .btn-std { background: #224397 !important; color: white !important; border-color: #224397 !important; }
    body::-webkit-scrollbar { display: block !important; width: 8px; }
    body::-webkit-scrollbar-thumb { background: rgba(34,67,151,0.3); border-radius: 4px; }
/* ===== Style tinh gọn cho trang Trễ học ===== */
.th-toolbar{gap:.5rem 1rem}
.th-card{border:1px solid rgba(0,0,0,.06);border-radius:.75rem}
.th-card .card-header{background:#fff;border-bottom:1px solid rgba(0,0,0,.06)}
.table thead th{position:sticky;top:0;background:#f8f9fa;z-index:1;border-bottom:1px solid #e9ecef}
.table tbody tr:hover{background:rgba(0,0,0,.02)}
.table td,.table th{vertical-align:middle}
.badge{font-weight:500}
.btn-sm-back{border-radius:999px}
.file-name{font-size:.9rem;color:#6c757d;max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
@media (max-width:576px){
  .th-toolbar{flex-direction:column;align-items:stretch}
  .th-toolbar .btn-sm{justify-content:center}
}
    body::-webkit-scrollbar, html::-webkit-scrollbar { display: block !important; width: 8px; height: 8px; }
    body::-webkit-scrollbar-thumb, html::-webkit-scrollbar-thumb { background: rgba(34,67,151,0.3); border-radius: 4px; }
    body::-webkit-scrollbar-track, html::-webkit-scrollbar-track { background: transparent; }
    /* Hover button chuan */
    .btn-std, a[href]:not(.report-action-card):not(.hub-card) { transition: all 0.2s; }
    a.btn:hover, button.btn:hover { background: #FAB723 !important; color: white !important; border-color: #FAB723 !important; }
</style>

<div class="w-full max-w-7xl mx-auto px-6 sm:px-4 lg:px-5">
    <h1 class="h4 mb-0 font-semibold text-primary-600">
        XỬ LÝ TRỄ - <?php echo htmlspecialchars($tuan_hoc['ten_tuan']); ?> - (<?= date('d/m/Y', strtotime($tuan_hoc['ngay_bat_dau'])); ?> - <?= date('d/m/Y', strtotime($tuan_hoc['ngay_ket_thuc'])); ?>)
    </h1>
    
    <div class="flex flex-wrap items-center justify-between mb-6 gap-4">
        <!-- Quay lại -->
        <a href="/thidua/admin/vi-pham?tuan_id=<?php echo $tuan_hoc['id']; ?>" class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm whitespace-nowrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/></svg> Quay lại
        </a>

        <!-- Cài đặt & Import -->
        <div class="flex items-center gap-2">
            <a href="/thidua/admin/xu-ly-tre-hoc/cai-dat" class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm whitespace-nowrap">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-gear-fill" viewBox="0 0 16 16"><path d="M9.405 1.05c-.413-1.4-2.397-1.4-2.81 0l-.1.34a1.464 1.464 0 0 1-2.105.872l-.31-.17c-1.283-.698-2.686.705-1.987 1.987l.169.311c.446.82.023 1.841-.872 2.105l-.34.1c-1.4.413-1.4 2.397 0 2.81l.34.1a1.464 1.464 0 0 1 .872 2.105l-.17.31c-.698 1.283.705 2.686 1.987 1.987l.311-.169a1.464 1.464 0 0 1 2.105.872l.1.34c.413 1.4 2.397 1.4 2.81 0l.1-.34a1.464 1.464 0 0 1 2.105-.872l.31.17c1.283.698 2.686-.705 1.987-1.987l-.169-.311a1.464 1.464 0 0 1 .872-2.105l.34-.1c1.4-.413 1.4-2.397 0-2.81l-.34-.1a1.464 1.464 0 0 1-.872-2.105l.17-.31c.698-1.283-.705-2.686-1.987-1.987l-.311.169a1.464 1.464 0 0 1-2.105-.872zM8 10.93a2.929 2.929 0 1 1 0-5.86 2.929 2.929 0 0 1 0 5.858z"/></svg> Cài đặt
            </a>

            <label for="excel_file_input" class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm whitespace-nowrap cursor-pointer mb-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-arrow-up-fill" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M6.354 9.854a.5.5 0 0 1-.708-.708l2-2a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1-.708.708L8.5 8.707V12.5a.5.5 0 0 1-1 0V8.707z"/></svg> Nhập Import Excel
            </label>
            <span id="excel_file_name" class="text-sm text-slate-500 font-medium hidden"></span>
            <input type="file" id="excel_file_input" class="hidden" accept=".xlsx,.xls">
        </div>
    </div>

    <!-- Bảng -->
    <div class="bg-white rounded-xl shadow-sm border border-[#224397]/25 mb-6 overflow-hidden">
        <div class="px-6 py-6 border-b border-slate-200 bg-slate-50 rounded-t-xl font-semibold flex justify-between items-center">
            <h6 class="m-0 font-semibold text-[#224397] flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-list-ul text-[#224397]" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m-3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/></svg> Danh sách học sinh đi trễ (Nháp)
            </h6>
            <button id="finalizeBtn" class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm whitespace-nowrap disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check-circle-fill fa-sm" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg> Hoàn tất
            </button>
        </div>

        <div class="p-0">
            <div class="overflow-x-auto w-full">
                <table class="w-full text-sm" style="border:1px solid rgba(34,67,151,0.25);border-collapse:collapse" id="tardinessTable">
                    <thead>
                        <tr class="text-center">
                            <th style="width:64px">STT</th>
                            <th style="width:120px">Mã HS</th>
                            <th class="text-left">Họ và tên</th>
                            <th style="width:120px">Lớp</th>
                            <th class="text-left">Nội dung trễ</th>
                        </tr>
                    </thead>
                    <tbody id="tardinessTableBody">
                        <tr><td colspan="5" class="text-center py-6">Vui lòng import file Excel danh sách đi trễ.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.showToast !== 'function') {
        window.showToast = (t,m,type)=>alert(`[${(type||'info').toUpperCase()}] ${t?t+': ':''}${m||''}`);
    }

    const fileInput   = document.getElementById('excel_file_input');
    const fileNameEl  = document.getElementById('excel_file_name');
    const tableBody   = document.getElementById('tardinessTableBody');
    const finalizeBtn = document.getElementById('finalizeBtn');
    const tuanId      = <?php echo json_encode($tuan_hoc['id']); ?>;

    fileInput.addEventListener('change', () => {
        const f = fileInput.files?.[0];
        if (f) { fileNameEl.textContent = f.name; fileNameEl.classList.remove('hidden'); }
        else   { fileNameEl.textContent = '';     fileNameEl.classList.add('hidden'); }
    });

    fileInput.addEventListener('change', async function(e) {
        const file = e.target.files[0];
        if (!file) return;

        tableBody.innerHTML = '<tr><td colspan="5" class="text-center py-6"><div class="spinner-border" role="status"></div> Đang xử lý file...</td></tr>';
        finalizeBtn.disabled = true;

        const formData = new FormData();
        formData.append('tardiness_file', file);
        formData.append('tuan_id', tuanId);

        try {
            const response = await fetch('/thidua/api/admin/tre-hoc/xu-ly-import', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });
            const result = await response.json();
            if (result.success) {
                renderTable(result.data || []);
                finalizeBtn.disabled = !(result.data && result.data.length > 0);
                if (result.errors && result.errors.length > 0) {
                    showToast('Cảnh báo', result.errors.join('\\n- '), 'warning');
                }
            } else {
                throw new Error(result.message || 'Không thể xử lý file.');
            }
        } catch (error) {
            tableBody.innerHTML = `<tr><td colspan="5" class="text-center text-red-600 py-6">Lỗi: ${escapeHtml(error.message)}</td></tr>`;
        }
    });

    function renderTable(data) {
        tableBody.innerHTML = '';
        if (!Array.isArray(data) || data.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="5" class="text-center py-6">Không tìm thấy học sinh "đi trễ" hợp lệ trong file.</td></tr>';
            return;
        }

        data.forEach((row, index) => {
            let chips = '';
            if (Array.isArray(row.ngay_tre)) {
                chips = row.ngay_tre.map(d => `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 mr-1 mb-1">${escapeHtml(d)}</span>`).join(' ');
            } else {
                chips = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">${escapeHtml(row.ngay_tre || '')}</span>`;
            }

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="text-center">${index + 1}</td>
                <td class="text-center">${escapeHtml(row.ma_hs || '')}</td>
                <td>${escapeHtml(row.ho_ten || '')}</td>
                <td class="text-center">${escapeHtml(row.lop || '')}</td>
                <td>${chips || '<span class="text-slate-500">-</span>'}</td>
            `;
            tableBody.appendChild(tr);
        });
    }

    function escapeHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
    }

    finalizeBtn.addEventListener('click', async function() {
        AppSwal.fire({
            title: 'Xác Nhận',
            text: 'Bạn có chắc muốn hoàn tất? Thao tác này sẽ tạo vi phạm cho các học sinh trong danh sách.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Hoàn tất',
            cancelButtonText: 'Hủy'
        }).then(async (result_swal) => {
            if (!result_swal.isConfirmed) return;

            const btn = document.getElementById('finalizeBtn');
            btn.disabled = true;
            const prev = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang xử lý...';

            try {
                const response = await fetch('/thidua/api/admin/tre-hoc/hoan-tat', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ tuan_id: tuanId })
                });
                const result = await response.json();
                showToast(result.success ? 'Thành công' : 'Kết quả', result.message || '', result.success ? 'success' : 'info');

                if (result.success) {
                    tableBody.innerHTML = '<tr><td colspan="5" class="text-center py-6 text-green-600">Đã hoàn tất.</td></tr>';
                } else {
                    btn.disabled = false;
                }
            } catch (error) {
                showToast('Lỗi', error.message, 'error');
                btn.disabled = false;
            } finally {
                btn.innerHTML = prev;
            }
        });
    });
});
</script>
