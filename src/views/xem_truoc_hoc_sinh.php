<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$danh_sach_hop_le = isset($_SESSION['import_preview_valid']) ? $_SESSION['import_preview_valid'] : [];
$danh_sach_khong_hop_le = isset($_SESSION['import_preview_invalid']) ? $_SESSION['import_preview_invalid'] : [];

$page_title = 'Xem Trước Dữ Liệu Nhập Học Sinh';
require_once __DIR__ . '/partials/admin_header.php';
?>

<div class="container-fluid p-4">
    <!-- Header Page -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-[#224397] flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-spreadsheet" viewBox="0 0 16 16"><path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V9H3V2a1 1 0 0 1 1-1h5.5zM3 12v-2h2v2zm0 1h2v2H4a1 1 0 0 1-1-1zm3 2v-2h3v2zm4 0v-2h3v1a1 1 0 0 1-1 1zm3-3h-3v-2h3zm-7 0v-2h3v2z"/></svg>
                Xem Trước Dữ Liệu
            </h2>
            <p class="text-slate-500 text-sm mt-1">Kiểm tra thông tin trước khi lưu vào hệ thống</p>
        </div>
    </div>

    <!-- Khối Dữ Liệu Không Hợp Lệ -->
    <?php if (!empty($danh_sach_khong_hop_le)): ?>
    <div class="bg-white rounded shadow-sm border border-red-200 overflow-hidden mb-6">
        <div class="bg-red-50 px-5 py-3 border-b border-red-200 font-semibold text-red-600 flex items-center gap-2 text-sm uppercase">
            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-exclamation-triangle-fill" viewBox="0 0 16 16"><path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/></svg>
            Dữ Liệu Không Hợp Lệ (<?php echo count($danh_sach_khong_hop_le); ?> dòng)
        </div>
        <div class="p-5">
            <p class="text-sm text-red-600 mb-4">Vui lòng sửa các lỗi sau trong file Excel của bạn và thử tải lên lại.</p>
            <div class="overflow-x-auto list-scrollbar w-full border border-red-100 rounded">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-red-50/50 border-b border-red-100 text-xs uppercase font-semibold text-red-500 sticky top-0">
                        <tr>
                            <th class="p-3">Dòng</th>
                            <th class="p-3">Số CCCD</th>
                            <th class="p-3">Lớp</th>
                            <th class="p-3">Họ Tên</th>
                            <th class="p-3">Chi Tiết Lỗi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-red-50">
                        <?php foreach ($danh_sach_khong_hop_le as $row): ?>
                        <tr class="hover:bg-red-50/30 transition">
                            <td class="p-3"><?php echo htmlspecialchars($row['dong']); ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($row['ma_hoc_sinh'] ?? ''); ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($row['ten_lop'] ?? ''); ?></td>
                            <td class="p-3"><?php echo htmlspecialchars(($row['ho_dem'] ?? '') . ' ' . ($row['ten'] ?? '')); ?></td>
                            <td class="p-3">
                                <ul class="list-none space-y-1 mb-0 text-red-500 text-xs">
                                    <?php foreach ($row['loi'] as $error): ?>
                                    <li class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-x-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/></svg>
                                        <?php echo htmlspecialchars($error); ?>
                                    </li>
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

    <!-- Khối Dữ Liệu Hợp Lệ -->
    <div class="bg-white rounded shadow-sm border border-[#224397]/25 overflow-hidden mb-6">
        <div class="bg-slate-50 px-5 py-3 border-b border-[#224397]/25 font-semibold text-[#224397] flex flex-wrap justify-between items-center text-sm uppercase">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check-circle-fill text-green-500" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
                Dữ Liệu Hợp Lệ (Sẵn sàng để nhập)
            </div>
            <div class="flex gap-2 mt-2 sm:mt-0">
                <a href="/thidua/admin/hoc-sinh<?= isset($_GET['iframe']) ? '?iframe=1' : '' ?>" class="px-4 py-2 bg-white border border-slate-300 rounded text-slate-700 hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] font-medium transition-all duration-300 flex items-center justify-center gap-2 text-sm shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/></svg>
                    Tải Lên Lại
                </a>
                <?php if (!empty($danh_sach_hop_le)): ?>
                <form action="/thidua/admin/hoc-sinh?action=api_save_import<?= isset($_GET['iframe']) ? '&iframe=1' : '' ?>" method="POST" class="d-inline">
                    <button type="submit" class="px-4 py-2 bg-[#224397] text-white rounded hover:bg-[#FAB723] font-medium shadow-sm hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 flex items-center justify-center gap-2 text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-save-fill" viewBox="0 0 16 16"><path d="M8.5 1.5A1.5 1.5 0 0 1 10 0h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h6c-.314.418-.5.937-.5 1.5v7.793L4.854 6.646a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0l3.5-3.5a.5.5 0 0 0-.708-.708L8.5 9.293z"/></svg>
                        Xác Nhận & Lưu Dữ Liệu
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
        <div class="p-5">
            <?php if (empty($danh_sach_hop_le)): ?>
                <div class="p-4 rounded border bg-yellow-50 text-yellow-800 border-yellow-200 text-sm flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-info-circle-fill" viewBox="0 0 16 16"><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2"/></svg>
                    Không có dữ liệu hợp lệ nào để nhập.
                </div>
            <?php else: ?>
                <p class="text-sm text-slate-600 mb-4">Có <strong><?php echo count($danh_sach_hop_le); ?></strong> học sinh hợp lệ sẽ được thêm vào hệ thống.</p>
                <div class="overflow-x-auto list-scrollbar w-full border border-[#224397]/25 rounded">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 border-b border-[#224397]/25 text-xs uppercase font-semibold text-slate-500 sticky top-0">
                            <tr>
                                <th class="p-3">Số CCCD</th>
                                <th class="p-3">Lớp</th>
                                <th class="p-3">Họ Đệm</th>
                                <th class="p-3">Tên</th>
                                <th class="p-3">Niên khóa</th>
                                <th class="p-3">Ngày Sinh</th>
                                <th class="p-3">Chức Vụ</th>
                                <th class="p-3">Giới Tính</th>
                                <th class="p-3">SĐT</th>
                                <th class="p-3">Gmail</th> 
                                <th class="p-3">Tỉnh/TP</th>
                                <th class="p-3">Xã/Phường</th>
                                <th class="p-3">Ấp/Khu phố</th>
                                <th class="p-3">ĐC Chi Tiết</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#224397]/20">
                            <?php foreach ($danh_sach_hop_le as $row): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="p-3"><?php echo htmlspecialchars($row['ma_hoc_sinh'] ?? ''); ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($row['ten_lop'] ?? ''); ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($row['ho_dem'] ?? ''); ?></td>
                                <td class="p-3 font-medium text-slate-800"><?php echo htmlspecialchars($row['ten'] ?? ''); ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($row['nien_khoa'] ?? ''); ?></td>
                                <td class="p-3"><?php echo htmlspecialchars(format_date_display($row['ngay_sinh'] ?? '')); ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($row['chuc_vu'] ?? ''); ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($row['gioi_tinh'] ?? ''); ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($row['sdt'] ?? ''); ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($row['gmail'] ?? ''); ?></td> 
                                <td class="p-3"><?php echo htmlspecialchars($row['tinh_thanhpho'] ?? ''); ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($row['xa_phuong'] ?? ''); ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($row['ap_khupho'] ?? ''); ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($row['dia_chi_chi_tiet'] ?? ''); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>
