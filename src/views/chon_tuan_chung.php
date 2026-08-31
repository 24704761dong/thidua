<?php
$page_title  = $page_title  ?? 'Chọn Tuần';
$page_icon   = $page_icon   ?? 'bi-calendar-week';
$school_year = $school_year ?? '';
$base_url    = $base_url    ?? '';
require_once __DIR__ . '/partials/admin_header.php';

// Giả định các biến này được nạp từ controller
$weeks_hk1 = $weeks_hk1 ?? [];
$weeks_hk2 = $weeks_hk2 ?? [];
$can_manage_weeks = (bool)($can_manage_weeks ?? false);

function getSvgIcon($name, $classes = '') {
    $icons = [
        'bi-calendar-week' => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="'.$classes.'" viewBox="0 0 16 16"><path d="M11 6.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5z"/><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/></svg>',
        'bi-calendar-x' => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="'.$classes.'" viewBox="0 0 16 16"><path d="M6.146 7.146a.5.5 0 0 1 .708 0L8 8.293l1.146-1.147a.5.5 0 1 1 .708.708L8.707 9l1.147 1.146a.5.5 0 0 1-.708.708L8 9.707l-1.146 1.147a.5.5 0 0 1-.708-.708L7.293 9 6.146 7.854a.5.5 0 0 1 0-.708z"/><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/></svg>',
        'bi-calendar-check' => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="'.$classes.'" viewBox="0 0 16 16"><path d="M10.854 7.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 9.793l2.646-2.647a.5.5 0 0 1 .708 0"/><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/></svg>',
        'bi-lock-fill' => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="'.$classes.'" viewBox="0 0 16 16"><path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2m3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2"/></svg>',
        'bi-unlock-fill' => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="'.$classes.'" viewBox="0 0 16 16"><path d="M11 1a2 2 0 0 0-2 2v4a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h5V3a3 3 0 0 1 6 0v4a.5.5 0 0 1-1 0V3a2 2 0 0 0-2-2"/></svg>'
    ];
    return $icons[$name] ?? '';
}
?>
<!-- Wrapper ngoài cùng -->
<div class="flex-1 overflow-y-auto bg-transparent p-6 min-h-screen">
    <div class="max-w-6xl mx-auto">
        
        <!-- Header của Trang -->
        <div class="relative flex flex-col items-center justify-center mb-6 border-b border-[#224397]/25 pb-4 min-h-[4rem]">
            <h3 class="text-xl font-bold text-[#224397] flex items-center gap-2 uppercase tracking-wide">
                <?php echo getSvgIcon($page_icon); ?> <?= mb_strtoupper(htmlspecialchars($page_title), 'UTF-8') ?>
            </h3>
            <?php if ($school_year): ?>
                <span class="mt-2 px-3 py-0.5 rounded-full text-[11px] font-bold bg-[#FAB723]/20 text-amber-800 border border-[#FAB723]/30 uppercase tracking-widest">
                    NĂM HỌC: <?= htmlspecialchars($school_year) ?>
                </span>
            <?php endif; ?>
            
            <div class="absolute right-0 top-1/2 -translate-y-1/2">
                <?php if ($can_manage_weeks): ?>
                    <button id="addWeekBtn" class="px-3.5 py-1.5 bg-white border border-[#224397]/20 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:-translate-y-0.5 transition-all font-medium flex items-center gap-1.5 text-sm shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-plus-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/> <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/></svg> 
                        Thêm Tuần
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Khối chứa dữ liệu chính -->
        <div class="bg-white rounded-xl shadow-sm border border-[#224397]/[45%] p-6 lg:p-8 mb-6">
            
            <div class="text-center mb-8">
                <h4 class="text-lg font-semibold text-slate-800 mb-1">Vui lòng chọn tuần học để tiếp tục</h4>
                <p class="text-sm text-slate-500">
                    <?php if ($can_manage_weeks): ?>
                        Di chuột vào thẻ tuần để hiển thị công cụ chỉnh sửa hoặc khóa tuần.
                    <?php else: ?>
                        Hệ thống tự động đồng bộ dữ liệu theo tuần bạn chọn.
                    <?php endif; ?>
                </p>
            </div>

            <!-- Học Kỳ 1 -->
            <div class="mb-10">
                <div class="flex items-center gap-2 mb-6 border-b border-[#224397]/25 pb-2">
                    <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-[#224397]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-hourglass-top" viewBox="0 0 16 16"><path d="M2 14.5a.5.5 0 0 0 .5.5h11a.5.5 0 1 0 0-1h-1v-1a4.5 4.5 0 0 0-2.557-4.06c-.29-.139-.443-.377-.443-.59v-.7c0-.213.154-.451.443-.59A4.5 4.5 0 0 0 12.5 3V2h1a.5.5 0 0 0 0-1h-11a.5.5 0 0 0 0 1h1v1a4.5 4.5 0 0 0 2.557 4.06c.29.139.443.377.443.59v.7c0 .213-.154.451-.443.59A4.5 4.5 0 0 0 3.5 13v1h-1a.5.5 0 0 0-.5.5m2.5-.5v-1a3.5 3.5 0 0 1 1.989-3.158c.533-.256 1.011-.791 1.011-1.491v-.702s.18-.149.5-.149.5.15.5.15v.7c0 .701.478 1.236 1.011 1.492A3.5 3.5 0 0 1 11.5 13v1z"/></svg>
                    </div>
                    <h4 class="text-base font-bold text-[#224397] uppercase tracking-wide">Học Kỳ 1</h4>
                </div>
                
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 lg:gap-4" id="grid-hk1">
                    <?php foreach($weeks_hk1 as $week): 
                        $is_locked = (int)($week['is_locked'] ?? 0) === 1;
                        $id = (int)$week['id'];
                    ?>
                    <a href="<?= !$is_locked ? htmlspecialchars($base_url . $id) : '#'; ?>" class="week-card relative flex justify-between items-center p-3 rounded-lg border-[1.5px] transition-all duration-300 group <?= $is_locked ? 'bg-slate-50 border-slate-200 cursor-not-allowed opacity-80' : 'bg-white border-blue-200 hover:bg-orange-50 hover:border-amber-300 hover:shadow-md hover:-translate-y-1' ?>" data-week-id="<?= $id ?>">
                        <?php if ($can_manage_weeks): ?>
                            <!-- Nút hành động nổi -->
                            <div class="absolute -top-3 -right-2 flex gap-0.5 opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-2 group-hover:translate-y-0 bg-white rounded-full shadow-md border border-slate-100 px-1.5 py-1 z-10">
                                <button type="button" class="action-icon edit w-6 h-6 flex items-center justify-center text-[11px] text-slate-500 hover:text-[#224397] hover:bg-blue-50 rounded-full transition-colors" title="Sửa tuần">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-pencil-fill pointer-events-none" viewBox="0 0 16 16"><path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.5.5 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11z"/></svg>
                                </button>
                                <button type="button" class="action-icon delete w-6 h-6 flex items-center justify-center text-[11px] text-slate-500 hover:text-red-600 hover:bg-red-50 rounded-full transition-colors" title="Xóa tuần">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-trash-fill pointer-events-none" viewBox="0 0 16 16"><path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5M8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5m3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0"/></svg>
                                </button>
                                <button type="button" class="action-icon lock w-6 h-6 flex items-center justify-center text-[11px] hover:bg-slate-100 rounded-full transition-colors <?= $is_locked ? 'text-red-600 is-locked' : 'text-slate-500' ?>" title="<?= $is_locked ? 'Đã khóa' : 'Mở'; ?>">
                                    <span class="icon-placeholder pointer-events-none"><?php echo getSvgIcon($is_locked ? 'bi-lock-fill' : 'bi-unlock-fill', 'pointer-events-none text-sm'); ?></span>
                                </button>
                            </div>
                        <?php endif; ?>
                        
                        <div class="flex flex-col gap-0.5 truncate overflow-hidden">
                            <span class="font-bold text-[13px] truncate transition-colors <?= $is_locked ? 'text-slate-500' : 'text-[#224397] group-hover:text-[#c2410c]' ?>"><?= htmlspecialchars($week['ten_tuan']) ?></span>
                            <span class="text-[11px] truncate transition-colors <?= $is_locked ? 'text-slate-400' : 'text-slate-500 group-hover:text-[#ea580c]' ?>"><?= date('d/m', strtotime($week['ngay_bat_dau'])) ?> - <?= date('d/m', strtotime($week['ngay_ket_thuc'])) ?></span>
                        </div>
                        
                        <div class="icon-box w-8 h-8 shrink-0 rounded-lg flex items-center justify-center border transition-colors shadow-sm <?= $is_locked ? 'bg-slate-100 border-slate-200 text-slate-400' : 'bg-slate-50 border-slate-100 text-[#224397] group-hover:bg-[#ffedd5] group-hover:border-[#fed7aa] group-hover:text-[#c2410c]' ?>">
                            <?php echo getSvgIcon($is_locked ? 'bi-calendar-x' : 'bi-calendar-check', 'text-base'); ?>
                        </div>
                    </a>
                    <?php endforeach; ?>
                    <?php if (empty($weeks_hk1)): ?>
                        <div class="col-span-full text-sm text-slate-400 italic py-6 text-center bg-slate-50 rounded-xl border border-[#224397]/[45%] border-dashed">Chưa có dữ liệu tuần cho Học Kỳ 1</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Học Kỳ 2 -->
            <div>
                <div class="flex items-center gap-2 mb-6 border-b border-[#224397]/25 pb-2">
                    <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-[#224397]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-hourglass-bottom" viewBox="0 0 16 16"><path d="M2 1.5a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-1v1a4.5 4.5 0 0 1-2.557 4.06c-.29.139-.443.377-.443.59v.7c0 .213.154.451.443.59A4.5 4.5 0 0 1 12.5 13v1h1a.5.5 0 0 1 0 1h-11a.5.5 0 1 1 0-1h1v-1a4.5 4.5 0 0 1 2.557-4.06c.29-.139.443-.377.443-.59v-.7c0-.213-.154-.451-.443-.59A4.5 4.5 0 0 1 3.5 3V2h-1a.5.5 0 0 1-.5-.5m2.5.5v1a3.5 3.5 0 0 0 1.989 3.158c.533.256 1.011.791 1.011 1.491v.702s.18.149.5.149.5-.15.5-.15v-.7c0-.701.478-1.236 1.011-1.492A3.5 3.5 0 0 0 11.5 3V2z"/></svg>
                    </div>
                    <h4 class="text-base font-bold text-[#224397] uppercase tracking-wide">Học Kỳ 2</h4>
                </div>
                
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 lg:gap-4" id="grid-hk2">
                     <?php foreach($weeks_hk2 as $week):
                        $is_locked = (int)($week['is_locked'] ?? 0) === 1;
                        $id = (int)$week['id'];
                    ?>
                    <a href="<?= !$is_locked ? htmlspecialchars($base_url . $id) : '#'; ?>" class="week-card relative flex justify-between items-center p-3 rounded-lg border-[1.5px] transition-all duration-300 group <?= $is_locked ? 'bg-slate-50 border-slate-200 cursor-not-allowed opacity-80' : 'bg-white border-blue-200 hover:bg-orange-50 hover:border-amber-300 hover:shadow-md hover:-translate-y-1' ?>" data-week-id="<?= $id ?>">
                        <?php if ($can_manage_weeks): ?>
                            <!-- Nút hành động nổi -->
                            <div class="absolute -top-3 -right-2 flex gap-0.5 opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-2 group-hover:translate-y-0 bg-white rounded-full shadow-md border border-slate-100 px-1.5 py-1 z-10">
                                <button type="button" class="action-icon edit w-6 h-6 flex items-center justify-center text-[11px] text-slate-500 hover:text-[#224397] hover:bg-blue-50 rounded-full transition-colors" title="Sửa tuần"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-pencil-fill pointer-events-none" viewBox="0 0 16 16"><path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.5.5 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11z"/></svg></button>
                                <button type="button" class="action-icon delete w-6 h-6 flex items-center justify-center text-[11px] text-slate-500 hover:text-red-600 hover:bg-red-50 rounded-full transition-colors" title="Xóa tuần"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-trash-fill pointer-events-none" viewBox="0 0 16 16"><path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5M8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5m3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0"/></svg></button>
                                <button type="button" class="action-icon lock w-6 h-6 flex items-center justify-center text-[11px] hover:bg-slate-100 rounded-full transition-colors <?= $is_locked ? 'text-red-600 is-locked' : 'text-slate-500' ?>" title="<?= $is_locked ? 'Đã khóa' : 'Mở'; ?>">
                                    <span class="icon-placeholder pointer-events-none"><?php echo getSvgIcon($is_locked ? 'bi-lock-fill' : 'bi-unlock-fill', 'pointer-events-none text-sm'); ?></span>
                                </button>
                            </div>
                        <?php endif; ?>
                        
                        <div class="flex flex-col gap-0.5 truncate overflow-hidden">
                            <span class="font-bold text-[13px] truncate transition-colors <?= $is_locked ? 'text-slate-500' : 'text-[#224397] group-hover:text-[#c2410c]' ?>"><?= htmlspecialchars($week['ten_tuan']) ?></span>
                            <span class="text-[11px] truncate transition-colors <?= $is_locked ? 'text-slate-400' : 'text-slate-500 group-hover:text-[#ea580c]' ?>"><?= date('d/m', strtotime($week['ngay_bat_dau'])) ?> - <?= date('d/m', strtotime($week['ngay_ket_thuc'])) ?></span>
                        </div>
                        
                        <div class="icon-box w-8 h-8 shrink-0 rounded-lg flex items-center justify-center border transition-colors shadow-sm <?= $is_locked ? 'bg-slate-100 border-slate-200 text-slate-400' : 'bg-slate-50 border-slate-100 text-[#224397] group-hover:bg-[#ffedd5] group-hover:border-[#fed7aa] group-hover:text-[#c2410c]' ?>">
                            <?php echo getSvgIcon($is_locked ? 'bi-calendar-x' : 'bi-calendar-check', 'text-base'); ?>
                        </div>
                    </a>
                    <?php endforeach; ?>
                    <?php if (empty($weeks_hk2)): ?>
                        <div class="col-span-full text-sm text-slate-400 italic py-6 text-center bg-slate-50 rounded-xl border border-[#224397]/[45%] border-dashed">Chưa có dữ liệu tuần cho Học Kỳ 2</div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal Thêm/Sửa Tuần (Tailwind + Vanilla JS) -->
<div id="weekModal" class="fixed inset-0 z-[10005] hidden opacity-0 transition-opacity duration-300">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModal('weekModal')"></div>
    
    <!-- Modal Content -->
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0 pointer-events-none">
        <div class="relative bg-white rounded-2xl shadow-2xl border border-[#224397]/[45%] flex flex-col w-full max-w-md pointer-events-auto transform transition-all duration-300 scale-95 translate-y-4 opacity-0">
            <form id="weekForm">
                
                <div class="flex items-center justify-between p-5 border-b border-[#224397]/25">
                    <h5 class="text-lg font-bold text-[#224397] flex items-center gap-2" id="modalTitle"></h5>
                    <button type="button" class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 p-1.5 rounded-lg transition-colors" onclick="closeModal('weekModal')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg>
                    </button>
                </div>
                
                <div class="p-6 text-left space-y-4">
                    <input type="hidden" name="tuan_id" id="tuan_id">
                    
                    <div>
                        <label for="ten_tuan" class="block text-[13px] font-semibold text-slate-700 mb-1">Tên tuần <span class="text-red-500">*</span></label>
                        <input type="text" class="w-full text-[13px] px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-[#224397] focus:ring-1 focus:ring-[#224397] transition-colors" name="ten_tuan" id="ten_tuan" placeholder="Ví dụ: TUẦN 1" required>
                    </div>
                    
                    <div>
                        <label for="hoc_ky" class="block text-[13px] font-semibold text-slate-700 mb-1">Học kỳ <span class="text-red-500">*</span></label>
                        <select name="hoc_ky" id="hoc_ky" class="w-full text-[13px] px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-[#224397] focus:ring-1 focus:ring-[#224397] transition-colors" required>
                            <option value="1">Học Kỳ 1</option>
                            <option value="2">Học Kỳ 2</option>
                        </select>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="ngay_bat_dau" class="block text-[13px] font-semibold text-slate-700 mb-1">Ngày bắt đầu</label>
                            <input type="date" class="w-full text-[13px] px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-[#224397] focus:ring-1 focus:ring-[#224397] transition-colors" name="ngay_bat_dau" id="ngay_bat_dau" required>
                        </div>
                        <div>
                            <label for="ngay_ket_thuc" class="block text-[13px] font-semibold text-slate-700 mb-1">Ngày kết thúc</label>
                            <input type="date" class="w-full text-[13px] px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-[#224397] focus:ring-1 focus:ring-[#224397] transition-colors" name="ngay_ket_thuc" id="ngay_ket_thuc" required>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center justify-end px-6 py-4 bg-slate-50 border-t border-[#224397]/25 gap-2 rounded-b-2xl">
                    <button type="button" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-[13px] font-medium text-slate-600 bg-white border border-slate-300 rounded hover:bg-slate-50 transition-colors shadow-sm" onclick="closeModal('weekModal')">
                        Hủy
                    </button>
                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-[13px] font-bold text-slate-900 bg-[#FAB723] border border-[#FAB723] rounded shadow-sm hover:bg-[#e5a61d] transition" id="saveWeekBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-floppy" viewBox="0 0 16 16"><path d="M11 2H9v3h2z"/> <path d="M1.5 0h11.586a1.5 1.5 0 0 1 1.06.44l1.415 1.414A1.5 1.5 0 0 1 16 2.914V14.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 14.5v-13A1.5 1.5 0 0 1 1.5 0M1 1.5v13a.5.5 0 0 0 .5.5H2v-4.5A1.5 1.5 0 0 1 3.5 9h9a1.5 1.5 0 0 1 1.5 1.5V15h.5a.5.5 0 0 0 .5-.5V2.914a.5.5 0 0 0-.146-.353l-1.415-1.415A.5.5 0 0 0 13.086 1H13v4.5A1.5 1.5 0 0 1 11.5 7h-7A1.5 1.5 0 0 1 3 5.5V1H1.5a.5.5 0 0 0-.5.5m3 4a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5V1H4zM3 15h10v-4.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5z"/></svg> 
                        Lưu lại
                    </button>
                </div>
                
            </form>
        </div>
    </div>
</div>

<!-- Modal Mật Khẩu -->
<div id="passwordModal" class="fixed inset-0 z-[10006] hidden opacity-0 transition-opacity duration-300">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModal('passwordModal')"></div>
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0 pointer-events-none">
        <div class="relative bg-white rounded-2xl shadow-2xl border border-[#224397]/[45%] flex flex-col w-full max-w-sm pointer-events-auto transform transition-all duration-300 scale-95 translate-y-4 opacity-0">
            <form id="passwordForm">
                <div class="flex items-center justify-between p-5 border-b border-[#224397]/25">
                    <h5 class="text-lg font-bold text-[#224397] flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-key text-[#FAB723]" viewBox="0 0 16 16"><path d="M0 8a4 4 0 0 1 7.465-2H14a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0L13 9.207l-.646.647a.5.5 0 0 1-.708 0L11 9.207l-.646.647a.5.5 0 0 1-.708 0L9 9.207l-.646.647A.5.5 0 0 1 8 10h-.535A4 4 0 0 1 0 8m4-3a3 3 0 1 0 2.712 4.285A.5.5 0 0 1 7.163 9h.63l.853-.854a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.793-.793-1-1h-6.63a.5.5 0 0 1-.451-.285A3 3 0 0 0 4 5"/> <path d="M4 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/></svg> Nhập Mật Khẩu</h5>
                    <button type="button" class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 p-1.5 rounded-lg transition-colors" onclick="closeModal('passwordModal')"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg></button>
                </div>
                <div class="p-6 text-left space-y-4">
                    <p class="text-[13px] text-slate-600">Vui lòng nhập mật khẩu để mở khóa tuần này:</p>
                    <div>
                        <input type="password" class="w-full text-[13px] px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-[#224397] focus:ring-1 focus:ring-[#224397] transition-colors" id="unlock_password" placeholder="Mật khẩu..." required>
                    </div>
                </div>
                <div class="flex items-center justify-end px-6 py-4 bg-slate-50 border-t border-[#224397]/25 gap-2 rounded-b-2xl">
                    <button type="button" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-[13px] font-medium text-slate-600 bg-white border border-slate-300 rounded hover:bg-slate-50 transition-colors shadow-sm" onclick="closeModal('passwordModal')">Hủy</button>
                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-[13px] font-bold text-slate-900 bg-[#FAB723] border border-[#FAB723] rounded shadow-sm hover:bg-[#e5a61d] transition"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-unlock" viewBox="0 0 16 16"><path d="M11 1a2 2 0 0 0-2 2v4a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h5V3a3 3 0 0 1 6 0v4a.5.5 0 0 1-1 0V3a2 2 0 0 0-2-2M3 8a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V9a1 1 0 0 0-1-1z"/></svg> Mở Khóa</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Javascript Bật/Tắt Modal Mượt Mà (Vanilla JS chuẩn Tailwind)
function openModal(id) {
    const modal = document.getElementById(id);
    const content = modal.querySelector('.bg-white.rounded-2xl') || modal.firstElementChild;
    modal.classList.remove('hidden');
    void modal.offsetWidth; // Ép trình duyệt render lại (Reflow)
    modal.classList.remove('opacity-0');
    if(content) content.classList.remove('scale-95', 'translate-y-4', 'opacity-0');
}

function closeModal(id) {
    const modal = document.getElementById(id);
    const content = modal.querySelector('.bg-white.rounded-2xl') || modal.firstElementChild;
    modal.classList.add('opacity-0');
    if(content) content.classList.add('scale-95', 'translate-y-4', 'opacity-0');
    setTimeout(() => modal.classList.add('hidden'), 300);
}

document.addEventListener('DOMContentLoaded', () => {
    const canManageWeeks = <?php echo $can_manage_weeks ? 'true' : 'false'; ?>;

    const weekForm = document.getElementById('weekForm');
    const modalTitle = document.getElementById('modalTitle');
    const tenTuanInput = document.getElementById('ten_tuan');
    const tuanIdInput = document.getElementById('tuan_id');
    const addWeekBtn = document.getElementById('addWeekBtn');
    let pendingUnlock = null;
    
    // Hàm xử lý Ajax form add/edit tuần
    if (weekForm) {
        weekForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const submitBtn = document.getElementById('saveWeekBtn');
            const originalContent = submitBtn.innerHTML;
            submitBtn.innerHTML = 'Đang lưu...';
            submitBtn.disabled = true;

            const formData = new FormData(weekForm);

            try {
                const res = await fetch('/thidua/admin/tuan-hoc?action=api_add_edit', {
                    method: 'POST',
                    body: formData
                });
                const result = await res.json();
                
                if (result.success) {
                    if(typeof showToast === 'function') showToast('success', result.message || 'Thành công!');
                    closeModal('weekModal');
                    setTimeout(() => window.location.reload(), 800);
                } else {
                    if(typeof showToast === 'function') showToast('error', result.message || 'Lỗi xử lý!');
                    else showToast('error', result.message || 'Lỗi xử lý!');
                }
            } catch (err) {
                if(typeof showToast === 'function') showToast('error', 'Lỗi kết nối tới máy chủ!');
                else showToast('error', 'Lỗi kết nối!');
            } finally {
                submitBtn.innerHTML = originalContent;
                submitBtn.disabled = false;
            }
        });
    }

    // Hàm thực thi khóa/mở khóa
    window.executeLock = async function(weekId, action, password, card, btnLock) {
        try {
            const res = await fetch('/thidua/admin/tuan-hoc?action=api_lock_week', {
                method:'POST',
                headers:{ 'Content-Type':'application/json' },
                body: JSON.stringify({ week_id: weekId, action, password })
            });
            const result = await res.json();
            if (!result.success) { 
                showToast('error', 'Lỗi: ' + (result.message || 'Không thành công')); 
                return false; 
            }

            // Cập nhật giao diện tức thì
            const lockedNow = !!result.is_locked;
            
            btnLock.classList.toggle('is-locked', lockedNow);
            btnLock.classList.toggle('text-red-600', lockedNow);
            btnLock.classList.toggle('text-slate-500', !lockedNow);
            
            const iconPlaceholder = btnLock.querySelector('.icon-placeholder');
            if (iconPlaceholder) {
                iconPlaceholder.innerHTML = lockedNow 
                    ? '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="pointer-events-none text-sm" viewBox="0 0 16 16"><path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2m3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2"/></svg>'
                    : '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="pointer-events-none text-sm" viewBox="0 0 16 16"><path d="M11 1a2 2 0 0 0-2 2v4a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h5V3a3 3 0 0 1 6 0v4a.5.5 0 0 1-1 0V3a2 2 0 0 0-2-2"/></svg>';
            }
            
            btnLock.setAttribute('title', lockedNow ? 'Đã khóa' : 'Mở');

            // Đổi card state
            card.classList.toggle('bg-slate-50', lockedNow);
            card.classList.toggle('border-slate-200', lockedNow);
            card.classList.toggle('cursor-not-allowed', lockedNow);
            card.classList.toggle('opacity-80', lockedNow);
            card.classList.toggle('bg-white', !lockedNow);
            card.classList.toggle('border-[1.5px]', !lockedNow);
            card.classList.toggle('border-blue-200', !lockedNow);
            card.classList.toggle('hover:bg-orange-50', !lockedNow);
            card.classList.toggle('hover:border-amber-300', !lockedNow);
            card.classList.toggle('hover:shadow-md', !lockedNow);
            card.classList.toggle('hover:-translate-y-1', !lockedNow);
            
            // Đổi icon to/màu sắc
            const iconBg = card.querySelector('.icon-box');
            if (iconBg) {
                iconBg.classList.toggle('bg-slate-100', lockedNow);
                iconBg.classList.toggle('border-slate-200', lockedNow);
                iconBg.classList.toggle('text-slate-400', lockedNow);
                
                iconBg.classList.toggle('bg-slate-50', !lockedNow);
                iconBg.classList.toggle('border-slate-100', !lockedNow);
                iconBg.classList.toggle('text-[#224397]', !lockedNow);
                iconBg.classList.toggle('group-hover:bg-[#ffedd5]', !lockedNow);
                iconBg.classList.toggle('group-hover:border-[#fed7aa]', !lockedNow);
                iconBg.classList.toggle('group-hover:text-[#c2410c]', !lockedNow);
                
                iconBg.innerHTML = lockedNow
                    ? '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="text-base" viewBox="0 0 16 16"><path d="M6.146 7.146a.5.5 0 0 1 .708 0L8 8.293l1.146-1.147a.5.5 0 1 1 .708.708L8.707 9l1.147 1.146a.5.5 0 0 1-.708.708L8 9.707l-1.146 1.147a.5.5 0 0 1-.708-.708L7.293 9 6.146 7.854a.5.5 0 0 1 0-.708z"/><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/></svg>'
                    : '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="text-base" viewBox="0 0 16 16"><path d="M10.854 7.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 9.793l2.646-2.647a.5.5 0 0 1 .708 0"/><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/></svg>';
            }

            // Đổi text link
            if (lockedNow) {
                card.setAttribute('href', '#');
            } else {
                card.setAttribute('href', '<?= htmlspecialchars($base_url) ?>' + weekId);
            }
            return true;
        } catch (err) {
            showToast('error', 'Lỗi: ' + err.message);
            return false;
        }
    };

    // Form submit cho mật khẩu
    const passwordForm = document.getElementById('passwordForm');
    if (passwordForm) {
        passwordForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!pendingUnlock) return;
            const password = document.getElementById('unlock_password').value;
            const { weekId, card, btnLock } = pendingUnlock;
            const success = await window.executeLock(weekId, 'unlock', password, card, btnLock);
            if (success) {
                closeModal('passwordModal');
                pendingUnlock = null;
            }
        });
    }

    // 1. Tự động viết hoa tên tuần khi nhập liệu
    if (tenTuanInput) {
        tenTuanInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    }

    // 2. Mở modal để Thêm Mới Tuần
    if (canManageWeeks && addWeekBtn) {
        addWeekBtn.addEventListener('click', () => {
            weekForm.reset();
            tuanIdInput.value = '';
            
            // Tự động chọn học kỳ dựa vào tháng hiện tại (Tháng 1 đến 7 thường là Học Kỳ 2)
            const currentMonth = new Date().getMonth() + 1;
            if (currentMonth >= 1 && currentMonth <= 7) {
                document.getElementById('hoc_ky').value = "2";
            } else {
                document.getElementById('hoc_ky').value = "1";
            }
            
            modalTitle.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-calendar-plus text-[#FAB723]" viewBox="0 0 16 16"><path d="M8 7a.5.5 0 0 1 .5.5V9H10a.5.5 0 0 1 0 1H8.5v1.5a.5.5 0 0 1-1 0V10H6a.5.5 0 0 1 0-1h1.5V7.5A.5.5 0 0 1 8 7"/> <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/></svg> Thêm Tuần Học Mới';
            openModal('weekModal');
        });
    }
    
    // 3. Sử dụng Event Delegation để xử lý click trên thẻ tuần
    const grids = [document.getElementById('grid-hk1'), document.getElementById('grid-hk2')];
    
    grids.forEach(grid => {
        if (!grid) return;
        grid.addEventListener('click', async (e) => {
            const btnEdit = e.target.closest('.edit');
            const btnDelete = e.target.closest('.delete');
            const btnLock = e.target.closest('.lock');
            const card = e.target.closest('.week-card');
            
            if (!card) return;
            const weekId = card.dataset.weekId;

            if (btnEdit || btnDelete || btnLock) {
                e.preventDefault();
                e.stopPropagation();
                
                if (!canManageWeeks) {
                    showToast('error', 'Bạn không có quyền chỉnh sửa tuần học.');
                    return;
                }

                // --- XỬ LÝ SỬA TUẦN ---
                if (btnEdit) {
                    try {
                        const res = await fetch(`/thidua/admin/tuan-hoc?action=api_get_week&id=${weekId}`);
                        const data = await res.json();
                        if (data.success) {
                            weekForm.reset();
                            tuanIdInput.value = data.week.id;
                            modalTitle.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-pencil-square text-[#FAB723]" viewBox="0 0 16 16"><path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/> <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/></svg> Chỉnh Sửa Tuần Học';
                            document.getElementById('ten_tuan').value = data.week.ten_tuan;
                            document.getElementById('hoc_ky').value = data.week.hoc_ky;
                            document.getElementById('ngay_bat_dau').value = data.week.ngay_bat_dau;
                            document.getElementById('ngay_ket_thuc').value = data.week.ngay_ket_thuc;
                            openModal('weekModal');
                        } else { throw new Error('Không tìm thấy dữ liệu tuần.'); }
                    } catch (err) { showToast('error', 'Lỗi: Không thể tải dữ liệu tuần để sửa.'); }
                }

                // --- XỬ LÝ XÓA TUẦN ---
                if (btnDelete) {
                    AppSwal.fire({
                        title: 'Cảnh Báo!',
                        text: 'Bạn có chắc chắn muốn xóa tuần này? Mọi dữ liệu thi đua, điểm danh liên quan cũng sẽ bị xóa vĩnh viễn.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Xóa',
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
                    }).then(async (result_swal) => {
                        if(result_swal.isConfirmed) {
                            try {
                                const res = await fetch('/thidua/admin/tuan-hoc?action=api_delete_week', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ tuan_id: weekId })
                                });
                                const result = await res.json();
                                if (result.success) {
                                    card.style.transition = 'transform 0.2s ease, opacity 0.2s ease';
                                    card.style.transform = 'scale(0.8)';
                                    card.style.opacity = '0';
                                    setTimeout(() => card.remove(), 200);
                                    showToast('success', 'Đã xóa tuần thành công');
                                } else { throw new Error(result.message || 'Không thể xóa tuần.'); }
                            } catch (err) { showToast('error', 'Lỗi: ' + err.message); }
                        }
                    });
                }
                
                // --- XỬ LÝ KHÓA/MỞ TUẦN ---
                if (btnLock) {
                    const isLocked = btnLock.classList.contains('is-locked');
                    const action = isLocked ? 'unlock' : 'lock';

                    if (action === 'unlock') {
                        pendingUnlock = { weekId, card, btnLock };
                        document.getElementById('unlock_password').value = '';
                        openModal('passwordModal');
                        setTimeout(() => document.getElementById('unlock_password').focus(), 100);
                        return;
                    } else {
                        window.executeLock(weekId, 'lock', null, card, btnLock);
                    }
                }
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>