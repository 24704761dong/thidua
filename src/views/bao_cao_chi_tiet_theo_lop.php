<?php
$page_title = 'Báo Cáo Chi Tiết Theo Lớp';
require_once __DIR__ . '/partials/admin_header.php';

// Các biến đã được nạp từ controller (bao_cao_web.php / bao_cao_chi_tiet_theo_lop.php)
$tuan_hoc = $tuan_hoc ?? ['ten_tuan' => 'Chưa chọn tuần'];
$all_weeks = $all_weeks ?? [];
$tuan_id = $tuan_id ?? 0;
$filter_khoi = $filter_khoi ?? 'all';
$filter_lop_id = $filter_lop_id ?? 'all';
$danh_sach_lop_all = $danh_sach_lop_all ?? [];
$report_data = $report_data ?? [];
$summary_data = $summary_data ?? [];
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
            <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-people-fill text-[#FAB723]" viewBox="0 0 16 16"><path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/></svg>
            <?php echo 'Báo Cáo Chi Tiết Theo Lớp'; ?>
        </h1>
    </div>

    <!-- KHU VỰC BỘ LỌC & THAO TÁC -->
    <div class="bg-white rounded-xl shadow-sm border border-[#224397]/20 mb-6">
        <!-- Toolbar: Trái (Bộ lọc) - Phải (Actions) -->
        <div class="px-5 py-3 border-b border-[#224397]/12 bg-[#f8fafc] rounded-t-xl flex flex-wrap items-end justify-between gap-4">
            <!-- Left: Filters -->
            <form id="filterForm" class="flex flex-wrap items-end gap-3" method="GET">
                <input type="hidden" name="iframe" value="1">
                <div>
                    <label class="block text-[12px] font-bold text-[#224397] mb-1">Chọn Tuần</label>
                    <select name="tuan_id" class="form-select-sm min-w-[140px]" onchange="this.form.submit()">
                        <?php foreach($all_weeks as $week): ?>
                            <option value="<?php echo $week['id']; ?>" <?php echo $week['id'] == $tuan_id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($week['ten_tuan']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-[12px] font-bold text-[#224397] mb-1">Khối</label>
                    <select id="khoi" name="khoi" class="form-select-sm min-w-[100px]">
                        <option value="all" <?php if ($filter_khoi === 'all') echo 'selected'; ?>>Toàn Khối</option>
                        <option value="10" <?php if ($filter_khoi === '10') echo 'selected'; ?>>Khối 10</option>
                        <option value="11" <?php if ($filter_khoi === '11') echo 'selected'; ?>>Khối 11</option>
                        <option value="12" <?php if ($filter_khoi === '12') echo 'selected'; ?>>Khối 12</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[12px] font-bold text-[#224397] mb-1">Lớp</label>
                    <select id="lop_id" name="lop_id" class="form-select-sm min-w-[120px]">
                        <option value="all">Tất cả Lớp</option>
                        <?php foreach ($danh_sach_lop_all as $lop): ?>
                            <option value="<?php echo $lop['id']; ?>" data-khoi="<?php echo substr($lop['ten_lop'], 0, 2); ?>" <?php if ($filter_lop_id == $lop['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($lop['ten_lop']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>

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
                <button type="button" class="btn-action-sm-green" onclick="openExportModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-excel-fill mr-1" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M5.884 6.68 8 9.219l2.116-2.54a.5.5 0 1 1 .768.641L8.651 10l2.233 2.68a.5.5 0 0 1-.768.64L8 10.781l-2.116 2.54a.5.5 0 0 1-.768-.641L7.349 10 5.116 7.32a.5.5 0 1 1 .768-.64"/></svg> 
                    Xuất Excel
                </button>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (empty($report_data)): ?>
            <div class="p-6 text-slate-500 font-medium text-center bg-white rounded-b-xl">Không có dữ liệu vi phạm cho tuần này.</div>
        <?php endif; ?>
    </div>

    <!-- DANH SÁCH BÁO CÁO CÁC LỚP -->
    <?php if (!empty($report_data)): ?>
    <div class="space-y-6">
        <?php foreach ($report_data as $lop_ten => $data): 
            $summary = $summary_data[$lop_ten] ?? null;
            $si_so = 'N/A';
            foreach ($danh_sach_lop_all as $l) {
                if (($l['ten_lop'] ?? '') === $lop_ten) {
                    $si_so = $l['si_so'] ?? 'N/A';
                    break;
                }
            }
        ?>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-[#f8fafc] px-6 py-4 border-b border-slate-200 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <h5 class="m-0 font-bold text-[#224397] text-base flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="#FAB723" class="bi bi-bookmark-star-fill" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M2 15.5V2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.74.439L8 13.069l-5.26 2.87A.5.5 0 0 1 2 15.5M8.16 4.1a.178.178 0 0 0-.32 0l-.634 1.285a.18.18 0 0 1-.134.098l-1.42.206a.178.178 0 0 0-.098.303L6.58 6.993c.042.041.061.1.051.158L6.39 8.565a.178.178 0 0 0 .258.187l1.27-.668a.18.18 0 0 1 .165 0l1.27.668a.178.178 0 0 0 .257-.187L9.368 7.15a.18.18 0 0 1 .05-.158l1.028-1.001a.178.178 0 0 0-.098-.303l-1.42-.206a.18.18 0 0 1-.134-.098z"/></svg>
                            Lớp <?php echo htmlspecialchars($lop_ten); ?>
                            <span class="text-xs font-semibold text-slate-500 bg-slate-200/60 px-2.5 py-0.5 rounded-full ml-2">Sĩ số: <?php echo $si_so; ?></span>
                        </h5>
                        <span class="text-slate-500 font-medium text-sm border-l border-slate-300 pl-3">GVCN: <?php echo htmlspecialchars($data['gvcn_ten'] ?? 'Chưa có'); ?></span>
                    </div>
                    
                    <?php if ($summary): ?>
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">Điểm: <?php echo round($summary['tong_diem'], 2); ?></span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Hạng: <?php echo $summary['kxtd'] ? 'KXTD' : ($summary['xep_hang'] ?? 'N/A'); ?></span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">Vắng P: <?php echo $summary['vang_p']; ?></span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">Vắng KP: <?php echo $summary['vang_kp']; ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="p-6">
                    <div class="overflow-x-auto w-full rounded-lg border border-slate-200">
                        <table class="log-table">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">STT</th>
                                    <th style="width: 130px;">Số CCCD</th>
                                    <th style="text-align: left; padding-left: 1rem;">Họ và Tên</th>
                                    <th style="width: 120px;">Ngày Vi Phạm</th>
                                    <th style="text-align: left; padding-left: 1rem;">Tên Nhóm Vi Phạm</th>
                                    <th style="text-align: left; padding-left: 1rem;">Ghi chú</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $student_list = $data['students'];
                                $stt_trong_lop = 1;
                                foreach ($student_list as $ho_ten => $violations): 
                                    foreach ($violations as $vp):
                                        $is_kxd = empty($vp['ma_hoc_sinh']);
                                ?>
                                    <tr>
                                        <td class="font-bold text-slate-400"><?php echo $stt_trong_lop++; ?></td>
                                        <td class="font-semibold text-[#224397]"><?php echo htmlspecialchars($vp['ma_hoc_sinh'] ?? 'KXD'); ?></td>
                                        <td style="text-align: left; padding-left: 1rem;" class="font-medium text-slate-800">
                                            <?php echo $is_kxd ? '<i class="text-amber-600">' . htmlspecialchars($ho_ten) . ' (KXD)</i>' : htmlspecialchars($ho_ten); ?>
                                        </td>
                                        <td class="text-slate-600"><?php echo date('d/m/Y', strtotime($vp['ngay_vi_pham'])); ?></td>
                                        <td style="text-align: left; padding-left: 1rem;" class="font-semibold text-rose-600"><?php echo htmlspecialchars($vp['ten_vi_pham']); ?></td>
                                        <td style="text-align: left; padding-left: 1rem;" class="text-slate-600"><?php echo htmlspecialchars($vp['ghi_chu'] ?? ''); ?></td>
                                    </tr>
                                <?php 
                                    endforeach; 
                                endforeach; 
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- MODAL XUẤT EXCEL (Pure Tailwind/JS Modal - Hoạt động ổn định 100% không phụ thuộc Bootstrap JS) -->
<div id="exportExcelModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[200] hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden w-full max-w-lg animate-in fade-in zoom-in duration-200">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-[#f8fafc]">
            <h5 class="text-base font-bold text-[#224397] m-0 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="#16a34a" class="bi bi-file-earmark-excel-fill" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M5.884 6.68 8 9.219l2.116-2.54a.5.5 0 1 1 .768.641L8.651 10l2.233 2.68a.5.5 0 0 1-.768.64L8 10.781l-2.116 2.54a.5.5 0 0 1-.768-.641L7.349 10 5.116 7.32a.5.5 0 1 1 .768-.64"/></svg>
                Tùy chọn Xuất Báo cáo Chi tiết
            </h5>
            <button type="button" class="text-slate-400 hover:text-slate-600 text-xl font-bold px-2 py-1" onclick="closeExportModal()">&times;</button>
        </div>
        <form action="/thidua/xuat-bao-cao-chi-tiet-lop" method="POST" target="_blank">
            <div class="p-6 space-y-4">
                <p class="text-sm text-slate-600 m-0">Vui lòng chọn một hoặc nhiều tuần để xuất dữ liệu. File Excel sẽ được tạo với mỗi lớp là một sheet riêng biệt.</p>
                <div>
                    <label class="block text-xs font-bold text-[#224397] mb-2">CHỌN TUẦN</label>
                    <div class="max-h-60 overflow-y-auto border border-slate-200 p-4 rounded-xl bg-slate-50 space-y-3">
                        <?php foreach($all_weeks as $week): ?>
                        <div class="flex items-center gap-3 bg-white p-3 rounded-lg border border-slate-200/60 shadow-sm">
                            <input class="rounded border-slate-300 text-[#224397] focus:ring-[#224397] w-4 h-4" type="checkbox" name="tuan_ids[]" value="<?php echo $week['id']; ?>" id="week_<?php echo $week['id']; ?>" checked>
                            <label class="text-sm font-semibold text-slate-800 m-0 cursor-pointer flex-1" for="week_<?php echo $week['id']; ?>">
                                <?php echo htmlspecialchars($week['ten_tuan']); ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-end px-6 py-4 border-t border-slate-100 bg-[#f8fafc] gap-2">
                <button type="button" class="btn-action-sm bg-slate-100 border-slate-300 text-slate-700 hover:bg-slate-200 hover:text-slate-800" onclick="closeExportModal()">Hủy</button>
                <button type="submit" class="btn-action-sm-green">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check2-circle mr-1" viewBox="0 0 16 16"><path d="M2.5 8a5.5 5.5 0 0 1 8.25-4.764.5.5 0 0 0 .5-.866A6.5 6.5 0 1 0 14.5 8a.5.5 0 0 0-1 0 5.5 5.5 0 1 1-11 0z"/><path d="M15.354 3.354a.5.5 0 0 0-.708-.708L8 9.293 5.354 6.646a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0l7-7z"/></svg> 
                    Bắt đầu Xuất
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>

<script>
function openExportModal() {
    const m = document.getElementById('exportExcelModal');
    if(m) { m.classList.remove('hidden'); m.classList.add('flex'); }
}

function closeExportModal() {
    const m = document.getElementById('exportExcelModal');
    if(m) { m.classList.add('hidden'); m.classList.remove('flex'); }
}

document.addEventListener('DOMContentLoaded', function() {
    const khoiSelect = document.getElementById('khoi');
    const lopSelect = document.getElementById('lop_id');
    if(!khoiSelect || !lopSelect) return;
    
    const originalLopOptions = Array.from(lopSelect.options);

    function filterLopTheoKhoi() {
        const selectedKhoi = khoiSelect.value;
        const currentUrl = new URL(window.location);
        const selectedLopId = currentUrl.searchParams.get('lop_id') || 'all';

        lopSelect.innerHTML = '';
        originalLopOptions.forEach(option => {
            if (option.value === 'all' || selectedKhoi === 'all' || option.dataset.khoi === selectedKhoi) {
                lopSelect.appendChild(option.cloneNode(true));
            }
        });
        lopSelect.value = selectedLopId;
    }
    
    filterLopTheoKhoi();
    
    khoiSelect.addEventListener('change', function() {
        document.getElementById('lop_id').value = 'all';
        document.getElementById('filterForm').submit();
    });

    lopSelect.addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
});
</script>
