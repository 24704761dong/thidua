<?php
$page_title = 'Báo Cáo Theo Tên Vi Phạm';
require_once __DIR__ . '/partials/admin_header.php';

// Các biến đã được nạp từ controller (bao_cao_web.php)
$tuan_hoc = $tuan_hoc ?? ['ten_tuan' => 'Chưa chọn tuần'];
$all_weeks = $all_weeks ?? [];
$tuan_id = $tuan_id ?? 0;
$report_data = $report_data ?? [];
?>
<style>
    body { background-color: #f4f7f9; }
    body::-webkit-scrollbar, html::-webkit-scrollbar { display: block !important; width: 8px; height: 8px; }
    body::-webkit-scrollbar-thumb { background: rgba(34,67,151,0.3); border-radius: 4px; }
    body::-webkit-scrollbar-track { background: transparent; }
    
    /* Table chuẩn như trang danh sách học sinh & báo cáo thi đua */
    .log-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
    .log-table thead th { 
        background: #f8fafc; color: #1e293b; font-weight: 700; 
        text-transform: uppercase; font-size: 0.75rem; 
        padding: 0.75rem 0.5rem; border: 1px solid #cbd5e1; 
        white-space: nowrap; text-align: center; 
    }
    .log-table td { 
        padding: 0.6rem 0.5rem; border: 1px solid #cbd5e1; 
        vertical-align: middle; color: #334155; text-align: center; 
    }
    .log-table tbody tr:hover { background: #f1f5f9; }

    /* Form UI chuẩn nhỏ gọn */
    .form-select-sm, .form-input-sm {
        display: block; width: 100%; border-radius: 6px;
        border: 1px solid #cbd5e1; padding: 0.25rem 0.6rem;
        font-size: 13px; color: #1e293b; background: #fff;
        height: 32px; 
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-select-sm:focus, .form-input-sm:focus { outline: none; border-color: #224397; box-shadow: 0 0 0 3px rgba(34,67,151,0.1); }
    
    .btn-action-sm { display: inline-flex; align-items: center; gap: 0.3rem; height: 32px; padding: 0 0.7rem; border-radius: 6px; font-size: 12px; font-weight: 600; border: 1px solid rgba(34,67,151,0.25); background: #fff; color: #224397; transition: all 0.2s; text-decoration: none; cursor: pointer; white-space: nowrap; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .btn-action-sm:hover { background: #FAB723; color: #fff; border-color: #FAB723; }
    
    .btn-action-sm-green { display: inline-flex; align-items: center; gap: 0.3rem; height: 32px; padding: 0 0.7rem; border-radius: 6px; font-size: 12px; font-weight: 600; border: 1px solid rgba(22,163,74,0.25); background: #16a34a; color: #fff; transition: all 0.2s; text-decoration: none; cursor: pointer; white-space: nowrap; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .btn-action-sm-green:hover { background: #15803d; color: #fff; border-color: #15803d; }
</style>

<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 pb-6 mt-4">
    <div class="flex flex-wrap items-center justify-between mb-5 gap-3">
        <h1 class="text-xl mb-0 font-semibold text-[#224397] flex items-center gap-2 uppercase">
            <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-tag-fill text-[#FAB723]" viewBox="0 0 16 16"><path d="M2 1a1 1 0 0 0-1 1v4.586a1 1 0 0 0 .293.707l7 7a1 1 0 0 0 1.414 0l4.586-4.586a1 1 0 0 0 0-1.414l-7-7A1 1 0 0 0 6.586 1zm4 3.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0"/></svg>
            <?php echo 'Báo Cáo Theo Tên Vi Phạm'; ?>
        </h1>
    </div>

    <!-- KHU VỰC BỘ LỌC & THAO TÁC -->
    <div class="bg-white rounded-xl shadow-sm border border-[#224397]/20 mb-6">
        <!-- Toolbar: Trái (Bộ lọc) - Phải (Actions) -->
        <div class="px-5 py-3 border-b border-[#224397]/12 bg-[#f8fafc] flex flex-wrap items-end justify-between gap-4">
            <!-- Left: Filters -->
            <div class="flex flex-wrap items-end gap-3">
                <form method="GET" action="" class="flex items-end" id="form-chon-tuan" onsubmit="event.preventDefault();">
                    <input type="hidden" name="iframe" value="1">
                    <div>
                        <label class="block text-[12px] font-bold text-[#224397] mb-1">Chọn Tuần</label>
                        <select name="tuan_id" id="tuan_id_select" class="form-select-sm min-w-[140px]" onchange="loadTuanData(this.value)">
                            <option value="">-- Chọn tuần --</option>
                            <?php foreach ($all_weeks as $t): ?>
                                <option value="<?php echo $t['id']; ?>" <?php echo $t['id'] == $tuan_id ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($t['ten_tuan']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>

                <div id="filters-container" class="flex flex-wrap items-end gap-3">
                <?php if (!empty($report_data)): ?>
                <div>
                    <label class="block text-[12px] font-bold text-[#224397] mb-1">Tìm kiếm nhanh</label>
                    <input type="text" id="quick-search" class="form-input-sm min-w-[200px]" placeholder="Tên vi phạm, học sinh...">
                </div>
                <?php endif; ?>
                </div>
            </div>

            <!-- Right: Action Buttons -->
            <div id="actions-container" class="flex flex-wrap items-center gap-2">
                <div class="relative inline-block text-left group z-50">
                    <button class="btn-action-sm shadow-sm whitespace-nowrap" type="button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-eye mr-1" viewBox="0 0 16 16"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/></svg> 
                        Xem báo cáo khác
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-chevron-down text-[9px] ml-1" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/></svg>
                    </button>
                    <ul class="absolute right-0 mt-1 w-52 bg-white rounded-lg shadow-[0_10px_40px_-10px_rgba(0,0,0,0.15)] border border-slate-100 focus:outline-none opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-[100] transform origin-top-right scale-95 group-hover:scale-100 py-1">
                        <li>
                            <a class="flex items-center gap-2 px-3 py-2 text-[13px] font-medium text-slate-700 hover:bg-blue-50 hover:text-[#224397]" href="/thidua/bao-cao/vi-pham?tuan_id=<?php echo htmlspecialchars($tuan_id ?? ''); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="currentColor" class="bi bi-list-check text-primary-600" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5M3.854 2.146a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708L2 3.293l1.146-1.147a.5.5 0 0 1 .708 0m0 4a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708L2 7.293l1.146-1.147a.5.5 0 0 1 .708 0m0 4a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0"/></svg>
                                <span>DS in vi phạm (tuần)</span>
                            </a>
                        </li>
                        <li>
                            <a class="flex items-center gap-2 px-3 py-2 text-[13px] font-medium text-slate-700 hover:bg-blue-50 hover:text-[#224397]" href="/thidua/bao-cao/vi-pham-chung-theo-lop?tuan_id=<?php echo htmlspecialchars($tuan_id ?? ''); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="currentColor" class="bi bi-graph-up text-amber-500" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M0 0h1v15h15v1H0zm14.817 3.113a.5.5 0 0 1 .07.704l-4.5 5.5a.5.5 0 0 1-.74.037L7.06 6.767l-3.656 5.027a.5.5 0 0 1-.808-.588l4-5.5a.5.5 0 0 1 .758-.06l2.609 2.61 4.15-5.073a.5.5 0 0 1 .704-.07"/></svg>
                                <span>T.Kê VP theo điểm</span>
                            </a>
                        </li>
                        <li>
                            <a class="flex items-center gap-2 px-3 py-2 text-[13px] font-medium text-slate-700 hover:bg-blue-50 hover:text-[#224397]" href="/thidua/bao-cao/theo-ten-vi-pham?tuan_id=<?php echo htmlspecialchars($tuan_id ?? ''); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="currentColor" class="bi bi-tag text-emerald-600" viewBox="0 0 16 16"><path d="M6 4.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m-1 0a.5.5 0 1 0-1 0 .5.5 0 0 0 1 0"/><path d="M2 1h4.586a1 1 0 0 1 .707.293l7 7a1 1 0 0 1 0 1.414l-4.586 4.586a1 1 0 0 1-1.414 0l-7-7A1 1 0 0 1 1 6.586V2a1 1 0 0 1 1-1m0 5.586 7 7L13.586 9l-7-7H2z"/></svg>
                                <span>T.Kê Theo tên vi phạm</span>
                            </a>
                        </li>
                        <li>
                            <a class="flex items-center gap-2 px-3 py-2 text-[13px] font-medium text-slate-700 hover:bg-blue-50 hover:text-[#224397]" href="/thidua/bao-cao/vi-pham-chi-tiet-theo-lop?tuan_id=<?php echo htmlspecialchars($tuan_id ?? ''); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="currentColor" class="bi bi-clipboard-data text-purple-600" viewBox="0 0 16 16"><path d="M4 11a1 1 0 1 1 2 0v1a1 1 0 1 1-2 0zm6-4a1 1 0 1 1 2 0v5a1 1 0 1 1-2 0zM7 9a1 1 0 0 1 2 0v3a1 1 0 1 1-2 0z"/><path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1z"/><path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0z"/></svg>
                                <span>T.Kê VP theo lớp</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <?php if ($tuan_id): ?>
                <a href="/thidua/xuat-bao-cao-theo-ten-vi-pham?tuan_id=<?php echo htmlspecialchars($tuan_id); ?>" class="btn-action-sm-green">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-excel-fill mr-1" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M5.884 6.68 8 9.219l2.116-2.54a.5.5 0 1 1 .768.641L8.651 10l2.233 2.68a.5.5 0 0 1-.768.64L8 10.781l-2.116 2.54a.5.5 0 0 1-.768-.641L7.349 10 5.116 7.32a.5.5 0 1 1 .768-.64"/></svg> 
                    Xuất Excel
                </a>
                <?php endif; ?>
            </div>
        </div>

        <div id="dynamic-content-wrapper">
            <!-- Thanh trạng thái bộ lọc -->
            <?php if (!empty($report_data)): ?>
            <div class="px-5 py-2 border-b border-[#224397]/10 bg-[#224397]/[0.01]">
                <div id="summary-text" class="text-[11px] text-slate-500 font-medium"></div>
            </div>
            <?php endif; ?>

            <div class="p-6">
                <h5 class="mb-4 text-sm font-semibold text-slate-800 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="#FAB723" class="bi bi-calendar-check" viewBox="0 0 16 16"><path d="M10.854 7.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 9.793l2.646-2.647a.5.5 0 0 1 .708 0"/><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/></svg>
                    <span class="text-[#224397]">Tuần:</span> 
                    <?php echo htmlspecialchars($tuan_hoc['ten_tuan'] ?? 'Chưa chọn tuần'); ?>
                </h5>
                
                <?php if (empty($report_data)): ?>
                    <div class="p-6 rounded-lg border bg-slate-50 text-slate-500 font-medium text-center border-slate-200">Không có dữ liệu vi phạm cho tuần này.</div>
                <?php else: ?>
                    <div class="space-y-4" id="violationList">
                        <?php foreach($report_data as $index => $item): 
                            $summary = $item['summary'];
                            $details = $item['details'];
                        ?>
                            <div class="violation-group bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden transition-all duration-200 hover:border-slate-300">
                                <div class="accordion-header bg-[#f8fafc] px-6 py-4 flex items-center justify-between cursor-pointer border-b border-slate-100 select-none" onclick="toggleAccordion(this)">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 bg-[#224397]/10 rounded-lg text-[#224397]">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-exclamation-triangle-fill" viewBox="0 0 16 16"><path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/></svg>
                                        </div>
                                        <span class="text-base font-bold text-slate-800 violation-title"><?php echo htmlspecialchars($summary['ten_vi_pham']); ?></span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">Số lần: <?php echo $summary['so_lan_vi_pham']; ?></span>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">Tổng điểm trừ: <?php echo $summary['so_lan_vi_pham'] * $summary['diem_tru']; ?></span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-chevron-down text-slate-400 transition-transform duration-200 transform chevron-icon" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/></svg>
                                    </div>
                                </div>
                                <div class="accordion-body hidden p-6 bg-white border-t border-slate-100">
                                    <?php if(empty($details)): ?>
                                        <p class="text-slate-500 font-medium text-center my-4">Không có dữ liệu chi tiết.</p>
                                    <?php else: ?>
                                        <div class="overflow-x-auto w-full rounded-lg border border-slate-200">
                                            <table class="log-table">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 50px;">STT</th>
                                                        <th style="width: 140px;">Số CCCD</th>
                                                        <th style="text-align: left; padding-left: 1rem;">Họ và Tên</th>
                                                        <th style="width: 120px;">Ngày Vi Phạm</th>
                                                        <th style="text-align: left; padding-left: 1rem;">Ghi chú</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach($details as $detail_idx => $detail_item): 
                                                        $is_kxd = empty($detail_item['ma_hoc_sinh']);
                                                    ?>
                                                        <tr class="detail-row">
                                                            <td class="font-bold text-slate-400"><?php echo $detail_idx + 1; ?></td>
                                                            <td class="font-semibold text-[#224397]"><?php echo htmlspecialchars($detail_item['ma_hoc_sinh'] ?? 'KXD'); ?></td>
                                                            <td style="text-align: left; padding-left: 1rem;" class="font-medium text-slate-800 detail-name">
                                                                <?php echo $is_kxd ? '<i class="text-amber-600">' . htmlspecialchars($detail_item['ho_ten']) . ' (KXD)</i>' : htmlspecialchars($detail_item['ho_ten']); ?>
                                                            </td>
                                                            <td class="text-slate-600"><?php echo date('d/m/Y', strtotime($detail_item['ngay_vi_pham'])); ?></td>
                                                            <td style="text-align: left; padding-left: 1rem;" class="text-slate-600"><?php echo htmlspecialchars($detail_item['ghi_chu'] ?? ''); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>

<script>
function toggleAccordion(el) {
    const group = el.closest('.violation-group');
    const body = group.querySelector('.accordion-body');
    const chevron = group.querySelector('.chevron-icon');
    
    if (body.classList.contains('hidden')) {
        body.classList.remove('hidden');
        chevron.classList.add('rotate-180');
    } else {
        body.classList.add('hidden');
        chevron.classList.remove('rotate-180');
    }
}

function initBaoCaoEvents() {
    const quickSearch = document.getElementById('quick-search');
    const summaryText = document.getElementById('summary-text');
    const groups = document.querySelectorAll('.violation-group');
    
    if(!groups.length) return;

    function applyFilters() {
        const q = quickSearch ? quickSearch.value.trim().toLowerCase() : '';
        
        let visGroups = 0;
        groups.forEach(group => {
            let groupMatch = false;
            const title = group.querySelector('.violation-title').textContent.toLowerCase();
            if (title.includes(q)) groupMatch = true;
            
            const rows = group.querySelectorAll('.detail-row');
            let hasMatchingRow = false;
            rows.forEach(row => {
                if (!q || row.textContent.toLowerCase().includes(q)) {
                    row.style.display = '';
                    hasMatchingRow = true;
                } else {
                    row.style.display = 'none';
                }
            });
            
            if (groupMatch || hasMatchingRow) {
                group.style.display = '';
                visGroups++;
                // Nếu đang search thì tự động mở accordion để dễ xem
                if (q) {
                    group.querySelector('.accordion-body').classList.remove('hidden');
                    group.querySelector('.chevron-icon').classList.add('rotate-180');
                }
            } else {
                group.style.display = 'none';
            }
        });

        if(summaryText) summaryText.innerHTML = `Hiển thị ${visGroups} / ${groups.length} nhóm vi phạm &middot; Đang dùng bộ lọc client`;
    }

    if(quickSearch) quickSearch.addEventListener('input', applyFilters);
    applyFilters();
}

async function loadTuanData(tuanId) {
    if(!tuanId) return;
    
    // Show loading state
    const wrapper = document.getElementById('dynamic-content-wrapper');
    if(wrapper) {
        wrapper.style.opacity = '0.5';
        wrapper.style.pointerEvents = 'none';
    }
    
    try {
        const url = `/thidua/bao-cao/theo-ten-vi-pham?iframe=1&tuan_id=${tuanId}`;
        const response = await fetch(url);
        const html = await response.text();
        const doc = new DOMParser().parseFromString(html, 'text/html');
        
        // Update URL without reloading
        window.history.pushState({}, '', `?tuan_id=${tuanId}`);
        
        // Replace dynamic content wrapper
        const newWrapper = doc.getElementById('dynamic-content-wrapper');
        if(newWrapper && wrapper) {
            wrapper.innerHTML = newWrapper.innerHTML;
        }
        
        // Replace actions container
        const newActions = doc.getElementById('actions-container');
        const oldActions = document.getElementById('actions-container');
        if(newActions && oldActions) {
            oldActions.innerHTML = newActions.innerHTML;
        }

        // Replace filters container
        const newFilters = doc.getElementById('filters-container');
        const oldFilters = document.getElementById('filters-container');
        if(newFilters && oldFilters) {
            oldFilters.innerHTML = newFilters.innerHTML;
        }
        
        // Re-initialize events
        initBaoCaoEvents();
        
    } catch (e) {
        console.error("Error loading data:", e);
        if (typeof showToast === 'function') showToast('Lỗi khi tải dữ liệu', 'error');
    } finally {
        if(wrapper) {
            wrapper.style.opacity = '1';
            wrapper.style.pointerEvents = 'auto';
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    initBaoCaoEvents();
});
</script>
