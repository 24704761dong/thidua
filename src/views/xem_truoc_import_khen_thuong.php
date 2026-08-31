<?php
$page_title = 'Xem Trước Import Khen Thưởng';
require_once __DIR__ . '/partials/admin_header.php';
$import_data = $_SESSION['import_khen_thuong'] ?? null;

if (!$import_data) {
    echo '<div class="p-6 text-center text-red-500 font-semibold">Không có dữ liệu import. Vui lòng quay lại.</div>';
    require_once __DIR__ . '/partials/admin_footer.php';
    exit;
}

$count_valid_cn = count($import_data['valid_cn'] ?? []);
$count_invalid_cn = count($import_data['invalid_cn'] ?? []);
$count_valid_tt = count($import_data['valid_tt'] ?? []);
$count_invalid_tt = count($import_data['invalid_tt'] ?? []);

$total_valid = $count_valid_cn + $count_valid_tt;
$total_invalid = $count_invalid_cn + $count_invalid_tt;
?>

<style>
    .import-table th { padding: 8px 12px; font-size: 12px; font-weight: 600; color: #334155; border-bottom: 1px solid #e2e8f0; background: #f8fafc; }
    .import-table td { padding: 8px 12px; font-size: 13px; color: #475569; border-bottom: 1px solid #f1f5f9; }
    .import-table tr:hover { background-color: rgba(34, 67, 151, 0.05) !important; }
</style>

<div class="w-full px-2 lg:px-6 mt-4">
    <div class="flex flex-col md:flex-row items-end justify-between gap-4 mb-4">
        <div>
            <h3 class="text-[18px] font-bold text-[#224397] flex items-center gap-2 mb-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-eye-fill text-[#FAB723]" viewBox="0 0 16 16"><path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/><path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/></svg>
                XEM TRƯỚC DỮ LIỆU IMPORT
            </h3>
        </div>
        
        <div class="flex items-center gap-1.5 flex-wrap">
            <a href="/thidua/admin/khen-thuong" class="px-2 py-1 bg-slate-500 border border-transparent rounded text-white hover:bg-slate-600 transition-colors font-medium flex items-center gap-1 text-[11px] shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/></svg> 
                Hủy bỏ
            </a>
            <form action="/thidua/admin/khen-thuong?action=api_preview_import" method="POST" class="m-0">
                <input type="hidden" name="confirm_import" value="1">
                <button type="submit" class="px-2 py-1 bg-green-600 border border-transparent rounded text-white hover:bg-green-700 transition-colors font-medium flex items-center gap-1 text-[11px] shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
                    Xác nhận & Lưu
                </button>
            </form>
        </div>
    </div>

    <!-- KHEN THƯỞNG CÁ NHÂN -->
    <div class="bg-white rounded shadow border border-[#224397]/25 mb-6 p-0 overflow-hidden">
        <div class="px-4 py-3 border-b border-[#224397]/20 bg-[#224397]/5 flex items-center">
            <h3 class="mb-0 text-[15px] font-bold text-[#224397] uppercase flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-person-fill mr-2" viewBox="0 0 16 16"><path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/></svg>
                KHEN THƯỞNG CÁ NHÂN
            </h3>
        </div>
        <div class="px-4 pb-4 pt-3">
            <!-- Hợp lệ -->
            <?php if ($count_valid_cn > 0): ?>
                <div class="mb-3 flex items-center gap-2 text-green-600 font-bold text-[13px]">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
                    <?php echo $count_valid_cn; ?> mục hợp lệ sẽ được import
                </div>
                <div class="w-full overflow-x-auto border border-slate-200 rounded mb-6">
                    <table class="w-full text-left import-table border-collapse">
                        <thead>
                            <tr>
                                <th>Họ và tên</th>
                                <th>Lớp</th>
                                <th>Ngày KT</th>
                                <th>Tên Khen Thưởng</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($import_data['valid_cn'] as $item): ?>
                                <tr>
                                    <td class="font-medium text-[#224397]"><?php echo htmlspecialchars($item['ho_va_ten']); ?></td>
                                    <td><?php echo htmlspecialchars($item['ten_lop']); ?></td>
                                    <td><?php echo htmlspecialchars($item['ngay_khen_thuong']); ?></td>
                                    <td><?php echo htmlspecialchars($item['ten_khen_thuong']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Không hợp lệ -->
            <?php if ($count_invalid_cn > 0): ?>
                <div class="mb-3 flex items-center gap-2 text-red-600 font-bold text-[13px] <?php echo $count_valid_cn > 0 ? 'mt-6' : ''; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z"/></svg>
                    <?php echo $count_invalid_cn; ?> mục không hợp lệ (bị bỏ qua)
                </div>
                <div class="w-full overflow-x-auto border border-red-200 rounded">
                    <table class="w-full text-left import-table border-collapse">
                        <thead style="background-color: #fef2f2;">
                            <tr>
                                <th style="color: #991b1b; border-bottom-color: #fecaca;">Dòng</th>
                                <th style="color: #991b1b; border-bottom-color: #fecaca;">Họ và tên</th>
                                <th style="color: #991b1b; border-bottom-color: #fecaca;">Lớp</th>
                                <th style="color: #991b1b; border-bottom-color: #fecaca;">Chi tiết lỗi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($import_data['invalid_cn'] as $item): ?>
                                <tr style="background-color: #fff;">
                                    <td class="font-bold text-red-600">#<?php echo $item['line_number']; ?></td>
                                    <td class="font-medium text-[#224397]"><?php echo htmlspecialchars($item['ho_va_ten'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($item['ten_lop'] ?? ''); ?></td>
                                    <td class="text-red-600 font-medium"><?php echo htmlspecialchars(implode(', ', $item['errors'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            
            <?php if ($count_valid_cn == 0 && $count_invalid_cn == 0): ?>
                <div class="text-center py-6 text-[13px] text-slate-500 italic">
                    Không có dữ liệu khen thưởng cá nhân trong file Excel.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- KHEN THƯỞNG TẬP THỂ -->
    <div class="bg-white rounded shadow border border-[#224397]/25 mb-6 p-0 overflow-hidden">
        <div class="px-4 py-3 border-b border-[#224397]/20 bg-[#224397]/5 flex items-center">
            <h3 class="mb-0 text-[15px] font-bold text-[#224397] uppercase flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-people-fill mr-2" viewBox="0 0 16 16"><path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/></svg>
                KHEN THƯỞNG TẬP THỂ
            </h3>
        </div>
        <div class="px-4 pb-4 pt-3">
            <!-- Hợp lệ -->
            <?php if ($count_valid_tt > 0): ?>
                <div class="mb-3 flex items-center gap-2 text-green-600 font-bold text-[13px]">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
                    <?php echo $count_valid_tt; ?> mục hợp lệ sẽ được import
                </div>
                <div class="w-full overflow-x-auto border border-slate-200 rounded mb-6">
                    <table class="w-full text-left import-table border-collapse">
                        <thead>
                            <tr>
                                <th>Tên Lớp / Tập Thể</th>
                                <th>Ngày KT</th>
                                <th>Tên Khen Thưởng</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($import_data['valid_tt'] as $item): ?>
                                <tr>
                                    <td class="font-medium text-[#224397]"><?php echo htmlspecialchars($item['ten_lop_hoac_tap_the']); ?></td>
                                    <td><?php echo htmlspecialchars($item['ngay_khen_thuong']); ?></td>
                                    <td><?php echo htmlspecialchars($item['ten_khen_thuong']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Không hợp lệ -->
            <?php if ($count_invalid_tt > 0): ?>
                <div class="mb-3 flex items-center gap-2 text-red-600 font-bold text-[13px] <?php echo $count_valid_tt > 0 ? 'mt-6' : ''; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z"/></svg>
                    <?php echo $count_invalid_tt; ?> mục không hợp lệ (bị bỏ qua)
                </div>
                <div class="w-full overflow-x-auto border border-red-200 rounded">
                    <table class="w-full text-left import-table border-collapse">
                        <thead style="background-color: #fef2f2;">
                            <tr>
                                <th style="color: #991b1b; border-bottom-color: #fecaca;">Dòng</th>
                                <th style="color: #991b1b; border-bottom-color: #fecaca;">Tên Lớp / Tập Thể</th>
                                <th style="color: #991b1b; border-bottom-color: #fecaca;">Chi tiết lỗi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($import_data['invalid_tt'] as $item): ?>
                                <tr style="background-color: #fff;">
                                    <td class="font-bold text-red-600">#<?php echo $item['line_number']; ?></td>
                                    <td class="font-medium text-[#224397]"><?php echo htmlspecialchars($item['ten_lop_hoac_tap_the'] ?? ''); ?></td>
                                    <td class="text-red-600 font-medium"><?php echo htmlspecialchars(implode(', ', $item['errors'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            
            <?php if ($count_valid_tt == 0 && $count_invalid_tt == 0): ?>
                <div class="text-center py-6 text-[13px] text-slate-500 italic">
                    Không có dữ liệu khen thưởng tập thể trong file Excel.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>