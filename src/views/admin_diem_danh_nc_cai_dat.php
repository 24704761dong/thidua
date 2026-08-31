<?php
// File: src/views/admin_diem_danh_nc_cai_dat.php

$page_title = "Cài đặt Điểm danh Nâng cao";
require_once __DIR__ . '/partials/admin_header.php';
?>

<style>
/* ===== DESIGN SYSTEM ===== */
:root {
    --primary: #224397;
    --accent: #FAB723;
    --border: rgba(34,67,151,0.25);
    --bg-header: rgba(34,67,151,0.08);
    --bg-page: #f4f7f9;
}

body { background-color: var(--bg-page); }

/* ===== TABLE ===== */
#violation-table {
    border: 1px solid var(--border);
    border-collapse: collapse;
    width: 100%;
}
#violation-table thead { background-color: var(--bg-header); }
#violation-table th {
    color: var(--primary);
    font-weight: 800;
    text-transform: uppercase;
    font-size: 0.78rem;
    letter-spacing: 0.5px;
    padding: 0.65rem 0.5rem;
    border: 1px solid var(--border);
    text-align: center;
    white-space: nowrap;
}
#violation-table td {
    padding: 0;
    font-size: 0.82rem;
    border: 1px solid var(--border);
    vertical-align: middle;
    text-align: center;
    position: relative;
    font-weight: 600;
    color: #1e293b;
}

/* Fix padding and sizing */
#violation-table td.row-number,
#violation-table td.student-code,
#violation-table td.delete-col,
#violation-table td.notification-status,
#violation-table td.user-name,
#violation-table td.text-center { padding: 0.5rem 0.4rem; }

#violation-table td.p-0 { padding: 0 !important; height: 1px; }

#violation-table .cell-edit,
#violation-table input.violation-name {
    width: 100%;
    height: 100%;
    min-height: 44px;
    padding: 0.5rem 0.4rem;
    box-sizing: border-box;
    display: flex;
    align-items: center;
    justify-content: center;
    outline: none !important;
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
    border-radius: 0 !important;
    font-size: 0.82rem;
    font-weight: 600;
    text-align: center;
    color: #1e293b;
}
#violation-table input.violation-name { text-align: center; }

#violation-table tbody tr:hover { background-color: rgba(34,67,151,0.03); }

/* Cell focus */
#violation-table .cell-edit:focus,
#violation-table input.violation-name:focus {
    background: rgba(34,67,151,0.06) !important;
    box-shadow: inset 0 0 0 2px rgba(34,67,151,0.3) !important;
}

/* Status dot */
.status-dot {
    position: absolute;
    top: 3px; right: 3px;
    width: 7px; height: 7px;
    border-radius: 50%;
    pointer-events: none;
}
tr[data-status="saved"] .status-dot { background-color: #22c55e; }
tr[data-status="new"] .status-dot { background-color: #f59e0b; }
tr[data-status="modified"] .status-dot { background-color: #ef4444; }

/* Row status colors */
tr[data-status="modified"] { background-color: rgba(250,183,35,0.06) !important; }
tr[data-kxd="1"] td.student-code { color: #dc2626; font-weight: 700; }

/* Cell error */
.input-error { box-shadow: inset 0 0 0 2px #ef4444 !important; background: #fef2f2 !important; }

/* Delete mode */
#violation-table.deletion-mode .delete-col { display: table-cell !important; }
#violation-table:not(.deletion-mode) .delete-col { display: none; }
#violation-table.deletion-mode tbody tr:hover { background-color: rgba(239,68,68,0.06); }

/* Range selection */
#violation-table.range-selecting { user-select: none; }
#violation-table td.cell-selected { background: rgba(34,67,151,0.12) !important; outline: 1px solid rgba(34,67,151,0.5); }

/* Scrollbar */
body::-webkit-scrollbar, html::-webkit-scrollbar { display: block !important; width: 8px !important; height: 8px !important; }
body::-webkit-scrollbar-thumb { background: var(--primary) !important; border-radius: 4px; }
body::-webkit-scrollbar-track { background: transparent !important; }
body, html { scrollbar-width: thin !important; scrollbar-color: var(--primary) transparent !important; -ms-overflow-style: auto !important; }

/* Table scroll */
.table-scroll-wrapper::-webkit-scrollbar { display: block !important; height: 6px; }
.table-scroll-wrapper::-webkit-scrollbar-thumb { background: rgba(34,67,151,0.25); border-radius: 3px; }

/* Utility */
.hidden { display: none !important; }

/* ===== CUSTOM MODAL ===== */
.custom-modal-backdrop {
    position: fixed; inset: 0; z-index: 9999;
    background: rgba(0,0,0,0.45);
    backdrop-filter: blur(3px);
    display: flex; align-items: center; justify-content: center;
    opacity: 0; visibility: hidden;
    transition: all 0.25s ease;
}
.custom-modal-backdrop.show { opacity: 1; visibility: visible; }
.custom-modal-box {
    background: white;
    border-radius: 16px;
    box-shadow: 0 25px 60px rgba(0,0,0,0.2);
    border: 1px solid rgba(34,67,151,0.15);
    max-width: 480px; width: 90%;
    padding: 2rem;
    text-align: center;
    transform: scale(0.9) translateY(-10px);
    transition: all 0.25s cubic-bezier(0.34,1.56,0.64,1);
    max-height: 90vh;
    overflow-y: auto;
}
.custom-modal-backdrop.show .custom-modal-box { transform: scale(1) translateY(0); }
.custom-modal-icon {
    font-size: 3.5rem;
    margin-bottom: 1rem;
    display: flex;
    justify-content: center;
    align-items: center;
}
.custom-modal-title { font-size: 1.3rem; font-weight: 700; margin-bottom: 0.5rem; }
.custom-modal-msg { color: #64748b; font-size: 0.92rem; margin-bottom: 1.5rem; }
.custom-modal-btns { display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap; }

/* Modal Header/Body cho cac form (layout tuong tu modal cu) */
.custom-modal-box.has-header { padding: 0; text-align: left; }
.custom-modal-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid rgba(34,67,151,0.15);
    background: rgba(34,67,151,0.05);
    display: flex; justify-content: space-between; align-items: center;
    border-radius: 16px 16px 0 0;
}
.custom-modal-header h5 { margin: 0; font-size: 1rem; font-weight: 700; color: #224397; text-transform: uppercase; }
.custom-modal-body { padding: 1.5rem; }
.custom-modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid rgba(34,67,151,0.15);
    display: flex; gap: 0.5rem; justify-content: flex-end;
}
.close-btn { background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #64748b; }
.close-btn:hover { color: #dc2626; }

/* ===== TOAST ===== */
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
    animation: toastIn 0.35s ease;
    max-width: 380px;
    border: 1px solid;
}
.toast-success { background: #f0fdf4; color: #166534; border-color: #86efac; }
.toast-error { background: #fef2f2; color: #991b1b; border-color: #fca5a5; }
.toast-warning { background: #fffbeb; color: #92400e; border-color: #fcd34d; }
.toast-info { background: #eff6ff; color: #1e40af; border-color: #93c5fd; }
@keyframes toastIn { from { opacity:0; transform: translateX(30px); } to { opacity:1; transform: translateX(0); } }
@keyframes toastOut { to { opacity:0; transform: translateX(30px); } }

/* ===== BTN CHUAN ===== */
.btn-std {
    padding: 0.3rem 0.65rem;
    background: white;
    border: 1px solid var(--border);
    border-radius: 4px;
    color: var(--primary);
    font-size: 0.6875rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    transition: all 0.2s;
    text-decoration: none;
    white-space: nowrap;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(34,67,151,0.08);
}
.btn-std:hover { background: var(--accent); color: white; border-color: var(--accent); }
.btn-std.active, .btn-std-primary { background: var(--primary); color: white; border-color: var(--primary); }
.btn-std.active:hover, .btn-std-primary:hover { background: var(--accent); border-color: var(--accent); }
.btn-std-danger { background: white; color: #dc2626; border-color: rgba(220,38,38,0.3); }
.btn-std-danger:hover { background: #dc2626; color: white; border-color: #dc2626; }
.btn-std-green { background: white; color: #16a34a; border-color: rgba(22,163,74,0.3); }
.btn-std-green:hover { background: #16a34a; color: white; border-color: #16a34a; }

/* Dropdown */
.dropdown-wrapper { position: relative; }
.dropdown-menu-std {
    position: absolute;
    top: calc(100% + 4px);
    right: 0;
    z-index: 999;
    min-width: 180px;
    background: white;
    border: 1px solid rgba(34,67,151,0.2);
    border-radius: 10px;
    box-shadow: 0 12px 30px rgba(34,67,151,0.15);
    padding: 0.4rem 0;
    opacity: 0; visibility: hidden;
    transform: scale(0.95) translateY(-4px);
    transition: all 0.15s ease;
    transform-origin: top right;
}
.dropdown-menu-std.open { opacity: 1; visibility: visible; transform: scale(1) translateY(0); }
.dropdown-menu-std a {
    display: flex; align-items: center; gap: 0.5rem;
    padding: 0.5rem 1rem;
    font-size: 0.82rem; font-weight: 600;
    color: #334155;
    text-decoration: none;
    transition: all 0.15s;
}
.dropdown-menu-std a:hover { background: rgba(34,67,151,0.06); color: var(--primary); }
.dropdown-menu-std hr { margin: 0.3rem 0; border-color: rgba(34,67,151,0.1); }

/* Loading overlay */
#loading-overlay {
    position: fixed; inset: 0;
    background: rgba(17,24,39,.55);
    backdrop-filter: saturate(160%) blur(2px);
    justify-content: center; align-items: center;
    z-index: 10055; display: none;
}
.spinner-ring {
    width: 52px; height: 52px;
    border: 5px solid rgba(255,255,255,.3);
    border-top: 5px solid white;
    border-radius: 50%;
    animation: spin 0.9s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>

<div class="w-full max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
    <div class="bg-white rounded shadow border border-[#224397]/25 mb-4 p-0 overflow-hidden">
        <div class="px-4 py-3 border-b border-[#224397]/20 bg-[#224397]/5 flex items-center justify-between">
            <h3 class="mb-0 text-[14px] font-bold text-[#224397] uppercase flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-link mr-2" viewBox="0 0 16 16"><path d="M6.354 5.5H4a3 3 0 0 0 0 6h3a3 3 0 0 0 2.83-4H9q-.13 0-.25.031A2 2 0 0 1 7 10.5H4a2 2 0 1 1 0-4h1.535c.218-.376.495-.714.82-1z"/><path d="M9 5.5a3 3 0 0 0-2.83 4h1.098A2 2 0 0 1 9 6.5h3a2 2 0 1 1 0 4h-1.535a4 4 0 0 1-.82 1H12a3 3 0 1 0 0-6z"/></svg> 
                Liên kết Lỗi Vi phạm
            </h3>
            <div class="flex flex-wrap gap-2 items-center">
           
            </div>
        </div>

        <div class="p-4 sm:p-6">
            <p class="text-sm text-slate-500 mb-6 italic">
                Chọn <b>lỗi vi phạm tương ứng</b> cho từng trường hợp. Khi hoàn tất điểm danh, hệ thống sẽ tự động tạo vi phạm với lỗi đã cấu hình tại đây.
            </p>

            <form id="settingForm" class="needs-validation" novalidate>
                <div class="flex flex-wrap -mx-3 gap-y-4">
                    <div class="w-full md:w-1/2 px-3">
                        <label for="loi_vang_p" class="block text-[13px] font-semibold text-[#224397] mb-1">Vắng Có Phép (P) <span class="text-red-500">*</span></label>
                        <select class="block w-full rounded border-slate-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50 text-[13px] p-2 border" id="loi_vang_p" name="diemdanh_loi_vang_p" required>
                            <option value="">-- Chọn lỗi vi phạm tương ứng --</option>
                            <?php foreach ($danh_sach_vi_pham as $vp) : ?>
                                <option value="<?php echo $vp['id']; ?>" <?php echo ($cai_dat_vang_p == $vp['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($vp['ten_vi_pham']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback text-red-500 text-xs mt-1 hidden">Vui lòng chọn lỗi cho trường hợp Vắng có phép.</div>
                    </div>

                    <div class="w-full md:w-1/2 px-3">
                        <label for="loi_vang_kp" class="block text-[13px] font-semibold text-[#224397] mb-1">Vắng Không Phép (KP) <span class="text-red-500">*</span></label>
                        <select class="block w-full rounded border-slate-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50 text-[13px] p-2 border" id="loi_vang_kp" name="diemdanh_loi_vang_kp" required>
                            <option value="">-- Chọn lỗi vi phạm tương ứng --</option>
                            <?php foreach ($danh_sach_vi_pham as $vp) : ?>
                                <option value="<?php echo $vp['id']; ?>" <?php echo ($cai_dat_vang_kp == $vp['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($vp['ten_vi_pham']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback text-red-500 text-xs mt-1 hidden">Vui lòng chọn lỗi cho trường hợp Vắng không phép.</div>
                    </div>

                    <div class="w-full md:w-1/2 px-3 mt-2">
                        <label for="loi_bo_tiet" class="block text-[13px] font-semibold text-[#224397] mb-1">Bỏ Tiết (BT) <span class="text-red-500">*</span></label>
                        <select class="block w-full rounded border-slate-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50 text-[13px] p-2 border" id="loi_bo_tiet" name="diemdanh_loi_bo_tiet" required>
                            <option value="">-- Chọn lỗi vi phạm tương ứng --</option>
                            <?php foreach ($danh_sach_vi_pham as $vp) : ?>
                                <option value="<?php echo $vp['id']; ?>" <?php echo (isset($cai_dat_bo_tiet) && $cai_dat_bo_tiet == $vp['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($vp['ten_vi_pham']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback text-red-500 text-xs mt-1 hidden">Vui lòng chọn lỗi cho trường hợp Bỏ tiết.</div>
                    </div>
                </div>

                <div class="flex justify-between items-center mt-6 pt-4 border-t border-slate-200">
                    <span id="saveStatus" class="text-slate-500 text-sm"></span>
                    <div class="flex gap-2">
                        <button type="reset" class="btn-std">Đặt lại</button>
                        <button type="submit" class="btn-std btn-std-primary" id="saveBtn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-save-fill mr-1" viewBox="0 0 16 16"><path d="M8.5 1.5A1.5 1.5 0 0 1 10 0h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h6c-.314.418-.5.937-.5 1.5v7.793L4.854 6.646a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0l3.5-3.5a.5.5 0 0 0-.708-.708L8.5 9.293z"/></svg> Lưu cài đặt
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>

<script>
    if (typeof window.showToast !== 'function') {
        window.showToast = (title, message, type) =>
            alert(`[${(type||'info').toUpperCase()}] ${title?title+': ':''}${message||''}`);
    }

    (() => {
        const form = document.getElementById('settingForm');
        const saveBtn = document.getElementById('saveBtn');
        const status = document.getElementById('saveStatus');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            // Simple manual validation check
            let isValid = true;
            form.querySelectorAll('select[required]').forEach(sel => {
                if (!sel.value) {
                    isValid = false;
                    sel.classList.add('border-red-500');
                    sel.nextElementSibling.classList.remove('hidden');
                } else {
                    sel.classList.remove('border-red-500');
                    sel.nextElementSibling.classList.add('hidden');
                }
            });
            if (!isValid) return;

            const data = Object.fromEntries(new FormData(form).entries());

            const prev = saveBtn.innerHTML;
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang lưu...';
            status.textContent = 'Đang lưu...';

            try {
                const res = await fetch('/thidua/api/admin/diem-danh-nang-cao/luu-cai-dat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(data)
                });
                const result = await res.json();

                if (result && result.success) {
                    showToast('Thành công', result.message || 'Đã lưu cài đặt.', 'success');
                    status.innerHTML = '<span class="text-green-600"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check-circle-fill mr-1 inline" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>Đã lưu</span>';
                } else {
                    showToast('Lỗi', (result && result.message) || 'Không thể lưu cài đặt.', 'error');
                    status.innerHTML = '<span class="text-red-600">Lưu thất bại</span>';
                }
            } catch (err) {
                showToast('Lỗi Mạng', 'Vui lòng thử lại.', 'error');
                status.innerHTML = '<span class="text-red-600">Lỗi mạng</span>';
            } finally {
                saveBtn.disabled = false;
                saveBtn.innerHTML = prev;
                setTimeout(() => (status.textContent = ''), 3000);
            }
        });
        
        // Remove error styles on change
        form.querySelectorAll('select[required]').forEach(sel => {
            sel.addEventListener('change', () => {
                sel.classList.remove('border-red-500');
                sel.nextElementSibling.classList.add('hidden');
            });
        });
    })();
</script>
