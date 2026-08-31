<?php
$page_title = 'Quản Lý Kỳ Thi';
require_once __DIR__ . '/partials/admin_header.php';
// Biến $ds_ky_thi đã được controller nạp theo năm học
?>

<style>
    body, body > div.w-full.min-h-screen.bg-slate-50 {
        background: linear-gradient(to bottom right, #f8fafc, #E4F6FD) !important;
    }
    body::-webkit-scrollbar, html::-webkit-scrollbar { display: none; }

    /* Custom Switch Toggle */
    .switch-toggle {
        position: relative;
        display: inline-block;
        width: 40px;
        height: 22px;
    }
    .switch-toggle input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .switch-slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #cbd5e1;
        transition: .3s;
        border-radius: 22px;
    }
    .switch-slider:before {
        position: absolute;
        content: "";
        height: 16px;
        width: 16px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }
    input:checked + .switch-slider {
        background-color: #224397;
    }
    input:checked + .switch-slider:before {
        transform: translateX(18px);
    }

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
        max-width: 380px;
        border: 1px solid;
    }
    .toast-success { background: #f0fdf4; color: #166534; border-color: #86efac; }
    .toast-error { background: #fef2f2; color: #991b1b; border-color: #fca5a5; }
    .toast-warning { background: #fffbeb; color: #92400e; border-color: #fcd34d; }
    .toast-info { background: #eff6ff; color: #1e40af; border-color: #93c5fd; }

    @keyframes toastIn { from { opacity:0; transform: translateX(50px); } to { opacity:1; transform: translateX(0); } }
    @keyframes toastOut { to { opacity:0; transform: translateX(50px); } }

    /* Action icon button styling */
    .icon-action-btn {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }
    .icon-action-btn:hover {
        transform: translateY(-2px) scale(1.08);
    }
</style>

<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 py-6 min-h-screen">
    
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 pb-4 border-b border-[#224397]/25 gap-4">
        <h1 class="text-xl font-bold text-[#224397] flex items-center gap-2.5 uppercase tracking-wide m-0">
            <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-calendar-event text-[#FAB723]" viewBox="0 0 16 16"><path d="M11 6.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5z"/><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/></svg>
            QUẢN LÝ KỲ THI
        </h1>
        
        <div class="flex items-center gap-2">
            <button type="button" class="px-4 py-2 bg-[#224397] text-white rounded-lg font-medium flex items-center gap-2 text-[13px] shadow-sm hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] transition-all duration-300" onclick="showCreateModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-plus-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3z"/></svg> 
                TẠO KỲ THI MỚI
            </button>
        </div>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-xl shadow-sm border border-[#224397]/25 mb-6">
        <div class="bg-slate-50 px-5 py-3.5 border-b border-[#224397]/25 font-semibold text-[#224397] flex items-center justify-between text-sm uppercase rounded-t-xl">
            <span class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="currentColor" class="bi bi-card-checklist text-[#FAB723]" viewBox="0 0 16 16"><path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2z"/><path d="M7 5.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0M7 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0"/></svg>
                DANH SÁCH CÁC KỲ THI
            </span>
            <span class="text-xs font-normal text-slate-500 normal-case">Tổng số: <strong class="text-[#224397] font-bold"><?= count($ds_ky_thi ?? []) ?></strong> kỳ thi</span>
        </div>

        <div class="w-full overflow-x-auto">
            <table class="w-full text-left text-[13.5px] text-slate-600 border-collapse">
                <thead>
                    <tr class="bg-slate-100/80 text-[#224397] uppercase text-[12px] font-bold tracking-wide border-b border-[#224397]/25 whitespace-nowrap">
                        <th class="p-3.5 border-r border-slate-200 text-center w-12">STT</th>
                        <th class="p-3.5 border-r border-slate-200 min-w-[220px]">Tên Kỳ Thi</th>
                        <th class="p-3.5 border-r border-slate-200 text-center w-48">Thời Gian</th>
                        <th class="p-3.5 border-r border-slate-200 text-center w-36">Thí Sinh</th>
                        <th class="p-3.5 border-r border-slate-200 text-center w-36">Công Khai</th>
                        <th class="p-3.5 text-center w-44">Thao Tác</th>
                    </tr>
                </thead>
                <tbody id="examTableBody">
                    <?php if (empty($ds_ky_thi)): ?>
                        <tr id="emptyRow">
                            <td colspan="6" class="text-center text-slate-400 p-12 italic">
                                <svg xmlns="http://www.w3.org/2000/svg" width="2.8em" height="2.8em" fill="currentColor" class="bi bi-calendar-x mx-auto mb-3 text-slate-300" viewBox="0 0 16 16"><path d="M6.146 7.146a.5.5 0 0 1 .708 0L8 8.293l1.146-1.147a.5.5 0 1 1 .708.708L8.707 9l1.147 1.146a.5.5 0 0 1-.708.708L8 9.707l-1.146 1.147a.5.5 0 0 1-.708-.708L7.293 9 6.146 7.854a.5.5 0 0 1 0-.708"/><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/></svg>
                                Chưa có kỳ thi nào trong năm học này.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ds_ky_thi as $index => $ky_thi): 
                            $count_ts = (int)($ky_thi['so_luong_thi_sinh'] ?? 0);
                        ?>
                            <tr id="exam-row-<?= $ky_thi['id'] ?>" 
                                class="hover:bg-blue-50/60 transition-colors duration-150 border-b border-slate-200 cursor-pointer group"
                                onclick="goToExam(<?= (int)$ky_thi['id'] ?>, event)"
                                title="Bấm vào để xem danh sách thí sinh">
                                
                                <td class="p-3.5 border-r border-slate-200 text-center font-medium text-slate-400 text-xs"><?= $index + 1 ?></td>
                                
                                <td class="p-3.5 border-r border-slate-200 font-bold text-[#224397]">
                                    <div class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-mortarboard-fill text-[#224397]/70 group-hover:text-[#FAB723] transition-colors shrink-0" viewBox="0 0 16 16"><path d="M8.211 2.047a.5.5 0 0 0-.422 0l-7.5 3.5a.5.5 0 0 0 .025.917l7.5 3a.5.5 0 0 0 .372 0L14 7.14V13a1 1 0 0 0-1 1v2h3v-2a1 1 0 0 0-1-1V6.739l.686-.275a.5.5 0 0 0 .025-.917z"/><path d="M4.176 9.032a.5.5 0 0 0-.656.327l-.5 1.7a.5.5 0 0 0 .294.605l4.5 1.8a.5.5 0 0 0 .372 0l4.5-1.8a.5.5 0 0 0 .294-.605l-.5-1.7a.5.5 0 0 0-.656-.327L8 10.466z"/></svg>
                                        <span class="group-hover:text-[#224397] group-hover:underline transition"><?= htmlspecialchars($ky_thi['ten_ky_thi']) ?></span>
                                    </div>
                                </td>
                                
                                <td class="p-3.5 border-r border-slate-200 text-center text-slate-600">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 rounded-md text-xs font-medium border border-slate-200">
                                        <?= $ky_thi['ngay_bat_dau'] ? date('d/m/Y', strtotime($ky_thi['ngay_bat_dau'])) : '---' ?> 
                                        ➔ 
                                        <?= $ky_thi['ngay_ket_thuc'] ? date('d/m/Y', strtotime($ky_thi['ngay_ket_thuc'])) : '---' ?>
                                    </span>
                                </td>

                                <!-- Participant Count Column -->
                                <td class="p-3.5 border-r border-slate-200 text-center">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold <?= $count_ts > 0 ? 'bg-blue-50 text-[#224397] border border-blue-200' : 'bg-slate-100 text-slate-400 border border-slate-200' ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16"><path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/></svg>
                                        <?= number_format($count_ts) ?> thí sinh
                                    </span>
                                </td>
                                
                                <td class="p-3.5 border-r border-slate-200 text-center">
                                    <label class="switch-toggle inline-block" title="Bật/Tắt công khai kỳ thi" onclick="event.stopPropagation()">
                                        <input type="checkbox" id="togglePublic_<?= $ky_thi['id'] ?>" 
                                            onchange="togglePublicStatusList(<?= $ky_thi['id'] ?>, this.checked)"
                                            <?= ($ky_thi['cong_khai'] ?? 0) ? 'checked' : '' ?>>
                                        <span class="switch-slider"></span>
                                    </label>
                                </td>
                                
                                <td class="p-3.5 text-center" onclick="event.stopPropagation()">
                                    <!-- Sleek Icon Buttons -->
                                    <div class="flex items-center justify-center gap-1.5">
                                        
                                        <!-- Thí Sinh Icon Button -->
                                        <a href="/thidua/admin/exam-participants?id=<?= $ky_thi['id'] ?>&iframe=1" 
                                           class="icon-action-btn bg-blue-50 text-[#224397] border border-blue-200/80 hover:bg-[#224397] hover:text-white hover:border-[#224397] shadow-sm" 
                                           title="Danh sách thí sinh">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" class="bi bi-people-fill" viewBox="0 0 16 16"><path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/></svg>
                                        </a>

                                        <!-- Phòng Thi Icon Button -->
                                        <a href="/thidua/admin/exam-rooms?id=<?= $ky_thi['id'] ?>&iframe=1" 
                                           class="icon-action-btn bg-emerald-50 text-emerald-700 border border-emerald-200/80 hover:bg-emerald-600 hover:text-white hover:border-emerald-600 shadow-sm" 
                                           title="Quản lý & xếp phòng thi">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" class="bi bi-door-open-fill" viewBox="0 0 16 16"><path d="M1.5 15a.5.5 0 0 0 0 1h13a.5.5 0 0 0 0-1H13V2.5A1.5 1.5 0 0 0 11.5 1H11V.5a.5.5 0 0 0-.57-.495l-7 1A.5.5 0 0 0 3 1.5V15zM11 2h.5a.5.5 0 0 1 .5.5V15h-1zm-2.5 8c-.276 0-.5-.448-.5-1s.224-1 .5-1 .5.448.5 1-.224 1-.5 1"/></svg>
                                        </a>

                                        <!-- Sửa Icon Button -->
                                        <button type="button" 
                                                class="icon-action-btn bg-amber-50 text-amber-700 border border-amber-200/80 hover:bg-[#FAB723] hover:text-slate-900 hover:border-[#FAB723] shadow-sm" 
                                                onclick='showEditModal(<?= json_encode($ky_thi) ?>)' 
                                                title="Chỉnh sửa kỳ thi">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16"><path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/><path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/></svg>
                                        </button>

                                        <!-- Xóa Icon Button -->
                                        <button type="button" 
                                                class="icon-action-btn bg-red-50 text-red-600 border border-red-200/80 hover:bg-red-600 hover:text-white hover:border-red-600 shadow-sm" 
                                                onclick='deleteExam(<?= $ky_thi['id'] ?>, <?= json_encode($ky_thi['ten_ky_thi']) ?>)' 
                                                title="Xóa kỳ thi">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16"><path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5"/></svg>
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

<!-- Modal Chuẩn Quản Lý Kỳ Thi -->
<div id="examModal" class="fixed inset-0 z-[10005] hidden items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0" onclick="closeModal('examModal')">
    <div class="bg-white rounded-xl shadow-2xl w-[520px] max-w-[92%] flex flex-col overflow-hidden border border-slate-300 transform transition-all duration-300 scale-95 translate-y-4 opacity-0 modal-content-box" onclick="event.stopPropagation()">
        
        <form id="examForm" onsubmit="event.preventDefault(); saveExam();">
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-slate-50">
                <h5 class="text-lg font-bold text-[#224397] flex items-center gap-2 m-0" id="examModalLabel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="currentColor" class="bi bi-calendar-plus text-[#FAB723]" viewBox="0 0 16 16"><path d="M8 7a.5.5 0 0 1 .5.5V9H10a.5.5 0 0 1 0 1H8.5v1.5a.5.5 0 0 1-1 0V10H6a.5.5 0 0 1 0-1h1.5V7.5A.5.5 0 0 1 8 7"/><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/></svg>
                    Tạo Kỳ Thi Mới
                </h5>
                <button type="button" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition" onclick="closeModal('examModal')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg>
                </button>
            </div>
            
            <!-- Body -->
            <div class="px-6 py-5 space-y-4 text-sm text-slate-700">
                <input type="hidden" id="examId" name="id" value="">
                
                <div>
                    <label for="tenKyThi" class="block text-sm font-semibold text-slate-700 mb-1.5">Tên Kỳ thi <span class="text-red-500">*</span></label>
                    <input type="text" class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:border-[#224397] focus:ring-1 focus:ring-[#224397] focus:outline-none shadow-sm transition-colors text-slate-800" id="tenKyThi" name="ten_ky_thi" placeholder="VD: Khảo Sát Giữa Kỳ 1 Năm Học 2026-2027" required>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="ngayBatDau" class="block text-sm font-semibold text-slate-700 mb-1.5">Ngày Bắt Đầu</label>
                        <input type="date" class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:border-[#224397] focus:ring-1 focus:ring-[#224397] focus:outline-none shadow-sm transition-colors text-slate-800" id="ngayBatDau" name="ngay_bat_dau">
                    </div>
                    <div>
                        <label for="ngayKetThuc" class="block text-sm font-semibold text-slate-700 mb-1.5">Ngày Kết Thúc</label>
                        <input type="date" class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:border-[#224397] focus:ring-1 focus:ring-[#224397] focus:outline-none shadow-sm transition-colors text-slate-800" id="ngayKetThuc" name="ngay_ket_thuc">
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-2">
                <button type="button" class="px-4 py-2 text-[13px] font-medium text-gray-600 bg-white border border-gray-300 rounded shadow-sm hover:bg-gray-50 hover:text-slate-900 transition" onclick="closeModal('examModal')">Hủy</button>
                <button type="submit" class="px-4 py-2 text-[13px] font-bold text-slate-900 bg-[#FAB723] border border-[#FAB723] rounded shadow-sm hover:bg-[#e5a61d] hover:translate-x-0.5 hover:scale-[1.02] transition-all flex items-center gap-1.5" id="saveExamButton">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check-lg" viewBox="0 0 16 16"><path d="M12.736 3.97a.75.75 0 0 1 1.02 1.084l-7.25 7.5a.75.75 0 0 1-1.08 0L2.21 8.804a.75.75 0 0 1 1.08-1.084l2.74 2.74z"/></svg>
                    <span>Lưu Kỳ Thi</span>
                </button>
            </div>
        </form>
        
    </div>
</div>

<!-- Toast Container -->
<div id="toast-container"></div>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>

<script>
    // ===== CLICKABLE ROW TO GO TO PARTICIPANTS =====
    function goToExam(id, event) {
        // Không chuyển trang nếu người dùng click vào button, link, hoặc switch toggle
        if (event.target.closest('button') || event.target.closest('a') || event.target.closest('label') || event.target.closest('input')) {
            return;
        }
        window.location.href = `/thidua/admin/exam-participants?id=${id}&iframe=1`;
    }

    // ===== MODAL ANIMATION HELPERS (UI_SYNC_STANDARDS.md) =====
    function openModal(id) {
        const modal = document.getElementById(id);
        const content = modal.querySelector('.modal-content-box');
        
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
        void modal.offsetWidth; // Force reflow
        
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

    // ===== TOAST NOTIFICATION HELPER =====
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

    const examForm = document.getElementById('examForm');
    const examIdField = document.getElementById('examId');
    const tenKyThiField = document.getElementById('tenKyThi');
    const ngayBatDauField = document.getElementById('ngayBatDau');
    const ngayKetThucField = document.getElementById('ngayKetThuc');
    const saveButton = document.getElementById('saveExamButton');

    function showCreateModal() {
        examForm.reset();
        examIdField.value = '';
        document.getElementById('examModalLabel').innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="currentColor" class="bi bi-calendar-plus text-[#FAB723]" viewBox="0 0 16 16"><path d="M8 7a.5.5 0 0 1 .5.5V9H10a.5.5 0 0 1 0 1H8.5v1.5a.5.5 0 0 1-1 0V10H6a.5.5 0 0 1 0-1h1.5V7.5A.5.5 0 0 1 8 7"/><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/></svg>
            Tạo Kỳ Thi Mới
        `;
        openModal('examModal');
    }

    function showEditModal(examData) {
        examForm.reset();
        examIdField.value = examData.id;
        tenKyThiField.value = examData.ten_ky_thi;
        ngayBatDauField.value = examData.ngay_bat_dau || '';
        ngayKetThucField.value = examData.ngay_ket_thuc || '';
        document.getElementById('examModalLabel').innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="currentColor" class="bi bi-pencil-square text-[#FAB723]" viewBox="0 0 16 16"><path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/><path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/></svg>
            Chỉnh Sửa Kỳ Thi
        `;
        openModal('examModal');
    }

    async function deleteExam(id, tenKyThi) {
        AppSwal.fire({
            title: 'Xóa kỳ thi?',
            text: `Bạn có chắc chắn muốn XÓA vĩnh viễn kỳ thi "${tenKyThi}" không? Mọi dữ liệu phòng thi và thí sinh liên quan sẽ bị xóa.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Xác nhận xóa',
            cancelButtonText: 'Hủy',
            customClass: {
                popup: 'bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.15)] border border-slate-200 py-6 px-6',
                title: 'text-red-600 font-bold text-xl mt-0',
                htmlContainer: 'text-slate-600 font-medium text-[14.5px] mt-2 mb-2',
                actions: 'flex justify-center gap-3 w-full mt-6',
                confirmButton: 'bg-red-600 text-white rounded-lg px-6 py-2 font-medium shadow-sm hover:bg-red-700 transition outline-none',
                cancelButton: 'bg-white text-slate-600 rounded-lg px-6 py-2 font-medium shadow-sm border border-slate-300 hover:bg-slate-50 transition outline-none',
                icon: 'scale-[0.85] my-2'
            },
            buttonsStyling: false
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const response = await fetch('/thidua/api/exam-crud', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'delete', id })
                    });
                    const res = await response.json();
                    if (res.success) {
                        const row = document.getElementById(`exam-row-${id}`);
                        if (row) row.remove();
                        showToast(res.message || 'Đã xóa kỳ thi thành công', 'success');
                        const tbody = document.getElementById('examTableBody');
                        if (tbody && tbody.children.length === 0) {
                            tbody.innerHTML = '<tr id="emptyRow"><td colspan="6" class="text-center text-slate-400 p-12 italic"><svg xmlns="http://www.w3.org/2000/svg" width="2.8em" height="2.8em" fill="currentColor" class="bi bi-calendar-x mx-auto mb-3 text-slate-300" viewBox="0 0 16 16"><path d="M6.146 7.146a.5.5 0 0 1 .708 0L8 8.293l1.146-1.147a.5.5 0 1 1 .708.708L8.707 9l1.147 1.146a.5.5 0 0 1-.708.708L8 9.707l-1.146 1.147a.5.5 0 0 1-.708-.708L7.293 9 6.146 7.854a.5.5 0 0 1 0-.708"/><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/></svg>Chưa có kỳ thi nào trong năm học này.</td></tr>';
                        }
                    } else {
                        showToast(res.message || 'Lỗi khi xóa', 'error');
                    }
                } catch (error) {
                    console.error(error);
                    showToast('Đã xảy ra lỗi khi kết nối máy chủ.', 'error');
                }
            }
        });
    }

    async function saveExam() {
        const examId = examIdField.value;
        const action = examId ? 'update' : 'create';
        const data = {
            action,
            id: examId,
            ten_ky_thi: tenKyThiField.value.trim(),
            ngay_bat_dau: ngayBatDauField.value || null,
            ngay_ket_thuc: ngayKetThucField.value || null
        };

        if (!data.ten_ky_thi) {
            showToast('Vui lòng nhập tên kỳ thi.', 'warning');
            return;
        }

        saveButton.disabled = true;
        saveButton.innerHTML = '<span class="inline-block w-4 h-4 border-2 border-slate-900 border-t-transparent rounded-full animate-spin"></span> <span>Đang lưu...</span>';

        try {
            const response = await fetch('/thidua/api/exam-crud', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            if (result.success) {
                closeModal('examModal');
                showToast(result.message || 'Lưu kỳ thi thành công!', 'success');
                setTimeout(() => location.reload(), 700);
            } else {
                showToast(result.message || 'Lỗi khi lưu kỳ thi.', 'error');
            }
        } catch (error) {
            console.error(error);
            showToast('Lỗi khi kết nối máy chủ.', 'error');
        } finally {
            saveButton.disabled = false;
            saveButton.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check-lg" viewBox="0 0 16 16"><path d="M12.736 3.97a.75.75 0 0 1 1.02 1.084l-7.25 7.5a.75.75 0 0 1-1.08 0L2.21 8.804a.75.75 0 0 1 1.08-1.084l2.74 2.74z"/></svg>
                <span>Lưu Kỳ Thi</span>
            `;
        }
    }

    async function togglePublicStatusList(kyThiId, isChecked) {
        const sw = document.getElementById(`togglePublic_${kyThiId}`);
        sw.disabled = true;

        try {
            const response = await fetch('/thidua/api/luu-cau-hinh-tra-cuu', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'toggle_public', ky_thi_id: kyThiId, cong_khai: isChecked })
            });
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || 'Lỗi không xác định');
            showToast(isChecked ? 'Đã công khai kỳ thi!' : 'Đã ẩn kỳ thi!', 'info');
        } catch (error) {
            console.error(error);
            showToast('Lỗi: ' + error.message, 'error');
            sw.checked = !isChecked; // Hoàn tác lại nếu có lỗi
        } finally {
            sw.disabled = false;
        }
    }
</script>
