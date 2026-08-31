<?php
// File: src/views/admin_quan_ly_khao_sat.php
$page_title = 'Quản Lý Khảo Sát Ý Kiến';
require_once __DIR__ . '/partials/admin_header.php';
require_once __DIR__ . '/../../config/database.php';

try {
    $db = get_db_connection();
    $stmt_surveys = $db->query("SELECT * FROM khao_sat ORDER BY created_at DESC");
    $surveys = $stmt_surveys->fetchAll(PDO::FETCH_ASSOC);

    // Lấy danh sách lớp học để filter báo cáo
    $stmt_classes = $db->query("SELECT id, ten_lop FROM raw_lop_hoc ORDER BY ten_lop ASC");
    $classes = $stmt_classes->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Lỗi CSDL: " . $e->getMessage());
}
?>


<!-- Include SheetJS (XLSX) for elegant client-side Excel export -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<!-- Include JSZip for downloading all files as ZIP -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
<!-- Include Cropper.js for professional 21:9 banner cropping (local) -->
<link rel="stylesheet" href="/thidua/public/assets/libs/cropper.min.css" />
<script src="/thidua/public/assets/libs/cropper.min.js"></script>


<div class="flex-1 overflow-y-auto bg-transparent p-6 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <!-- TIÊU ĐỀ & NÚT THÊM -->
        <div class="flex justify-between items-center mb-6 border-b border-[#224397]/25 pb-3">
            <h3 class="text-xl font-bold text-[#224397] flex items-center gap-2 uppercase">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-ui-checks-grid" viewBox="0 0 16 16">
                    <path d="M2 10h3a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1zm9-9h3a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-3a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1zm0 9a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1h-3zm0-10a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h3a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2h-3zM2 9a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h3a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H2zm7 2a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-3a2 2 0 0 1-2-2v-3zM0 2a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2zm5.354.854a.5.5 0 1 0-.708-.708L3 3.793l-.646-.647a.5.5 0 1 0-.708.708l1 1a.5.5 0 0 0 .708 0l2-2z"/>
                </svg> Quản Lý Khảo Sát
            </h3>
            <button onclick="window.location.href='/thidua/admin/khao-sat-builder?iframe=1'" class="px-4 py-2 bg-[#224397] text-white rounded shadow-sm hover:bg-[#FAB723] hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 font-medium flex items-center gap-2 text-sm">
                <i class="bi bi-plus-circle"></i> <span class="hidden md:inline">Tạo Bài Khảo Sát Mới</span><span class="inline md:hidden">Tạo Mới</span>
            </button>
        </div>

        <!-- DANH SÁCH BÀI KHẢO SÁT ĐANG CÓ -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php if (empty($surveys)): ?>
                            <div class="col-span-1 md:col-span-2 lg:col-span-3 py-12 text-center text-slate-400 font-medium">Chưa có bài khảo sát nào trên hệ thống.</div>
                        <?php else: ?>
                            <?php foreach ($surveys as $s): ?>
                                <div class="bg-white border border-slate-200 rounded p-6 shadow-sm hover:shadow-md transition flex flex-col justify-between gap-4">
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="px-3 py-1 rounded-full text-xs font-bold shadow-sm <?= $s['loai_khao_sat'] === 'bat_buoc' ? 'bg-rose-100 text-rose-700 border border-rose-200' : 'bg-blue-100 text-blue-700 border border-blue-200' ?>">
                                                <?= $s['loai_khao_sat'] === 'bat_buoc' ? 'Bắt buộc' : 'Tự nguyện' ?>
                                            </span>
                                            <span class="text-xs font-medium text-slate-500">Hạn: <?= htmlspecialchars($s['han_nop']) ?></span>
                                        </div>
                                        <h3 class="text-lg font-bold text-slate-800 leading-snug"><?= htmlspecialchars($s['tieu_de']) ?></h3>
                                        <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed"><?= htmlspecialchars($s['mo_ta']) ?></p>
                                    </div>
                                    <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                                        <button onclick="deleteSurvey(<?= $s['id'] ?>)" class="px-3 py-1.5 bg-rose-50 text-rose-600 rounded-lg text-xs font-bold hover:bg-rose-100 transition flex items-center gap-1 border border-rose-200">
                                            <i class="bi bi-trash3"></i> Xóa
                                        </button>
                                        <div class="flex items-center gap-2">
                                            <button onclick="window.location.href='/thidua/admin/khao-sat-builder?id=<?= $s['id'] ?>&iframe=1'" class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded-lg text-xs font-bold hover:bg-slate-200 transition border border-slate-200 flex items-center gap-1">
                                                <i class="bi bi-pencil-square text-[#224397]"></i> Sửa
                                            </button>
                                            <button onclick="window.location.href='/thidua/admin/khao-sat-bao-cao?id=<?= $s['id'] ?>&iframe=1'" class="px-4 py-2 bg-[#224397] text-white rounded text-xs font-bold hover:bg-[#FAB723] hover:text-slate-900 transition shadow-sm flex items-center gap-1.5">
                                                <i class="bi bi-bar-chart-fill"></i> Xem báo cáo
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
        </div>
    </div>
</div>
<script>
function deleteSurvey(id) {
    AppSwal.fire({
        title: 'Xóa bài khảo sát?',
        text: 'Bạn có chắc chắn muốn xóa bài khảo sát này và toàn bộ dữ liệu trả lời?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Đồng ý xóa',
        cancelButtonText: 'Hủy',
        confirmButtonColor: '#ef4444'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/thidua/api/admin/khao-sat`, { 
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete', survey_id: id })
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    if (typeof showToast === 'function') {
                        showToast('Đã xóa thành công', 'success');
                    } else {
                        AppSwal.fire('Đã xóa', '', 'success');
                    }
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    AppSwal.fire('Lỗi', res.message, 'error');
                }
            });
        }
    });
}
</script>


<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>



