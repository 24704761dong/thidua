<?php
// File: src/views/admin_cai_dat.php
$page_title = 'Cài Đặt Hệ Thống';
require_once __DIR__ . '/partials/admin_header.php';

/* Chuẩn hoá biến để tránh lỗi và warning */
$settings             = $settings ?? [];
$auto_grants          = (array)($settings['auto_grant_permissions_on_duty_approve'] ?? []);
$week_lock_password   = (string)($settings['week_lock_password'] ?? '');
?>

<div class="flex-1 overflow-y-auto bg-transparent p-6 min-h-screen">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 border-b border-[#224397]/25 pb-3 gap-4">
            <h3 class="text-xl font-bold text-[#224397] flex items-center gap-2 m-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-gear-wide-connected" viewBox="0 0 16 16"><path d="M7.068.727c.243-.97 1.62-.97 1.864 0l.071.286a.96.96 0 0 0 1.622.434l.205-.211c.695-.719 1.888-.03 1.613.931l-.08.284a.96.96 0 0 0 1.187 1.187l.283-.081c.96-.275 1.65.918.931 1.613l-.211.205a.96.96 0 0 0 .434 1.622l.286.071c.97.243.97 1.62 0 1.864l-.286.071a.96.96 0 0 0-.434 1.622l.211.205c.719.695.03 1.888-.931 1.613l-.284-.08a.96.96 0 0 0-1.187 1.187l.081.283c.275.96-.918 1.65-1.613.931l-.205-.211a.96.96 0 0 0-1.622.434l-.071.286c-.243.97-1.62.97-1.864 0l-.071-.286a.96.96 0 0 0-1.622-.434l-.205.211c-.695.719-1.888.03-1.613-.931l.08-.284a.96.96 0 0 0-1.186-1.187l-.284.081c-.96.275-1.65-.918-.931-1.613l.211-.205a.96.96 0 0 0-.434-1.622l-.286-.071c-.97-.243-.97-1.62 0-1.864l.286-.071a.96.96 0 0 0 .434-1.622l-.211-.205c-.719-.695-.03-1.888.931-1.613l.284.08a.96.96 0 0 0 1.187-1.186l-.081-.284c-.275-.96.918-1.65 1.613-.931l.205.211a.96.96 0 0 0 1.622-.434zM12.973 8.5H8.25l-2.834 3.779A4.998 4.998 0 0 0 12.973 8.5m0-1a4.998 4.998 0 0 0-7.557-3.779l2.834 3.78zM5.048 3.967l-.087.065zm-.431.355A4.98 4.98 0 0 0 3.002 8c0 1.455.622 2.765 1.615 3.678L7.375 8zm.344 7.646.087.065z"/></svg> CÀI ĐẶT HỆ THỐNG
            </h3>
            <div class="flex flex-wrap items-center gap-2">
                <a href="/thidua/admin/cau-hinh-vi-pham" class="px-4 py-2 bg-white border border-blue-200 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 font-medium flex items-center gap-2 text-sm shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-gear-fill" viewBox="0 0 16 16"><path d="M9.405 1.05c-.413-1.4-2.397-1.4-2.81 0l-.1.34a1.464 1.464 0 0 1-2.105.872l-.31-.17c-1.283-.698-2.686.705-1.987 1.987l.169.311c.446.82.023 1.841-.872 2.105l-.34.1c-1.4.413-1.4 2.397 0 2.81l.34.1a1.464 1.464 0 0 1 .872 2.105l-.17.31c-.698 1.283.705 2.686 1.987 1.987l.311-.169a1.464 1.464 0 0 1 2.105.872l.1.34c.413 1.4 2.397 1.4 2.81 0l.1-.34a1.464 1.464 0 0 1 2.105-.872l.31.17c1.283.698 2.686-.705 1.987-1.987l-.169-.311a1.464 1.464 0 0 1 .872-2.105l.34-.1c1.4-.413 1.4-2.397 0-2.81l-.34-.1a1.464 1.464 0 0 1-.872-2.105l.17-.31c.698-1.283-.705-2.686-1.987-1.987l-.311.169a1.464 1.464 0 0 1-2.105-.872zM8 10.93a2.929 2.929 0 1 1 0-5.86 2.929 2.929 0 0 1 0 5.858z"/></svg> Cấu Hình Vi Phạm
                </a>
                <a href="/thidua/admin/cau-hinh-bao-cao" class="px-4 py-2 bg-white border border-blue-200 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 font-medium flex items-center gap-2 text-sm shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-clipboard-data-fill" viewBox="0 0 16 16"><path d="M6.5 0A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0zm3 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5z"/>   <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1A2.5 2.5 0 0 1 9.5 5h-3A2.5 2.5 0 0 1 4 2.5zM10 8a1 1 0 1 1 2 0v5a1 1 0 1 1-2 0zm-6 4a1 1 0 1 1 2 0v1a1 1 0 1 1-2 0zm4-3a1 1 0 0 1 1 1v3a1 1 0 1 1-2 0v-3a1 1 0 0 1 1-1"/></svg> Cấu Hình Báo Cáo
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
            <!-- Tự Động Hóa & Đồng Bộ -->
            <div class="w-full bg-white rounded-xl shadow-sm border border-[#224397]/[45%] overflow-hidden">
                    <div class="bg-slate-50 px-5 py-3 border-b border-[#224397]/25 font-semibold text-[#224397] flex items-center gap-2 text-sm uppercase tracking-wide">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-robot" viewBox="0 0 16 16"><path d="M6 12.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5M3 8.062C3 6.76 4.235 5.765 5.53 5.886a26.6 26.6 0 0 0 4.94 0C11.765 5.765 13 6.76 13 8.062v1.157a.93.93 0 0 1-.765.935c-.845.147-2.34.346-4.235.346s-3.39-.2-4.235-.346A.93.93 0 0 1 3 9.219zm4.542-.827a.25.25 0 0 0-.217.068l-.92.9a25 25 0 0 1-1.871-.183.25.25 0 0 0-.068.495c.55.076 1.232.149 2.02.193a.25.25 0 0 0 .189-.071l.754-.736.847 1.71a.25.25 0 0 0 .404.062l.932-.97a25 25 0 0 0 1.922-.188.25.25 0 0 0-.068-.495c-.538.074-1.207.145-1.98.189a.25.25 0 0 0-.166.076l-.754.785-.842-1.7a.25.25 0 0 0-.182-.135"/>   <path d="M8.5 1.866a1 1 0 1 0-1 0V3h-2A4.5 4.5 0 0 0 1 7.5V8a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1v1a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-1a1 1 0 0 0 1-1V9a1 1 0 0 0-1-1v-.5A4.5 4.5 0 0 0 10.5 3h-2zM14 7.5V13a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V7.5A3.5 3.5 0 0 1 5.5 4h5A3.5 3.5 0 0 1 14 7.5"/></svg> Tự Động Hóa
                    </div>
                    <div class="divide-y divide-[#224397]/20">
                        <div class="px-5 py-4 flex justify-between items-center hover:bg-slate-50 transition-colors">
                            <div class="pr-4">
                                <h6 class="font-semibold text-slate-800 text-sm m-0">Tự động duyệt Vi phạm do CTV gửi</h6>
                                <p class="text-slate-500 text-xs mt-1 mb-0">Khi BẬT: Vi phạm do CTV gửi sẽ được duyệt ngay lập tức.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                <input type="checkbox" class="sr-only peer setting-toggle" data-key="auto_approve_violations" <?= (($settings['auto_approve_violations'] ?? 'off') === 'on') ? 'checked' : ''; ?>>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#224397]"></div>
                            </label>
                        </div>

                        <div class="px-5 py-4 hover:bg-slate-50 transition-colors">
                            <div class="flex justify-between items-center">
                                <div class="pr-4">
                                    <h6 class="font-semibold text-slate-800 text-sm m-0">Tự động duyệt Lịch đăng ký trực</h6>
                                    <p class="text-slate-500 text-xs mt-1 mb-0">Khi BẬT: Lịch trực do CTV gửi sẽ được duyệt ngay lập tức.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                    <input type="checkbox" class="sr-only peer setting-toggle" data-key="auto_approve_duty_roster" id="auto_approve_duty_roster" <?= (($settings['auto_approve_duty_roster'] ?? 'off') === 'on') ? 'checked' : ''; ?>>
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#224397]"></div>
                                </label>
                            </div>

                            <!-- Nhóm phụ thuộc -->
                            <div id="auto-grant-permissions-section" class="mt-4 pt-4 border-t border-[#224397]/25 transition-all" style="<?= (($settings['auto_approve_duty_roster'] ?? 'off') === 'on') ? 'display:block;' : 'display:none;'; ?>">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">Quyền tự động cấp cho học sinh trực:</label>
                                <div class="space-y-2">
                                    <label class="flex items-center gap-2 cursor-pointer group">
                                        <input type="checkbox" class="w-4 h-4 text-[#224397] border-slate-300 rounded focus:ring-[#224397] permission-select" value="nhap_vi_pham" <?= in_array('nhap_vi_pham', $auto_grants, true) ? 'checked' : ''; ?>>
                                        <span class="text-sm text-slate-700 group-hover:text-[#224397] transition-colors">Quyền Nhập Vi phạm</span>
                                    </label>
                            
                                    <label class="flex items-center gap-2 cursor-pointer group">
                                        <input type="checkbox" class="w-4 h-4 text-[#224397] border-slate-300 rounded focus:ring-[#224397] permission-select" value="dang_ky_truc" <?= in_array('dang_ky_truc', $auto_grants, true) ? 'checked' : ''; ?>>
                                        <span class="text-sm text-slate-700 group-hover:text-[#224397] transition-colors">Quyền Đăng ký trực</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <!-- Quyền Hạn Học Sinh -->
            <div class="w-full bg-white rounded-xl shadow-sm border border-[#224397]/[45%] overflow-hidden">
                    <div class="bg-slate-50 px-5 py-3 border-b border-[#224397]/25 font-semibold text-[#224397] flex items-center gap-2 text-sm uppercase tracking-wide">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-person-check-fill" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L12.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0"/>   <path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/></svg> Quyền Hạn Học Sinh
                    </div>
                    <div class="divide-y divide-[#224397]/20">
                        <div class="px-5 py-4 flex justify-between items-center hover:bg-slate-50 transition-colors">
                            <div class="pr-4">
                                <h6 class="font-semibold text-slate-800 text-sm m-0">Cho phép tự sửa Số điện thoại</h6>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                <input type="checkbox" class="sr-only peer setting-toggle" data-key="student_can_edit_sdt" <?= (($settings['student_can_edit_sdt'] ?? 'off') === 'on') ? 'checked' : ''; ?>>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#224397]"></div>
                            </label>
                        </div>
                        <div class="px-5 py-4 flex justify-between items-center hover:bg-slate-50 transition-colors">
                            <div class="pr-4">
                                <h6 class="font-semibold text-slate-800 text-sm m-0">Cho phép tự sửa Email</h6>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                <input type="checkbox" class="sr-only peer setting-toggle" data-key="student_can_edit_email" <?= (($settings['student_can_edit_email'] ?? 'on') === 'on') ? 'checked' : ''; ?>>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#224397]"></div>
                            </label>
                        </div>
                        <div class="px-5 py-4 flex justify-between items-center hover:bg-slate-50 transition-colors">
                            <div class="pr-4">
                                <h6 class="font-semibold text-slate-800 text-sm m-0">Cho phép tự sửa Chức vụ</h6>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                <input type="checkbox" class="sr-only peer setting-toggle" data-key="student_can_edit_chuc_vu" <?= (($settings['student_can_edit_chuc_vu'] ?? 'off') === 'on') ? 'checked' : ''; ?>>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#224397]"></div>
                            </label>
                        </div>
                    </div>
                </div>

            <!-- Tra Cứu & Đăng Nhập -->
            <div class="w-full bg-white rounded-xl shadow-sm border border-[#224397]/[45%] overflow-hidden">
                    <div class="bg-slate-50 px-5 py-3 border-b border-[#224397]/25 font-semibold text-[#224397] flex items-center gap-2 text-sm uppercase tracking-wide">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-door-open-fill" viewBox="0 0 16 16"><path d="M1.5 15a.5.5 0 0 0 0 1h13a.5.5 0 0 0 0-1H13V2.5A1.5 1.5 0 0 0 11.5 1H11V.5a.5.5 0 0 0-.57-.495l-7 1A.5.5 0 0 0 3 1.5V15zM11 2h.5a.5.5 0 0 1 .5.5V15h-1zm-2.5 8c-.276 0-.5-.448-.5-1s.224-1 .5-1 .5.448.5 1-.224 1-.5 1"/></svg> Tra Cứu & Đăng Nhập
                    </div>
                    <div class="divide-y divide-[#224397]/20">
                        <div class="px-5 py-4 bg-slate-50/50 flex justify-between items-center hover:bg-slate-50 transition-colors">
                            <div class="pr-4">
                                <h6 class="font-bold text-[#224397] text-sm m-0">Thiết lập Năm học tra cứu công khai</h6>
                                <p class="text-slate-500 text-xs mt-1 mb-0">Dữ liệu hiển thị trang chủ sẽ áp dụng theo năm học này.</p>
                            </div>
                            <div class="shrink-0">
                                <select class="block w-40 rounded-lg border-slate-300 shadow-sm focus:border-[#224397] focus:ring focus:ring-[#224397] focus:ring-opacity-20 px-3 py-2 text-sm font-medium outline-none transition-colors" id="public_lookup_nam_hoc_id" onchange="saveSetting('public_lookup_nam_hoc_id', this.value, this)">
                                    <option value="0">--- Chọn ---</option>
                                    <?php foreach($danh_sach_nam_hoc as $nh): ?>
                                        <option value="<?= $nh['id'] ?>" <?= (($settings['public_lookup_nam_hoc_id'] ?? '') == $nh['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($nh['ten_nam_hoc']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="px-5 py-4 flex justify-between items-center hover:bg-slate-50 transition-colors">
                            <div class="pr-4">
                                <h6 class="font-semibold text-slate-800 text-sm m-0">Tra cứu công khai theo CCCD</h6>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                <input type="checkbox" class="sr-only peer setting-toggle" data-key="allow_student_lookup" <?= (($settings['allow_student_lookup'] ?? 'on') === 'on') ? 'checked' : ''; ?>>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#224397]"></div>
                            </label>
                        </div>

                        <div class="px-5 py-4 flex justify-between items-center hover:bg-slate-50 transition-colors">
                            <div class="pr-4">
                                <h6 class="font-semibold text-slate-800 text-sm m-0">Tra cứu công khai theo Mã GV</h6>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                <input type="checkbox" class="sr-only peer setting-toggle" data-key="allow_teacher_lookup" <?= (($settings['allow_teacher_lookup'] ?? 'on') === 'on') ? 'checked' : ''; ?>>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#224397]"></div>
                            </label>
                        </div>

                        <div class="px-5 py-4 flex justify-between items-center hover:bg-slate-50 transition-colors">
                            <div class="pr-4">
                                <h6 class="font-semibold text-slate-800 text-sm m-0">Cho phép tất cả HS đăng nhập</h6>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                <input type="checkbox" class="sr-only peer setting-toggle" data-key="allow_all_students_login" <?= (($settings['allow_all_students_login'] ?? 'off') === 'on') ? 'checked' : ''; ?>>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#224397]"></div>
                            </label>
                        </div>
                    </div>
                </div>

            <!-- Cấu Hình Địa Chỉ -->
            <div class="w-full bg-white rounded-xl shadow-sm border border-[#224397]/[45%] overflow-hidden">
                <div class="bg-slate-50 px-5 py-3 border-b border-[#224397]/25 font-semibold text-[#224397] flex items-center gap-2 text-sm uppercase tracking-wide">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-geo-alt-fill" viewBox="0 0 16 16"><path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10m0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6"/></svg> Cấu Hình Địa Chỉ (Học Sinh)
                </div>
                <div class="p-5">
                    <label class="block text-sm font-semibold text-slate-800 mb-2">Danh sách Xã/Phường và Ấp/Khu phố (Thành phố Đồng Nai)</label>
                    <p class="text-xs text-slate-500 mb-3">Nhập theo định dạng: <code>Tên Xã/Phường: Ấp 1, Ấp 2, Ấp 3</code> (mỗi Xã/Phường 1 dòng).</p>
                    <textarea id="dia_chi_options" rows="6" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-[#224397] focus:ring focus:ring-[#224397] focus:ring-opacity-20 p-3 text-sm outline-none transition-colors" placeholder="Phường Trảng Dài: Khu phố 1, Khu phố 2&#10;Xã Bình Sơn: Ấp 1, Ấp 2"><?= htmlspecialchars($settings['dia_chi_options'] ?? '') ?></textarea>
                    <div class="mt-3 flex justify-end">
                        <button type="button" onclick="saveSetting('dia_chi_options', document.getElementById('dia_chi_options').value, this)" class="px-4 py-2 bg-white border border-blue-200 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 font-medium flex items-center gap-2 text-sm shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-floppy" viewBox="0 0 16 16"><path d="M11 2H9v3h2z"/><path d="M1.5 0h11.586a1.5 1.5 0 0 1 1.06.44l1.415 1.414A1.5 1.5 0 0 1 16 2.914V14.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 14.5v-13A1.5 1.5 0 0 1 1.5 0M1 1.5v13a.5.5 0 0 0 .5.5H2v-4.5A1.5 1.5 0 0 1 3.5 9h9a1.5 1.5 0 0 1 1.5 1.5V15h.5a.5.5 0 0 0 .5-.5V2.914a.5.5 0 0 0-.146-.353l-1.415-1.415A.5.5 0 0 0 13.086 1H13v4.5A1.5 1.5 0 0 1 11.5 7h-7A1.5 1.5 0 0 1 3 5.5V1H1.5a.5.5 0 0 0-.5.5m3 4a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5V1H4zM3 15h10v-4.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5z"/></svg> Lưu
                        </button>
                    </div>
                </div>
            </div>

            <!-- Bảo Mật -->
            <div class="w-full bg-white rounded-xl shadow-sm border border-[#224397]/[45%] overflow-hidden">
                    <div class="bg-slate-50 px-5 py-3 border-b border-[#224397]/25 font-semibold text-[#224397] flex items-center gap-2 text-sm uppercase tracking-wide">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-shield-lock-fill" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 0c-.69 0-1.843.265-2.928.56-1.11.3-2.229.655-2.887.87a1.54 1.54 0 0 0-1.044 1.262c-.596 4.477.787 7.795 2.465 9.99a11.8 11.8 0 0 0 2.517 2.453c.386.273.744.482 1.048.625.28.132.581.24.829.24s.548-.108.829-.24a7 7 0 0 0 1.048-.625 11.8 11.8 0 0 0 2.517-2.453c1.678-2.195 3.061-5.513 2.465-9.99a1.54 1.54 0 0 0-1.044-1.263 63 63 0 0 0-2.887-.87C9.843.266 8.69 0 8 0m0 5a1.5 1.5 0 0 1 .5 2.915l.385 1.99a.5.5 0 0 1-.491.595h-.788a.5.5 0 0 1-.49-.595l.384-1.99A1.5 1.5 0 0 1 8 5"/></svg> Bảo Mật Nâng Cao
                    </div>
                    <div class="divide-y divide-[#224397]/20">
                        <div class="px-5 py-4 hover:bg-slate-50 transition-colors">
                            <div class="mb-3">
                                <h6 class="font-semibold text-slate-800 text-sm m-0">Thời gian tự động đăng xuất (Admin)</h6>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-48">
                                    <input type="number" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-[#224397] focus:ring focus:ring-[#224397] focus:ring-opacity-20 px-3 py-2 text-sm font-medium outline-none transition-colors" id="auto_logout_duration" value="<?= htmlspecialchars($settings['auto_logout_duration'] ?? '1800'); ?>">
                                </div>
                                <button type="button" id="save-logout-duration" class="w-24 px-4 py-2 bg-white border border-blue-200 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 font-medium flex items-center justify-center gap-2 text-sm shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-floppy" viewBox="0 0 16 16"><path d="M11 2H9v3h2z"/>   <path d="M1.5 0h11.586a1.5 1.5 0 0 1 1.06.44l1.415 1.414A1.5 1.5 0 0 1 16 2.914V14.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 14.5v-13A1.5 1.5 0 0 1 1.5 0M1 1.5v13a.5.5 0 0 0 .5.5H2v-4.5A1.5 1.5 0 0 1 3.5 9h9a1.5 1.5 0 0 1 1.5 1.5V15h.5a.5.5 0 0 0 .5-.5V2.914a.5.5 0 0 0-.146-.353l-1.415-1.415A.5.5 0 0 0 13.086 1H13v4.5A1.5 1.5 0 0 1 11.5 7h-7A1.5 1.5 0 0 1 3 5.5V1H1.5a.5.5 0 0 0-.5.5m3 4a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5V1H4zM3 15h10v-4.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5z"/></svg> Lưu
                                </button>
                            </div>
                        </div>

                        <div class="px-5 py-4 hover:bg-slate-50 transition-colors">
                            <div class="mb-3">
                                <h6 class="font-semibold text-slate-800 text-sm m-0">Bảo mật Nhập liệu Tuần</h6>
                                <p class="text-slate-500 text-xs mt-1 mb-0">Mật khẩu đ quản trị viên mở khóa các tuần học đã chốt sổ.</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="relative w-48">
                                    <input type="password" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-[#224397] focus:ring focus:ring-[#224397] focus:ring-opacity-20 px-3 py-2 pr-10 text-sm font-medium outline-none transition-colors" id="week_lock_password" value="<?= htmlspecialchars($week_lock_password); ?>">
                                    <button type="button" id="toggle-lock-password" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-[#224397] transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>   <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/></svg>
                                    </button>
                                </div>
                                <button type="button" id="save-lock-password" class="w-24 px-4 py-2 bg-white border border-blue-200 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 font-medium flex items-center justify-center gap-2 text-sm shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-key" viewBox="0 0 16 16"><path d="M0 8a4 4 0 0 1 7.465-2H14a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0L13 9.207l-.646.647a.5.5 0 0 1-.708 0L11 9.207l-.646.647a.5.5 0 0 1-.708 0L9 9.207l-.646.647A.5.5 0 0 1 8 10h-.535A4 4 0 0 1 0 8m4-3a3 3 0 1 0 2.712 4.285A.5.5 0 0 1 7.163 9h.63l.853-.854a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.793-.793-1-1h-6.63a.5.5 0 0 1-.451-.285A3 3 0 0 0 4 5"/>   <path d="M4 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/></svg> Đổi
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            <!-- Kết nối Zalo OA -->
            <?php
            // Đọc token và thời gian cập nhật từ DB
            $zalo_token_status  = 'unknown';
            $zalo_token_preview = '';
            $zalo_token_updated = null; // timestamp lần cuối cập nhật
            $current_token      = '';

            try {
                // Lấy cả access token và thời gian cập nhật
                $stmt_tok = $db->prepare(
                    "SELECT setting_key, setting_value, updated_at
                     FROM settings
                     WHERE setting_key IN ('zalo_oa_access_token','zalo_oa_refresh_token')
                     ORDER BY setting_key"
                );
                $stmt_tok->execute();
                $tok_rows = $stmt_tok->fetchAll(PDO::FETCH_ASSOC);

                foreach ($tok_rows as $row) {
                    if ($row['setting_key'] === 'zalo_oa_access_token') {
                        $current_token      = $row['setting_value'] ?? '';
                        $zalo_token_updated = $row['updated_at'] ?? null;
                    }
                }

                if (!empty($current_token)) {
                    $zalo_token_status  = 'has_token';
                    $zalo_token_preview = substr($current_token, 0, 12) . '...' . substr($current_token, -8);
                } else {
                    $zalo_token_status = 'no_token';
                }
            } catch (\Throwable $e) {
                $zalo_token_status = 'error';
            }

            // Tính "tuổi" token dựa trên updated_at (Access Token sống 25h = 90000s)
            $token_age_seconds  = null;
            $token_age_label    = '';
            $zalo_token_expired = false; // true nếu chắc chắn hết hạn

            if ($zalo_token_updated && $zalo_token_status === 'has_token') {
                $updated_ts        = strtotime($zalo_token_updated);
                $token_age_seconds = time() - $updated_ts;
                $hours_ago         = round($token_age_seconds / 3600, 1);

                if ($token_age_seconds < 3600) {
                    $token_age_label = 'vừa cập nhật ' . round($token_age_seconds / 60) . ' phút trước';
                } elseif ($token_age_seconds < 86400) {
                    $token_age_label = "cập nhật {$hours_ago} giờ trước";
                } else {
                    $days = round($token_age_seconds / 86400, 1);
                    $token_age_label = "cập nhật {$days} ngày trước";
                }

                // Access Token hết hạn sau 25h (90000s) — thêm 10% buffer
                $zalo_token_expired = ($token_age_seconds > 81000); // 22.5 giờ
            }
            ?>
            <div class="break-inside-avoid inline-block w-full bg-white rounded-xl shadow-sm border border-[#224397]/[45%] overflow-hidden mb-6">
                <div class="bg-slate-50 px-5 py-3 border-b border-[#224397]/25 font-semibold text-[#224397] flex items-center gap-2 text-sm uppercase tracking-wide">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm4.93 7.44l-1.42 6.68c-.1.46-.38.57-.77.35l-2.13-1.57-1.03 .99c-.11.11-.21.2-.43.2l.15-2.18 3.97-3.59c.17-.15-.04-.24-.27-.08L7.84 13.73l-2.08-.65c-.45-.14-.46-.45.1-.67l8.13-3.13c.37-.14.7.09.58.66l-.02.02-.62 3.08z"/></svg>
                    Zalo OA — Kết nối & Quản lý Token
                </div>
                <div class="divide-y divide-[#224397]/20">
                    <!-- Trạng thái token -->
                    <div class="px-5 py-4">
                        <h6 class="font-semibold text-slate-800 text-sm m-0 mb-2">Trạng thái Token ZNS</h6>

                        <?php if ($zalo_token_status === 'no_token' || $zalo_token_status === 'error'): ?>
                            <div class="flex items-center gap-2 text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2 mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z"/></svg>
                                <span><strong>Chưa có token</strong> — Hệ thống chưa thể gửi ZNS qua Zalo. Vui lòng kết nối.</span>
                            </div>

                        <?php elseif ($zalo_token_expired): ?>
                            <div class="flex items-center gap-2 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 16 16"><path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/></svg>
                                <span>
                                    <strong>Access Token có thể đã hết hạn</strong>
                                    <?php if ($token_age_label): ?> — <em><?= htmlspecialchars($token_age_label) ?></em><?php endif; ?>
                                    <br><small class="text-amber-600">Hệ thống sẽ tự refresh khi gửi ZNS. Nếu vẫn lỗi, click "Thử Refresh Token".</small>
                                    <code class="text-xs bg-amber-100 px-1 rounded ml-1"><?= htmlspecialchars($zalo_token_preview) ?></code>
                                </span>
                            </div>

                        <?php else: ?>
                            <div class="flex items-center gap-2 text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg px-3 py-2 mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
                                <span>
                                    <strong>Token hợp lệ</strong>
                                    <?php if ($token_age_label): ?> — <em><?= htmlspecialchars($token_age_label) ?></em><?php endif; ?>
                                    <code class="text-xs bg-green-100 px-1 rounded ml-1"><?= htmlspecialchars($zalo_token_preview) ?></code>
                                </span>
                            </div>
                        <?php endif; ?>

                        <p class="text-slate-500 text-xs mb-3">
                            Click <strong>"Kết nối / Gia hạn Token Zalo OA"</strong> để hệ thống tự động lấy token mới thông qua Zalo OAuth. Token sẽ được lưu vào CSDL ngay sau khi xác thực.
                        </p>

                        <div class="flex flex-wrap gap-2">
                            <a href="/thidua/oauth-redirect-zalo-oa"
                               id="btn-connect-zalo-oa"
                               target="_top"
                               class="inline-flex items-center gap-2 px-4 py-2 bg-[#0068ff] text-white rounded-lg hover:bg-[#0055cc] hover:scale-[1.02] transition-all duration-200 font-semibold text-sm shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm4.93 7.44l-1.42 6.68c-.1.46-.38.57-.77.35l-2.13-1.57-1.03.99c-.11.11-.21.2-.43.2l.15-2.18 3.97-3.59c.17-.15-.04-.24-.27-.08L7.84 13.73l-2.08-.65c-.45-.14-.46-.45.1-.67l8.13-3.13c.37-.14.7.09.58.66l-.02.02-.62 3.08z"/></svg>
                                <?= ($zalo_token_status === 'no_token') ? 'Kết nối Zalo OA ngay' : 'Gia hạn / Kết nối lại' ?>
                            </a>

                            <?php if ($zalo_token_status === 'has_token'): ?>
                            <button type="button" id="btn-try-refresh-zalo"
                                    onclick="tryRefreshZaloToken(this)"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-amber-400 text-amber-700 rounded-lg hover:bg-amber-50 transition-all duration-200 font-semibold text-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 16 16"><path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41m-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9"/><path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0 0 8 3M3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9z"/></svg>
                                Thử Refresh Token
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>

        
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Toast Container -->
<div id="toast-container" class="fixed bottom-4 right-4 z-[9999] flex flex-col gap-2"></div>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>

<script>
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-emerald-500' : 'bg-red-500';
    toast.className = `px-4 py-3 rounded shadow-lg text-white font-medium text-sm transition-all duration-300 transform translate-y-10 opacity-0 flex items-center gap-2 ${bgColor}`;
    toast.innerHTML = type === 'success' 
        ? `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg> ${message}`
        : `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4zm.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/></svg> ${message}`;
    
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.remove('translate-y-10', 'opacity-0');
        toast.classList.add('translate-y-0', 'opacity-100');
    }, 10);

    setTimeout(() => {
        toast.classList.remove('translate-y-0', 'opacity-100');
        toast.classList.add('translate-y-10', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

window.saveSetting = async function(key, value, el) {
  let originalHtml = '';
  if (el) {
      el.disabled = true;
      if (el.tagName === 'BUTTON') {
          originalHtml = el.innerHTML;
          el.innerHTML = `<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-current inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Đang lưu...`;
      }
  }
  
  try {
    const res = await fetch('/thidua/api/toggle-setting', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify({ key, value })
    });
    const data = await res.json();
    if (!data.success) {
      showToast('Lỗi: ' + (data.message || 'Không thành công'), 'error');
      if (el && el.type === 'checkbox') el.checked = !el.checked;
    } else {
      showToast('Cập nhật thành công!');
      if (el && el.tagName === 'SELECT') {
        const orig = el.style.borderColor;
        el.style.borderColor = '#10b981'; // emerald-500
        setTimeout(() => { el.style.borderColor = orig; }, 1200);
      }
    }
  } catch (err) {
    showToast('Lỗi kết nối đến máy chủ.', 'error');
    if (el && el.type === 'checkbox') el.checked = !el.checked;
  } finally {
    if (el) {
        el.disabled = false;
        if (el.tagName === 'BUTTON') {
            el.innerHTML = originalHtml;
        }
    }
  }
};

document.addEventListener('DOMContentLoaded', () => {
  const $ = (sel, ctx=document) => ctx.querySelector(sel);
  const $$ = (sel, ctx=document) => Array.from(ctx.querySelectorAll(sel));

  $('#toggle-lock-password')?.addEventListener('click', () => {
    const ip = $('#week_lock_password');
    if (!ip) return;
    ip.type = (ip.type === 'password') ? 'text' : 'password';
  });

  $('#save-lock-password')?.addEventListener('click', function(){
    const pwd = $('#week_lock_password')?.value || '';
    if (!pwd) { alert('Mật khẩu không được để trống.'); return; }
    saveSetting('week_lock_password', pwd, this);
  });

  $$('.setting-toggle').forEach(tg => {
    tg.addEventListener('change', function(){
      saveSetting(this.dataset.key, this.checked ? 'on' : 'off', this);
      if (this.dataset.key === 'auto_approve_duty_roster') {
        const box = $('#auto-grant-permissions-section');
        if (box) {
            if (this.checked) {
                box.style.display = 'block';
            } else {
                box.style.display = 'none';
            }
        }
      }
    });
  });

  function saveAutoGrantPermissions(){
    const selected = $$('.permission-select:checked').map(cb => cb.value);
    const trigger = $('#auto_approve_duty_roster');
    saveSetting('auto_grant_permissions_on_duty_approve', selected, trigger);
  }
  $$('.permission-select').forEach(cb => cb.addEventListener('change', saveAutoGrantPermissions));

  $('#save-logout-duration')?.addEventListener('click', function(){
    const duration = $('#auto_logout_duration')?.value || '1800';
    if (!duration) { alert('Thời gian không được để trống.'); return; }
    saveSetting('auto_logout_duration', duration, this);
    if (window.top) {
      window.top.AUTO_LOGOUT_DURATION = parseInt(duration);
      if (typeof window.top.resetInactivityTimer === 'function') {
        window.top.resetInactivityTimer();
      }
    }
  });
});

async function tryRefreshZaloToken(btn) {
    if (!btn) return;
    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 16 16"><path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41m-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9"/><path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0 0 8 3M3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9z"/></svg> Đang thử...';
    try {
        const res = await fetch('/thidua/api/zalo-oa-refresh-token', { method: 'POST', credentials: 'same-origin' });
        const data = await res.json();
        if (data.success) {
            btn.innerHTML = '✅ Refresh thành công!';
            btn.classList.add('bg-green-50', 'border-green-400', 'text-green-700');
            setTimeout(() => location.reload(), 1500);
        } else {
            btn.disabled = false;
            btn.innerHTML = '❌ ' + (data.message || 'Refresh thất bại — Cần kết nối lại OAuth');
        }
    } catch(e) {
        btn.disabled = false;
        btn.innerHTML = '❌ Lỗi kết nối';
    }
}
</script>
