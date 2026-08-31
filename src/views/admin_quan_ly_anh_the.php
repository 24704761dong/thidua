<?php
// File: src/views/admin_quan_ly_anh_the.php (Đã đồng bộ Premium Tailwind UI & Custom Modals)

$page_title = 'Quản Lý Thư Viện Ảnh Thẻ';
require_once __DIR__ . '/partials/admin_header.php';

$danh_sach_anh = $student_data ?? $danh_sach_anh ?? [];
?>
<style>
    body { background-color: #f4f7f9; }
    body::-webkit-scrollbar, html::-webkit-scrollbar { display: block !important; width: 8px; height: 8px; }
    body::-webkit-scrollbar-thumb, html::-webkit-scrollbar-thumb { background: rgba(34,67,151,0.3); border-radius: 4px; }
    body::-webkit-scrollbar-track, html::-webkit-scrollbar-track { background: transparent; }
</style>

<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- HEADER -->
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6 border-b border-slate-200 pb-4">
        <h1 class="text-xl font-bold text-[#224397] uppercase flex items-center gap-2 m-0">
            <svg xmlns="http://www.w3.org/2000/svg" width="1.3em" height="1.3em" fill="currentColor" class="bi bi-images text-[#FAB723]" viewBox="0 0 16 16"><path d="M4.502 9a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/><path d="M14.002 13a2 2 0 0 1-2 2h-10a2 2 0 0 1-2-2V5A2 2 0 0 1 2 3a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v8a2 2 0 0 1-1.998 2M14 2H4a1 1 0 0 0-1 1h9.002a2 2 0 0 1 2 2v7A1 1 0 0 0 15 11V3a1 1 0 0 0-1-1M2.002 4a1 1 0 0 0-1 1v8l2.646-2.354a.5.5 0 0 1 .63-.062l2.66 1.773 3.71-3.71a.5.5 0 0 1 .577-.094l1.777 1.947V5a1 1 0 0 0-1-1z"/></svg>
            Quản Lý Thư Viện Ảnh Thẻ
        </h1>
        <div class="flex items-center gap-2">
            <a href="/thidua/admin/the-hoc-sinh?iframe=1" class="px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 hover:text-[#224397] transition-all duration-200 font-bold flex items-center gap-1.5 text-xs shadow-sm whitespace-nowrap text-decoration-none h-[36px]">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/></svg> 
                Quay lại Hub
            </a>

            <!-- DROPDOWN THAO TÁC -->
            <div class="relative inline-block text-left group">
                <button type="button" class="px-4 py-1.5 bg-[#224397] hover:bg-[#224397]/90 text-white rounded-lg font-bold flex items-center gap-1.5 text-xs shadow-md hover:shadow-lg transition-all duration-200 whitespace-nowrap h-[36px]">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-tools" viewBox="0 0 16 16"><path d="M1 0 0 1l2.2 3.081a1 1 0 0 0 .815.419h.07a1 1 0 0 1 .708.293l2.675 2.675-2.617 2.654A3.003 3.003 0 0 0 0 13a3 3 0 1 0 5.878-.851l2.654-2.617.968.968-.305.914a1 1 0 0 0 .242 1.023l3.27 3.27a.997.997 0 0 0 1.414 0l1.586-1.586a.997.997 0 0 0 0-1.414l-3.27-3.27a1 1 0 0 0-1.023-.242L10.5 9.5l-.96-.96 2.68-2.643A3.005 3.005 0 0 0 16 3q0-.405-.102-.777l-2.14 2.141L12 4l-.364-1.757L13.777.102a3 3 0 0 0-3.675 3.68L7.462 6.46 4.793 3.793a1 1 0 0 1-.293-.707v-.071a1 1 0 0 0-.419-.814zm9.646 10.646a.5.5 0 0 1 .708 0l2.914 2.915a.5.5 0 0 1-.707.707l-2.915-2.914a.5.5 0 0 1 0-.708M3 11l.471.242.529.026.287.445.445.287.026.529L5 13l-.242.471-.026.529-.445.287-.287.445-.529.026L3 15l-.471-.242L2 14.732l-.287-.445L1.268 14l-.026-.529L1 13l.242-.471.026-.529.445-.287.287-.445.529-.026z"/></svg> 
                    Thao tác 
                    <svg xmlns="http://www.w3.org/2000/svg" width="0.8em" height="0.8em" fill="currentColor" class="bi bi-chevron-down" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/></svg>
                </button>
                <ul class="absolute right-0 mt-1 w-48 bg-white rounded shadow-lg border border-slate-100 focus:outline-none opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-[100] transform origin-top-right scale-95 group-hover:scale-100 py-1 list-none pl-0">
                    <li>
                        <a href="javascript:void(0)" onclick="openModal('uploadModal')" class="flex items-center gap-2 px-4 py-2 text-xs font-medium text-slate-700 hover:bg-blue-50 hover:text-[#224397] text-decoration-none">
                            <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-upload text-[#224397]" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/><path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708z"/></svg>
                            Tải ảnh lên hàng loạt
                        </a>
                    </li>
                    <li><hr class="border-t border-slate-100 my-1"></li>
                    <li>
                        <a href="javascript:void(0)" id="autoRenameBtn" class="flex items-center gap-2 px-4 py-2 text-xs font-medium text-slate-700 hover:bg-blue-50 hover:text-[#224397] text-decoration-none">
                            <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-magic text-[#FAB723]" viewBox="0 0 16 16"><path d="M9.5 2.672a.5.5 0 1 0 1 0V.843a.5.5 0 0 0-1 0zm4.5.035A.5.5 0 0 0 13.293 2L12 3.293a.5.5 0 1 0 .707.707zM7.293 4A.5.5 0 1 0 8 3.293L6.707 2A.5.5 0 0 0 6 2.707zm-.621 2.5a.5.5 0 1 0 0-1H4.843a.5.5 0 1 0 0 1zm8.485 0a.5.5 0 1 0 0-1h-1.829a.5.5 0 0 0 0 1zM13.293 10A.5.5 0 1 0 14 9.293L12.707 8a.5.5 0 1 0-.707.707zM9.5 11.157a.5.5 0 0 0 1 0V9.328a.5.5 0 0 0-1 0zm1.854-5.097a.5.5 0 0 0 0-.706l-.708-.708a.5.5 0 0 0-.707 0L8.646 5.94a.5.5 0 0 0 0 .707l.708.708a.5.5 0 0 0 .707 0l1.293-1.293Zm-3 3a.5.5 0 0 0 0-.706l-.708-.708a.5.5 0 0 0-.707 0L.646 13.94a.5.5 0 0 0 0 .707l.708.708a.5.5 0 0 0 .707 0z"/></svg>
                            Đổi tên tự động
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- KHUNG BỘ LỌC -->
    <div class="mb-6">
        <button id="toggleFilterBtn" class="px-4 py-1.5 bg-white border border-slate-300 hover:border-[#224397] text-slate-700 hover:text-[#224397] rounded-lg font-bold flex items-center gap-1.5 text-xs shadow-sm transition-all duration-200" type="button">
            <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-funnel" viewBox="0 0 16 16"><path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5zm1 .5v1.308l4.372 4.858A.5.5 0 0 1 7 8.5v5.306l2-.666V8.5a.5.5 0 0 1 .128-.334L13.5 3.308V2z"/></svg>
            Bộ lọc hiển thị
        </button>
        <div id="filterBox" class="mt-3 bg-white border border-slate-200 rounded-xl p-4 shadow-sm hidden">
            <div class="flex flex-wrap gap-4 items-center">
                <div>
                    <label class="block text-xs font-bold text-[#224397] mb-1 uppercase tracking-wide">Lọc theo niên khóa</label>
                    <select id="filter-nk" class="block w-44 rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-3 py-1.5 text-sm font-medium text-slate-700 bg-white">
                        <option value="all">Tất cả niên khóa</option>
                        <?php foreach ($danh_sach_nien_khoa ?? [] as $nk): ?>
                            <option value="<?= htmlspecialchars($nk) ?>"><?= htmlspecialchars($nk) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#224397] mb-1 uppercase tracking-wide">Lọc theo lớp</label>
                    <select id="filter-lop" class="block w-44 rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-3 py-1.5 text-sm font-medium text-slate-700 bg-white">
                        <option value="all">Tất cả lớp</option>
                        <?php foreach ($lop_list ?? [] as $lop): ?>
                            <option value="<?= htmlspecialchars($lop['ten_lop']) ?>"><?= htmlspecialchars($lop['ten_lop']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#224397] mb-1 uppercase tracking-wide">Tình trạng gán ảnh</label>
                    <select id="filter-type" class="block w-48 rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-3 py-1.5 text-sm font-medium text-slate-700 bg-white">
                        <option value="all">Tất cả ảnh</option>
                        <option value="assigned">Đã gán cho học sinh</option>
                        <option value="unassigned">Chưa gán (Tự do)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#224397] mb-1 uppercase tracking-wide">Nguồn lưu trữ</label>
                    <select id="filter-source" class="block w-40 rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-3 py-1.5 text-sm font-medium text-slate-700 bg-white">
                        <option value="all">Tất cả</option>
                        <option value="local">Tại máy chủ</option>
                        <option value="cloud">Trên Cloud</option>
                <div>
                    <label class="block text-xs font-bold text-[#224397] mb-1 uppercase tracking-wide">Trạng thái học tập</label>
                    <select id="filter-trangthai" class="block w-44 rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-3 py-1.5 text-sm font-medium text-slate-700 bg-white">
                        <option value="all">Tất cả trạng thái</option>
                        <option value="dang_hoc">Đang học</option>
                        <option value="da_tot_nghiep">Đã tốt nghiệp</option>
                        <option value="nghi_hoc">Nghỉ học</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- GALLERY GRID -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6 image-gallery">
        <?php foreach ($danh_sach_anh as $data): ?>
            <?php if (!empty($data['anh_the'])): ?>
                <?php $isAssigned = ($data['ten'] !== ''); ?>
                <div class="gallery-item bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 flex flex-col" 
                     data-nk="<?= htmlspecialchars($data['nien_khoa'] ?? '') ?>"
                     data-lop="<?= htmlspecialchars($data['ten_lop'] ?? '') ?>" 
                     data-type="<?= $isAssigned ? 'assigned' : 'unassigned' ?>"
                     data-source="<?= !empty($data['is_cloud']) ? 'cloud' : 'local' ?>"
                     data-trangthai="<?= htmlspecialchars($data['trang_thai_hoc_tap'] ?? 'dang_hoc') ?>">
                    
                    <div class="relative w-full bg-slate-100 border-b border-slate-200 aspect-[3/4] overflow-hidden flex items-center justify-center">
                        <img src="<?= htmlspecialchars(get_student_avatar_url($data['anh_the'] ?? '', $data['anh_the_driver'] ?? 'local', $data['anh_the_cloud_key'] ?? null)) ?>" alt="Ảnh thẻ" class="w-full h-full object-cover" loading="lazy" onerror="this.src='/thidua/public/assets/img/anhthegoc.JPG'">
                        
                        <?php if (!empty($data['is_cloud'])): ?>
                            <span class="absolute top-2 left-2 z-10 px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-300 shadow-sm flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="currentColor" class="bi bi-cloud-check-fill" viewBox="0 0 16 16"><path d="M8 2a5.53 5.53 0 0 0-3.594 1.342c-.766.66-1.321 1.52-1.464 2.383C1.266 6.095 0 7.555 0 9.318 0 11.366 1.708 13 3.781 13h8.906C14.502 13 16 11.57 16 9.773c0-1.636-1.242-2.969-2.834-3.194C12.923 3.999 10.69 2 8 2m2.354 4.854-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7 8.793l2.646-2.647a.5.5 0 0 1 .708.708"/></svg>
                                CLOUD
                            </span>
                        <?php else: ?>
                            <span class="absolute top-2 left-2 z-10 px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-300 shadow-sm flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="currentColor" class="bi bi-hdd-fill" viewBox="0 0 16 16"><path d="M0 10a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v1a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1m2 0a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1M.91 7.204A2.993 2.993 0 0 1 2 7h12c.384 0 .752.072 1.09.204l-1.867-3.422A1.5 1.5 0 0 0 11.906 3H4.094a1.5 1.5 0 0 0-1.317.782z"/></svg>
                                LOCAL
                            </span>
                        <?php endif; ?>

                        <?php if ($isAssigned): ?>
                            <span class="absolute top-2 right-2 z-10 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-sm">ĐÃ GÁN</span>
                        <?php else: ?>
                            <span class="absolute top-2 right-2 z-10 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300 shadow-sm">CHƯA GÁN</span>
                        <?php endif; ?>
                    </div>

                    <div class="p-3 bg-white flex-1 flex flex-col justify-between">
                        <div>
                            <p class="text-xs font-bold text-[#224397] truncate mb-1" title="<?= htmlspecialchars($data['anh_the']) ?>"><?= htmlspecialchars($data['anh_the']) ?></p>
                            <?php if ($isAssigned): ?>
                                <p class="text-xs font-bold text-slate-800 truncate mb-1" title="<?= htmlspecialchars($data['ho_dem'] . ' ' . $data['ten']) ?>"><?= htmlspecialchars($data['ho_dem'] . ' ' . $data['ten']) ?></p>
                                <div class="text-[11px] font-medium text-slate-500 space-y-0.5 mb-2">
                                    <p class="truncate m-0">Lớp: <?= htmlspecialchars($data['ten_lop']) ?></p>
                                    <p class="truncate m-0 font-mono">CCCD: <?= htmlspecialchars($data['ma_hoc_sinh']) ?></p>
                                    <p class="truncate m-0">Niên khóa: <?= htmlspecialchars($data['nien_khoa'] ?? 'Chưa rõ') ?></p>
                                </div>
                            <?php else: ?>
                                <p class="text-xs font-medium text-amber-600 truncate mb-2 italic">Chưa gán học sinh</p>
                            <?php endif; ?>
                        </div>

                        <!-- ACTIONS -->
                        <div class="flex items-center justify-center gap-1.5 pt-2 border-t border-slate-100">
                            <?php if (!$isAssigned): ?>
                                <button type="button" class="flex-1 py-1 px-2 bg-emerald-50 hover:bg-emerald-600 text-emerald-700 hover:text-white border border-emerald-200 hover:border-emerald-600 rounded text-xs font-bold transition-all duration-200 assign-btn flex items-center justify-center shadow-sm" data-filename="<?= htmlspecialchars($data['anh_the']) ?>" title="Gán cho học sinh">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="currentColor" class="bi bi-person-plus" viewBox="0 0 16 16"><path d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H1s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C9.516 10.68 8.289 10 6 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/><path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5"/></svg>
                                </button>
                            <?php endif; ?>
                            <button type="button" class="flex-1 py-1 px-2 bg-slate-50 hover:bg-slate-700 text-slate-700 hover:text-white border border-slate-200 hover:border-slate-700 rounded text-xs font-bold transition-all duration-200 rename-btn flex items-center justify-center shadow-sm" data-filename="<?= htmlspecialchars($data['anh_the']) ?>" title="Đổi tên file">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16"><path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/><path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/></svg>
                            </button>
                            <button type="button" class="flex-1 py-1 px-2 bg-red-50 hover:bg-red-600 text-red-600 hover:text-white border border-red-200 hover:border-red-600 rounded text-xs font-bold transition-all duration-200 delete-btn flex items-center justify-center shadow-sm" data-filename="<?= htmlspecialchars($data['anh_the']) ?>" title="Xóa ảnh">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16"><path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5M8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5m3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    
    <!-- PAGINATION CONTROLS -->
    <div id="paginationControls" class="flex justify-center items-center gap-2 mt-8 mb-4">
        <!-- Pagination buttons will be rendered here by JS -->
    </div>
</div>

<!-- HỘP THOẠI CẢNH BÁO TÙY CHỈNH (CUSTOM ALERT MODAL) -->
<div id="customAlertModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[200] flex items-center justify-center opacity-0 pointer-events-none transition-all duration-200">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl p-6 border border-slate-200 transform scale-95 transition-all duration-200">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0 font-bold">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.5em" height="1.5em" fill="currentColor" class="bi bi-exclamation-triangle-fill" viewBox="0 0 16 16"><path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/></svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800 m-0">Thông Báo</h3>
        </div>
        <p id="customAlertMsg" class="text-sm text-slate-600 mb-6 leading-relaxed"></p>
        <div class="flex justify-end">
            <button onclick="closeCustomAlert()" class="px-6 py-2.5 bg-[#224397] hover:bg-[#224397]/90 text-white font-bold rounded-xl text-sm shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all">
                Đã hiểu
            </button>
        </div>
    </div>
</div>

<!-- HỘP THOẠI XÁC NHẬN TÙY CHỈNH (CUSTOM CONFIRM MODAL) -->
<div id="customConfirmModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[200] flex items-center justify-center opacity-0 pointer-events-none transition-all duration-200">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl p-6 border border-slate-200 transform scale-95 transition-all duration-200">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-full bg-blue-100 text-[#224397] flex items-center justify-center flex-shrink-0 font-bold">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.5em" height="1.5em" fill="currentColor" class="bi bi-question-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.496 6.033h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286a.237.237 0 0 0 .241.247m2.325 6.443c.61 0 1.029-.394 1.029-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94 0 .533.425.927 1.01.927z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800 m-0">Xác Nhận</h3>
        </div>
        <p id="customConfirmMsg" class="text-sm text-slate-600 mb-6 leading-relaxed whitespace-pre-line"></p>
        <div class="flex justify-end gap-3">
            <button onclick="closeCustomConfirm(false)" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-sm transition-colors">
                Hủy bỏ
            </button>
            <button onclick="closeCustomConfirm(true)" class="px-6 py-2.5 bg-[#224397] hover:bg-[#224397]/90 text-white font-bold rounded-xl text-sm shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all">
                Đồng ý
            </button>
        </div>
    </div>
</div>

<!-- MODAL ĐỔI TÊN -->
<div id="renameModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[100] flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-md overflow-hidden animate-in zoom-in-95 duration-200">
        <div class="flex items-center justify-between p-5 border-b border-slate-200 bg-slate-50">
            <h5 class="text-base font-bold text-[#224397] flex items-center gap-2 m-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.3em" height="1.3em" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16"><path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/><path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/></svg>
                Đổi tên file ảnh
            </h5>
            <button type="button" onclick="closeModal('renameModal')" class="text-slate-400 hover:text-slate-600 font-bold p-1">&times;</button>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-bold text-[#224397] mb-2 uppercase tracking-wide">Tên file mới</label>
                <input type="text" class="block w-full rounded-lg border border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 p-2.5 text-sm font-medium text-slate-800" id="newFilename" placeholder="Nhập tên file mới (vd: DSC_1234.jpg)">
                <input type="hidden" id="oldFilename">
            </div>
        </div>
        <div class="flex items-center justify-end gap-3 p-5 border-t border-slate-100 bg-slate-50">
            <button type="button" onclick="closeModal('renameModal')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-bold text-sm transition-colors">Hủy</button>
            <button type="button" class="px-5 py-2 bg-[#224397] hover:bg-[#224397]/90 text-white rounded-lg font-bold text-sm shadow-md hover:shadow-lg transition-all" id="confirmRename">Đổi tên</button>
        </div>
    </div>
</div>

<!-- MODAL GÁN ẢNH THẺ -->
<div id="assignModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[100] flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-2xl overflow-hidden animate-in zoom-in-95 duration-200 flex flex-col max-h-[90vh]">
        <div class="flex items-center justify-between p-5 border-b border-slate-200 bg-slate-50">
            <h5 class="text-base font-bold text-[#224397] flex items-center gap-2 m-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.3em" height="1.3em" fill="currentColor" class="bi bi-person-plus text-[#FAB723]" viewBox="0 0 16 16"><path d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H1s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C9.516 10.68 8.289 10 6 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/><path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5"/></svg>
                Gán ảnh thẻ cho học sinh
            </h5>
            <button type="button" onclick="closeModal('assignModal')" class="text-slate-400 hover:text-slate-600 font-bold p-1">&times;</button>
        </div>
        <div class="p-6 flex-1 overflow-y-auto space-y-6">
            <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="2em" height="2em" fill="currentColor" class="bi bi-image text-[#224397]" viewBox="0 0 16 16"><path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0"/><path d="M2.002 2A2 2 0 0 0 .002 4v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V4a1 1 0 0 1 1-1z"/></svg>
                <div>
                    <label class="block text-xs font-bold text-[#224397] uppercase tracking-wide">Đang gán file ảnh</label>
                    <div class="text-base font-bold text-slate-800" id="assignFilename"></div>
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-[#224397] mb-2 uppercase tracking-wide">Tìm kiếm học sinh</label>
                <input type="text" class="block w-full rounded-lg border border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 p-2.5 text-sm font-medium text-slate-800" id="studentSearchInput" placeholder="Nhập tên, mã học sinh hoặc lớp để tìm nhanh...">
                <p class="text-[11px] text-slate-500 mt-1.5 mb-0">Chỉ hiển thị tối đa 15 kết quả phù hợp nhất.</p>
            </div>
            <div id="studentList" class="space-y-2 max-h-[300px] overflow-y-auto pr-1"></div>
        </div>
        <div class="flex items-center justify-end p-5 border-t border-slate-100 bg-slate-50">
            <button type="button" onclick="closeModal('assignModal')" class="px-5 py-2 bg-slate-600 hover:bg-slate-700 text-white rounded-lg font-bold text-sm shadow-md transition-colors">Đóng</button>
        </div>
    </div>
</div>

<!-- MODAL UPLOAD ẢNH -->
<div id="uploadModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[100] flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-md overflow-hidden animate-in zoom-in-95 duration-200">
        <div class="flex items-center justify-between p-5 border-b border-slate-200 bg-slate-50">
            <h5 class="text-base font-bold text-[#224397] flex items-center gap-2 m-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.3em" height="1.3em" fill="currentColor" class="bi bi-upload" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/><path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708z"/></svg>
                Tải ảnh lên hàng loạt
            </h5>
            <button type="button" onclick="closeModal('uploadModal')" class="text-slate-400 hover:text-slate-600 font-bold p-1">&times;</button>
        </div>
        <form action="/thidua/api/upload-anh-the" method="POST" enctype="multipart/form-data" id="uploadForm" class="p-6 space-y-6 m-0">
            <div>
                <label class="block text-xs font-bold text-[#224397] mb-2 uppercase tracking-wide">Chọn file ảnh (.JPG, .PNG)</label>
                <input type="file" class="block w-full rounded-lg border border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 p-2 text-sm" name="anh_the_files[]" multiple required accept="image/jpeg,image/png">
            </div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeModal('uploadModal')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-bold text-sm transition-colors">Hủy</button>
                <button type="submit" class="px-5 py-2 bg-[#224397] hover:bg-[#224397]/90 text-white rounded-lg font-bold text-sm shadow-md hover:shadow-lg transition-all">Tải lên</button>
            </div>
        </form>
    </div>
</div>

<script>
const STUDENT_DIRECTORY = <?php
    $student_directory = array_map(function($student) {
        return [
            'id' => (int)($student['id'] ?? 0),
            'ma_hoc_sinh' => $student['ma_hoc_sinh'] ?? '',
            'ho_ten' => trim(($student['ho_dem'] ?? '') . ' ' . ($student['ten'] ?? '')),
            'ten_lop' => $student['ten_lop'] ?? '',
            'anh_the' => $student['anh_the'] ?? null
        ];
    }, $all_students ?? []);
    echo json_encode($student_directory, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>;

// CUSTOM MODAL HELPER FUNCTIONS
let confirmCallback = null;

function showCustomAlert(msg, onClosed = null) {
    const modal = document.getElementById('customAlertModal');
    const msgEl = document.getElementById('customAlertMsg');
    if(msgEl && modal) {
        msgEl.textContent = msg;
        modal.classList.remove('opacity-0', 'pointer-events-none');
        const box = modal.querySelector('.bg-white');
        box.classList.remove('scale-95');
        box.classList.add('scale-100');
        modal.onClosedCallback = onClosed;
    } else {
        alert(msg);
        if(onClosed) onClosed();
    }
}

function closeCustomAlert() {
    const modal = document.getElementById('customAlertModal');
    if(modal) {
        modal.classList.add('opacity-0', 'pointer-events-none');
        const box = modal.querySelector('.bg-white');
        box.classList.remove('scale-100');
        box.classList.add('scale-95');
        if (modal.onClosedCallback) modal.onClosedCallback();
    }
}

function showCustomConfirm(msg, callback) {
    const modal = document.getElementById('customConfirmModal');
    const msgEl = document.getElementById('customConfirmMsg');
    if(msgEl && modal) {
        msgEl.textContent = msg;
        confirmCallback = callback;
        modal.classList.remove('opacity-0', 'pointer-events-none');
        const box = modal.querySelector('.bg-white');
        box.classList.remove('scale-95');
        box.classList.add('scale-100');
    } else {
        callback(confirm(msg));
    }
}

function closeCustomConfirm(result) {
    const modal = document.getElementById('customConfirmModal');
    if(modal) {
        modal.classList.add('opacity-0', 'pointer-events-none');
        const box = modal.querySelector('.bg-white');
        box.classList.remove('scale-100');
        box.classList.add('scale-95');
        if(confirmCallback) confirmCallback(result);
    }
}

function openModal(id) {
    const el = document.getElementById(id);
    if(el) el.classList.remove('hidden');
}

function closeModal(id) {
    const el = document.getElementById(id);
    if(el) el.classList.add('hidden');
}

document.addEventListener('DOMContentLoaded', function() {
    const toggleFilterBtn = document.getElementById('toggleFilterBtn');
    const filterBox = document.getElementById('filterBox');
    if(toggleFilterBtn && filterBox) {
        toggleFilterBtn.addEventListener('click', () => {
            filterBox.classList.toggle('hidden');
        });
    }

    const normalizeText = (value) => {
        return (value || '').toString()
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
    };

    const assignFilenameEl = document.getElementById('assignFilename');
    const studentSearchInput = document.getElementById('studentSearchInput');
    const studentList = document.getElementById('studentList');
    let currentFilename = null;

    const buildSearchIndex = STUDENT_DIRECTORY.map(student => ({
        ...student,
        search: normalizeText([student.ma_hoc_sinh, student.ho_ten, student.ten_lop].join(' '))
    }));

    const renderStudentList = (keyword = '') => {
        const normalizedKeyword = normalizeText(keyword);
        const matches = buildSearchIndex
            .filter(student => {
                if (!normalizedKeyword) return true;
                return student.search.includes(normalizedKeyword);
            })
            .sort((a, b) => {
                const aHasPhoto = Boolean(a.anh_the);
                const bHasPhoto = Boolean(b.anh_the);
                if (aHasPhoto === bHasPhoto) {
                    return a.ho_ten.localeCompare(b.ho_ten, 'vi');
                }
                return aHasPhoto ? 1 : -1;
            })
            .slice(0, 15);

        if (matches.length === 0) {
            studentList.innerHTML = '<div class="text-center text-slate-500 py-6 font-medium text-sm">Không tìm thấy học sinh phù hợp.</div>';
            return;
        }

        studentList.innerHTML = matches.map(student => {
            const statusBadge = student.anh_the 
                ? `<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200 mt-2">Đang có ảnh: ${student.anh_the}</span>` 
                : `<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 mt-2">Chưa có ảnh</span>`;

            return `
                <button type="button" class="w-full text-left bg-white border border-slate-200 hover:border-[#224397] hover:bg-indigo-50/30 rounded-xl p-4 shadow-sm transition-all duration-200 select-student flex flex-col" data-id="${student.id}">
                    <div class="flex items-center justify-between w-full mb-1">
                        <strong class="text-slate-800 font-bold text-sm">${student.ho_ten || 'Không rõ tên'}</strong>
                        <span class="text-xs font-bold text-[#224397] bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">${student.ten_lop || ''}</span>
                    </div>
                    ${student.ma_hoc_sinh ? `<span class="text-xs text-slate-500 font-mono">Mã HS: ${student.ma_hoc_sinh}</span>` : ''}
                    ${statusBadge}
                </button>
            `;
        }).join('');
    };

    const handleAssign = (studentId) => {
        if (!currentFilename) return;
        const student = STUDENT_DIRECTORY.find(entry => entry.id === studentId);
        if (!student) {
            showCustomAlert('Không tìm thấy thông tin học sinh.');
            return;
        }

        let confirmMessage = `Xác nhận gán ảnh "${currentFilename}" cho học sinh ${student.ho_ten} (${student.ten_lop}).`;
        if (student.anh_the && student.anh_the !== currentFilename) {
            confirmMessage += `\nHọc sinh hiện đang có ảnh "${student.anh_the}". Ảnh cũ sẽ được thay thế.`;
        }
        
        showCustomConfirm(confirmMessage, (agreed) => {
            if (!agreed) return;
            
            fetch('/thidua/api/assign-anh-the', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ student_id: studentId, filename: currentFilename })
            })
            .then(res => res.json())
            .then(data => {
                showCustomAlert(data.message || 'Đã xử lý yêu cầu.', () => {
                    if (data.success) window.location.reload();
                });
            })
            .catch(() => {
                showCustomAlert('Không thể gán ảnh. Vui lòng thử lại.');
            });
        });
    };

    studentList.addEventListener('click', (event) => {
        const button = event.target.closest('.select-student');
        if (!button) return;
        const studentId = parseInt(button.dataset.id, 10);
        if (Number.isNaN(studentId)) return;
        handleAssign(studentId);
    });

    studentSearchInput.addEventListener('input', (event) => {
        renderStudentList(event.target.value);
    });

    document.querySelectorAll('.assign-btn').forEach(button => {
        button.addEventListener('click', () => {
            currentFilename = button.dataset.filename || '';
            assignFilenameEl.textContent = currentFilename;
            studentSearchInput.value = '';
            renderStudentList('');
            openModal('assignModal');
            setTimeout(() => studentSearchInput.focus(), 150);
        });
    });

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const filename = this.dataset.filename;
            showCustomConfirm(`Bạn có chắc chắn muốn xóa ảnh "${filename}"?`, (agreed) => {
                if(!agreed) return;
                fetch('/thidua/api/delete-anh-the', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ filename })
                })
                .then(res => res.json())
                .then(data => {
                    showCustomAlert(data.message, () => {
                        if (data.success) location.reload();
                    });
                });
            });
        });
    });

    document.querySelectorAll('.rename-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('oldFilename').value = this.dataset.filename;
            document.getElementById('newFilename').value = this.dataset.filename;
            openModal('renameModal');
        });
    });

    const confirmRename = document.getElementById('confirmRename');
    if(confirmRename) {
        confirmRename.addEventListener('click', function() {
            const oldName = document.getElementById('oldFilename').value;
            const newName = document.getElementById('newFilename').value;
            fetch('/thidua/api/rename-anh-the', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ old_filename: oldName, new_filename: newName })
            })
            .then(res => res.json())
            .then(data => {
                showCustomAlert(data.message, () => {
                    if (data.success) location.reload();
                });
            });
        });
    }

    const autoRenameBtn = document.getElementById('autoRenameBtn');
    if(autoRenameBtn) {
        autoRenameBtn.addEventListener('click', function() {
            showCustomConfirm('Bạn có chắc chắn muốn đổi tên tự động tất cả ảnh theo cấu trúc Niên khóa_Số CCCD_Họ và tên không?', (agreed) => {
                if(!agreed) return;
                fetch('/thidua/api/auto-rename-anh-the', { method: 'POST' })
                .then(res => res.json())
                .then(data => {
                    showCustomAlert(data.message, () => {
                        if (data.success) location.reload();
                    });
                });
            });
        });
    }

    const filterNk = document.getElementById('filter-nk');
    const filterLop = document.getElementById('filter-lop');
    const filterType = document.getElementById('filter-type');
    const filterSource = document.getElementById('filter-source');
    const filterTrangThai = document.getElementById('filter-trangthai');
    const items = document.querySelectorAll('.gallery-item');
    const paginationControls = document.getElementById('paginationControls');
    
    let currentPage = 1;
    const itemsPerPage = 20;

    function renderPagination(totalPages) {
        if (!paginationControls) return;
        paginationControls.innerHTML = '';
        if (totalPages <= 1) return;

        const btnPrev = document.createElement('button');
        btnPrev.className = 'px-3 py-1 bg-white border border-slate-300 rounded hover:bg-slate-50 text-sm font-medium text-slate-600 disabled:opacity-50 disabled:cursor-not-allowed';
        btnPrev.textContent = 'Trước';
        btnPrev.disabled = currentPage === 1;
        btnPrev.onclick = () => { currentPage--; applyFilters(); };
        paginationControls.appendChild(btnPrev);

        const pageInfo = document.createElement('span');
        pageInfo.className = 'px-4 py-1 text-sm font-bold text-[#224397]';
        pageInfo.textContent = `Trang ${currentPage} / ${totalPages}`;
        paginationControls.appendChild(pageInfo);

        const btnNext = document.createElement('button');
        btnNext.className = 'px-3 py-1 bg-white border border-slate-300 rounded hover:bg-slate-50 text-sm font-medium text-slate-600 disabled:opacity-50 disabled:cursor-not-allowed';
        btnNext.textContent = 'Sau';
        btnNext.disabled = currentPage === totalPages;
        btnNext.onclick = () => { currentPage++; applyFilters(); };
        paginationControls.appendChild(btnNext);
    }

    function applyFilters() {
        const nkValue = filterNk ? filterNk.value : 'all';
        const lopValue = filterLop ? filterLop.value : 'all';
        const typeValue = filterType ? filterType.value : 'all';
        const sourceValue = filterSource ? filterSource.value : 'all';
        const trangThaiValue = filterTrangThai ? filterTrangThai.value : 'all';

        let visibleItems = [];
        items.forEach(item => {
            const matchNk = (nkValue === 'all' || item.dataset.nk === nkValue);
            const matchLop = (lopValue === 'all' || item.dataset.lop === lopValue);
            const matchType = (typeValue === 'all' || item.dataset.type === typeValue);
            const matchSource = (sourceValue === 'all' || item.dataset.source === sourceValue);
            const matchTrangThai = (trangThaiValue === 'all' || item.dataset.trangthai === trangThaiValue);
            
            if (matchNk && matchLop && matchType && matchSource && matchTrangThai) {
                visibleItems.push(item);
            }
            item.style.display = 'none';
        });

        const totalPages = Math.ceil(visibleItems.length / itemsPerPage);
        if (currentPage > totalPages) currentPage = totalPages || 1;
        
        const start = (currentPage - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        visibleItems.slice(start, end).forEach(item => {
            item.style.display = '';
        });
        
        renderPagination(totalPages);
    }

    if(filterNk) filterNk.addEventListener('change', () => { currentPage = 1; applyFilters(); });
    if(filterLop) filterLop.addEventListener('change', () => { currentPage = 1; applyFilters(); });
    if(filterType) filterType.addEventListener('change', () => { currentPage = 1; applyFilters(); });
    if(filterSource) filterSource.addEventListener('change', () => { currentPage = 1; applyFilters(); });
    if(filterTrangThai) filterTrangThai.addEventListener('change', () => { currentPage = 1; applyFilters(); });
    
    // Initial render
    applyFilters();
});
</script>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>
