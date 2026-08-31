<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$danh_sach_hop_le = isset($_SESSION['import_preview_valid_chvp']) ? $_SESSION['import_preview_valid_chvp'] : [];
$danh_sach_khong_hop_le = isset($_SESSION['import_preview_invalid_chvp']) ? $_SESSION['import_preview_invalid_chvp'] : [];

$page_title = 'Import DS Vi Phạm';
require_once __DIR__ . '/partials/admin_header.php';
?>

<div class="flex-1 overflow-y-auto bg-slate-50 p-6 min-h-screen">
    <div class="max-w-6xl mx-auto">
        
        <!-- Header của Trang -->
        <div class="flex justify-between items-center mb-6 border-b border-slate-200 pb-3">
            <h3 class="text-xl font-bold text-[#224397] flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-check-fill text-emerald-600" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1m1.354 4.354-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 9.793l2.646-2.647a.5.5 0 0 1 .708.708"/></svg> XEM TRƯỚC DANH SÁCH VI PHẠM
            </h3>
        </div>

        <?php if (!empty($danh_sach_khong_hop_le)): ?>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 mb-6">
            <div class="px-6 py-4 border-b border-slate-200 bg-red-50 rounded-t-xl font-semibold text-red-700">
                <h4 class="mb-0 flex items-center text-lg"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-circle-fill mr-2" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z"/></svg>Dữ Liệu Lỗi (Sẽ bị bỏ qua)</h4>
            </div>
            <div class="p-6">
                <p class="mb-4 text-slate-600 text-sm">Vui lòng sửa các lỗi sau trong file Excel của bạn và tải lên lại nếu cần.</p>
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-left text-[13px] text-slate-600 border-collapse border border-slate-300 relative">
                        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold sticky top-0 z-10 border-b border-slate-300">
                            <tr>
                                <th class="py-3 px-4 w-20 text-center border-r border-slate-300">Dòng</th>
                                <th class="py-3 px-4 w-48 border-r border-slate-300">Nhóm</th>
                                <th class="py-3 px-4 border-r border-slate-300">Tên Nhóm Vi Phạm</th>
                                <th class="py-3 px-4 w-24 text-center border-r border-slate-300">Điểm Trừ</th>
                                <th class="py-3 px-4 w-1/3">Chi Tiết Lỗi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-300">
                            <?php foreach ($danh_sach_khong_hop_le as $row): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-3 px-4 text-center border-r border-slate-300 text-slate-600"><?php echo htmlspecialchars($row['line_number']); ?></td>
                                <td class="py-3 px-4 border-r border-slate-300">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700 border border-red-200 whitespace-nowrap">
                                        <?php echo htmlspecialchars($row['nhom']); ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 border-r border-slate-300 text-slate-800"><?php echo htmlspecialchars($row['ten_vp']); ?></td>
                                <td class="py-3 px-4 text-center border-r border-slate-300">
                                    <span class="font-bold text-red-600 bg-red-50 px-2.5 py-1 rounded border border-red-200">
                                        <?php echo htmlspecialchars($row['diem_tru']); ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <ul class="list-disc pl-4 text-red-600 space-y-1">
                                        <?php foreach ($row['errors'] as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 mb-6">
            <div class="px-6 py-4 border-b border-slate-200 bg-emerald-50 rounded-t-xl font-semibold text-emerald-700">
                <h4 class="mb-0 flex items-center text-lg"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check-circle-fill mr-2" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>Dữ Liệu Hợp Lệ</h4>
            </div>
            <div class="p-6">
                <?php if (empty($danh_sach_hop_le)): ?>
                    <div class="p-4 mb-4 rounded-lg border bg-amber-50 text-amber-800 border-amber-200 text-sm flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-exclamation-triangle-fill text-amber-500" viewBox="0 0 16 16"><path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/></svg> Không có dữ liệu hợp lệ nào để import.
                    </div>
                <?php else: ?>
                    <p class="mb-4 text-slate-600 text-sm">Có <strong><?php echo count($danh_sach_hop_le); ?></strong> mục hợp lệ sẽ được thêm hoặc cập nhật vào hệ thống.</p>
                    <div class="overflow-x-auto w-full mb-6">
                        <table class="w-full text-left text-[13px] text-slate-600 border-collapse border border-slate-300 relative">
                            <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold sticky top-0 z-10 border-b border-slate-300">
                                <tr>
                                    <th class="py-3 px-4 w-20 text-center border-r border-slate-300">Dòng</th>
                                    <th class="py-3 px-4 w-48 border-r border-slate-300">Nhóm</th>
                                    <th class="py-3 px-4 border-r border-slate-300">Tên Nhóm Vi Phạm</th>
                                    <th class="py-3 px-4 w-24 text-center">Điểm Trừ</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-300">
                                <?php foreach ($danh_sach_hop_le as $row): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-3 px-4 text-center border-r border-slate-300 text-slate-600"><?php echo htmlspecialchars($row['line_number']); ?></td>
                                    <td class="py-3 px-4 border-r border-slate-300">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-[#224397] border border-blue-200 whitespace-nowrap">
                                            <?php echo htmlspecialchars($row['nhom']); ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 border-r border-slate-300 text-slate-800 font-medium"><?php echo htmlspecialchars($row['ten_vp']); ?></td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="font-bold text-red-600 bg-red-50 px-2.5 py-1 rounded border border-red-200">
                                            <?php echo htmlspecialchars($row['diem_tru']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 mt-4">
                    <a href="/thidua/admin/cau-hinh-vi-pham<?= isset($_GET['iframe']) ? '?iframe=1' : '' ?>" class="px-4 py-2 bg-white border border-slate-300 rounded text-slate-600 hover:bg-slate-100 transition-all duration-300 font-medium flex items-center gap-2 text-sm shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg> Hủy Bỏ
                    </a>
                    <?php if (!empty($danh_sach_hop_le)): ?>
                    <form action="/thidua/luu-import-cau-hinh-vi-pham<?= isset($_GET['iframe']) ? '?iframe=1' : '' ?>" method="POST" class="inline-block m-0 p-0">
                        <button type="submit" class="px-4 py-2 bg-white border border-blue-200 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:-translate-y-0.5 hover:shadow-md transition-all duration-300 font-medium flex items-center gap-2 text-sm shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-cloud-arrow-up-fill" viewBox="0 0 16 16"><path d="M8 2a5.53 5.53 0 0 0-3.594 1.342c-.766.66-1.321 1.52-1.464 2.383C1.266 6.095 0 7.555 0 9.318 0 11.366 1.708 13 3.781 13h8.906C14.502 13 16 11.57 16 9.773c0-1.636-1.242-2.969-2.834-3.194C12.923 3.999 10.69 2 8 2m2.354 5.146a.5.5 0 0 1-.708.708L8.5 6.707V10.5a.5.5 0 0 1-1 0V6.707L6.354 7.854a.5.5 0 1 1-.708-.708l2-2a.5.5 0 0 1 .708 0z"/></svg> Lưu Dữ Liệu Hợp Lệ
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>
