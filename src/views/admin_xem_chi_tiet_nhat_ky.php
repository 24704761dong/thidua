<?php
// Đồng bộ màu sắc, thêm lịch sử từ chối, làm rõ border
$page_title = "Chi Tiết Sổ Nhật Kỳ";
require_once __DIR__ . "/partials/admin_header.php";

$nhat_ky = $nhat_ky ?? [];
$totals  = $totals ?? ["tot"=>0,"kha"=>0,"tb"=>0,"yeu"=>0];
$details = $details ?? [];
$proofs  = $proofs ?? [];
$submitted_at_formatted = $submitted_at_formatted ?? null;
$submitted_at_display = $submitted_at_formatted ?: "Không xác định";

// CÁC HÀM TIỆN ÍCH
function is_image($type, $filename = ''){
  $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
  if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'heic', 'heif'])) return true;
  if(empty($type) && empty($filename)) return true; // Fallback cho ảnh up từ Zalo bản cũ
  if(empty($type)) return false;
  return in_array(strtolower($type),["image/jpeg","image/png","image/gif","image/webp","image/bmp", "image/heic", "image/heif"]);
}
function is_pdf($type){
  return strtolower($type) === "application/pdf";
}

$status_colors = [
    "chua_nop" => "bg-slate-100 text-slate-700 border-slate-300",
    "nhap" => "bg-orange-100 text-orange-800 border-orange-300",
    "da_gui" => "bg-amber-100 text-amber-800 border-amber-300",
    "da_duyet" => "bg-green-100 text-green-800 border-green-300",
    "tu_choi" => "bg-red-100 text-red-800 border-red-300"
];
$status_labels = [
    "chua_nop" => "Chưa nộp",
    "nhap" => "Đang nháp",
    "da_gui" => "Chờ duyệt",
    "da_duyet" => "Đã duyệt",
    "tu_choi" => "Đã bị từ chối"
];
$st_color = $status_colors[$nhat_ky["trang_thai"] ?? "chua_nop"] ?? "bg-slate-100 text-slate-700 border-slate-300";
$st_label = $status_labels[$nhat_ky["trang_thai"] ?? "chua_nop"] ?? "Chưa nộp";

// Lịch sử từ chối
$ghi_chu_admin = $nhat_ky["ghi_chu_admin"] ?? "";
?>

<link href="/thidua/public/assets/libs/fancybox.css" rel="stylesheet">
<style>
/* Fancybox custom */
.proof-item { transition: all 0.3s; }
.proof-item:hover { transform: translateY(-3px); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15); border-color: #cbd5e1; }
:root {
    --accent: #FAB723;
    --primary: #224397;
}
.btn-primary-custom {
    background-color: var(--primary);
    color: white;
}
.btn-primary-custom:hover {
    background-color: #1a3478;
    color: white;
}
.text-[#224397] {
    color: var(--primary);
}
</style>

<div class="w-full max-w-6xl mx-auto px-4 py-6" id="detail-container" data-id="<?= htmlspecialchars($nhat_ky["id"]) ?>">
    <!-- Header -->
    <div class="mb-6">
        <h3 class="text-2xl font-bold text-[#224397] flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-journal-text" viewBox="0 0 16 16">#document</svg>
            Chi Tiết Sổ Nhật Kỳ
        </h3>
        <p class="text-slate-500 mt-1">Lớp: <span class="font-bold text-slate-700"><?= htmlspecialchars($nhat_ky["ten_lop"] ?? "") ?></span> - Tuần: <span class="font-bold text-slate-700"><?= htmlspecialchars($nhat_ky["ten_tuan"] ?? "") ?></span></p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Cột Trái -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- THÔNG TIN CƠ BẢN -->
            <div class="bg-white rounded-xl shadow-sm border border-[#224397]/25 overflow-hidden">
                <div class="bg-[rgba(34,67,151,0.08)] px-5 py-3 border-b border-[#224397]/25 font-bold text-[#224397] flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-info-circle" viewBox="0 0 16 16">#document</svg> Thông tin cơ bản
                </div>
                <div class="p-5 space-y-4">
                    <div class="flex justify-between items-center pb-3 border-b border-slate-200">
                        <span class="text-slate-600 font-medium">Trạng thái:</span>
                        <span class="px-3 py-1 rounded-full text-xs font-bold border <?= $st_color ?>"><?= $st_label ?></span>
                    </div>
                    <div class="flex justify-between items-center pb-3 border-b border-slate-200">
                        <span class="text-slate-600 font-medium">Người gửi:</span>
                        <span class="font-bold text-slate-800"><?= htmlspecialchars($nhat_ky["ten_ctv"] ?? "N/A") ?></span>
                    </div>
                    <div class="flex justify-between items-center pb-3 border-b border-slate-200">
                        <span class="text-slate-600 font-medium">Thời gian gửi:</span>
                        <span class="font-bold text-slate-800"><?= $submitted_at_display ?></span>
                    </div>
                    
                    <?php if(!empty($ghi_chu_admin)): ?>
                    <div class="pb-3 border-b border-slate-200">
                        <span class="text-red-600 font-bold block mb-2"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-clock-history" viewBox="0 0 16 16">#document</svg> Lịch sử từ chối:</span>
                        <div class="bg-red-50 text-red-800 p-3 rounded-lg text-sm border border-red-200 whitespace-pre-line font-medium"><?= htmlspecialchars($ghi_chu_admin) ?></div>
                    </div>
                    <?php endif; ?>

                    <div>
                        <span class="text-slate-600 font-medium block mb-2">Ghi chú của lớp:</span>
                        <div class="bg-amber-50 text-amber-800 p-3 rounded-lg text-sm border border-amber-200 font-medium">
                            <?= !empty($nhat_ky["ghi_chu"]) ? nl2br(htmlspecialchars($nhat_ky["ghi_chu"])) : "<em class=\"text-slate-400 font-normal\">Không có ghi chú</em>" ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TỔNG QUAN ĐIỂM -->
            <div class="bg-white rounded-xl shadow-sm border border-[#224397]/25 overflow-hidden">
                <div class="bg-[rgba(34,67,151,0.08)] px-5 py-3 border-b border-[#224397]/25 font-bold text-[#224397] flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-pie-chart" viewBox="0 0 16 16">#document</svg> Tổng quan điểm
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-green-50 rounded-lg p-3 text-center border border-green-200 shadow-sm">
                            <div class="text-sm text-green-700 font-bold mb-1">Tốt</div>
                            <div class="text-2xl font-black text-green-700"><?= $totals["tot"] ?></div>
                        </div>
                        <div class="bg-blue-50 rounded-lg p-3 text-center border border-blue-200 shadow-sm">
                            <div class="text-sm text-blue-700 font-bold mb-1">Khá</div>
                            <div class="text-2xl font-black text-blue-700"><?= $totals["kha"] ?></div>
                        </div>
                        <div class="bg-amber-50 rounded-lg p-3 text-center border border-amber-200 shadow-sm">
                            <div class="text-sm text-amber-700 font-bold mb-1">TB</div>
                            <div class="text-2xl font-black text-amber-700"><?= $totals["tb"] ?></div>
                        </div>
                        <div class="bg-red-50 rounded-lg p-3 text-center border border-red-200 shadow-sm">
                            <div class="text-sm text-red-700 font-bold mb-1">Yếu</div>
                            <div class="text-2xl font-black text-red-700"><?= $totals["yeu"] ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- HÀNH ĐỘNG -->
            <div class="bg-white rounded-xl shadow-sm border border-[#224397]/25 overflow-hidden hidden lg:block sticky top-6">
                <div class="bg-[rgba(34,67,151,0.08)] px-5 py-3 border-b border-[#224397]/25 font-bold text-[#224397] flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-joystick" viewBox="0 0 16 16">#document</svg> Hành động
                </div>
                <div class="p-5 text-center">
                    <?php if($nhat_ky["trang_thai"] === "da_gui"): ?>
                        <p class="text-slate-600 text-sm mb-4 font-medium">Vui lòng xem xét và ra quyết định cho sổ này.</p>
                        <div class="flex flex-col gap-3">
                            <button class="w-full px-4 py-2.5 btn-primary-custom font-bold rounded-lg transition shadow flex items-center justify-center gap-2 border border-[#1a3478]" id="approve-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16">#document</svg> Duyệt sổ
                            </button>
                            <button class="w-full px-4 py-2.5 bg-red-50 text-red-700 font-bold rounded-lg hover:bg-red-100 border border-red-300 transition shadow-sm flex items-center justify-center gap-2" id="reject-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-circle" viewBox="0 0 16 16">#document</svg> Từ chối
                            </button>
                        </div>
                    <?php elseif($nhat_ky["trang_thai"] === "da_duyet"): ?>
                        <div class="p-4 mb-4 rounded-lg bg-green-50 text-green-800 border border-green-300 flex items-center justify-center gap-2 font-bold shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg> Đã duyệt
                        </div>
                        <button class="w-full px-4 py-2.5 bg-slate-50 text-slate-700 font-bold rounded-lg hover:bg-amber-50 hover:text-amber-800 hover:border-amber-300 transition shadow-sm flex items-center justify-center gap-2 border border-[#224397]/25" id="unapprove-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-arrow-counterclockwise" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 3a5 5 0 1 1-4.546 2.914.5.5 0 0 0-.908-.417A6 6 0 1 0 8 2z"/><path d="M8 4.466V.534a.25.25 0 0 0-.41-.192L5.23 2.308a.25.25 0 0 0 0 .384l2.36 1.966A.25.25 0 0 0 8 4.466"/></svg> Hủy duyệt
                        </button>
                    <?php elseif($nhat_ky["trang_thai"] === "tu_choi"): ?>
                        <div class="p-4 rounded-lg bg-red-50 text-red-800 border border-red-300 flex items-center justify-center gap-2 font-bold shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z"/></svg> Sổ đã bị từ chối
                        </div>
                    <?php else: ?>
                        <div class="p-4 rounded-lg bg-orange-50 text-orange-800 border border-orange-300 flex items-center justify-center gap-2 font-bold shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16"><path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/><path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/></svg> Đang lưu nháp (chưa nộp)
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- Cột Phải -->
        <div class="lg:col-span-2 space-y-6">
            
            <?php 
            $loai_so_map = [
                "sdb_ck" => "Sổ đầu bài - Chính khóa",
                "sdb_nk" => "Sổ đầu bài - Ngoại khóa",
                "sdb_tt" => "Sổ Nhật kỳ"
            ];
            foreach($loai_so_map as $k => $label): 
                $d = $details[$k] ?? null;
                $p = $proofs[$k] ?? [];
            ?>
            <div class="bg-white rounded-xl shadow-sm border border-[#224397]/25 overflow-hidden">
                <div class="bg-[rgba(34,67,151,0.08)] px-5 py-4 border-b border-[#224397]/25 font-bold text-[#224397] flex items-center gap-2 text-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-journal-bookmark-fill" viewBox="0 0 16 16">#document</svg> <?= $label ?>
                </div>
                <div class="p-5">
                    
                    <?php if($k !== "sdb_tt"): ?>
                        <div class="flex flex-wrap gap-3 mb-5">
                            <span class="px-4 py-1.5 rounded-md bg-green-50 border border-green-200 text-green-800 font-bold text-sm shadow-sm">Tốt: <?= (int)($d["so_tiet_tot"]??0) ?></span>
                            <span class="px-4 py-1.5 rounded-md bg-blue-50 border border-blue-200 text-blue-800 font-bold text-sm shadow-sm">Khá: <?= (int)($d["so_tiet_kha"]??0) ?></span>
                            <span class="px-4 py-1.5 rounded-md bg-amber-50 border border-amber-200 text-amber-800 font-bold text-sm shadow-sm">TB: <?= (int)($d["so_tiet_tb"]??0) ?></span>
                            <span class="px-4 py-1.5 rounded-md bg-red-50 border border-red-200 text-red-800 font-bold text-sm shadow-sm">Yếu: <?= (int)($d["so_tiet_yeu"]??0) ?></span>
                        </div>
                    <?php endif; ?>

                    <h6 class="font-bold text-slate-700 mb-3 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-paperclip text-slate-500" viewBox="0 0 16 16">#document</svg> Minh chứng đính kèm:
                    </h6>

                    <?php if(empty($p)): ?>
                        <div class="p-5 bg-slate-50 rounded-lg text-slate-500 text-sm text-center border border-dashed border-[#224397]/25 font-medium">
                            Không có minh chứng cho mục này.
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 proof-gallery">
                            <?php foreach($p as $f): 
                                $is_cloud = ($f["storage_driver"] === "cloud" || $f["storage_driver"] === "r2");
                                $is_onedrive = ($f["storage_driver"] === "onedrive");
                                $name = htmlspecialchars($f["original_filename"] ?? 'Ảnh minh chứng');
                                $attrs = "data-fancybox=\"gallery-{$k}\" data-caption=\"{$name}\"";
                                
                                $path = "";
                                if ($is_onedrive) {
                                    $path = "/thidua/api/get-presigned-url?key=" . urlencode($f["cloud_key"]) . "&driver=onedrive";
                                    $attrs .= is_pdf($f["file_type"]) ? " data-type=\"pdf\"" : " data-type=\"image\"";
                                } elseif ($is_cloud) {
                                    $path = "/thidua/api/get-presigned-url?key=" . urlencode($f["cloud_key"]);
                                    $attrs .= is_pdf($f["file_type"]) ? " data-type=\"pdf\"" : " data-type=\"image\"";
                                } else {
                                    $path = "/thidua/" . htmlspecialchars($f["file_path"]);
                                    $attrs .= is_pdf($f["file_type"]) ? " data-type=\"pdf\"" : " data-type=\"image\"";
                                }

                                $thumb = "";
                                if (!$is_cloud && !$is_onedrive) {
                                    if(!empty($f["thumbnail_path"])) $thumb = "/thidua/".$f["thumbnail_path"];
                                    else if(is_image($f["file_type"], $f["original_filename"] ?? '')) $thumb = "/thidua/".$f["file_path"];
                                } else {
                                    if(is_image($f["file_type"], $f["original_filename"] ?? '')) $thumb = $path;
                                }
                                // Đã bật tải ảnh gốc từ Cloud/OneDrive theo yêu cầu vì số lượng ảnh ít
                            ?>
                            <div class="proof-item bg-white border border-[#224397]/25 rounded-xl overflow-hidden relative shadow-sm cursor-pointer">
                                <?php if($is_onedrive): ?>
                                    <div class="absolute top-2 left-2 bg-indigo-100/90 p-1.5 rounded-full text-indigo-800 text-xs shadow-md z-10" title="Lưu trên OneDrive">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-microsoft" viewBox="0 0 16 16"><path d="M7.462 0H0v7.19h7.462V0zM16 0H8.538v7.19H16V0zM7.462 8.211H0V16h7.462V8.211zm8.538 0H8.538V16H16V8.211z"/></svg>
                                    </div>
                                <?php elseif($is_cloud): ?>
                                    <div class="absolute top-2 left-2 bg-white/90 p-1.5 rounded-full text-blue-600 text-xs shadow-md z-10" title="Lưu trên Cloud">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-cloud-check-fill" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M10.854 7.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 9.793l2.646-2.647a.5.5 0 0 1 .708 0z"/><path d="M4.406 3.342A5.53 5.53 0 0 1 8 2c2.69 0 4.923 2 5.166 4.579C14.758 6.804 16 8.137 16 9.773 16 11.569 14.502 13 12.687 13H3.781C1.708 13 0 11.366 0 9.318c0-1.763 1.266-3.223 2.942-3.593.143-.863.698-1.723 1.464-2.383z"/></svg>
                                    </div>
                                <?php endif; ?>

                                <a href="<?= $path ?>" <?= $attrs ?> class="block w-full h-full">
                                    <div class="h-32 bg-slate-100 flex items-center justify-center overflow-hidden border-b border-slate-200">
                                        <?php if(is_image($f["file_type"], $f["original_filename"] ?? '')): ?>
                                            <?php if($thumb): ?>
                                                <img src="<?= htmlspecialchars($thumb) ?>" class="w-full h-full object-cover" loading="lazy">
                                            <?php else: ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-image text-4xl text-slate-300" viewBox="0 0 16 16">#document</svg>
                                            <?php endif; ?>
                                        <?php elseif(is_pdf($f["file_type"])): ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-pdf-fill text-4xl text-red-400" viewBox="0 0 16 16">#document</svg>
                                        <?php else: ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-text-fill text-4xl text-slate-400" viewBox="0 0 16 16">#document</svg>
                                        <?php endif; ?>
                                    </div>
                                    <div class="p-2.5 bg-slate-50">
                                        <p class="text-[13px] font-bold text-slate-700 truncate text-center" title="<?= $name ?>"><?= $name ?></p>
                                    </div>
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
            <?php endforeach; ?>

        </div>
    </div>
</div>

<!-- Nút Hành Động (Mobile) -->
<div class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-[#224397]/25 p-4 shadow-[0_-10px_15px_-3px_rgba(0,0,0,0.1)] z-50">
    <?php if($nhat_ky["trang_thai"] === "da_gui"): ?>
        <div class="flex gap-3">
            <button class="flex-1 py-3 btn-primary-custom font-bold rounded-lg border border-[#1a3478]" id="approve-btn-m">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16">#document</svg> Duyệt
            </button>
            <button class="flex-1 py-3 bg-red-50 text-red-700 font-bold rounded-lg border border-red-300" id="reject-btn-m">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-circle" viewBox="0 0 16 16">#document</svg> Từ chối
            </button>
        </div>
    <?php elseif($nhat_ky["trang_thai"] === "da_duyet"): ?>
        <button class="w-full py-3 bg-slate-50 text-slate-700 font-bold rounded-lg border border-[#224397]/25" id="unapprove-btn-m">
            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-arrow-counterclockwise" viewBox="0 0 16 16">#document</svg> Hủy duyệt
        </button>
    <?php elseif($nhat_ky["trang_thai"] === "tu_choi"): ?>
        <div class="w-full py-3 bg-red-50 text-red-800 font-bold rounded-lg border border-red-300 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-circle-fill" viewBox="0 0 16 16">#document</svg> Đã bị từ chối
        </div>
    <?php else: ?>
        <div class="w-full py-3 bg-orange-50 text-orange-800 font-bold rounded-lg border border-orange-300 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">#document</svg> Đang lưu nháp (chưa nộp)
        </div>
    <?php endif; ?>
</div>


<script src="/thidua/public/assets/libs/fancybox.umd.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  Fancybox.bind("[data-fancybox]", {
    groupAll: false,
    dragToClose: true,
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
    },
    Thumbs: { type: "classic" }
  });

  const id = document.getElementById("detail-container").dataset.id;
  
  const bindAction = (btnId, action) => {
    const btn = document.getElementById(btnId);
    if(btn) {
        btn.addEventListener("click", () => {
            if(action === "reject") {
                AppSwal.fire({
                    title: "Từ chối sổ nhật kỳ",
                    text: "Nhập lý do từ chối (bắt buộc):",
                    input: "text",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Từ chối",
                    cancelButtonText: "Hủy",
                    customClass: {
                        input: '!w-[90%] !mx-auto !px-4 !py-3 !border !border-solid !border-[#224397]/25 !rounded-lg !shadow-sm focus:!outline-none focus:!border-[#224397] focus:!ring-1 focus:!ring-[#224397] !font-medium !text-slate-700 !mt-4 !text-base',
                        confirmButton: '!bg-[#224397] !text-white !px-6 !py-2.5 !rounded-lg !font-bold',
                        cancelButton: '!bg-white !text-slate-700 !border !border-slate-300 !px-6 !py-2.5 !rounded-lg !font-bold',
                        actions: '!mt-6 !flex !gap-4 !w-full !justify-center'
                    },
                    inputValidator: (value) => {
                        if (!value || !value.trim()) {
                            return "Bạn cần nhập lý do từ chối!";
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        executeAction(action, result.value);
                    }
                });
            } else {
                const msgs = {
                    "approve": {title: "Duyệt sổ nhật kỳ?", text: "Bạn có chắc chắn muốn duyệt?", icon: "question"},
                    "unapprove": {title: "Hủy duyệt?", text: "Sổ sẽ quay về trạng thái Chờ duyệt.", icon: "warning"}
                };
                AppSwal.fire({
                    title: msgs[action].title,
                    text: msgs[action].text,
                    icon: msgs[action].icon,
                    showCancelButton: true,
                    confirmButtonText: "Đồng ý",
                    cancelButtonText: "Hủy"
                }).then((result) => {
                    if (result.isConfirmed) {
                        executeAction(action, null);
                    }
                });
            }
        });
    }
  };

  async function executeAction(action, note) {
      const btns = ["approve-btn", "reject-btn", "unapprove-btn", "approve-btn-m", "reject-btn-m", "unapprove-btn-m"];
      btns.forEach(b => { if(document.getElementById(b)) document.getElementById(b).disabled = true; });

      try {
          const res = await fetch("/thidua/api/admin/xu-ly-nhat-ky", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({ nhat_ky_id: id, action: action, ghi_chu: note })
          });
          const j = await res.json();
          if(j.success) {
              AppSwal.fire({ title: "Thành công!", text: j.message, icon: "success" }).then(() => { 
                  // Bắn sự kiện reload iframe cha nếu cần (mặc định reload hiện tại)
                  location.reload(); 
              });
          } else {
              AppSwal.fire({ title: "Lỗi!", text: j.message, icon: "error" });
              btns.forEach(b => { if(document.getElementById(b)) document.getElementById(b).disabled = false; });
          }
      } catch(e) {
          AppSwal.fire({ title: "Lỗi hệ thống!", text: e.message, icon: "error" });
          btns.forEach(b => { if(document.getElementById(b)) document.getElementById(b).disabled = false; });
      }
  }

  bindAction("approve-btn", "approve");
  bindAction("reject-btn", "reject");
  bindAction("unapprove-btn", "unapprove");
  bindAction("approve-btn-m", "approve");
  bindAction("reject-btn-m", "reject");
  bindAction("unapprove-btn-m", "unapprove");
});
</script>

<?php require_once __DIR__ . "/partials/admin_footer.php"; ?>

