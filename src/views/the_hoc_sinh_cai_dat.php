<?php
// File: src/views/the_hoc_sinh_cai_dat.php (Đã đồng bộ Premium Tailwind UI & Custom Modals)

$page_title = 'Thiết Kế Mẫu Thẻ Học Sinh';
require_once __DIR__ . '/partials/admin_header.php';

// Các biến đã được nạp từ controller
$mau_the_dang_chon = $mau_the_dang_chon ?? null;
$mau_the_id = $mau_the_dang_chon['id'] ?? null;
$danh_sach_mau_the = $danh_sach_mau_the ?? [];
$mau_the_json = $mau_the_dang_chon['cau_hinh_json'] ?? '{}';
?>

<style>
    body { background-color: #f4f7f9; }
    body::-webkit-scrollbar, html::-webkit-scrollbar { display: block !important; width: 8px; height: 8px; }
    body::-webkit-scrollbar-thumb, html::-webkit-scrollbar-thumb { background: rgba(34,67,151,0.3); border-radius: 4px; }
    body::-webkit-scrollbar-track, html::-webkit-scrollbar-track { background: transparent; }
    
    .rule-row {
        display: flex;
        gap: 8px;
        align-items: center;
        margin-bottom: 8px;
    }
    .rule-row input { text-align: center; }

    #card-designer { display: flex; flex-wrap: wrap; gap: 24px; min-height: 75vh; }
    #card-canvas-wrapper {
        flex-grow: 1; background-color: #e2e8f0; border: 2px dashed #cbd5e1;
        border-radius: 16px; display: flex; justify-content: center; 
        align-items: center; overflow: auto; padding: 2rem; min-width: 320px; min-height: 400px;
    }
    #card-canvas {
        position: relative; width: 450px; height: 284px;
        background-size: cover; background-position: center;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.2); flex-shrink: 0;
        border-radius: 8px; overflow: hidden; background-color: #ffffff;
    }
    .draggable-element {
        position: absolute; cursor: move; border: 1px solid rgba(34, 67, 151, 0.4);
        padding: 2px 6px; user-select: none; white-space: normal;
        background-color: rgba(255, 255, 255, 0.7);
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .draggable-element.selected {
        border: 2px solid #224397; z-index: 1000;
        background-color: rgba(34, 67, 151, 0.15);
        box-shadow: 0 4px 12px rgba(34,67,151,0.25);
    }
    .draggable-element .resize-handle {
        position: absolute;
        width: 8px;
        height: 20px;
        background-color: #ffffff;
        border: 2px solid #224397;
        border-radius: 4px;
        top: 50%;
        transform: translateY(-50%);
        cursor: ew-resize;
        z-index: 1002;
        display: none;
    }
    .draggable-element.selected .resize-handle {
        display: block;
    }
    .resize-handle.left { left: -5px; }
    .resize-handle.right { right: -5px; }
    .draggable-element:hover { border-style: dashed; }
    .draggable-element[contenteditable="true"] {
        cursor: text; border: 2px solid #FAB723;
        background-color: #ffffff; z-index: 1001;
    }
    #controls { width: 360px; flex-shrink: 0; display: flex; flex-direction: column; }
</style>

<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <?php if (!empty($card_template_warning)): ?>
        <div class="p-4 mb-6 rounded-xl border bg-amber-50 text-amber-800 border-amber-200 shadow-sm flex items-center gap-3" role="alert">
            <svg xmlns="http://www.w3.org/2000/svg" width="1.5em" height="1.5em" fill="currentColor" class="bi bi-exclamation-triangle-fill text-amber-500 flex-shrink-0" viewBox="0 0 16 16"><path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/></svg>
            <div class="text-sm font-medium"><?php echo htmlspecialchars($card_template_warning); ?></div>
        </div>
    <?php endif; ?>

    <!-- HEADER -->
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6 border-b border-slate-200 pb-4">
        <h1 class="text-xl font-bold text-[#224397] uppercase flex items-center gap-2 m-0">
            <svg xmlns="http://www.w3.org/2000/svg" width="1.3em" height="1.3em" fill="currentColor" class="bi bi-palette-fill text-[#FAB723]" viewBox="0 0 16 16"><path d="M12.433 10.07C14.133 10.585 16 11.15 16 8a8 8 0 1 0-8 8c1.996 0 1.826-1.504 1.649-3.08-.124-1.101-.252-2.237.351-2.92.465-.527 1.42-.237 2.433.07M8 5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m4.5 3a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3M5 6.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m.5 6.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3"/></svg>
            Thiết Kế Mẫu Thẻ Học Sinh
        </h1>
        <a href="/thidua/admin/the-hoc-sinh?iframe=1" class="px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 hover:text-[#224397] transition-all duration-200 font-bold flex items-center gap-1.5 text-xs shadow-sm whitespace-nowrap text-decoration-none">
            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/></svg> 
            Quay lại Hub
        </a>
    </div>

    <!-- DESIGNER WORKSPACE -->
    <div id="card-designer">
        
        <!-- CANVAS WRAPPER -->
        <div id="card-canvas-wrapper" class="shadow-inner">
            <div id="card-canvas"></div>
        </div>

        <!-- CONTROLS PANEL -->
        <div id="controls" class="bg-white rounded-2xl shadow-xl border border-[#224397]/20 flex flex-col overflow-hidden">
            <div class="p-6 flex-1 overflow-y-auto space-y-6">
                
                <!-- 1. CHỌN MẪU THẺ -->
                <div>
                    <label for="template-select" class="block text-xs font-bold text-[#224397] mb-2 uppercase tracking-wide">1. Chọn Mẫu Thẻ</label>
                    <select id="template-select" class="block w-full rounded-lg border border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 p-2.5 text-sm font-medium text-slate-800 bg-white mb-3" <?php if(empty($danh_sach_mau_the)) echo 'disabled'; ?>>
                        <?php if(empty($danh_sach_mau_the)): ?>
                            <option>Chưa có mẫu nào</option>
                        <?php else: ?>
                            <?php foreach($danh_sach_mau_the as $mau): ?>
                                <option value="<?php echo $mau['id']; ?>" <?php if($mau['id'] == $mau_the_id) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($mau['ten_mau']); ?>
                                    <?php if($mau['is_default']) echo ' (Mặc định)'; ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    
                    <div class="grid grid-cols-3 gap-2 mb-2">
                        <button id="new-template-btn" type="button" class="py-2 px-2 bg-indigo-50 hover:bg-[#224397] text-[#224397] hover:text-white border border-[#224397]/30 hover:border-[#224397] rounded-lg text-xs font-bold transition-all duration-200 shadow-sm">Tạo Mới</button>
                        <button id="rename-template-btn" type="button" class="py-2 px-2 bg-slate-50 hover:bg-slate-700 text-slate-700 hover:text-white border border-slate-300 hover:border-slate-700 rounded-lg text-xs font-bold transition-all duration-200 shadow-sm" <?php if(!$mau_the_id) echo 'disabled'; ?>>Đổi Tên</button>
                        <button id="delete-template-btn" type="button" class="py-2 px-2 bg-red-50 hover:bg-red-600 text-red-600 hover:text-white border border-red-200 hover:border-red-600 rounded-lg text-xs font-bold transition-all duration-200 shadow-sm" <?php if(!$mau_the_id) echo 'disabled'; ?>>Xóa</button>
                    </div>
                    <div class="grid grid-cols-2 gap-2 mt-2">
                        <button id="set-default-btn" type="button" class="w-full py-2 px-2 bg-emerald-50 hover:bg-emerald-600 text-emerald-700 hover:text-white border border-emerald-300 hover:border-emerald-600 rounded-lg text-xs font-bold transition-all duration-200 shadow-sm" <?php if(!$mau_the_id) echo 'disabled'; ?>>Làm mặc định</button>
                        <button id="duplicate-template-btn" type="button" class="w-full py-2 px-2 bg-purple-50 hover:bg-purple-600 text-purple-700 hover:text-white border border-purple-300 hover:border-purple-600 rounded-lg text-xs font-bold transition-all duration-200 shadow-sm" <?php if(!$mau_the_id) echo 'disabled'; ?>>Nhân bản</button>
                    </div>
                </div>
                
                <hr class="border-slate-200 m-0">
                
                <!-- 2. PHÔI THẺ -->
                <div>
                    <label class="block text-xs font-bold text-[#224397] mb-2 uppercase tracking-wide">2. Phôi thẻ (Nền)</label>
                    <input type="file" id="bg-upload-input" class="block w-full rounded-lg border border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 p-2 text-xs text-slate-700 bg-slate-50" accept="image/png, image/jpeg">
                </div>
                
                <hr class="border-slate-200 m-0">
                
                <!-- 3. THÊM THÔNG TIN -->
                <div>
                    <label class="block text-xs font-bold text-[#224397] mb-2 uppercase tracking-wide">3. Thêm thông tin</label>
                    <div class="flex items-center gap-2 mb-3">
                        <select id="add-element-select" class="block flex-1 rounded-lg border border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 p-2 text-sm font-medium text-slate-800 bg-white">
                            <option value="ho_ten">[HS] Họ và Tên</option>
                            <option value="ma_hoc_sinh">[HS] Mã Học Sinh</option>
                            <option value="lop">[HS] Lớp</option>
                            <option value="ngay_sinh">[HS] Ngày Sinh</option>
                            <option value="nien_khoa">[HS] Niên Khóa</option>
                            <option value="qr_code">[QR] Mã QR Code</option>
                            <option value="anh_the">[Ảnh] Ảnh 3x4</option>
                        </select>
                        <button id="add-element-btn" class="px-4 py-2 bg-[#224397] hover:bg-[#224397]/90 text-white rounded-lg font-bold text-xs shadow-md hover:shadow-lg transition-all" type="button">Thêm</button>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="text" id="custom-text-input" class="block flex-1 rounded-lg border border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 p-2 text-sm font-medium text-slate-800" placeholder="Text tùy chỉnh...">
                        <button id="add-custom-text-btn" class="px-3 py-2 bg-slate-700 hover:bg-slate-800 text-white rounded-lg font-bold text-xs shadow-md transition-all whitespace-nowrap" type="button">Thêm Text</button>
                    </div>
                </div>
                
                <hr class="border-slate-200 m-0">
                
                <!-- 4. TÙY CHỈNH THÀNH PHẦN -->
                <div id="element-properties" class="hidden space-y-4">
                    <div class="flex items-center justify-between bg-indigo-50 border border-indigo-100 rounded-lg p-2.5">
                        <span class="text-xs font-bold text-[#224397] uppercase tracking-wider">4. Tùy chỉnh ID:</span>
                        <span id="selected-element-id" class="px-2 py-0.5 rounded text-xs font-mono font-bold bg-[#224397] text-white shadow-sm"></span>
                    </div>

                    <!-- TỌA ĐỘ VÀ ĐIỀU HƯỚNG -->
                    <div class="pt-2 pb-1 border-b border-slate-100">
                        <label class="block text-xs font-bold text-slate-700 mb-2">Vị trí (Tọa độ X, Y):</label>
                        <div class="flex items-center gap-3">
                            <!-- Bảng điều khiển D-pad nhỏ -->
                            <div class="grid grid-cols-3 grid-rows-3 gap-1 bg-slate-50 p-1 rounded-lg border border-slate-200">
                                <div></div>
                                <button id="btn-nudge-up" class="w-7 h-7 bg-white hover:bg-blue-50 text-slate-600 rounded flex items-center justify-center shadow-sm border border-slate-200 transition-colors" type="button" title="Lên 1px">
                                    <svg width="12" height="12" fill="currentColor" viewBox="0 0 16 16"><path d="m7.247 4.86-4.796 5.481c-.566.647-.106 1.659.753 1.659h9.592a1 1 0 0 0 .753-1.659l-4.796-5.48a1 1 0 0 0-1.506 0z"/></svg>
                                </button>
                                <div></div>
                                <button id="btn-nudge-left" class="w-7 h-7 bg-white hover:bg-blue-50 text-slate-600 rounded flex items-center justify-center shadow-sm border border-slate-200 transition-colors" type="button" title="Sang trái 1px">
                                    <svg width="12" height="12" fill="currentColor" viewBox="0 0 16 16"><path d="m3.86 8.753 5.482 4.796c.646.566 1.658.106 1.658-.753V3.204a1 1 0 0 0-1.659-.753l-5.48 4.796a1 1 0 0 0 0 1.506z"/></svg>
                                </button>
                                <div class="w-7 h-7 flex items-center justify-center"><span class="text-[9px] font-bold text-slate-400">Di</span></div>
                                <button id="btn-nudge-right" class="w-7 h-7 bg-white hover:bg-blue-50 text-slate-600 rounded flex items-center justify-center shadow-sm border border-slate-200 transition-colors" type="button" title="Sang phải 1px">
                                    <svg width="12" height="12" fill="currentColor" viewBox="0 0 16 16"><path d="m12.14 8.753-5.482 4.796c-.646.566-1.658.106-1.658-.753V3.204a1 1 0 0 1 1.659-.753l5.48 4.796a1 1 0 0 1 0 1.506z"/></svg>
                                </button>
                                <div></div>
                                <button id="btn-nudge-down" class="w-7 h-7 bg-white hover:bg-blue-50 text-slate-600 rounded flex items-center justify-center shadow-sm border border-slate-200 transition-colors" type="button" title="Xuống 1px">
                                    <svg width="12" height="12" fill="currentColor" viewBox="0 0 16 16"><path d="M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z"/></svg>
                                </button>
                                <div></div>
                            </div>
                            <!-- Ô nhập X, Y -->
                            <div class="flex-1 space-y-2">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-slate-500 w-4">X:</span>
                                    <input type="number" id="pos-x-input" class="block w-full rounded border border-slate-300 shadow-sm focus:border-blue-500 p-1 text-sm text-slate-800">
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-slate-500 w-4">Y:</span>
                                    <input type="number" id="pos-y-input" class="block w-full rounded border border-slate-300 shadow-sm focus:border-blue-500 p-1 text-sm text-slate-800">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-properties">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Cỡ chữ (px):</label>
                        <input type="number" id="font-size-input" class="block w-full rounded-lg border border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 p-2 text-sm font-medium text-slate-800" min="8">
                    </div>
                    
                    <div class="text-properties">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Font chữ:</label>
                        <select id="font-family-select" class="block w-full rounded-lg border border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 p-2 text-sm font-medium text-slate-800 bg-white">
                            <option value="Arial, sans-serif">Arial</option>
                            <option value="Roboto, sans-serif">Roboto</option>
                            <option value="Montserrat, sans-serif">Montserrat</option>
                            <option value="Merriweather, serif">Merriweather</option>
                            <option value="Oswald, sans-serif">Oswald</option>
                            <option value="Lato, sans-serif">Lato</option>
                            <option value="Times New Roman, serif">Times New Roman</option>
                        </select>
                    </div>
                    
                    <div class="text-properties">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Màu chữ:</label>
                        <input type="color" id="color-input" class="block w-full h-10 rounded-lg border border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 p-1 cursor-pointer bg-white">
                    </div>
                    
                    <div class="text-properties flex items-center gap-6 pt-1">
                        <label class="flex items-center gap-2 cursor-pointer text-sm font-bold text-slate-800">
                            <input class="rounded border-slate-300 text-[#224397] shadow-sm focus:border-[#224397] w-4 h-4 cursor-pointer" type="checkbox" id="fw-bold-check">
                            In đậm
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-sm italic font-medium text-slate-800">
                            <input class="rounded border-slate-300 text-[#224397] shadow-sm focus:border-[#224397] w-4 h-4 cursor-pointer" type="checkbox" id="font-italic-check">
                            In nghiêng
                        </label>
                    </div>
                    
                    <div class="text-properties">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Căn lề:</label>
                        <select id="text-align-select" class="block w-full rounded-lg border border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 p-2 text-sm font-medium text-slate-800 bg-white">
                            <option value="left">Trái</option>
                            <option value="center">Giữa</option>
                            <option value="right">Phải</option>
                        </select>
                    </div>
                    
                    <!-- CỠ CHỮ ĐỘNG -->
                    <div id="dynamic-size-settings" class="pt-4 border-t border-slate-100 space-y-3" style="display: none;">
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-[#224397] uppercase tracking-wide">
                            <input class="rounded border-slate-300 text-[#224397] shadow-sm focus:border-[#224397] w-4 h-4 cursor-pointer" type="checkbox" id="dynamic-size-enable">
                            Tự động đổi cỡ chữ theo độ dài tên
                        </label>
                        <div id="size-rules-container" class="space-y-2"></div>
                        <button id="add-rule-btn" type="button" class="w-full py-1.5 px-3 bg-indigo-50 hover:bg-[#224397] text-[#224397] hover:text-white border border-[#224397]/30 hover:border-[#224397] rounded-lg text-xs font-bold transition-all duration-200 shadow-sm flex items-center justify-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="currentColor" class="bi bi-plus-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/></svg> 
                            Thêm quy tắc
                        </button>
                    </div>

                    <div class="size-properties">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Rộng (px):</label>
                        <input type="number" id="width-input" class="block w-full rounded-lg border border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 p-2 text-sm font-medium text-slate-800" min="10">
                    </div>
                    <div class="size-properties">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Cao (px):</label>
                        <input type="number" id="height-input" class="block w-full rounded-lg border border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 p-2 text-sm font-medium text-slate-800" min="10">
                    </div>

                    <div class="pt-4 border-t border-slate-100">
                        <button id="remove-element-btn" type="button" class="w-full py-2 px-4 bg-red-600 hover:bg-red-700 text-white rounded-lg font-bold text-xs shadow-md transition-all">Xóa thành phần này</button>
                    </div>
                </div>
            </div>

            <div class="p-6 border-t border-slate-200 bg-slate-50">
                <button id="save-template-btn" type="button" class="w-full py-3 px-4 bg-[#224397] hover:bg-[#224397]/90 text-white rounded-xl font-bold text-sm shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2" <?php if(!$mau_the_id) echo 'disabled'; ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-save-fill" viewBox="0 0 16 16"><path d="M8.5 1.5A1.5 1.5 0 0 1 10 0h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h6c-.314.418-.5.937-.5 1.5v7.793L4.854 6.646a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0l3.5-3.5a.5.5 0 0 0-.708-.708L8.5 9.293z"/></svg> 
                    Lưu Mẫu Hiện Tại
                </button>
            </div>
        </div>
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
        <p id="customConfirmMsg" class="text-sm text-slate-600 mb-6 leading-relaxed"></p>
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

<!-- HỘP THOẠI NHẬP LIỆU TÙY CHỈNH (CUSTOM PROMPT MODAL) -->
<div id="customPromptModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[200] flex items-center justify-center opacity-0 pointer-events-none transition-all duration-200">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl p-6 border border-slate-200 transform scale-95 transition-all duration-200">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center flex-shrink-0 font-bold">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.5em" height="1.5em" fill="currentColor" class="bi bi-input-cursor-text" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M5 2a.5.5 0 0 1 .5-.5c.862 0 1.573.287 2.06.566.174.099.321.198.44.286.119-.088.266-.187.44-.286C8.927 1.787 9.638 1.5 10.5 1.5a.5.5 0 0 1 0 1c-.638 0-1.177.213-1.564.434a3.5 3.5 0 0 0-.436.294V7.5H9a.5.5 0 0 1 0 1h-.5v4.272c.1.08.248.187.436.294.387.221.926.434 1.564.434a.5.5 0 0 1 0 1c-.862 0-1.573-.287-2.06-.566a5 5 0 0 1-.44-.286 5 5 0 0 1-.44.286c-.487.279-1.198.566-2.06.566a.5.5 0 0 1 0-1c.638 0 1.177-.213 1.564-.434.188-.107.335GI.214.436-.294V8.5H6a.5.5 0 0 1 0-1h.5V3.228a3.5 3.5 0 0 0-.436-.294A3.1 3.1 0 0 0 5.5 2.5.5.5 0 0 1 5 2z"/></svg>
            </div>
            <h3 id="customPromptTitle" class="text-lg font-bold text-slate-800 m-0">Nhập thông tin</h3>
        </div>
        <div class="mb-6">
            <input type="text" id="customPromptInput" class="block w-full rounded-xl border border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 p-3 text-sm font-medium text-slate-800 bg-white">
        </div>
        <div class="flex justify-end gap-3">
            <button onclick="closeCustomPrompt(null)" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-sm transition-colors">
                Hủy bỏ
            </button>
            <button onclick="submitCustomPrompt()" class="px-6 py-2.5 bg-[#224397] hover:bg-[#224397]/90 text-white font-bold rounded-xl text-sm shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all">
                Xác nhận
            </button>
        </div>
    </div>
</div>

<script>
// CUSTOM MODAL HELPER FUNCTIONS
let confirmCallback = null;
let promptCallback = null;

function showCustomAlert(msg) {
    const modal = document.getElementById('customAlertModal');
    const msgEl = document.getElementById('customAlertMsg');
    if(msgEl && modal) {
        msgEl.textContent = msg;
        modal.classList.remove('opacity-0', 'pointer-events-none');
        const box = modal.querySelector('.bg-white');
        box.classList.remove('scale-95');
        box.classList.add('scale-100');
    } else { alert(msg); }
}

function closeCustomAlert() {
    const modal = document.getElementById('customAlertModal');
    if(modal) {
        modal.classList.add('opacity-0', 'pointer-events-none');
        const box = modal.querySelector('.bg-white');
        box.classList.remove('scale-100');
        box.classList.add('scale-95');
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
    } else { callback(confirm(msg)); }
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

function showCustomPrompt(title, defaultVal, callback) {
    const modal = document.getElementById('customPromptModal');
    const titleEl = document.getElementById('customPromptTitle');
    const inputEl = document.getElementById('customPromptInput');
    if(titleEl && inputEl && modal) {
        titleEl.textContent = title;
        inputEl.value = defaultVal || '';
        promptCallback = callback;
        modal.classList.remove('opacity-0', 'pointer-events-none');
        const box = modal.querySelector('.bg-white');
        box.classList.remove('scale-95');
        box.classList.add('scale-100');
        setTimeout(() => inputEl.focus(), 150);
    } else { callback(prompt(title, defaultVal)); }
}

function closeCustomPrompt(val) {
    const modal = document.getElementById('customPromptModal');
    if(modal) {
        modal.classList.add('opacity-0', 'pointer-events-none');
        const box = modal.querySelector('.bg-white');
        box.classList.remove('scale-100');
        box.classList.add('scale-95');
        if(promptCallback) promptCallback(val);
    }
}

function submitCustomPrompt() {
    const inputEl = document.getElementById('customPromptInput');
    closeCustomPrompt(inputEl ? inputEl.value : null);
}

document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('card-canvas');
    const controls = {
        templateSelect: document.getElementById('template-select'),
        newBtn: document.getElementById('new-template-btn'),
        renameBtn: document.getElementById('rename-template-btn'),
        deleteBtn: document.getElementById('delete-template-btn'),
        setDefaultBtn: document.getElementById('set-default-btn'),
        duplicateBtn: document.getElementById('duplicate-template-btn'),
        bgUploadInput: document.getElementById('bg-upload-input'),
        addSelect: document.getElementById('add-element-select'),
        addBtn: document.getElementById('add-element-btn'),
        customTextInput: document.getElementById('custom-text-input'),
        addCustomTextBtn: document.getElementById('add-custom-text-btn'),
        
        propertiesPanel: document.getElementById('element-properties'),
        selectedElementId: document.getElementById('selected-element-id'),
        fontSizeInput: document.getElementById('font-size-input'),
        fontFamilySelect: document.getElementById('font-family-select'),
        colorInput: document.getElementById('color-input'),
        boldCheck: document.getElementById('fw-bold-check'),
        italicCheck: document.getElementById('font-italic-check'),
        textAlignSelect: document.getElementById('text-align-select'),
        widthInput: document.getElementById('width-input'),
        heightInput: document.getElementById('height-input'),
        posXInput: document.getElementById('pos-x-input'),
        posYInput: document.getElementById('pos-y-input'),
        removeBtn: document.getElementById('remove-element-btn'),
        saveBtn: document.getElementById('save-template-btn'),

        dynamicSizeSettings: document.getElementById('dynamic-size-settings'),
        dynamicSizeEnable: document.getElementById('dynamic-size-enable'),
        sizeRulesContainer: document.getElementById('size-rules-container'),
        addRuleBtn: document.getElementById('add-rule-btn'),
    };
    
    let template = <?php echo json_encode( json_decode($mau_the_json ?? '{}', true) ); ?> || {};
    let selectedElement = null;
    const currentTemplateId = <?php echo json_encode($mau_the_id); ?>;

    function migrateTemplateData(tpl) {
        if (Array.isArray(tpl.elements)) {
            const newElements = {};
            tpl.elements.forEach(el => {
                const id = el.id || `${el.type}_${Date.now()}`;
                newElements[id] = el;
            });
            tpl.elements = newElements;
        }
        
        if (!tpl.elements || typeof tpl.elements !== 'object') {
            tpl.elements = {};
        }

        return tpl;
    }

    function renderElements() {
        canvas.innerHTML = '';
        if (!template.elements) return;
        
        for (const id in template.elements) {
            const el = template.elements[id];
            const div = document.createElement('div');
            div.className = 'draggable-element';
            div.id = id;
            div.style.left = (el.x || 10) + 'px';
            div.style.top = (el.y || 10) + 'px';
            
            const isTextElement = el.type.startsWith('custom-text') || ['ho_ten', 'ma_hoc_sinh', 'lop', 'ngay_sinh', 'nien_khoa'].includes(el.type);

            if (isTextElement) {
                div.style.fontSize = el.fontSize + 'px';
                div.style.color = el.color;
                div.style.fontWeight = el.isBold ? 'bold' : 'normal';
                div.style.fontStyle = el.isItalic ? 'italic' : 'normal';
                div.style.fontFamily = el.fontFamily || 'Arial, sans-serif';
                div.textContent = el.text || `[${el.type}]`;
                div.style.width = (el.width || 150) + 'px';
                div.style.textAlign = el.textAlign || 'left';
                div.style.whiteSpace = 'normal';
                
                const handleL = document.createElement('div'); handleL.className = 'resize-handle left'; div.appendChild(handleL);
                const handleR = document.createElement('div'); handleR.className = 'resize-handle right'; div.appendChild(handleR);
                
            } else { 
                div.style.width = el.width + 'px';
                div.style.height = el.height + 'px';
                div.style.lineHeight = el.height + 'px';
                div.style.textAlign = 'center';
                div.style.backgroundColor = 'rgba(0,0,0,0.1)';
                div.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-arrows-fullscreen inline mr-1" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M5.828 10.172a.5.5 0 0 0-.707 0l-4.096 4.096V11.5a.5.5 0 0 0-1 0v3.975a.5.5 0 0 0 .5.5H4.5a.5.5 0 0 0 0-1H1.732l4.096-4.096a.5.5 0 0 0 0-.707m4.344 0a.5.5 0 0 1 .707 0l4.096 4.096V11.5a.5.5 0 1 1 1 0v3.975a.5.5 0 0 1-.5.5H11.5a.5.5 0 0 1 0-1h2.768l-4.096-4.096a.5.5 0 0 1 0-.707m0-4.344a.5.5 0 0 0 .707 0l4.096-4.096V4.5a.5.5 0 1 0 1 0V.525a.5.5 0 0 0-.5-.5H11.5a.5.5 0 0 0 0 1h2.768l-4.096 4.096a.5.5 0 0 0 0 .707m-4.344 0a.5.5 0 0 1-.707 0L1.025 1.732V4.5a.5.5 0 0 1-1 0V.525a.5.5 0 0 1 .5-.5H4.5a.5.5 0 0 1 0 1H1.732l4.096 4.096a.5.5 0 0 1 0 .707"/></svg> [${el.type}]`;
            }

            canvas.appendChild(div);
            makeDraggable(div);
            if (isTextElement) { makeResizable(div); }
        }
    }

    function updatePropertiesPanel() {
        if (!selectedElement) {
            controls.propertiesPanel.classList.add('hidden');
            return;
        }
        controls.dynamicSizeSettings.style.display = 'none';

        const id = selectedElement.id;
        const elData = template.elements[id];
        if (!elData) return;

        controls.propertiesPanel.classList.remove('hidden');
        controls.selectedElementId.textContent = id;

        const isTextElement = elData.type.startsWith('custom-text') || ['ho_ten', 'ma_hoc_sinh', 'lop', 'ngay_sinh', 'nien_khoa'].includes(elData.type);
        const isSizeableImage = ['qr_code', 'anh_the'].includes(elData.type);

        document.querySelectorAll('.text-properties').forEach(p => p.style.display = isTextElement ? '' : 'none');
        document.querySelectorAll('.size-properties').forEach(p => p.style.display = isSizeableImage ? '' : 'none');

        if (isTextElement) {
            controls.fontSizeInput.value = elData.fontSize;
            controls.fontFamilySelect.value = elData.fontFamily || 'Arial, sans-serif';
            controls.colorInput.value = elData.color;
            controls.boldCheck.checked = elData.isBold;
            controls.italicCheck.checked = elData.isItalic;
            controls.textAlignSelect.value = elData.textAlign || 'left';
        }

        if (isSizeableImage) {
            controls.widthInput.value = elData.width;
            controls.heightInput.value = elData.height;
        }
        
        controls.posXInput.value = elData.x || 0;
        controls.posYInput.value = elData.y || 0;

        if (id === 'ho_ten') {
            controls.dynamicSizeSettings.style.display = 'block';
            controls.dynamicSizeEnable.checked = !!elData.dynamicSize;
            renderSizeRules();
            controls.sizeRulesContainer.style.display = elData.dynamicSize ? 'block' : 'none';
            controls.addRuleBtn.style.display = elData.dynamicSize ? 'flex' : 'none';
        }
    }

    function makeDraggable(element) {
        let pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
        element.onmousedown = function(e) {
            if (e.target.classList.contains('resize-handle')) return;
            e.preventDefault();
            document.querySelectorAll('.draggable-element').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');
            selectedElement = element;
            updatePropertiesPanel();
            pos3 = e.clientX; pos4 = e.clientY;
            document.onmouseup = closeDragElement;
            document.onmousemove = elementDrag;
        };
        function elementDrag(e) {
            e.preventDefault();
            pos1 = pos3 - e.clientX; pos2 = pos4 - e.clientY;
            pos3 = e.clientX; pos4 = e.clientY;
            
            const newTop = Math.max(0, Math.min(element.offsetTop - pos2, canvas.clientHeight - element.clientHeight));
            const newLeft = Math.max(0, Math.min(element.offsetLeft - pos1, canvas.clientWidth - element.clientWidth));

            element.style.top = newTop + "px";
            element.style.left = newLeft + "px";
            
            if (selectedElement === element) {
                controls.posXInput.value = newLeft;
                controls.posYInput.value = newTop;
            }
        }

        function closeDragElement() {
            document.onmouseup = null; document.onmousemove = null;
            const id = element.id;
            if (template.elements[id]) {
                template.elements[id].x = element.offsetLeft;
                template.elements[id].y = element.offsetTop;
            }
        }
    }
    
    function makeResizable(element) {
        const handleR = element.querySelector('.resize-handle.right');
        const handleL = element.querySelector('.resize-handle.left');
        let original_width = 0;
        let original_x = 0;
        let original_mouse_x = 0;

        const resizeRight = (e) => {
            const width = original_width + (e.pageX - original_mouse_x);
            if (width > 50) element.style.width = width + 'px';
        };

        const resizeLeft = (e) => {
            const dx = e.pageX - original_mouse_x;
            const new_width = original_width - dx;
            if (new_width > 50) {
                element.style.width = new_width + 'px';
                element.style.left = original_x + dx + 'px';
            }
        };

        const stopResize = () => {
            document.onmousemove = null;
            document.onmouseup = null;
            const id = element.id;
            if (template.elements[id]) {
                template.elements[id].width = element.offsetWidth;
                template.elements[id].x = element.offsetLeft;
            }
        };

        if(handleR) {
            handleR.onmousedown = function(e) {
                e.preventDefault(); e.stopPropagation();
                original_width = element.offsetWidth;
                original_mouse_x = e.pageX;
                document.onmousemove = resizeRight;
                document.onmouseup = stopResize;
            };
        }

        if(handleL) {
            handleL.onmousedown = function(e) {
                e.preventDefault(); e.stopPropagation();
                original_width = element.offsetWidth;
                original_x = element.offsetLeft;
                original_mouse_x = e.pageX;
                document.onmousemove = resizeLeft;
                document.onmouseup = stopResize;
            };
        }
    }

    canvas.addEventListener('dblclick', function(e) {
        if (selectedElement && selectedElement.id.startsWith('custom-text-')) {
            selectedElement.setAttribute('contenteditable', 'true');
            selectedElement.focus();
        }
    });

    canvas.addEventListener('blur', function(e) {
        if (e.target.classList.contains('draggable-element') && e.target.getAttribute('contenteditable') === 'true') {
            e.target.setAttribute('contenteditable', 'false');
            if (template.elements[e.target.id]) {
                template.elements[e.target.id].text = e.target.textContent;
            }
        }
    }, true);

    if(controls.templateSelect) {
        controls.templateSelect.addEventListener('change', function() {
            // CRITICAL FIX: PRESERVE IFRAME PARAM TO PREVENT BLANK PAGE!
            window.location.href = '/thidua/admin/the-hoc-sinh/cai-dat?id=' + this.value + '&iframe=1';
        });
    }

    function renderSizeRules() {
        controls.sizeRulesContainer.innerHTML = '';
        const elData = template.elements['ho_ten'];
        if (!elData || !elData.sizeRules) return;

        elData.sizeRules.forEach((rule, idx) => {
            const div = document.createElement('div');
            div.className = 'rule-row bg-slate-50 p-2 rounded-lg border border-slate-200 flex items-center gap-2';
            div.innerHTML = `
                <span class="text-xs text-slate-600 font-medium">Tên ></span>
                <input type="number" class="w-16 rounded border-slate-300 p-1 text-xs font-bold text-slate-800" value="${rule.maxChars}" data-field="maxChars" data-idx="${idx}">
                <span class="text-xs text-slate-600 font-medium">ký tự &rarr; Cỡ</span>
                <input type="number" class="w-16 rounded border-slate-300 p-1 text-xs font-bold text-slate-800" value="${rule.fontSize}" data-field="fontSize" data-idx="${idx}">
                <button type="button" class="text-red-500 hover:text-red-700 font-bold px-1 text-sm remove-rule-btn" data-idx="${idx}">&times;</button>
            `;
            controls.sizeRulesContainer.appendChild(div);
        });

        controls.sizeRulesContainer.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', function() {
                const idx = parseInt(this.dataset.idx);
                const field = this.dataset.field;
                if (elData.sizeRules[idx]) {
                    elData.sizeRules[idx][field] = parseInt(this.value) || 0;
                }
            });
        });

        controls.sizeRulesContainer.querySelectorAll('.remove-rule-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const idx = parseInt(this.dataset.idx);
                elData.sizeRules.splice(idx, 1);
                renderSizeRules();
            });
        });
    }

    if(controls.dynamicSizeEnable) {
        controls.dynamicSizeEnable.addEventListener('change', function() {
            const elData = template.elements['ho_ten'];
            if (!elData) return;
            elData.dynamicSize = this.checked;
            if (this.checked && !elData.sizeRules) {
                elData.sizeRules = [
                    { maxChars: 18, fontSize: 16 },
                    { maxChars: 24, fontSize: 14 }
                ];
            }
            controls.sizeRulesContainer.style.display = this.checked ? 'block' : 'none';
            controls.addRuleBtn.style.display = this.checked ? 'flex' : 'none';
            if (this.checked) renderSizeRules();
        });
    }

    if(controls.addRuleBtn) {
        controls.addRuleBtn.addEventListener('click', () => {
            const elData = template.elements['ho_ten'];
            if (elData && elData.sizeRules) {
                elData.sizeRules.push({ maxChars: 30, fontSize: 12 });
                renderSizeRules();
            }
        });
    }

    const manageTemplate = async (action, id, name = '') => {
        try {
            const response = await fetch('/thidua/api/quan-ly-mau-the', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action, id, name })
            });
            const result = await response.json();
            if (!result.success) throw new Error(result.message);
            return result;
        } catch (error) { showCustomAlert('Lỗi: ' + error.message); return null; }
    };

    if(controls.newBtn) {
        controls.newBtn.addEventListener('click', () => {
            showCustomPrompt('Nhập tên cho mẫu thẻ mới:', '', async (name) => {
                if (name) {
                    const result = await manageTemplate('create', null, name);
                    // CRITICAL FIX: PRESERVE IFRAME PARAM TO PREVENT BLANK PAGE!
                    if (result) window.location.href = '/thidua/admin/the-hoc-sinh/cai-dat?id=' + result.new_id + '&iframe=1';
                }
            });
        });
    }

    if(controls.renameBtn) {
        controls.renameBtn.addEventListener('click', () => {
            const currentName = controls.templateSelect.options[controls.templateSelect.selectedIndex].text.replace(' (Mặc định)','').trim();
            showCustomPrompt('Nhập tên mới:', currentName, async (newName) => {
                if (newName && newName !== currentName) {
                    const result = await manageTemplate('rename', currentTemplateId, newName);
                    if (result) window.location.reload();
                }
            });
        });
    }

    if(controls.deleteBtn) {
        controls.deleteBtn.addEventListener('click', () => {
            showCustomConfirm('Bạn có chắc chắn muốn xóa mẫu thẻ này?', async (agreed) => {
                if(agreed) {
                    const result = await manageTemplate('delete', currentTemplateId);
                    // CRITICAL FIX: PRESERVE IFRAME PARAM TO PREVENT BLANK PAGE!
                    if (result) window.location.href = '/thidua/admin/the-hoc-sinh/cai-dat?iframe=1';
                }
            });
        });
    }

    if(controls.setDefaultBtn) {
        controls.setDefaultBtn.addEventListener('click', async () => {
            const result = await manageTemplate('set_default', currentTemplateId);
            if (result) window.location.reload();
        });
    }

    if(controls.duplicateBtn) {
        controls.duplicateBtn.addEventListener('click', () => {
            showCustomConfirm('Bạn có muốn nhân bản mẫu thẻ này không?', async (agreed) => {
                if(agreed) {
                    const result = await manageTemplate('duplicate', currentTemplateId);
                    if (result) window.location.href = '/thidua/admin/the-hoc-sinh/cai-dat?id=' + result.new_id + '&iframe=1';
                }
            });
        });
    }

    if(controls.addBtn) {
        controls.addBtn.addEventListener('click', () => {
            const type = controls.addSelect.value;
            const id = type;
            if(template.elements[id]) { showCustomAlert('Thông tin này đã được thêm.'); return; }

            const newElement = { type: type, x: 20, y: 20 };
            if(type === 'qr_code' || type === 'anh_the') {
                if (type === 'anh_the') {
                    newElement.width = 85; newElement.height = 113;
                } else if (type === 'qr_code') {
                    newElement.width = 60; newElement.height = 60;
                }
            } else {
                 Object.assign(newElement, { 
                    fontSize: 12, color: '#000000', isBold: false, isItalic: false, 
                    fontFamily: 'Arial, sans-serif',
                    width: canvas.clientWidth - 40,
                    textAlign: 'center' 
                 });
            }
            template.elements[id] = newElement;
            renderElements();
        });
    }

    if(controls.addCustomTextBtn) {
        controls.addCustomTextBtn.addEventListener('click', () => {
            const text = controls.customTextInput.value.trim();
            if(!text) return;
            const id = 'custom-text-' + Date.now();
            template.elements[id] = {
                type: 'custom-text', text: text, x: 20, y: 40, 
                fontSize: 12, color: '#000000', isBold: false, isItalic: false, fontFamily: 'Arial, sans-serif',
                width: canvas.clientWidth - 40,
                textAlign: 'center'
            };
            controls.customTextInput.value = '';
            renderElements();
        });
    }

    if(controls.removeBtn) {
        controls.removeBtn.addEventListener('click', () => {
            if (!selectedElement) return;
            delete template.elements[selectedElement.id];
            selectedElement = null;
            renderElements();
            updatePropertiesPanel();
        });
    }

    // --- SỰ KIỆN TỌA ĐỘ VÀ ĐIỀU HƯỚNG ---
    function updateSelectedPosition(x, y) {
        if (!selectedElement) return;
        const id = selectedElement.id;
        
        // Giới hạn trong canvas
        x = Math.max(0, Math.min(x, canvas.clientWidth - selectedElement.clientWidth));
        y = Math.max(0, Math.min(y, canvas.clientHeight - selectedElement.clientHeight));
        
        selectedElement.style.left = x + 'px';
        selectedElement.style.top = y + 'px';
        
        if (template.elements[id]) {
            template.elements[id].x = x;
            template.elements[id].y = y;
        }
        
        controls.posXInput.value = x;
        controls.posYInput.value = y;
    }

    controls.posXInput.addEventListener('input', (e) => {
        updateSelectedPosition(parseInt(e.target.value) || 0, parseInt(controls.posYInput.value) || 0);
    });
    
    controls.posYInput.addEventListener('input', (e) => {
        updateSelectedPosition(parseInt(controls.posXInput.value) || 0, parseInt(e.target.value) || 0);
    });

    function nudgeSelected(dx, dy) {
        if (!selectedElement) return;
        const currentX = parseInt(controls.posXInput.value) || 0;
        const currentY = parseInt(controls.posYInput.value) || 0;
        updateSelectedPosition(currentX + dx, currentY + dy);
    }

    document.getElementById('btn-nudge-up').addEventListener('click', () => nudgeSelected(0, -1));
    document.getElementById('btn-nudge-down').addEventListener('click', () => nudgeSelected(0, 1));
    document.getElementById('btn-nudge-left').addEventListener('click', () => nudgeSelected(-1, 0));
    document.getElementById('btn-nudge-right').addEventListener('click', () => nudgeSelected(1, 0));

    ['fontSizeInput', 'fontFamilySelect', 'colorInput', 'boldCheck', 'italicCheck', 'textAlignSelect', 'widthInput', 'heightInput'].forEach(key => {
        const input = controls[key];
        if(!input) return;
        const eventType = input.type === 'checkbox' || input.tagName === 'SELECT' ? 'change' : 'input';
        input.addEventListener(eventType, (e) => {
            if (!selectedElement) return;
            const elData = template.elements[selectedElement.id];
            if (!elData) return;
            
            const value = e.target.type === 'checkbox' ? e.target.checked : (input.type === 'number' ? parseInt(e.target.value) : e.target.value);
            
            if (key === 'fontSizeInput') { elData.fontSize = value; selectedElement.style.fontSize = value + 'px'; }
            if (key === 'fontFamilySelect') { elData.fontFamily = value; selectedElement.style.fontFamily = value; }
            if (key === 'colorInput') { elData.color = value; selectedElement.style.color = value; }
            if (key === 'boldCheck') { elData.isBold = value; selectedElement.style.fontWeight = value ? 'bold' : 'normal'; }
            if (key === 'italicCheck') { elData.isItalic = value; selectedElement.style.fontStyle = value ? 'italic' : 'normal'; }
            if (key === 'textAlignSelect') { elData.textAlign = value; selectedElement.style.textAlign = value; }
            
            if (key === 'widthInput') { elData.width = value; selectedElement.style.width = value + 'px'; }
            if (key === 'heightInput') { elData.height = value; selectedElement.style.height = value + 'px'; }
        });
    });

    if(controls.saveBtn) {
        controls.saveBtn.addEventListener('click', async () => {
            try {
                controls.saveBtn.disabled = true;
                controls.saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2"></span> Đang lưu...';
                const response = await fetch('/thidua/api/luu-mau-the', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: currentTemplateId, template: template })
                });
                const result = await response.json();
                if (result.success) {
                    showCustomAlert('Lưu mẫu thẻ thành công!');
                } else { throw new Error(result.message || 'Lưu thất bại.'); }
            } catch (error) {
                showCustomAlert('Lỗi: ' + error.message);
            } finally {
                controls.saveBtn.disabled = false;
                controls.saveBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-save-fill mr-2" viewBox="0 0 16 16"><path d="M8.5 1.5A1.5 1.5 0 0 1 10 0h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h6c-.314.418-.5.937-.5 1.5v7.793L4.854 6.646a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0l3.5-3.5a.5.5 0 0 0-.708-.708L8.5 9.293z"/></svg> Lưu Mẫu Hiện Tại';
            }
        });
    }

    canvas.style.backgroundImage = `url('${template.background || '/thidua/public/assets/phoi_the_mac_dinh.png'}')`;
    template = migrateTemplateData(template); 
    renderElements();

    if(controls.bgUploadInput) {
        controls.bgUploadInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const formData = new FormData();
                formData.append('bg_file', file);
                formData.append('id', currentTemplateId);
                fetch('/thidua/api/upload-phoi-the', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        template.background = data.file_url;
                        canvas.style.backgroundImage = `url('${template.background}')`;
                        showCustomAlert('Tải phôi thẻ thành công!');
                    } else { showCustomAlert('Lỗi: ' + data.message); }
                }).catch(err => showCustomAlert('Lỗi tải file: ' + err.message));
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>
