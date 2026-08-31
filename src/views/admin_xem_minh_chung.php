<?php
$page_title = "Thư Viện Minh Chứng";
require_once __DIR__ . "/partials/admin_header.php";

$all_weeks = $all_weeks ?? [];
$selected_tuan_id = $selected_tuan_id ?? null;
$selected_tuan_info = $selected_tuan_info ?? [];
$proofs_by_class = $proofs_by_class ?? [];

function is_image_by_type($file_type, $filename = '') {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'heic', 'heif'])) return true;
    if (empty($file_type) && empty($filename)) return true; // Fallback cho ảnh up từ Zalo bản cũ bị thiếu metadata
    if (empty($file_type)) return false;
    return in_array(strtolower($file_type),["image/jpeg","image/png","image/gif","image/webp","image/bmp", "image/heic", "image/heif"]);
}
function is_pdf_by_type($file_type) {
    return strtolower($file_type) === "application/pdf";
}
?>

<link href="/thidua/public/assets/libs/fancybox.css" rel="stylesheet">
<style>
    /* ----- Bảng màu và biến CSS hiện đại ----- */
    :root {
        --primary-blue: #00a8e8;
        --primary-green: #97c93c;
        --dark-blue: #2c3e50;
        --text-primary: #1d2d35;
        --text-secondary: #5a6a72;
        --bg-light: #f4f7f9;
        --card-border: #e9ecef;
    }
    
    body { background-color: var(--bg-light); }

    /* Fancybox custom */
    .proof-item { transition: all 0.3s; }
    .proof-item:hover { transform: translateY(-3px); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15); border-color: #cbd5e1; }
    .proof-item.hidden { display: none !important; }

    body::-webkit-scrollbar, html::-webkit-scrollbar { display: block !important; width: 8px; height: 8px; }
    body::-webkit-scrollbar-thumb, html::-webkit-scrollbar-thumb { background: rgba(34, 67, 151, 0.3); border-radius: 4px; }
    body::-webkit-scrollbar-track, html::-webkit-scrollbar-track { background: transparent; }
</style>

<div class="w-full px-2 lg:px-6">
    
    <!-- Filter và Buttons trên 1 hàng -->
    <div class="flex flex-row flex-wrap items-end justify-between gap-2 mb-4 mt-4">
        
        <!-- Filter Form (Bên trái) -->
        <div class="flex flex-row flex-wrap items-end gap-2 m-0">
            <input type="hidden" id="iframe_param" value="<?= isset($_GET["iframe"]) ? "1" : "0" ?>">
            
            <div>
                <label for="tuan_id_select" class="block text-[13.5px] font-bold text-[#224397] mb-0.5">Tuần</label>
                <select id="tuan_id_select" class="block w-40 rounded border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-2 py-1 text-[13px]" onchange="fetchWeekData(this.value)">
                    <option value="">-- Chọn tuần --</option>
                    <?php foreach ($all_weeks as $week): ?>
                        <option value="<?= $week["id"] ?>" <?= ($selected_tuan_id == $week["id"]) ? "selected" : "" ?>>
                            <?= htmlspecialchars($week["ten_tuan"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Vùng Bộ lọc Lớp & Trạng thái (Cập nhật động theo tuần) -->
            <div id="dynamic-subfilters" class="flex flex-row flex-wrap items-end gap-2">
                <?php if (!empty($proofs_by_class)): ?>
                <div>
                    <label for="classFilter" class="block text-[13.5px] font-bold text-[#224397] mb-0.5">Lớp</label>
                    <select id="classFilter" class="block w-32 rounded border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-2 py-1 text-[13px]">
                        <option value="*">Tất cả</option>
                        <?php foreach ($proofs_by_class as $ten_lop => $proofs): ?>
                            <option value=".lop-<?= md5($ten_lop) ?>"><?= htmlspecialchars($ten_lop) ?> (<?= count($proofs) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="stateFilter" class="block text-[13.5px] font-bold text-[#224397] mb-0.5">Hiển thị</label>
                    <select id="stateFilter" class="block w-36 rounded border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-2 py-1 text-[13px]">
                        <option value="all">Tất cả trạng thái</option>
                        <option value="local">Lưu ở Local</option>
                        <option value="cloud">Lưu ở Cloud</option>
                    </select>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Action Buttons (Bên phải, cập nhật động theo tuần) -->
        <div id="dynamic-toolbar" class="flex items-center flex-wrap gap-1.5">
            <?php if ($selected_tuan_id): ?>
            <div class="flex items-center flex-wrap gap-1.5" id="default-toolbar">
                
                <?php if (!empty($proofs_by_class)): ?>
                <div class="flex items-center h-[28px] mr-2">
                    <input class="rounded border-slate-300 text-blue-500 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200" type="checkbox" id="select-all-proofs">
                    <label class="ml-1.5 block text-[13.5px] font-bold text-[#224397] whitespace-nowrap cursor-pointer" for="select-all-proofs">Chọn tất cả</label>
                </div>
                <?php endif; ?>

                <a href="/thidua/admin/xuat-minh-chung-zip?tuan_id=<?= $selected_tuan_id ?>" target="_blank" class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm whitespace-nowrap"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-zip-fill" viewBox="0 0 16 16"><path d="M5.5 9.438V8.5h1v.938a1 1 0 0 0 .03.243l.4 1.598-.93.62-.93-.62.4-1.598a1 1 0 0 0 .03-.243z"/><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2zm-4 3.5v.938l-.4 1.599a1 1 0 0 0 .416 1.074l.93.62a1 1 0 0 0 1.108 0l.93-.62a1 1 0 0 0 .415-1.074l-.4-1.599V7h-3zm2 .938v.562h-1v-.562z"/></svg> TẢI ZIP</a>
                
                <button type="button" id="delete-proofs-btn" class="px-2 py-1 bg-red-500 border border-transparent rounded text-white hover:bg-red-600 transition-colors font-medium flex items-center gap-1 text-[11px] shadow-sm whitespace-nowrap"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16"><path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5M8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5m3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0"/></svg> XÓA</button>

                <!-- Tác Vụ Dropdown -->
                <div class="relative inline-block text-left group z-[60]">
                    <button type="button" class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm whitespace-nowrap">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-cloud-fill" viewBox="0 0 16 16"><path d="M4.406 3.342A5.53 5.53 0 0 1 8 2c2.69 0 4.923 2 5.166 4.579C14.758 6.804 16 8.137 16 9.773 16 11.569 14.502 13 12.687 13H3.781C1.708 13 0 11.366 0 9.318c0-1.763 1.266-3.223 2.942-3.593.143-.863.698-1.723 1.464-2.383z"/></svg> ĐỒNG BỘ CLOUD <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-chevron-down text-[9px]" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/></svg>
                    </button>
                    <ul class="absolute right-0 mt-1 w-40 bg-white rounded shadow-lg border border-slate-100 focus:outline-none opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-[100] transform origin-top-right scale-95 group-hover:scale-100 py-1">
                        <li><a class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] text-slate-700 hover:bg-blue-50 hover:text-[#224397] cursor-pointer" id="archive-selected-btn"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-cloud-arrow-up-fill" viewBox="0 0 16 16"><path d="M8 2a5.53 5.53 0 0 0-3.594 1.342c-.766.66-1.321 1.52-1.464 2.383C1.266 6.095 0 7.555 0 9.318 0 11.366 1.708 13 3.781 13h8.906C14.502 13 16 11.57 16 9.773c0-1.636-1.242-2.969-2.834-3.194C12.923 4 10.69 2 8 2m2.354 5.146a.5.5 0 0 1-.708.708L8.5 6.707V10.5a.5.5 0 0 1-1 0V6.707L6.354 7.854a.5.5 0 1 1-.708-.708l2-2a.5.5 0 0 1 .708 0z"/></svg> Đẩy file lên Cloud</a></li>
                        <li><a class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] text-slate-700 hover:bg-blue-50 hover:text-[#224397] cursor-pointer" id="restore-selected-btn"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-cloud-arrow-down-fill" viewBox="0 0 16 16"><path d="M8 2a5.53 5.53 0 0 0-3.594 1.342c-.766.66-1.321 1.52-1.464 2.383C1.266 6.095 0 7.555 0 9.318 0 11.366 1.708 13 3.781 13h8.906C14.502 13 16 11.57 16 9.773c0-1.636-1.242-2.969-2.834-3.194C12.923 4 10.69 2 8 2m-1.5 4a.5.5 0 1 1 1 0v3.793l1.146-1.147a.5.5 0 0 1 .708.708l-2 2a.5.5 0 0 1-.708 0l-2-2a.5.5 0 1 1 .708-.708L7.5 9.793z"/></svg> Tải về Local (Đã chọn)</a></li>
                        <li><hr class="border-t border-slate-100 my-1"></li>
                        <li><a class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] text-slate-700 hover:bg-blue-50 hover:text-[#224397] cursor-pointer" id="restore-from-cloud-btn"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-cloud-arrow-down-fill" viewBox="0 0 16 16"><path d="M8 2a5.53 5.53 0 0 0-3.594 1.342c-.766.66-1.321 1.52-1.464 2.383C1.266 6.095 0 7.555 0 9.318 0 11.366 1.708 13 3.781 13h8.906C14.502 13 16 11.57 16 9.773c0-1.636-1.242-2.969-2.834-3.194C12.923 4 10.69 2 8 2m-1.5 4a.5.5 0 1 1 1 0v3.793l1.146-1.147a.5.5 0 0 1 .708.708l-2 2a.5.5 0 0 1-.708 0l-2-2a.5.5 0 1 1 .708-.708L7.5 9.793z"/></svg> Tải về Local (Tuần)</a></li>
                        <li><a class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] text-slate-700 hover:bg-blue-50 hover:text-[#224397] cursor-pointer" id="restore-all-cloud-btn"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-cloud-arrow-down-fill" viewBox="0 0 16 16"><path d="M8 2a5.53 5.53 0 0 0-3.594 1.342c-.766.66-1.321 1.52-1.464 2.383C1.266 6.095 0 7.555 0 9.318 0 11.366 1.708 13 3.781 13h8.906C14.502 13 16 11.57 16 9.773c0-1.636-1.242-2.969-2.834-3.194C12.923 4 10.69 2 8 2m-1.5 4a.5.5 0 1 1 1 0v3.793l1.146-1.147a.5.5 0 0 1 .708.708l-2 2a.5.5 0 0 1-.708 0l-2-2a.5.5 0 1 1 .708-.708L7.5 9.793z"/></svg> Tải về Local (Năm)</a></li>
                    </ul>
                </div>

                <!-- OneDrive Dropdown -->
                <div class="relative inline-block text-left group z-[55]">
                    <button type="button" class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm whitespace-nowrap">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-microsoft" viewBox="0 0 16 16"><path d="M7.462 0H0v7.19h7.462V0zM16 0H8.538v7.19H16V0zM7.462 8.211H0V16h7.462V8.211zm8.538 0H8.538V16H16V8.211z"/></svg> SAO LƯU ONEDRIVE <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-chevron-down text-[9px]" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/></svg>
                    </button>
                    <ul class="absolute right-0 mt-1 w-44 bg-white rounded shadow-lg border border-slate-100 focus:outline-none opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-[100] transform origin-top-right scale-95 group-hover:scale-100 py-1">
                        <li><a class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] text-slate-700 hover:bg-blue-50 hover:text-[#224397] cursor-pointer" id="backup-onedrive-selected-btn"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-cloud-upload-fill" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 0a5.53 5.53 0 0 0-3.594 1.342c-.766.66-1.321 1.52-1.464 2.383C1.266 6.095 0 7.555 0 9.318 0 11.366 1.708 13 3.781 13h8.906C14.502 13 16 11.57 16 9.773c0-1.636-1.242-2.969-2.834-3.194C12.923 4 10.69 2 8 2M7.5 11.5V6.707L5.354 8.854a.5.5 0 1 1-.708-.708l3-3a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 6.707V11.5a.5.5 0 0 1-1 0"/></svg> Lưu OneDrive (Đã chọn)</a></li>
                        <li><hr class="border-t border-slate-100 my-1"></li>
                        <li><a class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] text-slate-700 hover:bg-blue-50 hover:text-[#224397] cursor-pointer" id="backup-onedrive-week-btn"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-cloud-upload-fill" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 0a5.53 5.53 0 0 0-3.594 1.342c-.766.66-1.321 1.52-1.464 2.383C1.266 6.095 0 7.555 0 9.318 0 11.366 1.708 13 3.781 13h8.906C14.502 13 16 11.57 16 9.773c0-1.636-1.242-2.969-2.834-3.194C12.923 4 10.69 2 8 2M7.5 11.5V6.707L5.354 8.854a.5.5 0 1 1-.708-.708l3-3a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 6.707V11.5a.5.5 0 0 1-1 0"/></svg> Lưu OneDrive (Tuần)</a></li>
                        <li><a class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] text-slate-700 hover:bg-blue-50 hover:text-[#224397] cursor-pointer" id="backup-onedrive-year-btn"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-cloud-upload-fill" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 0a5.53 5.53 0 0 0-3.594 1.342c-.766.66-1.321 1.52-1.464 2.383C1.266 6.095 0 7.555 0 9.318 0 11.366 1.708 13 3.781 13h8.906C14.502 13 16 11.57 16 9.773c0-1.636-1.242-2.969-2.834-3.194C12.923 4 10.69 2 8 2M7.5 11.5V6.707L5.354 8.854a.5.5 0 1 1-.708-.708l3-3a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 6.707V11.5a.5.5 0 0 1-1 0"/></svg> Lưu OneDrive (Năm)</a></li>
                    </ul>
                </div>

                <!-- Tác Vụ Khác Dropdown -->
                <div class="relative inline-block text-left group z-50">
                    <button type="button" class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm whitespace-nowrap">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-gear-fill" viewBox="0 0 16 16"><path d="M9.405 1.05c-.413-1.4-2.397-1.4-2.81 0l-.1.34a1.464 1.464 0 0 1-2.105.872l-.31-.17c-1.283-.698-2.686.705-1.987 1.987l.169.311c.446.82.023 1.841-.872 2.105l-.34.1c-1.4.413-1.4 2.397 0 2.81l.34.1a1.464 1.464 0 0 1 .872 2.105l-.17.31c-.698 1.283.705 2.686 1.987 1.987l.311-.169a1.464 1.464 0 0 1 2.105.872l.1.34c.413 1.4 2.397 1.4 2.81 0l.1-.34a1.464 1.464 0 0 1 2.105-.872l.31.17c1.283.698 2.686-.705 1.987-1.987l-.169-.311a1.464 1.464 0 0 1 .872-2.105l.34-.1c1.4-.413 1.4-2.397 0-2.81l-.34-.1a1.464 1.464 0 0 1-.872-2.105l.17-.31c.698-1.283-.705-2.686-1.987-1.987l-.311.169a1.464 1.464 0 0 1-2.105-.872zM8 10.93a2.929 2.929 0 1 1 0-5.86 2.929 2.929 0 0 1 0 5.858z"/></svg> TÁC VỤ <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-chevron-down text-[9px]" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/></svg>
                    </button>
                    <ul class="absolute right-0 mt-1 w-44 bg-white rounded shadow-lg border border-slate-100 focus:outline-none opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-[100] transform origin-top-right scale-95 group-hover:scale-100 py-1">
                        <li><a class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] text-slate-700 hover:bg-blue-50 hover:text-[#224397] cursor-pointer" id="regen-thumbnails-btn"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-images" viewBox="0 0 16 16"><path d="M4.502 9a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/><path d="M14.002 13a2 2 0 0 1-2 2h-10a2 2 0 0 1-2-2V5A2 2 0 0 1 2 3a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2M14 2H4a1 1 0 0 0-1 1h9.002a2 2 0 0 1 2 2v7A1 1 0 0 0 15 11V3a1 1 0 0 0-1-1M2.002 4a1 1 0 0 0-1 1v8l2.646-2.354a.5.5 0 0 1 .63-.062l2.66 1.773 3.71-3.71a.5.5 0 0 1 .577-.094l1.777 1.947V5a1 1 0 0 0-1-1h-10"/></svg> Tạo ảnh thu nhỏ (Tuần)</a></li>
                        <li><a class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] text-slate-700 hover:bg-blue-50 hover:text-[#224397] cursor-pointer" id="regen-thumbnails-year-btn"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-images" viewBox="0 0 16 16"><path d="M4.502 9a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/><path d="M14.002 13a2 2 0 0 1-2 2h-10a2 2 0 0 1-2-2V5A2 2 0 0 1 2 3a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2M14 2H4a1 1 0 0 0-1 1h9.002a2 2 0 0 1 2 2v7A1 1 0 0 0 15 11V3a1 1 0 0 0-1-1M2.002 4a1 1 0 0 0-1 1v8l2.646-2.354a.5.5 0 0 1 .63-.062l2.66 1.773 3.71-3.71a.5.5 0 0 1 .577-.094l1.777 1.947V5a1 1 0 0 0-1-1h-10"/></svg> Tạo ảnh thu nhỏ (Năm)</a></li>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Content Area (Cập nhật động theo tuần) -->
    <div id="dynamic-main-content" class="bg-white rounded shadow border border-[#224397]/25 mb-6 p-0 overflow-hidden relative">
        <div class="px-4 py-3 border-b border-[#224397]/20 bg-[#224397]/5 flex items-center">
            <h3 class="mb-0 text-[15px] font-bold text-[#224397] uppercase flex items-center"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-images mr-2" viewBox="0 0 16 16"><path d="M4.502 9a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/><path d="M14.002 13a2 2 0 0 1-2 2h-10a2 2 0 0 1-2-2V5A2 2 0 0 1 2 3a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2M14 2H4a1 1 0 0 0-1 1h9.002a2 2 0 0 1 2 2v7A1 1 0 0 0 15 11V3a1 1 0 0 0-1-1M2.002 4a1 1 0 0 0-1 1v8l2.646-2.354a.5.5 0 0 1 .63-.062l2.66 1.773 3.71-3.71a.5.5 0 0 1 .577-.094l1.777 1.947V5a1 1 0 0 0-1-1h-10"/></svg> THƯ VIỆN MINH CHỨNG</h3>
        </div>
        <div class="px-4 pb-4 pt-3">
            
            <?php if (empty($proofs_by_class) && $selected_tuan_id): ?>
                <div class="flex flex-col items-center justify-center text-slate-500 py-12">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-cloud-slash text-6xl text-slate-300 mb-4" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M3.112 5.112a3 3 0 0 0-1.742 2.73c0 1.404 1.138 2.55 2.54 2.558h.092l1.378 1.379a1.5 1.5 0 0 0-.256.027H3.91C1.75 11.805 0 10.055 0 7.896c0-1.802 1.226-3.328 2.909-3.722zM12.5 10.5h.719c1.536 0 2.781-1.245 2.781-2.781 0-1.428-1.077-2.6-2.483-2.76-1.015-2.029-3.08-3.371-5.411-3.371-1.79 0-3.415.753-4.55 1.986l1.107 1.107c.883-.951 2.146-1.543 3.543-1.543 2.115 0 3.916 1.458 4.417 3.518h.596c.775 0 1.406.63 1.406 1.406 0 .775-.63 1.406-1.406 1.406h-.343zM1.146 1.646a.5.5 0 0 1 .708 0l12.5 12.5a.5.5 0 0 1-.708.708l-12.5-12.5a.5.5 0 0 1 0-.708"/></svg>
                    <h4 class="text-[15px] font-bold text-slate-700 mb-1">Không có minh chứng nào</h4>
                    <p class="text-[13px]">Tuần này chưa có minh chứng được tải lên.</p>
                </div>
            <?php elseif (!$selected_tuan_id): ?>
                <div class="flex flex-col items-center justify-center text-slate-500 py-12">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-calendar-week text-6xl text-blue-200 mb-4" viewBox="0 0 16 16"><path d="M11 6.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm-3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm-5 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5z"/><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/></svg>
                    <h4 class="text-[15px] font-bold text-[#224397] mb-1">Vui lòng chọn tuần</h4>
                    <p class="text-[13px]">Hãy chọn một tuần để xem các minh chứng tương ứng.</p>
                </div>
            <?php else: ?>
                <!-- Bộ Sưu Tập -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 mt-2" id="proof-gallery-container">
                    <?php foreach ($proofs_by_class as $ten_lop => $proofs): ?>
                        <?php foreach ($proofs as $file): 
                            $gallery_group = "lop-" . md5($ten_lop);
                            $name = htmlspecialchars($file["original_filename"] ?? 'Ảnh minh chứng');
                            $is_cloud = ($file["storage_driver"] === "cloud" || $file["storage_driver"] === "r2");
                            $is_onedrive = ($file["storage_driver"] === "onedrive");
                            
                            $attrs = "data-fancybox=\"{$gallery_group}\" data-caption=\"{$name} ({$ten_lop})\"";
                            
                            if ($is_onedrive) {
                                $path = "/thidua/api/get-presigned-url?key=" . urlencode($file["cloud_key"]) . "&driver=onedrive";
                            } elseif ($is_cloud) {
                                $path = "/thidua/api/get-presigned-url?key=" . urlencode($file["cloud_key"]);
                            } else {
                                $path = "/thidua/" . htmlspecialchars($file["file_path"]);
                            }

                            $attrs .= is_pdf_by_type($file["file_type"]) ? " data-type=\"pdf\"" : " data-type=\"image\"";

                            $thumb = "";
                            if (!$is_cloud && !$is_onedrive) {
                                if (!empty($file["thumbnail_path"])) $thumb = "/thidua/" . htmlspecialchars($file["thumbnail_path"]);
                                else if (!empty($file["file_path"]) && is_image_by_type($file["file_type"], $file["original_filename"])) $thumb = "/thidua/" . htmlspecialchars($file["file_path"]);
                            }
                            // Với Cloud/OneDrive, KHÔNG gán $thumb = $path để tránh lag do tải hàng chục ảnh gốc vài MB cùng lúc.
                            // Người dùng nhấn vào xem thì Fancybox mới tải ảnh gốc.
                        ?>
                        <div class="proof-item bg-white border border-[#224397]/25 rounded-lg overflow-hidden relative shadow-sm <?= $gallery_group ?>" data-state="<?= $is_onedrive ? "onedrive" : ($is_cloud ? "cloud" : "local") ?>">
                            
                            <input type="checkbox" class="proof-item-checkbox absolute top-2 left-2 z-10 rounded border-slate-300 text-[#224397] focus:ring-[#224397] w-4 h-4 shadow-sm cursor-pointer" value="<?= $file["id"] ?>" data-is-cloud="<?= ($is_cloud || $is_onedrive) ? "1" : "0" ?>">

                            <?php if ($file["storage_driver"] === "onedrive"): ?>
                                <span class="absolute top-2 right-2 z-10 px-1.5 py-[1px] rounded text-[9px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-200 shadow-sm leading-tight"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-microsoft inline mr-1" viewBox="0 0 16 16"><path d="M7.462 0H0v7.19h7.462V0zM16 0H8.538v7.19H16V0zM7.462 8.211H0V16h7.462V8.211zm8.538 0H8.538V16H16V8.211z"/></svg>ONEDRIVE</span>
                            <?php elseif ($is_cloud): ?>
                                <span class="absolute top-2 right-2 z-10 px-1.5 py-[1px] rounded text-[9px] font-bold bg-blue-100 text-blue-800 border border-blue-200 shadow-sm leading-tight"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-cloud-check inline mr-1" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M10.854 7.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 9.793l2.646-2.647a.5.5 0 0 1 .708 0z"/><path d="M4.406 3.342A5.53 5.53 0 0 1 8 2c2.69 0 4.923 2 5.166 4.579C14.758 6.804 16 8.137 16 9.773 16 11.569 14.502 13 12.687 13H3.781C1.708 13 0 11.366 0 9.318c0-1.763 1.266-3.223 2.942-3.593.143-.863.698-1.723 1.464-2.383z"/></svg>CLOUD</span>
                            <?php else: ?>
                                <span class="absolute top-2 right-2 z-10 px-1.5 py-[1px] rounded text-[9px] font-bold bg-slate-100 text-slate-600 border border-slate-200 shadow-sm leading-tight"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-hdd inline mr-1" viewBox="0 0 16 16"><path d="M4.5 11a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1M3 10.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0"/><path d="M16 11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V9.51c0-.418.105-.83.305-1.197l2.472-4.531A1.5 1.5 0 0 1 4.094 3h7.812a1.5 1.5 0 0 1 1.317.782l2.472 4.53c.2.368.305.78.305 1.198zM3.655 4.26 1.504 8.207A1 1 0 0 0 1.35 8.71h13.3a1 1 0 0 0-.153-.502l-2.15-3.947a.5.5 0 0 0-.439-.261H4.094a.5.5 0 0 0-.44.26zM1 10.5V11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-.5z"/></svg>LOCAL</span>
                            <?php endif; ?>

                            <a href="<?= $path ?>" <?= $attrs ?> class="block w-full h-full <?= ($is_cloud || $is_onedrive) ? "opacity-80 hover:opacity-100" : "" ?>">
                                <div class="h-[110px] bg-slate-100 flex items-center justify-center overflow-hidden border-b border-[#224397]/20">
                                    <?php if(is_image_by_type($file["file_type"], $file["original_filename"])): ?>
                                        <?php if($thumb): ?>
                                            <img src="<?= $thumb ?>" class="w-full h-full object-cover" loading="lazy">
                                        <?php else: ?>
                                            <?php if (strtolower(pathinfo($file["original_filename"], PATHINFO_EXTENSION)) === 'heic' || strtolower(pathinfo($file["original_filename"], PATHINFO_EXTENSION)) === 'heif'): ?>
                                                <div class="heic-placeholder w-full h-full bg-slate-100 flex items-center justify-center relative" data-heic-src="<?= $path ?>">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-arrow-repeat animate-spin text-4xl text-[#224397]/50" viewBox="0 0 16 16"><path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41m-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9"/><path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5 5 0 0 0 8 3M3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9z"/></svg>
                                                </div>
                                            <?php else: ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-image text-4xl text-slate-300" viewBox="0 0 16 16"><path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0"/><path d="M2.002 2A2 2 0 0 0 .002 4v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V4a1 1 0 0 1 1-1z"/></svg>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php elseif(is_pdf_by_type($file["file_type"])): ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-pdf-fill text-4xl text-red-400" viewBox="0 0 16 16"><path d="M5.523 12.424c.14-.082.293-.162.459-.238a8 8 0 0 1-.45.606c-.28.337-.498.516-.635.572a.27.27 0 0 1-.15.011c-.04-.016-.08-.046-.09-.112a.5.5 0 0 1 .05-.31c.09-.19.26-.39.816-.529zm2.46-.867c.04-.11.08-.22.12-.33a18 18 0 0 0 .14-.4c.05-.15.1-.31.14-.47a6 6 0 0 1 .15-.55c-.01.01-.02.02-.04.04-.03.04-.06.07-.09.11-.08.11-.16.23-.24.34a1 1 0 0 0-.06.09c-.02.03-.04.06-.06.09-.04.06-.08.12-.12.18-.03.04-.06.08-.1.12a14 14 0 0 0 .16.7zm2.46-.867c-.07-.03-.15-.05-.24-.07a4 4 0 0 0-.48-.07c-.17-.01-.36-.02-.56-.02a6 6 0 0 0-.66.02 14 14 0 0 0-.7.08c-.24.03-.49.07-.75.12a19 19 0 0 0-1.07.26c-.36.1-.73.23-1.1.37-.37.15-.74.31-1.1.49-.36.18-.71.37-1.04.57-.34.2-.66.42-.96.65-.29.23-.55.48-.77.74-.21.26-.39.53-.52.81a2 2 0 0 0-.15.48c-.03.15-.04.3-.03.45.02.14.06.27.12.38.07.12.16.22.28.3.12.08.26.13.43.15.17.03.36.03.58.01.22-.02.47-.07.74-.15.27-.08.56-.19.86-.34.3-.15.61-.33.93-.55.31-.22.63-.48.95-.78.31-.3.62-.64.91-1 .29-.36.57-.75.83-1.16.26-.4.5-.83.72-1.27.21-.44.4-1 .56-1.58.17-.58.3-1.18.39-1.8.1-.62.15-1.25.17-1.88.02-.63.02-1.27.01-1.9-.01-.64-.04-1.27-.08-1.9-.04-.63-.1-1.26-.17-1.88a24 24 0 0 0-.25-1.87c-.11-.59-.24-1.17-.4-1.74a12 12 0 0 0-.53-1.58 6 6 0 0 0-.67-1.42 4 4 0 0 0-.8-1.25 3 3 0 0 0-.91-1 2 2 0 0 0-1.02-.74c-.36-.16-.72-.28-1.09-.37-.36-.09-.72-.15-1.09-.19-.36-.03-.73-.04-1.09-.03z"/></svg>
                                    <?php else: ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-text-fill text-4xl text-slate-400" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M5.884 6.68 8 9.219l2.116-2.54a.5.5 0 1 1 .768.641L8.651 10l2.233 2.68a.5.5 0 0 1-.768.64L8 10.781l-2.116 2.54a.5.5 0 0 1-.768-.641L7.349 10 5.116 7.32a.5.5 0 1 1 .768-.64"/></svg>
                                    <?php endif; ?>
                                </div>
                                <div class="p-2 bg-slate-50">
                                    <p class="text-[11px] font-bold text-[#224397] truncate text-center mb-0" title="<?= $name ?>"><?= $name ?></p>
                                    <p class="text-[10px] font-medium text-slate-500 text-center mt-0 leading-tight"><?= htmlspecialchars($ten_lop) ?></p>
                                </div>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="/thidua/public/assets/libs/fancybox.umd.js"></script>
<script src="https://cdn.jsdelivr.net/npm/heic2any@0.0.4/dist/heic2any.min.js"></script>
<script>
let currentTuanId = <?= json_encode($selected_tuan_id) ?>;

function initProofLibraryEvents() {
    Fancybox.unbind("[data-fancybox]");
    Fancybox.bind("[data-fancybox]", {
        groupAll: false,
        dragToClose: true,
        Hash: false,
        Toolbar: {
            display: {
                left: ["infobar"],
                middle: ["zoomIn", "zoomOut", "toggle1to1", "rotateCCW", "rotateCW", "flipX", "flipY"],
                right: ["slideshow", "fullscreen", "download", "close"]
            },
            items: {
                download: {
                    tpl: '<button class="f-button" title="Tải ảnh về máy" data-fancybox-download><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM17 13l-5 5-5-5h3V9h4v4h3z"/></svg></button>',
                    click: function() {
                        try {
                            const instance = (typeof Fancybox !== 'undefined' && Fancybox.getInstance) ? Fancybox.getInstance() : this.instance;
                            const slide = (instance && typeof instance.getSlide === 'function') ? instance.getSlide() : null;
                            
                            let src = slide ? slide.src : '';
                            let caption = slide ? (slide.caption || '') : '';
                            
                            if (!src) {
                                const activeSlide = document.querySelector('.fancybox__slide.is-selected');
                                const img = activeSlide ? activeSlide.querySelector('img.fancybox__image, .fancybox__content img') : null;
                                if (img) src = img.currentSrc || img.src;
                            }
                            if (!caption) {
                                const captionEl = document.querySelector('.fancybox__caption');
                                if (captionEl) caption = captionEl.textContent.trim();
                            }
                            caption = caption || 'minh_chung.jpg';

                            if (src) {
                                if (src.includes('/api/get-presigned-url')) {
                                    src += (src.includes('?') ? '&' : '?') + 'download=1&filename=' + encodeURIComponent(caption);
                                }
                                const a = document.createElement('a');
                                a.href = src;
                                a.target = '_blank';
                                a.setAttribute('download', caption);
                                document.body.appendChild(a);
                                a.click();
                                setTimeout(() => {
                                    if (a.parentNode) a.parentNode.removeChild(a);
                                }, 300);
                            }
                        } catch (err) {
                            console.error('Lỗi khi tải ảnh:', err);
                        }
                    }
                }
            }
        }
    });

    // Render HEIC to JPG dynamically
    document.querySelectorAll('.heic-placeholder').forEach(async (el) => {
        try {
            const path = el.dataset.heicSrc;
            const res = await fetch(path);
            const blob = await res.blob();
            const jpgBlob = await heic2any({ blob, toType: "image/jpeg", quality: 0.5 });
            const url = URL.createObjectURL(jpgBlob);
            
            // Thay icon spin bằng ảnh thumb
            el.innerHTML = `<img src="${url}" class="w-full h-full object-cover">`;
            
            // Đổi href của <a> bên ngoài để Fancybox nhận file jpg
            const aTag = el.closest('a');
            if (aTag) {
                aTag.href = url;
                aTag.dataset.type = "image";
            }
        } catch(e) {
            console.error("HEIC convert error", e);
            el.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-circle text-4xl text-red-400" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/></svg>`;
        }
    });

    // Lọc theo Lớp & Trạng thái
    const classFilter = document.getElementById("classFilter");
    const stateFilter = document.getElementById("stateFilter");
    const items = document.querySelectorAll(".proof-item");
    
    let currentClass = classFilter ? classFilter.value : "*";
    let currentState = stateFilter ? stateFilter.value : "all";

    function filterItems() {
        items.forEach(item => {
            const matchClass = (currentClass === "*" || item.classList.contains(currentClass.replace(".", "")));
            const matchState = (currentState === "all" || item.dataset.state === currentState);
            item.classList.toggle("hidden", !(matchClass && matchState));
        });
    }

    if (classFilter) {
        classFilter.addEventListener("change", (e) => {
            currentClass = e.target.value;
            filterItems();
        });
    }

    if (stateFilter) {
        stateFilter.addEventListener("change", (e) => {
            currentState = e.target.value;
            filterItems();
        });
    }

    // Chọn tất cả
    const selectAllBtn = document.getElementById("select-all-proofs");
    if(selectAllBtn) {
        selectAllBtn.addEventListener("change", (e) => {
            const isChecked = e.target.checked;
            document.querySelectorAll(".proof-item:not(.hidden) .proof-item-checkbox").forEach(cb => {
                cb.checked = isChecked;
            });
        });
    }

    function getSelectedIds() {
        return Array.from(document.querySelectorAll(".proof-item-checkbox:checked")).map(cb => cb.value);
    }

    // Các hàm xử lý API
    function handleApiCall(btnId, endpoint, payload, confirmMsg, loadingText) {
        const btn = document.getElementById(btnId);
        if(!btn) return;
        
        const newBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(newBtn, btn);

        newBtn.addEventListener("click", () => {
            const executeCall = () => {
                AppSwal.fire({
                    title: "Đang xử lý...",
                    html: loadingText || "Vui lòng chờ trong giây lát",
                    allowOutsideClick: false,
                    didOpen: () => AppSwal.showLoading()
                });

                fetch("/thidua/api/admin/" + endpoint, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(payload())
                })
                .then(r => r.json())
                .then(res => {
                    if(res.success) {
                        AppSwal.fire("Thành công!", res.message, "success").then(() => fetchWeekData(currentTuanId));
                    } else {
                        AppSwal.fire("Lỗi!", res.message, "error");
                    }
                })
                .catch(e => AppSwal.fire("Lỗi hệ thống!", e.message, "error"));
            };

            if (confirmMsg) {
                AppSwal.fire({
                    title: "Xác nhận",
                    text: confirmMsg,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Đồng ý",
                    cancelButtonText: "Hủy"
                }).then((result) => {
                    if (result.isConfirmed) executeCall();
                });
            } else {
                executeCall();
            }
        });
    }

    handleApiCall("delete-proofs-btn", "xoa-minh-chung-tuan", () => ({ tuan_id: currentTuanId }), "Xóa toàn bộ minh chứng của tuần này?");
    handleApiCall("regen-thumbnails-btn", "tao-lai-thumbnail-tuan", () => ({ tuan_id: currentTuanId }), false, "Đang tạo lại thumbnails...");
    handleApiCall("regen-thumbnails-year-btn", "tao-lai-thumbnail-nam", () => ({}), false, "Đang tạo thumbnails toàn bộ năm học...");
    
    // Nút lưu Cloud cần chọn checkbox
    const archiveBtn = document.getElementById("archive-selected-btn");
    if(archiveBtn) {
        const newArchive = archiveBtn.cloneNode(true);
        archiveBtn.parentNode.replaceChild(newArchive, archiveBtn);
        newArchive.addEventListener("click", () => {
            const ids = getSelectedIds();
            if(ids.length === 0) {
                AppSwal.fire("Chưa chọn file", "Vui lòng tick chọn các file Local để lưu lên Cloud.", "warning");
                return;
            }
            AppSwal.fire({
                title: "Đang tải lên Cloud...",
                html: `Đang xử lý ${ids.length} tệp. Không đóng trình duyệt.`,
                allowOutsideClick: false,
                didOpen: () => AppSwal.showLoading()
            });
            fetch("/thidua/api/archive-selected-proofs", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ ids: ids })
            }).then(r => r.json()).then(res => {
                if(res.success) AppSwal.fire("Thành công!", res.message, "success").then(() => fetchWeekData(currentTuanId));
                else AppSwal.fire("Lỗi!", res.message, "error");
            }).catch(e => AppSwal.fire("Lỗi hệ thống!", e.message, "error"));
        });
    }

    function handleRestore(btnId, payloadFn, title, confirmMsg) {
        const btn = document.getElementById(btnId);
        if(!btn) return;
        const newBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(newBtn, btn);
        newBtn.addEventListener("click", () => {
            let payload = payloadFn();
            if (payload.ids) {
                payload.ids = getSelectedIds();
                if(payload.ids.length === 0) {
                    AppSwal.fire("Chưa chọn file", "Vui lòng tick chọn các file Cloud để tải về Local.", "warning");
                    return;
                }
            }
            
            const executeRestore = () => {
                AppSwal.fire({
                    title: title,
                    html: "Đang xử lý tải về. Vui lòng không đóng trình duyệt.",
                    allowOutsideClick: false,
                    didOpen: () => AppSwal.showLoading()
                });
                fetch("/thidua/api/restore-selected-proofs", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(payload)
                }).then(r => r.json()).then(res => {
                    if(res.success) AppSwal.fire("Thành công!", res.message, "success").then(() => fetchWeekData(currentTuanId));
                    else AppSwal.fire("Lỗi!", res.message, "error");
                }).catch(e => AppSwal.fire("Lỗi hệ thống!", e.message, "error"));
            };

            if (confirmMsg) {
                AppSwal.fire({
                    title: "Xác nhận",
                    text: confirmMsg,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Đồng ý",
                    cancelButtonText: "Hủy"
                }).then((result) => {
                    if (result.isConfirmed) executeRestore();
                });
            } else {
                executeRestore();
            }
        });
    }

    handleRestore("restore-selected-btn", () => ({ ids: [] }), "Đang tải về Local...", false);
    handleRestore("restore-from-cloud-btn", () => ({ tuan_id: currentTuanId }), "Đang tải về Local (Tuần)...", "Tải minh chứng từ Cloud về Local cho tuần này?");
    handleRestore("restore-all-cloud-btn", () => ({ restore_all: true }), "Đang tải về Local (Năm)...", "Tiến trình này có thể mất nhiều thời gian. Bạn có chắc?");

    // Logic Sao lưu OneDrive
    async function startOneDriveBackup(payload) {
        try {
            // Bước 1: Lấy danh sách ID
            AppSwal.fire({
                title: "Đang phân tích...",
                html: "Đang lấy danh sách các file cần sao lưu.",
                allowOutsideClick: false,
                didOpen: () => AppSwal.showLoading()
            });

            const res = await fetch("/thidua/api/admin/api_get_proof_ids_for_backup", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            }).then(r => r.json());

            if (!res.success) {
                AppSwal.fire("Lỗi!", res.message, "error");
                return;
            }

            const ids = res.ids || [];
            if (ids.length === 0) {
                AppSwal.fire("Hoàn tất", "Không có file nào (hoặc đã sao lưu hết) để đẩy lên OneDrive.", "info");
                return;
            }

            // Bước 2: Upload tuần tự
            let successCount = 0;
            let errorCount = 0;

            for (let i = 0; i < ids.length; i++) {
                const proofId = ids[i];
                AppSwal.update({
                    title: "Đang sao lưu OneDrive",
                    html: `Đang xử lý file ${i + 1}/${ids.length}.<br><br>Thành công: ${successCount}<br>Lỗi: ${errorCount}<br><br><span class="text-sm text-red-500 font-bold">Vui lòng không đóng trình duyệt!</span>`
                });

                try {
                    const uploadRes = await fetch("/thidua/api/admin/api_backup_single_onedrive", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ id: proofId })
                    }).then(r => r.json());

                    if (uploadRes.success) {
                        successCount++;
                        // Ẩn ảnh này khỏi giao diện nếu đang hiển thị
                        const checkbox = document.querySelector(`.proof-item-checkbox[value="${proofId}"]`);
                        if (checkbox) {
                            const proofItem = checkbox.closest('.proof-item');
                            if (proofItem) proofItem.style.display = 'none';
                        }
                    } else {
                        console.error(`Lỗi upload ID ${proofId}:`, uploadRes.message);
                        errorCount++;
                    }
                } catch (e) {
                    console.error(`Network lỗi ID ${proofId}:`, e);
                    errorCount++;
                }
            }

            AppSwal.fire({
                title: "Hoàn tất sao lưu",
                html: `Đã xử lý xong.<br>Thành công: ${successCount}<br>Lỗi: ${errorCount}`,
                icon: errorCount === 0 ? "success" : "warning"
            }).then(() => fetchWeekData(currentTuanId));

        } catch (e) {
            AppSwal.fire("Lỗi hệ thống", e.message, "error");
        }
    }

    function handleOneDriveButton(btnId, payloadFn, confirmMsg) {
        const btn = document.getElementById(btnId);
        if(!btn) return;
        const newBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(newBtn, btn);
        newBtn.addEventListener("click", () => {
            let payload = payloadFn();
            if (payload.ids) {
                payload.ids = getSelectedIds();
                if(payload.ids.length === 0) {
                    AppSwal.fire("Chưa chọn file", "Vui lòng tick chọn các file cần sao lưu lên OneDrive.", "warning");
                    return;
                }
            }

            AppSwal.fire({
                title: "Xác nhận sao lưu",
                text: confirmMsg,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Bắt đầu",
                cancelButtonText: "Hủy"
            }).then((result) => {
                if (result.isConfirmed) startOneDriveBackup(payload);
            });
        });
    }

    handleOneDriveButton("backup-onedrive-selected-btn", () => ({ ids: [] }), "Bạn có chắc muốn sao lưu các file đã chọn lên OneDrive?");
    handleOneDriveButton("backup-onedrive-week-btn", () => ({ tuan_id: currentTuanId }), "Sao lưu toàn bộ minh chứng TUẦN NÀY lên OneDrive?");
    handleOneDriveButton("backup-onedrive-year-btn", () => ({ backup_all: true }), "Sao lưu TOÀN BỘ MINH CHỨNG CỦA NĂM HỌC lên OneDrive? Tiến trình này có thể mất rất nhiều thời gian!");
}

async function fetchWeekData(tuan_id) {
    if (!tuan_id) return;
    currentTuanId = tuan_id;
    const iframeParam = document.getElementById("iframe_param") ? document.getElementById("iframe_param").value : "0";
    const newUrl = window.location.pathname + "?tuan_id=" + tuan_id + (iframeParam === "1" ? "&iframe=1" : "");
    
    // Cập nhật URL trình duyệt mà không F5
    window.history.pushState({ tuan_id: tuan_id }, "", newUrl);

    const mainContent = document.getElementById("dynamic-main-content");
    if (mainContent) {
        // Thêm hiệu ứng loading mượt mà
        let loader = document.getElementById("seamless-loader");
        if (!loader) {
            loader = document.createElement("div");
            loader.id = "seamless-loader";
            loader.className = "absolute inset-0 bg-white/70 backdrop-blur-[2px] z-50 flex flex-col items-center justify-center animate-in fade-in duration-200";
            loader.innerHTML = `
                <div class="w-10 h-10 border-4 border-[#224397] border-t-transparent rounded-full animate-spin mb-3"></div>
                <p class="text-sm font-bold text-[#224397]">Đang tải dữ liệu minh chứng...</p>
            `;
            mainContent.appendChild(loader);
        }
    }

    try {
        const response = await fetch(newUrl);
        const htmlText = await response.text();
        const doc = new DOMParser().parseFromString(htmlText, "text/html");

        // Thay thế các vùng động
        const newSubfilters = doc.getElementById("dynamic-subfilters");
        const newToolbar = doc.getElementById("dynamic-toolbar");
        const newMainContent = doc.getElementById("dynamic-main-content");

        if (newSubfilters && document.getElementById("dynamic-subfilters")) {
            document.getElementById("dynamic-subfilters").innerHTML = newSubfilters.innerHTML;
        }
        if (newToolbar && document.getElementById("dynamic-toolbar")) {
            document.getElementById("dynamic-toolbar").innerHTML = newToolbar.innerHTML;
        }
        if (newMainContent && document.getElementById("dynamic-main-content")) {
            document.getElementById("dynamic-main-content").innerHTML = newMainContent.innerHTML;
        }

        // Gắn lại toàn bộ các sự kiện & Fancybox
        initProofLibraryEvents();
    } catch (error) {
        console.error("Lỗi tải dữ liệu:", error);
        AppSwal.fire("Lỗi kết nối", "Không thể tải dữ liệu tuần. Vui lòng thử lại.", "error");
    }
}

document.addEventListener("DOMContentLoaded", () => {
    initProofLibraryEvents();
});

// Xử lý khi nhấn nút Back/Forward trên trình duyệt
window.addEventListener("popstate", (e) => {
    if (e.state && e.state.tuan_id) {
        const select = document.getElementById("tuan_id_select");
        if(select) select.value = e.state.tuan_id;
        fetchWeekData(e.state.tuan_id);
    } else {
        location.reload();
    }
});
</script>
<?php require_once __DIR__ . "/partials/admin_footer.php"; ?>
