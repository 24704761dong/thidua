<?php
$page_title = 'Cấu Hình Bảng Xem Điểm Thi Đua';
require_once __DIR__ . '/partials/admin_header.php';

// Giả định các biến này được nạp từ controller
$settings = $settings ?? [];
$dieu_kien_kxtd = $dieu_kien_kxtd ?? [];
$danh_sach_vi_pham = $danh_sach_vi_pham ?? [];
?>

<div class="flex-1 overflow-y-auto bg-transparent p-6 min-h-screen">
    <div class="max-w-6xl mx-auto">
    <form id="reportSettingsForm">
        <div class="flex justify-between items-center mb-6 border-b border-[#224397]/25 pb-3">
            <h3 class="text-xl font-bold text-[#224397] flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-sliders text-[#224397]" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M11.5 2a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M9.05 3a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0V3zM4.5 7a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M2.05 8a2.5 2.5 0 0 1 4.9 0H16v1H6.95a2.5 2.5 0 0 1-4.9 0H0V8zm9.45 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3m-2.45 1a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0v-1z"/></svg> CẤU HÌNH BẢNG ĐIỂM THI ĐUA
            </h3>
            <div class="flex items-center gap-2">
                <a href="/thidua/admin/huong-dan-cau-hinh-bao-cao" onclick="if(window.top && typeof window.top.openApp === 'function') { window.top.openApp('huong_dan_cau_hinh_bao_cao', 'Hướng Dẫn Cấu Hình KXTĐ', '/thidua/admin/huong-dan-cau-hinh-bao-cao', '/thidua/public/assets/img/icons/cauhinh.svg'); return false; }" class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm whitespace-nowrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-question-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.496 6.033h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286a.237.237 0 0 0 .241.247m2.325 6.443c.61 0 1.029-.394 1.029-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94 0 .533.425.927 1.01.927z"/></svg> Hướng dẫn
                </a>
                <button type="submit" class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm whitespace-nowrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-save-fill" viewBox="0 0 16 16"><path d="M8.5 1.5A1.5 1.5 0 0 1 10 0h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h6c-.314.418-.5.937-.5 1.5v7.793L4.854 6.646a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0l3.5-3.5a.5.5 0 0 0-.708-.708L8.5 9.293z"/></svg> Lưu Toàn Bộ Cấu Hình
                </button>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-[#224397]/[45%] mb-6 overflow-hidden">
            <div class="p-6">
                <div class="mb-8">
                    <h5 class="text-lg font-bold text-slate-800 mb-4 border-b border-[#224397]/25 pb-2">1. Cấu hình điểm Số Tiết</h5>
                    <div class="flex flex-wrap -mx-3">
                        <div class="w-full md:w-1/2 px-3 mb-6">
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Điểm cho mỗi <strong>Tiết Tốt</strong> (x):</label>
                            <input type="number" step="0.5" class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-[#224397] focus:ring-1 focus:ring-[#224397] focus:outline-none shadow-sm transition-colors text-slate-700" name="report_diem_tiet_tot" value="<?php echo htmlspecialchars($settings['report_diem_tiet_tot'] ?? '0'); ?>">
                        </div>
                        <div class="w-full md:w-1/2 px-3 mb-6">
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Điểm cho mỗi <strong>Tiết Trung Bình</strong> (y):</label>
                            <input type="number" step="0.5" class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-[#224397] focus:ring-1 focus:ring-[#224397] focus:outline-none shadow-sm transition-colors text-slate-700" name="report_diem_tiet_tb" value="<?php echo htmlspecialchars($settings['report_diem_tiet_tb'] ?? '0'); ?>">
                        </div>
                    </div>
                </div>

                <div class="mb-8">
                    <h5 class="text-lg font-bold text-slate-800 mb-4 border-b border-[#224397]/25 pb-2">2. Cấu hình điểm Sổ Đầu Bài & Nhật kỳ</h5>
                    <p class="text-slate-500 text-sm mb-4">Tích vào "Sử dụng..." để cộng dồn điểm của mục đó vào cột "Sổ ĐB-NK".</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="bg-white rounded-xl shadow-sm border border-[#224397]/[45%] flex flex-col h-full overflow-hidden">
                            <div class="px-5 py-4 border-b border-[#224397]/25 bg-slate-50 flex items-center justify-between">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" name="report_sdb_use_tt" id="use_tt" class="sr-only peer" <?php echo ($settings['report_sdb_use_tt'] ?? 'off') === 'on' ? 'checked' : ''; ?>>
                                    <div class="relative w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#224397]/30 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#224397]"></div>
                                    <span class="ms-3 text-sm font-bold text-slate-800">Sử dụng SĐB-TT</span>
                                </label>
                            </div>
                            <div class="p-5 flex-1 bg-white">
                                <div class="mb-4">
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wide">Điểm nếu có 'X':</label>
                                    <input type="number" step="0.5" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-[#224397] focus:ring-1 focus:ring-[#224397] focus:outline-none shadow-sm transition-colors text-slate-700 text-sm" name="report_sdb_tt_tich" value="<?php echo htmlspecialchars($settings['report_sdb_tt_tich'] ?? '0'); ?>">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wide">Điểm nếu không có 'X':</label>
                                    <input type="number" step="0.5" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-[#224397] focus:ring-1 focus:ring-[#224397] focus:outline-none shadow-sm transition-colors text-slate-700 text-sm" name="report_sdb_tt_khong" value="<?php echo htmlspecialchars($settings['report_sdb_tt_khong'] ?? '0'); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-[#224397]/[45%] flex flex-col h-full overflow-hidden">
                            <div class="px-5 py-4 border-b border-[#224397]/25 bg-slate-50 flex items-center justify-between">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" name="report_sdb_use_ck" id="use_ck" class="sr-only peer" <?php echo ($settings['report_sdb_use_ck'] ?? 'off') === 'on' ? 'checked' : ''; ?>>
                                    <div class="relative w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#224397]/30 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#224397]"></div>
                                    <span class="ms-3 text-sm font-bold text-slate-800">Sử dụng SĐB-CK</span>
                                </label>
                            </div>
                            <div class="p-5 flex-1 bg-white">
                                <div class="mb-4">
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wide">Điểm nếu có 'X':</label>
                                    <input type="number" step="0.5" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-[#224397] focus:ring-1 focus:ring-[#224397] focus:outline-none shadow-sm transition-colors text-slate-700 text-sm" name="report_sdb_ck_tich" value="<?php echo htmlspecialchars($settings['report_sdb_ck_tich'] ?? '0'); ?>">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wide">Điểm nếu không có 'X':</label>
                                    <input type="number" step="0.5" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-[#224397] focus:ring-1 focus:ring-[#224397] focus:outline-none shadow-sm transition-colors text-slate-700 text-sm" name="report_sdb_ck_khong" value="<?php echo htmlspecialchars($settings['report_sdb_ck_khong'] ?? '0'); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-[#224397]/[45%] flex flex-col h-full overflow-hidden">
                            <div class="px-5 py-4 border-b border-[#224397]/25 bg-slate-50 flex items-center justify-between">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" name="report_sdb_use_nk" id="use_nk" class="sr-only peer" <?php echo ($settings['report_sdb_use_nk'] ?? 'off') === 'on' ? 'checked' : ''; ?>>
                                    <div class="relative w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#224397]/30 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#224397]"></div>
                                    <span class="ms-3 text-sm font-bold text-slate-800">Sử dụng SĐB-NK</span>
                                </label>
                            </div>
                            <div class="p-5 flex-1 bg-white">
                                <div class="mb-4">
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wide">Điểm nếu có 'X':</label>
                                    <input type="number" step="0.5" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-[#224397] focus:ring-1 focus:ring-[#224397] focus:outline-none shadow-sm transition-colors text-slate-700 text-sm" name="report_sdb_nk_tich" value="<?php echo htmlspecialchars($settings['report_sdb_nk_tich'] ?? '0'); ?>">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wide">Điểm nếu không có 'X':</label>
                                    <input type="number" step="0.5" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-[#224397] focus:ring-1 focus:ring-[#224397] focus:outline-none shadow-sm transition-colors text-slate-700 text-sm" name="report_sdb_nk_khong" value="<?php echo htmlspecialchars($settings['report_sdb_nk_khong'] ?? '0'); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-[#224397]/[45%] flex flex-col h-full overflow-hidden">
                            <div class="px-5 py-4 border-b border-[#224397]/25 bg-slate-50 flex items-center justify-between">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" name="report_sdb_use_nhat_ky" id="use_nhat_ky" class="sr-only peer" <?php echo ($settings['report_sdb_use_nhat_ky'] ?? 'off') === 'on' ? 'checked' : ''; ?>>
                                    <div class="relative w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#224397]/30 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#224397]"></div>
                                    <span class="ms-3 text-sm font-bold text-slate-800">Sử dụng Nhật kỳ</span>
                                </label>
                            </div>
                            <div class="p-5 flex-1 bg-white">
                                <div class="mb-4">
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wide">Điểm nếu có 'X':</label>
                                    <input type="number" step="0.5" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-[#224397] focus:ring-1 focus:ring-[#224397] focus:outline-none shadow-sm transition-colors text-slate-700 text-sm" name="report_nhat_ky_tich" value="<?php echo htmlspecialchars($settings['report_nhat_ky_tich'] ?? '0'); ?>">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wide">Điểm nếu không có 'X':</label>
                                    <input type="number" step="0.5" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-[#224397] focus:ring-1 focus:ring-[#224397] focus:outline-none shadow-sm transition-colors text-slate-700 text-sm" name="report_nhat_ky_khong" value="<?php echo htmlspecialchars($settings['report_nhat_ky_khong'] ?? '0'); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-8">
                    <h5 class="text-lg font-bold text-slate-800 mb-4 border-b border-[#224397]/25 pb-2">3. Cấu hình dữ liệu và điểm trừ Vắng</h5>
                    <div class="flex flex-wrap -mx-3 mb-6">
                        <div class="w-full px-3">
                            <label class="block text-sm font-medium text-slate-700 mb-2 font-bold">Nguồn dữ liệu Vắng</label>
                            <div class="flex items-center mb-2">
                                <input class="w-4 h-4 text-[#224397] bg-gray-100 border-gray-300 focus:ring-[#224397]" type="radio" name="report_vang_source" id="source_diem_danh" value="diem_danh" <?= ($settings['report_vang_source'] ?? 'diem_danh') === 'diem_danh' ? 'checked' : '' ?>>
                                <label class="ml-2 block text-sm text-slate-700 cursor-pointer" for="source_diem_danh">
                                    Lấy từ chức năng Điểm Danh (Khuyến nghị)
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input class="w-4 h-4 text-[#224397] bg-gray-100 border-gray-300 focus:ring-[#224397]" type="radio" name="report_vang_source" id="source_vi_pham" value="vi_pham" <?= ($settings['report_vang_source'] ?? '') === 'vi_pham' ? 'checked' : '' ?>>
                                <label class="ml-2 block text-sm text-slate-700 cursor-pointer" for="source_vi_pham">
                                    Đếm tổng số lần vi phạm được chỉ định
                                </label>
                            </div>
                        </div>
                    </div>

                    <div id="diem-tru-vang-options" class="mt-6 border-t border-[#224397]/25 pt-6">
                        <label class="block text-sm font-medium text-slate-700 mb-3 font-bold">Cấu hình điểm trừ Vắng</label>
                        <div class="flex flex-wrap -mx-3">
                            <div class="w-full md:w-1/2 px-3 mb-6">
                                <label for="report_tru_vang_p" class="block text-sm font-medium text-slate-700 mb-1.5">Trừ điểm Vắng có phép (P)</label>
                                <input type="number" step="0.1" class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-[#224397] focus:ring-1 focus:ring-[#224397] focus:outline-none shadow-sm transition-colors text-slate-700" id="report_tru_vang_p" name="report_tru_vang_p" value="<?= htmlspecialchars($settings['report_tru_vang_p'] ?? '0') ?>">
                            </div>
                            <div class="w-full md:w-1/2 px-3 mb-6">
                                <label for="report_tru_vang_kp" class="block text-sm font-medium text-slate-700 mb-1.5">Trừ điểm Vắng không phép (KP)</label>
                                <input type="number" step="0.1" class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-[#224397] focus:ring-1 focus:ring-[#224397] focus:outline-none shadow-sm transition-colors text-slate-700" id="report_tru_vang_kp" name="report_tru_vang_kp" value="<?= htmlspecialchars($settings['report_tru_vang_kp'] ?? '-1') ?>">
                            </div>
                        </div>
                    </div>

                    <div id="violation-source-options" class="mt-6 border-t border-[#224397]/25 pt-6">
                        <?php 
                            $vang_p_vids = json_decode($settings['report_vang_p_vids'] ?? '[]', true);
                            $vang_kp_vids = json_decode($settings['report_vang_kp_vids'] ?? '[]', true);
                        ?>
                        <label class="block text-sm font-medium text-slate-700 mb-3 font-bold">Bảng quy đổi vi phạm thành Vắng</label>
                        <div class="overflow-x-auto w-full border border-[#224397]/[45%] rounded-lg">
                            <table class="w-full text-left text-sm text-slate-600 border-collapse border border-slate-300 relative">
                                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold sticky top-0 z-10 border-b border-slate-300">
                                    <tr>
                                        <th class="py-3 px-4 border-r border-slate-300">Tên Nhóm Vi Phạm</th>
                                        <th class="py-3 px-4 text-center border-r border-slate-300 w-40">Tính là Vắng P</th>
                                        <th class="py-3 px-4 text-center w-40">Tính là Vắng KP</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-300">
                                    <?php foreach ($danh_sach_vi_pham as $vp): ?>
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="py-3 px-4 border-r border-slate-300 font-medium text-slate-800"><?= htmlspecialchars($vp['ten_vi_pham']) ?></td>
                                            <td class="py-3 px-4 text-center border-r border-slate-300">
                                                <input class="w-4 h-4 text-[#224397] bg-gray-100 border-gray-300 rounded focus:ring-[#224397]" type="checkbox" name="report_vang_p_vids[]" value="<?= $vp['id'] ?>" id="vp_p_<?= $vp['id'] ?>" <?= in_array($vp['id'], $vang_p_vids) ? 'checked' : '' ?>>
                                            </td>
                                            <td class="py-3 px-4 text-center">
                                                <input class="w-4 h-4 text-[#224397] bg-gray-100 border-gray-300 rounded focus:ring-[#224397]" type="checkbox" name="report_vang_kp_vids[]" value="<?= $vp['id'] ?>" id="vp_kp_<?= $vp['id'] ?>" <?= in_array($vp['id'], $vang_kp_vids) ? 'checked' : '' ?>>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>



                <div>
                    <h5 class="text-lg font-bold text-slate-800 mb-4 border-b border-[#224397]/25 pb-2">4. Quản lý Điều kiện Không Xét Thi Đua (KXTĐ)</h5>
                    <div id="kxtd-conditions" class="mt-4 space-y-6">
                        <?php foreach (($dieu_kien_kxtd ?? []) as $index => $dk):
                            $danh_sach_sdb_da_chon = json_decode($dk['danh_sach_sdb'] ?? '[]', true);
                        ?>
                            <div class="kxtd-condition-entry bg-white border border-[#224397]/[45%] rounded-xl shadow-sm p-6 relative overflow-hidden" data-index="<?php echo $index; ?>">
                                <div class="absolute top-0 left-0 w-1.5 h-full bg-[#224397]"></div>
                                <input type="hidden" name="kxtd[<?php echo $index; ?>][id]" value="<?php echo htmlspecialchars($dk['id']); ?>">
                                <h6 class="text-md font-bold text-[#224397] mb-4 pb-2 border-b border-[#224397]/25">Điều kiện KXTĐ #<?php echo $index + 1; ?></h6>
                                <div class="flex flex-wrap -mx-3">
                                    <div class="w-full px-3 mb-4">
                                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Tên/Mô tả điều kiện (*)</label>
                                        <input type="text" name="kxtd[<?php echo $index; ?>][ten_dieu_kien]" class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-[#224397] focus:ring-1 focus:ring-[#224397] focus:outline-none shadow-sm transition-colors text-slate-700" value="<?php echo htmlspecialchars($dk['ten_dieu_kien']); ?>" required>
                                    </div>
                                    <div class="w-full md:w-1/2 px-3 mb-4">
                                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Trường/Mã Cột So Sánh (*)</label>
                                        <input type="text" name="kxtd[<?php echo $index; ?>][truong_so_sanh]" class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-[#224397] focus:ring-1 focus:ring-[#224397] focus:outline-none shadow-sm transition-colors text-slate-700 disabled:bg-slate-100 disabled:text-slate-400" value="<?php echo htmlspecialchars($dk['truong_so_sanh']); ?>" placeholder="VD: vang_kp, diem_noi_quy, sdb_ck" required>
                                    </div>
                                    <div class="w-full md:w-1/2 px-3 mb-4">
                                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Toán tử so sánh (*)</label>
                                        <select name="kxtd[<?php echo $index; ?>][toan_tu]" class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-[#224397] focus:ring-1 focus:ring-[#224397] focus:outline-none shadow-sm transition-colors text-slate-700" required>
                                            <optgroup label="Toán tử số học">
                                                <option value=">" <?php echo ($dk['toan_tu'] ?? '') == '>' ? 'selected' : ''; ?>>&gt; (Lớn hơn)</option>
                                                <option value=">=" <?php echo ($dk['toan_tu'] ?? '') == '>=' ? 'selected' : ''; ?>>&gt;= (Lớn hơn hoặc bằng)</option>
                                                <option value="<" <?php echo ($dk['toan_tu'] ?? '') == '<' ? 'selected' : ''; ?>>&lt; (Nhỏ hơn)</option>
                                                <option value="<=" <?php echo ($dk['toan_tu'] ?? '') == '<=' ? 'selected' : ''; ?>>&lt;= (Nhỏ hơn hoặc bằng)</option>
                                                <option value="==" <?php echo ($dk['toan_tu'] ?? '') == '==' ? 'selected' : ''; ?>>== (Bằng)</option>
                                                <option value="!=" <?php echo ($dk['toan_tu'] ?? '') == '!=' ? 'selected' : ''; ?>>!= (Không bằng)</option>
                                            </optgroup>
                                            <optgroup label="Toán tử cho Sổ Đầu Bài (SĐB)">
                                                <option value="SDB_COMB_ALL_NOT_TICKED" <?php echo ($dk['toan_tu'] ?? '') == 'SDB_COMB_ALL_NOT_TICKED' ? 'selected' : ''; ?>>SĐB: TẤT CẢ mục chọn ĐỀU KHÔNG TICK (Không có cuốn sổ nào)</option>
                                                <option value="SDB_IS_NOT_TICKED" <?php echo ($dk['toan_tu'] ?? '') == 'SDB_IS_NOT_TICKED' ? 'selected' : ''; ?>>SĐB: Có mục chọn KHÔNG TICK (Thiếu sổ trong các mục chọn)</option>
                                                <option value="SDB_IS_TICKED" <?php echo ($dk['toan_tu'] ?? '') == 'SDB_IS_TICKED' ? 'selected' : ''; ?>>SĐB: Có ít nhất 1 mục chọn CÓ TICK</option>
                                                <option value="SDB_COUNT_TICKED_EQUALS" <?php echo ($dk['toan_tu'] ?? '') == 'SDB_COUNT_TICKED_EQUALS' ? 'selected' : ''; ?>>Đếm SĐB: Số mục tick BẰNG ngưỡng</option>
                                            </optgroup>
                                        </select>
                                    </div>
                                    <div class="w-full md:w-1/2 px-3 mb-4">
                                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Ngưỡng giá trị so sánh</label>
                                        <input type="number" step="any" name="kxtd[<?php echo $index; ?>][nguong_gia_tri]" class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-[#224397] focus:ring-1 focus:ring-[#224397] focus:outline-none shadow-sm transition-colors text-slate-700" value="<?php echo htmlspecialchars($dk['nguong_gia_tri'] ?? ''); ?>">
                                    </div>
                                    <div class="w-full md:w-1/2 px-3 mb-4">
                                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Các mục Sổ Đầu Bài áp dụng</label>
                                        <div class="border border-slate-300 p-3 rounded-lg bg-slate-50 flex flex-wrap gap-4">
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="checkbox" class="w-4 h-4 text-[#224397] bg-white border-gray-300 rounded focus:ring-[#224397]" name="kxtd[<?php echo $index; ?>][danh_sach_sdb][]" value="sdb_tt" <?php echo in_array('sdb_tt', $danh_sach_sdb_da_chon) ? 'checked' : ''; ?>>
                                                <span class="ml-2 text-sm text-slate-700">TT</span>
                                            </label>
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="checkbox" class="w-4 h-4 text-[#224397] bg-white border-gray-300 rounded focus:ring-[#224397]" name="kxtd[<?php echo $index; ?>][danh_sach_sdb][]" value="sdb_ck" <?php echo in_array('sdb_ck', $danh_sach_sdb_da_chon) ? 'checked' : ''; ?>>
                                                <span class="ml-2 text-sm text-slate-700">CK</span>
                                            </label>
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="checkbox" class="w-4 h-4 text-[#224397] bg-white border-gray-300 rounded focus:ring-[#224397]" name="kxtd[<?php echo $index; ?>][danh_sach_sdb][]" value="sdb_nk" <?php echo in_array('sdb_nk', $danh_sach_sdb_da_chon) ? 'checked' : ''; ?>>
                                                <span class="ml-2 text-sm text-slate-700">NK</span>
                                            </label>
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="checkbox" class="w-4 h-4 text-[#224397] bg-white border-gray-300 rounded focus:ring-[#224397]" name="kxtd[<?php echo $index; ?>][danh_sach_sdb][]" value="nhat_ky" <?php echo in_array('nhat_ky', $danh_sach_sdb_da_chon) ? 'checked' : ''; ?>>
                                                <span class="ml-2 text-sm text-slate-700">Nhật kỳ</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between mt-4 pt-4 border-t border-[#224397]/25">
                                    <label class="flex items-center cursor-pointer">
                                        <input type="checkbox" name="kxtd[<?php echo $index; ?>][kich_hoat]" value="1" class="sr-only peer" <?php echo ($dk['kich_hoat'] ?? 1) == 1 ? 'checked' : ''; ?>>
                                        <div class="relative w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#224397]/30 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#224397]"></div>
                                        <span class="ms-3 text-sm font-bold text-slate-700">Kích hoạt điều kiện</span>
                                    </label>
                                    <button type="button" class="px-3 py-1.5 bg-white border border-red-200 text-red-600 rounded-lg hover:bg-red-50 hover:border-red-300 transition-all shadow-sm text-sm font-medium flex items-center gap-1.5 btn-remove-kxtd-condition ml-auto">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-trash3" viewBox="0 0 16 16"><path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47M8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5"/></svg> Xóa
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="mt-6 px-4 py-2 bg-white border border-[#224397] text-[#224397] rounded-lg hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all shadow-sm text-sm font-medium flex items-center gap-1.5 hover:scale-105" id="btn-add-kxtd-condition">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-plus-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3z"/></svg> Thêm Điều Kiện KXTĐ Mới
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<template id="template-kxtd-condition">
    <div class="kxtd-condition-entry bg-white border border-[#224397]/[45%] rounded-xl shadow-sm p-6 relative overflow-hidden" data-index="__INDEX__">
        <div class="absolute top-0 left-0 w-1.5 h-full bg-[#224397]"></div>
        <input type="hidden" name="kxtd[__INDEX__][id]" value="">
        <h6 class="text-md font-bold text-[#224397] mb-4 pb-2 border-b border-[#224397]/25">Điều kiện KXTĐ mới #<span class="entry-number">__NUMBER__</span></h6>
        <div class="flex flex-wrap -mx-3">
            <div class="w-full px-3 mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Tên/Mô tả điều kiện (*)</label>
                <input type="text" name="kxtd[__INDEX__][ten_dieu_kien]" class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-[#224397] focus:ring-1 focus:ring-[#224397] focus:outline-none shadow-sm transition-colors text-slate-700" required>
            </div>
            <div class="w-full md:w-1/2 px-3 mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Trường/Mã Cột So Sánh (*)</label>
                <input type="text" name="kxtd[__INDEX__][truong_so_sanh]" class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-[#224397] focus:ring-1 focus:ring-[#224397] focus:outline-none shadow-sm transition-colors text-slate-700 disabled:bg-slate-100 disabled:text-slate-400" placeholder="VD: vang_kp, diem_noi_quy, sdb_ck" required>
            </div>
            <div class="w-full md:w-1/2 px-3 mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Toán tử so sánh (*)</label>
                <select name="kxtd[__INDEX__][toan_tu]" class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-[#224397] focus:ring-1 focus:ring-[#224397] focus:outline-none shadow-sm transition-colors text-slate-700" required>
                    <optgroup label="Toán tử số học">
                        <option value=">">&gt; (Lớn hơn)</option>
                        <option value=">=">&gt;= (Lớn hơn hoặc bằng)</option>
                        <option value="<">&lt; (Nhỏ hơn)</option>
                        <option value="<=">&lt;= (Nhỏ hơn hoặc bằng)</option>
                        <option value="==" selected>== (Bằng)</option>
                        <option value="!=">!= (Không bằng)</option>
                    </optgroup>
                    <optgroup label="Toán tử cho Sổ Đầu Bài (SĐB)">
                        <option value="SDB_COMB_ALL_NOT_TICKED">SĐB: TẤT CẢ mục chọn ĐỀU KHÔNG TICK (Không có cuốn sổ nào)</option>
                        <option value="SDB_IS_NOT_TICKED">SĐB: Có mục chọn KHÔNG TICK (Thiếu sổ trong các mục chọn)</option>
                        <option value="SDB_IS_TICKED">SĐB: Có ít nhất 1 mục chọn CÓ TICK</option>
                        <option value="SDB_COUNT_TICKED_EQUALS">Đếm SĐB: Số mục tick BẰNG ngưỡng</option>
                    </optgroup>
                </select>
            </div>
            <div class="w-full md:w-1/2 px-3 mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Ngưỡng giá trị so sánh</label>
                <input type="number" step="any" name="kxtd[__INDEX__][nguong_gia_tri]" class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-[#224397] focus:ring-1 focus:ring-[#224397] focus:outline-none shadow-sm transition-colors text-slate-700">
            </div>
            <div class="w-full md:w-1/2 px-3 mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Các mục Sổ Đầu Bài áp dụng</label>
                <div class="border border-slate-300 p-3 rounded-lg bg-slate-50 flex flex-wrap gap-4">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="w-4 h-4 text-[#224397] bg-white border-gray-300 rounded focus:ring-[#224397]" name="kxtd[__INDEX__][danh_sach_sdb][]" value="sdb_tt">
                        <span class="ml-2 text-sm text-slate-700">TT</span>
                    </label>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="w-4 h-4 text-[#224397] bg-white border-gray-300 rounded focus:ring-[#224397]" name="kxtd[__INDEX__][danh_sach_sdb][]" value="sdb_ck">
                        <span class="ml-2 text-sm text-slate-700">CK</span>
                    </label>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="w-4 h-4 text-[#224397] bg-white border-gray-300 rounded focus:ring-[#224397]" name="kxtd[__INDEX__][danh_sach_sdb][]" value="sdb_nk">
                        <span class="ml-2 text-sm text-slate-700">NK</span>
                    </label>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="w-4 h-4 text-[#224397] bg-white border-gray-300 rounded focus:ring-[#224397]" name="kxtd[__INDEX__][danh_sach_sdb][]" value="nhat_ky">
                        <span class="ml-2 text-sm text-slate-700">Nhật ký</span>
                    </label>
                </div>
            </div>
        </div>
        <div class="flex items-center justify-between mt-4 pt-4 border-t border-[#224397]/25">
            <label class="flex items-center cursor-pointer">
                <input type="checkbox" name="kxtd[__INDEX__][kich_hoat]" value="1" class="sr-only peer" checked>
                <div class="relative w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#224397]/30 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#224397]"></div>
                <span class="ms-3 text-sm font-bold text-slate-700">Kích hoạt điều kiện</span>
            </label>
            <button type="button" class="px-3 py-1.5 bg-white border border-red-200 text-red-600 rounded-lg hover:bg-red-50 hover:border-red-300 transition-all shadow-sm text-sm font-medium flex items-center gap-1.5 btn-remove-kxtd-condition ml-auto">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-trash3" viewBox="0 0 16 16"><path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47M8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5"/></svg> Xóa
            </button>
        </div>
    </div>
</template>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ----- START: LOGIC CHO VIỆC ẨN/HIỆN CẤU HÌNH ĐIỂM TRỪ VẮNG -----
    const sourceRadios = document.querySelectorAll('input[name="report_vang_source"]');
    const violationOptions = document.getElementById('violation-source-options');
    
    // Cập nhật: Thêm w-full max-w-6xl mx-auto px-4 của điểm trừ vắng
    const diemTruVangOptions = document.getElementById('diem-tru-vang-options'); // ID mới

    sourceRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (violationOptions) {
                violationOptions.style.display = this.value === 'vi_pham' ? 'block' : 'none';
            }
            if (diemTruVangOptions) {
                diemTruVangOptions.style.display = 'block';
            }
        });
    });
    // Kích hoạt lần đầu để đảm bảo trạng thái đúng khi tải trang
    document.querySelector('input[name="report_vang_source"]:checked').dispatchEvent(new Event('change'));
    // ----- END: LOGIC CHO VIỆC ẨN/HIỆN -----


    // ----- START: LOGIC CHO VIỆC THÊM/XÓA ĐIỀU KIỆN KXTĐ VÀ LƯU FORM -----
    const kxtdContainer = document.getElementById('kxtd-conditions');
    const addKxtdBtn = document.getElementById('btn-add-kxtd-condition');
    const kxtdTemplate = document.getElementById('template-kxtd-condition');
    let kxtdIndex = <?php echo count($dieu_kien_kxtd ?? []); ?>;

    function updateTruongSoSanhState(conditionEntry) {
        const toanTuSelect = conditionEntry.querySelector('select[name$="[toan_tu]"]');
        const truongSoSanhInput = conditionEntry.querySelector('input[name$="[truong_so_sanh]"]');
        if (!toanTuSelect || !truongSoSanhInput) return;

        const selectedOperator = toanTuSelect.value;
        
        // ===== DÒNG SỬA LỖI QUAN TRỌNG =====
        const isFieldRequired = !selectedOperator.startsWith('SDB_');

        truongSoSanhInput.required = isFieldRequired;
        truongSoSanhInput.disabled = !isFieldRequired;

        if (!isFieldRequired) {
            truongSoSanhInput.value = '';
            truongSoSanhInput.placeholder = '(Không áp dụng)';
        } else {
            truongSoSanhInput.placeholder = 'VD: vang_kp, diem_noi_quy';
        }
    }

    function addKxtdEntry() {
        const newEntryHtml = kxtdTemplate.innerHTML
            .replace(/__INDEX__/g, kxtdIndex)
            .replace(/__NUMBER__/g, kxtdIndex + 1);
        
        const div = document.createElement('div');
        div.innerHTML = newEntryHtml;
        const newEntryElement = div.firstElementChild;

        kxtdContainer.appendChild(newEntryElement);

        const newToanTuSelect = newEntryElement.querySelector('select[name$="[toan_tu]"]');
        if (newToanTuSelect) {
            newToanTuSelect.addEventListener('change', () => updateTruongSoSanhState(newEntryElement));
        }
        newEntryElement.querySelector('.btn-remove-kxtd-condition').addEventListener('click', removeKxtdEntry);
        
        updateTruongSoSanhState(newEntryElement);
        kxtdIndex++;
    }

    function removeKxtdEntry(event) {
        const entryDiv = event.target.closest('.kxtd-condition-entry');
        const idInput = entryDiv.querySelector('input[name$="[id]"]');

        if (idInput && idInput.value) {
            const deleteInput = document.createElement('input');
            deleteInput.type = 'hidden';
            deleteInput.name = 'kxtd[' + entryDiv.dataset.index + '][delete]';
            deleteInput.value = '1';
            entryDiv.appendChild(deleteInput);
            entryDiv.style.display = 'none';
        } else {
            entryDiv.remove();
        }
    }

    addKxtdBtn.addEventListener('click', addKxtdEntry);
    kxtdContainer.querySelectorAll('.kxtd-condition-entry').forEach(entry => {
        const toanTuSelect = entry.querySelector('select[name$="[toan_tu]"]');
        if (toanTuSelect) {
            toanTuSelect.addEventListener('change', () => updateTruongSoSanhState(entry));
            updateTruongSoSanhState(entry); // Chạy lần đầu khi tải trang
        }
        entry.querySelector('.btn-remove-kxtd-condition').addEventListener('click', removeKxtdEntry);
    });

    const form = document.getElementById('reportSettingsForm');
    form.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Nâng cấp: Thu thập dữ liệu từ các checkbox một cách tường minh
    const vang_p_vids = Array.from(document.querySelectorAll('input[name="report_vang_p_vids[]"]:checked')).map(cb => cb.value);
    const vang_kp_vids = Array.from(document.querySelectorAll('input[name="report_vang_kp_vids[]"]:checked')).map(cb => cb.value);

    const formData = new FormData(form);
    const settingsData = {};
    
    // Xử lý các checkbox SĐB
    settingsData['report_sdb_use_tt'] = formData.has('report_sdb_use_tt') ? 'on' : 'off';
    settingsData['report_sdb_use_ck'] = formData.has('report_sdb_use_ck') ? 'on' : 'off';
    settingsData['report_sdb_use_nk'] = formData.has('report_sdb_use_nk') ? 'on' : 'off';
    settingsData['report_sdb_use_nhat_ky'] = formData.has('report_sdb_use_nhat_ky') ? 'on' : 'off';

    // Lấy tất cả các cài đặt khác, TRỪ các checkbox vắng
    for (let [key, value] of formData.entries()) {
        if (!key.startsWith('kxtd[') && !key.startsWith('report_vang_') && key.indexOf('report_sdb_use_') === -1) {
             settingsData[key] = value;
        }
    }

    // Gán lại các cài đặt Vắng với dữ liệu đã thu thập từ checkbox
    settingsData['report_vang_source'] = formData.get('report_vang_source');
    settingsData['report_vang_p_vids'] = vang_p_vids;
    settingsData['report_vang_kp_vids'] = vang_kp_vids;
    
    const kxtdData = [];
    document.querySelectorAll('.kxtd-condition-entry').forEach(entry => {
        let isMarkedForDelete = entry.querySelector('input[name$="[delete]"]') && entry.querySelector('input[name$="[delete]"]').value === '1';
        if (isMarkedForDelete) {
            const idToDelete = entry.querySelector('input[name$="[id]"]').value;
            if (idToDelete) kxtdData.push({ id: idToDelete, delete: 1 });
            return;
        }
        const danh_sach_sdb = Array.from(entry.querySelectorAll('input[name$="[danh_sach_sdb][]"]:checked')).map(cb => cb.value);
        kxtdData.push({
            id: entry.querySelector('input[name$="[id]"]').value,
            ten_dieu_kien: entry.querySelector('input[name$="[ten_dieu_kien]"]').value,
            truong_so_sanh: entry.querySelector('input[name$="[truong_so_sanh]"]').value,
            toan_tu: entry.querySelector('select[name$="[toan_tu]"]').value,
            nguong_gia_tri: entry.querySelector('input[name$="[nguong_gia_tri]"]').value,
            kich_hoat: entry.querySelector('input[name$="[kich_hoat]"]').checked ? 1 : 0,
            danh_sach_sdb: danh_sach_sdb
        });
    });
    
    const payload = {
        settings: settingsData,
        kxtd_conditions: kxtdData
    };

    try {
        const response = await fetch('/thidua/admin/cau-hinh-bao-cao', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const result = await response.json();
        if(result.success) {
            showToast('success', result.message);
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast('error', result.message || 'Lỗi lưu cấu hình.');
        }
    } catch (error) {
        showToast('error', 'Lỗi kết nối đến máy chủ.');
    }
});
});
</script>
