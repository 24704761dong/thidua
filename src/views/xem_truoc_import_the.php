<?php
// File: src/views/xem_truoc_import_the.php (Đã đồng bộ Premium Tailwind UI & Iframe Preservation)

$page_title = 'Xác Nhận Dữ Liệu Import';
require_once __DIR__ . '/partials/admin_header.php';

// Lấy dữ liệu xem trước và log đã được lưu trong SESSION
$preview_data = $_SESSION['import_preview_data'] ?? [];
$import_log = $_SESSION['import_log'] ?? [];
$valid_rows = $import_log['valid_rows'] ?? 0;
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
            <svg xmlns="http://www.w3.org/2000/svg" width="1.3em" height="1.3em" fill="currentColor" class="bi bi-file-earmark-check-fill text-[#FAB723]" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1m1.354 4.354-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 9.793l2.646-2.647a.5.5 0 0 1 .708.708"/></svg>
            Xác Nhận Dữ Liệu Import
        </h1>
        <div>
            <a href="/thidua/admin/the-hoc-sinh/nhap-file-cap-nhat?action=cancel&iframe=1" class="px-4 py-2 bg-white border border-slate-300 rounded-xl text-slate-700 hover:bg-slate-50 hover:text-[#224397] transition-all duration-200 font-bold flex items-center gap-1.5 text-sm shadow-sm text-decoration-none">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="currentColor" class="bi bi-x-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/></svg> 
                Hủy và quay lại
            </a>
        </div>
    </div>

    <!-- TÓM TẮT QUÁ TRÌNH QUÉT FILE -->
    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 mb-8 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 font-bold text-[#224397] uppercase tracking-wide text-sm flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-shield-check text-[#FAB723]" viewBox="0 0 16 16"><path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533.12.057.218.095.293.118a1 1 0 0 0 .102.025 1 1 0 0 0 .102-.025c.075-.023.173-.061.293-.118.241-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C10.277 1.144 8.76 1 8 1s-2.278.144-2.662.59zM8 12.5a10 10 0 0 1-.553-.332c-.221-.154-.48-.358-.756-.606-1.342-1.207-2.637-3.238-2.14-7.408.577-.187 1.55-.472 2.535-.718C7.545 3.298 7.828 3.272 8 3.272c.172 0 .455.026.886.164.985.246 1.958.531 2.535.718.497 4.17-.798 6.201-2.14 7.408-.276.248-.535.452-.756.606A10 10 0 0 1 8 12.5"/><path d="M10.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0"/></svg>
            Tóm Tắt Quá Trình Quét File
        </div>
        <div class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-6 text-center shadow-sm">
                    <div class="text-3xl font-bold text-[#224397] mb-2"><?php echo htmlspecialchars($import_log['total_rows'] ?? 0); ?></div>
                    <div class="text-xs font-bold text-slate-600 uppercase tracking-wide">Tổng số dòng đã quét</div>
                </div>
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-6 text-center shadow-sm">
                    <div class="text-3xl font-bold text-emerald-600 mb-2"><?php echo htmlspecialchars($valid_rows); ?></div>
                    <div class="text-xs font-bold text-emerald-800 uppercase tracking-wide">Dòng hợp lệ (Sẽ được cập nhật)</div>
                </div>
                <div class="bg-red-50 border border-red-200 rounded-xl p-6 text-center shadow-sm">
                    <div class="text-3xl font-bold text-red-600 mb-2"><?php echo htmlspecialchars(($import_log['not_found_rows'] ?? 0) + ($import_log['empty_ma_hs_rows'] ?? 0)); ?></div>
                    <div class="text-xs font-bold text-red-800 uppercase tracking-wide">Dòng bị lỗi (Sẽ bị bỏ qua)</div>
                </div>
            </div>
            
            <p class="text-slate-600 text-sm mb-0 leading-relaxed bg-indigo-50/50 border border-indigo-100 p-4 rounded-xl">
                Hệ thống đã đọc file Excel của bạn. Vui lòng kiểm tra kỹ danh sách chi tiết bên dưới. Chỉ những dòng có trạng thái <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 mx-1">HỢP LỆ</span> mới được xử lý.
            </p>
        </div>
    </div>

    <!-- BẢNG CHI TIẾT DỮ LIỆU ĐÃ QUÉT -->
    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 font-bold text-[#224397] uppercase tracking-wide text-sm flex justify-between items-center">
            <span>Chi Tiết Dữ Liệu Đã Quét</span>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-[#224397] border border-blue-200">
                <?php echo count($preview_data); ?> dòng
            </span>
        </div>
        
        <div class="p-0 overflow-x-auto w-full">
            <table class="w-full text-left text-sm text-slate-600 border-collapse border border-slate-200 [&_th]:bg-slate-100 [&_th]:text-[#224397] [&_th]:font-bold [&_th]:p-4 [&_th]:border [&_th]:border-slate-200 [&_td]:p-4 [&_td]:border [&_td]:border-slate-200 [&_tr:hover]:bg-slate-50 mb-0">
                <thead class="sticky top-0 z-10 shadow-sm">
                    <tr class="text-center bg-slate-100">
                        <th class="w-12">STT</th>
                        <th class="w-36">Mã Học Sinh (File)</th>
                        <th>Họ Tên (File)</th>
                        <th>Ảnh Thẻ (Mới)</th>
                        <th>Mã MOET (Mới)</th>
                        <th class="w-28">Trạng Thái</th>
                        <th>Chi Tiết</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($preview_data)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-slate-500 py-12 font-medium text-base">Không có dữ liệu để xem trước.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($preview_data as $index => $row): ?>
                            <tr class="<?php echo $row['status'] === 'HỢP LỆ' ? 'bg-white hover:bg-emerald-50/30' : 'bg-red-50/20 hover:bg-red-50/40'; ?>">
                                <td class="text-center font-medium text-slate-700"><?php echo $index + 1; ?></td>
                                <td class="text-center"><code class="px-2 py-1 bg-slate-100 rounded text-slate-800 font-mono font-bold"><?php echo htmlspecialchars($row['ma_hoc_sinh']); ?></code></td>
                                <td class="font-bold text-slate-800"><?php echo htmlspecialchars($row['ho_ten']); ?></td>
                                <td class="font-medium text-[#224397]"><?php echo htmlspecialchars($row['anh_the']); ?></td>
                                <td class="font-mono text-slate-700"><?php echo htmlspecialchars($row['ma_moet']); ?></td>
                                <td class="text-center">
                                    <?php if ($row['status'] === 'HỢP LỆ'): ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-sm">HỢP LỆ</span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold bg-red-100 text-red-800 border border-red-300 shadow-sm">LỖI</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-xs text-slate-600 font-medium"><?php echo htmlspecialchars($row['message']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="p-6 border-t border-slate-200 bg-slate-50 flex justify-end">
            <form action="/thidua/admin/the-hoc-sinh/nhap-file-cap-nhat?iframe=1" method="POST" class="m-0">
                <input type="hidden" name="action" value="confirm_import">
                <button type="submit" class="px-8 py-3 bg-[#224397] hover:bg-[#224397]/90 text-white font-bold rounded-xl text-sm shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all flex items-center gap-2 disabled:opacity-50 disabled:pointer-events-none" <?php if ($valid_rows === 0) echo 'disabled'; ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" width="1.3em" height="1.3em" fill="currentColor" class="bi bi-check-circle-fill text-[#FAB723]" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg> 
                    Xác Nhận và Cập Nhật <?php echo $valid_rows; ?> Mục
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>
