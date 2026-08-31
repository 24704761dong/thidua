<?php
// File: src/views/admin_exam_rooms.php
$page_title = 'Quản Lý Ca Thi & Xếp Phòng: ' . htmlspecialchars($ky_thi_info['ten_ky_thi'] ?? '');
require_once __DIR__ . '/partials/admin_header.php';
?>

<style>
    body, body > div.w-full.min-h-screen.bg-slate-50 {
        background: linear-gradient(to bottom right, #f8fafc, #E4F6FD) !important;
    }
    body::-webkit-scrollbar, html::-webkit-scrollbar { display: none; }

    /* Toast Notification styles */
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
        animation: toastIn 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        max-width: 420px;
        border: 1px solid;
    }
    .toast-success { background: #f0fdf4; color: #166534; border-color: #86efac; }
    .toast-error { background: #fef2f2; color: #991b1b; border-color: #fca5a5; }
    .toast-warning { background: #fffbeb; color: #92400e; border-color: #fcd34d; }
    .toast-info { background: #eff6ff; color: #1e40af; border-color: #93c5fd; }

    @keyframes toastIn { from { opacity:0; transform: translateX(50px); } to { opacity:1; transform: translateX(0); } }
    @keyframes toastOut { to { opacity:0; transform: translateX(50px); } }
</style>

<script>
    function switchTab(tabId) {
        var tabs = ['tabAutoAssign', 'tabShifts', 'tabRooms'];
        for (var i = 0; i < tabs.length; i++) {
            var el = document.getElementById(tabs[i]);
            if (el) {
                if (tabs[i] === tabId) {
                    el.style.setProperty('display', 'block', 'important');
                } else {
                    el.style.setProperty('display', 'none', 'important');
                }
            }
        }
        var selectEl = document.getElementById('modeSelect');
        if (selectEl && selectEl.value !== tabId) {
            selectEl.value = tabId;
        }
    }
    window.switchTab = switchTab;
</script>

<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 py-6 min-h-screen">
    
    <!-- Top Breadcrumb & Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 pb-4 border-b border-[#224397]/25 gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-1.5">
                <a href="/thidua/admin/exam-list?iframe=1" class="hover:text-[#224397] transition flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-chevron-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0"/></svg>
                    Quản Lý Kỳ Thi
                </a>
                <span>/</span>
                <span class="text-[#224397] font-semibold">Ca Thi & Xếp Phòng</span>
            </div>
            <h1 class="text-xl font-bold text-[#224397] flex items-center gap-2.5 uppercase tracking-wide m-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-door-open-fill text-[#FAB723]" viewBox="0 0 16 16"><path d="M1.5 15a.5.5 0 0 0 0 1h13a.5.5 0 0 0 0-1H13V2.5A1.5 1.5 0 0 0 11.5 1H11V.5a.5.5 0 0 0-.57-.495l-7 1A.5.5 0 0 0 3 1.5V15zM11 2h.5a.5.5 0 0 1 .5.5V15h-1zm-2.5 8c-.276 0-.5-.448-.5-1s.224-1 .5-1 .5.448.5 1-.224 1-.5 1"/></svg>
                QUẢN LÝ CA THI & PHÒNG THI: <?= htmlspecialchars($ky_thi_info['ten_ky_thi'] ?? '') ?>
            </h1>
        </div>
        
        <div class="flex items-center gap-2.5 flex-wrap">
            <!-- Dropdown Chế Độ Làm Việc Gọn Gàng (Không icon) -->
            <select id="modeSelect" onchange="switchTab(this.value)" class="px-3.5 py-2 bg-white border border-[#224397]/30 rounded-lg text-xs font-bold text-[#224397] focus:outline-none focus:border-[#224397] shadow-sm cursor-pointer hover:bg-slate-50 transition">
                <option value="tabAutoAssign" selected>Xếp Phòng Tối Ưu</option>
                <option value="tabShifts">Thiết Lập Ca Thi</option>
                <option value="tabRooms">Phòng Thi Thủ Công</option>
            </select>

            <a href="/thidua/admin/exam-participants?id=<?= $ky_thi_id ?>&iframe=1" class="px-3.5 py-2 bg-blue-50 text-[#224397] border border-blue-200 rounded-lg hover:bg-[#224397] hover:text-white transition-all text-xs font-semibold flex items-center gap-1.5 shadow-sm hover:scale-105">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-people-fill" viewBox="0 0 16 16"><path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/></svg>
                Danh Sách Thí Sinh
            </a>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 1: XẾP PHÒNG THI TỐI ƯU ĐA LƯỢT (AUTO ASSIGN) - MẶC ĐỊNH -->
    <!-- ========================================================================= -->
    <div id="tabAutoAssign" class="tab-content space-y-6" style="display: block;">
        
        <!-- Control Form Basic & Gọn -->
        <div class="bg-white rounded-xl shadow-sm border border-[#224397]/25 p-4">
            <form id="autoAssignForm" onsubmit="event.preventDefault(); triggerAutoAssign();" class="flex flex-wrap items-end gap-3 text-xs">
                <div class="flex-1 min-w-[200px]">
                    <label class="block font-bold text-slate-700 mb-1">Chọn Ca Thi <span class="text-red-500">*</span></label>
                    <select id="assignShiftSelect" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-[#224397] focus:outline-none font-semibold text-slate-800 bg-slate-50">
                        <option value="">-- Xếp tất cả các ca thi --</option>
                        <?php foreach ($ds_ca_thi as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['ten_ca']) ?> (<?= $s['so_luot_thi'] ?> lượt)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="w-36">
                    <label class="block font-bold text-slate-700 mb-1">Sĩ số tối đa / phòng</label>
                    <input type="number" id="maxStudents" value="24" min="1" max="100" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-[#224397] focus:outline-none font-semibold text-slate-800 bg-slate-50 text-center" required>
                </div>

                <div class="w-36">
                    <label class="block font-bold text-slate-700 mb-1">Số môn tối đa / phòng</label>
                    <input type="number" id="maxSubjects" value="2" min="1" max="5" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-[#224397] focus:outline-none font-semibold text-slate-800 bg-slate-50 text-center" required>
                </div>

                <div>
                    <button type="button" onclick="triggerAutoAssign()" id="btnRunAssign" class="px-5 py-2 bg-[#224397] hover:bg-[#FAB723] hover:text-slate-900 text-white rounded-lg font-bold text-xs shadow-sm hover:scale-[1.02] transition-all flex items-center justify-center gap-1.5 h-[38px] cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-play-fill" viewBox="0 0 16 16"><path d="m11.596 8.697-6.363 3.692c-.54.313-1.233-.066-1.233-.697V4.308c0-.63.692-1.01 1.233-.696l6.363 3.692a.802.802 0 0 1 0 1.393"/></svg>
                        <span>Bắt Đầu Xếp Phòng</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Room Matrix Display per Shift -->
        <div id="shiftMatrixContainer" class="space-y-6">
            <?php foreach ($ds_ca_thi as $shift): 
                $shift_id = (int)$shift['id'];
                $matrix = $matrix_by_shift[$shift_id] ?? [];
                $so_luot = (int)$shift['so_luot_thi'];
            ?>
                <div class="bg-white rounded-xl shadow-sm border border-[#224397]/25 overflow-hidden">
                    <div class="bg-slate-50 px-5 py-3 border-b border-[#224397]/25 font-semibold text-[#224397] flex items-center justify-between text-xs uppercase">
                        <span class="flex items-center gap-2 font-bold text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" class="bi bi-grid-3x3-gap-fill text-[#FAB723]" viewBox="0 0 16 16"><path d="M1 2a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1zm5 0a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1zm5 0a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1zM1 7a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1zm5 0a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1zm5 0a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1zM1 12a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1zm5 0a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1zm5 0a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1z"/></svg>
                            MA TRẬN PHÂN PHÒNG: <?= htmlspecialchars($shift['ten_ca']) ?>
                        </span>
                        <span class="text-slate-500 normal-case font-medium">
                            Số lượt thi: <strong class="text-[#224397] font-bold"><?= $so_luot ?></strong> | Tổng phòng: <strong class="text-[#224397] font-bold"><?= count($matrix) ?></strong>
                        </span>
                    </div>

                    <div class="overflow-x-auto w-full">
                        <table class="w-full text-left text-xs text-slate-600 border-collapse">
                            <thead>
                                <tr class="bg-slate-100/80 text-[#224397] uppercase text-[11px] font-bold tracking-wide border-b border-[#224397]/25 whitespace-nowrap">
                                    <th class="p-2.5 border-r border-slate-200 text-center w-12">STT</th>
                                    <th class="p-2.5 border-r border-slate-200 text-center w-28">Phòng Thi</th>
                                    <th class="p-2.5 border-r border-slate-200 text-center w-24">Tổng Sĩ Số</th>
                                    <?php for ($l = 1; $l <= $so_luot; $l++): ?>
                                        <th class="p-2.5 border-r border-slate-200 text-center min-w-[200px]">Lượt <?= $l ?> (Môn Thi & Thí Sinh)</th>
                                    <?php endfor; ?>
                                    <th class="p-2.5 text-center w-24">Số Túi Đề</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($matrix)): ?>
                                    <tr>
                                        <td colspan="<?= 4 + $so_luot ?>" class="text-center text-slate-400 p-8 italic">
                                            Ca thi này chưa được xếp phòng. Bấm "Bắt Đầu Xếp Phòng" để phân bổ tự động.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $stt_r = 1; foreach ($matrix as $room_id => $r_data): ?>
                                        <tr class="hover:bg-blue-50/40 transition-colors border-b border-slate-200">
                                            <td class="p-2.5 border-r border-slate-200 text-center text-slate-400 font-medium"><?= $stt_r++ ?></td>
                                            <td class="p-2.5 border-r border-slate-200 text-center font-bold text-[#224397]">
                                                <?= htmlspecialchars($r_data['ten_phong']) ?>
                                            </td>
                                            <td class="p-2.5 border-r border-slate-200 text-center font-bold text-slate-800">
                                                <span class="inline-block px-2 py-0.5 rounded-full bg-blue-50 text-[#224397] font-bold border border-blue-200">
                                                    <?= $r_data['total_students'] ?> HS
                                                </span>
                                            </td>
                                            <?php for ($l = 1; $l <= $so_luot; $l++): 
                                                $slot_subs = $r_data['slots'][$l] ?? [];
                                            ?>
                                                <td class="p-2.5 border-r border-slate-200">
                                                    <?php if (empty($slot_subs)): ?>
                                                        <span class="text-slate-300 italic text-[11px]">Trống</span>
                                                    <?php else: ?>
                                                        <div class="flex flex-wrap gap-1.5 items-center">
                                                            <?php foreach ($slot_subs as $sub_code => $count): ?>
                                                                <span class="px-2 py-1 rounded bg-slate-100 border border-slate-200 font-semibold text-slate-700 text-[11px] flex items-center gap-1 shadow-xs">
                                                                    <strong class="text-[#224397]"><?= htmlspecialchars(exam_subject_label($sub_code)) ?>:</strong>
                                                                    <span class="text-emerald-700 font-bold"><?= $count ?> HS</span>
                                                                </span>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endfor; ?>
                                            <td class="p-2.5 text-center font-bold text-amber-800">
                                                <span class="inline-block px-2 py-0.5 rounded bg-amber-50 border border-amber-200">
                                                    <?= $r_data['bide_count'] ?> túi
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- TAB 2: THIẾT LẬP CA THI & LƯỢT THI -->
    <!-- ========================================================================= -->
    <div id="tabShifts" class="tab-content space-y-6" style="display: none;">
        <div class="bg-white rounded-xl shadow-sm border border-[#224397]/25 p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h3 class="text-sm font-bold text-[#224397] uppercase m-0 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clock-history text-[#FAB723]" viewBox="0 0 16 16"><path d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022zm2.004.45a7 7 0 0 0-.985-.299l.219-.976q.576.129 1.126.342zm1.37.71a7 7 0 0 0-.439-.27l.493-.87a8 8 0 0 1 .979.654l-.615.789a7 7 0 0 0-.418-.302zm1.834 1.79a7 7 0 0 0-.653-.796l.724-.69q.406.429.747.91zm.744 1.352a7 7 0 0 0-.214-.468l.893-.45a8 8 0 0 1 .45 1.088l-.95.313a7 7 0 0 0-.179-.483m.53 2.507a7 7 0 0 0-.1-1.025l.985-.17q.1.58.116 1.17zm-.131 1.538q.05-.254.084-.51l.992.115a8 8 0 0 1-.167 1.183l-.946-.365q.023-.21.037-.423"/></svg>
                    CẤU HÌNH CÁC CA THI TRONG KỲ THI
                </h3>
                <p class="text-xs text-slate-500 mt-1">
                    Thiết lập danh sách các môn thi diễn ra trong từng ca và số lượt thi tối đa (1 lượt cho môn bắt buộc, 2 - 3 lượt cho các môn tự chọn).
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" class="px-3.5 py-2 bg-[#224397] text-white rounded-lg hover:bg-[#FAB723] hover:text-white font-medium flex items-center gap-1.5 text-xs shadow-sm hover:scale-105 transition-all" onclick="openShiftModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-plus-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3z"/></svg>
                    Thêm Ca Thi Mới
                </button>
                <button type="button" class="px-3 py-2 bg-white text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-50 text-xs font-semibold" onclick="resetDefaultShifts()" title="Khôi phục ca mặc định">
                    Mặc Định (3 Ca)
                </button>
            </div>
        </div>

        <!-- Shift Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach ($ds_ca_thi as $shift): ?>
                <div class="bg-white rounded-xl shadow-sm border border-[#224397]/25 p-5 relative overflow-hidden flex flex-col justify-between">
                    <div class="absolute top-0 right-0 w-16 h-16 bg-[#224397]/5 rounded-bl-full pointer-events-none"></div>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-[#224397] border border-blue-200">
                                Thứ tự: <?= $shift['thu_tu'] ?>
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                <?= $shift['so_luot_thi'] ?> Lượt thi / ca
                            </span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-800 mb-3"><?= htmlspecialchars($shift['ten_ca']) ?></h4>
                        
                        <div class="text-xs text-slate-600 mb-4 space-y-1.5">
                            <div class="flex items-center gap-1.5">
                                <span class="text-slate-400">Môn thi trong ca:</span>
                            </div>
                            <div class="flex flex-wrap gap-1 mt-1">
                                <?php foreach ($shift['mon_hoc_list'] as $m_code): ?>
                                    <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                        <?= htmlspecialchars(exam_subject_label($m_code)) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                        <div class="text-[11px] text-slate-500">
                            Đã xếp: <strong class="text-[#224397]"><?= $shift['assigned_students'] ?? 0 ?></strong> thí sinh
                        </div>
                        <div class="flex items-center gap-1">
                            <button type="button" class="p-1.5 text-[#224397] hover:bg-blue-50 rounded transition" onclick='openShiftModal(<?= json_encode($shift, JSON_UNESCAPED_UNICODE) ?>)' title="Sửa ca thi">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16"><path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/></svg>
                            </button>
                            <button type="button" class="p-1.5 text-red-500 hover:bg-red-50 rounded transition" onclick="deleteShift(<?= $shift['id'] ?>, '<?= htmlspecialchars(addslashes($shift['ten_ca'])) ?>')" title="Xóa ca thi">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16"><path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 3: DANH SÁCH PHÒNG THI THỦ CÔNG -->
    <!-- ========================================================================= -->
    <div id="tabRooms" class="tab-content space-y-6" style="display: none;">
        <div class="bg-white rounded-xl shadow-sm border border-[#224397]/25 overflow-hidden">
            <div class="bg-slate-50 px-5 py-3.5 border-b border-[#224397]/25 font-semibold text-[#224397] flex items-center justify-between text-xs uppercase">
                <span class="flex items-center gap-2 font-bold text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-door-open-fill text-[#FAB723]" viewBox="0 0 16 16"><path d="M1.5 15a.5.5 0 0 0 0 1h13a.5.5 0 0 0 0-1H13V2.5A1.5 1.5 0 0 0 11.5 1H11V.5a.5.5 0 0 0-.57-.495l-7 1A.5.5 0 0 0 3 1.5V15zM11 2h.5a.5.5 0 0 1 .5.5V15h-1zm-2.5 8c-.276 0-.5-.448-.5-1s.224-1 .5-1 .5.448.5 1-.224 1-.5 1"/></svg>
                    DANH SÁCH TẤT CẢ CÁC PHÒNG THI
                </span>
                <button type="button" class="px-3.5 py-1.5 bg-[#224397] text-white rounded-lg hover:bg-[#FAB723] hover:text-white font-medium flex items-center gap-1.5 text-xs shadow-sm hover:scale-105 transition-all" onclick="openRoomModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" class="bi bi-plus-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3z"/></svg>
                    Thêm Phòng Thi
                </button>
            </div>

            <div class="overflow-x-auto w-full">
                <table class="w-full text-left text-xs text-slate-600 border-collapse">
                    <thead>
                        <tr class="bg-slate-100/80 text-[#224397] uppercase text-[11px] font-bold tracking-wide border-b border-[#224397]/25">
                            <th class="p-3 border-r border-slate-200 text-center w-12">STT</th>
                            <th class="p-3 border-r border-slate-200">Tên Phòng Thi</th>
                            <th class="p-3 border-r border-slate-200 text-center w-36">Sĩ Số Tối Đa</th>
                            <th class="p-3 border-r border-slate-200 text-center w-40">Đã Phân Bổ</th>
                            <th class="p-3 text-center w-32">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody id="roomsTableBody">
                        <?php if (empty($ds_phong_thi)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-slate-400 p-8 italic">
                                    Chưa có phòng thi nào được tạo.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($ds_phong_thi as $index => $room): ?>
                                <tr id="room-row-<?= $room['id'] ?>" class="hover:bg-blue-50/40 transition-colors border-b border-slate-200">
                                    <td class="p-3 border-r border-slate-200 text-center font-medium text-slate-400"><?= $index + 1 ?></td>
                                    <td class="p-3 border-r border-slate-200 font-bold text-[#224397]"><?= htmlspecialchars($room['ten_phong']) ?></td>
                                    <td class="p-3 border-r border-slate-200 text-center font-bold text-slate-700"><?= $room['si_so_toi_da'] ?> HS</td>
                                    <td class="p-3 border-r border-slate-200 text-center font-semibold text-emerald-700">
                                        <?= (int)($room['so_luong_thi_sinh'] ?? 0) ?> Thí sinh
                                    </td>
                                    <td class="p-3 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button type="button" class="p-1.5 text-[#224397] hover:bg-blue-50 rounded transition" onclick='openRoomModal(<?= json_encode($room, JSON_UNESCAPED_UNICODE) ?>)' title="Sửa phòng">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16"><path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/></svg>
                                            </button>
                                            <button type="button" class="p-1.5 text-red-500 hover:bg-red-50 rounded transition" onclick="deleteRoom(<?= $room['id'] ?>, '<?= htmlspecialchars(addslashes($room['ten_phong'])) ?>')" title="Xóa phòng">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16"><path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 1 0V5a.5.5 0 0 0-.5-.5"/></svg>
                                            </button>
                                        </div>
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

<!-- ========================================================================= -->
<!-- MODAL: THÊM / SỬA CA THI -->
<!-- ========================================================================= -->
<div id="shiftModal" class="fixed inset-0 z-[10005] hidden items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0" onclick="closeModal('shiftModal')">
    <div class="bg-white rounded-xl shadow-2xl w-[520px] max-w-[92%] flex flex-col overflow-hidden border border-slate-300 transform transition-all duration-300 scale-95 translate-y-4 opacity-0 modal-content-box" onclick="event.stopPropagation()">
        
        <form id="shiftForm" onsubmit="event.preventDefault(); submitShiftForm();">
            <input type="hidden" id="shiftId" name="id" value="">
            
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-slate-50">
                <h5 class="text-base font-bold text-[#224397] flex items-center gap-2 m-0" id="shiftModalTitle">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="currentColor" class="bi bi-clock-history text-[#FAB723]" viewBox="0 0 16 16"><path d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022zm2.004.45a7 7 0 0 0-.985-.299l.219-.976q.576.129 1.126.342zm1.37.71a7 7 0 0 0-.439-.27l.493-.87a8 8 0 0 1 .979.654l-.615.789a7 7 0 0 0-.418-.302zm1.834 1.79a7 7 0 0 0-.653-.796l.724-.69q.406.429.747.91zm.744 1.352a7 7 0 0 0-.214-.468l.893-.45a8 8 0 0 1 .45 1.088l-.95.313a7 7 0 0 0-.179-.483m.53 2.507a7 7 0 0 0-.1-1.025l.985-.17q.1.58.116 1.17zm-.131 1.538q.05-.254.084-.51l.992.115a8 8 0 0 1-.167 1.183l-.946-.365q.023-.21.037-.423"/></svg>
                    Thiết Lập Ca Thi
                </h5>
                <button type="button" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition" onclick="closeModal('shiftModal')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg>
                </button>
            </div>
            
            <div class="px-6 py-5 space-y-4 text-xs text-slate-700">
                <div>
                    <label class="block font-bold uppercase text-slate-600 mb-1">Tên Ca Thi <span class="text-red-500">*</span></label>
                    <input type="text" id="shiftName" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-[#224397] focus:outline-none font-semibold text-slate-800" placeholder="VD: Ca 3: Các môn tự chọn KHTN & KHXH" required>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold uppercase text-slate-600 mb-1">Số Lượt Thi Tối Đa Trong Ca</label>
                        <select id="shiftSlots" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-[#224397] focus:outline-none font-semibold text-slate-800">
                            <option value="1">1 Lượt thi / ca (Môn đơn)</option>
                            <option value="2">2 Lượt thi / ca (Tối ưu 2 môn)</option>
                            <option value="3">3 Lượt thi / ca (Tối ưu 3 môn)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold uppercase text-slate-600 mb-1">Thứ Tự Ca Thi</label>
                        <input type="number" id="shiftOrder" value="1" min="1" max="20" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-[#224397] focus:outline-none font-semibold text-slate-800 text-center" required>
                    </div>
                </div>

                <div>
                    <label class="block font-bold uppercase text-slate-600 mb-1.5">Các Môn Thi Diễn Ra Trong Ca Này <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 bg-slate-50 p-3 rounded-lg border border-slate-200 max-h-48 overflow-y-auto">
                        <?php 
                        $all_subjects = exam_all_subject_options();
                        foreach ($all_subjects as $code => $name): 
                        ?>
                            <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer hover:text-[#224397]">
                                <input type="checkbox" name="shiftSubjects" value="<?= $code ?>" class="rounded text-[#224397] focus:ring-[#224397]">
                                <span><?= htmlspecialchars($name) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex justify-end gap-2">
                <button type="button" class="px-4 py-2 text-xs font-semibold text-gray-600 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 transition" onclick="closeModal('shiftModal')">Hủy</button>
                <button type="submit" class="px-4 py-2 text-xs font-bold text-slate-900 bg-[#FAB723] rounded-lg shadow-sm hover:bg-[#e5a61d] transition" id="btnSaveShift">
                    Lưu Ca Thi
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: THÊM / SỬA PHÒNG THI THỦ CÔNG -->
<!-- ========================================================================= -->
<div id="roomModal" class="fixed inset-0 z-[10005] hidden items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0" onclick="closeModal('roomModal')">
    <div class="bg-white rounded-xl shadow-2xl w-[420px] max-w-[92%] flex flex-col overflow-hidden border border-slate-300 transform transition-all duration-300 scale-95 translate-y-4 opacity-0 modal-content-box" onclick="event.stopPropagation()">
        
        <form id="roomForm" onsubmit="event.preventDefault(); submitRoomForm();">
            <input type="hidden" id="roomId" name="id" value="">
            
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-slate-50">
                <h5 class="text-base font-bold text-[#224397] flex items-center gap-2 m-0" id="roomModalTitle">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="currentColor" class="bi bi-door-open-fill text-[#FAB723]" viewBox="0 0 16 16"><path d="M1.5 15a.5.5 0 0 0 0 1h13a.5.5 0 0 0 0-1H13V2.5A1.5 1.5 0 0 0 11.5 1H11V.5a.5.5 0 0 0-.57-.495l-7 1A.5.5 0 0 0 3 1.5V15z"/></svg>
                    Phòng Thi
                </h5>
                <button type="button" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition" onclick="closeModal('roomModal')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg>
                </button>
            </div>
            
            <div class="px-6 py-5 space-y-4 text-xs text-slate-700">
                <div>
                    <label class="block font-bold uppercase text-slate-600 mb-1">Tên Phòng Thi <span class="text-red-500">*</span></label>
                    <input type="text" id="roomName" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-[#224397] focus:outline-none font-semibold text-slate-800" placeholder="VD: Phòng 01" required>
                </div>
                
                <div>
                    <label class="block font-bold uppercase text-slate-600 mb-1">Sĩ Số Tối Đa Của Phòng</label>
                    <input type="number" id="roomCapacity" value="24" min="1" max="100" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-[#224397] focus:outline-none font-semibold text-slate-800 text-center" required>
                </div>
            </div>
            
            <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex justify-end gap-2">
                <button type="button" class="px-4 py-2 text-xs font-semibold text-gray-600 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 transition" onclick="closeModal('roomModal')">Hủy</button>
                <button type="submit" class="px-4 py-2 text-xs font-bold text-slate-900 bg-[#FAB723] rounded-lg shadow-sm hover:bg-[#e5a61d] transition" id="btnSaveRoom">
                    Lưu Phòng Thi
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Toast Container -->
<div id="toast-container"></div>

<script>
    const KY_THI_ID = <?= (int)$ky_thi_id ?>;

    // ===== TAB SWITCHING =====
    function switchTab(tabId) {
        const tabs = ['tabAutoAssign', 'tabShifts', 'tabRooms'];
        tabs.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                if (id === tabId) {
                    el.style.display = 'block';
                } else {
                    el.style.display = 'none';
                }
            }
        });

        const selectEl = document.getElementById('modeSelect');
        if (selectEl && selectEl.value !== tabId) {
            selectEl.value = tabId;
        }
    }
    window.switchTab = switchTab;

    // ===== MODAL ANIMATION HELPERS =====
    function openModal(id) {
        const modal = document.getElementById(id);
        const content = modal.querySelector('.modal-content-box');
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
        void modal.offsetWidth;
        modal.style.opacity = '1';
        modal.classList.remove('opacity-0');
        if (content) {
            content.style.transform = 'scale(1) translateY(0)';
            content.style.opacity = '1';
            content.classList.remove('scale-95', 'translate-y-4', 'opacity-0');
        }
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        const content = modal.querySelector('.modal-content-box');
        modal.style.opacity = '0';
        if (content) {
            content.style.transform = 'scale(0.95) translateY(1rem)';
            content.style.opacity = '0';
        }
        setTimeout(() => {
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }, 300);
    }

    // ===== TOAST HELPER =====
    function showToast(message, type = 'success', duration = 3000) {
        const container = document.getElementById('toast-container');
        if (!container) return;
        
        const toast = document.createElement('div');
        toast.className = `toast-item toast-${type}`;
        
        let icon = '';
        if (type === 'success') icon = `<svg width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill shrink-0" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>`;
        else if (type === 'error') icon = `<svg width="16" height="16" fill="currentColor" class="bi bi-exclamation-circle-fill shrink-0" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/></svg>`;
        else icon = `<svg width="16" height="16" fill="currentColor" class="bi bi-info-circle-fill shrink-0" viewBox="0 0 16 16"><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2"/></svg>`;
        
        toast.innerHTML = `${icon} <span>${message}</span>`;
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'toastOut 0.35s forwards';
            setTimeout(() => toast.remove(), 350);
        }, duration);
    }

    // ===== CA THI MODAL & CRUD =====
    function openShiftModal(shiftData = null) {
        const modal = document.getElementById('shiftModal');
        const form = document.getElementById('shiftForm');
        form.reset();

        const checkboxes = form.querySelectorAll('input[name="shiftSubjects"]');
        checkboxes.forEach(cb => cb.checked = false);

        if (shiftData) {
            document.getElementById('shiftId').value = shiftData.id;
            document.getElementById('shiftName').value = shiftData.ten_ca;
            document.getElementById('shiftSlots').value = shiftData.so_luot_thi;
            document.getElementById('shiftOrder').value = shiftData.thu_tu;
            document.getElementById('shiftModalTitle').textContent = 'Sửa Ca Thi: ' + shiftData.ten_ca;

            const subs = shiftData.mon_hoc_list || [];
            checkboxes.forEach(cb => {
                if (subs.includes(cb.value)) cb.checked = true;
            });
        } else {
            document.getElementById('shiftId').value = '';
            document.getElementById('shiftOrder').value = '1';
            document.getElementById('shiftModalTitle').textContent = 'Thêm Ca Thi Mới';
        }

        openModal('shiftModal');
    }

    async function submitShiftForm() {
        const form = document.getElementById('shiftForm');
        const id = document.getElementById('shiftId').value;
        const ten_ca = document.getElementById('shiftName').value.trim();
        const so_luot_thi = parseInt(document.getElementById('shiftSlots').value) || 1;
        const thu_tu = parseInt(document.getElementById('shiftOrder').value) || 1;

        const checkboxes = form.querySelectorAll('input[name="shiftSubjects"]:checked');
        const danh_sach_mon = Array.from(checkboxes).map(cb => cb.value);

        if (!ten_ca) {
            showToast('Vui lòng nhập tên ca thi.', 'warning');
            return;
        }
        if (danh_sach_mon.length === 0) {
            showToast('Vui lòng chọn ít nhất 1 môn thi cho ca này.', 'warning');
            return;
        }

        const btn = document.getElementById('btnSaveShift');
        btn.disabled = true;

        try {
            const res = await fetch('/thidua/api/exam-shift-crud', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: id ? 'update' : 'create',
                    id: id ? parseInt(id) : null,
                    ky_thi_id: KY_THI_ID,
                    ten_ca,
                    so_luot_thi,
                    thu_tu,
                    danh_sach_mon
                })
            });

            const data = await res.json();
            if (data.success) {
                closeModal('shiftModal');
                showToast(data.message || 'Lưu ca thi thành công!', 'success');
                setTimeout(() => location.reload(), 700);
            } else {
                showToast(data.message || 'Lỗi khi lưu ca thi.', 'error');
            }
        } catch (e) {
            showToast('Lỗi kết nối máy chủ.', 'error');
        } finally {
            btn.disabled = false;
        }
    }

    async function deleteShift(shiftId, shiftName) {
        if (!confirm(`Bạn có chắc chắn muốn xóa "${shiftName}"? Kết quả xếp phòng của ca này sẽ bị xóa.`)) {
            return;
        }

        try {
            const res = await fetch('/thidua/api/exam-shift-crud', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete', id: shiftId, ky_thi_id: KY_THI_ID })
            });
            const data = await res.json();
            if (data.success) {
                showToast('Đã xóa ca thi thành công!', 'success');
                setTimeout(() => location.reload(), 700);
            } else {
                showToast(data.message || 'Lỗi khi xóa ca thi.', 'error');
            }
        } catch (e) {
            showToast('Lỗi kết nối máy chủ.', 'error');
        }
    }

    async function resetDefaultShifts() {
        if (!confirm('Bạn có muốn khôi phục 3 ca thi chuẩn (Ca 1: Toán, Ca 2: Văn, Ca 3: Môn Tự chọn)?')) return;
        try {
            const res = await fetch('/thidua/api/exam-shift-crud', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'reset_default', ky_thi_id: KY_THI_ID })
            });
            const data = await res.json();
            if (data.success) {
                showToast('Khôi phục ca thi mặc định thành công!', 'success');
                setTimeout(() => location.reload(), 700);
            } else {
                showToast(data.message || 'Lỗi xử lý.', 'error');
            }
        } catch (e) {
            showToast('Lỗi kết nối.', 'error');
        }
    }

    // ===== THUẬT TOÁN XẾP PHÒNG TỐI ƯU (AUTO ASSIGN) =====
    async function triggerAutoAssign() {
        const shiftSelect = document.getElementById('assignShiftSelect');
        const ca_thi_id = shiftSelect ? shiftSelect.value : '';
        const max_students = parseInt(document.getElementById('maxStudents')?.value) || 24;
        const max_subjects = parseInt(document.getElementById('maxSubjects')?.value) || 2;

        const btn = document.getElementById('btnRunAssign');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span> <span>Đang xếp phòng...</span>';
        }

        showToast('Đang tiến hành tối ưu xếp phòng thi...', 'info');

        try {
            const res = await fetch('/thidua/api/exam-room-auto-assign', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    ky_thi_id: KY_THI_ID,
                    ca_thi_id: ca_thi_id ? parseInt(ca_thi_id) : null,
                    max_students_per_room: max_students,
                    max_subjects_per_room: max_subjects
                })
            });

            const text = await res.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (err) {
                console.error("Server raw response:", text);
                showToast('Lỗi phản hồi máy chủ: ' + text.substring(0, 120), 'error');
                return;
            }

            if (data.success) {
                showToast(data.message || 'Xếp phòng thành công!', 'success');
                setTimeout(() => location.reload(), 900);
            } else {
                showToast(data.message || 'Lỗi khi xếp phòng.', 'error');
            }
        } catch (e) {
            console.error(e);
            showToast('Lỗi kết nối máy chủ: ' + e.message, 'error');
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-play-fill" viewBox="0 0 16 16"><path d="m11.596 8.697-6.363 3.692c-.54.313-1.233-.066-1.233-.697V4.308c0-.63.692-1.01 1.233-.696l6.363 3.692a.802.802 0 0 1 0 1.393"/></svg>
                    <span>Bắt Đầu Xếp Phòng</span>
                `;
            }
        }
    }
    window.triggerAutoAssign = triggerAutoAssign;

    // ===== PHÒNG THI THỦ CÔNG CRUD =====
    function openRoomModal(roomData = null) {
        document.getElementById('roomForm').reset();
        if (roomData) {
            document.getElementById('roomId').value = roomData.id;
            document.getElementById('roomName').value = roomData.ten_phong;
            document.getElementById('roomCapacity').value = roomData.si_so_toi_da || 24;
            document.getElementById('roomModalTitle').textContent = 'Sửa Phòng Thi: ' + roomData.ten_phong;
        } else {
            document.getElementById('roomId').value = '';
            document.getElementById('roomCapacity').value = 24;
            document.getElementById('roomModalTitle').textContent = 'Thêm Phòng Thi Mới';
        }
        openModal('roomModal');
    }

    async function submitRoomForm() {
        const id = document.getElementById('roomId').value;
        const ten_phong = document.getElementById('roomName').value.trim();
        const si_so_toi_da = parseInt(document.getElementById('roomCapacity').value) || 24;

        if (!ten_phong) {
            showToast('Vui lòng nhập tên phòng thi.', 'warning');
            return;
        }

        const btn = document.getElementById('btnSaveRoom');
        btn.disabled = true;

        try {
            const res = await fetch('/thidua/api/exam-room-crud', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: id ? 'update' : 'create',
                    id: id ? parseInt(id) : null,
                    ky_thi_id: KY_THI_ID,
                    ten_phong,
                    si_so_toi_da
                })
            });

            const data = await res.json();
            if (data.success) {
                closeModal('roomModal');
                showToast(data.message || 'Lưu phòng thi thành công!', 'success');
                setTimeout(() => location.reload(), 700);
            } else {
                showToast(data.message || 'Lỗi khi lưu phòng.', 'error');
            }
        } catch (e) {
            showToast('Lỗi kết nối.', 'error');
        } finally {
            btn.disabled = false;
        }
    }

    async function deleteRoom(roomId, roomName) {
        if (!confirm(`Bạn có chắc chắn muốn xóa "${roomName}"? Thí sinh đã xếp vào phòng này sẽ bị hủy xếp phòng.`)) return;
        try {
            const res = await fetch('/thidua/api/exam-room-crud', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete', id: roomId, ky_thi_id: KY_THI_ID })
            });
            const data = await res.json();
            if (data.success) {
                showToast('Đã xóa phòng thi thành công!', 'success');
                setTimeout(() => location.reload(), 700);
            } else {
                showToast(data.message || 'Lỗi khi xóa.', 'error');
            }
        } catch (e) {
            showToast('Lỗi kết nối.', 'error');
        }
    }
</script>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>
