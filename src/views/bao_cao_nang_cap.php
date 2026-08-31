<?php
$page_title = 'Báo Cáo Nâng Cao';
require_once __DIR__ . '/partials/admin_header.php';

// Các biến được nạp từ controller (bao_cao_nang_cap.php)
$all_weeks = $all_weeks ?? [];
$all_classes = $all_classes ?? [];
$danh_sach_bao_cao = $danh_sach_bao_cao ?? [];
?>
<style>
    body { background-color: #f4f7f9; }
    body::-webkit-scrollbar, html::-webkit-scrollbar { display: block !important; width: 8px; height: 8px; }
    body::-webkit-scrollbar-thumb { background: rgba(34,67,151,0.3); border-radius: 4px; }
    body::-webkit-scrollbar-track { background: transparent; }
    
    /* Table chuẩn như các trang báo cáo khác */
    .log-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
    .log-table thead th { 
        background: #f8fafc; color: #1e293b; font-weight: 700; 
        text-transform: uppercase; font-size: 0.75rem; 
        padding: 0.85rem 0.75rem; border: 1px solid #cbd5e1; 
        white-space: nowrap; text-align: center; 
    }
    .log-table td { 
        padding: 0.85rem 0.75rem; border: 1px solid #cbd5e1; 
        vertical-align: middle; color: #334155; 
    }
    .log-table tbody tr:hover { background: #f1f5f9; }

    /* Form UI chuẩn nhỏ gọn */
    .form-select-sm {
        display: block; width: 100%; border-radius: 8px;
        border: 1px solid #cbd5e1; padding: 0.4rem 0.75rem;
        font-size: 14px; color: #1e293b; background: #fff;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-select-sm:focus { outline: none; border-color: #224397; box-shadow: 0 0 0 3px rgba(34,67,151,0.1); }
    
    .btn-action-sm { display: inline-flex; align-items: center; gap: 0.4rem; height: 34px; padding: 0 0.85rem; border-radius: 8px; font-size: 13px; font-weight: 600; border: 1px solid rgba(34,67,151,0.25); background: #fff; color: #224397; transition: all 0.2s; text-decoration: none; cursor: pointer; white-space: nowrap; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .btn-action-sm:hover { background: #FAB723; color: #fff; border-color: #FAB723; }
    
    .btn-action-sm-green { display: inline-flex; align-items: center; gap: 0.4rem; height: 34px; padding: 0 0.85rem; border-radius: 8px; font-size: 13px; font-weight: 600; border: 1px solid rgba(22,163,74,0.25); background: #16a34a; color: #fff; transition: all 0.2s; text-decoration: none; cursor: pointer; white-space: nowrap; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .btn-action-sm-green:hover { background: #15803d; color: #fff; border-color: #15803d; }

    .btn-action-sm-primary { display: inline-flex; align-items: center; gap: 0.4rem; height: 34px; padding: 0 0.85rem; border-radius: 8px; font-size: 13px; font-weight: 600; border: 1px solid rgba(34,67,151,0.25); background: #224397; color: #fff; transition: all 0.2s; text-decoration: none; cursor: pointer; white-space: nowrap; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .btn-action-sm-primary:hover { background: #1e3a8a; color: #fff; border-color: #1e3a8a; }
</style>

<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 pb-6 mt-4">
    <!-- HEADER -->
    <div class="flex flex-wrap items-center justify-between mb-6 gap-3">
        <h1 class="text-xl mb-0 font-bold text-[#224397] flex items-center gap-2 uppercase">
            <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-file-earmark-bar-graph-fill text-[#FAB723]" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1m.5 10v-6a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5m-2.5.5a.5.5 0 0 1-.5-.5v-4a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-.5.5zm-3 0a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5z"/></svg>
            <?php echo 'Báo Cáo Nâng Cao'; ?>
        </h1>
        <a href="/thidua/bao-cao" class="btn-action-sm bg-slate-600 border-slate-600 text-white hover:bg-slate-700 hover:border-slate-700 hover:text-white">
            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/></svg>
            Quay lại
        </a>
    </div>

    <!-- KHU VỰC BẢNG TRUNG TÂM BÁO CÁO -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 mb-6 overflow-hidden">
        <div class="px-6 py-4 bg-[#f8fafc] border-b border-slate-200">
            <p class="text-sm text-slate-600 m-0 font-medium">Đây là trung tâm tổng hợp các mẫu báo cáo phân tích chuyên sâu, kết hợp dữ liệu toàn diện từ nhiều nguồn trong hệ thống.</p>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto w-full rounded-lg border border-slate-200">
                <table class="log-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">STT</th>
                            <th style="width: 220px;">Mã Báo Cáo</th>
                            <th style="text-align: left; padding-left: 1.5rem;">Mô tả chi tiết</th>
                            <th style="width: 180px;">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($danh_sach_bao_cao)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-slate-500 p-8 font-medium">Chưa có báo cáo nào được định nghĩa.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($danh_sach_bao_cao as $index => $bao_cao): ?>
                                <tr>
                                    <td class="text-center font-bold text-slate-400"><?php echo $index + 1; ?></td>
                                    <td class="text-center">
                                        <span class="inline-block bg-indigo-50 text-[#224397] font-bold px-3 py-1 rounded-lg border border-indigo-200 text-xs tracking-wider">
                                            <?php echo htmlspecialchars($bao_cao['ma'] ?? 'N/A'); ?>
                                        </span>
                                    </td>
                                    <td style="text-align: left; padding-left: 1.5rem;">
                                        <p class="mb-0 text-slate-700 font-medium text-sm"><?php echo htmlspecialchars($bao_cao['mieu_ta'] ?? ''); ?></p>
                                    </td>
                                    <td class="text-center">
                                        <?php if (($bao_cao['action_type'] ?? '') === 'modal'): ?>
                                            <button type="button" class="btn-action-sm-green" onclick="openModal('<?php echo ltrim($bao_cao['modal_id'] ?? '', '#'); ?>', '<?php echo htmlspecialchars($bao_cao['url_tai_ve'] ?? '#'); ?>')">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="currentColor" class="bi bi-gear-fill" viewBox="0 0 16 16"><path d="M9.405 1.05c-.413-1.4-2.397-1.4-2.81 0l-.1.34a1.464 1.464 0 0 1-2.105.872l-.31-.17c-1.283-.698-2.686.705-1.987 1.987l.169.311c.446.82.023 1.841-.872 2.105l-.34.1c-1.4.413-1.4 2.397 0 2.81l.34.1a1.464 1.464 0 0 1 .872 2.105l-.17.31c-.698 1.283.705 2.686 1.987 1.987l.311-.169a1.464 1.464 0 0 1 2.105.872l.1.34c.413 1.4 2.397 1.4 2.81 0l.1-.34a1.464 1.464 0 0 1 2.105-.872l.31.17c1.283.698 2.686-.705 1.987-1.987l-.169-.311a1.464 1.464 0 0 1 .872-2.105l.34-.1c1.4-.413 1.4-2.397 0-2.81l-.34-.1a1.464 1.464 0 0 1-.872-2.105l.17-.31c.698-1.283-.705-2.686-1.987-1.987l-.311.169a1.464 1.464 0 0 1-2.105-.872zM8 10.93a2.929 2.929 0 1 1 0-5.86 2.929 2.929 0 0 1 0 5.858z"/></svg>
                                                Tùy chọn & Tải
                                            </button>
                                        <?php else: ?>
                                            <a href="<?php echo htmlspecialchars($bao_cao['url_tai_ve'] ?? '#'); ?>" class="btn-action-sm-primary" target="_blank">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="currentColor" class="bi bi-box-arrow-down" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M3.5 10a.5.5 0 0 1-.5-.5v-8a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 .5.5v8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 0 0 1h2A1.5 1.5 0 0 0 14 9.5v-8A1.5 1.5 0 0 0 12.5 0h-9A1.5 1.5 0 0 0 2 1.5v8A1.5 1.5 0 0 0 3.5 11h2a.5.5 0 0 0 0-1z"/><path fill-rule="evenodd" d="M7.646 15.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 14.293V5.5a.5.5 0 0 0-1 0v8.793l-2.146-2.147a.5.5 0 0 0-.708.708z"/></svg>
                                                Tải Báo Cáo
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CHỌN 1 TUẦN (Pure Tailwind/JS Modal) -->
<div id="chonTuanModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[200] hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden w-full max-w-lg animate-in fade-in zoom-in duration-200">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-[#f8fafc]">
            <h5 class="text-base font-bold text-[#224397] m-0 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="#16a34a" class="bi bi-calendar-check-fill" viewBox="0 0 16 16"><path d="M4 .5a.5.5 0 0 0-1 0V1H2a2 2 0 0 0-2 2v1h16V3a2 2 0 0 0-2-2h-1V.5a.5.5 0 0 0-1 0V1H4zM16 14V5H0v9a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2m-5.146-5.146-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L7.5 10.793l2.646-2.647a.5.5 0 0 1 .708.708"/></svg>
                Chọn Tuần Cần Xuất Báo Cáo
            </h5>
            <button type="button" class="text-slate-400 hover:text-slate-600 text-xl font-bold px-2 py-1" onclick="closeModal('chonTuanModal')">&times;</button>
        </div>
        <form id="chonTuanForm" method="GET" action="" target="_blank">
            <div class="p-6 space-y-4">
                <div>
                    <label for="tuan_hoc_id" class="block text-xs font-bold text-[#224397] mb-2">CHỌN TUẦN HỌC</label>
                    <select class="form-select-sm" id="tuan_hoc_id" name="tuan_hoc_id" required>
                        <option value="">-- Vui lòng chọn một tuần --</option>
                        <?php foreach ($all_weeks as $week): ?>
                            <option value="<?php echo $week['id']; ?>"><?php echo htmlspecialchars($week['ten_tuan']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="flex items-center justify-end px-6 py-4 border-t border-slate-100 bg-[#f8fafc] gap-2">
                <button type="button" class="btn-action-sm bg-slate-100 border-slate-300 text-slate-700 hover:bg-slate-200 hover:text-slate-800" onclick="closeModal('chonTuanModal')">Hủy</button>
                <button type="submit" class="btn-action-sm-green">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-download mr-1" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/><path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/></svg>
                    Xác Nhận & Tải Về
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL CHỌN NHIỀU TUẦN (Pure Tailwind/JS Modal) -->
<div id="chonNhieuTuanModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[200] hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden w-full max-w-lg animate-in fade-in zoom-in duration-200">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-[#f8fafc]">
            <h5 class="text-base font-bold text-[#224397] m-0 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="#16a34a" class="bi bi-ui-checks-grid" viewBox="0 0 16 16"><path d="M2 10h3a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1m9-9h3a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-3a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1m0 9a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1zm0-10a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h3a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zM2 9a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h3a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2zm7 2a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-3a2 2 0 0 1-2-2zM0 2a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2z"/></svg>
                Chọn Tuần Cần Xuất Dữ Liệu
            </h5>
            <button type="button" class="text-slate-400 hover:text-slate-600 text-xl font-bold px-2 py-1" onclick="closeModal('chonNhieuTuanModal')">&times;</button>
        </div>
        <form id="chonNhieuTuanForm" method="POST" action="" target="_blank">
            <div class="p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <label class="block text-xs font-bold text-[#224397] m-0">CHỌN MỘT HOẶC NHIỀU TUẦN</label>
                    <div class="flex items-center gap-2">
                        <input class="rounded border-slate-300 text-[#224397] focus:ring-[#224397] w-4 h-4 cursor-pointer" type="checkbox" id="selectAllWeeks">
                        <label class="text-xs font-bold text-slate-700 m-0 cursor-pointer" for="selectAllWeeks">Chọn tất cả</label>
                    </div>
                </div>
                <div class="max-h-60 overflow-y-auto border border-slate-200 p-4 rounded-xl bg-slate-50 space-y-3" id="week-checkboxes-container">
                    <?php foreach ($all_weeks as $week): ?>
                        <div class="flex items-center gap-3 bg-white p-3 rounded-lg border border-slate-200/60 shadow-sm hover:border-slate-300 transition-colors">
                            <input class="rounded border-slate-300 text-[#224397] focus:ring-[#224397] w-4 h-4 week-checkbox cursor-pointer" type="checkbox" name="tuan_ids[]" value="<?php echo $week['id']; ?>" id="week_bc_<?php echo $week['id']; ?>">
                            <label class="text-sm font-semibold text-slate-800 m-0 cursor-pointer flex-1" for="week_bc_<?php echo $week['id']; ?>">
                                <?php echo htmlspecialchars($week['ten_tuan']); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="flex items-center justify-end px-6 py-4 border-t border-slate-100 bg-[#f8fafc] gap-2">
                <button type="button" class="btn-action-sm bg-slate-100 border-slate-300 text-slate-700 hover:bg-slate-200 hover:text-slate-800" onclick="closeModal('chonNhieuTuanModal')">Hủy</button>
                <button type="submit" class="btn-action-sm-green">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-download mr-1" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/><path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/></svg>
                    Xuất Danh Sách
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>

<script>
function openModal(modalId, actionUrl) {
    const m = document.getElementById(modalId);
    if (!m) return;
    m.classList.remove('hidden');
    m.classList.add('flex');
    
    if (modalId === 'chonTuanModal') {
        const form = document.getElementById('chonTuanForm');
        if(form) form.setAttribute('action', actionUrl);
    } else if (modalId === 'chonNhieuTuanModal') {
        const form = document.getElementById('chonNhieuTuanForm');
        if(form) form.setAttribute('action', actionUrl);
    }
}

function closeModal(modalId) {
    const m = document.getElementById(modalId);
    if (!m) return;
    m.classList.add('hidden');
    m.classList.remove('flex');
}

document.addEventListener('DOMContentLoaded', function () {
    const selectAllCheckbox = document.getElementById('selectAllWeeks');
    const weekCheckboxes = document.querySelectorAll('.week-checkbox');

    if(selectAllCheckbox && weekCheckboxes.length > 0) {
        selectAllCheckbox.addEventListener('change', function() {
            weekCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        weekCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allChecked = Array.from(weekCheckboxes).every(cb => cb.checked);
                selectAllCheckbox.checked = allChecked;
            });
        });
    }
});
</script>
